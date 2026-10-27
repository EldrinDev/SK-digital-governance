<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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
        $user = $request->user()->load('barangay');
        $isYouth = $user->hasRole(UserRole::Youth);

        return view('account.edit', [
            'user' => $user,
            'isYouth' => $isYouth,
            'code' => $isYouth ? $user->memberCode() : null,
            'stats' => $isYouth ? [
                'Events attended' => $user->eventRegistrations()->whereNotNull('attended_at')->count(),
                'Registered, coming up' => $user->eventRegistrations()->whereHas('event', fn ($event) => $event->upcoming())->count(),
                'Surveys answered' => $user->surveyResponses()->count(),
                'Messages to your SK' => $user->feedback()->whereNull('event_id')->count(),
            ] : [],
        ]);
    }

    /**
     * Update the name and email of the signed-in account.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $changesEmail = $request->input('email', $user->email) !== $user->email;

        if ($changesEmail && $user->role->isStaff()) {
            return back()->withErrors(['email' => 'Staff emails are managed by an administrator. Ask one to change it.']);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'email_password' => [Rule::requiredIf($changesEmail), 'nullable', 'current_password'],
        ], [
            'email.unique' => 'Another account already uses this email.',
            'email_password.required' => 'Enter your password to change your email.',
            'email_password.current_password' => 'That password isn’t right.',
        ]);

        $user->fill(Arr::only($validated, ['name', 'email']));
        $changed = array_keys($user->getDirty());

        if ($changed === []) {
            return back()->toast('Nothing to change.', 'info');
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
        AuditLog::forgetYouth($user);

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

        return redirect()->route('home')->toast('Your account and personal details were deleted.', 'warning');
    }
}
