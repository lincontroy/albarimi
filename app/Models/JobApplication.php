<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'job_id',
        'cover_letter',
        'resume_path',
        'status',
        'application_fee',
        'transaction_id',
        'application_data',
        'notes',
        'applied_at',
        'reviewed_at'
    ];

    protected $casts = [
        'application_fee' => 'decimal:2',
        'applied_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'application_data' => 'array',
    ];

    // Status colors
    public static $statusColors = [
        'pending' => 'yellow',
        'reviewing' => 'blue',
        'shortlisted' => 'purple',
        'rejected' => 'red',
        'hired' => 'green',
    ];

    // Status texts
    public static $statusTexts = [
        'pending' => 'Pending Review',
        'reviewing' => 'Under Review',
        'shortlisted' => 'Shortlisted',
        'rejected' => 'Rejected',
        'hired' => 'Hired',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return in_array($this->status, ['reviewing', 'shortlisted', 'hired']);
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function getStatusColor()
    {
        return self::$statusColors[$this->status] ?? 'gray';
    }

    public function getStatusText()
    {
        return self::$statusTexts[$this->status] ?? 'Unknown';
    }

    public function getFormattedFee()
    {
        return 'KES ' . number_format($this->application_fee, 0);
    }

    public function getDaysSinceApplied()
    {
        return now()->diffInDays($this->applied_at);
    }
}