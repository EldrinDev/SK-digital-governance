<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ActivityController extends Controller
{
    /**
     * Show the youth's whole participation history: events registered for, attended or missed
     * (with how they were checked in), ratings given, surveys answered and messages sent.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $type = $request->validate(['type' => ['nullable', Rule::in(['events', 'surveys', 'messages'])]])['type'] ?? null;

        $registrations = $user->eventRegistrations()->with(['event.barangay:id,name', 'checkedInBy:id,name'])->get();
        $ratings = $user->feedback()->whereNotNull('event_id')->pluck('rating', 'event_id');
        $responses = $user->surveyResponses()->with('survey:id,title')->get();
        $messages = $user->feedback()->whereNull('event_id')->with('responder:id,name')->get();

        /** @var Collection<int, array{date: Carbon, type: string, kind: string, title: string, detail: ?string, url: ?string}> $items */
        $items = collect()
            ->merge($registrations->map(function ($registration) use ($ratings): array {
                $event = $registration->event;
                $kind = match (true) {
                    $registration->attended() => 'attended',
                    $event->hasStarted() => 'missed',
                    default => 'upcoming',
                };
                $rating = $ratings[$event->id] ?? null;

                return [
                    'date' => $kind === 'upcoming' ? $registration->created_at : $event->starts_at,
                    'type' => 'events',
                    'kind' => $kind,
                    'title' => $event->title,
                    'detail' => match ($kind) {
                        'attended' => 'Checked in '.$registration->attended_at->format('g:i A')
                            .($registration->check_in_method ? ' by '.str($registration->check_in_method->label())->lower() : '')
                            .($registration->checkedInBy ? ', '.$registration->checkedInBy->name : '')
                            .($rating ? '. You rated it '.$rating.' out of 5.' : '. Not rated yet.'),
                        'missed' => 'You registered but weren’t checked in.',
                        default => 'Registered. It’s on '.$event->starts_at->format('l, F j').'.',
                    },
                    'url' => route('events.show', $event),
                ];
            }))
            ->merge($responses->map(fn ($response): array => [
                'date' => $response->created_at,
                'type' => 'surveys',
                'kind' => 'survey',
                'title' => $response->survey->title,
                'detail' => 'You answered this survey. Answers are counted without your name.',
                'url' => null,
            ]))
            ->merge($messages->map(fn ($message): array => [
                'date' => $message->created_at,
                'type' => 'messages',
                'kind' => $message->isAnswered() ? 'replied' : 'waiting',
                'title' => str($message->message)->limit(90)->toString(),
                'detail' => $message->isAnswered()
                    ? ($message->responder?->name ?? 'Your SK').' replied: '.str($message->response)->limit(140)
                    : 'Waiting for a reply from your SK.',
                'url' => null,
            ]));

        $attended = $registrations->filter->attended()->count();
        $missed = $registrations->filter(fn ($registration) => ! $registration->attended() && $registration->event->hasStarted())->count();

        return view('account.activity', [
            'months' => $items
                ->when($type, fn ($all) => $all->where('type', $type))
                ->sortByDesc('date')
                ->groupBy(fn (array $item): string => $item['date']->format('F Y')),
            'type' => $type,
            'counts' => $items->countBy('type'),
            'summary' => [
                'attended' => $attended,
                'missed' => $missed,
                'rate' => $attended + $missed > 0 ? (int) round($attended / ($attended + $missed) * 100) : null,
                'byQr' => $registrations->filter(fn ($registration) => $registration->check_in_method?->value === 'qr')->count(),
                'surveys' => $responses->count(),
                'messages' => $messages->count(),
            ],
        ]);
    }
}
