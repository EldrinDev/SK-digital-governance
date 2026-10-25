<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\User;
use App\UserRole;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

#[Signature('app:create-administrator
    {email : Email for the new administrator}
    {--name= : Full name}')]
#[Description('Create a system administrator account, e.g. the first one')]
class CreateAdministrator extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Full name');
        $password = $this->secret('Password (at least 8 characters)');

        $validator = Validator::make(
            ['name' => $name, 'email' => $this->argument('email'), 'password' => $password],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', Rule::unique(User::class)],
                'password' => ['required', Password::defaults()],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $user = new User(['name' => $name, 'email' => $this->argument('email'), 'password' => $password]);
        $user->role = UserRole::Administrator;
        $user->email_verified_at = now();
        $user->save();

        AuditLog::record('user.created', properties: ['email' => $user->email, 'role' => $user->role->value, 'via' => 'console'], subject: $user);

        $this->info("{$user->name} can now log in through SK access, under System administration.");

        return self::SUCCESS;
    }
}
