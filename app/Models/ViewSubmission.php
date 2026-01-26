<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViewSubmission extends Model
{
    use HasFactory;

    protected $guarded= [];

    protected $casts = [
        'views_count' => 'integer',
        'earned_amount' => 'decimal:2',
        'submitted_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PROCESSING = 'processing';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Platform constants
     */
    const PLATFORM_YOUTUBE = 'YouTube';
    const PLATFORM_FACEBOOK = 'Facebook';
    const PLATFORM_INSTAGRAM = 'Instagram';
    const PLATFORM_TIKTOK = 'TikTok';

    /**
     * Get the user that owns the submission
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include pending submissions
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include approved submissions
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Get the screenshot URL
     */
    public function getScreenshotUrlAttribute()
    {
        return $this->screenshot_path ? Storage::url($this->screenshot_path) : null;
    }

    /**
     * Check if submission can be cancelled
     */
    public function getCanBeCancelledAttribute()
    {
        return $this->status === self::STATUS_PENDING;
    }
}