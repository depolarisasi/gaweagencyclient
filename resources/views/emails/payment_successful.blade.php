@component('mail::message')
# Pembayaran Berhasil

Halo {{ $user->name }},

Pembayaran untuk invoice **{{ $invoice->invoice_number }}** telah berhasil diterima.

Total Dibayar: **Rp {{ number_format($invoice->payment_amount ?? $invoice->total_amount, 0, ',', '.') }}**
Tanggal Pembayaran: **{{ optional($invoice->paid_at)->format('d M Y H:i') }}**

@component('mail::button', ['url' => url('/client/invoices/'.$invoice->id)])
Lihat Invoice
@endcomponent

Terima kasih atas kepercayaan Anda.

Salam,
Tim Gawe Agency
@endcomponent