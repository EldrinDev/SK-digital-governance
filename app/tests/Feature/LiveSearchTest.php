<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_and_filter_forms_update_as_you_type(): void
    {
        $officer = User::factory()->officer()->create();
        $administrator = User::factory()->administrator()->create();

        foreach (['officer.ppas.index', 'officer.resolutions.index', 'officer.search'] as $route) {
            $this->actingAs($officer)->get(route($route))->assertOk()->assertSee('<form method="GET" data-live', false);
        }

        foreach (['admin.users.index', 'admin.audit-logs.index'] as $route) {
            $this->actingAs($administrator)->get(route($route))->assertOk()->assertSee('<form method="GET" data-live', false);
        }

        auth()->logout();

        foreach (['announcements.index', 'events.index', 'programs.index'] as $route) {
            $this->get(route($route))->assertOk()->assertSee('<form method="GET" data-live', false);
        }
    }

    public function test_filtered_results_come_back_with_their_clear_link(): void
    {
        $officer = User::factory()->officer()->create();

        $this->actingAs($officer)->get(route('officer.ppas.index', ['search' => 'liga']))
            ->assertOk()
            ->assertSee('data-live-slot="actions"', false)
            ->assertSeeText('Clear');
    }
}
