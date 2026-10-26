<x-layouts.app title="Surveys" heading="Surveys" description="Ask your youth what they need. Answers are anonymous: you see totals per question, never who answered what.">
    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <ul class="space-y-3">
            @forelse ($surveys as $survey)
                <li>
                    <a href="{{ route('officer.surveys.show', $survey) }}" class="flex flex-wrap items-center justify-between gap-3 rounded-[12px] border border-line bg-white p-5 hover:border-emerald/50">
                        <span>
                            <span class="block font-medium">{{ $survey->title }}</span>
                            <span class="text-xs text-slate-muted">{{ trans_choice(':count question|:count questions', $survey->questions_count) }} · {{ trans_choice(':count response|:count responses', $survey->responses_count) }}</span>
                        </span>
                        <x-ui.badge :tone="match ($survey->statusLabel()) { 'Open' => 'success', 'Draft' => 'warning', default => 'neutral' }">{{ $survey->statusLabel() }}</x-ui.badge>
                    </a>
                </li>
            @empty
                <li class="rounded-[12px] border border-dashed border-line bg-white p-8 text-center text-sm text-slate-muted">No surveys yet.</li>
            @endforelse
        </ul>

        <section class="h-fit rounded-[12px] border border-line bg-white p-5" aria-labelledby="new-survey">
            <h2 id="new-survey" class="font-display text-lg font-semibold">New survey</h2>
            <form method="POST" action="{{ route('officer.surveys.store') }}" class="mt-4 space-y-4" novalidate>
                @csrf
                <x-form.input name="title" label="Name" placeholder="e.g. What programs do you want next year?" required />
                <div>
                    <label for="description" class="block text-sm font-medium">Intro <span class="font-normal text-slate-muted">(optional)</span></label>
                    <textarea id="description" name="description" rows="3" class="mt-1.5 block w-full rounded-[8px] border border-line px-3.5 py-2.5 text-sm outline-none focus:border-emerald focus:ring-3 focus:ring-emerald/20">{{ old('description') }}</textarea>
                </div>
                <x-form.submit busy="Starting…">Start survey</x-form.submit>
            </form>
        </section>
    </div>
</x-layouts.app>
