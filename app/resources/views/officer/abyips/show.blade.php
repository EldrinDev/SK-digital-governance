@php
    use App\PpaStatus;
    use Illuminate\Support\Carbon;

    $steps = [
        ['label' => 'CBYDP approved', 'done' => $abyip->cbydp->isApproved()],
        ['label' => 'PPAs listed', 'done' => $abyip->ppas->isNotEmpty()],
        ['label' => 'Council approval', 'done' => $abyip->isApproved()],
        ['label' => 'Implementation', 'done' => $abyip->isApproved() && $abyip->ppas->every(fn ($ppa) => $ppa->status->isClosed())],
    ];
    $currentStep = collect($steps)->search(fn (array $step): bool => ! $step['done']);

    // Every PPA already belongs to this plan; saves a query per row for isDelayed().
    $abyip->ppas->each->setRelation('abyip', $abyip);
    $active = $abyip->ppas->where('status', '!=', PpaStatus::Cancelled);
    $spent = (float) $active->sum('amount_spent');
    $completed = $abyip->ppas->where('status', PpaStatus::Completed)->count();
    $delayed = $abyip->ppas->filter->isDelayed()->count();
    $peso = fn (float $amount, int $decimals = 0): string => '₱'.number_format($amount, $decimals);

    // The year strip that each PPA's schedule is drawn on.
    $yearStart = Carbon::create($abyip->year)->startOfYear();
    $yearEnd = $yearStart->copy()->endOfYear();
    $yearDays = $yearStart->diffInDays($yearEnd) + 1;
    $position = fn (Carbon $date): float => max(0, min(100, $yearStart->diffInDays($date, false) / $yearDays * 100));
    $todayAt = today()->between($yearStart, $yearEnd) ? $position(today()) : null;
    $months = collect(range(1, 12))->map(fn (int $month) => Carbon::create($abyip->year, $month));

    $statusTone = fn ($ppa): array => match (true) {
        $ppa->isDelayed() => ['text' => 'text-[#b91c1c]', 'bar' => 'bg-[#dc2626]', 'label' => $ppa->status->label().', delayed'],
        $ppa->status === PpaStatus::Completed => ['text' => 'text-emerald', 'bar' => 'bg-emerald', 'label' => 'Completed'],
        $ppa->status === PpaStatus::Ongoing => ['text' => 'text-navy', 'bar' => 'bg-navy', 'label' => 'Ongoing'],
        $ppa->status === PpaStatus::Cancelled => ['text' => 'text-slate-muted line-through', 'bar' => 'bg-slate-muted/30', 'label' => 'Cancelled'],
        default => ['text' => 'text-slate-muted', 'bar' => 'bg-navy/25', 'label' => 'Planned'],
    };
    $figures = [
        ['value' => $peso($totalBudget), 'label' => trans_choice('budget for :count PPA|budget for :count PPAs', $active->count())],
        ['value' => $peso($spent), 'label' => $totalBudget > 0 ? 'spent so far, '.round($spent / $totalBudget * 100).'% of the budget' : 'spent so far'],
        ['value' => $completed.' of '.$abyip->ppas->count(), 'label' => 'PPAs completed'],
        ['value' => $delayed, 'label' => $delayed === 1 ? 'PPA past its end date' : 'PPAs past their end date', 'alert' => $delayed > 0],
    ];
    $approvedLine = $abyip->isApproved()
        ? 'Approved'.($abyip->approved_on ? ' '.$abyip->approved_on->format('M j, Y') : '').($abyip->resolution_number ? ' under '.$abyip->resolution_number : '')
        : 'Draft, not yet approved by the council';
@endphp
<x-layouts.app :title="'ABYIP '.$abyip->year" :heading="'ABYIP '.$abyip->year" :description="'Annual Barangay Youth Investment Program, from CBYDP '.$abyip->cbydp->period().'.'">
    <x-slot:actions>
        <div class="flex flex-wrap items-center gap-2">
            @if ($abyip->isApproved())
                <a href="{{ route('officer.abyips.report', $abyip) }}" class="rounded-[8px] border border-line bg-white px-4 py-2.5 text-sm font-semibold hover:border-navy">Accomplishment report</a>
            @endif
            @can('update', $abyip)
                <a href="{{ route('officer.ppas.create', $abyip) }}" class="inline-flex items-center gap-2 rounded-[8px] bg-emerald px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-dark">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    Add PPA
                </a>
            @endcan
        </div>
    </x-slot:actions>

    <div class="mb-6 flex flex-wrap items-center justify-between gap-x-6 gap-y-2 text-sm">
        <a href="{{ route('officer.plans.index') }}" class="font-medium text-slate-muted hover:text-emerald">&larr; All plans</a>
        <p @class(['inline-flex items-center gap-1.5 font-medium', 'text-emerald' => $abyip->isApproved(), 'text-[#92400e]' => ! $abyip->isApproved()])>
            @if ($abyip->isApproved())
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
            @endif
            {{ $approvedLine }}
        </p>
    </div>

    @if ($abyip->isBeingAmended())
        <section role="status" class="mb-6 rounded-[12px] border border-warning/40 bg-warning/10 p-5">
            <h2 class="font-display text-lg font-semibold">Amendment in progress</h2>
            <p class="mt-1 text-sm">
                Started by {{ $abyip->openAmendment->opener?->name ?? 'an officer' }} on {{ $abyip->openAmendment->created_at->format('M j, Y') }}:
                “{{ $abyip->openAmendment->reason }}”
            </p>
            <p class="mt-1 text-sm text-navy/75">The PPAs below can be changed. The ABYIP is hidden from the public board until the council approves the amendment. Every change is kept in the audit trail.</p>

            @can('approveAmendment', $abyip)
                <form method="POST" action="{{ route('officer.abyips.amendments.approve', $abyip) }}" enctype="multipart/form-data" class="mt-4 grid gap-4 rounded-[12px] border border-line bg-white p-4 sm:grid-cols-2" novalidate>
                    @csrf
                    <p class="text-sm font-semibold sm:col-span-2">Record the council’s approval of the amendment</p>
                    <x-form.input name="resolution_number" label="Resolution number" required />
                    <x-form.input name="approved_on" type="date" label="Date approved" :max="now()->toDateString()" required />
                    <div class="sm:col-span-2">
                        <label for="resolution_file" class="block text-sm font-medium">Signed resolution (PDF)</label>
                        <input id="resolution_file" name="resolution_file" type="file" accept="application/pdf" required class="mt-1.5 block w-full rounded-[8px] border border-line bg-white text-sm text-navy/80 file:mr-3 file:border-0 file:border-r file:border-line file:bg-mist file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-navy">
                        @error('resolution_file')
                            <p class="mt-1.5 text-sm text-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <x-form.submit busy="Recording…" tone="navy" :block="false">Approve amendment and lock</x-form.submit>
                    </div>
                </form>
            @endcan
        </section>
    @endif

    {{-- Where the plan is in its life cycle: a track that fills as each step is done --}}
    @php
        $closedCount = $abyip->ppas->filter(fn ($ppa) => $ppa->status->isClosed())->count();
        $implementation = $abyip->ppas->isNotEmpty() ? (int) round($closedCount / $abyip->ppas->count() * 100) : 0;
        $details = [
            $abyip->cbydp->isApproved() ? 'CBYDP '.$abyip->cbydp->period() : 'Waiting for the CBYDP',
            $abyip->ppas->isNotEmpty() ? trans_choice(':count PPA|:count PPAs', $abyip->ppas->count()).', '.$peso($totalBudget) : 'Add the PPAs',
            $abyip->isApproved() ? ($abyip->approved_on?->format('M j, Y') ?? 'Recorded') : 'Record the resolution',
            null,
        ];
    @endphp
    <section class="mb-8 rounded-[14px] border border-line bg-white px-5 py-5 sm:px-6" aria-labelledby="lifecycle-heading">
        <h2 id="lifecycle-heading" class="sr-only">Plan progress</h2>
        <ol class="grid gap-0 sm:grid-cols-4" aria-label="Plan life cycle">
            @foreach ($steps as $index => $step)
                @php
                    $isCurrent = $index === $currentStep;
                    $state = $step['done'] ? 'done' : ($isCurrent ? 'current' : 'upcoming');
                @endphp
                <li class="relative flex gap-4 pb-6 last:pb-0 sm:block sm:pb-0" @if ($isCurrent) aria-current="step" @endif>
                    @unless ($loop->last)
                        {{-- The track to the next step: down on phones, across on wider screens --}}
                        <span @class([
                            'absolute left-[0.9375rem] top-9 bottom-1 w-0.5 rounded-full sm:left-11 sm:right-3 sm:top-[0.9375rem] sm:bottom-auto sm:h-0.5 sm:w-auto',
                            'bg-emerald' => $step['done'],
                            'bg-line' => ! $step['done'],
                        ]) aria-hidden="true"></span>
                    @endunless

                    <span @class([
                        'relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-semibold',
                        'bg-emerald text-white' => $state === 'done',
                        'border-2 border-emerald bg-white text-emerald shadow-[0_0_0_4px_rgba(22,132,91,0.12)]' => $state === 'current',
                        'border-2 border-line bg-white text-slate-muted' => $state === 'upcoming',
                    ]) aria-hidden="true">
                        @if ($state === 'done')
                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5 9-10"/></svg>
                        @else
                            {{ $index + 1 }}
                        @endif
                    </span>

                    <div class="min-w-0 flex-1 pt-1 sm:mt-3 sm:pr-6 sm:pt-0">
                        <p @class(['text-sm font-semibold', 'text-navy' => $state !== 'upcoming', 'text-slate-muted' => $state === 'upcoming'])>
                            {{ $step['label'] }}
                            <span class="sr-only">, {{ match ($state) { 'done' => 'done', 'current' => 'in progress', default => 'not started' } }}</span>
                        </p>

                        @if ($index === 3 && $abyip->isApproved())
                            <div class="mt-1.5 w-full max-w-[16rem]">
                                <div class="flex items-baseline justify-between text-xs">
                                    <span @class(['font-semibold', 'text-emerald' => $step['done'], 'text-navy' => ! $step['done']])>{{ $step['done'] ? 'Done' : $closedCount.' of '.$abyip->ppas->count().' PPAs closed' }}</span>
                                    <span class="tabular-nums text-slate-muted">{{ $implementation }}%</span>
                                </div>
                                <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-mist" role="progressbar" aria-valuenow="{{ $implementation }}" aria-valuemin="0" aria-valuemax="100" aria-label="PPAs closed">
                                    <div class="h-full rounded-full bg-emerald" style="width: {{ max($implementation, 3) }}%"></div>
                                </div>
                            </div>
                        @else
                            <p @class(['mt-0.5 text-xs', 'text-slate-muted' => $state !== 'current', 'font-medium text-emerald' => $state === 'current'])>
                                {{ $details[$index] ?? 'Starts once the council approves' }}
                            </p>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    </section>

    @if ($abyip->ppas->isNotEmpty())
        <dl class="mb-8 grid grid-cols-2 gap-y-5 border-y border-line py-5 lg:grid-cols-4">
            @foreach ($figures as $figure)
                <div @class(['px-1 lg:px-5', 'lg:border-l lg:border-line' => ! $loop->first])>
                    <dt class="sr-only">{{ $figure['label'] }}</dt>
                    <dd @class(['font-display text-2xl font-semibold tabular-nums', 'text-[#b91c1c]' => $figure['alert'] ?? false, 'text-navy' => ! ($figure['alert'] ?? false)])>{{ $figure['value'] }}</dd>
                    <dd class="text-sm text-slate-muted">{{ $figure['label'] }}</dd>
                </div>
            @endforeach
        </dl>
    @endif

    <section class="mb-8" aria-labelledby="ppas-heading">
        <div class="mb-3 flex flex-wrap items-end justify-between gap-3">
            <h2 id="ppas-heading" class="font-display text-base font-semibold text-navy">Programs, projects and activities</h2>
            @if ($abyip->ppas->isNotEmpty())
                <p class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-muted" aria-hidden="true">
                    <span class="inline-flex items-center gap-1.5"><span class="h-2 w-4 rounded-full bg-emerald"></span>Completed</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-2 w-4 rounded-full bg-navy"></span>Ongoing</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-2 w-4 rounded-full bg-navy/25"></span>Planned</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-2 w-4 rounded-full bg-[#dc2626]"></span>Delayed</span>
                    @if ($todayAt !== null)
                        <span class="inline-flex items-center gap-1.5"><span class="h-3 w-0.5 bg-gold"></span>Today</span>
                    @endif
                </p>
            @endif
        </div>

        @if ($abyip->ppas->isEmpty())
            <div class="rounded-[14px] border border-dashed border-line bg-white p-8 text-center">
                <p class="font-display text-lg font-semibold text-navy">No PPAs yet</p>
                <p class="mx-auto mt-1 max-w-md text-sm text-slate-muted">Add each program, project or activity, linked to a priority in the CBYDP.</p>
                @can('update', $abyip)
                    <a href="{{ route('officer.ppas.create', $abyip) }}" class="mt-4 inline-flex rounded-[8px] bg-emerald px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-dark">Add the first PPA</a>
                @endcan
            </div>
        @else
            <div class="hidden overflow-x-auto rounded-[14px] border border-line bg-white md:block">
                <table class="w-full min-w-[60rem] text-left text-sm">
                    <caption class="sr-only">PPAs of ABYIP {{ $abyip->year }} with their schedule across the year, budget and status</caption>
                    <thead class="border-b border-line text-xs text-slate-muted">
                        <tr>
                            <th scope="col" class="w-[30%] px-5 py-3 font-semibold">PPA</th>
                            <th scope="col" class="px-3 py-3 font-normal">
                                <span class="sr-only">Schedule in {{ $abyip->year }}</span>
                                <span class="grid grid-cols-12" aria-hidden="true">
                                    @foreach ($months as $month)
                                        <span class="border-l border-line/70 pl-1 first:border-l-0">{{ $month->format('M') }}</span>
                                    @endforeach
                                </span>
                            </th>
                            <th scope="col" class="w-36 px-5 py-3 text-right font-semibold">Budget</th>
                            <th scope="col" class="w-48 px-5 py-3 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($abyip->ppas as $ppa)
                            @php
                                $tone = $statusTone($ppa);
                                $left = $position($ppa->starts_on);
                                $right = $position($ppa->ends_on->copy()->addDay());
                                $runsPastYear = $ppa->ends_on->greaterThan($yearEnd);
                                $progress = $ppa->progress();
                            @endphp
                            <tr class="group hover:bg-mist/40">
                                <td class="px-5 py-3.5 align-top">
                                    <a href="{{ route('officer.ppas.show', $ppa) }}" class="font-medium leading-snug text-navy group-hover:text-emerald">{{ $ppa->title }}</a>
                                    <p class="mt-0.5 text-xs text-slate-muted">{{ $ppa->priority->center->label() }}. Target: {{ $ppa->target }}</p>
                                </td>
                                <td class="px-3 py-3.5 align-middle">
                                    <div class="relative h-5">
                                        <div class="absolute inset-0 grid grid-cols-12" aria-hidden="true">
                                            @foreach ($months as $month)
                                                <span class="border-l border-line/50 first:border-l-0"></span>
                                            @endforeach
                                        </div>
                                        @if ($todayAt !== null)
                                            <span class="absolute inset-y-[-0.9rem] w-0.5 -translate-x-1/2 bg-gold" style="left: {{ $todayAt }}%" aria-hidden="true"></span>
                                        @endif
                                        <span @class(['absolute top-1/2 h-2.5 -translate-y-1/2 overflow-hidden rounded-full', $tone['bar'], 'rounded-r-none' => $runsPastYear]) style="left: {{ $left }}%; width: {{ max(1.5, $right - $left) }}%" title="{{ $ppa->starts_on->format('M j') }} to {{ $ppa->ends_on->format('M j, Y') }}" aria-hidden="true"></span>
                                        <span class="sr-only">{{ $ppa->starts_on->format('F j') }} to {{ $ppa->ends_on->format('F j, Y') }}</span>
                                    </div>
                                    <p @class(['mt-0.5 whitespace-nowrap text-xs tabular-nums text-slate-muted', 'text-right' => $left > 50]) style="{{ $left > 50 ? 'padding-right: '.max(0, 100 - $right).'%' : 'padding-left: '.$left.'%' }}"><span class="relative z-10 rounded bg-white px-1">{{ $ppa->starts_on->format('M j') }} to {{ $ppa->ends_on->format($runsPastYear ? 'M j, Y' : 'M j') }}</span></p>
                                </td>
                                <td class="px-5 py-3.5 text-right align-top">
                                    <p class="font-medium tabular-nums text-navy">{{ $peso((float) $ppa->budget) }}</p>
                                    <p class="text-xs tabular-nums text-slate-muted">{{ $peso((float) ($ppa->amount_spent ?? 0)) }} spent</p>
                                </td>
                                <td class="px-5 py-3.5 align-top">
                                    <p class="flex items-center gap-2 whitespace-nowrap font-medium {{ $tone['text'] }}">
                                        <span class="h-2 w-2 shrink-0 rounded-full {{ $tone['bar'] }}" aria-hidden="true"></span>
                                        {{ $tone['label'] }}
                                    </p>
                                    @if ($ppa->milestones->isNotEmpty())
                                        <p class="mt-0.5 whitespace-nowrap pl-4 text-xs text-slate-muted">{{ $progress }}% of milestones done</p>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t border-line text-sm">
                        <tr>
                            <td class="px-5 py-3 font-semibold text-navy" colspan="2">Total, not counting cancelled PPAs</td>
                            <td class="px-5 py-3 text-right">
                                <p class="font-semibold tabular-nums text-navy">{{ $peso($totalBudget) }}</p>
                                <p class="text-xs tabular-nums text-slate-muted">{{ $peso($spent) }} spent</p>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            {{-- Phones: one card per PPA, each with its own strip of the year --}}
            <ul class="divide-y divide-line overflow-hidden rounded-[14px] border border-line bg-white md:hidden">
                @foreach ($abyip->ppas as $ppa)
                    @php
                        $tone = $statusTone($ppa);
                        $left = $position($ppa->starts_on);
                        $right = $position($ppa->ends_on->copy()->addDay());
                        $runsPastYear = $ppa->ends_on->greaterThan($yearEnd);
                    @endphp
                    <li>
                        <a href="{{ route('officer.ppas.show', $ppa) }}" class="block px-4 py-4 active:bg-mist/60">
                            <div class="flex items-start justify-between gap-3">
                                <p class="font-medium leading-snug text-navy">{{ $ppa->title }}</p>
                                <p class="flex shrink-0 items-center gap-1.5 whitespace-nowrap pt-0.5 text-xs font-semibold {{ $tone['text'] }}">
                                    <span class="h-2 w-2 rounded-full {{ $tone['bar'] }}" aria-hidden="true"></span>
                                    {{ $tone['label'] }}
                                </p>
                            </div>
                            <p class="mt-0.5 text-xs text-slate-muted">{{ $ppa->priority->center->label() }}. Target: {{ $ppa->target }}</p>

                            <div class="relative mt-3 h-4" aria-hidden="true">
                                <div class="absolute inset-0 grid grid-cols-12">
                                    @foreach ($months as $month)
                                        <span class="border-l border-line/60 first:border-l-0"></span>
                                    @endforeach
                                </div>
                                @if ($todayAt !== null)
                                    <span class="absolute -inset-y-1 w-0.5 -translate-x-1/2 bg-gold" style="left: {{ $todayAt }}%"></span>
                                @endif
                                <span @class(['absolute top-1/2 h-2.5 -translate-y-1/2 rounded-full', $tone['bar'], 'rounded-r-none' => $runsPastYear]) style="left: {{ $left }}%; width: {{ max(2.5, $right - $left) }}%"></span>
                            </div>
                            <div class="mt-1 grid grid-cols-12 text-[0.625rem] tracking-tight text-slate-muted" aria-hidden="true">
                                @foreach ($months as $month)
                                    <span class="text-center">{{ $month->format('M') }}</span>
                                @endforeach
                            </div>

                            <div class="mt-2.5 flex items-end justify-between gap-3 text-xs">
                                <p class="tabular-nums text-slate-muted">
                                    {{ $ppa->starts_on->format('M j') }} to {{ $ppa->ends_on->format($runsPastYear ? 'M j, Y' : 'M j') }}
                                    @if ($ppa->milestones->isNotEmpty())
                                        <span class="block">{{ $ppa->progress() }}% of milestones done</span>
                                    @endif
                                </p>
                                <p class="text-right tabular-nums">
                                    <span class="block text-sm font-semibold text-navy">{{ $peso((float) $ppa->budget) }}</span>
                                    <span class="text-slate-muted">{{ $peso((float) ($ppa->amount_spent ?? 0)) }} spent</span>
                                </p>
                            </div>
                        </a>
                    </li>
                @endforeach
                <li class="flex items-baseline justify-between gap-3 bg-mist/40 px-4 py-3 text-sm">
                    <span class="font-semibold text-navy">Total</span>
                    <span class="text-right tabular-nums">
                        <span class="block font-semibold text-navy">{{ $peso($totalBudget) }}</span>
                        <span class="text-xs text-slate-muted">{{ $peso($spent) }} spent, not counting cancelled</span>
                    </span>
                </li>
            </ul>
        @endif
    </section>

    {{-- The plan's record: council approval and public visibility side by side, then its changes --}}
    <div class="grid gap-6 lg:grid-cols-2">
        @include('officer._approval', [
            'plan' => $abyip,
            'action' => route('officer.abyips.approval.store', $abyip),
            'resolutionRoute' => route('officer.abyips.resolution', $abyip),
            'planName' => 'ABYIP '.$abyip->year,
            'lockNote' => 'Its PPAs, budgets, and schedules will be locked, and you can start recording progress on each one.',
            'emptyNote' => $abyip->ppas->isEmpty() ? 'Add at least one PPA before recording the approval.' : null,
        ])

        @can('publish', $abyip)
            <section class="flex flex-col rounded-[12px] border border-line bg-white p-5" aria-labelledby="publish-heading">
                <h2 id="publish-heading" class="flex items-center gap-2 font-display text-lg font-semibold">
                    <span @class(['h-2.5 w-2.5 rounded-full', 'bg-emerald' => $abyip->isPublished(), 'bg-line' => ! $abyip->isPublished()]) aria-hidden="true"></span>
                    {{ $abyip->isPublished() ? 'On the public transparency board' : 'Not on the public board yet' }}
                </h2>
                @if ($abyip->isPublished())
                    <p class="mt-2 text-sm text-slate-muted">Published {{ $abyip->published_at->format('M j, Y') }}. Anyone can see its PPAs, budgets, progress, and the evidence you marked public.</p>
                @else
                    <p class="mt-2 text-sm text-slate-muted">Publishing shows its PPAs, budgets, progress, and public evidence to everyone. Internal evidence and personal details are never shown.</p>
                @endif
                <div class="mt-auto flex flex-wrap items-center gap-3 pt-4">
                    <form method="POST" action="{{ $abyip->isPublished() ? route('officer.abyips.publication.destroy', $abyip) : route('officer.abyips.publication.store', $abyip) }}">
                        @csrf
                        @if ($abyip->isPublished())
                            @method('DELETE')
                            <button type="submit" class="rounded-[8px] border border-line px-4 py-2 text-sm font-semibold hover:border-navy">Unpublish</button>
                        @else
                            <button type="submit" class="rounded-[8px] bg-emerald px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-dark">Publish to the board</button>
                        @endif
                    </form>
                    @if ($abyip->isPublished())
                        <a href="{{ route('transparency.show', $abyip->barangay_id) }}" target="_blank" class="text-sm font-semibold text-emerald hover:underline">View public page<span class="sr-only"> (opens in a new tab)</span></a>
                    @endif
                </div>
            </section>
        @elsecan('report', $abyip)
            <section class="rounded-[12px] border border-line bg-white p-5" aria-labelledby="publish-heading">
                <h2 id="publish-heading" class="font-display text-lg font-semibold">{{ $abyip->isPublished() ? 'On the public transparency board' : 'Not on the public board yet' }}</h2>
                <p class="mt-2 text-sm text-slate-muted">Only the SK Chairperson approves items for public disclosure.</p>
            </section>
        @endcan

        @if ($abyip->amendments->isNotEmpty() || Gate::allows('amend', $abyip))
            <section class="rounded-[12px] border border-line bg-white p-5 lg:col-span-2" aria-labelledby="changes-heading">
                <h2 id="changes-heading" class="font-display text-lg font-semibold">Changes to this plan</h2>
                <div @class(['mt-4 grid gap-x-10 gap-y-6', 'lg:grid-cols-2' => $abyip->amendments->isNotEmpty() && Gate::allows('amend', $abyip)])>
                    @if ($abyip->amendments->isNotEmpty())
                        <div>
                            <h3 class="text-sm font-semibold text-navy">{{ trans_choice('{1}1 approved amendment|[2,*]:count approved amendments', $abyip->amendments->count()) }}</h3>
                            <ol class="mt-3 space-y-4 border-l-2 border-line pl-5">
                                @foreach ($abyip->amendments->sortByDesc('approved_on') as $amendment)
                                    <li class="relative">
                                        <span class="absolute -left-[1.6rem] top-1.5 h-2.5 w-2.5 rounded-full border-2 border-white bg-emerald ring-1 ring-emerald/40" aria-hidden="true"></span>
                                        <p class="text-sm font-medium text-navy">Approved {{ $amendment->approved_on->format('M j, Y') }}<span class="font-normal text-slate-muted">, Resolution {{ $amendment->resolution_number }}</span></p>
                                        <p class="mt-0.5 text-sm text-navy/75">{{ $amendment->reason }}</p>
                                        @if ($amendment->resolution_path)
                                            <a href="{{ route('officer.amendments.resolution', $amendment) }}" class="mt-1 inline-flex items-center gap-1.5 text-xs font-semibold text-emerald hover:underline">
                                                <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/></svg>
                                                Signed resolution (PDF)
                                            </a>
                                        @endif
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    @endif

                    @can('amend', $abyip)
                        <form method="POST" action="{{ route('officer.abyips.amendments.store', $abyip) }}" class="space-y-3" novalidate>
                            @csrf
                            <div>
                                <h3 class="text-sm font-semibold text-navy">Start an amendment</h3>
                                <p class="mt-0.5 text-sm text-slate-muted">An approved ABYIP stays locked. An amendment lets you change its PPAs, then you record the council’s resolution approving the change.</p>
                            </div>
                            <label for="reason" class="block text-sm font-medium">Reason for the amendment</label>
                            <textarea id="reason" name="reason" rows="2" placeholder="e.g. Moving the leadership summit budget to the clean-up drive" class="block w-full rounded-[8px] border border-line px-3.5 py-2.5 text-sm outline-none focus:border-emerald focus:ring-3 focus:ring-emerald/20">{{ old('reason') }}</textarea>
                            @error('reason')
                                <p class="text-sm text-error">{{ $message }}</p>
                            @enderror
                            <x-form.submit busy="Starting…" tone="navy" :block="false">Start amendment</x-form.submit>
                        </form>
                    @endcan
                </div>
            </section>
        @endif
    </div>
</x-layouts.app>
