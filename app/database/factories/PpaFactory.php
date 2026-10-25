<?php

namespace Database\Factories;

use App\Models\Abyip;
use App\Models\CbydpPriority;
use App\Models\Ppa;
use App\PpaStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ppa>
 */
class PpaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsOn = now()->addWeeks(fake()->numberBetween(1, 8))->startOfDay();

        return [
            'abyip_id' => Abyip::factory(),
            'barangay_id' => fn (array $attributes) => Abyip::find($attributes['abyip_id'])->barangay_id,
            'cbydp_priority_id' => fn (array $attributes) => CbydpPriority::factory()
                ->create(['cbydp_id' => Abyip::find($attributes['abyip_id'])->cbydp_id])
                ->id,
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'target' => fake()->numberBetween(20, 200).' youth reached',
            'budget' => fake()->numberBetween(5, 150) * 1000,
            'starts_on' => $startsOn,
            'ends_on' => (clone $startsOn)->addWeeks(fake()->numberBetween(1, 10)),
            'status' => PpaStatus::Planned,
        ];
    }

    /**
     * Indicate that the PPA ended in the past but is still open.
     */
    public function delayed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PpaStatus::Ongoing,
            'starts_on' => now()->subMonths(2),
            'ends_on' => now()->subWeek(),
        ]);
    }
}
