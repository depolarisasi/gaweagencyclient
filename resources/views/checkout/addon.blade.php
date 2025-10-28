@extends('layouts.app')

@section('title', 'Pilih Add-ons - Checkout')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Progress Bar -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="flex items-center text-green-600">
                        <div class="flex items-center justify-center w-8 h-8 border-2 border-green-600 rounded-full bg-green-600 text-white text-sm font-medium">
                            ✓
                        </div>
                        <span class="ml-2 text-sm font-medium">Template</span>
                    </div>
                </div>
                <div class="flex-1 mx-4 h-1 bg-green-600"></div>
                <div class="flex items-center">
                    <div class="flex items-center text-green-600">
                        <div class="flex items-center justify-center w-8 h-8 border-2 border-green-600 rounded-full bg-green-600 text-white text-sm font-medium">
                            ✓
                        </div>
                        <span class="ml-2 text-sm font-medium">Konfigurasi</span>
                    </div>
                </div>
                <div class="flex-1 mx-4 h-1 bg-green-600"></div>
                <div class="flex items-center">
                    <div class="flex items-center text-blue-600">
                        <div class="flex items-center justify-center w-8 h-8 border-2 border-blue-600 rounded-full bg-blue-600 text-white text-sm font-medium">
                            3
                        </div>
                        <span class="ml-2 text-sm font-medium">Add-ons</span>
                    </div>
                </div>
                <div class="flex-1 mx-4 h-1 bg-gray-200"></div>
                <div class="flex items-center text-gray-400">
                    <div class="flex items-center justify-center w-8 h-8 border-2 border-gray-300 rounded-full text-sm font-medium">
                        4
                    </div>
                    <span class="ml-2 text-sm font-medium">Info Personal</span>
                </div>
                <div class="flex-1 mx-4 h-1 bg-gray-200"></div>
                <div class="flex items-center text-gray-400">
                    <div class="flex items-center justify-center w-8 h-8 border-2 border-gray-300 rounded-full text-sm font-medium">
                        5
                    </div>
                    <span class="ml-2 text-sm font-medium">Ringkasan</span>
                </div>
            </div>
        </div>

        <!-- Selected Plan Info -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Paket Terpilih</h3>
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="font-medium text-gray-900">{{ $subscriptionPlan->name }}</h4>
                    <p class="text-sm text-gray-600">{{ ucfirst($billingCycle) }} - {{ $template->name }}</p>
                </div>
                <div class="text-right">
                    <p class="text-lg font-semibold text-blue-600">
                        {{ $subscriptionPlan->formatted_price }}
                        <span class="text-sm text-gray-500">/{{ $subscriptionPlan->billing_cycle_label }}</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Add-ons Selection -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Pilih Add-ons (Opsional)</h2>
            
            <form action="{{ route('checkout.addon') }}" method="POST" id="addonForm">
                @csrf
                
                @if($addons->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                    @foreach($addons as $addon)
                    <div class="border border-gray-200 rounded-lg p-6 hover:border-blue-500 transition-colors addon-card" 
                         data-addon-id="{{ $addon->id }}">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $addon->name }}</h3>
                                <p class="text-sm text-gray-600 mb-3">{{ $addon->description }}</p>
                                
                                @if($addon->features)
                                <div class="mb-3">
                                    <h4 class="text-sm font-medium text-gray-900 mb-1">Fitur:</h4>
                                    <ul class="text-xs text-gray-600 space-y-1">
                                        @foreach(json_decode($addon->features, true) as $feature)
                                        <li class="flex items-center">
                                            <svg class="w-3 h-3 text-green-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ $feature }}
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endif
                                
                                <div class="text-lg font-semibold text-blue-600">
                                    Rp {{ number_format($addon->price, 0, ',', '.') }}
                                    @if($addon->billing_type === 'recurring')
                                        <span class="text-sm text-gray-500">/{{ $addon->billing_cycle }}</span>
                                    @else
                                        <span class="text-sm text-gray-500">(sekali bayar)</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="ml-4">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="selected_addons[]" value="{{ $addon->id }}" 
                                           class="addon-checkbox form-checkbox h-5 w-5 text-blue-600 rounded focus:ring-blue-500 border-gray-300"
                                           {{ in_array($addon->id, old('selected_addons', [])) ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm font-medium text-gray-700">Pilih</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada add-ons tersedia</h3>
                    <p class="mt-1 text-sm text-gray-500">Anda dapat melanjutkan ke langkah berikutnya.</p>
                </div>
                @endif

                @error('selected_addons')
                    <div class="text-red-600 text-sm mb-4">{{ $message }}</div>
                @enderror

                <!-- Navigation -->
                <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                    <a href="{{ route('checkout.configure') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Kembali
                    </a>
                    
                    <button type="submit"
                            class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Lanjutkan
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addonCheckboxes = document.querySelectorAll('.addon-checkbox');
    const addonCards = document.querySelectorAll('.addon-card');

    // Load persisted data from cookies if available
    const savedAddons = getCookie('checkout_selected_addons');
    if (savedAddons) {
        try {
            const selectedAddonIds = JSON.parse(savedAddons);
            selectedAddonIds.forEach(addonId => {
                const checkbox = document.querySelector(`input[value="${addonId}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                    updateAddonSelection(checkbox);
                }
            });
        } catch (e) {
            console.log('Error parsing saved addons:', e);
        }
    }

    addonCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateAddonSelection(this);
        });
    });

    function updateAddonSelection(checkbox) {
        const addonCard = checkbox.closest('.addon-card');
        if (checkbox.checked) {
            addonCard.classList.add('border-blue-500', 'bg-blue-50');
        } else {
            addonCard.classList.remove('border-blue-500', 'bg-blue-50');
        }
    }

    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    }
});
</script>
@endsection