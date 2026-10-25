<x-layouts.app title="Plans" heading="Plans" description="Your three-year CBYDP sets the youth priorities. Each year’s ABYIP turns them into PPAs with a budget.">
    <x-slot:actions>
        <a href="{{ route('officer.cbydps.create') }}" class="inline-flex items-center gap-2 rounded-[8px] bg-emerald px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-dark">
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            New CBYDP
        </a>
    </x-slot:actions>

    @forelse ($cbydps as $cbydp)
        <section class="mb-6 rounded-[12px] border border-line bg-white">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-line px-5 py-4">
                <div>
                    <h2 class="flex flex-wrap items-center gap-2 font-display text-lg font-semibold">
                        <a href="{{ route('officer.cbydps.show', $cbydp) }}" class="hover:text-emerald">CBYDP {{ $cbydp->period() }}</a>
                        <x-ui.plan-status :plan="$cbydp" />
                    </h2>
                    <p class="text-sm text-slate-muted">{{ trans_choice(':count youth priority|:count youth priorities', $cbydp->priorities_count) }}</p>
                </div>
                @if ($cbydp->isApproved())
                    <a href="{{ route('officer.abyips.create', ['cbydp' => $cbydp->id]) }}" class="rounded-[8px] border border-line px-3.5 py-2 text-sm font-semibold hover:border-navy">New ABYIP</a>
                @else
                    <a href="{{ route('officer.cbydps.show', $cbydp) }}" class="rounded-[8px] border border-line px-3.5 py-2 text-sm font-semibold hover:border-navy">Continue setting up</a>
                @endif
            </div>

            @if ($cbydp->abyips->isEmpty())
                <p class="px-5 py-5 text-sm text-slate-muted">
                    @if ($cbydp->isApproved())
                        No ABYIP yet. Start one for a year in {{ $cbydp->period() }}.
                    @else
                        ABYIPs can be started once the council approves this CBYDP.
                    @endif
                </p>
            @else
                <ul class="divide-y divide-line">
                    @foreach ($cbydp->abyips as $abyip)
                        <li class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5">
                            <a href="{{ route('officer.abyips.show', $abyip) }}" class="flex items-center gap-2 font-medium hover:text-emerald">
                                ABYIP {{ $abyip->year }}
                                <x-ui.plan-status :plan="$abyip" />
                            </a>
                            <p class="text-sm text-slate-muted">
                                {{ trans_choice(':count PPA|:count PPAs', $abyip->ppas_count) }} · ₱{{ number_format($abyip->ppas_sum_budget ?? 0) }}
                            </p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    @empty
        <section class="rounded-[12px] border border-dashed border-line bg-white p-8 text-center">
            <h2 class="font-display text-xl font-semibold">No plans yet</h2>
            <p class="mx-auto mt-2 max-w-md text-sm text-slate-muted">Start with the CBYDP: the three-year plan of youth needs and priorities your council will approve.</p>
            <a href="{{ route('officer.cbydps.create') }}" class="mt-5 inline-flex rounded-[8px] bg-emerald px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-dark">Start the CBYDP</a>
        </section>
    @endforelse
</x-layouts.app>
