<?php

namespace Database\Factories;

use App\Models\Announcement;
use App\Models\Barangay;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
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
            'title' => fake()->sentence(5),
            'body' => fake()->paragraphs(2, true),
        ];
    }
}
