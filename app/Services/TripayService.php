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
            // Generate signature
            $signature = $this->generateSignature($data);
            $data['signature'] = $signature;

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/transaction/create', $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Tripay createTransaction failed', [
                'status' => $response->status(),
                'response' => $response->body(),
                'data' => $data
            ]);

            return null;
        } catch (Exception $e) {
            Log::error('Tripay createTransaction exception', [
                'message' => $e->getMessage(),
                'data' => $data
            ]);
            return null;
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
     * Format transaction data for Tripay API
     */
    public function formatTransactionData($invoice, $paymentMethod, $customerInfo = [])
    {
        return [
            'method' => $paymentMethod,
            'merchant_ref' => $invoice->invoice_number,
            'amount' => (int) $invoice->total_amount,
            'customer_name' => $customerInfo['name'] ?? 'Customer',
            'customer_email' => $customerInfo['email'] ?? 'customer@example.com',
            'customer_phone' => $customerInfo['phone'] ?? '08123456789',
            'order_items' => [
                [
                    'sku' => $invoice->invoice_number,
                    'name' => 'Invoice ' . $invoice->invoice_number,
                    'price' => (int) $invoice->total_amount,
                    'quantity' => 1,
                ]
            ],
            'return_url' => route('client.invoices.show', $invoice->id),
            'expired_time' => (int) $invoice->due_date->timestamp,
        ];
    }

    /**
     * Check if sandbox mode
     */
    public function isSandbox()
    {
        return $this->isSandbox;
    }
}