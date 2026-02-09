<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarimaxAd extends Model
{
    use HasFactory;

    protected $fillable = [
        'image_url',
        'hd_image_url',
        'caption',
        'discount_percentage',
        'discount_code',
        'valid_until',
        'is_active',
        'views_count',
        'clicks_count',
        'ai_generated',
        'tags',
        'category',
        'description',
        'call_to_action',
        'background_color',
        'text_color',
    ];

    protected $casts = [
        'discount_percentage' => 'integer',
        'is_active' => 'boolean',
        'ai_generated' => 'boolean',
        'valid_until' => 'datetime',
        'views_count' => 'integer',
        'clicks_count' => 'integer',
        'tags' => 'array',
    ];

    // Scope for active ads
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where('valid_until', '>', now());
    }

    // Scope for featured ads
    public function scopeFeatured($query)
    {
        return $query->active()->where('ai_generated', true);
    }

    // Get current featured ad
    public static function getCurrentFeatured()
    {
        return Cache::remember('current_featured_ad', now()->addHours(6), function () {
            return self::featured()
                ->orderBy('created_at', 'desc')
                ->first();
        });
    }

    // Get random discount percentage
    public static function generateRandomDiscount()
    {
        $discounts = [5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55, 60, 65, 70];
        return $discounts[array_rand($discounts)];
    }

    // Generate discount code
    public static function generateDiscountCode()
    {
        $prefix = 'BARIMAX';
        $date = now()->format('md');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));
        
        return "{$prefix}{$date}{$random}";
    }

    // Get categories
    public static function getCategories()
    {
        return [
            'packages' => 'Earning Packages',
            'online_work' => 'Online Work',
            'certification' => 'Certification',
            'starlink' => 'Starlink',
            'agent' => 'Agent Program',
            'wallet' => 'Wallet Services',
            'cashflow' => 'Cash Flow',
            'team' => 'Team Building',
            'bonus' => 'Bonus Offers',
            'limited' => 'Limited Time',
            'seasonal' => 'Seasonal Offer',
        ];
    }

    // Increment views
    public function incrementViews()
    {
        $this->increment('views_count');
    }

    // Increment clicks
    public function incrementClicks()
    {
        $this->increment('clicks_count');
    }

    // Check if expired
    public function isExpired()
    {
        return $this->valid_until->isPast();
    }

    // Get days remaining
    public function getDaysRemainingAttribute()
    {
        return $this->valid_until->diffInDays(now());
    }

    // Get hours remaining
    public function getHoursRemainingAttribute()
    {
        return $this->valid_until->diffInHours(now());
    }

    // Get formatted discount
    public function getFormattedDiscountAttribute()
    {
        return "{$this->discount_percentage}% OFF";
    }

    // Get gradient colors
    public function getGradientColorsAttribute()
    {
        $colors = [
            'from-purple-600/90 to-pink-600/90',
            'from-blue-600/90 to-cyan-600/90',
            'from-green-600/90 to-emerald-600/90',
            'from-orange-600/90 to-yellow-600/90',
            'from-red-600/90 to-pink-600/90',
            'from-indigo-600/90 to-purple-600/90',
            'from-teal-600/90 to-cyan-600/90',
            'from-amber-600/90 to-orange-600/90',
        ];
        
        return $colors[array_rand($colors)];
    }
}