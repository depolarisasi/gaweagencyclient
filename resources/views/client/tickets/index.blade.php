@extends('layouts.client')

@section('title', 'Support Tickets')

@section('content')
        <div class="p-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Support Tickets</h1>
                    <p class="text-gray-600 mt-1">Get help from our support team</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('client.tickets.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-2"></i>Create Ticket
                    </a>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-md shadow-sm border border-base-300 p-6">
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
                
                <div class="bg-white rounded-md shadow-sm border border-base-300 p-6">
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
                
                <div class="bg-white rounded-md shadow-sm border border-base-300 p-6">
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
            <div class="bg-white rounded-md shadow-sm border border-base-300 p-6 mb-6">
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
                <div class="bg-white rounded-md shadow-sm border border-base-300 p-6 hover:shadow-md transition-shadow">
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
                            <p class="text-gray-600 mb-4">{{ Str::limit(strip_tags($ticket->description), 150) }}</p>
                            
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
                            <a href="{{ route('client.tickets.show', $ticket) }}" class="btn btn-sm btn-outline">
                                <i class="fas fa-eye mr-2"></i>View
                            </a>
                            @if($ticket->status !== 'closed')
                            <a href="{{ route('client.tickets.reply.form', $ticket) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-reply mr-2"></i>Reply
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="bg-white rounded-md shadow-sm border border-base-300 p-12 text-center">
                    <i class="fas fa-ticket-alt text-gray-300 text-6xl mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No Support Tickets</h3>
                    <p class="text-gray-600 mb-6">You haven't created any support tickets yet. Need help? Create your first ticket!</p>
                    <a href="{{ route('client.tickets.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus mr-2"></i>Create Your First Ticket
                    </a>
                </div>
                @endforelse
            </div>
            
            @if($tickets->hasPages())
            <div class="mt-8">
                {{ $tickets->links() }}
            </div>
            @endif
        </div>

@endsection