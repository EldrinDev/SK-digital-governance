<?php

namespace Database\Factories;

use App\Models\Abyip;
use App\Models\AbyipAmendment;
use App\PlanStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AbyipAmendment>
 */
class AbyipAmendmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'abyip_id' => Abyip::factory()->approved(),
            'barangay_id' => fn (array $attributes) => Abyip::find($attributes['abyip_id'])->barangay_id,
            'reason' => fake()->sentence(),
            'status' => PlanStatus::Draft,
        ];
    }
}
