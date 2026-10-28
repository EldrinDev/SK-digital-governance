<?php

namespace App\Http\Controllers;

use App\Models\AccomplishmentReport;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ReportPrintController extends Controller
{
    /**
     * Show a submitted report, as it was when submitted, in a print-friendly layout.
     */
    public function show(AccomplishmentReport $report): View
    {
        Gate::authorize('view', $report);

        return view('reports.print', [
            'report' => $report->snapshot,
            'logoUrl' => $report->barangay->logoUrl(),
            'isDraft' => false,
            'submission' => $report->load(['submitter:id,name', 'reviewer:id,name']),
        ]);
    }
}
