<?php

namespace App\Http\Controllers\Youth;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\QuestionType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SurveyResponseController extends Controller
{
    /**
     * Show the survey form.
     */
    public function create(Survey $survey): View
    {
        Gate::authorize('answer', $survey);

        return view('surveys.answer', ['survey' => $survey->load('questions')]);
    }

    /**
     * Save the youth's answers. Every question is required.
     */
    public function store(Request $request, Survey $survey): RedirectResponse
    {
        Gate::authorize('answer', $survey);

        $survey->load('questions');

        $rules = [];
        $messages = [];

        foreach ($survey->questions as $question) {
            $key = "answers.{$question->id}";
            $rules[$key] = match ($question->type) {
                QuestionType::Text => ['required', 'string', 'max:2000'],
                QuestionType::Choice => ['required', Rule::in($question->options)],
                QuestionType::Rating => ['required', 'integer', 'between:1,5'],
            };
            $messages["{$key}.required"] = 'Answer this question.';
        }

        $answers = $request->validate($rules, $messages)['answers'] ?? [];

        DB::transaction(function () use ($request, $survey, $answers): void {
            $response = $survey->responses()->create(['user_id' => $request->user()->id]);

            foreach ($survey->questions as $question) {
                $response->answers()->create([
                    'survey_question_id' => $question->id,
                    'value' => (string) $answers[$question->id],
                ]);
            }
        });

        return redirect()->route('dashboard')->with('status', 'Thanks! Your answers were sent to your SK.');
    }
}
