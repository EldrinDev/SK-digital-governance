<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Show the "forgot password" page.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Email a reset link when the address belongs to an account.
     *
     * The reply is the same whether or not an account exists, so the form
     * cannot be used to find out who has an account.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        Password::sendResetLink($request->only('email'));

        return back()->with('status', 'If an account uses that email, we sent it a link to reset the password. The link works for 60 minutes.');
    }
}
