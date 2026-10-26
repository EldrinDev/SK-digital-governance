<?php

namespace Tests\Feature\Officer;

use App\Models\Abyip;
use App\Models\Meeting;
use App\Models\Ppa;
use App\Models\User;
use App\OfficerPosition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OfficerPositionTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_the_chairperson_publishes_an_abyip(): void
    {
        $abyip = Abyip::factory()->approved()->create();
        $kagawad = User::factory()->officer()->position(OfficerPosition::Kagawad)->create(['barangay_id' => $abyip->barangay_id]);
        $chair = User::factory()->officer()->create(['barangay_id' => $abyip->barangay_id]);

        $this->actingAs($kagawad)->post(route('officer.abyips.publication.store', $abyip))->assertForbidden();
        $this->actingAs($kagawad)->get(route('officer.abyips.show', $abyip))->assertSeeText('Only the SK Chairperson approves items for public disclosure.');

        $this->actingAs($chair)->post(route('officer.abyips.publication.store', $abyip))->assertRedirect();
        $this->assertTrue($abyip->fresh()->isPublished());
    }

    public function test_any_officer_can_still_submit_the_accomplishment_report(): void
    {
        $abyip = Abyip::factory()->approved()->create();
        $kagawad = User::factory()->officer()->position(OfficerPosition::Kagawad)->create(['barangay_id' => $abyip->barangay_id]);

        $this->actingAs($kagawad)->get(route('officer.abyips.report', $abyip))->assertOk()->assertSee(route('officer.abyips.report.store', $abyip));
    }

    public function test_only_the_secretary_or_chairperson_uploads_minutes(): void
    {
        Storage::fake();
        $meeting = Meeting::factory()->create();
        $treasurer = User::factory()->officer()->position(OfficerPosition::Treasurer)->create(['barangay_id' => $meeting->barangay_id]);
        $secretary = User::factory()->officer()->position(OfficerPosition::Secretary)->create(['barangay_id' => $meeting->barangay_id]);
        $file = fn () => UploadedFile::fake()->create('minutes.pdf', 100, 'application/pdf');

        $this->actingAs($treasurer)->post(route('officer.meetings.minutes.store', $meeting), ['minutes' => $file()])->assertForbidden();
        $this->actingAs($secretary)->post(route('officer.meetings.minutes.store', $meeting), ['minutes' => $file()])->assertRedirect();

        $this->assertNotNull($meeting->fresh()->minutes_path);
    }

    public function test_treasurer_records_spending_and_over_budget_is_flagged(): void
    {
        $ppa = Ppa::factory()->for(Abyip::factory()->approved())->create(['budget' => 10000]);
        $treasurer = User::factory()->officer()->position(OfficerPosition::Treasurer)->create(['barangay_id' => $ppa->barangay_id]);

        $this->actingAs($treasurer)
            ->patch(route('officer.ppas.spending.update', $ppa), ['amount_spent' => '12500.50'])
            ->assertSessionHas('status', fn (string $status): bool => str_contains($status, 'above the PPA’s budget'));

        $ppa->refresh();
        $this->assertSame('12500.50', $ppa->amount_spent);
        $this->assertTrue($ppa->isOverBudget());
        $this->assertSame(125, $ppa->budgetUsedPercent());
        $this->assertDatabaseHas('audit_logs', ['action' => 'ppa.spending_recorded', 'subject_id' => $ppa->id]);
    }

    public function test_kagawad_cannot_record_spending(): void
    {
        $ppa = Ppa::factory()->for(Abyip::factory()->approved())->create();
        $kagawad = User::factory()->officer()->position(OfficerPosition::Kagawad)->create(['barangay_id' => $ppa->barangay_id]);

        $this->actingAs($kagawad)->patch(route('officer.ppas.spending.update', $ppa), ['amount_spent' => '100'])->assertForbidden();
    }

    public function test_spending_waits_for_the_approved_abyip(): void
    {
        $ppa = Ppa::factory()->create();
        $chair = User::factory()->officer()->create(['barangay_id' => $ppa->barangay_id]);

        $this->actingAs($chair)->patch(route('officer.ppas.spending.update', $ppa), ['amount_spent' => '100'])->assertForbidden();
    }
}
