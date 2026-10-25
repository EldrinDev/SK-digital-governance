<?php

namespace Tests\Feature\Listeners;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\AuditLog;
use App\Models\Barangay;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecordAuthenticationActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_login_is_recorded_with_user_and_barangay(): void
    {
        $officer = User::factory()->officer()->create();

        $this->post(route('staff.login.store'), [
            'workspace' => (string) $officer->barangay_id,
            'email' => $officer->email,
            'password' => 'password',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'auth.login',
            'user_id' => $officer->id,
            'barangay_id' => $officer->barangay_id,
        ]);
    }

    public function test_failed_login_records_email_but_not_password(): void
    {
        $user = User::factory()->create();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $entry = AuditLog::firstWhere('action', 'auth.failed');

        $this->assertNotNull($entry);
        $this->assertSame(['email' => $user->email], $entry->properties);
        $this->assertStringNotContainsString('wrong-password', json_encode($entry->getAttributes()));
    }

    public function test_lockout_is_recorded(): void
    {
        $user = User::factory()->create();

        for ($attempt = 0; $attempt <= LoginRequest::MAX_ATTEMPTS; $attempt++) {
            $this->post(route('login.store'), ['email' => $user->email, 'password' => 'wrong-password']);
        }

        $this->assertDatabaseHas('audit_logs', ['action' => 'auth.lockout']);
    }

    public function test_logout_is_recorded(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('logout'));

        $this->assertDatabaseHas('audit_logs', ['action' => 'auth.logout', 'user_id' => $user->id]);
    }

    public function test_registration_is_recorded(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Juan Dela Cruz',
            'email' => 'juan@example.com',
            'password' => 'secret-pass-123',
            'password_confirmation' => 'secret-pass-123',
            'barangay_id' => Barangay::factory()->create()->id,
            'privacy_consent' => '1',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'auth.registered',
            'user_id' => User::firstWhere('email', 'juan@example.com')->id,
        ]);
    }
}
