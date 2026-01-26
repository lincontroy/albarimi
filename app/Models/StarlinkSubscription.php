<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class StarlinkSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'starlink_package_id',
        'subscription_code',
        'amount_paid',
        'payment_method',
        'status',
        'subscribed_at',
        'activated_at',
        'expires_at',
        'transaction_id',
        'payment_details',
        'notes'
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'subscribed_at' => 'datetime',
        'activated_at' => 'datetime',
        'expires_at' => 'datetime',
        'payment_details' => 'array',
    ];

    // Status colors for UI
    public static $statusColors = [
        'pending' => 'yellow',
        'active' => 'green',
        'suspended' => 'orange',
        'cancelled' => 'red',
        'expired' => 'gray',
    ];

    // Status texts
    public static $statusTexts = [
        'pending' => 'Pending Activation',
        'active' => 'Active',
        'suspended' => 'Suspended',
        'cancelled' => 'Cancelled',
        'expired' => 'Expired',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(StarlinkPackage::class, 'starlink_package_id');
    }

    public static function generateSubscriptionCode()
    {
        do {
            $code = 'STAR-' . strtoupper(Str::random(4)) . '-' . mt_rand(1000, 9999) . '-' . strtoupper(Str::random(3));
        } while (self::where('subscription_code', $code)->exists());

        return $code;
    }

    public function getStatusColor()
    {
        return self::$statusColors[$this->status] ?? 'gray';
    }

    public function getStatusText()
    {
        return self::$statusTexts[$this->status] ?? 'Unknown';
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function getDaysRemaining()
    {
        if (!$this->expires_at) {
            return null;
        }
        
        return now()->diffInDays($this->expires_at, false);
    }

    public function getFormattedAmount()
    {
        return 'KES ' . number_format($this->amount_paid, 0);
    }

    public function activate()
    {
        $this->update([
            'status' => 'active',
            'activated_at' => now(),
            'expires_at' => now()->addMonth(), // Default 1 month subscription
        ]);
    }

    public function cancel()
    {
        $this->update([
            'status' => 'cancelled',
            'expires_at' => now(),
        ]);
    }

    public function renew()
    {
        if ($this->isActive() && $this->expires_at) {
            $this->update([
                'expires_at' => $this->expires_at->addMonth(),
                'status' => 'active',
            ]);
        }
    }
}