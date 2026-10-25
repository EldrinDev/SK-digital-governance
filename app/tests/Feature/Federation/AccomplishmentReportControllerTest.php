<?php

namespace Tests\Feature\Federation;

use App\Models\AccomplishmentReport;
use App\Models\User;
use App\ReportStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccomplishmentReportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_federation_lists_and_reads_submitted_reports(): void
    {
        $report = AccomplishmentReport::factory()->create();
        $federation = User::factory()->federation()->create();

        $this->actingAs($federation)
            ->get(route('federation.reports.index'))
            ->assertOk()
            ->assertSeeText($report->barangay->name)
            ->assertSeeText('Waiting for review');

        $this->actingAs($federation)->get(route('federation.reports.show', $report))->assertOk();
        $this->actingAs($federation)->get(route('reports.print', $report))->assertOk();
    }

    public function test_marking_reviewed_sends_the_note_as_a_remark(): void
    {
        $report = AccomplishmentReport::factory()->create();
        $federation = User::factory()->federation()->create();

        $this->actingAs($federation)
            ->patch(route('federation.reports.update', $report), ['review_note' => 'Good work. Please add photos for the summit.'])
            ->assertRedirect(route('federation.reports.index'));

        $report->refresh();
        $this->assertSame(ReportStatus::Reviewed, $report->status);
        $this->assertSame($federation->id, $report->reviewed_by);
        $this->assertDatabaseHas('monitoring_remarks', ['barangay_id' => $report->barangay_id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'report.reviewed']);
    }

    public function test_officers_cannot_review_reports(): void
    {
        $report = AccomplishmentReport::factory()->create();
        $officer = User::factory()->officer()->create(['barangay_id' => $report->barangay_id]);

        $this->actingAs($officer)->patch(route('federation.reports.update', $report))->assertForbidden();
    }
}
