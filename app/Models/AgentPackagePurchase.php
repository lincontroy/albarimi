<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentPackagePurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'package_name',
        'benefits',
        'transaction_id',
        'status',
        'purchased_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'benefits' => 'array',
        'purchased_at' => 'datetime',
    ];

    const PACKAGE_PRICE = 6500;
    const PACKAGE_NAME = 'Albarimi Motors Agent Package';

    /**
     * Get the user that owns the purchase
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate transaction ID
     */
    public function generateTransactionId()
    {
        // Generate a unique transaction ID using timestamp and user ID
        $timestamp = now()->format('YmdHis');
        $userId = str_pad($this->user_id, 6, '0', STR_PAD_LEFT);
        $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        return "AGENT-{$timestamp}-{$userId}-{$random}";
    }
    /**
     * Get package benefits
     */
    public static function getBenefits()
    {
        return [
            '15,000 KES cashback bonus',
            '35,000 KES monthly agent salary',
            'Automatic and instant withdrawals',
            'Most Superior package benefits',
            'Priority support',
            'Exclusive agent features'
        ];
    }
}