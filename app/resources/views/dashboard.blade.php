@php
    use App\EventKind;

    $hour = (int) now()->format('G');
    $greeting = $hour < 12 ? 'Magandang umaga' : ($hour < 18 ? 'Magandang hapon' : 'Magandang gabi');
    $todoCount = $openSurveys->count() + $toEvaluate->count();
    $inDays = fn ($date): string => $date->isToday() ? 'today' : ($date->isTomorrow() ? 'tomorrow' : 'in '.(int) ceil(now()->diffInDays($date)).' days');
@endphp
<x-layouts.app title="Dashboard">
    @if ($errors->any())
        <div role="alert" class="mb-6 rounded-[12px] border border-error/30 bg-error/5 p-4 text-sm">
            @foreach ($errors->all() as $message)
                <p>{{ $message }}</p>
            @endforeach
        </div>
    @endif

    {{-- Greeting, their numbers, and the one thing coming up next --}}
    <section class="mb-6 overflow-hidden rounded-[16px] bg-navy text-white" aria-labelledby="hello-heading">
        <div class="relative grid grid-cols-1 gap-6 p-6 sm:p-8 lg:grid-cols-[minmax(0,1fr)_minmax(0,22rem)] lg:items-center">
            <img src="{{ $barangay->logoUrl() }}" alt="" class="pointer-events-none absolute -right-14 -top-14 h-64 w-64 opacity-[0.08]">
            <div class="relative">
                <p class="text-sm text-white/70">{{ $greeting }}, ka-SK {{ $barangay->name }}!</p>
                <h1 id="hello-heading" class="mt-1 font-display text-3xl font-semibold tracking-tight sm:text-4xl">Hi, {{ str(auth()->user()->name)->before(' ') }}</h1>
                <dl class="mt-6 flex flex-wrap gap-x-8 gap-y-3">
                    <div>
                        <dt class="text-xs text-white/60">Events attended</dt>
                        <dd class="font-display text-2xl font-semibold tabular-nums">{{ $attendedCount }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-white/60">Coming up for you</dt>
                        <dd class="font-display text-2xl font-semibold tabular-nums">{{ $myUpcoming->count() }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-white/60">To do</dt>
                        <dd @class(['font-display text-2xl font-semibold tabular-nums', 'text-gold' => $todoCount > 0])>{{ $todoCount }}</dd>
                    </div>
                </dl>
            </div>

            @if ($nextUp)
                <a href="{{ route('events.show', $nextUp) }}" class="relative flex min-w-0 items-center gap-4 rounded-[14px] bg-white p-4 text-navy transition-transform hover:-translate-y-0.5">
                    <x-event.date-block :event="$nextUp" />
                    <span class="min-w-0">
                        <span class="text-xs font-semibold text-emerald">Your next event, {{ $inDays($nextUp->starts_at) }}</span>
                        <span class="mt-0.5 line-clamp-2 block font-display font-semibold leading-snug">{{ $nextUp->title }}</span>
                        <span class="mt-0.5 block truncate text-sm text-slate-muted">{{ $nextUp->starts_at->format('g:i A') }}, {{ $nextUp->venue }}</span>
                    </span>
                </a>
            @else
                <div class="relative rounded-[14px] bg-white/10 p-5">
                    <p class="font-semibold">No events lined up yet</p>
                    <p class="mt-1 text-sm text-white/75">See what your SK has planned and save your spot.</p>
                    <a href="{{ route('events.index', ['barangay' => $barangay->id]) }}" class="mt-4 inline-flex rounded-[8px] bg-gold px-4 py-2 text-sm font-semibold text-navy hover:bg-white">Browse events</a>
                </div>
            @endif
        </div>
    </section>

    <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="min-w-0 space-y-6">
            {{-- Everything that needs the youth's action, in one list --}}
            <section class="rounded-[14px] border border-line bg-white" aria-labelledby="todo-heading">
                <div class="flex items-center justify-between gap-3 border-b border-line px-5 py-4">
                    <h2 id="todo-heading" class="font-display text-lg font-semibold">To do</h2>
                    @if ($todoCount > 0)
                        <span class="rounded-full bg-gold/25 px-2.5 py-0.5 text-xs font-semibold">{{ $todoCount }} waiting</span>
                    @endif
                </div>
                <ul class="divide-y divide-line">
                    @foreach ($openSurveys as $survey)
                        <li class="flex flex-wrap items-center gap-4 px-5 py-4">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-[10px] bg-gold/20 text-navy" aria-hidden="true">
                                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-semibold text-slate-muted">Survey from your SK</p>
                                <p class="font-medium">{{ $survey->title }}</p>
                            </div>
                            <a href="{{ route('surveys.answer', $survey) }}" class="rounded-[8px] bg-emerald px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-dark">Answer<span class="sr-only"> {{ $survey->title }}</span></a>
                        </li>
                    @endforeach

                    @foreach ($toEvaluate as $registration)
                        <li class="px-5 py-4">
                            <details class="group" @if ($errors->any() && (string) old('event_id') === (string) $registration->event_id) open @endif>
                                <summary class="flex cursor-pointer list-none flex-wrap items-center gap-4 [&::-webkit-details-marker]:hidden">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-[10px] bg-info/10 text-info" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1 6.2L12 17.3 6.5 20.2l1-6.2L3 9.6l6.2-.9L12 3Z"/></svg>
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-xs font-semibold text-slate-muted">How did it go?</span>
                                        <span class="block font-medium">{{ $registration->event->title }}</span>
                                    </span>
                                    <span class="rounded-[8px] border border-line px-4 py-2 text-sm font-semibold group-open:hidden">Rate it</span>
                                    <span class="hidden text-sm font-semibold text-slate-muted group-open:inline">Close</span>
                                </summary>
                                <form method="POST" action="{{ route('feedback.store') }}" class="mt-4 space-y-3 rounded-[12px] bg-mist p-4 sm:ml-14" novalidate>
                                    @csrf
                                    <input type="hidden" name="event_id" value="{{ $registration->event_id }}">
                                    <fieldset>
                                        <legend class="text-sm font-medium">Your rating</legend>
                                        <div class="mt-1.5 flex flex-wrap gap-1.5">
                                            @foreach (range(1, 5) as $score)
                                                <label class="cursor-pointer rounded-[8px] border border-line bg-white px-3 py-1.5 text-sm font-semibold has-[:checked]:border-gold has-[:checked]:bg-gold/25">
                                                    <input type="radio" name="rating" value="{{ $score }}" class="sr-only" required>
                                                    {{ $score }}<span aria-hidden="true">★</span><span class="sr-only"> out of 5</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </fieldset>
                                    <label for="eval-{{ $registration->id }}" class="block text-sm font-medium">What should they keep or change?</label>
                                    <textarea id="eval-{{ $registration->id }}" name="message" rows="2" required class="block w-full rounded-[8px] border border-line bg-white px-3.5 py-2.5 text-sm outline-none focus:border-emerald focus:ring-3 focus:ring-emerald/20"></textarea>
                                    <x-form.submit busy="Sending…" :block="false" class="py-2">Send rating</x-form.submit>
                                </form>
                            </details>
                        </li>
                    @endforeach

                    @if ($todoCount === 0)
                        <li class="flex items-center gap-4 px-5 py-5">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-[10px] bg-emerald/10 text-emerald" aria-hidden="true">
                                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5 9-10"/></svg>
                            </span>
                            <div>
                                <p class="font-medium">You’re all caught up</p>
                                <p class="text-sm text-slate-muted">Surveys from your SK and events to rate show up here.</p>
                            </div>
                        </li>
                    @endif
                </ul>
            </section>

            {{-- The SK's latest posts --}}
            <section aria-labelledby="news-heading">
                <div class="mb-3 flex items-end justify-between gap-3">
                    <h2 id="news-heading" class="font-display text-lg font-semibold">From SK {{ $barangay->name }}</h2>
                    <a href="{{ route('announcements.index', ['barangay' => $barangay->id]) }}" class="text-sm font-semibold text-emerald hover:underline">All announcements</a>
                </div>
                @if ($announcements->isEmpty())
                    <p class="rounded-[12px] border border-dashed border-line bg-white p-6 text-sm text-slate-muted">Your SK hasn’t posted anything yet. Announcements show up here as soon as they do.</p>
                @else
                    @php($latest = $announcements->first())
                    <article class="grid overflow-hidden rounded-[14px] border border-line bg-white sm:grid-cols-[14rem_minmax(0,1fr)]">
                        <a href="{{ route('announcements.show', $latest) }}" class="block aspect-[4/3] bg-mist sm:aspect-auto sm:min-h-52">
                            <x-announcement.cover :announcement="$latest" />
                            <span class="sr-only">{{ $latest->title }}</span>
                        </a>
                        <div class="flex flex-col p-5">
                            <p class="flex flex-wrap items-center gap-2 text-xs text-slate-muted">
                                <span class="rounded-full bg-mist px-2 py-0.5 font-semibold text-navy">{{ $latest->category->label() }}</span>
                                @if ($latest->isPinned())
                                    <span class="rounded-full bg-gold/20 px-2 py-0.5 font-semibold text-navy">Pinned</span>
                                @endif
                                <time datetime="{{ $latest->postedAt()->toIso8601String() }}">{{ $latest->postedAt()->diffForHumans() }}</time>
                            </p>
                            <h3 class="mt-2 font-display text-lg font-semibold leading-snug">
                                <a href="{{ route('announcements.show', $latest) }}" class="hover:text-emerald">{{ $latest->title }}</a>
                            </h3>
                            <x-announcement.details :announcement="$latest" class="mt-3" />
                            <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-navy/75">{{ $latest->captionHtml(160, withoutDetails: true) }}</p>
                            <a href="{{ route('announcements.show', $latest) }}" class="mt-auto pt-3 text-sm font-semibold text-emerald hover:underline">Read the full post</a>
                        </div>
                    </article>

                    @if ($announcements->count() > 1)
                        <ul class="mt-3 grid gap-3 sm:grid-cols-2">
                            @foreach ($announcements->skip(1) as $announcement)
                                <li>
                                    <a href="{{ route('announcements.show', $announcement) }}" class="flex h-full items-center gap-3 rounded-[12px] border border-line bg-white p-2.5 hover:border-emerald/50">
                                        <span class="block h-14 w-14 shrink-0 overflow-hidden rounded-[8px]">
                                            <x-announcement.cover :announcement="$announcement" size="thumb" />
                                        </span>
                                        <span class="min-w-0">
                                            <span class="line-clamp-2 text-sm font-semibold leading-snug">{{ $announcement->title }}</span>
                                            <span class="block text-xs text-slate-muted">{{ $announcement->postedAt()->diffForHumans() }}</span>
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                @endif
            </section>

            {{-- Events they can still join --}}
            <section aria-labelledby="open-heading">
                <div class="mb-3 flex items-end justify-between gap-3">
                    <h2 id="open-heading" class="font-display text-lg font-semibold">Join an event</h2>
                    <a href="{{ route('events.index', ['barangay' => $barangay->id]) }}" class="text-sm font-semibold text-emerald hover:underline">All events</a>
                </div>
                @if ($openEvents->isEmpty())
                    <p class="rounded-[12px] border border-dashed border-line bg-white p-6 text-sm text-slate-muted">Nothing new to join right now. Your SK’s next events will show up here.</p>
                @else
                    <ul class="grid gap-3 sm:grid-cols-2">
                        @foreach ($openEvents as $event)
                            <li class="flex flex-col rounded-[14px] border border-line bg-white p-4">
                                <a href="{{ route('events.show', $event) }}" class="group flex gap-3">
                                    <x-event.date-block :event="$event" size="sm" />
                                    <span class="min-w-0">
                                        <span class="block text-xs font-semibold text-emerald">{{ $event->kind === EventKind::Consultation ? 'Youth consultation' : 'Event' }}, {{ $inDays($event->starts_at) }}</span>
                                        <span class="line-clamp-2 block font-semibold leading-snug group-hover:text-emerald">{{ $event->title }}</span>
                                        <span class="block truncate text-xs text-slate-muted">{{ $event->starts_at->format('g:i A') }}, {{ $event->venue }}</span>
                                    </span>
                                </a>
                                <div class="mt-auto flex items-end gap-3 pt-4">
                                    <x-event.capacity :event="$event" class="min-w-0 flex-1" />
                                    @unless ($event->isFull())
                                        <form method="POST" action="{{ route('events.registration.store', $event) }}">
                                            @csrf
                                            <button type="submit" class="rounded-[8px] bg-emerald px-3.5 py-2 text-sm font-semibold text-white hover:bg-emerald-dark">Register<span class="sr-only"> for {{ $event->title }}</span></button>
                                        </form>
                                    @endunless
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            {{-- Their own events: what's coming, and what they went to --}}
            <section class="rounded-[14px] border border-line bg-white" aria-labelledby="mine-heading">
                <div class="flex items-center justify-between gap-3 border-b border-line px-5 py-4">
                    <h2 id="mine-heading" class="font-display text-lg font-semibold">My events</h2>
                    <a href="{{ route('account.activity') }}" class="text-sm font-semibold text-emerald hover:underline">Full history</a>
                </div>
                <ul class="divide-y divide-line">
                    @foreach ($myUpcoming as $registration)
                        <li class="flex items-center gap-4 px-5 py-3.5">
                            <x-event.date-block :event="$registration->event" size="sm" />
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('events.show', $registration->event) }}" class="block truncate font-medium hover:text-emerald">{{ $registration->event->title }}</a>
                                <p class="truncate text-xs text-slate-muted">{{ $registration->event->starts_at->format('g:i A') }}, {{ $registration->event->venue }}</p>
                            </div>
                            <span class="hidden rounded-full bg-emerald/10 px-2.5 py-0.5 text-xs font-semibold text-emerald sm:inline">Registered</span>
                            <form method="POST" action="{{ route('events.registration.destroy', $registration->event) }}" onsubmit="return confirm('Cancel your registration? Your place goes to someone else.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-semibold text-slate-muted hover:text-error">Cancel<span class="sr-only"> registration for {{ $registration->event->title }}</span></button>
                            </form>
                        </li>
                    @endforeach

                    @foreach ($pastAttended as $registration)
                        <li class="flex items-center gap-4 px-5 py-3.5">
                            <x-event.date-block :event="$registration->event" size="sm" class="opacity-60 grayscale" />
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('events.show', $registration->event) }}" class="block truncate font-medium text-navy/80 hover:text-emerald">{{ $registration->event->title }}</a>
                                <p class="text-xs text-slate-muted">Attended {{ $registration->event->starts_at->format('M j, Y') }}</p>
                            </div>
                            @if ($ratings->has($registration->event_id))
                                <span class="text-sm font-semibold text-[#b45309]" title="Your rating">{{ str_repeat('★', (int) $ratings[$registration->event_id]) }}<span class="text-line">{{ str_repeat('★', 5 - (int) $ratings[$registration->event_id]) }}</span><span class="sr-only">You rated it {{ $ratings[$registration->event_id] }} out of 5</span></span>
                            @else
                                <span class="rounded-full bg-info/10 px-2.5 py-0.5 text-xs font-semibold text-info">Not rated yet</span>
                            @endif
                        </li>
                    @endforeach

                    @if ($myUpcoming->isEmpty() && $pastAttended->isEmpty())
                        <li class="px-5 py-6 text-sm text-slate-muted">You haven’t registered for anything yet. Pick one under “Join an event”.</li>
                    @endif
                </ul>
            </section>
        </div>

        <div class="min-w-0 space-y-6">
            {{-- Their SK: who leads it and how the SK fund is being used --}}
            <section class="rounded-[14px] border border-line bg-white p-5" aria-labelledby="sk-heading">
                <div class="flex items-center gap-3">
                    <img src="{{ $barangay->logoUrl() }}" alt="" class="h-12 w-12 rounded-full border border-line bg-white object-contain p-0.5">
                    <div class="min-w-0 leading-tight">
                        <h2 id="sk-heading" class="font-display text-lg font-semibold">Your SK</h2>
                        <p class="truncate text-sm text-slate-muted">Sangguniang Kabataan ng {{ $barangay->name }}</p>
                    </div>
                </div>
                @if ($chairperson)
                    <p class="mt-4 text-sm"><span class="text-slate-muted">SK Chairperson:</span> <span class="font-medium">{{ $chairperson }}</span></p>
                @endif

                <div class="mt-4 rounded-[12px] bg-mist p-4">
                    <p class="text-sm font-semibold">Where the SK fund goes</p>
                    @if ($fund['projects'] > 0)
                        @php($usedPercent = $fund['budget'] > 0 ? (int) min(100, round($fund['spent'] / $fund['budget'] * 100)) : 0)
                        <p class="mt-2 font-display text-2xl font-semibold tabular-nums">₱{{ number_format($fund['budget']) }}</p>
                        <p class="text-xs text-slate-muted">for {{ trans_choice(':count project|:count projects', $fund['projects']) }}, {{ $fund['completed'] }} done</p>
                        <div class="mt-3 flex justify-between text-xs"><span class="text-slate-muted">Spent so far</span><span class="font-semibold tabular-nums">{{ $usedPercent }}%</span></div>
                        <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-white" aria-hidden="true"><div class="h-full rounded-full bg-navy" style="width: {{ $usedPercent }}%"></div></div>
                        <a href="{{ route('transparency.show', $barangay) }}" class="mt-3 inline-block text-sm font-semibold text-emerald hover:underline">See every project</a>
                    @else
                        <p class="mt-1 text-sm text-slate-muted">Your SK hasn’t published its program yet. It shows here once the council approves it.</p>
                    @endif
                </div>

                <x-barangay.facebook-link :barangay="$barangay" class="mt-4 flex w-full justify-center" />
            </section>

            <section class="rounded-[14px] border border-line bg-white p-5" aria-labelledby="feedback-heading">
                <h2 id="feedback-heading" class="font-display text-lg font-semibold">Tell your SK something</h2>
                <p class="mt-1 text-sm text-slate-muted">Ideas, concerns or questions. Your SK officers read and reply here.</p>
                <form method="POST" action="{{ route('feedback.store') }}" class="mt-3 space-y-3" novalidate>
                    @csrf
                    <label for="message" class="sr-only">Your message</label>
                    <textarea id="message" name="message" rows="4" placeholder="e.g. Puwede po bang magkaroon ng study hub sa barangay hall?" class="block w-full rounded-[8px] border border-line px-3.5 py-2.5 text-sm outline-none focus:border-emerald focus:ring-3 focus:ring-emerald/20">{{ old('event_id') ? '' : old('message') }}</textarea>
                    <x-form.submit busy="Sending…">Send to my SK</x-form.submit>
                </form>
            </section>

            <section class="rounded-[14px] border border-line bg-white p-5" aria-labelledby="replies-heading">
                <h2 id="replies-heading" class="font-display text-lg font-semibold">My messages</h2>
                <ul class="mt-3 space-y-4 text-sm">
                    @forelse ($myFeedback as $item)
                        <li class="border-l-2 pl-3 {{ $item->isAnswered() ? 'border-emerald' : 'border-line' }}">
                            <p class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-muted">
                                <span>{{ $item->created_at->format('M j') }}{{ $item->event ? ', about '.$item->event->title : '' }}</span>
                                <span @class(['rounded-full px-2 py-0.5 font-semibold', 'bg-emerald/10 text-emerald' => $item->isAnswered(), 'bg-mist text-slate-muted' => ! $item->isAnswered()])>{{ $item->isAnswered() ? 'Replied' : 'Waiting' }}</span>
                            </p>
                            <p class="mt-1">{{ $item->message }}</p>
                            @if ($item->isAnswered())
                                <p class="mt-2 rounded-[8px] bg-mist p-2.5"><span class="font-semibold">{{ $item->responder?->name ?? 'SK' }}:</span> {{ $item->response }}</p>
                            @endif
                        </li>
                    @empty
                        <li class="text-slate-muted">No messages yet. Anything you send your SK shows up here with their reply.</li>
                    @endforelse
                </ul>
            </section>
        </div>
    </div>
</x-layouts.app>
