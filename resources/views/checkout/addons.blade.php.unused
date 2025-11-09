@extends('layouts.app')

@section('title', 'Pilih Add-ons - Checkout')

@push('styles')
<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .addon-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .addon-card:hover {
        transform: translateY(-2px);
    }
    
    .addon-card.selected {
        box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.3), 0 4px 6px -2px rgba(59, 130, 246, 0.05);
    }
    
    .selection-indicator {
        transition: all 0.2s ease-in-out;
    }
    
    .selection-overlay {
        transition: opacity 0.2s ease-in-out;
    }
    
    @media (max-width: 768px) {
        .addon-card {
            margin-bottom: 1rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Progress Steps -->
    <div class="mb-8">
        <div class="flex items-center justify-center">
            <div class="flex items-center space-x-4">
                <!-- Step 1: Domain (completed) -->
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center text-sm font-medium">✓</div>
                    <span class="ml-2 text-sm text-gray-600">Domain</span>
                </div>
                <div class="w-8 h-px bg-green-500"></div>

                <!-- Step 2: Template (completed) -->
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center text-sm font-medium">✓</div>
                    <span class="ml-2 text-sm text-gray-600">Template</span>
                </div>
                <div class="w-8 h-px bg-green-500"></div>

                <!-- Step 3: Info Personal (completed) -->
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center text-sm font-medium">✓</div>
                    <span class="ml-2 text-sm text-gray-600">Info Personal</span>
                </div>
                <div class="w-8 h-px bg-green-500"></div>

                <!-- Step 4: Paket & Add-ons (active) -->
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm font-medium">4</div>
                    <span class="ml-2 text-sm text-blue-600 font-medium">Paket & Add-ons</span>
                </div>
                <div class="w-8 h-px bg-gray-300"></div>

                <!-- Step 5: Ringkasan (upcoming) -->
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-medium">5</div>
                    <span class="ml-2 text-sm text-gray-400">Ringkasan</span>
                </div>
                <div class="w-8 h-px bg-gray-300"></div>

                <!-- Step 6: Pembayaran (upcoming) -->
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-medium">6</div>
                    <span class="ml-2 text-sm text-gray-400">Pembayaran</span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto">
        <!-- Template Terpilih -->
        @if(isset($template) && $template)
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Template Terpilih</h3>
            <div class="flex items-center space-x-4">
                @if($template->thumbnail)
                    <img src="{{ asset('storage/' . $template->thumbnail) }}" 
                         alt="{{ $template->name }}" 
                         class="w-16 h-16 object-cover rounded-lg">
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

        <div class="bg-white rounded-lg shadow-lg p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Pilih Add-ons (Opsional)</h2>
            <p class="text-gray-600 mb-6">Tingkatkan website Anda dengan add-ons tambahan</p>
            
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

            <!-- Selected Subscription Plan Info -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h3 class="text-lg font-medium text-blue-900 mb-2">Paket Terpilih</h3>
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-blue-800 font-medium">{{ $subscriptionPlan->name }}</p>
                        <p class="text-blue-600 text-sm">{{ $subscriptionPlan->billing_cycle }}</p>
                        @if(($subscriptionPlan->discount_percentage ?? 0) > 0)
                            <p class="text-xs text-green-700 mt-1">Diskon {{ number_format($subscriptionPlan->discount_percentage, 0, ',', '.') }}%</p>
                        @endif
                    </div>
                    <div class="text-right">
                        @if(($subscriptionPlan->discount_percentage ?? 0) > 0)
                            <p class="text-xs text-blue-600 line-through">Rp {{ number_format($subscriptionPlan->price, 0, ',', '.') }}</p>
                            <p class="text-blue-900 font-bold text-lg">Rp {{ number_format($subscriptionPlan->discounted_price ?? $subscriptionPlan->price, 0, ',', '.') }}</p>
                        @else
                            <p class="text-blue-900 font-bold text-lg">Rp {{ number_format($subscriptionPlan->price, 0, ',', '.') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <form action="{{ route('checkout.personal-info') }}" method="POST">
                @csrf
                
                @if($addons->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                        @foreach($addons as $addon)
                            <div class="addon-card relative border-2 border-gray-200 rounded-xl p-6 cursor-pointer transition-all duration-300 hover:shadow-lg hover:border-blue-300 bg-white" 
                                 data-addon-id="{{ $addon->id }}">
                                <!-- Selection Indicator -->
                                <div class="absolute top-4 right-4">
                                    <div class="selection-indicator w-6 h-6 border-2 border-gray-300 rounded-full flex items-center justify-center transition-all duration-200">
                                        <svg class="w-4 h-4 text-white opacity-0 transition-opacity duration-200" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Hidden Checkbox -->
                                <input type="checkbox" name="selected_addons[]" value="{{ $addon->id }}" class="hidden addon-checkbox">

                                <!-- Card Content -->
                                <div class="mb-4">
                                    <!-- Icon/Badge -->
                                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mb-4">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                                        </svg>
                                    </div>

                                    <!-- Title -->
                                    <h4 class="text-xl font-bold text-gray-900 mb-2">{{ $addon->name }}</h4>
                                    
                                    <!-- Description -->
                                    @if($addon->description)
                                        <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $addon->description }}</p>
                                    @endif
                                </div>

                                <!-- Features -->
                                @if($addon->features)
                                    @php
                                        $addonFeatures = is_array($addon->features) ? $addon->features : json_decode($addon->features, true);
                                    @endphp
                                    <div class="mb-6">
                                        <ul class="space-y-2">
                                            @foreach(array_slice($addonFeatures, 0, 3) as $feature)
                                                <li class="flex items-center text-sm text-gray-600">
                                                    <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    <span class="truncate">{{ $feature }}</span>
                                                </li>
                                            @endforeach
                                            @if(count($addonFeatures) > 3)
                                                <li class="text-xs text-gray-400 ml-6">
                                                    +{{ count($addonFeatures) - 3 }} fitur lainnya
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                @endif

                                <!-- Price -->
                                <div class="border-t border-gray-100 pt-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-2xl font-bold text-gray-900">
                                                +Rp {{ number_format($addon->price, 0, ',', '.') }}
                                            </p>
                                            <p class="text-sm text-gray-500">{{ $addon->billing_cycle }}</p>
                                        </div>
                                        <div class="text-right">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Add-on
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Selection Overlay -->
             
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada add-ons tersedia</h3>
                        <p class="mt-1 text-sm text-gray-500">Saat ini tidak ada add-ons yang tersedia untuk paket ini.</p>
                    </div>
                @endif

                <div class="flex justify-between">
                    <a href="{{ route('checkout.billing-cycle') }}" 
                       class="inline-flex items-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Kembali
                    </a>
                    
                    <button type="submit" 
                            class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        Lanjutkan ke Ringkasan
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
    const addonCards = document.querySelectorAll('.addon-card');
    
    addonCards.forEach(card => {
        const checkbox = card.querySelector('.addon-checkbox');
        const selectionIndicator = card.querySelector('.selection-indicator');
        const checkIcon = selectionIndicator.querySelector('svg');
        
        // Handle card click
        card.addEventListener('click', function() {
            checkbox.checked = !checkbox.checked;
            updateCardState(card, checkbox.checked);
        });
        
        // Handle checkbox change (for accessibility)
        checkbox.addEventListener('change', function() {
            updateCardState(card, this.checked);
        });
        
        // Update card visual state
         function updateCardState(card, isSelected) {
             if (isSelected) {
                 // Selected state
                 card.classList.add('border-blue-500', 'selected');
                 card.classList.remove('border-gray-200');
                 card.setAttribute('aria-checked', 'true');
                 
                 selectionIndicator.classList.add('bg-blue-500', 'border-blue-500');
                 selectionIndicator.classList.remove('border-gray-300');
                 
                 checkIcon.classList.add('opacity-100');
                 checkIcon.classList.remove('opacity-0');
                 
                 // Add subtle animation
                 card.style.transform = 'scale(1.02)';
                 setTimeout(() => {
                     card.style.transform = 'scale(1)';
                 }, 150);
                 
             } else {
                 // Unselected state
                 card.classList.remove('border-blue-500', 'selected');
                 card.classList.add('border-gray-200');
                 card.setAttribute('aria-checked', 'false');
                 
                 selectionIndicator.classList.remove('bg-blue-500', 'border-blue-500');
                 selectionIndicator.classList.add('border-gray-300');
                 
                 checkIcon.classList.remove('opacity-100');
                 checkIcon.classList.add('opacity-0');
             }
         }
        
        // Initialize state
        updateCardState(card, checkbox.checked);
    });
    
    // Add keyboard navigation support
    addonCards.forEach((card, index) => {
        card.setAttribute('tabindex', '0');
        card.setAttribute('role', 'checkbox');
        card.setAttribute('aria-checked', 'false');
        
        card.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                card.click();
            }
            
            // Arrow key navigation
            if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
                e.preventDefault();
                const nextCard = addonCards[index + 1];
                if (nextCard) nextCard.focus();
            }
            
            if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
                e.preventDefault();
                const prevCard = addonCards[index - 1];
                if (prevCard) prevCard.focus();
            }
        });
        
        card.addEventListener('focus', function() {
            this.classList.add('ring-2', 'ring-blue-500', 'ring-opacity-50');
        });
        
        card.addEventListener('blur', function() {
            this.classList.remove('ring-2', 'ring-blue-500', 'ring-opacity-50');
        });
    });
});
</script>
@endsection