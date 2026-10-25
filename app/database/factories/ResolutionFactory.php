<?php

namespace Database\Factories;

use App\Models\Barangay;
use App\Models\Resolution;
use App\ResolutionOutcome;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Resolution>
 */
class ResolutionFactory extends Factory
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
            'number' => 'Res. No. '.fake()->unique()->numberBetween(1, 999).', s. '.now()->year,
            'title' => fake()->sentence(5),
            'outcome' => ResolutionOutcome::Approved,
            'adopted_on' => now()->subWeek()->toDateString(),
        ];
    }
}
