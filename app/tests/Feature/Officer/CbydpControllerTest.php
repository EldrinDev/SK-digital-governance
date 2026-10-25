<?php

namespace Tests\Feature\Officer;

use App\Models\Cbydp;
use App\Models\CbydpPriority;
use App\Models\User;
use App\PlanStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CbydpControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->officer = User::factory()->officer()->create();
    }

    public function test_officer_starts_a_three_year_cbydp_in_own_barangay(): void
    {
        $this->actingAs($this->officer)
            ->post(route('officer.cbydps.store'), ['start_year' => 2026])
            ->assertRedirect();

        $cbydp = Cbydp::sole();
        $this->assertSame($this->officer->barangay_id, $cbydp->barangay_id);
        $this->assertSame(2028, $cbydp->end_year);
        $this->assertSame(PlanStatus::Draft, $cbydp->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'cbydp.created', 'subject_id' => $cbydp->id]);
    }

    public function test_overlapping_cbydp_is_rejected(): void
    {
        Cbydp::factory()->for($this->officer->barangay)->create(['start_year' => 2026, 'end_year' => 2028]);

        $this->actingAs($this->officer)
            ->post(route('officer.cbydps.store'), ['start_year' => 2028])
            ->assertSessionHasErrors(['start_year' => 'Your barangay already has a CBYDP covering some of these years.']);
    }

    public function test_officer_adds_and_removes_priorities_on_a_draft(): void
    {
        $cbydp = Cbydp::factory()->for($this->officer->barangay)->create();

        $this->actingAs($this->officer)->post(route('officer.cbydps.priorities.store', $cbydp), [
            'center' => 'education',
            'title' => 'Reduce out-of-school youth',
        ])->assertRedirect(route('officer.cbydps.show', $cbydp));

        $priority = $cbydp->priorities()->sole();
        $this->assertSame('Reduce out-of-school youth', $priority->title);

        $this->actingAs($this->officer)
            ->delete(route('officer.cbydps.priorities.destroy', [$cbydp, $priority]))
            ->assertRedirect(route('officer.cbydps.show', $cbydp));

        $this->assertModelMissing($priority);
    }

    public function test_priority_from_another_cbydp_cannot_be_removed_through_this_one(): void
    {
        $cbydp = Cbydp::factory()->for($this->officer->barangay)->create();
        $otherPriority = CbydpPriority::factory()->create();

        $this->actingAs($this->officer)
            ->delete(route('officer.cbydps.priorities.destroy', [$cbydp, $otherPriority]))
            ->assertNotFound();

        $this->assertModelExists($otherPriority);
    }

    public function test_officer_of_another_barangay_gets_not_found(): void
    {
        $cbydp = Cbydp::factory()->create();

        $this->actingAs($this->officer)->get(route('officer.cbydps.show', $cbydp))->assertNotFound();
        $this->actingAs($this->officer)
            ->post(route('officer.cbydps.priorities.store', $cbydp), ['center' => 'health', 'title' => 'X'])
            ->assertNotFound();
    }

    public function test_approval_needs_at_least_one_priority(): void
    {
        Storage::fake();
        $cbydp = Cbydp::factory()->for($this->officer->barangay)->create();

        $this->actingAs($this->officer)
            ->post(route('officer.cbydps.approval.store', $cbydp), $this->approvalPayload())
            ->assertSessionHasErrors(['plan' => 'Add at least one priority before recording the approval.']);

        $this->assertSame(PlanStatus::Draft, $cbydp->fresh()->status);
    }

    public function test_recording_approval_stores_resolution_and_locks_the_plan(): void
    {
        Storage::fake();
        $cbydp = Cbydp::factory()->for($this->officer->barangay)->has(CbydpPriority::factory(), 'priorities')->create(['start_year' => 2026, 'end_year' => 2028]);

        $this->actingAs($this->officer)
            ->post(route('officer.cbydps.approval.store', $cbydp), $this->approvalPayload())
            ->assertRedirect(route('officer.cbydps.show', $cbydp));

        $cbydp->refresh();
        $this->assertSame(PlanStatus::Approved, $cbydp->status);
        $this->assertSame('Res. No. 4, s. 2026', $cbydp->resolution_number);
        $this->assertSame($this->officer->id, $cbydp->approval_recorded_by);
        Storage::assertExists($cbydp->resolution_path);
        $this->assertDatabaseHas('audit_logs', ['action' => 'cbydp.approved', 'subject_id' => $cbydp->id]);

        $this->actingAs($this->officer)
            ->post(route('officer.cbydps.priorities.store', $cbydp), ['center' => 'health', 'title' => 'Late addition'])
            ->assertForbidden();

        $this->actingAs($this->officer)
            ->get(route('officer.cbydps.resolution', $cbydp))
            ->assertOk()
            ->assertDownload('CBYDP 2026-2028 resolution.pdf');
    }

    public function test_approval_rejects_non_pdf_and_future_date(): void
    {
        Storage::fake();
        $cbydp = Cbydp::factory()->for($this->officer->barangay)->has(CbydpPriority::factory(), 'priorities')->create();

        $this->actingAs($this->officer)->post(route('officer.cbydps.approval.store', $cbydp), [
            'resolution_number' => 'Res. No. 4',
            'approved_on' => now()->addDay()->toDateString(),
            'resolution_file' => UploadedFile::fake()->image('photo.jpg'),
        ])->assertSessionHasErrors([
            'approved_on' => 'The approval date can’t be in the future.',
            'resolution_file' => 'Attach the resolution as a PDF.',
        ]);
    }

    public function test_federation_user_can_read_but_officer_routes_are_for_officers(): void
    {
        $cbydp = Cbydp::factory()->create();

        $this->actingAs(User::factory()->federation()->create())
            ->get(route('officer.cbydps.show', $cbydp))
            ->assertForbidden();
    }

    /**
     * @return array<string, mixed>
     */
    private function approvalPayload(): array
    {
        return [
            'resolution_number' => 'Res. No. 4, s. 2026',
            'approved_on' => now()->subDay()->toDateString(),
            'resolution_file' => UploadedFile::fake()->create('resolution.pdf', 200, 'application/pdf'),
        ];
    }
}
