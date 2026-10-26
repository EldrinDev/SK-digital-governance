<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\EventRegistration;
use App\Models\Ppa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_officer_exports_own_barangays_ppas_only(): void
    {
        $ppa = Ppa::factory()->create(['title' => 'Coastal clean-up']);
        Ppa::factory()->create(['title' => 'Elsewhere project']);
        $officer = User::factory()->officer()->create(['barangay_id' => $ppa->barangay_id]);

        $csv = $this->actingAs($officer)->get(route('officer.ppas.export'))->assertOk()->streamedContent();

        $this->assertStringContainsString('Coastal clean-up', $csv);
        $this->assertStringNotContainsString('Elsewhere project', $csv);
    }

    public function test_formula_like_cells_are_neutralised(): void
    {
        $ppa = Ppa::factory()->create(['title' => '=HYPERLINK("http://evil.test")']);
        $officer = User::factory()->officer()->create(['barangay_id' => $ppa->barangay_id]);

        $csv = $this->actingAs($officer)->get(route('officer.ppas.export'))->streamedContent();

        $this->assertStringContainsString("'=HYPERLINK", $csv);
    }

    public function test_event_registrants_export_is_limited_to_own_barangay(): void
    {
        $registration = EventRegistration::factory()->create();
        $officer = User::factory()->officer()->create(['barangay_id' => $registration->event->barangay_id]);

        $this->actingAs($officer)
            ->get(route('officer.events.export', $registration->event))
            ->assertOk()
            ->assertDownload();

        $this->actingAs(User::factory()->officer()->create())
            ->get(route('officer.events.export', $registration->event))
            ->assertNotFound();
    }

    public function test_admin_exports_filtered_audit_trail_and_users(): void
    {
        AuditLog::factory()->create(['action' => 'auth.failed', 'properties' => ['email' => 'intruder@example.com']]);
        AuditLog::factory()->create(['action' => 'auth.login']);
        $admin = User::factory()->administrator()->create();

        $csv = $this->actingAs($admin)->get(route('admin.audit-logs.export', ['action' => 'auth.failed']))->assertOk()->streamedContent();
        $this->assertStringContainsString('Failed login', $csv);
        $this->assertStringNotContainsString('Logged in', $csv);

        $this->actingAs($admin)->get(route('admin.users.export'))->assertOk()->assertDownload();
    }

    public function test_federation_exports_compliance_and_officers_cannot(): void
    {
        $this->actingAs(User::factory()->federation()->create())->get(route('federation.compliance.export'))->assertOk()->assertDownload();
        $this->actingAs(User::factory()->officer()->create())->get(route('federation.compliance.export'))->assertForbidden();
    }
}
