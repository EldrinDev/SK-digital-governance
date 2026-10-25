<?php

namespace App\Listeners;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;

/**
 * Writes sign-ins, sign-outs, failed attempts, lockouts and new accounts to the audit trail.
 */
class RecordAuthenticationActivity
{
    /**
     * Record a successful login.
     */
    public function handleLogin(Login $event): void
    {
        $this->record('auth.login', $event->user);
    }

    /**
     * Record a failed login attempt. Only the email is kept, never the password.
     */
    public function handleFailed(Failed $event): void
    {
        $this->record('auth.failed', $event->user, [
            'email' => $event->credentials['email'] ?? null,
        ]);
    }

    /**
     * Record a lockout after too many failed attempts.
     */
    public function handleLockout(Lockout $event): void
    {
        AuditLog::record('auth.lockout', properties: [
            'email' => $event->request->input('email'),
        ]);
    }

    /**
     * Record a logout.
     */
    public function handleLogout(Logout $event): void
    {
        $this->record('auth.logout', $event->user);
    }

    /**
     * Record a new account.
     */
    public function handleRegistered(Registered $event): void
    {
        $this->record('auth.registered', $event->user);
    }

    /**
     * Record the action against the user when the user is one of ours.
     *
     * @param  array<string, mixed>  $properties
     */
    private function record(string $action, mixed $user, array $properties = []): void
    {
        AuditLog::record($action, $user instanceof User ? $user : null, $properties);
    }
}
