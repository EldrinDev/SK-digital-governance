<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginToastTest extends TestCase
{
    use RefreshDatabase;

    public function test_youth_see_a_toast_after_logging_in(): void
    {
        $user = User::factory()->create();

        $this->followingRedirects()
            ->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertOk()
            ->assertSee('data-toaster', false)
            ->assertSeeText('You are logged in.');
    }

    public function test_staff_see_a_toast_after_logging_in_to_their_workspace(): void
    {
        $officer = User::factory()->officer()->create();

        $this->followingRedirects()
            ->post(route('staff.login.store'), ['workspace' => (string) $officer->barangay_id, 'email' => $officer->email, 'password' => 'password'])
            ->assertOk()
            ->assertSeeText('You are logged in.');
    }

    public function test_the_landing_page_confirms_logging_out(): void
    {
        $this->actingAs(User::factory()->create())
            ->followingRedirects()
            ->post(route('logout'))
            ->assertOk()
            ->assertSeeText('You are logged out.');
    }

    public function test_action_confirmations_show_as_toasts_not_banners(): void
    {
        $this->actingAs(User::factory()->create())
            ->withSession(['status' => 'Profile saved.'])
            ->get(route('dashboard'))
            ->assertSee('data-toast ', false)
            ->assertSeeText('Profile saved.');
    }
}
