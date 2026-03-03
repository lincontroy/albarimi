<?php
// app/Http/Controllers/BarimaxAdController.php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
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

    /**
     * Download product image by ID
     */
    public function downloadProductImage($id)
    {
        try {
            // Find the product
            $product = Product::findOrFail($id);
            
            // Check if product has image path
            if (!$product->image_path) {
                Log::error('Product has no image path', ['product_id' => $id]);
                return response()->json([
                    'success' => false,
                    'message' => 'No image found for this product.'
                ], 404);
            }
            
            // Log the path for debugging
            Log::info('Attempting to download image:', [
                'product_id' => $id,
                'product_name' => $product->name,
                'image_path' => $product->image_path,
                'full_storage_path' => storage_path('app/public/' . $product->image_path),
                'disk_exists' => Storage::disk('public')->exists($product->image_path) ? 'Yes' : 'No'
            ]);
            
            // Check if file exists on the public disk
            if (!Storage::disk('public')->exists($product->image_path)) {
                Log::error('Image file not found on server', [
                    'path' => $product->image_path,
                    'full_path' => storage_path('app/public/' . $product->image_path)
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Image file not found on server.',
                    'path' => $product->image_path
                ], 404);
            }
            
            // Get the full path to the image
            $fullPath = Storage::disk('public')->path($product->image_path);
            
            // Check if file is readable
            if (!file_exists($fullPath)) {
                Log::error('File does not exist at path', ['full_path' => $fullPath]);
                return response()->json([
                    'success' => false,
                    'message' => 'File does not exist.'
                ], 404);
            }
            
            if (!is_readable($fullPath)) {
                Log::error('File is not readable', ['full_path' => $fullPath]);
                return response()->json([
                    'success' => false,
                    'message' => 'File is not readable.'
                ], 403);
            }
            
            // Get file size
            $fileSize = filesize($fullPath);
            
            // Generate a clean filename
            $extension = pathinfo($product->image_path, PATHINFO_EXTENSION);
            $cleanName = Str::slug($product->name) . '-' . $product->id . '.' . $extension;
            
            Log::info('Download successful', [
                'file_size' => $fileSize,
                'download_name' => $cleanName
            ]);
            
            // Return download response with proper headers
            return response()->download($fullPath, $cleanName, [
                'Content-Type' => mime_content_type($fullPath),
                'Content-Disposition' => 'attachment; filename="' . $cleanName . '"',
                'Content-Length' => $fileSize,
                'Cache-Control' => 'no-cache, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error downloading product image: ' . $e->getMessage(), [
                'product_id' => $id,
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error downloading image: ' . $e->getMessage()
            ], 500);
        }
    }

    // Format product for response
    private function formatProduct($product)
    {
        // Fix the image URL to use the admin subdomain
        $baseUrl = 'https://barimaxtop.com/storage/';
        
        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description ?? 'No description available',
            'price' => $product->price,
            'formatted_price' => 'KES ' . number_format($product->price, 2),
            'image_url' => $product->image_path ? $baseUrl . $product->image_path : null,
            'image_path' => $product->image_path,
            'thumbnail_url' => $product->thumbnail_path ? $baseUrl . $product->thumbnail_path : ($product->image_path ? $baseUrl . $product->image_path : null),
            'category' => $product->category ?? 'Uncategorized',
            'stock' => $product->stock,
            'in_stock' => $product->stock > 0,
            'download_url' => route('barimax-ads.download-product', $product->id),
        ];
    }

    // Format ad for response
    private function formatAd($ad)
    {
        // Fix the image URL to use the admin subdomain
        $baseUrl = 'https://barimaxtop.com/storage/';
        
        return [
            'id' => $ad->id,
            'image_url' => $ad->image_url ? (str_starts_with($ad->image_url, 'http') ? $ad->image_url : $baseUrl . $ad->image_url) : null,
            'hd_image_url' => $ad->hd_image_url ? (str_starts_with($ad->hd_image_url, 'http') ? $ad->hd_image_url : $baseUrl . $ad->hd_image_url) : null,
            'caption' => $ad->caption,
            'discount_percentage' => $ad->discount_percentage,
            'discount_code' => $ad->discount_code,
            'days_remaining' => $ad->days_remaining,
            'description' => $ad->description ?? 'Limited time AI-generated discount offer',
            'call_to_action' => $ad->call_to_action ?? 'Claim Discount',
            'gradient_colors' => $ad->gradient_colors,
        ];
    }

    // Generate new ad (admin only)
    public function generateNewAd()
    {
        if (!Auth::user() || !Auth::user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
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