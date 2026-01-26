<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'sort_order',
        'is_active'
    ];

    public function jobs()
    {
        return $this->hasMany(Job::class, 'category_id');
    }

    public function activeJobs()
    {
        return $this->hasMany(Job::class, 'category_id')->where('is_active', true);
    }

    // Default categories
    public static $defaultCategories = [
        [
            'name' => 'Information Technology',
            'slug' => 'information-technology',
            'description' => 'IT and software development jobs',
            'icon' => 'Code',
            'color' => 'blue',
            'sort_order' => 1,
        ],
        [
            'name' => 'Marketing & Sales',
            'slug' => 'marketing-sales',
            'description' => 'Marketing, advertising, and sales positions',
            'icon' => 'TrendingUp',
            'color' => 'green',
            'sort_order' => 2,
        ],
        [
            'name' => 'Finance & Accounting',
            'slug' => 'finance-accounting',
            'description' => 'Banking, finance, and accounting jobs',
            'icon' => 'DollarSign',
            'color' => 'emerald',
            'sort_order' => 3,
        ],
        [
            'name' => 'Healthcare',
            'slug' => 'healthcare',
            'description' => 'Medical and healthcare positions',
            'icon' => 'Heart',
            'color' => 'red',
            'sort_order' => 4,
        ],
        [
            'name' => 'Education',
            'slug' => 'education',
            'description' => 'Teaching and educational roles',
            'icon' => 'GraduationCap',
            'color' => 'purple',
            'sort_order' => 5,
        ],
        [
            'name' => 'Customer Service',
            'slug' => 'customer-service',
            'description' => 'Customer support and service jobs',
            'icon' => 'Headphones',
            'color' => 'orange',
            'sort_order' => 6,
        ],
        [
            'name' => 'Engineering',
            'slug' => 'engineering',
            'description' => 'Engineering and technical roles',
            'icon' => 'Cog',
            'color' => 'cyan',
            'sort_order' => 7,
        ],
        [
            'name' => 'Design & Creative',
            'slug' => 'design-creative',
            'description' => 'Design, arts, and creative jobs',
            'icon' => 'Palette',
            'color' => 'pink',
            'sort_order' => 8,
        ],
        [
            'name' => 'Administration',
            'slug' => 'administration',
            'description' => 'Administrative and office support',
            'icon' => 'Briefcase',
            'color' => 'gray',
            'sort_order' => 9,
        ],
        [
            'name' => 'Human Resources',
            'slug' => 'human-resources',
            'description' => 'HR and recruitment positions',
            'icon' => 'Users',
            'color' => 'indigo',
            'sort_order' => 10,
        ],
    ];

    // Job types
    public static $jobTypes = [
        'full_time' => 'Full Time',
        'part_time' => 'Part Time',
        'contract' => 'Contract',
        'freelance' => 'Freelance',
        'internship' => 'Internship',
        'remote' => 'Remote',
    ];

    // Experience levels
    public static $experienceLevels = [
        'entry' => 'Entry Level (0-2 years)',
        'mid' => 'Mid Level (2-5 years)',
        'senior' => 'Senior Level (5+ years)',
        'executive' => 'Executive',
    ];

    // Salary types
    public static $salaryTypes = [
        'hourly' => 'Per Hour',
        'daily' => 'Per Day',
        'weekly' => 'Per Week',
        'monthly' => 'Per Month',
        'yearly' => 'Per Year',
    ];
}