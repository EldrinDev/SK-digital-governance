@php
    $hour = (int) now()->format('G');
    $greeting = $hour < 12 ? 'Magandang umaga' : ($hour < 18 ? 'Magandang hapon' : 'Magandang gabi');
    $peso = fn (float $amount): string => '₱'.number_format($amount);
    $newItems = [
        ['label' => 'Announcement', 'hint' => 'News, a paalala or a pasasalamat', 'href' => route('officer.announcements.create'), 'icon' => '<path d="m3 11 18-5v12L3 14v-3Z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>'],
        ['label' => 'Event', 'hint' => 'Invite the youth and take registrations', 'href' => route('officer.events.create'), 'icon' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>'],
        ['label' => 'Council session', 'hint' => 'Record attendance, minutes and resolutions', 'href' => route('officer.meetings.create'), 'icon' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/>'],
        ['label' => 'Survey', 'hint' => 'Ask the youth what they need', 'href' => route('officer.surveys.index').'#new-survey', 'icon' => '<path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/>'],
    ];
    $checkInTotal = collect($monthlyCheckIns)->sum('value');
    $hasCheckIns = $checkInTotal > 0;
    $completedPercent = $currentPpaCount > 0 ? (int) round($currentPpaCompleted / $currentPpaCount * 100) : 0;

    // This year's ABYIP against the calendar: each bar is read against the line marking today.
    $pace = [
        ['label' => 'Year so far', 'percent' => $yearElapsedPercent, 'value' => 'as of '.now()->format('M j'), 'tone' => 'bg-navy/70'],
        ['label' => 'Budget spent', 'percent' => $budgetUsedPercent, 'value' => $peso($spentBudget).' of '.$peso($plannedBudget), 'tone' => 'bg-emerald', 'phrase' => 'spending'],
        ['label' => 'PPAs completed', 'percent' => $completedPercent, 'value' => $currentPpaCompleted.' of '.$currentPpaCount, 'tone' => 'bg-emerald', 'phrase' => 'completed PPAs'],
    ];
    $behind = collect($pace)->skip(1)->filter(fn (array $bar): bool => $bar['percent'] < $yearElapsedPercent - 20)->pluck('phrase');
    $paceNote = $behind->isEmpty()
        ? 'Spending and completed PPAs are keeping up with the calendar.'
        : Str::ucfirst($behind->join(' and ')).' '.($behind->count() === 1 ? 'is' : 'are').' behind the calendar. There’s still time to catch up before December.';
    $figures = [
        ['value' => $totalPpas, 'label' => 'PPAs across all plans', 'href' => route('officer.ppas.index')],
        ['value' => $completedPpas.' of '.$totalPpas, 'label' => 'completed, '.$completedWithEvidence.' with evidence', 'href' => route('officer.ppas.index', ['status' => 'completed'])],
        ['value' => $delayedPpas, 'label' => $delayedPpas > 0 ? ($delayedPpas === 1 ? 'PPA past its end date' : 'PPAs past their end date') : 'delayed, all on schedule', 'href' => route('officer.ppas.index', ['status' => 'delayed']), 'alert' => $delayedPpas > 0],
        ['value' => $peso($spentBudget), 'label' => $currentAbyip ? 'spent of '.$peso($plannedBudget).' in ABYIP '.$year : 'spent, no ABYIP for '.$year.' yet', 'href' => $currentAbyip ? route('officer.abyips.show', $currentAbyip) : null, 'meter' => $currentAbyip && $plannedBudget > 0 ? $budgetUsedPercent : null],
    ];
@endphp
<x-layouts.app title="Overview" :heading="$greeting.', '.str(auth()->user()->name)->before(' ').'!'" :description="now()->format('l, F j').'. Here’s where '.$barangay->name.'’s youth programs stand.'">
    <x-slot:actions>
        {{-- One way in to everything an officer posts or records; each also has its own page in the menu. --}}
        <details class="relative max-sm:w-full" data-dropdown>
            <summary class="inline-flex cursor-pointer list-none items-center justify-center gap-2 rounded-[8px] bg-emerald px-4 py-2.5 max-sm:flex max-sm:w-full max-sm:py-3 text-sm font-semibold text-white hover:bg-emerald-dark [&::-webkit-details-marker]:hidden">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                New
                <svg viewBox="0 0 24 24" class="h-4 w-4 opacity-80 transition-transform [details[open]_&]:rotate-180" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
            </summary>
            <ul class="absolute right-0 z-30 mt-2 w-72 rounded-[12px] border border-line bg-white p-2 shadow-lg shadow-navy/10 max-sm:inset-x-0 max-sm:w-auto">
                @foreach ($newItems as $item)
                    <li>
                        <a href="{{ $item['href'] }}" class="flex items-start gap-3 rounded-[8px] px-3 py-2.5 hover:bg-mist">
                            <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-[8px] bg-emerald/10 text-emerald">
                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $item['icon'] !!}</svg>
                            </span>
                            <span class="leading-tight">
                                <span class="block text-sm font-semibold text-navy">{{ $item['label'] }}</span>
                                <span class="text-xs text-slate-muted">{{ $item['hint'] }}</span>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </details>
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

        @if ($currentAbyip && $currentPpaCount > 0)
            <section class="mt-6 grid gap-6 rounded-[12px] border border-line bg-white p-5 lg:grid-cols-[minmax(0,1fr)_13rem] lg:p-6" aria-labelledby="pace-heading">
                <div>
                    <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                        <h2 id="pace-heading" class="font-display text-lg font-semibold">
                            <a href="{{ route('officer.abyips.show', $currentAbyip) }}" class="hover:text-emerald">ABYIP {{ $year }} so far</a>
                        </h2>
                        <p class="flex items-center gap-2 text-xs text-slate-muted">
                            <span class="inline-block h-3 w-0.5 rounded-full bg-gold" aria-hidden="true"></span>
                            The gold line is today
                        </p>
                    </div>
                    <p class="mt-1 text-sm text-slate-muted">{{ $paceNote }}</p>

                    <dl class="mt-5 grid grid-cols-[6.5rem_minmax(0,1fr)] items-center gap-x-4 gap-y-4 sm:grid-cols-[7.5rem_minmax(0,1fr)_auto]">
                        @foreach ($pace as $bar)
                            <dt class="text-sm font-medium">{{ $bar['label'] }}</dt>
                            <dd class="relative h-2.5 rounded-full bg-mist" role="img" aria-label="{{ $bar['label'] }}: {{ $bar['percent'] }}%">
                                <span class="block h-full rounded-full {{ $bar['tone'] }}" style="width: {{ $bar['percent'] }}%"></span>
                                <span class="absolute -inset-y-1.5 w-0.5 -translate-x-1/2 rounded-full bg-gold" style="left: {{ $yearElapsedPercent }}%" aria-hidden="true"></span>
                            </dd>
                            <dd class="col-start-2 -mt-3 whitespace-nowrap text-xs tabular-nums text-slate-muted sm:col-start-auto sm:mt-0 sm:text-sm">
                                <span class="inline-block w-10 font-semibold text-navy">{{ $bar['percent'] }}%</span>
                                <span class="ml-1">{{ $bar['value'] }}</span>
                            </dd>
                        @endforeach
                    </dl>
                </div>

                <dl class="grid grid-cols-2 gap-4 border-t border-line pt-5 lg:grid-cols-1 lg:content-center lg:border-l lg:border-t-0 lg:pl-6 lg:pt-0">
                    <div>
                        <dd class="font-display text-2xl font-semibold tabular-nums">
                            <a href="{{ route('officer.ppas.index') }}" class="hover:text-emerald">{{ $totalPpas }}</a>
                        </dd>
                        <dt class="text-sm text-slate-muted">PPAs across all plans</dt>
                    </div>
                    <div>
                        <dd @class(['font-display text-2xl font-semibold tabular-nums', 'text-[#b91c1c]' => $delayedPpas > 0])>
                            <a href="{{ route('officer.ppas.index', ['status' => 'delayed']) }}" class="hover:underline hover:underline-offset-4">{{ $delayedPpas }}</a>
                        </dd>
                        <dt class="text-sm text-slate-muted">{{ $delayedPpas === 1 ? 'PPA past its end date' : 'PPAs past their end date' }}</dt>
                    </div>
                </dl>
            </section>
        @else
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
        @endif

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
                                    <a href="{{ route('officer.ppas.show', $ppa) }}" class="group flex flex-col gap-2 px-5 py-3.5 hover:bg-mist/60 sm:flex-row sm:items-center sm:gap-6">
                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate font-medium group-hover:text-emerald">{{ $ppa->title }}</span>
                                            <span class="block text-xs text-slate-muted">ABYIP {{ $ppa->abyip->year }}, {{ $ppa->status->label() }}</span>
                                        </span>
                                        <span class="flex shrink-0 items-center gap-3">
                                            <span class="h-1.5 w-20 overflow-hidden rounded-full bg-mist" role="progressbar" aria-valuenow="{{ $ppa->progress() }}" aria-valuemin="0" aria-valuemax="100" aria-label="Milestones done">
                                                <span @class(['block h-full rounded-full', 'bg-[#dc2626]' => $isDelayed, 'bg-emerald' => ! $isDelayed]) style="width: {{ $ppa->progress() }}%"></span>
                                            </span>
                                            <span class="w-9 text-xs tabular-nums text-slate-muted">{{ $ppa->progress() }}%</span>
                                            <span @class(['w-24 text-right text-sm tabular-nums', 'font-semibold text-[#b91c1c]' => $isDelayed, 'text-slate-muted' => ! $isDelayed])>
                                                {{ $isDelayed ? 'Ended '.$ppa->ends_on->format('M j') : 'Ends '.$ppa->ends_on->format('M j') }}
                                            </span>
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
                    <div class="flex items-start justify-between gap-3 border-b border-line px-5 py-4">
                        <div>
                            <h2 id="agenda-heading" class="font-display text-lg font-semibold">Coming up</h2>
                            <p class="text-xs text-slate-muted">Events, sessions and milestones.</p>
                        </div>
                        <div class="flex shrink-0 gap-1">
                            <a href="{{ route('officer.events.create') }}" class="rounded-[6px] px-2 py-1 text-xs font-semibold text-emerald hover:bg-emerald/10">+ Event</a>
                            <a href="{{ route('officer.meetings.create') }}" class="rounded-[6px] px-2 py-1 text-xs font-semibold text-emerald hover:bg-emerald/10">+ Session</a>
                        </div>
                    </div>
                    @if ($agenda->isEmpty())
                        <p class="px-5 py-6 text-sm leading-relaxed text-slate-muted">
                            Nothing scheduled.
                            <a href="{{ route('officer.events.create') }}" class="font-semibold text-emerald hover:underline">Post an event</a>,
                            <a href="{{ route('officer.meetings.create') }}" class="font-semibold text-emerald hover:underline">record a session</a>,
                            or <a href="{{ route('officer.ppas.index') }}" class="font-semibold text-emerald hover:underline">add milestones to a PPA</a>.
                        </p>
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
                        <p class="mb-4 text-sm text-slate-muted"><span class="font-semibold text-navy">{{ $checkInTotal }}</span> {{ $checkInTotal === 1 ? 'youth checked in' : 'youth check-ins' }} at events in the last six months.</p>
                        <x-ui.column-chart label="Youth check-ins per month" unit="check-ins" :items="$monthlyCheckIns" />
                    @else
                        <p class="mt-2 text-sm text-slate-muted">No one has checked in to an event in the last six months. Post an event, then scan the youth’s digital IDs at the door.</p>
                    @endif
                </section>
            </div>
        </div>
    @endif
</x-layouts.app>
