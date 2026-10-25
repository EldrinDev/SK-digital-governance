<?php

namespace App;

/**
 * The implementation status an officer sets on a PPA.
 *
 * "Delayed" is not stored: a PPA is delayed when it is still open past its end date.
 */
enum PpaStatus: string
{
    case Planned = 'planned';
    case Ongoing = 'ongoing';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    /**
     * Get the label shown to people.
     */
    public function label(): string
    {
        return match ($this) {
            self::Planned => 'Planned',
            self::Ongoing => 'Ongoing',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    /**
     * Determine whether work on the PPA is finished one way or another.
     */
    public function isClosed(): bool
    {
        return $this === self::Completed || $this === self::Cancelled;
    }
}
