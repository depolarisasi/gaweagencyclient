@extends('layouts.app')

@section('title', 'Ringkasan Pesanan - Checkout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Progress Steps -->
    <div class="mb-8">
        <div class="flex items-center justify-center">
            <div class="flex items-center space-x-4">
                <!-- Step 1 -->
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center text-sm font-medium">
                        ✓
                    </div>
                    <span class="ml-2 text-sm text-gray-600">Template</span>
                </div>
                <div class="w-8 h-px bg-green-500"></div>
                
                <!-- Step 2 -->
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center text-sm font-medium">
                        ✓
                    </div>
                    <span class="ml-2 text-sm text-gray-600">Konfigurasi</span>
                </div>
                <div class="w-8 h-px bg-green-500"></div>
                
                <!-- Step 3 -->
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center text-sm font-medium">
                        ✓
                    </div>
                    <span class="ml-2 text-sm text-gray-600">Add-ons</span>
                </div>
                <div class="w-8 h-px bg-green-500"></div>
                
                <!-- Step 4 -->
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center text-sm font-medium">
                        ✓
                    </div>
                    <span class="ml-2 text-sm text-gray-600">Info Personal</span>
                </div>
                <div class="w-8 h-px bg-green-500"></div>
                
                <!-- Step 5 -->
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm font-medium">
                        5
                    </div>
                    <span class="ml-2 text-sm text-blue-600 font-medium">Ringkasan</span>
                </div>
                <div class="w-8 h-px bg-gray-300"></div>
                
                <!-- Step 6 -->
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-medium">
                        6
                    </div>
                    <span class="ml-2 text-sm text-gray-500">Pembayaran</span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Ringkasan Pesanan</h2>
            
            @if ($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Terjadi kesalahan:</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Livewire Checkout Summary Component -->
            @livewire('checkout-summary-component', [
                'template' => $template,
                'subscriptionPlan' => $subscriptionPlan,
                'addons' => $addons,
                'customerInfo' => $customerInfo,
                'domainInfo' => $domainInfo
            ])

            <!-- Submit Form -->
            <form action="{{ route('checkout.submit') }}" method="POST" class="mt-8" id="checkout-form">
                @csrf
                <input type="hidden" name="payment_channel" id="payment_channel" required>
                
                <div class="flex justify-between">
                    <a href="{{ route('checkout.personal-info') }}" 
                       class="inline-flex items-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Kembali
                    </a>
                    
                    <button type="submit" 
                            id="submit-button"
                            disabled
                            class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-gray-400 cursor-not-allowed transition-colors duration-200 disabled:opacity-50">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        Bayar Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('checkout-form');
    const submitButton = document.getElementById('submit-button');
    const paymentChannelInput = document.getElementById('payment_channel');

    // Function to enable submit button
    function enableSubmitButton(channelCode) {
        console.log('Enabling submit button for channel:', channelCode);
        paymentChannelInput.value = channelCode;
        
        // Enable submit button
        submitButton.disabled = false;
        submitButton.classList.remove('bg-gray-400', 'cursor-not-allowed');
        submitButton.classList.add('bg-blue-600', 'hover:bg-blue-700', 'focus:outline-none', 'focus:ring-2', 'focus:ring-offset-2', 'focus:ring-blue-500');
    }

    // Listen for payment channel selection from Livewire component
    window.addEventListener('payment-channel-selected', function(event) {
        console.log('Payment channel selected event received:', event.detail);
        const channelCode = event.detail[0];
        enableSubmitButton(channelCode);
    });

    // Also listen for Livewire events (alternative approach)
    document.addEventListener('livewire:init', function() {
        Livewire.on('payment-channel-selected', function(channelCode) {
            console.log('Livewire payment channel selected:', channelCode);
            enableSubmitButton(channelCode);
        });
    });

    // Fallback: Check for radio button changes directly
    document.addEventListener('change', function(e) {
        if (e.target.name === 'payment_channel') {
            console.log('Radio button changed:', e.target.value);
            enableSubmitButton(e.target.value);
        }
    });

    // Form submission handling
    form.addEventListener('submit', function(e) {
        if (!paymentChannelInput.value) {
            e.preventDefault();
            alert('Silakan pilih metode pembayaran terlebih dahulu.');
            return false;
        }

        // Show loading state
        submitButton.disabled = true;
        submitButton.innerHTML = `
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Memproses...
        `;
    });
});
</script>
@endpush
@endsection