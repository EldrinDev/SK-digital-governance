<?php

namespace App\Notifications;

use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AnnouncementPosted extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Announcement $announcement) {}

    /**
     * Get the notification's delivery channels. Only in-platform, so a busy SK doesn't flood inboxes.
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
            'title' => "Announcement: {$this->announcement->title}",
            'body' => str($this->announcement->body)->limit(120)->toString(),
            'url' => route('announcements.show', $this->announcement),
        ];
    }
}
