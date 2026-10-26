<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Survey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class SurveyStatusController extends Controller
{
    /**
     * Open a draft survey for answers, or close an open one.
     */
    public function update(Request $request, Survey $survey): RedirectResponse
    {
        Gate::authorize('changeStatus', $survey);

        $action = $request->validate(['action' => ['required', Rule::in(['open', 'close'])]])['action'];

        if ($action === 'open') {
            if (! $survey->isDraft()) {
                return back()->withErrors(['survey' => 'This survey was already opened.']);
            }

            if ($survey->questions()->doesntExist()) {
                return back()->withErrors(['survey' => 'Add at least one question before opening the survey.']);
            }

            $survey->forceFill(['opened_at' => now()])->save();
        } else {
            if (! $survey->isOpen()) {
                return back()->withErrors(['survey' => 'Only an open survey can be closed.']);
            }

            $survey->forceFill(['closed_at' => now()])->save();
        }

        AuditLog::record($action === 'open' ? 'survey.opened' : 'survey.closed', $request->user(), ['survey' => $survey->title], $survey);

        return back()->with('status', $action === 'open'
            ? 'Survey opened. Youth of your barangay can answer it from their dashboard.'
            : 'Survey closed. No more answers will be taken.');
    }
}
