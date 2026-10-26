<x-layouts.app title="Dashboard" :heading="'Welcome, '.auth()->user()->name" :description="'Events, consultations, and replies from the '.$barangay->name.' SK.'">
    @if ($errors->any())
        <div role="alert" class="mb-6 rounded-[12px] border border-error/30 bg-error/5 p-4 text-sm">
            @foreach ($errors->all() as $message)
                <p>{{ $message }}</p>
            @endforeach
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_24rem]">
        <div class="space-y-6">
            @if ($openSurveys->isNotEmpty())
                <section class="rounded-[12px] border border-gold/50 bg-gold/10 p-5" aria-labelledby="surveys-heading">
                    <h2 id="surveys-heading" class="font-display text-lg font-semibold">Your SK wants to hear from you</h2>
                    <ul class="mt-3 space-y-2">
                        @foreach ($openSurveys as $survey)
                            <li class="flex flex-wrap items-center justify-between gap-3 rounded-[8px] bg-white p-3">
                                <span class="text-sm font-medium">{{ $survey->title }}</span>
                                <a href="{{ route('surveys.answer', $survey) }}" class="rounded-[8px] bg-emerald px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-dark">Answer<span class="sr-only"> {{ $survey->title }}</span></a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if ($toEvaluate->isNotEmpty())
                <section class="rounded-[12px] border border-info/30 bg-info/5 p-5" aria-labelledby="evaluate-heading">
                    <h2 id="evaluate-heading" class="font-display text-lg font-semibold">How did it go?</h2>
                    <p class="mt-1 text-sm text-slate-muted">Your rating helps your SK plan better events.</p>
                    @foreach ($toEvaluate as $registration)
                        <form method="POST" action="{{ route('feedback.store') }}" class="mt-4 space-y-3 rounded-[12px] border border-line bg-white p-4" novalidate>
                            @csrf
                            <input type="hidden" name="event_id" value="{{ $registration->event_id }}">
                            <p class="font-medium">{{ $registration->event->title }}</p>
                            <fieldset>
                                <legend class="text-sm">Rating</legend>
                                <div class="mt-1.5 flex gap-1.5">
                                    @foreach (range(1, 5) as $score)
                                        <label class="cursor-pointer rounded-[8px] border border-line px-3 py-1.5 text-sm font-semibold has-[:checked]:border-gold has-[:checked]:bg-gold/20">
                                            <input type="radio" name="rating" value="{{ $score }}" class="sr-only" required>
                                            {{ $score }}<span aria-hidden="true">★</span><span class="sr-only"> out of 5</span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                            <label for="eval-{{ $registration->id }}" class="block text-sm">What should they keep or change?</label>
                            <textarea id="eval-{{ $registration->id }}" name="message" rows="2" required class="block w-full rounded-[8px] border border-line px-3.5 py-2.5 text-sm outline-none focus:border-emerald focus:ring-3 focus:ring-emerald/20"></textarea>
                            <x-form.submit busy="Sending…" :block="false" class="py-2">Send evaluation</x-form.submit>
                        </form>
                    @endforeach
                </section>
            @endif

            <section class="rounded-[12px] border border-line bg-white" aria-labelledby="mine-heading">
                <h2 id="mine-heading" class="border-b border-line px-5 py-4 font-display text-lg font-semibold">My upcoming events</h2>
                <ul class="divide-y divide-line">
                    @forelse ($myUpcoming as $registration)
                        <li class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5">
                            <div>
                                <a href="{{ route('events.show', $registration->event) }}" class="font-medium hover:text-emerald">{{ $registration->event->title }}</a>
                                <p class="text-xs text-slate-muted">{{ $registration->event->starts_at->format('D, M j · g:i A') }} · {{ $registration->event->venue }}</p>
                            </div>
                            <form method="POST" action="{{ route('events.registration.destroy', $registration->event) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-semibold text-slate-muted hover:text-error">Cancel<span class="sr-only"> registration for {{ $registration->event->title }}</span></button>
                            </form>
                        </li>
                    @empty
                        <li class="px-5 py-6 text-sm text-slate-muted">You haven’t registered for anything yet.</li>
                    @endforelse
                </ul>
            </section>

            <section class="rounded-[12px] border border-line bg-white" aria-labelledby="open-heading">
                <div class="flex items-center justify-between gap-3 border-b border-line px-5 py-4">
                    <h2 id="open-heading" class="font-display text-lg font-semibold">Coming up in {{ $barangay->name }}</h2>
                    <a href="{{ route('events.index', ['barangay' => $barangay->id]) }}" class="text-sm font-semibold text-emerald hover:underline">All events</a>
                </div>
                <ul class="divide-y divide-line">
                    @forelse ($openEvents as $event)
                        <li class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5">
                            <div>
                                <a href="{{ route('events.show', $event) }}" class="font-medium hover:text-emerald">{{ $event->title }}</a>
                                <p class="text-xs text-slate-muted">{{ $event->kind->label() }} · {{ $event->starts_at->format('D, M j · g:i A') }}</p>
                            </div>
                            @if ($event->isFull())
                                <span class="text-xs font-semibold text-[#b91c1c]">Full</span>
                            @else
                                <form method="POST" action="{{ route('events.registration.store', $event) }}">
                                    @csrf
                                    <button type="submit" class="rounded-[8px] bg-emerald px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-dark">Register<span class="sr-only"> for {{ $event->title }}</span></button>
                                </form>
                            @endif
                        </li>
                    @empty
                        <li class="px-5 py-6 text-sm text-slate-muted">No new events right now.</li>
                    @endforelse
                </ul>
            </section>
        </div>

        <div class="space-y-6">
            <section class="rounded-[12px] border border-line bg-white p-5" aria-labelledby="feedback-heading">
                <h2 id="feedback-heading" class="font-display text-lg font-semibold">Tell your SK something</h2>
                <form method="POST" action="{{ route('feedback.store') }}" class="mt-3 space-y-3" novalidate>
                    @csrf
                    <label for="message" class="sr-only">Your feedback</label>
                    <textarea id="message" name="message" rows="4" placeholder="Ideas, concerns, or questions for your SK council." class="block w-full rounded-[8px] border border-line px-3.5 py-2.5 text-sm outline-none focus:border-emerald focus:ring-3 focus:ring-emerald/20">{{ old('event_id') ? '' : old('message') }}</textarea>
                    <x-form.submit busy="Sending…">Send to my SK</x-form.submit>
                </form>
            </section>

            <section class="rounded-[12px] border border-line bg-white p-5" aria-labelledby="replies-heading">
                <h2 id="replies-heading" class="font-display text-lg font-semibold">My messages</h2>
                <ul class="mt-3 space-y-4 text-sm">
                    @forelse ($myFeedback as $item)
                        <li>
                            <p class="text-xs text-slate-muted">{{ $item->created_at->format('M j') }}{{ $item->event ? ' · '.$item->event->title : '' }}</p>
                            <p class="mt-0.5">{{ $item->message }}</p>
                            @if ($item->isAnswered())
                                <p class="mt-2 rounded-[8px] bg-mist p-2.5"><span class="font-semibold">{{ $item->responder?->name ?? 'SK' }}:</span> {{ $item->response }}</p>
                            @else
                                <p class="mt-1 text-xs text-slate-muted">Waiting for a reply</p>
                            @endif
                        </li>
                    @empty
                        <li class="text-slate-muted">No messages yet.</li>
                    @endforelse
                </ul>
            </section>
        </div>
    </div>
</x-layouts.app>
