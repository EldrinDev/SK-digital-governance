@php
    use App\ReportStatus;
@endphp
<x-layouts.app :title="$report->barangay->name.' report'" :heading="$report->barangay->name.' · ABYIP '.$report->snapshot['year']" :description="'Submitted '.$report->submitted_at->format('F j, Y').' by '.($report->submitter?->name ?? 'an officer').'. Figures are frozen as of submission.'">
    <x-slot:actions>
        <a href="{{ route('reports.print', $report) }}" target="_blank" class="rounded-[8px] border border-line bg-white px-4 py-2.5 text-sm font-semibold hover:border-navy">Print or save as PDF</a>
    </x-slot:actions>

    <p class="mb-6 text-sm"><a href="{{ route('federation.reports.index') }}" class="font-medium text-slate-muted hover:text-emerald">&larr; All reports</a></p>

    @include('reports._body', ['report' => $report->snapshot])

    <section class="mt-6 max-w-2xl rounded-[12px] border border-line bg-white p-5" aria-labelledby="review-heading">
        <h2 id="review-heading" class="font-display text-lg font-semibold">Review</h2>
        @if ($report->status === ReportStatus::Reviewed)
            <p class="mt-2 text-sm">Reviewed {{ $report->reviewed_at->format('M j, Y') }} by {{ $report->reviewer?->name ?? '—' }}.</p>
            @if ($report->review_note)
                <p class="mt-2 text-sm text-navy/80">“{{ $report->review_note }}”</p>
            @endif
        @else
            <form method="POST" action="{{ route('federation.reports.update', $report) }}" class="mt-3 space-y-3">
                @csrf
                @method('PATCH')
                <label for="review_note" class="block text-sm font-medium">Note for the barangay <span class="font-normal text-slate-muted">(optional, sent as a monitoring remark)</span></label>
                <textarea id="review_note" name="review_note" rows="3" class="block w-full rounded-[8px] border border-line px-3.5 py-2.5 text-sm outline-none focus:border-emerald focus:ring-3 focus:ring-emerald/20">{{ old('review_note') }}</textarea>
                <x-form.submit busy="Saving…" tone="navy" :block="false">Mark reviewed</x-form.submit>
            </form>
        @endif
    </section>
</x-layouts.app>
