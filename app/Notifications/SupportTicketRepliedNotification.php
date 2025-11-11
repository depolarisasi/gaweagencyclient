<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use App\Models\TicketReply;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupportTicketRepliedNotification extends Notification
{
    use Queueable;

    public function __construct(public SupportTicket $ticket, public TicketReply $reply)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Reply on Ticket: #'.$this->ticket->ticket_number)
            ->markdown('emails.support.ticket_replied', [
                'ticket' => $this->ticket,
                'reply' => $this->reply,
                'user' => $notifiable,
            ]);
    }
}