<?php

namespace App\Policies;

use App\Models\Meeting;
use App\Models\User;
use App\Policies\Concerns\ChecksWorkspace;
use Illuminate\Auth\Access\Response;

class MeetingPolicy
{
    use ChecksWorkspace;

    /**
     * Determine whether the user can see the meeting.
     */
    public function view(User $user, Meeting $meeting): Response
    {
        return $this->canRead($user, $meeting->barangay_id);
    }

    /**
     * Determine whether the user can record attendance, minutes and resolutions for it.
     */
    public function update(User $user, Meeting $meeting): Response
    {
        return $this->inOwnWorkspace($user, $meeting->barangay_id);
    }
}
