@extends('layouts.app')

@section('title', 'Edit Support Ticket - Admin')

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
                        
<a href="{{ route('admin.products.index') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
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
                        
                        <a href="{{ route('admin.templates.index') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                            <i class="fas fa-palette w-5"></i>
                            <span>Templates</span>
                        </a>
                        
                        <a href="{{ route('admin.settings') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                            <i class="fas fa-credit-card w-5"></i>
                            <span>Payment Settings</span>
                        </a>
                    </div>
                    
                    <div class="pt-6 border-t border-gray-200">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-red-50 hover:text-red-600 rounded-lg transition-colors w-full text-left">
                                <i class="fas fa-sign-out-alt w-5"></i>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </nav>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 p-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.tickets.index') }}" class="btn btn-outline btn-sm">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Tickets
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Edit Support Ticket</h1>
                        <p class="text-gray-600 mt-1">Update ticket information and assignment</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="badge {{ $ticket->priority === 'high' ? 'badge-error' : ($ticket->priority === 'medium' ? 'badge-warning' : 'badge-info') }}">
                        {{ ucfirst($ticket->priority) }} Priority
                    </div>
                    <div class="badge {{ $ticket->status === 'open' ? 'badge-success' : ($ticket->status === 'in_progress' ? 'badge-warning' : 'badge-neutral') }}">
                        {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
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
                <form action="{{ route('admin.tickets.update', $ticket->id) }}" method="POST" class="space-y-6 p-8">
                    @csrf
                    @method('PUT')
                    
                    <!-- Ticket Information Section -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-ticket-alt text-blue-600"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-800">Ticket Information</h4>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <fieldset class="fieldset mt-2">
                                <legend class="fieldset-legend">Subject *</legend>
                                <input type="text" name="subject" value="{{ old('subject', $ticket->subject) }}" 
                                       class="input input-bordered focus:input-primary @error('subject') input-error @enderror" 
                                       placeholder="Enter ticket subject" required>
                                @error('subject')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </fieldset>

                            <fieldset class="fieldset mt-2">
                                <legend class="fieldset-legend">Client</legend>
                                <input type="text" value="{{ $ticket->user->name }} ({{ $ticket->user->email }})" 
                                       class="input input-bordered bg-gray-100" readonly>
                                <label class="label">
                                    <span class="label-text-alt text-gray-500">Client cannot be changed</span>
                                </label>
                            </fieldset>

                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Priority *</span>
                                </label>
                                <select name="priority" class="select select-bordered focus:select-primary @error('priority') select-error @enderror" required>
                                    <option value="low" {{ old('priority', $ticket->priority) === 'low' ? 'selected' : '' }}>🟢 Low - General inquiry</option>
                                    <option value="medium" {{ old('priority', $ticket->priority) === 'medium' ? 'selected' : '' }}>🟡 Medium - Standard issue</option>
                                    <option value="high" {{ old('priority', $ticket->priority) === 'high' ? 'selected' : '' }}>🔴 High - Urgent issue</option>
                                </select>
                                @error('priority')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Department *</span>
                                </label>
                                <select name="department" class="select select-bordered focus:select-primary @error('department') select-error @enderror" required>
                                    <option value="technical" {{ old('department', $ticket->department) === 'technical' ? 'selected' : '' }}>🔧 Technical - Website issues</option>
                                    <option value="billing" {{ old('department', $ticket->department) === 'billing' ? 'selected' : '' }}>💳 Billing - Payment issues</option>
                                    <option value="general" {{ old('department', $ticket->department) === 'general' ? 'selected' : '' }}>💬 General - Other inquiries</option>
                                </select>
                                @error('department')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                        </div>
                        
                        <fieldset class="fieldset mt-4">
                            <legend class="fieldset-legend">Message</legend>
                            <textarea name="message" class="textarea textarea-bordered focus:textarea-primary @error('message') textarea-error @enderror" 
                                      rows="4" placeholder="Ticket message or description" readonly>{{ $ticket->message }}</textarea>
                            <label class="label">
                                <span class="label-text-alt text-gray-500">Original message cannot be edited</span>
                            </label>
                        </fieldset>
                    </div>
                    
                    <!-- Assignment Section -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-user-cog text-green-600"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-800">Assignment & Status</h4>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Assigned To</span>
                                </label>
                                <select name="assigned_to" class="select select-bordered focus:select-primary @error('assigned_to') select-error @enderror">
                                    <option value="">Unassigned</option>
                                    @foreach($staff as $member)
                                        <option value="{{ $member->id }}" {{ old('assigned_to', $ticket->assigned_to) == $member->id ? 'selected' : '' }}>
                                            {{ $member->name }} ({{ ucfirst($member->role) }})
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
                                    <span class="label-text font-medium text-gray-700">Status *</span>
                                </label>
                                <select name="status" class="select select-bordered focus:select-primary @error('status') select-error @enderror" required>
                                    <option value="open" {{ old('status', $ticket->status) === 'open' ? 'selected' : '' }}>🟢 Open - New ticket</option>
                                    <option value="in_progress" {{ old('status', $ticket->status) === 'in_progress' ? 'selected' : '' }}>🟡 In Progress - Being worked on</option>
                                    <option value="resolved" {{ old('status', $ticket->status) === 'resolved' ? 'selected' : '' }}>✅ Resolved - Issue fixed</option>
                                    <option value="closed" {{ old('status', $ticket->status) === 'closed' ? 'selected' : '' }}>🔒 Closed - Ticket completed</option>
                                </select>
                                @error('status')
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
                        
                        <fieldset class="fieldset mt-2">
                            <legend class="fieldset-legend">Internal Notes</legend>
                            <textarea name="internal_notes" class="textarea textarea-bordered focus:textarea-primary @error('internal_notes') textarea-error @enderror" 
                                      rows="4" placeholder="Internal notes for staff and admin only (not visible to client)">{{ old('internal_notes', $ticket->internal_notes) }}</textarea>
                            <label class="label">
                                <span class="label-text-alt text-gray-500">These notes are only visible to admin and staff members</span>
                            </label>
                            @error('internal_notes')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </fieldset>
                    </div>
                    
                    <!-- Resolution Section -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-check-circle text-purple-600"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-800">Resolution Information</h4>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <fieldset class="fieldset mt-2">
                                <legend class="fieldset-legend">Resolved At</legend>
                                <input type="datetime-local" name="resolved_at" 
                                       value="{{ old('resolved_at', $ticket->resolved_at?->format('Y-m-d\TH:i')) }}" 
                                       class="input input-bordered focus:input-primary @error('resolved_at') input-error @enderror">
                                <label class="label">
                                    <span class="label-text-alt text-gray-500">Leave empty if not resolved yet</span>
                                </label>
                                @error('resolved_at')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </fieldset>

                            <fieldset class="fieldset mt-2">
                                <legend class="fieldset-legend">Closed At</legend>
                                <input type="datetime-local" name="closed_at" 
                                       value="{{ old('closed_at', $ticket->closed_at?->format('Y-m-d\TH:i')) }}" 
                                       class="input input-bordered focus:input-primary @error('closed_at') input-error @enderror">
                                <label class="label">
                                    <span class="label-text-alt text-gray-500">Leave empty if not closed yet</span>
                                </label>
                                @error('closed_at')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                        <div class="text-sm text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Fields marked with * are required
                        </div>
                        
                        <div class="flex space-x-3">
                            <a href="{{ route('admin.tickets.index') }}" class="btn btn-outline">
                                <i class="fas fa-times mr-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>Update Ticket
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection