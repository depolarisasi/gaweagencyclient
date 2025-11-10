<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Invoice;

class InvoiceReminderBeforeDueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Invoice $invoice, public int $daysBefore)
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
            ->subject('Pengingat Pembayaran (H-' . $this->daysBefore . '): #' . $invoice->invoice_number)
            ->greeting('Halo,')
            ->line('Ini adalah pengingat pembayaran untuk invoice Anda.')
            ->line('Nomor Invoice: #' . $invoice->invoice_number)
            ->line('Total Tagihan: Rp ' . number_format($invoice->total_amount, 0, ',', '.'))
            ->line('Jatuh Tempo: ' . optional($invoice->due_date)->format('d M Y'))
            ->action('Bayar Sekarang', url('/invoices/' . $invoice->id))
            ->line('Mohon selesaikan pembayaran sebelum jatuh tempo.');
    }
}