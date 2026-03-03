<?php
// app/Models/Product.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'gallery' => 'array',
        'specifications' => 'array',
    ];

    // Get the full image URL
    public function getImageUrlAttribute()
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }

    // Get thumbnail URL
    public function getThumbnailUrlAttribute()
    {
        return $this->thumbnail_path ? asset('storage/' . $this->thumbnail_path) : $this->image_url;
    }

    // Scope for active products
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for featured products
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // Generate SKU
    public static function generateSku($name)
    {
        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), 0, 3));
        $uniqueId = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        return $prefix . '-' . $uniqueId;
    }

    // Get formatted price
    public function getFormattedPriceAttribute()
    {
        return 'KES ' . number_format($this->price, 2);
    }

    // Check if in stock
    public function inStock()
    {
        return $this->stock > 0;
    }

    // Increment views
    public function incrementViews()
    {
        $this->increment('views_count');
    }

    // Increment sales
    public function incrementSales($quantity = 1)
    {
        $this->increment('sales_count', $quantity);
        $this->decrement('stock', $quantity);
    }
}