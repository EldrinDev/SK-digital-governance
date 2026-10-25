<?php

namespace Tests\Feature\Auth;

use App\Models\Barangay;
use App\Models\User;
use App\UserRole;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisteredUserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_page_renders_with_barangay_choices(): void
    {
        Barangay::factory()->create(['name' => 'Barretto']);

        $this->get(route('register'))
            ->assertOk()
            ->assertSeeText('Create an account')
            ->assertSeeText('Barretto')
            ->assertSee(route('privacy'), false);
    }

    public function test_valid_payload_creates_youth_account_logs_in_and_redirects_to_dashboard(): void
    {
        Event::fake([Registered::class]);
        $barangay = Barangay::factory()->create();

        $this->post(route('register.store'), $this->validPayload(['barangay_id' => $barangay->id]))
            ->assertRedirect(route('dashboard'));

        $user = User::firstWhere('email', 'juan@example.com');

        $this->assertNotNull($user);
        $this->assertSame('Juan Dela Cruz', $user->name);
        $this->assertSame(UserRole::Youth, $user->fresh()->role);
        $this->assertTrue($user->barangay->is($barangay));
        $this->assertNotNull($user->privacy_consented_at);
        $this->assertTrue(Hash::check('secret-pass-123', $user->password));
        $this->assertAuthenticatedAs($user);
        Event::assertDispatched(Registered::class);
    }

    public function test_role_in_payload_is_ignored(): void
    {
        $this->post(route('register.store'), $this->validPayload(['role' => 'administrator']));

        $this->assertSame(UserRole::Youth, User::firstWhere('email', 'juan@example.com')->role);
    }

    public function test_empty_payload_rejects_registration_with_required_errors(): void
    {
        $this->post(route('register.store'), [])
            ->assertSessionHasErrors(['name', 'email', 'password', 'barangay_id', 'privacy_consent']);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_missing_privacy_consent_rejects_registration_with_message(): void
    {
        $payload = $this->validPayload();
        unset($payload['privacy_consent']);

        $this->post(route('register.store'), $payload)->assertSessionHasErrors([
            'privacy_consent' => 'Read and accept the privacy notice to create an account.',
        ]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_unknown_barangay_rejects_registration_with_message(): void
    {
        $this->post(route('register.store'), $this->validPayload(['barangay_id' => 999]))
            ->assertSessionHasErrors(['barangay_id' => 'Choose a barangay from the list.']);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_existing_email_rejects_registration_with_message(): void
    {
        User::factory()->create(['email' => 'juan@example.com']);

        $this->post(route('register.store'), $this->validPayload())->assertSessionHasErrors([
            'email' => 'An account with this email already exists. Log in instead.',
        ]);

        $this->assertDatabaseCount('users', 1);
        $this->assertGuest();
    }

    public function test_mismatched_password_confirmation_rejects_registration_with_message(): void
    {
        $this->post(route('register.store'), $this->validPayload(['password_confirmation' => 'different-pass-456']))
            ->assertSessionHasErrors(['password' => 'The two passwords don’t match.']);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_password_shorter_than_eight_characters_rejects_registration(): void
    {
        $this->post(route('register.store'), $this->validPayload(['password' => 'short', 'password_confirmation' => 'short']))
            ->assertSessionHasErrors('password');

        $this->assertDatabaseCount('users', 0);
    }

    /**
     * Build a registration payload that passes validation.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return [
            'name' => 'Juan Dela Cruz',
            'email' => 'juan@example.com',
            'password' => 'secret-pass-123',
            'password_confirmation' => 'secret-pass-123',
            'barangay_id' => $overrides['barangay_id'] ?? Barangay::factory()->create()->id,
            'privacy_consent' => '1',
            ...$overrides,
        ];
    }
}
