<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TicketReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'support_ticket_id',
        'user_id',
        'message',
        'attachments',
        'is_internal',
    ];

    protected function casts(): array
    {
        return [
            'attachments' => 'array',
            'is_internal' => 'boolean',
        ];
    }

    // Relationships
    public function supportTicket()
    {
        return $this->belongsTo(SupportTicket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopePublic($query)
    {
        return $query->where('is_internal', false);
    }

    public function scopeInternal($query)
    {
        return $query->where('is_internal', true);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Helper methods
    public function isInternal()
    {
        return $this->is_internal;
    }

    public function isPublic()
    {
        return !$this->is_internal;
    }

    public function hasAttachments()
    {
        return !empty($this->attachments);
    }

    public function getFormattedMessageAttribute()
    {
        return nl2br(e($this->message));
    }

    public function getTimeSinceCreatedAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getReplyTypeTextAttribute()
    {
        return $this->is_internal ? 'Internal' : 'Publik';
    }

    public function getReplyTypeBadgeClassAttribute()
    {
        return $this->is_internal ? 'badge-warning' : 'badge-primary';
    }
}
