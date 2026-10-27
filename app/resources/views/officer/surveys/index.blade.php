@php
    $sheet = 'overflow-hidden rounded-[14px] border border-line bg-white';
@endphp
<x-layouts.app title="Surveys" heading="Surveys" description="Ask your youth what they need. Answers are anonymous: you see totals per question, never who answered what.">
    <div class="grid items-start gap-x-8 gap-y-10 lg:grid-cols-[minmax(0,1fr)_21rem]">
        <section aria-labelledby="surveys-heading">
            <h2 id="surveys-heading" class="mb-3 font-display text-base font-semibold text-navy">{{ trans_choice('{0}Your surveys|{1}1 survey|[2,*]:count surveys', $surveys->count()) }}</h2>

            @if ($surveys->isEmpty())
                <div class="rounded-[14px] border border-dashed border-line bg-white px-6 py-10 text-center">
                    <p class="font-display text-lg font-semibold text-navy">No surveys yet</p>
                    <p class="mx-auto mt-1 max-w-md text-sm text-slate-muted">Start one on the right. A good first survey asks what programs youth want next year, before you write the ABYIP.</p>
                </div>
            @else
                <ul class="{{ $sheet }} divide-y divide-line">
                    @foreach ($surveys as $survey)
                        @php
                            $status = $survey->statusLabel();
                            $reach = $youthCount > 0 ? min(100, (int) round($survey->responses_count / $youthCount * 100)) : 0;
                            $when = match ($status) {
                                'Draft' => $survey->questions_count > 0 ? 'Ready to open when you are' : 'Add questions, then open it',
                                'Open' => 'Open since '.$survey->opened_at->format('M j'),
                                default => 'Closed '.$survey->closed_at->format('M j, Y'),
                            };
                        @endphp
                        <li>
                            <a href="{{ route('officer.surveys.show', $survey) }}" class="group grid gap-x-6 gap-y-3 px-5 py-4 hover:bg-mist/50 sm:grid-cols-[minmax(0,1fr)_12rem_1rem] sm:items-center">
                                <span class="min-w-0">
                                    <span class="flex items-center gap-2 text-xs font-semibold">
                                        <span @class([
                                            'h-2 w-2 rounded-full',
                                            'bg-emerald' => $status === 'Open',
                                            'bg-gold' => $status === 'Draft',
                                            'bg-slate-muted/50' => $status === 'Closed',
                                        ]) aria-hidden="true"></span>
                                        <span @class(['text-emerald' => $status === 'Open', 'text-[#92400e]' => $status === 'Draft', 'text-slate-muted' => $status === 'Closed'])>{{ $status }}</span>
                                        <span class="font-normal text-slate-muted">{{ $when }}</span>
                                    </span>
                                    <span class="mt-1 block font-medium leading-snug text-navy group-hover:text-emerald">{{ $survey->title }}</span>
                                    <span class="mt-0.5 block text-sm text-slate-muted">{{ trans_choice(':count question|:count questions', $survey->questions_count) }}</span>
                                </span>
                                <span class="block">
                                    @if ($status === 'Draft')
                                        <span class="text-sm text-slate-muted">Not open yet</span>
                                    @else
                                        <span class="flex items-baseline justify-between text-sm">
                                            <span class="font-semibold text-navy">{{ trans_choice(':count response|:count responses', $survey->responses_count) }}</span>
                                            <span class="text-xs text-slate-muted">{{ $reach }}% of youth</span>
                                        </span>
                                        <span class="mt-1.5 block h-1.5 overflow-hidden rounded-full bg-mist" aria-hidden="true">
                                            <span class="block h-full rounded-full bg-emerald" style="width: {{ max($reach, 2) }}%"></span>
                                        </span>
                                    @endif
                                </span>
                                <svg viewBox="0 0 24 24" class="hidden h-4 w-4 text-slate-muted group-hover:text-emerald sm:block" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <aside class="space-y-6 lg:sticky lg:top-24">
            <section aria-labelledby="new-survey">
                <h2 id="new-survey" class="mb-3 font-display text-base font-semibold text-navy">Start a survey</h2>
                <div class="{{ $sheet }} p-5">
                    <form method="POST" action="{{ route('officer.surveys.store') }}" class="space-y-4" novalidate>
                        @csrf
                        <x-form.input name="title" label="Question or topic" placeholder="e.g. What programs do you want next year?" required />
                        <div>
                            <label for="description" class="block text-sm font-medium">Intro <span class="font-normal text-slate-muted">(optional)</span></label>
                            <textarea id="description" name="description" rows="3" placeholder="Tell youth why you’re asking and how the answers will be used." class="mt-1.5 block w-full rounded-[8px] border border-line px-3.5 py-2.5 text-sm outline-none focus:border-emerald focus:ring-3 focus:ring-emerald/20">{{ old('description') }}</textarea>
                        </div>
                        <x-form.submit busy="Starting…">Start survey</x-form.submit>
                    </form>
                </div>
            </section>

            <section aria-labelledby="how-heading" class="px-1">
                <h2 id="how-heading" class="text-sm font-semibold text-navy">How it works</h2>
                <ol class="mt-3 space-y-3 text-sm text-slate-muted">
                    @foreach (['Start it with a name. It stays a draft, so youth can’t see it yet.', 'Add questions: multiple choice, a 1 to 5 rating, or a written answer.', 'Open it. Youth of your barangay see it on their dashboard.', 'Close it when you have enough answers. Results stay here.'] as $step)
                        <li class="flex gap-3">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-line bg-white text-xs font-semibold text-navy">{{ $loop->iteration }}</span>
                            <span class="pt-0.5">{{ $step }}</span>
                        </li>
                    @endforeach
                </ol>
            </section>
        </aside>
    </div>
</x-layouts.app>
