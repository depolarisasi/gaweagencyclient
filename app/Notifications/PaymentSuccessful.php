<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Invoice;
use App\Services\InvoicePdfService;

class PaymentSuccessful extends Notification
{
    use Queueable;

    public function __construct(
        protected Invoice $invoice
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Pembayaran Berhasil untuk Invoice #' . $this->invoice->invoice_number)
            ->markdown('emails.payment_successful', [
                'user' => $notifiable,
                'invoice' => $this->invoice,
            ]);

        // Lampirkan PDF invoice jika tersedia
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
            \Log::warning('Gagal melampirkan PDF invoice (payment successful)', [
                'invoice_id' => $this->invoice->id,
                'message' => $e->getMessage(),
            ]);
        }

        return $mail;
    }
}