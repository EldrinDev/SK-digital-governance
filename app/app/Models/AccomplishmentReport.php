<?php

namespace App\Models;

use App\PpaStatus;
use App\ReportStatus;
use Database\Factories\AccomplishmentReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An accomplishment report an officer submitted for an approved ABYIP.
 *
 * The figures are compiled from the PPA records, never typed in, and frozen in a
 * snapshot at submission so later changes don't alter what was reported.
 */
class AccomplishmentReport extends Model
{
    /** @use HasFactory<AccomplishmentReportFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ReportStatus::class,
            'snapshot' => 'array',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * Compile the report figures for an ABYIP from its current records.
     *
     * A completed PPA without any evidence is flagged, because the report
     * shouldn't go for review until it has proof.
     *
     * @return array{
     *     barangay: string,
     *     year: int,
     *     resolution: string|null,
     *     approved_on: string|null,
     *     compiled_at: string,
     *     totals: array{ppas: int, completed: int, ongoing: int, planned: int, cancelled: int, delayed: int, budget: float, budget_completed: float},
     *     missing_evidence: int,
     *     ppas: list<array{title: string, center: string, priority: string, target: string, budget: float, starts_on: string, ends_on: string, status: string, delayed: bool, milestones_done: int, milestones_total: int, evidence: int, missing_evidence: bool}>
     * }
     */
    public static function compile(Abyip $abyip): array
    {
        $abyip->loadMissing(['barangay', 'ppas' => fn ($query) => $query->with(['priority', 'milestones'])->withCount('evidence')->orderBy('starts_on')]);

        $ppas = $abyip->ppas->map(fn (Ppa $ppa): array => [
            'title' => $ppa->title,
            'center' => $ppa->priority->center->label(),
            'priority' => $ppa->priority->title,
            'target' => $ppa->target,
            'budget' => (float) $ppa->budget,
            'starts_on' => $ppa->starts_on->toDateString(),
            'ends_on' => $ppa->ends_on->toDateString(),
            'status' => $ppa->status->label(),
            'delayed' => $ppa->isDelayed(),
            'milestones_done' => $ppa->milestones->whereNotNull('completed_at')->count(),
            'milestones_total' => $ppa->milestones->count(),
            'evidence' => $ppa->evidence_count,
            'missing_evidence' => $ppa->status === PpaStatus::Completed && $ppa->evidence_count === 0,
        ]);

        $active = $abyip->ppas->where('status', '!=', PpaStatus::Cancelled);

        return [
            'barangay' => $abyip->barangay->name,
            'year' => $abyip->year,
            'resolution' => $abyip->resolution_number,
            'approved_on' => $abyip->approved_on?->toDateString(),
            'compiled_at' => now()->toIso8601String(),
            'totals' => [
                'ppas' => $abyip->ppas->count(),
                'completed' => $abyip->ppas->where('status', PpaStatus::Completed)->count(),
                'ongoing' => $abyip->ppas->where('status', PpaStatus::Ongoing)->count(),
                'planned' => $abyip->ppas->where('status', PpaStatus::Planned)->count(),
                'cancelled' => $abyip->ppas->where('status', PpaStatus::Cancelled)->count(),
                'delayed' => $ppas->where('delayed', true)->count(),
                'budget' => (float) $active->sum('budget'),
                'budget_completed' => (float) $abyip->ppas->where('status', PpaStatus::Completed)->sum('budget'),
            ],
            'missing_evidence' => $ppas->where('missing_evidence', true)->count(),
            'ppas' => $ppas->values()->all(),
        ];
    }

    /**
     * @return BelongsTo<Barangay, $this>
     */
    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }

    /**
     * @return BelongsTo<Abyip, $this>
     */
    public function abyip(): BelongsTo
    {
        return $this->belongsTo(Abyip::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
