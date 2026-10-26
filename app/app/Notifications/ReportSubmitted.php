<?php

namespace App\Notifications;

use App\Models\AccomplishmentReport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReportSubmitted extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public AccomplishmentReport $report) {}

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
            ->subject("{$this->report->barangay->name} submitted an accomplishment report")
            ->line("{$this->report->barangay->name} submitted its ABYIP {$this->report->snapshot['year']} accomplishment report for review.")
            ->action('Review the report', route('federation.reports.show', $this->report));
    }

    /**
     * Get the in-platform representation of the notification.
     *
     * @return array{title: string, body: string, url: string}
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => "{$this->report->barangay->name} submitted a report",
            'body' => "ABYIP {$this->report->snapshot['year']} accomplishment report is waiting for review.",
            'url' => route('federation.reports.show', $this->report),
        ];
    }
}
