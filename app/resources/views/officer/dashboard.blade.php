@php
    $hour = (int) now()->format('G');
    $greeting = $hour < 12 ? 'Magandang umaga' : ($hour < 18 ? 'Magandang hapon' : 'Magandang gabi');
@endphp
<x-layouts.app title="Overview" :heading="$greeting.', '.str(auth()->user()->name)->before(' ').'!'" :description="now()->format('l, F j').'. Here’s where '.$barangay->name.'’s youth programs stand.'">
    <x-slot:actions>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('officer.announcements.create') }}" class="inline-flex items-center gap-2 rounded-[8px] bg-emerald px-3.5 py-2 text-sm font-semibold text-white hover:bg-emerald-dark">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 11 18-5v12L3 14v-3Z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></svg>
                Post announcement
            </a>
            <a href="{{ route('officer.events.create') }}" class="inline-flex items-center gap-2 rounded-[8px] border border-line bg-white px-3.5 py-2 text-sm font-semibold hover:border-navy">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                Post event
            </a>
            <a href="{{ route('officer.meetings.create') }}" class="inline-flex items-center gap-2 rounded-[8px] border border-line bg-white px-3.5 py-2 text-sm font-semibold hover:border-navy">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>
                Record session
            </a>
        </div>
    </x-slot:actions>
    @if ($remarks->isNotEmpty())
        <section class="mb-6 rounded-[12px] border border-info/30 bg-info/5 p-5" aria-labelledby="remarks-heading">
            <h2 id="remarks-heading" class="flex items-center gap-2 font-display text-base font-semibold">
                <svg viewBox="0 0 24 24" class="h-5 w-5 text-info" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a8 8 0 0 1-11.6 7.1L4 20.5l1.4-4.8A8 8 0 1 1 21 12Z"/></svg>
                Remarks from the SK Federation
            </h2>
            <ul class="mt-3 space-y-3 text-sm">
                @foreach ($remarks as $remark)
                    <li>
                        <p class="leading-relaxed">{{ $remark->body }}</p>
                        <p class="mt-0.5 text-xs text-slate-muted">{{ $remark->author?->name ?? 'SK Federation' }} · {{ $remark->created_at->diffForHumans() }}</p>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

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
            <x-ui.stat label="PPAs" :value="$totalPpas" icon="clipboard" hint="across all plans" :href="route('officer.ppas.index')" />
            <x-ui.stat label="Completed" :value="$completedPpas" icon="check" :hint="$completedWithEvidence.' with evidence uploaded'" :href="route('officer.ppas.index', ['status' => 'completed'])" />
            <x-ui.stat label="Delayed" :value="$delayedPpas" icon="clock" :tone="$delayedPpas > 0 ? 'danger' : 'neutral'" :hint="$delayedPpas > 0 ? 'past their end date, still open' : 'everything on schedule'" :href="route('officer.ppas.index', ['status' => 'delayed'])" />
            <x-ui.stat :label="'Budget used, ABYIP '.$year" :value="'₱'.number_format($spentBudget)" icon="peso">
                @if ($currentAbyip)
                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-mist" role="progressbar" aria-valuenow="{{ $budgetUsedPercent }}" aria-valuemin="0" aria-valuemax="100" aria-label="Budget used">
                        <div class="h-full rounded-full bg-emerald" style="width: {{ $budgetUsedPercent }}%"></div>
                    </div>
                    <p class="mt-1 text-xs text-slate-muted tabular-nums">{{ $budgetUsedPercent }}% of ₱{{ number_format($plannedBudget) }} planned</p>
                @else
                    <p class="mt-1 text-xs text-slate-muted">No ABYIP for {{ $year }} yet.</p>
                @endif
            </x-ui.stat>
        </div>

        <div class="mt-6 grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
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
                <section class="rounded-[12px] border border-line bg-white p-5" aria-labelledby="checkins-heading">
                    <h2 id="checkins-heading" class="font-display text-lg font-semibold">Youth check-ins</h2>
                    <p class="mb-4 text-xs text-slate-muted">Event attendance, last six months.</p>
                    <x-ui.column-chart label="Youth check-ins per month" unit="check-ins" :items="$monthlyCheckIns" />
                </section>

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
