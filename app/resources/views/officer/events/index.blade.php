@php
    use App\EventKind;

    $sheet = 'overflow-hidden rounded-[14px] border border-line bg-white';
    $sectionTitle = 'mb-3 font-display text-base font-semibold text-navy';
    $inDays = fn ($date): string => $date->isToday() ? 'Today' : ($date->isTomorrow() ? 'Tomorrow' : 'In '.(int) ceil(now()->diffInDays($date)).' days');
    $rated = $past->filter(fn ($event) => $event->feedback_avg_rating !== null);
    $summary = [
        ['value' => $upcoming->count(), 'label' => 'coming up'],
        ['value' => $upcoming->sum('registrations_count'), 'label' => 'youth registered for them'],
        ['value' => $past->sum('registrations_count') > 0 ? round($past->sum('attended_count') / $past->sum('registrations_count') * 100).'%' : '—', 'label' => 'turnout at past events'],
        ['value' => $rated->isNotEmpty() ? number_format($rated->avg('feedback_avg_rating'), 1).' / 5' : '—', 'label' => 'average rating'],
    ];
@endphp
<x-layouts.app title="Events" heading="Events and consultations" description="Events you post appear on the public events page right away. Youth of your barangay register there.">
    <x-slot:actions>
        <a href="{{ route('officer.events.create') }}" class="inline-flex items-center gap-2 rounded-[8px] bg-emerald px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-dark">
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Post an event
        </a>
    </x-slot:actions>

    <dl class="mb-10 grid grid-cols-2 gap-y-5 border-y border-line py-5 sm:grid-cols-4">
        @foreach ($summary as $figure)
            <div @class(['px-1 sm:px-5', 'sm:border-l sm:border-line' => ! $loop->first])>
                <dt class="sr-only">{{ $figure['label'] }}</dt>
                <dd class="font-display text-2xl font-semibold text-navy">{{ $figure['value'] }}</dd>
                <dd class="text-sm text-slate-muted">{{ $figure['label'] }}</dd>
            </div>
        @endforeach
    </dl>

    <section aria-labelledby="upcoming-heading">
        <h2 id="upcoming-heading" class="{{ $sectionTitle }}">Coming up</h2>
        @if ($upcoming->isEmpty())
            <div class="rounded-[14px] border border-dashed border-line bg-white px-6 py-8 text-center">
                <p class="font-medium text-navy">Nothing scheduled</p>
                <p class="mt-1 text-sm text-slate-muted">Post an event or a youth consultation. It shows on the public events page and youth can register right away.</p>
            </div>
        @else
            <ul class="{{ $sheet }} divide-y divide-line">
                @foreach ($upcoming as $event)
                    @php
                        $isConsultation = $event->kind === EventKind::Consultation;
                        $share = $event->capacity ? min(100, (int) round($event->registrations_count / $event->capacity * 100)) : null;
                    @endphp
                    <li>
                        <a href="{{ route('officer.events.show', $event) }}" class="group grid grid-cols-[3rem_minmax(0,1fr)] items-center gap-x-5 gap-y-3 px-5 py-4 hover:bg-mist/50 sm:grid-cols-[3rem_minmax(0,1fr)_13rem_1rem]">
                            <time datetime="{{ $event->starts_at->toIso8601String() }}" class="text-center leading-none">
                                <span class="block text-[0.7rem] font-semibold text-slate-muted">{{ $event->starts_at->format('M') }}</span>
                                <span class="mt-1 block font-display text-2xl font-semibold text-navy">{{ $event->starts_at->format('j') }}</span>
                                <span class="mt-1 block text-[0.7rem] text-slate-muted">{{ $event->starts_at->format('D') }}</span>
                            </time>
                            <span class="min-w-0 border-l border-line pl-5">
                                <span @class(['block text-xs font-semibold', 'text-[#92400e]' => $isConsultation, 'text-emerald' => ! $isConsultation])>{{ $isConsultation ? 'Youth consultation' : 'Event' }}, {{ str($inDays($event->starts_at))->lcfirst() }}</span>
                                <span class="mt-0.5 block font-medium leading-snug text-navy group-hover:text-emerald">{{ $event->title }}</span>
                                <span class="mt-0.5 block truncate text-sm text-slate-muted">{{ $event->starts_at->format('g:i A') }} at {{ $event->venue }}</span>
                            </span>
                            <span class="col-span-2 block pl-[4.25rem] sm:col-span-1 sm:pl-0">
                                <span class="flex items-baseline justify-between text-sm">
                                    <span class="font-semibold text-navy">{{ $event->registrations_count }} registered</span>
                                    <span class="text-xs text-slate-muted">{{ $event->capacity ? 'of '.$event->capacity : 'no limit' }}</span>
                                </span>
                                @if ($share !== null)
                                    <span class="mt-1.5 block h-1.5 overflow-hidden rounded-full bg-mist" aria-hidden="true">
                                        <span @class(['block h-full rounded-full', 'bg-[#b45309]' => $share >= 90, 'bg-emerald' => $share < 90]) style="width: {{ max($share, 2) }}%"></span>
                                    </span>
                                @endif
                            </span>
                            <svg viewBox="0 0 24 24" class="hidden h-4 w-4 text-slate-muted group-hover:text-emerald sm:block" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <section class="mt-10" aria-labelledby="past-heading">
        <h2 id="past-heading" class="{{ $sectionTitle }}">Past events</h2>
        @if ($past->isEmpty())
            <p class="rounded-[14px] border border-dashed border-line bg-white px-5 py-6 text-sm text-slate-muted">Events move here once they start. You’ll see who came and how youth rated them.</p>
        @else
            <ul class="{{ $sheet }} divide-y divide-line">
                @foreach ($past as $event)
                    @php
                        $turnout = $event->registrations_count > 0 ? (int) round($event->attended_count / $event->registrations_count * 100) : null;
                        $rating = $event->feedback_avg_rating !== null ? round($event->feedback_avg_rating, 1) : null;
                    @endphp
                    <li>
                        <a href="{{ route('officer.events.show', $event) }}" class="group grid grid-cols-[3rem_minmax(0,1fr)] items-center gap-x-5 gap-y-2 px-5 py-3.5 hover:bg-mist/50 sm:grid-cols-[3rem_minmax(0,1fr)_9rem_8rem_1rem]">
                            <time datetime="{{ $event->starts_at->toIso8601String() }}" class="text-center leading-none">
                                <span class="block text-[0.7rem] font-semibold text-slate-muted">{{ $event->starts_at->format('M') }}</span>
                                <span class="mt-1 block font-display text-xl font-semibold text-navy/80">{{ $event->starts_at->format('j') }}</span>
                            </time>
                            <span class="min-w-0 border-l border-line pl-5">
                                <span class="block truncate font-medium text-navy group-hover:text-emerald">{{ $event->title }}</span>
                                <span class="block text-xs text-slate-muted">{{ $event->starts_at->format('Y') }}, {{ $event->venue }}</span>
                            </span>
                            <span class="col-span-2 pl-[4.25rem] text-sm sm:col-span-1 sm:pl-0">
                                <span class="font-semibold text-navy">{{ $event->attended_count }}</span><span class="text-slate-muted"> of {{ $event->registrations_count }} came</span>
                                @if ($turnout !== null)
                                    <span class="block text-xs text-slate-muted">{{ $turnout }}% turnout</span>
                                @endif
                            </span>
                            <span class="col-span-2 pl-[4.25rem] text-sm sm:col-span-1 sm:pl-0">
                                @if ($rating !== null)
                                    <span class="text-gold" aria-hidden="true">{{ str_repeat('★', (int) round($rating)) }}</span><span class="text-line" aria-hidden="true">{{ str_repeat('★', 5 - (int) round($rating)) }}</span>
                                    <span class="block text-xs text-slate-muted">{{ number_format($rating, 1) }} average<span class="sr-only"> rating out of 5</span></span>
                                @else
                                    <span class="text-xs text-slate-muted">No ratings yet</span>
                                @endif
                            </span>
                            <svg viewBox="0 0 24 24" class="hidden h-4 w-4 text-slate-muted group-hover:text-emerald sm:block" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</x-layouts.app>
