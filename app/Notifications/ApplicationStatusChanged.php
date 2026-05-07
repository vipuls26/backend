<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationStatusChanged extends Notification
{
    use Queueable;

    public function __construct(public Application $application) {}

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
        $this->application->loadMissing(['job.company']);

        return (new MailMessage)
            ->subject($this->subject())
            ->greeting("Hello {$notifiable->name},")
            ->line("Your application for {$this->application->job->title} at {$this->application->job->company->name} was {$this->statusMessage()}.")
            ->line('Please sign in to view the latest details.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }

    private function subject(): string
    {
        return match ($this->application->status) {
            'interview_scheduled' => 'Interview scheduled',
            'accepted' => 'Application accepted',
            'rejected' => 'Application rejected',
            default => 'Application status updated',
        };
    }

    private function statusMessage(): string
    {
        return match ($this->application->status) {
            'interview_scheduled' => 'moved to interview scheduled',
            'accepted' => 'accepted',
            'rejected' => 'rejected',
            default => 'updated',
        };
    }
}
