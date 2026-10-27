<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\OfficerPosition;
use App\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'barangay_id', 'privacy_consented_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The model's default values for attributes.
     *
     * @var array<string, string>
     */
    protected $attributes = [
        'role' => 'youth',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'privacy_consented_at' => 'datetime',
            'deactivated_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'position' => OfficerPosition::class,
        ];
    }

    /**
     * Get the barangay workspace this account belongs to.
     *
     * Youth and barangay SK officers belong to one barangay. SK Federation
     * users and administrators work across barangays, so theirs is empty.
     *
     * @return BelongsTo<Barangay, $this>
     */
    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }

    /**
     * The events this youth registered for.
     *
     * @return HasMany<EventRegistration, $this>
     */
    public function eventRegistrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    /**
     * Feedback and evaluations this youth sent.
     *
     * @return HasMany<Feedback, $this>
     */
    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    /**
     * Survey answers this youth submitted.
     *
     * @return HasMany<SurveyResponse, $this>
     */
    public function surveyResponses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }

    /**
     * Determine whether the user has the given role.
     */
    public function hasRole(UserRole $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Determine whether the account can still sign in.
     */
    public function isActive(): bool
    {
        return $this->deactivated_at === null;
    }

    /**
     * Determine whether the user is a barangay SK officer.
     */
    public function isOfficer(): bool
    {
        return $this->hasRole(UserRole::Officer);
    }

    /**
     * Get the code on the youth's digital Katipunan ng Kabataan ID, creating it the first time.
     * It's random, so a QR code reveals nothing about the person by itself.
     */
    public function memberCode(): string
    {
        if ($this->member_code === null) {
            $this->renewMemberCode();
        }

        return $this->member_code;
    }

    /**
     * Give the youth a new ID code, which makes any old ID (screenshot or printout) stop working.
     */
    public function renewMemberCode(): void
    {
        $prefix = 'KK-'.str($this->barangay?->name ?? 'OLO')->upper()->replaceMatches('/[^A-Z]/', '')->substr(0, 3);

        do {
            $code = $prefix.'-'.collect(range(1, 6))->map(fn () => 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'[random_int(0, 31)])->implode('');
        } while (self::where('member_code', $code)->exists());

        $this->forceFill(['member_code' => $code])->save();
    }

    /**
     * Get the person's initials, for avatars.
     */
    public function initials(): string
    {
        return collect(explode(' ', $this->name))->filter()->map(fn (string $part): string => mb_substr($part, 0, 1))->take(2)->implode('');
    }

    /**
     * Determine whether the user is an officer holding one of the given positions.
     */
    public function holdsPosition(OfficerPosition ...$positions): bool
    {
        return $this->isOfficer() && in_array($this->position, $positions, true);
    }
}
