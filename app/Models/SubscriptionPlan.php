<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'billing_cycle',
        'cycle_months',
        'discount_percentage',
        'features',
        'is_active',
        'sort_order',
        'is_popular',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'features' => 'array',
        'is_active' => 'boolean',
        'is_popular' => 'boolean',
    ];

    /**
     * Get orders for this subscription plan
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Scope for active plans
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for popular plans
     */
    public function scopePopular($query)
    {
        return $query->where('is_popular', true);
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    /**
     * Get billing cycle label
     */
    public function getBillingCycleLabelAttribute(): string
    {
        return match($this->billing_cycle) {
            'monthly' => 'Per Bulan',
            '6_months' => 'Per 6 Bulan',
            'annually' => 'Per Tahun',
            '2_years' => 'Per 2 Tahun',
            '3_years' => 'Per 3 Tahun',
            default => ucfirst($this->billing_cycle),
        };
    }

    /**
     * Calculate monthly equivalent price
     */
    public function getMonthlyEquivalentAttribute(): float
    {
        return $this->price / $this->cycle_months;
    }

    /**
     * Get savings compared to monthly plan
     */
    public function getSavingsAttribute(): float
    {
        $monthlyPlan = static::where('billing_cycle', 'monthly')->first();
        if (!$monthlyPlan) {
            return 0;
        }

        $totalMonthlyPrice = $monthlyPlan->price * $this->cycle_months;
        return $totalMonthlyPrice - $this->price;
    }

    /**
     * Get formatted savings
     */
    public function getFormattedSavingsAttribute(): string
    {
        $savings = $this->savings;
        if ($savings <= 0) {
            return '';
        }
        
        return 'Hemat Rp ' . number_format($savings, 0, ',', '.');
    }

    /**
     * Get price after applying discount percentage
     */
    public function getDiscountedPriceAttribute(): float
    {
        $discount = (float) ($this->discount_percentage ?? 0);
        $price = (float) ($this->price ?? 0);
        if ($discount <= 0) {
            return $price;
        }
        $discounted = $price * (1 - ($discount / 100));
        // Keep two decimals as per casts
        return round($discounted, 2);
    }

    /**
     * Check if this is the most popular plan
     */
    public function getIsRecommendedAttribute(): bool
    {
        return $this->is_popular || $this->billing_cycle === 'annually';
    }
}