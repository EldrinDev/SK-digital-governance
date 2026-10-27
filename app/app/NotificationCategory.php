<?php

namespace App;

use App\Notifications\AnnouncementPosted;
use App\Notifications\EventRegistrationConfirmed;
use App\Notifications\EventReminder;
use App\Notifications\FeedbackAnswered;
use App\Notifications\FeedbackReceived;
use App\Notifications\ProgressAlert;
use App\Notifications\RemarkReceived;
use App\Notifications\ReportReviewed;
use App\Notifications\ReportSubmitted;

/**
 * The kind of a notification, so the bell can label and filter them.
 * Worked out from the notification's class, which also covers ones sent before categories existed.
 */
enum NotificationCategory: string
{
    case Announcement = 'announcement';
    case Event = 'event';
    case Message = 'message';
    case Progress = 'progress';
    case Review = 'review';

    /**
     * Get the category of a stored notification from its class name.
     */
    public static function forType(string $type): self
    {
        return match ($type) {
            AnnouncementPosted::class => self::Announcement,
            EventRegistrationConfirmed::class, EventReminder::class => self::Event,
            FeedbackAnswered::class, FeedbackReceived::class => self::Message,
            ProgressAlert::class => self::Progress,
            RemarkReceived::class, ReportReviewed::class, ReportSubmitted::class => self::Review,
            default => self::Announcement,
        };
    }

    /**
     * Get the tab label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Announcement => 'Announcements',
            self::Event => 'Events',
            self::Message => 'Messages',
            self::Progress => 'PPA progress',
            self::Review => 'Reviews',
        };
    }

    /**
     * Get the short tag shown on each notification.
     */
    public function tag(): string
    {
        return match ($this) {
            self::Announcement => 'Announcement',
            self::Event => 'Event',
            self::Message => 'Message',
            self::Progress => 'PPA progress',
            self::Review => 'Review',
        };
    }

    /**
     * Get the outline icon paths.
     */
    public function icon(): string
    {
        return match ($this) {
            self::Announcement => '<path d="m3 11 18-5v12L3 14v-3Z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>',
            self::Event => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
            self::Message => '<path d="M21 12a8 8 0 0 1-11.6 7.1L4 20.5l1.4-4.8A8 8 0 1 1 21 12Z"/>',
            self::Progress => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
            self::Review => '<path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9l-6-6Z"/><path d="M14 3v6h6"/><path d="m9 15 2 2 4-4"/>',
        };
    }

    /**
     * Get the icon's background and colour classes.
     */
    public function tone(): string
    {
        return match ($this) {
            self::Announcement => 'bg-gold/20 text-[#92400e]',
            self::Event => 'bg-emerald/10 text-emerald',
            self::Message => 'bg-info/10 text-info',
            self::Progress => 'bg-error/10 text-[#b91c1c]',
            self::Review => 'bg-navy/10 text-navy',
        };
    }
}
