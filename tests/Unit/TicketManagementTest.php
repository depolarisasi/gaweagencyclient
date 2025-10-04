<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\SupportTicket;
use App\Models\TicketReply;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TicketManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $staff;
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->client = User::factory()->create(['role' => 'client']);
    }

    /** @test */
    public function admin_can_view_tickets_index()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/tickets');

        $response->assertStatus(200);
        $response->assertViewIs('admin.tickets.index');
        $response->assertViewHas('tickets');
    }

    /** @test */
    public function staff_can_view_tickets_index()
    {
        $response = $this->actingAs($this->staff)
            ->get('/admin/tickets');

        $response->assertStatus(200);
    }

    /** @test */
    public function client_cannot_access_admin_tickets()
    {
        $response = $this->actingAs($this->client)
            ->get('/admin/tickets');

        $response->assertStatus(403);
    }

    /** @test */
    public function client_can_create_support_ticket()
    {
        $ticketData = [
            'subject' => 'Website Issue',
            'message' => 'I am having trouble with my website login.',
            'priority' => 'medium',
            'department' => 'technical'
        ];

        $response = $this->actingAs($this->client)
            ->post('/client/tickets', $ticketData);

        $this->assertDatabaseHas('support_tickets', [
            'user_id' => $this->client->id,
            'subject' => 'Website Issue',
            'message' => 'I am having trouble with my website login.',
            'priority' => 'medium',
            'department' => 'technical',
            'status' => 'open'
        ]);

        $response->assertRedirect('/client/tickets');
    }

    /** @test */
    public function client_cannot_create_ticket_with_invalid_data()
    {
        $ticketData = [
            'subject' => '',
            'message' => '',
            'priority' => 'invalid_priority',
            'department' => 'invalid_department'
        ];

        $response = $this->actingAs($this->client)
            ->post('/client/tickets', $ticketData);

        $response->assertSessionHasErrors([
            'subject', 'message', 'priority', 'department'
        ]);
    }

    /** @test */
    public function admin_can_view_ticket_details()
    {
        $ticket = SupportTicket::factory()->create([
            'user_id' => $this->client->id
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/admin/tickets/{$ticket->id}");

        $response->assertStatus(200);
        $response->assertViewIs('admin.tickets.show');
        $response->assertViewHas('ticket', $ticket);
    }

    /** @test */
    public function admin_can_edit_ticket()
    {
        $ticket = SupportTicket::factory()->create([
            'user_id' => $this->client->id
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/admin/tickets/{$ticket->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('admin.tickets.edit');
        $response->assertViewHas('ticket', $ticket);
        $response->assertViewHas('staff');
    }

    /** @test */
    public function admin_can_update_ticket()
    {
        $ticket = SupportTicket::factory()->create([
            'subject' => 'Original Subject',
            'priority' => 'low',
            'status' => 'open',
            'user_id' => $this->client->id
        ]);

        $updateData = [
            'subject' => 'Updated Subject',
            'priority' => 'high',
            'department' => 'technical',
            'status' => 'in_progress',
            'assigned_to' => $this->staff->id,
            'internal_notes' => 'Escalated to high priority'
        ];

        $response = $this->actingAs($this->admin)
            ->put("/admin/tickets/{$ticket->id}", $updateData);

        $this->assertDatabaseHas('support_tickets', [
            'id' => $ticket->id,
            'subject' => 'Updated Subject',
            'priority' => 'high',
            'status' => 'in_progress',
            'assigned_to' => $this->staff->id,
            'internal_notes' => 'Escalated to high priority'
        ]);

        $response->assertRedirect('/admin/tickets');
    }

    /** @test */
    public function admin_can_assign_ticket_to_staff()
    {
        $ticket = SupportTicket::factory()->create([
            'assigned_to' => null
        ]);

        $response = $this->actingAs($this->admin)
            ->post("/admin/tickets/{$ticket->id}/assign", [
                'assigned_to' => $this->staff->id
            ]);

        $ticket->refresh();
        $this->assertEquals($this->staff->id, $ticket->assigned_to);
    }

    /** @test */
    public function admin_can_change_ticket_status()
    {
        $ticket = SupportTicket::factory()->create([
            'status' => 'open'
        ]);

        // Mark as in progress
        $response = $this->actingAs($this->admin)
            ->post("/admin/tickets/{$ticket->id}/in-progress");

        $ticket->refresh();
        $this->assertEquals('in_progress', $ticket->status);

        // Mark as resolved
        $response = $this->actingAs($this->admin)
            ->post("/admin/tickets/{$ticket->id}/resolve");

        $ticket->refresh();
        $this->assertEquals('resolved', $ticket->status);
        $this->assertNotNull($ticket->resolved_at);

        // Close ticket
        $response = $this->actingAs($this->admin)
            ->post("/admin/tickets/{$ticket->id}/close");

        $ticket->refresh();
        $this->assertEquals('closed', $ticket->status);
        $this->assertNotNull($ticket->closed_at);
    }

    /** @test */
    public function admin_can_reply_to_ticket()
    {
        $ticket = SupportTicket::factory()->create([
            'user_id' => $this->client->id
        ]);

        $replyData = [
            'message' => 'Thank you for contacting us. We will look into this issue.',
            'is_internal' => false
        ];

        $response = $this->actingAs($this->admin)
            ->post("/admin/tickets/{$ticket->id}/reply", $replyData);

        $this->assertDatabaseHas('ticket_replies', [
            'ticket_id' => $ticket->id,
            'user_id' => $this->admin->id,
            'message' => 'Thank you for contacting us. We will look into this issue.',
            'is_internal' => false
        ]);
    }

    /** @test */
    public function staff_can_reply_to_assigned_ticket()
    {
        $ticket = SupportTicket::factory()->create([
            'user_id' => $this->client->id,
            'assigned_to' => $this->staff->id
        ]);

        $replyData = [
            'message' => 'I am working on your issue and will update you soon.',
            'is_internal' => false
        ];

        $response = $this->actingAs($this->staff)
            ->post("/admin/tickets/{$ticket->id}/reply", $replyData);

        $this->assertDatabaseHas('ticket_replies', [
            'ticket_id' => $ticket->id,
            'user_id' => $this->staff->id,
            'message' => 'I am working on your issue and will update you soon.',
            'is_internal' => false
        ]);
    }

    /** @test */
    public function admin_can_add_internal_notes()
    {
        $ticket = SupportTicket::factory()->create();

        $replyData = [
            'message' => 'Internal note: This requires database access.',
            'is_internal' => true
        ];

        $response = $this->actingAs($this->admin)
            ->post("/admin/tickets/{$ticket->id}/reply", $replyData);

        $this->assertDatabaseHas('ticket_replies', [
            'ticket_id' => $ticket->id,
            'user_id' => $this->admin->id,
            'message' => 'Internal note: This requires database access.',
            'is_internal' => true
        ]);
    }

    /** @test */
    public function client_can_view_their_tickets()
    {
        $ticket = SupportTicket::factory()->create([
            'user_id' => $this->client->id
        ]);

        $response = $this->actingAs($this->client)
            ->get('/client/tickets');

        $response->assertStatus(200);
        $response->assertViewIs('client.tickets.index');
        $response->assertSee($ticket->subject);
    }

    /** @test */
    public function client_can_view_their_ticket_details()
    {
        $ticket = SupportTicket::factory()->create([
            'user_id' => $this->client->id
        ]);

        $response = $this->actingAs($this->client)
            ->get("/client/tickets/{$ticket->id}");

        $response->assertStatus(200);
        $response->assertViewIs('client.tickets.show');
        $response->assertViewHas('ticket', $ticket);
    }

    /** @test */
    public function client_cannot_view_other_clients_tickets()
    {
        $otherClient = User::factory()->create(['role' => 'client']);
        $ticket = SupportTicket::factory()->create([
            'user_id' => $otherClient->id
        ]);

        $response = $this->actingAs($this->client)
            ->get("/client/tickets/{$ticket->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function client_can_reply_to_their_ticket()
    {
        $ticket = SupportTicket::factory()->create([
            'user_id' => $this->client->id,
            'status' => 'open'
        ]);

        $replyData = [
            'message' => 'Thank you for the quick response. Here are more details...'
        ];

        $response = $this->actingAs($this->client)
            ->post("/client/tickets/{$ticket->id}/reply", $replyData);

        $this->assertDatabaseHas('ticket_replies', [
            'ticket_id' => $ticket->id,
            'user_id' => $this->client->id,
            'message' => 'Thank you for the quick response. Here are more details...',
            'is_internal' => false
        ]);
    }

    /** @test */
    public function client_can_close_their_ticket()
    {
        $ticket = SupportTicket::factory()->create([
            'user_id' => $this->client->id,
            'status' => 'resolved'
        ]);

        $response = $this->actingAs($this->client)
            ->post("/client/tickets/{$ticket->id}/close");

        $ticket->refresh();
        $this->assertEquals('closed', $ticket->status);
        $this->assertNotNull($ticket->closed_at);
    }

    /** @test */
    public function client_cannot_edit_closed_ticket()
    {
        $ticket = SupportTicket::factory()->create([
            'user_id' => $this->client->id,
            'status' => 'closed'
        ]);

        $response = $this->actingAs($this->client)
            ->get("/client/tickets/{$ticket->id}/edit");

        $response->assertRedirect("/client/tickets/{$ticket->id}");
        $response->assertSessionHas('error');
    }

    /** @test */
    public function ticket_model_relationships_work_correctly()
    {
        $ticket = SupportTicket::factory()->create([
            'user_id' => $this->client->id,
            'assigned_to' => $this->staff->id
        ]);

        $reply = TicketReply::factory()->create([
            'ticket_id' => $ticket->id,
            'user_id' => $this->admin->id
        ]);

        // Test relationships
        $this->assertInstanceOf(User::class, $ticket->user);
        $this->assertInstanceOf(User::class, $ticket->assignedStaff);
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $ticket->replies);

        $this->assertEquals($this->client->id, $ticket->user->id);
        $this->assertEquals($this->staff->id, $ticket->assignedStaff->id);
        $this->assertTrue($ticket->replies->contains($reply));
    }

    /** @test */
    public function ticket_model_has_correct_fillable_attributes()
    {
        $ticket = new SupportTicket();
        $fillable = $ticket->getFillable();

        $expectedFillable = [
            'user_id', 'subject', 'message', 'priority', 'department',
            'status', 'assigned_to', 'internal_notes', 'resolved_at', 'closed_at'
        ];

        foreach ($expectedFillable as $attribute) {
            $this->assertContains($attribute, $fillable);
        }
    }

    /** @test */
    public function ticket_model_has_correct_casts()
    {
        $ticket = new SupportTicket();
        $casts = $ticket->getCasts();

        $this->assertEquals('datetime', $casts['resolved_at']);
        $this->assertEquals('datetime', $casts['closed_at']);
    }

    /** @test */
    public function ticket_scopes_work_correctly()
    {
        $openTicket = SupportTicket::factory()->create(['status' => 'open']);
        $closedTicket = SupportTicket::factory()->create(['status' => 'closed']);
        $highPriorityTicket = SupportTicket::factory()->create(['priority' => 'high']);
        $lowPriorityTicket = SupportTicket::factory()->create(['priority' => 'low']);
        $assignedTicket = SupportTicket::factory()->create(['assigned_to' => $this->staff->id]);
        $unassignedTicket = SupportTicket::factory()->create(['assigned_to' => null]);

        // Test open scope
        $openTickets = SupportTicket::open()->get();
        $this->assertTrue($openTickets->contains($openTicket));
        $this->assertFalse($openTickets->contains($closedTicket));

        // Test closed scope
        $closedTickets = SupportTicket::closed()->get();
        $this->assertTrue($closedTickets->contains($closedTicket));
        $this->assertFalse($closedTickets->contains($openTicket));

        // Test high priority scope
        $highPriorityTickets = SupportTicket::highPriority()->get();
        $this->assertTrue($highPriorityTickets->contains($highPriorityTicket));
        $this->assertFalse($highPriorityTickets->contains($lowPriorityTicket));

        // Test assigned scope
        $assignedTickets = SupportTicket::assigned()->get();
        $this->assertTrue($assignedTickets->contains($assignedTicket));
        $this->assertFalse($assignedTickets->contains($unassignedTicket));

        // Test unassigned scope
        $unassignedTickets = SupportTicket::unassigned()->get();
        $this->assertTrue($unassignedTickets->contains($unassignedTicket));
        $this->assertFalse($unassignedTickets->contains($assignedTicket));
    }

    /** @test */
    public function ticket_priority_levels_are_validated()
    {
        $validPriorities = ['low', 'medium', 'high'];
        
        foreach ($validPriorities as $priority) {
            $ticket = SupportTicket::factory()->create(['priority' => $priority]);
            $this->assertEquals($priority, $ticket->priority);
        }
    }

    /** @test */
    public function ticket_departments_are_validated()
    {
        $validDepartments = ['technical', 'billing', 'general'];
        
        foreach ($validDepartments as $department) {
            $ticket = SupportTicket::factory()->create(['department' => $department]);
            $this->assertEquals($department, $ticket->department);
        }
    }

    /** @test */
    public function ticket_status_transitions_are_tracked()
    {
        $ticket = SupportTicket::factory()->create(['status' => 'open']);

        // Open -> In Progress
        $ticket->update(['status' => 'in_progress']);
        $this->assertEquals('in_progress', $ticket->status);

        // In Progress -> Resolved
        $ticket->update(['status' => 'resolved', 'resolved_at' => now()]);
        $this->assertEquals('resolved', $ticket->status);
        $this->assertNotNull($ticket->resolved_at);

        // Resolved -> Closed
        $ticket->update(['status' => 'closed', 'closed_at' => now()]);
        $this->assertEquals('closed', $ticket->status);
        $this->assertNotNull($ticket->closed_at);
    }

    /** @test */
    public function ticket_reply_model_works_correctly()
    {
        $ticket = SupportTicket::factory()->create();
        $reply = TicketReply::factory()->create([
            'ticket_id' => $ticket->id,
            'user_id' => $this->admin->id,
            'message' => 'Test reply message',
            'is_internal' => false
        ]);

        $this->assertInstanceOf(SupportTicket::class, $reply->ticket);
        $this->assertInstanceOf(User::class, $reply->user);
        $this->assertEquals($ticket->id, $reply->ticket->id);
        $this->assertEquals($this->admin->id, $reply->user->id);
        $this->assertEquals('Test reply message', $reply->message);
        $this->assertFalse($reply->is_internal);
    }

    /** @test */
    public function internal_replies_are_not_visible_to_clients()
    {
        $ticket = SupportTicket::factory()->create([
            'user_id' => $this->client->id
        ]);

        $publicReply = TicketReply::factory()->create([
            'ticket_id' => $ticket->id,
            'user_id' => $this->admin->id,
            'message' => 'Public reply',
            'is_internal' => false
        ]);

        $internalReply = TicketReply::factory()->create([
            'ticket_id' => $ticket->id,
            'user_id' => $this->admin->id,
            'message' => 'Internal note',
            'is_internal' => true
        ]);

        $response = $this->actingAs($this->client)
            ->get("/client/tickets/{$ticket->id}");

        $response->assertSee('Public reply');
        $response->assertDontSee('Internal note');
    }
}