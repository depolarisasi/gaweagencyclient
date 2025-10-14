<?php

namespace App\Livewire;

use App\Models\Invoice;
use App\Services\TripayService;
use Livewire\Component;

class InvoiceShow extends Component
{
    public $invoice;
    public $paymentUrl;

    public function mount(Invoice $invoice)
    {
        $this->invoice = $invoice;
        $this->paymentUrl = (new TripayService())->createPaymentUrl($invoice);
    }

    public function render()
    {
        return view('livewire.invoice-show');
    }
}
