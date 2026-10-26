@php
    use App\Models\AuditLog;
@endphp
<x-layouts.app title="Audit trail" heading="Audit trail" description="What was done, when, and by whom. Entries can’t be edited or deleted.">
    <x-slot:actions>
        <a href="{{ route('admin.audit-logs.export', request()->query()) }}" class="inline-flex items-center gap-2 rounded-[8px] border border-line bg-white px-4 py-2.5 text-sm font-semibold hover:border-navy">
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/></svg>
            Download CSV
        </a>
    </x-slot:actions>

    <form method="GET" class="mb-5 grid gap-3 rounded-[12px] border border-line bg-white p-4 sm:grid-cols-2 lg:grid-cols-[minmax(0,1.3fr)_repeat(2,minmax(0,1fr))_repeat(2,minmax(0,0.8fr))_auto]">
        <label class="sr-only" for="search">Search by person</label>
        <input id="search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search person or email" class="rounded-[8px] border border-line px-3 py-2 text-sm outline-none focus:border-emerald focus:ring-3 focus:ring-emerald/20">

        <label class="sr-only" for="action">Action</label>
        <select id="action" name="action" class="rounded-[8px] border border-line bg-white px-3 py-2 text-sm">
            <option value="">All actions</option>
            @foreach (AuditLog::ACTIONS as $value => $label)
                <option value="{{ $value }}" @selected(($filters['action'] ?? null) === $value)>{{ $label }}</option>
            @endforeach
        </select>

        <label class="sr-only" for="barangay">Barangay</label>
        <select id="barangay" name="barangay" class="rounded-[8px] border border-line bg-white px-3 py-2 text-sm">
            <option value="">All barangays</option>
            @foreach ($barangays as $barangay)
                <option value="{{ $barangay->id }}" @selected((int) ($filters['barangay'] ?? 0) === $barangay->id)>{{ $barangay->name }}</option>
            @endforeach
        </select>

        <label class="sr-only" for="from">From date</label>
        <input id="from" name="from" type="date" value="{{ $filters['from'] ?? '' }}" class="rounded-[8px] border border-line px-3 py-2 text-sm">

        <label class="sr-only" for="to">To date</label>
        <input id="to" name="to" type="date" value="{{ $filters['to'] ?? '' }}" class="rounded-[8px] border border-line px-3 py-2 text-sm">

        <div class="flex gap-2">
            <button type="submit" class="rounded-[8px] bg-navy px-4 py-2 text-sm font-semibold text-white hover:bg-navy-deep">Filter</button>
            @if (array_filter($filters))
                <a href="{{ route('admin.audit-logs.index') }}" class="rounded-[8px] px-3 py-2 text-sm font-medium text-slate-muted hover:text-navy">Clear</a>
            @endif
        </div>
        @error('to')
            <p class="text-sm text-error sm:col-span-full">{{ $message }}</p>
        @enderror
    </form>

    <div class="overflow-x-auto rounded-[12px] border border-line bg-white">
        <table class="w-full min-w-[48rem] text-left text-sm">
            <thead class="border-b border-line bg-mist/60 text-xs font-semibold text-slate-muted">
                <tr>
                    <th scope="col" class="px-5 py-3">When</th>
                    <th scope="col" class="px-5 py-3">Who</th>
                    <th scope="col" class="px-5 py-3">Action</th>
                    <th scope="col" class="px-5 py-3">Barangay</th>
                    <th scope="col" class="px-5 py-3">Details</th>
                    <th scope="col" class="px-5 py-3">IP address</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($entries as $entry)
                    <tr class="align-top">
                        <td class="whitespace-nowrap px-5 py-3">
                            <time datetime="{{ $entry->created_at->toIso8601String() }}">{{ $entry->created_at->format('M j, Y g:i A') }}</time>
                        </td>
                        <td class="px-5 py-3">
                            @if ($entry->user)
                                <p class="font-medium">{{ $entry->user->name }}</p>
                                <p class="text-xs text-slate-muted">{{ $entry->user->email }}</p>
                            @else
                                <span class="text-slate-muted">Unknown account</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <x-ui.badge :tone="in_array($entry->action, ['auth.failed', 'auth.lockout', 'user.deactivated'], true) ? 'error' : 'neutral'">{{ $entry->label() }}</x-ui.badge>
                        </td>
                        <td class="px-5 py-3 text-navy/80">{{ $entry->barangay?->name ?? '—' }}</td>
                        <td class="max-w-xs px-5 py-3 text-xs text-navy/70">
                            @foreach ($entry->properties ?? [] as $key => $value)
                                <span class="block"><span class="text-slate-muted">{{ Str::headline($key) }}:</span> {{ is_array($value) ? implode(', ', $value) : ($value ?? '—') }}</span>
                            @endforeach
                        </td>
                        <td class="whitespace-nowrap px-5 py-3 text-xs text-slate-muted">{{ $entry->ip_address }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-slate-muted">No entries match these filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $entries->links() }}</div>
</x-layouts.app>
