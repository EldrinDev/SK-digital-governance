<?php

namespace App;

enum MeetingKind: string
{
    case Regular = 'regular';
    case Special = 'special';

    /**
     * Get the label shown to people.
     */
    public function label(): string
    {
        return match ($this) {
            self::Regular => 'Regular session',
            self::Special => 'Special session',
        };
    }
}
