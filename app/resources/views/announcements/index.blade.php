<x-layouts.public title="Announcements" description="News, reminders and invitations from barangay SK councils in Olongapo City.">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div class="max-w-2xl">
            <h1 class="font-display text-3xl font-semibold tracking-tight sm:text-4xl">Announcements</h1>
            <p class="mt-3 leading-relaxed text-slate-muted">News, reminders and invitations from your barangay SK, in one place instead of scattered across group chats and pages.</p>
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

    @if ($announcements->isEmpty())
        <p class="mt-10 rounded-[12px] border border-dashed border-line bg-white p-8 text-center text-slate-muted">No announcements yet. Check back soon.</p>
    @else
        <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($announcements as $announcement)
                <x-announcement.post :announcement="$announcement" />
            @endforeach
        </div>
    @endif

    <div class="mt-8">{{ $announcements->links() }}</div>
</x-layouts.public>
