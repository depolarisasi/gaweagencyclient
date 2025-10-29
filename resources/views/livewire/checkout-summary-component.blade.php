<div class="space-y-6">
    <!-- Order Summary -->
    <div class="bg-gray-50 rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Ringkasan Pesanan</h3>
        
        <!-- Template Info -->
        <div class="flex justify-between items-start mb-4 pb-4 border-b border-gray-200">
            <div class="flex-1">
                <h4 class="font-medium text-gray-900">Template Website</h4>
                <p class="text-sm text-gray-600">{{ $template->name }}</p>
                @if($template->description)
                    <p class="text-xs text-gray-500 mt-1">{{ Str::limit($template->description, 100) }}</p>
                @endif
            </div>
            @if($template->thumbnail)
                <img src="{{ asset('storage/' . $template->thumbnail) }}" 
                     alt="{{ $template->name }}" 
                     class="w-16 h-16 object-cover rounded-lg ml-4">
            @else
                <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center ml-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            @endif
        </div>

        <!-- Subscription Plan -->
        <div class="flex justify-between items-center mb-4 pb-4 border-b border-gray-200">
            <div>
                <h4 class="font-medium text-gray-900">Paket Berlangganan</h4>
                <p class="text-sm text-gray-600">{{ $subscriptionPlan->name }} - {{ $subscriptionPlan->billing_cycle }}</p>
            </div>
            <div class="text-right">
                <p class="font-medium text-gray-900">Rp {{ number_format($subscriptionAmount, 0, ',', '.') }}</p>
            </div>
        </div>

        <!-- Domain Info -->
        <div class="flex justify-between items-center mb-4 pb-4 border-b border-gray-200">
            <div>
                <h4 class="font-medium text-gray-900">Domain</h4>
                <p class="text-sm text-gray-600">{{ $this->domainDisplay }}</p>
                <p class="text-xs text-gray-500">{{ ucfirst($domainInfo['type'] ?? $domainInfo['domain_type'] ?? 'unknown') }}</p>
            </div>
            <div class="text-right">
                @php($type = $domainInfo['type'] ?? $domainInfo['domain_type'] ?? '')
                @if($type === 'new')
                    <p class="text-sm font-medium text-green-700">Included</p>
                @elseif($type === 'existing')
                    <p class="text-sm text-gray-600">Domain Existing</p>
                @else
                    <p class="text-sm text-gray-600">-</p>
                @endif
            </div>
        </div>

        <!-- Add-ons -->
        @if($addons->count() > 0)
            <div class="mb-4 pb-4 border-b border-gray-200">
                <h4 class="font-medium text-gray-900 mb-2">Add-ons</h4>
                @foreach($addons as $addon)
                    <div class="flex justify-between items-center mb-2">
                        <div>
                            <p class="text-sm text-gray-900">{{ $addon->name }}</p>
                            <p class="text-xs text-gray-500">{{ $addon->billing_cycle }}</p>
                        </div>
                        <p class="text-sm font-medium text-gray-900">Rp {{ number_format($addon->price, 0, ',', '.') }}</p>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Total -->
        <div class="flex justify-between items-center text-lg font-bold text-gray-900">
            <span>Total</span>
            <span>Rp {{ number_format($totalAmount, 0, ',', '.') }}</span>
        </div>
    </div>

    <!-- Customer Information -->
    <div class="bg-gray-50 rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Pelanggan</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm font-medium text-gray-700">Nama</p>
                <p class="text-sm text-gray-900">{{ $customerInfo['full_name'] ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-700">Email</p>
                <p class="text-sm text-gray-900">{{ $customerInfo['email'] }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-700">Telepon</p>
                <p class="text-sm text-gray-900">{{ $customerInfo['phone'] }}</p>
            </div>
            @if(isset($customerInfo['company']) && $customerInfo['company'])
                <div>
                    <p class="text-sm font-medium text-gray-700">Perusahaan</p>
                    <p class="text-sm text-gray-900">{{ $customerInfo['company'] }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Payment Method Selection -->
    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Pilih Metode Pembayaran</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($paymentChannels as $channel)
                <div class="relative">
                    <input type="radio" 
                           name="payment_channel" 
                           value="{{ $channel['code'] }}" 
                           id="channel_{{ $channel['code'] }}" 
                           wire:click="selectPaymentChannel('{{ $channel['code'] }}')"
                           class="sr-only peer" 
                           required>
                    <label for="channel_{{ $channel['code'] }}" 
                           wire:click="selectPaymentChannel('{{ $channel['code'] }}')"
                           class="block p-4 bg-white border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-300 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all duration-200">
                        <div class="flex items-center">
                            <img src="{{ $channel['icon_url'] }}" 
                                 alt="{{ $channel['name'] }}" 
                                 class="w-8 h-8 object-contain mr-3">
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-900">{{ $channel['name'] }}</h4>
                                <p class="text-sm text-gray-600">{{ $channel['type'] }}</p>
                                @php
                                    $totalFee = 0;
                                    if (is_array($channel['total_fee'])) {
                                        $totalFee = $channel['total_fee']['flat'] + ($channel['total_fee']['percent'] * $totalAmount / 100);
                                    } else {
                                        $totalFee = $channel['total_fee'];
                                    }
                                @endphp
                                @if($totalFee > 0)
                                    <p class="text-xs text-gray-500">
                                        Biaya: Rp {{ number_format($totalFee, 0, ',', '.') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </label>
                </div>
            @endforeach
        </div>

        @if($selectedPaymentChannel)
            <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-sm text-blue-800">
                    <span class="font-medium">Metode pembayaran terpilih:</span>
                    {{ collect($paymentChannels)->firstWhere('code', $selectedPaymentChannel)['name'] ?? $selectedPaymentChannel }}
                </p>
            </div>
        @endif
    </div>
</div>