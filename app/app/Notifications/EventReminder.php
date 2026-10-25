<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventReminder extends Notification
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
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Tomorrow: {$this->event->title}")
            ->greeting("Hi {$notifiable->name},")
            ->line("A reminder that {$this->event->title} is tomorrow.")
            ->line('When: '.$this->event->starts_at->format('l, F j \a\t g:i A'))
            ->line("Where: {$this->event->venue}")
            ->action('See the event', route('events.show', $this->event));
    }
}
