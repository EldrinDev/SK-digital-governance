<x-layouts.app title="Announcements" heading="Announcements" description="Announcements appear on the public portal as soon as you post them, and youth of your barangay get a notification.">
    <x-slot:actions>
        <a href="{{ route('officer.announcements.create') }}" class="inline-flex items-center gap-2 rounded-[8px] bg-emerald px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-dark">
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Post an announcement
        </a>
    </x-slot:actions>

    <ul class="max-w-3xl space-y-3">
        @forelse ($announcements as $announcement)
            <li class="rounded-[12px] border border-line bg-white p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="font-display text-lg font-semibold">{{ $announcement->title }}</h2>
                        <p class="mt-0.5 text-xs text-slate-muted">
                            Posted {{ $announcement->created_at->format('M j, Y · g:i A') }}{{ $announcement->author ? ' by '.$announcement->author->name : '' }}
                            @if ($announcement->updated_at->gt($announcement->created_at))
                                · edited {{ $announcement->updated_at->diffForHumans() }}
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-4 text-sm font-semibold">
                        <a href="{{ route('announcements.show', $announcement) }}" target="_blank" class="text-slate-muted hover:text-navy">View public</a>
                        <a href="{{ route('officer.announcements.edit', $announcement) }}" class="text-emerald hover:underline">Edit</a>
                        <form method="POST" action="{{ route('officer.announcements.destroy', $announcement) }}" onsubmit="return confirm('Take this announcement down from the public portal?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-slate-muted hover:text-error">Take down</button>
                        </form>
                    </div>
                </div>
                <p class="mt-3 line-clamp-3 whitespace-pre-line text-sm leading-relaxed text-navy/80">{{ $announcement->body }}</p>
            </li>
        @empty
            <li class="rounded-[12px] border border-dashed border-line bg-white p-8 text-center text-sm text-slate-muted">No announcements yet. Post one to reach the youth of your barangay.</li>
        @endforelse
    </ul>

    <div class="mt-4 max-w-3xl">{{ $announcements->links() }}</div>
</x-layouts.app>
