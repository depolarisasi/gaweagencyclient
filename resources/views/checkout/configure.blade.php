@extends('layouts.app')

@section('title', 'Konfigurasi Paket - Checkout')

@section('content')
<div class="min-h-screen bg-base-300 py-8">
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
                        <span class="ml-2 text-sm text-gray-600">Template</span>
                    </div>
                    <div class="w-8 h-px bg-green-500"></div>
                    
                    <!-- Step 2 -->
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm font-medium">
                            2
                        </div>
                        <span class="ml-2 text-sm text-blue-600 font-medium">Konfigurasi</span>
                    </div>
                    <div class="w-8 h-px bg-gray-300"></div>
                    
                    <!-- Step 3 -->
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-medium">
                            3
                        </div>
                        <span class="ml-2 text-sm text-gray-400">Add-ons</span>
                    </div>
                    <div class="w-8 h-px bg-gray-300"></div>
                    
                    <!-- Step 4 -->
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-medium">
                            4
                        </div>
                        <span class="ml-2 text-sm text-gray-400">Info Personal</span>
                    </div>
                    <div class="w-8 h-px bg-gray-300"></div>
                    
                    <!-- Step 5 -->
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-medium">
                            5
                        </div>
                        <span class="ml-2 text-sm text-gray-400">Ringkasan</span>
                    </div>
                    <div class="w-8 h-px bg-gray-300"></div>
                    
                    <!-- Step 6 -->
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-medium">
                            6
                        </div>
                        <span class="ml-2 text-sm text-gray-400">Pembayaran</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Template Info -->
        @if($template)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Template Terpilih</h3>
            <div class="flex items-center space-x-4">
                <img src="{{ $template->thumbnail_url ?? $template->thumbnail }}" alt="{{ $template->name }}" class="w-16 h-16 object-cover rounded-lg">
                <div>
                    <h4 class="font-medium text-gray-900">{{ $template->name }}</h4>
                    <p class="text-sm text-gray-600">{{ $template->description }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Subscription Plans -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Pilih Paket Berlangganan</h2>
            
            <form action="{{ route('checkout.configure') }}" method="POST" id="configureForm">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                    @foreach($subscriptionPlans as $plan)
                    <div class="border border-gray-200 rounded-lg p-6 hover:border-blue-500 transition-colors cursor-pointer plan-card" 
                         data-plan-id="{{ $plan->id }}">
                        <div class="text-center">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $plan->name }}</h3>
                            <p class="text-sm text-gray-600 mb-4">{{ $plan->description }}</p>
                            
                            <!-- Plan Selection -->
                            <label class="flex items-center justify-between p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                                <div class="flex items-center">
                                    <input type="radio" name="subscription_plan_choice" 
                                           value="{{ $plan->id }}"
                                           class="plan-radio"
                                           data-plan="{{ $plan->id }}"
                                           data-billing-cycle="{{ $plan->billing_cycle }}"
                                           {{ old('subscription_plan_id') == $plan->id ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm font-medium">{{ $plan->billing_cycle_label }}</span>
                                </div>
                                <span class="text-sm font-semibold text-blue-600">{{ $plan->formatted_price }}</span>
                            </label>
                            
                            <!-- Features -->
                            @php
                                $features = is_array($plan->features) ? $plan->features : (is_string($plan->features) ? json_decode($plan->features, true) : []);
                                if (!is_array($features)) { $features = []; }
                            @endphp
                            @if(!empty($features))
                            <div class="text-left">
                                <h4 class="text-sm font-medium text-gray-900 mb-2">Fitur:</h4>
                                <ul class="text-xs text-gray-600 space-y-1">
                                    @foreach($features as $feature)
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
                        </div>
                    </div>
                    @endforeach
                </div>

                <input type="hidden" name="subscription_plan_id" id="subscription_plan_id" value="{{ old('subscription_plan_id') }}">
                <input type="hidden" name="billing_cycle" id="billing_cycle" value="{{ old('billing_cycle') }}">

                @error('subscription_plan_id')
                    <div class="text-red-600 text-sm mb-4">{{ $message }}</div>
                @enderror

                @error('billing_cycle')
                    <div class="text-red-600 text-sm mb-4">{{ $message }}</div>
                @enderror

                <!-- Navigation -->
                <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                    <a href="{{ route('checkout.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Kembali
                    </a>
                    
                    <button type="submit" id="nextButton" disabled
                            class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
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
    const planRadios = document.querySelectorAll('.plan-radio');
    const subscriptionPlanInput = document.getElementById('subscription_plan_id');
    const billingCycleInput = document.getElementById('billing_cycle');
    const nextButton = document.getElementById('nextButton');
    const planCards = document.querySelectorAll('.plan-card');

    // Load persisted data from cookies if available
    const savedPlanId = getCookie('checkout_subscription_plan_id');
    const savedBillingCycle = getCookie('checkout_billing_cycle');
    
    if (savedPlanId) {
        const savedRadio = document.querySelector(`input.plan-radio[data-plan="${savedPlanId}"]`);
        if (savedRadio) {
            savedRadio.checked = true;
            subscriptionPlanInput.value = savedPlanId;
            billingCycleInput.value = savedBillingCycle || savedRadio.getAttribute('data-billing-cycle');
            updatePlanSelection(savedPlanId);
            nextButton.disabled = false;
        }
    }

    planRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) {
                const planId = this.getAttribute('data-plan');
                const cycle = this.getAttribute('data-billing-cycle');
                subscriptionPlanInput.value = planId;
                billingCycleInput.value = cycle;
                updatePlanSelection(planId);
                nextButton.disabled = false;
            }
        });
    });

    // Make entire card clickable
    planCards.forEach(card => {
        card.addEventListener('click', function() {
            const radio = card.querySelector('.plan-radio');
            if (radio) {
                radio.checked = true;
                const planId = radio.getAttribute('data-plan');
                const cycle = radio.getAttribute('data-billing-cycle');
                subscriptionPlanInput.value = planId;
                billingCycleInput.value = cycle;
                updatePlanSelection(planId);
                nextButton.disabled = false;
            }
        });
    });

    function updatePlanSelection(selectedPlanId) {
        planCards.forEach(card => {
            if (card.getAttribute('data-plan-id') === selectedPlanId) {
                card.classList.add('border-blue-500', 'bg-blue-50');
            } else {
                card.classList.remove('border-blue-500', 'bg-blue-50');
            }
        });
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