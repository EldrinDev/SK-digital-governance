<?php

namespace Tests\Feature\Officer;

use App\Models\Abyip;
use App\Models\Cbydp;
use App\Models\CbydpPriority;
use App\Models\Ppa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PpaControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    private Abyip $abyip;

    private CbydpPriority $priority;

    protected function setUp(): void
    {
        parent::setUp();

        $this->officer = User::factory()->officer()->create();
        $cbydp = Cbydp::factory()->approved()->for($this->officer->barangay)->create(['start_year' => 2026, 'end_year' => 2028]);
        $this->priority = CbydpPriority::factory()->for($cbydp)->create();
        $this->abyip = Abyip::factory()->for($cbydp)->create(['barangay_id' => $this->officer->barangay_id, 'year' => 2026]);
    }

    public function test_officer_adds_a_ppa_to_a_draft_abyip(): void
    {
        $this->actingAs($this->officer)
            ->post(route('officer.ppas.store', $this->abyip), $this->payload())
            ->assertRedirect(route('officer.abyips.show', $this->abyip));

        $ppa = Ppa::sole();
        $this->assertSame('Youth Leadership Summit', $ppa->title);
        $this->assertSame('45000.50', $ppa->budget);
        $this->assertSame($this->officer->barangay_id, $ppa->barangay_id);
        $this->assertDatabaseHas('audit_logs', ['action' => 'ppa.created', 'subject_id' => $ppa->id]);
    }

    public function test_priority_from_another_cbydp_is_rejected(): void
    {
        $this->actingAs($this->officer)
            ->post(route('officer.ppas.store', $this->abyip), $this->payload(['cbydp_priority_id' => CbydpPriority::factory()->create()->id]))
            ->assertSessionHasErrors(['cbydp_priority_id' => 'Choose a priority from this ABYIP’s CBYDP.']);
    }

    public function test_dates_outside_the_abyip_year_are_rejected(): void
    {
        $this->actingAs($this->officer)
            ->post(route('officer.ppas.store', $this->abyip), $this->payload(['starts_on' => '2025-12-01', 'ends_on' => '2027-01-10']))
            ->assertSessionHasErrors([
                'starts_on' => 'The PPA must start in 2026.',
                'ends_on' => 'The PPA must end by December 31, 2026.',
            ]);
    }

    public function test_end_before_start_and_bad_budget_are_rejected(): void
    {
        $this->actingAs($this->officer)
            ->post(route('officer.ppas.store', $this->abyip), $this->payload(['starts_on' => '2026-06-10', 'ends_on' => '2026-06-01', 'budget' => '10.555']))
            ->assertSessionHasErrors([
                'ends_on' => 'The end date can’t be before the start date.',
                'budget' => 'Use at most two decimal places for centavos.',
            ]);
    }

    public function test_officer_edits_and_removes_a_ppa_while_draft(): void
    {
        $ppa = Ppa::factory()->create(['abyip_id' => $this->abyip->id, 'cbydp_priority_id' => $this->priority->id]);

        $this->actingAs($this->officer)
            ->put(route('officer.ppas.update', $ppa), $this->payload(['title' => 'Renamed summit']))
            ->assertRedirect(route('officer.ppas.show', $ppa));
        $this->assertSame('Renamed summit', $ppa->fresh()->title);

        $this->actingAs($this->officer)
            ->delete(route('officer.ppas.destroy', $ppa))
            ->assertRedirect(route('officer.abyips.show', $this->abyip));
        $this->assertModelMissing($ppa);
        $this->assertDatabaseHas('audit_logs', ['action' => 'ppa.deleted']);
    }

    public function test_planned_details_are_locked_after_approval(): void
    {
        $this->abyip->forceFill(['status' => 'approved'])->save();
        $ppa = Ppa::factory()->create(['abyip_id' => $this->abyip->id, 'cbydp_priority_id' => $this->priority->id]);

        $this->actingAs($this->officer)->get(route('officer.ppas.edit', $ppa))->assertForbidden();
        $this->actingAs($this->officer)->put(route('officer.ppas.update', $ppa), $this->payload(['budget' => 999999]))->assertForbidden();
        $this->actingAs($this->officer)->delete(route('officer.ppas.destroy', $ppa))->assertForbidden();
        $this->actingAs($this->officer)->post(route('officer.ppas.store', $this->abyip), $this->payload())->assertForbidden();
    }

    public function test_ppa_of_another_barangay_is_not_found(): void
    {
        $other = Ppa::factory()->create();

        $this->actingAs($this->officer)->get(route('officer.ppas.show', $other))->assertNotFound();
        $this->actingAs($this->officer)->put(route('officer.ppas.update', $other), $this->payload())->assertNotFound();
    }

    public function test_index_filters_delayed_ppas(): void
    {
        $delayed = Ppa::factory()->delayed()->create(['abyip_id' => $this->abyip->id, 'title' => 'Late clean-up drive']);
        Ppa::factory()->create(['abyip_id' => $this->abyip->id, 'title' => 'On-time sports fest']);
        Ppa::factory()->delayed()->create(['title' => 'Other barangay delay']);

        $this->actingAs($this->officer)
            ->get(route('officer.ppas.index', ['status' => 'delayed']))
            ->assertOk()
            ->assertSeeText($delayed->title)
            ->assertDontSeeText('On-time sports fest')
            ->assertDontSeeText('Other barangay delay');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return [
            'cbydp_priority_id' => $this->priority->id,
            'title' => 'Youth Leadership Summit',
            'target' => '150 youth trained',
            'budget' => '45000.50',
            'starts_on' => '2026-03-01',
            'ends_on' => '2026-03-31',
            ...$overrides,
        ];
    }
}
