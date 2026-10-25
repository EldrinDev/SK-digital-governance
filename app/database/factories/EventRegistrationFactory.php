<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventRegistration>
 */
class EventRegistrationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'user_id' => fn (array $attributes) => User::factory()->create(['barangay_id' => Event::find($attributes['event_id'])->barangay_id])->id,
        ];
    }

    /**
     * Indicate that the youth was checked in.
     */
    public function attended(): static
    {
        return $this->state(fn (array $attributes) => [
            'attended_at' => now(),
        ]);
    }
}
