<?php

namespace App\Models;

use App\UserRole;
use Database\Factories\BarangayFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'is_participating', 'facebook_url'])]
#[Hidden(['facebook_page_token'])]
class Barangay extends Model
{
    /** @use HasFactory<BarangayFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_participating' => 'boolean',
            'facebook_page_token' => 'encrypted',
            'facebook_connected_at' => 'datetime',
        ];
    }

    /**
     * Only barangays that take part in the platform (the pilot).
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function participating(Builder $query): void
    {
        $query->where('is_participating', true);
    }

    /**
     * Get the address of the SK's logo: its own when the administrator uploaded one,
     * otherwise the national SK logo.
     */
    public function logoUrl(): string
    {
        return $this->logo_path !== null
            ? route('barangays.logo', ['barangay' => $this, 'v' => substr(md5($this->logo_path), 0, 8)])
            : asset('images/sk-logo.svg');
    }

    /**
     * Determine whether the SK's Facebook Page is connected, so its posts can be imported.
     */
    public function hasFacebookPage(): bool
    {
        return $this->facebook_page_id !== null && $this->facebook_page_token !== null;
    }

    /**
     * Get the accounts that belong to this barangay workspace.
     *
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * The barangay's SK officers whose accounts can sign in, e.g. to notify them.
     *
     * @return Collection<int, User>
     */
    public function activeOfficers(): Collection
    {
        return $this->users()->where('role', UserRole::Officer)->whereNull('deactivated_at')->get();
    }

    /**
     * @return HasMany<Cbydp, $this>
     */
    public function cbydps(): HasMany
    {
        return $this->hasMany(Cbydp::class);
    }

    /**
     * @return HasMany<Abyip, $this>
     */
    public function abyips(): HasMany
    {
        return $this->hasMany(Abyip::class);
    }

    /**
     * @return HasMany<MonitoringRemark, $this>
     */
    public function remarks(): HasMany
    {
        return $this->hasMany(MonitoringRemark::class)->latest();
    }

    /**
     * @return HasMany<Announcement, $this>
     */
    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    /**
     * @return HasMany<Survey, $this>
     */
    public function surveys(): HasMany
    {
        return $this->hasMany(Survey::class);
    }

    /**
     * @return HasMany<Event, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * @return HasMany<Feedback, $this>
     */
    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    /**
     * @return HasMany<Meeting, $this>
     */
    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class);
    }

    /**
     * @return HasMany<Resolution, $this>
     */
    public function resolutions(): HasMany
    {
        return $this->hasMany(Resolution::class);
    }

    /**
     * @return HasMany<Ppa, $this>
     */
    public function ppas(): HasMany
    {
        return $this->hasMany(Ppa::class);
    }
}
