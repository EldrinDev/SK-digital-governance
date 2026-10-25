<?php

namespace App;

enum UserRole: string
{
    case Youth = 'youth';
    case Officer = 'officer';
    case Federation = 'federation';
    case Administrator = 'administrator';

    /**
     * Get the label shown to people.
     */
    public function label(): string
    {
        return match ($this) {
            self::Youth => 'Youth',
            self::Officer => 'Barangay SK officer',
            self::Federation => 'SK Federation',
            self::Administrator => 'System administrator',
        };
    }

    /**
     * Get the label for a group of accounts with this role.
     */
    public function pluralLabel(): string
    {
        return match ($this) {
            self::Youth => 'Youth accounts',
            self::Officer => 'Barangay SK officers',
            self::Federation => 'SK Federation users',
            self::Administrator => 'Administrators',
        };
    }

    /**
     * Determine whether accounts with this role work inside one barangay workspace.
     */
    public function belongsToBarangay(): bool
    {
        return $this === self::Youth || $this === self::Officer;
    }

    /**
     * Determine whether this role signs in through SK access.
     */
    public function isStaff(): bool
    {
        return $this !== self::Youth;
    }

    /**
     * Get the name of the route each role lands on after logging in.
     */
    public function homeRoute(): string
    {
        return match ($this) {
            self::Youth => 'dashboard',
            self::Officer => 'officer.dashboard',
            self::Federation => 'federation.dashboard',
            self::Administrator => 'admin.dashboard',
        };
    }
}
