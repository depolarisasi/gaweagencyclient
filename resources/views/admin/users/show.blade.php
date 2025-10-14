@extends('layouts.app')

@section('title', 'User Details - Admin')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="flex">
        <!-- Sidebar -->  
        @include('layouts.sidebar')
        
        <!-- Main Content -->
        <div class="flex-1 p-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline btn-sm">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Users
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">User Details</h1>
                        <p class="text-gray-600 mt-1">View and manage user information</p>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <button onclick="editUser({{ $user->id }})" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit mr-2"></i>Edit User
                    </button>
                    @if($user->id !== auth()->id())
                    <button onclick="toggleStatus({{ $user->id }})" class="btn btn-sm {{ $user->status === 'active' ? 'btn-warning' : 'btn-success' }}">
                        <i class="fas fa-{{ $user->status === 'active' ? 'pause' : 'play' }} mr-2"></i>
                        {{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}
                    </button>
                    @endif
                </div>
            </div>
            
            <!-- User Profile Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 mb-8">
                <div class="flex items-start space-x-6">
                    <div class="w-24 h-24 bg-gradient-to-r from-blue-500 to-purple-500 rounded-xl flex items-center justify-center">
                        <span class="text-white font-bold text-2xl">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                    </div>
                    
                    <div class="flex-1">
                        <div class="flex items-center space-x-4 mb-4">
                            <h2 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h2>
                            
                            @if($user->role === 'admin')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-crown mr-1"></i>Admin
                                </span>
                            @elseif($user->role === 'staff')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-user-tie mr-1"></i>Staff
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-user mr-1"></i>Client
                                </span>
                            @endif
                            
                            @if($user->status === 'active')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                    <i class="fas fa-pause-circle mr-1"></i>Inactive
                                </span>
                            @endif
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">Contact Information</h3>
                                <div class="space-y-2">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-envelope text-gray-400 w-4"></i>
                                        <span class="text-gray-900">{{ $user->email }}</span>
                                    </div>
                                    @if($user->phone)
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-phone text-gray-400 w-4"></i>
                                        <span class="text-gray-900">{{ $user->phone }}</span>
                                    </div>
                                    @endif
                                    @if($user->address)
                                    <div class="flex items-start space-x-2">
                                        <i class="fas fa-map-marker-alt text-gray-400 w-4 mt-1"></i>
                                        <span class="text-gray-900">{{ $user->address }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div>
                                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">Account Information</h3>
                                <div class="space-y-2">
                                    @if($user->company_name)
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-building text-gray-400 w-4"></i>
                                        <span class="text-gray-900">{{ $user->company_name }}</span>
                                    </div>
                                    @endif
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-calendar text-gray-400 w-4"></i>
                                        <span class="text-gray-900">Joined {{ $user->created_at->format('M d, Y') }}</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-clock text-gray-400 w-4"></i>
                                        <span class="text-gray-900">
                                            @if($user->last_login_at)
                                                Last login {{ $user->last_login_at->diffForHumans() }}
                                            @else
                                                Never logged in
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Total Orders</p>
                            <p class="text-3xl font-bold text-gray-900">{{ $user->orders->count() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Active Projects</p>
                            <p class="text-3xl font-bold text-gray-900">{{ $user->projects->where('status', 'active')->count() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-project-diagram text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Support Tickets</p>
                            <p class="text-3xl font-bold text-gray-900">{{ $user->supportTickets->count() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-ticket-alt text-purple-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Projects -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Projects</h3>
                        <a href="#" class="text-blue-600 hover:text-blue-700 text-sm font-medium">View All</a>
                    </div>
                    
                    <div class="space-y-4">
                        @forelse($user->projects->take(5) as $project)
                        <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition-colors">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-project-diagram text-blue-600 text-sm"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $project->project_name ?? 'Project #' . $project->id }}</p>
                                    <p class="text-sm text-gray-500">{{ $project->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                @if($project->status === 'pending')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pending</span>
                                @elseif($project->status === 'active')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Active</span>
                                @elseif($project->status === 'completed')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Completed</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{{ ucfirst($project->status) }}</span>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8">
                            <i class="fas fa-project-diagram text-gray-300 text-3xl mb-3"></i>
                            <p class="text-gray-500">No projects yet</p>
                        </div>
                        @endforelse
                    </div>
                </div>
                
                <!-- Recent Support Tickets -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Support Tickets</h3>
                        <a href="#" class="text-blue-600 hover:text-blue-700 text-sm font-medium">View All</a>
                    </div>
                    
                    <div class="space-y-4">
                        @forelse($user->supportTickets->take(5) as $ticket)
                        <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition-colors">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-ticket-alt text-purple-600 text-sm"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $ticket->subject }}</p>
                                    <p class="text-sm text-gray-500">{{ $ticket->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                @if($ticket->status === 'open')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Open</span>
                                @elseif($ticket->status === 'in_progress')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">In Progress</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Closed</span>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8">
                            <i class="fas fa-ticket-alt text-gray-300 text-3xl mb-3"></i>
                            <p class="text-gray-500">No support tickets</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function editUser(userId) {
    window.location.href = `/admin/users/${userId}/edit`;
}

function toggleStatus(userId) {
    if (confirm('Are you sure you want to change this user\'s status?')) {
        fetch(`/admin/users/${userId}/toggle-status`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error changing user status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error changing user status');
        });
    }
}
</script>
@endsection