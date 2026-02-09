<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnlineWorkAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'platform',
        'platform_name',
        'amount',
        'account_details',
        'login_credentials',
        'status',
        'purchased_at',
        'expires_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'purchased_at' => 'datetime',
        'expires_at' => 'datetime',
        'account_details' => 'array',
        'login_credentials' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Check if account is active
    public function isActive()
    {
        return $this->status === 'active' && 
               (!$this->expires_at || $this->expires_at->isFuture());
    }

    // Check if account has expired
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    // Generate unique account ID
    public static function generateAccountId($platform, $userId)
    {
        $prefix = strtoupper(substr($platform, 0, 4));
        $timestamp = now()->format('YmdHis');
        $uniqueId = str_pad($userId, 6, '0', STR_PAD_LEFT);
        $random = strtoupper(substr(md5(uniqid()), 0, 4));

        return "ACC-{$prefix}-{$timestamp}-{$uniqueId}-{$random}";
    }

    // Get platform details
    public static function getPlatformDetails($platform)
    {
        $platforms = [
            'handshake' => [
                'name' => 'Handshake Account',
                'amount' => 15000,
                'duration_days' => 180,
                'features' => [
                    'Fully verified account',
                    'Complete profile setup',
                    'Ready for freelance tasks',
                    'Payment method linked',
                    'Email verified',
                    'Skills tested',
                    'Premium rating',
                ],
                'description' => 'Premium freelance account with high success rate',
            ],
            'telus' => [
                'name' => 'Telus International Account',
                'amount' => 15000,
                'duration_days' => 180,
                'features' => [
                    'International account',
                    'Multiple language support',
                    'AI training ready',
                    'Data annotation access',
                    'Full profile completion',
                    'Test passed',
                    'Payment verified',
                ],
                'description' => 'Global AI training and data annotation platform',
            ],
            'imerit' => [
                'name' => 'iMerit Account',
                'amount' => 15000,
                'duration_days' => 180,
                'features' => [
                    'AI data labeling account',
                    'Quality rating above 90%',
                    'Multiple project access',
                    'Full training completed',
                    'Payment integration',
                    'Priority queue',
                    '24/7 support',
                ],
                'description' => 'Professional AI data labeling platform',
            ],
            'atlas_capture' => [
                'name' => 'Atlas Capture Account',
                'amount' => 15000,
                'duration_days' => 180,
                'features' => [
                    'Mapping data collection',
                    'Geo-spatial tasks',
                    'Image annotation ready',
                    'Full verification',
                    'Mobile app access',
                    'Real-time tracking',
                    'Global projects',
                ],
                'description' => 'Geographic data collection platform',
            ],
            'crowdgen' => [
                'name' => 'CrowdGen Account',
                'amount' => 15000,
                'duration_days' => 180,
                'features' => [
                    'Crowdsourcing platform',
                    'Multiple task types',
                    'High success rate',
                    'Quick payment',
                    'User-friendly interface',
                    'Skill matching',
                    'Regular projects',
                ],
                'description' => 'Crowdsourcing platform for various tasks',
            ],
            'mindrift' => [
                'name' => 'Mindrift Account',
                'amount' => 15000,
                'duration_days' => 180,
                'features' => [
                    'Creative tasks platform',
                    'Writing and editing',
                    'Content creation',
                    'Full profile setup',
                    'Portfolio included',
                    'Client ratings',
                    'Direct messaging',
                ],
                'description' => 'Creative content and writing platform',
            ],
            'dataannotation' => [
                'name' => 'DataAnnotation.tech Account',
                'amount' => 15000,
                'duration_days' => 180,
                'features' => [
                    'AI training specialist',
                    'Data labeling expert',
                    'Multiple project types',
                    'High earning potential',
                    'Regular availability',
                    'Skill verification',
                    'Priority access',
                ],
                'description' => 'Specialized AI data annotation platform',
            ],
            'cloudworkers' => [
                'name' => 'CloudWorkers Account',
                'amount' => 15000,
                'duration_days' => 180,
                'features' => [
                    'Remote work platform',
                    'Multiple skill categories',
                    'Full verification',
                    'Payment processed',
                    'Profile optimization',
                    'Task matching',
                    'Support available',
                ],
                'description' => 'Cloud-based remote work platform',
            ],
            'textfactory' => [
                'name' => 'Text Factory Account',
                'amount' => 15000,
                'duration_days' => 180,
                'features' => [
                    'Text processing tasks',
                    'Data entry ready',
                    'Full training completed',
                    'Quality assurance',
                    'Regular projects',
                    'Quick payment',
                    'User support',
                ],
                'description' => 'Text processing and data entry platform',
            ],
        ];

        return $platforms[$platform] ?? null;
    }

    // Get all platforms
    public static function getAllPlatforms()
    {
        return [
            'handshake',
            'telus',
            'imerit',
            'atlas_capture',
            'crowdgen',
            'mindrift',
            'dataannotation',
            'cloudworkers',
            'textfactory',
        ];
    }
}