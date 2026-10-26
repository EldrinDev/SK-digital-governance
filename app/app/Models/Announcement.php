<?php

namespace App\Models;

use App\AnnouncementCategory;
use Database\Factories\AnnouncementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\HtmlString;

/**
 * A post from the barangay SK to its youth, shown on the public portal as soon as it's posted.
 */
#[Fillable(['category', 'title', 'body'])]
class Announcement extends Model
{
    /** @use HasFactory<AnnouncementFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => AnnouncementCategory::class,
            'pinned_at' => 'datetime',
        ];
    }

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
     * Pinned posts first, then the newest.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function pinnedFirst(Builder $query): void
    {
        $query->orderByRaw('pinned_at is null')->orderByDesc('pinned_at')->latest()->orderByDesc('id');
    }

    /**
     * Determine whether the post is pinned to the top.
     */
    public function isPinned(): bool
    {
        return $this->pinned_at !== null;
    }

    /**
     * Get the caption as HTML with its hashtags highlighted. The text is escaped first,
     * so only the highlight markup is trusted.
     */
    public function captionHtml(?int $limit = null): HtmlString
    {
        $text = $limit ? str($this->body)->limit($limit)->toString() : $this->body;

        return new HtmlString(preg_replace(
            '/(^|\s)#([\p{L}\p{N}_]+)/u',
            '$1<span class="font-semibold text-emerald">#$2</span>',
            e($text),
        ));
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
