<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Officer\RecordApprovalRequest;
use App\Models\AuditLog;
use App\Models\Cbydp;
use App\PlanStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CbydpApprovalController extends Controller
{
    /**
     * Record the council's approval of the CBYDP, attach the resolution, and lock the plan.
     */
    public function store(RecordApprovalRequest $request, Cbydp $cbydp): RedirectResponse
    {
        $cbydp->forceFill([
            'status' => PlanStatus::Approved,
            'resolution_number' => $request->validated('resolution_number'),
            'approved_on' => $request->validated('approved_on'),
            'resolution_path' => $request->file('resolution_file')->store("resolutions/{$cbydp->barangay_id}"),
            'approval_recorded_by' => $request->user()->id,
        ])->save();

        AuditLog::record('cbydp.approved', $request->user(), ['resolution' => $cbydp->resolution_number], $cbydp);

        return redirect()->route('officer.cbydps.show', $cbydp)
            ->with('status', "Approval recorded. CBYDP {$cbydp->period()} is now locked, and you can start its ABYIPs.");
    }

    /**
     * Download the signed resolution.
     */
    public function show(Cbydp $cbydp): StreamedResponse
    {
        Gate::authorize('view', $cbydp);
        abort_if($cbydp->resolution_path === null, 404);

        return Storage::download($cbydp->resolution_path, "CBYDP {$cbydp->start_year}-{$cbydp->end_year} resolution.pdf");
    }
}
