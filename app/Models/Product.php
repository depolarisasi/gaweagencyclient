<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'price',
        'billing_cycle',
        'features',
        'setup_time_days',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'features' => 'array',
            'is_active' => 'boolean',
            'setup_time_days' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    // Relationships
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Helper methods
    public function getFormattedPriceAttribute()
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    public function getBillingCycleTextAttribute()
    {
        $cycles = [
            'monthly' => 'Bulanan',
            'quarterly' => 'Triwulan',
            'semi_annually' => 'Semester',
            'annually' => 'Tahunan',
        ];

        return $cycles[$this->billing_cycle] ?? $this->billing_cycle;
    }
}
