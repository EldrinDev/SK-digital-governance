<?php

namespace Database\Factories;

use App\EventKind;
use App\Models\Barangay;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'barangay_id' => Barangay::factory(),
            'kind' => EventKind::Event,
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(10),
            'starts_at' => now()->addWeeks(2)->setTime(9, 0),
            'venue' => 'Barangay covered court',
            'capacity' => null,
        ];
    }

    /**
     * Indicate that the event already happened.
     */
    public function past(): static
    {
        return $this->state(fn (array $attributes) => [
            'starts_at' => now()->subWeek()->setTime(9, 0),
        ]);
    }
}
