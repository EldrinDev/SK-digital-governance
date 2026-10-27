<x-layouts.app title="Federation console" heading="Participating barangays" :description="'Read-only progress and compliance across the pilot, for '.$year.'.'">
    <x-slot:actions>
        <x-ui.export-menu route="federation.compliance.export" label="Export table" />
    </x-slot:actions>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat :label="'Youth participants in '.$year" :value="number_format($totals['participants'])" icon="users" :hint="'checked in at an event, of '.number_format($totals['youth']).' youth accounts in '.$barangays->count().' barangays'" />
        <x-ui.stat label="PPAs completed" :value="$totals['completionRate'].'%'" icon="check" :hint="'of '.$totals['ppas'].' PPAs, not counting cancelled'" />
        <x-ui.stat label="Delayed PPAs" :value="$totals['delayed']" icon="clock" :tone="$totals['delayed'] > 0 ? 'danger' : 'neutral'" :hint="$totals['delayed'] > 0 ? 'see the compliance table below' : 'everything on schedule'" />
        <x-ui.stat :label="'Budget in '.$year.' ABYIPs'" :value="'₱'.number_format($totals['budget'])" icon="peso" hint="approved and draft programs" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <section class="rounded-[12px] border border-line bg-white p-5" aria-labelledby="completion-heading">
            <h2 id="completion-heading" class="font-display text-lg font-semibold">PPA completion</h2>
            <p class="mb-4 text-xs text-slate-muted">Share of PPAs completed, not counting cancelled.</p>
            <x-ui.bar-list label="PPA completion by barangay" :limit="6" :items="$barangays->map(function ($barangay) {
                $open = $barangay->ppas_count - $barangay->cancelled_count;
                $rate = $open > 0 ? (int) round($barangay->completed_count / $open * 100) : 0;

                return ['label' => $barangay->name, 'value' => $rate, 'display' => $rate.'%'];
            })" />
        </section>
        <section class="rounded-[12px] border border-line bg-white p-5" aria-labelledby="budget-used-heading">
            <h2 id="budget-used-heading" class="font-display text-lg font-semibold">Budget used</h2>
            <p class="mb-4 text-xs text-slate-muted">Spending recorded against each {{ $year }} ABYIP.</p>
            <x-ui.bar-list label="Budget used by barangay" :limit="6" :items="$barangays->map(function ($barangay) {
                $used = $barangay->budget_this_year > 0 ? (int) round($barangay->spent_this_year / $barangay->budget_this_year * 100) : 0;

                return ['label' => $barangay->name, 'value' => $used, 'display' => $used.'%'];
            })" />
        </section>
        <section class="rounded-[12px] border border-line bg-white p-5" aria-labelledby="checkins-heading">
            <h2 id="checkins-heading" class="font-display text-lg font-semibold">Youth check-ins</h2>
            <p class="mb-4 text-xs text-slate-muted">Event attendance across all barangays, last six months.</p>
            <x-ui.column-chart label="Youth check-ins per month" unit="check-ins" :items="$monthlyCheckIns" />
        </section>
    </div>

    <section class="mt-6 overflow-x-auto rounded-[12px] border border-line bg-white" aria-labelledby="compliance-heading">
        <h2 id="compliance-heading" class="border-b border-line px-5 py-4 font-display text-lg font-semibold">Compliance</h2>
        <table class="w-full min-w-[56rem] text-left text-sm">
            <thead class="border-b border-line bg-mist/60 text-xs font-semibold text-slate-muted">
                <tr>
                    <th scope="col" class="px-5 py-3">Barangay</th>
                    <th scope="col" class="px-5 py-3">CBYDP</th>
                    <th scope="col" class="px-5 py-3">ABYIP {{ $year }}</th>
                    <th scope="col" class="px-5 py-3">PPAs done</th>
                    <th scope="col" class="px-5 py-3 text-right">Delayed</th>
                    <th scope="col" class="px-5 py-3 text-right">Budget</th>
                    <th scope="col" class="px-5 py-3 text-right">Sessions</th>
                    <th scope="col" class="px-5 py-3">Last remark</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($barangays as $barangay)
                    @php
                        $open = $barangay->ppas_count - $barangay->cancelled_count;
                        $rate = $open > 0 ? (int) round($barangay->completed_count / $open * 100) : 0;
                    @endphp
                    <tr>
                        <th scope="row" class="px-5 py-3 font-medium">
                            <a href="{{ route('federation.barangays.show', $barangay) }}" class="hover:text-emerald">{{ $barangay->name }}</a>
                        </th>
                        <td class="px-5 py-3">
                            @if ($barangay->approved_cbydp_count > 0)
                                <x-ui.badge tone="success">Approved</x-ui.badge>
                            @else
                                <x-ui.badge tone="error">None approved</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            @if ($barangay->approved_abyip_count > 0)
                                <x-ui.badge tone="success">Approved</x-ui.badge>
                            @elseif ($barangay->draft_abyip_count > 0)
                                <x-ui.badge tone="warning">Draft</x-ui.badge>
                            @else
                                <x-ui.badge tone="error">Not started</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <div class="h-2 w-20 overflow-hidden rounded-full bg-mist" aria-hidden="true">
                                    <div class="h-full rounded-full bg-emerald" style="width: {{ $rate }}%"></div>
                                </div>
                                <span class="text-xs text-slate-muted">{{ $barangay->completed_count }} of {{ $open }}</span>
                            </div>
                        </td>
                        <td @class(['px-5 py-3 text-right tabular-nums', 'font-semibold text-[#b91c1c]' => $barangay->delayed_count > 0])>{{ $barangay->delayed_count }}</td>
                        <td class="whitespace-nowrap px-5 py-3 text-right tabular-nums">₱{{ number_format($barangay->budget_this_year ?? 0) }}</td>
                        <td class="px-5 py-3 text-right tabular-nums">{{ $barangay->meetings_this_year_count }}</td>
                        <td class="whitespace-nowrap px-5 py-3 text-xs text-slate-muted">
                            {{ $barangay->last_remark_at ? \Illuminate\Support\Carbon::parse($barangay->last_remark_at)->diffForHumans() : 'None' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-10 text-center text-slate-muted">No barangays take part yet. The administrator turns them on under Barangays.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <section class="mt-6 rounded-[12px] border border-line bg-white" aria-labelledby="remarks-heading">
        <h2 id="remarks-heading" class="border-b border-line px-5 py-4 font-display text-lg font-semibold">Latest monitoring remarks</h2>
        <ul class="divide-y divide-line">
            @forelse ($latestRemarks as $remark)
                <li class="px-5 py-3.5">
                    <p class="text-sm">{{ $remark->body }}</p>
                    <p class="mt-1 text-xs text-slate-muted">
                        <a href="{{ route('federation.barangays.show', $remark->barangay) }}" class="font-semibold text-emerald hover:underline">{{ $remark->barangay->name }}</a>
                        · {{ $remark->author?->name ?? 'SK Federation' }} · {{ $remark->created_at->diffForHumans() }}
                    </p>
                </li>
            @empty
                <li class="px-5 py-6 text-sm text-slate-muted">No remarks yet. Open a barangay to leave one.</li>
            @endforelse
        </ul>
    </section>
</x-layouts.app>
