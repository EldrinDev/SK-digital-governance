<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Officer\StoreAbyipRequest;
use App\Models\Abyip;
use App\Models\AuditLog;
use App\PlanStatus;
use App\PpaStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AbyipController extends Controller
{
    /**
     * Show the form for starting a year's investment program.
     */
    public function create(Request $request): View
    {
        $cbydps = $request->user()->barangay->cbydps()
            ->where('status', PlanStatus::Approved)
            ->orderByDesc('start_year')
            ->get();

        return view('officer.abyips.create', [
            'cbydps' => $cbydps,
            'selectedCbydp' => $request->integer('cbydp') ?: ($cbydps->count() === 1 ? $cbydps->first()->id : null),
            'takenYears' => $request->user()->barangay->abyips()->pluck('year')->all(),
        ]);
    }

    /**
     * Start a draft ABYIP under an approved CBYDP.
     */
    public function store(StoreAbyipRequest $request): RedirectResponse
    {
        $abyip = new Abyip(['year' => $request->integer('year')]);
        $abyip->barangay()->associate($request->user()->barangay);
        $abyip->cbydp()->associate($request->cbydp());
        $abyip->save();

        AuditLog::record('abyip.created', $request->user(), ['year' => $abyip->year], $abyip);

        return redirect()->route('officer.abyips.show', $abyip)
            ->with('status', "ABYIP {$abyip->year} started. Add its PPAs next.");
    }

    /**
     * Show the program, where it is in the approval cycle, and its PPAs.
     */
    public function show(Abyip $abyip): View
    {
        Gate::authorize('view', $abyip);

        $abyip->load([
            'cbydp',
            'approvalRecorder:id,name',
            'openAmendment.opener:id,name',
            'amendments' => fn ($query) => $query->where('status', PlanStatus::Approved)->with('approvalRecorder:id,name'),
            'ppas' => fn ($query) => $query->with(['priority', 'milestones'])->orderBy('starts_on'),
        ]);

        return view('officer.abyips.show', [
            'abyip' => $abyip,
            'totalBudget' => $abyip->ppas->where('status', '!=', PpaStatus::Cancelled)->sum('budget'),
        ]);
    }
}
