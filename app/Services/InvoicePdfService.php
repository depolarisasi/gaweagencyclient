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
            $invoice->loadMissing([
                'user',
                'items',
                'order',
                'order.product',
                'order.template',
                'order.orderAddons.productAddon',
            ]);

            $html = View::make('pdf.invoice', [
                'invoice' => $invoice,
            ])->render();

            if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                $pdf = Pdf::loadHTML($html)->setPaper('A4', 'portrait');
                // Enable remote resources (images/fonts via http/https) when needed
                if (method_exists($pdf, 'setOptions')) {
                    $pdf->setOptions([
                        'isRemoteEnabled' => true,
                    ]);
                }
                return $pdf->output();
            }

            if (class_exists(\Dompdf\Dompdf::class)) {
                // Enable remote resources for Dompdf
                if (class_exists(\Dompdf\Options::class)) {
                    $options = new \Dompdf\Options();
                    $options->set('isRemoteEnabled', true);
                    $dompdf = new \Dompdf\Dompdf($options);
                } else {
                    $dompdf = new \Dompdf\Dompdf();
                }
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