<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use App\Policies\Concerns\ChecksWorkspace;
use Illuminate\Auth\Access\Response;

class EventPolicy
{
    use ChecksWorkspace;

    /**
     * Determine whether the officer can manage the event: see registrants and check them in.
     */
    public function update(User $user, Event $event): Response
    {
        return $this->inOwnWorkspace($user, $event->barangay_id);
    }
}
