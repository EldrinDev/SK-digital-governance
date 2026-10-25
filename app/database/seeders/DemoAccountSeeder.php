<?php

namespace Database\Seeders;

use App\Models\Barangay;
use App\Models\User;
use App\UserRole;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * One sign-in account per role, sharing a known password, for local testing and demos.
 * Never run this where real people use the platform.
 */
class DemoAccountSeeder extends Seeder
{
    /**
     * The shared password for every demo account.
     */
    public const string PASSWORD = 'password123';

    /**
     * The barangay that the youth and officer demo accounts belong to.
     */
    public const string BARANGAY = 'Santa Rita';

    /**
     * Demo accounts, one per role.
     *
     * @var array<string, array{name: string, role: UserRole}>
     */
    public const array ACCOUNTS = [
        'youth@sk.test' => ['name' => 'Yvonne Youth', 'role' => UserRole::Youth],
        'officer@sk.test' => ['name' => 'Oscar Officer', 'role' => UserRole::Officer],
        'federation@sk.test' => ['name' => 'Faye Federation', 'role' => UserRole::Federation],
        'admin@sk.test' => ['name' => 'Ada Admin', 'role' => UserRole::Administrator],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('Demo accounts share a known password and must not be created in production.');
        }

        $this->call(BarangaySeeder::class);

        $barangay = Barangay::firstWhere('name', self::BARANGAY);
        $barangay->update(['is_participating' => true]);

        foreach (self::ACCOUNTS as $email => ['name' => $name, 'role' => $role]) {
            $user = User::firstOrNew(['email' => $email]);
            $user->forceFill([
                'name' => $name,
                'password' => self::PASSWORD,
                'role' => $role,
                'barangay_id' => $role->belongsToBarangay() ? $barangay->id : null,
                'email_verified_at' => now(),
                'privacy_consented_at' => $role === UserRole::Youth ? now() : null,
                'deactivated_at' => null,
            ])->save();
        }
    }
}
