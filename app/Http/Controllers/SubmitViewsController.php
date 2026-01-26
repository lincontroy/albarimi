<?php

namespace App\Http\Controllers;

use App\Models\ViewSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SubmitViewsController extends Controller
{
    /**
     * Display the submit views page
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get user's view submissions
        $submissions = ViewSubmission::where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($submission) {
                return [
                    'id' => $submission->id,
                    'views_count' => $submission->views_count,
                    'earned_amount' => $submission->earned_amount,
                    'whatsapp_number' => $submission->whatsapp_number,
                    'view_type' => $submission->view_type,
                    'status' => $submission->status,
                    'screenshot_url' => Storage::url($submission->screenshot_path),
                    'created_at' => $submission->created_at->toISOString(),
                    'updated_at' => $submission->updated_at->toISOString(),
                ];
            });

        // Calculate stats
        $stats = [
            'total_views' => ViewSubmission::where('user_id', $user->id)->sum('views_count'),
            'total_earned' => ViewSubmission::where('user_id', $user->id)->where('status', 'approved')->sum('earned_amount'),
            'pending_views' => ViewSubmission::where('user_id', $user->id)->where('status', 'pending')->sum('views_count'),
            'approved_views' => ViewSubmission::where('user_id', $user->id)->where('status', 'approved')->sum('views_count'),
        ];

        return Inertia::render('SubmitViews/Index', [
            'submissions' => $submissions,
            'stats' => $stats,
            'viewTypes' => [
                ['id' => 1, 'name' => 'Status Views', 'rate' => 0.5],
                ['id' => 2, 'name' => 'Story Views', 'rate' => 0.6],
                ['id' => 3, 'name' => 'Image Views', 'rate' => 0.4],
                ['id' => 4, 'name' => 'Video Views', 'rate' => 0.7],
            ]
        ]);
    }

    /**
     * Store a new view submission
     */
    public function store(Request $request)
    {
        $request->validate([
            'whatsapp_number' => 'required|string|regex:/^254[0-9]{9}$/',
            'views_count' => 'required|integer|min:1|max:10000',
           
            'screenshot' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ]);

        try {
            // Calculate earnings based on view type rates
            $rates = [
                'Status Views' => 0.5,
                'Story Views' => 0.6,
                'Image Views' => 0.4,
                'Video Views' => 0.7,
            ];

            $rate = 100;
            $earnedAmount = $request->views_count * $rate;

            // Store screenshot
            $screenshotPath = $request->file('screenshot')->store('view-submissions', 'public');

            // Create submission
            $submission = ViewSubmission::create([
                'user_id' => Auth::id(),
                'whatsapp_number' => $request->whatsapp_number,
                'views_count' => $request->views_count,
             
                'platform' => 'WhatsApp', // Set platform as WhatsApp
                'earned_amount' => $earnedAmount,
                'screenshot_path' => $screenshotPath,
                'status' => 'approved',
                'submitted_at' => now(),
            ]);

            return response()->json([
                'message' => 'Congratulations,WhatsApp views submitted successfully! You have earned KES ' . number_format($earnedAmount, 2),
                'submission' => [
                    'id' => $submission->id,
                    'views_count' => $submission->views_count,
                    'earned_amount' => $submission->earned_amount,
                    'whatsapp_number' => $submission->whatsapp_number,
                    
                    'status' => $submission->status,
                    'screenshot_url' => Storage::url($submission->screenshot_path),
                    'created_at' => $submission->created_at->toISOString(),
                ]
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error submitting views: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error submitting WhatsApp views. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's submission history
     */
    public function history()
    {
        $submissions = ViewSubmission::where('user_id', Auth::id())
            ->latest()
            ->paginate(15)
            ->through(function ($submission) {
                return [
                    'id' => $submission->id,
                    'views_count' => $submission->views_count,
                    'earned_amount' => $submission->earned_amount,
                    'whatsapp_number' => $submission->whatsapp_number,
                    'view_type' => $submission->view_type,
                    'status' => $submission->status,
                    'screenshot_url' => Storage::url($submission->screenshot_path),
                    'created_at' => $submission->created_at->toISOString(),
                    'updated_at' => $submission->updated_at->toISOString(),
                ];
            });

        return Inertia::render('SubmitViews/History', [
            'submissions' => $submissions,
        ]);
    }

    /**
     * Get submission statistics
     */
    public function stats()
    {
        $user = Auth::user();
        
        $stats = [
            'total_views' => ViewSubmission::where('user_id', $user->id)->sum('views_count'),
            'total_earned' => ViewSubmission::where('user_id', $user->id)->where('status', 'approved')->sum('earned_amount'),
            'pending_views' => ViewSubmission::where('user_id', $user->id)->where('status', 'pending')->sum('views_count'),
            'approved_views' => ViewSubmission::where('user_id', $user->id)->where('status', 'approved')->sum('views_count'),
            'rejected_views' => ViewSubmission::where('user_id', $user->id)->where('status', 'rejected')->sum('views_count'),
            'total_submissions' => ViewSubmission::where('user_id', $user->id)->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Show a specific submission
     */
    public function show(ViewSubmission $submission)
    {
        // Authorization - user can only view their own submissions
        if ($submission->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return Inertia::render('SubmitViews/Show', [
            'submission' => [
                'id' => $submission->id,
                'views_count' => $submission->views_count,
                'earned_amount' => $submission->earned_amount,
                'whatsapp_number' => $submission->whatsapp_number,
                'view_type' => $submission->view_type,
                'status' => $submission->status,
                'screenshot_url' => Storage::url($submission->screenshot_path),
                'admin_notes' => $submission->admin_notes,
                'created_at' => $submission->created_at->toISOString(),
                'updated_at' => $submission->updated_at->toISOString(),
            ],
        ]);
    }

    /**
     * Cancel a pending submission
     */
    public function cancel(ViewSubmission $submission)
    {
        if ($submission->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($submission->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending submissions can be cancelled.'
            ], 422);
        }

        $submission->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Submission cancelled successfully.'
        ]);
    }
}