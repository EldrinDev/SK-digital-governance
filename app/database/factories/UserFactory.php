<?php

namespace Database\Factories;

use App\Models\Barangay;
use App\Models\User;
use App\OfficerPosition;
use App\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'barangay_id' => Barangay::factory(),
            'privacy_consented_at' => now(),
        ];
    }

    /**
     * Indicate that the user is a barangay SK officer.
     */
    public function officer(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Officer,
            'position' => OfficerPosition::Chairperson,
        ]);
    }

    /**
     * Indicate the officer's seat on the council.
     */
    public function position(OfficerPosition $position): static
    {
        return $this->state(fn (array $attributes) => [
            'position' => $position,
        ]);
    }

    /**
     * Indicate that the user is an SK Federation user, who works across barangays.
     */
    public function federation(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Federation,
            'barangay_id' => null,
        ]);
    }

    /**
     * Indicate that the user is a system administrator, who works across barangays.
     */
    public function administrator(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Administrator,
            'barangay_id' => null,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
