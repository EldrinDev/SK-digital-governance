@php
    use App\EventKind;
    use App\QrCode;

    $hour = (int) now()->format('G');
    $greeting = $hour < 12 ? 'Magandang umaga' : ($hour < 18 ? 'Magandang hapon' : 'Magandang gabi');
    $todoCount = $openSurveys->count() + $toEvaluate->count();
    $inDays = fn ($date): string => $date->isToday() ? 'today' : ($date->isTomorrow() ? 'tomorrow' : 'in '.(int) ceil(now()->diffInDays($date)).' days');
    $laterEvents = $myUpcoming->slice(1);
    $sectionTitle = 'font-display text-base font-semibold text-navy';
    $sectionLink = 'text-sm font-medium text-emerald hover:underline';
    $sheet = 'overflow-hidden rounded-[14px] border border-line bg-white';
@endphp
<x-layouts.app title="Dashboard">
    @if ($errors->any())
        <div role="alert" class="mb-6 rounded-[12px] border border-error/30 bg-error/5 p-4 text-sm">
            @foreach ($errors->all() as $message)
                <p>{{ $message }}</p>
            @endforeach
        </div>
    @endif

    {{--
        The greeting, in the landing page's hero style. Its one bold element is the pass for the
        next event: the same QR as the digital ID, ready to show at the check-in table.
    --}}
    <section class="relative mb-10 overflow-hidden rounded-[20px] border border-line bg-white" aria-labelledby="hello-heading">
        <figure class="absolute inset-y-0 right-0 hidden w-[55%] lg:block" aria-hidden="true">
            <img src="{{ asset('images/ulo-ng-apo.jpg') }}" alt="" class="h-full w-full object-cover object-[50%_35%] brightness-[0.55] saturate-[0.75] [mask-image:linear-gradient(to_right,transparent_0%,rgba(0,0,0,0.35)_30%,#000_62%)]">
        </figure>

        <div class="relative z-10 grid gap-7 px-6 pb-11 pt-8 sm:px-8 lg:grid-cols-[minmax(0,1fr)_minmax(0,25rem)] lg:items-center">
            <div class="min-w-0">
                <p class="text-sm font-semibold text-emerald">{{ $greeting }}, ka-SK {{ $barangay->name }}</p>
                <h1 id="hello-heading" class="mt-1 font-display text-[2rem] font-bold leading-tight tracking-tight text-navy sm:text-[2.5rem]">Hi, {{ str(auth()->user()->name)->before(' ') }}.</h1>
                <p class="mt-2 max-w-md text-[0.95rem] leading-relaxed text-navy/75">
                    @if ($todoCount > 0)
                        <a href="#todo" class="font-semibold text-navy underline decoration-gold decoration-2 underline-offset-4 hover:decoration-emerald">{{ trans_choice('{1}1 thing needs you|[2,*]:count things need you', $todoCount) }}</a>{{ $nextUp ? ', and your next event is '.$inDays($nextUp->starts_at).'.' : '. Nothing is on your calendar yet.' }}
                    @elseif ($nextUp)
                        You’re all caught up. Your next event is {{ $inDays($nextUp->starts_at) }}.
                    @else
                        You’re all caught up. Pick an event to join below.
                    @endif
                </p>
            </div>

            @if ($nextUp)
                <div class="drop-shadow-[0_18px_28px_rgba(13,31,56,0.28)]">
                    <div class="ticket-notched flex min-w-0 rounded-[14px] bg-mist lg:bg-white">
                        <a href="{{ route('events.show', $nextUp) }}" class="group min-w-0 flex-1 px-5 py-4">
                            <span class="flex items-center justify-between gap-3 text-xs font-semibold">
                                <span class="text-slate-muted">Your next event</span>
                                <span class="text-emerald">{{ ucfirst($inDays($nextUp->starts_at)) }}</span>
                            </span>
                            <span class="mt-2 line-clamp-2 block font-display text-[1.05rem] font-semibold leading-snug text-navy group-hover:text-emerald">{{ $nextUp->title }}</span>
                            <span class="mt-2 block text-sm font-medium text-navy/80">{{ $nextUp->starts_at->format('D, M j') }} at {{ $nextUp->starts_at->format('g:i A') }}</span>
                            <span class="block truncate text-sm text-slate-muted">{{ $nextUp->venue }}</span>
                        </a>
                        <div class="flex w-[7.5rem] shrink-0 flex-col items-center justify-center gap-1.5 border-l-2 border-dashed border-line px-3 py-4">
                            <a href="{{ route('account.id') }}" class="block w-[4.6rem] rounded-[6px] bg-white p-1 [&_svg]:h-auto [&_svg]:w-full" title="Open my digital ID">
                                {{ QrCode::svg(route('youth-id.verify', $memberCode), 160) }}
                                <span class="sr-only">Open my digital ID to show at check-in</span>
                            </a>
                            <span class="text-center text-[0.65rem] font-medium leading-tight text-slate-muted">Show at check-in</span>
                        </div>
                    </div>
                </div>
            @else
                <div class="rounded-[14px] border border-dashed border-line bg-white/95 px-5 py-4 lg:border-white/40">
                    <p class="font-display font-semibold text-navy">No events lined up yet</p>
                    <p class="mt-0.5 text-sm text-slate-muted">See what your SK has planned and save your spot.</p>
                    <a href="{{ route('events.index', ['barangay' => $barangay->id]) }}" class="mt-3 inline-flex rounded-[8px] bg-emerald px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-dark">Browse events</a>
                </div>
            @endif
        </div>

        {{-- The same flag-colored ribbon that closes the landing page's hero --}}
        <svg viewBox="0 0 1440 140" preserveAspectRatio="none" class="absolute inset-x-0 -bottom-px z-20 h-7 w-full sm:h-8" aria-hidden="true">
            <path d="M0 140V96C420 150 860 132 1140 86c120-20 220-50 300-84v138Z" fill="#E5B64B"/>
            <path d="M0 140v-34c430 50 870 34 1150-6 120-17 215-42 290-72v112Z" fill="#16845B"/>
            <path d="M0 140v-26c440 44 880 30 1160 0 120-13 210-32 280-54v80Z" fill="#F5F7FA"/>
        </svg>
    </section>

    <div class="grid items-start gap-x-8 gap-y-10 lg:grid-cols-[minmax(0,1fr)_21rem]">
        <div class="min-w-0 space-y-10">
            {{-- Only what needs the youth's action; the section disappears when there's nothing --}}
            @if ($todoCount > 0)
                <section id="todo" class="scroll-mt-24" aria-labelledby="todo-heading">
                    <h2 id="todo-heading" class="mb-3 {{ $sectionTitle }}">Needs you <span class="ml-1 font-sans text-sm font-medium text-slate-muted">{{ $todoCount }}</span></h2>
                    <ul class="{{ $sheet }} divide-y divide-line">
                        @foreach ($openSurveys as $survey)
                            <li class="flex flex-wrap items-center gap-x-4 gap-y-3 px-5 py-4">
                                <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-gold" aria-hidden="true"></span>
                                <div class="min-w-[12rem] flex-1">
                                    <p class="text-xs font-medium text-slate-muted">Survey from your SK</p>
                                    <p class="font-medium text-navy">{{ $survey->title }}</p>
                                </div>
                                <a href="{{ route('surveys.answer', $survey) }}" class="ml-[1.625rem] rounded-[8px] bg-emerald px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-dark sm:ml-0">Answer survey<span class="sr-only">: {{ $survey->title }}</span></a>
                            </li>
                        @endforeach

                        @foreach ($toEvaluate as $registration)
                            <li class="px-5 py-4">
                                <details class="group" @if ($errors->any() && (string) old('event_id') === (string) $registration->event_id) open @endif>
                                    <summary class="flex cursor-pointer list-none flex-wrap items-center gap-x-4 gap-y-3 [&::-webkit-details-marker]:hidden">
                                        <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-gold" aria-hidden="true"></span>
                                        <span class="min-w-[12rem] flex-1">
                                            <span class="block text-xs font-medium text-slate-muted">How did it go?</span>
                                            <span class="block font-medium text-navy">{{ $registration->event->title }}</span>
                                        </span>
                                        <span class="ml-[1.625rem] rounded-[8px] bg-emerald px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-dark group-open:hidden sm:ml-0">Rate event</span>
                                        <span class="hidden text-sm font-medium text-slate-muted group-open:inline">Close</span>
                                    </summary>
                                    <form method="POST" action="{{ route('feedback.store') }}" class="mt-4 space-y-4 border-t border-line pt-4 sm:ml-6" novalidate>
                                        @csrf
                                        <input type="hidden" name="event_id" value="{{ $registration->event_id }}">
                                        <fieldset>
                                            <legend class="text-sm font-medium text-navy">Your rating</legend>
                                            <div class="mt-2 inline-flex overflow-hidden rounded-[8px] border border-line bg-white">
                                                @foreach (range(1, 5) as $score)
                                                    <label class="cursor-pointer border-l border-line px-3.5 py-2 text-sm font-semibold text-navy/70 first:border-l-0 hover:bg-mist has-[:checked]:bg-gold/25 has-[:checked]:text-navy has-[:focus-visible]:outline has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-emerald">
                                                        <input type="radio" name="rating" value="{{ $score }}" class="sr-only" required>
                                                        {{ $score }}<span aria-hidden="true" class="ml-0.5 text-gold">★</span><span class="sr-only"> out of 5</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </fieldset>
                                        <div>
                                            <label for="eval-{{ $registration->id }}" class="block text-sm font-medium text-navy">What should they keep or change?</label>
                                            <textarea id="eval-{{ $registration->id }}" name="message" rows="2" required class="mt-1.5 block w-full rounded-[8px] border border-line px-3.5 py-2.5 text-sm outline-none focus:border-emerald focus:ring-3 focus:ring-emerald/20"></textarea>
                                        </div>
                                        <x-form.submit busy="Sending…" :block="false">Send rating</x-form.submit>
                                    </form>
                                </details>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            {{-- The SK's latest post up front, the next two as headlines --}}
            <section aria-labelledby="news-heading">
                <div class="mb-3 flex items-end justify-between gap-3">
                    <h2 id="news-heading" class="{{ $sectionTitle }}">News from SK {{ $barangay->name }}</h2>
                    <a href="{{ route('announcements.index', ['barangay' => $barangay->id]) }}" class="{{ $sectionLink }}">All announcements</a>
                </div>
                @if ($announcements->isEmpty())
                    <p class="rounded-[14px] border border-dashed border-line bg-white px-5 py-6 text-sm text-slate-muted">Your SK hasn’t posted anything yet. Announcements show up here as soon as they do.</p>
                @else
                    @php
                        $latest = $announcements->first();
                        $when = $latest->happensAt();
                    @endphp
                    <div class="{{ $sheet }}">
                        <article class="grid gap-5 px-5 py-4 sm:grid-cols-[14rem_minmax(0,1fr)] sm:p-5">
                            <a href="{{ route('announcements.show', $latest) }}" class="hidden aspect-[4/3] overflow-hidden rounded-[10px] bg-mist sm:block" tabindex="-1">
                                <x-announcement.cover :announcement="$latest" size="compact" />
                                <span class="sr-only">{{ $latest->title }}</span>
                            </a>
                            <div class="min-w-0 sm:py-1">
                                <p class="text-xs font-semibold text-emerald">{{ $latest->category->label() }}{{ $latest->isPinned() ? ', pinned by your SK' : '' }}</p>
                                <h3 class="mt-1 font-display text-lg font-semibold leading-snug text-navy">
                                    <a href="{{ route('announcements.show', $latest) }}" class="hover:text-emerald">{{ $latest->title }}</a>
                                </h3>
                                @if ($when)
                                    <p class="mt-2 flex items-start gap-2 text-sm font-medium text-navy/80">
                                        <svg viewBox="0 0 24 24" class="mt-0.5 h-4 w-4 shrink-0 text-emerald" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                                        <span>{{ $when['start']->format($when['allDay'] ? 'l, F j' : 'l, F j, g:i A') }}{{ $latest->place() ? ', '.$latest->place() : '' }}</span>
                                    </p>
                                @endif
                                <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-navy/70">{{ $latest->captionHtml(170, withoutDetails: true) }}</p>
                                <p class="mt-3 text-xs text-slate-muted">Posted {{ $latest->postedAt()->diffForHumans() }}</p>
                            </div>
                        </article>
                        @if ($announcements->count() > 1)
                            <ul class="divide-y divide-line border-t border-line">
                                @foreach ($announcements->skip(1) as $announcement)
                                    <li>
                                        <a href="{{ route('announcements.show', $announcement) }}" class="flex items-center justify-between gap-4 px-5 py-3 hover:bg-mist/60">
                                            <span class="min-w-0">
                                                <span class="block text-xs font-semibold text-emerald">{{ $announcement->category->label() }}</span>
                                                <span class="block truncate text-sm font-medium text-navy">{{ $announcement->title }}</span>
                                            </span>
                                            <span class="shrink-0 text-xs text-slate-muted">{{ $announcement->postedAt()->format('M j') }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endif
            </section>

            {{-- Events they can still join, as an agenda --}}
            <section aria-labelledby="join-heading">
                <div class="mb-3 flex items-end justify-between gap-3">
                    <h2 id="join-heading" class="{{ $sectionTitle }}">Events you can join</h2>
                    <a href="{{ route('events.index', ['barangay' => $barangay->id]) }}" class="{{ $sectionLink }}">All events</a>
                </div>
                @if ($openEvents->isEmpty())
                    <p class="rounded-[14px] border border-dashed border-line bg-white px-5 py-6 text-sm text-slate-muted">Nothing new to join right now. Your SK’s next events show up here.</p>
                @else
                    <ul class="{{ $sheet }} divide-y divide-line">
                        @foreach ($openEvents as $event)
                            @php($isConsultation = $event->kind === EventKind::Consultation)
                            <li class="flex flex-wrap items-center gap-x-5 gap-y-3 px-5 py-4">
                                <time datetime="{{ $event->starts_at->toIso8601String() }}" class="w-11 shrink-0 text-center leading-none">
                                    <span class="block text-[0.7rem] font-semibold text-slate-muted">{{ $event->starts_at->format('M') }}</span>
                                    <span class="mt-1 block font-display text-2xl font-semibold text-navy">{{ $event->starts_at->format('j') }}</span>
                                    <span class="mt-1 block text-[0.7rem] text-slate-muted">{{ $event->starts_at->format('D') }}</span>
                                </time>
                                <div class="min-w-0 flex-1 border-l border-line pl-5">
                                    <p @class(['text-xs font-semibold', 'text-[#92400e]' => $isConsultation, 'text-emerald' => ! $isConsultation])>{{ $isConsultation ? 'Youth consultation' : 'Event' }}, {{ $inDays($event->starts_at) }}</p>
                                    <a href="{{ route('events.show', $event) }}" class="mt-0.5 block font-medium leading-snug text-navy hover:text-emerald">{{ $event->title }}</a>
                                    <p class="mt-0.5 truncate text-sm text-slate-muted">{{ $event->starts_at->format('g:i A') }}, {{ $event->venue }}</p>
                                </div>
                                <div class="flex w-full items-center justify-between gap-4 pl-16 sm:w-auto sm:justify-end sm:pl-0">
                                    <span class="text-xs text-slate-muted">{{ $event->capacity === null ? 'Open to all' : ($event->isFull() ? 'No places left' : $event->spotsLeft().' places left') }}</span>
                                    @unless ($event->isFull())
                                        <form method="POST" action="{{ route('events.registration.store', $event) }}">
                                            @csrf
                                            <button type="submit" class="rounded-[8px] border border-emerald px-3.5 py-1.5 text-sm font-semibold text-emerald hover:bg-emerald hover:text-white">Register<span class="sr-only"> for {{ $event->title }}</span></button>
                                        </form>
                                    @endunless
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            {{-- Their other registrations and what they went to --}}
            @if ($laterEvents->isNotEmpty() || $pastAttended->isNotEmpty())
                <section aria-labelledby="mine-heading">
                    <div class="mb-3 flex items-end justify-between gap-3">
                        <h2 id="mine-heading" class="{{ $sectionTitle }}">Your events</h2>
                        <a href="{{ route('account.activity') }}" class="{{ $sectionLink }}">Full history</a>
                    </div>
                    <ul class="{{ $sheet }} divide-y divide-line">
                        @foreach ($laterEvents as $registration)
                            <li class="flex items-center gap-5 px-5 py-4">
                                <time datetime="{{ $registration->event->starts_at->toIso8601String() }}" class="w-11 shrink-0 text-center leading-none">
                                    <span class="block text-[0.7rem] font-semibold text-slate-muted">{{ $registration->event->starts_at->format('M') }}</span>
                                    <span class="mt-1 block font-display text-2xl font-semibold text-navy">{{ $registration->event->starts_at->format('j') }}</span>
                                </time>
                                <div class="min-w-0 flex-1 border-l border-line pl-5">
                                    <p class="text-xs font-semibold text-emerald">You’re registered</p>
                                    <a href="{{ route('events.show', $registration->event) }}" class="block truncate font-medium text-navy hover:text-emerald">{{ $registration->event->title }}</a>
                                </div>
                                <form method="POST" action="{{ route('events.registration.destroy', $registration->event) }}" onsubmit="return confirm('Cancel your registration? Your place goes to someone else.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm font-medium text-slate-muted hover:text-[#b91c1c]">Cancel<span class="sr-only"> registration for {{ $registration->event->title }}</span></button>
                                </form>
                            </li>
                        @endforeach

                        @foreach ($pastAttended as $registration)
                            <li class="flex items-center gap-5 px-5 py-4">
                                <time datetime="{{ $registration->event->starts_at->toIso8601String() }}" class="w-11 shrink-0 text-center leading-none text-slate-muted">
                                    <span class="block text-[0.7rem] font-semibold">{{ $registration->event->starts_at->format('M') }}</span>
                                    <span class="mt-1 block font-display text-2xl font-semibold">{{ $registration->event->starts_at->format('j') }}</span>
                                </time>
                                <div class="min-w-0 flex-1 border-l border-line pl-5">
                                    <p class="text-xs font-semibold text-slate-muted">You attended</p>
                                    <a href="{{ route('events.show', $registration->event) }}" class="block truncate font-medium text-navy/80 hover:text-emerald">{{ $registration->event->title }}</a>
                                </div>
                                @if ($ratings->has($registration->event_id))
                                    <span class="shrink-0 text-sm tracking-wide text-gold" title="Your rating">{{ str_repeat('★', (int) $ratings[$registration->event_id]) }}<span class="text-line">{{ str_repeat('★', 5 - (int) $ratings[$registration->event_id]) }}</span><span class="sr-only">You rated it {{ $ratings[$registration->event_id] }} out of 5</span></span>
                                @else
                                    <a href="#todo" class="shrink-0 text-sm font-medium text-emerald hover:underline">Rate it</a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>

        <aside class="min-w-0 space-y-10">
            {{-- Their SK: who leads it and how the SK fund is being used --}}
            <section aria-labelledby="sk-heading">
                <h2 id="sk-heading" class="mb-3 {{ $sectionTitle }}">Your SK</h2>
                <div class="rounded-[14px] border border-line bg-white p-5">
                <div class="flex items-center gap-3">
                    <img src="{{ $barangay->logoUrl() }}" alt="" class="h-11 w-11 rounded-full border border-line bg-white object-contain p-0.5">
                    <div class="min-w-0 leading-tight">
                        <p class="font-display text-[0.95rem] font-semibold text-navy">SK {{ $barangay->name }}</p>
                        <p class="truncate text-xs text-slate-muted">Your barangay’s youth council</p>
                    </div>
                </div>
                @if ($chairperson)
                    <p class="mt-4 text-sm"><span class="text-slate-muted">SK Chairperson:</span> <span class="font-medium text-navy">{{ $chairperson }}</span></p>
                @endif

                <div class="mt-4 border-t border-line pt-4">
                    <p class="text-xs font-medium text-slate-muted">SK fund this year</p>
                    @if ($fund['projects'] > 0)
                        @php($usedPercent = $fund['budget'] > 0 ? (int) min(100, round($fund['spent'] / $fund['budget'] * 100)) : 0)
                        <p class="mt-1 font-display text-2xl font-semibold tabular-nums text-navy">₱{{ number_format($fund['budget']) }}</p>
                        <p class="text-xs text-slate-muted">for {{ trans_choice(':count project|:count projects', $fund['projects']) }}, {{ $fund['completed'] }} done</p>
                        <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-mist" role="progressbar" aria-valuenow="{{ $usedPercent }}" aria-valuemin="0" aria-valuemax="100" aria-label="SK fund spent">
                            <div class="h-full rounded-full bg-navy" style="width: {{ $usedPercent }}%"></div>
                        </div>
                        <p class="mt-1.5 text-xs text-slate-muted">{{ $usedPercent }}% spent so far</p>
                        <a href="{{ route('transparency.show', $barangay) }}" class="mt-3 inline-block text-sm font-medium text-emerald hover:underline">See where it goes</a>
                    @else
                        <p class="mt-1 text-sm text-slate-muted">Your SK hasn’t published this year’s program yet. It shows here once the council approves it.</p>
                    @endif
                </div>

                <x-barangay.facebook-link :barangay="$barangay" class="mt-4 flex w-full justify-center" />
                </div>
            </section>

            {{-- One conversation with the SK: what they sent, the replies, and a box to write more --}}
            <section aria-labelledby="messages-heading">
                <div class="mb-3 flex items-end justify-between gap-3">
                    <h2 id="messages-heading" class="{{ $sectionTitle }}">Messages</h2>
                    <a href="{{ route('account.activity', ['type' => 'messages']) }}" class="{{ $sectionLink }}">All messages</a>
                </div>
                <div class="{{ $sheet }}">
                <div class="space-y-4 bg-mist/50 px-4 py-4">
                    @forelse ($thread as $item)
                        <div class="flex flex-col items-end">
                            <p class="max-w-[88%] rounded-[14px] rounded-br-[4px] bg-navy px-3.5 py-2.5 text-sm leading-relaxed text-white">{{ $item->message }}</p>
                            <p class="mt-1 max-w-[88%] text-right text-[0.7rem] text-slate-muted">
                                {{ $item->event ? 'About '.$item->event->title.', ' : '' }}{{ $item->created_at->format('M j') }}{{ $item->isAnswered() ? '' : ', waiting for a reply' }}
                            </p>
                        </div>
                        @if ($item->isAnswered())
                            <div class="flex items-end gap-2">
                                <img src="{{ $barangay->logoUrl() }}" alt="" class="h-6 w-6 shrink-0 rounded-full border border-line bg-white object-contain">
                                <div class="max-w-[85%]">
                                    <p class="rounded-[14px] rounded-bl-[4px] border border-line bg-white px-3.5 py-2.5 text-sm leading-relaxed text-navy">{{ $item->response }}</p>
                                    <p class="mt-1 text-[0.7rem] text-slate-muted">{{ $item->responder?->name ?? 'SK '.$barangay->name }}, {{ $item->responded_at?->format('M j') }}</p>
                                </div>
                            </div>
                        @endif
                    @empty
                        <p class="text-sm leading-relaxed text-slate-muted">Have an idea, a concern or a question for SK {{ $barangay->name }}? Send it below. Their reply shows up right here.</p>
                    @endforelse
                </div>

                <form method="POST" action="{{ route('feedback.store') }}" class="border-t border-line p-3" novalidate>
                    @csrf
                    <label for="message" class="sr-only">Message to SK {{ $barangay->name }}</label>
                    <textarea id="message" name="message" rows="2" placeholder="Write to SK {{ $barangay->name }}…" class="block w-full resize-none rounded-[10px] border border-line bg-white px-3.5 py-2.5 text-sm outline-none focus:border-emerald focus:ring-3 focus:ring-emerald/20">{{ old('event_id') ? '' : old('message') }}</textarea>
                    <div class="mt-2 flex items-center justify-between gap-3">
                        <p class="text-xs text-slate-muted">SK officers read and reply here.</p>
                        <button type="submit" class="rounded-[8px] bg-emerald px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-dark">Send</button>
                    </div>
                </form>
                </div>
            </section>
        </aside>
    </div>
</x-layouts.app>
