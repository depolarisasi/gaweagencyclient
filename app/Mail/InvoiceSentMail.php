<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceSentMail extends Mailable
{
    use Queueable, SerializesModels;

    public Invoice $invoice;

    /**
     * Create a new message instance.
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = 'Invoice #' . ($this->invoice->invoice_number ?? $this->invoice->id);

        return $this->subject($subject)
            ->view('emails.invoice-sent')
            ->with([
                'invoice' => $this->invoice,
            ]);
    }
}