<?php

namespace App\Http\Controllers\Officer;

use App\CenterOfParticipation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Officer\StoreCbydpRequest;
use App\Models\AuditLog;
use App\Models\Cbydp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CbydpController extends Controller
{
    /**
     * Show the form for starting a new three-year plan.
     */
    public function create(): View
    {
        return view('officer.cbydps.create', [
            'suggestedStartYear' => (int) now()->format('Y'),
        ]);
    }

    /**
     * Start a draft CBYDP covering three years.
     */
    public function store(StoreCbydpRequest $request): RedirectResponse
    {
        $start = $request->integer('start_year');

        $cbydp = $request->user()->barangay->cbydps()->create([
            'start_year' => $start,
            'end_year' => $start + 2,
        ]);

        AuditLog::record('cbydp.created', $request->user(), ['period' => $cbydp->period()], $cbydp);

        return redirect()->route('officer.cbydps.show', $cbydp)
            ->with('status', "CBYDP {$cbydp->period()} started. Add the youth priorities next.");
    }

    /**
     * Show the plan with its priorities.
     */
    public function show(Cbydp $cbydp): View
    {
        Gate::authorize('view', $cbydp);

        $cbydp->load(['priorities' => fn ($query) => $query->orderBy('center')->orderBy('title'), 'approvalRecorder:id,name', 'abyips']);

        return view('officer.cbydps.show', [
            'cbydp' => $cbydp,
            'centers' => CenterOfParticipation::cases(),
        ]);
    }
}
