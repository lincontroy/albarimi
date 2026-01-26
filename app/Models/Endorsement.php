<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Endorsement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'application_id',
        'type',
        'title',
        'description',
        'skills',
        'documents',
        'links',
        'package_fee',
        'status',
        'review_notes',
        'rating',
        'views',
        'endorsements_count',
        'submitted_at',
        'reviewed_at',
        'approved_at',
        'expires_at',
        'transaction_id',
    ];

    protected $casts = [
        'skills' => 'array',
        'documents' => 'array',
        'links' => 'array',
        'package_fee' => 'decimal:2',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Type helpers
    public function getTypeText(): string
    {
        $types = [
            'professional' => 'Professional Endorsement',
            'skill' => 'Skill Endorsement',
            'service' => 'Service Endorsement',
            'product' => 'Product Endorsement',
            'business' => 'Business Endorsement',
        ];

        return $types[$this->type] ?? 'Professional Endorsement';
    }

    public function getTypeColor(): string
    {
        $colors = [
            'professional' => '#3b82f6',
            'skill' => '#10b981',
            'service' => '#8b5cf6',
            'product' => '#f59e0b',
            'business' => '#ec4899',
        ];

        return $colors[$this->type] ?? '#3b82f6';
    }

    public function getTypeIcon(): string
    {
        $icons = [
            'professional' => 'briefcase',
            'skill' => 'award',
            'service' => 'users',
            'product' => 'package',
            'business' => 'building',
        ];

        return $icons[$this->type] ?? 'briefcase';
    }

    // Status helpers
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isUnderReview(): bool
    {
        return $this->status === 'under_review';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    // Status badge
    public function getStatusBadge(): array
    {
        $badges = [
            'pending' => ['text' => 'Pending Payment', 'color' => '#fbbf24', 'bgColor' => 'rgba(251, 191, 36, 0.2)'],
            'under_review' => ['text' => 'Under Review', 'color' => '#3b82f6', 'bgColor' => 'rgba(59, 130, 246, 0.2)'],
            'approved' => ['text' => 'Approved', 'color' => '#10b981', 'bgColor' => 'rgba(16, 185, 129, 0.2)'],
            'rejected' => ['text' => 'Rejected', 'color' => '#ef4444', 'bgColor' => 'rgba(239, 68, 68, 0.2)'],
            'active' => ['text' => 'Active', 'color' => '#8b5cf6', 'bgColor' => 'rgba(139, 92, 246, 0.2)'],
            'expired' => ['text' => 'Expired', 'color' => '#6b7280', 'bgColor' => 'rgba(107, 114, 128, 0.2)'],
        ];

        return $badges[$this->status] ?? $badges['pending'];
    }

    // Format currency
    public function getFormattedFee(): string
    {
        return 'KES ' . number_format($this->package_fee, 2);
    }

    // Check if endorsement is visible
    public function isVisible(): bool
    {
        return in_array($this->status, ['active', 'approved']);
    }

    // Check days remaining
    public function getDaysRemaining(): ?int
    {
        if (!$this->expires_at) {
            return null;
        }

        return now()->diffInDays($this->expires_at, false);
    }

    // Generate application ID
    public static function generateApplicationId(): string
    {
        return 'END-' . date('Ymd') . '-' . strtoupper(uniqid());
    }
}