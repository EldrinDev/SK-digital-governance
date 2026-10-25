<?php

namespace App\Http\Controllers\Youth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Event;
use App\Notifications\EventRegistrationConfirmed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventRegistrationController extends Controller
{
    /**
     * Register the youth for an event after checking eligibility, capacity and duplicates.
     */
    public function store(Request $request, Event $event): RedirectResponse
    {
        $user = $request->user();

        $problem = match (true) {
            $event->hasStarted() => 'Registration is closed because the event has started.',
            $user->barangay_id !== $event->barangay_id => "This event is for youth of {$event->barangay->name}.",
            $user->privacy_consented_at === null => 'Accept the privacy notice before registering.',
            default => null,
        };

        if ($problem) {
            return back()->withErrors(['registration' => $problem]);
        }

        $result = DB::transaction(function () use ($event, $user): string {
            $locked = Event::whereKey($event->id)->lockForUpdate()->first();

            if ($locked->registrations()->where('user_id', $user->id)->exists()) {
                return 'duplicate';
            }

            if ($locked->capacity !== null && $locked->registrations()->count() >= $locked->capacity) {
                return 'full';
            }

            $locked->registrations()->create(['user_id' => $user->id]);

            return 'registered';
        });

        if ($result === 'duplicate') {
            return back()->withErrors(['registration' => 'You’re already registered for this event.']);
        }

        if ($result === 'full') {
            return back()->withErrors(['registration' => 'Sorry, every place is taken.']);
        }

        $user->notify(new EventRegistrationConfirmed($event));
        AuditLog::record('event.registered', $user, ['event' => $event->title], $event);

        return back()->with('status', 'You’re registered. We emailed you the details.');
    }

    /**
     * Cancel the youth's registration before the event starts.
     */
    public function destroy(Request $request, Event $event): RedirectResponse
    {
        if ($event->hasStarted()) {
            return back()->withErrors(['registration' => 'The event has started, so the registration can’t be cancelled.']);
        }

        $deleted = $event->registrations()->where('user_id', $request->user()->id)->delete();

        if ($deleted) {
            AuditLog::record('event.unregistered', $request->user(), ['event' => $event->title], $event);
        }

        return back()->with('status', 'Your registration was cancelled.');
    }
}
