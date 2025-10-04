@extends('layouts.app')

@section('title', 'Edit Template - Admin')

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
                        
                        <a href="{{ route('admin.products') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
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
                        
                        <a href="{{ route('admin.tickets.index') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                            <i class="fas fa-ticket-alt w-5"></i>
                            <span>Support Tickets</span>
                        </a>
                    </div>
                    
                    <div class="pt-4">
                        <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Settings</p>
                        
                        <a href="{{ route('admin.templates.index') }}" class="flex items-center space-x-3 px-4 py-3 bg-blue-50 text-blue-700 rounded-lg border border-blue-200">
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
                    <a href="{{ route('admin.templates.index') }}" class="btn btn-outline btn-sm">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Templates
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Edit Template</h1>
                        <p class="text-gray-600 mt-1">Update template information and settings</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="badge {{ $template->category_badge_class }}">
                        {{ $template->category_text }}
                    </div>
                    <div class="badge {{ $template->is_active ? 'badge-success' : 'badge-error' }}">
                        {{ $template->is_active ? 'Active' : 'Inactive' }}
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
                <form action="{{ route('admin.templates.update', $template->id) }}" method="POST" class="space-y-6 p-8">
                    @csrf
                    @method('PUT')
                    
                    <!-- Template Information Section -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-palette text-blue-600"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-800">Template Information</h4>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Template Name *</span>
                                </label>
                                <input type="text" name="name" value="{{ old('name', $template->name) }}" 
                                       class="input input-bordered focus:input-primary @error('name') input-error @enderror" 
                                       placeholder="Enter template name" required>
                                @error('name')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Category *</span>
                                </label>
                                <select name="category" class="select select-bordered focus:select-primary @error('category') select-error @enderror" required>
                                    <option value="">Choose category</option>
                                    <option value="business" {{ old('category', $template->category) === 'business' ? 'selected' : '' }}>🏢 Business - Corporate websites</option>
                                    <option value="ecommerce" {{ old('category', $template->category) === 'ecommerce' ? 'selected' : '' }}>🛒 E-commerce - Online stores</option>
                                    <option value="portfolio" {{ old('category', $template->category) === 'portfolio' ? 'selected' : '' }}>🎨 Portfolio - Creative showcase</option>
                                    <option value="blog" {{ old('category', $template->category) === 'blog' ? 'selected' : '' }}>📝 Blog - Content websites</option>
                                    <option value="landing" {{ old('category', $template->category) === 'landing' ? 'selected' : '' }}>🎯 Landing - Marketing pages</option>
                                </select>
                                @error('category')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Demo URL</span>
                                </label>
                                <input type="url" name="demo_url" value="{{ old('demo_url', $template->demo_url) }}" 
                                       class="input input-bordered focus:input-primary @error('demo_url') input-error @enderror" 
                                       placeholder="https://demo.example.com">
                                @error('demo_url')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Thumbnail URL</span>
                                </label>
                                <input type="url" name="thumbnail_url" value="{{ old('thumbnail_url', $template->thumbnail_url) }}" 
                                       class="input input-bordered focus:input-primary @error('thumbnail_url') input-error @enderror" 
                                       placeholder="https://example.com/thumbnail.jpg">
                                @error('thumbnail_url')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-control mt-4">
                            <label class="label">
                                <span class="label-text font-medium text-gray-700">Description *</span>
                            </label>
                            <textarea name="description" class="textarea textarea-bordered focus:textarea-primary @error('description') textarea-error @enderror" 
                                      rows="3" placeholder="Enter template description" required>{{ old('description', $template->description) }}</textarea>
                            @error('description')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Template Features Section -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-list text-green-600"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-800">Template Features</h4>
                        </div>
                        
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium text-gray-700">Features (One per line)</span>
                            </label>
                            <textarea name="features" class="textarea textarea-bordered focus:textarea-primary @error('features') textarea-error @enderror" 
                                      rows="6" placeholder="Enter each feature on a new line&#10;Example:&#10;Responsive Design&#10;SEO Optimized&#10;Contact Form&#10;Gallery Section">{{ old('features', $template->features ? implode("\n", $template->features) : '') }}</textarea>
                            <label class="label">
                                <span class="label-text-alt text-gray-500">Enter each feature on a separate line. These will be displayed as bullet points.</span>
                            </label>
                            @error('features')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Template Settings Section -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-cog text-purple-600"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-800">Template Settings</h4>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Sort Order</span>
                                </label>
                                <input type="number" name="sort_order" value="{{ old('sort_order', $template->sort_order) }}" 
                                       class="input input-bordered focus:input-primary @error('sort_order') input-error @enderror" 
                                       min="0" placeholder="0">
                                <label class="label">
                                    <span class="label-text-alt text-gray-500">Lower numbers appear first. Default is 0.</span>
                                </label>
                                @error('sort_order')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Status *</span>
                                </label>
                                <select name="is_active" class="select select-bordered focus:select-primary @error('is_active') select-error @enderror" required>
                                    <option value="1" {{ old('is_active', $template->is_active) == '1' ? 'selected' : '' }}>✅ Active - Visible to clients</option>
                                    <option value="0" {{ old('is_active', $template->is_active) == '0' ? 'selected' : '' }}>❌ Inactive - Hidden from clients</option>
                                </select>
                                @error('is_active')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <!-- Preview Section -->
                    @if($template->thumbnail_url)
                    <div class="bg-gray-50 rounded-lg p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-eye text-yellow-600"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-800">Current Preview</h4>
                        </div>
                        
                        <div class="aspect-video bg-gray-100 rounded-lg overflow-hidden">
                            <img src="{{ $template->thumbnail_url }}" alt="{{ $template->name }}" 
                                 class="w-full h-full object-cover">
                        </div>
                    </div>
                    @endif
                    
                    <!-- Form Actions -->
                    <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                        <div class="text-sm text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Fields marked with * are required
                        </div>
                        
                        <div class="flex space-x-3">
                            <a href="{{ route('admin.templates.index') }}" class="btn btn-outline">
                                <i class="fas fa-times mr-2"></i>Cancel
                            </a>
                            @if($template->demo_url)
                                <a href="{{ $template->demo_url }}" target="_blank" class="btn btn-info">
                                    <i class="fas fa-external-link-alt mr-2"></i>View Demo
                                </a>
                            @endif
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>Update Template
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection