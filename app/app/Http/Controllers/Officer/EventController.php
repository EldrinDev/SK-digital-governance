<?php

namespace App\Http\Controllers\Officer;

use App\EventKind;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EventController extends Controller
{
    /**
     * List the barangay's events, upcoming first.
     */
    public function index(Request $request): View
    {
        $events = $request->user()->barangay->events()->withCount([
            'registrations',
            'registrations as attended_count' => fn ($query) => $query->whereNotNull('attended_at'),
        ]);

        return view('officer.events.index', [
            'upcoming' => (clone $events)->upcoming()->orderBy('starts_at')->get(),
            'past' => (clone $events)->where('starts_at', '<=', now())->withAvg('feedback', 'rating')->orderByDesc('starts_at')->limit(20)->get(),
        ]);
    }

    /**
     * Show the form for posting an event or consultation.
     */
    public function create(Request $request): View
    {
        return view('officer.events.create', [
            'kinds' => EventKind::cases(),
            'ppas' => $request->user()->barangay->ppas()->orderBy('title')->get(['id', 'title']),
        ]);
    }

    /**
     * Post the event. It appears on the public events page straight away.
     */
    public function store(Request $request): RedirectResponse
    {
        $barangay = $request->user()->barangay;

        $validated = $request->validate([
            'kind' => ['required', Rule::enum(EventKind::class)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'starts_at' => ['required', 'date', 'after:now'],
            'venue' => ['required', 'string', 'max:255'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'ppa_id' => ['nullable', Rule::exists('ppas', 'id')->where('barangay_id', $barangay->id)],
        ], [
            'title.required' => 'Give the event a name.',
            'starts_at.required' => 'Enter when it starts.',
            'starts_at.after' => 'The event must start in the future.',
            'venue.required' => 'Enter where it will be held.',
        ]);

        $event = $barangay->events()->create($validated);

        AuditLog::record('event.created', $request->user(), ['event' => $event->title], $event);

        return redirect()->route('officer.events.show', $event)->with('status', 'Event posted. Youth in your barangay can register now.');
    }

    /**
     * Show registrants for check-in, and evaluations once it's over.
     */
    public function show(Event $event): View
    {
        Gate::authorize('update', $event);

        $event->load([
            'registrations' => fn ($query) => $query->with('user:id,name,email')->oldest(),
            'feedback' => fn ($query) => $query->with('author:id,name')->latest(),
            'ppa:id,title',
        ]);

        return view('officer.events.show', ['event' => $event]);
    }
}
