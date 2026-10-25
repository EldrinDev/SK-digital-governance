<?php

namespace App\Http\Requests\Auth;

use App\UserRole;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * The number of failed attempts allowed before the login is locked.
     */
    public const int MAX_ATTEMPTS = 5;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Get the account conditions this login form accepts, besides email and password.
     *
     * The public login only lets youth accounts in; staff use SK access.
     *
     * @return array<string, int|string>
     */
    protected function accountConditions(): array
    {
        return ['role' => UserRole::Youth->value];
    }

    /**
     * Attempt to log the user in with the submitted credentials.
     *
     * An account that exists but fails the conditions gets the same message as a
     * wrong password, so the form never reveals which accounts exist.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = [
            ...$this->only('email', 'password'),
            'deactivated_at' => null,
            ...$this->accountConditions(),
        ];

        if (! Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'credentials' => 'That email and password don’t match an account. Check them and try again.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Stop the attempt when there have been too many failed logins.
     *
     * @throws ValidationException
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_ATTEMPTS)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'credentials' => "Too many login attempts. Try again in {$seconds} seconds.",
        ]);
    }

    /**
     * Get the rate limiting key for this email and IP address.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
