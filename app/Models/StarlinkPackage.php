<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StarlinkPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'monthly_fee',
        'data_limit_gb',
        'speed_mbps',
        'duration',
        'features',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'monthly_fee' => 'decimal:2',
        'features' => 'array',
        'is_active' => 'boolean',
    ];

    // Package types
    public static $packageTypes = [
        'residential' => [
            'name' => 'Residential',
            'description' => 'High-speed internet for home use',
            'icon' => 'Home',
            'color' => 'blue'
        ],
        'business' => [
            'name' => 'Business',
            'description' => 'Reliable internet for businesses',
            'icon' => 'Briefcase',
            'color' => 'purple'
        ],
        'roaming' => [
            'name' => 'Roaming',
            'description' => 'Global coverage for travelers',
            'icon' => 'Globe',
            'color' => 'green'
        ],
        'maritime' => [
            'name' => 'Maritime',
            'description' => 'Internet for maritime use',
            'icon' => 'Ship',
            'color' => 'cyan'
        ],
    ];

    // Available durations
    public static $durations = [
        'monthly' => 'Monthly',
        'quarterly' => 'Quarterly (3 months)',
        'annually' => 'Annually (12 months)',
    ];

    // Default packages (you can seed these)
    public static $defaultPackages = [
        [
            'name' => 'Starlink Residential',
            'description' => 'High-speed, low-latency broadband internet for homes',
            'price' => 59900,
            'monthly_fee' => 9900,
            'data_limit_gb' => null, // Unlimited
            'speed_mbps' => 100,
            'duration' => 'monthly',
            'features' => [
                'High-speed internet (up to 100 Mbps)',
                'Low latency (20-40 ms)',
                'Unlimited data',
                'Easy self-installation',
                '24/7 customer support',
                'Weather-resistant hardware'
            ],
            'sort_order' => 1,
        ],
        [
            'name' => 'Starlink Business',
            'description' => 'Premium internet service for businesses and offices',
            'price' => 99900,
            'monthly_fee' => 19900,
            'data_limit_gb' => null,
            'speed_mbps' => 200,
            'duration' => 'monthly',
            'features' => [
                'High-speed internet (up to 200 Mbps)',
                'Priority customer support',
                'Business-grade hardware',
                'Static IP address',
                'SLA guarantee',
                'Multiple device support'
            ],
            'sort_order' => 2,
        ],
        [
            'name' => 'Starlink Roaming',
            'description' => 'Global internet access for travelers and mobile use',
            'price' => 79900,
            'monthly_fee' => 14900,
            'data_limit_gb' => 1000,
            'speed_mbps' => 50,
            'duration' => 'monthly',
            'features' => [
                'Global coverage',
                'Portable hardware',
                'Mobile connectivity',
                'Easy activation/deactivation',
                'No long-term contract',
                'Travel-friendly'
            ],
            'sort_order' => 3,
        ],
        [
            'name' => 'Starlink Premium',
            'description' => 'Ultra-high-speed internet for power users',
            'price' => 149900,
            'monthly_fee' => 29900,
            'data_limit_gb' => null,
            'speed_mbps' => 500,
            'duration' => 'monthly',
            'features' => [
                'Ultra-high-speed (up to 500 Mbps)',
                'Lowest latency',
                'Priority network access',
                'Professional installation',
                '24/7 dedicated support',
                'Hardware warranty'
            ],
            'sort_order' => 4,
        ],
    ];

    public function subscriptions()
    {
        return $this->hasMany(StarlinkSubscription::class);
    }

    public function activeSubscriptions()
    {
        return $this->hasMany(StarlinkSubscription::class)->where('status', 'active');
    }

    public function isUnlimited()
    {
        return $this->data_limit_gb === null;
    }

    public function getDataLimitText()
    {
        return $this->isUnlimited() ? 'Unlimited' : $this->data_limit_gb . ' GB';
    }

    public function getFormattedPrice()
    {
        return 'KES ' . number_format($this->price, 0);
    }

    public function getFormattedMonthlyFee()
    {
        return 'KES ' . number_format($this->monthly_fee, 0);
    }

    public function getDurationText()
    {
        return self::$durations[$this->duration] ?? $this->duration;
    }

    public function getMonthlySavings()
    {
        if ($this->duration === 'quarterly') {
            return $this->monthly_fee * 3 - $this->price;
        } elseif ($this->duration === 'annually') {
            return $this->monthly_fee * 12 - $this->price;
        }
        return 0;
    }

    public function isAvailable()
    {
        return $this->is_active;
    }
}