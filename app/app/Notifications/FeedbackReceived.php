<?php

namespace App\Notifications;

use App\Models\Feedback;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Tells officers about new feedback. In-platform only, so a busy barangay
 * doesn't flood their inboxes.
 */
class FeedbackReceived extends Notification
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
        return ['database'];
    }

    /**
     * Get the in-platform representation of the notification.
     *
     * @return array{title: string, body: string, url: string}
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->feedback->event_id ? 'New event evaluation' : 'New feedback from a youth',
            'body' => str($this->feedback->message)->limit(120)->toString(),
            'url' => route('officer.feedback.index', ['show' => 'unanswered']),
        ];
    }
}
