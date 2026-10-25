@php
    use App\ReportStatus;
@endphp
<x-layouts.app title="Reports" heading="Accomplishment reports" description="Reports submitted by barangay SK officers, compiled from their records. Waiting reports are listed first.">
    <div class="overflow-x-auto rounded-[12px] border border-line bg-white">
        <table class="w-full min-w-[40rem] text-left text-sm">
            <thead class="border-b border-line bg-mist/60 text-xs font-semibold text-slate-muted">
                <tr>
                    <th scope="col" class="px-5 py-3">Barangay</th>
                    <th scope="col" class="px-5 py-3">ABYIP</th>
                    <th scope="col" class="px-5 py-3">Submitted</th>
                    <th scope="col" class="px-5 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($reports as $report)
                    <tr>
                        <td class="px-5 py-3"><a href="{{ route('federation.reports.show', $report) }}" class="font-medium hover:text-emerald">{{ $report->barangay->name }}</a></td>
                        <td class="px-5 py-3">{{ $report->abyip->year }}</td>
                        <td class="px-5 py-3">{{ $report->submitted_at->format('M j, Y') }} <span class="text-xs text-slate-muted">by {{ $report->submitter?->name ?? '—' }}</span></td>
                        <td class="px-5 py-3"><x-ui.badge :tone="$report->status === ReportStatus::Submitted ? 'warning' : 'success'">{{ $report->status === ReportStatus::Submitted ? 'Waiting for review' : 'Reviewed' }}</x-ui.badge></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-10 text-center text-slate-muted">No reports submitted yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $reports->links() }}</div>
</x-layouts.app>
