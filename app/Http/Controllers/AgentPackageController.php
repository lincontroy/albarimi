<?php

namespace App\Http\Controllers;

use App\Models\AgentPackagePurchase;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AgentPackageController extends Controller
{
    /**
     * Display agent package information
     */
    public function index()
    {
        $user = Auth::user();
        
        $packageInfo = [
            'name' => 'Barimax Agent Package',
            'price' => AgentPackagePurchase::PACKAGE_PRICE,
            'benefits' => AgentPackagePurchase::getBenefits(),
            'is_agent' => $user->is_agent,
            'agent_since' => $user->agent_since,
            'agent_bonus' => $user->agent_bonus,
            'agent_salary' => $user->agent_salary,
            'deposit_balance' => $user->deposit_balance,
        ];

        // Get recent agent purchases
        $recentPurchases = AgentPackagePurchase::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($purchase) {
                return [
                    'id' => $purchase->id,
                    'amount' => $purchase->amount,
                    'package_name' => $purchase->package_name,
                    'transaction_id' => $purchase->transaction_id,
                    'status' => $purchase->status,
                    'purchased_at' => $purchase->purchased_at->toISOString(),
                ];
            });

        return Inertia::render('Agent/Index', [
            'package' => $packageInfo,
            'recentPurchases' => $recentPurchases,
        ]);
    }

    /**
     * Purchase agent package
     */
    public function purchase(Request $request)
    {
        $user = Auth::user();

        // Check if user is already an agent
        if ($user->is_agent) {
            return response()->json([
                'message' => 'You are already an agent!',
                'errors' => ['general' => 'You already have the agent package.']
            ], 422);
        }

        // Check if user has sufficient deposit balance
        if ($user->deposit_balance < AgentPackagePurchase::PACKAGE_PRICE) {
            return response()->json([
                'message' => 'Insufficient deposit balance.',
                'errors' => ['balance' => 'You need KES ' . AgentPackagePurchase::PACKAGE_PRICE . ' in your deposit balance to purchase this package.']
            ], 422);
        }

        try {
            DB::transaction(function () use ($user) {
                // Create package purchase record
                $purchase = AgentPackagePurchase::create([
                    'user_id' => $user->id,
                    'amount' => AgentPackagePurchase::PACKAGE_PRICE,
                    'package_name' => AgentPackagePurchase::PACKAGE_NAME,
                    'benefits' => AgentPackagePurchase::getBenefits(),
                    'transaction_id' => $this->generateTransactionId(),
                    'status' => 'completed',
                    'purchased_at' => now(),
                ]);


                // Deduct from deposit balance
                $user->decrement('deposit_balance', AgentPackagePurchase::PACKAGE_PRICE);

                $uplineId = $user->referred_by;
                // dd('Upline ID: ' . $uplineId);
                if ($uplineId) {
                    $upline = User::find($uplineId);
                    if ($upline) {
                     //   dd('Upline found: ' . $upline->name . ' - Commission: ' . $commissionAmount);
                        $upline->increment('deposit_balance', $commissionAmount);
                    }
                }

            

                // Make user an agent
                // $user->becomeAgent();

                $user->update([
                    'is_agent' => 1,
                    'agent_since' => now(),
                    'agent_bonus' => 15000,
                    'agent_salary' => 35000,
                ]);
            });

            return response()->json([
                'message' => 'Agent package purchased successfully! Welcome to the Barimax Agent team!',
                'agent' => [
                    'is_agent' => true,
                    'agent_since' => now()->toISOString(),
                    'agent_bonus' => 15000,
                    'agent_salary' => 35000,
                ]
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error purchasing agent package: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error purchasing agent package. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function generateTransactionId()
    {
        // Generate a unique transaction ID using timestamp and user ID
        $timestamp = now()->format('YmdHis');
        $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        return "AGENT-{$timestamp}-{$random}";
    }

    /**
     * Get agent statistics
     */
    public function stats()
    {
        $user = Auth::user();
        
        $stats = [
            'is_agent' => $user->is_agent,
            'agent_since' => $user->agent_since?->diffForHumans(),
            'agent_bonus' => $user->agent_bonus,
            'agent_salary' => $user->agent_salary,
            'deposit_balance' => $user->deposit_balance,
            'total_earnings' => $user->agent_bonus + $user->agent_salary,
            'next_salary_date' => now()->addMonth()->startOfMonth()->toISOString(),
        ];

        return response()->json($stats);
    }
}