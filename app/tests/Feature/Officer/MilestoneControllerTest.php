<?php

namespace Tests\Feature\Officer;

use App\Models\Abyip;
use App\Models\Milestone;
use App\Models\Ppa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MilestoneControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_officer_adds_milestones_even_before_approval(): void
    {
        $ppa = Ppa::factory()->create();
        $officer = User::factory()->officer()->create(['barangay_id' => $ppa->barangay_id]);

        $this->actingAs($officer)
            ->post(route('officer.ppas.milestones.store', $ppa), ['title' => 'Venue booked', 'due_on' => now()->addWeek()->toDateString()])
            ->assertRedirect(route('officer.ppas.show', $ppa));

        $this->assertSame('Venue booked', $ppa->milestones()->sole()->title);
    }

    public function test_completing_a_milestone_needs_an_approved_abyip(): void
    {
        $milestone = Milestone::factory()->create();
        $officer = User::factory()->officer()->create(['barangay_id' => $milestone->ppa->barangay_id]);

        $this->actingAs($officer)
            ->patch(route('officer.ppas.milestones.update', [$milestone->ppa, $milestone]), ['completed' => '1'])
            ->assertForbidden();

        $this->assertNull($milestone->fresh()->completed_at);
    }

    public function test_officer_completes_and_reopens_a_milestone_after_approval(): void
    {
        $ppa = Ppa::factory()->create(['abyip_id' => Abyip::factory()->approved()]);
        $milestone = Milestone::factory()->for($ppa)->create();
        $officer = User::factory()->officer()->create(['barangay_id' => $ppa->barangay_id]);

        $this->actingAs($officer)->patch(route('officer.ppas.milestones.update', [$ppa, $milestone]), ['completed' => '1']);
        $this->assertNotNull($milestone->fresh()->completed_at);
        $this->assertSame(100, $ppa->fresh()->progress());

        $this->actingAs($officer)->patch(route('officer.ppas.milestones.update', [$ppa, $milestone]), ['completed' => '0']);
        $this->assertNull($milestone->fresh()->completed_at);
        $this->assertDatabaseHas('audit_logs', ['action' => 'milestone.reopened', 'subject_id' => $ppa->id]);
    }

    public function test_milestone_of_another_ppa_is_not_found(): void
    {
        $ppa = Ppa::factory()->create(['abyip_id' => Abyip::factory()->approved()]);
        $otherMilestone = Milestone::factory()->create();
        $officer = User::factory()->officer()->create(['barangay_id' => $ppa->barangay_id]);

        $this->actingAs($officer)
            ->patch(route('officer.ppas.milestones.update', [$ppa, $otherMilestone]), ['completed' => '1'])
            ->assertNotFound();
    }
}
