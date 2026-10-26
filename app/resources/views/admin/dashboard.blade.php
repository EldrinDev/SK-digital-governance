<x-layouts.app title="Administration" heading="Administration" description="Accounts, workspaces, and recent activity across the platform.">
    <x-slot:actions>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 rounded-[8px] bg-emerald px-3.5 py-2 text-sm font-semibold text-white hover:bg-emerald-dark">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="4"/><path d="M2 21a7 7 0 0 1 14 0M19 8v6M22 11h-6"/></svg>
                Add an account
            </a>
            <a href="{{ route('admin.backups.index') }}" class="inline-flex items-center gap-2 rounded-[8px] border border-line bg-white px-3.5 py-2 text-sm font-semibold hover:border-navy">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v6c0 1.7 3.6 3 8 3s8-1.3 8-3V5"/><path d="M4 11v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6"/></svg>
                Backups
            </a>
        </div>
    </x-slot:actions>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($accountsByRole as $label => $total)
            <x-ui.stat :label="$label" :value="number_format($total)" :icon="['user', 'users', 'building', 'shield'][$loop->index] ?? 'users'" :href="route('admin.users.index')" />
        @endforeach
    </div>

    <div class="mt-6 grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <section class="rounded-[12px] border border-line bg-white">
            <div class="flex items-center justify-between gap-4 border-b border-line px-5 py-4">
                <h2 class="font-display text-lg font-semibold">Recent activity</h2>
                <a href="{{ route('admin.audit-logs.index') }}" class="text-sm font-semibold text-emerald hover:underline">See full audit trail</a>
            </div>
            @if ($recentEntries->isEmpty())
                <p class="px-5 py-8 text-sm text-slate-muted">Nothing has been recorded yet.</p>
            @else
                <ul class="divide-y divide-line">
                    @foreach ($recentEntries as $entry)
                        <li class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 px-5 py-3 text-sm">
                            <p>
                                <span class="font-medium">{{ $entry->user?->name ?? ($entry->properties['email'] ?? 'Unknown account') }}</span>
                                <span class="text-navy/70">· {{ $entry->label() }}</span>
                                @if ($entry->barangay)
                                    <span class="text-slate-muted">· {{ $entry->barangay->name }}</span>
                                @endif
                            </p>
                            <time datetime="{{ $entry->created_at->toIso8601String() }}" class="text-xs text-slate-muted">{{ $entry->created_at->diffForHumans() }}</time>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <div class="space-y-6">
            <section class="rounded-[12px] border border-line bg-white p-5">
                <h2 class="font-display text-lg font-semibold">Participating barangays</h2>
                @if ($participatingBarangays->isEmpty())
                    <p class="mt-2 text-sm text-slate-muted">None yet. Turn on the pilot barangays so their officers and youth can sign in.</p>
                @else
                    <p class="mt-1 text-sm text-slate-muted">{{ $participatingBarangays->count() }} of {{ $barangayCount }} can sign in and register.</p>
                    <p class="mt-3 text-sm leading-relaxed">{{ $participatingBarangays->implode(', ') }}</p>
                @endif
                <a href="{{ route('admin.barangays.index') }}" class="mt-4 inline-block text-sm font-semibold text-emerald hover:underline">Manage barangays</a>
            </section>

            <section class="rounded-[12px] border border-line bg-white p-5 text-sm">
                <h2 class="font-display text-lg font-semibold">Watch list</h2>
                <dl class="mt-3 space-y-2">
                    <div class="flex justify-between gap-4"><dt class="text-slate-muted">Failed logins today</dt><dd class="font-semibold">{{ $failedLoginsToday }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-muted">Deactivated accounts</dt><dd class="font-semibold">{{ $deactivatedCount }}</dd></div>
                </dl>
            </section>
        </div>
    </div>
</x-layouts.app>
