<?php

namespace App\Http\Controllers\Federation;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Barangay;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MonitoringRemarkController extends Controller
{
    /**
     * Leave a monitoring remark for the barangay SK. The barangay's records stay untouched.
     */
    public function store(Request $request, Barangay $barangay): RedirectResponse
    {
        abort_unless($barangay->is_participating, 404);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ], [
            'body.required' => 'Write the remark before saving.',
        ]);

        $remark = $barangay->remarks()->make($validated);
        $remark->author()->associate($request->user());
        $remark->save();

        AuditLog::record('remark.created', $request->user(), [], $barangay);

        return redirect()->route('federation.barangays.show', $barangay)
            ->with('status', "Remark sent to {$barangay->name}’s SK officers.");
    }
}
