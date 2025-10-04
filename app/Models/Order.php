<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'product_id',
        'amount',
        'setup_fee',
        'billing_cycle',
        'status',
        'next_due_date',
        'order_details',
        'notes',
        'activated_at',
        'suspended_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'setup_fee' => 'decimal:2',
            'next_due_date' => 'date',
            'order_details' => 'array',
            'activated_at' => 'datetime',
            'suspended_at' => 'datetime',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Helper methods
    public function getTotalAmountAttribute()
    {
        return $this->amount + $this->setup_fee;
    }

    public function getFormattedAmountAttribute()
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    public function getFormattedTotalAmountAttribute()
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isSuspended()
    {
        return $this->status === 'suspended';
    }

    public function calculateNextDueDate()
    {
        if (!$this->activated_at) {
            return null;
        }

        $baseDate = $this->next_due_date ? Carbon::parse($this->next_due_date) : Carbon::parse($this->activated_at);

        switch ($this->billing_cycle) {
            case 'monthly':
                return $baseDate->addMonth();
            case 'quarterly':
                return $baseDate->addMonths(3);
            case 'semi_annually':
                return $baseDate->addMonths(6);
            case 'annually':
                return $baseDate->addYear();
            default:
                return null;
        }
    }
}
