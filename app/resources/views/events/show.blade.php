@php
    use App\EventKind;
    use App\UserRole;

    $user = auth()->user();
    $isConsultation = $event->kind === EventKind::Consultation;
    $mapUrl = 'https://www.google.com/maps/search/?api=1&query='.urlencode("{$event->venue}, {$event->barangay->name}, Olongapo City");
    $countdown = match (true) {
        $event->hasStarted() => 'Already started',
        $event->starts_at->isToday() => 'Today',
        $event->starts_at->isTomorrow() => 'Tomorrow',
        default => 'In '.(int) ceil(now()->diffInDays($event->starts_at)).' days',
    };
@endphp
<x-layouts.public :title="$event->title" :description="$event->kind->label().' by the '.$event->barangay->name.' SK on '.$event->starts_at->format('F j, Y').'.'">
    <p class="text-sm"><a href="{{ route('events.index') }}" class="font-medium text-slate-muted hover:text-emerald">&larr; All events</a></p>

    <div class="mt-5 grid gap-8 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <article class="min-w-0">
            <header class="flex flex-col gap-6 rounded-[16px] border border-line bg-white p-6 sm:flex-row sm:items-center sm:p-8">
                <x-event.date-block :event="$event" size="lg" />
                <div class="min-w-0">
                    <p class="flex flex-wrap items-center gap-2 text-sm">
                        <span class="font-semibold text-emerald">SK {{ $event->barangay->name }}</span>
                        <span @class(['rounded-full px-2.5 py-0.5 text-xs font-semibold', 'bg-gold/25 text-navy' => $isConsultation, 'bg-emerald/10 text-emerald' => ! $isConsultation])>{{ $event->kind->label() }}</span>
                    </p>
                    <h1 class="mt-2 font-display text-3xl font-semibold leading-tight tracking-tight sm:text-4xl">{{ $event->title }}</h1>
                </div>
            </header>

            <dl class="mt-5 grid gap-3 sm:grid-cols-3">
                <div class="rounded-[12px] border border-line bg-white p-4">
                    <dt class="flex items-center gap-2 text-sm text-slate-muted">
                        <svg viewBox="0 0 24 24" class="h-4 w-4 text-emerald" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                        When
                    </dt>
                    <dd class="mt-1.5 font-semibold">{{ $event->starts_at->format('l, F j') }}</dd>
                    <dd class="text-sm text-navy/75">{{ $event->starts_at->format('g:i A') }} · {{ $countdown }}</dd>
                </div>
                <div class="rounded-[12px] border border-line bg-white p-4">
                    <dt class="flex items-center gap-2 text-sm text-slate-muted">
                        <svg viewBox="0 0 24 24" class="h-4 w-4 text-emerald" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21Z"/><circle cx="12" cy="9.5" r="2.5"/></svg>
                        Where
                    </dt>
                    <dd class="mt-1.5 font-semibold">{{ $event->venue }}</dd>
                    <dd class="text-sm"><a href="{{ $mapUrl }}" target="_blank" rel="noopener" class="font-semibold text-emerald hover:underline">Open in Maps<span class="sr-only"> (opens in a new tab)</span></a></dd>
                </div>
                <div class="rounded-[12px] border border-line bg-white p-4">
                    <dt class="flex items-center gap-2 text-sm text-slate-muted">
                        <svg viewBox="0 0 24 24" class="h-4 w-4 text-emerald" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        Who can join
                    </dt>
                    <dd class="mt-1.5 font-semibold">Youth of {{ $event->barangay->name }}</dd>
                    <dd class="text-sm text-navy/75">Ages 15 to 30</dd>
                </div>
            </dl>

            <section class="mt-5 rounded-[12px] border border-line bg-white p-6 sm:p-8" aria-labelledby="about-heading">
                <h2 id="about-heading" class="font-display text-lg font-semibold">{{ $isConsultation ? 'About this consultation' : 'About this event' }}</h2>
                @if ($event->description)
                    <p class="mt-3 max-w-[68ch] whitespace-pre-line leading-relaxed text-navy/85">{{ $event->description }}</p>
                @else
                    <p class="mt-3 text-sm text-slate-muted">The SK hasn’t added details yet. Check its announcements for updates.</p>
                @endif
                @if ($isConsultation)
                    <p class="mt-4 max-w-[68ch] rounded-[10px] bg-gold/15 p-4 text-sm leading-relaxed">
                        Consultations are where the SK asks the Katipunan ng Kabataan what to include in its plans. What you say here can shape next year’s programs and budget.
                    </p>
                @endif
            </section>

            @if ($moreEvents->isNotEmpty())
                <section class="mt-8" aria-labelledby="more-heading">
                    <h2 id="more-heading" class="font-display text-lg font-semibold">More from SK {{ $event->barangay->name }}</h2>
                    <ul class="mt-3 grid gap-3 sm:grid-cols-3">
                        @foreach ($moreEvents as $other)
                            <li>
                                <a href="{{ route('events.show', $other) }}" class="flex h-full gap-3 rounded-[12px] border border-line bg-white p-3 hover:border-emerald/50">
                                    <x-event.date-block :event="$other" size="sm" />
                                    <span class="min-w-0">
                                        <span class="line-clamp-2 text-sm font-semibold leading-snug">{{ $other->title }}</span>
                                        <span class="mt-0.5 block text-xs text-slate-muted">{{ $other->starts_at->format('g:i A') }}</span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </article>

        <aside class="lg:sticky lg:top-6 lg:self-start" aria-labelledby="register-heading">
            <div class="rounded-[16px] border border-line bg-white p-6">
                <h2 id="register-heading" class="font-display text-lg font-semibold">Registration</h2>
                <x-event.capacity :event="$event" class="mt-3" />
                <p class="sr-only">
                    @if ($event->capacity === null)
                        Open registration.
                    @else
                        {{ $event->spotsLeft() }} of {{ $event->capacity }} places left.
                    @endif
                </p>

                @if (session('status'))
                    <p role="status" class="mt-4 rounded-[8px] bg-success/10 p-3 text-sm">{{ session('status') }}</p>
                @endif
                @error('registration')
                    <p role="alert" class="mt-4 rounded-[8px] bg-error/5 p-3 text-sm text-[#b91c1c]">{{ $message }}</p>
                @enderror

                <div class="mt-5">
                    @if ($event->hasStarted())
                        <p class="rounded-[8px] bg-mist p-3 text-sm text-slate-muted">This event has started, so registration is closed.</p>
                    @elseif (! $user)
                        <a href="{{ route('login') }}" class="block rounded-[8px] bg-emerald px-4 py-3 text-center text-sm font-semibold text-white hover:bg-emerald-dark">Log in to register</a>
                        <p class="mt-3 text-center text-xs text-slate-muted">No account yet? <a href="{{ route('register') }}" class="font-semibold text-emerald hover:underline">Create one</a>. It takes a minute.</p>
                    @elseif (! $user->hasRole(UserRole::Youth))
                        <p class="rounded-[8px] bg-mist p-3 text-sm text-slate-muted">Registration is for youth accounts.</p>
                    @elseif ($registration)
                        <div class="rounded-[10px] border border-emerald/30 bg-emerald/5 p-4">
                            <p class="flex items-center gap-2 font-semibold text-emerald">
                                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg>
                                You’re registered
                            </p>
                            <p class="mt-1 text-sm text-navy/75">Give your name at the registration table on the day. We’ll remind you the evening before.</p>
                        </div>
                        <form method="POST" action="{{ route('events.registration.destroy', $event) }}" class="mt-3" onsubmit="return confirm('Cancel your registration? Your place goes to someone else.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full rounded-[8px] px-4 py-2 text-sm font-semibold text-slate-muted hover:text-[#b91c1c]">Can’t make it? Cancel my registration</button>
                        </form>
                    @elseif ($user->barangay_id !== $event->barangay_id)
                        <p class="rounded-[8px] bg-mist p-3 text-sm text-slate-muted">This event is for youth of {{ $event->barangay->name }}. Check the events of your own barangay.</p>
                    @elseif ($event->isFull())
                        <p class="rounded-[8px] bg-mist p-3 text-sm text-slate-muted">No places left. Keep an eye on the announcements in case places open up.</p>
                    @else
                        <form method="POST" action="{{ route('events.registration.store', $event) }}">
                            @csrf
                            <x-form.submit busy="Registering…">Register for this {{ $isConsultation ? 'consultation' : 'event' }}</x-form.submit>
                        </form>
                        <p class="mt-3 text-center text-xs text-slate-muted">You’ll get a confirmation and a reminder the evening before.</p>
                    @endif
                </div>

                @unless ($event->hasStarted())
                    <a href="{{ route('events.calendar', $event) }}" class="mt-5 flex items-center justify-center gap-2 border-t border-line pt-4 text-sm font-semibold text-navy hover:text-emerald">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18M12 14v4M10 16h4"/></svg>
                        Add to my calendar
                    </a>
                @endunless
            </div>
        </aside>
    </div>
</x-layouts.public>
