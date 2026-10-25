{{--
    The body of an accomplishment report, from compiled figures. Expects $report
    (the array from AccomplishmentReport::compile, or a saved snapshot).
--}}
@php
    $totals = $report['totals'];
    $peso = fn (float $amount): string => '₱'.number_format($amount, 2);
@endphp
<dl class="grid grid-cols-2 gap-3 sm:grid-cols-4 print:grid-cols-4">
    <div class="rounded-[12px] border border-line bg-white p-4"><dt class="text-xs text-slate-muted">PPAs</dt><dd class="mt-1 font-display text-2xl font-semibold">{{ $totals['ppas'] }}</dd></div>
    <div class="rounded-[12px] border border-line bg-white p-4"><dt class="text-xs text-slate-muted">Completed</dt><dd class="mt-1 font-display text-2xl font-semibold">{{ $totals['completed'] }}</dd></div>
    <div class="rounded-[12px] border border-line bg-white p-4"><dt class="text-xs text-slate-muted">Ongoing · Planned · Delayed</dt><dd class="mt-1 font-display text-2xl font-semibold">{{ $totals['ongoing'] }} · {{ $totals['planned'] }} · {{ $totals['delayed'] }}</dd></div>
    <div class="rounded-[12px] border border-line bg-white p-4"><dt class="text-xs text-slate-muted">Budget (completed / total)</dt><dd class="mt-1 text-sm font-semibold">{{ $peso($totals['budget_completed']) }} / {{ $peso($totals['budget']) }}</dd></div>
</dl>

<div class="mt-6 overflow-x-auto rounded-[12px] border border-line bg-white">
    <table class="w-full min-w-[56rem] text-left text-sm print:min-w-0 print:text-xs">
        <thead class="border-b border-line bg-mist/60 text-xs font-semibold text-slate-muted">
            <tr>
                <th scope="col" class="px-4 py-3">PPA</th>
                <th scope="col" class="px-4 py-3">Target</th>
                <th scope="col" class="px-4 py-3">Schedule</th>
                <th scope="col" class="px-4 py-3 text-right">Budget</th>
                <th scope="col" class="px-4 py-3">Milestones</th>
                <th scope="col" class="px-4 py-3 text-right">Evidence</th>
                <th scope="col" class="px-4 py-3">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-line">
            @foreach ($report['ppas'] as $ppa)
                <tr class="align-top">
                    <td class="px-4 py-3"><p class="font-medium">{{ $ppa['title'] }}</p><p class="text-xs text-slate-muted">{{ $ppa['center'] }} · {{ $ppa['priority'] }}</p></td>
                    <td class="px-4 py-3">{{ $ppa['target'] }}</td>
                    <td class="whitespace-nowrap px-4 py-3">{{ \Illuminate\Support\Carbon::parse($ppa['starts_on'])->format('M j') }} – {{ \Illuminate\Support\Carbon::parse($ppa['ends_on'])->format('M j') }}</td>
                    <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums">{{ $peso($ppa['budget']) }}</td>
                    <td class="px-4 py-3">{{ $ppa['milestones_done'] }} of {{ $ppa['milestones_total'] }}</td>
                    <td class="px-4 py-3 text-right">
                        @if ($ppa['missing_evidence'])
                            <span class="inline-flex items-center gap-1 font-semibold text-[#b45309]">
                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 2 21h20L12 3Z"/><path d="M12 10v5M12 18h.01"/></svg>
                                Missing
                            </span>
                        @else
                            {{ $ppa['evidence'] }}
                        @endif
                    </td>
                    <td class="px-4 py-3">{{ $ppa['status'] }}@if ($ppa['delayed']) <span class="font-semibold text-[#b91c1c]">· Delayed</span>@endif</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
