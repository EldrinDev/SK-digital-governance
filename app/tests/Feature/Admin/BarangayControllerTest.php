<?php

namespace Tests\Feature\Admin;

use App\Models\Barangay;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarangayControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_barangays_with_participation(): void
    {
        Barangay::factory()->create(['name' => 'Kalaklan']);

        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('admin.barangays.index'))
            ->assertOk()
            ->assertSeeText('Kalaklan')
            ->assertSeeText('Participating');
    }

    public function test_turning_off_a_barangay_hides_it_from_registration_and_records_it(): void
    {
        $admin = User::factory()->administrator()->create();
        $barangay = Barangay::factory()->create(['name' => 'Kalaklan']);

        $this->actingAs($admin)
            ->patch(route('admin.barangays.update', $barangay), ['is_participating' => '0'])
            ->assertRedirect(route('admin.barangays.index'));

        $this->assertFalse($barangay->fresh()->is_participating);
        $this->assertDatabaseHas('audit_logs', ['action' => 'barangay.updated', 'barangay_id' => $barangay->id]);

        auth()->logout();

        $this->get(route('register'))->assertDontSee('>Kalaklan</option>', false);
    }

    public function test_officers_of_a_non_participating_barangay_cannot_sign_in(): void
    {
        $officer = User::factory()->officer()->create();
        $officer->barangay->update(['is_participating' => false]);

        $this->post(route('staff.login.store'), [
            'workspace' => (string) $officer->barangay_id,
            'email' => $officer->email,
            'password' => 'password',
        ])->assertSessionHasErrors(['workspace' => 'Choose a workspace from the list.']);

        $this->assertGuest();
    }

    public function test_officer_cannot_change_participation(): void
    {
        $this->actingAs(User::factory()->officer()->create())
            ->patch(route('admin.barangays.update', Barangay::factory()->create()), ['is_participating' => '0'])
            ->assertForbidden();
    }
}
