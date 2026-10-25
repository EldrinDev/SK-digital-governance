<?php

namespace Database\Factories;

use App\Models\Abyip;
use App\Models\AccomplishmentReport;
use App\ReportStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccomplishmentReport>
 */
class AccomplishmentReportFactory extends Factory
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
            'status' => ReportStatus::Submitted,
            'snapshot' => fn (array $attributes) => AccomplishmentReport::compile(Abyip::find($attributes['abyip_id'])),
            'submitted_at' => now(),
        ];
    }
}
