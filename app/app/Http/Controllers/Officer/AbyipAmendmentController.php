<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Models\Abyip;
use App\Models\AbyipAmendment;
use App\Models\AuditLog;
use App\PlanStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AbyipAmendmentController extends Controller
{
    /**
     * Open an amendment, which unlocks the ABYIP's PPAs for editing.
     */
    public function store(Request $request, Abyip $abyip): RedirectResponse
    {
        Gate::authorize('amend', $abyip);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ], [
            'reason.required' => 'Say why the ABYIP needs to change.',
        ]);

        $amendment = new AbyipAmendment($validated);
        $amendment->forceFill(['barangay_id' => $abyip->barangay_id, 'opened_by' => $request->user()->id]);
        $abyip->amendments()->save($amendment);

        AuditLog::record('abyip.amendment_opened', $request->user(), ['year' => $abyip->year, 'reason' => $amendment->reason], $abyip);

        return redirect()->route('officer.abyips.show', $abyip)->with('status', 'Amendment started. You can now change the PPAs. Record the council’s resolution to lock them again.');
    }

    /**
     * Record the council's approval of the amendment and lock the ABYIP again.
     */
    public function approve(Request $request, Abyip $abyip): RedirectResponse
    {
        Gate::authorize('approveAmendment', $abyip);

        $validated = $request->validate([
            'resolution_number' => ['required', 'string', 'max:100'],
            'approved_on' => ['required', 'date', 'before_or_equal:today'],
            'resolution_file' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ], [
            'resolution_number.required' => 'Enter the SK resolution number.',
            'approved_on.required' => 'Enter the date the council approved it.',
            'approved_on.before_or_equal' => 'The approval date can’t be in the future.',
            'resolution_file.required' => 'Attach a scanned copy of the signed resolution.',
            'resolution_file.mimes' => 'Attach the resolution as a PDF.',
        ]);

        $amendment = $abyip->openAmendment;
        $amendment->forceFill([
            'status' => PlanStatus::Approved,
            'resolution_number' => $validated['resolution_number'],
            'approved_on' => $validated['approved_on'],
            'resolution_path' => $request->file('resolution_file')->store("resolutions/{$abyip->barangay_id}"),
            'approval_recorded_by' => $request->user()->id,
        ])->save();

        AuditLog::record('abyip.amendment_approved', $request->user(), ['year' => $abyip->year, 'resolution' => $amendment->resolution_number], $abyip);

        return redirect()->route('officer.abyips.show', $abyip)->with('status', "Amendment approved. ABYIP {$abyip->year} is locked again.");
    }

    /**
     * Download the amendment's signed resolution.
     */
    public function resolution(AbyipAmendment $amendment): StreamedResponse
    {
        Gate::authorize('view', $amendment->abyip);
        abort_if($amendment->resolution_path === null, 404);

        return Storage::download($amendment->resolution_path, "ABYIP amendment resolution {$amendment->id}.pdf");
    }
}
