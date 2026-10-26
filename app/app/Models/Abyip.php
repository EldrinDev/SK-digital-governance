<?php

namespace App\Models;

use App\Models\Concerns\HasCouncilApproval;
use App\PlanStatus;
use Database\Factories\AbyipFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Annual Barangay Youth Investment Program: the year's PPAs, taken from the CBYDP.
 */
#[Fillable(['year'])]
class Abyip extends Model
{
    /** @use HasFactory<AbyipFactory> */
    use HasCouncilApproval, HasFactory;

    /**
     * The model's default values for attributes.
     *
     * @var array<string, string>
     */
    protected $attributes = [
        'status' => 'draft',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PlanStatus::class,
            'approved_on' => 'date',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Determine whether the ABYIP is on the public transparency board.
     */
    public function isPublished(): bool
    {
        return $this->published_at !== null;
    }

    /**
     * ABYIPs on the public transparency board. One being amended is held back
     * until the council approves the amendment, so the public never sees unapproved figures.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->whereNotNull('published_at')
            ->whereDoesntHave('amendments', fn (Builder $amendment) => $amendment->where('status', PlanStatus::Draft));
    }

    /**
     * Determine whether an amendment is being prepared.
     */
    public function isBeingAmended(): bool
    {
        return $this->openAmendment !== null;
    }

    /**
     * Determine whether the planned PPAs can change: still a draft, or approved with an open amendment.
     */
    public function isEditable(): bool
    {
        return ! $this->isApproved() || $this->isBeingAmended();
    }

    /**
     * @return BelongsTo<Barangay, $this>
     */
    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }

    /**
     * @return BelongsTo<Cbydp, $this>
     */
    public function cbydp(): BelongsTo
    {
        return $this->belongsTo(Cbydp::class);
    }

    /**
     * @return HasMany<AbyipAmendment, $this>
     */
    public function amendments(): HasMany
    {
        return $this->hasMany(AbyipAmendment::class)->latest();
    }

    /**
     * The amendment being prepared, if any.
     *
     * @return HasOne<AbyipAmendment, $this>
     */
    public function openAmendment(): HasOne
    {
        return $this->hasOne(AbyipAmendment::class)->where('status', PlanStatus::Draft);
    }

    /**
     * @return HasMany<AccomplishmentReport, $this>
     */
    public function reports(): HasMany
    {
        return $this->hasMany(AccomplishmentReport::class)->latest('submitted_at');
    }

    /**
     * @return HasMany<Ppa, $this>
     */
    public function ppas(): HasMany
    {
        return $this->hasMany(Ppa::class)->chaperone();
    }
}
