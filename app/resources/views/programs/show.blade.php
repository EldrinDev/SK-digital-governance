@php
    use App\PpaStatus;

    $barangay = $program->barangay;
    $photos = $program->evidence->filter->isImage();
    $documents = $program->evidence->reject->isImage();
    $done = $program->milestones->whereNotNull('completed_at')->count();
    $timing = match (true) {
        $program->status === PpaStatus::Completed => 'Finished',
        $program->starts_on->isFuture() => 'Starts in '.(int) today()->diffInDays($program->starts_on).' days',
        $program->ends_on->isPast() => 'Running late',
        default => (int) today()->diffInDays($program->ends_on).' days left',
    };
@endphp
<x-layouts.public :title="$program->title" :description="'A '.$program->priority->center->label().' program of SK '.$barangay->name.': '.$program->target.'.'">
    <nav class="flex flex-wrap items-center gap-1.5 text-sm text-slate-muted" aria-label="Breadcrumb">
        <a href="{{ route('programs.index') }}" class="font-medium hover:text-emerald">Programs</a>
        <span aria-hidden="true">/</span>
        <a href="{{ route('programs.index', ['barangay' => $barangay->id]) }}" class="font-medium hover:text-emerald">SK {{ $barangay->name }}</a>
    </nav>

    <div class="mt-5 grid gap-8 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <article class="min-w-0">
            <header>
                <p class="flex flex-wrap items-center gap-2 text-sm">
                    <span class="rounded-full bg-emerald/10 px-2.5 py-0.5 font-semibold text-emerald">{{ $program->priority->center->label() }}</span>
                    <x-ui.ppa-status :ppa="$program" />
                </p>
                <h1 class="mt-3 max-w-3xl font-display text-3xl font-semibold leading-tight tracking-tight sm:text-4xl">{{ $program->title }}</h1>
                <div class="mt-4 flex items-center gap-3">
                    <img src="{{ $barangay->logoUrl() }}" alt="" class="h-10 w-10 rounded-full border border-line bg-white object-contain p-0.5">
                    <p class="text-sm leading-tight">
                        <span class="block font-semibold">A program of SK {{ $barangay->name }}</span>
                        <span class="text-slate-muted">Part of the council-approved ABYIP {{ $program->abyip->year }}</span>
                    </p>
                </div>
            </header>

            {{-- The four things people want to know first --}}
            <dl class="mt-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
                <div class="rounded-[14px] border border-line bg-white p-4">
                    <dt class="text-xs text-slate-muted">Goal</dt>
                    <dd class="mt-1 font-semibold leading-snug">{{ $program->target }}</dd>
                </div>
                <div class="rounded-[14px] border border-line bg-white p-4">
                    <dt class="text-xs text-slate-muted">When</dt>
                    <dd class="mt-1 font-semibold leading-snug">{{ $program->starts_on->format('M j') }} – {{ $program->ends_on->format('M j, Y') }}</dd>
                    <dd @class(['text-xs font-semibold', 'text-[#b91c1c]' => $timing === 'Running late', 'text-emerald' => $timing !== 'Running late'])>{{ $timing }}</dd>
                </div>
                <div class="rounded-[14px] border border-line bg-white p-4">
                    <dt class="text-xs text-slate-muted">Budget</dt>
                    <dd class="mt-1 font-semibold tabular-nums">₱{{ number_format($program->budget) }}</dd>
                    @if ($program->amount_spent !== null)
                        <dd class="text-xs text-slate-muted tabular-nums">₱{{ number_format($program->amount_spent) }} spent so far</dd>
                    @endif
                </div>
                <div class="rounded-[14px] border border-line bg-white p-4">
                    <dt class="text-xs text-slate-muted">Progress</dt>
                    <dd class="mt-1 font-semibold tabular-nums">{{ $program->progress() }}% done</dd>
                    <dd class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-mist" aria-hidden="true"><span class="block h-full rounded-full bg-emerald" style="width: {{ $program->progress() }}%"></span></dd>
                </div>
            </dl>

            @if ($program->description)
                <section class="mt-6 rounded-[16px] border border-line bg-white p-5 sm:p-7" aria-labelledby="about-heading">
                    <h2 id="about-heading" class="font-display text-lg font-semibold">About this program</h2>
                    <p class="mt-2 max-w-[65ch] whitespace-pre-line leading-relaxed text-navy/85">{{ $program->description }}</p>
                </section>
            @endif

            {{-- Events youth can join --}}
            <section class="mt-6" aria-labelledby="join-heading">
                <h2 id="join-heading" class="font-display text-lg font-semibold">Join in</h2>
                @if ($upcomingEvents->isEmpty())
                    <p class="mt-3 rounded-[14px] border border-dashed border-line bg-white p-5 text-sm text-slate-muted">
                        No events open right now{{ $pastEventsCount ? ', '.trans_choice('{1}1 event already took place|[2,*]:count events already took place', $pastEventsCount) : '' }}. Follow SK {{ $barangay->name }}’s announcements for the next one.
                    </p>
                @else
                    <ul class="mt-3 grid gap-3 sm:grid-cols-2">
                        @foreach ($upcomingEvents as $event)
                            <li>
                                <a href="{{ route('events.show', $event) }}" class="flex h-full items-center gap-4 rounded-[14px] border border-line bg-white p-4 hover:border-emerald/50">
                                    <x-event.date-block :event="$event" size="sm" />
                                    <span class="min-w-0">
                                        <span class="line-clamp-2 block font-semibold leading-snug">{{ $event->title }}</span>
                                        <span class="block truncate text-xs text-slate-muted">{{ $event->starts_at->format('g:i A') }}, {{ $event->venue }}</span>
                                        <span class="mt-1 block text-xs font-semibold text-emerald">{{ $event->isFull() ? 'Full' : 'Register now' }}</span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            {{-- Steps of the program --}}
            @if ($program->milestones->isNotEmpty())
                <section class="mt-6 rounded-[16px] border border-line bg-white p-5 sm:p-7" aria-labelledby="steps-heading">
                    <div class="flex items-center justify-between gap-3">
                        <h2 id="steps-heading" class="font-display text-lg font-semibold">Steps</h2>
                        <span class="text-sm text-slate-muted">{{ $done }} of {{ $program->milestones->count() }} done</span>
                    </div>
                    <ol class="mt-4 space-y-0">
                        @foreach ($program->milestones as $milestone)
                            <li class="relative flex gap-4 pb-5 last:pb-0">
                                @unless ($loop->last)
                                    <span class="absolute left-[0.6875rem] top-6 h-full w-px bg-line" aria-hidden="true"></span>
                                @endunless
                                <span @class([
                                    'relative flex h-6 w-6 shrink-0 items-center justify-center rounded-full border-2',
                                    'border-emerald bg-emerald text-white' => $milestone->isCompleted(),
                                    'border-error bg-white' => $milestone->isOverdue(),
                                    'border-line bg-white' => ! $milestone->isCompleted() && ! $milestone->isOverdue(),
                                ]) aria-hidden="true">
                                    @if ($milestone->isCompleted())
                                        <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5 9-10"/></svg>
                                    @endif
                                </span>
                                <div class="min-w-0 pt-0.5">
                                    <p @class(['font-medium leading-snug', 'text-navy/60' => $milestone->isCompleted()])>{{ $milestone->title }}</p>
                                    <p @class(['text-xs', 'font-semibold text-[#b91c1c]' => $milestone->isOverdue(), 'text-slate-muted' => ! $milestone->isOverdue()])>
                                        @if ($milestone->isCompleted())
                                            Done {{ $milestone->completed_at->format('M j, Y') }}
                                        @elseif ($milestone->isOverdue())
                                            Was due {{ $milestone->due_on->format('M j, Y') }}
                                        @else
                                            Due {{ $milestone->due_on->format('M j, Y') }}
                                        @endif
                                    </p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </section>
            @endif

            {{-- Proof of work the SK made public --}}
            @if ($program->evidence->isNotEmpty())
                <section class="mt-6" aria-labelledby="photos-heading">
                    <h2 id="photos-heading" class="font-display text-lg font-semibold">Photos and documents</h2>
                    @if ($photos->isNotEmpty())
                        <ul class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3">
                            @foreach ($photos as $file)
                                <li>
                                    <a href="{{ route('transparency.evidence', $file) }}" target="_blank" class="block overflow-hidden rounded-[12px] border border-line bg-white hover:border-emerald">
                                        <img src="{{ route('transparency.evidence', $file) }}" alt="{{ $file->title }}" loading="lazy" class="aspect-[4/3] w-full object-cover">
                                        <span class="block truncate px-3 py-2 text-xs font-medium">{{ $file->title }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    @foreach ($documents as $file)
                        <a href="{{ route('transparency.evidence', $file) }}" target="_blank" class="mt-3 flex items-center gap-3 rounded-[12px] border border-line bg-white p-3 text-sm hover:border-emerald">
                            <span class="flex h-9 w-9 items-center justify-center rounded-[8px] bg-mist text-xs font-semibold text-slate-muted">PDF</span>
                            <span class="font-medium">{{ $file->title }}</span>
                        </a>
                    @endforeach
                </section>
            @endif
        </article>

        <aside class="space-y-6 lg:sticky lg:top-24 lg:self-start">
            <section class="rounded-[16px] border border-line bg-white p-5" aria-labelledby="sk-heading">
                <div class="flex items-center gap-3">
                    <img src="{{ $barangay->logoUrl() }}" alt="" class="h-12 w-12 rounded-full border border-line bg-white object-contain p-0.5">
                    <div class="leading-tight">
                        <h2 id="sk-heading" class="font-display font-semibold">SK {{ $barangay->name }}</h2>
                        <p class="text-sm text-slate-muted">Olongapo City</p>
                    </div>
                </div>
                <ul class="mt-4 space-y-1 text-sm font-semibold">
                    <li><a href="{{ route('programs.index', ['barangay' => $barangay->id]) }}" class="flex justify-between rounded-[8px] px-2 py-1.5 hover:bg-mist">All their programs <span aria-hidden="true" class="text-slate-muted">›</span></a></li>
                    <li><a href="{{ route('events.index', ['barangay' => $barangay->id]) }}" class="flex justify-between rounded-[8px] px-2 py-1.5 hover:bg-mist">Their upcoming events <span aria-hidden="true" class="text-slate-muted">›</span></a></li>
                    <li><a href="{{ route('transparency.show', $barangay) }}" class="flex justify-between rounded-[8px] px-2 py-1.5 hover:bg-mist">Full budget on the transparency board <span aria-hidden="true" class="text-slate-muted">›</span></a></li>
                </ul>
                <x-barangay.facebook-link :barangay="$barangay" class="mt-3 flex w-full justify-center" />
            </section>

            @if ($more->isNotEmpty())
                <section aria-labelledby="more-heading">
                    <h2 id="more-heading" class="font-display font-semibold">More from SK {{ $barangay->name }}</h2>
                    <ul class="mt-3 space-y-3">
                        @foreach ($more as $other)
                            <li>
                                <a href="{{ route('programs.show', $other) }}" class="block rounded-[12px] border border-line bg-white p-3.5 hover:border-emerald/50">
                                    <span class="flex items-center justify-between gap-2 text-xs">
                                        <span class="font-semibold text-emerald">{{ $other->priority->center->label() }}</span>
                                        <x-ui.ppa-status :ppa="$other" />
                                    </span>
                                    <span class="mt-1 line-clamp-2 block text-sm font-semibold leading-snug">{{ $other->title }}</span>
                                    <span class="mt-2 block h-1 overflow-hidden rounded-full bg-mist" aria-hidden="true"><span class="block h-full rounded-full bg-emerald" style="width: {{ $other->progress() }}%"></span></span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </aside>
    </div>
</x-layouts.public>
