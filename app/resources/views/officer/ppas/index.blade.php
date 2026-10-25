@php
    use App\PpaStatus;
@endphp
<x-layouts.app title="PPAs" heading="PPAs" description="Every program, project, and activity in your barangay’s ABYIPs.">
    <form method="GET" class="mb-5 grid gap-3 rounded-[12px] border border-line bg-white p-4 sm:grid-cols-[minmax(0,1.5fr)_repeat(2,minmax(0,1fr))_auto]">
        <label class="sr-only" for="search">Search PPAs</label>
        <input id="search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search by name" class="rounded-[8px] border border-line px-3 py-2 text-sm outline-none focus:border-emerald focus:ring-3 focus:ring-emerald/20">

        <label class="sr-only" for="year">ABYIP year</label>
        <select id="year" name="year" class="rounded-[8px] border border-line bg-white px-3 py-2 text-sm">
            <option value="">All years</option>
            @foreach ($years as $year)
                <option value="{{ $year }}" @selected((int) ($filters['year'] ?? 0) === $year)>{{ $year }}</option>
            @endforeach
        </select>

        <label class="sr-only" for="status">Status</label>
        <select id="status" name="status" class="rounded-[8px] border border-line bg-white px-3 py-2 text-sm">
            <option value="">Any status</option>
            <option value="delayed" @selected(($filters['status'] ?? null) === 'delayed')>Delayed</option>
            @foreach (PpaStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(($filters['status'] ?? null) === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>

        <div class="flex gap-2">
            <button type="submit" class="rounded-[8px] bg-navy px-4 py-2 text-sm font-semibold text-white hover:bg-navy-deep">Filter</button>
            @if (array_filter($filters))
                <a href="{{ route('officer.ppas.index') }}" class="rounded-[8px] px-3 py-2 text-sm font-medium text-slate-muted hover:text-navy">Clear</a>
            @endif
        </div>
    </form>

    <div class="overflow-x-auto rounded-[12px] border border-line bg-white">
        <table class="w-full min-w-[48rem] text-left text-sm">
            <thead class="border-b border-line bg-mist/60 text-xs font-semibold text-slate-muted">
                <tr>
                    <th scope="col" class="px-5 py-3">PPA</th>
                    <th scope="col" class="px-5 py-3">ABYIP</th>
                    <th scope="col" class="px-5 py-3">Schedule</th>
                    <th scope="col" class="px-5 py-3">Progress</th>
                    <th scope="col" class="px-5 py-3 text-right">Budget</th>
                    <th scope="col" class="px-5 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($ppas as $ppa)
                    <tr class="align-top">
                        <td class="px-5 py-3">
                            <a href="{{ route('officer.ppas.show', $ppa) }}" class="font-medium hover:text-emerald">{{ $ppa->title }}</a>
                            <p class="text-xs text-slate-muted">{{ $ppa->priority->center->label() }}</p>
                        </td>
                        <td class="px-5 py-3">{{ $ppa->abyip->year }}</td>
                        <td class="whitespace-nowrap px-5 py-3 text-navy/80">{{ $ppa->starts_on->format('M j') }} – {{ $ppa->ends_on->format('M j') }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <div class="h-2 w-20 overflow-hidden rounded-full bg-mist" aria-hidden="true">
                                    <div class="h-full rounded-full bg-emerald" style="width: {{ $ppa->progress() }}%"></div>
                                </div>
                                <span class="text-xs text-slate-muted">{{ $ppa->progress() }}%</span>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-5 py-3 text-right tabular-nums">₱{{ number_format($ppa->budget, 2) }}</td>
                        <td class="px-5 py-3"><x-ui.ppa-status :ppa="$ppa" /></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-slate-muted">
                            @if (array_filter($filters))
                                No PPAs match these filters.
                            @else
                                No PPAs yet. Add them from an ABYIP on the <a href="{{ route('officer.plans.index') }}" class="font-semibold text-emerald hover:underline">Plans</a> page.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $ppas->links() }}</div>
</x-layouts.app>
