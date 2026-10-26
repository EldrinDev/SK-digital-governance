<?php

namespace App\Http\Controllers\Youth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the youth's events, evaluations to give, and feedback with replies.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $registrations = $user->eventRegistrations()->with('event')->get();
        $evaluated = $user->feedback()->whereNotNull('event_id')->pluck('event_id');

        return view('dashboard', [
            'barangay' => $user->barangay,
            'myUpcoming' => $registrations->filter(fn ($registration) => ! $registration->event->hasStarted())
                ->sortBy('event.starts_at')->values(),
            'toEvaluate' => $registrations->filter(fn ($registration) => $registration->attended() && ! $evaluated->contains($registration->event_id))
                ->values(),
            'openEvents' => $user->barangay->events()->upcoming()
                ->whereDoesntHave('registrations', fn ($query) => $query->where('user_id', $user->id))
                ->withCount('registrations')
                ->orderBy('starts_at')
                ->limit(5)
                ->get(),
            'openSurveys' => $user->barangay->surveys()->open()
                ->whereDoesntHave('responses', fn ($query) => $query->where('user_id', $user->id))
                ->latest('opened_at')
                ->get(),
            'announcements' => $user->barangay->announcements()->with('barangay:id,name,logo_path')->pinnedFirst()->limit(3)->get(),
            'myFeedback' => $user->feedback()->with(['event:id,title', 'responder:id,name'])->latest()->limit(5)->get(),
        ]);
    }
}
