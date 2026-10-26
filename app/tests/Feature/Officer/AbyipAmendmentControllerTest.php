<?php

namespace Tests\Feature\Officer;

use App\Models\Abyip;
use App\Models\Evidence;
use App\Models\Ppa;
use App\Models\User;
use App\PlanStatus;
use App\PpaStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AbyipAmendmentControllerTest extends TestCase
{
    use RefreshDatabase;

    private Abyip $abyip;

    private Ppa $ppa;

    private User $officer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->abyip = Abyip::factory()->approved()->create(['published_at' => now()]);
        $this->ppa = Ppa::factory()->create(['abyip_id' => $this->abyip->id, 'budget' => 10000]);
        $this->officer = User::factory()->officer()->create(['barangay_id' => $this->abyip->barangay_id]);
    }

    public function test_amendment_unlocks_ppas_and_approval_locks_them_again(): void
    {
        $this->actingAs($this->officer)->get(route('officer.ppas.edit', $this->ppa))->assertForbidden();

        $this->actingAs($this->officer)
            ->post(route('officer.abyips.amendments.store', $this->abyip), ['reason' => 'Move budget to the clean-up'])
            ->assertRedirect(route('officer.abyips.show', $this->abyip));
        $this->assertTrue($this->abyip->fresh()->isBeingAmended());
        $this->assertDatabaseHas('audit_logs', ['action' => 'abyip.amendment_opened']);

        $this->actingAs($this->officer)->get(route('officer.ppas.edit', $this->ppa))->assertOk();

        Storage::fake();
        $this->actingAs($this->officer)->post(route('officer.abyips.amendments.approve', $this->abyip), [
            'resolution_number' => 'Res. No. 15',
            'approved_on' => now()->toDateString(),
            'resolution_file' => UploadedFile::fake()->create('amendment.pdf', 50, 'application/pdf'),
        ])->assertRedirect(route('officer.abyips.show', $this->abyip));

        $abyip = $this->abyip->fresh();
        $this->assertFalse($abyip->isBeingAmended());
        $this->assertSame(PlanStatus::Approved, $abyip->amendments()->sole()->status);
        $this->actingAs($this->officer)->get(route('officer.ppas.edit', $this->ppa))->assertForbidden();
    }

    public function test_only_one_amendment_open_at_a_time(): void
    {
        $this->actingAs($this->officer)->post(route('officer.abyips.amendments.store', $this->abyip), ['reason' => 'First']);

        $this->actingAs($this->officer)
            ->post(route('officer.abyips.amendments.store', $this->abyip), ['reason' => 'Second'])
            ->assertForbidden();
    }

    public function test_draft_abyip_cannot_be_amended(): void
    {
        $draft = Abyip::factory()->create();
        $officer = User::factory()->officer()->create(['barangay_id' => $draft->barangay_id]);

        $this->actingAs($officer)->post(route('officer.abyips.amendments.store', $draft), ['reason' => 'x'])->assertForbidden();
    }

    public function test_ppa_with_progress_cannot_be_deleted_during_amendment(): void
    {
        $this->ppa->forceFill(['status' => PpaStatus::Ongoing])->save();
        $fresh = Ppa::factory()->create(['abyip_id' => $this->abyip->id]);
        $this->actingAs($this->officer)->post(route('officer.abyips.amendments.store', $this->abyip), ['reason' => 'Drop a PPA']);

        $this->actingAs($this->officer)->delete(route('officer.ppas.destroy', $this->ppa))->assertForbidden();
        $this->actingAs($this->officer)->delete(route('officer.ppas.destroy', $fresh))->assertRedirect();
        $this->assertModelMissing($fresh);
    }

    public function test_abyip_leaves_the_public_board_while_being_amended(): void
    {
        Storage::fake();
        $evidence = Evidence::factory()->public()->for($this->ppa)->create();
        Storage::put($evidence->path, 'img');

        $this->get(route('transparency.show', $this->abyip->barangay))->assertSeeText($this->ppa->title);

        $this->actingAs($this->officer)->post(route('officer.abyips.amendments.store', $this->abyip), ['reason' => 'Fix budget']);
        auth()->logout();

        $this->get(route('transparency.show', $this->abyip->barangay))->assertDontSeeText($this->ppa->title);
        $this->get(route('transparency.evidence', $evidence))->assertNotFound();
    }

    public function test_approving_the_abyip_again_is_refused(): void
    {
        Storage::fake();

        $this->actingAs($this->officer)->post(route('officer.abyips.approval.store', $this->abyip), [
            'resolution_number' => 'Res. No. 99',
            'approved_on' => now()->toDateString(),
            'resolution_file' => UploadedFile::fake()->create('r.pdf', 10, 'application/pdf'),
        ])->assertForbidden();
    }
}
