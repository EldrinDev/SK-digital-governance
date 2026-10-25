<?php

namespace Tests\Feature\Federation;

use App\Models\Abyip;
use App\Models\Barangay;
use App\Models\Cbydp;
use App\Models\Ppa;
use App\Models\User;
use App\PpaStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_compares_participating_barangays_only(): void
    {
        $santaRita = Barangay::factory()->create(['name' => 'Santa Rita']);
        Barangay::factory()->create(['name' => 'Asinan', 'is_participating' => false]);

        $cbydp = Cbydp::factory()->approved()->for($santaRita)->create();
        $abyip = Abyip::factory()->approved()->for($cbydp)->create(['barangay_id' => $santaRita->id, 'year' => now()->year]);
        Ppa::factory()->create(['abyip_id' => $abyip->id, 'status' => PpaStatus::Completed, 'budget' => 10000]);
        Ppa::factory()->delayed()->create(['abyip_id' => $abyip->id, 'budget' => 5000]);

        $response = $this->actingAs(User::factory()->federation()->create())
            ->get(route('federation.dashboard'))
            ->assertOk()
            ->assertSeeText('Santa Rita')
            ->assertDontSeeText('Asinan')
            ->assertSeeText('₱15,000');

        $row = $response->viewData('barangays')->firstWhere('name', 'Santa Rita');
        $this->assertSame(1, $row->completed_count);
        $this->assertSame(1, $row->delayed_count);
        $this->assertSame(1, $row->approved_abyip_count);
        $this->assertSame(50, $response->viewData('totals')['completionRate']);
    }

    public function test_officers_cannot_open_the_federation_console(): void
    {
        $this->actingAs(User::factory()->officer()->create())
            ->get(route('federation.dashboard'))
            ->assertForbidden();
    }
}
