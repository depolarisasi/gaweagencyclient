<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Template;
use App\Models\SubscriptionPlan;
use App\Models\ProductAddon;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Invoice;
use App\Services\TripayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Session;
use Mockery;

class CheckoutEndToEndTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $template;
    protected $subscriptionPlan;
    protected $addon;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock TripayService to prevent actual API calls
        $tripayMock = Mockery::mock(TripayService::class);
        $tripayMock->shouldReceive('getPaymentChannels')
            ->andReturn([
                'success' => true,
                'data' => [
                    [
                        'code' => 'BRIVA',
                        'name' => 'BRI Virtual Account',
                        'type' => 'Virtual Account',
                        'fee_merchant' => ['flat' => 4000, 'percent' => 0],
                        'fee_customer' => ['flat' => 0, 'percent' => 0],
                        'total_fee' => ['flat' => 4000, 'percent' => 0],
                        'minimum_amount' => 10000,
                        'maximum_amount' => 10000000,
                        'active' => true,
                        'icon_url' => 'https://tripay.co.id/images/payment/briva.png'
                    ]
                ]
            ]);
        
        $tripayMock->shouldReceive('createTransaction')
            ->andReturn([
                'success' => true,
                'message' => 'Transaction created successfully',
                'data' => [
                    'reference' => 'T123456789012345',
                    'merchant_ref' => 'SUB-1-' . time(),
                    'payment_method' => 'BRIVA',
                    'payment_name' => 'BRI Virtual Account',
                    'customer_name' => 'Test User',
                    'customer_email' => 'kalangantertentu@gmail.com',
                    'customer_phone' => '081234567890',
                    'amount' => 75000,
                    'fee_merchant' => 4000,
                    'fee_customer' => 0,
                    'total_fee' => 4000,
                    'amount_received' => 71000,
                    'pay_code' => '12345678901234567890',
                    'checkout_url' => 'https://tripay.co.id/checkout/T123456789012345',
                    'status' => 'UNPAID',
                    'expired_time' => time() + (24 * 60 * 60),
                    'instructions' => [
                        [
                            'title' => 'ATM BRI',
                            'steps' => [
                                'Masukkan kartu ATM dan PIN Anda',
                                'Pilih menu Transaksi Lain',
                                'Pilih menu Pembayaran',
                                'Pilih menu Lainnya',
                                'Pilih menu BRIVA',
                                'Masukkan nomor Virtual Account: 12345678901234567890',
                                'Masukkan nominal pembayaran: 75000',
                                'Ikuti instruksi untuk menyelesaikan transaksi'
                            ]
                        ]
                    ],
                    'qr_string' => null,
                    'qr_url' => null
                ],
                // Also include flattened data for compatibility
                'reference' => 'T123456789012345',
                'merchant_ref' => 'SUB-1-' . time(),
                'payment_method' => 'BRIVA',
                'payment_name' => 'BRI Virtual Account',
                'customer_name' => 'Test User',
                'customer_email' => 'kalangantertentu@gmail.com',
                'customer_phone' => '081234567890',
                'amount' => 75000,
                'fee_merchant' => 4000,
                'fee_customer' => 0,
                'total_fee' => 4000,
                'amount_received' => 71000,
                'pay_code' => '12345678901234567890',
                'checkout_url' => 'https://tripay.co.id/checkout/T123456789012345',
                'status' => 'UNPAID',
                'expired_time' => time() + (24 * 60 * 60),
                'instructions' => [
                    [
                        'title' => 'ATM BRI',
                        'steps' => [
                            'Masukkan kartu ATM dan PIN Anda',
                            'Pilih menu Transaksi Lain',
                            'Pilih menu Pembayaran',
                            'Pilih menu Lainnya',
                            'Pilih menu BRIVA',
                            'Masukkan nomor Virtual Account: 12345678901234567890',
                            'Masukkan nominal pembayaran: 75000',
                            'Ikuti instruksi untuk menyelesaikan transaksi'
                        ]
                    ]
                ],
                'qr_string' => null,
                'qr_url' => null
            ]);
        
        $this->app->instance(TripayService::class, $tripayMock);
        
        // Create test data
        $this->user = User::factory()->create([
            'email' => 'kalangantertentu@gmail.com',
            'password' => bcrypt('Ganteng1212'),
            'name' => 'Test User',
            'phone' => '081234567890'
        ]);

        $this->template = Template::factory()->create([
            'name' => 'Test Template',
            'description' => 'A test template for checkout',
            'category' => 'business',
            'is_active' => true
        ]);

        $this->subscriptionPlan = SubscriptionPlan::factory()->create([
            'name' => 'Basic Plan',
            'price' => 50000,
            'billing_cycle' => 'monthly',
            'cycle_months' => 1,
            'is_active' => true
        ]);

        $this->addon = ProductAddon::factory()->create([
            'name' => 'Test Addon',
            'price' => 25000,
            'is_active' => true
        ]);
    }

    /** @test */
    public function logged_in_user_can_complete_full_checkout_flow()
    {
        // Login as test user and start session
        $this->actingAs($this->user);
        $this->startSession();

        // Step 1: Template selection
        $response = $this->get('/checkout');
        $response->assertStatus(200);

        $response = $this->post('/checkout/step1', [
            'template_id' => $this->template->id
        ]);
        $response->assertRedirect('/checkout/configure');

        // Step 3: Configure subscription plan
        $response = $this->get('/checkout/configure');
        $response->assertStatus(200);

        $response = $this->post('/checkout/configure', [
            'subscription_plan_id' => $this->subscriptionPlan->id,
            'billing_cycle' => 'monthly'
        ]);
        $response->assertRedirect('/checkout/addon');

        // Step 4: Add addons (optional)
        $response = $this->get('/checkout/addon');
        $response->assertStatus(200);

        $response = $this->post('/checkout/addon', [
            'selected_addons' => [$this->addon->id]
        ]);
        $response->assertRedirect('/checkout/domain');

        // Step 5: Domain
        $response = $this->get('/checkout/domain');
        $response->assertStatus(200);

        $response = $this->post('/checkout/domain', [
            'domain_name' => 'testdomain.com',
            'domain_type' => 'new'
        ]);
        $response->assertRedirect('/checkout/personal-info');

        // Step 6: Personal info
        $response = $this->get('/checkout/personal-info');
        $response->assertStatus(200);

        $response = $this->post('/checkout/personal-info', [
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '081234567890',
            'company' => 'Test Company'
        ]);
        $response->assertRedirect('/checkout/summary');

        // Step 6: Summary and payment method selection
        $response = $this->get('/checkout/summary');
        $response->assertStatus(200);

        // Verify cart was created and populated
        $cart = Cart::where('user_id', $this->user->id)->first();
        $this->assertNotNull($cart);
        $this->assertEquals($this->template->id, $cart->template_id);
        $this->assertEquals($this->subscriptionPlan->id, $cart->subscription_plan_id);
        $this->assertEquals('monthly', $cart->billing_cycle);
        $this->assertNotNull($cart->domain_data);
        $this->assertEquals('testdomain.com', $cart->domain_data['domain_name']);
        $this->assertEquals('new', $cart->domain_data['domain_type']);

        // Check cart has addon
        $this->assertEquals(1, $cart->cartAddons()->count());
        $this->assertEquals($this->addon->id, $cart->cartAddons()->first()->product_addon_id);

        // Submit order with payment method
        $response = $this->post('/checkout/summary', [
            'payment_channel' => 'BRIVA'
        ]);
        
        // Should redirect to billing page
        $response->assertRedirect('/checkout/billing');

        // Verify order and invoice were created
        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertNotNull($order);
        
        $invoice = Invoice::where('order_id', $order->id)->first();
        $this->assertNotNull($invoice);

        // Step 7: Check billing page
        $response = $this->get('/checkout/billing');
        $response->assertStatus(200);
    }

    /** @test */
    public function guest_user_can_complete_full_checkout_flow()
    {
        // Create a fixed session ID for consistency
        $sessionId = 'test-session-' . time();
        
        // Use Session facade to ensure consistency
        Session::setId($sessionId);
        Session::start();
        
        \Log::info('Test session ID:', ['session_id' => $sessionId]);
        
        // Step 1: Start checkout as guest - Template selection
        $response = $this->get('/checkout');
        $response->assertStatus(200);

        $response = $this->post('/checkout/step1', [
            'template_id' => $this->template->id
        ]);
        $response->assertRedirect('/checkout/configure');

        // Step 2: Configure subscription plan
        $response = $this->get('/checkout/configure');
        $response->assertStatus(200);

        $response = $this->post('/checkout/configure', [
            'subscription_plan_id' => $this->subscriptionPlan->id,
            'billing_cycle' => 'monthly'
        ]);
        $response->assertRedirect('/checkout/addon');

        // Verify session data is set before addon step
        $this->assertEquals($this->template->id, session('checkout.template_id'));
        $this->assertEquals($this->subscriptionPlan->id, session('checkout.subscription_plan_id'));
        $this->assertEquals('monthly', session('checkout.billing_cycle'));

        // Step 3: Skip addons
        $response = $this->get('/checkout/addon');
        $response->assertStatus(200);

        $response = $this->post('/checkout/addon', [
            'selected_addons' => []
        ]);
        $response->assertRedirect('/checkout/domain');

        // Step 4: Domain
        $response = $this->get('/checkout/domain');
        $response->assertStatus(200);

        $response = $this->post('/checkout/domain', [
            'domain_name' => 'guestdomain.com',
            'domain_type' => 'new'
        ]);
        $response->assertRedirect('/checkout/personal-info');

        // Step 5: Personal info (with user registration data)
        $response = $this->get('/checkout/personal-info');
        $response->assertStatus(200);

        $response = $this->post('/checkout/personal-info', [
            'full_name' => 'Guest User',
            'email' => 'guest@example.com',
            'phone' => '081234567891',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);
        $response->assertRedirect('/checkout/summary');

        // Debug: Check cart data before summary step
        $cart = Cart::where('session_id', session()->getId())->first();
        if ($cart) {
            \Log::info('Cart data before summary:', [
                'template_id' => $cart->template_id,
                'subscription_plan_id' => $cart->subscription_plan_id,
                'billing_cycle' => $cart->billing_cycle,
                'configuration' => $cart->configuration,
                'domain_data' => $cart->domain_data,
            ]);
        } else {
            \Log::info('No cart found for session: ' . session()->getId());
        }

        // Step 5: Summary and payment method selection
        \Log::info('Session ID before summary:', ['session_id' => session()->getId()]);
        $response = $this->get('/checkout/summary');
        $response->assertStatus(200);

        // Verify cart was created for session
        $cart = Cart::where('session_id', session()->getId())->first();
        $this->assertNotNull($cart);
        $this->assertEquals($this->template->id, $cart->template_id);
        $this->assertEquals($this->subscriptionPlan->id, $cart->subscription_plan_id);

        // Submit order with payment method
        $response = $this->post('/checkout/summary', [
            'payment_channel' => 'BRIVA'
        ]);
        
        // Should redirect to billing page
        $response->assertRedirect('/checkout/billing');

        // Verify user was created during checkout
        $newUser = User::where('email', 'guest@example.com')->first();
        $this->assertNotNull($newUser);
        $this->assertEquals('Guest User', $newUser->name);

        // Verify order and invoice were created
        $order = Order::where('user_id', $newUser->id)->first();
        $this->assertNotNull($order);
        
        $invoice = Invoice::where('order_id', $order->id)->first();
        $this->assertNotNull($invoice);

        // Step 6: Check billing page
        $response = $this->get('/checkout/billing');
        $response->assertStatus(200);
    }

    /** @test */
    public function cart_system_properly_calculates_totals()
    {
        $this->actingAs($this->user);

        // Create cart with template, subscription plan, and addon
        $cart = Cart::create([
            'user_id' => $this->user->id,
            'template_id' => $this->template->id,
            'subscription_plan_id' => $this->subscriptionPlan->id,
            'billing_cycle' => 'monthly',
            'template_amount' => 100000, // Set template amount manually since templates don't have price
            'domain_amount' => 0
        ]);

        // Add addon to cart
        $cart->cartAddons()->create([
            'product_addon_id' => $this->addon->id,
            'price' => $this->addon->price
        ]);

        // Calculate totals
        $cart->calculateTotals();

        // Verify calculations
        $expectedTemplateAmount = 100000;
        $expectedAddonAmount = $this->addon->price;
        $expectedSubtotal = $expectedTemplateAmount + $expectedAddonAmount;
        
        $this->assertEquals($expectedTemplateAmount, $cart->template_amount);
        $this->assertEquals($expectedAddonAmount, $cart->addons_amount);
        $this->assertEquals($expectedSubtotal, $cart->subtotal);
        $this->assertGreaterThan($expectedSubtotal, $cart->total_amount); // Should include customer fee
    }

    /** @test */
    public function cart_system_supports_all_checkout_data()
    {
        $cart = Cart::create([
            'session_id' => 'test-session-id',
            'template_id' => $this->template->id,
            'subscription_plan_id' => $this->subscriptionPlan->id,
            'billing_cycle' => 'monthly',
            'configuration' => [
                'customer_info' => [
                    'full_name' => 'Test Customer',
                    'email' => 'test@example.com',
                    'phone' => '081234567890'
                ]
            ],
            'domain_data' => [
                'domain_name' => 'testdomain.com',
                'domain_type' => 'new'
            ]
        ]);

        // Verify all data is stored correctly
        $this->assertEquals($this->template->id, $cart->template_id);
        $this->assertEquals($this->subscriptionPlan->id, $cart->subscription_plan_id);
        $this->assertEquals('monthly', $cart->billing_cycle);
        $this->assertEquals('Test Customer', $cart->configuration['customer_info']['full_name']);
        $this->assertEquals('testdomain.com', $cart->domain_data['domain_name']);
        $this->assertEquals('new', $cart->domain_data['domain_type']);

        // Test cart completion check
        $this->assertTrue($cart->template_id !== null);
        $this->assertTrue($cart->subscription_plan_id !== null);
        $this->assertTrue($cart->billing_cycle !== null);
        $this->assertTrue(isset($cart->configuration['customer_info']));
        $this->assertTrue($cart->domain_data !== null);
    }
}