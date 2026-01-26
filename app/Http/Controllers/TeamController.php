<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\User;

class TeamController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get referrals with pagination
        $referrals = $user->referrals()
            ->select([
                'id',
                'name',
                'email',
                'phone',
                'created_at',
                'last_active_at',
                'deposit_balance',
                'is_agent',
                'agent_since'
            ])
            ->with(['agentPackage' => function($query) {
                $query->select(['id', 'user_id', 'package_type', 'purchased_at']);
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->through(function ($referral) {
                return [
                    'id' => $referral->id,
                    'name' => $referral->name,
                    'email' => $referral->email,
                    'phone' => $referral->phone,
                    'joined_date' => $referral->created_at->format('M d, Y'),
                    'last_active' => $referral->last_active_at ? $referral->last_active_at->diffForHumans() : 'Never',
                    'is_active' => $referral->is_active,
                    'balance' => $referral->deposit_balance,
                    'is_agent' => $referral->is_agent,
                    'agent_since' => $referral->agent_since ? $referral->agent_since->format('M d, Y') : null,
                    'agent_package' => $referral->agentPackage ? [
                        'type' => $referral->agentPackage->package_type,
                        'purchased_at' => $referral->agentPackage->purchased_at->format('M d, Y'),
                    ] : null,
                ];
            });

        // Get referral stats
        $stats = [
            'total_referrals' => $user->referral_count,
            'active_referrals' => $user->active_referral_count,
            'inactive_referrals' => $user->referral_count - $user->active_referral_count,
            'total_earned' => $user->total_earned_from_referrals,
            'referral_link' => $user->referral_link,
            'referral_code' => $user->referral_code,
        ];

        // Get this month's referrals
        $monthlyReferrals = $user->referrals()
            ->whereMonth('created_at', now()->month)
            ->count();

        // Get active referrals percentage
        $activePercentage = $user->referral_count > 0 
            ? round(($user->active_referral_count / $user->referral_count) * 100, 1)
            : 0;

        return Inertia::render('Team/Index', [
            'referrals' => $referrals,
            'stats' => $stats,
            'monthly_referrals' => $monthlyReferrals,
            'active_percentage' => $activePercentage,
        ]);
    }

    public function generateReferralCode(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->referral_code) {
            $user->update([
                'referral_code' => $this->generateUniqueReferralCode()
            ]);
            
            return back()->with([
                'flash' => [
                    'success' => 'Referral code generated successfully!'
                ]
            ]);
        }
        
        return back()->with([
            'flash' => [
                'info' => 'You already have a referral code.'
            ]
        ]);
    }

    private function generateUniqueReferralCode()
    {
        do {
            $code = strtoupper(substr(md5(uniqid()), 0, 8));
        } while (User::where('referral_code', $code)->exists());
        
        return $code;
    }
}