<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_entries_with_plain_language_labels(): void
    {
        $officer = User::factory()->officer()->create(['name' => 'Olivia Officer']);
        AuditLog::factory()->for($officer)->create(['action' => 'auth.login']);

        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('admin.audit-logs.index'))
            ->assertOk()
            ->assertSeeText('Olivia Officer')
            ->assertSeeText('Logged in');
    }

    public function test_filters_by_action(): void
    {
        AuditLog::factory()->for(User::factory()->create(['name' => 'Logged In Person']))->create(['action' => 'auth.login']);
        AuditLog::factory()->for(User::factory()->create(['name' => 'Failed Person']))->create(['action' => 'auth.failed']);

        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('admin.audit-logs.index', ['action' => 'auth.failed']))
            ->assertSeeText('Failed Person')
            ->assertDontSeeText('Logged In Person');
    }

    public function test_rejects_end_date_before_start_date(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('admin.audit-logs.index', ['from' => '2026-10-20', 'to' => '2026-10-01']))
            ->assertSessionHasErrors('to');
    }

    public function test_federation_user_cannot_open_audit_trail(): void
    {
        $this->actingAs(User::factory()->federation()->create())
            ->get(route('admin.audit-logs.index'))
            ->assertForbidden();
    }
}
