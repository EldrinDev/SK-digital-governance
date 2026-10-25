<?php

namespace Database\Factories;

use App\MeetingKind;
use App\Models\Barangay;
use App\Models\Meeting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Meeting>
 */
class MeetingFactory extends Factory
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
            'title' => 'Regular session',
            'kind' => MeetingKind::Regular,
            'held_on' => now()->subWeeks(fake()->numberBetween(1, 8))->toDateString(),
            'venue' => 'Barangay hall',
            'agenda' => fake()->sentence(),
        ];
    }
}
