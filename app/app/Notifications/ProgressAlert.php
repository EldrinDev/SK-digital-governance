<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * A daily digest of what needs attention in the barangay's PPAs.
 */
class ProgressAlert extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public int $delayedPpas,
        public int $overdueMilestones,
        public int $milestonesDueSoon,
        public int $days,
    ) {}

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
        $message = (new MailMessage)
            ->subject('PPAs that need attention')
            ->greeting("Hi {$notifiable->name},");

        foreach ($this->lines() as $line) {
            $message->line($line);
        }

        return $message->action('Open your PPAs', $this->url());
    }

    /**
     * Get the in-platform representation of the notification.
     *
     * @return array{title: string, body: string, url: string}
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'PPAs that need attention',
            'body' => implode(' ', $this->lines()),
            'url' => $this->url(),
        ];
    }

    /**
     * @return list<string>
     */
    private function lines(): array
    {
        return array_values(array_filter([
            $this->delayedPpas ? trans_choice('{1} 1 PPA is past its end date.|[2,*] :count PPAs are past their end date.', $this->delayedPpas) : null,
            $this->overdueMilestones ? trans_choice('{1} 1 milestone is overdue.|[2,*] :count milestones are overdue.', $this->overdueMilestones) : null,
            $this->milestonesDueSoon ? trans_choice('{1} 1 milestone is due in the next :days days.|[2,*] :count milestones are due in the next :days days.', $this->milestonesDueSoon, ['days' => $this->days]) : null,
        ]));
    }

    private function url(): string
    {
        return $this->delayedPpas ? route('officer.ppas.index', ['status' => 'delayed']) : route('officer.dashboard');
    }
}
