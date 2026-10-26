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
     * Pattern for caption lines that give the when and where, written the way SK pages do:
     * "🗓️ November 7", "🕑 2:00 PM", "📍 Covered court".
     */
    private const string DETAIL_LINE = '/^\s*(🗓️?|📅|⏰|[\x{1F550}-\x{1F567}]|📍)\s*(.+)$/u';

    /**
     * Get the date, time and place lines from the caption.
     *
     * @return list<array{kind: 'date'|'time'|'place', text: string}>
     */
    public function details(): array
    {
        $details = [];

        foreach (preg_split('/\R/u', $this->body) as $line) {
            if (preg_match(self::DETAIL_LINE, $line, $match) === 1) {
                $details[] = [
                    'kind' => match (true) {
                        str_starts_with($match[1], '📍') => 'place',
                        str_starts_with($match[1], '🗓'), str_starts_with($match[1], '📅') => 'date',
                        default => 'time',
                    },
                    'text' => trim($match[2]),
                ];
            }
        }

        return $details;
    }

    /**
     * Get the caption as HTML with its hashtags highlighted. The text is escaped first,
     * so only the highlight markup is trusted. Detail lines can be left out where they
     * are already shown on their own.
     */
    public function captionHtml(?int $limit = null, bool $withoutDetails = false): HtmlString
    {
        $text = $this->body;

        if ($withoutDetails) {
            $lines = array_filter(preg_split('/\R/u', $text), fn (string $line): bool => preg_match(self::DETAIL_LINE, $line) !== 1);
            $text = trim(preg_replace('/\n{3,}/', "\n\n", implode("\n", $lines)));
        }

        if ($limit !== null) {
            $text = str($text)->limit($limit, '…', preserveWords: true)->toString();
        }

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
