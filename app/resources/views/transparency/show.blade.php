<x-layouts.public :title="$barangay->name.' transparency board'" :description="'Approved youth programs and budgets of the '.$barangay->name.' SK.'">
    <p class="text-sm"><a href="{{ route('transparency.index') }}" class="font-medium text-slate-muted hover:text-emerald">&larr; All barangays</a></p>
    <h1 class="mt-4 font-display text-3xl font-semibold tracking-tight sm:text-4xl">{{ $barangay->name }}</h1>
    <p class="mt-2 max-w-2xl text-slate-muted">Approved investment programs of the {{ $barangay->name }} Sangguniang Kabataan, as published by its officers.</p>

    @forelse ($abyips as $abyip)
        @php($active = $abyip->ppas->where('status', '!=', \App\PpaStatus::Cancelled))
        <section class="mt-10" aria-labelledby="abyip-{{ $abyip->id }}">
            <div class="flex flex-wrap items-end justify-between gap-4 border-b border-line pb-4">
                <div>
                    <h2 id="abyip-{{ $abyip->id }}" class="font-display text-2xl font-semibold">ABYIP {{ $abyip->year }}</h2>
                    <p class="mt-1 text-sm text-slate-muted">
                        Approved {{ $abyip->approved_on?->format('F j, Y') }} by {{ $abyip->resolution_number }} · from CBYDP {{ $abyip->cbydp->period() }}
                    </p>
                </div>
                <p class="text-right">
                    <span class="block font-display text-2xl font-semibold">₱{{ number_format($active->sum('budget'), 2) }}</span>
                    <span class="text-xs text-slate-muted">{{ trans_choice(':count project|:count projects', $active->count()) }}, {{ $active->where('status', \App\PpaStatus::Completed)->count() }} completed</span>
                </p>
            </div>

            <ul class="mt-6 grid gap-4 lg:grid-cols-2">
                @foreach ($abyip->ppas as $ppa)
                    <li class="rounded-[12px] border border-line bg-white p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs text-slate-muted">{{ $ppa->priority->center->label() }}</p>
                                <h3 class="font-semibold">{{ $ppa->title }}</h3>
                            </div>
                            <x-ui.ppa-status :ppa="$ppa" />
                        </div>
                        <dl class="mt-4 grid grid-cols-3 gap-3 text-sm">
                            <div><dt class="text-xs text-slate-muted">Budget</dt><dd class="font-medium tabular-nums">₱{{ number_format($ppa->budget) }}</dd></div>
                            <div><dt class="text-xs text-slate-muted">Schedule</dt><dd class="font-medium">{{ $ppa->starts_on->format('M j') }} – {{ $ppa->ends_on->format('M j') }}</dd></div>
                            <div><dt class="text-xs text-slate-muted">Target</dt><dd class="font-medium">{{ $ppa->target }}</dd></div>
                        </dl>
                        <div class="mt-4 flex items-center gap-3">
                            <div class="h-2 flex-1 overflow-hidden rounded-full bg-mist" role="progressbar" aria-valuenow="{{ $ppa->progress() }}" aria-valuemin="0" aria-valuemax="100" aria-label="Progress of {{ $ppa->title }}">
                                <div class="h-full rounded-full bg-emerald" style="width: {{ $ppa->progress() }}%"></div>
                            </div>
                            <span class="text-xs text-slate-muted">{{ $ppa->progress() }}%</span>
                        </div>
                        @if ($ppa->evidence->isNotEmpty())
                            <ul class="mt-4 flex flex-wrap gap-2" aria-label="Evidence for {{ $ppa->title }}">
                                @foreach ($ppa->evidence as $file)
                                    <li>
                                        <a href="{{ route('transparency.evidence', $file) }}" target="_blank" class="block overflow-hidden rounded-[8px] border border-line" title="{{ $file->title }}">
                                            @if ($file->isImage())
                                                <img src="{{ route('transparency.evidence', $file) }}" alt="{{ $file->title }}" loading="lazy" class="h-16 w-20 object-cover">
                                            @else
                                                <span class="flex h-16 w-20 items-center justify-center bg-mist text-xs font-semibold text-slate-muted">PDF</span>
                                            @endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>
        </section>
    @empty
        <div class="mt-10 rounded-[12px] border border-dashed border-line bg-white p-8 text-center">
            <p class="font-medium">Nothing published yet</p>
            <p class="mt-1 text-sm text-slate-muted">Programs appear here after the SK council approves them and the officers publish them.</p>
        </div>
    @endforelse
</x-layouts.public>
