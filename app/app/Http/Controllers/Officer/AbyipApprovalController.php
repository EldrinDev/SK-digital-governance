<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Officer\RecordApprovalRequest;
use App\Models\Abyip;
use App\Models\AuditLog;
use App\PlanStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AbyipApprovalController extends Controller
{
    /**
     * Record the council's approval of the ABYIP, attach the resolution, and lock it.
     * From here on its PPAs move into implementation.
     */
    public function store(RecordApprovalRequest $request, Abyip $abyip): RedirectResponse
    {
        $abyip->forceFill([
            'status' => PlanStatus::Approved,
            'resolution_number' => $request->validated('resolution_number'),
            'approved_on' => $request->validated('approved_on'),
            'resolution_path' => $request->file('resolution_file')->store("resolutions/{$abyip->barangay_id}"),
            'approval_recorded_by' => $request->user()->id,
        ])->save();

        AuditLog::record('abyip.approved', $request->user(), ['resolution' => $abyip->resolution_number], $abyip);

        return redirect()->route('officer.abyips.show', $abyip)
            ->with('status', "Approval recorded. ABYIP {$abyip->year} is locked and its PPAs are ready for implementation.");
    }

    /**
     * Download the signed resolution.
     */
    public function show(Abyip $abyip): StreamedResponse
    {
        Gate::authorize('view', $abyip);
        abort_if($abyip->resolution_path === null, 404);

        return Storage::download($abyip->resolution_path, "ABYIP {$abyip->year} resolution.pdf");
    }
}
