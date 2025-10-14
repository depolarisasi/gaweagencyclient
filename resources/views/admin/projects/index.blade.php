@extends('layouts.app')

@section('title', 'Project Management - Admin')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="flex">
        <!-- Sidebar --> 
    @include('layouts.sidebar')
        
        <!-- Main Content -->
        <div class="flex-1 p-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Project Management</h1>
                    <p class="text-gray-600 mt-1">Manage projects and assign to staff</p>
                </div>
                <div class="flex space-x-3">
                    <button class="btn btn-outline btn-sm">
                        <i class="fas fa-download mr-2"></i>Export Projects
                    </button>
                    <button class="btn btn-primary btn-sm" onclick="openCreateModal()">
                        <i class="fas fa-plus mr-2"></i>Create Project
                    </button>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Total Projects</p>
                            <p class="text-3xl font-bold text-gray-900">{{ $projects->total() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-project-diagram text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Active Projects</p>
                            <p class="text-3xl font-bold text-gray-900">{{ \App\Models\Project::where('status', 'active')->count() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-play text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Completed</p>
                            <p class="text-3xl font-bold text-gray-900">{{ \App\Models\Project::where('status', 'completed')->count() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check text-purple-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Overdue</p>
                            <p class="text-3xl font-bold text-gray-900">{{ \App\Models\Project::where('status', '!=', 'completed')->where('due_date', '<', now())->count() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
                <form method="GET" action="{{ route('admin.projects.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Project name or client..." class="input input-bordered w-full">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="select select-bordered w-full">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="on_hold" {{ request('status') == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Assigned To</label>
                        <select name="assigned_to" class="select select-bordered w-full">
                            <option value="">All Staff</option>
                            @foreach(\App\Models\User::where('role', 'staff')->where('status', 'active')->get() as $staff)
                                <option value="{{ $staff->id }}" {{ request('assigned_to') == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Due Date</label>
                        <select name="due_filter" class="select select-bordered w-full">
                            <option value="">All Dates</option>
                            <option value="overdue" {{ request('due_filter') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                            <option value="this_week" {{ request('due_filter') == 'this_week' ? 'selected' : '' }}>Due This Week</option>
                            <option value="this_month" {{ request('due_filter') == 'this_month' ? 'selected' : '' }}>Due This Month</option>
                        </select>
                    </div>
                    
                    <div class="flex items-end space-x-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                        <a href="{{ route('admin.projects.index') }}" class="btn btn-outline">
                            <i class="fas fa-times mr-2"></i>Clear
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Projects Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left font-semibold text-gray-900">Project</th>
                                <th class="text-left font-semibold text-gray-900">Client</th>
                                <th class="text-left font-semibold text-gray-900">Assigned To</th>
                                <th class="text-left font-semibold text-gray-900">Progress</th>
                                <th class="text-left font-semibold text-gray-900">Status</th>
                                <th class="text-left font-semibold text-gray-900">Due Date</th>
                                <th class="text-center font-semibold text-gray-900">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($projects as $project)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td>
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $project->project_name ?? 'Project #' . $project->id }}</p>
                                        <p class="text-sm text-gray-500">{{ Str::limit($project->description, 50) }}</p>
                                        @if($project->order)
                                            <p class="text-xs text-gray-400">Order #{{ $project->order->id }}</p>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg flex items-center justify-center">
                                            <span class="text-white font-semibold text-xs">{{ strtoupper(substr($project->user->name, 0, 2)) }}</span>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $project->user->name }}</p>
                                            <p class="text-sm text-gray-500">{{ $project->user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($project->assignedUser)
                                        <div class="flex items-center space-x-2">
                                            <div class="w-6 h-6 bg-orange-100 rounded-full flex items-center justify-center">
                                                <span class="text-orange-600 font-semibold text-xs">{{ strtoupper(substr($project->assignedUser->name, 0, 1)) }}</span>
                                            </div>
                                            <span class="text-sm text-gray-900">{{ $project->assignedUser->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-sm">Unassigned</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="w-full">
                                        <div class="flex items-center justify-between text-sm mb-1">
                                            <span class="text-gray-600">{{ $project->progress_percentage ?? 0 }}%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="h-2 rounded-full {{ $project->progress_percentage >= 100 ? 'bg-green-600' : ($project->progress_percentage >= 50 ? 'bg-blue-600' : 'bg-yellow-600') }}" style="width: {{ $project->progress_percentage ?? 0 }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($project->status === 'completed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>Completed
                                        </span>
                                    @elseif($project->status === 'active')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-play mr-1"></i>Active
                                        </span>
                                    @elseif($project->status === 'pending')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i>Pending
                                        </span>
                                    @elseif($project->status === 'on_hold')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <i class="fas fa-pause mr-1"></i>On Hold
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-times mr-1"></i>Cancelled
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        @if($project->due_date)
                                            <p class="text-sm text-gray-900">{{ $project->due_date->format('M d, Y') }}</p>
                                            @if($project->status !== 'completed' && $project->due_date < now())
                                                <p class="text-xs text-red-600">{{ abs($project->days_until_due) }} days overdue</p>
                                            @elseif($project->status !== 'completed')
                                                <p class="text-xs text-gray-500">{{ $project->days_until_due }} days left</p>
                                            @endif
                                        @else
                                            <span class="text-gray-400 text-sm">No due date</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="flex items-center justify-center space-x-2">
                                        <button onclick="viewProject({{ $project->id }})" class="btn btn-sm btn-outline" title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button onclick="editProject({{ $project->id }})" class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <div class="dropdown dropdown-end">
                                            <button tabindex="0" class="btn btn-sm btn-ghost">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-52">
                                                @if(!$project->assignedUser)
                                                <li><a onclick="assignProject({{ $project->id }})"><i class="fas fa-user-plus mr-2"></i>Assign Staff</a></li>
                                                @endif
                                                <li><a onclick="updateProgress({{ $project->id }})"><i class="fas fa-chart-line mr-2"></i>Update Progress</a></li>
                                                @if($project->status !== 'completed')
                                                <li><a onclick="markCompleted({{ $project->id }})"><i class="fas fa-check mr-2"></i>Mark Completed</a></li>
                                                @endif
                                                @if($project->status === 'active')
                                                <li><a onclick="holdProject({{ $project->id }})"><i class="fas fa-pause mr-2"></i>Put On Hold</a></li>
                                                @elseif($project->status === 'on_hold')
                                                <li><a onclick="resumeProject({{ $project->id }})"><i class="fas fa-play mr-2"></i>Resume</a></li>
                                                @endif
                                                <li><a onclick="deleteProject({{ $project->id }})" class="text-red-600"><i class="fas fa-trash mr-2"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-12">
                                    <i class="fas fa-project-diagram text-gray-300 text-4xl mb-4"></i>
                                    <p class="text-gray-500 text-lg">No projects found</p>
                                    <p class="text-gray-400">Create your first project to get started</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($projects->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $projects->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Project Modal -->
<div id="projectModal" class="modal">
    <div class="modal-box w-11/12 max-w-5xl max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-r from-green-600 to-blue-600 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-900" id="modalTitle">Create New Project</h3>
                    <p class="text-sm text-gray-500">Manage project details and assignments</p>
                </div>
            </div>
            <button type="button" onclick="closeModal()" class="btn btn-sm btn-circle btn-ghost">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <form id="projectForm" class="space-y-6 p-8">
            <!-- Basic Information Section -->
            <div class="bg-gray-50 rounded-lg p-6">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-800">Project Information</h4>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Project Name -->
                    <fieldset class="fieldset mt-2">
                        <legend class="fieldset-legend">Project Name *</legend>
                        <input type="text" name="project_name" class="input input-bordered focus:input-primary" required placeholder="Enter project name">
                    </fieldset>

                    <!-- Client -->
                    <fieldset class="fieldset mt-2">
                        <legend class="fieldset-legend">Client *</legend>
                        <select name="user_id" class="select select-bordered focus:select-primary" required>
                            <option value="">👤 Select Client</option>
                            @foreach(\App\Models\User::where('role', 'client')->where('status', 'active')->get() as $client)
                                <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->email }})</option>
                            @endforeach
                        </select>
                    </fieldset>
                </div>
            </div>
            
            <!-- Assignment & Status Section -->
            <div class="bg-gray-50 rounded-lg p-6">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-800">Assignment & Status</h4>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Assigned To -->
                    <fieldset class="fieldset mt-2">
                        <legend class="fieldset-legend">Assign To</legend>
                        <select name="assigned_to" class="select select-bordered focus:select-primary">
                            <option value="">👨‍💼 Select Staff Member</option>
                            @foreach(\App\Models\User::where('role', 'staff')->where('status', 'active')->get() as $staff)
                                <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-sm text-gray-500 mt-2">Optional: Assign project to a staff member</p>
                    </fieldset>

                    <!-- Status -->
                    <fieldset class="fieldset mt-2">
                        <legend class="fieldset-legend">Status *</legend>
                        <select name="status" class="select select-bordered focus:select-primary" required>
                            <option value="pending">⏳ Pending</option>
                            <option value="active">🚀 Active</option>
                            <option value="on_hold">⏸️ On Hold</option>
                            <option value="completed">✅ Completed</option>
                        </select>
                    </fieldset>
                </div>
            </div>
            
            <!-- Timeline Section -->
            <div class="bg-gray-50 rounded-lg p-6">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-800">Project Timeline</h4>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <!-- Start Date -->
                    <fieldset class="fieldset mt-2">
                        <legend class="fieldset-legend">Start Date</legend>
                        <input type="date" name="start_date" class="input input-bordered focus:input-primary" value="{{ now()->format('Y-m-d') }}">
                        <p class="text-sm text-gray-500 mt-2">When should the project start?</p>
                    </fieldset>
                    
                    <!-- Due Date -->
                    <fieldset class="fieldset mt-2">
                        <legend class="fieldset-legend">Due Date</legend>
                        <input type="date" name="due_date" class="input input-bordered focus:input-primary">
                        <p class="text-sm text-gray-500 mt-2">Project deadline (optional)</p>
                    </fieldset>
                </div>
            </div>
            
            <!-- Project Details Section -->
            <div class="bg-gray-50 rounded-lg p-6">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-800">Project Details</h4>
                </div>
                
                <!-- Description -->
                <fieldset class="fieldset mt-2 mb-4">
                    <legend class="fieldset-legend">Description</legend>
                    <textarea name="description" class="textarea textarea-bordered focus:textarea-primary" rows="4" placeholder="Describe the project scope, objectives, and deliverables..."></textarea>
                    <p class="text-sm text-gray-500 mt-2">Provide a clear overview of what this project entails</p>
                </fieldset>
                
                <!-- Requirements -->
                <fieldset class="fieldset mt-2 mb-4">
                    <legend class="fieldset-legend">Requirements</legend>
                    <textarea name="requirements" class="textarea textarea-bordered focus:textarea-primary" rows="4" placeholder="List project requirements (one per line):\n• Responsive design\n• SEO optimization\n• Contact form integration"></textarea>
                    <p class="text-sm text-gray-500 mt-2">List specific requirements and features needed</p>
                </fieldset>
                
                <!-- Notes -->
                <fieldset class="fieldset mt-2">
                    <legend class="fieldset-legend">Internal Notes</legend>
                    <textarea name="notes" class="textarea textarea-bordered focus:textarea-primary" rows="3" placeholder="Add any internal notes, special considerations, or reminders..."></textarea>
                    <p class="text-sm text-gray-500 mt-2">Internal notes visible only to admin and staff</p>
                </fieldset>
            </div>
            
            <!-- Modal Actions -->
            <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                <div class="text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Fields marked with * are required
                </div>
                
                <div class="flex space-x-3">
                    <button type="button" class="btn btn-outline" onclick="closeModal()">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>Save Project
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Progress Update Modal -->
<div id="progressModal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg mb-4">Update Progress</h3>
        
        <form id="progressForm">
            <input type="hidden" id="progressProjectId" name="project_id">
            
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Progress Percentage</span>
                </label>
                <input type="range" id="progressRange" name="progress_percentage" min="0" max="100" value="0" class="range range-primary">
                <div class="w-full flex justify-between text-xs px-2">
                    <span>0%</span>
                    <span>25%</span>
                    <span>50%</span>
                    <span>75%</span>
                    <span>100%</span>
                </div>
                <div class="text-center mt-2">
                    <span id="progressValue" class="text-lg font-bold">0%</span>
                </div>
            </div>
            
            <div class="modal-action">
                <button type="button" class="btn" onclick="closeProgressModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i>Update Progress
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let isEditMode = false;
let currentProjectId = null;

function openCreateModal() {
    isEditMode = false;
    currentProjectId = null;
    document.getElementById('modalTitle').textContent = 'Create New Project';
    document.getElementById('projectForm').reset();
    document.getElementById('projectModal').classList.add('modal-open');
}

function closeModal() {
    document.getElementById('projectModal').classList.remove('modal-open');
}

function viewProject(projectId) {
    window.location.href = `/admin/projects/${projectId}`;
}

function editProject(projectId) {
    isEditMode = true;
    currentProjectId = projectId;
    document.getElementById('modalTitle').textContent = 'Edit Project';
    
    // Fetch project data and populate form
    fetch(`/admin/projects/${projectId}`)
        .then(response => response.json())
        .then(project => {
            document.querySelector('[name="project_name"]').value = project.project_name || '';
            document.querySelector('[name="user_id"]').value = project.user_id;
            document.querySelector('[name="assigned_to"]').value = project.assigned_to || '';
            document.querySelector('[name="status"]').value = project.status;
            document.querySelector('[name="start_date"]').value = project.start_date || '';
            document.querySelector('[name="due_date"]').value = project.due_date || '';
            document.querySelector('[name="description"]').value = project.description || '';
            document.querySelector('[name="requirements"]').value = project.requirements ? project.requirements.join('\n') : '';
            document.querySelector('[name="notes"]').value = project.notes || '';
        });
    
    document.getElementById('projectModal').classList.add('modal-open');
}

function updateProgress(projectId) {
    document.getElementById('progressProjectId').value = projectId;
    
    // Fetch current progress
    fetch(`/admin/projects/${projectId}`)
        .then(response => response.json())
        .then(project => {
            const progress = project.progress_percentage || 0;
            document.getElementById('progressRange').value = progress;
            document.getElementById('progressValue').textContent = progress + '%';
        });
    
    document.getElementById('progressModal').classList.add('modal-open');
}

function closeProgressModal() {
    document.getElementById('progressModal').classList.remove('modal-open');
}

function assignProject(projectId) {
    // Simple prompt for now - could be enhanced with a proper modal
    const staffSelect = prompt('Enter staff ID to assign:');
    if (staffSelect) {
        fetch(`/admin/projects/${projectId}/assign`, {
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
                alert('Error assigning project');
            }
        });
    }
}

function markCompleted(projectId) {
    Swal.fire({
        title: 'Tandai selesai?',
        text: 'Proyek akan ditandai sebagai selesai.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, selesai',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/projects/${projectId}/complete`, {
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
                        text: 'Proyek berhasil ditandai selesai.',
                        icon: 'success'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Gagal',
                        text: data.message || 'Terjadi kesalahan saat menandai selesai.',
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

function holdProject(projectId) {
    Swal.fire({
        title: 'Tahan proyek?',
        text: 'Status proyek akan menjadi on hold.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, tahan',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/projects/${projectId}/hold`, {
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
                        title: 'Ditahan',
                        text: 'Proyek berhasil ditahan.',
                        icon: 'success'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Gagal',
                        text: data.message || 'Terjadi kesalahan saat menahan proyek.',
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

function resumeProject(projectId) {
    Swal.fire({
        title: 'Lanjutkan proyek?',
        text: 'Status proyek akan diubah menjadi aktif.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, lanjutkan',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/projects/${projectId}/resume`, {
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
                        title: 'Dilanjutkan',
                        text: 'Proyek berhasil dilanjutkan.',
                        icon: 'success'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Gagal',
                        text: data.message || 'Terjadi kesalahan saat melanjutkan proyek.',
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

function deleteProject(projectId) {
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
            fetch(`/admin/projects/${projectId}`, {
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
                        text: 'Proyek berhasil dihapus.',
                        icon: 'success'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Gagal',
                        text: data.message || 'Terjadi kesalahan saat menghapus proyek.',
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

// Progress range slider
document.getElementById('progressRange').addEventListener('input', function() {
    document.getElementById('progressValue').textContent = this.value + '%';
});

// Handle project form submission
document.getElementById('projectForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const url = isEditMode ? `/admin/projects/${currentProjectId}` : '/admin/projects';
    const method = isEditMode ? 'PUT' : 'POST';
    
    if (isEditMode) {
        formData.append('_method', 'PUT');
    }
    
    fetch(url, {
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
            alert('Error saving project');
        }
    });
});

// Handle progress form submission
document.getElementById('progressForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const projectId = document.getElementById('progressProjectId').value;
    
    fetch(`/admin/projects/${projectId}/progress`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeProgressModal();
            location.reload();
        } else {
            alert('Error updating progress');
        }
    });
});
</script>
@endsection