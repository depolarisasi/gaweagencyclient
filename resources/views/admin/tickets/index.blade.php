@extends('layouts.app')

@section('title', 'Support Ticket Management - Admin')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg border-r border-gray-200">
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-8">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-crown text-white text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800">Admin Panel</h3>
                        <p class="text-xs text-gray-500">Management Center</p>
                    </div>
                </div>
                
                <nav class="space-y-2">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                        <i class="fas fa-chart-line w-5"></i>
                        <span class="font-medium">Dashboard</span>
                    </a>
                    
                    <div class="pt-4">
                        <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Management</p>
                        
                        <a href="{{ route('admin.users.index') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                            <i class="fas fa-users w-5"></i>
                            <span>User Management</span>
                        </a>
                        
                        <a href="#" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                            <i class="fas fa-box w-5"></i>
                            <span>Products</span>
                        </a>
                        
                        <a href="{{ route('admin.projects.index') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                            <i class="fas fa-project-diagram w-5"></i>
                            <span>Projects</span>
                        </a>
                        
                        <a href="{{ route('admin.invoices.index') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                            <i class="fas fa-file-invoice w-5"></i>
                            <span>Invoices</span>
                        </a>
                    </div>
                    
                    <div class="pt-4">
                        <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Support</p>
                        
                        <a href="{{ route('admin.tickets.index') }}" class="flex items-center space-x-3 px-4 py-3 bg-blue-50 text-blue-700 rounded-lg border border-blue-200">
                            <i class="fas fa-ticket-alt w-5"></i>
                            <span>Support Tickets</span>
                        </a>
                    </div>
                    
                    <div class="pt-4">
                        <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Settings</p>
                        
                        <a href="#" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                            <i class="fas fa-palette w-5"></i>
                            <span>Templates</span>
                        </a>
                        
                        <a href="#" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                            <i class="fas fa-credit-card w-5"></i>
                            <span>Payment Settings</span>
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
                    <h1 class="text-3xl font-bold text-gray-900">Support Ticket Management</h1>
                    <p class="text-gray-600 mt-1">Manage customer support tickets and responses</p>
                </div>
                <div class="flex space-x-3">
                    <button class="btn btn-outline btn-sm">
                        <i class="fas fa-download mr-2"></i>Export Tickets
                    </button>
                    <button class="btn btn-primary btn-sm" onclick="openCreateModal()">
                        <i class="fas fa-plus mr-2"></i>Create Ticket
                    </button>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Total Tickets</p>
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
                            <p class="text-3xl font-bold text-gray-900">{{ \App\Models\SupportTicket::whereIn('status', ['open', 'in_progress'])->count() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-exclamation-circle text-red-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Resolved Today</p>
                            <p class="text-3xl font-bold text-gray-900">{{ \App\Models\SupportTicket::where('status', 'resolved')->whereDate('resolved_at', today())->count() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Unassigned</p>
                            <p class="text-3xl font-bold text-gray-900">{{ \App\Models\SupportTicket::whereNull('assigned_to')->whereIn('status', ['open', 'in_progress'])->count() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-user-slash text-yellow-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
                <form method="GET" action="{{ route('admin.tickets.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Ticket number or subject..." class="input input-bordered w-full">
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
                        <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                        <select name="priority" class="select select-bordered w-full">
                            <option value="">All Priority</option>
                            <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                            <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
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
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Assigned To</label>
                        <select name="assigned_to" class="select select-bordered w-full">
                            <option value="">All Staff</option>
                            <option value="unassigned" {{ request('assigned_to') == 'unassigned' ? 'selected' : '' }}>Unassigned</option>
                            @foreach(\App\Models\User::where('role', 'staff')->where('status', 'active')->get() as $staff)
                                <option value="{{ $staff->id }}" {{ request('assigned_to') == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="flex items-end space-x-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                        <a href="{{ route('admin.tickets.index') }}" class="btn btn-outline">
                            <i class="fas fa-times mr-2"></i>Clear
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Tickets Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left font-semibold text-gray-900">Ticket</th>
                                <th class="text-left font-semibold text-gray-900">Client</th>
                                <th class="text-left font-semibold text-gray-900">Assigned To</th>
                                <th class="text-left font-semibold text-gray-900">Priority</th>
                                <th class="text-left font-semibold text-gray-900">Status</th>
                                <th class="text-left font-semibold text-gray-900">Last Reply</th>
                                <th class="text-center font-semibold text-gray-900">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tickets as $ticket)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td>
                                    <div>
                                        <p class="font-semibold text-gray-900">#{{ $ticket->ticket_number }}</p>
                                        <p class="text-sm text-gray-900 font-medium">{{ Str::limit($ticket->subject, 40) }}</p>
                                        <div class="flex items-center space-x-2 mt-1">
                                            @if($ticket->category === 'technical')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                    <i class="fas fa-cog mr-1"></i>Technical
                                                </span>
                                            @elseif($ticket->category === 'billing')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-dollar-sign mr-1"></i>Billing
                                                </span>
                                            @elseif($ticket->category === 'feature_request')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                                    <i class="fas fa-lightbulb mr-1"></i>Feature
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                    <i class="fas fa-comment mr-1"></i>General
                                                </span>
                                            @endif
                                            <span class="text-xs text-gray-500">{{ $ticket->created_at->format('M d, Y') }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg flex items-center justify-center">
                                            <span class="text-white font-semibold text-xs">{{ strtoupper(substr($ticket->user->name, 0, 2)) }}</span>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $ticket->user->name }}</p>
                                            <p class="text-sm text-gray-500">{{ $ticket->user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($ticket->assignedUser)
                                        <div class="flex items-center space-x-2">
                                            <div class="w-6 h-6 bg-orange-100 rounded-full flex items-center justify-center">
                                                <span class="text-orange-600 font-semibold text-xs">{{ strtoupper(substr($ticket->assignedUser->name, 0, 1)) }}</span>
                                            </div>
                                            <span class="text-sm text-gray-900">{{ $ticket->assignedUser->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-sm">Unassigned</span>
                                    @endif
                                </td>
                                <td>
                                    @if($ticket->priority === 'high')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>High
                                        </span>
                                    @elseif($ticket->priority === 'medium')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-minus mr-1"></i>Medium
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-arrow-down mr-1"></i>Low
                                        </span>
                                    @endif
                                </td>
                                <td>
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
                                </td>
                                <td>
                                    <div>
                                        @if($ticket->last_reply_at)
                                            <p class="text-sm text-gray-900">{{ $ticket->last_reply_at->diffForHumans() }}</p>
                                            @if($ticket->lastReplyUser)
                                                <p class="text-xs text-gray-500">by {{ $ticket->lastReplyUser->name }}</p>
                                            @endif
                                        @else
                                            <span class="text-gray-400 text-sm">No replies</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="flex items-center justify-center space-x-2">
                                        <button onclick="viewTicket({{ $ticket->id }})" class="btn btn-sm btn-outline" title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button onclick="replyTicket({{ $ticket->id }})" class="btn btn-sm btn-primary" title="Reply">
                                            <i class="fas fa-reply"></i>
                                        </button>
                                        <div class="dropdown dropdown-end">
                                            <button tabindex="0" class="btn btn-sm btn-ghost">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-52">
                                                @if(!$ticket->assignedUser)
                                                <li><a onclick="assignTicket({{ $ticket->id }})"><i class="fas fa-user-plus mr-2"></i>Assign Staff</a></li>
                                                @endif
                                                @if($ticket->status === 'open')
                                                <li><a onclick="markInProgress({{ $ticket->id }})"><i class="fas fa-play mr-2"></i>Mark In Progress</a></li>
                                                @endif
                                                @if($ticket->status !== 'resolved' && $ticket->status !== 'closed')
                                                <li><a onclick="markResolved({{ $ticket->id }})"><i class="fas fa-check mr-2"></i>Mark Resolved</a></li>
                                                @endif
                                                @if($ticket->status !== 'closed')
                                                <li><a onclick="closeTicket({{ $ticket->id }})"><i class="fas fa-times mr-2"></i>Close Ticket</a></li>
                                                @endif
                                                <li><a onclick="deleteTicket({{ $ticket->id }})" class="text-red-600"><i class="fas fa-trash mr-2"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-12">
                                    <i class="fas fa-ticket-alt text-gray-300 text-4xl mb-4"></i>
                                    <p class="text-gray-500 text-lg">No support tickets found</p>
                                    <p class="text-gray-400">Tickets will appear here when customers create them</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($tickets->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $tickets->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Create Ticket Modal -->
<div id="ticketModal" class="modal">
    <div class="modal-box w-11/12 max-w-3xl">
        <h3 class="font-bold text-lg mb-4" id="modalTitle">Create New Ticket</h3>
        
        <form id="ticketForm" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Client -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Client *</span>
                    </label>
                    <select name="user_id" class="select select-bordered focus:select-primary" required>
                        <option value="">Select Client</option>
                        @foreach(\App\Models\User::where('role', 'client')->where('status', 'active')->get() as $client)
                            <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->email }})</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Assigned To -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Assign To</span>
                    </label>
                    <select name="assigned_to" class="select select-bordered focus:select-primary">
                        <option value="">Select Staff Member</option>
                        @foreach(\App\Models\User::where('role', 'staff')->where('status', 'active')->get() as $staff)
                            <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Priority -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Priority *</span>
                    </label>
                    <select name="priority" class="select select-bordered focus:select-primary" required>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="low">Low</option>
                    </select>
                </div>
                
                <!-- Category -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Category *</span>
                    </label>
                    <select name="category" class="select select-bordered focus:select-primary" required>
                        <option value="general">General</option>
                        <option value="technical">Technical</option>
                        <option value="billing">Billing</option>
                        <option value="feature_request">Feature Request</option>
                    </select>
                </div>
                
                <!-- Subject -->
                <fieldset class="fieldset md:col-span-2 mt-2">
                    <legend class="fieldset-legend">Subject *</legend>
                    <input type="text" name="subject" class="input input-bordered focus:input-primary" required placeholder="Brief description of the issue">
                </fieldset>
                
                <!-- Description -->
                <fieldset class="fieldset md:col-span-2 mt-2">
                    <legend class="fieldset-legend">Description *</legend>
                    <textarea name="description" class="textarea textarea-bordered focus:textarea-primary" rows="4" required placeholder="Detailed description of the issue or request..."></textarea>
                </fieldset>
            </div>
            
            <div class="modal-action">
                <button type="button" class="btn" onclick="closeModal()"><i class="fas fa-times mr-2"></i>Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i>Create Ticket
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
                    <span class="label-text font-medium">Reply Message *</span>
                </label>
                <textarea name="message" class="textarea textarea-bordered" rows="5" required placeholder="Type your reply here..."></textarea>
            </div>
            
            <div class="form-control mb-4">
                <label class="cursor-pointer label">
                    <span class="label-text">Internal Note (not visible to client)</span>
                    <input type="checkbox" name="is_internal" class="checkbox checkbox-primary">
                </label>
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
    document.getElementById('modalTitle').textContent = 'Create New Ticket';
    document.getElementById('ticketForm').reset();
    document.getElementById('ticketModal').classList.add('modal-open');
}

function closeModal() {
    document.getElementById('ticketModal').classList.remove('modal-open');
}

function viewTicket(ticketId) {
    window.location.href = `/admin/tickets/${ticketId}`;
}

function replyTicket(ticketId) {
    document.getElementById('replyTicketId').value = ticketId;
    document.getElementById('replyForm').reset();
    document.getElementById('replyModal').classList.add('modal-open');
}

function closeReplyModal() {
    document.getElementById('replyModal').classList.remove('modal-open');
}

function assignTicket(ticketId) {
    // Simple prompt for now - could be enhanced with a proper modal
    const staffSelect = prompt('Enter staff ID to assign:');
    if (staffSelect) {
        fetch(`/admin/tickets/${ticketId}/assign`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ assigned_to: staffSelect })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error assigning ticket');
            }
        });
    }
}

function markInProgress(ticketId) {
    Swal.fire({
        title: 'Tandai in progress?',
        text: 'Status tiket akan menjadi in progress.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, lanjutkan',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/tickets/${ticketId}/in-progress`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Berhasil',
                        text: 'Tiket ditandai in progress.',
                        icon: 'success'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Gagal',
                        text: data.message || 'Kesalahan saat memperbarui status tiket.',
                        icon: 'error'
                    });
                }
            })
            .catch(() => {
                Swal.fire({
                    title: 'Gagal',
                    text: 'Terjadi kesalahan jaringan.',
                    icon: 'error'
                });
            });
        }
    });
}

function markResolved(ticketId) {
    Swal.fire({
        title: 'Tandai resolved?',
        text: 'Tiket akan ditandai sebagai resolved.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, selesai',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/tickets/${ticketId}/resolve`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Berhasil',
                        text: 'Tiket berhasil ditandai resolved.',
                        icon: 'success'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Gagal',
                        text: data.message || 'Kesalahan saat menandai tiket resolved.',
                        icon: 'error'
                    });
                }
            })
            .catch(() => {
                Swal.fire({
                    title: 'Gagal',
                    text: 'Terjadi kesalahan jaringan.',
                    icon: 'error'
                });
            });
        }
    });
}

function closeTicket(ticketId) {
    Swal.fire({
        title: 'Tutup tiket?',
        text: 'Tiket akan ditutup.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, tutup',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/tickets/${ticketId}/close`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Berhasil',
                        text: 'Tiket berhasil ditutup.',
                        icon: 'success'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Gagal',
                        text: data.message || 'Kesalahan saat menutup tiket.',
                        icon: 'error'
                    });
                }
            })
            .catch(() => {
                Swal.fire({
                    title: 'Gagal',
                    text: 'Terjadi kesalahan jaringan.',
                    icon: 'error'
                });
            });
        }
    });
}

function deleteTicket(ticketId) {
    Swal.fire({
        title: 'Anda yakin?',
        text: 'Data yang dihapus tidak dapat dikembalikan!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/tickets/${ticketId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Terhapus!',
                        text: 'Tiket berhasil dihapus.',
                        icon: 'success'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Gagal',
                        text: data.message || 'Terjadi kesalahan saat menghapus tiket.',
                        icon: 'error'
                    });
                }
            })
            .catch(() => {
                Swal.fire({
                    title: 'Gagal',
                    text: 'Terjadi kesalahan jaringan.',
                    icon: 'error'
                });
            });
        }
    });
}

// Handle ticket form submission
document.getElementById('ticketForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/admin/tickets', {
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
            alert('Error creating ticket');
        }
    });
});

// Handle reply form submission
document.getElementById('replyForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const ticketId = document.getElementById('replyTicketId').value;
    
    fetch(`/admin/tickets/${ticketId}/reply`, {
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
            alert('Error sending reply');
        }
    });
});
</script>
@endsection