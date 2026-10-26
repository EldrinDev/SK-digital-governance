<?php

namespace App\Notifications;

use App\Models\AccomplishmentReport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReportReviewed extends Notification
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
        $mail = (new MailMessage)
            ->subject('The SK Federation reviewed your report')
            ->line("Your ABYIP {$this->report->snapshot['year']} accomplishment report was reviewed.");

        if ($this->report->review_note) {
            $mail->line("“{$this->report->review_note}”");
        }

        return $mail->action('See the report', route('officer.abyips.report', $this->report->abyip_id));
    }

    /**
     * Get the in-platform representation of the notification.
     *
     * @return array{title: string, body: string, url: string}
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Your report was reviewed',
            'body' => $this->report->review_note
                ? str($this->report->review_note)->limit(120)->toString()
                : "ABYIP {$this->report->snapshot['year']} accomplishment report.",
            'url' => route('officer.abyips.report', $this->report->abyip_id),
        ];
    }
}
