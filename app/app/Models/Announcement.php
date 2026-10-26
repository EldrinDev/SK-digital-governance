<?php

namespace App\Models;

use Database\Factories\AnnouncementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A notice from the barangay SK to its youth, shown on the public portal as soon as it's posted.
 */
#[Fillable(['title', 'body'])]
class Announcement extends Model
{
    /** @use HasFactory<AnnouncementFactory> */
    use HasFactory;

    /**
     * Announcements of barangays taking part in the platform, which the public may see.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function visibleToPublic(Builder $query): void
    {
        $query->whereHas('barangay', fn ($barangay) => $barangay->participating());
    }

    /**
     * @return BelongsTo<Barangay, $this>
     */
    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
