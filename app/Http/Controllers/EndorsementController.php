<?php

namespace App\Http\Controllers;

use App\Models\Endorsement;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class EndorsementController extends Controller
{
    // Main endorsements page
    public function index()
    {
        $user = Auth::user();
        
        // Get active endorsements
        $activeEndorsements = Endorsement::where('user_id', $user->id)
            ->whereIn('status', ['active', 'approved'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($endorsement) {
                return [
                    'id' => $endorsement->id,
                    'application_id' => $endorsement->application_id,
                    'title' => $endorsement->title,
                    'type' => $endorsement->type,
                    'type_text' => $endorsement->getTypeText(),
                    'type_color' => $endorsement->getTypeColor(),
                    'type_icon' => $endorsement->getTypeIcon(),
                    'description' => $endorsement->description,
                    'status' => $endorsement->status,
                    'status_badge' => $endorsement->getStatusBadge(),
                    'rating' => $endorsement->rating,
                    'views' => $endorsement->views,
                    'endorsements_count' => $endorsement->endorsements_count,
                    'package_fee' => $endorsement->getFormattedFee(),
                    'days_remaining' => $endorsement->getDaysRemaining(),
                    'expires_at' => $endorsement->expires_at?->format('M d, Y'),
                    'created_at' => $endorsement->created_at->format('M d, Y'),
                ];
            });

        // Check if user has pending application
        $hasPendingApplication = Endorsement::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'under_review'])
            ->exists();

        // Stats
        $stats = [
            'total_applications' => Endorsement::where('user_id', $user->id)->count(),
            'active_endorsements' => Endorsement::where('user_id', $user->id)->where('status', 'active')->count(),
            'total_views' => Endorsement::where('user_id', $user->id)->sum('views'),
            'total_endorsements' => Endorsement::where('user_id', $user->id)->sum('endorsements_count'),
        ];

        return Inertia::render('Endorsement/Index', [
            'activeEndorsements' => $activeEndorsements,
            'hasPendingApplication' => $hasPendingApplication,
            'stats' => $stats,
            'userBalance' => $user->deposit_balance ?? 0,
            'packageFee' => 3500,
        ]);
    }

    // Apply for endorsement page
    public function apply()
    {
        $user = Auth::user();
        
        // Check if user has pending application
        $hasPendingApplication = Endorsement::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'under_review'])
            ->exists();

        if ($hasPendingApplication) {
            return redirect()->route('endorsement.status')->with([
                'flash' => [
                    'error' => 'You already have a pending endorsement application. Please wait for it to be reviewed.'
                ]
            ]);
        }

        // Check user balance for package fee
        $packageFee = 3500;
        $hasEnoughBalance = $user->deposit_balance >= $packageFee;

        return Inertia::render('Endorsement/Apply', [
            'packageFee' => $packageFee,
            'hasEnoughBalance' => $hasEnoughBalance,
            'userBalance' => $user->deposit_balance ?? 0,
            'types' => [
                ['id' => 'professional', 'name' => 'Professional Endorsement', 'description' => 'For professional qualifications and experience'],
                ['id' => 'skill', 'name' => 'Skill Endorsement', 'description' => 'For specific skills and expertise'],
                ['id' => 'service', 'name' => 'Service Endorsement', 'description' => 'For services you provide'],
                ['id' => 'product', 'name' => 'Product Endorsement', 'description' => 'For products you create or sell'],
                ['id' => 'business', 'name' => 'Business Endorsement', 'description' => 'For business verification'],
            ],
        ]);
    }

    // Process endorsement application
    public function processApplication(Request $request)
    {
        $user = Auth::user();
        $packageFee = 3500;

        // Validate
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:professional,skill,service,product,business',
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:100|max:2000',
            'skills' => 'nullable|array',
            'skills.*' => 'string|max:100',
            'links' => 'nullable|array',
            'links.*' => 'url|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check if user has pending application
        $hasPendingApplication = Endorsement::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'under_review'])
            ->exists();

        if ($hasPendingApplication) {
            return back()->with([
                'flash' => [
                    'error' => 'You already have a pending endorsement application. Please wait for it to be reviewed.'
                ]
            ]);
        }

        // Check user balance
        if ($user->deposit_balance < $packageFee) {
            return back()->with([
                'flash' => [
                    'error' => 'Insufficient balance. You need KES ' . number_format($packageFee) . ' for the endorsement package.'
                ]
            ]);
        }

        try {
            DB::transaction(function () use ($user, $request, $packageFee) {
                // Deduct package fee
                $user->decrement('deposit_balance', $packageFee);

                // Create endorsement application
                $endorsement = Endorsement::create([
                    'user_id' => $user->id,
                    'application_id' => Endorsement::generateApplicationId(),
                    'type' => $request->type,
                    'title' => $request->title,
                    'description' => $request->description,
                    'skills' => $request->skills ?? [],
                    'links' => $request->links ?? [],
                    'package_fee' => $packageFee,
                    'status' => 'pending',
                    'submitted_at' => now(),
                    'transaction_id' => 'END-' . time() . '-' . strtoupper(uniqid()),
                    'expires_at' => now()->addMonths(12), // 1 year validity
                ]);

                // Create transaction record
                Transaction::create([
                    'user_id' => $user->id,
                    'transaction_id' => $endorsement->transaction_id,
                    'type' => 'payment',
                    'amount' => $packageFee,
                    'fee' => 0,
                    'net_amount' => $packageFee,
                    'balance_before' => $user->deposit_balance + $packageFee,
                    'balance_after' => $user->deposit_balance,
                    'status' => 'completed',
                    'payment_method' => 'wallet',
                    'description' => 'Endorsement Package Fee: ' . $endorsement->title,
                    'metadata' => [
                        'endorsement_id' => $endorsement->id,
                        'application_id' => $endorsement->application_id,
                        'type' => $endorsement->type,
                    ],
                    'completed_at' => now(),
                ]);

                // Update endorsement status to under review
                $endorsement->update([
                    'status' => 'under_review',
                    'reviewed_at' => now(),
                ]);
            });

            return redirect()->route('endorsement.status')->with([
                'flash' => [
                    'success' => 'Endorsement application submitted successfully! KES ' . number_format($packageFee) . ' package fee deducted. Your application is now under review.'
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Endorsement application failed: ' . $e->getMessage());
            return back()->with([
                'flash' => [
                    'error' => 'Failed to submit endorsement application. Please try again.'
                ]
            ]);
        }
    }

    // Application status page
    public function status()
    {
        $user = Auth::user();
        
        // Get latest application
        $latestApplication = Endorsement::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();

        // Get all applications
        $applications = Endorsement::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($application) {
                return [
                    'id' => $application->id,
                    'application_id' => $application->application_id,
                    'title' => $application->title,
                    'type' => $application->type,
                    'type_text' => $application->getTypeText(),
                    'type_color' => $application->getTypeColor(),
                    'description' => $application->description,
                    'status' => $application->status,
                    'status_badge' => $application->getStatusBadge(),
                    'package_fee' => $application->getFormattedFee(),
                    'review_notes' => $application->review_notes,
                    'rating' => $application->rating,
                    'submitted_at' => $application->submitted_at?->format('M d, Y H:i'),
                    'reviewed_at' => $application->reviewed_at?->format('M d, Y H:i'),
                    'approved_at' => $application->approved_at?->format('M d, Y H:i'),
                    'expires_at' => $application->expires_at?->format('M d, Y'),
                    'days_remaining' => $application->getDaysRemaining(),
                    'created_at' => $application->created_at->format('M d, Y'),
                ];
            });

        // Stats
        $stats = [
            'total' => Endorsement::where('user_id', $user->id)->count(),
            'pending' => Endorsement::where('user_id', $user->id)->where('status', 'pending')->count(),
            'under_review' => Endorsement::where('user_id', $user->id)->where('status', 'under_review')->count(),
            'approved' => Endorsement::where('user_id', $user->id)->where('status', 'approved')->count(),
            'active' => Endorsement::where('user_id', $user->id)->where('status', 'active')->count(),
            'rejected' => Endorsement::where('user_id', $user->id)->where('status', 'rejected')->count(),
        ];

        return Inertia::render('Endorsement/Status', [
            'latestApplication' => $latestApplication ? [
                'id' => $latestApplication->id,
                'application_id' => $latestApplication->application_id,
                'title' => $latestApplication->title,
                'type' => $latestApplication->type,
                'type_text' => $latestApplication->getTypeText(),
                'type_color' => $latestApplication->getTypeColor(),
                'status' => $latestApplication->status,
                'status_badge' => $latestApplication->getStatusBadge(),
                'review_notes' => $latestApplication->review_notes,
                'submitted_at' => $latestApplication->submitted_at?->format('M d, Y H:i'),
                'reviewed_at' => $latestApplication->reviewed_at?->format('M d, Y H:i'),
                'days_since_submitted' => $latestApplication->submitted_at ? now()->diffInDays($latestApplication->submitted_at) : null,
            ] : null,
            'applications' => $applications,
            'stats' => $stats,
            'userBalance' => $user->deposit_balance ?? 0,
            'packageFee' => 3500,
        ]);
    }
}