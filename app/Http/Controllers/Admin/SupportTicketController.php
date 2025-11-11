<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Models\User;
use App\Notifications\SupportTicketCreatedNotification;
use App\Notifications\SupportTicketRepliedNotification;
use App\Notifications\SupportTicketStatusUpdatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SupportTicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SupportTicket::with(['user', 'assignedUser', 'lastReplyUser']);
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Priority filter
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        
        // Category filter
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        
        // Assigned to filter
        if ($request->filled('assigned_to')) {
            if ($request->assigned_to === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $request->assigned_to);
            }
        }
        
        $tickets = $query->orderBy('created_at', 'desc')->paginate(15);

        // Preload staff list for filters and assignment (avoid querying in Blade)
        $staff = User::where('role', 'staff')
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.tickets.index', compact('tickets', 'staff'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clients = User::where('role', 'client')->where('status', 'active')->get();
        $staff = User::where('role', 'staff')->where('status', 'active')->get();
        
        return view('admin.tickets.create', compact('clients', 'staff'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'assigned_to' => 'nullable|exists:users,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'priority' => 'required|in:high,medium,low',
            'category' => 'required|in:technical,billing,general,feature_request',
        ]);
        
        // Generate unique ticket number
        $ticketNumber = $this->generateTicketNumber();
        
        // Sanitize rich text description (align with client sanitization)
        $sanitizedDescription = $this->sanitizeHtml($validated['description']);

        $ticket = SupportTicket::create([
            'ticket_number' => $ticketNumber,
            'user_id' => $validated['user_id'],
            'assigned_to' => $validated['assigned_to'],
            'subject' => $validated['subject'],
            'description' => $sanitizedDescription,
            'priority' => $validated['priority'],
            'category' => $validated['category'],
            'status' => 'open',
        ]);
        
        // Notify client about ticket creation
        try {
            $ticket->loadMissing('user');
            if ($ticket->user) {
                $ticket->user->notify(new SupportTicketCreatedNotification($ticket));
            }
        } catch (\Throwable $e) {
            Log::error('Failed to send SupportTicketCreatedNotification (admin create)', [
                'ticket_id' => $ticket->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Support ticket created successfully',
                'ticket' => $ticket
            ]);
        }
        
        return redirect()->route('admin.tickets.index')
                        ->with('success', 'Support ticket created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SupportTicket $ticket)
    {
        $ticket->load(['user', 'assignedUser', 'replies.user']);
        
        if (request()->expectsJson()) {
            return response()->json($ticket);
        }
        
        return view('admin.tickets.show', compact('ticket'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SupportTicket $ticket)
    {
        $clients = User::where('role', 'client')->where('status', 'active')->get();
        $staff = User::where('role', 'staff')->where('status', 'active')->get();
        
        return view('admin.tickets.edit', compact('ticket', 'clients', 'staff'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'assigned_to' => 'nullable|exists:users,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'priority' => 'required|in:high,medium,low',
            'category' => 'required|in:technical,billing,general,feature_request',
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);
        // Sanitize description before update
        $validated['description'] = $this->sanitizeHtml($validated['description']);

        $ticket->update($validated);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Support ticket updated successfully',
                'ticket' => $ticket
            ]);
        }
        
        return redirect()->route('admin.tickets.index')
                        ->with('success', 'Support ticket updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SupportTicket $ticket)
    {
        // Delete all replies first
        $ticket->replies()->delete();
        
        // Delete the ticket
        $ticket->delete();
        
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Support ticket deleted successfully'
            ]);
        }
        
        return redirect()->route('admin.tickets.index')
                        ->with('success', 'Support ticket deleted successfully.');
    }
    
    /**
     * Assign ticket to staff member
     */
    public function assign(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id'
        ]);
        
        // Verify the user is staff
        $staff = User::findOrFail($request->assigned_to);
        if (!in_array($staff->role, ['admin', 'staff'])) {
            return response()->json([
                'success' => false,
                'message' => 'Selected user is not a staff member'
            ], 400);
        }
        
        $ticket->update([
            'assigned_to' => $request->assigned_to,
            'status' => 'in_progress' // Auto-mark as in progress when assigned
        ]);
        
        // Notify client about status update to in_progress
        try {
            $ticket->loadMissing('user');
            if ($ticket->user) {
                $ticket->user->notify(new SupportTicketStatusUpdatedNotification($ticket, 'open', 'in_progress'));
            }
        } catch (\Throwable $e) {
            Log::error('Failed to send SupportTicketStatusUpdatedNotification (assign)', [
                'ticket_id' => $ticket->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Ticket assigned successfully'
        ]);
    }
    
    /**
     * Mark ticket as in progress
     */
    public function markInProgress(SupportTicket $ticket)
    {
        if ($ticket->status === 'closed') {
            return response()->json([
                'success' => false,
                'message' => 'Closed tickets cannot be reopened'
            ], 400);
        }
        
        $old = $ticket->status;
        $ticket->update(['status' => 'in_progress']);

        // Notify client about status update
        try {
            $ticket->loadMissing('user');
            if ($ticket->user) {
                $ticket->user->notify(new SupportTicketStatusUpdatedNotification($ticket, $old, 'in_progress'));
            }
        } catch (\Throwable $e) {
            Log::error('Failed to send SupportTicketStatusUpdatedNotification (markInProgress)', [
                'ticket_id' => $ticket->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Ticket marked as in progress'
        ]);
    }
    
    /**
     * Mark ticket as resolved
     */
    public function resolve(SupportTicket $ticket)
    {
        if ($ticket->status === 'closed') {
            return response()->json([
                'success' => false,
                'message' => 'Closed tickets cannot be resolved'
            ], 400);
        }
        
        $old = $ticket->status;
        $ticket->markAsResolved(auth()->id());
        
        // Notify client about status update
        try {
            $ticket->loadMissing('user');
            if ($ticket->user) {
                $ticket->user->notify(new SupportTicketStatusUpdatedNotification($ticket, $old, 'resolved'));
            }
        } catch (\Throwable $e) {
            Log::error('Failed to send SupportTicketStatusUpdatedNotification (resolve)', [
                'ticket_id' => $ticket->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Ticket marked as resolved'
        ]);
    }
    
    /**
     * Close ticket
     */
    public function close(SupportTicket $ticket)
    {
        $old = $ticket->status;
        $ticket->markAsClosed(auth()->id());
        
        // Notify client about status update
        try {
            $ticket->loadMissing('user');
            if ($ticket->user) {
                $ticket->user->notify(new SupportTicketStatusUpdatedNotification($ticket, $old, 'closed'));
            }
        } catch (\Throwable $e) {
            Log::error('Failed to send SupportTicketStatusUpdatedNotification (close)', [
                'ticket_id' => $ticket->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Ticket closed successfully'
        ]);
    }

    /**
     * Reopen a closed ticket
     */
    public function reopen(SupportTicket $ticket)
    {
        if ($ticket->status !== 'closed') {
            return response()->json([
                'success' => false,
                'message' => 'Only closed tickets can be reopened'
            ], 400);
        }

        $old = $ticket->status;
        $ticket->update([
            'status' => 'open',
            'closed_at' => null
        ]);

        // Notify client about status update
        try {
            $ticket->loadMissing('user');
            if ($ticket->user) {
                $ticket->user->notify(new SupportTicketStatusUpdatedNotification($ticket, $old, 'open'));
            }
        } catch (\Throwable $e) {
            Log::error('Failed to send SupportTicketStatusUpdatedNotification (reopen)', [
                'ticket_id' => $ticket->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ticket reopened successfully'
        ]);
    }
    
    /**
     * Add reply to ticket
     */
    public function reply(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'message' => 'required|string|max:5000',
            'is_internal' => 'boolean',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:pdf,png,jpg,jpeg,gif|max:10240'
        ]);
        
        $isInternal = $request->boolean('is_internal');
        
        // Sanitize message (align with client sanitization)
        $sanitizedMessage = $this->sanitizeHtml($request->message);

        // Handle attachments upload (align with client upload path and disk)
        $uploaded = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if (!$file->isValid()) {
                    Log::warning('Skipping invalid attachment on admin reply', [
                        'ticket_id' => $ticket->id ?? null,
                        'name' => $file->getClientOriginalName(),
                    ]);
                    continue;
                }
                try {
                    $path = $file->store('ticket_attachments/' . $ticket->id, 'public');
                    if (!$path) {
                        Log::error('Failed to store attachment (empty path) on admin reply', [
                            'ticket_id' => $ticket->id ?? null,
                            'name' => $file->getClientOriginalName(),
                        ]);
                        continue;
                    }
                    $uploaded[] = [
                        'path' => $path,
                        'name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'mime' => $file->getClientMimeType(),
                    ];
                } catch (\Throwable $e) {
                    Log::error('Exception while storing attachment on admin reply', [
                        'ticket_id' => $ticket->id ?? null,
                        'name' => $file->getClientOriginalName(),
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        $reply = $ticket->addReply(
            $sanitizedMessage,
            auth()->id(),
            $isInternal,
            !empty($uploaded) ? $uploaded : null
        );
        
        // If not internal, update ticket status to in_progress if it was open
        if (!$isInternal && $ticket->status === 'open') {
            $old = $ticket->status;
            $ticket->update(['status' => 'in_progress']);
            // Notify client about status update
            try {
                $ticket->loadMissing('user');
                if ($ticket->user) {
                    $ticket->user->notify(new SupportTicketStatusUpdatedNotification($ticket, $old, 'in_progress'));
                }
            } catch (\Throwable $e) {
                Log::error('Failed to send SupportTicketStatusUpdatedNotification (reply -> in_progress)', [
                    'ticket_id' => $ticket->id ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        // Notify client about reply if not internal
        if (!$isInternal) {
            try {
                $ticket->loadMissing('user');
                if ($ticket->user) {
                    $ticket->user->notify(new SupportTicketRepliedNotification($ticket, $reply));
                }
            } catch (\Throwable $e) {
                Log::error('Failed to send SupportTicketRepliedNotification (admin reply)', [
                    'ticket_id' => $ticket->id ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Reply added successfully',
                'reply' => $reply
            ]);
        }
        
        return redirect()->route('admin.tickets.show', $ticket)
                        ->with('success', 'Reply added successfully.');
    }

    /**
     * Allow only safe HTML tags for rich text fields (same as client).
     */
    private function sanitizeHtml(string $content): string
    {
        $allowed = '<div><p><br><strong><em><u><ol><ul><li><a><span><blockquote><code><pre><table><thead><tbody><tr><th><td>';
        $clean = strip_tags($content, $allowed);
        return trim($clean);
    }
    
    /**
     * Get ticket statistics
     */
    public function statistics()
    {
        $stats = [
            'total_tickets' => SupportTicket::count(),
            'open_tickets' => SupportTicket::whereIn('status', ['open', 'in_progress'])->count(),
            'resolved_today' => SupportTicket::where('status', 'resolved')
                                            ->whereDate('resolved_at', today())
                                            ->count(),
            'unassigned_tickets' => SupportTicket::whereNull('assigned_to')
                                                 ->whereIn('status', ['open', 'in_progress'])
                                                 ->count(),
            'high_priority_open' => SupportTicket::where('priority', 'high')
                                                 ->whereIn('status', ['open', 'in_progress'])
                                                 ->count(),
            'avg_resolution_time' => $this->getAverageResolutionTime(),
        ];
        
        return response()->json($stats);
    }
    
    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:assign,in_progress,resolve,close,delete',
            'ticket_ids' => 'required|array',
            'ticket_ids.*' => 'exists:support_tickets,id',
            'assigned_to' => 'required_if:action,assign|exists:users,id'
        ]);
        
        $ticketIds = $request->ticket_ids;
        $action = $request->action;
        
        switch ($action) {
            case 'assign':
                SupportTicket::whereIn('id', $ticketIds)
                            ->update([
                                'assigned_to' => $request->assigned_to,
                                'status' => 'in_progress'
                            ]);
                $message = 'Tickets assigned successfully';
                break;
                
            case 'in_progress':
                SupportTicket::whereIn('id', $ticketIds)
                            ->where('status', '!=', 'closed')
                            ->update(['status' => 'in_progress']);
                $message = 'Tickets marked as in progress';
                break;
                
            case 'resolve':
                SupportTicket::whereIn('id', $ticketIds)
                            ->where('status', '!=', 'closed')
                            ->update([
                                'status' => 'resolved',
                                'resolved_at' => now()
                            ]);
                $message = 'Tickets marked as resolved';
                break;
                
            case 'close':
                SupportTicket::whereIn('id', $ticketIds)
                            ->update([
                                'status' => 'closed',
                                'closed_at' => now()
                            ]);
                $message = 'Tickets closed successfully';
                break;
                
            case 'delete':
                // Delete replies first
                TicketReply::whereIn('support_ticket_id', $ticketIds)->delete();
                // Delete tickets
                SupportTicket::whereIn('id', $ticketIds)->delete();
                $message = 'Tickets deleted successfully';
                break;
        }
        
        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }
    
    /**
     * Generate unique ticket number
     */
    private function generateTicketNumber()
    {
        $prefix = 'TKT';
        $date = now()->format('Ymd');
        
        // Get the last ticket number for today
        $lastTicket = SupportTicket::where('ticket_number', 'like', $prefix . $date . '%')
                                  ->orderBy('ticket_number', 'desc')
                                  ->first();
        
        if ($lastTicket) {
            // Extract the sequence number and increment
            $lastSequence = (int) substr($lastTicket->ticket_number, -4);
            $sequence = str_pad($lastSequence + 1, 4, '0', STR_PAD_LEFT);
        } else {
            // First ticket of the day
            $sequence = '0001';
        }
        
        return $prefix . $date . $sequence;
    }
    
    /**
     * Calculate average resolution time in hours
     */
    private function getAverageResolutionTime()
    {
        $resolvedTickets = SupportTicket::where('status', 'resolved')
                                       ->whereNotNull('resolved_at')
                                       ->get();
        
        if ($resolvedTickets->isEmpty()) {
            return 0;
        }
        
        $totalHours = 0;
        foreach ($resolvedTickets as $ticket) {
            $totalHours += $ticket->created_at->diffInHours($ticket->resolved_at);
        }
        
        return round($totalHours / $resolvedTickets->count(), 2);
    }
}