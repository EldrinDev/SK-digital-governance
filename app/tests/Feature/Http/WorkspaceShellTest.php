<?php

namespace Tests\Feature\Http;

use App\Models\Barangay;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceShellTest extends TestCase
{
    use RefreshDatabase;

    public function test_signed_in_users_keep_their_side_menu_on_public_pages(): void
    {
        $youth = User::factory()->create();
        $youth->barangay->update(['is_participating' => true]);

        foreach (['announcements.index', 'events.index', 'transparency.index'] as $route) {
            $this->actingAs($youth)
                ->get(route($route))
                ->assertOk()
                ->assertSee('data-sidebar-toggle', false)
                ->assertSee('aria-label="Workspace"', false)
                ->assertSee('aria-current="page"', false)
                ->assertDontSeeText('Log in');
        }
    }

    public function test_guests_get_the_public_header_instead(): void
    {
        Barangay::factory()->create(['is_participating' => true]);

        $this->get(route('events.index'))
            ->assertOk()
            ->assertDontSee('data-sidebar-toggle', false)
            ->assertSeeText('Log in');
    }

    public function test_workspace_pages_have_the_collapse_toggle_and_restore_it_before_paint(): void
    {
        $this->actingAs(User::factory()->officer()->create())
            ->get(route('officer.dashboard'))
            ->assertSee('data-sidebar-toggle', false)
            ->assertSee("localStorage.getItem('sk.sidebar')", false);
    }
}
