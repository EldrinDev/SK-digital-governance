<?php

namespace App\Http\Controllers\Officer;

use App\CheckInMethod;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class EventAttendanceController extends Controller
{
    /**
     * Check a registrant in, or undo it. Check-in opens on the day of the event.
     */
    public function update(Request $request, Event $event, EventRegistration $registration): RedirectResponse
    {
        Gate::authorize('update', $event);

        if ($event->starts_at->isAfter(today()->endOfDay())) {
            return back()->withErrors(['attendance' => 'Check-in opens on the day of the event.']);
        }

        $validated = $request->validate([
            'attended' => ['required', 'boolean'],
            'method' => ['nullable', Rule::enum(CheckInMethod::class)],
        ]);
        $attended = (bool) $validated['attended'];
        $method = $attended ? (CheckInMethod::tryFrom($validated['method'] ?? '') ?? CheckInMethod::Manual) : null;

        $registration->forceFill([
            'attended_at' => $attended ? now() : null,
            'check_in_method' => $method,
            'checked_in_by' => $attended ? $request->user()->id : null,
        ])->save();

        AuditLog::record('event.checked_in', $request->user(), [
            'event' => $event->title,
            'youth' => $registration->user->name,
            'youth_id' => $registration->user_id,
            'attended' => $attended ? 'yes' : 'no',
            'method' => $method?->label(),
        ], $event);

        return back()->with('status', $attended ? "{$registration->user->name} checked in." : "Check-in undone for {$registration->user->name}.");
    }
}
