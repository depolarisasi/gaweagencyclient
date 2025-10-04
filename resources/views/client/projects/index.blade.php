@extends('layouts.app')

@section('title', 'My Projects')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-green-50 to-blue-50">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">
                        <i class="fas fa-project-diagram text-green-600 mr-3"></i>
                        My Projects
                    </h1>
                    <p class="text-gray-600">Track your website projects and progress</p>
                </div>
                <a href="{{ route('home') }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-2"></i>
                    Order New Project
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="card bg-base-100 shadow-lg">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Total Projects</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $projects->total() }}</p>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-full">
                            <i class="fas fa-project-diagram text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card bg-base-100 shadow-lg">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Active Projects</p>
                            <p class="text-2xl font-bold text-green-600">{{ $activeCount }}</p>
                        </div>
                        <div class="p-3 bg-green-100 rounded-full">
                            <i class="fas fa-play-circle text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card bg-base-100 shadow-lg">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">In Progress</p>
                            <p class="text-2xl font-bold text-orange-600">{{ $inProgressCount }}</p>
                        </div>
                        <div class="p-3 bg-orange-100 rounded-full">
                            <i class="fas fa-cog text-orange-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card bg-base-100 shadow-lg">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Completed</p>
                            <p class="text-2xl font-bold text-purple-600">{{ $completedCount }}</p>
                        </div>
                        <div class="p-3 bg-purple-100 rounded-full">
                            <i class="fas fa-check-circle text-purple-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Projects Grid -->
        @if($projects->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($projects as $project)
                    <div class="card bg-base-100 shadow-xl hover:shadow-2xl transition-shadow duration-300">
                        <div class="card-body">
                            <!-- Project Header -->
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="card-title text-lg font-bold text-gray-800">
                                    {{ $project->project_name }}
                                </h3>
                                @if($project->status === 'active')
                                    <div class="badge badge-success">Active</div>
                                @elseif($project->status === 'pending')
                                    <div class="badge badge-warning">Pending</div>
                                @elseif($project->status === 'in_progress')
                                    <div class="badge badge-info">In Progress</div>
                                @elseif($project->status === 'completed')
                                    <div class="badge badge-success">Completed</div>
                                @elseif($project->status === 'suspended')
                                    <div class="badge badge-error">Suspended</div>
                                @else
                                    <div class="badge badge-ghost">{{ ucfirst($project->status) }}</div>
                                @endif
                            </div>

                            <!-- Template Info -->
                            @if($project->template)
                                <div class="mb-4">
                                    <div class="flex items-center gap-2 mb-2">
                                        <i class="fas fa-palette text-primary"></i>
                                        <span class="font-medium text-gray-700">Template:</span>
                                    </div>
                                    <div class="bg-gray-50 p-3 rounded-lg">
                                        <div class="font-medium text-gray-800">{{ $project->template->name }}</div>
                                        <div class="badge {{ $project->template->category_badge_class }} badge-sm mt-1">
                                            {{ $project->template->category_text }}
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Progress Bar -->
                            @if($project->status === 'in_progress' || $project->status === 'active')
                                <div class="mb-4">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm font-medium text-gray-700">Progress</span>
                                        <span class="text-sm text-gray-600">{{ $project->progress_percentage }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-primary h-2 rounded-full transition-all duration-300" 
                                             style="width: {{ $project->progress_percentage }}%"></div>
                                    </div>
                                </div>
                            @endif

                            <!-- Project Details -->
                            <div class="space-y-2 mb-4">
                                @if($project->start_date)
                                    <div class="flex items-center gap-2 text-sm text-gray-600">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>Started: {{ $project->start_date->format('d M Y') }}</span>
                                    </div>
                                @endif
                                
                                @if($project->due_date)
                                    <div class="flex items-center gap-2 text-sm text-gray-600">
                                        <i class="fas fa-calendar-check"></i>
                                        <span>Due: {{ $project->due_date->format('d M Y') }}</span>
                                    </div>
                                @endif
                                
                                @if($project->assigned_to)
                                    <div class="flex items-center gap-2 text-sm text-gray-600">
                                        <i class="fas fa-user-tie"></i>
                                        <span>Assigned to: {{ $project->assignedStaff->name ?? 'Staff' }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Description -->
                            @if($project->description)
                                <div class="mb-4">
                                    <p class="text-sm text-gray-600 line-clamp-3">{{ $project->description }}</p>
                                </div>
                            @endif

                            <!-- Actions -->
                            <div class="card-actions justify-end">
                                <a href="{{ route('client.projects.show', $project) }}" 
                                   class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye mr-1"></i>
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($projects->hasPages())
                <div class="flex justify-center mt-8">
                    {{ $projects->links() }}
                </div>
            @endif
        @else
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body text-center py-12">
                    <div class="text-6xl text-gray-300 mb-4">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">No Projects Yet</h3>
                    <p class="text-gray-500 mb-6">You don't have any projects at the moment. Start by ordering a new website!</p>
                    <a href="{{ route('home') }}" class="btn btn-primary">
                        <i class="fas fa-plus mr-2"></i>
                        Order Your First Project
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection