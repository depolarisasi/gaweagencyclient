@extends('layouts.app')

@section('title', 'Payment')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-green-50 to-blue-50">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">
                        <i class="fas fa-credit-card text-green-600 mr-3"></i>
                        Payment for Invoice {{ $invoice->invoice_number }}
                    </h1>
                    <p class="text-gray-600">Choose your preferred payment method</p>
                </div>
                <a href="{{ route('client.invoices.show', $invoice) }}" class="btn btn-outline">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Invoice
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Payment Methods -->
            <div class="lg:col-span-2">
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <h2 class="card-title text-xl font-bold mb-6">
                            <i class="fas fa-wallet text-primary"></i>
                            Select Payment Method
                        </h2>

                        @if($paymentChannels && count($paymentChannels) > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($paymentChannels as $channel)
                                    @if($channel['active'])
                                        <div class="payment-method-card border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-primary transition-colors"
                                             data-method="{{ $channel['code'] }}"
                                             data-name="{{ $channel['name'] }}">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-3">
                                                    @if(isset($channel['icon_url']))
                                                        <img src="{{ $channel['icon_url'] }}" alt="{{ $channel['name'] }}" class="w-8 h-8">
                                                    @else
                                                        <div class="w-8 h-8 bg-primary rounded flex items-center justify-center">
                                                            <i class="fas fa-credit-card text-white text-sm"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="font-medium text-gray-800">{{ $channel['name'] }}</div>
                                                        <div class="text-sm text-gray-600">{{ $channel['group'] }}</div>
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    @if($channel['total_fee']['flat'] > 0)
                                                        <div class="text-sm text-gray-600">
                                                            Fee: Rp {{ number_format($channel['total_fee']['flat'], 0, ',', '.') }}
                                                        </div>
                                                    @endif
                                                    @if($channel['total_fee']['percent'] > 0)
                                                        <div class="text-sm text-gray-600">
                                                            + {{ $channel['total_fee']['percent'] }}%
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>

                            <div class="mt-6">
                                <button id="payButton" class="btn btn-disabled btn-lg btn-block" disabled>
                                    <i class="fas fa-lock mr-2"></i>
                                    <span id="payButtonText">Pilih Metode Pembayaran</span>
                                </button>
                            </div>
                        @else
                            <div class="text-center py-8">
                                <div class="text-4xl text-gray-300 mb-4">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-600 mb-2">Payment Methods Unavailable</h3>
                                <p class="text-gray-500">Unable to load payment methods. Please try again later or contact support.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Invoice Summary -->
            <div class="lg:col-span-1">
                <div class="card bg-base-100 shadow-xl sticky top-4">
                    <div class="card-body">
                        <h3 class="card-title text-lg font-bold mb-4">
                            <i class="fas fa-receipt text-primary"></i>
                            Invoice Summary
                        </h3>

                        <div class="space-y-3 mb-4">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Invoice Number:</span>
                                <span class="font-medium">{{ $invoice->invoice_number }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Due Date:</span>
                                <span class="font-medium">{{ $invoice->due_date->format('d M Y') }}</span>
                            </div>
                            @if($invoice->due_date < now())
                                <div class="alert alert-error alert-sm">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <span class="text-sm">This invoice is overdue!</span>
                                </div>
                            @endif
                        </div>

                        <div class="divider"></div>

                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal:</span>
                                <span class="font-medium">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tax (PPN 11%):</span>
                                <span class="font-medium">Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</span>
                            </div>
                            <div class="border-t pt-2">
                                <div class="flex justify-between">
                                    <span class="text-lg font-bold text-gray-800">Total:</span>
                                    <span class="text-lg font-bold text-primary">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info alert-sm">
                            <i class="fas fa-info-circle"></i>
                            <div class="text-sm">
                                <p class="font-medium">Payment Information:</p>
                                <ul class="text-xs mt-1 space-y-1">
                                    <li>• Payment will be processed securely via Tripay</li>
                                    <li>• You will receive confirmation after payment</li>
                                    <li>• Project will be activated automatically</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Processing Modal -->
<div id="paymentModal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg mb-4">
            <i class="fas fa-credit-card text-primary mr-2"></i>
            Processing Payment
        </h3>
        <div id="paymentContent">
            <div class="flex items-center justify-center py-8">
                <span class="loading loading-spinner loading-lg text-primary"></span>
            </div>
            <p class="text-center text-gray-600">Creating payment transaction...</p>
        </div>
        <div class="modal-action">
            <button id="closeModal" class="btn btn-outline" style="display: none;">Close</button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.btn-disabled {
    background-color: #e5e7eb !important;
    color: #9ca3af !important;
    border-color: #d1d5db !important;
    cursor: not-allowed !important;
    pointer-events: none !important;
    opacity: 0.6 !important;
}

.btn-disabled:hover {
    background-color: #e5e7eb !important;
    color: #9ca3af !important;
    border-color: #d1d5db !important;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
<script>
let selectedMethod = null;

// Payment method selection
document.querySelectorAll('.payment-method-card').forEach(card => {
    card.addEventListener('click', function() {
        // Remove previous selection
        document.querySelectorAll('.payment-method-card').forEach(c => {
            c.classList.remove('border-primary', 'bg-primary/5');
            c.classList.add('border-gray-200');
        });
        
        // Add selection to clicked card
        this.classList.remove('border-gray-200');
        this.classList.add('border-primary', 'bg-primary/5');
        
        selectedMethod = this.dataset.method;
        const methodName = this.dataset.name;
        
        // Enable pay button
        const payButton = document.getElementById('payButton');
        const payButtonText = document.getElementById('payButtonText');
        
        // Remove disabled state completely
        payButton.disabled = false;
        payButton.removeAttribute('disabled');
        
        // Update classes
        payButton.classList.remove('btn-disabled');
        payButton.classList.add('btn-success');
        
        // Update content
        payButton.innerHTML = `
            <i class="fas fa-credit-card mr-2"></i>
            <span id="payButtonText">Bayar Sekarang dengan ${methodName}</span>
        `;
        
        // Remove inline styles that prevent interaction
        payButton.style.pointerEvents = 'auto';
        payButton.style.cursor = 'pointer';
    });
});

// Payment button click
document.getElementById('payButton').addEventListener('click', function() {
    if (!selectedMethod) return;
    
    // Show modal
    document.getElementById('paymentModal').classList.add('modal-open');
    
    // Create payment
    fetch('{{ route("client.invoices.payment.create", $invoice) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            payment_method: selectedMethod
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Handle successful payment creation
            if (data.data.checkout_url) {
                // Redirect payment (OVO, DANA, ShopeePay) - show countdown then redirect
                showRedirectCountdown(data.data.checkout_url);
            } else {
                // Direct payment (VA, QRIS, etc.) - show instructions in modal first
                showPaymentInstructions(data.data);
            }
        } else {
            showError(data.message || 'Failed to create payment');
        }
    })
    .catch(error => {
        console.error('Payment error:', error);
        showError('An error occurred while processing payment');
    });
});

function showPaymentInstructions(paymentData) {
    const content = document.getElementById('paymentContent');
    let html = '<div class="space-y-4">';
    
    html += `
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <div>
                <h4 class="font-bold">Pembayaran Berhasil Dibuat</h4>
                <p class="text-sm">Silakan lanjutkan pembayaran sesuai instruksi</p>
            </div>
        </div>
    `;
    
    if (paymentData.pay_code) {
        html += `
            <div class="text-center">
                <div class="text-sm text-gray-600 mb-2">Kode Pembayaran:</div>
                <div class="text-2xl font-bold text-primary font-mono">${paymentData.pay_code}</div>
                <button onclick="copyToClipboard('${paymentData.pay_code}')" class="btn btn-outline btn-sm mt-2">
                    <i class="fas fa-copy mr-1"></i> Salin Kode
                </button>
            </div>
        `;
    }
    
    if (paymentData.qr_string) {
        html += `
            <div class="text-center">
                <div class="text-sm text-gray-600 mb-2">Scan QR Code:</div>
                <div class="bg-white p-4 rounded-lg inline-block">
                    <div id="qrcode-${Date.now()}"></div>
                </div>
            </div>
        `;
    }
    
    if (paymentData.expired_time) {
        html += `
            <div class="alert alert-info">
                <i class="fas fa-clock"></i>
                <div class="text-sm">
                    <p class="font-medium">Batas waktu pembayaran:</p>
                    <p id="countdown">Menghitung...</p>
                </div>
            </div>
        `;
    }
    
    html += `
        <div class="text-center space-y-2">
            <button onclick="window.location.href='{{ route("client.invoices.payment.instructions", $invoice) }}'" class="btn btn-primary w-full">
                <i class="fas fa-list mr-2"></i>
                Lihat Instruksi Lengkap
            </button>
        </div>
    </div>`;
    
    content.innerHTML = html;
    
    // Generate QR Code if qr_string exists
    if (paymentData.qr_string) {
        const qrId = content.querySelector('[id^="qrcode-"]').id;
        const qr = qrcode(0, 'M');
        qr.addData(paymentData.qr_string);
        qr.make();
        document.getElementById(qrId).innerHTML = qr.createImgTag(4, 8);
    }
    
    // Show close button
    document.getElementById('closeModal').style.display = 'block';
    
    // Start countdown
    if (paymentData.expired_time) {
        startCountdown(paymentData.expired_time);
    }
    
    // Start payment status checking
    startPaymentStatusCheck();
}

function showRedirectCountdown(checkoutUrl) {
    // Show modal
    document.getElementById('paymentModal').classList.add('modal-open');
    
    const content = document.getElementById('paymentContent');
    let countdown = 5;
    
    function updateCountdown() {
        content.innerHTML = `
            <div class="text-center space-y-4">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <h4 class="font-bold">Redirecting to Payment</h4>
                        <p class="text-sm">You will be redirected to complete your payment</p>
                    </div>
                </div>
                <div class="text-6xl font-bold text-primary">${countdown}</div>
                <p class="text-gray-600">Redirecting in ${countdown} seconds...</p>
                <button onclick="window.location.href='${checkoutUrl}'" class="btn btn-primary">
                    <i class="fas fa-external-link-alt mr-2"></i>
                    Go to Payment Now
                </button>
            </div>
        `;
        
        if (countdown <= 0) {
            window.location.href = checkoutUrl;
        } else {
            countdown--;
            setTimeout(updateCountdown, 1000);
        }
    }
    
    updateCountdown();
}

function showError(message) {
    const content = document.getElementById('paymentContent');
    content.innerHTML = `
        <div class="alert alert-error">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <h4 class="font-bold">Payment Failed</h4>
                <p class="text-sm">${message}</p>
            </div>
        </div>
    `;
    document.getElementById('closeModal').style.display = 'block';
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        // Show success feedback
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check mr-1"></i> Copied!';
        btn.classList.add('btn-success');
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.classList.remove('btn-success');
        }, 2000);
    });
}

function startCountdown(expiredTime) {
    const countdownElement = document.getElementById('countdown');
    if (!countdownElement) return;
    
    const interval = setInterval(() => {
        const now = Math.floor(Date.now() / 1000);
        const timeLeft = expiredTime - now;
        
        if (timeLeft <= 0) {
            countdownElement.textContent = 'Expired';
            clearInterval(interval);
            return;
        }
        
        const hours = Math.floor(timeLeft / 3600);
        const minutes = Math.floor((timeLeft % 3600) / 60);
        const seconds = timeLeft % 60;
        
        countdownElement.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }, 1000);
}

function startPaymentStatusCheck() {
    const interval = setInterval(() => {
        fetch('{{ route("client.invoices.payment.status", $invoice) }}')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.status === 'PAID') {
                    clearInterval(interval);
                    // Redirect to invoice page
                    window.location.href = '{{ route("client.invoices.show", $invoice) }}';
                }
            })
            .catch(error => console.log('Status check failed:', error));
    }, 10000); // Check every 10 seconds
}

// Close modal
document.getElementById('closeModal').addEventListener('click', function() {
    document.getElementById('paymentModal').classList.remove('modal-open');
});
</script>
@endpush