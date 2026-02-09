<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\BarimaxAd;
use App\Services\BarimaxAIService;
use Illuminate\Support\Facades\Cache;

class BarimaxAdController extends Controller
{
    protected $aiService;

    public function __construct(BarimaxAIService $aiService)
    {
        $this->aiService = $aiService;
    }

    // Display Barimax Ads page
    public function index()
    {
        $user = Auth::user();
        
        // Get current featured ad
        $featuredAd = BarimaxAd::getCurrentFeatured();
        
        // Get active ads
        $activeAds = $this->aiService->getActiveAds(6);
        
        // Get user's claimed discounts (you might want to create a separate model for this)
        $userDiscounts = [];
        
        // Get ad statistics
        $adStats = [
            'total_ads' => BarimaxAd::count(),
            'active_ads' => BarimaxAd::active()->count(),
            'total_views' => BarimaxAd::sum('views_count'),
            'total_clicks' => BarimaxAd::sum('clicks_count'),
        ];

        return Inertia::render('BarimaxAds/Index', [
            'featuredAd' => $featuredAd ? $this->formatAd($featuredAd) : null,
            'activeAds' => $activeAds->map(function ($ad) {
                return $this->formatAd($ad);
            }),
            'userDiscounts' => $userDiscounts,
            'adStats' => $adStats,
            'categories' => BarimaxAd::getCategories(),
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'has_claimed' => false, // You can implement this
            ] : null,
        ]);
    }

    // View specific ad
    public function show($id)
    {
        $ad = BarimaxAd::findOrFail($id);
        
        // Increment views
        $ad->incrementViews();
        
        return Inertia::render('BarimaxAds/Show', [
            'ad' => $this->formatAd($ad),
        ]);
    }

    // Claim discount
    public function claimDiscount(Request $request, $id)
    {
        $user = Auth::user();
        $ad = BarimaxAd::findOrFail($id);
        
        // Check if ad is active and valid
        if (!$ad->is_active || $ad->isExpired()) {
            return back()->with([
                'flash' => [
                    'error' => 'This offer has expired or is no longer available.'
                ]
            ]);
        }
        
        // Increment clicks
        $ad->incrementClicks();
        
        // Here you would typically:
        // 1. Create a user discount record
        // 2. Apply discount to user's account
        // 3. Send notification
        
        return back()->with([
            'flash' => [
                'success' => "Discount claimed successfully! Use code: {$ad->discount_code} for {$ad->discount_percentage}% OFF"
            ]
        ]);
    }

    // Generate new ad (admin only)
    public function generateNewAd()
    {
        if (!Auth::user()->is_admin) {
            abort(403, 'Unauthorized action.');
        }
        
        try {
            $ad = $this->aiService->generateNewAd();
            
            return response()->json([
                'success' => true,
                'message' => 'New Barimax ad generated successfully!',
                'ad' => $this->formatAd($ad),
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate ad: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Get all ads (admin)
    public function getAllAds()
    {
        if (!Auth::user()->is_admin) {
            abort(403, 'Unauthorized action.');
        }
        
        $ads = BarimaxAd::orderBy('created_at', 'desc')
            ->paginate(20);
        
        return Inertia::render('BarimaxAds/Admin', [
            'ads' => $ads->through(function ($ad) {
                return $this->formatAd($ad, true);
            }),
            'pagination' => [
                'total' => $ads->total(),
                'per_page' => $ads->perPage(),
                'current_page' => $ads->currentPage(),
                'last_page' => $ads->lastPage(),
            ],
        ]);
    }

    // Format ad for response
    private function formatAd($ad, $includeStats = false)
    {
        $formatted = [
            'id' => $ad->id,
            'image_url' => $ad->image_url,
            'hd_image_url' => $ad->hd_image_url,
            'caption' => $ad->caption,
            'discount_percentage' => $ad->discount_percentage,
            'formatted_discount' => $ad->formatted_discount,
            'discount_code' => $ad->discount_code,
            'valid_until' => $ad->valid_until->format('M d, Y H:i'),
            'days_remaining' => $ad->days_remaining,
            'hours_remaining' => $ad->hours_remaining,
            'category' => $ad->category,
            'category_name' => BarimaxAd::getCategories()[$ad->category] ?? 'General',
            'description' => $ad->description,
            'call_to_action' => $ad->call_to_action,
            'is_active' => $ad->is_active,
            'is_expired' => $ad->isExpired(),
            'gradient_colors' => $ad->gradient_colors,
            'created_at' => $ad->created_at->format('M d, Y'),
        ];
        
        if ($includeStats) {
            $formatted['views_count'] = $ad->views_count;
            $formatted['clicks_count'] = $ad->clicks_count;
            $formatted['click_rate'] = $ad->views_count > 0 
                ? round(($ad->clicks_count / $ad->views_count) * 100, 2)
                : 0;
        }
        
        return $formatted;
    }
}