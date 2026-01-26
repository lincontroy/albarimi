<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class VisaCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'visa_type',
        'country',
        'amount',
        'status',
        'purchased_by',
        'purchased_at',
        'activated_at',
        'used_at',
        'expires_at',
        'visa_details',
        'notes'
    ];

    protected $casts = [
        'purchased_at' => 'datetime',
        'activated_at' => 'datetime',
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
        'visa_details' => 'array',
    ];

    // Available visa types and their details
    public static $visaTypes = [
        'tourist' => [
            'name' => 'Tourist Visa',
            'duration' => '30-90 days',
            'price' => 5000,
            'features' => [
                'Single entry visa',
                'Tourism purposes only',
                'Valid for 30-90 days',
                'Non-extendable'
            ]
        ],
        'business' => [
            'name' => 'Business Visa',
            'duration' => '1-2 years',
            'price' => 15000,
            'features' => [
                'Multiple entries',
                'Business activities allowed',
                'Valid for 1-2 years',
                'Possible extensions'
            ]
        ],
        'student' => [
            'name' => 'Student Visa',
            'duration' => 'Duration of study',
            'price' => 10000,
            'features' => [
                'For educational purposes',
                'Allows part-time work',
                'Valid for study duration',
                'Renewable'
            ]
        ],
        'work' => [
            'name' => 'Work Visa',
            'duration' => '1-3 years',
            'price' => 20000,
            'features' => [
                'Employment authorization',
                'Multiple entries',
                'Valid for 1-3 years',
                'Renewable'
            ]
        ],
        'transit' => [
            'name' => 'Transit Visa',
            'duration' => '24-72 hours',
            'price' => 2000,
            'features' => [
                'For transit purposes',
                'Short duration',
                'Single entry',
                'Non-extendable'
            ]
        ],
    ];

    // Available countries
    public static $countries = [
        'USA' => 'United States',
        'UK' => 'United Kingdom',
        'CANADA' => 'Canada',
        'AUSTRALIA' => 'Australia',
        'GERMANY' => 'Germany',
        'FRANCE' => 'France',
        'ITALY' => 'Italy',
        'SPAIN' => 'Spain',
        'JAPAN' => 'Japan',
        'CHINA' => 'China',
        'UAE' => 'United Arab Emirates',
        'SAUDI_ARABIA' => 'Saudi Arabia',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function purchaser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'purchased_by');
    }

    public function purchase(): HasOne
    {
        return $this->hasOne(VisaCodePurchase::class);
    }

    public static function generateCode(): string
    {
        do {
            $code = 'VISA' . strtoupper(Str::random(3)) . '-' . mt_rand(1000, 9999) . '-' . strtoupper(Str::random(3));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    public static function getPackageDetails(string $visaType): ?array
    {
        return self::$visaTypes[$visaType] ?? null;
    }

    public function isPurchased(): bool
    {
        return $this->status === 'purchased';
    }

    public function isActivated(): bool
    {
        return $this->status === 'activated';
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getDaysRemaining(): ?int
    {
        if (!$this->expires_at) {
            return null;
        }
        
        return now()->diffInDays($this->expires_at, false);
    }

    public function getStatusBadge(): array
    {
        $statuses = [
            'available' => ['color' => 'green', 'text' => 'Available'],
            'purchased' => ['color' => 'blue', 'text' => 'Purchased'],
            'activated' => ['color' => 'purple', 'text' => 'Activated'],
            'used' => ['color' => 'gray', 'text' => 'Used'],
            'expired' => ['color' => 'red', 'text' => 'Expired'],
        ];

        return $statuses[$this->status] ?? ['color' => 'gray', 'text' => 'Unknown'];
    }
}