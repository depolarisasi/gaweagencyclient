<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'order_id',
        'order_addon_id',
        'product_addon_id',
        'item_type',
        'description',
        'amount',
        'quantity',
        'billing_type',
        'billing_cycle',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderAddon(): BelongsTo
    {
        return $this->belongsTo(OrderAddon::class);
    }

    public function productAddon(): BelongsTo
    {
        return $this->belongsTo(ProductAddon::class);
    }
}