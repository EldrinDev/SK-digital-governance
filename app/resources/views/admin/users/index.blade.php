@php
    use App\UserRole;
@endphp
<x-layouts.app title="Users" heading="Users" description="Everyone who can sign in, with their role and workspace.">
    <x-slot:actions>
        <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 rounded-[8px] bg-emerald px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-dark">
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Add account
        </a>
    </x-slot:actions>

    <form method="GET" class="mb-5 grid gap-3 rounded-[12px] border border-line bg-white p-4 sm:grid-cols-2 lg:grid-cols-[minmax(0,1.5fr)_repeat(3,minmax(0,1fr))_auto]">
        <label class="sr-only" for="search">Search by name or email</label>
        <input id="search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search name or email" class="rounded-[8px] border border-line px-3 py-2 text-sm outline-none focus:border-emerald focus:ring-3 focus:ring-emerald/20">

        <label class="sr-only" for="role">Role</label>
        <select id="role" name="role" class="rounded-[8px] border border-line bg-white px-3 py-2 text-sm">
            <option value="">All roles</option>
            @foreach (UserRole::cases() as $role)
                <option value="{{ $role->value }}" @selected(($filters['role'] ?? null) === $role->value)>{{ $role->label() }}</option>
            @endforeach
        </select>

        <label class="sr-only" for="barangay">Barangay</label>
        <select id="barangay" name="barangay" class="rounded-[8px] border border-line bg-white px-3 py-2 text-sm">
            <option value="">All barangays</option>
            @foreach ($barangays as $barangay)
                <option value="{{ $barangay->id }}" @selected((int) ($filters['barangay'] ?? 0) === $barangay->id)>{{ $barangay->name }}</option>
            @endforeach
        </select>

        <label class="sr-only" for="status">Status</label>
        <select id="status" name="status" class="rounded-[8px] border border-line bg-white px-3 py-2 text-sm">
            <option value="">Any status</option>
            <option value="active" @selected(($filters['status'] ?? null) === 'active')>Active</option>
            <option value="deactivated" @selected(($filters['status'] ?? null) === 'deactivated')>Deactivated</option>
        </select>

        <div class="flex gap-2">
            <button type="submit" class="rounded-[8px] bg-navy px-4 py-2 text-sm font-semibold text-white hover:bg-navy-deep">Filter</button>
            @if (array_filter($filters))
                <a href="{{ route('admin.users.index') }}" class="rounded-[8px] px-3 py-2 text-sm font-medium text-slate-muted hover:text-navy">Clear</a>
            @endif
        </div>
    </form>

    <div class="overflow-x-auto rounded-[12px] border border-line bg-white">
        <table class="w-full min-w-[40rem] text-left text-sm">
            <thead class="border-b border-line bg-mist/60 text-xs font-semibold text-slate-muted">
                <tr>
                    <th scope="col" class="px-5 py-3">Name</th>
                    <th scope="col" class="px-5 py-3">Role</th>
                    <th scope="col" class="px-5 py-3">Workspace</th>
                    <th scope="col" class="px-5 py-3">Status</th>
                    <th scope="col" class="px-5 py-3"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($users as $user)
                    <tr>
                        <td class="px-5 py-3">
                            <p class="font-medium">{{ $user->name }}</p>
                            <p class="text-xs text-slate-muted">{{ $user->email }}</p>
                        </td>
                        <td class="px-5 py-3">{{ $user->role->label() }}</td>
                        <td class="px-5 py-3 text-navy/80">{{ $user->barangay?->name ?? 'City-wide' }}</td>
                        <td class="px-5 py-3">
                            @if ($user->isActive())
                                <x-ui.badge tone="success">Active</x-ui.badge>
                            @else
                                <x-ui.badge tone="error">Deactivated</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('admin.users.edit', $user) }}" class="font-semibold text-emerald hover:underline">Edit<span class="sr-only"> {{ $user->name }}</span></a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-slate-muted">
                            No accounts match these filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
</x-layouts.app>
