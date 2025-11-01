@extends('layouts.app')

@section('title', 'System Settings - Admin')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg border-r border-gray-200">
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-8">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800">Admin Panel</h3>
                        <p class="text-xs text-gray-500">Management Center</p>
                    </div>
                </div>
                
                <nav class="space-y-2">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <span class="font-medium">Dashboard</span>
                    </a>
                    
                    <div class="pt-4">
                        <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Management</p>
                        
                        <a href="{{ route('admin.users.index') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                            <span>User Management</span>
                        </a>
                        
<a href="{{ route('admin.products.index') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            <span>Products</span>
                        </a>
                        
                        <a href="{{ route('admin.projects.index') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <span>Projects</span>
                        </a>
                        
                        <a href="{{ route('admin.invoices.index') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span>Invoices</span>
                        </a>
                    </div>
                    
                    <div class="pt-4">
                        <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Support</p>
                        
                        <a href="{{ route('admin.tickets.index') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                            </svg>
                            <span>Support Tickets</span>
                        </a>
                    </div>
                    
                    <div class="pt-4">
                        <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Settings</p>
                        
                        <a href="{{ route('admin.templates.index') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17v4a2 2 0 002 2h4M13 13h4a2 2 0 012 2v4a2 2 0 01-2 2h-4m-6-4a2 2 0 01-2-2V7a2 2 0 012-2h2m0 0V3a2 2 0 012-2h4a2 2 0 012 2v2m-6 4h2m4 0h2m-6 4h2m4 0h2"></path>
                            </svg>
                            <span>Templates</span>
                        </a>
                        
                        <a href="{{ route('admin.settings') }}" class="flex items-center space-x-3 px-4 py-3 bg-blue-50 text-blue-700 rounded-lg border border-blue-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span>System Settings</span>
                        </a>
                    </div>
                </nav>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 p-8">
            <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
                @csrf
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">System Settings</h1>
                    <p class="text-gray-600 mt-1">Configure system preferences and integrations</p>
                </div>
                <div class="flex space-x-3">
                    <button class="btn btn-outline btn-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        Export Settings
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        Save Changes
                    </button>
                </div>
            </div>
            @if(session('status'))
                <div class="alert alert-success mb-6">{{ session('status') }}</div>
            @endif
            
            <!-- Settings Sections -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- General Settings -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Company Information -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Company Information</h3>
                                <p class="text-sm text-gray-500">Basic company details and branding</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Company Name</span>
                                </label>
                                <input name="company_name" type="text" class="input input-bordered focus:input-primary" value="{{ old('company_name', config('app.company_name')) }}" placeholder="Enter company name">
                            </div>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Company Email</span>
                                </label>
                                <input name="company_email" type="email" class="input input-bordered focus:input-primary" value="{{ old('company_email', config('app.company_email')) }}" placeholder="Enter company email">
                            </div>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Phone Number</span>
                                </label>
                                <input name="company_phone" type="tel" class="input input-bordered focus:input-primary" value="{{ old('company_phone', config('app.company_phone')) }}" placeholder="Enter phone number">
                            </div>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Website URL</span>
                                </label>
                                <input name="company_website" type="url" class="input input-bordered focus:input-primary" value="{{ old('company_website', config('app.company_website')) }}" placeholder="Enter website URL">
                            </div>
                            
                            <div class="form-control md:col-span-2">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Company Address</span>
                                </label>
                                <textarea name="company_address" class="textarea textarea-bordered focus:textarea-primary" rows="3" placeholder="Enter company address">{{ old('company_address', config('app.company_address')) }}</textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Settings -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Payment Gateway</h3>
                                <p class="text-sm text-gray-500">Configure Tripay integration settings</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <fieldset class="fieldset mt-2">
                                <legend class="fieldset-legend">Tripay Merchant Code</legend>
                                <input name="tripay_merchant_code" type="text" class="input input-bordered focus:input-primary" value="{{ old('tripay_merchant_code', config('tripay.merchant_code')) }}" placeholder="Enter merchant code">
                            </fieldset>
                            
                            <fieldset class="fieldset mt-2">
                                <legend class="fieldset-legend">API Key</legend>
                                <input name="tripay_api_key" type="password" class="input input-bordered focus:input-primary" value="{{ old('tripay_api_key', config('tripay.api_key')) }}" placeholder="Enter API key">
                            </fieldset>
                            
                            <fieldset class="fieldset mt-2">
                                <legend class="fieldset-legend">Private Key</legend>
                                <input name="tripay_private_key" type="password" class="input input-bordered focus:input-primary" value="{{ old('tripay_private_key', config('tripay.private_key')) }}" placeholder="Enter private key">
                            </fieldset>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Environment</span>
                                </label>
                                <select name="tripay_mode" class="select select-bordered focus:select-primary">
                                    @php($currentMode = old('tripay_mode', config('tripay.sandbox') ? 'sandbox' : 'production'))
                                    <option value="sandbox" @if($currentMode==='sandbox') selected @endif>Sandbox (Testing)</option>
                                    <option value="production" @if($currentMode==='production') selected @endif>Production (Live)</option>
                                </select>
                            </div>
                            
                            <fieldset class="fieldset md:col-span-2 mt-2">
                                <legend class="fieldset-legend">Callback URL</legend>
                                <input type="url" class="input input-bordered focus:input-primary" value="{{ url('/payment/callback') }}" readonly>
                                <label class="label">
                                    <span class="label-text-alt text-gray-500">This URL should be configured in your Tripay dashboard</span>
                                </label>
                            </fieldset>
                        </div>
                    </div>
                    
                    <!-- Email Settings -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Email Configuration</h3>
                                <p class="text-sm text-gray-500">SMTP settings for system emails</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">SMTP Host</span>
                                </label>
                                <input name="mail_host" type="text" class="input input-bordered focus:input-primary" value="{{ old('mail_host', config('mail.mailers.smtp.host')) }}" placeholder="smtp.gmail.com">
                            </div>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">SMTP Port</span>
                                </label>
                                <input name="mail_port" type="number" class="input input-bordered focus:input-primary" value="{{ old('mail_port', config('mail.mailers.smtp.port')) }}" placeholder="587">
                            </div>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Username</span>
                                </label>
                                <input name="mail_username" type="text" class="input input-bordered focus:input-primary" value="{{ old('mail_username', config('mail.mailers.smtp.username')) }}" placeholder="your-email@gmail.com">
                            </div>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Password</span>
                                </label>
                                <input name="mail_password" type="password" class="input input-bordered focus:input-primary" value="{{ old('mail_password', config('mail.mailers.smtp.password')) }}" placeholder="Enter password">
                            </div>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Encryption</span>
                                </label>
                                <select name="mail_encryption" class="select select-bordered focus:select-primary">
                                    @php($currentEnc = old('mail_encryption', config('mail.mailers.smtp.encryption')))
                                    <option value="tls" @if($currentEnc==='tls') selected @endif>TLS</option>
                                    <option value="ssl" @if($currentEnc==='ssl') selected @endif>SSL</option>
                                </select>
                            </div>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">From Name</span>
                                </label>
                                <input name="mail_from_name" type="text" class="input input-bordered focus:input-primary" value="{{ old('mail_from_name', config('mail.from.name')) }}" placeholder="Enter sender name">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions Sidebar -->
                <div class="space-y-6">
                    <!-- System Status -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">System Status</h3>
                        
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Database</span>
                                <div class="badge badge-success badge-sm">Connected</div>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Payment Gateway</span>
                                <div class="badge badge-warning badge-sm">Testing</div>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Email Service</span>
                                <div class="badge badge-error badge-sm">Not Configured</div>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Storage</span>
                                <div class="badge badge-success badge-sm">Available</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                        
                        <div class="space-y-3">
                            <button class="btn btn-outline btn-sm w-full justify-start">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Clear Cache
                            </button>
                            
                            <button class="btn btn-outline btn-sm w-full justify-start">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                View Logs
                            </button>
                            
                            <button class="btn btn-outline btn-sm w-full justify-start">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                Backup Database
                            </button>
                            
                            <button class="btn btn-outline btn-sm w-full justify-start">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Run Maintenance
                            </button>
                        </div>
                    </div>
                    
                    <!-- System Info -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">System Information</h3>
                        
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Version:</span>
                                <span class="font-medium">1.0.0</span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-gray-600">Laravel:</span>
                                <span class="font-medium">{{ app()->version() }}</span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-gray-600">PHP:</span>
                                <span class="font-medium">{{ PHP_VERSION }}</span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-gray-600">Environment:</span>
                                <span class="font-medium">{{ app()->environment() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </form>
        </div>
    </div>
</div>
@endsection