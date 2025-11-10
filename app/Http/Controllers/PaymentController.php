<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Project;
use App\Services\TripayService;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PaymentSuccessful;
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
        if ($invoice->status !== 'sent' || $invoice->due_date < now()) {
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
                'tripay_reference' => $tripayResponse['data']['reference'],
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
            // Find invoice by Tripay reference
            $invoice = Invoice::where('tripay_reference', $callbackData['reference'])->first();
            
            if (!$invoice) {
                Log::warning('Invoice not found for Tripay callback', [
                    'reference' => $callbackData['reference']
                ]);
                return response('Invoice not found', 404);
            }

            DB::beginTransaction();

            // Update invoice status based on payment status; only proceed if transitioned to PAID
            $transitionToPaid = $this->updateInvoiceStatus($invoice, $callbackData);

            // If payment transitioned to paid, activate order & project
            if ($transitionToPaid) {
                $this->activateOrder($invoice);
                $this->activateProject($invoice);

                // Send payment successful notification to client
                try {
                    if ($invoice->user) {
                        $invoice->user->notify(new PaymentSuccessful($invoice));
                    }
                } catch (\Throwable $e) {
                    Log::warning('Failed to send PaymentSuccessful notification', [
                        'invoice_id' => $invoice->id,
                        'user_id' => $invoice->user_id,
                        'message' => $e->getMessage(),
                    ]);
                }
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
        $newStatus = $statusMapping[$callbackData['status']] ?? 'sent';

        $previousStatus = $invoice->status;
        $updateData = [
            'tripay_data' => $callbackData,
        ];

        // If payment is successful, record payment details
        if ($callbackData['status'] === 'PAID') {
            // Gunakan paid_date agar konsisten dengan model & tampilan
            $updateData['paid_date'] = now();
            // Amount akan ditampilkan dari tripay_data (amount_received)
        }

        // Update status hanya jika berubah
        if ($newStatus !== $previousStatus) {
            $updateData['status'] = $newStatus;
            $invoice->update($updateData);
            
            Log::info('Invoice status updated', [
                'invoice_id' => $invoice->id,
                'old_status' => $previousStatus,
                'new_status' => $newStatus,
                'payment_status' => $callbackData['status']
            ]);
        } else {
            // Tetap sinkronkan tripay_data meski status sama
            $invoice->update(['tripay_data' => $callbackData] + ($updateData['paid_date'] ?? []));
        }

        // Return true jika terjadi transisi ke paid
        return ($previousStatus !== 'paid' && $newStatus === 'paid');
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
            return;
        }

        if (!$project) {
            $order = Order::find($invoice->order_id);
            if ($order) {
                $projectName = $this->generateProjectName($order);
                $websiteUrl = $order->domain_name ? ('https://' . $order->domain_name) : null;

                $project = Project::create([
                    'project_name' => $projectName,
                    'user_id' => $order->user_id,
                    'order_id' => $order->id,
                    'template_id' => $order->template_id,
                    'status' => 'active',
                    'website_url' => $websiteUrl,
                    'start_date' => now(),
                ]);

                Log::info('Project auto-created & activated after payment', [
                    'project_id' => $project->id,
                    'invoice_id' => $invoice->id,
                    'order_id' => $order->id,
                ]);
            }
        }
    }

    private function activateOrder(Invoice $invoice)
    {
        $order = Order::find($invoice->order_id);
        if (!$order) { return; }

        // Pastikan order aktif setelah pembayaran
        if ($order->status !== 'active') {
            $order->status = 'active';
            $order->activated_at = $order->activated_at ?? now();
        }

        // Update next_due_date ke akhir periode penagihan invoice
        if ($invoice->billing_period_end) {
            $order->next_due_date = $invoice->billing_period_end;
        } else {
            // fallback
            $order->next_due_date = $order->calculateNextDueDate();
        }
        $order->save();

        Log::info('Order activated/updated after payment', [
            'order_id' => $order->id,
            'invoice_id' => $invoice->id,
            'next_due_date' => $order->next_due_date,
        ]);
    }

    private function generateProjectName(Order $order): string
    {
        $baseName = '';

        if ($order->domain_name) {
            $baseName = 'Website for ' . $order->domain_name;
        } elseif ($order->template) {
            $baseName = $order->template->name . ' Project';
        } elseif ($order->product) {
            $baseName = $order->product->name . ' Project';
        } else {
            $baseName = 'Project';
        }

        if ($order->user) {
            $baseName .= ' for ' . $order->user->name;
        }

        return $baseName;
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
        if (!$invoice->tripay_reference) {
            return response()->json([
                'success' => false,
                'message' => 'Payment reference not found'
            ], 404);
        }

        $tripayResponse = $this->tripayService->getTransactionDetail($invoice->tripay_reference);
        
        if (!$tripayResponse || !$tripayResponse['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check payment status'
            ], 500);
        }

        // Fallback: jika status dari Tripay adalah final (PAID/EXPIRED/FAILED), sinkronkan status invoice
        try {
            $status = $tripayResponse['data']['status'] ?? null;
            if (in_array($status, ['PAID', 'EXPIRED', 'FAILED', 'REFUND'])) {
                // Update status invoice; hanya lanjut jika transisi ke paid
                $transitionToPaid = $this->updateInvoiceStatus($invoice, $tripayResponse['data']);

                if ($transitionToPaid) {
                    $this->activateOrder($invoice);
                    $this->activateProject($invoice);
                    try {
                        if ($invoice->user) {
                            $invoice->user->notify(new PaymentSuccessful($invoice));
                        }
                    } catch (\Throwable $e) {
                        Log::warning('Failed to send PaymentSuccessful notification (polling)', [
                            'invoice_id' => $invoice->id,
                            'user_id' => $invoice->user_id,
                            'message' => $e->getMessage(),
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('checkPaymentStatus fallback update failed', [
                'invoice_id' => $invoice->id,
                'message' => $e->getMessage(),
            ]);
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