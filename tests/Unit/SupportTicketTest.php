<?php

namespace Tests\Unit;

use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class SupportTicketTest extends TestCase
{
    use RefreshDatabase;
    
    protected $user;
    protected $staff;
    protected $ticket;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test users
        $this->user = User::create([
            'name' => 'Test Client',
            'email' => 'client@test.com',
            'password' => bcrypt('password'),
            'role' => 'client',
            'status' => 'active',
        ]);
        
        $this->staff = User::create([
            'name' => 'Test Staff',
            'email' => 'staff@test.com',
            'password' => bcrypt('password'),
            'role' => 'staff',
            'status' => 'active',
        ]);
        
        // Create test ticket
        $this->ticket = SupportTicket::create([
            'ticket_number' => 'TKT-20250101-ABC123',
            'user_id' => $this->user->id,
            'subject' => 'Test Support Ticket',
            'description' => 'This is a test support ticket description',
            'priority' => 'medium',
            'status' => 'open',
            'category' => 'technical',
        ]);
    }
    
    public function test_support_ticket_can_be_created_with_required_fields()
    {
        $ticket = SupportTicket::create([
            'ticket_number' => 'TKT-20250101-XYZ789',
            'user_id' => $this->user->id,
            'subject' => 'New Test Ticket',
            'description' => 'New ticket description',
            'priority' => 'high',
            'status' => 'open',
            'category' => 'billing',
        ]);
        
        $this->assertDatabaseHas('support_tickets', [
            'ticket_number' => 'TKT-20250101-XYZ789',
            'user_id' => $this->user->id,
            'subject' => 'New Test Ticket',
            'priority' => 'high',
            'status' => 'open',
            'category' => 'billing',
        ]);
    }
    
    public function test_support_ticket_belongs_to_user()
    {
        $this->assertInstanceOf(User::class, $this->ticket->user);
        $this->assertEquals($this->user->id, $this->ticket->user->id);
    }
    
    public function test_support_ticket_can_be_assigned_to_staff()
    {
        $this->ticket->update(['assigned_to' => $this->staff->id]);
        
        $this->assertInstanceOf(User::class, $this->ticket->assignedUser);
        $this->assertEquals($this->staff->id, $this->ticket->assignedUser->id);
    }
    
    public function test_support_ticket_has_many_replies()
    {
        $reply = TicketReply::create([
            'support_ticket_id' => $this->ticket->id,
            'user_id' => $this->staff->id,
            'message' => 'This is a test reply',
            'is_internal' => false,
        ]);
        
        $this->assertTrue($this->ticket->replies->contains($reply));
        $this->assertEquals(1, $this->ticket->replies->count());
    }
    
    public function test_open_scope()
    {
        // Create tickets with different statuses
        SupportTicket::create([
            'ticket_number' => 'TKT-OPEN-001',
            'user_id' => $this->user->id,
            'subject' => 'Open Ticket',
            'description' => 'Open ticket description',
            'priority' => 'medium',
            'status' => 'open',
            'category' => 'technical',
        ]);
        
        SupportTicket::create([
            'ticket_number' => 'TKT-PROGRESS-001',
            'user_id' => $this->user->id,
            'subject' => 'In Progress Ticket',
            'description' => 'In progress ticket description',
            'priority' => 'medium',
            'status' => 'in_progress',
            'category' => 'technical',
        ]);
        
        SupportTicket::create([
            'ticket_number' => 'TKT-CLOSED-001',
            'user_id' => $this->user->id,
            'subject' => 'Closed Ticket',
            'description' => 'Closed ticket description',
            'priority' => 'medium',
            'status' => 'closed',
            'category' => 'technical',
        ]);
        
        $openTickets = SupportTicket::open()->get();
        $this->assertEquals(3, $openTickets->count()); // original + open + in_progress
    }
    
    public function test_closed_scope()
    {
        $this->ticket->update(['status' => 'closed']);
        
        $closedTickets = SupportTicket::closed()->get();
        $this->assertEquals(1, $closedTickets->count());
        $this->assertEquals('closed', $closedTickets->first()->status);
    }
    
    public function test_by_priority_scope()
    {
        SupportTicket::create([
            'ticket_number' => 'TKT-HIGH-001',
            'user_id' => $this->user->id,
            'subject' => 'High Priority Ticket',
            'description' => 'High priority description',
            'priority' => 'high',
            'status' => 'open',
            'category' => 'technical',
        ]);
        
        $highPriorityTickets = SupportTicket::byPriority('high')->get();
        $this->assertEquals(1, $highPriorityTickets->count());
        $this->assertEquals('high', $highPriorityTickets->first()->priority);
    }
    
    public function test_by_category_scope()
    {
        SupportTicket::create([
            'ticket_number' => 'TKT-BILLING-001',
            'user_id' => $this->user->id,
            'subject' => 'Billing Issue',
            'description' => 'Billing issue description',
            'priority' => 'medium',
            'status' => 'open',
            'category' => 'billing',
        ]);
        
        $billingTickets = SupportTicket::byCategory('billing')->get();
        $this->assertEquals(1, $billingTickets->count());
        $this->assertEquals('billing', $billingTickets->first()->category);
    }
    
    public function test_is_open_method()
    {
        $this->assertTrue($this->ticket->isOpen());
        
        $this->ticket->update(['status' => 'in_progress']);
        $this->assertTrue($this->ticket->isOpen());
        
        $this->ticket->update(['status' => 'closed']);
        $this->assertFalse($this->ticket->isOpen());
    }
    
    public function test_is_closed_method()
    {
        $this->assertFalse($this->ticket->isClosed());
        
        $this->ticket->update(['status' => 'closed']);
        $this->assertTrue($this->ticket->isClosed());
    }
    
    public function test_is_resolved_method()
    {
        $this->assertFalse($this->ticket->isResolved());
        
        $this->ticket->update(['status' => 'resolved']);
        $this->assertTrue($this->ticket->isResolved());
    }
    
    public function test_status_badge_class_attribute()
    {
        $this->assertEquals('badge-danger', $this->ticket->status_badge_class);
        
        $this->ticket->update(['status' => 'in_progress']);
        $this->assertEquals('badge-warning', $this->ticket->status_badge_class);
        
        $this->ticket->update(['status' => 'closed']);
        $this->assertEquals('badge-success', $this->ticket->status_badge_class);
    }
    
    public function test_priority_badge_class_attribute()
    {
        $this->assertEquals('badge-warning', $this->ticket->priority_badge_class);
        
        $this->ticket->update(['priority' => 'high']);
        $this->assertEquals('badge-danger', $this->ticket->priority_badge_class);
        
        $this->ticket->update(['priority' => 'low']);
        $this->assertEquals('badge-success', $this->ticket->priority_badge_class);
    }
    
    public function test_add_reply_method()
    {
        $replyMessage = 'This is a test reply from staff';
        
        $reply = $this->ticket->addReply($replyMessage, $this->staff->id, false);
        
        $this->assertInstanceOf(TicketReply::class, $reply);
        $this->assertEquals($replyMessage, $reply->message);
        $this->assertEquals($this->staff->id, $reply->user_id);
        $this->assertFalse($reply->is_internal);
        
        // Check if ticket was updated
        $this->ticket->refresh();
        $this->assertNotNull($this->ticket->last_reply_at);
        $this->assertEquals($this->staff->id, $this->ticket->last_reply_by);
    }
    
    public function test_mark_as_resolved_method()
    {
        $this->ticket->markAsResolved($this->staff->id);
        
        $this->ticket->refresh();
        $this->assertEquals('resolved', $this->ticket->status);
        $this->assertNotNull($this->ticket->resolved_at);
    }
    
    public function test_mark_as_closed_method()
    {
        $this->ticket->markAsClosed($this->staff->id);
        
        $this->ticket->refresh();
        $this->assertEquals('closed', $this->ticket->status);
        $this->assertNotNull($this->ticket->closed_at);
    }
    
    public function test_ticket_number_format()
    {
        // Test that ticket number follows expected format
        $this->assertStringStartsWith('TKT-', $this->ticket->ticket_number);
        $this->assertStringContainsString('20250101', $this->ticket->ticket_number);
    }
    
    public function test_time_since_created_attribute()
    {
        $timeSince = $this->ticket->time_since_created;
        $this->assertIsString($timeSince);
        $this->assertStringContainsString('ago', $timeSince);
    }
    
    public function test_time_since_last_reply_attribute()
    {
        $this->assertEquals('Belum ada balasan', $this->ticket->time_since_last_reply);
        
        $this->ticket->addReply('Test reply', $this->staff->id);
        $this->ticket->refresh();
        
        $timeSince = $this->ticket->time_since_last_reply;
        $this->assertIsString($timeSince);
        $this->assertStringContainsString('ago', $timeSince);
    }
    
    public function test_public_and_internal_replies_relationships()
    {
        // Add public reply
        $this->ticket->addReply('Public reply', $this->staff->id, false);
        
        // Add internal reply
        $this->ticket->addReply('Internal note', $this->staff->id, true);
        
        $this->assertEquals(1, $this->ticket->publicReplies->count());
        $this->assertEquals(1, $this->ticket->internalReplies->count());
        $this->assertEquals(2, $this->ticket->replies->count());
    }
}