<?php

namespace App\Notifications;

use App\Models\MonitoringRemark;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RemarkReceived extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public MonitoringRemark $remark) {}

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
            ->subject('New remark from the SK Federation')
            ->greeting("Hi {$notifiable->name},")
            ->line('The SK Federation left a remark for your barangay:')
            ->line("“{$this->remark->body}”")
            ->action('Open your workspace', route('officer.dashboard'));
    }

    /**
     * Get the in-platform representation of the notification.
     *
     * @return array{title: string, body: string, url: string}
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New remark from the SK Federation',
            'body' => str($this->remark->body)->limit(120)->toString(),
            'url' => route('officer.dashboard'),
        ];
    }
}
