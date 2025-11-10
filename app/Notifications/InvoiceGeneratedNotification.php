<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Invoice;

class InvoiceGeneratedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Invoice $invoice)
    {
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $invoice = $this->invoice;
        return (new MailMessage)
            ->subject('Invoice Baru: #' . $invoice->invoice_number)
            ->greeting('Halo,')
            ->line('Invoice baru telah dibuat untuk pembaruan layanan Anda.')
            ->line('Nomor Invoice: #' . $invoice->invoice_number)
            ->line('Total Tagihan: Rp ' . number_format($invoice->total_amount, 0, ',', '.'))
            ->line('Jatuh Tempo: ' . optional($invoice->due_date)->format('d M Y'))
            ->action('Lihat & Bayar Invoice', url('/invoices/' . $invoice->id))
            ->line('Terima kasih telah menggunakan layanan kami.');
    }
}