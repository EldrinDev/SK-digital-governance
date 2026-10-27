<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\QuestionType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class SurveyQuestionController extends Controller
{
    /**
     * Add a question to a draft survey. Choices are typed one per line.
     */
    public function store(Request $request, Survey $survey): RedirectResponse
    {
        Gate::authorize('update', $survey);

        $validated = $request->validate([
            'prompt' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(QuestionType::class)],
            'options' => ['nullable', 'required_if:type,choice', 'string', 'max:2000'],
        ], [
            'prompt.required' => 'Write the question.',
            'options.required_if' => 'List the choices, one per line.',
        ]);

        $options = null;

        if ($validated['type'] === QuestionType::Choice->value) {
            $options = collect(preg_split('/\R/', $validated['options']))->map(fn (string $option): string => trim($option))->filter()->unique()->values()->all();

            if (count($options) < 2) {
                return back()->withInput()->withErrors(['options' => 'Give at least two different choices.']);
            }
        }

        $survey->questions()->create([
            'position' => ($survey->questions()->max('position') ?? 0) + 1,
            'prompt' => $validated['prompt'],
            'type' => $validated['type'],
            'options' => $options,
        ]);

        return redirect()->route('officer.surveys.show', $survey)->with('status', 'Question added.');
    }

    /**
     * Remove a question from a draft survey.
     */
    public function destroy(Survey $survey, SurveyQuestion $question): RedirectResponse
    {
        Gate::authorize('update', $survey);

        $question->delete();

        return redirect()->route('officer.surveys.show', $survey)->toast('Question removed.', 'warning');
    }
}
