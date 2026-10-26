<x-layouts.public title="Announcements" description="News and notices from barangay SK councils in Olongapo City.">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div class="max-w-2xl">
            <h1 class="font-display text-3xl font-semibold tracking-tight sm:text-4xl">Announcements</h1>
            <p class="mt-3 leading-relaxed text-slate-muted">News and notices from your barangay SK council.</p>
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

    <ul class="mt-10 max-w-3xl space-y-4">
        @forelse ($announcements as $announcement)
            <li>
                <a href="{{ route('announcements.show', $announcement) }}" class="block rounded-[12px] border border-line bg-white p-5 transition-colors hover:border-emerald/50">
                    <span class="flex items-center justify-between gap-2 text-xs">
                        <span class="font-semibold text-emerald">{{ $announcement->barangay->name }}</span>
                        <time datetime="{{ $announcement->created_at->toDateString() }}" class="text-slate-muted">{{ $announcement->created_at->format('M j, Y') }}</time>
                    </span>
                    <span class="mt-2 block font-display text-lg font-semibold">{{ $announcement->title }}</span>
                    <span class="mt-1 line-clamp-2 block text-sm text-slate-muted">{{ $announcement->body }}</span>
                </a>
            </li>
        @empty
            <li class="rounded-[12px] border border-dashed border-line bg-white p-8 text-center text-slate-muted">No announcements yet. Check back soon.</li>
        @endforelse
    </ul>

    <div class="mt-6 max-w-3xl">{{ $announcements->links() }}</div>
</x-layouts.public>
