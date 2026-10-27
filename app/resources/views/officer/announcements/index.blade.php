<x-layouts.app title="Announcements" heading="Announcements" description="Posts appear on the public portal as soon as you publish them, and youth of your barangay get a notification.">
    <x-slot:actions>
        <a href="{{ route('officer.announcements.create') }}" class="inline-flex items-center gap-2 rounded-[8px] bg-emerald px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-dark">
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Post an announcement
        </a>
    </x-slot:actions>

    <div>
        <div class="mb-3 flex items-end justify-between gap-3">
            <h2 class="font-display text-base font-semibold text-navy">{{ trans_choice('{0}No posts yet|{1}1 post|[2,*]:count posts', $announcements->total()) }}</h2>
            <p class="text-xs text-slate-muted">Pinned posts stay on top of your public page.</p>
        </div>

        @if ($announcements->isEmpty())
            <div class="rounded-[14px] border border-dashed border-line bg-white px-6 py-10 text-center">
                <p class="font-display text-lg font-semibold text-navy">Nothing posted yet</p>
                <p class="mt-1 text-sm text-slate-muted">Share an event, a reminder or a thank-you. Youth of your barangay get a notification.</p>
                <a href="{{ route('officer.announcements.create') }}" class="mt-5 inline-flex rounded-[8px] bg-emerald px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-dark">Write the first post</a>
            </div>
        @else
            <ul class="divide-y divide-line overflow-hidden rounded-[14px] border border-line bg-white">
                @foreach ($announcements as $announcement)
                    <li class="grid grid-cols-[4.5rem_minmax(0,1fr)] gap-x-4 gap-y-3 p-4 sm:grid-cols-[7rem_minmax(0,1fr)_auto] sm:items-center sm:px-5">
                        <a href="{{ route('announcements.show', $announcement) }}" target="_blank" class="block aspect-[4/3] overflow-hidden rounded-[8px]" tabindex="-1">
                            <x-announcement.cover :announcement="$announcement" size="thumb" />
                        </a>
                        <div class="min-w-0">
                            <p class="flex flex-wrap items-center gap-x-2 text-xs font-semibold">
                                <span class="text-emerald">{{ $announcement->category->label() }}</span>
                                @if ($announcement->isPinned())
                                    <span class="inline-flex items-center gap-1 text-[#92400e]">
                                        <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 17v5M9 3h6l-1 6 3 3v2H7v-2l3-3-1-6Z"/></svg>
                                        Pinned
                                    </span>
                                @endif
                            </p>
                            <a href="{{ route('announcements.show', $announcement) }}" target="_blank" class="mt-0.5 block font-medium leading-snug text-navy hover:text-emerald">{{ $announcement->title }}<span class="sr-only"> (opens the public post)</span></a>
                            <p class="mt-0.5 line-clamp-1 text-sm text-slate-muted">{{ html_entity_decode(strip_tags((string) $announcement->captionHtml(160)), ENT_QUOTES) }}</p>
                            <p class="mt-1 text-xs text-slate-muted">
                                Posted {{ $announcement->postedAt()->format('M j, Y') }}{{ $announcement->author ? ' by '.$announcement->author->name : '' }}
                            </p>
                        </div>
                        <div class="col-span-2 flex items-center gap-2 sm:col-span-1 sm:justify-end">
                            <a href="{{ route('officer.announcements.edit', $announcement) }}" class="rounded-[8px] border border-line px-3 py-1.5 text-sm font-semibold text-navy hover:border-navy">Edit<span class="sr-only"> {{ $announcement->title }}</span></a>
                            <form method="POST" action="{{ route('officer.announcements.destroy', $announcement) }}" onsubmit="return confirm('Take this announcement down from the public portal?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-[8px] px-3 py-1.5 text-sm font-medium text-slate-muted hover:bg-[#b91c1c]/5 hover:text-[#b91c1c]">Take down<span class="sr-only"> {{ $announcement->title }}</span></button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

        <div class="mt-4">{{ $announcements->links() }}</div>
    </div>
</x-layouts.app>
