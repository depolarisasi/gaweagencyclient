@extends('layouts.client')

@section('title', 'Payment Instructions')

@section('content')
    <div class="max-w-4xl mx-auto p-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Payment Instructions</h1>
                    <p class="text-gray-600 mt-1">Complete your payment using the details below</p>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500">Invoice #{{ $invoice->invoice_number }}</div>
                    <div class="text-2xl font-bold text-primary">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Payment Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Payment Method Info -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center mb-4">
                        @if($channelDetails && isset($channelDetails['icon_url']))
                            <img src="{{ $channelDetails['icon_url'] }}" alt="{{ $channelDetails['name'] ?? 'Payment Method' }}" class="w-12 h-12 mr-4">
                        @endif
                        <div>
                            <h2 class="text-xl font-semibold">{{ $channelDetails['name'] ?? 'Payment Method' }}</h2>
                            <p class="text-gray-600">{{ $channelDetails['group'] ?? '' }}</p>
                        </div>
                    </div>

                    <!-- Payment Code (Virtual Account) -->
                    @if(isset($tripayData['pay_code']))
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                        <div class="text-center">
                            <div class="text-sm text-gray-600 mb-2">Virtual Account Number:</div>
                            <div class="text-3xl font-bold text-primary font-mono tracking-wider">{{ $tripayData['pay_code'] }}</div>
                            <button onclick="copyToClipboard('{{ $tripayData['pay_code'] }}', this)" class="btn btn-outline btn-sm mt-3">
                                <i class="fas fa-copy mr-1"></i> Copy Number
                            </button>
                        </div>
                    </div>
                    @endif

                    <!-- QR Code -->
                    @if(isset($tripayData['qr_url']) || isset($tripayData['qr_string']))
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                        <div class="text-center">
                            <div class="text-sm text-gray-600 mb-4">Scan QR Code to Pay:</div>
                            @if(isset($tripayData['qr_url']))
                                <img src="{{ $tripayData['qr_url'] }}" alt="QR Code" class="mx-auto max-w-64 border rounded-lg">
                            @elseif(isset($tripayData['qr_string']))
                                <div id="qrcode" class="mx-auto max-w-64 border rounded-lg bg-white p-4"></div>
                            @endif
                            <p class="text-xs text-gray-500 mt-2">Open your mobile banking or e-wallet app and scan this QR code</p>
                        </div>
                    </div>
                    @endif

                    <!-- Payment Instructions -->
                    @if(isset($tripayData['instructions']) && is_array($tripayData['instructions']))
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <h3 class="font-semibold text-gray-900 mb-3">
                            <i class="fas fa-info-circle text-yellow-600 mr-2"></i>
                            Payment Instructions
                        </h3>
                        <div class="space-y-3">
                            @foreach($tripayData['instructions'] as $instruction)
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-6 h-6 bg-yellow-600 text-white rounded-full flex items-center justify-center text-xs font-bold mr-3 mt-0.5">
                                    {{ $loop->iteration }}
                                </div>
                                <div class="text-sm text-gray-700">
                                    <div class="font-medium">{{ $instruction['title'] ?? '' }}</div>
                                    @if(isset($instruction['steps']) && is_array($instruction['steps']))
                                        <ul class="mt-1 space-y-1">
                                            @foreach($instruction['steps'] as $step)
                                                <li class="text-gray-600">• {{ $step }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Payment Status Check -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">
                        <i class="fas fa-sync-alt text-blue-600 mr-2"></i>
                        Payment Status
                    </h3>
                    <div id="paymentStatus" class="text-center py-4">
                        <div class="inline-flex items-center px-4 py-2 bg-yellow-100 text-yellow-800 rounded-full">
                            <i class="fas fa-clock mr-2"></i>
                            Waiting for Payment
                        </div>
                        <p class="text-sm text-gray-600 mt-2">We'll automatically update when payment is received</p>
                    </div>
                    <div class="text-center mt-4">
                        <button onclick="checkPaymentStatus(this)" class="btn btn-outline btn-sm">
                            <i class="fas fa-refresh mr-1"></i> Check Status
                        </button>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Countdown Timer -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">
                        <i class="fas fa-clock text-red-600 mr-2"></i>
                        Payment Deadline
                    </h3>
                    <div class="text-center">
                        <div id="countdown" class="text-2xl font-bold text-red-600 mb-2">
                            Calculating...
                        </div>
                        <div class="text-sm text-gray-600">
                            Expires: {{ \Carbon\Carbon::parse($tripayData['expired_time'])->format('d M Y, H:i') }} WIB
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Order Summary</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal</span>
                            <span>Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</span>
                        </div>
                        @if($invoice->tax_amount > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Tax</span>
                            <span>Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        @if(isset($tripayData['total_fee']) && is_array($tripayData['total_fee']))
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Payment Fee</span>
                            <span>Rp {{ number_format(($tripayData['total_fee']['customer'] ?? 0) + ($tripayData['total_fee']['merchant'] ?? 0), 0, ',', '.') }}</span>
                        </div>
                        @endif
                        <div class="border-t pt-3">
                            <div class="flex justify-between font-semibold">
                                <span>Total</span>
                                <span class="text-primary">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Help -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">
                        <i class="fas fa-question-circle text-blue-600 mr-2"></i>
                        Need Help?
                    </h3>
                    <div class="space-y-3 text-sm">
                        <div>
                            <div class="font-medium text-gray-900">Payment Issues</div>
                            <div class="text-gray-600">Contact our support team if you encounter any problems</div>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">Processing Time</div>
                            <div class="text-gray-600">Payments are usually processed within 1-5 minutes</div>
                        </div>
                        <a href="#" class="btn btn-outline btn-sm w-full mt-3">
                            <i class="fas fa-headset mr-1"></i> Contact Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
<script>
// Generate QR Code if qr_string exists
@if(isset($tripayData['qr_string']) && !isset($tripayData['qr_url']))
document.addEventListener('DOMContentLoaded', function() {
    const qr = qrcode(0, 'M');
    qr.addData('{{ $tripayData['qr_string'] }}');
    qr.make();
    document.getElementById('qrcode').innerHTML = qr.createImgTag(4);
});
@endif

// Countdown Timer
function startCountdown() {
    // Tripay memberikan expired_time dalam detik UNIX, konversi ke ms
    const expiredTime = ({{ $tripayData['expired_time'] }} * 1000);
    
    function updateCountdown() {
        const now = new Date().getTime();
        const distance = expiredTime - now;
        
        if (distance < 0) {
            document.getElementById('countdown').innerHTML = 'EXPIRED';
            document.getElementById('countdown').className = 'text-2xl font-bold text-red-600 mb-2';
            return;
        }
        
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
        document.getElementById('countdown').innerHTML = 
            String(hours).padStart(2, '0') + ':' + 
            String(minutes).padStart(2, '0') + ':' + 
            String(seconds).padStart(2, '0');
    }
    
    updateCountdown();
    setInterval(updateCountdown, 1000);
}

// Copy to clipboard function
function copyToClipboard(text, btn) {
    const originalText = btn ? btn.innerHTML : null;
    const applyOk = () => {
        if (!btn) return;
        btn.innerHTML = '<i class="fas fa-check mr-1"></i> Copied!';
        btn.classList.add('btn-success');
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.classList.remove('btn-success');
        }, 2000);
    };
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(applyOk).catch(applyOk);
    } else {
        // Fallback execCommand
        const ta = document.createElement('textarea');
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy'); } catch (e) {}
        document.body.removeChild(ta);
        applyOk();
    }
}

// Check payment status
function checkPaymentStatus(btn) {
    const originalHtml = btn ? btn.innerHTML : null;
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="loading loading-spinner loading-xs mr-1"></span> Checking...';
    }
    fetch('{{ route("client.invoices.payment.status", $invoice) }}')
        .then(response => response.json())
        .then(data => {
            if (data && data.success) {
                const status = (data.data && data.data.status) ? data.data.status : 'UNKNOWN';
                const statusElement = document.getElementById('paymentStatus');
                if (!statusElement) return;
                if (status === 'PAID') {
                    statusElement.innerHTML = `
                        <div class="inline-flex items-center px-4 py-2 bg-green-100 text-green-800 rounded-full">
                            <i class="fas fa-check-circle mr-2"></i>
                            Payment Successful
                        </div>
                        <p class="text-sm text-gray-600 mt-2">Redirecting to dashboard...</p>
                    `;
                    setTimeout(() => { window.location.href = '{{ route("client.dashboard") }}'; }, 1500);
                } else if (status === 'EXPIRED') {
                    statusElement.innerHTML = `
                        <div class="inline-flex items-center px-4 py-2 bg-red-100 text-red-800 rounded-full">
                            <i class="fas fa-times-circle mr-2"></i>
                            Payment Expired
                        </div>
                        <p class="text-sm text-gray-600 mt-2">Please create a new payment</p>
                    `;
                } else if (status === 'FAILED' || status === 'REFUND') {
                    statusElement.innerHTML = `
                        <div class="inline-flex items-center px-4 py-2 bg-red-100 text-red-800 rounded-full">
                            <i class="fas fa-times-circle mr-2"></i>
                            Payment ${status}
                        </div>
                        <p class="text-sm text-gray-600 mt-2">Please try another method</p>
                    `;
                } else {
                    statusElement.innerHTML = `
                        <div class="inline-flex items-center px-4 py-2 bg-yellow-100 text-yellow-800 rounded-full">
                            <i class="fas fa-clock mr-2"></i>
                            Waiting for Payment (${status})
                        </div>
                        <p class="text-sm text-gray-600 mt-2">We'll automatically update when payment is received</p>
                    `;
                }
            }
        })
        .catch(error => { console.error('Error checking payment status:', error); })
        .finally(() => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        });
}

// Auto-check payment status every 30 seconds
setInterval(checkPaymentStatus, 30000);

// Start countdown when page loads
document.addEventListener('DOMContentLoaded', function() {
    startCountdown();
});
</script>
@endsection