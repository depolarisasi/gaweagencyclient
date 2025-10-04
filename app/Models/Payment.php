<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'user_id',
        'reference',
        'payment_method',
        'amount',
        'status',
        'expired_at',
        'paid_at',
        'callback_data',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expired_at' => 'datetime',
            'paid_at' => 'datetime',
            'callback_data' => 'array',
        ];
    }

    // Relationships
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeByMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    // Helper methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isFailed()
    {
        return $this->status === 'failed';
    }

    public function isExpired()
    {
        return $this->status === 'expired';
    }

    public function getStatusBadgeClassAttribute()
    {
        switch ($this->status) {
            case 'pending':
                return 'badge-warning';
            case 'completed':
                return 'badge-success';
            case 'failed':
                return 'badge-danger';
            case 'expired':
                return 'badge-secondary';
            default:
                return 'badge-light';
        }
    }

    public function getStatusTextAttribute()
    {
        $statuses = [
            'pending' => 'Menunggu Pembayaran',
            'completed' => 'Berhasil',
            'failed' => 'Gagal',
            'expired' => 'Kedaluwarsa',
            'cancelled' => 'Dibatalkan',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    public function getPaymentMethodNameAttribute()
    {
        $methods = [
            'BRIVA' => 'BRI Virtual Account',
            'BCAVA' => 'BCA Virtual Account',
            'BNIVA' => 'BNI Virtual Account',
            'MANDIRIVA' => 'Mandiri Virtual Account',
            'PERMATAVA' => 'Permata Virtual Account',
            'ALFAMART' => 'Alfamart',
            'INDOMARET' => 'Indomaret',
        ];

        return $methods[$this->payment_method] ?? $this->payment_method;
    }

    public function markAsCompleted($callbackData = null)
    {
        $this->update([
            'status' => 'completed',
            'paid_at' => now(),
            'callback_data' => $callbackData,
        ]);
    }

    public function markAsFailed($callbackData = null)
    {
        $this->update([
            'status' => 'failed',
            'callback_data' => $callbackData,
        ]);
    }

    public function markAsExpired($callbackData = null)
    {
        $this->update([
            'status' => 'expired',
            'callback_data' => $callbackData,
        ]);
    }
}