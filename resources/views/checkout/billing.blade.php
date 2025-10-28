@extends('layouts.app')

@section('title', 'Pembayaran - Checkout')

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
                    <div class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center text-sm font-medium">
                        ✓
                    </div>
                    <span class="ml-2 text-sm text-gray-600">Ringkasan</span>
                </div>
                <div class="w-8 h-px bg-green-500"></div>
                
                <!-- Step 6 -->
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm font-medium">
                        6
                    </div>
                    <span class="ml-2 text-sm text-blue-600 font-medium">Pembayaran</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Back Navigation -->
    <div class="max-w-4xl mx-auto mb-6">
        <div class="flex justify-start">
            <a href="{{ route('checkout.summary') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-md transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Kembali ke Ringkasan
            </a>
        </div>
    </div>

    <div class="max-w-4xl mx-auto">
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

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

        <!-- Invoice Information -->
        <div class="mb-8">
            <div class="bg-white shadow-sm border border-gray-200 rounded-lg p-6">
                <div class="flex items-center mb-4">
                    <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h2 class="text-xl font-semibold text-gray-900">Informasi Invoice</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-600">Nomor Invoice</p>
                        <p class="font-semibold text-gray-900">
                            @if(isset($invoice) && $invoice && $invoice->invoice_number)
                                {{ $invoice->invoice_number }}
                            @elseif(isset($tripayTransaction['merchant_ref']) && $tripayTransaction['merchant_ref'])
                                {{ $tripayTransaction['merchant_ref'] }}
                            @else
                                INV-{{ date('Ymd') }}-{{ substr($tripayTransaction['reference'] ?? 'TEMP', -6) }}
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Referensi Pembayaran</p>
                        <p class="font-semibold text-gray-900">{{ $tripayTransaction['reference'] ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Tanggal Invoice</p>
                        <p class="font-semibold text-gray-900">{{ \Carbon\Carbon::now()->format('d M Y, H:i') }} WIB</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Status Invoice</p>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            {{ $tripayTransaction['status'] ?? 'Menunggu Pembayaran' }}
                        </span>
                    </div>
                    @if(isset($customerInfo['full_name']))
                        <div>
                            <p class="text-sm text-gray-600">Nama Customer</p>
                            <p class="font-semibold text-gray-900">{{ $customerInfo['full_name'] }}</p>
                        </div>
                        @endif
                    @if(isset($customerInfo['email']))
                    <div>
                        <p class="text-sm text-gray-600">Email Customer</p>
                        <p class="font-semibold text-gray-900">{{ $customerInfo['email'] }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Pembayaran</h2>
            
            @if(isset($tripayTransaction) && $tripayTransaction)
                <!-- Payment Information -->
                <div class="mb-8">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-blue-900 mb-4">Informasi Pembayaran</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">Nomor Referensi</p>
                                <p class="font-semibold text-gray-900">{{ $tripayTransaction['reference'] ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Total Pembayaran</p>
                                <p class="font-semibold text-gray-900">Rp {{ number_format($totalAmountWithFees ?? $tripayTransaction['amount'] ?? 0, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Metode Pembayaran</p>
                                <p class="font-semibold text-gray-900">{{ $channelDetails['name'] ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Status</p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Menunggu Pembayaran
                                </span>
                            </div>
                            @if(isset($tripayTransaction['expired_time']) && $tripayTransaction['expired_time'])
                            <div>
                                <p class="text-sm text-gray-600">Batas Waktu Pembayaran</p>
                                <p class="font-semibold text-red-600">
                                    {{ \Carbon\Carbon::createFromTimestamp($tripayTransaction['expired_time'])->format('d M Y, H:i') }} WIB
                                </p>
                                <p class="text-xs text-gray-500">
                                    ({{ \Carbon\Carbon::createFromTimestamp($tripayTransaction['expired_time'])->diffForHumans() }})
                                </p>
                            </div>
                            @endif
                            @if(isset($tripayTransaction['pay_code']) && $tripayTransaction['pay_code'])
                            <div class="md:col-span-2">
                                <p class="text-sm text-gray-600">Nomor Virtual Account</p>
                                <div class="flex items-center space-x-2">
                                    <p class="font-semibold text-lg text-gray-900 font-mono bg-gray-100 px-3 py-2 rounded border">{{ $tripayTransaction['pay_code'] }}</p>
                                    <button onclick="copyToClipboard('{{ $tripayTransaction['pay_code'] }}')" 
                                            class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="ml-1">Copy</span>
                                    </button>
                                </div>
                            </div>
                            @endif
                            @if(isset($tripayTransaction['instructions']) && count($tripayTransaction['instructions']) > 0)
                            <div class="md:col-span-2">
                                <button onclick="openPaymentGuideModal()" 
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent text-sm font-medium rounded-md text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Lihat Panduan Pembayaran
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Payment Countdown Timer -->
                @if(isset($tripayTransaction['expired_time']) && $tripayTransaction['expired_time'])
                <div class="mb-8">
                    <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-red-900">Segera Lakukan Pembayaran</h3>
                                <p class="text-red-700">Pembayaran akan kedaluwarsa pada: <strong>{{ \Carbon\Carbon::createFromTimestamp($tripayTransaction['expired_time'])->format('d M Y, H:i') }} WIB</strong></p>
                                <div class="mt-2">
                                    <div id="countdown-timer" class="text-2xl font-bold text-red-600" data-expired-time="{{ $tripayTransaction['expired_time'] }}">
                                        <!-- Countdown will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Payment Instructions - Collapsible -->
                @if(isset($tripayTransaction['instructions']) && count($tripayTransaction['instructions']) > 0)
                    <div class="mb-8">
                        <div class="bg-gray-50 border border-gray-200 rounded-lg">
                            <button onclick="togglePaymentInstructions('tripay')" 
                                    class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-900">Cara Pembayaran</h3>
                                <svg id="payment-instructions-icon-tripay" class="w-5 h-5 text-gray-500 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div id="payment-instructions-content-tripay" class="hidden border-t border-gray-200 p-6">
                                @foreach($tripayTransaction['instructions'] as $instruction)
                                    <div class="mb-4">
                                        <h4 class="font-semibold text-gray-900 mb-2">{!! $instruction['title'] !!}</h4>
                                        <ol class="list-decimal list-inside space-y-1 text-gray-700">
                                            @foreach($instruction['steps'] as $step)
                                                <li>{!! $step !!}</li>
                                            @endforeach
                                        </ol>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @elseif(isset($channelDetails['guide']))
                    <div class="mb-8">
                        <div class="bg-gray-50 border border-gray-200 rounded-lg">
                            <button onclick="togglePaymentInstructions('channel')" 
                                    class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-900">Cara Pembayaran</h3>
                                <svg id="payment-instructions-icon-channel" class="w-5 h-5 text-gray-500 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div id="payment-instructions-content-channel" class="hidden border-t border-gray-200 p-6">
                                {!! $channelDetails['guide'] !!}
                            </div>
                        </div>
                    </div>
                            
                            <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                                <div class="flex">
                                    <svg class="w-5 h-5 text-yellow-400 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-yellow-800">Penting!</p>
                                        <p class="text-sm text-yellow-700">Pastikan nominal yang dibayarkan sesuai dengan total pembayaran yang tertera. Pembayaran dengan nominal yang tidak sesuai akan ditolak secara otomatis.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                @endif

                <!-- QR Code Payment -->
                @if(isset($tripayTransaction) && (isset($tripayTransaction['qr_url']) || isset($tripayTransaction['qr_string'])))
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">QR Code Pembayaran</h3>
                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <div class="text-center">
                                @if(isset($tripayTransaction['qr_url']) && !empty($tripayTransaction['qr_url']))
                                    <div class="mb-4">
                                        <img src="{{ $tripayTransaction['qr_url'] }}" alt="QR Code Pembayaran" class="mx-auto max-w-xs">
                                    </div>
                                @elseif(isset($tripayTransaction['qr_string']) && !empty($tripayTransaction['qr_string']))
                                    <div class="mb-4">
                                        <div id="qr-code-container" class="mx-auto max-w-xs border rounded-lg bg-white p-4"></div>
                                    </div>
                                    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
                                    <script>
                                        // Generate QR code from qr_string
                                        document.addEventListener('DOMContentLoaded', function() {
                                            const qrContainer = document.getElementById('qr-code-container');
                                            if (qrContainer && typeof qrcode !== 'undefined') {
                                                const qr = qrcode(0, 'M');
                                                qr.addData("{{ $tripayTransaction['qr_string'] }}");
                                                qr.make();
                                                qrContainer.innerHTML = qr.createImgTag(4);
                                            }
                                        });
                                    </script>
                                @endif
                                
                                <p class="text-sm text-gray-600 mb-4">
                                    Scan QR code di atas menggunakan aplikasi mobile banking atau e-wallet Anda untuk melakukan pembayaran.
                                </p>
                                
                                @if(isset($tripayTransaction['qr_string']) && !empty($tripayTransaction['qr_string']))
                                    <div class="mt-4">
                                        <button onclick="copyToClipboard('{{ $tripayTransaction['qr_string'] }}')" 
                                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                            </svg>
                                            Salin QR String
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Order Summary -->

                <!-- Order Summary -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Pesanan</h3>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                        @if(isset($template))
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600">Template: {{ $template->name }}</span>
                                <span class="font-semibold text-green-600">Included</span>
                            </div>
                        @endif
                        
                        @if(isset($subscriptionPlan))
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600">{{ $subscriptionPlan->name }} ({{ $subscriptionPlan->billing_cycle }})</span>
                                <span class="font-semibold">Rp {{ number_format($subscriptionPlan->price, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        
                        @if(isset($domainInfo) && $domainInfo)
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600">Domain: {{ $domainInfo['domain_name'] ?? 'N/A' }}</span>
                                @if($domainInfo['domain_type'] === 'new' || $domainInfo['type'] === 'new')
                                    <span class="font-semibold">Rp {{ number_format($domainPrice ?? 0, 0, ',', '.') }}</span>
                                @else
                                    <span class="font-semibold text-green-600">{{ $domainInfo['domain_type'] === 'existing' ? 'Domain Existing' : 'Subdomain' }}</span>
                                @endif
                            </div>
                        @endif
                        
                        @if(isset($addons) && $addons->count() > 0)
                            @foreach($addons as $addon)
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-gray-600">{{ $addon->name }}</span>
                                    <span class="font-semibold">Rp {{ number_format($addon->price, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        @endif
                        
                        <hr class="my-4">
                        
                        <!-- Subtotal breakdown -->
                        @if(isset($subscriptionAmount) && $subscriptionAmount > 0)
                            <div class="flex justify-between items-center py-1 text-sm text-gray-600">
                                <span>Subtotal Subscription</span>
                                <span>Rp {{ number_format($subscriptionAmount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        
                        @if(isset($addonsAmount) && $addonsAmount > 0)
                            <div class="flex justify-between items-center py-1 text-sm text-gray-600">
                                <span>Subtotal Add-ons</span>
                                <span>Rp {{ number_format($addonsAmount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        
                        @if(isset($domainPrice) && $domainPrice > 0)
                            <div class="flex justify-between items-center py-1 text-sm text-gray-600">
                                <span>Subtotal Domain</span>
                                <span>Rp {{ number_format($domainPrice, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        
                        <!-- Customer Fee Information (only show customer fees, not merchant fees) -->
                        @if(isset($tripayTransaction['fee_customer']) && $tripayTransaction['fee_customer'] > 0)
                            <div class="flex justify-between items-center py-1 text-sm text-gray-600">
                                <span>Biaya Admin</span>
                                <span>Rp {{ number_format($tripayTransaction['fee_customer'], 0, ',', '.') }}</span>
                            </div>
                        @endif
                        
                        <hr class="my-2">
                        <div class="flex justify-between items-center font-bold text-lg">
                            <span>Total</span>
                            <span>Rp {{ number_format($totalAmountWithFees ?? $tripayTransaction['amount'] ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Customer Information -->
                @if(isset($customerInfo))
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Customer</h3>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600">Nama Lengkap</p>
                                    <p class="font-semibold text-gray-900">{{ $customerInfo['full_name'] ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Email</p>
                                    <p class="font-semibold text-gray-900">{{ $customerInfo['email'] ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Nomor Telepon</p>
                                    <p class="font-semibold text-gray-900">{{ $customerInfo['phone'] ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="flex justify-between">
                    <a href="{{ route('checkout.summary') }}" 
                       class="inline-flex items-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Kembali ke Ringkasan
                    </a>
                    
                    <div class="space-x-4">
                        <button onclick="checkPaymentStatus()" 
                                class="inline-flex items-center px-6 py-3 border border-blue-300 text-base font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Cek Status Pembayaran
                        </button>
                    </div>
                </div>
            @else
                <!-- No Payment Information -->
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada informasi pembayaran</h3>
                    <p class="mt-1 text-sm text-gray-500">Silakan kembali ke ringkasan untuk memilih metode pembayaran.</p>
                    <div class="mt-6">
                        <a href="{{ route('checkout.summary') }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Kembali ke Ringkasan
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show success message
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = `
            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span class="ml-1 text-green-600">Copied!</span>
        `;
        
        setTimeout(() => {
            button.innerHTML = originalText;
        }, 2000);
    }).catch(function(err) {
        console.error('Could not copy text: ', err);
        alert('Gagal menyalin ke clipboard');
    });
}

function checkPaymentStatus() {
    @if(isset($tripayTransaction['reference']))
        // Show loading state
        const button = event.target;
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = `
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Mengecek...
        `;

        // Make AJAX request to check payment status
        fetch('/api/payment/status/{{ $tripayTransaction["reference"] }}', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.status === 'PAID') {
                // Redirect to success page
                window.location.href = '{{ route("checkout.success") }}';
            } else if (data.success) {
                alert('Pembayaran belum diterima. Status: ' + data.status + '. Silakan coba lagi dalam beberapa menit.');
            } else {
                alert(data.message || 'Terjadi kesalahan saat mengecek status pembayaran.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mengecek status pembayaran.');
        })
        .finally(() => {
            // Restore button state
            button.disabled = false;
            button.innerHTML = originalText;
        });
    @else
        alert('Tidak ada informasi pembayaran yang tersedia.');
    @endif
}

// Countdown Timer
@if(isset($tripayTransaction['expired_time']) && $tripayTransaction['expired_time'])
function updateCountdown() {
    const expiredTime = {{ $tripayTransaction['expired_time'] }};
    const now = Math.floor(Date.now() / 1000);
    const timeLeft = expiredTime - now;
    
    const countdownElement = document.getElementById('countdown-timer');
    
    if (timeLeft <= 0) {
        countdownElement.innerHTML = '<span class="text-red-800">KEDALUWARSA</span>';
        // Optionally reload page or show expired message
        return;
    }
    
    const hours = Math.floor(timeLeft / 3600);
    const minutes = Math.floor((timeLeft % 3600) / 60);
    const seconds = timeLeft % 60;
    
    countdownElement.innerHTML = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
}

// Update countdown every second
updateCountdown();
setInterval(updateCountdown, 1000);
@endif

// Payment Guide Modal Functions
function openPaymentGuideModal() {
    document.getElementById('paymentGuideModal').classList.remove('hidden');
}

function closePaymentGuideModal() {
    document.getElementById('paymentGuideModal').classList.add('hidden');
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('paymentGuideModal');
    if (event.target === modal) {
        closePaymentGuideModal();
    }
});

// Payment Instructions Toggle Function
function togglePaymentInstructions(type = '') {
    const suffix = type ? '-' + type : '';
    const content = document.getElementById('payment-instructions-content' + suffix);
    const icon = document.getElementById('payment-instructions-icon' + suffix);
    
    if (content && icon) {
        if (content.classList.contains('hidden')) {
            content.classList.remove('hidden');
            icon.style.transform = 'rotate(180deg)';
        } else {
            content.classList.add('hidden');
            icon.style.transform = 'rotate(0deg)';
        }
    }
}
</script>
@endpush

<!-- Payment Guide Modal -->
@if(isset($tripayTransaction['instructions']) && count($tripayTransaction['instructions']) > 0)
<div id="paymentGuideModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Panduan Pembayaran
                </h3>
                <button onclick="closePaymentGuideModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="max-h-96 overflow-y-auto">
                @foreach($tripayTransaction['instructions'] as $instruction)
                    <div class="mb-6">
                        <h4 class="font-semibold text-gray-900 mb-3">{!! $instruction['title'] !!}</h4>
                        <ol class="list-decimal list-inside space-y-2 text-gray-700">
                            @foreach($instruction['steps'] as $step)
                                <li class="text-sm">{!! $step !!}</li>
                            @endforeach
                        </ol>
                    </div>
                @endforeach
                
                <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                    <div class="flex">
                        <svg class="w-5 h-5 text-yellow-400 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-yellow-800">Penting!</p>
                            <p class="text-sm text-yellow-700">Pastikan nominal yang dibayarkan sesuai dengan total pembayaran yang tertera. Pembayaran dengan nominal yang tidak sesuai akan ditolak secara otomatis.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end">
                <button onclick="closePaymentGuideModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endif

@endsection