<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Feedback;
use App\Notifications\FeedbackAnswered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    /**
     * List feedback from the barangay's youth, unanswered first.
     */
    public function index(Request $request): View
    {
        $filter = $request->validate(['show' => ['nullable', Rule::in(['unanswered', 'answered'])]])['show'] ?? null;

        $feedback = $request->user()->barangay->feedback()
            ->with(['author:id,name', 'event:id,title', 'responder:id,name'])
            ->when($filter === 'unanswered', fn ($query) => $query->whereNull('responded_at'))
            ->when($filter === 'answered', fn ($query) => $query->whereNotNull('responded_at'))
            ->orderByRaw('responded_at is not null')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('officer.feedback.index', [
            'feedback' => $feedback,
            'filter' => $filter,
            'unansweredCount' => $request->user()->barangay->feedback()->whereNull('responded_at')->count(),
        ]);
    }

    /**
     * Reply to the youth. They get an email with the reply.
     */
    public function update(Request $request, Feedback $feedback): RedirectResponse
    {
        Gate::authorize('respond', $feedback);

        $feedback->response = $request->validate([
            'response' => ['required', 'string', 'max:2000'],
        ], [
            'response.required' => 'Write a reply before sending.',
        ])['response'];
        $feedback->responded_by = $request->user()->id;
        $feedback->responded_at = now();
        $feedback->save();

        $feedback->author->notify(new FeedbackAnswered($feedback));

        AuditLog::record('feedback.answered', $request->user(), [], $feedback);

        return back()->with('status', "Reply sent to {$feedback->author->name}.");
    }
}
