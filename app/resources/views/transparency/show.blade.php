@php
    use App\PpaStatus;

    $filters = [null => 'All', 'ongoing' => 'Ongoing', 'completed' => 'Completed', 'planned' => 'Planned', 'delayed' => 'Delayed'];
@endphp
<x-layouts.public :title="$barangay->name.' transparency board'" :description="'Approved youth programs and budgets of the '.$barangay->name.' SK.'">
    <p class="text-sm"><a href="{{ route('transparency.index') }}" class="font-medium text-slate-muted hover:text-emerald">&larr; All barangays</a></p>

    <div class="mt-5 flex items-center gap-4">
        <img src="{{ asset('images/sk-logo.svg') }}" alt="" class="h-14 w-14 rounded-full border border-line bg-white p-1">
        <div>
            <h1 class="font-display text-3xl font-semibold tracking-tight sm:text-4xl">{{ $barangay->name }}</h1>
            <p class="text-slate-muted">Approved programs of the Sangguniang Kabataan, as published by its officers.</p>
        </div>
    </div>

    @forelse ($abyips as $abyip)
        @php
            $active = $abyip->ppas->where('status', '!=', PpaStatus::Cancelled);
            $budget = (float) $active->sum('budget');
            $spent = (float) $active->sum('amount_spent');
            $byCenter = $active->groupBy(fn ($ppa) => $ppa->priority->center->label())
                ->map(fn ($ppas, $center) => ['label' => $center, 'value' => (float) $ppas->sum('budget'), 'display' => '₱'.number_format($ppas->sum('budget'))])
                ->sortByDesc('value')
                ->values();
            $shown = $abyip->ppas->filter(fn ($ppa) => match ($status) {
                'delayed' => $ppa->isDelayed(),
                'ongoing' => $ppa->status === PpaStatus::Ongoing,
                'completed' => $ppa->status === PpaStatus::Completed,
                'planned' => $ppa->status === PpaStatus::Planned,
                default => true,
            });
        @endphp
        <section class="mt-10" aria-labelledby="abyip-{{ $abyip->id }}">
            <div class="rounded-[16px] border border-line bg-white p-6 sm:p-7">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h2 id="abyip-{{ $abyip->id }}" class="font-display text-2xl font-semibold">ABYIP {{ $abyip->year }}</h2>
                        <p class="mt-1 text-sm text-slate-muted">
                            Approved {{ $abyip->approved_on?->format('F j, Y') }} by {{ $abyip->resolution_number }}, from the CBYDP {{ $abyip->cbydp->period() }}
                        </p>
                    </div>
                    <x-ui.badge tone="success">Council-approved</x-ui.badge>
                </div>

                <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
                    <dl class="grid grid-cols-2 gap-3">
                        <div class="rounded-[12px] bg-mist p-4">
                            <dt class="text-xs text-slate-muted">Total budget</dt>
                            <dd class="mt-1 font-display text-2xl font-semibold tabular-nums">₱{{ number_format($budget) }}</dd>
                        </div>
                        <div class="rounded-[12px] bg-mist p-4">
                            <dt class="text-xs text-slate-muted">Spent so far</dt>
                            <dd class="mt-1 font-display text-2xl font-semibold tabular-nums">₱{{ number_format($spent) }}</dd>
                            <dd class="text-xs text-slate-muted">{{ $budget > 0 ? (int) round($spent / $budget * 100) : 0 }}% of the budget</dd>
                        </div>
                        <div class="rounded-[12px] bg-mist p-4">
                            <dt class="text-xs text-slate-muted">Projects completed</dt>
                            <dd class="mt-1 font-display text-2xl font-semibold tabular-nums">{{ $active->where('status', PpaStatus::Completed)->count() }} <span class="text-base font-medium text-slate-muted">of {{ $active->count() }}</span></dd>
                        </div>
                        <div class="rounded-[12px] bg-mist p-4">
                            <dt class="text-xs text-slate-muted">Behind schedule</dt>
                            @php($delayed = $active->filter->isDelayed()->count())
                            <dd @class(['mt-1 font-display text-2xl font-semibold tabular-nums', 'text-[#b91c1c]' => $delayed > 0])>{{ $delayed }}</dd>
                        </div>
                    </dl>
                    <div>
                        <h3 class="text-sm font-semibold">Where the budget goes</h3>
                        <p class="mb-3 text-xs text-slate-muted">By center of youth participation</p>
                        <x-ui.bar-list label="Budget by center of participation" :items="$byCenter" :max="$byCenter->max('value')" />
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
                <h3 class="font-display text-lg font-semibold">Projects</h3>
                <nav class="flex flex-wrap gap-1.5" aria-label="Filter projects">
                    @foreach ($filters as $value => $label)
                        @php($isActive = $status === ($value ?: null))
                        <a href="{{ route('transparency.show', array_filter(['barangay' => $barangay, 'status' => $value ?: null])) }}#abyip-{{ $abyip->id }}" @class([
                            'rounded-full border px-3 py-1 text-sm font-semibold',
                            'border-navy bg-navy text-white' => $isActive,
                            'border-line bg-white text-navy/75 hover:border-navy/40' => ! $isActive,
                        ]) @if ($isActive) aria-current="page" @endif>{{ $label }}</a>
                    @endforeach
                </nav>
            </div>

            <ul class="mt-4 grid gap-4 lg:grid-cols-2">
                @forelse ($shown as $ppa)
                    <li class="flex flex-col rounded-[14px] border border-line bg-white p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold text-emerald">{{ $ppa->priority->center->label() }}</p>
                                <h4 class="mt-0.5 font-display font-semibold leading-snug">{{ $ppa->title }}</h4>
                            </div>
                            <x-ui.ppa-status :ppa="$ppa" class="shrink-0" />
                        </div>
                        <dl class="mt-4 grid grid-cols-3 gap-3 text-sm">
                            <div><dt class="text-xs text-slate-muted">Budget</dt><dd class="font-medium tabular-nums">₱{{ number_format($ppa->budget) }}</dd></div>
                            <div><dt class="text-xs text-slate-muted">Spent</dt><dd @class(['font-medium tabular-nums', 'text-[#b91c1c]' => $ppa->isOverBudget()])>{{ $ppa->amount_spent !== null ? '₱'.number_format($ppa->amount_spent) : '—' }}</dd></div>
                            <div><dt class="text-xs text-slate-muted">Schedule</dt><dd class="font-medium">{{ $ppa->starts_on->format('M j') }} – {{ $ppa->ends_on->format('M j') }}</dd></div>
                        </dl>
                        <p class="mt-3 text-sm"><span class="text-slate-muted">Target:</span> {{ $ppa->target }}</p>
                        <div class="mt-4 flex items-center gap-3">
                            <div class="h-2 flex-1 overflow-hidden rounded-full bg-mist" role="progressbar" aria-valuenow="{{ $ppa->progress() }}" aria-valuemin="0" aria-valuemax="100" aria-label="Progress of {{ $ppa->title }}">
                                <div class="h-full rounded-full bg-emerald" style="width: {{ $ppa->progress() }}%"></div>
                            </div>
                            <span class="text-xs tabular-nums text-slate-muted">{{ $ppa->progress() }}% of milestones</span>
                        </div>
                        @if ($ppa->evidence->isNotEmpty())
                            <div class="mt-4 border-t border-line pt-4">
                                <p class="text-xs text-slate-muted">Proof of work</p>
                                <ul class="mt-2 flex flex-wrap gap-2" aria-label="Evidence for {{ $ppa->title }}">
                                    @foreach ($ppa->evidence as $file)
                                        <li>
                                            <a href="{{ route('transparency.evidence', $file) }}" target="_blank" class="block overflow-hidden rounded-[8px] border border-line hover:border-emerald" title="{{ $file->title }}">
                                                @if ($file->isImage())
                                                    <img src="{{ route('transparency.evidence', $file) }}" alt="{{ $file->title }}" loading="lazy" class="h-20 w-28 object-cover">
                                                @else
                                                    <span class="flex h-20 w-28 items-center justify-center bg-mist text-xs font-semibold text-slate-muted">PDF</span>
                                                @endif
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </li>
                @empty
                    <li class="rounded-[12px] border border-dashed border-line bg-white p-6 text-center text-sm text-slate-muted lg:col-span-2">No projects match this filter.</li>
                @endforelse
            </ul>
        </section>
    @empty
        <div class="mt-10 rounded-[14px] border border-dashed border-line bg-white p-10 text-center">
            <p class="font-display text-lg font-semibold">Nothing published yet</p>
            <p class="mt-1 text-sm text-slate-muted">Programs appear here after the SK council approves them and the Chairperson publishes them.</p>
        </div>
    @endforelse
</x-layouts.public>
