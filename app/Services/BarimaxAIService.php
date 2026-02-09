<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\BarimaxAd;

class BarimaxAIService
{
    // Using Pexels API for high-quality HD images (Free tier available)
    private $pexelsApiKey;
    private $pexelsBaseUrl = 'https://api.pexels.com/v1';
    
    // Using Unsplash API as alternative
    private $unsplashAccessKey;
    private $unsplashBaseUrl = 'https://api.unsplash.com';
    
    // AI Caption Generation (using OpenAI or similar)
    private $openaiApiKey;
    private $openaiBaseUrl = 'https://api.openai.com/v1';

    public function __construct()
    {
        $this->pexelsApiKey = env('PEXELS_API_KEY');
        $this->unsplashAccessKey = env('UNSPLASH_ACCESS_KEY');
        $this->openaiApiKey = env('OPENAI_API_KEY');
    }

    // Fetch random HD image from Pexels
    public function fetchHDImage()
    {
        try {
            // Try Pexels first if API key is available
            if ($this->pexelsApiKey && $this->pexelsApiKey !== 'test_key') {
                $query = $this->getRandomTheme();
                $response = Http::withHeaders([
                    'Authorization' => $this->pexelsApiKey,
                ])->get("{$this->pexelsBaseUrl}/search", [
                    'query' => $query,
                    'per_page' => 1,
                    'page' => rand(1, 10),
                    'orientation' => 'landscape',
                    'size' => 'large',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (!empty($data['photos'][0]['src']['large2x'])) {
                        return [
                            'image_url' => $data['photos'][0]['src']['large2x'],
                            'hd_image_url' => $data['photos'][0]['src']['original'],
                            'photographer' => $data['photos'][0]['photographer'],
                            'photographer_url' => $data['photos'][0]['photographer_url'],
                            'source' => 'pexels',
                        ];
                    }
                }
            }

            // Fallback to Unsplash if API key is available
            if ($this->unsplashAccessKey && $this->unsplashAccessKey !== 'test_key') {
                $unsplashResult = $this->fetchFromUnsplash();
                if ($unsplashResult['source'] !== 'fallback') {
                    return $unsplashResult;
                }
            }

            // Ultimate fallback
            return $this->getFallbackImage();

        } catch (\Exception $e) {
            Log::error('Failed to fetch HD image: ' . $e->getMessage());
            return $this->getFallbackImage();
        }
    }

    // Fetch from Unsplash as fallback
    private function fetchFromUnsplash()
    {
        try {
            $query = $this->getRandomTheme();
            $response = Http::get("{$this->unsplashBaseUrl}/photos/random", [
                'client_id' => $this->unsplashAccessKey,
                'query' => $query,
                'orientation' => 'landscape',
                'count' => 1,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (is_array($data) && !empty($data[0]['urls']['regular'])) {
                    return [
                        'image_url' => $data[0]['urls']['regular'],
                        'hd_image_url' => $data[0]['urls']['full'],
                        'photographer' => $data[0]['user']['name'],
                        'photographer_url' => $data[0]['user']['links']['html'],
                        'source' => 'unsplash',
                    ];
                }
            }

            return $this->getFallbackImage();

        } catch (\Exception $e) {
            Log::error('Failed to fetch from Unsplash: ' . $e->getMessage());
            return $this->getFallbackImage();
        }
    }

    // Get random theme for image search
    private function getRandomTheme()
    {
        $themes = [
            'technology background',
            'digital marketing',
            'online earning',
            'financial success',
            'business growth',
            'money concept',
            'digital transformation',
            'entrepreneurship',
            'investment',
            'wealth building',
            'digital currency',
            'team collaboration',
            'success mindset',
            'financial freedom',
            'online business',
        ];
        
        return $themes[array_rand($themes)];
    }

    // Generate AI caption for discount
    public function generateAICaption($discountPercentage)
    {
        // Only use OpenAI if API key is available
        if ($this->openaiApiKey && $this->openaiApiKey !== 'test_key') {
            try {
                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$this->openaiApiKey}",
                    'Content-Type' => 'application/json',
                ])->post("{$this->openaiBaseUrl}/chat/completions", [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a marketing expert creating catchy captions for discount offers. Keep it short, engaging, and exciting.',
                        ],
                        [
                            'role' => 'user',
                            'content' => "Create a catchy caption for a {$discountPercentage}% discount offer on an online earning platform. Maximum 8 words.",
                        ],
                    ],
                    'max_tokens' => 50,
                    'temperature' => 0.8,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return trim($data['choices'][0]['message']['content'], '"\'');
                }

            } catch (\Exception $e) {
                Log::error('Failed to generate AI caption: ' . $e->getMessage());
            }
        }

        // Fallback captions
        return $this->getFallbackCaption($discountPercentage);
    }

    // Get fallback caption
    private function getFallbackCaption($discountPercentage)
    {
        $captions = [
            "🔥 Exclusive {$discountPercentage}% Off! Limited Time",
            "🎯 Grab {$discountPercentage}% Discount Today!",
            "💎 Special {$discountPercentage}% Offer Just For You",
            "🚀 {$discountPercentage}% OFF - Boost Your Earnings!",
            "🌟 Limited Offer: {$discountPercentage}% Discount",
            "⚡ Flash Sale: {$discountPercentage}% OFF All Plans",
            "🎁 Get {$discountPercentage}% Off Now!",
            "✨ Exclusive {$discountPercentage}% Discount Inside",
        ];
        
        return $captions[array_rand($captions)];
    }

    // Get fallback image (local assets)
    private function getFallbackImage()
    {
        $fallbackImages = [
            'https://images.unsplash.com/photo-1611224923853-80b023f02d71?w=1600&q=80',
            'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=1600&q=80',
            'https://images.unsplash.com/photo-1553877522-43269d4ea984?w=1600&q=80',
            'https://images.unsplash.com/photo-1559136555-9303baea8ebd?w=1600&q=80',
        ];
        
        return [
            'image_url' => $fallbackImages[array_rand($fallbackImages)],
            'hd_image_url' => $fallbackImages[array_rand($fallbackImages)],
            'photographer' => 'Barimax Team',
            'photographer_url' => 'https://barimax.com',
            'source' => 'fallback',
        ];
    }

    // Generate new Barimax Ad
    public function generateNewAd()
    {
        $discountPercentage = BarimaxAd::generateRandomDiscount();
        $imageData = $this->fetchHDImage();
        $caption = $this->generateAICaption($discountPercentage);
        
        $ad = BarimaxAd::create([
            'image_url' => $imageData['image_url'],
            'hd_image_url' => $imageData['hd_image_url'],
            'caption' => $caption,
            'discount_percentage' => $discountPercentage,
            'discount_code' => BarimaxAd::generateDiscountCode(),
            'valid_until' => now()->addDays(3),
            'is_active' => true,
            'ai_generated' => true,
            'category' => array_rand(BarimaxAd::getCategories()),
            'description' => "Exclusive AI-generated discount offer. Valid for 3 days.",
            'call_to_action' => 'Claim Your Discount',
            'tags' => ['ai-generated', 'limited-time', 'special-offer'],
            'views_count' => 0,
            'clicks_count' => 0,
        ]);

        // Clear cache
        Cache::forget('current_featured_ad');
        Cache::forget('active_barimax_ads');

        return $ad;
    }

    // Get all active ads
    public function getActiveAds($limit = 5)
    {
        return Cache::remember("active_barimax_ads_{$limit}", now()->addHours(6), function () use ($limit) {
            return BarimaxAd::active()
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
        });
    }
}