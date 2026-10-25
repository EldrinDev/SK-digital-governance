<?php

namespace App\Models;

use Database\Factories\AuditLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One entry in the audit trail: what was done, when, and by whom.
 */
#[Fillable(['user_id', 'barangay_id', 'action', 'properties', 'ip_address', 'user_agent'])]
class AuditLog extends Model
{
    /** @use HasFactory<AuditLogFactory> */
    use HasFactory;

    /**
     * Audit entries are never edited, so they only keep a creation time.
     */
    public const UPDATED_AT = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }

    /**
     * Add an entry to the audit trail for the current request.
     *
     * @param  array<string, mixed>  $properties
     */
    public static function record(string $action, ?User $user = null, array $properties = []): self
    {
        return self::create([
            'user_id' => $user?->id,
            'barangay_id' => $user?->barangay_id,
            'action' => $action,
            'properties' => $properties ?: null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Get the account that performed the action, if any.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the barangay workspace the action happened in, if any.
     *
     * @return BelongsTo<Barangay, $this>
     */
    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }
}
