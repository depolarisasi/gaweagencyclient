<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Project;
use App\Services\TripayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PaymentController extends Controller
{
    protected $tripayService;

    public function __construct(TripayService $tripayService)
    {
        $this->tripayService = $tripayService;
    }

    /**
     * Show payment page for invoice
     */
    public function showPayment(Invoice $invoice)
    {
        // Check if invoice is still valid
        if ($invoice->status !== 'sent' || $invoice->due_date < now()) {
            return redirect()->route('client.invoices.index')
                ->with('error', 'Invoice sudah tidak valid atau sudah expired.');
        }

        // Get available payment channels
        $paymentChannels = $this->tripayService->getPaymentChannels();
        
        if (!$paymentChannels || !$paymentChannels['success']) {
            return redirect()->route('client.invoices.index')
                ->with('error', 'Tidak dapat memuat metode pembayaran. Silakan coba lagi.');
        }

        return view('client.payment.show', [
            'invoice' => $invoice,
            'paymentChannels' => $paymentChannels['data'] ?? [],
        ]);
    }

    /**
     * Create payment transaction
     */
    public function createPayment(Request $request, Invoice $invoice)
    {
        $request->validate([
            'payment_method' => 'required|string',
        ]);

        // Check if invoice is still valid
        if ($invoice->status !== 'pending' || $invoice->due_date < now()) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice sudah tidak valid atau sudah expired.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Prepare customer info
            $customerInfo = [
                'name' => $invoice->user->name,
                'email' => $invoice->user->email,
                'phone' => $invoice->user->phone,
            ];

            // Format transaction data
            $transactionData = $this->tripayService->formatTransactionData(
                $invoice,
                $request->payment_method,
                $customerInfo
            );

            // Create transaction with Tripay
            $tripayResponse = $this->tripayService->createTransaction($transactionData);

            if (!$tripayResponse || !$tripayResponse['success']) {
                throw new \Exception('Gagal membuat transaksi pembayaran: ' . ($tripayResponse['message'] ?? 'Unknown error'));
            }

            // Update invoice with payment details
            $invoice->update([
                'payment_method' => $request->payment_method,
                'payment_reference' => $tripayResponse['data']['reference'],
                'payment_url' => $tripayResponse['data']['checkout_url'] ?? null,
                'payment_code' => $tripayResponse['data']['pay_code'] ?? null,
                'payment_instructions' => $tripayResponse['data']['instructions'] ?? null,
                'payment_expired_at' => Carbon::createFromTimestamp($tripayResponse['data']['expired_time']),
                'tripay_data' => $tripayResponse['data'],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi pembayaran berhasil dibuat.',
                'data' => [
                    'reference' => $tripayResponse['data']['reference'],
                    'checkout_url' => $tripayResponse['data']['checkout_url'] ?? null,
                    'pay_code' => $tripayResponse['data']['pay_code'] ?? null,
                    'instructions' => $tripayResponse['data']['instructions'] ?? null,
                    'expired_time' => $tripayResponse['data']['expired_time'],
                    'qr_string' => $tripayResponse['data']['qr_string'] ?? null,
                    'qr_url' => $tripayResponse['data']['qr_url'] ?? null,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment creation failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle Tripay callback
     */
    public function handleTripayCallback(Request $request)
    {
        // Get callback signature from header
        $callbackSignature = $request->header('X-Callback-Signature');
        
        if (!$callbackSignature) {
            Log::warning('Tripay callback received without signature');
            return response('Unauthorized', 401);
        }

        // Get callback data
        $callbackData = $request->all();
        
        // Validate signature
        if (!$this->tripayService->validateCallbackSignature($callbackSignature, $callbackData)) {
            Log::warning('Tripay callback signature validation failed', [
                'signature' => $callbackSignature,
                'data' => $callbackData
            ]);
            return response('Unauthorized', 401);
        }

        try {
            // Find invoice by payment reference
            $invoice = Invoice::where('payment_reference', $callbackData['reference'])->first();
            
            if (!$invoice) {
                Log::warning('Invoice not found for Tripay callback', [
                    'reference' => $callbackData['reference']
                ]);
                return response('Invoice not found', 404);
            }

            DB::beginTransaction();

            // Update invoice status based on payment status
            $this->updateInvoiceStatus($invoice, $callbackData);

            // If payment is successful, activate project
            if ($callbackData['status'] === 'PAID') {
                $this->activateProject($invoice);
            }

            DB::commit();

            Log::info('Tripay callback processed successfully', [
                'reference' => $callbackData['reference'],
                'status' => $callbackData['status'],
                'invoice_id' => $invoice->id
            ]);

            return response('OK', 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tripay callback processing failed', [
                'reference' => $callbackData['reference'] ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response('Internal Server Error', 500);
        }
    }

    /**
     * Update invoice status based on payment callback
     */
    private function updateInvoiceStatus(Invoice $invoice, array $callbackData)
    {
        $statusMapping = config('tripay.status_mapping');
        $newStatus = $statusMapping[$callbackData['status']] ?? 'pending';

        $updateData = [
            'status' => $newStatus,
            'tripay_data' => $callbackData,
        ];

        // If payment is successful, record payment details
        if ($callbackData['status'] === 'PAID') {
            $updateData['paid_at'] = now();
            $updateData['payment_amount'] = $callbackData['amount_received'] ?? $callbackData['amount'];
        }

        $invoice->update($updateData);

        Log::info('Invoice status updated', [
            'invoice_id' => $invoice->id,
            'old_status' => $invoice->getOriginal('status'),
            'new_status' => $newStatus,
            'payment_status' => $callbackData['status']
        ]);
    }

    /**
     * Activate project after successful payment
     */
    private function activateProject(Invoice $invoice)
    {
        $project = Project::where('order_id', $invoice->order_id)->first();
        
        if ($project && $project->status === 'pending') {
            $project->update([
                'status' => 'active',
                'start_date' => now(),
            ]);

            Log::info('Project activated after payment', [
                'project_id' => $project->id,
                'invoice_id' => $invoice->id
            ]);
        }
    }

    /**
     * Show payment instructions page
     */
    public function showPaymentInstructions(Invoice $invoice)
    {
        // Check if invoice has payment data
        if (!$invoice->tripay_reference || !$invoice->tripay_data) {
            return redirect()->route('client.invoices.payment', $invoice)
                ->with('error', 'Payment not found. Please select a payment method first.');
        }

        $tripayData = $invoice->tripay_data;
        $paymentChannel = $tripayData['payment_method'] ?? null;

        // Get payment channel details for instructions
        $channels = $this->tripayService->getPaymentChannels();
        $channelDetails = collect($channels['data'] ?? [])->firstWhere('code', $paymentChannel);

        return view('client.payment.instructions', compact('invoice', 'tripayData', 'channelDetails'));
    }

    /**
     * Check payment status
     */
    public function checkPaymentStatus(Invoice $invoice)
    {
        if (!$invoice->payment_reference) {
            return response()->json([
                'success' => false,
                'message' => 'Payment reference not found'
            ], 404);
        }

        $tripayResponse = $this->tripayService->getTransactionDetail($invoice->payment_reference);
        
        if (!$tripayResponse || !$tripayResponse['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check payment status'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $tripayResponse['data']['status'],
                'paid_at' => $tripayResponse['data']['paid_at'] ?? null,
                'amount_received' => $tripayResponse['data']['amount_received'] ?? null,
            ]
        ]);
    }
}