<?php

namespace Database\Factories;

use App\Models\Milestone;
use App\Models\Ppa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Milestone>
 */
class MilestoneFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ppa_id' => Ppa::factory(),
            'title' => fake()->sentence(3),
            'due_on' => now()->addWeeks(fake()->numberBetween(1, 6)),
        ];
    }

    /**
     * Indicate that the milestone is done.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed_at' => now(),
        ]);
    }
}
