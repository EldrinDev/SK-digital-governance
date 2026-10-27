<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Officer\StoreCbydpPriorityRequest;
use App\Models\AuditLog;
use App\Models\Cbydp;
use App\Models\CbydpPriority;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CbydpPriorityController extends Controller
{
    /**
     * Add a youth priority to a draft CBYDP.
     */
    public function store(StoreCbydpPriorityRequest $request, Cbydp $cbydp): RedirectResponse
    {
        $priority = $cbydp->priorities()->create($request->validated());

        AuditLog::record('cbydp.updated', $request->user(), ['added_priority' => $priority->title], $cbydp);

        return redirect()->route('officer.cbydps.show', $cbydp)->with('status', 'Priority added.');
    }

    /**
     * Remove a priority from a draft CBYDP.
     */
    public function destroy(Request $request, Cbydp $cbydp, CbydpPriority $priority): RedirectResponse
    {
        Gate::authorize('update', $cbydp);

        $priority->delete();

        AuditLog::record('cbydp.updated', $request->user(), ['removed_priority' => $priority->title], $cbydp);

        return redirect()->route('officer.cbydps.show', $cbydp)->toast('Priority removed.', 'warning');
    }
}
