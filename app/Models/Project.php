<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_name',
        'user_id',
        'order_id',
        'template_id',
        'status',
        'assigned_to',
        'description',
        'website_url',
        'admin_url',
        'admin_username',
        'admin_password',
        'notes',
        'start_date',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'additional_access' => 'array',
            'suspended_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
    
    public function assignedStaff()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
    
    public function template()
    {
        return $this->belongsTo(Template::class);
    }
    
    public function overdueInvoice()
    {
        return $this->belongsTo(Invoice::class, 'overdue_invoice_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'completed')
                    ->where('due_date', '<', now());
    }

    // Helper methods
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isOverdue()
    {
        return $this->status !== 'completed' && $this->due_date < now();
    }

    public function getDaysUntilDueAttribute()
    {
        if ($this->isCompleted()) {
            return null;
        }

        return now()->diffInDays($this->due_date, false);
    }

    public function getStatusBadgeClassAttribute()
    {
        switch ($this->status) {
            case 'completed':
                return 'badge-success';
            case 'active':
                return $this->isOverdue() ? 'badge-danger' : 'badge-primary';
            case 'pending':
                return 'badge-warning';
            case 'on_hold':
                return 'badge-secondary';
            default:
                return 'badge-light';
        }
    }

    public function getStatusTextAttribute()
    {
        $statuses = [
            'pending' => 'Menunggu',
            'active' => 'Aktif',
            'completed' => 'Selesai',
            'on_hold' => 'Ditunda',
            'cancelled' => 'Dibatalkan',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    public function getProgressBarClassAttribute()
    {
        if ($this->progress_percentage >= 100) {
            return 'bg-success';
        } elseif ($this->progress_percentage >= 75) {
            return 'bg-info';
        } elseif ($this->progress_percentage >= 50) {
            return 'bg-warning';
        } else {
            return 'bg-danger';
        }
    }

    public function updateProgress($percentage)
    {
        $this->progress_percentage = min(100, max(0, $percentage));
        
        if ($this->progress_percentage >= 100 && $this->status !== 'completed') {
            $this->status = 'completed';
            $this->completed_date = now();
        }
        
        $this->save();
    }
}
