<?php

namespace Tests\Feature\Officer;

use App\Models\Abyip;
use App\Models\Cbydp;
use App\Models\Ppa;
use App\Models\User;
use App\PlanStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AbyipControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    private Cbydp $cbydp;

    protected function setUp(): void
    {
        parent::setUp();

        $this->officer = User::factory()->officer()->create();
        $this->cbydp = Cbydp::factory()->approved()->for($this->officer->barangay)->create(['start_year' => 2026, 'end_year' => 2028]);
    }

    public function test_officer_starts_an_abyip_under_an_approved_cbydp(): void
    {
        $this->actingAs($this->officer)
            ->post(route('officer.abyips.store'), ['cbydp_id' => $this->cbydp->id, 'year' => 2027])
            ->assertRedirect();

        $abyip = Abyip::sole();
        $this->assertSame(2027, $abyip->year);
        $this->assertSame($this->officer->barangay_id, $abyip->barangay_id);
        $this->assertDatabaseHas('audit_logs', ['action' => 'abyip.created', 'subject_id' => $abyip->id]);
    }

    public function test_form_offers_only_the_cbydp_years_and_marks_taken_ones(): void
    {
        Abyip::factory()->for($this->officer->barangay)->create(['cbydp_id' => $this->cbydp->id, 'year' => 2026]);

        $this->actingAs($this->officer)
            ->get(route('officer.abyips.create'))
            ->assertOk()
            ->assertDontSee('type="number"', false)
            ->assertSeeInOrder(['<select', 'name="year"'], false)
            ->assertSee('data-listbox', false)
            ->assertSeeText('2026, already has an ABYIP')
            ->assertSee('data-hint="Year 1 of 3"  data-taken data-note="Has an ABYIP"', false)
            ->assertSee('<option value="2027" data-for="'.$this->cbydp->id.'" data-label="2027" data-hint="Year 2 of 3"  selected', false)
            ->assertSee('value="2028"', false)
            ->assertDontSee('value="2025"', false)
            ->assertDontSee('value="2029"', false);
    }

    public function test_draft_cbydp_cannot_be_used(): void
    {
        $draft = Cbydp::factory()->for($this->officer->barangay)->create(['start_year' => 2029, 'end_year' => 2031]);

        $this->actingAs($this->officer)
            ->post(route('officer.abyips.store'), ['cbydp_id' => $draft->id, 'year' => 2029])
            ->assertSessionHasErrors(['cbydp_id' => 'Choose one of your barangay’s approved CBYDPs.']);
    }

    public function test_cbydp_of_another_barangay_cannot_be_used(): void
    {
        $other = Cbydp::factory()->approved()->create();

        $this->actingAs($this->officer)
            ->post(route('officer.abyips.store'), ['cbydp_id' => $other->id, 'year' => $other->start_year])
            ->assertSessionHasErrors('cbydp_id');
    }

    public function test_year_outside_the_cbydp_period_is_rejected(): void
    {
        $this->actingAs($this->officer)
            ->post(route('officer.abyips.store'), ['cbydp_id' => $this->cbydp->id, 'year' => 2030])
            ->assertSessionHasErrors(['year' => 'Choose a year within the CBYDP’s period, 2026–2028.']);
    }

    public function test_second_abyip_for_the_same_year_is_rejected(): void
    {
        Abyip::factory()->for($this->cbydp)->create(['barangay_id' => $this->officer->barangay_id, 'year' => 2026]);

        $this->actingAs($this->officer)
            ->post(route('officer.abyips.store'), ['cbydp_id' => $this->cbydp->id, 'year' => 2026])
            ->assertSessionHasErrors(['year' => 'Your barangay already has an ABYIP for 2026.']);
    }

    public function test_show_lists_ppas_and_hides_other_barangays(): void
    {
        $ppa = Ppa::factory()->create(['abyip_id' => Abyip::factory()->for($this->cbydp)->create(['barangay_id' => $this->officer->barangay_id])->id]);

        $this->actingAs($this->officer)
            ->get(route('officer.abyips.show', $ppa->abyip))
            ->assertOk()
            ->assertSeeText($ppa->title)
            ->assertSee('aria-label="Plan life cycle"', false);

        $this->actingAs($this->officer)->get(route('officer.abyips.show', Abyip::factory()->create()))->assertNotFound();
    }

    public function test_recording_approval_locks_the_abyip(): void
    {
        Storage::fake();
        $abyip = Abyip::factory()->for($this->cbydp)->create(['barangay_id' => $this->officer->barangay_id]);
        Ppa::factory()->create(['abyip_id' => $abyip->id]);

        $this->actingAs($this->officer)->post(route('officer.abyips.approval.store', $abyip), [
            'resolution_number' => 'Res. No. 9',
            'approved_on' => now()->toDateString(),
            'resolution_file' => UploadedFile::fake()->create('resolution.pdf', 100, 'application/pdf'),
        ])->assertRedirect(route('officer.abyips.show', $abyip));

        $this->assertSame(PlanStatus::Approved, $abyip->fresh()->status);
        $this->actingAs($this->officer)->get(route('officer.ppas.create', $abyip))->assertForbidden();
    }

    public function test_approval_needs_at_least_one_ppa(): void
    {
        Storage::fake();
        $abyip = Abyip::factory()->for($this->cbydp)->create(['barangay_id' => $this->officer->barangay_id]);

        $this->actingAs($this->officer)->post(route('officer.abyips.approval.store', $abyip), [
            'resolution_number' => 'Res. No. 9',
            'approved_on' => now()->toDateString(),
            'resolution_file' => UploadedFile::fake()->create('resolution.pdf', 100, 'application/pdf'),
        ])->assertSessionHasErrors(['plan' => 'Add at least one PPA before recording the approval.']);
    }
}
