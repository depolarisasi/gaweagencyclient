<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Template extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'demo_url',
        'thumbnail_url',
        'category',
        'is_active',
        'sort_order',
        'features',
        'preview_images',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'features' => 'array',
        'preview_images' => 'array',
    ];

    // Relationships
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // Helper methods
    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function hasDemo(): bool
    {
        return !empty($this->demo_url);
    }

    public function hasThumbnail(): bool
    {
        return !empty($this->thumbnail_url);
    }

    public function getFormattedFeaturesAttribute(): string
    {
        if (empty($this->features)) {
            return 'Tidak ada fitur yang tercantum';
        }

        return implode(', ', $this->features);
    }

    public function getCategoryBadgeClassAttribute(): string
    {
        return match($this->category) {
            'business' => 'badge-primary',
            'ecommerce' => 'badge-success',
            'portfolio' => 'badge-info',
            'blog' => 'badge-warning',
            'landing' => 'badge-accent',
            default => 'badge-secondary'
        };
    }

    public function getCategoryTextAttribute(): string
    {
        return match($this->category) {
            'business' => 'Bisnis',
            'ecommerce' => 'E-Commerce',
            'portfolio' => 'Portfolio',
            'blog' => 'Blog',
            'landing' => 'Landing Page',
            default => ucfirst($this->category)
        };
    }
}