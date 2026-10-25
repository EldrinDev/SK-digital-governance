<?php

namespace Database\Factories;

use App\CenterOfParticipation;
use App\Models\Cbydp;
use App\Models\CbydpPriority;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CbydpPriority>
 */
class CbydpPriorityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cbydp_id' => Cbydp::factory(),
            'center' => fake()->randomElement(CenterOfParticipation::cases()),
            'title' => fake()->sentence(4),
            'description' => fake()->sentence(),
        ];
    }
}
