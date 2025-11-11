<p>Halo {{ optional($invoice->user)->name }},</p>

<p>Invoice #{{ $invoice->invoice_number ?? $invoice->id }} telah dikirim.</p>

<p>
    Total: Rp {{ number_format($invoice->total_amount ?? $invoice->amount ?? 0, 0, ',', '.') }}<br>
    @if(!empty($invoice->due_date))
        Jatuh tempo: {{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}
    @endif
</p>

<p>Anda dapat melihat invoice di akun Anda.</p>