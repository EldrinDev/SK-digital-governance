<?php

namespace App;

enum ResolutionOutcome: string
{
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Deferred = 'deferred';

    /**
     * Get the label shown to people.
     */
    public function label(): string
    {
        return match ($this) {
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Deferred => 'Deferred',
        };
    }

    /**
     * Get the badge tone for the outcome.
     */
    public function tone(): string
    {
        return match ($this) {
            self::Approved => 'success',
            self::Rejected => 'error',
            self::Deferred => 'warning',
        };
    }
}
