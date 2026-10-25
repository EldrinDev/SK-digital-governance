<x-layouts.app title="Overview" heading="Overview" :description="'Where '.$barangay->name.'’s youth programs stand today.'">
    @if ($totalPpas === 0)
        <section class="rounded-[12px] border border-dashed border-line bg-white p-8 text-center">
            <h2 class="font-display text-xl font-semibold">Start with your three-year plan</h2>
            <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-slate-muted">
                Set up the CBYDP with your youth priorities, record the council’s approval, then build this year’s ABYIP and its PPAs.
            </p>
            <a href="{{ route('officer.plans.index') }}" class="mt-5 inline-flex rounded-[8px] bg-emerald px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-dark">Go to plans</a>
        </section>
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-[12px] border border-line bg-white p-5">
                <p class="text-sm text-slate-muted">Total PPAs</p>
                <p class="mt-2 font-display text-3xl font-semibold">{{ $totalPpas }}</p>
            </div>
            <div class="rounded-[12px] border border-line bg-white p-5">
                <p class="text-sm text-slate-muted">Completed</p>
                <p class="mt-2 font-display text-3xl font-semibold">{{ $completedPpas }}</p>
                <p class="mt-1 text-xs text-slate-muted">{{ $completedWithEvidence }} with evidence uploaded</p>
            </div>
            <a href="{{ route('officer.ppas.index', ['status' => 'delayed']) }}" @class([
                'rounded-[12px] border bg-white p-5 transition-colors',
                'border-error/40 hover:border-error' => $delayedPpas > 0,
                'border-line hover:border-navy/30' => $delayedPpas === 0,
            ])>
                <p class="text-sm text-slate-muted">Delayed</p>
                <p @class(['mt-2 font-display text-3xl font-semibold', 'text-[#b91c1c]' => $delayedPpas > 0])>{{ $delayedPpas }}</p>
            </a>
            <div class="rounded-[12px] border border-line bg-white p-5">
                <p class="text-sm text-slate-muted">Budget in ABYIP {{ $year }}</p>
                <p class="mt-2 font-display text-3xl font-semibold">₱{{ number_format($plannedBudget) }}</p>
                @unless ($currentAbyip)
                    <p class="mt-1 text-xs text-slate-muted">No ABYIP for {{ $year }} yet.</p>
                @endunless
            </div>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
            <section class="rounded-[12px] border border-line bg-white">
                <div class="flex items-center justify-between gap-4 border-b border-line px-5 py-4">
                    <h2 class="font-display text-lg font-semibold">Open PPAs, by end date</h2>
                    <a href="{{ route('officer.ppas.index') }}" class="text-sm font-semibold text-emerald hover:underline">All PPAs</a>
                </div>
                @if ($activePpas->isEmpty())
                    <p class="px-5 py-8 text-sm text-slate-muted">No open PPAs. Everything planned is completed or cancelled.</p>
                @else
                    <ul class="divide-y divide-line">
                        @foreach ($activePpas as $ppa)
                            <li class="px-5 py-4">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <a href="{{ route('officer.ppas.show', $ppa) }}" class="font-medium hover:text-emerald">{{ $ppa->title }}</a>
                                    <x-ui.ppa-status :ppa="$ppa" />
                                </div>
                                <div class="mt-2 flex items-center gap-3">
                                    <div class="h-2 flex-1 overflow-hidden rounded-full bg-mist" role="progressbar" aria-valuenow="{{ $ppa->progress() }}" aria-valuemin="0" aria-valuemax="100" aria-label="Milestones done">
                                        <div class="h-full rounded-full bg-emerald" style="width: {{ $ppa->progress() }}%"></div>
                                    </div>
                                    <span class="w-24 text-right text-xs text-slate-muted">Ends {{ $ppa->ends_on->format('M j') }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <div class="space-y-6">
                <section class="rounded-[12px] border border-line bg-white p-5">
                    <h2 class="font-display text-lg font-semibold">Upcoming milestones</h2>
                    @if ($upcomingMilestones->isEmpty())
                        <p class="mt-2 text-sm text-slate-muted">Nothing due. Add milestones to a PPA to track it here.</p>
                    @else
                        <ul class="mt-3 space-y-3 text-sm">
                            @foreach ($upcomingMilestones as $milestone)
                                <li class="flex items-start justify-between gap-3">
                                    <span>
                                        <span class="block font-medium">{{ $milestone->title }}</span>
                                        <a href="{{ route('officer.ppas.show', $milestone->ppa_id) }}" class="text-xs text-slate-muted hover:text-emerald">{{ $milestone->ppa->title }}</a>
                                    </span>
                                    <span @class(['shrink-0 text-xs font-semibold', 'text-[#b91c1c]' => $milestone->isOverdue(), 'text-slate-muted' => ! $milestone->isOverdue()])>
                                        {{ $milestone->isOverdue() ? 'Overdue' : $milestone->due_on->format('M j') }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>

                <section class="rounded-[12px] border border-line bg-white p-5">
                    <h2 class="font-display text-lg font-semibold">Recent activity</h2>
                    <ul class="mt-3 space-y-2.5 text-sm">
                        @forelse ($recentActivity as $entry)
                            <li>
                                <span class="font-medium">{{ $entry->user?->name ?? 'Someone' }}</span>
                                <span class="text-navy/70">{{ Str::lcfirst($entry->label()) }}</span>
                                <span class="block text-xs text-slate-muted">{{ $entry->created_at->diffForHumans() }}</span>
                            </li>
                        @empty
                            <li class="text-slate-muted">No changes yet.</li>
                        @endforelse
                    </ul>
                </section>
            </div>
        </div>
    @endif
</x-layouts.app>
