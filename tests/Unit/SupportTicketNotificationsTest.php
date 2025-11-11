<?php

namespace Tests\Unit;

use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Models\User;
use App\Notifications\SupportTicketCreatedNotification;
use App\Notifications\SupportTicketRepliedNotification;
use App\Notifications\SupportTicketStatusUpdatedNotification;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SupportTicketNotificationsTest extends TestCase
{
    public function test_created_notification_is_sent_to_client(): void
    {
        Notification::fake();

        $user = new User(['name' => 'Client User', 'email' => 'client@example.com']);
        $ticket = new SupportTicket([
            'ticket_number' => 'TKT202501010001',
            'user_id' => 1,
            'subject' => 'Contoh Ticket',
            'description' => '<p>Deskripsi</p>',
            'priority' => 'medium',
            'category' => 'general',
            'status' => 'open',
        ]);

        $user->notify(new SupportTicketCreatedNotification($ticket));

        Notification::assertSentTo($user, SupportTicketCreatedNotification::class);
    }

    public function test_replied_notification_is_sent_to_client(): void
    {
        Notification::fake();

        $user = new User(['name' => 'Client User', 'email' => 'client@example.com']);
        $ticket = new SupportTicket([
            'ticket_number' => 'TKT202501010002',
            'user_id' => 1,
            'subject' => 'Balasan Ticket',
            'description' => '<p>Deskripsi</p>',
            'priority' => 'low',
            'category' => 'technical',
            'status' => 'in_progress',
        ]);
        $reply = new TicketReply([
            'message' => '<p>Balasan dari admin</p>',
            'is_internal' => false,
        ]);

        $user->notify(new SupportTicketRepliedNotification($ticket, $reply));

        Notification::assertSentTo($user, SupportTicketRepliedNotification::class);
    }

    public function test_status_updated_notification_is_sent_to_client(): void
    {
        Notification::fake();

        $user = new User(['name' => 'Client User', 'email' => 'client@example.com']);
        $ticket = new SupportTicket([
            'ticket_number' => 'TKT202501010003',
            'user_id' => 1,
            'subject' => 'Update Status',
            'description' => '<p>Deskripsi</p>',
            'priority' => 'high',
            'category' => 'billing',
            'status' => 'resolved',
        ]);

        $user->notify(new SupportTicketStatusUpdatedNotification($ticket, 'in_progress', 'resolved'));

        Notification::assertSentTo($user, SupportTicketStatusUpdatedNotification::class);
    }
}