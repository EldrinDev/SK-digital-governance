@php
    $checkInOpen = ! $event->starts_at->isAfter(today()->endOfDay());
    $attended = $event->registrations->filter->attended()->count();
@endphp
<x-layouts.app :title="$event->title" :heading="$event->title" :description="$event->kind->label().' · '.$event->starts_at->format('l, F j, Y · g:i A').' · '.$event->venue">
    <x-slot:actions>
        <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('officer.events.export', $event) }}" class="inline-flex items-center gap-2 rounded-[8px] border border-line bg-white px-4 py-2.5 text-sm font-semibold hover:border-navy">
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/></svg>
            Download CSV
        </a>
        <a href="{{ route('events.show', $event) }}" target="_blank" class="rounded-[8px] border border-line bg-white px-4 py-2.5 text-sm font-semibold hover:border-navy">View public page</a>
        </div>
    </x-slot:actions>

    <p class="mb-6 text-sm"><a href="{{ route('officer.events.index') }}" class="font-medium text-slate-muted hover:text-emerald">&larr; All events</a></p>

    @error('attendance')
        <p role="alert" class="mb-6 rounded-[12px] border border-warning/40 bg-warning/10 p-4 text-sm">{{ $message }}</p>
    @enderror

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <section class="rounded-[12px] border border-line bg-white" aria-labelledby="registrants-heading">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-5 py-4">
                <h2 id="registrants-heading" class="font-display text-lg font-semibold">Registrants</h2>
                <p class="text-sm text-slate-muted">{{ $attended }} of {{ $event->registrations->count() }} checked in{{ $event->capacity ? ' · '.$event->capacity.' places' : '' }}</p>
            </div>
            @if ($event->registrations->isEmpty())
                <p class="px-5 py-6 text-sm text-slate-muted">No one has registered yet.</p>
            @else
                <ul class="divide-y divide-line">
                    @foreach ($event->registrations as $registration)
                        <li class="flex flex-wrap items-center justify-between gap-3 px-5 py-3">
                            <div>
                                <p class="text-sm font-medium">{{ $registration->user->name }}</p>
                                <p class="text-xs text-slate-muted">Registered {{ $registration->created_at->format('M j') }}</p>
                            </div>
                            @if ($checkInOpen)
                                <form method="POST" action="{{ route('officer.events.registrations.update', [$event, $registration]) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="attended" value="{{ $registration->attended() ? 0 : 1 }}">
                                    @if ($registration->attended())
                                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-[8px] bg-emerald/10 px-3 py-1.5 text-xs font-semibold text-emerald">
                                            <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg>
                                            Checked in<span class="sr-only">. Undo for {{ $registration->user->name }}</span>
                                        </button>
                                    @else
                                        <button type="submit" class="rounded-[8px] border border-line px-3 py-1.5 text-xs font-semibold hover:border-emerald hover:text-emerald">Check in<span class="sr-only"> {{ $registration->user->name }}</span></button>
                                    @endif
                                </form>
                            @else
                                <span class="text-xs text-slate-muted">Check-in opens on the day</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <div class="space-y-6">
            @if ($event->description)
                <section class="rounded-[12px] border border-line bg-white p-5">
                    <h2 class="font-display text-lg font-semibold">Details</h2>
                    <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-navy/80">{{ $event->description }}</p>
                    @if ($event->ppa)
                        <p class="mt-3 text-xs text-slate-muted">Part of PPA: <a href="{{ route('officer.ppas.show', $event->ppa) }}" class="font-semibold text-emerald hover:underline">{{ $event->ppa->title }}</a></p>
                    @endif
                </section>
            @endif

            <section class="rounded-[12px] border border-line bg-white p-5" aria-labelledby="evaluations-heading">
                <h2 id="evaluations-heading" class="font-display text-lg font-semibold">Evaluations</h2>
                @if ($event->feedback->isEmpty())
                    <p class="mt-2 text-sm text-slate-muted">Youth who attended can rate the event from their dashboard.</p>
                @else
                    <p class="mt-1 text-sm text-slate-muted">Average {{ number_format($event->feedback->avg('rating'), 1) }} / 5 from {{ $event->feedback->count() }}</p>
                    <ul class="mt-3 space-y-3 text-sm">
                        @foreach ($event->feedback as $evaluation)
                            <li>
                                <p><span class="font-semibold">{{ str_repeat('★', $evaluation->rating) }}</span><span class="text-line">{{ str_repeat('★', 5 - $evaluation->rating) }}</span> <span class="sr-only">{{ $evaluation->rating }} out of 5</span></p>
                                <p class="mt-0.5 text-navy/80">{{ $evaluation->message }}</p>
                                <p class="text-xs text-slate-muted">{{ $evaluation->author->name }}</p>
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('officer.feedback.index') }}" class="mt-3 inline-block text-sm font-semibold text-emerald hover:underline">Reply in Feedback</a>
                @endif
            </section>
        </div>
    </div>
</x-layouts.app>
