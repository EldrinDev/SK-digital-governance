<?php

namespace App;

enum ReportStatus: string
{
    case Submitted = 'submitted';
    case Reviewed = 'reviewed';

    /**
     * Get the label shown to people.
     */
    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Waiting for federation review',
            self::Reviewed => 'Reviewed by the federation',
        };
    }
}
