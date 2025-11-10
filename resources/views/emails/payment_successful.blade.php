@component('mail::message')
# Pembayaran Berhasil

Halo {{ $user->name }},

Pembayaran untuk invoice **{{ $invoice->invoice_number }}** telah berhasil diterima.

Total Dibayar: **Rp {{ number_format(($invoice->tripay_data['amount_received'] ?? $invoice->total_amount), 0, ',', '.') }}**
Tanggal Pembayaran: **{{ optional($invoice->paid_date)->format('d M Y H:i') ?? (isset($invoice->tripay_data['paid_at']) ? \Carbon\Carbon::parse($invoice->tripay_data['paid_at'])->format('d M Y H:i') : '-') }}**

@component('mail::button', ['url' => url('/client/invoices/'.$invoice->id)])
Lihat Invoice
@endcomponent

Terima kasih atas kepercayaan Anda.

Salam,
Tim Gawe Agency
@endcomponent