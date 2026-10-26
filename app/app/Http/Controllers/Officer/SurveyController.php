<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Survey;
use App\QuestionType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SurveyController extends Controller
{
    /**
     * List the barangay's surveys.
     */
    public function index(Request $request): View
    {
        return view('officer.surveys.index', [
            'surveys' => $request->user()->barangay->surveys()->withCount(['questions', 'responses'])->latest()->get(),
        ]);
    }

    /**
     * Start a draft survey.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ], [
            'title.required' => 'Give the survey a name.',
        ]);

        $survey = $request->user()->barangay->surveys()->create($validated);

        AuditLog::record('survey.created', $request->user(), ['survey' => $survey->title], $survey);

        return redirect()->route('officer.surveys.show', $survey)->with('status', 'Survey started. Add its questions, then open it.');
    }

    /**
     * Show the questions (editable while draft) and the anonymous results.
     */
    public function show(Survey $survey): View
    {
        Gate::authorize('view', $survey);

        $survey->load('questions.answers')->loadCount('responses');

        return view('officer.surveys.show', [
            'survey' => $survey,
            'types' => QuestionType::cases(),
        ]);
    }
}
