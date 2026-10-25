<?php

namespace App\Models;

use App\CenterOfParticipation;
use Database\Factories\CbydpPriorityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One youth priority in a CBYDP, under a center of participation.
 */
#[Fillable(['center', 'title', 'description'])]
class CbydpPriority extends Model
{
    /** @use HasFactory<CbydpPriorityFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'center' => CenterOfParticipation::class,
        ];
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
