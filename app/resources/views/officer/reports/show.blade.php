<x-layouts.app :title="'Report · ABYIP '.$abyip->year" :heading="'Accomplishment report · ABYIP '.$abyip->year" description="Compiled from your PPA records as of now. Nothing here is typed in by hand, so keep the PPAs, milestones, and evidence up to date.">
    <x-slot:actions>
        <a href="{{ route('officer.abyips.report.print', $abyip) }}" target="_blank" class="inline-flex items-center gap-2 rounded-[8px] border border-line bg-white px-4 py-2.5 text-sm font-semibold hover:border-navy">
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9V3h12v6"/><rect x="3" y="9" width="18" height="8" rx="2"/><path d="M6 14h12v7H6z"/></svg>
            Print or save as PDF
        </a>
    </x-slot:actions>

    <p class="mb-6 text-sm"><a href="{{ route('officer.abyips.show', $abyip) }}" class="font-medium text-slate-muted hover:text-emerald">&larr; ABYIP {{ $abyip->year }}</a></p>

    @if ($report['missing_evidence'] > 0)
        <div role="alert" class="mb-6 flex items-start gap-3 rounded-[12px] border border-warning/40 bg-warning/10 p-4 text-sm">
            <svg viewBox="0 0 24 24" class="mt-0.5 h-5 w-5 shrink-0 text-warning" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 2 21h20L12 3Z"/><path d="M12 10v5M12 18h.01"/></svg>
            <p>{{ trans_choice(':count completed PPA has no evidence.|:count completed PPAs have no evidence.', $report['missing_evidence']) }} Upload photos or documents on each one, marked “Missing” below, before submitting.</p>
        </div>
    @endif

    @include('reports._body', ['report' => $report])

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        @can('report', $abyip)
            <section class="rounded-[12px] border border-line bg-white p-5" aria-labelledby="submit-heading">
                <h2 id="submit-heading" class="font-display text-lg font-semibold">Submit for federation review</h2>
                <p class="mt-1 text-sm text-slate-muted">The figures are frozen as they are now. You can submit again later if things change; each submission is kept.</p>
                @error('report')
                    <p class="mt-3 text-sm text-error">{{ $message }}</p>
                @enderror
                <form method="POST" action="{{ route('officer.abyips.report.store', $abyip) }}" class="mt-4">
                    @csrf
                    <x-form.submit busy="Submitting…" tone="navy" :block="false" :disabled="$report['missing_evidence'] > 0">Submit report</x-form.submit>
                </form>
            </section>
        @else
            <section class="rounded-[12px] border border-line bg-white p-5 text-sm text-slate-muted">
                Reports can be submitted once the council approves ABYIP {{ $abyip->year }}.
            </section>
        @endcan

        <section class="rounded-[12px] border border-line bg-white p-5" aria-labelledby="history-heading">
            <h2 id="history-heading" class="font-display text-lg font-semibold">Submissions</h2>
            <ul class="mt-3 space-y-3 text-sm">
                @forelse ($submissions as $submission)
                    <li class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <p class="font-medium">{{ $submission->submitted_at->format('M j, Y g:i A') }}</p>
                            <p class="text-xs text-slate-muted">by {{ $submission->submitter?->name ?? 'Someone' }} · {{ $submission->status->label() }}</p>
                            @if ($submission->review_note)
                                <p class="mt-1 text-xs">“{{ $submission->review_note }}” — {{ $submission->reviewer?->name }}</p>
                            @endif
                        </div>
                        <a href="{{ route('reports.print', $submission) }}" target="_blank" class="text-xs font-semibold text-emerald hover:underline">View</a>
                    </li>
                @empty
                    <li class="text-slate-muted">Not submitted yet.</li>
                @endforelse
            </ul>
        </section>
    </div>
</x-layouts.app>
