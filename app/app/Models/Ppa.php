<?php

namespace App\Models;

use App\PlanStatus;
use App\PpaStatus;
use Database\Factories\PpaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A program, project or activity carried out by the SK, listed in an ABYIP.
 */
#[Fillable(['cbydp_priority_id', 'title', 'description', 'target', 'budget', 'starts_on', 'ends_on'])]
class Ppa extends Model
{
    /** @use HasFactory<PpaFactory> */
    use HasFactory;

    /**
     * The model's default values for attributes.
     *
     * @var array<string, string>
     */
    protected $attributes = [
        'status' => 'planned',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PpaStatus::class,
            'budget' => 'decimal:2',
            'amount_spent' => 'decimal:2',
            'spending_recorded_at' => 'datetime',
            'starts_on' => 'date',
            'ends_on' => 'date',
        ];
    }

    /**
     * Determine whether the recorded spending is above the PPA's budget.
     */
    public function isOverBudget(): bool
    {
        return $this->amount_spent !== null && (float) $this->amount_spent > (float) $this->budget;
    }

    /**
     * Get the share of the budget spent so far, as a whole percentage.
     */
    public function budgetUsedPercent(): ?int
    {
        if ($this->amount_spent === null || (float) $this->budget <= 0) {
            return null;
        }

        return (int) round((float) $this->amount_spent / (float) $this->budget * 100);
    }

    /**
     * Determine whether the PPA is in implementation (its ABYIP is approved) and still open after its end date.
     */
    public function isDelayed(): bool
    {
        return ! $this->status->isClosed()
            && $this->ends_on->isBefore(today())
            && $this->abyip->isApproved();
    }

    /**
     * PPAs in an approved ABYIP that are still open after their end date.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function delayed(Builder $query): void
    {
        $query->whereIn('status', [PpaStatus::Planned, PpaStatus::Ongoing])
            ->whereDate('ends_on', '<', today())
            ->whereHas('abyip', fn (Builder $abyip) => $abyip->where('status', PlanStatus::Approved));
    }

    /**
     * Programs the public may see: not cancelled, in an ABYIP the SK published,
     * of a barangay taking part in the platform.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function publiclyVisible(Builder $query): void
    {
        $query->where('status', '!=', PpaStatus::Cancelled)
            ->whereHas('abyip', fn (Builder $abyip) => $abyip->published())
            ->whereHas('barangay', fn (Builder $barangay) => $barangay->participating());
    }

    /**
     * Get the share of milestones completed, from 0 to 100.
     */
    public function progress(): int
    {
        $total = $this->milestones->count();

        if ($total === 0) {
            return $this->status === PpaStatus::Completed ? 100 : 0;
        }

        return (int) round($this->milestones->whereNotNull('completed_at')->count() / $total * 100);
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
     * @return BelongsTo<CbydpPriority, $this>
     */
    public function priority(): BelongsTo
    {
        return $this->belongsTo(CbydpPriority::class, 'cbydp_priority_id');
    }

    /**
     * @return HasMany<Evidence, $this>
     */
    public function evidence(): HasMany
    {
        return $this->hasMany(Evidence::class)->latest();
    }

    /**
     * Events held as part of this program.
     *
     * @return HasMany<Event, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * @return HasMany<Milestone, $this>
     */
    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class)->orderBy('due_on');
    }
}
