<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private const string LINK_SENT = 'If an account uses that email, we sent it a link to reset the password. The link works for 60 minutes.';

    public function test_login_pages_link_to_forgot_password(): void
    {
        $this->get(route('login'))->assertSee(route('password.request'), false);
        $this->get(route('staff.login'))->assertSee(route('password.request'), false);
    }

    public function test_reset_link_is_emailed_to_existing_account(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect(route('password.request'))
            ->assertSessionHas('status', self::LINK_SENT);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_unknown_email_gets_the_same_reply(): void
    {
        Notification::fake();

        $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => 'nobody@example.com'])
            ->assertSessionHas('status', self::LINK_SENT);

        Notification::assertNothingSent();
    }

    public function test_valid_token_changes_password_and_sends_staff_to_sk_access(): void
    {
        Notification::fake();
        $officer = User::factory()->officer()->create();

        $this->post(route('password.email'), ['email' => $officer->email]);

        Notification::assertSentTo($officer, ResetPassword::class, function (ResetPassword $notification) use ($officer): bool {
            $this->get(route('password.reset', ['token' => $notification->token, 'email' => $officer->email]))
                ->assertOk()
                ->assertSee('value="'.$officer->email.'"', false);

            $this->post(route('password.store'), [
                'token' => $notification->token,
                'email' => $officer->email,
                'password' => 'brand-new-pass-1',
                'password_confirmation' => 'brand-new-pass-1',
            ])->assertRedirect(route('staff.login'));

            return true;
        });

        $this->assertTrue(Hash::check('brand-new-pass-1', $officer->fresh()->password));
        $this->assertDatabaseHas('audit_logs', ['action' => 'auth.password_reset', 'user_id' => $officer->id]);
    }

    public function test_invalid_token_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->from(route('password.reset', ['token' => 'bad-token']))
            ->post(route('password.store'), [
                'token' => 'bad-token',
                'email' => $user->email,
                'password' => 'brand-new-pass-1',
                'password_confirmation' => 'brand-new-pass-1',
            ])->assertSessionHasErrors(['credentials' => 'This reset link is invalid or has expired. Ask for a new one.']);

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }
}
