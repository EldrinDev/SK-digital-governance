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

    {{-- Where the plan is in its life cycle: a slim line of steps --}}
    <ol class="mb-8 flex flex-col gap-2 text-sm sm:flex-row sm:flex-wrap sm:items-center sm:gap-x-3" aria-label="Plan life cycle">
        @foreach ($steps as $index => $step)
            @php
                $isCurrent = $index === $currentStep;
            @endphp
            <li class="flex items-center gap-3" @if ($isCurrent) aria-current="step" @endif>
                @unless ($loop->first)
                    <span @class(['hidden h-px w-10 sm:block', 'bg-emerald' => $step['done'] || $isCurrent, 'bg-line' => ! $step['done'] && ! $isCurrent]) aria-hidden="true"></span>
                @endunless
                <span class="flex items-center gap-2">
                    <span @class([
                        'flex h-5 w-5 shrink-0 items-center justify-center rounded-full',
                        'bg-emerald text-white' => $step['done'],
                        'ring-2 ring-emerald ring-offset-2 ring-offset-mist bg-emerald/15' => ! $step['done'] && $isCurrent,
                        'border border-line bg-white' => ! $step['done'] && ! $isCurrent,
                    ])>
                        @if ($step['done'])
                            <svg viewBox="0 0 24 24" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg>
                            <span class="sr-only">Done:</span>
                        @endif
                    </span>
                    <span @class(['font-semibold text-navy' => $isCurrent, 'text-navy/70' => ! $isCurrent])>
                        {{ $step['label'] }}@if ($isCurrent && $step['label'] === 'Implementation')<span class="font-normal text-slate-muted">, {{ $completed }} of {{ $abyip->ppas->count() }} done</span>@endif
                    </span>
                </span>
            </li>
        @endforeach
    </ol>

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
            <div class="overflow-x-auto rounded-[14px] border border-line bg-white">
                <table class="w-full min-w-[60rem] text-left text-sm">
                    <caption class="sr-only">PPAs of ABYIP {{ $abyip->year }} with their schedule across the year, budget and status</caption>
                    <thead class="border-b border-line text-xs text-slate-muted">
                        <tr>
                            <th scope="col" class="w-[30%] px-5 py-3 font-semibold">PPA</th>
                            <th scope="col" class="px-3 py-3 font-normal">
                                <span class="sr-only">Schedule in {{ $abyip->year }}</span>
                                <span class="grid grid-cols-12" aria-hidden="true">
                                    @foreach ($months as $month)
                                        <span class="border-l border-line/70 pl-1 first:border-l-0">{{ $month->format('M')[0] }}</span>
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
        @endif
    </section>

    <div class="grid items-start gap-6 lg:grid-cols-2 [&>*]:!mt-0">
        @include('officer._approval', [
            'plan' => $abyip,
            'action' => route('officer.abyips.approval.store', $abyip),
            'resolutionRoute' => route('officer.abyips.resolution', $abyip),
            'planName' => 'ABYIP '.$abyip->year,
            'lockNote' => 'Its PPAs, budgets, and schedules will be locked, and you can start recording progress on each one.',
            'emptyNote' => $abyip->ppas->isEmpty() ? 'Add at least one PPA before recording the approval.' : null,
        ])

        @can('amend', $abyip)
            <section class="mt-6 rounded-[12px] border border-line bg-white p-5 lg:order-last lg:col-span-2" aria-labelledby="amend-heading">
                <h2 id="amend-heading" class="font-display text-lg font-semibold">Need to change this ABYIP?</h2>
                <p class="mt-1 text-sm text-slate-muted">An approved ABYIP stays locked. Start an amendment to change its PPAs, then record the council’s resolution approving it.</p>
                <form method="POST" action="{{ route('officer.abyips.amendments.store', $abyip) }}" class="mt-4 space-y-3" novalidate>
                    @csrf
                    <label for="reason" class="block text-sm font-medium">Reason for the amendment</label>
                    <textarea id="reason" name="reason" rows="2" placeholder="e.g. Moving the leadership summit budget to the clean-up drive" class="block w-full rounded-[8px] border border-line px-3.5 py-2.5 text-sm outline-none focus:border-emerald focus:ring-3 focus:ring-emerald/20">{{ old('reason') }}</textarea>
                    @error('reason')
                        <p class="text-sm text-error">{{ $message }}</p>
                    @enderror
                    <x-form.submit busy="Starting…" tone="navy" :block="false">Start amendment</x-form.submit>
                </form>
            </section>
        @endcan

        @if ($abyip->amendments->isNotEmpty())
            <section class="mt-6 rounded-[12px] border border-line bg-white p-5 lg:col-span-2" aria-labelledby="amendments-heading">
                <h2 id="amendments-heading" class="font-display text-lg font-semibold">Approved amendments</h2>
                <ul class="mt-3 space-y-3 text-sm">
                    @foreach ($abyip->amendments as $amendment)
                        <li class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="font-medium">{{ $amendment->resolution_number }} · {{ $amendment->approved_on->format('M j, Y') }}</p>
                                <p class="text-navy/75">{{ $amendment->reason }}</p>
                            </div>
                            @if ($amendment->resolution_path)
                                <a href="{{ route('officer.amendments.resolution', $amendment) }}" class="text-xs font-semibold text-emerald hover:underline">PDF</a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        @can('publish', $abyip)
            <section class="mt-6 rounded-[12px] border border-line bg-white p-5" aria-labelledby="publish-heading">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h2 id="publish-heading" class="font-display text-lg font-semibold">Public transparency board</h2>
                        @if ($abyip->isPublished())
                            <p class="mt-1 text-sm text-slate-muted">
                                Published {{ $abyip->published_at->format('M j, Y') }}. Anyone can see its PPAs, budgets, progress, and the evidence you marked public.
                                <a href="{{ route('transparency.show', $abyip->barangay_id) }}" target="_blank" class="font-semibold text-emerald hover:underline">View public page</a>
                            </p>
                        @else
                            <p class="mt-1 text-sm text-slate-muted">Not published. Publishing shows its PPAs, budgets, progress, and public evidence to everyone. Internal evidence and personal details are never shown.</p>
                        @endif
                    </div>
                    <form method="POST" action="{{ $abyip->isPublished() ? route('officer.abyips.publication.destroy', $abyip) : route('officer.abyips.publication.store', $abyip) }}">
                        @csrf
                        @if ($abyip->isPublished())
                            @method('DELETE')
                            <button type="submit" class="rounded-[8px] border border-line px-4 py-2.5 text-sm font-semibold hover:border-navy">Unpublish</button>
                        @else
                            <button type="submit" class="rounded-[8px] bg-emerald px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-dark">Publish to the board</button>
                        @endif
                    </form>
                </div>
            </section>
        @elsecan('report', $abyip)
            <p class="mt-6 rounded-[12px] border border-line bg-white p-5 text-sm text-slate-muted">
                {{ $abyip->isPublished() ? 'This ABYIP is on the public transparency board.' : 'This ABYIP is not on the public transparency board yet.' }}
                Only the SK Chairperson approves items for public disclosure.
            </p>
        @endcan
    </div>
</x-layouts.app>
