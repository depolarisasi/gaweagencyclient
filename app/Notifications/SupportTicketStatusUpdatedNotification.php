<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupportTicketStatusUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(public SupportTicket $ticket, public string $oldStatus, public string $newStatus)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Ticket Status Updated: #'.$this->ticket->ticket_number)
            ->markdown('emails.support.ticket_status_updated', [
                'ticket' => $this->ticket,
                'oldStatus' => $this->oldStatus,
                'newStatus' => $this->newStatus,
                'user' => $notifiable,
            ]);
    }
}