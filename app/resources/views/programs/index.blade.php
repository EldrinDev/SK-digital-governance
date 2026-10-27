@php
    $myBarangay = auth()->user()?->barangay;
    $query = fn (array $changes): string => route('programs.index', array_filter([
        'barangay' => $selectedBarangay,
        'area' => $selectedArea?->value,
        'status' => $selectedStatus,
        ...$changes,
    ], fn ($value) => $value !== null && $value !== ''));
    $statusTabs = [null => 'All', 'ongoing' => 'Happening now', 'upcoming' => 'Coming soon', 'completed' => 'Completed'];
    $hasFilters = $selectedBarangay || $selectedArea || $selectedStatus;
@endphp
<x-layouts.public title="Programs" description="Youth programs of the barangay SK councils of Olongapo City: what each is doing, how far along it is, and how to join.">
    <div class="max-w-2xl">
        <h1 class="font-display text-3xl font-semibold tracking-tight sm:text-4xl">SK programs</h1>
        <p class="mt-3 leading-relaxed text-slate-muted">What your barangay SK is doing for the youth this year: scholarships, sports, health, the environment and more. See how far along each program is and join its events.</p>
    </div>

    <div class="mt-8 rounded-[16px] border border-line bg-white p-4 sm:p-5">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <nav class="flex flex-wrap gap-1.5" aria-label="Program status">
                @foreach ($statusTabs as $value => $label)
                    @php
                        $isActive = $selectedStatus === ($value ?: null);
                        $count = $value ? $statusCounts[$value] : array_sum($statusCounts);
                    @endphp
                    <a href="{{ $query(['status' => $value ?: null, 'page' => null]) }}" @class([
                        'inline-flex items-center gap-1.5 rounded-full border px-3.5 py-1.5 text-sm font-semibold transition-colors',
                        'border-navy bg-navy text-white' => $isActive,
                        'border-line bg-white text-navy/75 hover:border-navy/40' => ! $isActive,
                    ]) @if ($isActive) aria-current="page" @endif>
                        {{ $label }}
                        <span @class(['rounded-full px-1.5 text-xs tabular-nums', 'bg-white/20' => $isActive, 'bg-mist text-slate-muted' => ! $isActive])>{{ $count }}</span>
                    </a>
                @endforeach
            </nav>

            <form method="GET" action="{{ route('programs.index') }}" class="flex flex-col gap-2 sm:flex-row">
                @if ($selectedStatus)
                    <input type="hidden" name="status" value="{{ $selectedStatus }}">
                @endif
                <label for="area" class="sr-only">Area</label>
                <select id="area" name="area" onchange="this.form.requestSubmit()" class="h-11 rounded-[10px] border border-line bg-white px-3 text-sm sm:w-56">
                    <option value="">All areas</option>
                    @foreach ($areas as $area)
                        <option value="{{ $area->value }}" @selected($selectedArea === $area)>{{ $area->label() }}</option>
                    @endforeach
                </select>
                <label for="barangay" class="sr-only">Barangay</label>
                <select id="barangay" name="barangay" onchange="this.form.requestSubmit()" class="h-11 rounded-[10px] border border-line bg-white px-3 text-sm sm:w-52">
                    <option value="">All barangays</option>
                    @foreach ($barangays as $barangay)
                        <option value="{{ $barangay->id }}" @selected($selectedBarangay === $barangay->id)>{{ $barangay->name }}</option>
                    @endforeach
                </select>
                <noscript><button type="submit" class="h-11 rounded-[10px] bg-navy px-5 text-sm font-semibold text-white">Show</button></noscript>
            </form>
        </div>

        @if (($myBarangay && $selectedBarangay !== $myBarangay->id) || $hasFilters)
            <div class="mt-4 flex flex-wrap items-center gap-3 border-t border-line pt-4">
                @if ($myBarangay && $selectedBarangay !== $myBarangay->id)
                    <a href="{{ $query(['barangay' => $myBarangay->id, 'page' => null]) }}" class="inline-flex items-center gap-1.5 rounded-full border border-emerald/40 bg-emerald/5 px-3.5 py-1.5 text-sm font-semibold text-emerald hover:border-emerald">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21Z"/><circle cx="12" cy="9.5" r="2.5"/></svg>
                        Only {{ $myBarangay->name }}
                    </a>
                @endif
                @if ($hasFilters)
                    <a href="{{ route('programs.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-emerald hover:underline sm:ml-auto">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        Clear filters
                    </a>
                @endif
            </div>
        @endif
    </div>

    @if ($programs->isEmpty())
        <div class="mt-6 rounded-[16px] border border-dashed border-line bg-white p-10 text-center">
            <p class="font-display text-lg font-semibold">{{ $hasFilters ? 'No programs match these filters' : 'No programs published yet' }}</p>
            <p class="mt-1 text-sm text-slate-muted">Programs show up here once the SK council approves its yearly plan (ABYIP) and the Chairperson publishes it.</p>
            @if ($hasFilters)
                <a href="{{ route('programs.index') }}" class="mt-5 inline-flex rounded-[8px] bg-navy px-4 py-2 text-sm font-semibold text-white hover:bg-navy-deep">Show all programs</a>
            @endif
        </div>
    @else
        <div class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($programs as $program)
                <x-program.card :program="$program" />
            @endforeach
        </div>
    @endif

    <div class="mt-10">{{ $programs->onEachSide(1)->links() }}</div>
</x-layouts.public>
