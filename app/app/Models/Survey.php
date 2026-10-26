<?php

namespace App\Models;

use Database\Factories\SurveyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A survey the barangay SK asks its youth. Questions can change only while it's a draft;
 * once opened, youth of the barangay answer it once each until it's closed.
 */
#[Fillable(['title', 'description'])]
class Survey extends Model
{
    /** @use HasFactory<SurveyFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    /**
     * Surveys currently taking answers.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function open(Builder $query): void
    {
        $query->whereNotNull('opened_at')->whereNull('closed_at');
    }

    /**
     * Determine whether the survey is still being written.
     */
    public function isDraft(): bool
    {
        return $this->opened_at === null;
    }

    /**
     * Determine whether youth can answer it now.
     */
    public function isOpen(): bool
    {
        return $this->opened_at !== null && $this->closed_at === null;
    }

    /**
     * Get the status shown to people.
     */
    public function statusLabel(): string
    {
        return match (true) {
            $this->isDraft() => 'Draft',
            $this->isOpen() => 'Open',
            default => 'Closed',
        };
    }

    /**
     * @return BelongsTo<Barangay, $this>
     */
    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }

    /**
     * @return HasMany<SurveyQuestion, $this>
     */
    public function questions(): HasMany
    {
        return $this->hasMany(SurveyQuestion::class)->orderBy('position');
    }

    /**
     * @return HasMany<SurveyResponse, $this>
     */
    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }
}
