<?php

namespace Database\Factories;

use App\Models\Abyip;
use App\Models\Cbydp;
use App\PlanStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Abyip>
 */
class AbyipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cbydp_id' => Cbydp::factory()->approved(),
            'barangay_id' => fn (array $attributes) => Cbydp::find($attributes['cbydp_id'])->barangay_id,
            'year' => fn (array $attributes) => Cbydp::find($attributes['cbydp_id'])->start_year,
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
            'resolution_number' => 'Res. No. '.fake()->numberBetween(1, 40),
            'approved_on' => now()->subWeeks(2),
        ]);
    }
}
