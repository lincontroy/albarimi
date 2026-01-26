<?php

namespace App\Http\Controllers;

use App\Models\VisaCode;
use App\Models\VisaCodePurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class VisaCodeController extends Controller
{
    // Display available visa codes
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        // Get filters from request
        $selectedVisaType = $request->input('visa_type', '');
        $selectedCountry = $request->input('country', '');
        
        // Build query with filters
        $query = VisaCode::where('status', 'available');
        
        if ($selectedVisaType) {
            $query->where('visa_type', $selectedVisaType);
        }
        
        if ($selectedCountry) {
            $query->where('country', $selectedCountry);
        }
        
        // Get paginated results - ensure we have at least some data
        $availableCodes = $query->orderBy('created_at', 'desc')
            ->paginate(12)
            ->withQueryString();
        
        // Transform the data safely
        $transformedData = $availableCodes->through(function ($code) {
            try {
                $package = VisaCode::getPackageDetails($code->visa_type);
                
                return [
                    'id' => $code->id ?? null,
                    'code' => $code->code ?? 'N/A',
                    'visa_type' => $code->visa_type ?? 'unknown',
                    'visa_name' => $package['name'] ?? ($code->visa_type ?? 'Unknown'),
                    'country' => $code->country ?? 'Unknown',
                    'country_name' => VisaCode::$countries[$code->country] ?? ($code->country ?? 'Unknown'),
                    'amount' => $code->amount ?? 0,
                    'status' => $code->status ?? 'unknown',
                    'expires_at' => $code->expires_at?->format('M d, Y') ?? null,
                    'days_remaining' => $code->getDaysRemaining(),
                    'features' => $package['features'] ?? [],
                    'duration' => $package['duration'] ?? 'N/A',
                    'status_badge' => $code->getStatusBadge() ?? ['color' => 'gray', 'text' => 'Unknown'],
                ];
            } catch (\Exception $e) {
                \Log::error('Error transforming visa code: ' . $e->getMessage());
                return null;
            }
        })->filter(); // Remove null values
        
        // Get user's purchased codes
        $myCodes = VisaCode::where('purchased_by', $user->id)
            ->orderBy('purchased_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($code) {
                try {
                    $package = VisaCode::getPackageDetails($code->visa_type);
                    
                    return [
                        'id' => $code->id ?? null,
                        'code' => $code->code ?? 'N/A',
                        'visa_type' => $code->visa_type ?? 'unknown',
                        'visa_name' => $package['name'] ?? ($code->visa_type ?? 'Unknown'),
                        'country' => $code->country ?? 'Unknown',
                        'country_name' => VisaCode::$countries[$code->country] ?? ($code->country ?? 'Unknown'),
                        'amount' => $code->amount ?? 0,
                        'status' => $code->status ?? 'unknown',
                        'purchased_at' => $code->purchased_at?->format('M d, Y') ?? 'N/A',
                        'activated_at' => $code->activated_at?->format('M d, Y') ?? null,
                        'expires_at' => $code->expires_at?->format('M d, Y') ?? null,
                        'days_remaining' => $code->getDaysRemaining(),
                        'status_badge' => $code->getStatusBadge() ?? ['color' => 'gray', 'text' => 'Unknown'],
                    ];
                } catch (\Exception $e) {
                    \Log::error('Error transforming my visa code: ' . $e->getMessage());
                    return null;
                }
            })
            ->filter()
            ->values();
    
        $visaTypes = VisaCode::$visaTypes;
        $countries = VisaCode::$countries;
    
        return Inertia::render('VisaCodes/Index', [
            'availableCodes' => $availableCodes->setCollection($transformedData),
            'myCodes' => $myCodes,
            'visaTypes' => $visaTypes,
            'countries' => $countries,
            'userBalance' => $user->deposit_balance ?? 0,
            'selectedVisaType' => $selectedVisaType,
            'selectedCountry' => $selectedCountry,
        ]);
    }
    // Show purchase page
    public function purchase(Request $request)
    {
        $request->validate([
            'visa_type' => 'nullable|in:' . implode(',', array_keys(VisaCode::$visaTypes)),
            'country' => 'nullable|in:' . implode(',', array_keys(VisaCode::$countries)),
        ]);

        $visaType = $request->visa_type;
        $country = $request->country;

        // If specific visa type is requested, show codes for that type
        $query = VisaCode::where('status', 'available');

        if ($visaType) {
            $query->where('visa_type', $visaType);
        }

        if ($country) {
            $query->where('country', $country);
        }

        $availableCodes = $query->orderBy('amount', 'asc')->get()->map(function ($code) {
            $package = VisaCode::getPackageDetails($code->visa_type);
            
            return [
                'id' => $code->id,
                'code' => $code->code,
                'visa_type' => $code->visa_type,
                'visa_name' => $package['name'] ?? $code->visa_type,
                'country' => $code->country,
                'country_name' => VisaCode::$countries[$code->country] ?? $code->country,
                'amount' => $code->amount,
                'expires_at' => $code->expires_at?->format('M d, Y'),
                'days_remaining' => $code->getDaysRemaining(),
                'features' => $package['features'] ?? [],
                'duration' => $package['duration'] ?? '',
            ];
        });

        return Inertia::render('VisaCodes/Purchase', [
            'availableCodes' => $availableCodes,
            'visaTypes' => VisaCode::$visaTypes,
            'countries' => VisaCode::$countries,
            'selectedVisaType' => $visaType,
            'selectedCountry' => $country,
            'userBalance' => Auth::user()->deposit_balance,
        ]);
    }

    // Process purchase
    public function processPurchase(Request $request)
    {
        $request->validate([
            'visa_code_id' => 'required|exists:visa_codes,id',
        ]);

        $user = Auth::user();
        $visaCode = VisaCode::where('status', 'available')->findOrFail($request->visa_code_id);

        // Check if code is still available
        if (!$visaCode->isAvailable()) {
            return back()->with([
                'flash' => [
                    'error' => 'This visa code is no longer available.'
                ]
            ]);
        }

        // Check user balance
        if ($user->deposit_balance < $visaCode->amount) {
            return back()->with([
                'flash' => [
                    'error' => 'Insufficient balance. You need KES ' . number_format($visaCode->amount) . ' to purchase this visa code.'
                ]
            ]);
        }

        try {
            DB::transaction(function () use ($user, $visaCode) {
                // Deduct amount from user balance
                $user->decrement('deposit_balance', $visaCode->amount);

                // Update visa code status
                $visaCode->update([
                    'status' => 'purchased',
                    'purchased_by' => $user->id,
                    'purchased_at' => now(),
                    'expires_at' => now()->addDays(30), // 30 days to activate
                ]);

                // Create purchase record
                VisaCodePurchase::create([
                    'user_id' => $user->id,
                    'visa_code_id' => $visaCode->id,
                    'amount' => $visaCode->amount,
                    'payment_method' => 'wallet',
                    'status' => 'completed',
                    'transaction_id' => 'VISA-' . time() . '-' . strtoupper(uniqid()),
                    'payment_details' => [
                        'method' => 'wallet',
                        'balance_before' => $user->deposit_balance + $visaCode->amount,
                        'balance_after' => $user->deposit_balance,
                    ],
                ]);
            });

            return redirect()->route('visa-codes.my-codes')->with([
                'flash' => [
                    'success' => 'Visa code purchased successfully! Your code is now in your account.'
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Visa code purchase failed: ' . $e->getMessage());
            return back()->with([
                'flash' => [
                    'error' => 'Failed to purchase visa code. Please try again.'
                ]
            ]);
        }
    }

    // Show user's visa codes
    public function myCodes()
    {
        $user = Auth::user();
        
        $codes = VisaCode::where('purchased_by', $user->id)
            ->orderBy('purchased_at', 'desc')
            ->paginate(20)
            ->through(function ($code) {
                $package = VisaCode::getPackageDetails($code->visa_type);
                
                return [
                    'id' => $code->id,
                    'code' => $code->code,
                    'visa_type' => $code->visa_type,
                    'visa_name' => $package['name'] ?? $code->visa_type,
                    'country' => $code->country,
                    'country_name' => VisaCode::$countries[$code->country] ?? $code->country,
                    'amount' => $code->amount,
                    'status' => $code->status,
                    'purchased_at' => $code->purchased_at?->format('M d, Y H:i'),
                    'activated_at' => $code->activated_at?->format('M d, Y H:i'),
                    'used_at' => $code->used_at?->format('M d, Y H:i'),
                    'expires_at' => $code->expires_at?->format('M d, Y H:i'),
                    'days_remaining' => $code->getDaysRemaining(),
                    'features' => $package['features'] ?? [],
                    'duration' => $package['duration'] ?? '',
                    'status_badge' => $code->getStatusBadge(),
                    'notes' => $code->notes,
                ];
            });

        return Inertia::render('VisaCodes/MyCodes', [
            'codes' => $codes,
        ]);
    }

    // Show purchase history
    public function history()
    {
        $purchases = VisaCodePurchase::where('user_id', Auth::id())
            ->with('visaCode')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->through(function ($purchase) {
                return [
                    'id' => $purchase->id,
                    'visa_code' => $purchase->visaCode->code,
                    'visa_type' => $purchase->visaCode->visa_type,
                    'country' => $purchase->visaCode->country,
                    'amount' => $purchase->amount,
                    'payment_method' => $purchase->payment_method,
                    'status' => $purchase->status,
                    'transaction_id' => $purchase->transaction_id,
                    'created_at' => $purchase->created_at->format('M d, Y H:i'),
                    'payment_details' => $purchase->payment_details,
                ];
            });

        return Inertia::render('VisaCodes/History', [
            'purchases' => $purchases,
        ]);
    }

    // Admin: Generate visa codes (optional - for admin panel)
    public function generateCodes(Request $request)
    {
        $request->validate([
            'visa_type' => 'required|in:' . implode(',', array_keys(VisaCode::$visaTypes)),
            'country' => 'required|in:' . implode(',', array_keys(VisaCode::$countries)),
            'quantity' => 'required|integer|min:1|max:100',
            'amount' => 'required|numeric|min:0',
        ]);

        $package = VisaCode::getPackageDetails($request->visa_type);
        $codes = [];

        for ($i = 0; $i < $request->quantity; $i++) {
            $code = VisaCode::create([
                'code' => VisaCode::generateCode(),
                'visa_type' => $request->visa_type,
                'country' => $request->country,
                'amount' => $request->amount,
                'status' => 'available',
                'visa_details' => $package,
                'notes' => 'Generated by admin',
            ]);

            $codes[] = $code->code;
        }

        return response()->json([
            'success' => true,
            'message' => $request->quantity . ' visa codes generated successfully.',
            'codes' => $codes,
        ]);
    }
}