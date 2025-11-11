@extends('layouts.client')

@section('title', 'Invoice Details')

@section('content')
    <div class="p-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">
                        <i class="fas fa-file-invoice text-green-600 mr-3"></i>
                        Invoice {{ $invoice->invoice_number }}
                    </h1>
                    <p class="text-gray-600">Invoice details and payment information</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('client.invoices.index') }}" class="btn btn-outline">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Invoices
                    </a>
                    @if(in_array($invoice->status, ['sent','overdue']))
                        <a href="{{ route('client.invoices.payment', $invoice) }}" class="btn btn-success">
                            <i class="fas fa-credit-card mr-2"></i>
                            Pay Now
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Invoice Details -->
            <div class="lg:col-span-2">
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <!-- Invoice Header -->
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800">{{ $invoice->invoice_number }}</h2>
                                <p class="text-gray-600">Invoice Date: {{ $invoice->created_at->format('d M Y') }}</p>
                                <p class="text-gray-600">Due Date: {{ $invoice->due_date->format('d M Y') }}</p>
                            </div>
                            <div class="text-right">
                                @if($invoice->status === 'sent')
                                    <div class="badge badge-warning badge-lg">Menunggu Pembayaran</div>
                                @elseif($invoice->status === 'paid')
                                    <div class="badge badge-success badge-lg">Paid</div>
                                @elseif($invoice->status === 'overdue')
                                    <div class="badge badge-error badge-lg">Overdue</div>
                                @elseif($invoice->status === 'cancelled')
                                    <div class="badge badge-error badge-lg">Cancelled</div>
                                @endif
                                
                                @if($invoice->is_renewal)
                                    <div class="badge badge-info mt-2">Renewal Invoice</div>
                                @endif
                            </div>
                        </div>

                        <!-- Customer Info -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-3">Bill To:</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="font-medium text-gray-800">{{ $invoice->user->name }}</p>
                                <p class="text-gray-600">{{ $invoice->user->email }}</p>
                                @if($invoice->user->phone)
                                    <p class="text-gray-600">{{ $invoice->user->phone }}</p>
                                @endif
                                @if($invoice->user->company)
                                    <p class="text-gray-600">{{ $invoice->user->company }}</p>
                                @endif
                            </div>
                        </div>

                        <!-- Invoice Items -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-3">Invoice Items:</h3>
                            <div class="overflow-x-auto">
                                <table class="table w-full">
                                    <thead>
                                        <tr>
                                            <th>Description</th>
                                            <th>Billing Period</th>
                                            <th class="text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($invoice->items && $invoice->items->count() > 0)
                                            @foreach($invoice->items as $item)
                                                <tr>
                                                    <td>
                                                        <div class="font-medium">{{ $item->description ?? 'Item' }}</div>
                                                        @if(!empty($item->meta) && isset($item->meta['template']))
                                                            <div class="text-sm text-gray-600">Template: {{ $item->meta['template']['name'] ?? '' }}</div>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(!empty($item->billing_period_start) && !empty($item->billing_period_end))
                                                            {{ \Carbon\Carbon::parse($item->billing_period_start)->format('d M Y') }} -
                                                            {{ \Carbon\Carbon::parse($item->billing_period_end)->format('d M Y') }}
                                                        @else
                                                            {{ ucfirst($item->billing_cycle ?? 'monthly') }}
                                                        @endif
                                                    </td>
                                                    <td class="text-right font-medium">
                                                        Rp {{ number_format($item->amount ?? 0, 0, ',', '.') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @elseif($invoice->order)
                                            @php($order = $invoice->order)
                                            <!-- Subscription/Base Service Row with Template info -->
                                            <tr>
                                                <td>
                                                    <div class="font-medium">{{ $order->product->name ?? 'Service' }}</div>
                                                    @if($order->template)
                                                        <div class="text-sm text-gray-600">Template: {{ $order->template->name }}</div>
                                                    @elseif($order->order_details && isset($order->order_details['template']))
                                                        <div class="text-sm text-gray-600">Template: {{ $order->order_details['template']['name'] }}</div>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($invoice->billing_period_start && $invoice->billing_period_end)
                                                        {{ $invoice->billing_period_start->format('d M Y') }} - {{ $invoice->billing_period_end->format('d M Y') }}
                                                    @else
                                                        {{ ucfirst($order->billing_cycle ?? 'monthly') }}
                                                    @endif
                                                </td>
                                                <td class="text-right font-medium">
                                                    Rp {{ number_format($order->subscription_amount ?? ($invoice->amount ?? 0), 0, ',', '.') }}
                                                </td>
                                            </tr>
                                            <!-- Domain Row (if any) -->
                                            @php($domainAmount = $order->domain_amount ?? 0)
                                            @if($domainAmount > 0)
                                                <tr>
                                                    <td>
                                                        <div class="font-medium">Domain</div>
                                                        @if(!empty($order->domain_name))
                                                            <div class="text-sm text-gray-600">{{ $order->domain_name }}</div>
                                                        @endif
                                                    </td>
                                                    <td>-</td>
                                                    <td class="text-right font-medium">Rp {{ number_format($domainAmount, 0, ',', '.') }}</td>
                                                </tr>
                                            @endif
                                            <!-- Add-ons Rows from orderAddons when invoice items are empty -->
                                            @if($order->orderAddons && $order->orderAddons->count() > 0)
                                                @foreach($order->orderAddons as $orderAddon)
                                                    <tr>
                                                        <td>
                                                            <div class="font-medium">{{ $orderAddon->addon_details['name'] ?? ($orderAddon->productAddon->name ?? 'Addon') }}</div>
                                                        </td>
                                                        <td>{{ $orderAddon->billing_cycle_label }}</td>
                                                        <td class="text-right font-medium">Rp {{ number_format($orderAddon->price ?? 0, 0, ',', '.') }}</td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        @else
                                            <tr>
                                                <td>Service</td>
                                                <td>-</td>
                                                <td class="text-right font-medium">
                                                    Rp {{ number_format($invoice->amount, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Invoice Total -->
                        <div class="border-t pt-4">
                            <div class="flex justify-end">
                                <div class="w-64">
                                    @php
                                        $subtotalCalc = $invoice->amount ?? 0;
                                        $feeCustomer = $invoice->fee_customer ?? 0;
                                    @endphp
                                    <div class="flex justify-between mb-2">
                                        <span class="text-gray-600">Subtotal:</span>
                                        <span class="font-medium">Rp {{ number_format($subtotalCalc, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-gray-600">Tax (PPN 11%):</span>
                                        <span class="font-medium">Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</span>
                                    </div>
                                    @if($feeCustomer > 0)
                                        <div class="flex justify-between mb-2">
                                            <span class="text-gray-600">Biaya Admin (Customer):</span>
                                            <span class="font-medium">Rp {{ number_format($feeCustomer, 0, ',', '.') }}</span>
                                        </div>
                                    @endif
                                    
                                    <!-- Hanya tampil Biaya Admin (Customer); Biaya Admin (Merchant) tidak ditampilkan -->
                                    
                                    <div class="border-t pt-2">
                                        <div class="flex justify-between">
                                            <span class="text-lg font-bold text-gray-800">Total:</span>
                                            <span class="text-lg font-bold text-primary">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Info -->
            <div class="lg:col-span-1">
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <h3 class="card-title text-lg font-bold mb-4">
                            <i class="fas fa-credit-card text-green-600"></i>
                            Payment Information
                        </h3>

                        @if(in_array($invoice->status, ['sent','overdue']))
                            <div class="alert alert-warning mb-4">
                                <i class="fas fa-exclamation-triangle"></i>
                                <div>
                                    <h4 class="font-bold">Payment Required</h4>
                                    <div class="text-sm">
                                        @if($invoice->due_date < now())
                                            This invoice is overdue. Please pay immediately.
                                        @else
                                            Due: {{ $invoice->due_date->format('d M Y') }}
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @php
                                $tripayData = $invoice->tripay_data ?? [];
                                $expiredAt = isset($tripayData['expired_time']) ? \Carbon\Carbon::createFromTimestamp($tripayData['expired_time']) : null;
                                $isStillActive = $expiredAt ? $expiredAt->isFuture() : false;
                                // Nama channel deskriptif
                                $paymentMethod = $invoice->payment_method ?? null;
                                $paymentName = $tripayData['payment_name'] ?? ($tripayData['channel_name'] ?? null);
                                $channelMap = [
                                    'BRIVA' => 'BRI Virtual Account',
                                    'BNIVA' => 'BNI Virtual Account',
                                    'BCAVA' => 'BCA Virtual Account',
                                    'MANDIRIVA' => 'Mandiri Virtual Account',
                                    'PERMATAVA' => 'Permata Virtual Account',
                                    'CIMBNIAGA' => 'CIMB Niaga Virtual Account',
                                    'MUAMALATVA' => 'Bank Muamalat Virtual Account',
                                    'QRIS' => 'QRIS',
                                    'ALFAMART' => 'Alfamart',
                                    'INDOMARET' => 'Indomaret',
                                    'OVO' => 'OVO',
                                    'GOPAY' => 'GoPay',
                                    'SHOPEEPAY' => 'ShopeePay',
                                    'DANA' => 'DANA',
                                    'LINKAJA' => 'LinkAja',
                                ];
                                $displayChannel = $paymentName ?: ($paymentMethod ? ($channelMap[$paymentMethod] ?? $paymentMethod) : 'Tidak diketahui');
                            @endphp

                            @if($invoice->tripay_reference && !empty($tripayData) && $isStillActive)
                                <div class="bg-white rounded-lg border p-4 mb-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <div>
                                            <div class="text-sm text-gray-600">Tripay Reference</div>
                                            <div class="font-mono text-xs">{{ $invoice->tripay_reference }}</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm text-gray-600">Expires</div>
                                            <div class="font-medium">{{ $expiredAt->format('d M Y, H:i') }} WIB</div>
                                            <div class="text-sm text-gray-600 mt-2">Sisa Waktu</div>
                                            <div id="tripay-countdown" class="font-medium text-red-600">—</div>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between mb-3">
                                        <div>
                                            <div class="text-sm text-gray-600">Metode Pembayaran</div>
                                            <div class="font-medium">{{ $displayChannel }}</div>
                                        </div>
                                    </div>

                                    @if(isset($tripayData['pay_code']))
                                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-3">
                                            <div class="flex items-center justify-between">
                                                <div class="text-center w-full">
                                                    <div class="text-xs text-gray-600">Virtual Account Number</div>
                                                    <div class="text-2xl font-bold text-primary font-mono tracking-wider">{{ $tripayData['pay_code'] }}</div>
                                                </div>
                                                <div class="ml-3">
                                                    <button type="button" class="btn btn-outline btn-sm" onclick="copyTripayPayCode('{{ $tripayData['pay_code'] }}')">
                                                        <i class="fas fa-copy mr-1"></i> Salin
                                                    </button>
                                                    <span id="tripay-copy-feedback" class="text-xs text-green-600 ml-2" style="display:none;">Tersalin</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if(isset($tripayData['qr_url']))
                                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 mb-3">
                                            <div class="text-center">
                                                <div class="text-xs text-gray-600">Scan QR untuk membayar</div>
                                                <img src="{{ $tripayData['qr_url'] }}" alt="QR Code" class="mx-auto max-w-48 border rounded" />
                                            </div>
                                        </div>
                                    @endif

                                    <div class="text-center">
                                        <button class="btn btn-outline btn-sm" onclick="checkInvoicePaymentStatus(this)">
                                            <i class="fas fa-sync-alt mr-1"></i> Check Payment Status
                                        </button>
                                        <a href="{{ route('client.invoices.payment.instructions', $invoice) }}" class="btn btn-outline btn-sm ml-2">
                                            <i class="fas fa-info-circle mr-1"></i> Lihat Instruksi
                                        </a>
                                    </div>
                                </div>
                            @else
                                <a href="{{ route('client.invoices.payment', $invoice) }}" class="btn btn-success btn-block mb-4">
                                    <i class="fas fa-credit-card mr-2"></i>
                                    Bayar Sekarang
                                </a>
                            @endif
                        @elseif($invoice->status === 'paid')
                            <div class="alert alert-success mb-4">
                                <i class="fas fa-check-circle"></i>
                                <div>
                                    <h4 class="font-bold">Payment Received</h4>
                                    <div class="text-sm">
                                        Paid on: {{ $invoice->paid_date ? $invoice->paid_date->format('d M Y H:i') : 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Payment Details -->
                        @if($invoice->payment_method)
                            <div class="space-y-3">
                                <div>
                                    <span class="text-sm text-gray-600">Payment Method:</span>
                                    <div class="font-medium">{{ strtoupper($invoice->payment_method) }}</div>
                                </div>
                                
                                @if($invoice->tripay_reference)
                                    <div>
                                        <span class="text-sm text-gray-600">Reference:</span>
                                        <div class="font-medium text-xs">{{ $invoice->tripay_reference }}</div>
                                    </div>
                                @endif
                                
                                @php
                                    $paidAmount = $invoice->payment_amount
                                        ?? ($invoice->tripay_data['amount_received'] ?? null);
                                @endphp
                                @if($paidAmount)
                                    <div>
                                        <span class="text-sm text-gray-600">Amount Paid:</span>
                                        <div class="font-medium">Rp {{ number_format($paidAmount, 0, ',', '.') }}</div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- Invoice Actions -->
                        <div class="divider"></div>
                        <div class="space-y-2">
                            <button onclick="window.print()" class="btn btn-outline btn-block">
                                <i class="fas fa-print mr-2"></i>
                                Print Invoice
                            </button>
                            <a href="{{ route('client.invoices.download', $invoice) }}" target="_blank" class="btn btn-outline btn-block">
                                <i class="fas fa-file-pdf mr-2"></i>
                                Buka PDF Invoice
                            </a>
                            <button type="button" class="btn btn-outline btn-block" onclick="printPdf('{{ route('client.invoices.download', $invoice) }}')">
                                <i class="fas fa-file-pdf mr-2"></i>
                                Cetak PDF Invoice
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
// Auto-refresh payment status for sent (unpaid) invoices
@if($invoice->status === 'sent')
setInterval(function() {
    fetch('{{ route("client.invoices.payment.status", $invoice) }}')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.status === 'PAID') {
                location.reload();
            }
        })
        .catch(error => console.log('Status check failed:', error));
}, 30000); // Check every 30 seconds
@endif

// Manual check from button in Tripay panel
function checkInvoicePaymentStatus(btn) {
    const originalHtml = btn ? btn.innerHTML : null;
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="loading loading-spinner loading-xs mr-1"></span> Memeriksa...';
    }

    fetch('{{ route("client.invoices.payment.status", $invoice) }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const status = (data.data && data.data.status) ? data.data.status : 'UNKNOWN';
                if (status === 'PAID') {
                    location.reload();
                } else if (status === 'EXPIRED') {
                    alert('Pembayaran kedaluwarsa. Silakan buat transaksi baru.');
                } else if (status === 'FAILED' || status === 'REFUND') {
                    alert('Transaksi tidak berhasil (' + status + '). Silakan coba metode lain.');
                } else {
                    alert('Status pembayaran: ' + status);
                }
            } else {
                alert('Gagal memeriksa status pembayaran');
            }
        })
        .catch(error => alert('Terjadi kesalahan saat memeriksa status: ' + error))
        .finally(() => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        });
}

function printPdf(url) {
    const w = window.open(url, '_blank');
    if (!w) {
        alert('Pop-up diblokir. Mohon izinkan pop-up untuk mencetak PDF.');
        return;
    }
    // Coba trigger print setelah viewer siap
    setTimeout(() => {
        try { w.focus(); w.print(); } catch (e) {
            console.log('Auto-print gagal, silakan gunakan ikon print di viewer.', e);
        }
    }, 1500);
}

// Countdown Tripay expiry
function startTripayCountdown(expiredTs) {
    const el = document.getElementById('tripay-countdown');
    if (!el) return;
    const expiryMs = expiredTs * 1000;
    function update() {
        const remainingMs = expiryMs - Date.now();
        if (remainingMs <= 0) {
            el.textContent = 'Kedaluwarsa';
            alert('Waktu pembayaran sudah habis. Silakan buat transaksi baru.');
            location.reload();
            return;
        }
        const totalSec = Math.floor(remainingMs / 1000);
        const h = Math.floor(totalSec / 3600);
        const m = Math.floor((totalSec % 3600) / 60);
        const s = totalSec % 60;
        el.textContent = (h > 0 ? (h + 'j ') : '') + (m + 'm ') + (s + 's');
    }
    update();
    setInterval(update, 1000);
}

// Initialize countdown when applicable
@if($invoice->status === 'sent' && $invoice->tripay_reference && !empty($tripayData) && isset($tripayData['expired_time']))
    startTripayCountdown({{ $tripayData['expired_time'] }});
@endif

// Copy VA pay_code to clipboard
function copyTripayPayCode(code) {
    const feedback = document.getElementById('tripay-copy-feedback');
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(code).then(() => {
            if (feedback) { feedback.style.display = 'inline'; }
            setTimeout(() => { if (feedback) { feedback.style.display = 'none'; } }, 1500);
        }).catch(() => {
            fallbackCopy(code, feedback);
        });
    } else {
        fallbackCopy(code, feedback);
    }
}

function fallbackCopy(text, feedback) {
    const ta = document.createElement('textarea');
    ta.value = text;
    document.body.appendChild(ta);
    ta.select();
    try { document.execCommand('copy'); } catch (e) {}
    document.body.removeChild(ta);
    if (feedback) { feedback.style.display = 'inline'; }
    setTimeout(() => { if (feedback) { feedback.style.display = 'none'; } }, 1500);
}
</script>
@endpush