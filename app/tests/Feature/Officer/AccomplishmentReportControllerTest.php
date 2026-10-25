<?php

namespace Tests\Feature\Officer;

use App\Models\Abyip;
use App\Models\AccomplishmentReport;
use App\Models\Evidence;
use App\Models\Ppa;
use App\Models\User;
use App\PpaStatus;
use App\ReportStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccomplishmentReportControllerTest extends TestCase
{
    use RefreshDatabase;

    private Abyip $abyip;

    private User $officer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->abyip = Abyip::factory()->approved()->create();
        $this->officer = User::factory()->officer()->create(['barangay_id' => $this->abyip->barangay_id]);
    }

    public function test_draft_is_compiled_from_records_and_flags_missing_evidence(): void
    {
        $withProof = Ppa::factory()->create(['abyip_id' => $this->abyip->id, 'status' => PpaStatus::Completed, 'budget' => 10000]);
        Evidence::factory()->for($withProof)->create();
        Ppa::factory()->create(['abyip_id' => $this->abyip->id, 'status' => PpaStatus::Completed, 'budget' => 5000, 'title' => 'No proof yet']);
        Ppa::factory()->create(['abyip_id' => $this->abyip->id, 'status' => PpaStatus::Cancelled, 'budget' => 99000]);

        $report = AccomplishmentReport::compile($this->abyip);

        $this->assertSame(3, $report['totals']['ppas']);
        $this->assertSame(2, $report['totals']['completed']);
        $this->assertSame(15000.0, $report['totals']['budget']);
        $this->assertSame(1, $report['missing_evidence']);

        $this->actingAs($this->officer)
            ->get(route('officer.abyips.report', $this->abyip))
            ->assertOk()
            ->assertSeeText('1 completed PPA has no evidence.')
            ->assertSeeText('No proof yet');
    }

    public function test_submission_is_blocked_while_evidence_is_missing(): void
    {
        Ppa::factory()->create(['abyip_id' => $this->abyip->id, 'status' => PpaStatus::Completed]);

        $this->actingAs($this->officer)
            ->post(route('officer.abyips.report.store', $this->abyip))
            ->assertSessionHasErrors(['report' => '1 completed PPA has no evidence. Upload proof before submitting.']);

        $this->assertDatabaseCount('accomplishment_reports', 0);
    }

    public function test_submission_freezes_a_snapshot(): void
    {
        $ppa = Ppa::factory()->create(['abyip_id' => $this->abyip->id, 'title' => 'Original title']);

        $this->actingAs($this->officer)
            ->post(route('officer.abyips.report.store', $this->abyip))
            ->assertRedirect(route('officer.abyips.report', $this->abyip));

        $report = AccomplishmentReport::sole();
        $this->assertSame(ReportStatus::Submitted, $report->status);
        $this->assertSame($this->officer->id, $report->submitted_by);

        $ppa->forceFill(['title' => 'Changed later'])->save();

        $this->assertSame('Original title', $report->fresh()->snapshot['ppas'][0]['title']);
        $this->actingAs($this->officer)->get(route('reports.print', $report))->assertOk()->assertSeeText('Original title');
    }

    public function test_draft_abyip_cannot_be_reported(): void
    {
        $draft = Abyip::factory()->create();
        $officer = User::factory()->officer()->create(['barangay_id' => $draft->barangay_id]);

        $this->actingAs($officer)->post(route('officer.abyips.report.store', $draft))->assertForbidden();
    }

    public function test_other_barangays_report_is_not_found(): void
    {
        $other = AccomplishmentReport::factory()->create();

        $this->actingAs($this->officer)->get(route('reports.print', $other))->assertNotFound();
        $this->actingAs($this->officer)->get(route('officer.abyips.report', $other->abyip))->assertNotFound();
    }
}
