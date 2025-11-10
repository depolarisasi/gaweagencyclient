@extends('layouts.app')

@section('title', 'Informasi Pembayaran - Checkout')

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

                <!-- Step 4: Paket & Add-ons (completed) -->
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center text-sm font-medium">✓</div>
                    <span class="ml-2 text-sm text-gray-600">Paket & Add-ons</span>
                </div>
                <div class="w-8 h-px bg-green-500"></div>

                <!-- Step 5: Ringkasan (completed) -->
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center text-sm font-medium">✓</div>
                    <span class="ml-2 text-sm text-gray-600">Ringkasan</span>
                </div>
                <div class="w-8 h-px bg-green-500"></div>

                <!-- Step 6: Pembayaran (active) -->
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm font-medium">6</div>
                    <span class="ml-2 text-sm text-blue-600 font-medium">Pembayaran</span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto">
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Payment Information -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Informasi Pembayaran</h2>
                    
                    @if(isset($tripayTransaction) && $tripayTransaction)
                        <!-- Payment Method Info -->
                        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-center mb-2">
                                <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                </svg>
                                <h3 class="text-lg font-semibold text-blue-900">Metode Pembayaran</h3>
                            </div>
                            <p class="text-blue-800">{{ $channelDetails['name'] ?? 'Metode Pembayaran' }}</p>
                        </div>

                        <!-- Payment Amount -->
                        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                    <span class="text-green-800 font-medium">Total Pembayaran</span>
                                </div>
                                <span class="text-2xl font-bold text-green-900">
                                    Rp {{ number_format($tripayTransaction['amount'] ?? 0, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        <!-- Payment Deadline -->
                        @if(isset($tripayTransaction['expired_time']))
                        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex items-center mb-2">
                                <svg class="w-5 h-5 text-yellow-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <h3 class="text-lg font-semibold text-yellow-900">Batas Waktu Pembayaran</h3>
                            </div>
                            <p class="text-yellow-800 font-medium">
                                {{ \Carbon\Carbon::parse($tripayTransaction['expired_time'])->format('d M Y, H:i') }} WIB
                            </p>
                            <p class="text-sm text-yellow-700 mt-1">
                                Pembayaran akan otomatis dibatalkan setelah batas waktu ini
                            </p>
                        </div>
                        @endif

                        <!-- Payment Instructions -->
                        @if(isset($tripayTransaction['payment_method']) && $tripayTransaction['payment_method'] === 'QRIS')
                            <!-- QR Code Payment -->
                            <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Cara Pembayaran QRIS</h3>
                                
                                @if(isset($tripayTransaction['qr_url']))
                                <div class="text-center mb-4">
                                    <img src="{{ $tripayTransaction['qr_url'] }}" alt="QR Code" class="mx-auto max-w-xs">
                                </div>
                                @endif
                                
                                <ol class="list-decimal list-inside space-y-2 text-sm text-gray-700">
                                    <li>Buka aplikasi mobile banking atau e-wallet yang mendukung QRIS</li>
                                    <li>Pilih menu "Scan QR" atau "Bayar dengan QR"</li>
                                    <li>Scan QR Code di atas</li>
                                    <li>Pastikan nominal pembayaran sesuai</li>
                                    <li>Konfirmasi pembayaran</li>
                                </ol>
                            </div>
                        @elseif(isset($tripayTransaction['payment_method']) && in_array($tripayTransaction['payment_method'], ['SHOPEEPAY', 'OVO']))
                            <!-- E-Wallet Redirect -->
                            <div class="mb-6 p-4 bg-purple-50 border border-purple-200 rounded-lg">
                                <h3 class="text-lg font-semibold text-purple-900 mb-4">Pembayaran {{ $channelDetails['name'] ?? $tripayTransaction['payment_method'] }}</h3>
                                
                                @if(isset($tripayTransaction['checkout_url']))
                                <div class="text-center mb-4">
                                    <a href="{{ $tripayTransaction['checkout_url'] }}" target="_blank" 
                                       class="inline-flex items-center px-6 py-3 bg-purple-600 text-white font-medium rounded-lg hover:bg-purple-700 transition-colors">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                        </svg>
                                        Bayar Sekarang
                                    </a>
                                </div>
                                @endif
                                
                                <p class="text-sm text-purple-700">
                                    Klik tombol "Bayar Sekarang" untuk diarahkan ke aplikasi {{ $channelDetails['name'] ?? $tripayTransaction['payment_method'] }} dan selesaikan pembayaran.
                                </p>
                            </div>
                        @else
                            <!-- Virtual Account -->
                            <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Nomor Virtual Account</h3>
                                
                                @if(isset($tripayTransaction['pay_code']))
                                <div class="mb-4 p-3 bg-white border border-gray-300 rounded-lg">
                                    <div class="flex items-center justify-between">
                                        <span class="text-lg font-mono font-bold text-gray-900">{{ $tripayTransaction['pay_code'] }}</span>
                                        <button onclick="copyToClipboard('{{ $tripayTransaction['pay_code'] }}', this)" 
                                                class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            Salin
                                        </button>
                                    </div>
                                </div>
                                @endif
                                
                                <div class="text-sm text-gray-700 space-y-2">
                                    <p><strong>Cara Pembayaran:</strong></p>
                                    <ol class="list-decimal list-inside space-y-1 ml-4">
                                        <li>Login ke mobile banking atau ATM</li>
                                        <li>Pilih menu "Transfer" atau "Bayar"</li>
                                        <li>Pilih "Virtual Account" atau "{{ $channelDetails['name'] ?? 'Bank' }}"</li>
                                        <li>Masukkan nomor Virtual Account di atas</li>
                                        <li>Pastikan nominal pembayaran sesuai</li>
                                        <li>Konfirmasi pembayaran</li>
                                    </ol>
                                </div>
                            </div>
                        @endif

                        <!-- Reference Number -->
                        @if(isset($tripayTransaction['reference']))
                        <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Nomor Referensi</h3>
                            <p class="font-mono text-gray-700">{{ $tripayTransaction['reference'] }}</p>
                            <p class="text-sm text-gray-600 mt-1">Simpan nomor ini untuk referensi pembayaran Anda</p>
                        </div>
                        @endif

                    @else
                        <div class="text-center py-8">
                            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Informasi Pembayaran Tidak Tersedia</h3>
                            <p class="text-gray-600">Silakan kembali ke halaman sebelumnya dan pilih metode pembayaran.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Pesanan</h3>
                    
                    <!-- Template -->
                    @if(isset($template))
                    <div class="mb-4 pb-4 border-b border-gray-200">
                        <h4 class="font-medium text-gray-900">Template</h4>
                        <p class="text-sm text-gray-600">{{ $template->name }}</p>
                    </div>
                    @endif

                    <!-- Subscription Plan -->
                    @if(isset($subscriptionPlan))
                    <div class="mb-4 pb-4 border-b border-gray-200">
                        <h4 class="font-medium text-gray-900">Paket Berlangganan</h4>
                        <p class="text-sm text-gray-600">{{ $subscriptionPlan->name }}</p>
                        <p class="text-sm font-medium text-gray-900">Rp {{ number_format($subscriptionPlan->price, 0, ',', '.') }}</p>
                    </div>
                    @endif

                    <!-- Domain -->
                    @if(isset($domainInfo))
                    <div class="mb-4 pb-4 border-b border-gray-200">
                        <h4 class="font-medium text-gray-900">Domain</h4>
                        @if($domainInfo['type'] === 'new')
                            <p class="text-sm text-gray-600">Domain Baru: {{ $domainInfo['name'] ?? '' }}</p>
                            <p class="text-xs font-medium text-green-700">Included</p>
                        @elseif($domainInfo['type'] === 'existing')
                            <p class="text-sm text-gray-600">Domain Existing: {{ $domainInfo['existing'] ?? '' }}</p>
                            <p class="text-xs text-gray-600">Pemilik akan mengarahkan nameserver</p>
                        @else
                            <p class="text-sm text-gray-600">-</p>
                        @endif
                    </div>
                    @endif

                    <!-- Add-ons -->
                    @if(isset($addons) && $addons->count() > 0)
                    <div class="mb-4 pb-4 border-b border-gray-200">
                        <h4 class="font-medium text-gray-900">Add-ons</h4>
                        @foreach($addons as $addon)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ $addon->name }}</span>
                            <span class="font-medium text-gray-900">Rp {{ number_format($addon->price, 0, ',', '.') }}</span>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    <!-- Customer Info -->
                    @if(isset($customerInfo))
                    <div class="mb-4">
                        <h4 class="font-medium text-gray-900 mb-2">Informasi Customer</h4>
                        <div class="text-sm text-gray-600 space-y-1">
                            <p><strong>Nama:</strong> {{ $customerInfo['full_name'] ?? 'N/A' }}</p>
                            <p><strong>Email:</strong> {{ $customerInfo['email'] }}</p>
                            <p><strong>Telepon:</strong> {{ $customerInfo['phone'] }}</p>
                            @if(isset($customerInfo['company']) && $customerInfo['company'])
                                <p><strong>Perusahaan:</strong> {{ $customerInfo['company'] }}</p>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 space-y-3">
                    <a href="{{ route('checkout.summary') }}" 
                       class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Kembali ke Ringkasan
                    </a>
                    
                    <form action="{{ route('checkout.submit') }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Konfirmasi Pesanan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text, buttonEl) {
    navigator.clipboard.writeText(text).then(function() {
        // Show success message
        const button = buttonEl || document.activeElement;
        if (!button) return;
        const originalText = button.textContent;
        button.textContent = 'Tersalin!';
        button.classList.add('text-green-600');
        
        setTimeout(function() {
            button.textContent = originalText;
            button.classList.remove('text-green-600');
        }, 2000);
    }).catch(function(err) {
        console.error('Could not copy text: ', err);
    });
}
</script>
@endsection