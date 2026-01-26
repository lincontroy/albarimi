<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BonusClaim extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bonus_type',
        'bonus_amount',
        'claim_cost',
        'status',
        'failure_reason'
    ];

    protected $casts = [
        'bonus_amount' => 'decimal:2',
        'claim_cost' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Check if user has already claimed a specific bonus type
    public static function hasClaimed($userId, $bonusType)
    {
        return self::where('user_id', $userId)
            ->where('bonus_type', $bonusType)
            ->where('status', 'claimed')
            ->exists();
    }
}