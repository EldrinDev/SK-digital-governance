<x-layouts.app title="Resolutions" heading="Register of resolutions" description="Every resolution of your SK council, with its outcome and signed copy. Add new ones from the session that adopted them.">
    <form method="GET" class="mb-5 flex max-w-xl gap-2">
        <label for="search" class="sr-only">Search resolutions</label>
        <input id="search" name="search" type="search" value="{{ $search }}" placeholder="Search by number or subject" class="flex-1 rounded-[8px] border border-line bg-white px-3 py-2 text-sm outline-none focus:border-emerald focus:ring-3 focus:ring-emerald/20">
        <button type="submit" class="rounded-[8px] bg-navy px-4 py-2 text-sm font-semibold text-white hover:bg-navy-deep">Search</button>
    </form>

    <div class="overflow-x-auto rounded-[12px] border border-line bg-white">
        <table class="w-full min-w-[42rem] text-left text-sm">
            <thead class="border-b border-line bg-mist/60 text-xs font-semibold text-slate-muted">
                <tr>
                    <th scope="col" class="px-5 py-3">Number</th>
                    <th scope="col" class="px-5 py-3">Subject</th>
                    <th scope="col" class="px-5 py-3">Session</th>
                    <th scope="col" class="px-5 py-3">Outcome</th>
                    <th scope="col" class="px-5 py-3"><span class="sr-only">Signed copy</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($resolutions as $resolution)
                    <tr class="align-top">
                        <td class="whitespace-nowrap px-5 py-3 font-medium">{{ $resolution->number }}</td>
                        <td class="px-5 py-3">{{ $resolution->title }}<p class="text-xs text-slate-muted">{{ $resolution->adopted_on->format('M j, Y') }}</p></td>
                        <td class="px-5 py-3">
                            @if ($resolution->meeting)
                                <a href="{{ route('officer.meetings.show', $resolution->meeting) }}" class="hover:text-emerald">{{ $resolution->meeting->title }}</a>
                            @else
                                <span class="text-slate-muted">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3"><x-ui.badge :tone="$resolution->outcome->tone()">{{ $resolution->outcome->label() }}</x-ui.badge></td>
                        <td class="px-5 py-3 text-right">
                            @if ($resolution->file_path)
                                <a href="{{ route('officer.resolutions.show', $resolution) }}" class="text-xs font-semibold text-emerald hover:underline">PDF<span class="sr-only"> of {{ $resolution->number }}</span></a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-slate-muted">
                            {{ $search ? 'No resolutions match your search.' : 'No resolutions yet. Record a session and add the resolutions it adopted.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $resolutions->links() }}</div>
</x-layouts.app>
