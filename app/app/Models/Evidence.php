<?php

namespace App\Models;

use Database\Factories\EvidenceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A photo or document proving work on a PPA. Internal unless the officer marks it public.
 */
#[Fillable(['title', 'is_public'])]
class Evidence extends Model
{
    /** @use HasFactory<EvidenceFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'size' => 'integer',
        ];
    }

    /**
     * Determine whether the file is a photo that can be shown inline.
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Get the file size for people, e.g. "1.4 MB".
     */
    public function readableSize(): string
    {
        return $this->size >= 1_048_576
            ? number_format($this->size / 1_048_576, 1).' MB'
            : max(1, (int) round($this->size / 1024)).' KB';
    }

    /**
     * @return BelongsTo<Ppa, $this>
     */
    public function ppa(): BelongsTo
    {
        return $this->belongsTo(Ppa::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
