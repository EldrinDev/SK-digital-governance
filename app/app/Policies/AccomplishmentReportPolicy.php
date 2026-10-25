<?php

namespace App\Policies;

use App\Models\AccomplishmentReport;
use App\Models\User;
use App\Policies\Concerns\ChecksWorkspace;
use App\ReportStatus;
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
     * Determine whether the user can mark the report as reviewed. A review is
     * recorded once, so who reviewed it and when can't be overwritten later.
     */
    public function review(User $user, AccomplishmentReport $report): Response
    {
        if (! $user->hasRole(UserRole::Federation)) {
            return Response::denyAsNotFound();
        }

        return $report->status === ReportStatus::Submitted
            ? Response::allow()
            : Response::deny('This report has already been reviewed.');
    }
}
