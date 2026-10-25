<?php

namespace App\Models;

use Database\Factories\BarangayFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'is_participating'])]
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
     * Get the accounts that belong to this barangay workspace.
     *
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
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
