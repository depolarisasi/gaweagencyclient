<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductAddon extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'billing_type', // 'one_time' or 'recurring'
        'billing_cycle', // for recurring addons
        'is_active',
        'sort_order',
        'category',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Relationships
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_addon_pivot')
                    ->withTimestamps();
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'addon_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOneTime($query)
    {
        return $query->where('billing_type', 'one_time');
    }

    public function scopeRecurring($query)
    {
        return $query->where('billing_type', 'recurring');
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

    public function isOneTime(): bool
    {
        return $this->billing_type === 'one_time';
    }

    public function isRecurring(): bool
    {
        return $this->billing_type === 'recurring';
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    public function getBillingTypeTextAttribute(): string
    {
        return match($this->billing_type) {
            'one_time' => 'Sekali Bayar',
            'recurring' => 'Berulang',
            default => ucfirst($this->billing_type)
        };
    }

    public function getBillingCycleTextAttribute(): string
    {
        if ($this->billing_type === 'one_time') {
            return 'Sekali Bayar';
        }

        $cycles = [
            'monthly' => 'Bulanan',
            'quarterly' => 'Triwulan',
            'semi_annually' => 'Semester',
            'annually' => 'Tahunan',
        ];

        return $cycles[$this->billing_cycle] ?? $this->billing_cycle;
    }

    public function getCategoryBadgeClassAttribute(): string
    {
        return match($this->category) {
            'hosting' => 'badge-primary',
            'domain' => 'badge-success',
            'ssl' => 'badge-warning',
            'maintenance' => 'badge-info',
            'marketing' => 'badge-accent',
            default => 'badge-secondary'
        };
    }

    public function getCategoryTextAttribute(): string
    {
        return match($this->category) {
            'hosting' => 'Hosting',
            'domain' => 'Domain',
            'ssl' => 'SSL Certificate',
            'maintenance' => 'Maintenance',
            'marketing' => 'Marketing',
            default => ucfirst($this->category)
        };
    }
}