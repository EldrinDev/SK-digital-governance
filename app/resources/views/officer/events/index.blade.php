<x-layouts.app title="Events" heading="Events and consultations" description="Events you post appear on the public events page right away. Youth of your barangay register there.">
    <x-slot:actions>
        <a href="{{ route('officer.events.create') }}" class="inline-flex items-center gap-2 rounded-[8px] bg-emerald px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-dark">
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Post an event
        </a>
    </x-slot:actions>

    <section class="rounded-[12px] border border-line bg-white" aria-labelledby="upcoming-heading">
        <h2 id="upcoming-heading" class="border-b border-line px-5 py-4 font-display text-lg font-semibold">Upcoming</h2>
        <ul class="divide-y divide-line">
            @forelse ($upcoming as $event)
                <li class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5">
                    <div>
                        <a href="{{ route('officer.events.show', $event) }}" class="font-medium hover:text-emerald">{{ $event->title }}</a>
                        <p class="text-xs text-slate-muted">{{ $event->kind->label() }} · {{ $event->starts_at->format('D, M j · g:i A') }} · {{ $event->venue }}</p>
                    </div>
                    <span class="text-sm text-slate-muted">{{ $event->registrations_count }}{{ $event->capacity ? ' of '.$event->capacity : '' }} registered</span>
                </li>
            @empty
                <li class="px-5 py-6 text-sm text-slate-muted">Nothing scheduled. Post an event or consultation for your youth.</li>
            @endforelse
        </ul>
    </section>

    <section class="mt-6 overflow-x-auto rounded-[12px] border border-line bg-white" aria-labelledby="past-heading">
        <h2 id="past-heading" class="border-b border-line px-5 py-4 font-display text-lg font-semibold">Past</h2>
        @if ($past->isEmpty())
            <p class="px-5 py-6 text-sm text-slate-muted">No past events yet.</p>
        @else
            <table class="w-full min-w-[40rem] text-left text-sm">
                <thead class="border-b border-line bg-mist/60 text-xs font-semibold text-slate-muted">
                    <tr>
                        <th scope="col" class="px-5 py-3">Event</th>
                        <th scope="col" class="px-5 py-3">Date</th>
                        <th scope="col" class="px-5 py-3 text-right">Attended</th>
                        <th scope="col" class="px-5 py-3 text-right">Average rating</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @foreach ($past as $event)
                        <tr>
                            <td class="px-5 py-3"><a href="{{ route('officer.events.show', $event) }}" class="font-medium hover:text-emerald">{{ $event->title }}</a></td>
                            <td class="px-5 py-3">{{ $event->starts_at->format('M j, Y') }}</td>
                            <td class="px-5 py-3 text-right tabular-nums">{{ $event->attended_count }} of {{ $event->registrations_count }}</td>
                            <td class="px-5 py-3 text-right tabular-nums">{{ $event->feedback_avg_rating ? number_format($event->feedback_avg_rating, 1).' / 5' : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>
</x-layouts.app>
