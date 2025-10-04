<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Template;
use App\Models\Product;
use App\Models\ProductAddon;
use App\Models\User;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Project;
use App\Livewire\ProductShowcase;
use App\Livewire\CheckoutProcess;
use App\Services\TripayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Mockery;

class MVPUserFlowIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $template;
    protected $product;
    protected $addons;
    protected $tripayServiceMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->template = Template::factory()->create([
            'name' => 'Business Template',
            'category' => 'business',
            'is_active' => true,
            'sort_order' => 1,
            'features' => ['Responsive Design', 'SEO Optimized', 'Contact Form'],
            'demo_url' => 'https://demo.example.com/business'
        ]);
        
        $this->product = Product::factory()->create([
            'name' => 'Basic Package',
            'price' => 500000,
            'billing_cycle' => 'monthly',
            'is_active' => true,
            'setup_time_days' => 7,
            'features' => ['Hosting', 'Domain', 'Maintenance']
        ]);
        
        $this->addons = collect([
            ProductAddon::factory()->create([
                'name' => 'SSL Certificate',
                'price' => 150000,
                'billing_type' => 'recurring',
                'billing_cycle' => 'annually',
                'is_active' => true
            ]),
            ProductAddon::factory()->create([
                'name' => 'Premium Support',
                'price' => 200000,
                'billing_type' => 'recurring',
                'billing_cycle' => 'monthly',
                'is_active' => true
            ])
        ]);
        
        // Mock TripayService
        $this->tripayServiceMock = Mockery::mock(TripayService::class);
        $this->app->instance(TripayService::class, $this->tripayServiceMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function complete_mvp_user_flow_from_template_selection_to_project_activation()
    {
        // Step 1: User visits homepage and sees template selection
        $this->get('/')
            ->assertStatus(200)
            ->assertSee('Business Template')
            ->assertSee('Template Website Profesional');

        // Step 2: User selects a template
        $templateSelectionComponent = Livewire::test(ProductShowcase::class)
            ->assertSee('Business Template')
            ->assertSee('Responsive Design')
            ->call('selectTemplate', $this->template->id)
            ->assertRedirect(route('checkout.template', ['template' => $this->template->id]));

        // Step 3: User goes through checkout process
        $checkoutComponent = Livewire::test(CheckoutProcess::class, ['template' => $this->template])
            ->assertSet('template.id', $this->template->id)
            ->assertSee('Business Template');

        // Step 4: User selects product and billing cycle
        $checkoutComponent
            ->call('selectProduct', $this->product->id)
            ->assertSet('selectedProduct', $this->product->id)
            ->set('billing_cycle', 'monthly');

        // Step 5: User selects addons
        $sslAddon = $this->addons->first();
        $checkoutComponent
            ->call('toggleAddon', $sslAddon->id)
            ->assertSet('selectedAddons', [$sslAddon->id]);

        // Step 6: User fills registration form and submits order
        $checkoutComponent
            ->set('name', 'John Doe')
            ->set('email', 'john@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('phone', '081234567890')
            ->set('company', 'Test Company')
            ->call('submitOrder');

        // Verify user was created and auto-logged in
        $this->assertAuthenticated();
        $user = auth()->user();
        $this->assertEquals('john@example.com', $user->email);
        $this->assertEquals('client', $user->role);

        // Verify order was created
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'product_id' => $this->product->id,
            'status' => 'pending',
            'billing_cycle' => 'monthly'
        ]);

        $order = Order::where('user_id', $user->id)->first();
        $this->assertNotNull($order);

        // Verify invoice was created with H+7 due date
        $this->assertDatabaseHas('invoices', [
            'user_id' => $user->id,
            'order_id' => $order->id,
            'status' => 'pending'
        ]);

        $invoice = Invoice::where('user_id', $user->id)->first();
        $expectedDueDate = now()->addDays(7)->format('Y-m-d');
        $this->assertEquals($expectedDueDate, $invoice->due_date->format('Y-m-d'));

        // Verify project was created with pending status
        $this->assertDatabaseHas('projects', [
            'user_id' => $user->id,
            'order_id' => $order->id,
            'template_id' => $this->template->id,
            'status' => 'pending'
        ]);

        $project = Project::where('user_id', $user->id)->first();
        $this->assertStringContains('Business Template', $project->description);

        // Step 7: User makes payment
        $this->tripayServiceMock
            ->shouldReceive('getPaymentChannels')
            ->once()
            ->andReturn([
                'success' => true,
                'data' => [
                    [
                        'code' => 'BRIVA',
                        'name' => 'BRI Virtual Account',
                        'group' => 'Virtual Account',
                        'active' => true
                    ]
                ]
            ]);

        // Visit payment page
        $this->actingAs($user)
            ->get(route('client.invoices.show', $invoice->id))
            ->assertStatus(200)
            ->assertSee('BRI Virtual Account');

        // Step 8: Simulate payment creation
        $this->tripayServiceMock
            ->shouldReceive('createTransaction')
            ->once()
            ->andReturn([
                'success' => true,
                'data' => [
                    'reference' => 'TEST123456',
                    'checkout_url' => 'https://tripay.co.id/checkout/TEST123456',
                    'expired_time' => time() + 3600
                ]
            ]);

        $this->actingAs($user)
            ->post(route('payment.create', $invoice->id), [
                'payment_method' => 'BRIVA'
            ])
            ->assertJson(['success' => true]);

        // Verify invoice was updated with payment reference
        $invoice->refresh();
        $this->assertEquals('TEST123456', $invoice->payment_reference);
        $this->assertEquals('BRIVA', $invoice->payment_method);

        // Step 9: Simulate payment callback (payment successful)
        $this->tripayServiceMock
            ->shouldReceive('validateCallbackSignature')
            ->once()
            ->andReturn(true);

        $callbackData = [
            'reference' => 'TEST123456',
            'status' => 'PAID',
            'total_amount' => $invoice->total_amount,
            'paid_amount' => $invoice->total_amount,
            'paid_at' => now()->timestamp
        ];

        $this->post(route('payment.callback'), $callbackData, [
            'X-Callback-Signature' => 'valid_signature'
        ])->assertStatus(200);

        // Verify invoice status was updated to paid
        $invoice->refresh();
        $this->assertEquals('paid', $invoice->status);
        $this->assertNotNull($invoice->paid_at);

        // Verify project was activated
        $project->refresh();
        $this->assertEquals('active', $project->status);
        $this->assertNotNull($project->start_date);

        // Step 10: Admin manages project (add website access details)
        $admin = User::factory()->create(['role' => 'admin']);
        
        $this->actingAs($admin)
            ->patch(route('admin.projects.update', $project->id), [
                'status' => 'active',
                'website_url' => 'https://johndoe.example.com',
                'admin_url' => 'https://johndoe.example.com/admin',
                'admin_username' => 'admin',
                'admin_password' => 'secure123',
                'notes' => 'Website setup completed'
            ])
            ->assertRedirect();

        // Verify project was updated with website access
        $project->refresh();
        $this->assertEquals('https://johndoe.example.com', $project->website_url);
        $this->assertEquals('admin', $project->admin_username);

        // Step 11: Client views project details
        $this->actingAs($user)
            ->get(route('client.projects.show', $project->id))
            ->assertStatus(200)
            ->assertSee('Business Template')
            ->assertSee('https://johndoe.example.com')
            ->assertSee('Active');

        // Step 12: Test recurring billing (simulate time passing)
        $order->update([
            'status' => 'active',
            'next_due_date' => now()->subDays(1) // Due yesterday
        ]);

        $this->artisan('invoices:generate-recurring');

        // Verify renewal invoice was created
        $this->assertDatabaseHas('invoices', [
            'user_id' => $user->id,
            'order_id' => $order->id,
            'is_renewal' => true,
            'status' => 'pending'
        ]);

        $renewalInvoice = Invoice::where('is_renewal', true)->first();
        $this->assertNotNull($renewalInvoice);

        // Step 13: Test project suspension for overdue payment
        $renewalInvoice->update([
            'due_date' => now()->subDays(15) // Overdue for 15 days
        ]);

        $this->artisan('projects:suspend-overdue');

        // Verify project was suspended
        $project->refresh();
        $this->assertEquals('suspended', $project->status);
        $this->assertNotNull($project->suspended_at);
        $this->assertEquals($renewalInvoice->id, $project->overdue_invoice_id);

        // Step 14: Test invoice auto-cancellation
        $expiredInvoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'status' => 'pending',
            'due_date' => now()->subDays(8) // Expired 8 days ago
        ]);

        $this->artisan('invoices:cancel-expired');

        // Verify invoice was cancelled
        $expiredInvoice->refresh();
        $this->assertEquals('cancelled', $expiredInvoice->status);
        $this->assertNotNull($expiredInvoice->cancelled_at);
    }

    /** @test */
    public function user_can_complete_checkout_without_template_selection()
    {
        // User goes directly to checkout with product
        $checkoutComponent = Livewire::test(CheckoutProcess::class, ['product' => $this->product])
            ->assertSet('selectedProduct', $this->product->id)
            ->set('name', 'Jane Doe')
            ->set('email', 'jane@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('phone', '081234567890')
            ->call('submitOrder');

        // Verify order was created without template
        $user = User::where('email', 'jane@example.com')->first();
        $project = Project::where('user_id', $user->id)->first();
        
        $this->assertNull($project->template_id);
        $this->assertStringContains($this->product->name, $project->description);
    }

    /** @test */
    public function admin_can_manage_all_aspects_of_user_project()
    {
        // Create user with project
        $user = User::factory()->create(['role' => 'client']);
        $order = Order::factory()->create(['user_id' => $user->id, 'product_id' => $this->product->id]);
        $project = Project::factory()->create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'template_id' => $this->template->id,
            'status' => 'pending'
        ]);
        
        $admin = User::factory()->create(['role' => 'admin']);

        // Admin can view project
        $this->actingAs($admin)
            ->get(route('admin.projects.index'))
            ->assertStatus(200)
            ->assertSee($project->project_name);

        // Admin can update project status and details
        $this->actingAs($admin)
            ->patch(route('admin.projects.update', $project->id), [
                'status' => 'in_progress',
                'assigned_to' => $admin->id,
                'website_url' => 'https://client.example.com',
                'admin_url' => 'https://client.example.com/wp-admin',
                'admin_username' => 'admin',
                'admin_password' => 'secure123',
                'notes' => 'Development in progress'
            ])
            ->assertRedirect();

        // Verify project was updated
        $project->refresh();
        $this->assertEquals('in_progress', $project->status);
        $this->assertEquals($admin->id, $project->assigned_to);
        $this->assertEquals('https://client.example.com', $project->website_url);
    }

    /** @test */
    public function client_dashboard_shows_correct_project_and_invoice_information()
    {
        $user = User::factory()->create(['role' => 'client']);
        $order = Order::factory()->create(['user_id' => $user->id, 'product_id' => $this->product->id]);
        
        $activeProject = Project::factory()->create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'status' => 'active'
        ]);
        
        $pendingInvoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'status' => 'pending',
            'amount' => 500000
        ]);

        $this->actingAs($user)
            ->get(route('client.dashboard'))
            ->assertStatus(200)
            ->assertSee('1') // Active projects count
            ->assertSee('1') // Pending invoices count
            ->assertSee('Rp 500.000'); // Pending amount
    }

    /** @test */
    public function system_handles_multiple_addons_correctly()
    {
        $sslAddon = $this->addons->first();
        $supportAddon = $this->addons->last();
        
        $checkoutComponent = Livewire::test(CheckoutProcess::class, ['template' => $this->template])
            ->set('selectedProduct', $this->product->id)
            ->call('toggleAddon', $sslAddon->id)
            ->call('toggleAddon', $supportAddon->id)
            ->assertSet('selectedAddons', [$sslAddon->id, $supportAddon->id]);

        // Calculate expected total
        $expectedTotal = $this->product->price + $sslAddon->price + $supportAddon->price;
        $this->assertEquals($expectedTotal, $checkoutComponent->get('totalAmount'));

        // Complete checkout
        $checkoutComponent
            ->set('name', 'Multi Addon User')
            ->set('email', 'multi@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('phone', '081234567890')
            ->call('submitOrder');

        // Verify order includes both addons
        $user = User::where('email', 'multi@example.com')->first();
        $order = Order::where('user_id', $user->id)->first();
        $orderDetails = $order->order_details;
        
        $this->assertArrayHasKey('addons', $orderDetails);
        $this->assertCount(2, $orderDetails['addons']);
        $this->assertEquals($sslAddon->name, $orderDetails['addons'][0]['name']);
        $this->assertEquals($supportAddon->name, $orderDetails['addons'][1]['name']);
    }
}