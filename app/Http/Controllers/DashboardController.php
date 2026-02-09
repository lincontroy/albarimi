<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Review;
use App\Models\User;
use App\Models\AgentPackagePurchase;
use App\Models\CertificationPurchase;
use App\Models\Endorsement;
use App\Models\Job;
use App\Models\BonusClaim;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get user's agent package
        $agentPackage = $user->agentPackage;
        
        // Get user's certification
        $certification = CertificationPurchase::where('user_id', $user->id)
            ->where('status', 'active')
            ->latest()
            ->first();
        
        // Get recent withdrawals (you'll need to create a Withdrawal model)
        $recentWithdrawals = $user->withdrawals()
    ->completed()
    ->latest()
    ->take(2)
    ->get()
    ->map(function($withdrawal) {
        return [
            'user' => $withdrawal->user->name,
            'amount' => $withdrawal->amount,
            'date' => $withdrawal->processed_at,
            'type' => $withdrawal->type,
        ];
    })->toArray();
        
        // Calculate total earnings
        $totalEarnings = $user->deposit_balance + 
                        ($agentPackage ? $agentPackage->amount : 0) +
                        $user->agent_bonus +
                        $user->agent_salary;
        
        // Get recent reviews/endorsements
        // In DashboardController::index()
// Replace the recent reviews section with:

// In DashboardController::index() method
$recentReviews = Review::with('user')
    ->published()
    ->recent(5)
    ->get()
    ->map(function($review) {
        return [
            'id' => $review->id,
            'user' => $review->display_name, // Use display name
            'rating' => $review->rating,
            'stars' => $review->stars,
            'comment' => $review->comment,
            'date' => $review->created_at,
            'time_ago' => $review->time_ago,
        ];
    })->toArray();

// If no reviews, show sample data
if (empty($recentReviews)) {
$recentReviews = [
    [
        'id' => 1,
        'user' => 'Admin',
        'rating' => 5,
        'stars' => '★★★★★',
        'comment' => 'Welcome to the platform! Share your experience with others.',
        'date' => now(),
        'time_ago' => 'Just now',
    ],
];
}
        
      
        
        // Get total withdrawn (you'll need a Withdrawal model)
        $totalWithdrawn = $user->total_withdrawn;
        $whatsappWithdrawn = $user->whatsapp_withdrawn;
        
        // Get user's job applications count
        $jobApplications = \App\Models\JobApplication::where('user_id', $user->id)->count();
        
        // Get active endorsements
        $activeEndorsements = Endorsement::where('user_id', $user->id)
            ->where('status', 'active')
            ->count();
        
        // Get referral stats
        $referralStats = $user->referral_stats;
        
        $dashboardData = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at,
                'is_agent' => $user->is_agent,
                'agent_since' => $user->agent_since,
                'last_active_at' => $user->last_active_at,
                'deposit_balance' => $user->deposit_balance,
                'agent_bonus' => $user->agent_bonus,
                'agent_salary' => $user->agent_salary,
            ],
            'balances' => [
                'whatsapp_balance' => $user->deposit_balance * 0.6, // Example calculation
                'whatsapp_withdrawn' => $totalWithdrawn,
                'deposit_balance' => $user->deposit_balance,
                'cashback_bonus' => $user->agent_bonus,
                'card_balance' => $user->deposit_balance * 0.3, // Example calculation
                'total_withdrawn' => $totalWithdrawn + ($user->deposit_balance * 0.6),
                'total_earnings' => $totalEarnings,
            ],
            'package' => [
                'current' => $agentPackage ? $agentPackage->package_name : 'No Package',
                'status' => $agentPackage ? ($agentPackage->status ?? 'Active') : 'Inactive',
                'purchased_at' => $agentPackage ? $agentPackage->purchased_at : null,
                'amount' => $agentPackage ? $agentPackage->amount : 0,
            ],
            'certification' => [
                'status' => $certification ? 'Verified' : 'Not Verified',
                'verified' => (bool)$certification,
                'package_type' => $certification ? $certification->package_type : null,
                'expires_at' => $certification ? $certification->expires_at : null,
            ],
            'recent_withdrawals' => $recentWithdrawals,
            'reviews' => $recentReviews,
            'stats' => [
                'job_applications' => $jobApplications,
                'active_endorsements' => $activeEndorsements,
                'referrals' => $referralStats,
                'total_referrals' => $referralStats['total'] ?? 0,
                'active_referrals' => $referralStats['active'] ?? 0,
            ],
            'quick_stats' => [
                'total_invested' => $agentPackage ? $agentPackage->amount : 0,
                'monthly_income' => $user->agent_salary,
                'bonus_earned' => $user->agent_bonus,
                'total_transactions' => $jobApplications + $activeEndorsements,
            ],
        ];
        
        return Inertia::render('Dashboard', $dashboardData);
    }
}