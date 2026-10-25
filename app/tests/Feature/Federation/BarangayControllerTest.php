<?php

namespace Tests\Feature\Federation;

use App\Models\Abyip;
use App\Models\Barangay;
use App\Models\Ppa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarangayControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_federation_user_reads_a_barangays_ppas(): void
    {
        $ppa = Ppa::factory()->create(['title' => 'Coastal clean-up drive']);

        $this->actingAs(User::factory()->federation()->create())
            ->get(route('federation.barangays.show', $ppa->barangay))
            ->assertOk()
            ->assertSeeText('Coastal clean-up drive')
            ->assertSeeText('ABYIP '.$ppa->abyip->year);
    }

    public function test_non_participating_barangay_is_not_found(): void
    {
        $barangay = Barangay::factory()->create(['is_participating' => false]);

        $this->actingAs(User::factory()->federation()->create())
            ->get(route('federation.barangays.show', $barangay))
            ->assertNotFound();
    }

    public function test_remark_reaches_the_barangays_officers_without_changing_records(): void
    {
        $abyip = Abyip::factory()->approved()->create();
        $federation = User::factory()->federation()->create(['name' => 'Faye Federation']);
        $officer = User::factory()->officer()->create(['barangay_id' => $abyip->barangay_id]);

        $this->actingAs($federation)
            ->post(route('federation.barangays.remarks.store', $abyip->barangay), ['body' => 'Please upload evidence for completed PPAs.'])
            ->assertRedirect(route('federation.barangays.show', $abyip->barangay));

        $this->assertDatabaseHas('monitoring_remarks', ['barangay_id' => $abyip->barangay_id, 'user_id' => $federation->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'remark.created', 'barangay_id' => $abyip->barangay_id]);

        $this->actingAs($officer)
            ->get(route('officer.dashboard'))
            ->assertSeeText('Remarks from the SK Federation')
            ->assertSeeText('Please upload evidence for completed PPAs.');
    }

    public function test_empty_remark_is_rejected(): void
    {
        $barangay = Barangay::factory()->create();

        $this->actingAs(User::factory()->federation()->create())
            ->post(route('federation.barangays.remarks.store', $barangay), ['body' => ''])
            ->assertSessionHasErrors(['body' => 'Write the remark before saving.']);
    }

    public function test_officer_cannot_leave_remarks(): void
    {
        $officer = User::factory()->officer()->create();

        $this->actingAs($officer)
            ->post(route('federation.barangays.remarks.store', $officer->barangay), ['body' => 'Self-praise'])
            ->assertForbidden();
    }
}
