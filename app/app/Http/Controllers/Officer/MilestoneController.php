<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Officer\StoreMilestoneRequest;
use App\Models\AuditLog;
use App\Models\Milestone;
use App\Models\Ppa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MilestoneController extends Controller
{
    /**
     * Add a milestone to the PPA.
     */
    public function store(StoreMilestoneRequest $request, Ppa $ppa): RedirectResponse
    {
        $milestone = $ppa->milestones()->create($request->validated());

        AuditLog::record('milestone.created', $request->user(), ['milestone' => $milestone->title], $ppa);

        return redirect()->route('officer.ppas.show', $ppa)->with('status', 'Milestone added.');
    }

    /**
     * Mark a milestone done, or reopen it.
     */
    public function update(Request $request, Ppa $ppa, Milestone $milestone): RedirectResponse
    {
        Gate::authorize('track', $ppa);

        $completed = $request->validate(['completed' => ['required', 'boolean']])['completed'];

        $milestone->completed_at = $completed ? now() : null;
        $milestone->save();

        AuditLog::record($completed ? 'milestone.completed' : 'milestone.reopened', $request->user(), ['milestone' => $milestone->title], $ppa);

        return redirect()->route('officer.ppas.show', $ppa)->toast($completed ? 'Milestone marked done.' : 'Milestone reopened.', $completed ? 'success' : 'info');
    }
}
