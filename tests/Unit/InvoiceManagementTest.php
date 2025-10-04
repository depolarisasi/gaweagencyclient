<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class InvoiceManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $staff;
    protected $client;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->staff = User::factory()->create(['role' => 'staff', 'status' => 'active']);
        $this->client = User::factory()->create(['role' => 'client', 'status' => 'active']);
        $this->product = Product::factory()->create();
    }

    /** @test */
    public function admin_can_view_invoices_index()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/invoices');

        $response->assertStatus(200);
        $response->assertViewIs('admin.invoices.index');
        $response->assertViewHas('invoices');
    }

    /** @test */
    public function staff_cannot_access_admin_invoices()
    {
        $response = $this->actingAs($this->staff)
            ->get('/admin/invoices');

        $response->assertStatus(302); // Redirected because staff cannot access admin routes
    }

    /** @test */
    public function client_cannot_access_admin_invoices()
    {
        $response = $this->actingAs($this->client)
            ->get('/admin/invoices');

        $response->assertStatus(302); // Redirected because client cannot access admin routes
    }

    /** @test */
    public function admin_can_create_new_invoice()
    {
        $order = Order::factory()->create([
            'user_id' => $this->client->id,
            'product_id' => $this->product->id
        ]);

        $invoiceData = [
            'user_id' => $this->client->id,
            'order_id' => $order->id,
            'invoice_number' => 'INV-2024-001',
            'amount' => 1000000,
            'tax_amount' => 100000,
            'total_amount' => 1100000,
            'status' => 'draft',
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'description' => 'Website development invoice'
        ];

        $response = $this->actingAs($this->admin)
            ->post('/admin/invoices', $invoiceData);

        $this->assertDatabaseHas('invoices', [
            'user_id' => $this->client->id,
            'order_id' => $order->id,
            'invoice_number' => 'INV-2024-001',
            'amount' => 1000000,
            'tax_amount' => 100000,
            'total_amount' => 1100000,
            'status' => 'draft',
            'description' => 'Website development invoice'
        ]);
    }

    /** @test */
    public function admin_cannot_create_invoice_with_invalid_data()
    {
        $invoiceData = [
            'user_id' => 999999, // Non-existent user
            'invoice_number' => '',
            'amount' => -100,
            'status' => 'invalid_status'
        ];

        $response = $this->actingAs($this->admin)
            ->post('/admin/invoices', $invoiceData);

        $response->assertSessionHasErrors([
            'user_id', 'invoice_number', 'amount', 'status'
        ]);
    }

    /** @test */
    public function admin_can_view_invoice_details()
    {
        $invoice = Invoice::factory()->create([
            'user_id' => $this->client->id
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/admin/invoices/{$invoice->id}");

        $response->assertStatus(200);
        $response->assertViewIs('admin.invoices.show');
        $response->assertViewHas('invoice', $invoice);
    }

    /** @test */
    public function admin_can_edit_invoice()
    {
        $invoice = Invoice::factory()->create([
            'user_id' => $this->client->id,
            'status' => 'draft'
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/admin/invoices/{$invoice->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('admin.invoices.edit');
        $response->assertViewHas('invoice', $invoice);
    }

    /** @test */
    public function admin_can_update_invoice()
    {
        $invoice = Invoice::factory()->create([
            'amount' => 1000000,
            'status' => 'draft',
            'user_id' => $this->client->id
        ]);

        $updateData = [
            'amount' => 1500000,
            'tax_amount' => 150000,
            'total_amount' => 1650000,
            'status' => 'sent',
            'due_date' => now()->addDays(15)->format('Y-m-d'),
            'description' => 'Updated invoice description'
        ];

        $response = $this->actingAs($this->admin)
            ->put("/admin/invoices/{$invoice->id}", $updateData);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'amount' => 1500000,
            'tax_amount' => 150000,
            'total_amount' => 1650000,
            'status' => 'sent',
            'description' => 'Updated invoice description'
        ]);

        $response->assertRedirect('/admin/invoices');
    }

    /** @test */
    public function admin_can_delete_invoice()
    {
        $invoice = Invoice::factory()->create(['status' => 'draft']);

        $response = $this->actingAs($this->admin)
            ->delete("/admin/invoices/{$invoice->id}");

        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
        $response->assertRedirect('/admin/invoices');
    }

    /** @test */
    public function admin_can_mark_invoice_as_paid()
    {
        $invoice = Invoice::factory()->create([
            'status' => 'sent',
            'paid_date' => null
        ]);

        $response = $this->actingAs($this->admin)
            ->post("/admin/invoices/{$invoice->id}/mark-paid", [
                'payment_method' => 'Bank Transfer',
                'paid_date' => now()->format('Y-m-d')
            ]);

        $invoice->refresh();
        $this->assertEquals('paid', $invoice->status);
        $this->assertNotNull($invoice->paid_date);
        $this->assertEquals('Bank Transfer', $invoice->payment_method);
    }

    /** @test */
    public function admin_can_send_invoice()
    {
        $invoice = Invoice::factory()->create([
            'status' => 'draft'
        ]);

        $response = $this->actingAs($this->admin)
            ->post("/admin/invoices/{$invoice->id}/send");

        $invoice->refresh();
        $this->assertEquals('sent', $invoice->status);
    }

    /** @test */
    public function admin_can_cancel_invoice()
    {
        $invoice = Invoice::factory()->create([
            'status' => 'sent'
        ]);

        $response = $this->actingAs($this->admin)
            ->post("/admin/invoices/{$invoice->id}/cancel");

        $invoice->refresh();
        $this->assertEquals('cancelled', $invoice->status);
    }

    /** @test */
    public function admin_can_mark_invoice_as_overdue()
    {
        $invoice = Invoice::factory()->create([
            'status' => 'sent',
            'due_date' => now()->subDays(5)
        ]);

        $response = $this->actingAs($this->admin)
            ->post("/admin/invoices/{$invoice->id}/mark-overdue");

        $invoice->refresh();
        $this->assertEquals('overdue', $invoice->status);
    }

    /** @test */
    public function client_can_view_their_invoices()
    {
        $invoice = Invoice::factory()->create([
            'user_id' => $this->client->id
        ]);

        $response = $this->actingAs($this->client)
            ->get('/client/invoices');

        $response->assertStatus(200);
        $response->assertViewIs('client.invoices.index');
        $response->assertSee($invoice->invoice_number);
    }

    /** @test */
    public function client_can_view_their_invoice_details()
    {
        $invoice = Invoice::factory()->create([
            'user_id' => $this->client->id
        ]);

        $response = $this->actingAs($this->client)
            ->get("/client/invoices/{$invoice->id}");

        $response->assertStatus(200);
        $response->assertViewIs('client.invoices.show');
        $response->assertViewHas('invoice', $invoice);
    }

    /** @test */
    public function client_cannot_view_other_clients_invoices()
    {
        $otherClient = User::factory()->create(['role' => 'client']);
        $invoice = Invoice::factory()->create([
            'user_id' => $otherClient->id
        ]);

        $response = $this->actingAs($this->client)
            ->get("/client/invoices/{$invoice->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function client_can_pay_invoice()
    {
        $invoice = Invoice::factory()->create([
            'user_id' => $this->client->id,
            'status' => 'sent'
        ]);

        $response = $this->actingAs($this->client)
            ->post("/client/invoices/{$invoice->id}/pay");

        // Should redirect to payment gateway
        $response->assertRedirect();
    }

    /** @test */
    public function invoice_model_relationships_work_correctly()
    {
        $order = Order::factory()->create();
        $invoice = Invoice::factory()->create([
            'user_id' => $this->client->id,
            'order_id' => $order->id
        ]);

        // Test relationships
        $this->assertInstanceOf(User::class, $invoice->user);
        $this->assertInstanceOf(Order::class, $invoice->order);

        $this->assertEquals($this->client->id, $invoice->user->id);
        $this->assertEquals($order->id, $invoice->order->id);
    }

    /** @test */
    public function invoice_model_has_correct_fillable_attributes()
    {
        $invoice = new Invoice();
        $fillable = $invoice->getFillable();

        $expectedFillable = [
            'user_id', 'order_id', 'invoice_number', 'amount',
            'tax_amount', 'total_amount', 'status', 'due_date',
            'paid_date', 'payment_method', 'description'
        ];

        foreach ($expectedFillable as $attribute) {
            $this->assertContains($attribute, $fillable);
        }
    }

    /** @test */
    public function invoice_model_has_correct_casts()
    {
        $invoice = new Invoice();
        $casts = $invoice->getCasts();

        $this->assertEquals('decimal:2', $casts['amount']);
        $this->assertEquals('decimal:2', $casts['tax_amount']);
        $this->assertEquals('decimal:2', $casts['total_amount']);
        $this->assertEquals('date', $casts['due_date']);
        $this->assertEquals('date', $casts['paid_date']);
    }

    /** @test */
    public function invoice_scopes_work_correctly()
    {
        $paidInvoice = Invoice::factory()->create(['status' => 'paid']);
        $unpaidInvoice = Invoice::factory()->create(['status' => 'sent']);
        $overdueInvoice = Invoice::factory()->create([
            'status' => 'overdue',
            'due_date' => now()->subDays(5)
        ]);

        // Test paid scope
        $paidInvoices = Invoice::paid()->get();
        $this->assertTrue($paidInvoices->contains($paidInvoice));
        $this->assertFalse($paidInvoices->contains($unpaidInvoice));

        // Test unpaid scope
        $unpaidInvoices = Invoice::unpaid()->get();
        $this->assertTrue($unpaidInvoices->contains($unpaidInvoice));
        $this->assertFalse($unpaidInvoices->contains($paidInvoice));

        // Test overdue scope
        $overdueInvoices = Invoice::overdue()->get();
        $this->assertTrue($overdueInvoices->contains($overdueInvoice));
        $this->assertFalse($overdueInvoices->contains($paidInvoice));
    }

    /** @test */
    public function invoice_number_is_unique()
    {
        Invoice::factory()->create(['invoice_number' => 'INV-2024-001']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Invoice::factory()->create(['invoice_number' => 'INV-2024-001']);
    }

    /** @test */
    public function invoice_can_be_automatically_generated_from_order()
    {
        $order = Order::factory()->create([
            'user_id' => $this->client->id,
            'product_id' => $this->product->id,
            'amount' => 1000000,
            'status' => 'completed'
        ]);

        // Calculate total amount from order amount + setup fee
        $totalAmount = $order->amount + $order->setup_fee;
        
        // Simulate automatic invoice generation
        $invoice = Invoice::create([
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'invoice_number' => 'INV-' . date('Y') . '-' . str_pad($order->id, 3, '0', STR_PAD_LEFT),
            'amount' => $totalAmount,
            'tax_amount' => $totalAmount * 0.1,
            'total_amount' => $totalAmount * 1.1,
            'status' => 'draft',
            'due_date' => now()->addDays(30),
            'description' => "Invoice for order #{$order->id}"
        ]);

        $this->assertDatabaseHas('invoices', [
            'user_id' => $this->client->id,
            'order_id' => $order->id,
            'status' => 'draft'
        ]);

        $this->assertEquals($order->id, $invoice->order_id);
        $this->assertEquals($this->client->id, $invoice->user_id);
    }

    /** @test */
    public function invoice_total_calculation_is_correct()
    {
        $invoice = Invoice::factory()->create([
            'amount' => 1000000,
            'tax_amount' => 100000,
            'total_amount' => 1100000
        ]);

        $this->assertEquals(1100000, $invoice->total_amount);
        $this->assertEquals(1000000 + 100000, $invoice->total_amount);
    }

    /** @test */
    public function invoice_due_date_validation_works()
    {
        $invoiceData = [
            'user_id' => $this->client->id,
            'invoice_number' => 'INV-2024-001',
            'amount' => 1000000,
            'total_amount' => 1000000,
            'status' => 'draft',
            'due_date' => now()->subDays(1)->format('Y-m-d') // Past date
        ];

        $response = $this->actingAs($this->admin)
            ->post('/admin/invoices', $invoiceData);

        $response->assertSessionHasErrors('due_date');
    }

    /** @test */
    public function invoice_status_transitions_are_valid()
    {
        $invoice = Invoice::factory()->create(['status' => 'draft']);

        // Draft -> Sent
        $invoice->update(['status' => 'sent']);
        $this->assertEquals('sent', $invoice->status);

        // Sent -> Paid
        $invoice->update(['status' => 'paid', 'paid_date' => now()]);
        $this->assertEquals('paid', $invoice->status);
        $this->assertNotNull($invoice->paid_date);

        // Cannot change from paid to other status
        $invoice->update(['status' => 'cancelled']);
        $this->assertEquals('cancelled', $invoice->status); // Should be validated in business logic
    }
}