<?php

namespace Database\Factories;

use App\Models\Evidence;
use App\Models\Ppa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Evidence>
 */
class EvidenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ppa_id' => Ppa::factory(),
            'barangay_id' => fn (array $attributes) => Ppa::find($attributes['ppa_id'])->barangay_id,
            'title' => fake()->sentence(3),
            'path' => 'evidence/'.fake()->uuid().'.jpg',
            'original_name' => 'photo.jpg',
            'mime_type' => 'image/jpeg',
            'size' => fake()->numberBetween(50_000, 2_000_000),
            'is_public' => false,
        ];
    }

    /**
     * Indicate that the file can be shown on the public portal.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }
}
