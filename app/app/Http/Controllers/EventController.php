<?php

namespace App\Http\Controllers;

use App\CalendarFile;
use App\EventKind;
use App\Models\Barangay;
use App\Models\Event;
use App\Models\Ppa;
use App\UserRole;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Public list of upcoming events and consultations from participating barangays.
 */
class EventController extends Controller
{
    /**
     * List upcoming events as an agenda with a month calendar. Youth start on their own
     * barangay's events ("For you") and can switch to every barangay; anyone can narrow
     * the list by kind, barangay or a day picked on the calendar.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'scope' => ['nullable', Rule::in(['mine', 'all'])],
            'barangay' => ['nullable', 'integer'],
            'kind' => ['nullable', Rule::enum(EventKind::class)],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'month' => ['nullable', 'date_format:Y-m'],
        ]);
        $user = $request->user();
        $youth = $user?->hasRole(UserRole::Youth) ? $user : null;
        $scope = $youth ? ($filters['scope'] ?? 'mine') : 'all';
        $barangayId = $scope === 'mine' ? $youth->barangay_id : ((int) ($filters['barangay'] ?? 0) ?: null);
        $kind = EventKind::tryFrom($filters['kind'] ?? '');
        $date = isset($filters['date']) ? Carbon::parse($filters['date'])->startOfDay() : null;
        $thisMonth = today()->startOfMonth();
        $month = $date?->copy()->startOfMonth()
            ?? (isset($filters['month']) ? Carbon::parse($filters['month'].'-01')->startOfMonth() : $thisMonth);
        $month = $month->lessThan($thisMonth) ? $thisMonth : $month;

        $matching = Event::query()
            ->upcoming()
            ->whereHas('barangay', fn ($query) => $query->participating())
            ->when($barangayId, fn ($query) => $query->where('barangay_id', $barangayId))
            ->when($kind, fn ($query) => $query->where('kind', $kind));

        $events = (clone $matching)
            ->when($date, fn ($query) => $query->whereDate('starts_at', $date))
            ->with('barangay:id,name,logo_path')
            ->withCount('registrations')
            ->orderBy('starts_at')
            ->paginate(20)
            ->withQueryString();

        // Which days of the shown month have events, and whether any of them is a consultation.
        $eventDays = (clone $matching)
            ->whereBetween('starts_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
            ->get(['starts_at', 'kind'])
            ->groupBy(fn (Event $event): string => $event->starts_at->format('Y-m-d'))
            ->map(fn ($day): array => ['count' => $day->count(), 'consultation' => $day->contains('kind', EventKind::Consultation)]);

        return view('events.index', [
            'events' => $events,
            'months' => $events->getCollection()->groupBy(fn (Event $event): string => $event->starts_at->format('F Y')),
            'barangays' => Barangay::participating()->orderBy('name')->get(['id', 'name']),
            'youth' => $youth,
            'scope' => $scope,
            'selectedBarangay' => $scope === 'all' ? $barangayId : null,
            'selectedKind' => $kind,
            'selectedDate' => $date,
            'kinds' => EventKind::cases(),
            'calendar' => [
                'month' => $month,
                'weeks' => collect(CarbonPeriod::create($month->copy()->startOfWeek(Carbon::SUNDAY), $month->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY)))->chunk(7),
                'days' => $eventDays,
                'previous' => $month->greaterThan($thisMonth) ? $month->copy()->subMonth()->format('Y-m') : null,
                'next' => $month->copy()->addMonth()->format('Y-m'),
            ],
            'registeredEventIds' => $youth
                ? $youth->eventRegistrations()->whereIn('event_id', $events->pluck('id'))->pluck('event_id')->all()
                : [],
            'myRegistrations' => $youth
                ? $youth->eventRegistrations()->whereHas('event', fn ($event) => $event->upcoming())->with('event')->get()->sortBy('event.starts_at')->take(4)->values()
                : collect(),
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

        $registration = $user ? $event->registrations()->with('checkedInBy:id,name')->where('user_id', $user->id)->first() : null;

        return view('events.show', [
            'event' => $event,
            'registration' => $registration,
            'memberCode' => $registration ? $user->memberCode() : null,
            // The program this event belongs to, when the public can see that program.
            'program' => $event->ppa_id ? Ppa::query()->publiclyVisible()->with('priority:id,center')->find($event->ppa_id) : null,
            'moreEvents' => $event->barangay->events()->upcoming()->whereKeyNot($event->id)->orderBy('starts_at')->limit(3)->get(),
        ]);
    }

    /**
     * Download the event as a calendar file, so youth can add it to their phone's calendar.
     */
    public function calendar(Event $event): Response
    {
        abort_unless($event->barangay->is_participating, 404);

        return CalendarFile::download(
            uid: 'event-'.$event->id,
            title: $event->title,
            start: $event->starts_at,
            allDay: false,
            location: "{$event->venue}, {$event->barangay->name}, Olongapo City",
            description: trim(($event->description ?? '')."\n\n".route('events.show', $event)),
            url: route('events.show', $event),
        );
    }
}
