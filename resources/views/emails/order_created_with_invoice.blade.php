@component('mail::message')
# Order Berhasil Dibuat

Halo {{ isset($user) && $user ? $user->name : (isset($invoice) && $invoice->user ? $invoice->user->name : 'Pelanggan') }},

Order Anda ({{ $order->order_type }}) telah berhasil dibuat. Kami juga melampirkan invoice untuk pembayaran.

Nomor Invoice: **{{ $invoice->invoice_number }}**
Total Pembayaran: **Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}**
Jatuh Tempo: **{{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}**

@component('mail::button', ['url' => url('/client/invoices/'.$invoice->id)])
Lihat Invoice
@endcomponent

Silakan lakukan pembayaran sesuai instruksi pada halaman invoice.

Terima kasih,
Tim Gawe Agency
@endcomponent