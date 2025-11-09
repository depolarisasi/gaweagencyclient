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
        .meta { margin-top: 8px; }
        .row { display: flex; justify-content: space-between; }
        .col { width: 48%; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #f2f2f2; text-align: left; }
        .text-right { text-align: right; }
        .footer { margin-top: 24px; font-size: 11px; color: #666; }
        .small { font-size: 11px; }
    </style>
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="title">Invoice {{ $invoice->invoice_number }}</div>
            <div class="meta small">
                Tanggal: {{ optional($invoice->created_at)->format('d M Y') }}<br>
                Jatuh Tempo: {{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') : '-' }}<br>
                Status: {{ ucfirst($invoice->status) }}
            </div>
        </div>

        <div class="row">
            <div class="col">
                <strong>Tagihan Kepada:</strong><br>
                {{ $invoice->user->name ?? 'Client' }}<br>
                {{ $invoice->user->email ?? '-' }}
            </div>
            <div class="col">
                <strong>Informasi Order:</strong><br>
                @if($invoice->order)
                    Order #: {{ $invoice->order->order_number ?? $invoice->order->id }}<br>
                    Produk: {{ $invoice->order->product->name ?? 'Service' }}
                @else
                    -
                @endif
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Deskripsi</th>
                    <th class="text-right">Jumlah (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $invoice->description ?? (($invoice->order && $invoice->order->product) ? $invoice->order->product->name : 'Layanan') }}</td>
                    <td class="text-right">{{ number_format($invoice->amount ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Pajak</td>
                    <td class="text-right">{{ number_format($invoice->tax_amount ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td><strong>Total</strong></td>
                    <td class="text-right"><strong>{{ number_format($invoice->total_amount ?? 0, 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>

        <div class="footer">
            <p>Terima kasih atas kepercayaan Anda kepada Gawe Agency.</p>
            @if($invoice->payment_method)
                <p class="small">Metode Pembayaran: {{ strtoupper($invoice->payment_method) }}</p>
            @endif
        </div>
    </div>
</body>
</html>