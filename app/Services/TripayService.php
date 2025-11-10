<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class TripayService
{
    private $apiKey;
    private $privateKey;
    private $merchantCode;
    private $baseUrl;
    private $isSandbox;

    public function __construct()
    {
        $this->isSandbox = config('tripay.is_sandbox', true);
        $this->apiKey = config('tripay.api_key');
        $this->privateKey = config('tripay.private_key');
        $this->merchantCode = config('tripay.merchant_code');
        $this->baseUrl = $this->isSandbox 
            ? 'https://tripay.co.id/api-sandbox'
            : 'https://tripay.co.id/api';
    }

    /**
     * Get available payment channels
     */
    public function getPaymentChannels()
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get($this->baseUrl . '/merchant/payment-channel');

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Tripay getPaymentChannels failed', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return null;
        } catch (Exception $e) {
            Log::error('Tripay getPaymentChannels exception', [
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Calculate transaction fee
     */
    public function calculateFee($amount, $paymentMethod = null)
    {
        try {
            $params = ['amount' => $amount];
            if ($paymentMethod) {
                $params['code'] = $paymentMethod;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get($this->baseUrl . '/merchant/fee-calculator', $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Tripay calculateFee failed', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return null;
        } catch (Exception $e) {
            Log::error('Tripay calculateFee exception', [
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Create payment transaction
     */
    public function createTransaction($data)
    {
        try {
            Log::info('TripayService createTransaction called', ['data' => $data]);
            
            // Generate signature
            $signature = $this->generateSignature($data);
            $data['signature'] = $signature;
            
            Log::info('TripayService signature generated', ['signature' => $signature]);

            // Ensure required optional fields have sensible defaults
            if (empty($data['callback_url'])) {
                $defaultCallback = config('tripay.callback_url');
                // Fallback to internal callback route if config is not set
                $data['callback_url'] = $defaultCallback ?: route('payment.callback');
            }
            if (empty($data['return_url'])) {
                // Fallback to home if return_url not provided
                $data['return_url'] = url('/');
            }
            if (empty($data['customer_phone'])) {
                // Basic phone fallback to avoid Tripay validation failure
                $data['customer_phone'] = '08123456789';
            }

            Log::info('TripayService making API request', [
                'url' => $this->baseUrl . '/transaction/create',
                'headers' => [
                    'Authorization' => 'Bearer ' . substr($this->apiKey, 0, 10) . '...',
                ]
            ]);

            // Send as JSON to comply with Tripay API expectations
            $response = Http::asJson()->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->post($this->baseUrl . '/transaction/create', $data);

            Log::info('TripayService API response received', [
                'status' => $response->status(),
                'successful' => $response->successful()
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                Log::info('TripayService transaction created successfully', [
                    'response_data' => $responseData
                ]);
                return $responseData;
            }

            // Non-2xx: coba kembalikan JSON error dari Tripay agar controller bisa menampilkan pesan
            $errorJson = null;
            try {
                $errorJson = $response->json();
            } catch (\Throwable $t) {
                $errorJson = null;
            }

            Log::error('Tripay createTransaction failed', [
                'status' => $response->status(),
                'response' => $response->body(),
                'data' => $data
            ]);

            if (is_array($errorJson)) {
                // Tripay biasanya mengirim { success:false, message:"..." }
                return $errorJson;
            }

            // Fallback bila body bukan JSON: pulangkan struktur standar dengan pesan ringkas
            $bodySnippet = substr($response->body() ?? '', 0, 200);
            return [
                'success' => false,
                'message' => 'Tripay error ' . $response->status() . ($bodySnippet ? ' - ' . $bodySnippet : ''),
                'data' => null,
            ];
        } catch (Exception $e) {
            Log::error('Tripay createTransaction exception', [
                'message' => $e->getMessage(),
                'data' => $data
            ]);
            // Pastikan selalu mengembalikan struktur yang bisa ditampilkan di UI
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * Get transaction detail
     */
    public function getTransactionDetail($reference)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get($this->baseUrl . '/transaction/detail', [
                'reference' => $reference
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Tripay getTransactionDetail failed', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return null;
        } catch (Exception $e) {
            Log::error('Tripay getTransactionDetail exception', [
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get payment instructions
     */
    public function getPaymentInstructions($paymentMethod, $payCode = null, $amount = null)
    {
        try {
            $params = ['code' => $paymentMethod];
            if ($payCode) {
                $params['pay_code'] = $payCode;
            }
            if ($amount) {
                $params['amount'] = $amount;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get($this->baseUrl . '/payment/instruction', $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Tripay getPaymentInstructions failed', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return null;
        } catch (Exception $e) {
            Log::error('Tripay getPaymentInstructions exception', [
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Validate callback signature
     */
    public function validateCallbackSignature($callbackSignature, $data)
    {
        $signature = hash_hmac('sha256', json_encode($data), $this->privateKey);
        return hash_equals($signature, $callbackSignature);
    }

    /**
     * Generate signature for transaction creation
     */
    private function generateSignature($data)
    {
        $signatureData = $this->merchantCode . $data['merchant_ref'] . $data['amount'];
        return hash_hmac('sha256', $signatureData, $this->privateKey);
    }

    /**
     * Format transaction data untuk Tripay API berdasarkan Invoice
     */
    public function formatTransactionData($invoice, $paymentMethod, $customerInfo = [])
    {
        $order = $invoice->order;

        // Bangun itemisasi transaksi dari order
        $orderItems = [];
        if ($order) {
            if ($order->subscription_amount > 0) {
                $orderItems[] = [
                    'sku' => $invoice->invoice_number . '-SUB',
                    'name' => 'Subscription ' . ($order->template->name ?? 'Website'),
                    'price' => (int) $order->subscription_amount,
                    'quantity' => 1,
                ];
            }
            if ($order->domain_amount > 0) {
                $orderItems[] = [
                    'sku' => $invoice->invoice_number . '-DOM',
                    'name' => 'Domain Registration',
                    'price' => (int) $order->domain_amount,
                    'quantity' => 1,
                ];
            }
            foreach ($order->orderAddons as $addon) {
                $orderItems[] = [
                    'sku' => $invoice->invoice_number . '-ADD-' . $addon->product_addon_id,
                    'name' => $addon->addon_details['name'] ?? 'Addon',
                    'price' => (int) $addon->price,
                    'quantity' => (int) ($addon->quantity ?? 1),
                ];
            }
        }

        if (empty($orderItems)) {
            // Fallback jika tidak ada order atau item
            $orderItems[] = [
                'sku' => $invoice->invoice_number,
                'name' => 'Invoice ' . $invoice->invoice_number,
                'price' => (int) $invoice->amount,
                'quantity' => 1,
            ];
        }

        // Gunakan data pelanggan dari invoice->user jika tersedia
        $user = $invoice->user;
        $customerName = $customerInfo['name'] ?? ($user->name ?? 'Customer');
        $customerEmail = $customerInfo['email'] ?? ($user->email ?? 'customer@example.com');
        $customerPhone = $customerInfo['phone'] ?? ($user->phone ?? '08123456789');

        return [
            'method' => $paymentMethod,
            'merchant_ref' => $invoice->invoice_number,
            'amount' => (int) $invoice->amount, // kirim subtotal tanpa fee Tripay
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'customer_phone' => $customerPhone,
            'order_items' => $orderItems,
            'return_url' => route('invoice.show', $invoice->id),
            'expired_time' => (int) $invoice->due_date->timestamp,
        ];
    }

    /**
     * Buat URL pembayaran dari data Tripay di invoice
     */
    public function createPaymentUrl($invoice)
    {
        $tripayData = $invoice->tripay_data ?? [];
        return $tripayData['checkout_url'] ?? null;
    }

    /**
     * Check if sandbox mode
     */
    public function isSandbox()
    {
        return $this->isSandbox;
    }
}