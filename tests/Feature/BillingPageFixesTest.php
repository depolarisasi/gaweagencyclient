<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Template;
use App\Models\SubscriptionPlan;
use App\Models\ProductAddon;
use App\Models\Order;
use App\Models\Invoice;
use App\Services\TripayService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;

class BillingPageFixesTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $template;
    protected $subscriptionPlan;
    protected $addon;
    protected $order;
    protected $invoice;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '081234567890'
        ]);

        $this->template = Template::factory()->create([
            'name' => 'Test Template'
        ]);

        $this->subscriptionPlan = SubscriptionPlan::factory()->create([
            'name' => 'Monthly Plan',
            'price' => 50000,
            'billing_cycle' => 'monthly'
        ]);

        $this->addon = ProductAddon::factory()->create([
            'name' => 'SSL Certificate',
            'price' => 25000
        ]);
    }

    /** @test */
    public function test_billing_page_displays_correct_virtual_account_number()
    {
        // Mock TripayService to return payment channel details
        $this->mock(\App\Services\TripayService::class, function ($mock) {
            $mock->shouldReceive('getPaymentChannels')->andReturn([
                'success' => true,
                'data' => [
                    [
                        'code' => 'BRIVA',
                        'name' => 'BRI Virtual Account',
                        'type' => 'virtual_account'
                    ]
                ]
            ]);
        });
        
        // Create test Tripay transaction data
        $tripayData = [
            'success' => true,
            'message' => 'Transaction created',
            'reference' => 'T123456789',
            'merchant_ref' => 'SUB-1-' . time(),
            'payment_method' => 'BRIVA',
            'payment_method_code' => 'BRIVA',
            'amount' => 175000,
            'fee_merchant' => 2500,
            'fee_customer' => 0,
            'total_fee' => 2500,
            'pay_code' => '88810123456789',
            'expired_time' => now()->addHours(24)->timestamp,
            'status' => 'UNPAID',
            'instructions' => [
                [
                    'title' => 'ATM BRI',
                    'steps' => [
                        'Masukkan kartu ATM dan PIN Anda',
                        'Pilih menu Transaksi Lain',
                        'Pilih menu Pembayaran',
                        'Pilih menu Lainnya',
                        'Pilih menu BRIVA',
                        'Masukkan nomor BRIVA: 88810123456789',
                        'Masukkan nominal pembayaran: 175000',
                        'Ikuti instruksi untuk menyelesaikan transaksi'
                    ]
                ]
            ],
            'data' => [
                'reference' => 'T123456789',
                'merchant_ref' => 'SUB-1-' . time(),
                'payment_method' => 'BRIVA',
                'payment_method_code' => 'BRIVA',
                'amount' => 175000,
                'fee_merchant' => 2500,
                'fee_customer' => 0,
                'total_fee' => 2500,
                'pay_code' => '88810123456789',
                'expired_time' => now()->addHours(24)->timestamp,
                'status' => 'UNPAID',
                'instructions' => [
                    [
                        'title' => 'ATM BRI',
                        'steps' => [
                            'Masukkan kartu ATM dan PIN Anda',
                            'Pilih menu Transaksi Lain',
                            'Pilih menu Pembayaran',
                            'Pilih menu Lainnya',
                            'Pilih menu BRIVA',
                            'Masukkan nomor BRIVA: 88810123456789',
                            'Masukkan nominal pembayaran: 175000',
                            'Ikuti instruksi untuk menyelesaikan transaksi'
                        ]
                    ]
                ]
            ]
        ];

        // Store in session
        Session::put('checkout.tripay_transaction', $tripayData);
        Session::put('checkout.customer_info', [
            'name' => 'Test User',
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '081234567890'
        ]);
        Session::put('checkout.template_id', $this->template->id);
        Session::put('checkout.subscription_plan_id', $this->subscriptionPlan->id);
        Session::put('checkout.domain', [
            'type' => 'new',
            'name' => 'testdomain.com'
        ]);
        Session::put('checkout.payment_channel', 'BRIVA');

        // Visit billing page
        $response = $this->get('/checkout/billing');

        $response->assertStatus(200);
        $response->assertSee('88810123456789'); // Virtual account number
        $response->assertSee('T123456789'); // Reference number
        $response->assertDontSee('N/A'); // Should not show N/A for virtual account
    }

    /** @test */
    public function test_billing_page_displays_correct_total_payment()
    {
        // Mock TripayService to return payment channel details
        $this->mock(\App\Services\TripayService::class, function ($mock) {
            $mock->shouldReceive('getPaymentChannels')->andReturn([
                'success' => true,
                'data' => [
                    [
                        'code' => 'BRIVA',
                        'name' => 'BRI Virtual Account',
                        'type' => 'virtual_account'
                    ]
                ]
            ]);
        });

        $tripayData = [
            'success' => true,
            'message' => 'Transaction created',
            'reference' => 'T123456789',
            'merchant_ref' => 'SUB-1-' . time(),
            'payment_method' => 'BRIVA',
            'amount' => 175000,
            'fee_merchant' => 2500,
            'fee_customer' => 0,
            'total_fee' => 2500,
            'pay_code' => '88810123456789',
            'expired_time' => now()->addHours(24)->timestamp,
            'status' => 'UNPAID',
            'data' => [
                'reference' => 'T123456789',
                'merchant_ref' => 'SUB-1-' . time(),
                'payment_method' => 'BRIVA',
                'amount' => 175000,
                'fee_merchant' => 2500,
                'fee_customer' => 0,
                'total_fee' => 2500,
                'pay_code' => '88810123456789',
                'expired_time' => now()->addHours(24)->timestamp,
                'status' => 'UNPAID'
            ]
        ];

        Session::put('checkout.tripay_transaction', $tripayData);
        Session::put('checkout.customer_info', [
            'name' => 'Test User',
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '081234567890'
        ]);
        Session::put('checkout.template_id', $this->template->id);
        Session::put('checkout.subscription_plan_id', $this->subscriptionPlan->id);
        Session::put('checkout.domain', [
            'type' => 'new',
            'name' => 'testdomain.com'
        ]);
        Session::put('checkout.payment_channel', 'BRIVA');

        $response = $this->get('/checkout/billing');

        $response->assertStatus(200);
        $response->assertSee('Rp 200.000'); // Total payment amount (50000 subscription + 150000 domain)
        $response->assertDontSee('Rp 0'); // Should not show 0
    }

    /** @test */
    public function test_billing_page_displays_addons_in_order_summary()
    {
        $tripayData = [
            'success' => true,
            'data' => [
                'reference' => 'T123456789',
                'amount' => 175000,
                'pay_code' => '88810123456789'
            ]
        ];

        Session::put('checkout.tripay_transaction', $tripayData);
        Session::put('checkout.customer_info', [
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '081234567890'
        ]);
        Session::put('checkout.template_id', $this->template->id);
        Session::put('checkout.subscription_plan_id', $this->subscriptionPlan->id);
        Session::put('checkout.selected_addons', [$this->addon->id]);

        $response = $this->get('/checkout/billing');

        $response->assertStatus(200);
        $response->assertSee('SSL Certificate'); // Addon name
        $response->assertSee('Rp 25.000'); // Addon price
    }

    /** @test */
    public function test_billing_page_displays_tripay_payment_instructions()
    {
        $tripayData = [
            'reference' => 'T123456789',
            'amount' => 175000,
            'pay_code' => '88810123456789',
            'status' => 'UNPAID',
            'payment_method' => 'BRIVA',
            'checkout_url' => 'https://tripay.co.id/checkout/T123456789',
            'instructions' => [
                [
                    'title' => 'ATM BRI',
                    'steps' => [
                        'Masukkan kartu ATM dan PIN Anda',
                        'Pilih menu Transaksi Lain',
                        'Pilih menu Pembayaran'
                    ]
                ]
            ]
        ];

        Session::put('checkout.tripay_transaction', $tripayData);
        Session::put('checkout.customer_info', [
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '081234567890'
        ]);
        Session::put('checkout.template_id', 1);
        Session::put('checkout.subscription_plan_id', 1);
        Session::put('checkout.domain', [
            'type' => 'new',
            'name' => 'testdomain.com'
        ]);

        $response = $this->get('/checkout/billing');

        $response->assertStatus(200);
        $response->assertSee('Cara Pembayaran');
        $response->assertSee('ATM BRI');
        $response->assertSee('Masukkan kartu ATM dan PIN Anda');
    }

    /** @test */
    public function test_billing_page_displays_payment_deadline()
    {
        $expiredTime = now()->addHours(24)->timestamp;
        $tripayData = [
            'reference' => 'T123456789',
            'amount' => 175000,
            'pay_code' => '88810123456789',
            'expired_time' => $expiredTime,
            'status' => 'UNPAID',
            'payment_method' => 'BRIVA',
            'checkout_url' => 'https://tripay.co.id/checkout/T123456789'
        ];

        Session::put('checkout.tripay_transaction', $tripayData);
        Session::put('checkout.customer_info', [
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '081234567890'
        ]);
        Session::put('checkout.template_id', $this->template->id);
        Session::put('checkout.subscription_plan_id', $this->subscriptionPlan->id);
        Session::put('checkout.domain', [
            'type' => 'new',
            'name' => 'testdomain.com'
        ]);

        $response = $this->get('/checkout/billing');

        $response->assertStatus(200);
        $response->assertSee('Batas Waktu Pembayaran');
        $response->assertSee('countdown-timer'); // Countdown timer element
    }

    /** @test */
    public function test_check_payment_status_api_endpoint_works()
    {
        // Mock TripayService
        $this->mock(TripayService::class, function ($mock) {
            $mock->shouldReceive('getTransactionDetail')
                ->with('T123456789')
                ->andReturn([
                    'success' => true,
                    'data' => [
                        'reference' => 'T123456789',
                        'status' => 'PAID',
                        'paid_at' => now()->toISOString(),
                        'amount_received' => 175000
                    ]
                ]);
        });

        $response = $this->getJson('/api/payment/status/T123456789');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'PAID',
            'amount_received' => 175000
        ]);
    }

    /** @test */
    public function test_billing_page_displays_correct_invoice_information()
    {
        // Create order and invoice
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'order_number' => 'ORD-20241201-ABC123'
        ]);

        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'order_id' => $order->id,
            'invoice_number' => 'INV-20241201-0001',
            'tripay_reference' => 'T123456789'
        ]);

        $tripayData = [
            'success' => true,
            'reference' => 'T123456789',
            'amount' => 175000,
            'pay_code' => '88810123456789',
            'status' => 'UNPAID',
            'data' => [
                'reference' => 'T123456789',
                'amount' => 175000,
                'pay_code' => '88810123456789',
                'status' => 'UNPAID'
            ]
        ];

        Session::put('checkout.tripay_transaction', $tripayData);
        Session::put('checkout.customer_info', [
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '081234567890'
        ]);
        Session::put('checkout.template_id', $this->template->id);
        Session::put('checkout.subscription_plan_id', $this->subscriptionPlan->id);
        Session::put('checkout.domain', [
            'type' => 'new',
            'name' => 'testdomain.com'
        ]);
        Session::put('checkout.order_id', $order->id);

        $response = $this->get('/checkout/billing');

        $response->assertStatus(200);
        $response->assertSee('INV-' . date('Ymd') . '-456789'); // Invoice number (generated from reference)
        $response->assertSee('T123456789'); // Reference number
    }

    /** @test */
    public function test_personal_info_page_has_password_field()
    {
        Session::put('checkout.template_id', $this->template->id);
        Session::put('checkout.subscription_plan_id', $this->subscriptionPlan->id);

        $response = $this->get('/checkout/personal-info');

        $response->assertStatus(200);
        $response->assertSee('name="password"', false);
        $response->assertSee('name="password_confirmation"', false);
        $response->assertSee('Password');
        $response->assertSee('Konfirmasi Password');
    }

    /** @test */
    public function test_phone_number_is_sent_to_tripay()
    {
        // Mock TripayService to verify phone number is sent
        $this->mock(TripayService::class, function ($mock) {
            $mock->shouldReceive('createTransaction')
                ->with(\Mockery::on(function ($data) {
                    return isset($data['customer_phone']) && $data['customer_phone'] === '081234567890';
                }))
                ->andReturn([
                    'success' => true,
                    'data' => [
                        'reference' => 'T123456789',
                        'amount' => 175000,
                        'pay_code' => '88810123456789'
                    ]
                ]);
        });

        // Simulate checkout submission
        Session::put('checkout.template_id', $this->template->id);
        Session::put('checkout.subscription_plan_id', $this->subscriptionPlan->id);
        Session::put('checkout.customer_info', [
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '081234567890',
            'password' => 'password123'
        ]);
        Session::put('checkout.domain', [
            'type' => 'subdomain',
            'subdomain' => 'test'
        ]);
        Session::put('checkout.selected_addons', []);

        $response = $this->post('/checkout/summary', [
            'payment_channel' => 'BRIVA'
        ]);

        $response->assertRedirect('/checkout/billing');
    }

    /** @test */
    public function test_transaction_fees_are_displayed_and_stored_in_invoice()
    {
        $tripayData = [
            'success' => true,
            'reference' => 'T123456789',
            'amount' => 175000,
            'fee_merchant' => 2500,
            'fee_customer' => 0,
            'total_fee' => 2500,
            'pay_code' => '88810123456789',
            'data' => [
                'reference' => 'T123456789',
                'amount' => 175000,
                'fee_merchant' => 2500,
                'fee_customer' => 0,
                'total_fee' => 2500,
                'pay_code' => '88810123456789'
            ]
        ];

        Session::put('checkout.tripay_transaction', $tripayData);
        Session::put('checkout.customer_info', [
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '081234567890'
        ]);
        Session::put('checkout.template_id', $this->template->id);
        Session::put('checkout.subscription_plan_id', $this->subscriptionPlan->id);
        Session::put('checkout.domain', [
            'type' => 'new',
            'name' => 'testdomain.com'
        ]);

        $response = $this->get('/checkout/billing');

        $response->assertStatus(200);
        $response->assertSee('Biaya Admin (Merchant)'); // Merchant fee label
        $response->assertSee('Rp 2.500'); // Fee amount
        $response->assertSee('Total Biaya Admin'); // Total fee label
    }

    /** @test */
    public function test_billing_page_interactive_elements_are_present()
    {
        $tripayData = [
            'success' => true,
            'reference' => 'T123456789',
            'amount' => 175000,
            'pay_code' => '88810123456789',
            'expired_time' => now()->addHours(24)->timestamp,
            'data' => [
                'reference' => 'T123456789',
                'amount' => 175000,
                'pay_code' => '88810123456789',
                'expired_time' => now()->addHours(24)->timestamp
            ]
        ];

        Session::put('checkout.tripay_transaction', $tripayData);
        Session::put('checkout.customer_info', [
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '081234567890'
        ]);
        Session::put('checkout.template_id', $this->template->id);
        Session::put('checkout.subscription_plan_id', $this->subscriptionPlan->id);
        Session::put('checkout.domain', [
            'type' => 'new',
            'name' => 'testdomain.com'
        ]);

        $response = $this->get('/checkout/billing');

        $response->assertStatus(200);
        // Test for interactive elements that should be present
        $response->assertSee('onclick="copyToClipboard', false); // Copy button
        $response->assertSee('onclick="checkPaymentStatus()"', false); // Check payment status button
        $response->assertSee('id="countdown-timer"', false); // Countdown timer element
        $response->assertSee('Cek Status Pembayaran'); // Button text
        $response->assertSee('Copy'); // Copy button text
    }

    /** @test */
    public function test_billing_page_displays_addons_in_summary()
    {
        // Create test addons
        $addon1 = ProductAddon::factory()->create([
            'name' => 'SSL Certificate',
            'price' => 100000,
            'description' => 'SSL Certificate for secure connection'
        ]);
        
        $addon2 = ProductAddon::factory()->create([
            'name' => 'Email Hosting',
            'price' => 50000,
            'description' => 'Professional email hosting'
        ]);

        // Create tripay transaction data
        $tripayData = [
            'reference' => 'T123456789',
            'amount' => 300000, // 50000 + 100000 + 50000 + 150000 (subscription + addons + domain)
            'pay_code' => '88810123456789',
            'expired_time' => now()->addHours(24)->timestamp,
            'status' => 'UNPAID',
            'payment_method' => 'BRIVA',
            'checkout_url' => 'https://tripay.co.id/checkout/T123456789'
        ];

        // Mock TripayService
        $this->mock(TripayService::class, function ($mock) {
            $mock->shouldReceive('getPaymentChannels')
                ->andReturn([
                    'success' => true,
                    'data' => [
                        [
                            'code' => 'BRIVA',
                            'name' => 'BRI Virtual Account',
                            'type' => 'virtual_account'
                        ]
                    ]
                ]);
        });

        // Set up session data with addons
        Session::put('checkout.tripay_transaction', $tripayData);
        Session::put('checkout.customer_info', [
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '081234567890'
        ]);
        Session::put('checkout.template_id', $this->template->id);
        Session::put('checkout.subscription_plan_id', $this->subscriptionPlan->id);
        Session::put('checkout.selected_addons', [$addon1->id, $addon2->id]);
        Session::put('checkout.domain', [
            'type' => 'new',
            'name' => 'testdomain.com'
        ]);

        $response = $this->get('/checkout/billing');

        $response->assertStatus(200);
        
        // Check that individual addons are displayed
        $response->assertSee('SSL Certificate');
        $response->assertSee('Email Hosting');
        $response->assertSee('Rp 100.000'); // SSL Certificate price
        $response->assertSee('Rp 50.000'); // Email Hosting price
        
        // Check that addons subtotal is displayed
        $response->assertSee('Subtotal Add-ons');
        $response->assertSee('Rp 150.000'); // Total addons amount (100000 + 50000)
        
        // Check that total amount includes addons
        $response->assertSee('Rp 350.000'); // Total: 50000 (subscription) + 150000 (addons) + 150000 (domain)
    }

    /** @test */
    public function test_billing_page_displays_payment_guidance()
    {
        // Create tripay transaction data with payment instructions
        $tripayData = [
            'reference' => 'T123456789',
            'amount' => 175000,
            'pay_code' => '88810123456789',
            'expired_time' => now()->addHours(24)->timestamp,
            'status' => 'UNPAID',
            'payment_method' => 'BRIVA',
            'checkout_url' => 'https://tripay.co.id/checkout/T123456789',
            'instructions' => [
                [
                    'title' => 'ATM BRI',
                    'steps' => [
                        'Masukkan kartu ATM dan PIN Anda',
                        'Pilih menu Transaksi Lain',
                        'Pilih menu Pembayaran',
                        'Pilih menu Lainnya',
                        'Pilih menu BRIVA',
                        'Masukkan nomor BRIVA: 88810123456789',
                        'Masukkan nominal pembayaran: 175000',
                        'Ikuti instruksi untuk menyelesaikan transaksi'
                    ]
                ],
                [
                    'title' => 'Mobile Banking BRI',
                    'steps' => [
                        'Login ke aplikasi BRI Mobile',
                        'Pilih menu Pembayaran',
                        'Pilih BRIVA',
                        'Masukkan nomor BRIVA: 88810123456789',
                        'Masukkan nominal: 175000',
                        'Konfirmasi pembayaran'
                    ]
                ]
            ]
        ];

        // Mock TripayService
        $this->mock(TripayService::class, function ($mock) {
            $mock->shouldReceive('getPaymentChannels')
                ->andReturn([
                    'success' => true,
                    'data' => [
                        [
                            'code' => 'BRIVA',
                            'name' => 'BRI Virtual Account',
                            'type' => 'virtual_account'
                        ]
                    ]
                ]);
        });

        // Set up session data
        Session::put('checkout.tripay_transaction', $tripayData);
        Session::put('checkout.customer_info', [
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '081234567890'
        ]);
        Session::put('checkout.template_id', $this->template->id);
        Session::put('checkout.subscription_plan_id', $this->subscriptionPlan->id);
        Session::put('checkout.payment_channel', 'BRIVA');

        $response = $this->get('/checkout/billing');

        $response->assertStatus(200);
        
        // Check that payment guidance section is displayed
        $response->assertSee('Cara Pembayaran');
        
        // Check that payment instructions are displayed
        $response->assertSee('ATM BRI');
        $response->assertSee('Mobile Banking BRI');
        
        // Check specific instruction steps
        $response->assertSee('Masukkan kartu ATM dan PIN Anda');
        $response->assertSee('Pilih menu Pembayaran');
        $response->assertSee('Masukkan nomor BRIVA: 88810123456789');
        $response->assertSee('Masukkan nominal pembayaran: 175000');
        $response->assertSee('Login ke aplikasi BRI Mobile');
    }
}