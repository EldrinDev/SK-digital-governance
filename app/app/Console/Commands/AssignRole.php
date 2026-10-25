<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Barangay;
use App\Models\User;
use App\UserRole;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:assign-role
    {email : Email of the account to change}
    {role : youth, officer, federation or administrator}
    {--barangay= : Barangay name, required for youth and officers}')]
#[Description('Set the role and barangay workspace of an existing account')]
class AssignRole extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $user = User::firstWhere('email', $this->argument('email'));

        if ($user === null) {
            $this->error("No account uses {$this->argument('email')}. Ask them to register first.");

            return self::FAILURE;
        }

        $role = UserRole::tryFrom($this->argument('role'));

        if ($role === null) {
            $this->error('Role must be one of: '.implode(', ', array_column(UserRole::cases(), 'value')).'.');

            return self::FAILURE;
        }

        $barangay = null;

        if ($role->belongsToBarangay()) {
            $barangay = Barangay::firstWhere('name', $this->option('barangay'));

            if ($barangay === null) {
                $this->error("A {$role->label()} needs a barangay. Pass --barangay with one of: ".Barangay::orderBy('name')->pluck('name')->implode(', ').'.');

                return self::FAILURE;
            }
        }

        $previousRole = $user->role;

        $user->forceFill(['role' => $role, 'barangay_id' => $barangay?->id])->save();

        AuditLog::record('user.role_changed', $user, [
            'from' => $previousRole->value,
            'to' => $role->value,
            'barangay' => $barangay?->name,
        ]);

        $this->info("{$user->name} is now a {$role->label()}".($barangay ? " in {$barangay->name}" : '').'.');

        return self::SUCCESS;
    }
}
