<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectActivatedNotification extends Notification
{
    use Queueable;

    public $project;

    /**
     * Create a new notification instance.
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Project Has Been Activated!')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Good news! Your project \'' . $this->project->project_name . '\' has been activated.')
            ->line('You can now access your project details and start collaborating.')
            ->action('View Your Project', url('/client/projects/' . $this->project->id))
            ->line('Thank you for choosing our service!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'project_id' => $this->project->id,
            'project_name' => $this->project->project_name,
            'status' => $this->project->status,
            'message' => 'Your project \'' . $this->project->project_name . '\' has been activated.',
            'url' => url('/client/projects/' . $this->project->id),
        ];
    }
}
