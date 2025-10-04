<?php

namespace Tests\Unit;

use App\Models\Invoice;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;
    
    protected $user;
    protected $product;
    protected $order;
    protected $invoice;
    
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
            'setup_fee' => 0,
            'billing_cycle' => 'monthly',
            'status' => 'pending',
            'next_due_date' => now()->addMonth(),
            'order_details' => [
                'product_name' => 'Website Basic',
                'billing_cycle' => 'monthly',
                'features' => ['Responsive Design', 'SEO Optimized'],
            ],
        ]);
        
        $this->invoice = Invoice::create([
            'invoice_number' => 'INV-20250911-123456',
            'user_id' => $this->user->id,
            'order_id' => $this->order->id,
            'amount' => 1000000,
            'tax_amount' => 110000,
            'total_amount' => 1110000,
            'status' => 'draft',
            'due_date' => now()->addDays(7),
        ]);
    }
    
    public function test_invoice_can_be_created_with_required_fields()
    {
        $invoice = Invoice::create([
            'invoice_number' => 'INV-TEST-001',
            'user_id' => $this->user->id,
            'order_id' => $this->order->id,
            'amount' => 500000,
            'tax_amount' => 55000,
            'total_amount' => 555000,
            'status' => 'draft',
            'due_date' => now()->addDays(7),
        ]);
        
        $this->assertDatabaseHas('invoices', [
            'invoice_number' => 'INV-TEST-001',
            'user_id' => $this->user->id,
            'order_id' => $this->order->id,
            'amount' => 500000,
            'tax_amount' => 55000,
            'total_amount' => 555000,
            'status' => 'draft'
        ]);
    }
    
    public function test_invoice_belongs_to_user()
    {
        $this->assertInstanceOf(User::class, $this->invoice->user);
        $this->assertEquals($this->user->id, $this->invoice->user->id);
    }
    
    public function test_invoice_belongs_to_order()
    {
        $this->assertInstanceOf(Order::class, $this->invoice->order);
        $this->assertEquals($this->order->id, $this->invoice->order->id);
    }
    
    public function test_formatted_amount_attribute()
    {
        $this->assertEquals('Rp 1.000.000', $this->invoice->formatted_amount);
    }
    
    public function test_is_paid_method()
    {
        $this->assertFalse($this->invoice->isPaid());
        
        $this->invoice->update(['status' => 'paid']);
        $this->assertTrue($this->invoice->isPaid());
    }
    
    public function test_is_pending_method()
    {
        $this->invoice->update(['status' => 'sent']);
        $this->assertTrue($this->invoice->isPending());
        
        $this->invoice->update(['status' => 'paid']);
        $this->assertFalse($this->invoice->isPending());
    }
    
    public function test_is_overdue_method()
    {
        // Invoice with future due date should not be overdue
        $this->invoice->update([
            'status' => 'sent',
            'due_date' => now()->addDays(5)
        ]);
        $this->assertFalse($this->invoice->isOverdue());
        
        // Invoice with past due date should be overdue
        $this->invoice->update([
            'status' => 'overdue',
            'due_date' => now()->subDays(5)
        ]);
        $this->assertTrue($this->invoice->isOverdue());
        
        // Paid invoice should not be overdue even if past due date
        $this->invoice->update([
            'status' => 'paid',
            'due_date' => now()->subDays(5)
        ]);
        $this->assertFalse($this->invoice->isOverdue());
    }
    
    public function test_is_cancelled_method()
    {
        $this->assertFalse($this->invoice->isCancelled());
        
        $this->invoice->update(['status' => 'cancelled']);
        $this->assertTrue($this->invoice->isCancelled());
    }
    
    public function test_days_until_due_attribute()
    {
        // For sent invoice with future due date
        $this->invoice->update([
            'status' => 'sent',
            'due_date' => now()->addDays(5)->toDateString()
        ]);
        $this->assertGreaterThanOrEqual(4, $this->invoice->days_until_due);
        $this->assertLessThanOrEqual(5, $this->invoice->days_until_due);
        
        // For overdue invoice
        $this->invoice->update([
            'status' => 'overdue',
            'due_date' => now()->subDays(3)->toDateString()
        ]);
        $this->assertLessThan(0, $this->invoice->days_until_due);
        
        // For paid invoice should return null
        $this->invoice->update(['status' => 'paid']);
        $this->assertNull($this->invoice->days_until_due);
    }
    
    public function test_status_badge_class_attribute()
    {
        $this->invoice->update(['status' => 'paid']);
        $this->assertEquals('badge-success', $this->invoice->status_badge_class);
        
        $this->invoice->update([
            'status' => 'sent',
            'due_date' => now()->addDays(5)
        ]);
        $this->assertEquals('badge-warning', $this->invoice->status_badge_class);
        
        $this->invoice->update([
            'status' => 'overdue',
            'due_date' => now()->subDays(5)
        ]);
        $this->assertEquals('badge-danger', $this->invoice->status_badge_class);
        
        $this->invoice->update(['status' => 'cancelled']);
        $this->assertEquals('badge-secondary', $this->invoice->status_badge_class);
    }
    
    public function test_status_text_attribute()
    {
        $this->invoice->update(['status' => 'sent']);
        $this->assertEquals('Menunggu Pembayaran', $this->invoice->status_text);
        
        $this->invoice->update(['status' => 'paid']);
        $this->assertEquals('Lunas', $this->invoice->status_text);
        
        $this->invoice->update(['status' => 'cancelled']);
        $this->assertEquals('Dibatalkan', $this->invoice->status_text);
        
        $this->invoice->update(['status' => 'overdue']);
        $this->assertEquals('Kedaluwarsa', $this->invoice->status_text);
    }
    
    public function test_has_tripay_reference_method()
    {
        $this->assertFalse($this->invoice->hasTripayReference());
        
        $this->invoice->update(['tripay_reference' => 'TR123456']);
        $this->assertTrue($this->invoice->hasTripayReference());
    }
    
    public function test_tripay_reference_can_be_set()
    {
        $this->invoice->update(['tripay_reference' => 'TR123456']);
        $this->assertEquals('TR123456', $this->invoice->tripay_reference);
        
        $this->invoice->update(['payment_method' => 'BRIVA']);
        $this->assertEquals('BRIVA', $this->invoice->payment_method);
    }
    
    public function test_tripay_response_can_be_set()
    {
        // Test JSON field tripay_response
        $response = ['status' => 'PAID', 'amount' => 1000000];
        $this->invoice->update(['tripay_response' => $response]);
        $this->invoice->refresh();
        
        $this->assertNotNull($this->invoice->tripay_response);
        $this->assertIsArray($this->invoice->tripay_response);
        $this->assertEquals('PAID', $this->invoice->tripay_response['status']);
    }
    
    public function test_paid_scope()
    {
        Invoice::create([
            'invoice_number' => 'INV-PAID-001',
            'user_id' => $this->user->id,
            'order_id' => $this->order->id,
            'amount' => 500000,
            'tax_amount' => 55000,
            'total_amount' => 555000,
            'status' => 'paid',
            'due_date' => now()->addDays(7),
        ]);
        
        $paidInvoices = Invoice::paid()->get();
        $this->assertEquals(1, $paidInvoices->count());
        $this->assertEquals('paid', $paidInvoices->first()->status);
    }
    
    public function test_pending_scope()
    {
        $this->invoice->update(['status' => 'sent']);
        
        $pendingInvoices = Invoice::pending()->get();
        $this->assertEquals(1, $pendingInvoices->count());
        $this->assertEquals('sent', $pendingInvoices->first()->status);
    }
    
    public function test_overdue_scope()
    {
        $this->invoice->update([
            'status' => 'overdue',
            'due_date' => now()->subDays(5)
        ]);
        
        $overdueInvoices = Invoice::overdue()->get();
        $this->assertEquals(1, $overdueInvoices->count());
        $this->assertTrue($overdueInvoices->first()->isOverdue());
    }
    
    public function test_by_status_scope()
    {
        $this->invoice->update(['status' => 'cancelled']);
        
        $cancelledInvoices = Invoice::byStatus('cancelled')->get();
        $this->assertEquals(1, $cancelledInvoices->count());
        $this->assertEquals('cancelled', $cancelledInvoices->first()->status);
    }
    
    public function test_amount_casting_to_decimal()
    {
        $this->assertIsString($this->invoice->amount);
        $this->assertEquals('1000000.00', $this->invoice->amount);
        
        $this->assertIsString($this->invoice->tax_amount);
        $this->assertEquals('110000.00', $this->invoice->tax_amount);
        
        $this->assertIsString($this->invoice->total_amount);
        $this->assertEquals('1110000.00', $this->invoice->total_amount);
    }
    
    public function test_due_date_casting()
    {
        $this->assertInstanceOf(Carbon::class, $this->invoice->due_date);
        
        // Test updating due_date
        $newDate = now()->addDays(10)->toDateString();
        $this->invoice->update(['due_date' => $newDate]);
        $this->invoice->refresh();
        
        $this->assertInstanceOf(Carbon::class, $this->invoice->due_date);
        $this->assertEquals($newDate, $this->invoice->due_date->toDateString());
    }
}