<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $report['barangay'] }} ABYIP {{ $report['year'] }} accomplishment report</title>
        @fonts
        @vite(['resources/css/app.css'])
        <style>
            @page { size: A4 landscape; margin: 14mm; }
        </style>
    </head>
    <body class="bg-white font-sans text-navy antialiased print:text-[11px]">
        <div class="mx-auto max-w-6xl p-6 print:p-0">
            <div class="mb-6 flex items-center justify-between gap-4 print:hidden">
                <p class="text-sm text-slate-muted">Use your browser’s print dialog and choose “Save as PDF”.</p>
                <button type="button" onclick="window.print()" class="rounded-[8px] bg-navy px-4 py-2 text-sm font-semibold text-white">Print</button>
            </div>

            <header class="flex items-start justify-between gap-6 border-b-2 border-navy pb-4">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/sk-logo.svg') }}" alt="" width="48" height="48" class="h-12 w-12">
                    <div>
                        <p class="text-xs text-slate-muted">Sangguniang Kabataan of Barangay {{ $report['barangay'] }}, Olongapo City</p>
                        <h1 class="font-display text-2xl font-semibold">Accomplishment report · ABYIP {{ $report['year'] }}</h1>
                        <p class="text-sm text-slate-muted">
                            @if ($report['resolution'])
                                Approved by {{ $report['resolution'] }}@if ($report['approved_on']), {{ \Illuminate\Support\Carbon::parse($report['approved_on'])->format('F j, Y') }}@endif
                            @endif
                        </p>
                    </div>
                </div>
                <div class="text-right text-xs text-slate-muted">
                    @if ($isDraft)
                        <p class="font-semibold uppercase text-[#b45309]">Draft — not submitted</p>
                    @else
                        <p>Submitted {{ $submission->submitted_at->format('F j, Y g:i A') }}</p>
                        <p>by {{ $submission->submitter?->name ?? '—' }}</p>
                        <p>{{ $submission->status->label() }}</p>
                    @endif
                    <p class="mt-1">Figures as of {{ \Illuminate\Support\Carbon::parse($report['compiled_at'])->format('F j, Y g:i A') }}</p>
                </div>
            </header>

            <div class="mt-6">
                @include('reports._body', ['report' => $report])
            </div>

            <p class="mt-6 text-xs text-slate-muted">
                Compiled by the SK Digital Governance Platform from recorded PPAs, milestones, and evidence. This report supports, and does not replace, the official reports required by the DILG and NYC.
            </p>
        </div>
    </body>
</html>
