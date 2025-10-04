<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Project;
use App\Console\Commands\GenerateRecurringInvoices;
use App\Console\Commands\CancelExpiredInvoices;
use App\Console\Commands\SuspendOverdueProjects;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class RecurringBillingTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product;
    protected $activeOrder;
    protected $expiredInvoice;
    protected $overdueProject;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->user = User::factory()->create([
            'role' => 'client',
            'email' => 'test@example.com'
        ]);
        
        $this->product = Product::factory()->create([
            'name' => 'Monthly Package',
            'price' => 500000,
            'billing_cycle' => 'monthly',
            'is_active' => true
        ]);
        
        // Create active order that needs renewal
        $this->activeOrder = Order::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'amount' => 500000,
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'next_due_date' => now()->subDays(1), // Due yesterday
            'is_renewal' => false
        ]);
        
        // Create expired invoice
        $this->expiredInvoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'order_id' => $this->activeOrder->id,
            'amount' => 500000,
            'status' => 'pending',
            'due_date' => now()->subDays(8), // Expired 8 days ago
            'is_renewal' => false
        ]);
        
        // Create project that should be suspended
        $this->overdueProject = Project::factory()->create([
            'user_id' => $this->user->id,
            'order_id' => $this->activeOrder->id,
            'status' => 'active'
        ]);
    }

    /** @test */
    public function it_generates_recurring_invoices_for_due_orders()
    {
        $this->assertEquals(1, Invoice::count());
        
        Artisan::call('invoices:generate-recurring');
        
        // Should create a new renewal invoice
        $this->assertEquals(2, Invoice::count());
        
        $renewalInvoice = Invoice::where('is_renewal', true)->first();
        $this->assertNotNull($renewalInvoice);
        $this->assertEquals($this->user->id, $renewalInvoice->user_id);
        $this->assertEquals($this->activeOrder->id, $renewalInvoice->order_id);
        $this->assertEquals(500000, $renewalInvoice->amount);
        $this->assertEquals('pending', $renewalInvoice->status);
        $this->assertTrue($renewalInvoice->is_renewal);
        
        // Due date should be 7 days from now
        $expectedDueDate = now()->addDays(7)->format('Y-m-d');
        $this->assertEquals($expectedDueDate, $renewalInvoice->due_date->format('Y-m-d'));
    }

    /** @test */
    public function it_updates_order_next_due_date_after_generating_renewal()
    {
        $originalNextDueDate = $this->activeOrder->next_due_date;
        
        Artisan::call('invoices:generate-recurring');
        
        $this->activeOrder->refresh();
        $this->assertNotEquals($originalNextDueDate, $this->activeOrder->next_due_date);
        
        // Next due date should be one month from original due date
        $expectedNextDueDate = $originalNextDueDate->addMonth();
        $this->assertEquals(
            $expectedNextDueDate->format('Y-m-d'),
            $this->activeOrder->next_due_date->format('Y-m-d')
        );
    }

    /** @test */
    public function it_does_not_generate_recurring_invoices_for_inactive_orders()
    {
        $this->activeOrder->update(['status' => 'cancelled']);
        
        Artisan::call('invoices:generate-recurring');
        
        // Should not create any new invoices
        $this->assertEquals(1, Invoice::count());
        $this->assertEquals(0, Invoice::where('is_renewal', true)->count());
    }

    /** @test */
    public function it_does_not_generate_recurring_invoices_for_future_due_dates()
    {
        $this->activeOrder->update(['next_due_date' => now()->addDays(5)]);
        
        Artisan::call('invoices:generate-recurring');
        
        // Should not create any new invoices
        $this->assertEquals(1, Invoice::count());
        $this->assertEquals(0, Invoice::where('is_renewal', true)->count());
    }

    /** @test */
    public function it_cancels_expired_invoices_after_7_days()
    {
        $this->assertEquals('pending', $this->expiredInvoice->status);
        
        Artisan::call('invoices:cancel-expired');
        
        $this->expiredInvoice->refresh();
        $this->assertEquals('cancelled', $this->expiredInvoice->status);
        $this->assertNotNull($this->expiredInvoice->cancelled_at);
        $this->assertEquals('Auto-cancelled: Payment not received within 7 days', $this->expiredInvoice->cancellation_reason);
    }

    /** @test */
    public function it_does_not_cancel_invoices_that_are_not_expired()
    {
        // Create invoice that expires tomorrow
        $futureInvoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'order_id' => $this->activeOrder->id,
            'status' => 'pending',
            'due_date' => now()->addDay()
        ]);
        
        Artisan::call('invoices:cancel-expired');
        
        $futureInvoice->refresh();
        $this->assertEquals('pending', $futureInvoice->status);
        $this->assertNull($futureInvoice->cancelled_at);
    }

    /** @test */
    public function it_does_not_cancel_already_paid_invoices()
    {
        $this->expiredInvoice->update([
            'status' => 'paid',
            'paid_at' => now()->subDays(5)
        ]);
        
        Artisan::call('invoices:cancel-expired');
        
        $this->expiredInvoice->refresh();
        $this->assertEquals('paid', $this->expiredInvoice->status);
        $this->assertNull($this->expiredInvoice->cancelled_at);
    }

    /** @test */
    public function it_suspends_projects_with_overdue_invoices_after_14_days()
    {
        // Create invoice that's overdue for 15 days
        $overdueInvoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'order_id' => $this->activeOrder->id,
            'status' => 'pending',
            'due_date' => now()->subDays(15),
            'is_renewal' => true
        ]);
        
        $this->assertEquals('active', $this->overdueProject->status);
        
        Artisan::call('projects:suspend-overdue');
        
        $this->overdueProject->refresh();
        $this->assertEquals('suspended', $this->overdueProject->status);
        $this->assertNotNull($this->overdueProject->suspended_at);
        $this->assertEquals('Auto-suspended: Payment overdue for more than 14 days', $this->overdueProject->suspension_reason);
        $this->assertEquals($overdueInvoice->id, $this->overdueProject->overdue_invoice_id);
    }

    /** @test */
    public function it_does_not_suspend_projects_with_recent_overdue_invoices()
    {
        // Create invoice that's overdue for only 10 days
        Invoice::factory()->create([
            'user_id' => $this->user->id,
            'order_id' => $this->activeOrder->id,
            'status' => 'pending',
            'due_date' => now()->subDays(10),
            'is_renewal' => true
        ]);
        
        Artisan::call('projects:suspend-overdue');
        
        $this->overdueProject->refresh();
        $this->assertEquals('active', $this->overdueProject->status);
        $this->assertNull($this->overdueProject->suspended_at);
    }

    /** @test */
    public function it_does_not_suspend_already_suspended_projects()
    {
        $this->overdueProject->update([
            'status' => 'suspended',
            'suspended_at' => now()->subDays(5)
        ]);
        
        // Create overdue invoice
        Invoice::factory()->create([
            'user_id' => $this->user->id,
            'order_id' => $this->activeOrder->id,
            'status' => 'pending',
            'due_date' => now()->subDays(15),
            'is_renewal' => true
        ]);
        
        $originalSuspendedAt = $this->overdueProject->suspended_at;
        
        Artisan::call('projects:suspend-overdue');
        
        $this->overdueProject->refresh();
        $this->assertEquals('suspended', $this->overdueProject->status);
        $this->assertEquals($originalSuspendedAt, $this->overdueProject->suspended_at);
    }

    /** @test */
    public function it_handles_different_billing_cycles_for_recurring_invoices()
    {
        // Create quarterly order
        $quarterlyOrder = Order::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'billing_cycle' => 'quarterly',
            'status' => 'active',
            'next_due_date' => now()->subDays(1)
        ]);
        
        Artisan::call('invoices:generate-recurring');
        
        $quarterlyOrder->refresh();
        
        // Next due date should be 3 months from original due date
        $expectedNextDueDate = now()->subDays(1)->addMonths(3);
        $this->assertEquals(
            $expectedNextDueDate->format('Y-m-d'),
            $quarterlyOrder->next_due_date->format('Y-m-d')
        );
    }

    /** @test */
    public function it_includes_tax_in_recurring_invoices()
    {
        Artisan::call('invoices:generate-recurring');
        
        $renewalInvoice = Invoice::where('is_renewal', true)->first();
        
        $expectedTax = round(500000 * 0.11, 2); // 11% tax
        $expectedTotal = round(500000 + $expectedTax, 2);
        
        $this->assertEquals(500000, $renewalInvoice->amount);
        $this->assertEquals($expectedTax, $renewalInvoice->tax_amount);
        $this->assertEquals($expectedTotal, $renewalInvoice->total_amount);
    }

    /** @test */
    public function it_logs_command_execution_results()
    {
        // Test that commands run without errors
        $exitCode1 = Artisan::call('invoices:generate-recurring');
        $exitCode2 = Artisan::call('invoices:cancel-expired');
        $exitCode3 = Artisan::call('projects:suspend-overdue');
        
        $this->assertEquals(0, $exitCode1);
        $this->assertEquals(0, $exitCode2);
        $this->assertEquals(0, $exitCode3);
    }

    /** @test */
    public function it_handles_edge_case_of_multiple_overdue_invoices()
    {
        // Create multiple overdue invoices for the same project
        Invoice::factory()->create([
            'user_id' => $this->user->id,
            'order_id' => $this->activeOrder->id,
            'status' => 'pending',
            'due_date' => now()->subDays(20),
            'is_renewal' => true
        ]);
        
        Invoice::factory()->create([
            'user_id' => $this->user->id,
            'order_id' => $this->activeOrder->id,
            'status' => 'pending',
            'due_date' => now()->subDays(15),
            'is_renewal' => true
        ]);
        
        Artisan::call('projects:suspend-overdue');
        
        $this->overdueProject->refresh();
        $this->assertEquals('suspended', $this->overdueProject->status);
        
        // Should reference the oldest overdue invoice
        $oldestOverdueInvoice = Invoice::where('order_id', $this->activeOrder->id)
            ->where('status', 'pending')
            ->where('due_date', '<', now()->subDays(14))
            ->orderBy('due_date')
            ->first();
            
        $this->assertEquals($oldestOverdueInvoice->id, $this->overdueProject->overdue_invoice_id);
    }
}