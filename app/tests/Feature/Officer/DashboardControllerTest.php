<?php

namespace Tests\Feature\Officer;

use App\Models\Abyip;
use App\Models\Cbydp;
use App\Models\Ppa;
use App\Models\User;
use App\PpaStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_workspace_points_officer_to_plans(): void
    {
        $this->actingAs(User::factory()->officer()->create())
            ->get(route('officer.dashboard'))
            ->assertOk()
            ->assertSeeText('Start with your three-year plan');
    }

    public function test_shows_counts_for_own_barangay_only(): void
    {
        $officer = User::factory()->officer()->create();
        $cbydp = Cbydp::factory()->approved()->for($officer->barangay)->create();
        $abyip = Abyip::factory()->approved()->for($cbydp)->create(['barangay_id' => $officer->barangay_id, 'year' => now()->year]);

        Ppa::factory()->create(['abyip_id' => $abyip->id, 'budget' => 10000]);
        Ppa::factory()->create(['abyip_id' => $abyip->id, 'budget' => 5000, 'status' => PpaStatus::Completed]);
        Ppa::factory()->delayed()->create(['abyip_id' => $abyip->id, 'budget' => 2500, 'title' => 'Overdue tree planting']);
        Ppa::factory()->delayed()->create(['title' => 'Elsewhere delayed']);

        $response = $this->actingAs($officer)->get(route('officer.dashboard'))->assertOk();

        $response->assertViewHas('totalPpas', 3)
            ->assertViewHas('completedPpas', 1)
            ->assertViewHas('delayedPpas', 1)
            ->assertSeeText('₱17,500')
            ->assertSeeText('Overdue tree planting')
            ->assertDontSeeText('Elsewhere delayed');
    }

    public function test_next_up_points_to_the_missing_abyip_and_delayed_work(): void
    {
        $officer = User::factory()->officer()->create();
        $year = now()->year;
        $cbydp = Cbydp::factory()->approved()->for($officer->barangay)->create(['start_year' => $year - 1, 'end_year' => $year + 1]);
        $lastYear = Abyip::factory()->approved()->for($cbydp)->create(['barangay_id' => $officer->barangay_id, 'year' => $year - 1]);
        Ppa::factory()->delayed()->create(['abyip_id' => $lastYear->id, 'title' => 'Basketball league']);
        Ppa::factory()->create(['abyip_id' => $lastYear->id, 'status' => PpaStatus::Completed]);

        $this->actingAs($officer)->get(route('officer.dashboard'))
            ->assertOk()
            ->assertSeeTextInOrder([
                'Next up for SK '.$officer->barangay->name,
                '1 PPA is past its end date and still open',
                'Basketball league. Mark each one completed, or cancel it.',
                "There’s no ABYIP for {$year} yet",
                '1 completed PPA has no evidence',
            ])
            ->assertSee(route('officer.abyips.create', ['cbydp' => $cbydp->id]), false);
    }

    public function test_next_up_says_so_when_nothing_is_waiting(): void
    {
        $officer = User::factory()->officer()->create();
        $cbydp = Cbydp::factory()->approved()->for($officer->barangay)->create();
        $abyip = Abyip::factory()->approved()->for($cbydp)->create(['barangay_id' => $officer->barangay_id, 'year' => now()->year, 'published_at' => now()]);
        Ppa::factory()->create(['abyip_id' => $abyip->id, 'ends_on' => now()->addMonth()]);

        $this->actingAs($officer)->get(route('officer.dashboard'))
            ->assertOk()
            ->assertSeeText('Nothing needs you right now');
    }
}
