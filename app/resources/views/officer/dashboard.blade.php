@php
    $hour = (int) now()->format('G');
    $greeting = $hour < 12 ? 'Magandang umaga' : ($hour < 18 ? 'Magandang hapon' : 'Magandang gabi');
    $peso = fn (float $amount): string => '₱'.number_format($amount);
    $hasCheckIns = collect($monthlyCheckIns)->sum('value') > 0;
    $figures = [
        ['value' => $totalPpas, 'label' => 'PPAs across all plans', 'href' => route('officer.ppas.index')],
        ['value' => $completedPpas.' of '.$totalPpas, 'label' => 'completed, '.$completedWithEvidence.' with evidence', 'href' => route('officer.ppas.index', ['status' => 'completed'])],
        ['value' => $delayedPpas, 'label' => $delayedPpas > 0 ? ($delayedPpas === 1 ? 'PPA past its end date' : 'PPAs past their end date') : 'delayed, all on schedule', 'href' => route('officer.ppas.index', ['status' => 'delayed']), 'alert' => $delayedPpas > 0],
        ['value' => $peso($spentBudget), 'label' => $currentAbyip ? 'spent of '.$peso($plannedBudget).' in ABYIP '.$year : 'spent, no ABYIP for '.$year.' yet', 'href' => $currentAbyip ? route('officer.abyips.show', $currentAbyip) : null, 'meter' => $currentAbyip && $plannedBudget > 0 ? $budgetUsedPercent : null],
    ];
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
                        <p class="mt-0.5 text-xs text-slate-muted">{{ $remark->author?->name ?? 'SK Federation' }}, {{ $remark->created_at->diffForHumans() }}</p>
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
        {{-- The one loud element: what the council should do next, each with the button that does it. --}}
        <section class="overflow-hidden rounded-[16px] bg-navy-deep text-white" aria-labelledby="next-heading">
            @if ($nextSteps === [])
                <div class="flex items-center gap-4 px-6 py-5">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald/25 text-[#6ee7b7]">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                    </span>
                    <div>
                        <h2 id="next-heading" class="font-display text-lg font-semibold">Nothing needs you right now</h2>
                        <p class="text-sm text-white/65">Plans are approved, PPAs are on schedule and every message has a reply.</p>
                    </div>
                </div>
            @else
                <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 px-6 pb-2 pt-5">
                    <h2 id="next-heading" class="font-display text-lg font-semibold">Next up for SK {{ $barangay->name }}</h2>
                    <p class="text-sm text-white/60">{{ trans_choice(':count thing needs your attention|:count things need your attention', count($nextSteps)) }}</p>
                </div>
                <ul>
                    @foreach ($nextSteps as $step)
                        <li class="flex flex-col gap-3 border-t border-white/10 px-6 py-4 first:border-t-0 sm:flex-row sm:items-center sm:gap-5">
                            <span @class([
                                'hidden h-2.5 w-2.5 shrink-0 rounded-full sm:block',
                                'bg-[#f87171]' => $step['urgent'],
                                'bg-gold' => ! $step['urgent'] && $loop->first,
                                'bg-white/30' => ! $step['urgent'] && ! $loop->first,
                            ]) aria-hidden="true"></span>
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold">
                                    @if ($step['urgent'])<span class="sr-only">Urgent: </span>@endif{{ $step['text'] }}
                                </p>
                                <p class="mt-0.5 text-sm leading-relaxed text-white/65">{{ $step['detail'] }}</p>
                            </div>
                            <a href="{{ $step['href'] }}" @class([
                                'inline-flex shrink-0 items-center justify-center gap-1.5 self-start rounded-[8px] px-3.5 py-2 text-sm font-semibold transition-colors sm:self-auto',
                                'bg-gold text-navy-deep hover:bg-[#f0c766]' => $loop->first,
                                'bg-white/10 text-white hover:bg-white/[0.16]' => ! $loop->first,
                            ])>
                                {{ $step['action'] }}
                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 6 6 6-6 6"/></svg>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <dl class="mt-6 grid grid-cols-2 gap-y-5 border-y border-line py-5 lg:grid-cols-4">
            @foreach ($figures as $figure)
                <div @class(['px-1 lg:px-5', 'lg:border-l lg:border-line' => ! $loop->first, 'lg:pl-1' => $loop->first])>
                    <dt class="sr-only">{{ $figure['label'] }}</dt>
                    <dd @class(['font-display text-2xl font-semibold tabular-nums', 'text-[#b91c1c]' => $figure['alert'] ?? false, 'text-navy' => ! ($figure['alert'] ?? false)])>
                        @if ($figure['href'])
                            <a href="{{ $figure['href'] }}" class="hover:underline hover:decoration-2 hover:underline-offset-4">{{ $figure['value'] }}</a>
                        @else
                            {{ $figure['value'] }}
                        @endif
                    </dd>
                    <dd class="text-sm text-slate-muted">{{ $figure['label'] }}</dd>
                    @isset($figure['meter'])
                        <dd class="mt-2 h-1 max-w-48 overflow-hidden rounded-full bg-line" role="progressbar" aria-valuenow="{{ $figure['meter'] }}" aria-valuemin="0" aria-valuemax="100" aria-label="Share of the budget spent">
                            <span class="block h-full rounded-full bg-emerald" style="width: {{ $figure['meter'] }}%"></span>
                        </dd>
                    @endisset
                </div>
            @endforeach
        </dl>

        {{-- Two columns on wide screens; on phones the sections stack in order of use. --}}
        <div class="mt-6 grid grid-cols-1 items-start gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
            <div class="max-lg:contents lg:space-y-6">
                <section class="rounded-[12px] border border-line bg-white max-lg:order-1" aria-labelledby="open-heading">
                    <div class="flex items-center justify-between gap-4 border-b border-line px-5 py-4">
                        <h2 id="open-heading" class="font-display text-lg font-semibold">Open PPAs, by end date</h2>
                        <a href="{{ route('officer.ppas.index') }}" class="text-sm font-semibold text-emerald hover:underline">All PPAs</a>
                    </div>
                    @if ($activePpas->isEmpty())
                        <p class="px-5 py-8 text-sm text-slate-muted">No open PPAs. Everything planned is completed or cancelled.</p>
                    @else
                        <ul class="divide-y divide-line">
                            @foreach ($activePpas as $ppa)
                                @php
                                    $isDelayed = $ppa->isDelayed();
                                @endphp
                                <li>
                                    <a href="{{ route('officer.ppas.show', $ppa) }}" class="group grid grid-cols-[minmax(0,1fr)_auto] items-center gap-x-4 gap-y-2 px-5 py-4 hover:bg-mist/60">
                                        <span class="min-w-0">
                                            <span class="block truncate font-medium group-hover:text-emerald">{{ $ppa->title }}</span>
                                            <span class="block text-xs text-slate-muted">ABYIP {{ $ppa->abyip->year }}, {{ $ppa->status->label() }}</span>
                                        </span>
                                        <span @class(['text-right text-sm tabular-nums', 'font-semibold text-[#b91c1c]' => $isDelayed, 'text-slate-muted' => ! $isDelayed])>
                                            {{ $isDelayed ? 'Ended '.$ppa->ends_on->format('M j') : 'Ends '.$ppa->ends_on->format('M j') }}
                                        </span>
                                        <span class="col-span-2 flex items-center gap-3">
                                            <span class="h-1.5 flex-1 overflow-hidden rounded-full bg-mist" role="progressbar" aria-valuenow="{{ $ppa->progress() }}" aria-valuemin="0" aria-valuemax="100" aria-label="Milestones done">
                                                <span @class(['block h-full rounded-full', 'bg-[#dc2626]' => $isDelayed, 'bg-emerald' => ! $isDelayed]) style="width: {{ $ppa->progress() }}%"></span>
                                            </span>
                                            <span class="w-10 text-right text-xs tabular-nums text-slate-muted">{{ $ppa->progress() }}%</span>
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            </div>

            <div class="max-lg:contents lg:space-y-6">
                <section class="rounded-[12px] border border-line bg-white max-lg:order-2" aria-labelledby="agenda-heading">
                    <div class="border-b border-line px-5 py-4">
                        <h2 id="agenda-heading" class="font-display text-lg font-semibold">Coming up</h2>
                        <p class="text-xs text-slate-muted">Events, council sessions and PPA milestones.</p>
                    </div>
                    @if ($agenda->isEmpty())
                        <p class="px-5 py-6 text-sm text-slate-muted">Nothing scheduled. Post an event, record a session, or add milestones to a PPA.</p>
                    @else
                        <ul class="divide-y divide-line">
                            @foreach ($agenda as $item)
                                <li>
                                    <a href="{{ $item['href'] }}" class="group flex items-center gap-4 px-5 py-3 hover:bg-mist/60">
                                        <span @class(['flex w-10 shrink-0 flex-col items-center leading-none', 'text-[#b91c1c]' => $item['overdue']])>
                                            <span @class(['text-xs', 'text-slate-muted' => ! $item['overdue']])>{{ $item['date']->format('M') }}</span>
                                            <span class="mt-1 font-display text-lg font-semibold tabular-nums">{{ $item['date']->format('j') }}</span>
                                        </span>
                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate text-sm font-medium group-hover:text-emerald">{{ $item['title'] }}</span>
                                            <span @class(['block truncate text-xs', 'font-medium text-[#b91c1c]' => $item['overdue'], 'text-slate-muted' => ! $item['overdue']])>
                                                {{ $item['overdue'] ? 'Overdue. ' : '' }}{{ $item['detail'] }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>

                <section class="rounded-[12px] border border-line bg-white p-5 max-lg:order-3" aria-labelledby="checkins-heading">
                    <h2 id="checkins-heading" class="font-display text-lg font-semibold">Youth check-ins</h2>
                    @if ($hasCheckIns)
                        <p class="mb-4 text-xs text-slate-muted">Event attendance, last six months.</p>
                        <x-ui.column-chart label="Youth check-ins per month" unit="check-ins" :items="$monthlyCheckIns" />
                    @else
                        <p class="mt-2 text-sm text-slate-muted">No one has checked in to an event in the last six months. Post an event, then scan the youth’s digital IDs at the door.</p>
                    @endif
                </section>
            </div>
        </div>
    @endif
</x-layouts.app>
