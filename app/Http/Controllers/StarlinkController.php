<?php

namespace App\Http\Controllers;

use App\Models\StarlinkPackage;
use App\Models\StarlinkSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StarlinkController extends Controller
{
    // Main dashboard/index page
    public function index()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Get active packages
        $packages = StarlinkPackage::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($package) {
                return [
                    'id' => $package->id,
                    'name' => $package->name,
                    'description' => $package->description,
                    'price' => $package->price,
                    'monthly_fee' => $package->monthly_fee,
                    'data_limit_gb' => $package->data_limit_gb,
                    'data_limit_text' => $package->getDataLimitText(),
                    'speed_mbps' => $package->speed_mbps,
                    'duration' => $package->duration,
                    'duration_text' => $package->getDurationText(),
                    'features' => $package->features ?? [],
                    'formatted_price' => $package->getFormattedPrice(),
                    'formatted_monthly_fee' => $package->getFormattedMonthlyFee(),
                    'monthly_savings' => $package->getMonthlySavings(),
                    'is_unlimited' => $package->isUnlimited(),
                ];
            });

        // Get user's active subscription
        $activeSubscription = StarlinkSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('package')
            ->first();

        // Get user's subscription history
        $subscriptions = StarlinkSubscription::where('user_id', $user->id)
            ->with('package')
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get()
            ->map(function ($subscription) {
                return [
                    'id' => $subscription->id,
                    'subscription_code' => $subscription->subscription_code,
                    'package_name' => $subscription->package->name ?? 'Unknown',
                    'amount_paid' => $subscription->amount_paid,
                    'status' => $subscription->status,
                    'status_text' => $subscription->getStatusText(),
                    'status_color' => $subscription->getStatusColor(),
                    'subscribed_at' => $subscription->subscribed_at?->format('M d, Y'),
                    'activated_at' => $subscription->activated_at?->format('M d, Y'),
                    'expires_at' => $subscription->expires_at?->format('M d, Y'),
                    'days_remaining' => $subscription->getDaysRemaining(),
                    'formatted_amount' => $subscription->getFormattedAmount(),
                ];
            });

        return Inertia::render('Starlink/Index', [
            'packages' => $packages,
            'activeSubscription' => $activeSubscription ? [
                'id' => $activeSubscription->id,
                'subscription_code' => $activeSubscription->subscription_code,
                'package_name' => $activeSubscription->package->name,
                'package_description' => $activeSubscription->package->description,
                'amount_paid' => $activeSubscription->amount_paid,
                'status' => $activeSubscription->status,
                'status_text' => $activeSubscription->getStatusText(),
                'status_color' => $activeSubscription->getStatusColor(),
                'subscribed_at' => $activeSubscription->subscribed_at?->format('M d, Y'),
                'activated_at' => $activeSubscription->activated_at?->format('M d, Y'),
                'expires_at' => $activeSubscription->expires_at?->format('M d, Y'),
                'days_remaining' => $activeSubscription->getDaysRemaining(),
                'formatted_amount' => $activeSubscription->getFormattedAmount(),
                'package_features' => $activeSubscription->package->features ?? [],
            ] : null,
            'subscriptions' => $subscriptions,
            'userBalance' => $user->deposit_balance ?? 0,
        ]);
    }

    // Packages page
    public function packages()
    {
        $user = Auth::user();
        
        $packages = StarlinkPackage::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($package) {
                return [
                    'id' => $package->id,
                    'name' => $package->name,
                    'description' => $package->description,
                    'price' => $package->price,
                    'monthly_fee' => $package->monthly_fee,
                    'data_limit_gb' => $package->data_limit_gb,
                    'data_limit_text' => $package->getDataLimitText(),
                    'speed_mbps' => $package->speed_mbps,
                    'duration' => $package->duration,
                    'duration_text' => $package->getDurationText(),
                    'features' => $package->features ?? [],
                    'formatted_price' => $package->getFormattedPrice(),
                    'formatted_monthly_fee' => $package->getFormattedMonthlyFee(),
                    'monthly_savings' => $package->getMonthlySavings(),
                    'is_unlimited' => $package->isUnlimited(),
                ];
            });

        return Inertia::render('Starlink/Packages', [
            'packages' => $packages,
            'userBalance' => $user->deposit_balance ?? 0,
        ]);
    }

    // Subscribe to a package
    public function subscribe(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:starlink_packages,id',
        ]);

        $user = Auth::user();
        $package = StarlinkPackage::findOrFail($request->package_id);

        // Check if package is available
        if (!$package->is_active) {
            return back()->with([
                'flash' => [
                    'error' => 'This package is no longer available.'
                ]
            ]);
        }

        // Check if user already has an active subscription
        $activeSubscription = StarlinkSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if ($activeSubscription) {
            return back()->with([
                'flash' => [
                    'error' => 'You already have an active Starlink subscription. Please cancel it before subscribing to a new one.'
                ]
            ]);
        }

        // Check user balance
        if ($user->deposit_balance < $package->price) {
            return back()->with([
                'flash' => [
                    'error' => 'Insufficient balance. You need KES ' . number_format($package->price) . ' to subscribe to this package.'
                ]
            ]);
        }

        try {
            DB::transaction(function () use ($user, $package) {
                // Deduct amount from user balance
                $user->decrement('deposit_balance', $package->price);

                // Create subscription
                $subscription = StarlinkSubscription::create([
                    'user_id' => $user->id,
                    'starlink_package_id' => $package->id,
                    'subscription_code' => StarlinkSubscription::generateSubscriptionCode(),
                    'amount_paid' => $package->price,
                    'payment_method' => 'wallet',
                    'status' => 'pending',
                    'subscribed_at' => now(),
                    'transaction_id' => 'STAR-' . time() . '-' . strtoupper(uniqid()),
                    'payment_details' => [
                        'method' => 'wallet',
                        'balance_before' => $user->deposit_balance + $package->price,
                        'balance_after' => $user->deposit_balance,
                        'package_name' => $package->name,
                        'duration' => $package->duration,
                    ],
                    'notes' => 'Awaiting activation',
                ]);

                // Auto-activate if it's an immediate service
                // For Starlink, you might want to keep it pending until hardware is delivered
                // For now, we'll auto-activate
                $subscription->activate();
            });

            return redirect()->route('starlink.index')->with([
                'flash' => [
                    'success' => 'Successfully subscribed to ' . $package->name . '! Your subscription is now active.'
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Starlink subscription failed: ' . $e->getMessage());
            return back()->with([
                'flash' => [
                    'error' => 'Failed to subscribe. Please try again.'
                ]
            ]);
        }
    }

    // Cancel subscription (optional)
    public function cancel(Request $request)
    {
        $request->validate([
            'subscription_id' => 'required|exists:starlink_subscriptions,id',
        ]);

        $user = Auth::user();
        $subscription = StarlinkSubscription::where('user_id', $user->id)
            ->findOrFail($request->subscription_id);

        if (!$subscription->isActive()) {
            return back()->with([
                'flash' => [
                    'error' => 'Only active subscriptions can be cancelled.'
                ]
            ]);
        }

        $subscription->cancel();

        return back()->with([
            'flash' => [
                'success' => 'Subscription cancelled successfully.'
            ]
        ]);
    }

    // Renew subscription (optional)
    public function renew(Request $request)
    {
        $request->validate([
            'subscription_id' => 'required|exists:starlink_subscriptions,id',
        ]);

        $user = Auth::user();
        $subscription = StarlinkSubscription::where('user_id', $user->id)
            ->with('package')
            ->findOrFail($request->subscription_id);

        if (!$subscription->isActive()) {
            return back()->with([
                'flash' => [
                    'error' => 'Only active subscriptions can be renewed.'
                ]
            ]);
        }

        $package = $subscription->package;

        // Check user balance
        if ($user->deposit_balance < $package->monthly_fee) {
            return back()->with([
                'flash' => [
                    'error' => 'Insufficient balance. You need KES ' . number_format($package->monthly_fee) . ' to renew your subscription.'
                ]
            ]);
        }

        try {
            DB::transaction(function () use ($user, $package, $subscription) {
                // Deduct monthly fee
                $user->decrement('deposit_balance', $package->monthly_fee);

                // Renew subscription
                $subscription->renew();

                // Create renewal record (optional - you might want a separate renewals table)
                \App\Models\StarlinkRenewal::create([
                    'user_id' => $user->id,
                    'starlink_subscription_id' => $subscription->id,
                    'amount' => $package->monthly_fee,
                    'renewed_at' => now(),
                ]);
            });

            return back()->with([
                'flash' => [
                    'success' => 'Subscription renewed successfully for another month.'
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Starlink renewal failed: ' . $e->getMessage());
            return back()->with([
                'flash' => [
                    'error' => 'Failed to renew subscription. Please try again.'
                ]
            ]);
        }
    }

    // Get subscription details (optional)
    public function subscription($id)
    {
        $user = Auth::user();
        
        $subscription = StarlinkSubscription::where('user_id', $user->id)
            ->with('package')
            ->findOrFail($id);

        return Inertia::render('Starlink/Subscription', [
            'subscription' => [
                'id' => $subscription->id,
                'subscription_code' => $subscription->subscription_code,
                'package_name' => $subscription->package->name,
                'package_description' => $subscription->package->description,
                'amount_paid' => $subscription->amount_paid,
                'status' => $subscription->status,
                'status_text' => $subscription->getStatusText(),
                'status_color' => $subscription->getStatusColor(),
                'subscribed_at' => $subscription->subscribed_at?->format('M d, Y H:i'),
                'activated_at' => $subscription->activated_at?->format('M d, Y H:i'),
                'expires_at' => $subscription->expires_at?->format('M d, Y H:i'),
                'days_remaining' => $subscription->getDaysRemaining(),
                'formatted_amount' => $subscription->getFormattedAmount(),
                'payment_method' => $subscription->payment_method,
                'transaction_id' => $subscription->transaction_id,
                'payment_details' => $subscription->payment_details,
                'notes' => $subscription->notes,
                'package_features' => $subscription->package->features ?? [],
            ],
            'userBalance' => $user->deposit_balance ?? 0,
        ]);
    }
}