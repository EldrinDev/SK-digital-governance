<?php

namespace App\Policies;

use App\Models\Ppa;
use App\Models\User;
use App\Policies\Concerns\ChecksWorkspace;
use App\PpaStatus;
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

        return $ppa->abyip->isEditable()
            ? Response::allow()
            : Response::deny('This PPA is part of an approved ABYIP. Start an amendment to change it.');
    }

    /**
     * Determine whether the user can remove the PPA. During an amendment, a PPA that already
     * has progress or evidence must be cancelled instead, so its records are kept.
     */
    public function delete(User $user, Ppa $ppa): Response
    {
        $update = $this->update($user, $ppa);

        if ($update->denied() || ! $ppa->abyip->isApproved()) {
            return $update;
        }

        $hasHistory = $ppa->status !== PpaStatus::Planned
            || $ppa->evidence()->exists()
            || $ppa->milestones()->whereNotNull('completed_at')->exists();

        return $hasHistory
            ? Response::deny('This PPA already has progress or evidence. Set its status to Cancelled instead.')
            : Response::allow();
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
