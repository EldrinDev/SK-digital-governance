@props([
    'announcement',
    'excerpt' => 180,
])
{{-- One announcement as a social-style post: who posted it, the photo or pubmat, then the caption. --}}
<article {{ $attributes->class(['flex flex-col overflow-hidden rounded-[12px] border border-line bg-white']) }}>
    <header class="flex items-center gap-3 px-4 py-3">
        <img src="{{ asset('images/sk-logo.svg') }}" alt="" class="h-9 w-9 rounded-full border border-line p-0.5">
        <div class="min-w-0 flex-1 leading-tight">
            <p class="truncate text-sm font-semibold">SK {{ $announcement->barangay->name }}</p>
            <p class="text-xs text-slate-muted">
                <time datetime="{{ $announcement->created_at->toIso8601String() }}" title="{{ $announcement->created_at->format('F j, Y g:i A') }}">{{ $announcement->created_at->diffForHumans() }}</time>
            </p>
        </div>
        @if ($announcement->isPinned())
            <span class="inline-flex items-center gap-1 rounded-full bg-gold/20 px-2 py-0.5 text-xs font-semibold text-navy">
                <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 17v5"/><path d="M9 3h6l-1 7 4 3v2H6v-2l4-3-1-7Z"/></svg>
                Pinned
            </span>
        @endif
    </header>

    <a href="{{ route('announcements.show', $announcement) }}" class="block aspect-[4/3] bg-mist">
        <x-announcement.cover :announcement="$announcement" />
        <span class="sr-only">{{ $announcement->title }}</span>
    </a>

    <div class="flex flex-1 flex-col px-4 pb-4 pt-3.5">
        <h3 class="font-display text-base font-semibold leading-snug">
            <a href="{{ route('announcements.show', $announcement) }}" class="hover:text-emerald">{{ $announcement->title }}</a>
        </h3>
        <p class="mt-1.5 whitespace-pre-line text-sm leading-relaxed text-navy/75">{{ $announcement->captionHtml($excerpt) }}</p>
        <a href="{{ route('announcements.show', $announcement) }}" class="mt-auto pt-3 text-sm font-semibold text-emerald hover:underline">Read the full post</a>
    </div>
</article>
