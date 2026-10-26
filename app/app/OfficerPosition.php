<?php

namespace App;

/**
 * An officer's seat on the barangay SK council. Some steps are kept to the
 * officer whose office is responsible for them.
 */
enum OfficerPosition: string
{
    case Chairperson = 'chairperson';
    case Kagawad = 'kagawad';
    case Secretary = 'secretary';
    case Treasurer = 'treasurer';

    /**
     * Get the label shown to people.
     */
    public function label(): string
    {
        return match ($this) {
            self::Chairperson => 'SK Chairperson',
            self::Kagawad => 'SK Kagawad',
            self::Secretary => 'SK Secretary',
            self::Treasurer => 'SK Treasurer',
        };
    }
}
