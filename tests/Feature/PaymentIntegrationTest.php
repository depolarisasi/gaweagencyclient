<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Invoice;
use App\Services\TripayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class PaymentIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $client;
    protected $product;
    protected $order;
    protected $invoice;
    protected $tripayService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->client = User::factory()->create(['role' => 'client']);
        $this->product = Product::factory()->create([
            'name' => 'Website Development',
            'price' => 5000000
        ]);
        
        $this->order = Order::factory()->create([
            'user_id' => $this->client->id,
            'product_id' => $this->product->id,
            'total_amount' => 5500000,
            'status' => 'completed'
        ]);
        
        $this->invoice = Invoice::factory()->create([
            'user_id' => $this->client->id,
            'order_id' => $this->order->id,
            'total_amount' => 5500000,
            'status' => 'sent'
        ]);

        // Mock Tripay configuration
        Config::set('tripay.api_key', 'test_api_key');
        Config::set('tripay.private_key', 'test_private_key');
        Config::set('tripay.merchant_code', 'TEST123');
        Config::set('tripay.base_url', 'https://tripay.co.id/api-sandbox');
        
        $this->tripayService = new TripayService();
    }

    /** @test */
    public function client_can_initiate_payment_for_invoice()
    {
        // Mock Tripay API response for payment channels
        Http::fake([
            'tripay.co.id/api-sandbox/merchant/payment-channel*' => Http::response([
                'success' => true,
                'message' => 'Success',
                'data' => [
                    [
                        'code' => 'BRIVA',
                        'name' => 'BRI Virtual Account',
                        'type' => 'Virtual Account',
                        'fee_merchant' => [
                            'flat' => 4000,
                            'percent' => 0
                        ],
                        'fee_customer' => [
                            'flat' => 0,
                            'percent' => 0
                        ],
                        'total_fee' => [
                            'flat' => 4000,
                            'percent' => 0
                        ],
                        'minimum_fee' => 0,
                        'maximum_fee' => 0,
                        'icon_url' => 'https://tripay.co.id/images/payment/bri.png',
                        'active' => true
                    ],
                    [
                        'code' => 'BCAVA',
                        'name' => 'BCA Virtual Account',
                        'type' => 'Virtual Account',
                        'fee_merchant' => [
                            'flat' => 4000,
                            'percent' => 0
                        ],
                        'fee_customer' => [
                            'flat' => 0,
                            'percent' => 0
                        ],
                        'total_fee' => [
                            'flat' => 4000,
                            'percent' => 0
                        ],
                        'minimum_fee' => 0,
                        'maximum_fee' => 0,
                        'icon_url' => 'https://tripay.co.id/images/payment/bca.png',
                        'active' => true
                    ]
                ]
            ], 200)
        ]);

        $this->actingAs($this->client);
        
        // Client views invoice and initiates payment
        $response = $this->get("/client/invoices/{$this->invoice->id}");
        $response->assertStatus(200);
        $response->assertSee($this->invoice->invoice_number);
        $response->assertSee('Pay Now');

        // Client clicks pay button
        $response = $this->post("/client/invoices/{$this->invoice->id}/pay");
        $response->assertRedirect();
        $response->assertSessionHas('payment_channels');
    }

    /** @test */
    public function payment_transaction_can_be_created_via_tripay()
    {
        // Mock Tripay API response for creating transaction
        Http::fake([
            'tripay.co.id/api-sandbox/transaction/create' => Http::response([
                'success' => true,
                'message' => 'Transaction created successfully',
                'data' => [
                    'reference' => 'T123456789',
                    'merchant_ref' => 'INV-2024-001',
                    'payment_selection_type' => 'static',
                    'payment_method' => 'BRIVA',
                    'payment_name' => 'BRI Virtual Account',
                    'customer_name' => 'John Doe',
                    'customer_email' => 'john@example.com',
                    'customer_phone' => '+62812345678',
                    'callback_url' => 'https://example.com/payment/callback',
                    'return_url' => 'https://example.com/payment/return',
                    'amount' => 5500000,
                    'fee_merchant' => 4000,
                    'fee_customer' => 0,
                    'total_fee' => 4000,
                    'amount_received' => 5496000,
                    'pay_code' => '12345678901234567',
                    'pay_url' => null,
                    'checkout_url' => 'https://tripay.co.id/checkout/T123456789',
                    'status' => 'UNPAID',
                    'expired_time' => now()->addHours(24)->timestamp,
                    'order_items' => [
                        [
                            'sku' => 'WEBSITE-DEV',
                            'name' => 'Website Development',
                            'price' => 5000000,
                            'quantity' => 1,
                            'subtotal' => 5000000
                        ]
                    ],
                    'instructions' => [
                        [
                            'title' => 'ATM BRI',
                            'steps' => [
                                'Masukkan kartu ATM dan PIN Anda',
                                'Pilih menu Transaksi Lain',
                                'Pilih menu Transfer',
                                'Pilih ke Rekening BRI',
                                'Masukkan nomor Virtual Account: 12345678901234567',
                                'Masukkan nominal: 5500000',
                                'Ikuti instruksi untuk menyelesaikan transaksi'
                            ]
                        ]
                    ],
                    'qr_code' => null,
                    'qr_string' => null
                ]
            ], 200)
        ]);

        $this->actingAs($this->client);
        
        $paymentData = [
            'payment_method' => 'BRIVA',
            'customer_name' => $this->client->name,
            'customer_email' => $this->client->email,
            'customer_phone' => $this->client->phone ?? '+62812345678'
        ];

        $response = $this->post("/client/invoices/{$this->invoice->id}/process-payment", $paymentData);
        
        // Should redirect to payment page or show payment instructions
        $response->assertRedirect();
        $response->assertSessionHas('payment_data');
        
        // Verify payment record is created
        $this->assertDatabaseHas('payments', [
            'invoice_id' => $this->invoice->id,
            'user_id' => $this->client->id,
            'payment_method' => 'BRIVA',
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function payment_callback_updates_invoice_status()
    {
        // Create a payment record
        $payment = \App\Models\Payment::create([
            'invoice_id' => $this->invoice->id,
            'user_id' => $this->client->id,
            'reference' => 'T123456789',
            'payment_method' => 'BRIVA',
            'amount' => 5500000,
            'status' => 'pending'
        ]);

        // Mock Tripay callback data
        $callbackData = [
            'reference' => 'T123456789',
            'merchant_ref' => $this->invoice->invoice_number,
            'payment_method' => 'BRIVA',
            'payment_method_name' => 'BRI Virtual Account',
            'customer_name' => $this->client->name,
            'customer_email' => $this->client->email,
            'customer_phone' => $this->client->phone ?? '+62812345678',
            'callback_virtual_account_id' => '12345678901234567',
            'callback_virtual_account_name' => 'BRI Virtual Account',
            'callback_amount' => 5500000,
            'callback_paid_at' => now()->timestamp,
            'is_closed_payment' => 1,
            'status' => 'PAID',
            'paid_amount' => 5500000,
            'note' => 'Payment successful'
        ];

        // Generate signature for callback validation
        $signature = hash_hmac('sha256', json_encode($callbackData), config('tripay.private_key'));
        
        $response = $this->withHeaders([
            'X-Callback-Signature' => $signature
        ])->post('/payment/callback', $callbackData);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify invoice is marked as paid
        $this->invoice->refresh();
        $this->assertEquals('paid', $this->invoice->status);
        $this->assertNotNull($this->invoice->paid_date);
        $this->assertEquals('BRI Virtual Account', $this->invoice->payment_method);

        // Verify payment record is updated
        $payment->refresh();
        $this->assertEquals('completed', $payment->status);
        $this->assertNotNull($payment->paid_at);
    }

    /** @test */
    public function payment_callback_handles_failed_payment()
    {
        $payment = \App\Models\Payment::create([
            'invoice_id' => $this->invoice->id,
            'user_id' => $this->client->id,
            'reference' => 'T123456789',
            'payment_method' => 'BRIVA',
            'amount' => 5500000,
            'status' => 'pending'
        ]);

        $callbackData = [
            'reference' => 'T123456789',
            'merchant_ref' => $this->invoice->invoice_number,
            'payment_method' => 'BRIVA',
            'status' => 'FAILED',
            'note' => 'Payment failed due to insufficient funds'
        ];

        $signature = hash_hmac('sha256', json_encode($callbackData), config('tripay.private_key'));
        
        $response = $this->withHeaders([
            'X-Callback-Signature' => $signature
        ])->post('/payment/callback', $callbackData);

        $response->assertStatus(200);

        // Verify invoice remains unpaid
        $this->invoice->refresh();
        $this->assertEquals('sent', $this->invoice->status);
        $this->assertNull($this->invoice->paid_date);

        // Verify payment record is marked as failed
        $payment->refresh();
        $this->assertEquals('failed', $payment->status);
    }

    /** @test */
    public function payment_callback_validates_signature()
    {
        $callbackData = [
            'reference' => 'T123456789',
            'merchant_ref' => $this->invoice->invoice_number,
            'status' => 'PAID'
        ];

        // Send callback with invalid signature
        $response = $this->withHeaders([
            'X-Callback-Signature' => 'invalid_signature'
        ])->post('/payment/callback', $callbackData);

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Invalid signature']);

        // Verify invoice status is not changed
        $this->invoice->refresh();
        $this->assertEquals('sent', $this->invoice->status);
    }

    /** @test */
    public function tripay_service_can_get_payment_channels()
    {
        Http::fake([
            'tripay.co.id/api-sandbox/merchant/payment-channel*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'code' => 'BRIVA',
                        'name' => 'BRI Virtual Account',
                        'type' => 'Virtual Account',
                        'active' => true
                    ]
                ]
            ], 200)
        ]);

        $channels = $this->tripayService->getPaymentChannels();
        
        $this->assertIsArray($channels);
        $this->assertCount(1, $channels);
        $this->assertEquals('BRIVA', $channels[0]['code']);
        $this->assertEquals('BRI Virtual Account', $channels[0]['name']);
    }

    /** @test */
    public function tripay_service_can_create_transaction()
    {
        Http::fake([
            'tripay.co.id/api-sandbox/transaction/create' => Http::response([
                'success' => true,
                'data' => [
                    'reference' => 'T123456789',
                    'checkout_url' => 'https://tripay.co.id/checkout/T123456789',
                    'status' => 'UNPAID'
                ]
            ], 200)
        ]);

        $transactionData = [
            'method' => 'BRIVA',
            'merchant_ref' => $this->invoice->invoice_number,
            'amount' => $this->invoice->total_amount,
            'customer_name' => $this->client->name,
            'customer_email' => $this->client->email,
            'customer_phone' => $this->client->phone ?? '+62812345678',
            'order_items' => [
                [
                    'sku' => 'WEBSITE-DEV',
                    'name' => 'Website Development',
                    'price' => $this->invoice->amount,
                    'quantity' => 1
                ]
            ],
            'callback_url' => url('/payment/callback'),
            'return_url' => url('/client/invoices/' . $this->invoice->id)
        ];

        $transaction = $this->tripayService->createTransaction($transactionData);
        
        $this->assertIsArray($transaction);
        $this->assertEquals('T123456789', $transaction['reference']);
        $this->assertEquals('UNPAID', $transaction['status']);
        $this->assertStringContains('checkout', $transaction['checkout_url']);
    }

    /** @test */
    public function tripay_service_can_get_transaction_detail()
    {
        Http::fake([
            'tripay.co.id/api-sandbox/transaction/detail*' => Http::response([
                'success' => true,
                'data' => [
                    'reference' => 'T123456789',
                    'merchant_ref' => 'INV-2024-001',
                    'status' => 'PAID',
                    'amount' => 5500000,
                    'paid_amount' => 5500000,
                    'paid_at' => now()->timestamp
                ]
            ], 200)
        ]);

        $transaction = $this->tripayService->getTransactionDetail('T123456789');
        
        $this->assertIsArray($transaction);
        $this->assertEquals('T123456789', $transaction['reference']);
        $this->assertEquals('PAID', $transaction['status']);
        $this->assertEquals(5500000, $transaction['amount']);
    }

    /** @test */
    public function payment_flow_handles_expired_transactions()
    {
        $payment = \App\Models\Payment::create([
            'invoice_id' => $this->invoice->id,
            'user_id' => $this->client->id,
            'reference' => 'T123456789',
            'payment_method' => 'BRIVA',
            'amount' => 5500000,
            'status' => 'pending',
            'expired_at' => now()->subHour() // Expired 1 hour ago
        ]);

        $callbackData = [
            'reference' => 'T123456789',
            'merchant_ref' => $this->invoice->invoice_number,
            'status' => 'EXPIRED',
            'note' => 'Payment expired'
        ];

        $signature = hash_hmac('sha256', json_encode($callbackData), config('tripay.private_key'));
        
        $response = $this->withHeaders([
            'X-Callback-Signature' => $signature
        ])->post('/payment/callback', $callbackData);

        $response->assertStatus(200);

        // Verify payment is marked as expired
        $payment->refresh();
        $this->assertEquals('expired', $payment->status);

        // Invoice should remain unpaid
        $this->invoice->refresh();
        $this->assertEquals('sent', $this->invoice->status);
    }

    /** @test */
    public function multiple_payment_attempts_are_tracked()
    {
        $this->actingAs($this->client);

        // First payment attempt
        $payment1 = \App\Models\Payment::create([
            'invoice_id' => $this->invoice->id,
            'user_id' => $this->client->id,
            'reference' => 'T123456789',
            'payment_method' => 'BRIVA',
            'amount' => 5500000,
            'status' => 'expired'
        ]);

        // Second payment attempt
        $payment2 = \App\Models\Payment::create([
            'invoice_id' => $this->invoice->id,
            'user_id' => $this->client->id,
            'reference' => 'T987654321',
            'payment_method' => 'BCAVA',
            'amount' => 5500000,
            'status' => 'pending'
        ]);

        // Verify both payments are tracked
        $payments = \App\Models\Payment::where('invoice_id', $this->invoice->id)->get();
        $this->assertCount(2, $payments);
        
        $this->assertTrue($payments->contains('reference', 'T123456789'));
        $this->assertTrue($payments->contains('reference', 'T987654321'));
        $this->assertTrue($payments->contains('status', 'expired'));
        $this->assertTrue($payments->contains('status', 'pending'));
    }

    /** @test */
    public function payment_fees_are_calculated_correctly()
    {
        Http::fake([
            'tripay.co.id/api-sandbox/merchant/fee-calculator*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'code' => 'BRIVA',
                        'name' => 'BRI Virtual Account',
                        'fee' => [
                            'flat' => 4000,
                            'percent' => 0
                        ],
                        'total_fee' => 4000
                    ]
                ]
            ], 200)
        ]);

        $fees = $this->tripayService->calculateFee(5500000, 'BRIVA');
        
        $this->assertIsArray($fees);
        $this->assertEquals(4000, $fees['total_fee']);
        $this->assertEquals(5504000, $fees['amount_with_fee']);
    }

    /** @test */
    public function payment_notifications_are_sent_to_client()
    {
        // Mock notification sending
        \Illuminate\Support\Facades\Notification::fake();
        
        $payment = \App\Models\Payment::create([
            'invoice_id' => $this->invoice->id,
            'user_id' => $this->client->id,
            'reference' => 'T123456789',
            'payment_method' => 'BRIVA',
            'amount' => 5500000,
            'status' => 'pending'
        ]);

        // Simulate successful payment callback
        $callbackData = [
            'reference' => 'T123456789',
            'status' => 'PAID',
            'paid_amount' => 5500000
        ];

        $signature = hash_hmac('sha256', json_encode($callbackData), config('tripay.private_key'));
        
        $this->withHeaders([
            'X-Callback-Signature' => $signature
        ])->post('/payment/callback', $callbackData);

        // Verify notification was sent
        \Illuminate\Support\Facades\Notification::assertSentTo(
            $this->client,
            \App\Notifications\PaymentSuccessful::class
        );
    }

    /** @test */
    public function payment_history_is_accessible_to_client()
    {
        $payment1 = \App\Models\Payment::create([
            'invoice_id' => $this->invoice->id,
            'user_id' => $this->client->id,
            'reference' => 'T123456789',
            'payment_method' => 'BRIVA',
            'amount' => 5500000,
            'status' => 'completed',
            'paid_at' => now()->subDays(1)
        ]);

        $payment2 = \App\Models\Payment::create([
            'invoice_id' => $this->invoice->id,
            'user_id' => $this->client->id,
            'reference' => 'T987654321',
            'payment_method' => 'BCAVA',
            'amount' => 5500000,
            'status' => 'expired',
            'expired_at' => now()->subHours(2)
        ]);

        $this->actingAs($this->client);
        
        $response = $this->get('/client/payments');
        $response->assertStatus(200);
        $response->assertSee('T123456789');
        $response->assertSee('T987654321');
        $response->assertSee('BRIVA');
        $response->assertSee('BCAVA');
        $response->assertSee('completed');
        $response->assertSee('expired');
    }
}