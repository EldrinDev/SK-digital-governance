<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\MeetingKind;
use App\Models\AuditLog;
use App\Models\Meeting;
use App\ResolutionOutcome;
use App\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MeetingController extends Controller
{
    /**
     * List the council's sessions, newest first.
     */
    public function index(Request $request): View
    {
        return view('officer.meetings.index', [
            'meetings' => $request->user()->barangay->meetings()
                ->with('attendees')
                ->withCount('resolutions')
                ->orderByDesc('held_on')
                ->paginate(15),
        ]);
    }

    /**
     * Show the form for recording a session.
     */
    public function create(): View
    {
        return view('officer.meetings.create', ['kinds' => MeetingKind::cases()]);
    }

    /**
     * Record a session.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'kind' => ['required', Rule::enum(MeetingKind::class)],
            'held_on' => ['required', 'date'],
            'venue' => ['nullable', 'string', 'max:255'],
            'agenda' => ['nullable', 'string', 'max:5000'],
        ], [
            'title.required' => 'Give the session a name, e.g. “3rd regular session”.',
            'held_on.required' => 'Enter the date of the session.',
        ]);

        $meeting = $request->user()->barangay->meetings()->create($validated);

        AuditLog::record('meeting.created', $request->user(), ['meeting' => $meeting->title], $meeting);

        return redirect()->route('officer.meetings.show', $meeting)->with('status', 'Session recorded. Mark attendance next.');
    }

    /**
     * Show the session with attendance, minutes and resolutions.
     */
    public function show(Request $request, Meeting $meeting): View
    {
        Gate::authorize('view', $meeting);

        $meeting->load(['attendees', 'resolutions']);

        return view('officer.meetings.show', [
            'meeting' => $meeting,
            'members' => $meeting->barangay->users()
                ->where('role', UserRole::Officer)
                ->whereNull('deactivated_at')
                ->orderBy('name')
                ->get(['id', 'name']),
            'outcomes' => ResolutionOutcome::cases(),
        ]);
    }
}
