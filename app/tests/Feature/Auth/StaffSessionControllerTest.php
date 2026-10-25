<?php

namespace Tests\Feature\Auth;

use App\Http\Requests\Auth\StaffLoginRequest;
use App\Models\Barangay;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StaffSessionControllerTest extends TestCase
{
    use RefreshDatabase;

    private const string NO_MATCH = 'That email and password don’t match an account. Check them and try again.';

    public function test_sk_access_page_lists_barangay_and_citywide_workspaces(): void
    {
        Barangay::factory()->create(['name' => 'Kalaklan']);

        $this->get(route('staff.login'))
            ->assertOk()
            ->assertSeeText('SK access')
            ->assertSeeText('Kalaklan')
            ->assertSeeText('SK Federation')
            ->assertSeeText('System administration');
    }

    public function test_officer_logs_in_to_own_barangay_and_lands_on_officer_dashboard(): void
    {
        $officer = User::factory()->officer()->create();

        $this->post(route('staff.login.store'), [
            'workspace' => (string) $officer->barangay_id,
            'email' => $officer->email,
            'password' => 'password',
        ])->assertRedirect(route('officer.dashboard'));

        $this->assertAuthenticatedAs($officer);
    }

    public function test_officer_choosing_another_barangay_is_rejected(): void
    {
        $officer = User::factory()->officer()->create();
        $otherBarangay = Barangay::factory()->create();

        $this->post(route('staff.login.store'), [
            'workspace' => (string) $otherBarangay->id,
            'email' => $officer->email,
            'password' => 'password',
        ])->assertSessionHasErrors(['credentials' => self::NO_MATCH]);

        $this->assertGuest();
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function citywideWorkspaces(): array
    {
        return [
            'federation' => ['federation', StaffLoginRequest::FEDERATION_WORKSPACE, 'federation.dashboard'],
            'administrator' => ['administrator', StaffLoginRequest::ADMINISTRATION_WORKSPACE, 'admin.dashboard'],
        ];
    }

    #[DataProvider('citywideWorkspaces')]
    public function test_citywide_staff_log_in_to_their_console(string $state, string $workspace, string $home): void
    {
        $staff = User::factory()->{$state}()->create();

        $this->post(route('staff.login.store'), [
            'workspace' => $workspace,
            'email' => $staff->email,
            'password' => 'password',
        ])->assertRedirect(route($home));

        $this->assertAuthenticatedAs($staff);
    }

    public function test_officer_choosing_federation_workspace_is_rejected(): void
    {
        $officer = User::factory()->officer()->create();

        $this->post(route('staff.login.store'), [
            'workspace' => StaffLoginRequest::FEDERATION_WORKSPACE,
            'email' => $officer->email,
            'password' => 'password',
        ])->assertSessionHasErrors(['credentials' => self::NO_MATCH]);

        $this->assertGuest();
    }

    public function test_youth_account_is_rejected_with_the_generic_message(): void
    {
        $youth = User::factory()->create();

        $this->post(route('staff.login.store'), [
            'workspace' => (string) $youth->barangay_id,
            'email' => $youth->email,
            'password' => 'password',
        ])->assertSessionHasErrors(['credentials' => self::NO_MATCH]);

        $this->assertGuest();
    }

    public function test_missing_workspace_rejects_login_with_message(): void
    {
        $officer = User::factory()->officer()->create();

        $this->post(route('staff.login.store'), [
            'email' => $officer->email,
            'password' => 'password',
        ])->assertSessionHasErrors(['workspace' => 'Choose the workspace you are signing in to.']);

        $this->assertGuest();
    }

    public function test_unknown_workspace_rejects_login_with_message(): void
    {
        $this->post(route('staff.login.store'), [
            'workspace' => '999',
            'email' => 'someone@example.com',
            'password' => 'password',
        ])->assertSessionHasErrors(['workspace' => 'Choose a workspace from the list.']);
    }
}
