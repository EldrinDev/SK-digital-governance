<?php

namespace App\Models;

use Database\Factories\AuditLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * One entry in the audit trail: what was done, when, and by whom.
 */
#[Fillable(['user_id', 'barangay_id', 'action', 'subject_type', 'subject_id', 'properties', 'ip_address', 'user_agent'])]
class AuditLog extends Model
{
    /** @use HasFactory<AuditLogFactory> */
    use HasFactory;

    /**
     * Audit entries are never edited, so they only keep a creation time.
     */
    public const UPDATED_AT = null;

    /**
     * Plain-language names for each recorded action.
     *
     * @var array<string, string>
     */
    public const array ACTIONS = [
        'auth.login' => 'Logged in',
        'auth.logout' => 'Logged out',
        'auth.failed' => 'Failed login',
        'auth.lockout' => 'Locked out',
        'auth.registered' => 'Created an account',
        'auth.password_reset' => 'Reset password',
        'user.created' => 'Created a user',
        'user.updated' => 'Updated a user',
        'user.role_changed' => 'Changed a role',
        'user.deactivated' => 'Deactivated a user',
        'user.reactivated' => 'Reactivated a user',
        'barangay.updated' => 'Updated a barangay',
        'cbydp.created' => 'Created a CBYDP',
        'cbydp.updated' => 'Updated a CBYDP',
        'cbydp.approved' => 'Recorded CBYDP approval',
        'abyip.created' => 'Created an ABYIP',
        'abyip.approved' => 'Recorded ABYIP approval',
        'ppa.created' => 'Added a PPA',
        'ppa.updated' => 'Updated a PPA',
        'ppa.deleted' => 'Removed a PPA',
        'ppa.status_changed' => 'Changed PPA status',
        'milestone.created' => 'Added a milestone',
        'milestone.completed' => 'Completed a milestone',
        'milestone.reopened' => 'Reopened a milestone',
        'evidence.uploaded' => 'Uploaded evidence',
        'evidence.visibility_changed' => 'Changed evidence visibility',
        'evidence.deleted' => 'Deleted evidence',
        'meeting.created' => 'Recorded a session',
        'meeting.attendance_recorded' => 'Recorded attendance',
        'meeting.minutes_uploaded' => 'Uploaded minutes',
        'resolution.recorded' => 'Added a resolution',
        'remark.created' => 'Left a monitoring remark',
        'abyip.published' => 'Published an ABYIP',
        'abyip.unpublished' => 'Unpublished an ABYIP',
        'report.submitted' => 'Submitted a report',
        'report.reviewed' => 'Reviewed a report',
    ];

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
     * The barangay comes from the record that was acted on when it has one,
     * otherwise from the account that acted.
     *
     * @param  array<string, mixed>  $properties
     */
    public static function record(string $action, ?User $user = null, array $properties = [], ?Model $subject = null): self
    {
        $barangayId = match (true) {
            $subject instanceof Barangay => $subject->id,
            $subject !== null && $subject->getAttribute('barangay_id') !== null => $subject->getAttribute('barangay_id'),
            default => $user?->barangay_id,
        };

        return self::create([
            'user_id' => $user?->id,
            'barangay_id' => $barangayId,
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'properties' => $properties ?: null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Get the plain-language name of the action.
     */
    public function label(): string
    {
        return self::ACTIONS[$this->action] ?? $this->action;
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

    /**
     * Get the record the action was performed on, if any.
     *
     * @return MorphTo<Model, $this>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
