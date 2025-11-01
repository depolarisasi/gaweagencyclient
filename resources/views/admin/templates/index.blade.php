@extends('layouts.app')

@section('title', 'Template Management - Admin')

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
                    <h1 class="text-3xl font-bold text-gray-900">Template Management</h1>
                    <p class="text-gray-600 mt-1">Manage website templates and email templates</p>
                </div>
                <div class="flex space-x-3">
                    <button class="btn btn-outline btn-sm">
                        <i class="fas fa-download mr-2"></i>Export Templates
                    </button>
                    <button class="btn btn-primary btn-sm" onclick="openCreateModal()">
                        <i class="fas fa-plus mr-2"></i>Add Template
                    </button>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Total Templates</p>
                            <p class="text-3xl font-bold text-gray-900">{{ $templates->total() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-palette text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Active Templates</p>
                            <p class="text-3xl font-bold text-gray-900">{{ \App\Models\Template::where('is_active', true)->count() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Categories</p>
                            <p class="text-3xl font-bold text-gray-900">{{ \App\Models\Template::distinct('category')->count('category') }}</p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-tags text-purple-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Most Popular</p>
                            <p class="text-3xl font-bold text-gray-900">{{ \App\Models\Template::where('category', 'business')->count() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-star text-orange-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
                <form method="GET" action="{{ route('admin.templates.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Template name or description..." class="input input-bordered w-full">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <select name="category" class="select select-bordered w-full">
                            <option value="">All Categories</option>
                            <option value="business" {{ request('category') == 'business' ? 'selected' : '' }}>Business</option>
                            <option value="ecommerce" {{ request('category') == 'ecommerce' ? 'selected' : '' }}>E-Commerce</option>
                            <option value="portfolio" {{ request('category') == 'portfolio' ? 'selected' : '' }}>Portfolio</option>
                            <option value="blog" {{ request('category') == 'blog' ? 'selected' : '' }}>Blog</option>
                            <option value="landing" {{ request('category') == 'landing' ? 'selected' : '' }}>Landing Page</option>
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
                        <a href="{{ route('admin.templates.index') }}" class="btn btn-outline">
                            <i class="fas fa-times mr-2"></i>Clear
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Templates Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($templates as $template)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                    <!-- Template Thumbnail -->
                    <div class="relative h-48 bg-gray-100">
                        @if($template->thumbnail_url)
                            <img src="{{ $template->thumbnail_url }}" alt="{{ $template->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <i class="fas fa-image text-gray-400 text-4xl"></i>
                            </div>
                        @endif
                        
                        <!-- Status Badge -->
                        <div class="absolute top-3 left-3">
                            @if($template->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <i class="fas fa-pause-circle mr-1"></i>Inactive
                                </span>
                            @endif
                        </div>
                        
                        <!-- Category Badge -->
                        <div class="absolute top-3 right-3">
                            @if($template->category === 'business')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-briefcase mr-1"></i>Business
                                </span>
                            @elseif($template->category === 'ecommerce')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-shopping-cart mr-1"></i>E-Commerce
                                </span>
                            @elseif($template->category === 'portfolio')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    <i class="fas fa-user mr-1"></i>Portfolio
                                </span>
                            @elseif($template->category === 'blog')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-blog mr-1"></i>Blog
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                    <i class="fas fa-rocket mr-1"></i>Landing
                                </span>
                            @endif
                        </div>
                        
                        <!-- Demo Button -->
                        @if($template->demo_url)
                        <div class="absolute bottom-3 right-3">
                            <a href="{{ $template->demo_url }}" target="_blank" class="btn btn-sm btn-primary">
                                <i class="fas fa-external-link-alt mr-1"></i>Demo
                            </a>
                        </div>
                        @endif
                    </div>
                    
                    <!-- Template Info -->
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $template->name }}</h3>
                        <p class="text-gray-600 text-sm mb-4">{{ Str::limit($template->description, 100) }}</p>
                        
                        <!-- Features -->
                        @if($template->features && count($template->features) > 0)
                        <div class="mb-4">
                            <p class="text-xs font-medium text-gray-500 mb-2">Features:</p>
                            <div class="flex flex-wrap gap-1">
                                @foreach(array_slice($template->features, 0, 3) as $feature)
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-gray-100 text-gray-700">
                                    {{ $feature }}
                                </span>
                                @endforeach
                                @if(count($template->features) > 3)
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-gray-100 text-gray-700">
                                    +{{ count($template->features) - 3 }} more
                                </span>
                                @endif
                            </div>
                        </div>
                        @endif
                        
                        <!-- Actions -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <button onclick="viewTemplate({{ $template->id }})" class="btn btn-sm btn-outline" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="editTemplate({{ $template->id }})" class="btn btn-sm btn-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <div class="dropdown dropdown-end">
                                    <button tabindex="0" class="btn btn-sm btn-ghost">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-52">
                                        @if($template->is_active)
                                        <li><a onclick="toggleStatus({{ $template->id }})"><i class="fas fa-pause mr-2"></i>Deactivate</a></li>
                                        @else
                                        <li><a onclick="toggleStatus({{ $template->id }})"><i class="fas fa-play mr-2"></i>Activate</a></li>
                                        @endif
                                        <li><a onclick="duplicateTemplate({{ $template->id }})"><i class="fas fa-copy mr-2"></i>Duplicate</a></li>
                                        <li><a onclick="deleteTemplate({{ $template->id }})" class="text-red-600"><i class="fas fa-trash mr-2"></i>Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="text-right">
                                <p class="text-xs text-gray-500">Order: {{ $template->sort_order ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-span-full bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                    <i class="fas fa-palette text-gray-300 text-6xl mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No Templates Found</h3>
                    <p class="text-gray-600 mb-6">Start building your template library by adding your first template.</p>
                    <button onclick="openCreateModal()" class="btn btn-primary">
                        <i class="fas fa-plus mr-2"></i>Add Your First Template
                    </button>
                </div>
                @endforelse
            </div>
            
            @if($templates->hasPages())
            <div class="mt-8">
                {{ $templates->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Create/Edit Template Modal -->
<div id="templateModal" class="modal">
    <div class="modal-box w-11/12 max-w-5xl max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17v4a2 2 0 002 2h4M13 13h4a2 2 0 012 2v4a2 2 0 01-2 2h-4m-6-4a2 2 0 01-2-2V7a2 2 0 012-2h2m0 0V3a2 2 0 012-2h4a2 2 0 012 2v2m-6 4h2m4 0h2m-6 4h2m4 0h2"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-900" id="modalTitle">Add New Template</h3>
                    <p class="text-sm text-gray-500">Create and manage website templates</p>
                </div>
            </div>
            <button type="button" onclick="closeModal()" class="btn btn-sm btn-circle btn-ghost">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <form id="templateForm" class="space-y-6">
            <!-- Basic Information Section -->
            <div class="bg-gray-50 rounded-lg p-6">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-800">Template Information</h4>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Template Name -->
                    <fieldset class="fieldset mt-2">
                        <legend class="fieldset-legend">Template Name *</legend>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17v4a2 2 0 002 2h4M13 13h4a2 2 0 012 2v4a2 2 0 01-2 2h-4m-6-4a2 2 0 01-2-2V7a2 2 0 012-2h2m0 0V3a2 2 0 012-2h4a2 2 0 012 2v2m-6 4h2m4 0h2m-6 4h2m4 0h2"></path>
                                </svg>
                            </div>
                            <input type="text" name="name" class="input input-bordered pl-10 focus:input-primary" required placeholder="Enter template name">
                        </div>
                    </fieldset>
                    
                    <!-- Category -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium text-gray-700">Category *</span>
                        </label>
                        <select name="category" class="select select-bordered focus:select-primary" required>
                            <option value="">📁 Select Category</option>
                            <option value="business">🏢 Business</option>
                            <option value="ecommerce">🛒 E-Commerce</option>
                            <option value="portfolio">🎨 Portfolio</option>
                            <option value="blog">📝 Blog</option>
                            <option value="landing">🚀 Landing Page</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Media & Links Section -->
            <div class="bg-gray-50 rounded-lg p-6">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                        </svg>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-800">Media & Links</h4>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Demo URL -->
                    <fieldset class="fieldset mt-2">
                        <legend class="fieldset-legend">Demo URL</legend>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                            </div>
                            <input type="url" name="demo_url" class="input input-bordered pl-10 focus:input-primary" placeholder="https://demo.example.com">
                        </div>
                        <label class="label">
                            <span class="label-text-alt text-gray-500">Live preview URL for clients</span>
                        </label>
                    </fieldset>
                    
                    <!-- Thumbnail URL -->
                    <fieldset class="fieldset mt-2">
                        <legend class="fieldset-legend">Thumbnail URL</legend>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <input type="url" name="thumbnail_url" class="input input-bordered pl-10 focus:input-primary" placeholder="https://example.com/thumbnail.jpg">
                        </div>
                        <label class="label">
                            <span class="label-text-alt text-gray-500">Template preview image</span>
                        </label>
                    </fieldset>
                </div>
            </div>
            
            <!-- Settings Section -->
            <div class="bg-gray-50 rounded-lg p-6">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-800">Template Settings</h4>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Sort Order -->
                    <fieldset class="fieldset mt-2">
                        <legend class="fieldset-legend">Sort Order</legend>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                </svg>
                            </div>
                            <input type="number" name="sort_order" class="input input-bordered pl-10 focus:input-primary" min="0" value="0" placeholder="0">
                        </div>
                        <label class="label">
                            <span class="label-text-alt text-gray-500">Lower numbers appear first</span>
                        </label>
                    </fieldset>
                    
                    <!-- Status -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium text-gray-700">Status *</span>
                        </label>
                        <select name="is_active" class="select select-bordered focus:select-primary" required>
                            <option value="1">✅ Active</option>
                            <option value="0">⏸️ Inactive</option>
                        </select>
                        <label class="label">
                            <span class="label-text-alt text-gray-500">Active templates are visible to clients</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Content Section -->
            <div class="bg-gray-50 rounded-lg p-6">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-800">Template Content</h4>
                </div>
                
                <!-- Description -->
                <fieldset class="fieldset mb-4">
                    <legend class="fieldset-legend">Description</legend>
                    <textarea name="description" class="textarea textarea-bordered focus:textarea-primary" rows="4" placeholder="Describe the template design, style, and target audience..."></textarea>
                    <label class="label">
                        <span class="label-text-alt text-gray-500">Provide a clear description of the template</span>
                    </label>
                </fieldset>
                
                <!-- Features -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Features</legend>
                    <textarea name="features" class="textarea textarea-bordered focus:textarea-primary" rows="5" placeholder="List template features (one per line):\n• Responsive Design\n• Modern Layout\n• SEO Optimized\n• Contact Form\n• Gallery Section"></textarea>
                    <label class="label">
                        <span class="label-text-alt text-gray-500">Enter each feature on a new line to highlight template capabilities</span>
                    </label>
                </fieldset>
            </div>
            
            <!-- Modal Actions -->
            <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                <div class="text-sm text-gray-500">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Fields marked with * are required
                </div>
                
                <div class="flex space-x-3">
                    <button type="button" class="btn btn-outline" onclick="closeModal()">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        Save Template
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let isEditMode = false;
let currentTemplateId = null;

function openCreateModal() {
    isEditMode = false;
    currentTemplateId = null;
    document.getElementById('modalTitle').textContent = 'Add New Template';
    document.getElementById('templateForm').reset();
    document.getElementById('templateModal').classList.add('modal-open');
}

function closeModal() {
    document.getElementById('templateModal').classList.remove('modal-open');
}

function viewTemplate(templateId) {
    window.location.href = `/admin/templates/${templateId}`;
}

function editTemplate(templateId) {
    isEditMode = true;
    currentTemplateId = templateId;
    document.getElementById('modalTitle').textContent = 'Edit Template';
    
    // Fetch template data and populate form
    fetch(`/admin/templates/${templateId}`)
        .then(response => response.json())
        .then(template => {
            document.querySelector('[name="name"]').value = template.name || '';
            document.querySelector('[name="category"]').value = template.category || '';
            document.querySelector('[name="demo_url"]').value = template.demo_url || '';
            document.querySelector('[name="thumbnail_url"]').value = template.thumbnail_url || '';
            document.querySelector('[name="sort_order"]').value = template.sort_order || 0;
            document.querySelector('[name="is_active"]').value = template.is_active ? '1' : '0';
            document.querySelector('[name="description"]').value = template.description || '';
            document.querySelector('[name="features"]').value = template.features ? template.features.join('\n') : '';
        });
    
    document.getElementById('templateModal').classList.add('modal-open');
}

function toggleStatus(templateId) {
    Swal.fire({
        title: 'Ubah status template?',
        text: 'Status aktif/nonaktif akan diperbarui.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, ubah',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/templates/${templateId}/toggle-status`, {
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
                        text: 'Status template diperbarui.',
                        icon: 'success'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Gagal',
                        text: data.message || 'Kesalahan saat mengubah status template.',
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

function duplicateTemplate(templateId) {
    Swal.fire({
        title: 'Duplikasi template?',
        text: 'Salinan baru akan dibuat dari template ini.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, duplikasi',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/templates/${templateId}/duplicate`, {
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
                        text: 'Template berhasil diduplikasi.',
                        icon: 'success'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Gagal',
                        text: data.message || 'Kesalahan saat menduplikasi template.',
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

function deleteTemplate(templateId) {
    Swal.fire({
        title: 'Anda yakin?',
        text: 'Template yang dihapus tidak dapat dikembalikan!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/templates/${templateId}`, {
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
                        text: 'Template berhasil dihapus.',
                        icon: 'success'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Gagal',
                        text: data.message || 'Terjadi kesalahan saat menghapus template.',
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

// Handle template form submission
document.getElementById('templateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const url = isEditMode ? `/admin/templates/${currentTemplateId}` : '/admin/templates';
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
            alert('Error saving template');
        }
    });
});
</script>
@endsection