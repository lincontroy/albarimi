<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisaCodePurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'visa_code_id',
        'amount',
        'payment_method',
        'status',
        'transaction_id',
        'payment_details'
    ];

    protected $casts = [
        'payment_details' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function visaCode(): BelongsTo
    {
        return $this->belongsTo(VisaCode::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}