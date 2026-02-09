<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'agent_since' => 'datetime',
            'last_active_at' => 'datetime',
        ];
    }

    public function agentPackage()
    {
        return $this->hasOne(AgentPackagePurchase::class)->latest();
    }

    public function isAgent()
    {
        return $this->is_agent;
    }

    public function becomeAgent()
    {
        $this->update([
            'is_agent' => true,
            'agent_since' => now(),
            'agent_bonus' => 15000,
            'agent_salary' => 35000,
        ]);
    }

    public function bonusClaims()
    {
        return $this->hasMany(BonusClaim::class);
    }

    public function hasSufficientBalance($amount)
    {
        return $this->deposit_balance >= $amount;
    }

    public function deductFromBalance($amount)
    {
        $this->decrement('deposit_balance', $amount);
    }

    // ============ REFERRAL SYSTEM ============
    
    // Referrer relationship (who referred this user)
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    // Referrals relationship (users who were referred by this user)
    public function referrals()
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    // Active referrals (users who have been active recently)
    public function activeReferrals()
    {
        return $this->referrals()
            ->where('last_active_at', '>=', now()->subDays(30))
            ->where('is_active', true);
    }

    // Generate referral link
    public function getReferralLinkAttribute()
    {
        return url('/register?ref=' . $this->referral_code);
    }

    // Check if user is active (logged in within last 7 days)
    public function getIsActiveAttribute()
    {
        if (!$this->last_active_at) {
            return false;
        }
        return $this->last_active_at->gt(now()->subDays(7));
    }


    // Get referral stats
    public function getReferralStatsAttribute()
    {
        $totalReferrals = $this->referrals()->count();
        $activeReferrals = $this->activeReferrals()->count();
        
        return [
            'total' => $totalReferrals,
            'active' => $activeReferrals,
            'inactive' => $totalReferrals - $activeReferrals,
        ];
    }

    // Add to User model
public function withdrawals()
{
    return $this->hasMany(Withdrawal::class);
}

public function getTotalWithdrawnAttribute()
{
    return $this->withdrawals()->completed()->sum('amount');
}

public function getWhatsappWithdrawnAttribute()
{
    return $this->withdrawals()->completed()->whatsapp()->sum('amount');
}
// In User model, add this method to automatically update is_active:
protected static function booted()
{
    static::updating(function ($user) {
        // Update is_active based on last_active_at
        if ($user->isDirty('last_active_at')) {
            $user->is_active = $user->last_active_at && 
                              $user->last_active_at->gt(now()->subDays(7));
        }
    });
}

// Or update the updateLastActive method:
public function updateLastActive()
{
    $this->update([
        'last_active_at' => now(),
        'is_active' => true,
    ]);
}
// Reviews received by this user
public function receivedReviews()
{
    return $this->hasMany(Review::class, 'user_id');
}

// Reviews given by this user
public function givenReviews()
{
    return $this->hasMany(Review::class, 'reviewer_id');
}

// Active reviews received
public function activeReviews()
{
    return $this->receivedReviews()->where('status', 'published');
}

// Featured reviews received
public function featuredReviews()
{
    return $this->activeReviews()->where('is_featured', true);
}

// Calculate average rating
public function getAverageRatingAttribute()
{
    return $this->activeReviews()->avg('rating') ?? 0;
}

// Get rating stars
public function getRatingStarsAttribute()
{
    $rating = round($this->average_rating);
    $stars = '';
    for ($i = 1; $i <= 5; $i++) {
        $stars .= $i <= $rating ? '★' : '☆';
    }
    return $stars;
}

// Get total reviews count
public function getTotalReviewsAttribute()
{
    return $this->activeReviews()->count();
}
}