<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Invoice;

class InvoiceReminderAfterDueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Invoice $invoice, public int $daysAfter)
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
            ->subject('Pengingat Tertunda (H+' . $this->daysAfter . '): #' . $invoice->invoice_number)
            ->greeting('Halo,')
            ->line('Invoice Anda telah melewati jatuh tempo dan belum dibayar.')
            ->line('Nomor Invoice: #' . $invoice->invoice_number)
            ->line('Total Tagihan: Rp ' . number_format($invoice->total_amount, 0, ',', '.'))
            ->line('Jatuh Tempo: ' . optional($invoice->due_date)->format('d M Y'))
            ->action('Bayar Sekarang', url('/invoices/' . $invoice->id))
            ->line('Mohon segera lakukan pembayaran untuk menghindari suspensi.');
    }
}