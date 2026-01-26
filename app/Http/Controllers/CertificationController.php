<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\CertificationPurchase;
use App\Models\User;

class CertificationController extends Controller
{
    // Display certification packages
    public function index()
    {
        $user = Auth::user();
        
        $packages = [
            'certification' => CertificationPurchase::getPackageDetails('certification'),
            'access_code' => CertificationPurchase::getPackageDetails('access_code'),
            'verification' => CertificationPurchase::getPackageDetails('verification'),
        ];

        // Get user's active purchases
        $activePurchases = CertificationPurchase::where('user_id', $user->id)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($purchase) {
                return [
                    'id' => $purchase->id,
                    'package_type' => $purchase->package_type,
                    'package_name' => $purchase->package_name,
                    'amount' => $purchase->amount,
                    'status' => $purchase->status,
                    'activated_at' => $purchase->activated_at?->format('M d, Y'),
                    'expires_at' => $purchase->expires_at?->format('M d, Y'),
                    'days_remaining' => $purchase->expires_at ? $purchase->expires_at->diffInDays(now()) : null,
                    'codes' => [
                        'certification' => $purchase->certification_code,
                        'access' => $purchase->access_code,
                        'verification' => $purchase->verification_code,
                    ],
                    'is_active' => $purchase->isActive(),
                    'is_expired' => $purchase->isExpired(),
                ];
            });

        return Inertia::render('Certification/Index', [
            'packages' => $packages,
            'activePurchases' => $activePurchases,
            'userBalance' => $user->deposit_balance,
        ]);
    }

    // Purchase a certification package
    public function purchase(Request $request)
    {
        $request->validate([
            'package_type' => 'required|in:certification,access_code,verification',
        ]);

        $user = Auth::user();
        $packageType = $request->package_type;
        $packageDetails = CertificationPurchase::getPackageDetails($packageType);

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

                // Generate codes based on package type
                $certificationCode = null;
                $accessCode = null;
                $verificationCode = null;

                if ($packageType === 'certification') {
                    $certificationCode = CertificationPurchase::generateCode('certification', $user->id);
                } elseif ($packageType === 'access_code') {
                    $accessCode = CertificationPurchase::generateCode('access_code', $user->id);
                } elseif ($packageType === 'verification') {
                    $verificationCode = CertificationPurchase::generateCode('verification', $user->id);
                }

                // Create purchase record
                $purchase = CertificationPurchase::create([
                    'user_id' => $user->id,
                    'package_type' => $packageType,
                    'package_name' => $packageDetails['name'],
                    'amount' => $packageDetails['amount'],
                    'certification_code' => $certificationCode,
                    'access_code' => $accessCode,
                    'verification_code' => $verificationCode,
                    'status' => 'active',
                    'activated_at' => now(),
                    'expires_at' => now()->addDays($packageDetails['duration_days']),
                    'package_details' => $packageDetails,
                ]);

                // Update user if they purchase verification package
                if ($packageType === 'verification') {
                    $user->update([
                        'is_verified' => true,
                        'verified_at' => now(),
                    ]);
                }
            });

            return back()->with([
                'flash' => [
                    'success' => 'Package purchased successfully! Your ' . $packageDetails['name'] . ' is now active.'
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Certification purchase failed: ' . $e->getMessage());
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
        
        $purchases = CertificationPurchase::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->through(function ($purchase) {
                return [
                    'id' => $purchase->id,
                    'package_type' => $purchase->package_type,
                    'package_name' => $purchase->package_name,
                    'amount' => $purchase->amount,
                    'status' => $purchase->status,
                    'activated_at' => $purchase->activated_at?->format('M d, Y H:i'),
                    'expires_at' => $purchase->expires_at?->format('M d, Y H:i'),
                    'created_at' => $purchase->created_at->format('M d, Y H:i'),
                    'codes' => [
                        'certification' => $purchase->certification_code,
                        'access' => $purchase->access_code,
                        'verification' => $purchase->verification_code,
                    ],
                    'is_active' => $purchase->isActive(),
                    'is_expired' => $purchase->isExpired(),
                    'days_remaining' => $purchase->expires_at ? $purchase->expires_at->diffInDays(now()) : null,
                ];
            });

        return Inertia::render('Certification/History', [
            'purchases' => $purchases,
        ]);
    }

    // Show specific purchase details
    public function show($id)
    {
        $purchase = CertificationPurchase::where('user_id', Auth::id())
            ->findOrFail($id);

        return Inertia::render('Certification/Show', [
            'purchase' => [
                'id' => $purchase->id,
                'package_type' => $purchase->package_type,
                'package_name' => $purchase->package_name,
                'amount' => $purchase->amount,
                'status' => $purchase->status,
                'activated_at' => $purchase->activated_at?->format('M d, Y H:i'),
                'expires_at' => $purchase->expires_at?->format('M d, Y H:i'),
                'created_at' => $purchase->created_at->format('M d, Y H:i'),
                'codes' => [
                    'certification' => $purchase->certification_code,
                    'access' => $purchase->access_code,
                    'verification' => $purchase->verification_code,
                ],
                'package_details' => $purchase->package_details,
                'notes' => $purchase->notes,
                'is_active' => $purchase->isActive(),
                'is_expired' => $purchase->isExpired(),
                'days_remaining' => $purchase->expires_at ? $purchase->expires_at->diffInDays(now()) : null,
            ],
        ]);
    }

    // Activate a purchased package
    public function activate($id)
    {
        $purchase = CertificationPurchase::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->findOrFail($id);

        $purchase->update([
            'status' => 'active',
            'activated_at' => now(),
            'expires_at' => now()->addDays($purchase->package_details['duration_days'] ?? 30),
        ]);

        return back()->with([
            'flash' => [
                'success' => 'Package activated successfully!'
            ]
        ]);
    }

    // Check package status
    public function checkStatus(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $code = $request->code;
        
        $purchase = CertificationPurchase::where('certification_code', $code)
            ->orWhere('access_code', $code)
            ->orWhere('verification_code', $code)
            ->with('user')
            ->first();

        if (!$purchase) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid code or package not found.'
            ]);
        }

        return response()->json([
            'valid' => true,
            'data' => [
                'package_name' => $purchase->package_name,
                'package_type' => $purchase->package_type,
                'status' => $purchase->status,
                'activated_at' => $purchase->activated_at?->format('Y-m-d H:i:s'),
                'expires_at' => $purchase->expires_at?->format('Y-m-d H:i:s'),
                'is_active' => $purchase->isActive(),
                'is_expired' => $purchase->isExpired(),
                'user' => [
                    'name' => $purchase->user->name,
                    'email' => $purchase->user->email,
                ]
            ]
        ]);
    }
}