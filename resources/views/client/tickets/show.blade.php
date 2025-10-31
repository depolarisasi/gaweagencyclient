@extends('layouts.client')

@section('title', 'Ticket #' . $ticket->ticket_number)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <a href="{{ route('client.tickets.index') }}" class="btn btn-outline btn-sm">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
            <h1 class="text-2xl font-bold text-gray-900">#{{ $ticket->ticket_number }} - {{ $ticket->subject }}</h1>
        </div>
        <div class="flex items-center space-x-2">
            @if($ticket->status === 'open')
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    <i class="fas fa-circle mr-1"></i>Open
                </span>
            @elseif($ticket->status === 'in_progress')
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    <i class="fas fa-clock mr-1"></i>In Progress
                </span>
            @elseif($ticket->status === 'resolved')
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    <i class="fas fa-check mr-1"></i>Resolved
                </span>
            @else
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                    <i class="fas fa-times mr-1"></i>Closed
                </span>
            @endif

            @if($ticket->status !== 'closed')
                <a href="{{ route('client.tickets.reply.form', $ticket) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-reply mr-2"></i>Reply
                </a>
            @endif

            @if($ticket->status === 'resolved')
                <form method="POST" action="{{ route('client.tickets.close', $ticket) }}" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-sm">
                        <i class="fas fa-door-closed mr-2"></i>Close Ticket
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Ticket Details -->
    <div class="bg-white rounded-md shadow-sm border border-gray-300 p-6">
        <div class="flex flex-wrap items-center gap-6 text-sm text-gray-600 mb-4">
            <div class="flex items-center space-x-1">
                <i class="fas fa-calendar"></i>
                <span>Created {{ $ticket->created_at->format('M d, Y') }}</span>
            </div>
            <div class="flex items-center space-x-1">
                <i class="fas fa-layer-group"></i>
                <span>Priority: {{ ucfirst($ticket->priority) }}</span>
            </div>
            <div class="flex items-center space-x-1">
                <i class="fas fa-tags"></i>
                <span>Category: {{ ucfirst(str_replace('_', ' ', $ticket->category)) }}</span>
            </div>
            @if($ticket->assignedUser)
            <div class="flex items-center space-x-1">
                <i class="fas fa-user"></i>
                <span>Assigned to {{ $ticket->assignedUser->name }}</span>
            </div>
            @endif
            @if($ticket->last_reply_at)
            <div class="flex items-center space-x-1">
                <i class="fas fa-reply"></i>
                <span>Last reply {{ $ticket->last_reply_at->diffForHumans() }}</span>
            </div>
            @endif
        </div>
        <div class="prose max-w-none">
            <p class="text-gray-800 whitespace-pre-line">{{ $ticket->description }}</p>
        </div>
    </div>

    <!-- Replies Thread -->
    <div class="space-y-4">
        <h2 class="text-lg font-semibold text-gray-900">Conversation</h2>
        @php use Illuminate\Support\Facades\Storage; @endphp
        @forelse($ticket->replies->sortBy('created_at') as $reply)
            <div class="bg-white rounded-md shadow-sm border {{ $reply->user_id === auth()->id() ? 'border-blue-300' : 'border-gray-300' }} p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-user-circle text-gray-500"></i>
                        <span class="font-medium text-gray-900">{{ $reply->user->name }}</span>
                        @if($reply->user_id === auth()->id())
                            <span class="text-xs text-blue-600">(You)</span>
                        @endif
                    </div>
                    <div class="text-sm text-gray-500">{{ $reply->created_at->format('M d, Y H:i') }}</div>
                </div>
                <div class="text-gray-700 whitespace-pre-line">{{ $reply->message }}</div>
                @if($reply->attachments)
                    <div class="mt-3 border-t pt-3">
                        <div class="text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-paperclip mr-2"></i>Attachments
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @foreach($reply->attachments as $file)
                                <a href="{{ Storage::url($file['path']) }}" target="_blank" class="btn btn-xs btn-outline">
                                    <i class="fas fa-file mr-1"></i>{{ $file['name'] ?? basename($file['path']) }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-md border border-gray-300 p-6 text-center text-gray-600">
                No replies yet. Be the first to reply.
            </div>
        @endforelse
    </div>
</div>
@endsection