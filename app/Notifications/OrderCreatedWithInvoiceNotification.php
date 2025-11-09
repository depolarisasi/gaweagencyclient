<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Order;
use App\Models\Invoice;
use App\Services\InvoicePdfService;

class OrderCreatedWithInvoiceNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Order $order,
        protected Invoice $invoice
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Order Baru & Invoice #' . $this->invoice->invoice_number)
            ->markdown('emails.order_created_with_invoice', [
                'user' => $notifiable,
                'order' => $this->order,
                'invoice' => $this->invoice,
            ]);

        // Lampirkan PDF invoice jika service tersedia
        try {
            if (class_exists(InvoicePdfService::class)) {
                $pdfService = app(InvoicePdfService::class);
                $pdfData = $pdfService->generate($this->invoice);
                if ($pdfData) {
                    $mail->attachData($pdfData, 'Invoice-' . $this->invoice->invoice_number . '.pdf', [
                        'mime' => 'application/pdf'
                    ]);
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('Gagal melampirkan PDF invoice', [
                'invoice_id' => $this->invoice->id,
                'message' => $e->getMessage(),
            ]);
        }

        return $mail;
    }
}