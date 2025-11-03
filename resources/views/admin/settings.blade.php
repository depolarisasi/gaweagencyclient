@extends('layouts.app')

@section('title', 'System Settings - Admin')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="flex">
        <!-- Sidebar -->
       @include('layouts.sidebar')
        
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
            <div class="grid grid-cols-1 ">
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
                
                
            </div>
            </form>
        </div>
    </div>
</div>
@endsection