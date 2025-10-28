<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderAddon extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_addon_id',
        'price',
        'billing_cycle',
        'quantity',
        'addon_details',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'addon_details' => 'array',
    ];

    /**
     * Get the order that owns this addon
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product addon
     */
    public function productAddon(): BelongsTo
    {
        return $this->belongsTo(ProductAddon::class);
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    /**
     * Get total price (price * quantity)
     */
    public function getTotalPriceAttribute(): float
    {
        return $this->price * $this->quantity;
    }

    /**
     * Get formatted total price
     */
    public function getFormattedTotalPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->total_price, 0, ',', '.');
    }

    /**
     * Get billing cycle label
     */
    public function getBillingCycleLabelAttribute(): string
    {
        if (!$this->billing_cycle) {
            return 'One-time';
        }

        return match($this->billing_cycle) {
            'monthly' => 'Per Bulan',
            'quarterly' => 'Per 3 Bulan',
            'semi_annually' => 'Per 6 Bulan',
            'annually' => 'Per Tahun',
            default => ucfirst($this->billing_cycle),
        };
    }
}