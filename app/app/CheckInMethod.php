<?php

namespace App;

/**
 * How a youth was checked in to an event.
 */
enum CheckInMethod: string
{
    case Qr = 'qr';
    case Manual = 'manual';

    /**
     * Get the label shown to people.
     */
    public function label(): string
    {
        return match ($this) {
            self::Qr => 'QR scan',
            self::Manual => 'Manual',
        };
    }
}
