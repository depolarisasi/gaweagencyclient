<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TripayIntegrationTest extends TestCase
{
    use RefreshDatabase;
    
    protected $user;
    protected $product;
    protected $order;
    protected $invoice;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->user = User::create([
            'name' => 'Test Client',
            'email' => 'client@test.com',
            'password' => bcrypt('password'),
            'role' => 'client',
            'status' => 'active',
        ]);
        
        // Create test product
        $this->product = Product::create([
            'name' => 'Website Package',
            'description' => 'Professional website package',
            'type' => 'website',
            'price' => 2500000,
            'billing_cycle' => 'annually',
            'setup_time_days' => 14,
            'is_active' => true,
        ]);
        
        // Create test order
        $this->order = Order::create([
            'order_number' => 'ORD-TRIPAY-001',
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'amount' => $this->product->price,
            'setup_fee' => 0,
            'billing_cycle' => 'annually',
            'status' => 'pending',
            'next_due_date' => now()->addYear(),
            'order_details' => [
                'product_name' => $this->product->name,
                'billing_cycle' => 'annually',
            ],
        ]);
        
        // Create test invoice
        $this->invoice = Invoice::create([
            'invoice_number' => 'INV-TRIPAY-001',
            'user_id' => $this->user->id,
            'order_id' => $this->order->id,
            'amount' => $this->product->price,
            'tax_amount' => round($this->product->price * 0.11, 2),
            'total_amount' => round($this->product->price * 1.11, 2),
            'status' => 'draft',
            'due_date' => now()->addDays(7),
        ]);
    }
    
    public function test_tripay_payment_creation_request()
    {
        // Mock Tripay API response for payment creation
        Http::fake([
            'tripay.co.id/api/*' => Http::response([
                'success' => true,
                'message' => 'Transaction created successfully',
                'data' => [
                    'reference' => 'T123456789',
                    'merchant_ref' => $this->invoice->invoice_number,
                    'payment_method' => 'MYBVA',
                    'payment_name' => 'Maybank Virtual Account',
                    'customer_name' => $this->user->name,
                    'customer_email' => $this->user->email,
                    'amount' => $this->invoice->total_amount,
                    'fee_merchant' => 5000,
                    'fee_customer' => 0,
                    'total_fee' => 5000,
                    'amount_received' => $this->invoice->total_amount - 5000,
                    'pay_code' => '1234567890123456',
                    'checkout_url' => 'https://tripay.co.id/checkout/T123456789',
                    'status' => 'UNPAID',
                    'expired_at' => now()->addHours(24)->timestamp,
                ]
            ], 200)
        ]);
        
        // Simulate payment creation request
        $paymentData = $this->createTripayPayment($this->invoice);
        
        $this->assertNotNull($paymentData);
        $this->assertEquals('T123456789', $paymentData['reference']);
        $this->assertEquals($this->invoice->invoice_number, $paymentData['merchant_ref']);
        $this->assertEquals('UNPAID', $paymentData['status']);
        $this->assertNotEmpty($paymentData['checkout_url']);
    }
    
    public function test_tripay_payment_callback_success()
    {
        // Set up invoice with Tripay reference
        $this->invoice->update([
            'tripay_reference' => 'T123456789',
            'tripay_merchant_ref' => $this->invoice->invoice_number,
        ]);
        
        // Simulate successful payment callback
        $callbackData = [
            'reference' => 'T123456789',
            'merchant_ref' => $this->invoice->invoice_number,
            'payment_method' => 'MYBVA',
            'payment_method_code' => 'MYBVA',
            'total_amount' => $this->invoice->total_amount,
            'fee_merchant' => 5000,
            'fee_customer' => 0,
            'total_fee' => 5000,
            'amount_received' => $this->invoice->total_amount - 5000,
            'is_closed_payment' => 1,
            'status' => 'PAID',
            'paid_at' => now()->timestamp,
        ];
        
        // Process callback
        $this->processTripayCallback($callbackData);
        
        // Verify invoice status updated
        $this->invoice->refresh();
        $this->assertEquals('paid', $this->invoice->status);
        $this->assertNotNull($this->invoice->paid_at);
        
        // Verify order status updated
        $this->order->refresh();
        $this->assertEquals('active', $this->order->status);
        $this->assertNotNull($this->order->activated_at);
    }
    
    public function test_tripay_payment_callback_expired()
    {
        // Set up invoice with Tripay reference
        $this->invoice->update([
            'tripay_reference' => 'T123456789',
            'tripay_merchant_ref' => $this->invoice->invoice_number,
        ]);
        
        // Simulate expired payment callback
        $callbackData = [
            'reference' => 'T123456789',
            'merchant_ref' => $this->invoice->invoice_number,
            'payment_method' => 'MYBVA',
            'payment_method_code' => 'MYBVA',
            'total_amount' => $this->invoice->total_amount,
            'fee_merchant' => 5000,
            'fee_customer' => 0,
            'total_fee' => 5000,
            'amount_received' => 0,
            'is_closed_payment' => 1,
            'status' => 'EXPIRED',
            'paid_at' => null,
        ];
        
        // Process callback
        $this->processTripayCallback($callbackData);
        
        // Verify invoice status updated
        $this->invoice->refresh();
        $this->assertEquals('cancelled', $this->invoice->status);
        $this->assertNull($this->invoice->paid_at);
        
        // Verify order remains pending
        $this->order->refresh();
        $this->assertEquals('pending', $this->order->status);
    }
    
    public function test_tripay_payment_callback_failed()
    {
        // Set up invoice with Tripay reference
        $this->invoice->update([
            'tripay_reference' => 'T123456789',
            'tripay_merchant_ref' => $this->invoice->invoice_number,
        ]);
        
        // Simulate failed payment callback
        $callbackData = [
            'reference' => 'T123456789',
            'merchant_ref' => $this->invoice->invoice_number,
            'payment_method' => 'MYBVA',
            'payment_method_code' => 'MYBVA',
            'total_amount' => $this->invoice->total_amount,
            'fee_merchant' => 5000,
            'fee_customer' => 0,
            'total_fee' => 5000,
            'amount_received' => 0,
            'is_closed_payment' => 1,
            'status' => 'FAILED',
            'paid_at' => null,
        ];
        
        // Process callback
        $this->processTripayCallback($callbackData);
        
        // Verify invoice status updated
        $this->invoice->refresh();
        $this->assertEquals('cancelled', $this->invoice->status);
        $this->assertNull($this->invoice->paid_at);
    }
    
    public function test_project_creation_after_successful_payment()
    {
        // Set up paid invoice
        $this->invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
            'tripay_reference' => 'T123456789',
        ]);
        
        $this->order->update([
            'status' => 'active',
            'activated_at' => now(),
        ]);
        
        // Simulate project creation after payment
        $project = $this->createProjectAfterPayment($this->order);
        
        $this->assertNotNull($project);
        $this->assertEquals($this->order->id, $project->order_id);
        $this->assertEquals($this->user->id, $project->user_id);
        $this->assertEquals('pending', $project->status);
        $this->assertStringContains('Website', $project->project_name);
    }
    
    public function test_tripay_payment_methods_retrieval()
    {
        // Mock Tripay API response for payment methods
        Http::fake([
            'tripay.co.id/api/merchant/payment-channel*' => Http::response([
                'success' => true,
                'message' => 'Success',
                'data' => [
                    [
                        'group' => 'Virtual Account',
                        'code' => 'MYBVA',
                        'name' => 'Maybank Virtual Account',
                        'type' => 'redirect',
                        'fee_merchant' => [
                            'flat' => 5000,
                            'percent' => 0
                        ],
                        'fee_customer' => [
                            'flat' => 0,
                            'percent' => 0
                        ],
                        'total_fee' => [
                            'flat' => 5000,
                            'percent' => 0
                        ],
                        'minimum_fee' => 0,
                        'maximum_fee' => 0,
                        'icon_url' => 'https://tripay.co.id/images/payment/mybva.png',
                        'active' => true
                    ],
                    [
                        'group' => 'E-Wallet',
                        'code' => 'SHOPEEPAY',
                        'name' => 'ShopeePay',
                        'type' => 'redirect',
                        'fee_merchant' => [
                            'flat' => 0,
                            'percent' => 2.5
                        ],
                        'fee_customer' => [
                            'flat' => 0,
                            'percent' => 0
                        ],
                        'total_fee' => [
                            'flat' => 0,
                            'percent' => 2.5
                        ],
                        'minimum_fee' => 0,
                        'maximum_fee' => 25000,
                        'icon_url' => 'https://tripay.co.id/images/payment/shopeepay.png',
                        'active' => true
                    ]
                ]
            ], 200)
        ]);
        
        // Get payment methods
        $paymentMethods = $this->getTripayPaymentMethods();
        
        $this->assertNotEmpty($paymentMethods);
        $this->assertCount(2, $paymentMethods);
        
        // Check Virtual Account method
        $virtualAccount = collect($paymentMethods)->firstWhere('code', 'MYBVA');
        $this->assertNotNull($virtualAccount);
        $this->assertEquals('Maybank Virtual Account', $virtualAccount['name']);
        $this->assertTrue($virtualAccount['active']);
        
        // Check E-Wallet method
        $eWallet = collect($paymentMethods)->firstWhere('code', 'SHOPEEPAY');
        $this->assertNotNull($eWallet);
        $this->assertEquals('ShopeePay', $eWallet['name']);
        $this->assertTrue($eWallet['active']);
    }
    
    public function test_tripay_transaction_status_check()
    {
        // Mock Tripay API response for transaction status
        Http::fake([
            'tripay.co.id/api/transaction/detail*' => Http::response([
                'success' => true,
                'message' => 'Success',
                'data' => [
                    'reference' => 'T123456789',
                    'merchant_ref' => $this->invoice->invoice_number,
                    'payment_selection_type' => 'static',
                    'payment_method' => 'MYBVA',
                    'payment_name' => 'Maybank Virtual Account',
                    'customer_name' => $this->user->name,
                    'customer_email' => $this->user->email,
                    'customer_phone' => $this->user->phone,
                    'amount' => $this->invoice->total_amount,
                    'fee_merchant' => 5000,
                    'fee_customer' => 0,
                    'total_fee' => 5000,
                    'amount_received' => $this->invoice->total_amount - 5000,
                    'pay_code' => '1234567890123456',
                    'pay_url' => null,
                    'checkout_url' => 'https://tripay.co.id/checkout/T123456789',
                    'status' => 'PAID',
                    'paid_at' => now()->timestamp,
                    'note' => null,
                    'created_at' => now()->subHour()->timestamp,
                    'expired_at' => now()->addHours(23)->timestamp,
                ]
            ], 200)
        ]);
        
        // Check transaction status
        $transactionStatus = $this->checkTripayTransactionStatus('T123456789');
        
        $this->assertNotNull($transactionStatus);
        $this->assertEquals('T123456789', $transactionStatus['reference']);
        $this->assertEquals('PAID', $transactionStatus['status']);
        $this->assertNotNull($transactionStatus['paid_at']);
    }
    
    public function test_invalid_callback_signature_rejection()
    {
        // Simulate callback with invalid signature
        $callbackData = [
            'reference' => 'T123456789',
            'merchant_ref' => $this->invoice->invoice_number,
            'status' => 'PAID',
        ];
        
        // Process callback with invalid signature
        $result = $this->processTripayCallbackWithSignature($callbackData, 'invalid_signature');
        
        $this->assertFalse($result['success']);
        $this->assertStringContains('Invalid signature', $result['message']);
        
        // Verify invoice status not changed
        $this->invoice->refresh();
        $this->assertEquals('draft', $this->invoice->status);
    }
    
    /**
     * Simulate creating Tripay payment
     */
    private function createTripayPayment($invoice)
    {
        // In real implementation, this would call Tripay API
        $response = Http::post('https://tripay.co.id/api/transaction/create', [
            'method' => 'MYBVA',
            'merchant_ref' => $invoice->invoice_number,
            'amount' => $invoice->total_amount,
            'customer_name' => $invoice->user->name,
            'customer_email' => $invoice->user->email,
            'customer_phone' => $invoice->user->phone ?? '',
            'order_items' => [
                [
                    'sku' => $invoice->order->product->id,
                    'name' => $invoice->order->product->name,
                    'price' => $invoice->amount,
                    'quantity' => 1,
                    'subtotal' => $invoice->amount,
                ]
            ],
            'callback_url' => url('/api/tripay/callback'),
            'return_url' => url('/invoice/' . $invoice->id),
            'expired_time' => now()->addHours(24)->timestamp,
        ]);
        
        return $response->json()['data'] ?? null;
    }
    
    /**
     * Simulate processing Tripay callback
     */
    private function processTripayCallback($callbackData)
    {
        $invoice = Invoice::where('tripay_merchant_ref', $callbackData['merchant_ref'])->first();
        
        if (!$invoice) {
            return false;
        }
        
        switch ($callbackData['status']) {
            case 'PAID':
                $invoice->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
                
                $invoice->order->update([
                    'status' => 'active',
                    'activated_at' => now(),
                ]);
                break;
                
            case 'EXPIRED':
            case 'FAILED':
                $invoice->update([
                    'status' => 'cancelled',
                ]);
                break;
        }
        
        return true;
    }
    
    /**
     * Simulate processing Tripay callback with signature validation
     */
    private function processTripayCallbackWithSignature($callbackData, $signature)
    {
        // In real implementation, validate signature here
        $expectedSignature = hash_hmac('sha256', json_encode($callbackData), config('tripay.private_key'));
        
        if ($signature !== $expectedSignature) {
            return ['success' => false, 'message' => 'Invalid signature'];
        }
        
        $this->processTripayCallback($callbackData);
        return ['success' => true, 'message' => 'Callback processed successfully'];
    }
    
    /**
     * Simulate getting Tripay payment methods
     */
    private function getTripayPaymentMethods()
    {
        $response = Http::get('https://tripay.co.id/api/merchant/payment-channel');
        return $response->json()['data'] ?? [];
    }
    
    /**
     * Simulate checking Tripay transaction status
     */
    private function checkTripayTransactionStatus($reference)
    {
        $response = Http::get("https://tripay.co.id/api/transaction/detail?reference={$reference}");
        return $response->json()['data'] ?? null;
    }
    
    /**
     * Simulate creating project after payment
     */
    private function createProjectAfterPayment($order)
    {
        return Project::create([
            'project_name' => 'Website ' . ($order->user->company ?: $order->user->name),
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'status' => 'pending',
            'description' => 'Pembuatan website menggunakan ' . $order->product->name,
            'start_date' => now(),
            'due_date' => now()->addDays($order->product->setup_time_days),
            'progress_percentage' => 0,
        ]);
    }
}