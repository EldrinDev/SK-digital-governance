<?php

namespace App;

/**
 * What kind of post an announcement is, which sets its label and the colour of its poster tile.
 */
enum AnnouncementCategory: string
{
    case Announcement = 'announcement';
    case Reminder = 'reminder';
    case Event = 'event';
    case Recognition = 'recognition';

    /**
     * Get the label shown to people.
     */
    public function label(): string
    {
        return match ($this) {
            self::Announcement => 'Announcement',
            self::Reminder => 'Paalala',
            self::Event => 'Event',
            self::Recognition => 'Pasasalamat',
        };
    }

    /**
     * Get the short description shown in the officer's form.
     */
    public function hint(): string
    {
        return match ($this) {
            self::Announcement => 'News and general notices',
            self::Reminder => 'Reminders and deadlines',
            self::Event => 'Invitations to an activity',
            self::Recognition => 'Thank-yous, recaps and congratulations',
        };
    }
}
