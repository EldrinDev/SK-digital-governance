@php
    use App\EventKind;

    $query = fn (array $changes): string => route('events.index', array_filter([
        'scope' => $youth ? $scope : null,
        'barangay' => $selectedBarangay,
        'kind' => $selectedKind?->value,
        'date' => $selectedDate?->format('Y-m-d'),
        ...$changes,
    ], fn ($value) => $value !== null && $value !== ''));
    $inDays = fn ($date): string => $date->isToday() ? 'today' : ($date->isTomorrow() ? 'tomorrow' : 'in '.(int) ceil(now()->diffInDays($date)).' days');
    $tab = 'rounded-[8px] px-3.5 py-1.5 text-sm font-semibold transition-colors';
    $sheet = 'overflow-hidden rounded-[14px] border border-line bg-white';
@endphp
<x-layouts.public title="Events" description="Upcoming youth events and consultations from barangay SK councils in Olongapo City.">
    <div class="max-w-2xl">
        <h1 class="font-display text-3xl font-semibold tracking-tight sm:text-4xl">Events and consultations</h1>
        <p class="mt-3 leading-relaxed text-slate-muted">Join what your barangay SK has planned, or have your say in a youth consultation. Registration is open to youth of the barangay that hosts the event.</p>
    </div>

    {{-- Whose events, and what kind --}}
    <div class="mt-8 flex flex-wrap items-center gap-3">
        @if ($youth)
            <nav class="inline-flex rounded-[10px] border border-line bg-white p-1" aria-label="Whose events">
                <a href="{{ $query(['scope' => 'mine', 'barangay' => null, 'page' => null]) }}" @class([$tab, 'bg-navy text-white' => $scope === 'mine', 'text-navy/70 hover:text-navy' => $scope !== 'mine']) @if ($scope === 'mine') aria-current="page" @endif>For you</a>
                <a href="{{ $query(['scope' => 'all', 'page' => null]) }}" @class([$tab, 'bg-navy text-white' => $scope === 'all', 'text-navy/70 hover:text-navy' => $scope !== 'all']) @if ($scope === 'all') aria-current="page" @endif>All barangays</a>
            </nav>
        @endif

        <nav class="inline-flex rounded-[10px] border border-line bg-white p-1" aria-label="Kind of event">
            @foreach ([null => 'All kinds', 'event' => 'Events', 'consultation' => 'Consultations'] as $value => $label)
                @php
                    $isActive = $selectedKind?->value === ($value ?: null);
                @endphp
                <a href="{{ $query(['kind' => $value ?: null, 'page' => null]) }}" @class([$tab, 'bg-navy text-white' => $isActive, 'text-navy/70 hover:text-navy' => ! $isActive]) @if ($isActive) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>

        @if ($scope === 'all')
            <form method="GET" data-live action="{{ route('events.index') }}" class="sm:ml-auto">
                @foreach (['scope' => $youth ? 'all' : null, 'kind' => $selectedKind?->value, 'date' => $selectedDate?->format('Y-m-d')] as $name => $value)
                    @if ($value)
                        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                    @endif
                @endforeach
                <label for="barangay" class="sr-only">Barangay</label>
                <select id="barangay" name="barangay" class="h-10 rounded-[10px] border border-line bg-white px-3 text-sm">
                    <option value="">Every barangay</option>
                    @foreach ($barangays as $barangay)
                        <option value="{{ $barangay->id }}" @selected($selectedBarangay === $barangay->id)>{{ $barangay->name }}</option>
                    @endforeach
                </select>
                <noscript><button type="submit" class="ml-1 rounded-[8px] bg-navy px-3 py-2 text-sm font-semibold text-white">Show</button></noscript>
            </form>
        @endif
    </div>

    @error('registration')
        <p role="alert" class="mt-6 rounded-[12px] border border-error/30 bg-error/5 p-4 text-sm">{{ $message }}</p>
    @enderror

    <div class="mt-8 grid items-start gap-x-8 gap-y-10 lg:grid-cols-[minmax(0,1fr)_19rem]">
        {{-- The agenda --}}
        <div class="min-w-0 space-y-8">
            @if ($selectedDate)
                <p class="flex flex-wrap items-center justify-between gap-3 rounded-[12px] bg-navy px-4 py-3 text-sm text-white">
                    <span>Showing events on <span class="font-semibold">{{ $selectedDate->format('l, F j') }}</span></span>
                    <a href="{{ $query(['date' => null, 'page' => null]) }}" class="font-semibold text-gold hover:underline">Show all dates</a>
                </p>
            @endif

            @forelse ($months as $month => $monthEvents)
                <section aria-labelledby="month-{{ $loop->index }}">
                    <h2 id="month-{{ $loop->index }}" class="mb-3 font-display text-base font-semibold text-navy">{{ $month }}</h2>
                    <ul class="{{ $sheet }} divide-y divide-line">
                        @foreach ($monthEvents as $event)
                            @php
                                $isConsultation = $event->kind === EventKind::Consultation;
                                $isRegistered = in_array($event->id, $registeredEventIds, true);
                                $canRegister = $youth && $youth->barangay_id === $event->barangay_id;
                                $left = $event->spotsLeft();
                                $places = match (true) {
                                    $event->capacity === null => 'Open registration',
                                    $event->isFull() => 'All places taken',
                                    $left <= max(3, (int) ceil($event->capacity * 0.2)) => 'Only '.$left.' '.str('place')->plural($left).' left',
                                    default => $left.' places left',
                                };
                            @endphp
                            <li class="flex flex-wrap items-center gap-x-5 gap-y-3 px-5 py-4">
                                <time datetime="{{ $event->starts_at->toIso8601String() }}" class="w-11 shrink-0 text-center leading-none">
                                    <span class="block text-[0.7rem] font-semibold text-slate-muted">{{ $event->starts_at->format('D') }}</span>
                                    <span class="mt-1 block font-display text-2xl font-semibold text-navy">{{ $event->starts_at->format('j') }}</span>
                                    <span class="mt-1 block text-[0.7rem] text-slate-muted">{{ $event->starts_at->format('g:i A') }}</span>
                                </time>
                                <div class="min-w-0 flex-1 border-l border-line pl-5">
                                    <p class="text-xs font-semibold">
                                        <span @class(['text-[#92400e]' => $isConsultation, 'text-emerald' => ! $isConsultation])>{{ $isConsultation ? 'Youth consultation' : 'Event' }}, {{ $inDays($event->starts_at) }}</span>
                                        @if ($scope === 'all')
                                            <span class="font-medium text-slate-muted">, SK {{ $event->barangay->name }}</span>
                                        @endif
                                    </p>
                                    <a href="{{ route('events.show', $event) }}" class="mt-0.5 block font-medium leading-snug text-navy hover:text-emerald">{{ $event->title }}</a>
                                    <p class="mt-0.5 truncate text-sm text-slate-muted">{{ $event->venue }}</p>
                                </div>
                                <div class="flex w-full items-center justify-between gap-4 pl-16 sm:w-40 sm:flex-col sm:items-end sm:justify-center sm:gap-1.5 sm:pl-0">
                                    @if ($isRegistered)
                                        <a href="{{ route('events.show', $event) }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-emerald hover:underline">
                                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg>
                                            You’re registered
                                        </a>
                                    @elseif ($canRegister && ! $event->isFull())
                                        <form method="POST" action="{{ route('events.registration.store', $event) }}">
                                            @csrf
                                            <button type="submit" class="rounded-[8px] border border-emerald px-4 py-1.5 text-sm font-semibold text-emerald hover:bg-emerald hover:text-white">Register<span class="sr-only"> for {{ $event->title }}</span></button>
                                        </form>
                                    @elseif ($youth && ! $canRegister)
                                        <span class="text-right text-xs text-slate-muted">For youth of {{ $event->barangay->name }}</span>
                                    @elseif (! $youth)
                                        <a href="{{ route('events.show', $event) }}" class="text-sm font-semibold text-emerald hover:underline">See details</a>
                                    @endif
                                    @unless ($youth && ! $canRegister)
                                        <span @class([
                                            'text-xs',
                                            'font-semibold text-[#b91c1c]' => $event->isFull(),
                                            'font-semibold text-[#92400e]' => ! $event->isFull() && str_starts_with($places, 'Only'),
                                            'text-slate-muted' => ! $event->isFull() && ! str_starts_with($places, 'Only'),
                                        ])>{{ $places }}</span>
                                    @endunless
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @empty
                <div class="rounded-[14px] border border-dashed border-line bg-white px-6 py-10 text-center">
                    <p class="font-display text-lg font-semibold text-navy">
                        @if ($selectedDate)
                            Nothing on {{ $selectedDate->format('F j') }}
                        @elseif ($scope === 'mine')
                            SK {{ $youth->barangay->name }} has nothing scheduled yet
                        @else
                            Nothing scheduled{{ $selectedBarangay || $selectedKind ? ' for this filter' : '' }} yet
                        @endif
                    </p>
                    <p class="mt-1 text-sm text-slate-muted">Your SK posts events and consultations here. Its announcements tell you first.</p>
                    <div class="mt-5 flex flex-wrap justify-center gap-3">
                        @if ($scope === 'mine')
                            <a href="{{ $query(['scope' => 'all', 'date' => null, 'page' => null]) }}" class="rounded-[8px] border border-line px-4 py-2 text-sm font-semibold hover:border-navy">See other barangays’ events</a>
                        @elseif ($selectedDate || $selectedBarangay || $selectedKind)
                            <a href="{{ route('events.index', $youth ? ['scope' => 'all'] : []) }}" class="rounded-[8px] border border-line px-4 py-2 text-sm font-semibold hover:border-navy">Show every event</a>
                        @endif
                        <a href="{{ route('announcements.index') }}" class="rounded-[8px] bg-navy px-4 py-2 text-sm font-semibold text-white hover:bg-navy-deep">Read announcements</a>
                    </div>
                </div>
            @endforelse

            <div>{{ $events->onEachSide(1)->links() }}</div>
        </div>

        {{-- The month at a glance, and the youth's own registrations --}}
        <aside class="min-w-0 space-y-10 lg:sticky lg:top-24">
            <section aria-labelledby="calendar-heading">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <h2 id="calendar-heading" class="font-display text-base font-semibold text-navy">{{ $calendar['month']->format('F Y') }}</h2>
                    <div class="flex gap-1">
                        @if ($calendar['previous'])
                            <a href="{{ $query(['month' => $calendar['previous'], 'date' => null, 'page' => null]) }}" class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-line bg-white text-navy hover:border-navy" aria-label="Previous month">
                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                            </a>
                        @endif
                        <a href="{{ $query(['month' => $calendar['next'], 'date' => null, 'page' => null]) }}" class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-line bg-white text-navy hover:border-navy" aria-label="Next month">
                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                        </a>
                    </div>
                </div>
                <div class="{{ $sheet }} p-3">
                    <table class="w-full table-fixed text-center text-sm">
                        <caption class="sr-only">Days with events in {{ $calendar['month']->format('F Y') }}. Pick a day to see its events.</caption>
                        <thead>
                            <tr>
                                @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $weekday)
                                    <th scope="col" class="pb-1.5 text-[0.7rem] font-medium text-slate-muted"><abbr title="{{ $weekday }}" class="no-underline">{{ mb_substr($weekday, 0, 1) }}</abbr></th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($calendar['weeks'] as $week)
                                <tr>
                                    @foreach ($week as $day)
                                        @php
                                            $key = $day->format('Y-m-d');
                                            $info = $calendar['days'][$key] ?? null;
                                            $inMonth = $day->month === $calendar['month']->month;
                                            $isSelected = $selectedDate?->isSameDay($day);
                                        @endphp
                                        <td class="p-0.5">
                                            @if ($info && $inMonth)
                                                <a href="{{ $query(['date' => $key, 'month' => null, 'page' => null]) }}" @class([
                                                    'relative mx-auto flex h-9 w-9 items-center justify-center rounded-full font-semibold transition-colors',
                                                    'bg-navy text-white' => $isSelected,
                                                    'text-navy hover:bg-mist' => ! $isSelected,
                                                ]) @if ($isSelected) aria-current="date" @endif>
                                                    {{ $day->day }}
                                                    <span @class(['absolute bottom-1 h-1 w-1 rounded-full', 'bg-gold' => $info['consultation'], 'bg-emerald' => ! $info['consultation'] && ! $isSelected, 'bg-white' => ! $info['consultation'] && $isSelected]) aria-hidden="true"></span>
                                                    <span class="sr-only">{{ trans_choice('{1}, 1 event|[2,*], :count events', $info['count']) }} on {{ $day->format('F j') }}</span>
                                                </a>
                                            @else
                                                <span @class([
                                                    'mx-auto flex h-9 w-9 items-center justify-center rounded-full',
                                                    'text-line' => ! $inMonth,
                                                    'text-slate-muted/60' => $inMonth && $day->isPast() && ! $day->isToday(),
                                                    'text-navy/60' => $inMonth && ($day->isFuture() || $day->isToday()),
                                                    'ring-1 ring-navy/30' => $day->isToday(),
                                                ])>{{ $day->day }}</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <p class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 border-t border-line px-1 pt-2.5 text-xs text-slate-muted">
                        <span class="inline-flex items-center gap-1.5"><span class="h-1.5 w-1.5 rounded-full bg-emerald" aria-hidden="true"></span>Event</span>
                        <span class="inline-flex items-center gap-1.5"><span class="h-1.5 w-1.5 rounded-full bg-gold" aria-hidden="true"></span>Consultation</span>
                        <span class="inline-flex items-center gap-1.5"><span class="h-3 w-3 rounded-full ring-1 ring-navy/30" aria-hidden="true"></span>Today</span>
                    </p>
                </div>
            </section>

            @if ($youth)
                <section aria-labelledby="registered-heading">
                    <h2 id="registered-heading" class="mb-3 font-display text-base font-semibold text-navy">You’re going to</h2>
                    @if ($myRegistrations->isEmpty())
                        <p class="rounded-[14px] border border-dashed border-line bg-white px-4 py-5 text-sm text-slate-muted">Nothing yet. Events you register for show up here, with your pass for check-in.</p>
                    @else
                        <ul class="{{ $sheet }} divide-y divide-line">
                            @foreach ($myRegistrations as $registration)
                                <li>
                                    <a href="{{ route('events.show', $registration->event) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-mist/60">
                                        <span class="w-9 shrink-0 text-center leading-none">
                                            <span class="block text-[0.65rem] font-semibold text-slate-muted">{{ $registration->event->starts_at->format('M') }}</span>
                                            <span class="mt-0.5 block font-display text-lg font-semibold text-navy">{{ $registration->event->starts_at->format('j') }}</span>
                                        </span>
                                        <span class="min-w-0">
                                            <span class="block truncate text-sm font-medium text-navy">{{ $registration->event->title }}</span>
                                            <span class="block text-xs text-slate-muted">{{ ucfirst($inDays($registration->event->starts_at)) }}, show your pass</span>
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            @endif
        </aside>
    </div>
</x-layouts.public>
