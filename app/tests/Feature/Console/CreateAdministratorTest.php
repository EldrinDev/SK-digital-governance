<?php

namespace Tests\Feature\Console;

use App\Models\User;
use App\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateAdministratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_administrator_with_the_typed_password(): void
    {
        $this->artisan('app:create-administrator', ['email' => 'admin@example.com', '--name' => 'Ana Admin'])
            ->expectsQuestion('Password (at least 8 characters)', 'secret-pass-123')
            ->expectsOutput('Ana Admin can now log in through SK access, under System administration.')
            ->assertSuccessful();

        $admin = User::firstWhere('email', 'admin@example.com');

        $this->assertSame(UserRole::Administrator, $admin->role);
        $this->assertNull($admin->barangay_id);
        $this->assertTrue(Hash::check('secret-pass-123', $admin->password));
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.created', 'subject_id' => $admin->id]);
    }

    public function test_rejects_short_password(): void
    {
        $this->artisan('app:create-administrator', ['email' => 'admin@example.com', '--name' => 'Ana Admin'])
            ->expectsQuestion('Password (at least 8 characters)', 'short')
            ->assertFailed();

        $this->assertDatabaseMissing('users', ['email' => 'admin@example.com']);
    }

    public function test_rejects_email_already_in_use(): void
    {
        User::factory()->create(['email' => 'admin@example.com']);

        $this->artisan('app:create-administrator', ['email' => 'admin@example.com', '--name' => 'Ana Admin'])
            ->expectsQuestion('Password (at least 8 characters)', 'secret-pass-123')
            ->assertFailed();
    }
}
