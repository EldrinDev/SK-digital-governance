<?php

namespace Tests\Feature\Seeders;

use App\Models\Barangay;
use App\Models\Cbydp;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake();
    }

    public function test_an_empty_database_gets_the_barangays_and_the_demo_data(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(17, Barangay::participating()->count());
        $this->assertTrue(User::where('email', 'officer@sk.test')->exists());
        $this->assertTrue(Cbydp::query()->exists());
        $this->get(route('staff.login'))->assertOk()->assertSeeText('Santa Rita')->assertSeeText('West Tapinac');
    }

    public function test_seeding_again_keeps_existing_data_and_does_not_fail(): void
    {
        $this->seed(DatabaseSeeder::class);
        $users = User::count();

        $this->seed(DatabaseSeeder::class);

        $this->assertSame(17, Barangay::count());
        $this->assertSame($users, User::count());
    }

    public function test_a_database_with_its_own_accounts_only_gets_the_missing_barangays(): void
    {
        $user = User::factory()->create();

        $this->seed(DatabaseSeeder::class);

        $this->assertSame(1, User::count());
        $this->assertTrue($user->fresh()->is($user));
        $this->assertFalse(Cbydp::query()->exists());
        $this->assertTrue(Barangay::where('name', 'Santa Rita')->exists());
    }

    public function test_production_gets_the_barangays_but_never_demo_accounts(): void
    {
        $this->app['env'] = 'production';

        // Called directly: in production `db:seed` stops to ask for confirmation.
        $this->app->make(DatabaseSeeder::class)->setContainer($this->app)->__invoke();

        $this->assertSame(17, Barangay::count());
        $this->assertSame(0, User::count(), 'No demo accounts with the shared password.');
    }
}
