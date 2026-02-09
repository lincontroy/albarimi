<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\Package;
use App\Models\User;

class PackageController extends Controller
{
    // Display packages
    public function index()
    {
        $user = Auth::user();
        
        $packages = [];
        foreach (Package::getAllPackages() as $package) {
            $packages[$package] = Package::getPackageDetails($package);
        }

        // Get user's active packages
        $activePackages = Package::where('user_id', $user->id)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($package) {
                return [
                    'id' => $package->id,
                    'package_type' => $package->package_type,
                    'package_name' => $package->package_name,
                    'amount' => $package->amount,
                    'status' => $package->status,
                    'activated_at' => $package->activated_at?->format('M d, Y'),
                    'expires_at' => $package->expires_at?->format('M d, Y'),
                    'days_remaining' => $package->expires_at ? $package->expires_at->diffInDays(now()) : null,
                    'features' => $package->features,
                    'potential_earnings' => $package->getPotentialEarnings(),
                    'is_active' => $package->isActive(),
                    'is_expired' => $package->isExpired(),
                ];
            });

        return Inertia::render('Packages/Index', [
            'packages' => $packages,
            'activePackages' => $activePackages,
            'userBalance' => $user->deposit_balance,
        ]);
    }

    // Purchase a package
    public function purchase(Request $request)
    {
        $request->validate([
            'package_type' => 'required|in:' . implode(',', Package::getAllPackages()),
        ]);

        $user = Auth::user();
        $packageType = $request->package_type;
        $packageDetails = Package::getPackageDetails($packageType);

        if (!$packageDetails) {
            return back()->with([
                'flash' => [
                    'error' => 'Invalid package selected.'
                ]
            ]);
        }

        // Check user balance
        if ($user->deposit_balance < $packageDetails['amount']) {
            return back()->with([
                'flash' => [
                    'error' => 'Insufficient balance. You need KES ' . number_format($packageDetails['amount']) . ' to purchase this package.'
                ]
            ]);
        }

        try {
            DB::transaction(function () use ($user, $packageType, $packageDetails) {
                // Deduct amount from user balance
                $user->decrement('deposit_balance', $packageDetails['amount']);

                $commissionAmount = $packageDetails['amount'] * 0.80; // 80% commission

                // Distribute commission to upline
                $uplineId = $user->referred_by;
                if ($uplineId) {
                    $upline = User::find($uplineId);
                    if ($upline) {
                        $upline->increment('deposit_balance', $commissionAmount);
                    }
                }

                // Check if user has active package of same type
                $existingPackage = Package::where('user_id', $user->id)
                    ->where('package_type', $packageType)
                    ->where('status', 'active')
                    ->where('expires_at', '>', now())
                    ->first();

                if ($existingPackage) {
                    // Extend existing package
                    $existingPackage->update([
                        'expires_at' => $existingPackage->expires_at->addDays($packageDetails['duration_days']),
                        'notes' => ($existingPackage->notes ?? '') . "\nExtended on " . now()->format('Y-m-d H:i:s'),
                    ]);
                } else {
                    // Create new package
                    $package = Package::create([
                        'user_id' => $user->id,
                        'package_type' => $packageType,
                        'package_name' => $packageDetails['name'],
                        'amount' => $packageDetails['amount'],
                        'status' => 'active',
                        'activated_at' => now(),
                        'expires_at' => now()->addDays($packageDetails['duration_days']),
                        'features' => $packageDetails['features'],
                        'notes' => 'Package purchased successfully',
                    ]);
                }
            });

            return back()->with([
                'flash' => [
                    'success' => 'Package purchased successfully! Your ' . $packageDetails['name'] . ' is now active.'
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Package purchase failed: ' . $e->getMessage());
            return back()->with([
                'flash' => [
                    'error' => 'Failed to purchase package. Please try again.'
                ]
            ]);
        }
    }

    // Show purchase history
    public function history()
    {
        $user = Auth::user();
        
        $purchases = Package::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->through(function ($package) {
                return [
                    'id' => $package->id,
                    'package_type' => $package->package_type,
                    'package_name' => $package->package_name,
                    'amount' => $package->amount,
                    'status' => $package->status,
                    'activated_at' => $package->activated_at?->format('M d, Y H:i'),
                    'expires_at' => $package->expires_at?->format('M d, Y H:i'),
                    'created_at' => $package->created_at->format('M d, Y H:i'),
                    'features' => $package->features,
                    'potential_earnings' => $package->getPotentialEarnings(),
                    'is_active' => $package->isActive(),
                    'is_expired' => $package->isExpired(),
                    'days_remaining' => $package->expires_at ? $package->expires_at->diffInDays(now()) : null,
                ];
            });

        return Inertia::render('Packages/History', [
            'purchases' => $purchases,
        ]);
    }

    // Show specific package details
    public function show($id)
    {
        $package = Package::where('user_id', Auth::id())
            ->findOrFail($id);

        $packageDetails = Package::getPackageDetails($package->package_type);

        return Inertia::render('Packages/Show', [
            'package' => [
                'id' => $package->id,
                'package_type' => $package->package_type,
                'package_name' => $package->package_name,
                'amount' => $package->amount,
                'status' => $package->status,
                'activated_at' => $package->activated_at?->format('M d, Y H:i'),
                'expires_at' => $package->expires_at?->format('M d, Y H:i'),
                'created_at' => $package->created_at->format('M d, Y H:i'),
                'features' => $package->features,
                'notes' => $package->notes,
                'is_active' => $package->isActive(),
                'is_expired' => $package->isExpired(),
                'days_remaining' => $package->expires_at ? $package->expires_at->diffInDays(now()) : null,
                'package_details' => $packageDetails,
                'potential_earnings' => $package->getPotentialEarnings(),
            ],
        ]);
    }

    // Renew package
    public function renew($id)
    {
        $package = Package::where('user_id', Auth::id())
            ->findOrFail($id);

        $user = Auth::user();
        $packageDetails = Package::getPackageDetails($package->package_type);

        if ($user->deposit_balance < $packageDetails['amount']) {
            return back()->with([
                'flash' => [
                    'error' => 'Insufficient balance. You need KES ' . number_format($packageDetails['amount']) . ' to renew this package.'
                ]
            ]);
        }

        try {
            DB::transaction(function () use ($user, $package, $packageDetails) {
                // Deduct amount from user balance
                $user->decrement('deposit_balance', $packageDetails['amount']);

                $commissionAmount = $packageDetails['amount'] * 0.80; // 80% commission

                // Distribute commission to upline
                $uplineId = $user->referred_by;
                if ($uplineId) {
                    $upline = User::find($uplineId);
                    if ($upline) {
                        $upline->increment('deposit_balance', $commissionAmount);
                    }
                }

                // Update package expiry
                $newExpiry = $package->expires_at && $package->expires_at->isFuture()
                    ? $package->expires_at->addDays($packageDetails['duration_days'])
                    : now()->addDays($packageDetails['duration_days']);

                $package->update([
                    'status' => 'active',
                    'expires_at' => $newExpiry,
                    'notes' => $package->notes . "\nRenewed on " . now()->format('Y-m-d H:i:s'),
                ]);
            });

            return back()->with([
                'flash' => [
                    'success' => 'Package renewed successfully!'
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Package renewal failed: ' . $e->getMessage());
            return back()->with([
                'flash' => [
                    'error' => 'Failed to renew package. Please try again.'
                ]
            ]);
        }
    }

    // Upgrade package
    public function upgrade(Request $request, $id)
    {
        $request->validate([
            'upgrade_to' => 'required|in:' . implode(',', Package::getAllPackages()),
        ]);

        $currentPackage = Package::where('user_id', Auth::id())
            ->findOrFail($id);

        $user = Auth::user();
        $upgradeTo = $request->upgrade_to;
        $newPackageDetails = Package::getPackageDetails($upgradeTo);
        $currentPackageDetails = Package::getPackageDetails($currentPackage->package_type);

        // Calculate upgrade cost
        $upgradeCost = $newPackageDetails['amount'] - $currentPackageDetails['amount'];
        if ($upgradeCost <= 0) {
            return back()->with([
                'flash' => [
                    'error' => 'Cannot downgrade package. Please purchase a higher tier.'
                ]
            ]);
        }

        if ($user->deposit_balance < $upgradeCost) {
            return back()->with([
                'flash' => [
                    'error' => 'Insufficient balance. You need KES ' . number_format($upgradeCost) . ' to upgrade.'
                ]
            ]);
        }

        try {
            DB::transaction(function () use ($user, $currentPackage, $upgradeTo, $newPackageDetails, $upgradeCost) {
                // Deduct upgrade cost from user balance
                $user->decrement('deposit_balance', $upgradeCost);

                $commissionAmount = $upgradeCost * 0.80; // 80% commission

                // Distribute commission to upline
                $uplineId = $user->referred_by;
                if ($uplineId) {
                    $upline = User::find($uplineId);
                    if ($upline) {
                        $upline->increment('deposit_balance', $commissionAmount);
                    }
                }

                // Update package
                $currentPackage->update([
                    'package_type' => $upgradeTo,
                    'package_name' => $newPackageDetails['name'],
                    'amount' => $newPackageDetails['amount'],
                    'features' => $newPackageDetails['features'],
                    'notes' => $currentPackage->notes . "\nUpgraded to " . $newPackageDetails['name'] . " on " . now()->format('Y-m-d H:i:s'),
                ]);
            });

            return back()->with([
                'flash' => [
                    'success' => 'Package upgraded successfully to ' . $newPackageDetails['name'] . '!'
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Package upgrade failed: ' . $e->getMessage());
            return back()->with([
                'flash' => [
                    'error' => 'Failed to upgrade package. Please try again.'
                ]
            ]);
        }
    }
}