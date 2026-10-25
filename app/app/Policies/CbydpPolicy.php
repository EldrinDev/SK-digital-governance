<?php

namespace App\Policies;

use App\Models\Cbydp;
use App\Models\User;
use App\Policies\Concerns\ChecksWorkspace;
use Illuminate\Auth\Access\Response;

class CbydpPolicy
{
    use ChecksWorkspace;

    /**
     * Determine whether the user can see the plan.
     */
    public function view(User $user, Cbydp $cbydp): Response
    {
        return $this->canRead($user, $cbydp->barangay_id);
    }

    /**
     * Determine whether the user can change the plan's priorities.
     */
    public function update(User $user, Cbydp $cbydp): Response
    {
        $workspace = $this->inOwnWorkspace($user, $cbydp->barangay_id);

        if ($workspace->denied()) {
            return $workspace;
        }

        return $cbydp->isApproved()
            ? Response::deny('This CBYDP is approved and locked.')
            : Response::allow();
    }

    /**
     * Determine whether the user can record the council's approval.
     */
    public function approve(User $user, Cbydp $cbydp): Response
    {
        return $this->update($user, $cbydp);
    }
}
