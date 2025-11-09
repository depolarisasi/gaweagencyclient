<div>
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Progress Steps -->
            <div class="mb-8">
                <div class="flex items-center justify-center space-x-4">
                    <!-- Step 1 -->
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center text-sm font-medium">
                            ✓
                        </div>
                        <span class="ml-2 text-sm text-green-600">Template</span>
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

            <div class="bg-white rounded-lg shadow-sm p-6">
                <h1 class="text-2xl font-bold text-gray-900 mb-6">Konfigurasi Layanan</h1>

                @if ($template)
                    <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
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

                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Pilih Paket Berlangganan</h2>
                    <p class="text-sm text-gray-600 mb-6">Pilih paket berlangganan yang sesuai dengan kebutuhan Anda.</p>
                    
                    @error('selectedSubscriptionPlanId')
                        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-md">
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        </div>
                    @enderror

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($subscriptionPlans as $plan)
                            <div class="subscription-plan-card border-2 rounded-lg p-6 cursor-pointer transition-all duration-200 hover:shadow-lg {{ $selectedSubscriptionPlanId == $plan->id ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300' }}" 
                                 wire:click="$set('selectedSubscriptionPlanId', {{ $plan->id }})">
                                
                                <div class="text-center">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $plan->name }}</h3>
                                    <p class="text-sm text-gray-600 mb-4">{{ $plan->description }}</p>
                                    
                                    <div class="mb-4">
                                        <span class="text-3xl font-bold text-gray-900">Rp{{ number_format($plan->price, 0, ',', '.') }}</span>
                                        <span class="text-sm text-gray-600">/ {{ $plan->billing_cycle_label }}</span>
                                    </div>
                                    
                                    @if($plan->discount_percentage > 0)
                                        <div class="mb-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Hemat {{ $plan->discount_percentage }}%
                                            </span>
                                        </div>
                                    @endif
                                    
                                    <div class="text-left">
                                        <h4 class="text-sm font-medium text-gray-900 mb-2">Fitur yang termasuk:</h4>
                                        <ul class="text-sm text-gray-600 space-y-1">
                                            @if(is_array($plan->features) && count($plan->features) > 0)
                                                @foreach($plan->features as $feature)
                                                    <li class="flex items-start">
                                                        <svg class="w-4 h-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                        </svg>
                                                        {{ $feature }}
                                                    </li>
                                                @endforeach
                                            @elseif(is_string($plan->features))
                                                @php
                                                    $features = json_decode($plan->features, true) ?: [];
                                                @endphp
                                                @foreach($features as $feature)
                                                    <li class="flex items-start">
                                                        <svg class="w-4 h-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                        </svg>
                                                        {{ $feature }}
                                                    </li>
                                                @endforeach
                                            @else
                                                <li class="text-gray-500">Tidak ada fitur yang tersedia</li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                    <a href="{{ route('checkout.template') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Kembali
                    </a>
                    
                    <button wire:click="configureProduct" 
                            class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            {{ !$selectedSubscriptionPlanId ? 'disabled' : '' }}>
                        Lanjutkan ke Add-ons
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
