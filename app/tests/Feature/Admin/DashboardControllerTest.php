<?php

namespace Tests\Feature\Admin;

use App\Models\Barangay;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_shows_participating_barangays_and_side_menu(): void
    {
        Barangay::factory()->create(['name' => 'Santa Rita']);
        Barangay::factory()->create(['name' => 'Asinan', 'is_participating' => false]);

        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeText('Participating barangays')
            ->assertSeeText('Santa Rita')
            ->assertDontSeeText('Asinan')
            ->assertSee(route('admin.users.index'), false)
            ->assertSee(route('admin.audit-logs.index'), false);
    }
}
