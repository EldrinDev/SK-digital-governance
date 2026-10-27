@php
    use App\AnnouncementCategory;
    use App\UserRole;

    $barangay = $announcement->barangay;
    $when = $announcement->happensAt();
    $place = $announcement->place();
    $details = collect($announcement->details());
    $mapUrl = $place ? 'https://www.google.com/maps/search/?api=1&query='.urlencode("{$place}, {$barangay->name}, Olongapo City") : null;
    $viewer = auth()->user();
    $canAsk = $viewer?->hasRole(UserRole::Youth) && $viewer->barangay_id === $barangay->id;

    // Plain-language meaning of each kind of post, for readers who don't know the SK's terms.
    $kindHelp = match ($announcement->category) {
        AnnouncementCategory::Reminder => 'A reminder from your SK',
        AnnouncementCategory::Event => 'An invitation to an activity',
        AnnouncementCategory::Recognition => 'A thank-you or recap',
        default => 'News from your SK',
    };

    $countdown = null;
    if ($when) {
        $day = $when['start']->copy()->startOfDay();
        $countdown = match (true) {
            $day->isToday() => 'Today',
            $day->isTomorrow() => 'Tomorrow',
            $day->isPast() => 'This has already happened',
            default => 'In '.(int) today()->diffInDays($day).' days',
        };
    }
@endphp
<x-layouts.public :title="$announcement->title" :description="str($announcement->body)->limit(150)->toString()">
    <nav class="flex flex-wrap items-center gap-1.5 text-sm text-slate-muted" aria-label="Breadcrumb">
        <a href="{{ route('announcements.index') }}" class="font-medium hover:text-emerald">Announcements</a>
        <span aria-hidden="true">/</span>
        <a href="{{ route('announcements.index', ['barangay' => $barangay->id]) }}" class="font-medium hover:text-emerald">SK {{ $barangay->name }}</a>
    </nav>

    <div class="mt-5 grid gap-8 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <article class="min-w-0">
            {{-- What it is, who posted it, and when --}}
            <header>
                <p class="flex flex-wrap items-center gap-2 text-sm">
                    <span class="rounded-full bg-emerald/10 px-2.5 py-0.5 font-semibold text-emerald">{{ $announcement->category->label() }}</span>
                    <span class="text-slate-muted">{{ $kindHelp }}</span>
                    @if ($announcement->isPinned())
                        <span class="rounded-full bg-gold/20 px-2.5 py-0.5 text-xs font-semibold">Pinned by the SK</span>
                    @endif
                </p>
                <h1 class="mt-3 max-w-3xl font-display text-3xl font-semibold leading-tight tracking-tight sm:text-4xl">{{ $announcement->title }}</h1>
                <div class="mt-4 flex items-center gap-3">
                    <img src="{{ $barangay->logoUrl() }}" alt="" class="h-10 w-10 rounded-full border border-line bg-white object-contain p-0.5">
                    <p class="text-sm leading-tight">
                        <span class="block font-semibold">Sangguniang Kabataan ng {{ $barangay->name }}</span>
                        <span class="text-slate-muted">
                            Posted <time datetime="{{ $announcement->postedAt()->toIso8601String() }}">{{ $announcement->postedAt()->format('F j, Y') }}</time>
                            ({{ $announcement->postedAt()->diffForHumans() }})
                        </span>
                    </p>
                </div>
            </header>

            {{-- When and where, with the things people do next --}}
            @if ($details->isNotEmpty())
                <section class="mt-6 overflow-hidden rounded-[16px] border border-emerald/30 bg-white" aria-labelledby="when-heading">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line bg-emerald/5 px-5 py-3">
                        <h2 id="when-heading" class="font-display font-semibold">When and where</h2>
                        @if ($countdown)
                            <span @class(['rounded-full px-2.5 py-0.5 text-xs font-semibold', 'bg-emerald text-white' => ! str_starts_with($countdown, 'This'), 'bg-mist text-slate-muted' => str_starts_with($countdown, 'This')])>{{ $countdown }}</span>
                        @endif
                    </div>
                    <dl class="grid gap-4 px-5 py-4 sm:grid-cols-3">
                        @foreach (['date' => 'Date', 'time' => 'Time', 'place' => 'Place'] as $kind => $label)
                            @if ($detail = $details->firstWhere('kind', $kind))
                                <div>
                                    <dt class="text-xs font-medium text-slate-muted">{{ $label }}</dt>
                                    <dd class="mt-0.5 font-semibold leading-snug">{{ $detail['text'] }}</dd>
                                </div>
                            @endif
                        @endforeach
                    </dl>
                    @if (($when && ! $when['start']->copy()->endOfDay()->isPast()) || $mapUrl)
                        <div class="flex flex-wrap gap-2 border-t border-line px-5 py-3">
                            @if ($when && ! $when['start']->copy()->endOfDay()->isPast())
                                <a href="{{ route('announcements.calendar', $announcement) }}" class="inline-flex items-center gap-2 rounded-[8px] bg-emerald px-3.5 py-2 text-sm font-semibold text-white hover:bg-emerald-dark">
                                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18M12 14v4M10 16h4"/></svg>
                                    Add to my calendar
                                </a>
                            @endif
                            @if ($mapUrl)
                                <a href="{{ $mapUrl }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-[8px] border border-line px-3.5 py-2 text-sm font-semibold hover:border-navy">
                                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21Z"/><circle cx="12" cy="9.5" r="2.5"/></svg>
                                    Open in Maps<span class="sr-only"> (opens in a new tab)</span>
                                </a>
                            @endif
                        </div>
                    @endif
                </section>
            @endif

            {{-- A real photo is part of the story; the poster tile isn't needed once the details are spelled out. --}}
            @if ($announcement->image_path)
                <figure class="mt-6 overflow-hidden rounded-[16px] border border-line bg-white">
                    <img src="{{ route('announcements.image', $announcement) }}" alt="Photo for: {{ $announcement->title }}" class="max-h-[32rem] w-full object-cover">
                    @if ($announcement->image_credit)
                        <figcaption class="px-4 py-2 text-xs text-slate-muted">Photo: {{ $announcement->image_credit }}</figcaption>
                    @endif
                </figure>
            @endif

            {{-- The message itself --}}
            <div class="mt-6 rounded-[16px] border border-line bg-white px-5 py-6 sm:px-8 sm:py-8">
                <h2 class="sr-only">The announcement</h2>
                <div class="max-w-[65ch] whitespace-pre-line text-[1.05rem] leading-[1.8] text-navy/90">{{ $announcement->captionHtml(withoutDetails: true) }}</div>

                <div class="mt-8 flex flex-wrap items-center gap-2 border-t border-line pt-5">
                    <button type="button" data-share data-share-title="{{ $announcement->title }}" data-share-url="{{ route('announcements.show', $announcement) }}" class="inline-flex items-center gap-2 rounded-[8px] border border-line px-3.5 py-2 text-sm font-semibold hover:border-navy">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 13.5 6.8 4M15.4 6.5l-6.8 4"/></svg>
                        <span data-share-label>Share this post</span>
                    </button>
                    @if ($announcement->isFromFacebook())
                        <a href="{{ $announcement->source_url }}" target="_blank" rel="noopener noreferrer nofollow" class="inline-flex items-center gap-2 rounded-[8px] border border-line px-3.5 py-2 text-sm hover:border-[#1877f2]">
                            <svg viewBox="0 0 24 24" class="h-4 w-4 text-[#1877f2]" fill="currentColor" aria-hidden="true"><path d="M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.05V9.41c0-3.02 1.79-4.69 4.53-4.69 1.31 0 2.68.24 2.68.24v2.97h-1.51c-1.49 0-1.96.93-1.96 1.89v2.25h3.33l-.53 3.49h-2.8V24C19.61 23.1 24 18.1 24 12.07Z"/></svg>
                            <span>First posted on Facebook. <span class="font-semibold text-[#1877f2]">See the original</span></span>
                            <span class="sr-only">(opens in a new tab)</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- A direct line to the SK about this post --}}
            @if ($canAsk)
                <section class="mt-6 rounded-[16px] border border-line bg-white p-5 sm:p-6" aria-labelledby="ask-heading">
                    <h2 id="ask-heading" class="font-display text-lg font-semibold">Have a question about this?</h2>
                    <p class="mt-1 text-sm text-slate-muted">Ask SK {{ $barangay->name }}. Their reply shows up under “My messages” on your dashboard.</p>
                    <form method="POST" action="{{ route('feedback.store') }}" class="mt-4 space-y-3" novalidate>
                        @csrf
                        <label for="question" class="sr-only">Your question</label>
                        <textarea id="question" name="message" rows="3" maxlength="2000" class="block w-full rounded-[8px] border border-line px-3.5 py-2.5 text-sm outline-none focus:border-emerald focus:ring-3 focus:ring-emerald/20">{{ old('message', 'Tungkol sa “'.$announcement->title.'”: ') }}</textarea>
                        @error('message')
                            <p class="text-sm text-error">{{ $message }}</p>
                        @enderror
                        <x-form.submit busy="Sending…" :block="false">Send my question</x-form.submit>
                    </form>
                </section>
            @elseif (! $viewer)
                <p class="mt-6 rounded-[12px] bg-white p-4 text-sm text-slate-muted">
                    Live in {{ $barangay->name }}? <a href="{{ route('login') }}" class="font-semibold text-emerald hover:underline">Log in</a> to ask your SK about this post or register for its events.
                </p>
            @endif
        </article>

        <aside class="space-y-6 lg:sticky lg:top-24 lg:self-start">
            <section class="rounded-[16px] border border-line bg-white p-5" aria-labelledby="about-heading">
                <div class="flex items-center gap-3">
                    <img src="{{ $barangay->logoUrl() }}" alt="" class="h-12 w-12 rounded-full border border-line bg-white object-contain p-0.5">
                    <div class="leading-tight">
                        <h2 id="about-heading" class="font-display font-semibold">SK {{ $barangay->name }}</h2>
                        <p class="text-sm text-slate-muted">Olongapo City</p>
                    </div>
                </div>
                <ul class="mt-4 space-y-1 text-sm font-semibold">
                    <li><a href="{{ route('announcements.index', ['barangay' => $barangay->id]) }}" class="flex justify-between rounded-[8px] px-2 py-1.5 hover:bg-mist">All their posts <span aria-hidden="true" class="text-slate-muted">›</span></a></li>
                    <li><a href="{{ route('events.index', ['barangay' => $barangay->id]) }}" class="flex justify-between rounded-[8px] px-2 py-1.5 hover:bg-mist">Their upcoming events <span aria-hidden="true" class="text-slate-muted">›</span></a></li>
                    <li><a href="{{ route('transparency.show', $barangay) }}" class="flex justify-between rounded-[8px] px-2 py-1.5 hover:bg-mist">How they use the SK fund <span aria-hidden="true" class="text-slate-muted">›</span></a></li>
                </ul>
                <x-barangay.facebook-link :barangay="$barangay" class="mt-3 flex w-full justify-center" />
            </section>

            @if ($more->isNotEmpty())
                <section aria-labelledby="more-heading">
                    <h2 id="more-heading" class="font-display font-semibold">More from SK {{ $barangay->name }}</h2>
                    <ul class="mt-3 space-y-3">
                        @foreach ($more as $other)
                            <li>
                                <a href="{{ route('announcements.show', $other) }}" class="flex gap-3 rounded-[12px] border border-line bg-white p-2.5 hover:border-emerald/50">
                                    <span class="block h-16 w-16 shrink-0 overflow-hidden rounded-[8px]">
                                        <x-announcement.cover :announcement="$other" size="thumb" />
                                    </span>
                                    <span class="min-w-0 py-0.5">
                                        <span class="line-clamp-2 text-sm font-semibold leading-snug">{{ $other->title }}</span>
                                        <span class="mt-1 block text-xs text-slate-muted">{{ $other->category->label() }}, {{ $other->postedAt()->format('M j') }}</span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </aside>
    </div>
</x-layouts.public>
