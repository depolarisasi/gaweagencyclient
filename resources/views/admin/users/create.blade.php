@extends('layouts.app')

@section('title', 'Edit User - Admin')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="flex"> 
        @include('layouts.sidebar')
        
        <!-- Main Content -->
        <div class="flex-1 p-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center space-x-4">  
                        <h1 class="text-3xl font-bold text-gray-900">Create User</h1>
                        <p class="text-gray-600 mt-1">Create a new user account</p> 

                        
                </div> 
                <div>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline btn-sm">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Users
                    </a>
                </div>
            </div>
            
           
            
            <!-- Edit Form -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                
                <form id="userForm" action="{{ route('admin.users.store') }}" method="POST" class="space-y-6 p-8">
                    @csrf 
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