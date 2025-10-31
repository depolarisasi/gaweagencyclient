<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\TicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SupportTicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SupportTicket::with(['assignedUser', 'lastReplyUser'])
                              ->where('user_id', auth()->id());
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Category filter
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        
        $tickets = $query->orderBy('created_at', 'desc')->paginate(10);
        
        return view('client.tickets.index', compact('tickets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('client.tickets.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'priority' => 'required|in:high,medium,low',
            'category' => 'required|in:technical,billing,general,feature_request',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:pdf,png,jpg,jpeg,gif|max:10240',
        ]);
        
        // Generate unique ticket number
        $ticketNumber = $this->generateTicketNumber();

        // Sanitize rich text input to allow limited safe tags
        $sanitizedDescription = $this->sanitizeHtml($validated['description']);
        
        $ticket = SupportTicket::create([
            'ticket_number' => $ticketNumber,
            'user_id' => auth()->id(),
            'subject' => $validated['subject'],
            'description' => $sanitizedDescription,
            'priority' => $validated['priority'],
            'category' => $validated['category'],
            'status' => 'open',
        ]);

        // If attachments provided during creation, save them as an initial reply
        $uploaded = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if (!$file) { continue; }
                $path = $file->store('ticket_attachments/' . $ticket->id, 'public');
                $uploaded[] = [
                    'path' => $path,
                    'name' => $file->getClientOriginalName(),
                    'mime' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                ];
            }
        }

        if (!empty($uploaded)) {
            $ticket->addReply(
                $sanitizedDescription,
                auth()->id(),
                false,
                $uploaded
            );
        }
        
        // TODO: Send notification to admin/staff
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Support ticket created successfully',
                'ticket' => $ticket
            ]);
        }
        
        return redirect()->route('client.tickets.index')
                        ->with('success', 'Support ticket created successfully. We will respond as soon as possible.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SupportTicket $ticket)
    {
        // Ensure user can only view their own tickets
        if ($ticket->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this ticket.');
        }
        
        $ticket->load(['assignedUser', 'replies.user']);
        
        if (request()->expectsJson()) {
            return response()->json($ticket);
        }
        
        return view('client.tickets.show', compact('ticket'));
    }

    /**
     * Show the form for replying to a ticket (client side).
     */
    public function replyForm(SupportTicket $ticket)
    {
        // Ensure user can only view their own tickets
        if ($ticket->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this ticket.');
        }

        // Load minimal relations for context
        $ticket->load(['assignedUser']);
        return view('client.tickets.reply', compact('ticket'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SupportTicket $ticket)
    {
        // Ensure user can only edit their own tickets
        if ($ticket->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this ticket.');
        }
        
        // Only allow editing open tickets
        if ($ticket->status !== 'open') {
            return redirect()->route('client.tickets.show', $ticket)
                            ->with('error', 'Only open tickets can be edited.');
        }
        
        return view('client.tickets.edit', compact('ticket'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SupportTicket $ticket)
    {
        // Ensure user can only update their own tickets
        if ($ticket->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this ticket.');
        }
        
        // Only allow updating open tickets
        if ($ticket->status !== 'open') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only open tickets can be updated'
                ], 403);
            }
            
            return redirect()->route('client.tickets.show', $ticket)
                            ->with('error', 'Only open tickets can be updated.');
        }
        
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'priority' => 'required|in:high,medium,low',
            'category' => 'required|in:technical,billing,general,feature_request',
        ]);
        
        $ticket->update($validated);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Support ticket updated successfully',
                'ticket' => $ticket
            ]);
        }
        
        return redirect()->route('client.tickets.show', $ticket)
                        ->with('success', 'Support ticket updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SupportTicket $ticket)
    {
        // Ensure user can only delete their own tickets
        if ($ticket->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this ticket.');
        }
        
        // Only allow deleting open tickets with no replies
        if ($ticket->status !== 'open' || $ticket->replies()->exists()) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only open tickets with no replies can be deleted'
                ], 403);
            }
            
            return redirect()->route('client.tickets.index')
                            ->with('error', 'Only open tickets with no replies can be deleted.');
        }
        
        $ticket->delete();
        
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Support ticket deleted successfully'
            ]);
        }
        
        return redirect()->route('client.tickets.index')
                        ->with('success', 'Support ticket deleted successfully.');
    }
    
    /**
     * Add reply to ticket
     */
    public function reply(Request $request, SupportTicket $ticket)
    {
        // Ensure user can only reply to their own tickets
        if ($ticket->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this ticket.');
        }
        
        // Don't allow replies to closed tickets
        if ($ticket->status === 'closed') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot reply to closed tickets'
                ], 400);
            }
            
            return redirect()->route('client.tickets.show', $ticket)
                            ->with('error', 'Cannot reply to closed tickets.');
        }
        
        $request->validate([
            'message' => 'required|string|max:5000',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:pdf,png,jpg,jpeg,gif|max:10240',
        ]);

        // Handle file uploads
        $uploaded = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if (!$file) { continue; }
                $path = $file->store('ticket_attachments/' . $ticket->id, 'public');
                $uploaded[] = [
                    'path' => $path,
                    'name' => $file->getClientOriginalName(),
                    'mime' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                ];
            }
        }

        $replyMessage = $this->sanitizeHtml($request->message);
        $reply = $ticket->addReply(
            $replyMessage,
            auth()->id(),
            false, // Client replies are never internal
            !empty($uploaded) ? $uploaded : null
        );
        
        // If ticket was resolved, reopen it
        if ($ticket->status === 'resolved') {
            $ticket->update(['status' => 'in_progress']);
        }
        
        // TODO: Send notification to assigned staff
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Reply added successfully',
                'reply' => $reply
            ]);
        }
        
        return redirect()->route('client.tickets.show', $ticket)
                        ->with('success', 'Reply added successfully.');
    }

    /**
     * Allow only safe HTML tags for rich text fields.
     */
    private function sanitizeHtml(string $content): string
    {
        $allowed = '<p><br><strong><em><u><ol><ul><li><a><span><blockquote><code><pre><table><thead><tbody><tr><th><td>';
        $clean = strip_tags($content, $allowed);
        return trim($clean);
    }
    
    /**
     * Close ticket (client can close their own resolved tickets)
     */
    public function close(SupportTicket $ticket)
    {
        // Ensure user can only close their own tickets
        if ($ticket->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this ticket.');
        }
        
        // Only allow closing resolved tickets
        if ($ticket->status !== 'resolved') {
            return response()->json([
                'success' => false,
                'message' => 'Only resolved tickets can be closed by clients'
            ], 400);
        }
        
        $ticket->markAsClosed(auth()->id());
        
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Ticket closed successfully'
            ]);
        }
        
        return redirect()->route('client.tickets.show', $ticket)
                        ->with('success', 'Ticket closed successfully.');
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
}