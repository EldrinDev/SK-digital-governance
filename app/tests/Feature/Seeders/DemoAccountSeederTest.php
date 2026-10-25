<?php

namespace Tests\Feature\Seeders;

use App\Http\Requests\Auth\StaffLoginRequest;
use App\Models\Barangay;
use App\Models\User;
use Database\Seeders\DemoAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class DemoAccountSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_demo_account_logs_in_to_its_own_home(): void
    {
        $this->seed(DemoAccountSeeder::class);
        $barangayId = (string) Barangay::firstWhere('name', DemoAccountSeeder::BARANGAY)->id;

        $this->post(route('login.store'), ['email' => 'youth@sk.test', 'password' => 'password123'])
            ->assertRedirect(route('dashboard'));
        auth()->logout();

        $staff = [
            'officer@sk.test' => [$barangayId, 'officer.dashboard'],
            'federation@sk.test' => [StaffLoginRequest::FEDERATION_WORKSPACE, 'federation.dashboard'],
            'admin@sk.test' => [StaffLoginRequest::ADMINISTRATION_WORKSPACE, 'admin.dashboard'],
        ];

        foreach ($staff as $email => [$workspace, $home]) {
            $this->post(route('staff.login.store'), ['workspace' => $workspace, 'email' => $email, 'password' => 'password123'])
                ->assertRedirect(route($home));
            auth()->logout();
        }
    }

    public function test_running_twice_does_not_duplicate_accounts(): void
    {
        $this->seed(DemoAccountSeeder::class);
        $this->seed(DemoAccountSeeder::class);

        $this->assertSame(4, User::count());
    }

    public function test_refuses_to_run_in_production(): void
    {
        $this->app['env'] = 'production';

        try {
            $this->app->make(DemoAccountSeeder::class)->run();
            $this->fail('The seeder ran in production.');
        } catch (RuntimeException) {
            $this->assertSame(0, User::count());
        }
    }
}
