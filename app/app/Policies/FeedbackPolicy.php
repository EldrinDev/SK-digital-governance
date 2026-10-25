<?php

namespace App\Policies;

use App\Models\Feedback;
use App\Models\User;
use App\Policies\Concerns\ChecksWorkspace;
use Illuminate\Auth\Access\Response;

class FeedbackPolicy
{
    use ChecksWorkspace;

    /**
     * Determine whether the officer can reply to the feedback. Each item gets one reply.
     */
    public function respond(User $user, Feedback $feedback): Response
    {
        $workspace = $this->inOwnWorkspace($user, $feedback->barangay_id);

        if ($workspace->denied()) {
            return $workspace;
        }

        return $feedback->isAnswered()
            ? Response::deny('This feedback already has a reply.')
            : Response::allow();
    }
}
