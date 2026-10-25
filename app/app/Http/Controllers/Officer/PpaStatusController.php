<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Ppa;
use App\PpaStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class PpaStatusController extends Controller
{
    /**
     * Record a change in the PPA's implementation status.
     */
    public function update(Request $request, Ppa $ppa): RedirectResponse
    {
        Gate::authorize('track', $ppa);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(PpaStatus::class)],
        ]);

        $previous = $ppa->status;
        $ppa->status = PpaStatus::from($validated['status']);
        $ppa->save();

        if ($previous !== $ppa->status) {
            AuditLog::record('ppa.status_changed', $request->user(), [
                'from' => $previous->label(),
                'to' => $ppa->status->label(),
            ], $ppa);
        }

        return redirect()->route('officer.ppas.show', $ppa)->with('status', "Status set to {$ppa->status->label()}.");
    }
}
