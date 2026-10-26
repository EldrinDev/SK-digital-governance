<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Show the account settings page.
     */
    public function edit(Request $request): View
    {
        return view('account.edit', ['user' => $request->user()]);
    }

    /**
     * Update the name and email of the signed-in account.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
        ], [
            'email.unique' => 'Another account already uses this email.',
        ]);

        $user->fill($validated);
        $changed = array_keys($user->getDirty());

        if ($changed === []) {
            return back()->with('status', 'Nothing to change.');
        }

        $user->save();

        AuditLog::record('account.updated', $user, ['fields' => $changed]);

        return back()->with('status', 'Your details were saved.');
    }

    /**
     * Close a youth account. Personal details are erased, while attendance counts,
     * survey tallies and the audit trail stay intact without the person's name.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_if($user->role->isStaff(), 403, 'Staff accounts are closed by an administrator.');

        $request->validate(['confirm_password' => ['required', 'current_password']], [
            'confirm_password.current_password' => 'That password isn’t right.',
        ]);

        AuditLog::record('account.deleted', $user);

        $user->forceFill([
            'name' => 'Deleted account',
            'email' => "deleted-{$user->id}@deleted.invalid",
            'password' => Str::random(64),
            'remember_token' => null,
            'deactivated_at' => now(),
        ])->save();

        $user->notifications()->delete();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
