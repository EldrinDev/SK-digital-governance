<x-layouts.public title="Events" description="Upcoming youth events and consultations from barangay SK councils in Olongapo City.">
    <div class="max-w-2xl">
        <h1 class="font-display text-3xl font-semibold tracking-tight sm:text-4xl">Events and consultations</h1>
        <p class="mt-3 leading-relaxed text-slate-muted">Join programs run by your barangay SK, or have your say in a youth consultation. Registration is for youth of the barangay hosting the event.</p>
    </div>

    <form method="GET" class="mt-8 flex flex-wrap items-center gap-3 border-b border-line pb-5">
        <div class="flex flex-wrap gap-1.5" role="group" aria-label="Kind of event">
            @foreach ([null, ...$kinds] as $kind)
                @php($isActive = $selectedKind === $kind)
                <a
                    href="{{ route('events.index', array_filter(['barangay' => $selectedBarangay, 'kind' => $kind?->value])) }}"
                    @class([
                        'rounded-full border px-3.5 py-1.5 text-sm font-semibold transition-colors',
                        'border-navy bg-navy text-white' => $isActive,
                        'border-line bg-white text-navy/75 hover:border-navy/40' => ! $isActive,
                    ])
                    @if ($isActive) aria-current="page" @endif
                >{{ $kind ? $kind->label().'s' : 'All' }}</a>
            @endforeach
        </div>
        <div class="flex gap-2 sm:ml-auto">
            @if ($selectedKind)
                <input type="hidden" name="kind" value="{{ $selectedKind->value }}">
            @endif
            <label for="barangay" class="sr-only">Barangay</label>
            <select id="barangay" name="barangay" class="rounded-[8px] border border-line bg-white px-3 py-2 text-sm" onchange="this.form.requestSubmit()">
                <option value="">All barangays</option>
                @foreach ($barangays as $barangay)
                    <option value="{{ $barangay->id }}" @selected($selectedBarangay === $barangay->id)>{{ $barangay->name }}</option>
                @endforeach
            </select>
            <noscript><button type="submit" class="rounded-[8px] bg-navy px-4 py-2 text-sm font-semibold text-white">Show</button></noscript>
        </div>
    </form>

    @forelse ($months as $month => $monthEvents)
        <section class="mt-8" aria-labelledby="month-{{ $loop->index }}">
            <h2 id="month-{{ $loop->index }}" class="font-display text-lg font-semibold">{{ $month }}</h2>
            <ul class="mt-4 space-y-3">
                @foreach ($monthEvents as $event)
                    @php($isRegistered = in_array($event->id, $registeredEventIds, true))
                    <li>
                        <a href="{{ route('events.show', $event) }}" class="group flex flex-col gap-4 rounded-[14px] border border-line bg-white p-4 transition-colors hover:border-emerald/50 sm:flex-row sm:items-center sm:p-5">
                            <div class="flex min-w-0 flex-1 gap-4">
                                <x-event.date-block :event="$event" />
                                <div class="min-w-0 py-0.5">
                                    <p class="flex flex-wrap items-center gap-2 text-xs">
                                        <span class="font-semibold text-emerald">SK {{ $event->barangay->name }}</span>
                                        @if ($event->kind === \App\EventKind::Consultation)
                                            <span class="rounded-full bg-gold/25 px-2 py-0.5 font-semibold text-navy">Youth consultation</span>
                                        @endif
                                        @if ($isRegistered)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald/10 px-2 py-0.5 font-semibold text-emerald">
                                                <svg viewBox="0 0 24 24" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg>
                                                You’re registered
                                            </span>
                                        @endif
                                    </p>
                                    <h3 class="mt-1 font-display text-lg font-semibold leading-snug group-hover:text-emerald">{{ $event->title }}</h3>
                                    <p class="mt-1.5 flex flex-wrap gap-x-4 gap-y-1 text-sm text-slate-muted">
                                        <span class="inline-flex items-center gap-1.5">
                                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                                            {{ $event->starts_at->format('g:i A') }}
                                        </span>
                                        <span class="inline-flex items-center gap-1.5">
                                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21Z"/><circle cx="12" cy="9.5" r="2.5"/></svg>
                                            {{ $event->venue }}
                                        </span>
                                        <span>{{ $event->starts_at->isToday() ? 'Today' : ($event->starts_at->isTomorrow() ? 'Tomorrow' : 'In '.(int) ceil(now()->diffInDays($event->starts_at)).' days') }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 border-t border-line pt-3 sm:w-56 sm:flex-col sm:items-stretch sm:border-l sm:border-t-0 sm:pl-5 sm:pt-0">
                                <x-event.capacity :event="$event" class="flex-1 sm:flex-none" />
                                <span @class([
                                    'shrink-0 rounded-[8px] px-3.5 py-2 text-center text-sm font-semibold',
                                    'bg-mist text-slate-muted' => $isRegistered || $event->isFull(),
                                    'bg-emerald text-white group-hover:bg-emerald-dark' => ! $isRegistered && ! $event->isFull(),
                                ])>{{ $isRegistered ? 'View details' : ($event->isFull() ? 'See details' : 'Register') }}</span>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @empty
        <div class="mt-10 rounded-[14px] border border-dashed border-line bg-white p-10 text-center">
            <p class="font-display text-lg font-semibold">Nothing scheduled{{ $selectedBarangay || $selectedKind ? ' for this filter' : '' }} yet</p>
            <p class="mt-1 text-sm text-slate-muted">Your SK posts events and consultations here. Follow the announcements so you hear first.</p>
            <div class="mt-5 flex flex-wrap justify-center gap-3">
                @if ($selectedBarangay || $selectedKind)
                    <a href="{{ route('events.index') }}" class="rounded-[8px] border border-line px-4 py-2 text-sm font-semibold hover:border-navy">Show all events</a>
                @endif
                <a href="{{ route('announcements.index') }}" class="rounded-[8px] bg-navy px-4 py-2 text-sm font-semibold text-white hover:bg-navy-deep">Read announcements</a>
            </div>
        </div>
    @endforelse

    <div class="mt-8">{{ $events->links() }}</div>
</x-layouts.public>
