@extends('layouts.app')

@section('title', 'Edit Project - Admin')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="flex"> 
    @include('layouts.sidebar')
        <!-- Main Content -->
        <div class="flex-1 p-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.projects.index') }}" class="btn btn-outline btn-sm">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Projects
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Edit Project</h1>
                        <p class="text-gray-600 mt-1">Update project information and settings</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="badge {{ $project->status === 'active' ? 'badge-success' : ($project->status === 'completed' ? 'badge-primary' : 'badge-warning') }}">
                        {{ ucfirst($project->status) }}
                    </div>
                </div>
            </div>
            
            @if(session('success'))
                <div class="alert alert-success mb-6">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            
            @if($errors->any())
                <div class="alert alert-error mb-6">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <h3 class="font-bold">Please fix the following errors:</h3>
                        <ul class="list-disc list-inside mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
            
            <!-- Edit Form -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <form action="{{ route('admin.projects.update', $project->id) }}" method="POST" class="space-y-6 p-8">
                    @csrf
                    @method('PUT')
                    
                    <!-- Project Information Section -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-project-diagram text-blue-600"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-800">Project Information</h4>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Project Name *</span>
                                </label>
                                <input type="text" name="project_name" value="{{ old('project_name', $project->project_name) }}" 
                                       class="input input-bordered focus:input-primary @error('project_name') input-error @enderror" 
                                       placeholder="Enter project name" required>
                                @error('project_name')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Client *</span>
                                </label>
                                <select name="user_id" class="select select-bordered focus:select-primary @error('user_id') select-error @enderror" required>
                                    <option value="">Choose client</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ old('user_id', $project->user_id) == $client->id ? 'selected' : '' }}>
                                            {{ $client->name }} ({{ $client->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Assigned Staff</span>
                                </label>
                                <select name="assigned_to" class="select select-bordered focus:select-primary @error('assigned_to') select-error @enderror">
                                    <option value="">No assignment</option>
                                    @foreach($staff as $member)
                                        <option value="{{ $member->id }}" {{ old('assigned_to', $project->assigned_to) == $member->id ? 'selected' : '' }}>
                                            {{ $member->name }} ({{ $member->role }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('assigned_to')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Project Status *</span>
                                </label>
                                <select name="status" class="select select-bordered focus:select-primary @error('status') select-error @enderror" required>
                                    <option value="pending" {{ old('status', $project->status) === 'pending' ? 'selected' : '' }}>⏳ Pending - Waiting to start</option>
                                    <option value="in_progress" {{ old('status', $project->status) === 'in_progress' ? 'selected' : '' }}>🚀 In Progress - Currently working</option>
                                    <option value="completed" {{ old('status', $project->status) === 'completed' ? 'selected' : '' }}>✅ Completed - Project finished</option>
                                    <option value="on_hold" {{ old('status', $project->status) === 'on_hold' ? 'selected' : '' }}>⏸️ On Hold - Temporarily paused</option>
                                    <option value="cancelled" {{ old('status', $project->status) === 'cancelled' ? 'selected' : '' }}>❌ Cancelled - Project terminated</option>
                                </select>
                                @error('status')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-control mt-4">
                            <label class="label">
                                <span class="label-text font-medium text-gray-700">Project Description</span>
                            </label>
                            <textarea name="description" class="textarea textarea-bordered focus:textarea-primary @error('description') textarea-error @enderror" 
                                      rows="3" placeholder="Enter project description">{{ old('description', $project->description) }}</textarea>
                            @error('description')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Website Access Section -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-globe text-green-600"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-800">Website Access Information</h4>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Website URL</span>
                                </label>
                                <input type="url" name="website_url" value="{{ old('website_url', $project->website_url) }}" 
                                       class="input input-bordered focus:input-primary @error('website_url') input-error @enderror" 
                                       placeholder="https://example.com">
                                @error('website_url')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Admin Panel URL</span>
                                </label>
                                <input type="url" name="admin_url" value="{{ old('admin_url', $project->admin_url) }}" 
                                       class="input input-bordered focus:input-primary @error('admin_url') input-error @enderror" 
                                       placeholder="https://example.com/admin">
                                @error('admin_url')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Admin Username</span>
                                </label>
                                <input type="text" name="admin_username" value="{{ old('admin_username', $project->admin_username) }}" 
                                       class="input input-bordered focus:input-primary @error('admin_username') input-error @enderror" 
                                       placeholder="admin">
                                @error('admin_username')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Admin Password</span>
                                </label>
                                <input type="text" name="admin_password" value="{{ old('admin_password', $project->admin_password) }}" 
                                       class="input input-bordered focus:input-primary @error('admin_password') input-error @enderror" 
                                       placeholder="secure_password">
                                @error('admin_password')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <!-- Internal Notes Section -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-sticky-note text-yellow-600"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-800">Internal Notes</h4>
                        </div>
                        
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium text-gray-700">Notes (Internal Only)</span>
                            </label>
                            <textarea name="notes" class="textarea textarea-bordered focus:textarea-primary @error('notes') textarea-error @enderror" 
                                      rows="4" placeholder="Internal notes for staff and admin only">{{ old('notes', $project->notes) }}</textarea>
                            <label class="label">
                                <span class="label-text-alt text-gray-500">These notes are only visible to admin and staff members</span>
                            </label>
                            @error('notes')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                        <div class="text-sm text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Fields marked with * are required
                        </div>
                        
                        <div class="flex space-x-3">
                            <a href="{{ route('admin.projects.index') }}" class="btn btn-outline">
                                <i class="fas fa-times mr-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>Update Project
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection