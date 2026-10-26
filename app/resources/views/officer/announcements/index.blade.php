<x-layouts.app title="Announcements" heading="Announcements" description="Posts appear on the public portal as soon as you publish them, and youth of your barangay get a notification.">
    <x-slot:actions>
        <a href="{{ route('officer.announcements.create') }}" class="inline-flex items-center gap-2 rounded-[8px] bg-emerald px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-dark">
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Post an announcement
        </a>
    </x-slot:actions>

    <ul class="max-w-4xl space-y-3">
        @forelse ($announcements as $announcement)
            <li class="flex gap-4 rounded-[12px] border border-line bg-white p-3 sm:p-4">
                <a href="{{ route('announcements.show', $announcement) }}" target="_blank" class="block h-20 w-20 shrink-0 overflow-hidden rounded-[8px] sm:h-24 sm:w-32">
                    <x-announcement.cover :announcement="$announcement" size="thumb" />
                    <span class="sr-only">View {{ $announcement->title }} on the public portal</span>
                </a>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-start justify-between gap-x-4 gap-y-2">
                        <div class="min-w-0">
                            <p class="flex flex-wrap items-center gap-2 text-xs">
                                <span class="rounded-full bg-mist px-2 py-0.5 font-semibold">{{ $announcement->category->label() }}</span>
                                @if ($announcement->isPinned())
                                    <span class="rounded-full bg-gold/20 px-2 py-0.5 font-semibold">Pinned</span>
                                @endif
                                <span class="text-slate-muted">
                                    {{ $announcement->postedAt()->format('M j, Y · g:i A') }}{{ $announcement->author ? ', by '.$announcement->author->name : '' }}
                                </span>
                            </p>
                            <h2 class="mt-1.5 font-display text-base font-semibold leading-snug sm:text-lg">{{ $announcement->title }}</h2>
                        </div>
                        <div class="flex items-center gap-4 text-sm font-semibold">
                            <a href="{{ route('officer.announcements.edit', $announcement) }}" class="text-emerald hover:underline">Edit</a>
                            <form method="POST" action="{{ route('officer.announcements.destroy', $announcement) }}" onsubmit="return confirm('Take this announcement down from the public portal?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-muted hover:text-error">Take down</button>
                            </form>
                        </div>
                    </div>
                    <p class="mt-1.5 line-clamp-2 text-sm leading-relaxed text-navy/75">{{ $announcement->captionHtml(220) }}</p>
                </div>
            </li>
        @empty
            <li class="rounded-[12px] border border-dashed border-line bg-white p-8 text-center text-sm text-slate-muted">No announcements yet. Post one to reach the youth of your barangay.</li>
        @endforelse
    </ul>

    <div class="mt-4 max-w-4xl">{{ $announcements->links() }}</div>
</x-layouts.app>
