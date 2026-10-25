<?php

namespace App\Models\Concerns;

use App\Models\User;
use App\PlanStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Shared by plans (CBYDP and ABYIP) that the SK council approves by resolution.
 * The system only records the council's decision; it never approves anything itself.
 *
 * @property PlanStatus $status
 */
trait HasCouncilApproval
{
    /**
     * Determine whether the council's approval has been recorded, which locks the plan.
     */
    public function isApproved(): bool
    {
        return $this->status === PlanStatus::Approved;
    }

    /**
     * Get the officer who recorded the council's approval.
     *
     * @return BelongsTo<User, $this>
     */
    public function approvalRecorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approval_recorded_by');
    }
}
