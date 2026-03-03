<?php
// app/Http/Controllers/BarimaxAdController.php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use App\Models\BarimaxAd;
use App\Models\Product;
use Illuminate\Support\Str;
use App\Services\BarimaxAIService;

class BarimaxAdController extends Controller
{
    protected $aiService;

    public function __construct(BarimaxAIService $aiService)
    {
        $this->aiService = $aiService;
    }

    // Display Barimax Ads page with latest product
    public function index()
    {
        $user = Auth::user();
        
        // Get current featured ad
        $featuredAd = BarimaxAd::getCurrentFeatured();
        
        // Get active ads
        $activeAds = $this->aiService->getActiveAds(6);
        
        // Get the latest product with image
        $latestProduct = Product::whereNotNull('image_path')
            ->where('is_active', true)
            ->latest()
            ->first();
        
        // Get ad statistics
        $adStats = [
            'active_ads' => BarimaxAd::active()->count(),
            'total_views' => BarimaxAd::sum('views_count'),
        ];

        return Inertia::render('BarimaxAds/Index', [
            'featuredAd' => $featuredAd ? $this->formatAd($featuredAd) : null,
            'activeAds' => $activeAds->map(function ($ad) {
                return $this->formatAd($ad);
            }),
            'latestProduct' => $latestProduct ? $this->formatProduct($latestProduct) : null,
            'adStats' => $adStats,
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
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
        
        return back()->with([
            'flash' => [
                'success' => "Discount claimed successfully! Use code: {$ad->discount_code} for {$ad->discount_percentage}% OFF"
            ]
        ]);
    }

    // Download product image
    public function downloadProductImage($id)
    {
        $product = Product::findOrFail($id);
        
        if (!$product->image_path) {
            return back()->with('error', 'No image found for this product.');
        }
        
        // Get the full path to the image
        $path = Storage::disk('public')->path($product->image_path);
        
        // Generate a clean filename
        $filename = Str::slug($product->name) . '.' . pathinfo($product->image_path, PATHINFO_EXTENSION);
        
        return response()->download($path, $filename, [
            'Content-Type' => mime_content_type($path),
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    // Format product for response
    private function formatProduct($product)
    {
        // Fix the image URL to use the admin subdomain
        $baseUrl = 'https://admin.barimaxtop.com/storage/';
        
        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'formatted_price' => 'KES ' . number_format($product->price, 2),
            'image_url' => $product->image_path ? $baseUrl . $product->image_path : null,
            'thumbnail_url' => $product->thumbnail_path ? $baseUrl . $product->thumbnail_path : ($product->image_path ? $baseUrl . $product->image_path : null),
            'category' => $product->category,
            'stock' => $product->stock,
            'in_stock' => $product->stock > 0,
            'download_url' => route('barimax-ads.download-product', $product->id),
        ];
    }

    // Format ad for response
    private function formatAd($ad)
    {
        // Fix the image URL to use the admin subdomain
        $baseUrl = 'https://admin.barimaxtop.com/storage/';
        
        return [
            'id' => $ad->id,
            'image_url' => $ad->image_url ? (str_starts_with($ad->image_url, 'http') ? $ad->image_url : $baseUrl . $ad->image_url) : null,
            'hd_image_url' => $ad->hd_image_url ? (str_starts_with($ad->hd_image_url, 'http') ? $ad->hd_image_url : $baseUrl . $ad->hd_image_url) : null,
            'caption' => $ad->caption,
            'discount_percentage' => $ad->discount_percentage,
            'discount_code' => $ad->discount_code,
            'days_remaining' => $ad->days_remaining,
            'description' => $ad->description,
            'call_to_action' => $ad->call_to_action,
            'gradient_colors' => $ad->gradient_colors,
        ];
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
}