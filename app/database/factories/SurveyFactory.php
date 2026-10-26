<?php

namespace Database\Factories;

use App\Models\Barangay;
use App\Models\Survey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Survey>
 */
class SurveyFactory extends Factory
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
            'title' => fake()->sentence(4),
            'description' => fake()->sentence(),
        ];
    }

    /**
     * Indicate that youth can answer it.
     */
    public function opened(): static
    {
        return $this->state(fn (array $attributes) => [
            'opened_at' => now()->subDay(),
        ]);
    }
}
