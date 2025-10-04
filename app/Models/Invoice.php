<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'invoice_number',
        'amount',
        'tax_amount',
        'total_amount',
        'status',
        'due_date',
        'paid_date',
        'payment_method',
        'description',
        'tripay_reference',
        'tripay_response',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'due_date' => 'date',
            'paid_date' => 'date',
            'tripay_response' => 'array',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Scopes
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['draft', 'sent', 'overdue']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
                    ->where('due_date', '<', now());
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Helper methods
    public function getFormattedAmountAttribute()
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    public function isPaid()
    {
        return $this->status === 'paid';
    }

    public function isPending()
    {
        return $this->status === 'sent';
    }

    public function isOverdue()
    {
        return $this->status === 'overdue';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function getDaysUntilDueAttribute()
    {
        if ($this->isPaid()) {
            return null;
        }

        return now()->diffInDays($this->due_date, false);
    }

    public function getStatusBadgeClassAttribute()
    {
        switch ($this->status) {
            case 'paid':
                return 'badge-success';
            case 'sent':
                return 'badge-warning';
            case 'overdue':
                return 'badge-danger';
            case 'cancelled':
                return 'badge-secondary';
            default:
                return 'badge-light';
        }
    }

    public function getStatusTextAttribute()
    {
        $statuses = [
            'draft' => 'Draft',
            'sent' => 'Menunggu Pembayaran',
            'paid' => 'Lunas',
            'overdue' => 'Kedaluwarsa',
            'cancelled' => 'Dibatalkan',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    // Tripay related methods
    public function hasTripayReference()
    {
        return !empty($this->tripay_reference);
    }

    public function getTripayStatusTextAttribute()
    {
        $statuses = [
            'UNPAID' => 'Belum Dibayar',
            'PAID' => 'Sudah Dibayar',
            'EXPIRED' => 'Kedaluwarsa',
            'FAILED' => 'Gagal',
        ];

        return $statuses[$this->tripay_status] ?? $this->tripay_status;
    }

    public function isExpired()
    {
        return $this->tripay_expired_time && now() > $this->tripay_expired_time;
    }
}
