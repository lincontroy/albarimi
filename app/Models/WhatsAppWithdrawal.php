<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppWithdrawal extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'whatsapp_withdrawals';

    protected $casts = [
        'amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    /**
     * Minimum and maximum withdrawal amounts
     */
    const MIN_WITHDRAWAL = 100;
    const MAX_WITHDRAWAL = 50000;

    /**
     * Get the user that owns the withdrawal
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include pending withdrawals
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include approved withdrawals
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope a query to only include processing withdrawals
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    /**
     * Check if withdrawal can be processed
     */
    public function canBeProcessed()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if withdrawal is pending
     */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Generate transaction ID
     */
    public function generateTransactionId()
    {
        return 'WT' . now()->format('YmdHis') . $this->id;
    }
}