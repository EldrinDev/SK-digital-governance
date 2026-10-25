<?php

namespace Tests\Feature\Http;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EnsureUserHasRoleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Each role's home page and the factory state that creates that role.
     *
     * @return array<string, array{string, ?string}>
     */
    public static function homes(): array
    {
        return [
            'youth' => ['dashboard', null],
            'officer' => ['officer.dashboard', 'officer'],
            'federation' => ['federation.dashboard', 'federation'],
            'administrator' => ['admin.dashboard', 'administrator'],
        ];
    }

    #[DataProvider('homes')]
    public function test_each_role_opens_its_own_home(string $route, ?string $state): void
    {
        $factory = User::factory();
        $user = ($state ? $factory->{$state}() : $factory)->create();

        $this->actingAs($user)->get(route($route))->assertOk();
    }

    #[DataProvider('homes')]
    public function test_guests_are_redirected_to_login(string $route): void
    {
        $this->get(route($route))->assertRedirect(route('login'));
    }

    public function test_youth_cannot_open_officer_workspace(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('officer.dashboard'))
            ->assertForbidden();
    }

    public function test_officer_cannot_open_federation_console(): void
    {
        $this->actingAs(User::factory()->officer()->create())
            ->get(route('federation.dashboard'))
            ->assertForbidden();
    }

    public function test_federation_user_cannot_open_administration_console(): void
    {
        $this->actingAs(User::factory()->federation()->create())
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }
}
