<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappLinkage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone_number',
        'verification_code',
        'verification_sent_at',
        'verified_at',
        'status',
        'verification_attempts',
        'last_verification_attempt',
        'metadata',
    ];

    protected $casts = [
        'verification_sent_at' => 'datetime',
        'verified_at' => 'datetime',
        'last_verification_attempt' => 'datetime',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Status helpers
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    public function isUnlinked(): bool
    {
        return $this->status === 'unlinked';
    }

    public function isBlocked(): bool
    {
        return $this->status === 'blocked';
    }

    // Status badge
    public function getStatusBadge(): array
    {
        $badges = [
            'pending' => ['text' => 'Pending Verification', 'color' => '#fbbf24', 'bgColor' => 'rgba(251, 191, 36, 0.2)'],
            'verified' => ['text' => 'Verified', 'color' => '#10b981', 'bgColor' => 'rgba(16, 185, 129, 0.2)'],
            'unlinked' => ['text' => 'Unlinked', 'color' => '#6b7280', 'bgColor' => 'rgba(107, 114, 128, 0.2)'],
            'blocked' => ['text' => 'Blocked', 'color' => '#ef4444', 'bgColor' => 'rgba(239, 68, 68, 0.2)'],
        ];

        return $badges[$this->status] ?? $badges['pending'];
    }

    // Check if verification can be sent
    public function canSendVerification(): bool
    {
        if ($this->verification_sent_at && now()->diffInMinutes($this->verification_sent_at) < 2) {
            return false; // Wait 2 minutes between verification attempts
        }

        if ($this->verification_attempts >= 5) {
            return false; // Maximum 5 attempts
        }

        return true;
    }

    // Generate verification code
    public function generateVerificationCode(): string
    {
        return str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    // Format phone number
    public function getFormattedPhone(): string
    {
        $phone = $this->phone_number;
        if (str_starts_with($phone, '0')) {
            $phone = '254' . substr($phone, 1);
        }
        return $phone;
    }

    // Get WhatsApp link
    public function getWhatsappLink(): string
    {
        $phone = $this->getFormattedPhone();
        return "https://wa.me/{$phone}";
    }

    // Get WhatsApp chat link
    public function getWhatsappChatLink(): string
    {
        $phone = $this->getFormattedPhone();
        return "https://wa.me/{$phone}?text=Hello";
    }

    // Check if verification code is expired (10 minutes)
    public function isVerificationCodeExpired(): bool
    {
        if (!$this->verification_sent_at) {
            return true;
        }

        return now()->diffInMinutes($this->verification_sent_at) > 10;
    }
}