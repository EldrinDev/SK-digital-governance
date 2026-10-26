<?php

namespace App\Http\Controllers;

use App\EventKind;
use App\Models\Barangay;
use App\Models\Event;
use App\UserRole;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Public list of upcoming events and consultations from participating barangays.
 */
class EventController extends Controller
{
    /**
     * List upcoming events as an agenda, optionally for one barangay or one kind.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'barangay' => ['nullable', 'integer'],
            'kind' => ['nullable', Rule::enum(EventKind::class)],
        ]);
        $barangayId = (int) ($filters['barangay'] ?? 0) ?: null;
        $kind = EventKind::tryFrom($filters['kind'] ?? '');
        $user = $request->user();

        $events = Event::query()
            ->upcoming()
            ->whereHas('barangay', fn ($query) => $query->participating())
            ->when($barangayId, fn ($query) => $query->where('barangay_id', $barangayId))
            ->when($kind, fn ($query) => $query->where('kind', $kind))
            ->with('barangay:id,name')
            ->withCount('registrations')
            ->orderBy('starts_at')
            ->paginate(20)
            ->withQueryString();

        return view('events.index', [
            'events' => $events,
            'months' => $events->getCollection()->groupBy(fn (Event $event): string => $event->starts_at->format('F Y')),
            'barangays' => Barangay::participating()->orderBy('name')->get(['id', 'name']),
            'selectedBarangay' => $barangayId,
            'selectedKind' => $kind,
            'kinds' => EventKind::cases(),
            'registeredEventIds' => $user?->hasRole(UserRole::Youth)
                ? $user->eventRegistrations()->whereIn('event_id', $events->pluck('id'))->pluck('event_id')->all()
                : [],
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
            'moreEvents' => $event->barangay->events()->upcoming()->whereKeyNot($event->id)->orderBy('starts_at')->limit(3)->get(),
        ]);
    }

    /**
     * Download the event as a calendar file, so youth can add it to their phone's calendar.
     */
    public function calendar(Event $event): Response
    {
        abort_unless($event->barangay->is_participating, 404);

        $escape = fn (string $text): string => str_replace(['\\', ';', ',', "\r\n", "\n"], ['\\\\', '\;', '\,', '\n', '\n'], $text);
        $format = fn ($date): string => $date->copy()->utc()->format('Ymd\THis\Z');

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//SK Digital Governance//Olongapo City//EN',
            'BEGIN:VEVENT',
            'UID:event-'.$event->id.'@'.parse_url(config('app.url'), PHP_URL_HOST),
            'DTSTAMP:'.$format(now()),
            'DTSTART:'.$format($event->starts_at),
            'DTEND:'.$format($event->starts_at->copy()->addHours(3)),
            'SUMMARY:'.$escape($event->title),
            'LOCATION:'.$escape("{$event->venue}, {$event->barangay->name}, Olongapo City"),
            'DESCRIPTION:'.$escape(trim(($event->description ?? '')."\n\n".route('events.show', $event))),
            'URL:'.route('events.show', $event),
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        return response(implode("\r\n", $lines)."\r\n", 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.Str::slug($event->title).'.ics"',
        ]);
    }
}
