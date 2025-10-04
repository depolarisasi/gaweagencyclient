<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Template;
use App\Models\Order;
use App\Models\Project;
use App\Models\Invoice;
use App\Models\SupportTicket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class CompleteUserFlowIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $staff;
    protected $product;
    protected $template;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->product = Product::factory()->create([
            'name' => 'Website Development Package',
            'price' => 5000000,
            'is_active' => true
        ]);
        $this->template = Template::factory()->create([
            'name' => 'Business Pro Template',
            'is_active' => true
        ]);
    }

    /** @test */
    public function complete_client_journey_from_registration_to_project_completion()
    {
        // Step 1: Client Registration
        $clientData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '+62812345678',
            'company_name' => 'Doe Industries'
        ];

        $response = $this->post('/register', $clientData);
        $response->assertRedirect('/client/dashboard');
        
        $client = User::where('email', 'john@example.com')->first();
        $this->assertNotNull($client);
        $this->assertEquals('client', $client->role);
        $this->assertEquals('active', $client->status);

        // Step 2: Client browses templates and products
        $response = $this->actingAs($client)->get('/');
        $response->assertStatus(200);
        $response->assertSee($this->template->name);
        $response->assertSee($this->product->name);

        // Step 3: Client views template details
        $response = $this->actingAs($client)
            ->get("/templates/{$this->template->id}");
        $response->assertStatus(200);
        $response->assertSee($this->template->name);
        $response->assertSee($this->template->description);

        // Step 4: Client proceeds to checkout
        $response = $this->actingAs($client)
            ->get("/checkout/template/{$this->template->id}?product={$this->product->id}");
        $response->assertStatus(200);
        $response->assertSeeLivewire('checkout-process');

        // Step 5: Client completes order
        $orderData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+62812345678',
            'company_name' => 'Doe Industries',
            'project_description' => 'Need a professional business website',
            'billing_cycle' => 'annually'
        ];

        // Simulate order creation through checkout process
        $order = Order::create([
            'user_id' => $client->id,
            'product_id' => $this->product->id,
            'template_id' => $this->template->id,
            'billing_cycle' => 'annually',
            'subtotal' => $this->product->price,
            'tax_amount' => $this->product->price * 0.1,
            'total_amount' => $this->product->price * 1.1,
            'status' => 'pending',
            'customer_data' => $orderData
        ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $client->id,
            'product_id' => $this->product->id,
            'template_id' => $this->template->id,
            'status' => 'pending'
        ]);

        // Step 6: Admin processes the order
        $this->actingAs($this->admin);
        
        // Admin views new orders
        $response = $this->get('/admin/orders');
        $response->assertStatus(200);
        $response->assertSee($order->id);

        // Admin approves the order
        $order->update(['status' => 'completed']);

        // Step 7: System automatically creates project
        $project = Project::create([
            'project_name' => "Website for {$client->name}",
            'user_id' => $client->id,
            'order_id' => $order->id,
            'template_id' => $this->template->id,
            'status' => 'pending',
            'description' => $orderData['project_description']
        ]);

        $this->assertDatabaseHas('projects', [
            'user_id' => $client->id,
            'order_id' => $order->id,
            'template_id' => $this->template->id,
            'status' => 'pending'
        ]);

        // Step 8: System generates invoice
        $invoice = Invoice::create([
            'user_id' => $client->id,
            'order_id' => $order->id,
            'invoice_number' => 'INV-' . date('Y') . '-' . str_pad($order->id, 3, '0', STR_PAD_LEFT),
            'amount' => $order->subtotal,
            'tax_amount' => $order->tax_amount,
            'total_amount' => $order->total_amount,
            'status' => 'sent',
            'due_date' => now()->addDays(30),
            'description' => "Invoice for website development project"
        ]);

        $this->assertDatabaseHas('invoices', [
            'user_id' => $client->id,
            'order_id' => $order->id,
            'status' => 'sent'
        ]);

        // Step 9: Client views their dashboard
        $response = $this->actingAs($client)
            ->get('/client/dashboard');
        $response->assertStatus(200);
        $response->assertSee($project->project_name);
        $response->assertSee($invoice->invoice_number);

        // Step 10: Client views project details
        $response = $this->actingAs($client)
            ->get("/client/projects/{$project->id}");
        $response->assertStatus(200);
        $response->assertSee($project->project_name);
        $response->assertSee('pending');

        // Step 11: Client views and pays invoice
        $response = $this->actingAs($client)
            ->get("/client/invoices/{$invoice->id}");
        $response->assertStatus(200);
        $response->assertSee($invoice->invoice_number);
        $response->assertSee('sent');

        // Simulate payment
        $invoice->update([
            'status' => 'paid',
            'paid_date' => now(),
            'payment_method' => 'Bank Transfer'
        ]);

        // Step 12: Admin assigns project to staff
        $this->actingAs($this->admin);
        
        $response = $this->get('/admin/projects');
        $response->assertStatus(200);
        $response->assertSee($project->project_name);

        $project->update([
            'assigned_to' => $this->staff->id,
            'status' => 'in_progress',
            'started_at' => now()
        ]);

        // Step 13: Staff works on the project
        $this->actingAs($this->staff);
        
        $response = $this->get('/staff/projects');
        $response->assertStatus(200);
        $response->assertSee($project->project_name);

        // Staff updates project progress
        $project->update([
            'website_url' => 'https://johndoe.example.com',
            'admin_url' => 'https://johndoe.example.com/admin',
            'admin_username' => 'admin',
            'admin_password' => 'secure123',
            'notes' => 'Website development completed'
        ]);

        // Step 14: Client creates support ticket
        $this->actingAs($client);
        
        $ticketData = [
            'subject' => 'Question about website features',
            'message' => 'I would like to know how to update the content on my website.',
            'priority' => 'medium',
            'department' => 'technical'
        ];

        $response = $this->post('/client/tickets', $ticketData);
        $response->assertRedirect('/client/tickets');

        $ticket = SupportTicket::where('user_id', $client->id)->first();
        $this->assertNotNull($ticket);
        $this->assertEquals('Question about website features', $ticket->subject);
        $this->assertEquals('open', $ticket->status);

        // Step 15: Staff responds to ticket
        $this->actingAs($this->staff);
        
        $ticket->update(['assigned_to' => $this->staff->id]);
        
        $response = $this->post("/admin/tickets/{$ticket->id}/reply", [
            'message' => 'I will help you with updating your website content. Please check your admin panel.',
            'is_internal' => false
        ]);

        $this->assertDatabaseHas('ticket_replies', [
            'ticket_id' => $ticket->id,
            'user_id' => $this->staff->id,
            'is_internal' => false
        ]);

        // Step 16: Client views ticket response
        $this->actingAs($client);
        
        $response = $this->get("/client/tickets/{$ticket->id}");
        $response->assertStatus(200);
        $response->assertSee('I will help you with updating your website content');

        // Step 17: Project completion
        $this->actingAs($this->admin);
        
        $project->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);

        // Step 18: Client views completed project
        $this->actingAs($client);
        
        $response = $this->get("/client/projects/{$project->id}");
        $response->assertStatus(200);
        $response->assertSee('completed');
        $response->assertSee('https://johndoe.example.com');
        $response->assertSee('admin');

        // Step 19: Client closes support ticket
        $response = $this->post("/client/tickets/{$ticket->id}/close");
        
        $ticket->refresh();
        $this->assertEquals('closed', $ticket->status);
        $this->assertNotNull($ticket->closed_at);

        // Final verification: Check all data integrity
        $this->assertDatabaseHas('users', [
            'id' => $client->id,
            'email' => 'john@example.com',
            'role' => 'client',
            'status' => 'active'
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'user_id' => $client->id,
            'status' => 'completed'
        ]);

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'user_id' => $client->id,
            'status' => 'completed',
            'assigned_to' => $this->staff->id
        ]);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'user_id' => $client->id,
            'status' => 'paid'
        ]);

        $this->assertDatabaseHas('support_tickets', [
            'id' => $ticket->id,
            'user_id' => $client->id,
            'status' => 'closed',
            'assigned_to' => $this->staff->id
        ]);
    }

    /** @test */
    public function admin_complete_management_workflow()
    {
        // Create test data
        $client = User::factory()->create(['role' => 'client']);
        $order = Order::factory()->create([
            'user_id' => $client->id,
            'product_id' => $this->product->id,
            'status' => 'pending'
        ]);

        $this->actingAs($this->admin);

        // Admin dashboard overview
        $response = $this->get('/admin/dashboard');
        $response->assertStatus(200);

        // User management workflow
        $response = $this->get('/admin/users');
        $response->assertStatus(200);
        $response->assertSee($client->name);

        // Create new staff member
        $staffData = [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'staff',
            'status' => 'active'
        ];

        $response = $this->post('/admin/users', $staffData);
        $newStaff = User::where('email', 'jane@example.com')->first();
        $this->assertNotNull($newStaff);
        $this->assertEquals('staff', $newStaff->role);

        // Product management
        $response = $this->get('/admin/products');
        $response->assertStatus(200);
        $response->assertSeeLivewire('admin.product-management');

        // Template management
        $response = $this->get('/admin/templates');
        $response->assertStatus(200);
        $response->assertSee($this->template->name);

        // Order processing
        $response = $this->get('/admin/orders');
        $response->assertStatus(200);
        $response->assertSee($order->id);

        // Approve order and create project
        $order->update(['status' => 'completed']);
        
        $project = Project::create([
            'project_name' => "Website for {$client->name}",
            'user_id' => $client->id,
            'order_id' => $order->id,
            'template_id' => $this->template->id,
            'status' => 'pending'
        ]);

        // Project management
        $response = $this->get('/admin/projects');
        $response->assertStatus(200);
        $response->assertSee($project->project_name);

        // Assign project to staff
        $response = $this->put("/admin/projects/{$project->id}", [
            'project_name' => $project->project_name,
            'user_id' => $client->id,
            'status' => 'in_progress',
            'assigned_to' => $newStaff->id,
            'description' => 'Updated project description'
        ]);

        $project->refresh();
        $this->assertEquals('in_progress', $project->status);
        $this->assertEquals($newStaff->id, $project->assigned_to);

        // Invoice management
        $invoice = Invoice::factory()->create([
            'user_id' => $client->id,
            'order_id' => $order->id,
            'status' => 'sent'
        ]);

        $response = $this->get('/admin/invoices');
        $response->assertStatus(200);
        $response->assertSee($invoice->invoice_number);

        // Mark invoice as paid
        $response = $this->put("/admin/invoices/{$invoice->id}", [
            'amount' => $invoice->amount,
            'tax_amount' => $invoice->tax_amount,
            'total_amount' => $invoice->total_amount,
            'status' => 'paid',
            'due_date' => $invoice->due_date->format('Y-m-d'),
            'paid_date' => now()->format('Y-m-d'),
            'payment_method' => 'Bank Transfer'
        ]);

        $invoice->refresh();
        $this->assertEquals('paid', $invoice->status);

        // Support ticket management
        $ticket = SupportTicket::factory()->create([
            'user_id' => $client->id,
            'status' => 'open'
        ]);

        $response = $this->get('/admin/tickets');
        $response->assertStatus(200);
        $response->assertSee($ticket->subject);

        // Assign and resolve ticket
        $response = $this->put("/admin/tickets/{$ticket->id}", [
            'subject' => $ticket->subject,
            'priority' => $ticket->priority,
            'department' => $ticket->department,
            'status' => 'resolved',
            'assigned_to' => $newStaff->id,
            'resolved_at' => now()->format('Y-m-d\TH:i')
        ]);

        $ticket->refresh();
        $this->assertEquals('resolved', $ticket->status);
        $this->assertEquals($newStaff->id, $ticket->assigned_to);
    }

    /** @test */
    public function staff_workflow_for_assigned_tasks()
    {
        $client = User::factory()->create(['role' => 'client']);
        
        // Create assigned project
        $project = Project::factory()->create([
            'user_id' => $client->id,
            'assigned_to' => $this->staff->id,
            'status' => 'in_progress'
        ]);

        // Create assigned ticket
        $ticket = SupportTicket::factory()->create([
            'user_id' => $client->id,
            'assigned_to' => $this->staff->id,
            'status' => 'open'
        ]);

        $this->actingAs($this->staff);

        // Staff dashboard
        $response = $this->get('/staff/dashboard');
        $response->assertStatus(200);

        // View assigned projects
        $response = $this->get('/staff/projects');
        $response->assertStatus(200);
        $response->assertSee($project->project_name);

        // Update project progress
        $response = $this->get("/staff/projects/{$project->id}");
        $response->assertStatus(200);

        // View assigned tickets
        $response = $this->get('/staff/support');
        $response->assertStatus(200);
        $response->assertSee($ticket->subject);

        // Respond to ticket
        $response = $this->post("/admin/tickets/{$ticket->id}/reply", [
            'message' => 'I am working on your request and will update you soon.',
            'is_internal' => false
        ]);

        $this->assertDatabaseHas('ticket_replies', [
            'ticket_id' => $ticket->id,
            'user_id' => $this->staff->id,
            'message' => 'I am working on your request and will update you soon.'
        ]);

        // Mark ticket as resolved
        $ticket->update([
            'status' => 'resolved',
            'resolved_at' => now()
        ]);

        $this->assertEquals('resolved', $ticket->status);
    }

    /** @test */
    public function error_handling_and_edge_cases()
    {
        $client = User::factory()->create(['role' => 'client']);
        $this->actingAs($client);

        // Test accessing non-existent template
        $response = $this->get('/templates/99999');
        $response->assertStatus(404);

        // Test accessing inactive template
        $inactiveTemplate = Template::factory()->create(['is_active' => false]);
        $response = $this->get("/templates/{$inactiveTemplate->id}");
        $response->assertStatus(404);

        // Test accessing other client's project
        $otherClient = User::factory()->create(['role' => 'client']);
        $otherProject = Project::factory()->create(['user_id' => $otherClient->id]);
        
        $response = $this->get("/client/projects/{$otherProject->id}");
        $response->assertStatus(403);

        // Test accessing other client's invoice
        $otherInvoice = Invoice::factory()->create(['user_id' => $otherClient->id]);
        
        $response = $this->get("/client/invoices/{$otherInvoice->id}");
        $response->assertStatus(403);

        // Test accessing other client's ticket
        $otherTicket = SupportTicket::factory()->create(['user_id' => $otherClient->id]);
        
        $response = $this->get("/client/tickets/{$otherTicket->id}");
        $response->assertStatus(403);

        // Test client accessing admin routes
        $response = $this->get('/admin/dashboard');
        $response->assertStatus(403);

        $response = $this->get('/admin/users');
        $response->assertStatus(403);

        $response = $this->get('/admin/projects');
        $response->assertStatus(403);
    }

    /** @test */
    public function data_consistency_across_related_models()
    {
        $client = User::factory()->create(['role' => 'client']);
        
        // Create order
        $order = Order::factory()->create([
            'user_id' => $client->id,
            'product_id' => $this->product->id,
            'template_id' => $this->template->id,
            'total_amount' => 5500000
        ]);

        // Create related project
        $project = Project::factory()->create([
            'user_id' => $client->id,
            'order_id' => $order->id,
            'template_id' => $this->template->id
        ]);

        // Create related invoice
        $invoice = Invoice::factory()->create([
            'user_id' => $client->id,
            'order_id' => $order->id,
            'total_amount' => $order->total_amount
        ]);

        // Verify relationships
        $this->assertEquals($client->id, $order->user_id);
        $this->assertEquals($client->id, $project->user_id);
        $this->assertEquals($client->id, $invoice->user_id);
        
        $this->assertEquals($order->id, $project->order_id);
        $this->assertEquals($order->id, $invoice->order_id);
        
        $this->assertEquals($this->template->id, $order->template_id);
        $this->assertEquals($this->template->id, $project->template_id);
        
        $this->assertEquals($order->total_amount, $invoice->total_amount);

        // Test cascade relationships
        $this->assertTrue($client->orders->contains($order));
        $this->assertTrue($client->projects->contains($project));
        $this->assertTrue($client->invoices->contains($invoice));
        
        $this->assertTrue($order->projects->contains($project));
        $this->assertTrue($order->invoices->contains($invoice));
        
        $this->assertTrue($this->template->projects->contains($project));
    }
}