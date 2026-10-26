<x-layouts.public title="Transparency board" description="Approved youth programs, budgets, and progress from participating barangay SK councils in Olongapo City.">
    <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)] lg:items-end">
        <div class="max-w-xl">
            <h1 class="font-display text-3xl font-semibold tracking-tight sm:text-4xl">Transparency board</h1>
            <p class="mt-3 leading-relaxed text-slate-muted">
                The SK fund is the youth’s money. Here you can see each barangay SK’s approved investment program: what was planned, what each project costs, how much has been spent, and how far along it is.
            </p>
        </div>

        <dl class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-[12px] border border-line bg-white p-4">
                <dt class="text-xs text-slate-muted">Budget published</dt>
                <dd class="mt-1 font-display text-xl font-semibold tabular-nums">₱{{ number_format($totals['budget']) }}</dd>
            </div>
            <div class="rounded-[12px] border border-line bg-white p-4">
                <dt class="text-xs text-slate-muted">Spent so far</dt>
                <dd class="mt-1 font-display text-xl font-semibold tabular-nums">₱{{ number_format($totals['spent']) }}</dd>
            </div>
            <div class="rounded-[12px] border border-line bg-white p-4">
                <dt class="text-xs text-slate-muted">Projects</dt>
                <dd class="mt-1 font-display text-xl font-semibold tabular-nums">{{ $totals['completed'] }} <span class="text-sm font-medium text-slate-muted">of {{ $totals['ppas'] }} done</span></dd>
            </div>
            <div class="rounded-[12px] border border-line bg-white p-4">
                <dt class="text-xs text-slate-muted">Barangays publishing</dt>
                <dd class="mt-1 font-display text-xl font-semibold tabular-nums">{{ $totals['publishing'] }} <span class="text-sm font-medium text-slate-muted">of {{ $barangays->count() }}</span></dd>
            </div>
        </dl>
    </div>

    <ul class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($barangays as $barangay)
            @php
                $hasPublished = $barangay->published_abyips_count > 0;
                $done = $barangay->published_ppas_count > 0 ? (int) round($barangay->published_completed_count / $barangay->published_ppas_count * 100) : 0;
                $used = $barangay->published_budget > 0 ? (int) min(100, round($barangay->published_spent / $barangay->published_budget * 100)) : 0;
            @endphp
            <li>
                <a href="{{ route('transparency.show', $barangay) }}" @class([
                    'group flex h-full flex-col rounded-[14px] border bg-white p-5 transition-colors',
                    'border-line hover:border-emerald/50' => $hasPublished,
                    'border-dashed border-line hover:border-navy/30' => ! $hasPublished,
                ])>
                    <span class="flex items-center gap-3">
                        <img src="{{ $barangay->logoUrl() }}" alt="" class="h-10 w-10 rounded-full border border-line bg-white object-contain p-0.5">
                        <span class="leading-tight">
                            <span class="block font-display text-lg font-semibold group-hover:text-emerald">{{ $barangay->name }}</span>
                            <span class="text-xs text-slate-muted">
                                {{ $hasPublished ? 'Latest: ABYIP '.$barangay->latest_published_year : 'No program published yet' }}
                            </span>
                        </span>
                    </span>

                    @if ($hasPublished)
                        <span class="mt-5 block font-display text-2xl font-semibold tabular-nums">₱{{ number_format($barangay->published_budget ?? 0) }}</span>
                        <span class="text-xs text-slate-muted">budget across {{ trans_choice(':count project|:count projects', $barangay->published_ppas_count) }}</span>

                        <span class="mt-5 grid gap-3 text-xs">
                            <span>
                                <span class="flex justify-between"><span class="text-slate-muted">Projects completed</span><span class="font-semibold tabular-nums">{{ $done }}%</span></span>
                                <span class="mt-1 block h-1.5 overflow-hidden rounded-full bg-mist" aria-hidden="true"><span class="block h-full rounded-full bg-emerald" style="width: {{ $done }}%"></span></span>
                            </span>
                            <span>
                                <span class="flex justify-between"><span class="text-slate-muted">Budget spent</span><span class="font-semibold tabular-nums">{{ $used }}%</span></span>
                                <span class="mt-1 block h-1.5 overflow-hidden rounded-full bg-mist" aria-hidden="true"><span class="block h-full rounded-full bg-navy" style="width: {{ $used }}%"></span></span>
                            </span>
                        </span>
                        <span class="mt-auto pt-5 text-sm font-semibold text-emerald">Open the board</span>
                    @else
                        <span class="mt-4 text-sm leading-relaxed text-slate-muted">Programs appear here once the SK council approves them and the Chairperson publishes them.</span>
                    @endif
                </a>
            </li>
        @empty
            <li class="rounded-[12px] border border-dashed border-line bg-white p-8 text-center text-slate-muted sm:col-span-2 lg:col-span-3">
                No barangays are taking part yet.
            </li>
        @endforelse
    </ul>

    <p class="mt-8 flex max-w-3xl items-start gap-3 rounded-[12px] bg-white p-4 text-sm leading-relaxed text-navy/75">
        <svg viewBox="0 0 24 24" class="mt-0.5 h-5 w-5 shrink-0 text-info" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/></svg>
        This board makes approved information easier to reach. It doesn’t replace the documents the SK posts at the barangay hall, and personal details of youth are never published here.
    </p>
</x-layouts.public>
