<x-layouts.app title="Meetings" heading="Meetings" description="Sessions of your SK council, with attendance, minutes, and the resolutions adopted.">
    <x-slot:actions>
        <a href="{{ route('officer.meetings.create') }}" class="inline-flex items-center gap-2 rounded-[8px] bg-emerald px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-dark">
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Record a session
        </a>
    </x-slot:actions>

    <div class="overflow-x-auto rounded-[12px] border border-line bg-white">
        <table class="w-full min-w-[42rem] text-left text-sm">
            <thead class="border-b border-line bg-mist/60 text-xs font-semibold text-slate-muted">
                <tr>
                    <th scope="col" class="px-5 py-3">Session</th>
                    <th scope="col" class="px-5 py-3">Date</th>
                    <th scope="col" class="px-5 py-3">Quorum</th>
                    <th scope="col" class="px-5 py-3">Minutes</th>
                    <th scope="col" class="px-5 py-3 text-right">Resolutions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($meetings as $meeting)
                    <tr>
                        <td class="px-5 py-3">
                            <a href="{{ route('officer.meetings.show', $meeting) }}" class="font-medium hover:text-emerald">{{ $meeting->title }}</a>
                            <p class="text-xs text-slate-muted">{{ $meeting->kind->label() }}</p>
                        </td>
                        <td class="whitespace-nowrap px-5 py-3">{{ $meeting->held_on->format('M j, Y') }}</td>
                        <td class="px-5 py-3">
                            @php($quorum = $meeting->hasQuorum())
                            @if ($quorum === true)
                                <x-ui.badge tone="success">{{ $meeting->presentCount() }} of {{ $meeting->attendees->count() }} present</x-ui.badge>
                            @elseif ($quorum === false)
                                <x-ui.badge tone="error">No quorum</x-ui.badge>
                            @else
                                <span class="text-xs text-slate-muted">Attendance not recorded</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            @if ($meeting->minutes_path)
                                <x-ui.badge tone="info">Uploaded</x-ui.badge>
                            @else
                                <span class="text-xs text-slate-muted">Missing</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right tabular-nums">{{ $meeting->resolutions_count }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-slate-muted">No sessions recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $meetings->links() }}</div>
</x-layouts.app>
