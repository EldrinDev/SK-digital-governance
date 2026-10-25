<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Show the form for choosing a new password.
     */
    public function create(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->string('email')->toString(),
        ]);
    }

    /**
     * Save the new password and send the person to the right login page.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ], [
            'password.confirmed' => 'The two passwords don’t match.',
        ]);

        $resetUser = null;

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) use (&$resetUser): void {
                $user->forceFill(['password' => $password])->setRememberToken(Str::random(60));
                $user->save();

                $resetUser = $user;

                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PasswordReset) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['credentials' => 'This reset link is invalid or has expired. Ask for a new one.']);
        }

        $loginRoute = $resetUser->role->isStaff() ? 'staff.login' : 'login';

        return redirect()->route($loginRoute)->with('status', 'Your password was changed. You can log in with it now.');
    }
}
