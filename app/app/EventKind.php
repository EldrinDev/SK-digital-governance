<?php

namespace App;

enum EventKind: string
{
    case Event = 'event';
    case Consultation = 'consultation';

    /**
     * Get the label shown to people.
     */
    public function label(): string
    {
        return match ($this) {
            self::Event => 'Event',
            self::Consultation => 'Youth consultation',
        };
    }
}
