<?php

namespace App\Models;

use Database\Factories\MilestoneFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A checkpoint on the way to finishing a PPA.
 */
#[Fillable(['title', 'due_on'])]
class Milestone extends Model
{
    /** @use HasFactory<MilestoneFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_on' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Determine whether the milestone is done.
     */
    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    /**
     * Determine whether the milestone is past due and not done.
     */
    public function isOverdue(): bool
    {
        return ! $this->isCompleted() && $this->due_on->isBefore(today());
    }

    /**
     * @return BelongsTo<Ppa, $this>
     */
    public function ppa(): BelongsTo
    {
        return $this->belongsTo(Ppa::class);
    }
}
