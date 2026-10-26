@php
    $steps = [
        ['label' => 'CBYDP approved', 'done' => $abyip->cbydp->isApproved()],
        ['label' => 'PPAs listed', 'done' => $abyip->ppas->isNotEmpty()],
        ['label' => 'Council approval recorded', 'done' => $abyip->isApproved()],
        ['label' => 'Implementation', 'done' => $abyip->isApproved() && $abyip->ppas->every(fn ($ppa) => $ppa->status->isClosed())],
    ];
    $currentStep = collect($steps)->search(fn (array $step): bool => ! $step['done']);
@endphp
<x-layouts.app :title="'ABYIP '.$abyip->year" :heading="'ABYIP '.$abyip->year" :description="'From CBYDP '.$abyip->cbydp->period().'. Total budget ₱'.number_format($totalBudget, 2).'.'">
    <x-slot:actions>
        <div class="flex items-center gap-3">
            <x-ui.plan-status :plan="$abyip" />
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

    <p class="mb-6 text-sm"><a href="{{ route('officer.plans.index') }}" class="font-medium text-slate-muted hover:text-emerald">&larr; All plans</a></p>

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

    {{-- Plan life cycle --}}
    <ol class="mb-6 grid gap-2 rounded-[12px] border border-line bg-white p-4 sm:grid-cols-4" aria-label="Plan life cycle">
        @foreach ($steps as $index => $step)
            @php($isCurrent = $index === $currentStep)
            <li @class([
                'flex items-center gap-3 rounded-[8px] px-3 py-2.5 text-sm',
                'bg-emerald/10 font-semibold text-emerald' => $isCurrent,
            ]) @if ($isCurrent) aria-current="step" @endif>
                <span @class([
                    'flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-semibold',
                    'bg-emerald text-white' => $step['done'],
                    'border-2 border-emerald text-emerald' => ! $step['done'] && $isCurrent,
                    'border border-line text-slate-muted' => ! $step['done'] && ! $isCurrent,
                ])>
                    @if ($step['done'])
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg>
                        <span class="sr-only">Done:</span>
                    @else
                        {{ $index + 1 }}
                    @endif
                </span>
                <span @class(['text-navy/80' => ! $isCurrent && ! $step['done']])>{{ $step['label'] }}</span>
            </li>
        @endforeach
    </ol>

    <section class="mb-6 overflow-x-auto rounded-[12px] border border-line bg-white">
        @if ($abyip->ppas->isEmpty())
            <div class="p-8 text-center">
                <h2 class="font-display text-lg font-semibold">No PPAs yet</h2>
                <p class="mx-auto mt-2 max-w-md text-sm text-slate-muted">Add each program, project, or activity, linked to a priority in the CBYDP.</p>
                @can('update', $abyip)
                    <a href="{{ route('officer.ppas.create', $abyip) }}" class="mt-4 inline-flex rounded-[8px] bg-emerald px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-dark">Add the first PPA</a>
                @endcan
            </div>
        @else
            <table class="w-full min-w-[48rem] text-left text-sm">
                <thead class="border-b border-line bg-mist/60 text-xs font-semibold text-slate-muted">
                    <tr>
                        <th scope="col" class="px-5 py-3">PPA</th>
                        <th scope="col" class="px-5 py-3">Priority</th>
                        <th scope="col" class="px-5 py-3">Schedule</th>
                        <th scope="col" class="px-5 py-3 text-right">Budget</th>
                        <th scope="col" class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @foreach ($abyip->ppas as $ppa)
                        <tr class="align-top">
                            <td class="px-5 py-3">
                                <a href="{{ route('officer.ppas.show', $ppa) }}" class="font-medium hover:text-emerald">{{ $ppa->title }}</a>
                                <p class="text-xs text-slate-muted">Target: {{ $ppa->target }}</p>
                            </td>
                            <td class="px-5 py-3 text-navy/80">
                                <span class="block text-xs text-slate-muted">{{ $ppa->priority->center->label() }}</span>
                                {{ $ppa->priority->title }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-navy/80">{{ $ppa->starts_on->format('M j') }} – {{ $ppa->ends_on->format('M j') }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right tabular-nums">₱{{ number_format($ppa->budget, 2) }}</td>
                            <td class="px-5 py-3"><x-ui.ppa-status :ppa="$ppa" /></td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t border-line text-sm font-semibold">
                    <tr>
                        <td colspan="3" class="px-5 py-3">Total (excluding cancelled)</td>
                        <td class="whitespace-nowrap px-5 py-3 text-right tabular-nums">₱{{ number_format($totalBudget, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        @endif
    </section>

    <div class="max-w-3xl">
        @include('officer._approval', [
            'plan' => $abyip,
            'action' => route('officer.abyips.approval.store', $abyip),
            'resolutionRoute' => route('officer.abyips.resolution', $abyip),
            'planName' => 'ABYIP '.$abyip->year,
            'lockNote' => 'Its PPAs, budgets, and schedules will be locked, and you can start recording progress on each one.',
            'emptyNote' => $abyip->ppas->isEmpty() ? 'Add at least one PPA before recording the approval.' : null,
        ])

        @can('amend', $abyip)
            <section class="mt-6 rounded-[12px] border border-line bg-white p-5" aria-labelledby="amend-heading">
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
            <section class="mt-6 rounded-[12px] border border-line bg-white p-5" aria-labelledby="amendments-heading">
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
        @endcan
    </div>
</x-layouts.app>
