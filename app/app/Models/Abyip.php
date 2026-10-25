<?php

namespace App\Models;

use App\Models\Concerns\HasCouncilApproval;
use App\PlanStatus;
use Database\Factories\AbyipFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
     * @return BelongsTo<Cbydp, $this>
     */
    public function cbydp(): BelongsTo
    {
        return $this->belongsTo(Cbydp::class);
    }

    /**
     * @return HasMany<Ppa, $this>
     */
    public function ppas(): HasMany
    {
        return $this->hasMany(Ppa::class);
    }
}
