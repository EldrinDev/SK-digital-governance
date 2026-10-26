<?php

namespace App\Policies;

use App\Models\Abyip;
use App\Models\User;
use App\OfficerPosition;
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

        return $abyip->isEditable()
            ? Response::allow()
            : Response::deny('This ABYIP is approved and locked. Start an amendment to change it.');
    }

    /**
     * Determine whether the user can start an amendment to the approved ABYIP.
     */
    public function amend(User $user, Abyip $abyip): Response
    {
        $workspace = $this->inOwnWorkspace($user, $abyip->barangay_id);

        if ($workspace->denied()) {
            return $workspace;
        }

        return match (true) {
            ! $abyip->isApproved() => Response::deny('A draft ABYIP can be edited directly.'),
            $abyip->isBeingAmended() => Response::deny('An amendment is already open.'),
            default => Response::allow(),
        };
    }

    /**
     * Determine whether the user can record the council's approval of the open amendment.
     */
    public function approveAmendment(User $user, Abyip $abyip): Response
    {
        $workspace = $this->inOwnWorkspace($user, $abyip->barangay_id);

        if ($workspace->denied()) {
            return $workspace;
        }

        return $abyip->isBeingAmended()
            ? Response::allow()
            : Response::deny('There is no open amendment.');
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

        if (! $abyip->isApproved()) {
            return Response::deny('Only an approved ABYIP can be published.');
        }

        return $user->holdsPosition(OfficerPosition::Chairperson)
            ? Response::allow()
            : Response::deny('Only the SK Chairperson approves items for public disclosure.');
    }

    /**
     * Determine whether the user can submit an accomplishment report for the approved ABYIP.
     */
    public function report(User $user, Abyip $abyip): Response
    {
        $workspace = $this->inOwnWorkspace($user, $abyip->barangay_id);

        if ($workspace->denied()) {
            return $workspace;
        }

        return $abyip->isApproved()
            ? Response::allow()
            : Response::deny('Only an approved ABYIP can be reported on.');
    }

    /**
     * Determine whether the user can record the council's approval.
     */
    public function approve(User $user, Abyip $abyip): Response
    {
        $workspace = $this->inOwnWorkspace($user, $abyip->barangay_id);

        if ($workspace->denied()) {
            return $workspace;
        }

        return $abyip->isApproved()
            ? Response::deny('This ABYIP is already approved.')
            : Response::allow();
    }
}
