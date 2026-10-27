<?php

namespace Tests\Feature;

use App\Models\Abyip;
use App\Models\Event;
use App\Models\Milestone;
use App\Models\Ppa;
use App\Models\User;
use App\PpaStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramControllerTest extends TestCase
{
    use RefreshDatabase;

    private function publishedAbyip(): Abyip
    {
        $abyip = Abyip::factory()->approved()->create(['published_at' => now()]);
        $abyip->barangay->update(['is_participating' => true]);

        return $abyip;
    }

    public function test_lists_only_programs_of_published_plans(): void
    {
        $abyip = $this->publishedAbyip();
        Ppa::factory()->for($abyip)->create(['title' => 'Published summer league', 'status' => PpaStatus::Ongoing]);
        Ppa::factory()->for($abyip)->create(['title' => 'Cancelled thing', 'status' => PpaStatus::Cancelled]);
        Ppa::factory()->for(Abyip::factory()->approved())->create(['title' => 'Unpublished plan program']);

        $this->get(route('programs.index'))
            ->assertOk()
            ->assertSeeText('Published summer league')
            ->assertDontSeeText('Cancelled thing')
            ->assertDontSeeText('Unpublished plan program');
    }

    public function test_filters_by_status_and_counts_each(): void
    {
        $abyip = $this->publishedAbyip();
        Ppa::factory()->for($abyip)->create(['title' => 'Ongoing tutorial', 'status' => PpaStatus::Ongoing]);
        Ppa::factory()->for($abyip)->create(['title' => 'Planned bootcamp', 'status' => PpaStatus::Planned]);

        $this->get(route('programs.index', ['status' => 'upcoming']))
            ->assertSeeText('Planned bootcamp')
            ->assertDontSeeText('Ongoing tutorial')
            ->assertViewHas('statusCounts', ['ongoing' => 1, 'upcoming' => 1, 'completed' => 0]);
    }

    public function test_program_page_shows_goal_steps_and_events_to_join(): void
    {
        $abyip = $this->publishedAbyip();
        $program = Ppa::factory()->for($abyip)->create(['title' => 'Coastal clean-up', 'target' => '2 km cleared', 'status' => PpaStatus::Ongoing]);
        Milestone::factory()->completed()->for($program)->create(['title' => 'Volunteers signed up']);
        Milestone::factory()->for($program)->create(['title' => 'Clean-up day', 'due_on' => now()->addWeek()]);
        Event::factory()->for($abyip->barangay)->create(['title' => 'Clean-up Saturday', 'ppa_id' => $program->id, 'starts_at' => now()->addWeek()]);

        $this->get(route('programs.show', $program))
            ->assertOk()
            ->assertSeeText('2 km cleared')
            ->assertSeeText('Volunteers signed up')
            ->assertSeeText('1 of 2 done')
            ->assertSeeText('Clean-up Saturday');
    }

    public function test_program_of_an_unpublished_plan_is_not_found(): void
    {
        $this->get(route('programs.show', Ppa::factory()->create()))->assertNotFound();
    }

    public function test_youth_menu_has_programs_and_stays_in_the_workspace(): void
    {
        $youth = User::factory()->create();

        $this->actingAs($youth)
            ->get(route('programs.index'))
            ->assertOk()
            ->assertSee('data-sidebar-toggle', false)
            ->assertSee('href="'.route('programs.index').'"', false);
    }
}
