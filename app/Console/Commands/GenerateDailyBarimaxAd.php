<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BarimaxAIService;
use App\Models\BarimaxAd;
use Illuminate\Support\Facades\Log;

class GenerateDailyBarimaxAd extends Command
{
    protected $signature = 'barimax:generate-daily-ad';
    protected $description = 'Generate a new Barimax AI ad with HD image and random discount every midnight';

    protected $aiService;

    public function __construct(BarimaxAIService $aiService)
    {
        parent::__construct();
        $this->aiService = $aiService;
    }

    public function handle()
    {
        $this->info('Starting daily Barimax ad generation...');
        
        try {
            // Deactivate old ads
            $this->deactivateOldAds();
            
            // Generate new ad
            $ad = $this->aiService->generateNewAd();
            
            $this->info("✅ New Barimax ad generated successfully!");
            $this->info("📸 Image: {$ad->image_url}");
            $this->info("🎯 Caption: {$ad->caption}");
            $this->info("💰 Discount: {$ad->discount_percentage}%");
            $this->info("🎟️ Code: {$ad->discount_code}");
            $this->info("⏰ Valid until: {$ad->valid_until}");
            
            Log::info('Daily Barimax ad generated', [
                'ad_id' => $ad->id,
                'discount' => $ad->discount_percentage,
                'code' => $ad->discount_code,
            ]);
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("❌ Failed to generate ad: " . $e->getMessage());
            Log::error('Failed to generate daily Barimax ad', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return Command::FAILURE;
        }
    }
    
    private function deactivateOldAds()
    {
        // Deactivate ads older than 7 days
        BarimaxAd::where('valid_until', '<', now()->subDays(7))
            ->update(['is_active' => false]);
        
        $this->info('Old ads deactivated.');
    }
}