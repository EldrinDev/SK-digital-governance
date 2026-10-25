<?php

namespace App\Policies;

use App\Models\Abyip;
use App\Models\User;
use App\Policies\Concerns\ChecksWorkspace;
use Illuminate\Auth\Access\Response;

class AbyipPolicy
{
    use ChecksWorkspace;

    /**
     * Determine whether the user can see the program.
     */
    public function view(User $user, Abyip $abyip): Response
    {
        return $this->canRead($user, $abyip->barangay_id);
    }

    /**
     * Determine whether the user can add, change or remove the program's PPAs.
     */
    public function update(User $user, Abyip $abyip): Response
    {
        $workspace = $this->inOwnWorkspace($user, $abyip->barangay_id);

        if ($workspace->denied()) {
            return $workspace;
        }

        return $abyip->isApproved()
            ? Response::deny('This ABYIP is approved and locked. Changes need an amendment approved by the council.')
            : Response::allow();
    }

    /**
     * Determine whether the user can put the ABYIP on, or take it off, the public transparency board.
     * Only approved programs can be published.
     */
    public function publish(User $user, Abyip $abyip): Response
    {
        $workspace = $this->inOwnWorkspace($user, $abyip->barangay_id);

        if ($workspace->denied()) {
            return $workspace;
        }

        return $abyip->isApproved()
            ? Response::allow()
            : Response::deny('Only an approved ABYIP can be published.');
    }

    /**
     * Determine whether the user can submit an accomplishment report for the approved ABYIP.
     */
    public function report(User $user, Abyip $abyip): Response
    {
        return $this->publish($user, $abyip);
    }

    /**
     * Determine whether the user can record the council's approval.
     */
    public function approve(User $user, Abyip $abyip): Response
    {
        return $this->update($user, $abyip);
    }
}
