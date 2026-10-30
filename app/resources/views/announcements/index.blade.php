@php
    $viewer = auth()->user();
    $myBarangay = $viewer?->barangay;
    $total = $categoryCounts->sum();
    $query = fn (array $changes): string => route('announcements.index', array_filter([
        'barangay' => $selectedBarangay,
        'category' => $selectedCategory?->value,
        'q' => $search ?: null,
        ...$changes,
    ], fn ($value) => $value !== null && $value !== ''));
    $hasFilters = $selectedBarangay || $selectedCategory || $search !== '';
    $selectedBarangayName = $selectedBarangay ? $barangays->firstWhere('id', $selectedBarangay)?->name : null;
@endphp
<x-layouts.public title="Announcements" description="News, reminders and invitations from barangay SK councils in Olongapo City.">
    <div class="max-w-2xl">
        <h1 class="font-display text-3xl font-semibold tracking-tight sm:text-4xl">Announcements</h1>
        <p class="mt-3 leading-relaxed text-slate-muted">News, reminders and invitations from the barangay SK councils, in one place instead of scattered across group chats and pages.</p>
    </div>

    {{-- Filters: kind of post, search, and barangay --}}
    <div class="mt-8 rounded-[16px] border border-line bg-white p-4 sm:p-5">
        <form method="GET" data-live action="{{ route('announcements.index') }}" class="flex flex-col gap-3 lg:flex-row lg:items-center" role="search">
            @if ($selectedCategory)
                <input type="hidden" name="category" value="{{ $selectedCategory->value }}">
            @endif
            <div class="relative flex-1">
                <label for="q" class="sr-only">Search announcements</label>
                <svg viewBox="0 0 24 24" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-muted" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <input id="q" name="q" type="search" value="{{ $search }}" placeholder="Search, e.g. clean-up, liga, scholarship" class="block h-11 w-full rounded-[10px] border border-line bg-mist/50 pl-9 pr-3 text-sm outline-none focus:border-emerald focus:bg-white focus:ring-3 focus:ring-emerald/20">
            </div>
            <div class="flex gap-2">
                <label for="barangay" class="sr-only">Barangay</label>
                <select id="barangay" name="barangay" class="h-11 min-w-0 flex-1 rounded-[10px] border border-line bg-white px-3 text-sm lg:w-56 lg:flex-none">
                    <option value="">All barangays ({{ $barangays->count() }})</option>
                    @foreach ($barangays as $barangay)
                        <option value="{{ $barangay->id }}" @selected($selectedBarangay === $barangay->id)>{{ $barangay->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="h-11 rounded-[10px] bg-navy px-5 text-sm font-semibold text-white hover:bg-navy-deep">Search</button>
            </div>
        </form>

        <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-line pt-4">
            <nav class="flex flex-wrap gap-1.5" aria-label="Kind of post">
                @foreach ([null, ...$categories] as $category)
                    @php
                        $isActive = $selectedCategory === $category;
                        $count = $category ? (int) ($categoryCounts[$category->value] ?? 0) : $total;
                    @endphp
                    <a href="{{ $query(['category' => $category?->value, 'page' => null]) }}" @class([
                        'inline-flex items-center gap-1.5 rounded-full border px-3.5 py-1.5 text-sm font-semibold transition-colors',
                        'border-navy bg-navy text-white' => $isActive,
                        'border-line bg-white text-navy/75 hover:border-navy/40' => ! $isActive,
                    ]) @if ($isActive) aria-current="page" @endif>
                        {{ $category?->label() ?? 'All' }}
                        <span @class(['rounded-full px-1.5 text-xs tabular-nums', 'bg-white/20' => $isActive, 'bg-mist text-slate-muted' => ! $isActive])>{{ $count }}</span>
                    </a>
                @endforeach
            </nav>
            @if ($myBarangay && $selectedBarangay !== $myBarangay->id)
                <a href="{{ $query(['barangay' => $myBarangay->id, 'page' => null]) }}" class="inline-flex items-center gap-1.5 rounded-full border border-emerald/40 bg-emerald/5 px-3.5 py-1.5 text-sm font-semibold text-emerald hover:border-emerald lg:ml-auto">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21Z"/><circle cx="12" cy="9.5" r="2.5"/></svg>
                    Only {{ $myBarangay->name }}
                </a>
            @endif
        </div>
    </div>

    {{-- What is being shown, with a way back to everything --}}
    <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-muted">
            @if ($hasFilters)
                {{ trans_choice('{0} No posts|{1} 1 post|[2,*] :count posts', $announcements->total()) }}
                @if ($selectedCategory) of <span class="font-semibold text-navy">{{ $selectedCategory->label() }}</span>@endif
                @if ($selectedBarangayName) from <span class="font-semibold text-navy">SK {{ $selectedBarangayName }}</span>@endif
                @if ($search !== '') matching <span class="font-semibold text-navy">“{{ $search }}”</span>@endif
            @else
                Pinned posts first, then the newest.
            @endif
        </p>
        @if ($hasFilters)
            <a href="{{ route('announcements.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-emerald hover:underline">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                Clear filters
            </a>
        @endif
    </div>

    @if ($announcements->isEmpty())
        <div class="mt-4 rounded-[16px] border border-dashed border-line bg-white p-10 text-center">
            <p class="font-display text-lg font-semibold">{{ $hasFilters ? 'Nothing matches these filters' : 'No announcements yet' }}</p>
            <p class="mt-1 text-sm text-slate-muted">{{ $hasFilters ? 'Try another word, another kind of post, or all barangays.' : 'Posts from the barangay SK councils show up here. Check back soon.' }}</p>
            @if ($hasFilters)
                <a href="{{ route('announcements.index') }}" class="mt-5 inline-flex rounded-[8px] bg-navy px-4 py-2 text-sm font-semibold text-white hover:bg-navy-deep">Show all announcements</a>
            @endif
        </div>
    @else
        <div class="mt-4 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($announcements as $announcement)
                <x-announcement.post :announcement="$announcement" />
            @endforeach
        </div>
    @endif

    <div class="mt-10">{{ $announcements->onEachSide(1)->links() }}</div>
</x-layouts.public>
