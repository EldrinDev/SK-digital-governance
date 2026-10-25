<x-layouts.app title="Administration" heading="Administration" description="Accounts, workspaces, and recent activity across the platform.">
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($accountsByRole as $label => $total)
            <div class="rounded-[12px] border border-line bg-white p-5">
                <p class="text-sm text-slate-muted">{{ $label }}</p>
                <p class="mt-2 font-display text-3xl font-semibold">{{ number_format($total) }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
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
