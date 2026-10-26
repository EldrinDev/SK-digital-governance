<?php

namespace App\Policies;

use App\Models\Meeting;
use App\Models\User;
use App\OfficerPosition;
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

    /**
     * Determine whether the user can upload the minutes, which the Secretary keeps.
     */
    public function recordMinutes(User $user, Meeting $meeting): Response
    {
        $workspace = $this->inOwnWorkspace($user, $meeting->barangay_id);

        if ($workspace->denied()) {
            return $workspace;
        }

        return $user->holdsPosition(OfficerPosition::Secretary, OfficerPosition::Chairperson)
            ? Response::allow()
            : Response::deny('Only the SK Secretary or Chairperson uploads the minutes.');
    }
}
