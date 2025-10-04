@extends('layouts.app')

@section('title', 'Staff Dashboard - Gawe Agency')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-orange-50 to-red-100">
    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-xl border-r border-gray-200">
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-8">
                    <div class="w-10 h-10 bg-gradient-to-r from-orange-500 to-red-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-user-tie text-white text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800">Staff Panel</h3>
                        <p class="text-xs text-gray-500">Work Management</p>
                    </div>
                </div>
                
                <nav class="space-y-2">
                    <a href="{{ route('staff.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 bg-orange-50 text-orange-700 rounded-lg border border-orange-200">
                        <i class="fas fa-chart-bar w-5"></i>
                        <span class="font-medium">Dashboard</span>
                    </a>
                    
                    <div class="pt-4">
                        <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">My Work</p>
                        
                        <a href="#" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                            <i class="fas fa-project-diagram w-5"></i>
                            <span>Assigned Projects</span>
                        </a>
                        
                        <a href="#" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                            <i class="fas fa-tasks w-5"></i>
                            <span>My Tasks</span>
                        </a>
                        
                        <a href="#" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                            <i class="fas fa-clock w-5"></i>
                            <span>Time Tracking</span>
                        </a>
                    </div>
                    
                    <div class="pt-4">
                        <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Support</p>
                        
                        <a href="#" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                            <i class="fas fa-ticket-alt w-5"></i>
                            <span>Support Tickets</span>
                        </a>
                        
                        <a href="#" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                            <i class="fas fa-comments w-5"></i>
                            <span>Team Chat</span>
                        </a>
                    </div>
                    
                    <div class="pt-4">
                        <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Tools</p>
                        
                        <a href="#" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                            <i class="fas fa-calendar w-5"></i>
                            <span>Calendar</span>
                        </a>
                        
                        <a href="#" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                            <i class="fas fa-file-alt w-5"></i>
                            <span>Reports</span>
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
                    <h1 class="text-3xl font-bold text-gray-900">Staff Dashboard</h1>
                    <p class="text-gray-600 mt-1">Manage your tasks and projects efficiently</p>
                </div>
                <div class="flex space-x-3">
                    <button class="btn btn-outline btn-sm">
                        <i class="fas fa-clock mr-2"></i>Clock In/Out
                    </button>
                    <button class="btn btn-warning btn-sm">
                        <i class="fas fa-plus mr-2"></i>New Task
                    </button>
                </div>
            </div>
            
            <!-- Welcome Card -->
            <div class="bg-gradient-to-r from-orange-500 to-red-500 rounded-xl p-6 text-white mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold mb-2">Good day, {{ auth()->user()->name }}!</h2>
                        <p class="text-orange-100">You have {{ \App\Models\Project::where('assigned_to', auth()->id())->where('status', 'active')->count() }} active projects and {{ \App\Models\SupportTicket::where('assigned_to', auth()->id())->where('status', 'open')->count() }} pending tickets to work on.</p>
                        <div class="mt-4">
                            <button class="bg-white text-orange-600 px-4 py-2 rounded-lg font-medium hover:bg-gray-100 transition-colors">
                                <i class="fas fa-play mr-2"></i>Start Working
                            </button>
                        </div>
                    </div>
                    <div class="hidden md:block">
                        <i class="fas fa-user-tie text-6xl text-orange-200 opacity-50"></i>
                    </div>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">My Active Projects</p>
                            <p class="text-3xl font-bold text-gray-900">{{ \App\Models\Project::where('assigned_to', auth()->id())->where('status', 'active')->count() }}</p>
                            <p class="text-sm text-orange-600 mt-1">
                                <i class="fas fa-tasks mr-1"></i>In progress
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-project-diagram text-orange-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Pending Tickets</p>
                            <p class="text-3xl font-bold text-gray-900">{{ \App\Models\SupportTicket::where('assigned_to', auth()->id())->where('status', 'open')->count() }}</p>
                            <p class="text-sm text-red-600 mt-1">
                                <i class="fas fa-exclamation-circle mr-1"></i>Needs response
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-ticket-alt text-red-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Completed This Month</p>
                            <p class="text-3xl font-bold text-gray-900">{{ \App\Models\Project::where('assigned_to', auth()->id())->where('status', 'completed')->whereMonth('updated_at', now()->month)->count() }}</p>
                            <p class="text-sm text-green-600 mt-1">
                                <i class="fas fa-check-circle mr-1"></i>Well done!
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-trophy text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Hours This Week</p>
                            <p class="text-3xl font-bold text-gray-900">42</p>
                            <p class="text-sm text-blue-600 mt-1">
                                <i class="fas fa-clock mr-1"></i>Productive week
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-stopwatch text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- My Tasks -->
                <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">My Assigned Projects</h3>
                        <a href="#" class="text-orange-600 hover:text-orange-700 text-sm font-medium">View All</a>
                    </div>
                                        Pending Tasks
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">8</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-tasks fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Support Tickets
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">3</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-ticket-alt fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Content Sections -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-project-diagram me-2"></i>Proyek Terbaru
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Nama Proyek</th>
                                            <th>Client</th>
                                            <th>Status</th>
                                            <th>Deadline</th>
                                            <th>Progress</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse(\App\Models\Project::latest()->take(5)->get() as $project)
                                        <tr>
                                            <td>{{ $project->name }}</td>
                                            <td>{{ $project->user->name ?? 'N/A' }}</td>
                                            <td>
                                                @if($project->status === 'pending')
                                                    <span class="badge bg-warning">Pending</span>
                                                @elseif($project->status === 'in_progress')
                                                    <span class="badge bg-primary">In Progress</span>
                                                @elseif($project->status === 'completed')
                                                    <span class="badge bg-success">Completed</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ ucfirst($project->status) }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $project->deadline ? $project->deadline->format('d/m/Y') : 'N/A' }}</td>
                                            <td>
                                                <div class="progress">
                                                    <div class="progress-bar" role="progressbar" style="width: {{ $project->progress ?? 0 }}%">
                                                        {{ $project->progress ?? 0 }}%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center">Belum ada proyek</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-tasks me-2"></i>Task Hari Ini
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Review Design Website</h6>
                                        <p class="mb-1 text-muted small">Project: Company Profile</p>
                                    </div>
                                    <span class="badge bg-warning rounded-pill">High</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Update Progress Report</h6>
                                        <p class="mb-1 text-muted small">Project: E-commerce</p>
                                    </div>
                                    <span class="badge bg-info rounded-pill">Medium</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Client Meeting</h6>
                                        <p class="mb-1 text-muted small">15:00 - 16:00</p>
                                    </div>
                                    <span class="badge bg-success rounded-pill">Low</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-clock me-2"></i>Time Tracking
                            </h6>
                        </div>
                        <div class="card-body text-center">
                            <h4 class="text-primary">7h 30m</h4>
                            <p class="text-muted">Waktu kerja hari ini</p>
                            <button class="btn btn-success btn-sm">
                                <i class="fas fa-play me-1"></i>Start Timer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
.sidebar {
    min-height: calc(100vh - 56px);
}
</style>
@endpush