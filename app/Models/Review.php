<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'rating',
        'title',
        'comment',
        'status',
        'is_featured',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_featured' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Status scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    // Get average rating
    public static function getAverageRating()
    {
        return self::published()->avg('rating') ?? 0;
    }

    // Get total reviews
    public static function getTotalReviews()
    {
        return self::published()->count();
    }

    // Get rating distribution
    public static function getRatingDistribution()
    {
        return self::published()
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->orderBy('rating', 'desc')
            ->get()
            ->pluck('count', 'rating')
            ->toArray();
    }

    // Format rating stars
    public function getStarsAttribute()
    {
        $stars = '';
        for ($i = 1; $i <= 5; $i++) {
            $stars .= $i <= $this->rating ? '★' : '☆';
        }
        return $stars;
    }

    // Get human readable time
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    // Get user's display name (protect privacy)
    public function getDisplayNameAttribute()
    {
        $name = $this->user->name;
        $parts = explode(' ', $name);
        
        if (count($parts) > 1) {
            return $parts[0] . ' ' . substr($parts[1], 0, 1) . '.';
        }
        
        return $name;
    }
}