<?php

namespace Database\Factories;

use App\Models\Barangay;
use App\Models\MonitoringRemark;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MonitoringRemark>
 */
class MonitoringRemarkFactory extends Factory
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
            'user_id' => User::factory()->federation(),
            'body' => fake()->sentence(12),
        ];
    }
}
