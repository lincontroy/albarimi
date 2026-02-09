<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'package_type',
        'package_name',
        'amount',
        'status',
        'activated_at',
        'expires_at',
        'features',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'activated_at' => 'datetime',
        'expires_at' => 'datetime',
        'features' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Check if package is active
    public function isActive()
    {
        return $this->status === 'active' && 
               (!$this->expires_at || $this->expires_at->isFuture());
    }

    // Check if package has expired
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    // Get package details
    public static function getPackageDetails($type)
    {
        $packages = [
            'entry' => [
                'name' => 'Entry Package',
                'amount' => 800,
                'duration_days' => 30,
                'daily_earnings' => 50,
                'monthly_earnings' => 1500,
                'features' => [
                    'Basic task access',
                    'Up to 5 tasks daily',
                    'Email support',
                    'Progress tracking',
                    'Mobile app access',
                    'Basic analytics',
                ],
                'description' => 'Perfect for beginners starting their online earning journey',
            ],
            'lite' => [
                'name' => 'Lite Package',
                'amount' => 1000,
                'duration_days' => 30,
                'daily_earnings' => 80,
                'monthly_earnings' => 2400,
                'features' => [
                    'All Entry features',
                    'Up to 10 tasks daily',
                    'Priority task queue',
                    'WhatsApp support',
                    'Advanced analytics',
                    'Skill development',
                    'Weekly bonus tasks',
                ],
                'description' => 'Enhanced features for consistent earners',
            ],
            'pro' => [
                'name' => 'Pro Package',
                'amount' => 2400,
                'duration_days' => 30,
                'daily_earnings' => 150,
                'monthly_earnings' => 4500,
                'features' => [
                    'All Lite features',
                    'Unlimited tasks',
                    '24/7 priority support',
                    'Personal account manager',
                    'Advanced training',
                    'Early access to new features',
                    'Premium analytics dashboard',
                    'Referral bonus x2',
                ],
                'description' => 'Professional package for serious online earners',
            ],
            'bariplus' => [
                'name' => 'BariPlus Package',
                'amount' => 4800,
                'duration_days' => 30,
                'daily_earnings' => 250,
                'monthly_earnings' => 7500,
                'features' => [
                    'All Pro features',
                    'VIP priority tasks',
                    'Direct manager support',
                    'Custom earning strategies',
                    'Premium training modules',
                    'Exclusive bonus opportunities',
                    'Auto-task optimization',
                    'Daily performance reviews',
                    'Priority withdrawal processing',
                    'Premium community access',
                ],
                'description' => 'Premium package for maximum earning potential',
            ],
        ];

        return $packages[$type] ?? null;
    }

    // Get all package types
    public static function getAllPackages()
    {
        return ['entry', 'lite', 'pro', 'bariplus'];
    }

    // Calculate potential earnings
    public function getPotentialEarnings()
    {
        $details = self::getPackageDetails($this->package_type);
        if (!$details) return 0;

        return $details['daily_earnings'] * ($details['duration_days'] ?? 30);
    }
}