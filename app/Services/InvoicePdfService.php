<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoicePdfService
{
    /**
     * Generate PDF binary for given invoice, or return null on failure.
     */
    public function generate(Invoice $invoice): ?string
    {
        try {
            $invoice->loadMissing(['user', 'order', 'order.product']);

            $html = View::make('pdf.invoice', [
                'invoice' => $invoice,
            ])->render();

            if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                $pdf = Pdf::loadHTML($html)->setPaper('A4', 'portrait');
                return $pdf->output();
            }

            if (class_exists(\Dompdf\Dompdf::class)) {
                $dompdf = new \Dompdf\Dompdf();
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                return $dompdf->output();
            }

            return null;
        } catch (\Throwable $e) {
            Log::error('Gagal generate PDF invoice', [
                'invoice_id' => $invoice->id,
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }
}