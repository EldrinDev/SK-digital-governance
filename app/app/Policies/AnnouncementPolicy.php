<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;
use App\Policies\Concerns\ChecksWorkspace;
use Illuminate\Auth\Access\Response;

class AnnouncementPolicy
{
    use ChecksWorkspace;

    /**
     * Determine whether the user can edit or take down the announcement.
     */
    public function update(User $user, Announcement $announcement): Response
    {
        return $this->inOwnWorkspace($user, $announcement->barangay_id);
    }
}
