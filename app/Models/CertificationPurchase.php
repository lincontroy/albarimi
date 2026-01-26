<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificationPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'package_type',
        'package_name',
        'amount',
        'certification_code',
        'access_code',
        'verification_code',
        'status',
        'activated_at',
        'expires_at',
        'package_details',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'activated_at' => 'datetime',
        'expires_at' => 'datetime',
        'package_details' => 'array',
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

    // Generate unique code based on package type
    public static function generateCode($type, $userId)
    {
        $prefix = match($type) {
            'certification' => 'CERT',
            'access_code' => 'ACC',
            'verification' => 'VER',
            default => 'PKG'
        };

        $timestamp = now()->format('YmdHis');
        $uniqueId = str_pad($userId, 6, '0', STR_PAD_LEFT);
        $random = strtoupper(substr(md5(uniqid()), 0, 4));

        return "{$prefix}-{$timestamp}-{$uniqueId}-{$random}";
    }

    // Get package details
    public static function getPackageDetails($type)
    {
        return match($type) {
            'certification' => [
                'name' => 'Certification Package',
                'amount' => 8500,
                'duration_days' => 365, // 1 year
                'features' => [
                    'Full certification access',
                    'Priority support',
                    'Advanced analytics',
                    'Certificate of completion',
                    'Digital badge',
                    'Lifetime access to materials',
                ]
            ],
            'access_code' => [
                'name' => 'Access Code Package',
                'amount' => 5500,
                'duration_days' => 180, // 6 months
                'features' => [
                    'Access to premium content',
                    'Monthly updates',
                    'Community access',
                    'Basic support',
                    'Progress tracking',
                ]
            ],
            'verification' => [
                'name' => 'Verification Package',
                'amount' => 4500,
                'duration_days' => 90, // 3 months
                'features' => [
                    'Account verification',
                    'Identity verification',
                    'Basic analytics',
                    'Email support',
                    'Verification badge',
                ]
            ],
            default => null
        };
    }
}