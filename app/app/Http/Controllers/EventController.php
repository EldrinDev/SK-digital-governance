<?php

namespace App\Http\Controllers;

use App\Models\Barangay;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Public list of upcoming events and consultations from participating barangays.
 */
class EventController extends Controller
{
    /**
     * List upcoming events, optionally for one barangay.
     */
    public function index(Request $request): View
    {
        $barangayId = $request->integer('barangay') ?: null;

        return view('events.index', [
            'events' => Event::query()
                ->upcoming()
                ->whereHas('barangay', fn ($query) => $query->participating())
                ->when($barangayId, fn ($query) => $query->where('barangay_id', $barangayId))
                ->with('barangay:id,name')
                ->withCount('registrations')
                ->orderBy('starts_at')
                ->paginate(12)
                ->withQueryString(),
            'barangays' => Barangay::participating()->orderBy('name')->get(['id', 'name']),
            'selectedBarangay' => $barangayId,
        ]);
    }

    /**
     * Show one event with how to register.
     */
    public function show(Request $request, Event $event): View
    {
        abort_unless($event->barangay->is_participating, 404);

        $event->loadCount('registrations');
        $user = $request->user();

        return view('events.show', [
            'event' => $event,
            'registration' => $user ? $event->registrations()->where('user_id', $user->id)->first() : null,
        ]);
    }
}
