@extends('layouts.app')

@section('title', 'Admin Dashboard - Gawe Agency')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="flex">
      @include('layouts.sidebar')
        
        <!-- Main Content -->
        <div class="flex-1 p-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
                    <p class="text-gray-600 mt-1">Welcome back, manage your agency operations</p>
                </div>
                <div class="flex space-x-3">
                    <button class="btn btn-outline btn-sm">
                        <i class="fas fa-download mr-2"></i>Export Report
                    </button>
                    <button class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-2"></i>Quick Action
                    </button>
                </div>
            </div>
            
            <!-- Welcome Card -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl p-6 text-white mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold mb-2">Welcome back, {{ auth()->user()->name }}!</h2>
                        <p class="text-blue-100">You're logged in as Administrator. Here's what's happening with your agency today.</p>
                    </div>
                    <div class="hidden md:block">
                        <i class="fas fa-user-shield text-6xl text-blue-200 opacity-50"></i>
                    </div>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Total Users</p>
                            <p class="text-3xl font-bold text-gray-900">{{ \App\Models\User::count() }}</p>
                            <p class="text-sm text-green-600 mt-1">
                                <i class="fas fa-arrow-up mr-1"></i>+12% from last month
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-users text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Active Projects</p>
                            <p class="text-3xl font-bold text-gray-900">{{ \App\Models\Project::where('status', 'active')->count() }}</p>
                            <p class="text-sm text-green-600 mt-1">
                                <i class="fas fa-arrow-up mr-1"></i>+8% from last month
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-project-diagram text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Monthly Revenue</p>
                            <p class="text-3xl font-bold text-gray-900">Rp {{ number_format(\App\Models\Invoice::where('status', 'paid')->whereMonth('created_at', now()->month)->sum('total_amount'), 0, ',', '.') }}</p>
                            <p class="text-sm text-green-600 mt-1">
                                <i class="fas fa-arrow-up mr-1"></i>+15% from last month
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Open Tickets</p>
                            <p class="text-3xl font-bold text-gray-900">{{ \App\Models\SupportTicket::where('status', 'open')->count() }}</p>
                            <p class="text-sm text-orange-600 mt-1">
                                <i class="fas fa-exclamation-triangle mr-1"></i>Needs attention
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-ticket-alt text-orange-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <button class="w-full flex items-center space-x-3 p-3 text-left hover:bg-gray-50 rounded-lg transition-colors">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-user-plus text-blue-600"></i>
                            </div>
                            <span class="font-medium text-gray-700">Add New User</span>
                        </button>
                        <button class="w-full flex items-center space-x-3 p-3 text-left hover:bg-gray-50 rounded-lg transition-colors">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-plus text-green-600"></i>
                            </div>
                            <span class="font-medium text-gray-700">Create Project</span>
                        </button>
                        <button class="w-full flex items-center space-x-3 p-3 text-left hover:bg-gray-50 rounded-lg transition-colors">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-file-invoice text-purple-600"></i>
                            </div>
                            <span class="font-medium text-gray-700">Generate Invoice</span>
                        </button>
                    </div>
                </div>
            
                <!-- Recent Projects -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Projects</h3>
                        <a href="#" class="text-blue-600 hover:text-blue-700 text-sm font-medium">View All</a>
                    </div>
                    <div class="space-y-4">
                        @forelse(\App\Models\Project::latest()->take(5)->get() as $project)
                        <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition-colors">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-project-diagram text-gray-600"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $project->project_name ?? 'Project #' . $project->id }}</p>
                                    <p class="text-sm text-gray-500">{{ $project->user->name ?? 'Unknown Client' }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                @if($project->status === 'pending')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pending</span>
                                @elseif($project->status === 'active')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{{ ucfirst($project->status) }}</span>
                                @endif
                                <p class="text-xs text-gray-500 mt-1">{{ $project->created_at->diffForHumans() }}</p>
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
                
                <!-- Pending Tasks -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Pending Tasks</h3>
                        <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ \App\Models\SupportTicket::where('status', 'open')->count() }} Open</span>
                    </div>
                    <div class="space-y-4">
                        @forelse(\App\Models\SupportTicket::where('status', 'open')->latest()->take(5)->get() as $ticket)
                        <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition-colors">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-ticket-alt text-red-600"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $ticket->subject }}</p>
                                    <p class="text-sm text-gray-500">{{ $ticket->user->name ?? 'Unknown User' }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                @if($ticket->priority === 'high')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">High</span>
                                @elseif($ticket->priority === 'medium')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Medium</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Low</span>
                                @endif
                                <p class="text-xs text-gray-500 mt-1">{{ $ticket->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8">
                            <i class="fas fa-check-circle text-green-300 text-3xl mb-3"></i>
                            <p class="text-gray-500">All caught up!</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection