<?php

namespace Tests\Feature\Officer;

use App\Models\Abyip;
use App\Models\Ppa;
use App\Models\User;
use App\PpaStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PpaStatusControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_officer_updates_status_after_approval_and_it_is_recorded(): void
    {
        $ppa = Ppa::factory()->create(['abyip_id' => Abyip::factory()->approved()]);
        $officer = User::factory()->officer()->create(['barangay_id' => $ppa->barangay_id]);

        $this->actingAs($officer)
            ->patch(route('officer.ppas.status.update', $ppa), ['status' => 'ongoing'])
            ->assertRedirect(route('officer.ppas.show', $ppa));

        $this->assertSame(PpaStatus::Ongoing, $ppa->fresh()->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'ppa.status_changed', 'subject_id' => $ppa->id]);
    }

    public function test_status_cannot_change_before_approval(): void
    {
        $ppa = Ppa::factory()->create();
        $officer = User::factory()->officer()->create(['barangay_id' => $ppa->barangay_id]);

        $this->actingAs($officer)
            ->patch(route('officer.ppas.status.update', $ppa), ['status' => 'completed'])
            ->assertForbidden();

        $this->assertSame(PpaStatus::Planned, $ppa->fresh()->status);
    }

    public function test_unknown_status_is_rejected(): void
    {
        $ppa = Ppa::factory()->create(['abyip_id' => Abyip::factory()->approved()]);
        $officer = User::factory()->officer()->create(['barangay_id' => $ppa->barangay_id]);

        $this->actingAs($officer)
            ->patch(route('officer.ppas.status.update', $ppa), ['status' => 'delayed'])
            ->assertSessionHasErrors('status');
    }
}
