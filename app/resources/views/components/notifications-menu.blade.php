@props(['user'])
@php
    use App\NotificationCategory;

    /*
     * The bell opens the latest notifications right in the header, so checking them never
     * leaves the page. Each one is labelled with its kind, and tabs narrow the list to one kind.
     * Opening one marks it read and goes to what it's about.
     */
    $notifications = $user->notifications()->latest()->limit(15)->get()
        ->each(fn ($notification) => $notification->category = NotificationCategory::forType($notification->type));
    $unread = $notifications->whereNull('read_at')->count();
    $unreadTotal = $unread === 15 ? $user->unreadNotifications()->count() : $unread;
    $tabs = collect(NotificationCategory::cases())
        ->filter(fn ($category) => $notifications->contains('category', $category));
@endphp
<details class="relative" data-dropdown>
    <summary class="relative flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-[8px] border border-line hover:border-navy [&::-webkit-details-marker]:hidden" aria-label="Notifications{{ $unreadTotal ? ', '.$unreadTotal.' unread' : '' }}">
        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
        @if ($unreadTotal)
            <span class="absolute -right-1.5 -top-1.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-error px-1 text-[0.65rem] font-semibold text-white" aria-hidden="true">{{ $unreadTotal > 9 ? '9+' : $unreadTotal }}</span>
        @endif
    </summary>

    <div class="fixed inset-x-3 top-[4.25rem] z-50 overflow-hidden rounded-[14px] border border-line bg-white shadow-xl shadow-navy/10 sm:absolute sm:inset-x-auto sm:right-0 sm:top-auto sm:mt-2 sm:w-[26rem]" data-notification-panel>
        <div class="flex items-center justify-between gap-3 px-4 pb-2 pt-3">
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

        @if ($tabs->count() > 1)
            <div class="flex gap-1.5 overflow-x-auto border-b border-line px-4 pb-3" role="group" aria-label="Show notifications about">
                <button type="button" data-notification-filter="all" aria-pressed="true" class="shrink-0 rounded-full border border-line px-3 py-1 text-xs font-semibold text-navy/75 aria-pressed:border-navy aria-pressed:bg-navy aria-pressed:text-white">All</button>
                @foreach ($tabs as $category)
                    @php
                        $newInTab = $notifications->where('category', $category)->whereNull('read_at')->count();
                    @endphp
                    <button type="button" data-notification-filter="{{ $category->value }}" aria-pressed="false" class="inline-flex shrink-0 items-center gap-1.5 rounded-full border border-line px-3 py-1 text-xs font-semibold text-navy/75 hover:border-navy/40 aria-pressed:border-navy aria-pressed:bg-navy aria-pressed:text-white">
                        {{ $category->label() }}
                        @if ($newInTab)
                            <span class="h-1.5 w-1.5 rounded-full bg-info" aria-hidden="true"></span>
                            <span class="sr-only">({{ $newInTab }} new)</span>
                        @endif
                    </button>
                @endforeach
            </div>
        @else
            <div class="border-b border-line"></div>
        @endif

        <ul class="max-h-[min(28rem,65dvh)] divide-y divide-line overflow-y-auto overscroll-contain">
            @forelse ($notifications as $notification)
                @php
                    $category = $notification->category;
                    // Older announcement notifications repeated the kind in the title; the tag says it now.
                    $title = str($notification->data['title'] ?? 'Update')->replaceStart('Announcement: ', '')->toString();
                @endphp
                <li data-category="{{ $category->value }}">
                    <a href="{{ route('notifications.show', $notification->id) }}" @class(['flex items-start gap-3 px-4 py-3 hover:bg-mist', 'bg-info/5' => $notification->unread()])>
                        <span class="relative mt-0.5 shrink-0">
                            <span class="flex h-9 w-9 items-center justify-center rounded-[10px] {{ $category->tone() }}" aria-hidden="true">
                                <svg viewBox="0 0 24 24" class="h-[1.1rem] w-[1.1rem]" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">{!! $category->icon() !!}</svg>
                            </span>
                            @if ($notification->unread())
                                <span class="absolute -right-0.5 -top-0.5 h-2.5 w-2.5 rounded-full border-2 border-white bg-info" aria-hidden="true"></span>
                            @endif
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="flex items-center justify-between gap-2 text-xs">
                                <span class="font-semibold text-slate-muted">{{ $category->tag() }}</span>
                                <span class="shrink-0 text-slate-muted">{{ $notification->created_at->diffForHumans(short: true) }}</span>
                            </span>
                            <span @class(['mt-0.5 block text-sm leading-snug', 'font-semibold' => $notification->unread(), 'font-medium text-navy/80' => $notification->read()])>
                                {{ $title }}<span class="sr-only">{{ $notification->unread() ? ' (unread)' : '' }}</span>
                            </span>
                            @if (! empty($notification->data['body']))
                                <span class="mt-0.5 line-clamp-2 block text-sm text-navy/65">{{ $notification->data['body'] }}</span>
                            @endif
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
