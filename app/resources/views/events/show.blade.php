@php
    use App\EventKind;
    use App\UserRole;

    $user = auth()->user();
    $barangay = $event->barangay;
    $isConsultation = $event->kind === EventKind::Consultation;
    $mapUrl = 'https://www.google.com/maps/search/?api=1&query='.urlencode("{$event->venue}, {$barangay->name}, Olongapo City");
    $isYouth = $user?->hasRole(UserRole::Youth);
    $canRegister = $isYouth && $user->barangay_id === $event->barangay_id;
    $countdown = match (true) {
        $event->hasStarted() && $event->starts_at->isToday() => 'Happening today',
        $event->hasStarted() => 'This event has passed',
        $event->starts_at->isToday() => 'Today',
        $event->starts_at->isTomorrow() => 'Tomorrow',
        default => 'In '.(int) ceil(now()->diffInDays($event->starts_at)).' days',
    };
    $sectionTitle = 'mb-3 font-display text-base font-semibold text-navy';
    $sheet = 'overflow-hidden rounded-[14px] border border-line bg-white';
@endphp
<x-layouts.public :title="$event->title" :description="$event->kind->label().' by the '.$barangay->name.' SK on '.$event->starts_at->format('F j, Y').'.'">
    <nav class="flex flex-wrap items-center gap-1.5 text-sm text-slate-muted" aria-label="Breadcrumb">
        <a href="{{ route('events.index') }}" class="font-medium hover:text-emerald">Events</a>
        <span aria-hidden="true">/</span>
        <a href="{{ route('events.index', ['scope' => 'all', 'barangay' => $barangay->id]) }}" class="font-medium hover:text-emerald">SK {{ $barangay->name }}</a>
    </nav>

    <div class="mt-5 grid items-start gap-x-8 gap-y-10 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <article class="min-w-0">
            <header>
                <p class="flex flex-wrap items-center gap-2 text-sm">
                    <img src="{{ $barangay->logoUrl() }}" alt="" class="h-6 w-6 rounded-full border border-line bg-white object-contain">
                    <span @class(['font-semibold', 'text-[#92400e]' => $isConsultation, 'text-emerald' => ! $isConsultation])>{{ $isConsultation ? 'Youth consultation' : 'Event' }}</span>
                    <span class="text-slate-muted">by SK {{ $barangay->name }}</span>
                </p>
                <h1 class="mt-2 max-w-3xl font-display text-3xl font-semibold leading-tight tracking-tight text-navy sm:text-4xl">{{ $event->title }}</h1>
            </header>

            {{-- The facts, in the order people look for them --}}
            <dl class="{{ $sheet }} mt-6 divide-y divide-line">
                <div class="grid gap-1 px-5 py-4 sm:grid-cols-[8.5rem_minmax(0,1fr)] sm:gap-4">
                    <dt class="text-sm text-slate-muted">When</dt>
                    <dd>
                        <p class="font-medium text-navy">{{ $event->starts_at->format('l, F j, Y') }}</p>
                        <p class="text-sm text-navy/75">{{ $event->starts_at->format('g:i A') }}, <span @class(['font-semibold', 'text-emerald' => ! $event->hasStarted(), 'text-slate-muted' => $event->hasStarted()])>{{ str($countdown)->lcfirst() }}</span></p>
                    </dd>
                </div>
                <div class="grid gap-1 px-5 py-4 sm:grid-cols-[8.5rem_minmax(0,1fr)] sm:gap-4">
                    <dt class="text-sm text-slate-muted">Where</dt>
                    <dd class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                        <span class="font-medium text-navy">{{ $event->venue }}, {{ $barangay->name }}</span>
                        <a href="{{ $mapUrl }}" target="_blank" rel="noopener" class="text-sm font-medium text-emerald hover:underline">Open in Maps<span class="sr-only"> (opens in a new tab)</span></a>
                    </dd>
                </div>
                <div class="grid gap-1 px-5 py-4 sm:grid-cols-[8.5rem_minmax(0,1fr)] sm:gap-4">
                    <dt class="text-sm text-slate-muted">Who can join</dt>
                    <dd class="font-medium text-navy">Youth of {{ $barangay->name }}, ages 15 to 30</dd>
                </div>
                @if ($program)
                    <div class="grid gap-1 px-5 py-4 sm:grid-cols-[8.5rem_minmax(0,1fr)] sm:gap-4">
                        <dt class="text-sm text-slate-muted">Part of</dt>
                        <dd class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                            <a href="{{ route('programs.show', $program) }}" class="font-medium text-navy hover:text-emerald">{{ $program->title }}</a>
                            <span class="text-sm text-slate-muted">{{ $program->priority->center->label() }} program</span>
                        </dd>
                    </div>
                @endif
            </dl>

            <section class="mt-10" aria-labelledby="about-heading">
                <h2 id="about-heading" class="{{ $sectionTitle }}">{{ $isConsultation ? 'About this consultation' : 'About this event' }}</h2>
                <div class="{{ $sheet }} px-5 py-5 sm:px-6">
                    @if ($event->description)
                        <p class="max-w-[65ch] whitespace-pre-line leading-relaxed text-navy/85">{{ $event->description }}</p>
                    @else
                        <p class="text-sm text-slate-muted">The SK hasn’t added details yet. Its announcements will have the latest.</p>
                    @endif
                    @if ($isConsultation)
                        <p class="mt-4 max-w-[65ch] border-l-2 border-gold pl-4 text-sm leading-relaxed text-navy/80">
                            In a consultation, the SK asks the Katipunan ng Kabataan what to include in its plans. What you say here can shape next year’s programs and budget.
                        </p>
                    @endif
                </div>
            </section>

            @if ($moreEvents->isNotEmpty())
                <section class="mt-10" aria-labelledby="more-heading">
                    <div class="mb-3 flex items-end justify-between gap-3">
                        <h2 id="more-heading" class="font-display text-base font-semibold text-navy">More from SK {{ $barangay->name }}</h2>
                        <a href="{{ route('events.index', ['scope' => 'all', 'barangay' => $barangay->id]) }}" class="text-sm font-medium text-emerald hover:underline">All their events</a>
                    </div>
                    <ul class="{{ $sheet }} divide-y divide-line">
                        @foreach ($moreEvents as $other)
                            <li>
                                <a href="{{ route('events.show', $other) }}" class="flex items-center gap-5 px-5 py-3.5 hover:bg-mist/60">
                                    <span class="w-11 shrink-0 text-center leading-none">
                                        <span class="block text-[0.7rem] font-semibold text-slate-muted">{{ $other->starts_at->format('M') }}</span>
                                        <span class="mt-1 block font-display text-xl font-semibold text-navy">{{ $other->starts_at->format('j') }}</span>
                                    </span>
                                    <span class="min-w-0 border-l border-line pl-5">
                                        <span class="block truncate font-medium text-navy">{{ $other->title }}</span>
                                        <span class="block truncate text-sm text-slate-muted">{{ $other->starts_at->format('g:i A') }}, {{ $other->venue }}</span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </article>

        <aside class="min-w-0 space-y-6 lg:sticky lg:top-24">
            @if ($registration && $registration->attended())
                {{-- They came: the record of their check-in --}}
                <section aria-labelledby="attended-heading">
                    <h2 id="attended-heading" class="{{ $sectionTitle }}">You attended</h2>
                    <div class="{{ $sheet }} p-5">
                        <p class="text-sm text-navy/80">
                            Checked in {{ $registration->attended_at->format('M j, g:i A') }}{{ $registration->check_in_method ? ' by '.str($registration->check_in_method->label())->lower() : '' }}{{ $registration->checkedInBy ? ', '.$registration->checkedInBy->name : '' }}.
                        </p>
                        <a href="{{ route('dashboard') }}#todo" class="mt-3 inline-block text-sm font-semibold text-emerald hover:underline">Rate it on your dashboard</a>
                    </div>
                </section>
            @elseif ($registration)
                {{-- Registered: the pass to show at the table --}}
                <section aria-labelledby="pass-heading">
                    <h2 id="pass-heading" class="{{ $sectionTitle }}">You’re registered</h2>
                    <x-event.pass :event="$event" :member-code="$memberCode" label="Your pass" />
                    <p class="mt-4 text-sm leading-relaxed text-slate-muted">Show the QR at the registration table. An SK officer scans it to check you in. We’ll remind you the evening before.</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @unless ($event->hasStarted())
                            <a href="{{ route('events.calendar', $event) }}" class="inline-flex items-center gap-2 rounded-[8px] border border-line bg-white px-3.5 py-2 text-sm font-semibold hover:border-navy">
                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18M12 14v4M10 16h4"/></svg>
                                Add to my calendar
                            </a>
                        @endunless
                        <button type="button" data-share data-share-title="{{ $event->title }}" data-share-url="{{ route('events.show', $event) }}" class="inline-flex items-center gap-2 rounded-[8px] border border-line bg-white px-3.5 py-2 text-sm font-semibold hover:border-navy">
                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 13.5 6.8 4M15.4 6.5l-6.8 4"/></svg>
                            <span data-share-label>Invite a friend</span>
                        </button>
                    </div>
                    @unless ($event->hasStarted())
                        <form method="POST" action="{{ route('events.registration.destroy', $event) }}" class="mt-4 border-t border-line pt-3" onsubmit="return confirm('Cancel your registration? Your place goes to someone else.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm font-medium text-slate-muted hover:text-[#b91c1c]">Can’t make it? Cancel my registration</button>
                        </form>
                    @endunless
                </section>
            @else
                {{-- Not registered yet --}}
                <section aria-labelledby="register-heading">
                    <h2 id="register-heading" class="{{ $sectionTitle }}">Registration</h2>
                    <div class="{{ $sheet }} p-5">
                        <x-event.capacity :event="$event" />
                        <p class="sr-only">
                            @if ($event->capacity === null)
                                Open registration.
                            @else
                                {{ $event->spotsLeft() }} of {{ $event->capacity }} places left.
                            @endif
                        </p>

                        @error('registration')
                            <p role="alert" class="mt-4 rounded-[8px] bg-error/5 p-3 text-sm text-[#b91c1c]">{{ $message }}</p>
                        @enderror

                        <div class="mt-5">
                            @if ($event->hasStarted())
                                <p class="text-sm text-slate-muted">Registration is closed because the event has started.</p>
                            @elseif (! $user)
                                <a href="{{ route('login') }}" class="block rounded-[8px] bg-emerald px-4 py-3 text-center text-sm font-semibold text-white hover:bg-emerald-dark">Log in to register</a>
                                <p class="mt-3 text-center text-xs text-slate-muted">No account yet? <a href="{{ route('register') }}" class="font-semibold text-emerald hover:underline">Create one</a>. It takes a minute.</p>
                            @elseif (! $isYouth)
                                <p class="text-sm text-slate-muted">Registration is for youth accounts.</p>
                            @elseif (! $canRegister)
                                <p class="text-sm text-slate-muted">This event is for youth of {{ $barangay->name }}.</p>
                                <a href="{{ route('events.index') }}" class="mt-2 inline-block text-sm font-semibold text-emerald hover:underline">See events for you</a>
                            @elseif ($event->isFull())
                                <p class="text-sm text-slate-muted">Every place is taken. Watch your SK’s announcements in case places open up.</p>
                            @else
                                <form method="POST" action="{{ route('events.registration.store', $event) }}">
                                    @csrf
                                    <x-form.submit busy="Registering…">Register for this {{ $isConsultation ? 'consultation' : 'event' }}</x-form.submit>
                                </form>
                                <p class="mt-3 text-center text-xs text-slate-muted">You’ll get your pass right away, and a reminder the evening before.</p>
                            @endif
                        </div>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-x-5 gap-y-2 px-1 text-sm font-medium">
                        @unless ($event->hasStarted())
                            <a href="{{ route('events.calendar', $event) }}" class="text-emerald hover:underline">Add to my calendar</a>
                        @endunless
                        <button type="button" data-share data-share-title="{{ $event->title }}" data-share-url="{{ route('events.show', $event) }}" class="text-emerald hover:underline"><span data-share-label>Share this event</span></button>
                    </div>
                </section>
            @endif

            <x-barangay.facebook-link :barangay="$barangay" class="flex w-full justify-center" />
        </aside>
    </div>
</x-layouts.public>
