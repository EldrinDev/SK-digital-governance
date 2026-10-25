<?php

namespace App\Http\Controllers\Federation;

use App\Http\Controllers\Controller;
use App\Models\AccomplishmentReport;
use App\Models\AuditLog;
use App\ReportStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AccomplishmentReportController extends Controller
{
    /**
     * List submitted reports, waiting ones first.
     */
    public function index(): View
    {
        return view('federation.reports.index', [
            'reports' => AccomplishmentReport::query()
                ->with(['barangay:id,name', 'abyip:id,year', 'submitter:id,name'])
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

        $report->load(['barangay:id,name', 'submitter:id,name', 'reviewer:id,name']);

        return view('federation.reports.show', ['report' => $report]);
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

        return redirect()->route('federation.reports.index')->with('status', "{$report->barangay->name}’s report was marked reviewed.");
    }
}
