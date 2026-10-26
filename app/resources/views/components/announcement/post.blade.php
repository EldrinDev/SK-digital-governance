@props([
    'announcement',
    'excerpt' => 180,
])
{{-- One announcement as a social-style post: who posted it, the photo or pubmat, then the caption. --}}
<article {{ $attributes->class(['flex flex-col overflow-hidden rounded-[12px] border border-line bg-white']) }}>
    <header class="flex items-center gap-3 px-4 py-3">
        <img src="{{ $announcement->barangay->logoUrl() }}" alt="" class="h-9 w-9 rounded-full border border-line bg-white object-contain p-0.5">
        <div class="min-w-0 flex-1 leading-tight">
            <p class="truncate text-sm font-semibold">SK {{ $announcement->barangay->name }}</p>
            <p class="text-xs text-slate-muted">
                <time datetime="{{ $announcement->postedAt()->toIso8601String() }}" title="{{ $announcement->postedAt()->format('F j, Y g:i A') }}">{{ $announcement->postedAt()->diffForHumans() }}</time>
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
        <p class="mt-1.5 whitespace-pre-line text-sm leading-relaxed text-navy/75">{{ $announcement->captionHtml($excerpt, withoutDetails: true) }}</p>
        <div class="mt-auto flex flex-wrap items-center justify-between gap-2 pt-3">
            <a href="{{ route('announcements.show', $announcement) }}" class="text-sm font-semibold text-emerald hover:underline">Read the full post</a>
            @if ($announcement->isFromFacebook())
                <span class="inline-flex items-center gap-1 text-xs text-slate-muted">
                    <svg viewBox="0 0 24 24" class="h-3.5 w-3.5 text-[#1877f2]" fill="currentColor" aria-hidden="true"><path d="M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.05V9.41c0-3.02 1.79-4.69 4.53-4.69 1.31 0 2.68.24 2.68.24v2.97h-1.51c-1.49 0-1.96.93-1.96 1.89v2.25h3.33l-.53 3.49h-2.8V24C19.61 23.1 24 18.1 24 12.07Z"/></svg>
                    From Facebook
                </span>
            @endif
        </div>
    </div>
</article>
