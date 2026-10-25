<?php

namespace Tests\Feature\Admin;

use App\Models\Barangay;
use App\Models\User;
use App\UserRole;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->administrator()->create(['name' => 'Ana Admin']);
    }

    public function test_officer_cannot_open_user_management(): void
    {
        $this->actingAs(User::factory()->officer()->create())
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_index_lists_accounts_and_filters_by_role(): void
    {
        User::factory()->officer()->create(['name' => 'Olivia Officer']);
        User::factory()->create(['name' => 'Yuri Youth']);

        $this->actingAs($this->admin)
            ->get(route('admin.users.index', ['role' => 'officer']))
            ->assertOk()
            ->assertSeeText('Olivia Officer')
            ->assertDontSeeText('Yuri Youth');
    }

    public function test_index_searches_by_email(): void
    {
        User::factory()->create(['name' => 'Maria Santos', 'email' => 'maria@example.com']);
        User::factory()->create(['name' => 'Juan Cruz', 'email' => 'juan@example.com']);

        $this->actingAs($this->admin)
            ->get(route('admin.users.index', ['search' => 'maria@']))
            ->assertSeeText('Maria Santos')
            ->assertDontSeeText('Juan Cruz');
    }

    public function test_store_creates_officer_emails_set_password_link_and_records_it(): void
    {
        Notification::fake();
        $barangay = Barangay::factory()->create();

        $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Carlo Reyes',
            'email' => 'carlo@example.com',
            'role' => 'officer',
            'barangay_id' => $barangay->id,
        ])->assertRedirect(route('admin.users.index'));

        $officer = User::firstWhere('email', 'carlo@example.com');

        $this->assertSame(UserRole::Officer, $officer->role);
        $this->assertTrue($officer->barangay->is($barangay));
        Notification::assertSentTo($officer, ResetPassword::class);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'user.created',
            'user_id' => $this->admin->id,
            'subject_id' => $officer->id,
        ]);
    }

    public function test_store_rejects_officer_without_barangay(): void
    {
        $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Carlo Reyes',
            'email' => 'carlo@example.com',
            'role' => 'officer',
        ])->assertSessionHasErrors(['barangay_id' => 'Youth and barangay SK officers need a barangay.']);

        $this->assertDatabaseMissing('users', ['email' => 'carlo@example.com']);
    }

    public function test_store_drops_barangay_for_federation_user(): void
    {
        Notification::fake();

        $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Fe Federation',
            'email' => 'fe@example.com',
            'role' => 'federation',
            'barangay_id' => Barangay::factory()->create()->id,
        ]);

        $this->assertNull(User::firstWhere('email', 'fe@example.com')->barangay_id);
    }

    public function test_update_changes_role_and_barangay(): void
    {
        $user = User::factory()->create();
        $barangay = Barangay::factory()->create();

        $this->actingAs($this->admin)->put(route('admin.users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'officer',
            'barangay_id' => $barangay->id,
            'is_active' => '1',
        ])->assertRedirect(route('admin.users.index'));

        $user->refresh();
        $this->assertSame(UserRole::Officer, $user->role);
        $this->assertTrue($user->barangay->is($barangay));
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.updated', 'subject_id' => $user->id]);
    }

    public function test_update_deactivates_account_and_records_it(): void
    {
        $user = User::factory()->create();

        $this->actingAs($this->admin)->put(route('admin.users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'youth',
            'barangay_id' => $user->barangay_id,
            'is_active' => '0',
        ]);

        $this->assertFalse($user->fresh()->isActive());
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.deactivated', 'subject_id' => $user->id]);
    }

    public function test_admin_cannot_demote_or_deactivate_themselves(): void
    {
        $this->actingAs($this->admin)->put(route('admin.users.update', $this->admin), [
            'name' => $this->admin->name,
            'email' => $this->admin->email,
            'role' => 'youth',
            'barangay_id' => Barangay::factory()->create()->id,
            'is_active' => '0',
        ])->assertSessionHasErrors([
            'role' => 'You can’t change your own role. Ask another administrator.',
            'is_active' => 'You can’t deactivate your own account.',
        ]);

        $this->admin->refresh();
        $this->assertSame(UserRole::Administrator, $this->admin->role);
        $this->assertTrue($this->admin->isActive());
    }
}
