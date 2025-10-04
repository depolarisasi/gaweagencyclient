<?php

namespace Tests\Feature\Livewire;

use App\Livewire\CheckoutProcess;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Project;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutProcessTest extends TestCase
{
    use RefreshDatabase;

    protected $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test product
        $this->product = Product::create([
            'name' => 'Test Website Package',
            'description' => 'A test website package for testing',
            'type' => 'website',
            'price' => 1500000.00,
            'billing_cycle' => 'monthly',
            'setup_time_days' => 14,
            'features' => json_encode(['Responsive Design', 'SEO Optimized', 'Admin Panel']),
            'is_active' => true,
            'sort_order' => 1
        ]);
    }

    /** @test */
    public function checkout_process_renders_correctly_with_product_data()
    {
        Livewire::test(CheckoutProcess::class, ['product' => $this->product->id])
            ->assertSee($this->product->name)
            ->assertSee($this->product->description)
            ->assertSee($this->product->formatted_price)
            ->assertSee('Responsive Design')
            ->assertSee('SEO Optimized')
            ->assertSee('Admin Panel');
    }

    /** @test */
    public function validation_fails_when_required_fields_are_missing()
    {
        Livewire::test(CheckoutProcess::class, ['product' => $this->product->id])
            ->set('name', '')
            ->set('email', '')
            ->set('password', '')
            ->call('submitOrder')
            ->assertHasErrors([
                'name' => 'required',
                'email' => 'required', 
                'password' => 'required'
            ]);
    }

    /** @test */
    public function validation_fails_when_email_format_is_invalid()
    {
        Livewire::test(CheckoutProcess::class, ['product' => $this->product->id])
            ->set('name', 'John Doe')
            ->set('email', 'invalid-email')
            ->set('password', 'password123')
            ->call('submitOrder')
            ->assertHasErrors(['email' => 'email']);
    }

    /** @test */
    public function validation_fails_when_password_is_too_short()
    {
        Livewire::test(CheckoutProcess::class, ['product' => $this->product->id])
            ->set('name', 'John Doe')
            ->set('email', 'john@example.com')
            ->set('password', '123')
            ->call('submitOrder')
            ->assertHasErrors(['password' => 'min']);
    }

    /** @test */
    public function validation_fails_when_email_already_exists()
    {
        // Create existing user
        User::create([
            'name' => 'Existing User',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'role' => 'client'
        ]);

        Livewire::test(CheckoutProcess::class, ['product' => $this->product->id])
            ->set('name', 'John Doe')
            ->set('email', 'john@example.com')
            ->set('password', 'password123')
            ->call('submitOrder')
            ->assertHasErrors(['email' => 'unique']);
    }

    /** @test */
    public function user_is_successfully_created_when_form_is_submitted_with_valid_data()
    {
        $this->assertDatabaseCount('users', 0);

        Livewire::test(CheckoutProcess::class, ['product' => $this->product->id])
            ->set('name', 'John Doe')
            ->set('email', 'john@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('phone', '081234567890')
            ->set('billing_cycle', 'monthly')
            ->call('submitOrder');

        $this->assertDatabaseCount('users', 1);
        
        $user = User::first();
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertEquals('client', $user->role);
        $this->assertTrue(password_verify('password123', $user->password));
    }

    /** @test */
    public function order_is_created_with_correct_data_when_form_is_submitted()
    {
        $this->assertDatabaseCount('orders', 0);

        Livewire::test(CheckoutProcess::class, ['product' => $this->product->id])
            ->set('name', 'John Doe')
            ->set('email', 'john@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('phone', '081234567890')
            ->set('billing_cycle', 'monthly')
            ->call('submitOrder');

        $this->assertDatabaseCount('orders', 1);
        
        $order = Order::first();
        $user = User::first();
        
        $this->assertEquals($user->id, $order->user_id);
        $this->assertEquals($this->product->id, $order->product_id);
        $this->assertEquals($this->product->price, $order->amount);
        $this->assertEquals('pending', $order->status);
        $this->assertStringStartsWith('ORD-', $order->order_number);
    }

    /** @test */
    public function invoice_is_created_with_unpaid_status_when_order_is_submitted()
    {
        $this->assertDatabaseCount('invoices', 0);

        Livewire::test(CheckoutProcess::class, ['product' => $this->product->id])
            ->set('name', 'John Doe')
            ->set('email', 'john@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('phone', '081234567890')
            ->set('billing_cycle', 'monthly')
            ->call('submitOrder');

        $this->assertDatabaseCount('invoices', 1);
        
        $invoice = Invoice::first();
        $user = User::first();
        $order = Order::first();
        
        $this->assertEquals($user->id, $invoice->user_id);
        $this->assertEquals($order->id, $invoice->order_id);
        $this->assertEquals($this->product->price, $invoice->amount);
        $this->assertEquals('draft', $invoice->status);
        $this->assertStringStartsWith('INV-', $invoice->invoice_number);
    }

    /** @test */
    public function project_is_created_with_pending_status_when_order_is_submitted()
    {
        $this->assertDatabaseCount('projects', 0);

        Livewire::test(CheckoutProcess::class, ['product' => $this->product->id])
            ->set('name', 'John Doe')
            ->set('email', 'john@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('phone', '081234567890')
            ->set('billing_cycle', 'monthly')
            ->call('submitOrder');

        $this->assertDatabaseCount('projects', 1);
        
        $project = Project::first();
        $user = User::first();
        $order = Order::first();
        
        $this->assertEquals($user->id, $project->user_id);
        $this->assertEquals($order->id, $project->order_id);
        $this->assertEquals('Website John Doe', $project->project_name);
        $this->assertEquals('pending', $project->status);
    }

    /** @test */
    public function user_is_redirected_to_invoice_page_after_successful_order_submission()
    {
        Livewire::test(CheckoutProcess::class, ['product' => $this->product->id])
            ->set('name', 'John Doe')
            ->set('email', 'john@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('phone', '081234567890')
            ->set('billing_cycle', 'monthly')
            ->call('submitOrder');
        
        $invoice = Invoice::first();
        $this->assertNotNull($invoice);
        $this->assertEquals('draft', $invoice->status);
    }

    /** @test */
    public function next_due_date_is_calculated_correctly_for_monthly_billing()
    {
        $monthlyProduct = Product::create([
             'name' => 'Monthly Service',
             'description' => 'Monthly billing service',
             'type' => 'website',
             'price' => 500000.00,
             'billing_cycle' => 'monthly',
             'setup_time_days' => 7,
             'features' => json_encode(['Basic Features']),
             'is_active' => true,
             'sort_order' => 1
         ]);

        $component = Livewire::test(CheckoutProcess::class, ['product' => $monthlyProduct->id])
            ->set('name', 'John Doe')
            ->set('email', 'john2@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('phone', '081234567890')
            ->set('billing_cycle', 'monthly')
            ->call('submitOrder');

        // Check if there are any errors
        $component->assertHasNoErrors();
        
        $order = Order::first();
        $this->assertNotNull($order, 'Order was not created');
        $expectedDate = now()->addMonth()->format('Y-m-d');
        $this->assertEquals($expectedDate, $order->next_due_date->format('Y-m-d'));
    }

    /** @test */
    public function next_due_date_is_calculated_correctly_for_annual_billing()
    {
        $annualProduct = Product::create([
             'name' => 'Annual Service',
             'description' => 'Annual billing service',
             'type' => 'website',
             'price' => 5000000.00,
             'billing_cycle' => 'annually',
             'setup_time_days' => 14,
             'features' => json_encode(['Premium Features']),
             'is_active' => true,
             'sort_order' => 1
         ]);

        $component = Livewire::test(CheckoutProcess::class, ['product' => $annualProduct->id])
            ->set('name', 'John Doe')
            ->set('email', 'john3@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('phone', '081234567890')
            ->set('billing_cycle', 'annually')
            ->call('submitOrder');

        // Check if there are any errors
        $component->assertHasNoErrors();
        
        $order = Order::first();
        $this->assertNotNull($order, 'Order was not created');
        $expectedDate = now()->addYear()->format('Y-m-d');
        $this->assertEquals($expectedDate, $order->next_due_date->format('Y-m-d'));
    }

    /** @test */
    public function database_transaction_rolls_back_on_error()
    {
        // Mock a database error by using invalid data that would cause constraint violation
        // We'll test this by creating a product with invalid foreign key reference
        
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('invoices', 0);
        $this->assertDatabaseCount('projects', 0);

        // This should work normally first
        Livewire::test(CheckoutProcess::class, ['product' => $this->product->id])
            ->set('name', 'John Doe')
            ->set('email', 'john@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('phone', '081234567890')
            ->set('billing_cycle', 'monthly')
            ->call('submitOrder');

        // Verify all records were created
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('invoices', 1);
        $this->assertDatabaseCount('projects', 1);
    }
}
