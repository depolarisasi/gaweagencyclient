<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        .container { width: 100%; }
        .header { margin-bottom: 20px; }
        .title { font-size: 20px; font-weight: bold; }
        .brand { font-size: 12px; color: #666; margin-top: 6px; }
        .meta { margin-top: 8px; }
        .row { display: flex; justify-content: space-between; gap: 16px; }
        .col { width: 48%; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #f7f7f7; text-align: left; }
        .text-right { text-align: right; }
        .footer { margin-top: 24px; font-size: 11px; color: #666; }
        .small { font-size: 11px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @php
                $status = strtolower($invoice->status ?? 'sent');
                $map = ['paid' => 'LUNAS', 'sent' => 'BELUM LUNAS', 'overdue' => 'KEDALUWARSA', 'cancelled' => 'DIBATALKAN'];
                $statusLabel = $map[$status] ?? strtoupper($status);
                $statusBg = match($status) {
                    'paid' => '#16a34a', // green
                    'overdue' => '#dc2626', // red
                    'cancelled' => '#6b7280', // gray
                    default => '#f59e0b', // amber for pending/sent
                };
            @endphp
            <div class="row" style="align-items:center; justify-content:space-between;">
                <div class="title">INVOICE #{{ $invoice->invoice_number }}</div>
                <div style="background: {{ $statusBg }}; color:#fff; padding:4px 8px; font-weight:600; border-radius:4px; font-size:12px;">
                    STATUS: {{ $statusLabel }}
                </div>
            </div>
            <div class="brand">
                <div>{{ config('app.company_name', config('app.name')) }}</div>
                @if(config('app.company_address'))
                    <div>{{ config('app.company_address') }}</div>
                @endif
                <div>
                    @if(config('app.company_phone'))Telp: {{ config('app.company_phone') }}@endif
                    @if(config('app.company_email')) &middot; {{ config('app.company_email') }}@endif
                    @if(config('app.company_website', config('app.url'))) &middot; {{ config('app.company_website', config('app.url')) }}@endif
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <strong>DITAGIHKAN KEPADA (BILL TO)</strong><br>
                {{ $invoice->user->name ?? 'Client' }}<br>
                {{ $invoice->user->email ?? '-' }}
            </div>
            <div class="col">
                <strong>RINCIAN INVOICE</strong><br>
                Nomor: {{ $invoice->invoice_number }}<br>
                Terbit: {{ optional($invoice->created_at)->format('d M Y') }}<br>
                Jatuh Tempo: {{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') : '-' }}
                @if($invoice->order)
                    <br>Order #: {{ $invoice->order->order_number ?? $invoice->order->id }}
                    <br>Produk: {{ $invoice->order->product->name ?? 'Service' }}
                    @if($invoice->order->template)
                        <br>Template: {{ $invoice->order->template->name }}
                    @endif
                @endif
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Deskripsi</th>
                    <th>QTY</th>
                    <th>Harga Satuan (Rp)</th>
                    <th class="text-right">Jumlah (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @if($invoice->items && $invoice->items->count() > 0)
                    @foreach($invoice->items as $item)
                        @php($qty = $item->quantity ?: 1)
                        @php($unitPrice = ($item->amount ?? 0) / max($qty,1))
                        <tr>
                            <td>
                                <div style="font-weight:600;">{{ $item->description ?? 'Item' }}</div>
                                <div class="small" style="color:#666;">
                                    @if(!empty($item->billing_period_start) && !empty($item->billing_period_end))
                                        Periode: {{ \Carbon\Carbon::parse($item->billing_period_start)->format('d M Y') }} - {{ \Carbon\Carbon::parse($item->billing_period_end)->format('d M Y') }}
                                    @elseif(!empty($item->billing_cycle))
                                        Siklus: {{ ucfirst($item->billing_cycle) }}
                                    @endif
                                </div>
                            </td>
                            <td>{{ $qty }}</td>
                            <td>{{ formatIDR($unitPrice) }}</td>
                            <td class="text-right">{{ formatIDR($item->amount ?? 0) }}</td>
                        </tr>
                    @endforeach
                @else
                    @php($order = $invoice->order)
                    <!-- Subscription/Base Service Row with Template info -->
                    <tr>
                        <td>
                            {{ ($order && $order->product) ? $order->product->name : ($invoice->description ?? 'Layanan') }}
                            @if($order && $order->template)
                                <div class="small">Template: {{ $order->template->name }}</div>
                            @elseif($order && $order->order_details && isset($order->order_details['template']))
                                <div class="small">Template: {{ $order->order_details['template']['name'] }}</div>
                            @endif
                            @if($invoice->billing_period_start && $invoice->billing_period_end)
                                <div class="small">Periode: {{ $invoice->billing_period_start->format('d M Y') }} - {{ $invoice->billing_period_end->format('d M Y') }}</div>
                            @elseif($order?->billing_cycle)
                                <div class="small">Siklus: {{ ucfirst($order->billing_cycle) }}</div>
                            @endif
                        </td>
                        <td>1</td>
                        <td>{{ formatIDR($order?->subscription_amount ?? ($invoice->amount ?? 0)) }}</td>
                        <td class="text-right">{{ formatIDR($order?->subscription_amount ?? ($invoice->amount ?? 0)) }}</td>
                    </tr>

                    <!-- Domain Row (if any) -->
                    @php($domainAmount = $order?->domain_amount ?? 0)
                    @if($domainAmount > 0)
                        <tr>
                            <td>
                                Domain
                                @if(!empty($order->domain_name))
                                    <div class="small">{{ $order->domain_name }}</div>
                                @endif
                            </td>
                            <td>1</td>
                            <td>{{ formatIDR($domainAmount) }}</td>
                            <td class="text-right">{{ formatIDR($domainAmount) }}</td>
                        </tr>
                    @endif

                    <!-- Add-ons Rows from orderAddons when invoice items are empty -->
                    @if($order && $order->orderAddons && $order->orderAddons->count() > 0)
                        @foreach($order->orderAddons as $orderAddon)
                            <tr>
                                <td>
                                    {{ $orderAddon->addon_details['name'] ?? ($orderAddon->productAddon->name ?? 'Addon') }}
                                    <div class="small">{{ $orderAddon->billing_cycle_label }}</div>
                                </td>
                                <td>1</td>
                                <td>{{ formatIDR($orderAddon->price ?? 0) }}</td>
                                <td class="text-right">{{ formatIDR($orderAddon->price ?? 0) }}</td>
                            </tr>
                        @endforeach
                    @endif
                @endif
                @php($subtotal = $invoice->amount ?? 0)
                @php($tax = $invoice->tax_amount ?? 0)
                @php($total = $invoice->total_amount ?? ($subtotal + $tax))
                @php($discount = max(($subtotal + $tax) - $total, 0))
                <tr>
                    <td colspan="3">Subtotal</td>
                    <td class="text-right">{{ formatIDR($subtotal) }}</td>
                </tr>
                @if($discount > 0)
                <tr>
                    <td colspan="3">Diskon/Potongan</td>
                    <td class="text-right">-{{ formatIDR($discount) }}</td>
                </tr>
                @endif
                <tr>
                    <td colspan="3">Pajak</td>
                    <td class="text-right">{{ formatIDR($invoice->tax_amount ?? 0) }}</td>
                </tr>
                <tr>
                    <td colspan="3"><strong>Total</strong></td>
                    <td class="text-right"><strong>{{ formatIDR($total) }}</strong></td>
                </tr>
            </tbody>
        </table>

        <div class="footer">
            <p>Terima kasih atas kepercayaan Anda kepada {{ config('app.name') }}.</p>
            @if($invoice->payment_method)
                <p class="small">Metode Pembayaran: {{ strtoupper($invoice->payment_method) }}</p>
            @endif
            @if(function_exists('terbilang_idr'))
                <p class="small">Terbilang: {{ terbilang_idr($total ?? ($invoice->total_amount ?? 0)) }} rupiah</p>
            @endif
            <p class="small" style="margin-top:8px; font-weight:600;">Payment Details</p>
            <p class="small">Silakan selesaikan pembayaran sebelum tanggal jatuh tempo melalui kanal Tripay yang tersedia.</p>
            <p class="small">Jika kanal pembayaran kadaluarsa, buat ulang pembayaran dari halaman invoice atau ikuti instruksi pembayaran terbaru.</p>
            <p class="small" style="margin-top:8px; font-weight:600;">Notes/Terms</p>
            <p class="small">Mohon cantumkan nomor invoice <strong>{{ $invoice->invoice_number }}</strong> pada bukti pembayaran.</p>
            <p class="small">Layanan akan diaktifkan setelah pembayaran tervalidasi oleh sistem.</p>
            <p class="small">Jika membutuhkan bantuan, silakan hubungi support kami melalui email atau telepon yang tertera.</p>
            <!-- Biaya admin pihak ketiga tidak ditampilkan dalam ringkasan PDF -->
        </div>
    </div>
</body>
</html>