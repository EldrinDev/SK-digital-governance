<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * The 17 barangays are always made sure of, so the SK access login always has its
     * workspaces. Outside production, an empty database also gets the demo data, so
     * `php artisan migrate:fresh --seed` gives every branch the same working setup.
     * A database that already has accounts is never touched beyond the barangays.
     */
    public function run(): void
    {
        $this->call(BarangaySeeder::class);

        if (app()->isProduction()) {
            return;
        }

        if (User::query()->exists()) {
            $this->command?->warn('Skipped the demo data because the database already has accounts. To start over: php artisan migrate:fresh --seed');

            return;
        }

        $this->call(DemoDataSeeder::class);
    }
}
