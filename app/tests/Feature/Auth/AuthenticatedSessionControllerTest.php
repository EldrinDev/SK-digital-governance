<?php

namespace Tests\Feature\Auth;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticatedSessionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_with_link_to_sk_access(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSeeText('Log in')
            ->assertSeeText('SK access')
            ->assertSee(route('staff.login'), false);
    }

    public function test_login_page_redirects_authenticated_users_to_their_home(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('login'))
            ->assertRedirect(route('dashboard'));

        $this->actingAs(User::factory()->federation()->create())
            ->get(route('login'))
            ->assertRedirect(route('federation.dashboard'));
    }

    public function test_staff_account_is_rejected_on_the_public_login(): void
    {
        $officer = User::factory()->officer()->create();

        $this->post(route('login.store'), [
            'email' => $officer->email,
            'password' => 'password',
        ])->assertSessionHasErrors([
            'credentials' => 'That email and password don’t match an account. Check them and try again.',
        ]);

        $this->assertGuest();
    }

    public function test_valid_credentials_log_the_user_in_and_redirect_to_dashboard(): void
    {
        $user = User::factory()->create();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_password_rejects_login_with_message(): void
    {
        $user = User::factory()->create();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors([
            'credentials' => 'That email and password don’t match an account. Check them and try again.',
        ]);

        $this->assertGuest();
    }

    public function test_wrong_password_shows_alert_and_keeps_the_email(): void
    {
        $user = User::factory()->create();

        $this->from(route('login'))
            ->followingRedirects()
            ->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'wrong-password',
            ])
            ->assertOk()
            ->assertSee('role="alert"', false)
            ->assertSeeText('That email and password don’t match an account.')
            ->assertSee('value="'.$user->email.'"', false);
    }

    public function test_empty_payload_rejects_login_with_required_errors(): void
    {
        $this->post(route('login.store'), [])
            ->assertSessionHasErrors(['email', 'password']);

        $this->assertGuest();
    }

    public function test_too_many_failed_attempts_lock_out_even_the_correct_password(): void
    {
        $user = User::factory()->create();

        for ($attempt = 0; $attempt < LoginRequest::MAX_ATTEMPTS; $attempt++) {
            $this->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);
        }

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('credentials');

        $this->assertStringStartsWith('Too many login attempts.', session('errors')->first('credentials'));
        $this->assertGuest();
    }

    public function test_logout_ends_the_session_and_redirects_home(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest();
    }

    public function test_dashboard_redirects_guests_to_login(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }
}
