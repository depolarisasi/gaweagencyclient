<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class SupportTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'user_id',
        'assigned_to',
        'subject',
        'description',
        'priority',
        'status',
        'category',
        'last_reply_at',
        'last_reply_by',
        'resolved_at',
        'closed_at',
        // Optional legacy/admin fields
        'internal_notes',
    ];

    protected function casts(): array
    {
        return [
            'last_reply_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    // Relationships
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

    public function lastReplyUser()
    {
        return $this->belongsTo(User::class, 'last_reply_by');
    }

    public function replies()
    {
        return $this->hasMany(TicketReply::class);
    }

    public function publicReplies()
    {
        return $this->hasMany(TicketReply::class)->where('is_internal', false);
    }

    public function internalReplies()
    {
        return $this->hasMany(TicketReply::class)->where('is_internal', true);
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'in_progress']);
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }

    public function scopeAssigned($query)
    {
        return $query->whereNotNull('assigned_to');
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Helper methods
    public function isOpen()
    {
        return in_array($this->status, ['open', 'in_progress']);
    }

    public function isClosed()
    {
        return $this->status === 'closed';
    }

    public function isResolved()
    {
        return $this->status === 'resolved';
    }

    public function isAssigned()
    {
        return !is_null($this->assigned_to);
    }

    public function getStatusBadgeClassAttribute()
    {
        switch ($this->status) {
            case 'open':
                return 'badge-danger';
            case 'in_progress':
                return 'badge-warning';
            case 'resolved':
                return 'badge-info';
            case 'closed':
                return 'badge-success';
            default:
                return 'badge-light';
        }
    }

    public function getPriorityBadgeClassAttribute()
    {
        switch ($this->priority) {
            case 'high':
                return 'badge-danger';
            case 'medium':
                return 'badge-warning';
            case 'low':
                return 'badge-success';
            default:
                return 'badge-light';
        }
    }

    public function getStatusTextAttribute()
    {
        $statuses = [
            'open' => 'Terbuka',
            'in_progress' => 'Sedang Diproses',
            'resolved' => 'Terselesaikan',
            'closed' => 'Ditutup',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    public function getPriorityTextAttribute()
    {
        $priorities = [
            'high' => 'Tinggi',
            'medium' => 'Sedang',
            'low' => 'Rendah',
        ];

        return $priorities[$this->priority] ?? $this->priority;
    }

    public function getCategoryTextAttribute()
    {
        $categories = [
            'technical' => 'Teknis',
            'billing' => 'Tagihan',
            'general' => 'Umum',
            'feature_request' => 'Permintaan Fitur',
        ];

        return $categories[$this->category] ?? $this->category;
    }

    public function getTimeSinceCreatedAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getTimeSinceLastReplyAttribute()
    {
        if (!$this->last_reply_at) {
            return 'Belum ada balasan';
        }

        return $this->last_reply_at->diffForHumans();
    }

    public function addReply($message, $userId, $isInternal = false, $attachments = null)
    {
        $reply = $this->replies()->create([
            'user_id' => $userId,
            'message' => $message,
            'is_internal' => $isInternal,
            'attachments' => $attachments,
        ]);

        $this->update([
            'last_reply_at' => now(),
            'last_reply_by' => $userId,
        ]);

        return $reply;
    }

    public function markAsResolved($userId = null)
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);
    }

    public function markAsClosed($userId = null)
    {
        $this->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);
    }
}
