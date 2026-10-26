<x-layouts.app title="Notifications" heading="Notifications" description="Updates sent to you inside the platform. Most are also emailed.">
    @if (auth()->user()->unreadNotifications()->exists())
        <x-slot:actions>
            <form method="POST" action="{{ route('notifications.read') }}">
                @csrf
                <button type="submit" class="rounded-[8px] border border-line bg-white px-4 py-2.5 text-sm font-semibold hover:border-navy">Mark all as read</button>
            </form>
        </x-slot:actions>
    @endif

    <ul class="max-w-3xl divide-y divide-line overflow-hidden rounded-[12px] border border-line bg-white">
        @forelse ($notifications as $notification)
            <li>
                <a href="{{ route('notifications.show', $notification->id) }}" @class(['flex items-start gap-3 px-5 py-4 hover:bg-mist', 'bg-info/5' => $notification->unread()])>
                    <span @class(['mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full', 'bg-info' => $notification->unread(), 'bg-transparent' => $notification->read()])>
                        <span class="sr-only">{{ $notification->unread() ? 'Unread' : 'Read' }}</span>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span @class(['block text-sm', 'font-semibold' => $notification->unread(), 'font-medium' => $notification->read()])>{{ $notification->data['title'] ?? 'Update' }}</span>
                        <span class="mt-0.5 block text-sm text-navy/75">{{ $notification->data['body'] ?? '' }}</span>
                        <span class="mt-1 block text-xs text-slate-muted">{{ $notification->created_at->diffForHumans() }}</span>
                    </span>
                </a>
            </li>
        @empty
            <li class="px-5 py-10 text-center text-sm text-slate-muted">No notifications yet.</li>
        @endforelse
    </ul>

    <div class="mt-4 max-w-3xl">{{ $notifications->links() }}</div>
</x-layouts.app>
