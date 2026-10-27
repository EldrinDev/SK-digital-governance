<?php

namespace App\Http\Controllers\Federation;

use App\DocumentExporter;
use App\ExportDocument;
use App\ExportFormat;
use App\Http\Controllers\Controller;
use App\Models\AccomplishmentReport;
use App\Models\AuditLog;
use App\Notifications\ReportReviewed;
use App\ReportStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class AccomplishmentReportController extends Controller
{
    /**
     * List submitted reports, waiting ones first.
     */
    public function index(): View
    {
        return view('federation.reports.index', [
            'reports' => AccomplishmentReport::query()
                ->with(['barangay:id,name,logo_path', 'abyip:id,year', 'submitter:id,name'])
                ->orderByRaw('case when status = ? then 0 else 1 end', [ReportStatus::Submitted->value])
                ->latest('submitted_at')
                ->paginate(20),
        ]);
    }

    /**
     * Show a submitted report.
     */
    public function show(AccomplishmentReport $report): View
    {
        Gate::authorize('view', $report);

        $report->load(['barangay:id,name,logo_path', 'submitter:id,name', 'reviewer:id,name']);

        return view('federation.reports.show', ['report' => $report]);
    }

    /**
     * Download the report, as it was when submitted, as Excel, PDF, Word or CSV.
     */
    public function export(Request $request, AccomplishmentReport $report): Response
    {
        Gate::authorize('view', $report);

        $format = ExportFormat::from($request->validate([
            'format' => ['nullable', Rule::enum(ExportFormat::class)],
        ])['format'] ?? ExportFormat::Pdf->value);

        $report->load(['barangay:id,name', 'submitter:id,name', 'reviewer:id,name']);
        $snapshot = $report->snapshot;
        $totals = $snapshot['totals'];
        $peso = fn (float $amount): string => '₱'.number_format($amount, 2);

        $document = ExportDocument::make("ABYIP {$snapshot['year']} accomplishment report", "SK {$report->barangay->name}. Figures are as of submission.")
            ->detail('Barangay', $report->barangay->name)
            ->detail('Resolution', $snapshot['resolution'] ?? null)
            ->detail('ABYIP approved on', isset($snapshot['approved_on']) ? Carbon::parse($snapshot['approved_on'])->format('F j, Y') : null)
            ->detail('Submitted', $report->submitted_at->format('F j, Y').' by '.($report->submitter?->name ?? 'an officer'))
            ->detail('Status', $report->status->label())
            ->detail('Reviewed', $report->reviewed_at ? $report->reviewed_at->format('F j, Y').' by '.($report->reviewer?->name ?? 'the federation') : null)
            ->detail('Review note', $report->review_note)
            ->table('Programs, projects and activities', [
                'PPA' => 'text',
                'Center of participation' => 'text',
                'Target' => 'text',
                'Schedule' => 'text',
                'Budget' => 'money',
                'Milestones' => 'text',
                'Evidence' => 'text',
                'Status' => 'text',
            ], collect($snapshot['ppas'])->map(fn (array $ppa): array => [
                $ppa['title'],
                $ppa['center'],
                $ppa['target'],
                Carbon::parse($ppa['starts_on'])->format('M j').' to '.Carbon::parse($ppa['ends_on'])->format('M j, Y'),
                $ppa['budget'],
                "{$ppa['milestones_done']} of {$ppa['milestones_total']}",
                $ppa['missing_evidence'] ? 'Missing' : (string) $ppa['evidence'],
                $ppa['status'].($ppa['delayed'] ? ', delayed' : ''),
            ]), summary: [
                'PPAs' => (string) $totals['ppas'],
                'Completed' => (string) $totals['completed'],
                'Ongoing' => (string) $totals['ongoing'],
                'Planned' => (string) $totals['planned'],
                'Delayed' => (string) $totals['delayed'],
                'Budget' => $peso($totals['budget']),
                'Budget of completed PPAs' => $peso($totals['budget_completed']),
            ], empty: 'No PPAs in this plan.');

        return DocumentExporter::download($document, $format, 'accomplishment-report-'.str($report->barangay->name)->slug()."-{$snapshot['year']}");
    }

    /**
     * Mark the report reviewed. An optional note is also sent to the barangay as a monitoring remark.
     */
    public function update(Request $request, AccomplishmentReport $report): RedirectResponse
    {
        Gate::authorize('review', $report);

        $note = $request->validate(['review_note' => ['nullable', 'string', 'max:2000']])['review_note'] ?? null;

        $report->forceFill([
            'status' => ReportStatus::Reviewed,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'review_note' => $note,
        ])->save();

        if ($note) {
            $remark = $report->barangay->remarks()->make(['body' => "On the ABYIP {$report->snapshot['year']} accomplishment report: {$note}"]);
            $remark->author()->associate($request->user());
            $remark->save();
        }

        AuditLog::record('report.reviewed', $request->user(), ['year' => $report->snapshot['year']], $report);

        Notification::send($report->barangay->activeOfficers(), new ReportReviewed($report));

        return redirect()->route('federation.reports.index')->with('status', "{$report->barangay->name}’s report was marked reviewed.");
    }
}
