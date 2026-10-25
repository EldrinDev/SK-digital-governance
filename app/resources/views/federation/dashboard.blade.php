<x-layouts.app title="Federation console" heading="Participating barangays" :description="'Read-only progress and compliance across the pilot, for '.$year.'.'">
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-[12px] border border-line bg-white p-5">
            <p class="text-sm text-slate-muted">Barangays</p>
            <p class="mt-2 font-display text-3xl font-semibold">{{ $barangays->count() }}</p>
        </div>
        <div class="rounded-[12px] border border-line bg-white p-5">
            <p class="text-sm text-slate-muted">PPAs completed</p>
            <p class="mt-2 font-display text-3xl font-semibold">{{ $totals['completionRate'] }}%</p>
            <p class="mt-1 text-xs text-slate-muted">of {{ $totals['ppas'] }} PPAs, not counting cancelled</p>
        </div>
        <div @class(['rounded-[12px] border bg-white p-5', 'border-error/40' => $totals['delayed'] > 0, 'border-line' => $totals['delayed'] === 0])>
            <p class="text-sm text-slate-muted">Delayed PPAs</p>
            <p @class(['mt-2 font-display text-3xl font-semibold', 'text-[#b91c1c]' => $totals['delayed'] > 0])>{{ $totals['delayed'] }}</p>
        </div>
        <div class="rounded-[12px] border border-line bg-white p-5">
            <p class="text-sm text-slate-muted">Budget in {{ $year }} ABYIPs</p>
            <p class="mt-2 font-display text-3xl font-semibold">₱{{ number_format($totals['budget']) }}</p>
        </div>
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
</x-layouts.app>
