@props(['user'])
@php
    /*
     * The bell opens the latest notifications right in the header, so checking them never
     * leaves the page. Opening one marks it read and goes to what it's about.
     */
    $notifications = $user->notifications()->latest()->limit(15)->get();
    $unread = $notifications->whereNull('read_at')->count();
    $unreadTotal = $unread === 15 ? $user->unreadNotifications()->count() : $unread;
@endphp
<details class="relative" data-dropdown>
    <summary class="relative flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-[8px] border border-line hover:border-navy [&::-webkit-details-marker]:hidden" aria-label="Notifications{{ $unreadTotal ? ', '.$unreadTotal.' unread' : '' }}">
        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
        @if ($unreadTotal)
            <span class="absolute -right-1.5 -top-1.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-error px-1 text-[0.65rem] font-semibold text-white" aria-hidden="true">{{ $unreadTotal > 9 ? '9+' : $unreadTotal }}</span>
        @endif
    </summary>

    <div class="fixed inset-x-3 top-[4.25rem] z-50 overflow-hidden rounded-[14px] border border-line bg-white shadow-xl shadow-navy/10 sm:absolute sm:inset-x-auto sm:right-0 sm:top-auto sm:mt-2 sm:w-[24rem]">
        <div class="flex items-center justify-between gap-3 border-b border-line px-4 py-3">
            <p class="font-display font-semibold">
                Notifications
                @if ($unreadTotal)
                    <span class="ml-1 rounded-full bg-error/10 px-2 py-0.5 text-xs font-semibold text-[#b91c1c]">{{ $unreadTotal }} new</span>
                @endif
            </p>
            @if ($unreadTotal)
                <form method="POST" action="{{ route('notifications.read') }}">
                    @csrf
                    <button type="submit" class="text-xs font-semibold text-emerald hover:underline">Mark all as read</button>
                </form>
            @endif
        </div>

        <ul class="max-h-[min(28rem,70dvh)] divide-y divide-line overflow-y-auto overscroll-contain">
            @forelse ($notifications as $notification)
                <li>
                    <a href="{{ route('notifications.show', $notification->id) }}" @class(['flex items-start gap-3 px-4 py-3 hover:bg-mist', 'bg-info/5' => $notification->unread()])>
                        <span @class(['mt-1.5 h-2 w-2 shrink-0 rounded-full', 'bg-info' => $notification->unread(), 'bg-transparent' => $notification->read()])>
                            <span class="sr-only">{{ $notification->unread() ? 'Unread' : 'Read' }}</span>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span @class(['block text-sm leading-snug', 'font-semibold' => $notification->unread(), 'font-medium' => $notification->read()])>{{ $notification->data['title'] ?? 'Update' }}</span>
                            @if (! empty($notification->data['body']))
                                <span class="mt-0.5 line-clamp-2 block text-sm text-navy/70">{{ $notification->data['body'] }}</span>
                            @endif
                            <span class="mt-1 block text-xs text-slate-muted">{{ $notification->created_at->diffForHumans() }}</span>
                        </span>
                    </a>
                </li>
            @empty
                <li class="px-4 py-10 text-center">
                    <svg viewBox="0 0 24 24" class="mx-auto h-8 w-8 text-line" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                    <p class="mt-2 text-sm font-medium">You’re all caught up</p>
                    <p class="text-xs text-slate-muted">Replies, reminders and updates from your SK show up here.</p>
                </li>
            @endforelse
        </ul>

        @if ($notifications->count() === 15)
            <p class="border-t border-line px-4 py-2 text-center text-xs text-slate-muted">Showing your 15 latest</p>
        @endif
    </div>
</details>
