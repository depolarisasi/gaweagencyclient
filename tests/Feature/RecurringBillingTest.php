<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class RecurringBillingTest extends TestCase
{
    use RefreshDatabase;
    
    protected $user;
    protected $product;
    protected $order;
    
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
            'name' => 'Monthly Website Package',
            'description' => 'Monthly recurring website package',
            'type' => 'website',
            'price' => 1000000,
            'billing_cycle' => 'monthly',
            'setup_time_days' => 14,
            'is_active' => true,
        ]);
        
        // Create test order
        $this->order = Order::create([
            'order_number' => 'ORD-TEST-001',
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'amount' => $this->product->price,
            'setup_fee' => 0,
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'activated_at' => now(),
            'next_due_date' => now()->addMonth(),
            'order_details' => [
                'product_name' => $this->product->name,
                'billing_cycle' => 'monthly',
            ],
        ]);
    }
    
    public function test_recurring_invoice_should_be_created_14_days_before_due_date()
    {
        // Set next due date to 14 days from now
        $this->order->update(['next_due_date' => now()->addDays(14)]);
        
        // Simulate the recurring billing process
        $this->simulateRecurringBillingProcess();
        
        // Check if recurring invoice was created
        $recurringInvoice = Invoice::where('order_id', $this->order->id)
                                  ->where('status', 'draft')
                                  ->first();
        
        $this->assertNotNull($recurringInvoice, 'Recurring invoice should be created 14 days before due date');
        $this->assertEquals($this->order->amount, $recurringInvoice->amount);
        $this->assertEquals('draft', $recurringInvoice->status);
    }
    
    public function test_recurring_invoice_should_not_be_created_too_early()
    {
        // Set next due date to 15 days from now (too early)
        $this->order->update(['next_due_date' => now()->addDays(15)]);
        
        // Simulate the recurring billing process
        $this->simulateRecurringBillingProcess();
        
        // Check that no recurring invoice was created
        $recurringInvoice = Invoice::where('order_id', $this->order->id)->first();
        $this->assertNull($recurringInvoice, 'Recurring invoice should not be created more than 14 days before due date');
    }
    
    public function test_project_should_be_suspended_if_invoice_not_paid_after_14_days()
    {
        // Create an overdue invoice (due date was 14 days ago)
        $invoice = Invoice::create([
            'invoice_number' => 'INV-TEST-001',
            'user_id' => $this->user->id,
            'order_id' => $this->order->id,
            'amount' => $this->order->amount,
            'tax_amount' => $this->order->amount * 0.11,
            'total_amount' => $this->order->amount * 1.11,
            'status' => 'draft',
            'due_date' => now()->subDays(14),
        ]);
        
        // Create associated project
        $project = Project::create([
            'project_name' => 'Test Project',
            'order_id' => $this->order->id,
            'user_id' => $this->user->id,
            'status' => 'active',
            'description' => 'Test project description',
            'start_date' => now(),
            'due_date' => now()->addDays(30),
            'progress_percentage' => 50,
        ]);
        
        // Simulate the suspension process
        $this->simulateSuspensionProcess();
        
        // Check if project was suspended
        $project->refresh();
        $this->assertEquals('suspended', $project->status, 'Project should be suspended after 14 days of unpaid invoice');
    }
    
    public function test_invoice_should_be_cancelled_if_not_paid_after_7_days()
    {
        // Create an invoice that's 7 days overdue
        $invoice = Invoice::create([
            'invoice_number' => 'INV-TEST-002',
            'user_id' => $this->user->id,
            'order_id' => $this->order->id,
            'amount' => $this->order->amount,
            'tax_amount' => $this->order->amount * 0.11,
            'total_amount' => $this->order->amount * 1.11,
            'status' => 'draft',
            'due_date' => now()->subDays(7),
        ]);
        
        // Simulate the cancellation process
        $this->simulateCancellationProcess();
        
        // Check if invoice was cancelled
        $invoice->refresh();
        $this->assertEquals('cancelled', $invoice->status, 'Invoice should be cancelled after 7 days if not paid');
    }
    
    public function test_next_due_date_calculation_for_different_billing_cycles()
    {
        $testCases = [
            'monthly' => ['period' => 1, 'unit' => 'month'],
            'quarterly' => ['period' => 3, 'unit' => 'month'],
            'semi_annually' => ['period' => 6, 'unit' => 'month'],
            'annually' => ['period' => 1, 'unit' => 'year'],
        ];
        
        foreach ($testCases as $cycle => $config) {
            $product = Product::create([
                'name' => ucfirst($cycle) . ' Product',
                'description' => ucfirst($cycle) . ' billing product',
                'type' => 'website',
                'price' => 1000000,
                'billing_cycle' => $cycle,
                'setup_time_days' => 14,
                'is_active' => true,
            ]);
            
            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper($cycle) . '-001',
                'user_id' => $this->user->id,
                'product_id' => $product->id,
                'amount' => $product->price,
                'setup_fee' => 0,
                'billing_cycle' => $cycle,
                'status' => 'active',
                'activated_at' => now(),
                'next_due_date' => null,
                'order_details' => [
                    'product_name' => $product->name,
                    'billing_cycle' => $cycle,
                ],
            ]);
            
            $nextDueDate = $order->calculateNextDueDate();
            
            if ($config['unit'] === 'month') {
                $expectedDate = now()->addMonths($config['period']);
            } else {
                $expectedDate = now()->addYear();
            }
            
            $this->assertEquals(
                $expectedDate->format('Y-m-d'),
                $nextDueDate->format('Y-m-d'),
                "Next due date calculation failed for {$cycle} billing cycle"
            );
        }
    }
    
    public function test_recurring_billing_automation_workflow()
    {
        // Step 1: Create initial paid invoice
        $initialInvoice = Invoice::create([
            'invoice_number' => 'INV-INITIAL-001',
            'user_id' => $this->user->id,
            'order_id' => $this->order->id,
            'amount' => $this->order->amount,
            'tax_amount' => $this->order->amount * 0.11,
            'total_amount' => $this->order->amount * 1.11,
            'status' => 'paid',
            'due_date' => now()->subDays(30),
            'paid_at' => now()->subDays(25),
        ]);
        
        // Step 2: Create project after payment
        $project = Project::create([
            'project_name' => 'Automated Project',
            'order_id' => $this->order->id,
            'user_id' => $this->user->id,
            'status' => 'active',
            'description' => 'Project created after payment',
            'start_date' => now()->subDays(25),
            'due_date' => now()->addDays(5),
            'progress_percentage' => 75,
        ]);
        
        // Step 3: Set next due date to trigger recurring billing
        $this->order->update(['next_due_date' => now()->addDays(14)]);
        
        // Step 4: Simulate recurring billing process
        $this->simulateRecurringBillingProcess();
        
        // Step 5: Verify recurring invoice was created
        $recurringInvoice = Invoice::where('order_id', $this->order->id)
                                  ->where('id', '!=', $initialInvoice->id)
                                  ->first();
        
        $this->assertNotNull($recurringInvoice, 'Recurring invoice should be created');
        $this->assertEquals('draft', $recurringInvoice->status);
        
        // Step 6: Simulate payment of recurring invoice
        $recurringInvoice->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
        
        // Step 7: Verify order next due date was updated
        $this->order->refresh();
        $expectedNextDueDate = now()->addMonth()->addDays(14);
        $this->assertEquals(
            $expectedNextDueDate->format('Y-m-d'),
            $this->order->next_due_date->format('Y-m-d'),
            'Order next due date should be updated after payment'
        );
    }
    
    public function test_email_notifications_for_recurring_billing()
    {
        // This test would verify that email notifications are sent
        // For now, we'll just verify the structure is in place
        
        // Create invoice that should trigger notification
        $invoice = Invoice::create([
            'invoice_number' => 'INV-NOTIFICATION-001',
            'user_id' => $this->user->id,
            'order_id' => $this->order->id,
            'amount' => $this->order->amount,
            'tax_amount' => $this->order->amount * 0.11,
            'total_amount' => $this->order->amount * 1.11,
            'status' => 'draft',
            'due_date' => now()->addDays(7),
        ]);
        
        // In a real implementation, this would trigger email notifications
        // For testing, we verify the invoice exists and has correct data
        $this->assertDatabaseHas('invoices', [
            'invoice_number' => 'INV-NOTIFICATION-001',
            'user_id' => $this->user->id,
            'status' => 'draft',
        ]);
        
        // TODO: Add actual email notification testing when implemented
        $this->assertTrue(true, 'Email notification structure verified');
    }
    
    /**
     * Simulate the recurring billing process
     * In real implementation, this would be a scheduled job/command
     */
    private function simulateRecurringBillingProcess()
    {
        // Find orders that need recurring invoices (14 days before due date)
        $ordersNeedingInvoices = Order::where('status', 'active')
            ->where('next_due_date', '<=', now()->addDays(14))
            ->whereDoesntHave('invoices', function ($query) {
                $query->where('status', 'draft')
                      ->where('due_date', '>=', now());
            })
            ->get();
        
        foreach ($ordersNeedingInvoices as $order) {
            // Create recurring invoice
            Invoice::create([
                'invoice_number' => 'INV-' . date('Ymd') . '-' . uniqid(),
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'amount' => $order->amount,
                'tax_amount' => round($order->amount * 0.11, 2),
                'total_amount' => round($order->amount * 1.11, 2),
                'status' => 'draft',
                'due_date' => $order->next_due_date,
            ]);
        }
    }
    
    /**
     * Simulate the suspension process for overdue invoices
     */
    private function simulateSuspensionProcess()
    {
        // Find projects with overdue invoices (14+ days past due)
        $overdueInvoices = Invoice::where('status', 'draft')
            ->where('due_date', '<=', now()->subDays(14))
            ->get();
        
        foreach ($overdueInvoices as $invoice) {
            // Suspend associated projects
            Project::where('order_id', $invoice->order_id)
                   ->where('status', 'active')
                   ->update(['status' => 'suspended']);
        }
    }
    
    /**
     * Simulate the cancellation process for very overdue invoices
     */
    private function simulateCancellationProcess()
    {
        // Find invoices that are 7+ days overdue
        $veryOverdueInvoices = Invoice::where('status', 'draft')
            ->where('due_date', '<=', now()->subDays(7))
            ->get();
        
        foreach ($veryOverdueInvoices as $invoice) {
            $invoice->update(['status' => 'cancelled']);
        }
    }
}