<?php

namespace App\Models;

use App\EventKind;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * An event or youth consultation that youth of the barangay can register for.
 */
#[Fillable(['ppa_id', 'kind', 'title', 'description', 'starts_at', 'venue', 'capacity'])]
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => EventKind::class,
            'starts_at' => 'datetime',
            'capacity' => 'integer',
            'reminders_sent_at' => 'datetime',
        ];
    }

    /**
     * Events that haven't started yet.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function upcoming(Builder $query): void
    {
        $query->where('starts_at', '>', now());
    }

    /**
     * Determine whether the event has already started.
     */
    public function hasStarted(): bool
    {
        return $this->starts_at->isPast();
    }

    /**
     * Get how many places are left, or null when there's no limit.
     */
    public function spotsLeft(): ?int
    {
        if ($this->capacity === null) {
            return null;
        }

        $taken = $this->registrations_count ?? $this->registrations()->count();

        return max(0, $this->capacity - $taken);
    }

    /**
     * Determine whether every place is taken.
     */
    public function isFull(): bool
    {
        return $this->spotsLeft() === 0;
    }

    /**
     * @return BelongsTo<Barangay, $this>
     */
    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }

    /**
     * The PPA this event carries out, if any.
     *
     * @return BelongsTo<Ppa, $this>
     */
    public function ppa(): BelongsTo
    {
        return $this->belongsTo(Ppa::class);
    }

    /**
     * @return HasMany<EventRegistration, $this>
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    /**
     * Evaluations from youth who attended.
     *
     * @return HasMany<Feedback, $this>
     */
    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }
}
