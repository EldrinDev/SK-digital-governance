<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventRegistrationConfirmed extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Event $event) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("You’re registered: {$this->event->title}")
            ->greeting("Hi {$notifiable->name},")
            ->line("You’re registered for {$this->event->title}.")
            ->line('When: '.$this->event->starts_at->format('l, F j, Y \a\t g:i A'))
            ->line("Where: {$this->event->venue}")
            ->action('See the event', route('events.show', $this->event))
            ->line('We’ll send a reminder the day before. If you can’t go, cancel from your dashboard so someone else can take your place.');
    }

    /**
     * Get the in-platform representation of the notification.
     *
     * @return array{title: string, body: string, url: string}
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => "You’re registered: {$this->event->title}",
            'body' => $this->event->starts_at->format('D, M j · g:i A').' · '.$this->event->venue,
            'url' => route('events.show', $this->event),
        ];
    }
}
