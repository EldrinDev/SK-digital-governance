<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Models\Abyip;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AbyipPublicationController extends Controller
{
    /**
     * Put the approved ABYIP on the public transparency board.
     */
    public function store(Request $request, Abyip $abyip): RedirectResponse
    {
        Gate::authorize('publish', $abyip);

        $abyip->forceFill(['published_at' => now()])->save();

        AuditLog::record('abyip.published', $request->user(), ['year' => $abyip->year], $abyip);

        return redirect()->route('officer.abyips.show', $abyip)
            ->with('status', "ABYIP {$abyip->year} is now on the public transparency board.");
    }

    /**
     * Take the ABYIP off the public transparency board.
     */
    public function destroy(Request $request, Abyip $abyip): RedirectResponse
    {
        Gate::authorize('publish', $abyip);

        $abyip->forceFill(['published_at' => null])->save();

        AuditLog::record('abyip.unpublished', $request->user(), ['year' => $abyip->year], $abyip);

        return redirect()->route('officer.abyips.show', $abyip)
            ->with('status', "ABYIP {$abyip->year} was taken off the public board.");
    }
}
