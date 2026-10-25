<?php

namespace App\Policies;

use App\Models\Ppa;
use App\Models\User;
use App\Policies\Concerns\ChecksWorkspace;
use Illuminate\Auth\Access\Response;

class PpaPolicy
{
    use ChecksWorkspace;

    /**
     * Determine whether the user can see the PPA.
     */
    public function view(User $user, Ppa $ppa): Response
    {
        return $this->canRead($user, $ppa->barangay_id);
    }

    /**
     * Determine whether the user can change the PPA's planned details.
     * Only possible while its ABYIP is still a draft.
     */
    public function update(User $user, Ppa $ppa): Response
    {
        $workspace = $this->inOwnWorkspace($user, $ppa->barangay_id);

        if ($workspace->denied()) {
            return $workspace;
        }

        return $ppa->abyip->isApproved()
            ? Response::deny('This PPA is part of an approved ABYIP. Its planned details are locked.')
            : Response::allow();
    }

    /**
     * Determine whether the user can remove the PPA from its draft ABYIP.
     */
    public function delete(User $user, Ppa $ppa): Response
    {
        return $this->update($user, $ppa);
    }

    /**
     * Determine whether the user can plan milestones, which is allowed before and after approval.
     */
    public function planMilestones(User $user, Ppa $ppa): Response
    {
        return $this->inOwnWorkspace($user, $ppa->barangay_id);
    }

    /**
     * Determine whether the user can record implementation progress: status and milestones.
     * Implementation starts once the council has approved the ABYIP.
     */
    public function track(User $user, Ppa $ppa): Response
    {
        $workspace = $this->inOwnWorkspace($user, $ppa->barangay_id);

        if ($workspace->denied()) {
            return $workspace;
        }

        return $ppa->abyip->isApproved()
            ? Response::allow()
            : Response::deny('Progress can be recorded once the council approves the ABYIP.');
    }
}
