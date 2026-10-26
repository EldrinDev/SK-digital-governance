<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\AuditLog;
use App\Models\Barangay;
use App\Models\User;
use App\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * List accounts, with search and filters.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', Rule::enum(UserRole::class)],
            'barangay' => ['nullable', 'integer'],
            'status' => ['nullable', Rule::in(['active', 'deactivated'])],
        ]);

        $users = User::query()
            ->with('barangay:id,name')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(fn ($inner) => $inner
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            })
            ->when($filters['role'] ?? null, fn ($query, string $role) => $query->where('role', $role))
            ->when($filters['barangay'] ?? null, fn ($query, int $barangay) => $query->where('barangay_id', $barangay))
            ->when(($filters['status'] ?? null) === 'active', fn ($query) => $query->whereNull('deactivated_at'))
            ->when(($filters['status'] ?? null) === 'deactivated', fn ($query) => $query->whereNotNull('deactivated_at'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'filters' => $filters,
            'barangays' => Barangay::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Show the form for a new staff or youth account.
     */
    public function create(): View
    {
        return view('admin.users.create', [
            'barangays' => Barangay::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Create the account and email the person a link to set their password.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = new User([
            ...$request->safe()->only(['name', 'email']),
            'barangay_id' => $request->barangayId(),
            'password' => Str::password(32),
        ]);
        $user->role = $request->selectedRole();
        $user->position = $request->selectedPosition();
        $user->save();

        AuditLog::record('user.created', $request->user(), [
            'email' => $user->email,
            'role' => $user->role->value,
        ], $user);

        Password::sendResetLink(['email' => $user->email]);

        return redirect()->route('admin.users.index')
            ->with('status', "{$user->name}’s account was created. We emailed them a link to set their password.");
    }

    /**
     * Show the form for changing an account.
     */
    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user,
            'barangays' => Barangay::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Save changes to an account's details, role, workspace and status.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $wasActive = $user->isActive();

        $user->fill($request->safe()->only(['name', 'email']));
        $user->role = $request->selectedRole();
        $user->barangay_id = $request->barangayId();
        $user->position = $request->selectedPosition();
        $user->deactivated_at = $request->boolean('is_active') ? null : ($user->deactivated_at ?? now());

        $changes = array_keys($user->getDirty());
        $user->save();

        if ($changes !== []) {
            AuditLog::record('user.updated', $request->user(), ['email' => $user->email, 'changed' => $changes], $user);
        }

        if ($wasActive !== $user->isActive()) {
            AuditLog::record($user->isActive() ? 'user.reactivated' : 'user.deactivated', $request->user(), ['email' => $user->email], $user);
        }

        return redirect()->route('admin.users.index')->with('status', "{$user->name}’s account was updated.");
    }
}
