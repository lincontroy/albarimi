<?php
// app/Http/Controllers/CronController.php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class CronController extends Controller
{
    /**
     * Simple cron method to clear cache
     * Access via: yourdomain.com/cron/clear-cache
     */
    public function clearCache(Request $request)
    {
        // Optional: Add secret key for security
        $secretKey = 'your-secret-key-here'; // Change this
        if ($request->key !== $secretKey) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            // Clear all caches
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');
            
            // Log the execution
            Log::info('Cron: Cache cleared successfully at ' . now());
            
            return response()->json([
                'success' => true,
                'message' => 'Cache cleared successfully',
                'time' => now()->toDateTimeString()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Cron: Cache clear failed - ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Cache clear failed: ' . $e->getMessage()
            ], 500);
        }
    }
}