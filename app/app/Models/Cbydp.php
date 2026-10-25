<?php

namespace App\Models;

use App\Models\Concerns\HasCouncilApproval;
use App\PlanStatus;
use Database\Factories\CbydpFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Comprehensive Barangay Youth Development Plan: a barangay's three-year plan of youth priorities.
 */
#[Fillable(['start_year', 'end_year'])]
class Cbydp extends Model
{
    /** @use HasFactory<CbydpFactory> */
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
        ];
    }

    /**
     * Get the plan's period, e.g. "2026–2028".
     */
    public function period(): string
    {
        return "{$this->start_year}–{$this->end_year}";
    }

    /**
     * Determine whether a year falls inside this plan's period.
     */
    public function covers(int $year): bool
    {
        return $year >= $this->start_year && $year <= $this->end_year;
    }

    /**
     * @return BelongsTo<Barangay, $this>
     */
    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }

    /**
     * @return HasMany<CbydpPriority, $this>
     */
    public function priorities(): HasMany
    {
        return $this->hasMany(CbydpPriority::class);
    }

    /**
     * @return HasMany<Abyip, $this>
     */
    public function abyips(): HasMany
    {
        return $this->hasMany(Abyip::class);
    }
}
