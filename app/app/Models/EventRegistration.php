<?php

namespace App\Models;

use Database\Factories\EventRegistrationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A youth's place at an event, and whether they were checked in.
 */
#[Fillable(['user_id'])]
class EventRegistration extends Model
{
    /** @use HasFactory<EventRegistrationFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attended_at' => 'datetime',
        ];
    }

    /**
     * Determine whether the youth was checked in.
     */
    public function attended(): bool
    {
        return $this->attended_at !== null;
    }

    /**
     * Count check-ins for each of the last few months, oldest first, optionally for given barangays.
     * Grouped in PHP so it works the same on every database engine.
     *
     * @param  list<int>|null  $barangayIds
     * @return list<array{label: string, value: int}>
     */
    public static function monthlyCheckIns(?array $barangayIds = null, int $months = 6): array
    {
        $start = today()->startOfMonth()->subMonths($months - 1);

        $counts = self::query()
            ->whereNotNull('attended_at')
            ->where('attended_at', '>=', $start)
            ->when($barangayIds !== null, fn ($query) => $query->whereHas('event', fn ($event) => $event->whereIn('barangay_id', $barangayIds)))
            ->pluck('attended_at')
            ->countBy(fn (Carbon $attendedAt): string => $attendedAt->format('Y-m'));

        return collect(range(0, $months - 1))
            ->map(fn (int $offset): array => [
                'label' => $start->copy()->addMonths($offset)->format('M'),
                'value' => $counts->get($start->copy()->addMonths($offset)->format('Y-m'), 0),
            ])
            ->all();
    }

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
