<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Models\Abyip;
use App\Models\AccomplishmentReport;
use App\Models\AuditLog;
use App\Models\User;
use App\Notifications\ReportSubmitted;
use App\ReportStatus;
use App\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class AccomplishmentReportController extends Controller
{
    /**
     * Show the report draft compiled from the ABYIP's records, and earlier submissions.
     */
    public function show(Abyip $abyip): View
    {
        Gate::authorize('view', $abyip);

        return view('officer.reports.show', [
            'abyip' => $abyip,
            'report' => AccomplishmentReport::compile($abyip),
            'submissions' => $abyip->reports()->with(['submitter:id,name', 'reviewer:id,name'])->get(),
        ]);
    }

    /**
     * Show the draft in a print-friendly layout, for saving as PDF.
     */
    public function print(Abyip $abyip): View
    {
        Gate::authorize('view', $abyip);

        return view('reports.print', [
            'report' => AccomplishmentReport::compile($abyip),
            'isDraft' => true,
        ]);
    }

    /**
     * Freeze the compiled figures and send the report to the SK Federation for review.
     */
    public function store(Request $request, Abyip $abyip): RedirectResponse
    {
        Gate::authorize('report', $abyip);

        $snapshot = AccomplishmentReport::compile($abyip);

        if ($snapshot['missing_evidence'] > 0) {
            return back()->withErrors([
                'report' => trans_choice(
                    ':count completed PPA has no evidence. Upload proof before submitting.|:count completed PPAs have no evidence. Upload proof before submitting.',
                    $snapshot['missing_evidence'],
                ),
            ]);
        }

        $report = new AccomplishmentReport;
        $report->forceFill([
            'barangay_id' => $abyip->barangay_id,
            'status' => ReportStatus::Submitted,
            'snapshot' => $snapshot,
            'submitted_by' => $request->user()->id,
            'submitted_at' => now(),
        ]);
        $abyip->reports()->save($report);

        AuditLog::record('report.submitted', $request->user(), ['year' => $abyip->year], $report);

        Notification::send(
            User::where('role', UserRole::Federation)->whereNull('deactivated_at')->get(),
            new ReportSubmitted($report->setRelation('barangay', $abyip->barangay)),
        );

        return redirect()->route('officer.abyips.report', $abyip)
            ->with('status', 'Report submitted to the SK Federation for review.');
    }
}
