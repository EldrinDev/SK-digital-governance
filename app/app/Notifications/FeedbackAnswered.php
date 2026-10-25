<?php

namespace App\Notifications;

use App\Models\Feedback;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FeedbackAnswered extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Feedback $feedback) {}

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
            ->subject('Your SK replied to your feedback')
            ->greeting("Hi {$notifiable->name},")
            ->line("The {$this->feedback->barangay->name} SK replied:")
            ->line("“{$this->feedback->response}”")
            ->action('Open your dashboard', route('dashboard'));
    }
}
