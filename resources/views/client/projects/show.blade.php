@extends('layouts.app')

@section('title', 'Project Details')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-green-50 to-blue-50">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">
                        <i class="fas fa-project-diagram text-green-600 mr-3"></i>
                        {{ $project->project_name }}
                    </h1>
                    <p class="text-gray-600">Project details and progress tracking</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('client.projects.index') }}" class="btn btn-outline">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Projects
                    </a>
                    @if($project->status === 'completed' && $project->website_url)
                        <a href="{{ $project->website_url }}" target="_blank" class="btn btn-success">
                            <i class="fas fa-external-link-alt mr-2"></i>
                            Visit Website
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Project Details -->
            <div class="lg:col-span-2">
                <!-- Status Card -->
                <div class="card bg-base-100 shadow-xl mb-6">
                    <div class="card-body">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="card-title text-xl font-bold">
                                <i class="fas fa-info-circle text-primary"></i>
                                Project Status
                            </h2>
                            @if($project->status === 'active')
                                <div class="badge badge-success badge-lg">Active</div>
                            @elseif($project->status === 'pending')
                                <div class="badge badge-warning badge-lg">Pending</div>
                            @elseif($project->status === 'in_progress')
                                <div class="badge badge-info badge-lg">In Progress</div>
                            @elseif($project->status === 'completed')
                                <div class="badge badge-success badge-lg">Completed</div>
                            @elseif($project->status === 'suspended')
                                <div class="badge badge-error badge-lg">Suspended</div>
                            @else
                                <div class="badge badge-ghost badge-lg">{{ ucfirst($project->status) }}</div>
                            @endif
                        </div>

                        @if($project->status === 'suspended')
                            <div class="alert alert-error mb-4">
                                <i class="fas fa-exclamation-triangle"></i>
                                <div>
                                    <h4 class="font-bold">Project Suspended</h4>
                                    <div class="text-sm">
                                        {{ $project->suspension_reason ?? 'This project has been suspended. Please contact support for more information.' }}
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Progress Bar -->
                        @if(in_array($project->status, ['in_progress', 'active', 'completed']))
                            <div class="mb-4">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="font-medium text-gray-700">Project Progress</span>
                                    <span class="text-gray-600">{{ $project->progress_percentage }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3">
                                    <div class="bg-primary h-3 rounded-full transition-all duration-500" 
                                         style="width: {{ $project->progress_percentage }}%"></div>
                                </div>
                            </div>
                        @endif

                        <!-- Project Timeline -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @if($project->start_date)
                                <div class="text-center">
                                    <div class="text-sm text-gray-600">Start Date</div>
                                    <div class="font-medium text-gray-800">{{ $project->start_date->format('d M Y') }}</div>
                                </div>
                            @endif
                            
                            @if($project->due_date)
                                <div class="text-center">
                                    <div class="text-sm text-gray-600">Due Date</div>
                                    <div class="font-medium text-gray-800">{{ $project->due_date->format('d M Y') }}</div>
                                </div>
                            @endif
                            
                            @if($project->completed_date)
                                <div class="text-center">
                                    <div class="text-sm text-gray-600">Completed</div>
                                    <div class="font-medium text-gray-800">{{ $project->completed_date->format('d M Y') }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Template Information -->
                @if($project->template)
                    <div class="card bg-base-100 shadow-xl mb-6">
                        <div class="card-body">
                            <h2 class="card-title text-xl font-bold mb-4">
                                <i class="fas fa-palette text-primary"></i>
                                Template Information
                            </h2>
                            
                            <div class="flex flex-col md:flex-row gap-4">
                                @if($project->template->thumbnail_url)
                                    <div class="md:w-1/3">
                                        <img src="{{ $project->template->thumbnail_url }}" 
                                             alt="{{ $project->template->name }}"
                                             class="w-full h-32 object-cover rounded-lg">
                                    </div>
                                @endif
                                
                                <div class="flex-1">
                                    <h3 class="text-lg font-bold text-gray-800 mb-2">{{ $project->template->name }}</h3>
                                    <div class="badge {{ $project->template->category_badge_class }} mb-2">
                                        {{ $project->template->category_text }}
                                    </div>
                                    <p class="text-gray-600 mb-3">{{ $project->template->description }}</p>
                                    
                                    @if($project->template->demo_url)
                                        <a href="{{ $project->template->demo_url }}" target="_blank" 
                                           class="btn btn-outline btn-sm">
                                            <i class="fas fa-eye mr-1"></i>
                                            View Demo
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Project Description -->
                @if($project->description)
                    <div class="card bg-base-100 shadow-xl mb-6">
                        <div class="card-body">
                            <h2 class="card-title text-xl font-bold mb-4">
                                <i class="fas fa-file-alt text-primary"></i>
                                Project Description
                            </h2>
                            <p class="text-gray-700 leading-relaxed">{{ $project->description }}</p>
                        </div>
                    </div>
                @endif

                <!-- Project Requirements -->
                @if($project->requirements)
                    <div class="card bg-base-100 shadow-xl mb-6">
                        <div class="card-body">
                            <h2 class="card-title text-xl font-bold mb-4">
                                <i class="fas fa-list-check text-primary"></i>
                                Requirements
                            </h2>
                            <ul class="space-y-2">
                                @foreach($project->requirements as $requirement)
                                    <li class="flex items-start gap-2">
                                        <i class="fas fa-check-circle text-success mt-1"></i>
                                        <span class="text-gray-700">{{ $requirement }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <!-- Deliverables -->
                @if($project->deliverables && $project->status === 'completed')
                    <div class="card bg-base-100 shadow-xl mb-6">
                        <div class="card-body">
                            <h2 class="card-title text-xl font-bold mb-4">
                                <i class="fas fa-gift text-primary"></i>
                                Deliverables
                            </h2>
                            <ul class="space-y-2">
                                @foreach($project->deliverables as $deliverable)
                                    <li class="flex items-start gap-2">
                                        <i class="fas fa-check-circle text-success mt-1"></i>
                                        <span class="text-gray-700">{{ $deliverable }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Project Info -->
                <div class="card bg-base-100 shadow-xl mb-6">
                    <div class="card-body">
                        <h3 class="card-title text-lg font-bold mb-4">
                            <i class="fas fa-info text-primary"></i>
                            Project Info
                        </h3>
                        
                        <div class="space-y-3">
                            @if($project->assigned_to)
                                <div>
                                    <span class="text-sm text-gray-600">Assigned to:</span>
                                    <div class="font-medium">{{ $project->assignedStaff->name ?? 'Staff Member' }}</div>
                                </div>
                            @endif
                            
                            <div>
                                <span class="text-sm text-gray-600">Created:</span>
                                <div class="font-medium">{{ $project->created_at->format('d M Y H:i') }}</div>
                            </div>
                            
                            <div>
                                <span class="text-sm text-gray-600">Last Updated:</span>
                                <div class="font-medium">{{ $project->updated_at->format('d M Y H:i') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Website Access -->
                @if($project->status === 'completed' && ($project->website_url || $project->admin_url))
                    <div class="card bg-base-100 shadow-xl mb-6">
                        <div class="card-body">
                            <h3 class="card-title text-lg font-bold mb-4">
                                <i class="fas fa-globe text-primary"></i>
                                Website Access
                            </h3>
                            
                            <div class="space-y-3">
                                @if($project->website_url)
                                    <div>
                                        <span class="text-sm text-gray-600">Website URL:</span>
                                        <div class="font-medium">
                                            <a href="{{ $project->website_url }}" target="_blank" 
                                               class="link link-primary">{{ $project->website_url }}</a>
                                        </div>
                                    </div>
                                @endif
                                
                                @if($project->admin_url)
                                    <div>
                                        <span class="text-sm text-gray-600">Admin Panel:</span>
                                        <div class="font-medium">
                                            <a href="{{ $project->admin_url }}" target="_blank" 
                                               class="link link-primary">{{ $project->admin_url }}</a>
                                        </div>
                                    </div>
                                @endif
                                
                                @if($project->admin_username)
                                    <div>
                                        <span class="text-sm text-gray-600">Admin Username:</span>
                                        <div class="font-medium font-mono">{{ $project->admin_username }}</div>
                                    </div>
                                @endif
                                
                                @if($project->admin_password)
                                    <div>
                                        <span class="text-sm text-gray-600">Admin Password:</span>
                                        <div class="font-medium font-mono">{{ $project->admin_password }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Support -->
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <h3 class="card-title text-lg font-bold mb-4">
                            <i class="fas fa-headset text-primary"></i>
                            Need Help?
                        </h3>
                        
                        <p class="text-gray-600 mb-4">Have questions about your project? Our support team is here to help!</p>
                        
                        <a href="{{ route('client.tickets.create') }}" class="btn btn-primary btn-block">
                            <i class="fas fa-ticket-alt mr-2"></i>
                            Create Support Ticket
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection