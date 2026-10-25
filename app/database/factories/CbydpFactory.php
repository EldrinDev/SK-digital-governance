<?php

namespace Database\Factories;

use App\Models\Barangay;
use App\Models\Cbydp;
use App\PlanStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cbydp>
 */
class CbydpFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startYear = (int) now()->format('Y');

        return [
            'barangay_id' => Barangay::factory(),
            'start_year' => $startYear,
            'end_year' => $startYear + 2,
            'status' => PlanStatus::Draft,
        ];
    }

    /**
     * Indicate that the council's approval has been recorded.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PlanStatus::Approved,
            'resolution_number' => 'Res. No. '.fake()->numberBetween(1, 40).', s. '.$attributes['start_year'],
            'approved_on' => now()->subMonth(),
        ]);
    }
}
