@extends('layouts.app')

@section('title', 'Edit User - Admin')

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
                        
                        <a href="{{ route('admin.users.index') }}" class="flex items-center space-x-3 px-4 py-3 bg-blue-50 text-blue-700 rounded-lg border border-blue-200">
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
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline btn-sm">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Users
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Create User</h1>
                        <p class="text-gray-600 mt-1">Create a new user account</p>
                    </div>
                </div> 
            </div>
            
           
            
            <!-- Edit Form -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                
                <form id="userForm" class="space-y-6 p-8">
                    <!-- Personal Information Section -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-user text-blue-600"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-800">Personal Information</h4>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <fieldset class="fieldset mt-2">
                                <legend class="fieldset-legend">Full Name *</legend>
                                <input type="text" name="name" class="input input-bordered focus:input-primary" placeholder="Enter full name" required>               
                            </fieldset>

                             <fieldset class="fieldset mt-2">
                                <legend class="fieldset-legend">Email Address *</legend>
                                <input type="email" name="email" class="input input-bordered focus:input-primary" 
                                    placeholder="user@example.com" required>
                            </fieldset>
                             
                            <fieldset class="fieldset mt-2">
                                <legend class="fieldset-legend">Phone Number</legend>
                                <input type="text" name="phone" class="input input-bordered focus:input-primary" 
                                    placeholder="+62 812 3456 7890">
                            </fieldset>
                            
                            <fieldset class="fieldset mt-2">
                                <legend class="fieldset-legend">Company Name</legend>
                                <input type="text" name="company_name" class="input input-bordered focus:input-primary" 
                                    placeholder="Company or Organization">
                            </fieldset> 
                                
                            </div> 
                               <fieldset class="fieldset mt-2">
                                <legend class="fieldset-legend">Address</legend>
                                <textarea name="address" class="textarea textarea-bordered focus:textarea-primary" 
                                    rows="3" placeholder="Enter complete address (optional)"></textarea> 
                            </fieldset>
                        </div>
                         
                          
                          
                    </div>
                    
                    <!-- Account Settings Section -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-cog text-green-600"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-800">Account Settings</h4>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">User Role *</span>
                                </label>
                                <select name="role" class="select select-bordered focus:select-primary" required>
                                    <option value="">Choose user role</option>
                                    <option value="admin">👑 Admin - Full system access</option>
                                    <option value="staff">👔 Staff - Project management</option>
                                    <option value="client">👤 Client - Limited access</option>
                                </select>
                                
                            </div>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Account Status *</span>
                                </label>
                                <select name="status" class="select select-bordered focus:select-primary" required>
                                    <option value="active">✅ Active - Can login and use system</option>
                                    <option value="inactive">⏸️ Inactive - Account suspended</option>
                                </select>
                                
                            </div>
                        </div>
                    </div>
                    
                    <!-- Security Section -->
                    <div class="bg-gray-50 rounded-lg p-6" id="securitySection">
                        <div class="flex items-center mb-4">
                            <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-lock text-red-600"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-800">Security Settings</h4>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-control" id="passwordField">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Password *</span>
                                </label>
                                <input type="password" name="password" class="input input-bordered focus:input-primary" 
                                    placeholder="Enter secure password" minlength="8">
                                <label class="label">
                                    <span class="label-text-alt text-gray-500">Minimum 8 characters required</span>
                                </label>
                            </div>
                            
                            <div class="form-control" id="confirmPasswordField">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Confirm Password *</span>
                                </label>
                                <input type="password" name="password_confirmation" class="input input-bordered focus:input-primary" 
                                    placeholder="Confirm password">
                                <label class="label">
                                    <span class="label-text-alt text-gray-500">Must match the password above</span>
                                </label>
                            </div>
                        </div>
                     
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
                                <i class="fas fa-save mr-2"></i>
                                <span id="submitButtonText">Create User</span>
                            </button>
                        </div>
                    </div>
                </form>

             
            </div>
        </div>
    </div>
</div>
@endsection