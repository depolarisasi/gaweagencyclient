@extends('layouts.app')

@section('title', 'User Management - Admin')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="flex">
    @include('layouts.sidebar')
      
        <!-- Main Content -->
        <div class="flex-1 p-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">User Management</h1>
                    <p class="text-gray-600 mt-1">Manage staff and client accounts</p>
                </div>
                <div class="flex space-x-3">
                     
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-2"></i>Add User
                    </a>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Total Users</p>
                            <p class="text-3xl font-bold text-gray-900">{{ $users->total() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-users text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Active Clients</p>
                            <p class="text-3xl font-bold text-gray-900">{{ \App\Models\User::where('role', 'client')->where('status', 'active')->count() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-user text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Staff Members</p>
                            <p class="text-3xl font-bold text-gray-900">{{ \App\Models\User::where('role', 'staff')->count() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-user-tie text-purple-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">New This Month</p>
                            <p class="text-3xl font-bold text-gray-900">{{ \App\Models\User::whereMonth('created_at', now()->month)->count() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-user-plus text-orange-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
                <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or email..." class="input input-bordered w-full">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                        <select name="role" class="select select-bordered w-full">
                            <option value="">All Roles</option>
                            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="staff" {{ request('role') == 'staff' ? 'selected' : '' }}>Staff</option>
                            <option value="client" {{ request('role') == 'client' ? 'selected' : '' }}>Client</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="select select-bordered w-full">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    
                    <div class="flex items-end space-x-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline">
                            <i class="fas fa-times mr-2"></i>Clear
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Users Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left font-semibold text-gray-900">User</th>
                                <th class="text-left font-semibold text-gray-900">Role</th>
                                <th class="text-left font-semibold text-gray-900">Status</th>
                                <th class="text-left font-semibold text-gray-900">Last Login</th>
                                <th class="text-left font-semibold text-gray-900">Joined</th>
                                <th class="text-center font-semibold text-gray-900">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td>
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg flex items-center justify-center">
                                            <span class="text-white font-semibold text-sm">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900">{{ $user->name }}</p>
                                            <p class="text-sm text-gray-500">{{ $user->email }}</p>
                                            @if($user->company_name)
                                                <p class="text-xs text-gray-400">{{ $user->company_name }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($user->role === 'admin')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-crown mr-1"></i>Admin
                                        </span>
                                    @elseif($user->role === 'staff')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-user-tie mr-1"></i>Staff
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-user mr-1"></i>Client
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->status === 'active')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <i class="fas fa-pause-circle mr-1"></i>Inactive
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->last_login_at)
                                        <span class="text-sm text-gray-900">{{ $user->last_login_at->diffForHumans() }}</span>
                                    @else
                                        <span class="text-sm text-gray-400">Never</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-sm text-gray-900">{{ $user->created_at->format('M d, Y') }}</span>
                                </td>
                                <td>
                                    <div class="flex items-center justify-center space-x-2">
                                        <button onclick="viewUser({{ $user->id }})" class="btn btn-sm btn-outline">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($user->id !== auth()->id())
                                        <button onclick="deleteUser({{ $user->id }})" class="btn btn-sm btn-error">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-12">
                                    <i class="fas fa-users text-gray-300 text-4xl mb-4"></i>
                                    <p class="text-gray-500 text-lg">No users found</p>
                                    <p class="text-gray-400">Try adjusting your search criteria</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($users->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $users->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
 

<script>
let isEditMode = false;
let currentUserId = null;
 
 
 

function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user?')) {
        fetch(`/admin/users/${userId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting user: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting user. Please try again.');
        });
    }
}
 
 
</script>
@endsection