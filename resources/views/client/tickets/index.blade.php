@extends('layouts.app')

@section('title', 'Support Tickets - Client')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-green-50 to-blue-50">
    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-xl border-r border-gray-200">
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-8">
                    <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-blue-500 rounded-xl flex items-center justify-center">
                        <i class="fas fa-user text-white text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800">Client Portal</h3>
                        <p class="text-xs text-gray-500">Welcome back!</p>
                    </div>
                </div>
                
                <nav class="space-y-2">
                    <a href="{{ route('client.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-green-50 hover:text-green-700 rounded-xl transition-all duration-200">
                        <i class="fas fa-chart-pie w-5"></i>
                        <span class="font-medium">Dashboard</span>
                    </a>
                    
                    <div class="pt-4">
                        <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">My Services</p>
                        
                        <a href="{{ route('client.projects') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-green-50 hover:text-green-700 rounded-xl transition-all duration-200">
                            <i class="fas fa-project-diagram w-5"></i>
                            <span>My Projects</span>
                        </a>
                        
                        <a href="{{ route('client.orders') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-green-50 hover:text-green-700 rounded-xl transition-all duration-200">
                            <i class="fas fa-shopping-cart w-5"></i>
                            <span>My Orders</span>
                        </a>
                        
                        <a href="{{ route('client.invoices') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-green-50 hover:text-green-700 rounded-xl transition-all duration-200">
                            <i class="fas fa-file-invoice w-5"></i>
                            <span>Invoices</span>
                        </a>
                    </div>
                    
                    <div class="pt-4">
                        <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Support</p>
                        
                        <a href="{{ route('client.tickets.index') }}" class="flex items-center space-x-3 px-4 py-3 bg-green-100 text-green-700 rounded-xl border border-green-200">
                            <i class="fas fa-headset w-5"></i>
                            <span>Support Tickets</span>
                        </a>
                    </div>
                </nav>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 p-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Support Tickets</h1>
                    <p class="text-gray-600 mt-1">Get help from our support team</p>
                </div>
                <div class="flex space-x-3">
                    <button class="btn btn-primary btn-sm" onclick="openCreateModal()">
                        <i class="fas fa-plus mr-2"></i>Create Ticket
                    </button>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">My Tickets</p>
                            <p class="text-3xl font-bold text-gray-900">{{ $tickets->total() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-ticket-alt text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Open Tickets</p>
                            <p class="text-3xl font-bold text-gray-900">{{ \App\Models\SupportTicket::where('user_id', auth()->id())->whereIn('status', ['open', 'in_progress'])->count() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clock text-orange-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Resolved</p>
                            <p class="text-3xl font-bold text-gray-900">{{ \App\Models\SupportTicket::where('user_id', auth()->id())->where('status', 'resolved')->count() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
                <form method="GET" action="{{ route('client.tickets.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tickets..." class="input input-bordered w-full">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="select select-bordered w-full">
                            <option value="">All Status</option>
                            <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <select name="category" class="select select-bordered w-full">
                            <option value="">All Categories</option>
                            <option value="technical" {{ request('category') == 'technical' ? 'selected' : '' }}>Technical</option>
                            <option value="billing" {{ request('category') == 'billing' ? 'selected' : '' }}>Billing</option>
                            <option value="general" {{ request('category') == 'general' ? 'selected' : '' }}>General</option>
                            <option value="feature_request" {{ request('category') == 'feature_request' ? 'selected' : '' }}>Feature Request</option>
                        </select>
                    </div>
                    
                    <div class="flex items-end space-x-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                        <a href="{{ route('client.tickets.index') }}" class="btn btn-outline">
                            <i class="fas fa-times mr-2"></i>Clear
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Tickets List -->
            <div class="space-y-4">
                @forelse($tickets as $ticket)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3 mb-2">
                                <h3 class="text-lg font-semibold text-gray-900">#{{ $ticket->ticket_number }}</h3>
                                
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
                                
                                @if($ticket->priority === 'high')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>High Priority
                                    </span>
                                @endif
                            </div>
                            
                            <h4 class="text-xl font-medium text-gray-900 mb-2">{{ $ticket->subject }}</h4>
                            <p class="text-gray-600 mb-4">{{ Str::limit($ticket->description, 150) }}</p>
                            
                            <div class="flex items-center space-x-6 text-sm text-gray-500">
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-calendar"></i>
                                    <span>Created {{ $ticket->created_at->format('M d, Y') }}</span>
                                </div>
                                
                                @if($ticket->category === 'technical')
                                    <div class="flex items-center space-x-1">
                                        <i class="fas fa-cog text-blue-500"></i>
                                        <span>Technical</span>
                                    </div>
                                @elseif($ticket->category === 'billing')
                                    <div class="flex items-center space-x-1">
                                        <i class="fas fa-dollar-sign text-green-500"></i>
                                        <span>Billing</span>
                                    </div>
                                @elseif($ticket->category === 'feature_request')
                                    <div class="flex items-center space-x-1">
                                        <i class="fas fa-lightbulb text-purple-500"></i>
                                        <span>Feature Request</span>
                                    </div>
                                @else
                                    <div class="flex items-center space-x-1">
                                        <i class="fas fa-comment text-gray-500"></i>
                                        <span>General</span>
                                    </div>
                                @endif
                                
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
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <button onclick="viewTicket({{ $ticket->id }})" class="btn btn-sm btn-outline">
                                <i class="fas fa-eye mr-2"></i>View
                            </button>
                            @if($ticket->status !== 'closed')
                            <button onclick="replyTicket({{ $ticket->id }})" class="btn btn-sm btn-primary">
                                <i class="fas fa-reply mr-2"></i>Reply
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                    <i class="fas fa-ticket-alt text-gray-300 text-6xl mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No Support Tickets</h3>
                    <p class="text-gray-600 mb-6">You haven't created any support tickets yet. Need help? Create your first ticket!</p>
                    <button onclick="openCreateModal()" class="btn btn-primary">
                        <i class="fas fa-plus mr-2"></i>Create Your First Ticket
                    </button>
                </div>
                @endforelse
            </div>
            
            @if($tickets->hasPages())
            <div class="mt-8">
                {{ $tickets->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Create Ticket Modal -->
<div id="ticketModal" class="modal">
    <div class="modal-box w-11/12 max-w-2xl">
        <h3 class="font-bold text-lg mb-4">Create Support Ticket</h3>
        
        <form id="ticketForm">
            <div class="space-y-4">
                <!-- Subject -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Subject *</span>
                    </label>
                    <input type="text" name="subject" class="input input-bordered" required placeholder="Brief description of your issue">
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Priority -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Priority *</span>
                        </label>
                        <select name="priority" class="select select-bordered" required>
                            <option value="low">Low - General inquiry</option>
                            <option value="medium" selected>Medium - Standard issue</option>
                            <option value="high">High - Urgent issue</option>
                        </select>
                    </div>
                    
                    <!-- Category -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Category *</span>
                        </label>
                        <select name="category" class="select select-bordered" required>
                            <option value="general">General Support</option>
                            <option value="technical">Technical Issue</option>
                            <option value="billing">Billing Question</option>
                            <option value="feature_request">Feature Request</option>
                        </select>
                    </div>
                </div>
                
                <!-- Description -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Description *</span>
                    </label>
                    <textarea name="description" class="textarea textarea-bordered" rows="5" required placeholder="Please provide detailed information about your issue or request..."></textarea>
                    <label class="label">
                        <span class="label-text-alt text-gray-500">The more details you provide, the faster we can help you!</span>
                    </label>
                </div>
            </div>
            
            <div class="modal-action">
                <button type="button" class="btn" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane mr-2"></i>Submit Ticket
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Reply Modal -->
<div id="replyModal" class="modal">
    <div class="modal-box w-11/12 max-w-2xl">
        <h3 class="font-bold text-lg mb-4">Reply to Ticket</h3>
        
        <form id="replyForm">
            <input type="hidden" id="replyTicketId" name="ticket_id">
            
            <div class="form-control mb-4">
                <label class="label">
                    <span class="label-text font-medium">Your Reply *</span>
                </label>
                <textarea name="message" class="textarea textarea-bordered" rows="5" required placeholder="Type your reply here..."></textarea>
            </div>
            
            <div class="modal-action">
                <button type="button" class="btn" onclick="closeReplyModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-reply mr-2"></i>Send Reply
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('ticketForm').reset();
    document.getElementById('ticketModal').classList.add('modal-open');
}

function closeModal() {
    document.getElementById('ticketModal').classList.remove('modal-open');
}

function viewTicket(ticketId) {
    window.location.href = `/client/tickets/${ticketId}`;
}

function replyTicket(ticketId) {
    document.getElementById('replyTicketId').value = ticketId;
    document.getElementById('replyForm').reset();
    document.getElementById('replyModal').classList.add('modal-open');
}

function closeReplyModal() {
    document.getElementById('replyModal').classList.remove('modal-open');
}

// Handle ticket form submission
document.getElementById('ticketForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/client/tickets', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal();
            location.reload();
        } else {
            alert('Error creating ticket. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error creating ticket. Please try again.');
    });
});

// Handle reply form submission
document.getElementById('replyForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const ticketId = document.getElementById('replyTicketId').value;
    
    fetch(`/client/tickets/${ticketId}/reply`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeReplyModal();
            location.reload();
        } else {
            alert('Error sending reply. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error sending reply. Please try again.');
    });
});
</script>
@endsection