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
        'status',
        'started_at',
        'next_due_date',
        'cancel_at_period_end',
        'cancelled_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'addon_details' => 'array',
        'started_at' => 'datetime',
        'next_due_date' => 'date',
        'cancel_at_period_end' => 'boolean',
        'cancelled_at' => 'datetime',
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
            return 'Sekali';
        }
        // Kebijakan baru: semua add-on ditagihkan bulanan
        return 'Per Bulan';
    }
}