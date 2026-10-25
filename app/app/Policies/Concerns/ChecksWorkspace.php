<?php

namespace App\Policies\Concerns;

use App\Models\User;
use App\UserRole;
use Illuminate\Auth\Access\Response;

/**
 * Keeps each barangay's records inside its own workspace.
 *
 * Records from another barangay answer "not found" rather than "forbidden",
 * so officers cannot learn what other barangays have.
 */
trait ChecksWorkspace
{
    /**
     * Allow reading: officers of the same barangay, and SK Federation users (read-only).
     */
    protected function canRead(User $user, int $barangayId): Response
    {
        if ($user->hasRole(UserRole::Federation)) {
            return Response::allow();
        }

        return $this->inOwnWorkspace($user, $barangayId);
    }

    /**
     * Allow changes only by officers of the same barangay.
     */
    protected function inOwnWorkspace(User $user, int $barangayId): Response
    {
        if (! $user->isOfficer() || $user->barangay_id !== $barangayId) {
            return Response::denyAsNotFound();
        }

        return Response::allow();
    }
}
