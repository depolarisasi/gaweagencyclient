@extends('layouts.app')

@section('title', 'Checkout - Informasi Personal & Domain')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Progress Steps -->
        <div class="mb-8">
            <div class="flex items-center justify-center">
                <div class="flex items-center space-x-4">
                    <!-- Step 1 -->
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center text-sm font-medium">
                            ✓
                        </div>
                        <span class="ml-2 text-sm font-medium text-gray-900">Template</span>
                    </div>
                    
                    <div class="w-8 h-px bg-gray-300"></div>
                    
                    <!-- Step 2 -->
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center text-sm font-medium">
                            ✓
                        </div>
                        <span class="ml-2 text-sm font-medium text-gray-900">Konfigurasi</span>
                    </div>
                    
                    <div class="w-8 h-px bg-gray-300"></div>
                    
                    <!-- Step 3 -->
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center text-sm font-medium">
                            ✓
                        </div>
                        <span class="ml-2 text-sm font-medium text-gray-900">Add-ons</span>
                    </div>
                    
                    <div class="w-8 h-px bg-green-500"></div>
                    
                    <!-- Step 4 -->
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm font-medium">
                            4
                        </div>
                        <span class="ml-2 text-sm font-medium text-blue-600">Info Personal</span>
                    </div>
                    
                    <div class="w-8 h-px bg-gray-300"></div>
                    
                    <!-- Step 5 -->
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-medium">
                            5
                        </div>
                        <span class="ml-2 text-sm font-medium text-gray-500">Ringkasan</span>
                    </div>
                    
                    <div class="w-8 h-px bg-gray-300"></div>
                    
                    <!-- Step 6 -->
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-medium">
                            6
                        </div>
                        <span class="ml-2 text-sm font-medium text-gray-500">Pembayaran</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-blue-50 border-b border-blue-100">
                <h1 class="text-2xl font-bold text-gray-900">Informasi Personal & Domain</h1>
                <p class="text-gray-600 mt-1">Masukkan informasi personal Anda dan pilih opsi domain</p>
            </div>

            <form action="{{ route('checkout.personal-info.post') }}" method="POST" class="p-6 space-y-8">
                @csrf

                <!-- Template Info -->
                @if($template)
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Template Terpilih</h3>
                    <div class="flex items-center space-x-4">
                        @if($template->thumbnail_url)
                            <img src="{{ $template->thumbnail_url }}" alt="{{ $template->name }}" class="w-16 h-16 object-cover rounded-lg">
                        @else
                            <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        @endif
                        <div>
                            <h4 class="font-medium text-gray-900">{{ $template->name }}</h4>
                            <p class="text-sm text-gray-600">{{ $template->description }}</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Personal Information -->
                <div class="space-y-6 p-6">
                    <h3 class="text-lg font-medium text-gray-900">Informasi Personal</h3>
                    
                    @auth
                        <!-- Logged in user display -->
                        <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-medium text-gray-900">Login sebagai {{ auth()->user()->name }}</h4>
                                        <p class="text-sm text-gray-600">{{ auth()->user()->email }}</p>
                                        @if(auth()->user()->phone)
                                            <p class="text-sm text-gray-600">{{ auth()->user()->phone }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex space-x-3">
                                    <form action="{{ route('logout') }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-3 py-2 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                            </svg>
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Hidden inputs for logged in user -->
                        <input type="hidden" name="full_name" value="{{ auth()->user()->name }}">
                        <input type="hidden" name="email" value="{{ auth()->user()->email }}">
                        <input type="hidden" name="phone" value="{{ auth()->user()->phone ?? '' }}">
                        <input type="hidden" name="company" value="{{ auth()->user()->company ?? '' }}">
                        <input type="hidden" name="user_logged_in" value="1">
                    @else
                        <!-- Guest user form -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nama Lengkap <span class="text-red-500">*</span>
                                </label>
                                <input 
                                        type="text" 
                                        id="full_name" 
                                        name="full_name" 
                                        value="{{ old('full_name', $customerInfo['full_name'] ?? '') }}"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('full_name') border-red-500 @enderror">
                                @error('full_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email', $customerInfo['email'] ?? '') }}"
                                       required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror">
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nomor Telepon <span class="text-red-500">*</span>
                                </label>
                                <input type="tel" 
                                       id="phone" 
                                       name="phone" 
                                       value="{{ old('phone', $customerInfo['phone'] ?? '') }}"
                                       required
                                       placeholder="08xxxxxxxxxx"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('phone') border-red-500 @enderror">
                                @error('phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                    Password <span class="text-red-500">*</span>
                                </label>
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       required
                                       minlength="8"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('password') border-red-500 @enderror">
                                @error('password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Minimal 8 karakter</p>
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                    Konfirmasi Password <span class="text-red-500">*</span>
                                </label>
                                <input type="password" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       required
                                       minlength="8"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('password_confirmation') border-red-500 @enderror">
                                @error('password_confirmation')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="company" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nama Perusahaan <span class="text-gray-400">(Opsional)</span>
                                </label>
                                <input type="text" 
                                       id="company" 
                                       name="company" 
                                       value="{{ old('company', $customerInfo['company'] ?? '') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('company') border-red-500 @enderror">
                                @error('company')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    @endauth
                </div>

                <!-- Domain Selection -->
                <div class="space-y-6 p-6">
                    <h3 class="text-lg font-medium text-gray-900">Pilihan Domain</h3>
                    
                    <div class="bg-white p-6 rounded-lg border border-gray-200">
                        @livewire('domain-selector')
                    </div>

                    <!-- Hidden inputs for domain data -->
                    @php
                         $domainData = session('checkout.domain', []);
                         $domainType = $domainData['type'] ?? '';
                         $domainName = $domainData['name'] ?? '';
                         
                         // Debug: Log session data to browser console
                         $debugData = json_encode([
                             'sessionData' => $domainData,
                             'domainType' => $domainType,
                             'domainName' => $domainName
                         ]);
                     @endphp
                    
                    <input type="hidden" name="domain_type" value="{{ $domainType }}" id="domain_type_input">
                    <input type="hidden" name="domain_name" value="{{ $domainName }}" id="domain_name_input">

                    <div class="flex justify-between pt-6">
                        <a href="{{ route('checkout.configure') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Kembali
                        </a>
                        
                        <button type="submit" 
                                class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Lanjutkan ke Billing
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Debug: Log session data
    console.log('Session data on page load:', {!! $debugData !!});
    
    let domainDataReady = false;
    
    // Function to check if domain data is valid
    function isDomainDataValid() {
        const domainTypeInput = document.getElementById('domain_type_input');
        const domainNameInput = document.getElementById('domain_name_input');
        
        if (!domainTypeInput || !domainNameInput) {
            return false;
        }
        
        const domainType = domainTypeInput.value;
        const domainName = domainNameInput.value;
        
        return domainType && domainName && domainName.trim() !== '';
    }
    
    // Function to update hidden inputs
    window.updateHiddenInputs = function(data) {
        console.log('updateHiddenInputs called with data:', data);
        const domainTypeInput = document.getElementById('domain_type_input');
        const domainNameInput = document.getElementById('domain_name_input');
        
        console.log('Found elements:', {
            domainTypeInput: !!domainTypeInput,
            domainNameInput: !!domainNameInput
        });
        
        if (domainTypeInput && domainNameInput && data) {
            domainTypeInput.value = data.type || '';
            domainNameInput.value = data.name || '';
            
            // Mark domain data as ready if both fields are filled
            domainDataReady = domainTypeInput.value && domainNameInput.value;
            
            console.log('Updated hidden inputs:', {
                type: domainTypeInput.value,
                name: domainNameInput.value,
                ready: domainDataReady,
                domainTypeValue: domainTypeInput.value,
                domainNameValue: domainNameInput.value
            });
        } else {
            console.log('Failed to update hidden inputs - missing elements or data');
        }
    }
    
    // Initialize domain data from existing values
    if (isDomainDataValid()) {
        domainDataReady = true;
        console.log('Domain data already valid on page load');
    }
    
    // Listen for domain updates from Livewire component
    document.addEventListener('livewire:init', () => {
        console.log('Livewire initialized, setting up domainUpdated listener');
        Livewire.on('domainUpdated', (data) => {
            console.log('Livewire domainUpdated event received:', data);
            window.lastDomainUpdateData = data;
            window.updateHiddenInputs(data);
        });
    });

    // Listen for browser custom events (single listener)
    window.addEventListener('domainUpdated', function(event) {
        console.log('Browser domainUpdated event received:', event.detail);
        
        // Store event data for debugging
        window.lastDomainUpdateData = event.detail;
        
        // Update hidden inputs with the event data
        window.updateHiddenInputs(event.detail);
    });
    
    // Form submission validation
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Double-check domain data validity
            if (!isDomainDataValid()) {
                console.error('Missing domain data on form submission');
                alert('Silakan pilih domain terlebih dahulu. Pastikan Anda telah memilih jenis domain dan mengisi nama domain.');
                e.preventDefault();
                return false;
            }
            
            // Additional validation for form fields
            // Check if user is logged in by looking for hidden input
            const isLoggedIn = document.querySelector('input[name="user_logged_in"]');
            
            let requiredFields = ['full_name', 'email', 'phone'];
            
            // Only validate password fields for guest users
            if (!isLoggedIn) {
                requiredFields.push('password', 'password_confirmation');
            }
            
            for (let fieldName of requiredFields) {
                const field = document.querySelector(`input[name="${fieldName}"]`);
                if (!field || !field.value.trim()) {
                    alert(`Silakan isi field ${fieldName.replace('_', ' ')}`);
                    e.preventDefault();
                    return false;
                }
            }
            
            // Password confirmation check (only for guest users)
            if (!isLoggedIn) {
                const password = document.querySelector('input[name="password"]');
                const passwordConfirmation = document.querySelector('input[name="password_confirmation"]');
                
                if (password && passwordConfirmation) {
                    if (password.value !== passwordConfirmation.value) {
                        alert('Password dan konfirmasi password tidak sama');
                        e.preventDefault();
                        return false;
                    }
                }
            }
            
            console.log('Form validation passed, submitting...');
            return true;
        });
    }
});
</script>
@endpush