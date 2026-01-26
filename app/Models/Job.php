<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    protected $table = 'jobss';

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'description',
        'requirements',
        'benefits',
        'salary',
        'salary_type',
        'location',
        'job_type',
        'experience_level',
        'education_level',
        'vacancies',
        'application_deadline',
        'is_remote',
        'is_featured',
        'is_active',
        'view_count',
        'application_count',
        'published_at'
    ];

    protected $casts = [
        'application_deadline' => 'date',
        'published_at' => 'datetime',
        'is_remote' => 'boolean',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'salary' => 'decimal:2',
        'requirements' => 'array',
        'benefits' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(JobCategory::class, 'category_id');
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }

    public function activeApplications()
    {
        return $this->hasMany(JobApplication::class)->where('status', '!=', 'rejected');
    }

    public function userApplications()
    {
        return $this->hasMany(JobApplication::class)->where('user_id', auth()->id());
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('application_deadline')
                  ->orWhere('application_deadline', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('published_at')
                  ->orWhere('published_at', '<=', now());
            });
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeRemote($query)
    {
        return $query->where('is_remote', true);
    }

    public function getFormattedSalary()
    {
        if (!$this->salary) {
            return 'Negotiable';
        }

        $salary = 'KES ' . number_format($this->salary);
        
        if ($this->salary_type) {
            $salary .= ' / ' . (JobCategory::$salaryTypes[$this->salary_type] ?? $this->salary_type);
        }

        return $salary;
    }

    public function getJobTypeText()
    {
        return JobCategory::$jobTypes[$this->job_type] ?? $this->job_type;
    }

    public function getExperienceLevelText()
    {
        return JobCategory::$experienceLevels[$this->experience_level] ?? $this->experience_level;
    }

    public function isActive()
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->application_deadline && $this->application_deadline->isPast()) {
            return false;
        }

        if ($this->published_at && $this->published_at->isFuture()) {
            return false;
        }

        return true;
    }

    public function hasVacancies()
    {
        return $this->vacancies > $this->application_count;
    }

    public function incrementViewCount()
    {
        $this->increment('view_count');
    }

    public function incrementApplicationCount()
    {
        $this->increment('application_count');
    }

    public function getDaysRemaining()
    {
        if (!$this->application_deadline) {
            return null;
        }

        return now()->diffInDays($this->application_deadline, false);
    }

    public function getStatusBadge()
    {
        if (!$this->is_active) {
            return ['color' => 'red', 'text' => 'Closed'];
        }

        if ($this->application_deadline && $this->application_deadline->isPast()) {
            return ['color' => 'red', 'text' => 'Expired'];
        }

        if (!$this->hasVacancies()) {
            return ['color' => 'red', 'text' => 'Filled'];
        }

        return ['color' => 'green', 'text' => 'Active'];
    }
}