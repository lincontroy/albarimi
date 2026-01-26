<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class JobController extends Controller
{
    // Main jobs page
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get filters
        $category = $request->input('category', '');
        $jobType = $request->input('job_type', '');
        $experience = $request->input('experience', '');
        $search = $request->input('search', '');
        $remote = $request->input('remote', false);
        $featured = $request->input('featured', false);

        // Build query
        $query = Job::with('category')->active();

        if ($category) {
            $query->where('category_id', $category);
        }

        if ($jobType) {
            $query->where('job_type', $jobType);
        }

        if ($experience) {
            $query->where('experience_level', $experience);
        }

        if ($remote) {
            $query->where('is_remote', true);
        }

        if ($featured) {
            $query->where('is_featured', true);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Get jobs
        $jobs = $query->orderBy('is_featured', 'desc')
            ->orderBy('published_at', 'desc')
            ->paginate(12)
            ->through(function ($job) use ($user) {
                return [
                    'id' => $job->id,
                    'title' => $job->title,
                    'slug' => $job->slug,
                    'description' => $job->description,
                    'salary' => $job->getFormattedSalary(),
                    'location' => $job->location,
                    'job_type' => $job->getJobTypeText(),
                    'experience_level' => $job->getExperienceLevelText(),
                    'is_remote' => $job->is_remote,
                    'is_featured' => $job->is_featured,
                    'category' => [
                        'name' => $job->category->name,
                        'color' => $job->category->color,
                        'icon' => $job->category->icon,
                    ],
                    'view_count' => $job->view_count,
                    'application_count' => $job->application_count,
                    'vacancies' => $job->vacancies,
                    'days_remaining' => $job->getDaysRemaining(),
                    'status_badge' => $job->getStatusBadge(),
                    'has_applied' => $user ? $job->userApplications()->exists() : false,
                    'can_apply' => $job->isActive() && $job->hasVacancies(),
                    'created_at' => $job->created_at->format('M d, Y'),
                ];
            });

        // Get categories for filter
        $categories = JobCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'color' => $category->color,
                    'icon' => $category->icon,
                    'job_count' => $category->activeJobs()->count(),
                ];
            });

        // Get featured jobs for sidebar
        $featuredJobs = Job::with('category')
            ->active()
            ->featured()
            ->orderBy('published_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($job) {
                return [
                    'id' => $job->id,
                    'title' => $job->title,
                    'salary' => $job->getFormattedSalary(),
                    'location' => $job->location,
                    'category_name' => $job->category->name,
                    'category_color' => $job->category->color,
                ];
            });

        // Get job types for filter
        $jobTypes = JobCategory::$jobTypes;
        $experienceLevels = JobCategory::$experienceLevels;

        return Inertia::render('Jobs/Index', [
            'jobs' => $jobs,
            'categories' => $categories,
            'featuredJobs' => $featuredJobs,
            'jobTypes' => $jobTypes,
            'experienceLevels' => $experienceLevels,
            'filters' => [
                'category' => $category,
                'job_type' => $jobType,
                'experience' => $experience,
                'search' => $search,
                'remote' => $remote,
                'featured' => $featured,
            ],
            'userBalance' => $user->deposit_balance ?? 0,
            'hasAppliedJobs' => $user ? JobApplication::where('user_id', $user->id)->exists() : false,
        ]);
    }

    // Available jobs (similar to index but simpler)
    public function available()
    {
        $user = Auth::user();
        
        $jobs = Job::with('category')
            ->active()
            ->orderBy('published_at', 'desc')
            ->paginate(16)
            ->through(function ($job) use ($user) {
                return [
                    'id' => $job->id,
                    'title' => $job->title,
                    'slug' => $job->slug,
                    'description' => $job->description,
                    'salary' => $job->getFormattedSalary(),
                    'location' => $job->location,
                    'job_type' => $job->getJobTypeText(),
                    'is_remote' => $job->is_remote,
                    'category' => [
                        'name' => $job->category->name,
                        'color' => $job->category->color,
                    ],
                    'days_remaining' => $job->getDaysRemaining(),
                    'has_applied' => $user ? $job->userApplications()->exists() : false,
                    'can_apply' => $job->isActive() && $job->hasVacancies(),
                ];
            });

        return Inertia::render('Jobs/Available', [
            'jobs' => $jobs,
            'userBalance' => $user->deposit_balance ?? 0,
        ]);
    }

    // Show single job
    public function show(Job $job)
    {
        $user = Auth::user();
        
        // Increment view count
        $job->incrementViewCount();

        // Check if user has applied
        $hasApplied = $user ? $job->userApplications()->exists() : false;
        $application = $hasApplied ? $job->userApplications()->first() : null;

        // Get related jobs
        $relatedJobs = Job::with('category')
            ->where('category_id', $job->category_id)
            ->where('id', '!=', $job->id)
            ->active()
            ->orderBy('published_at', 'desc')
            ->limit(4)
            ->get()
            ->map(function ($relatedJob) {
                return [
                    'id' => $relatedJob->id,
                    'title' => $relatedJob->title,
                    'salary' => $relatedJob->getFormattedSalary(),
                    'location' => $relatedJob->location,
                    'job_type' => $relatedJob->getJobTypeText(),
                    'category_name' => $relatedJob->category->name,
                ];
            });

        $jobData = [
            'id' => $job->id,
            'title' => $job->title,
            'description' => $job->description,
            'requirements' => $job->requirements ?? [],
            'benefits' => $job->benefits ?? [],
            'salary' => $job->getFormattedSalary(),
            'salary_raw' => $job->salary,
            'salary_type' => $job->salary_type,
            'location' => $job->location,
            'job_type' => $job->getJobTypeText(),
            'job_type_raw' => $job->job_type,
            'experience_level' => $job->getExperienceLevelText(),
            'experience_level_raw' => $job->experience_level,
            'education_level' => $job->education_level,
            'is_remote' => $job->is_remote,
            'is_featured' => $job->is_featured,
            'vacancies' => $job->vacancies,
            'application_count' => $job->application_count,
            'view_count' => $job->view_count,
            'application_deadline' => $job->application_deadline?->format('M d, Y'),
            'days_remaining' => $job->getDaysRemaining(),
            'status_badge' => $job->getStatusBadge(),
            'category' => [
                'id' => $job->category->id,
                'name' => $job->category->name,
                'color' => $job->category->color,
                'icon' => $job->category->icon,
            ],
            'created_at' => $job->created_at->format('M d, Y'),
            'has_applied' => $hasApplied,
            'application_status' => $application ? $application->getStatusText() : null,
            'application_color' => $application ? $application->getStatusColor() : null,
            'can_apply' => $job->isActive() && $job->hasVacancies() && !$hasApplied,
        ];

        return Inertia::render('Jobs/Show', [
            'job' => $jobData,
            'relatedJobs' => $relatedJobs,
            'userBalance' => $user->deposit_balance ?? 0,
        ]);
    }

    // Apply for a job
    public function apply(Request $request, Job $job)
    {
        $user = Auth::user();
        
        // Check if job is active
        if (!$job->isActive()) {
            return back()->with([
                'flash' => [
                    'error' => 'This job is no longer accepting applications.'
                ]
            ]);
        }

        // Check if job has vacancies
        if (!$job->hasVacancies()) {
            return back()->with([
                'flash' => [
                    'error' => 'All vacancies for this position have been filled.'
                ]
            ]);
        }

        // Check if user has already applied
        if ($job->userApplications()->exists()) {
            return back()->with([
                'flash' => [
                    'error' => 'You have already applied for this job.'
                ]
            ]);
        }

        // Check user balance (1000 KES application fee)
        $applicationFee = 1000;
        if ($user->deposit_balance < $applicationFee) {
            return back()->with([
                'flash' => [
                    'error' => 'Insufficient balance. You need KES ' . number_format($applicationFee) . ' to apply for this job.'
                ]
            ]);
        }

        try {
            DB::transaction(function () use ($user, $job, $applicationFee) {
                // Deduct application fee
                $user->decrement('deposit_balance', $applicationFee);

                // Create application
                $application = JobApplication::create([
                    'user_id' => $user->id,
                    'job_id' => $job->id,
                    'application_fee' => $applicationFee,
                    'status' => 'pending',
                    'applied_at' => now(),
                    'transaction_id' => 'JOB-' . time() . '-' . strtoupper(uniqid()),
                    'application_data' => [
                        'job_title' => $job->title,
                        'job_category' => $job->category->name,
                        'application_fee' => $applicationFee,
                        'balance_before' => $user->deposit_balance + $applicationFee,
                        'balance_after' => $user->deposit_balance,
                    ],
                ]);

                // Increment job application count
                $job->incrementApplicationCount();

                // Create transaction record (optional)
                // You can add to your existing transaction system here
            });

            return redirect()->route('jobs.my-jobs')->with([
                'flash' => [
                    'success' => 'Successfully applied for "' . $job->title . '". Your application is under review.'
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Job application failed: ' . $e->getMessage());
            return back()->with([
                'flash' => [
                    'error' => 'Failed to apply for job. Please try again.'
                ]
            ]);
        }
    }

    // User's job applications
    public function myJobs()
    {

    
        $user = Auth::user();
        
        $applications = JobApplication::where('user_id', $user->id)
            ->with(['job', 'job.category'])
            ->orderBy('applied_at', 'desc')
            ->paginate(15)
            ->through(function ($application) {
                return [
                    'id' => $application->id,
                    'job_id' => $application->job_id,
                    'job_title' => $application->job->title,
                    'job_category' => $application->job->category->name,
                    'job_category_color' => $application->job->category->color,
                    'job_location' => $application->job->location,
                    'job_salary' => $application->job->getFormattedSalary(),
                    'status' => $application->status,
                    'status_text' => $application->getStatusText(),
                    'status_color' => $application->getStatusColor(),
                    'application_fee' => $application->application_fee,
                    'formatted_fee' => $application->getFormattedFee(),
                    'applied_at' => $application->applied_at->format('M d, Y H:i'),
                    'days_since_applied' => $application->getDaysSinceApplied(),
                    'reviewed_at' => $application->reviewed_at?->format('M d, Y'),
                    'cover_letter' => $application->cover_letter,
                    'notes' => $application->notes,
                ];
            });

        // Stats
        $stats = [
            'total' => JobApplication::where('user_id', $user->id)->count(),
            'pending' => JobApplication::where('user_id', $user->id)->where('status', 'pending')->count(),
            'reviewing' => JobApplication::where('user_id', $user->id)->where('status', 'reviewing')->count(),
            'shortlisted' => JobApplication::where('user_id', $user->id)->where('status', 'shortlisted')->count(),
            'hired' => JobApplication::where('user_id', $user->id)->where('status', 'hired')->count(),
            'total_spent' => JobApplication::where('user_id', $user->id)->sum('application_fee'),
        ];

        // dd($applications);

        return Inertia::render('Jobs/MyJobs', [
            'applications' => $applications,
            'stats' => $stats,
            'userBalance' => $user->deposit_balance ?? 0,
        ]);
    }

    // Withdraw application (optional)
    public function withdraw(JobApplication $application)
    {
        $user = Auth::user();
        
        if ($application->user_id !== $user->id) {
            abort(403);
        }

        if (!$application->isPending()) {
            return back()->with([
                'flash' => [
                    'error' => 'Only pending applications can be withdrawn.'
                ]
            ]);
        }

        $application->update([
            'status' => 'rejected',
            'notes' => 'Withdrawn by applicant',
        ]);

        return back()->with([
            'flash' => [
                'success' => 'Application withdrawn successfully.'
            ]
        ]);
    }
}