<?php

namespace Tests\Feature\Console;

use App\Models\Barangay;
use App\Models\User;
use App\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_makes_account_an_officer_of_the_given_barangay_and_records_it(): void
    {
        $user = User::factory()->create();
        $barangay = Barangay::factory()->create(['name' => 'Gordon Heights']);

        $this->artisan('app:assign-role', ['email' => $user->email, 'role' => 'officer', '--barangay' => 'Gordon Heights'])
            ->expectsOutput("{$user->name} is now a Barangay SK officer in Gordon Heights.")
            ->assertSuccessful();

        $user->refresh();
        $this->assertSame(UserRole::Officer, $user->role);
        $this->assertTrue($user->barangay->is($barangay));
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.role_changed', 'user_id' => $user->id]);
    }

    public function test_citywide_role_clears_the_barangay(): void
    {
        $user = User::factory()->create();

        $this->artisan('app:assign-role', ['email' => $user->email, 'role' => 'federation'])
            ->assertSuccessful();

        $user->refresh();
        $this->assertSame(UserRole::Federation, $user->role);
        $this->assertNull($user->barangay_id);
    }

    public function test_officer_without_barangay_fails_and_keeps_the_role(): void
    {
        $user = User::factory()->create();

        $this->artisan('app:assign-role', ['email' => $user->email, 'role' => 'officer'])
            ->assertFailed();

        $this->assertSame(UserRole::Youth, $user->fresh()->role);
    }

    public function test_unknown_role_fails(): void
    {
        $user = User::factory()->create();

        $this->artisan('app:assign-role', ['email' => $user->email, 'role' => 'mayor'])
            ->expectsOutput('Role must be one of: youth, officer, federation, administrator.')
            ->assertFailed();
    }

    public function test_unknown_email_fails(): void
    {
        $this->artisan('app:assign-role', ['email' => 'nobody@example.com', 'role' => 'officer'])
            ->expectsOutput('No account uses nobody@example.com. Ask them to register first.')
            ->assertFailed();
    }
}
