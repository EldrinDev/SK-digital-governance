<?php

namespace App\Policies;

use App\Models\AccomplishmentReport;
use App\Models\User;
use App\Policies\Concerns\ChecksWorkspace;
use App\UserRole;
use Illuminate\Auth\Access\Response;

class AccomplishmentReportPolicy
{
    use ChecksWorkspace;

    /**
     * Determine whether the user can read the submitted report.
     */
    public function view(User $user, AccomplishmentReport $report): Response
    {
        return $this->canRead($user, $report->barangay_id);
    }

    /**
     * Determine whether the user can mark the report as reviewed.
     */
    public function review(User $user, AccomplishmentReport $report): Response
    {
        return $user->hasRole(UserRole::Federation)
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
