<?php

namespace App;

/**
 * Where a CBYDP or ABYIP stands. An approved plan is locked.
 */
enum PlanStatus: string
{
    case Draft = 'draft';
    case Approved = 'approved';

    /**
     * Get the label shown to people.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Approved => 'Approved',
        };
    }
}
