<?php

namespace App\Policies;

use App\Models\Resolution;
use App\Models\User;
use App\Policies\Concerns\ChecksWorkspace;
use Illuminate\Auth\Access\Response;

class ResolutionPolicy
{
    use ChecksWorkspace;

    /**
     * Determine whether the user can see the resolution.
     */
    public function view(User $user, Resolution $resolution): Response
    {
        return $this->canRead($user, $resolution->barangay_id);
    }
}
