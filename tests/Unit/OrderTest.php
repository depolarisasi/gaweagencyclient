<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class OrderTest extends TestCase
{
    use RefreshDatabase;
    
    protected $user;
    protected $product;
    protected $order;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'phone' => '081234567890',
            'role' => 'client'
        ]);
        
        $this->product = Product::create([
            'name' => 'Website Basic',
            'description' => 'Basic website package',
            'type' => 'website',
            'price' => 1000000,
            'billing_cycle' => 'monthly',
            'setup_time_days' => 14,
            'features' => ['Responsive Design', 'SEO Optimized'],
            'is_active' => true,
        ]);
        
        $this->order = Order::create([
            'order_number' => 'ORD-20250911-123456',
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'amount' => 1000000,
            'setup_fee' => 100000,
            'billing_cycle' => 'monthly',
            'status' => 'pending',
            'next_due_date' => now()->addMonth(),
            'order_details' => [
                'product_name' => 'Website Basic',
                'billing_cycle' => 'monthly',
                'features' => ['Responsive Design', 'SEO Optimized'],
            ],
        ]);
    }
    
    public function test_order_can_be_created_with_required_fields()
    {
        $order = Order::create([
            'order_number' => 'ORD-TEST-001',
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'amount' => 500000,
            'setup_fee' => 50000,
            'billing_cycle' => 'monthly',
            'status' => 'pending',
            'next_due_date' => now()->addMonth(),
            'order_details' => [
                'product_name' => 'Test Product',
                'billing_cycle' => 'monthly',
            ],
        ]);
        
        $this->assertDatabaseHas('orders', [
            'order_number' => 'ORD-TEST-001',
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'amount' => 500000,
            'setup_fee' => 50000,
            'status' => 'pending'
        ]);
    }
    
    public function test_order_belongs_to_user()
    {
        $this->assertInstanceOf(User::class, $this->order->user);
        $this->assertEquals($this->user->id, $this->order->user->id);
    }
    
    public function test_order_belongs_to_product()
    {
        $this->assertInstanceOf(Product::class, $this->order->product);
        $this->assertEquals($this->product->id, $this->order->product->id);
    }
    
    public function test_order_has_many_invoices()
    {
        $invoice = Invoice::create([
            'invoice_number' => 'INV-TEST-001',
            'user_id' => $this->user->id,
            'order_id' => $this->order->id,
            'amount' => 1000000,
            'tax_amount' => 110000,
            'total_amount' => 1110000,
            'status' => 'draft',
            'due_date' => now()->addDays(7),
        ]);
        
        $this->assertTrue($this->order->invoices->contains($invoice));
        $this->assertEquals(1, $this->order->invoices->count());
    }
    
    public function test_order_has_many_projects()
    {
        $project = Project::create([
            'project_name' => 'Test Project',
            'order_id' => $this->order->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
            'description' => 'Test project description',
            'start_date' => now(),
            'due_date' => now()->addDays(14),
            'progress_percentage' => 0,
        ]);
        
        $this->assertTrue($this->order->projects->contains($project));
        $this->assertEquals(1, $this->order->projects->count());
    }
    
    public function test_total_amount_attribute()
    {
        $this->assertEquals('1100000.00', $this->order->total_amount);
        
        $order = Order::create([
            'order_number' => 'ORD-TEST-002',
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'amount' => 500000,
            'setup_fee' => 0,
            'billing_cycle' => 'monthly',
            'status' => 'pending',
            'next_due_date' => now()->addMonth(),
            'order_details' => [],
        ]);
        
        $this->assertEquals('500000.00', $order->total_amount);
    }
    
    public function test_formatted_amount_attribute()
    {
        $this->assertEquals('Rp 1.000.000', $this->order->formatted_amount);
    }
    
    public function test_formatted_total_amount_attribute()
    {
        $this->assertEquals('Rp 1.100.000', $this->order->formatted_total_amount);
    }
    
    public function test_is_active_method()
    {
        $this->assertFalse($this->order->isActive());
        
        $this->order->update(['status' => 'active']);
        $this->assertTrue($this->order->isActive());
    }
    
    public function test_is_pending_method()
    {
        $this->assertTrue($this->order->isPending());
        
        $this->order->update(['status' => 'active']);
        $this->assertFalse($this->order->isPending());
    }
    
    public function test_is_suspended_method()
    {
        $this->assertFalse($this->order->isSuspended());
        
        $this->order->update(['status' => 'suspended']);
        $this->assertTrue($this->order->isSuspended());
    }
    
    public function test_calculate_next_due_date_without_activation()
    {
        $this->order->update(['activated_at' => null]);
        $this->assertNull($this->order->calculateNextDueDate());
    }
    
    public function test_calculate_next_due_date_monthly_billing()
    {
        $activatedAt = now();
        $this->order->update([
            'activated_at' => $activatedAt,
            'billing_cycle' => 'monthly',
            'next_due_date' => null
        ]);
        
        $result = $this->order->calculateNextDueDate();
        $expected = $activatedAt->copy()->addMonth();
        
        $this->assertEquals($expected->format('Y-m-d'), $result->format('Y-m-d'));
    }
    
    public function test_calculate_next_due_date_quarterly_billing()
    {
        $activatedAt = now();
        $this->order->update([
            'activated_at' => $activatedAt,
            'billing_cycle' => 'quarterly',
            'next_due_date' => null
        ]);
        
        $result = $this->order->calculateNextDueDate();
        $expected = $activatedAt->copy()->addMonths(3);
        
        $this->assertEquals($expected->format('Y-m-d'), $result->format('Y-m-d'));
    }
    
    public function test_calculate_next_due_date_semi_annually_billing()
    {
        $activatedAt = now();
        $this->order->update([
            'activated_at' => $activatedAt,
            'billing_cycle' => 'semi_annually',
            'next_due_date' => null
        ]);
        
        $result = $this->order->calculateNextDueDate();
        $expected = $activatedAt->copy()->addMonths(6);
        
        $this->assertEquals($expected->format('Y-m-d'), $result->format('Y-m-d'));
    }
    
    public function test_calculate_next_due_date_annually_billing()
    {
        $activatedAt = now();
        $this->order->update([
            'activated_at' => $activatedAt,
            'billing_cycle' => 'annually',
            'next_due_date' => null
        ]);
        
        $result = $this->order->calculateNextDueDate();
        $expected = $activatedAt->copy()->addYear();
        
        $this->assertEquals($expected->format('Y-m-d'), $result->format('Y-m-d'));
    }
    
    public function test_calculate_next_due_date_with_existing_next_due_date()
    {
        $activatedAt = now();
        $nextDueDate = now()->addDays(30);
        
        $this->order->update([
            'activated_at' => $activatedAt,
            'billing_cycle' => 'monthly',
            'next_due_date' => $nextDueDate
        ]);
        
        $result = $this->order->calculateNextDueDate();
        $expected = $nextDueDate->copy()->addMonth();
        
        $this->assertEquals($expected->format('Y-m-d'), $result->format('Y-m-d'));
    }
    
    public function test_calculate_next_due_date_invalid_billing_cycle()
    {
        // Test method directly without updating database since invalid billing_cycle
        // would violate CHECK constraint
        $order = new Order([
            'activated_at' => now(),
            'billing_cycle' => 'invalid_cycle'
        ]);
        
        $this->assertNull($order->calculateNextDueDate());
    }
    
    public function test_active_scope()
    {
        $this->order->update(['status' => 'active']);
        
        $activeOrders = Order::active()->get();
        $this->assertEquals(1, $activeOrders->count());
        $this->assertEquals('active', $activeOrders->first()->status);
    }
    
    public function test_pending_scope()
    {
        $pendingOrders = Order::pending()->get();
        $this->assertEquals(1, $pendingOrders->count());
        $this->assertEquals('pending', $pendingOrders->first()->status);
    }
    
    public function test_by_status_scope()
    {
        $this->order->update(['status' => 'suspended']);
        
        $suspendedOrders = Order::byStatus('suspended')->get();
        $this->assertEquals(1, $suspendedOrders->count());
        $this->assertEquals('suspended', $suspendedOrders->first()->status);
    }
    
    public function test_amount_casting_to_decimal()
    {
        $this->assertIsString($this->order->amount);
        $this->assertEquals('1000000.00', $this->order->amount);
        
        $this->assertIsString($this->order->setup_fee);
        $this->assertEquals('100000.00', $this->order->setup_fee);
    }
    
    public function test_date_casting()
    {
        $this->assertInstanceOf(Carbon::class, $this->order->next_due_date);
        
        $this->order->update(['activated_at' => now()]);
        $this->order->refresh();
        $this->assertInstanceOf(Carbon::class, $this->order->activated_at);
        
        $this->order->update(['suspended_at' => now()]);
        $this->order->refresh();
        $this->assertInstanceOf(Carbon::class, $this->order->suspended_at);
    }
    
    public function test_order_details_casting_to_array()
    {
        $this->assertIsArray($this->order->order_details);
        $this->assertEquals('Website Basic', $this->order->order_details['product_name']);
        $this->assertEquals('monthly', $this->order->order_details['billing_cycle']);
        
        $newDetails = [
            'product_name' => 'Updated Product',
            'billing_cycle' => 'annually',
            'custom_field' => 'custom_value'
        ];
        
        $this->order->update(['order_details' => $newDetails]);
        $this->order->refresh();
        
        $this->assertIsArray($this->order->order_details);
        $this->assertEquals('Updated Product', $this->order->order_details['product_name']);
        $this->assertEquals('custom_value', $this->order->order_details['custom_field']);
    }
    
    public function test_order_number_generation_uniqueness()
    {
        $order1 = Order::create([
            'order_number' => 'ORD-UNIQUE-001',
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'amount' => 500000,
            'setup_fee' => 0,
            'billing_cycle' => 'monthly',
            'status' => 'pending',
            'next_due_date' => now()->addMonth(),
            'order_details' => [],
        ]);
        
        $order2 = Order::create([
            'order_number' => 'ORD-UNIQUE-002',
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'amount' => 500000,
            'setup_fee' => 0,
            'billing_cycle' => 'monthly',
            'status' => 'pending',
            'next_due_date' => now()->addMonth(),
            'order_details' => [],
        ]);
        
        $this->assertNotEquals($order1->order_number, $order2->order_number);
        $this->assertEquals('ORD-UNIQUE-001', $order1->order_number);
        $this->assertEquals('ORD-UNIQUE-002', $order2->order_number);
    }
}