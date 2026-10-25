<?php

namespace App\Models;

use App\AttendanceStatus;
use App\MeetingKind;
use Database\Factories\MeetingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A session of the barangay SK council.
 */
#[Fillable(['title', 'kind', 'held_on', 'venue', 'agenda'])]
class Meeting extends Model
{
    /** @use HasFactory<MeetingFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => MeetingKind::class,
            'held_on' => 'date',
        ];
    }

    /**
     * Get how many recorded members were present.
     */
    public function presentCount(): int
    {
        return $this->attendees->filter(fn (User $member): bool => $member->pivot->status === AttendanceStatus::Present->value)->count();
    }

    /**
     * Determine whether a majority of the recorded members were present, or null when attendance isn't recorded yet.
     */
    public function hasQuorum(): ?bool
    {
        $recorded = $this->attendees->count();

        return $recorded === 0 ? null : $this->presentCount() > $recorded / 2;
    }

    /**
     * @return BelongsTo<Barangay, $this>
     */
    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }

    /**
     * The officers whose attendance was recorded, with their status on the pivot.
     *
     * @return BelongsToMany<User, $this>
     */
    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'meeting_attendances')
            ->withPivot('status')
            ->withTimestamps()
            ->orderBy('name');
    }

    /**
     * @return HasMany<Resolution, $this>
     */
    public function resolutions(): HasMany
    {
        return $this->hasMany(Resolution::class)->orderBy('number');
    }
}
