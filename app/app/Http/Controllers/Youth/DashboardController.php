<?php

namespace App\Http\Controllers\Youth;

use App\Http\Controllers\Controller;
use App\OfficerPosition;
use App\PpaStatus;
use App\UserRole;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the youth's to-dos, events, their SK's news and fund use, and their messages.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $barangay = $user->barangay;
        $registrations = $user->eventRegistrations()->with('event')->get();
        $ratings = $user->feedback()->whereNotNull('event_id')->pluck('rating', 'event_id');

        $upcoming = $registrations->filter(fn ($registration) => ! $registration->event->hasStarted())
            ->sortBy('event.starts_at')->values();

        $publishedPpas = $barangay->ppas()
            ->where('status', '!=', PpaStatus::Cancelled)
            ->whereHas('abyip', fn ($abyip) => $abyip->published());

        return view('dashboard', [
            'barangay' => $barangay,
            'nextUp' => $upcoming->first()?->event,
            'attendedCount' => $registrations->filter->attended()->count(),
            'myUpcoming' => $upcoming,
            'pastAttended' => $registrations->filter->attended()->sortByDesc('event.starts_at')->take(4)->values(),
            'ratings' => $ratings,
            'toEvaluate' => $registrations->filter(fn ($registration) => $registration->attended() && ! $ratings->has($registration->event_id))
                ->values(),
            'openEvents' => $barangay->events()->upcoming()
                ->whereDoesntHave('registrations', fn ($query) => $query->where('user_id', $user->id))
                ->withCount('registrations')
                ->orderBy('starts_at')
                ->limit(4)
                ->get(),
            'openSurveys' => $barangay->surveys()->open()
                ->whereDoesntHave('responses', fn ($query) => $query->where('user_id', $user->id))
                ->latest('opened_at')
                ->get(),
            'announcements' => $barangay->announcements()->with('barangay:id,name,logo_path')->pinnedFirst()->limit(3)->get(),
            'myFeedback' => $user->feedback()->with(['event:id,title', 'responder:id,name'])->latest()->limit(4)->get(),
            'chairperson' => $barangay->users()
                ->where('role', UserRole::Officer)
                ->where('position', OfficerPosition::Chairperson)
                ->whereNull('deactivated_at')
                ->value('name'),
            'fund' => [
                'budget' => (float) (clone $publishedPpas)->sum('budget'),
                'spent' => (float) (clone $publishedPpas)->sum('amount_spent'),
                'projects' => (clone $publishedPpas)->count(),
                'completed' => (clone $publishedPpas)->where('status', PpaStatus::Completed)->count(),
            ],
        ]);
    }
}
