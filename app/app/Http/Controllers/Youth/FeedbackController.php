<?php

namespace App\Http\Controllers\Youth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Event;
use App\Models\Feedback;
use App\Notifications\FeedbackReceived;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class FeedbackController extends Controller
{
    /**
     * Send feedback to the barangay SK, or evaluate an event the youth attended.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'event_id' => ['nullable', 'integer'],
            'rating' => ['nullable', 'required_with:event_id', 'integer', 'between:1,5'],
            'message' => ['required', 'string', 'max:2000'],
        ], [
            'rating.required_with' => 'Rate the event from 1 to 5.',
            'message.required' => 'Write your feedback before sending.',
        ]);

        $event = null;

        if ($validated['event_id'] ?? null) {
            $event = Event::find($validated['event_id']);
            $attended = $event?->registrations()->where('user_id', $user->id)->whereNotNull('attended_at')->exists();

            if (! $attended) {
                return back()->withErrors(['event_id' => 'You can only evaluate events you attended.']);
            }

            if ($user->feedback()->where('event_id', $event->id)->exists()) {
                return back()->withErrors(['event_id' => 'You already evaluated this event.']);
            }
        }

        $feedback = new Feedback([
            'event_id' => $event?->id,
            'rating' => $event ? $validated['rating'] : null,
            'message' => $validated['message'],
        ]);
        $feedback->barangay_id = $user->barangay_id;
        $user->feedback()->save($feedback);

        AuditLog::record('feedback.sent', $user, $event ? ['event' => $event->title] : [], $feedback);

        Notification::send($user->barangay->activeOfficers(), new FeedbackReceived($feedback));

        return back()->with('status', $event ? 'Thanks for your evaluation.' : 'Your feedback was sent to your SK.');
    }
}
