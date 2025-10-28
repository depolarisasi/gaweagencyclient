<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartAddon extends Model
{
    protected $fillable = [
        'cart_id',
        'product_addon_id',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function productAddon(): BelongsTo
    {
        return $this->belongsTo(ProductAddon::class);
    }
}
