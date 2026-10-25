<x-layouts.public title="Events" description="Upcoming youth events and consultations from barangay SK councils in Olongapo City.">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div class="max-w-2xl">
            <h1 class="font-display text-3xl font-semibold tracking-tight sm:text-4xl">Events and consultations</h1>
            <p class="mt-3 leading-relaxed text-slate-muted">Join programs run by your barangay SK, or have your say in a youth consultation. Registration is for youth of the barangay hosting the event.</p>
        </div>
        <form method="GET" class="flex gap-2">
            <label for="barangay" class="sr-only">Barangay</label>
            <select id="barangay" name="barangay" class="rounded-[8px] border border-line bg-white px-3 py-2 text-sm">
                <option value="">All barangays</option>
                @foreach ($barangays as $barangay)
                    <option value="{{ $barangay->id }}" @selected($selectedBarangay === $barangay->id)>{{ $barangay->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-[8px] bg-navy px-4 py-2 text-sm font-semibold text-white hover:bg-navy-deep">Show</button>
        </form>
    </div>

    <ul class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($events as $event)
            <li>
                <a href="{{ route('events.show', $event) }}" class="flex h-full flex-col rounded-[12px] border border-line bg-white p-5 transition-colors hover:border-emerald/50">
                    <span class="flex items-center justify-between gap-2 text-xs">
                        <span class="font-semibold text-emerald">{{ $event->kind->label() }}</span>
                        <span class="text-slate-muted">{{ $event->barangay->name }}</span>
                    </span>
                    <span class="mt-2 font-display text-lg font-semibold">{{ $event->title }}</span>
                    <span class="mt-1 text-sm text-slate-muted">{{ $event->starts_at->format('D, M j · g:i A') }} · {{ $event->venue }}</span>
                    <span class="mt-auto pt-4 text-xs font-medium">
                        @if ($event->capacity === null)
                            Open registration
                        @elseif ($event->isFull())
                            <span class="text-[#b91c1c]">Full</span>
                        @else
                            {{ $event->spotsLeft() }} of {{ $event->capacity }} places left
                        @endif
                    </span>
                </a>
            </li>
        @empty
            <li class="rounded-[12px] border border-dashed border-line bg-white p-8 text-center text-slate-muted sm:col-span-2 lg:col-span-3">No upcoming events right now. Check back soon.</li>
        @endforelse
    </ul>

    <div class="mt-6">{{ $events->links() }}</div>
</x-layouts.public>
