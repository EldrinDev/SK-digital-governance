<x-layouts.public title="Transparency board" description="Approved youth programs, budgets, and progress from participating barangay SK councils in Olongapo City.">
    <div class="max-w-2xl">
        <h1 class="font-display text-3xl font-semibold tracking-tight sm:text-4xl">Transparency board</h1>
        <p class="mt-3 leading-relaxed text-slate-muted">
            See each barangay SK’s approved investment program: the projects planned, how much each one costs, and how far along it is. Only programs the council has approved and published appear here.
        </p>
    </div>

    <ul class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($barangays as $barangay)
            <li>
                <a href="{{ route('transparency.show', $barangay) }}" class="flex h-full flex-col rounded-[12px] border border-line bg-white p-5 transition-colors hover:border-emerald/50">
                    <span class="font-display text-lg font-semibold">{{ $barangay->name }}</span>
                    @if ($barangay->published_abyips_count > 0)
                        <span class="mt-1 text-sm text-slate-muted">{{ trans_choice(':count published program|:count published programs', $barangay->published_abyips_count) }}</span>
                        <span class="mt-4 font-display text-2xl font-semibold">₱{{ number_format($barangay->published_budget ?? 0) }}</span>
                        <span class="text-xs text-slate-muted">total budget published</span>
                    @else
                        <span class="mt-1 text-sm text-slate-muted">Nothing published yet.</span>
                    @endif
                </a>
            </li>
        @empty
            <li class="rounded-[12px] border border-dashed border-line bg-white p-8 text-center text-slate-muted sm:col-span-2 lg:col-span-3">
                No barangays are taking part yet.
            </li>
        @endforelse
    </ul>
</x-layouts.public>
