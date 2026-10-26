<?php

namespace Tests\Feature\Officer;

use App\Models\Announcement;
use App\Models\Evidence;
use App\Models\Ppa;
use App\Models\Resolution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_finds_records_across_the_workspace(): void
    {
        $ppa = Ppa::factory()->create(['title' => 'Coastal clean-up drive']);
        $officer = User::factory()->officer()->create(['barangay_id' => $ppa->barangay_id]);
        Evidence::factory()->for($ppa)->create(['title' => 'Clean-up photo', 'barangay_id' => $ppa->barangay_id]);
        Resolution::factory()->create(['barangay_id' => $ppa->barangay_id, 'number' => 'Res. No. 9', 'title' => 'Approving the clean-up budget']);
        Announcement::factory()->create(['barangay_id' => $ppa->barangay_id, 'title' => 'Clean-up on Saturday']);

        $this->actingAs($officer)
            ->get(route('officer.search', ['q' => 'clean-up']))
            ->assertOk()
            ->assertSeeText('Coastal clean-up drive')
            ->assertSeeText('Clean-up photo')
            ->assertSeeText('Res. No. 9: Approving the clean-up budget')
            ->assertSeeText('Clean-up on Saturday');
    }

    public function test_never_shows_records_of_another_barangay(): void
    {
        Ppa::factory()->create(['title' => 'Other barangay clean-up']);

        $this->actingAs(User::factory()->officer()->create())
            ->get(route('officer.search', ['q' => 'clean-up']))
            ->assertOk()
            ->assertDontSeeText('Other barangay clean-up')
            ->assertSeeText('Nothing in your workspace matches');
    }

    public function test_federation_users_cannot_use_workspace_search(): void
    {
        $this->actingAs(User::factory()->federation()->create())->get(route('officer.search', ['q' => 'x']))->assertForbidden();
    }
}
