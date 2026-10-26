<x-layouts.app :title="$survey->title" :heading="$survey->title" :description="$survey->statusLabel().' · '.trans_choice(':count response|:count responses', $survey->responses_count).($survey->description ? ' · '.$survey->description : '')">
    <x-slot:actions>
        @can('changeStatus', $survey)
            @if ($survey->isDraft() || $survey->isOpen())
                <form method="POST" action="{{ route('officer.surveys.status.update', $survey) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="action" value="{{ $survey->isDraft() ? 'open' : 'close' }}">
                    <button type="submit" @class([
                        'rounded-[8px] px-4 py-2.5 text-sm font-semibold',
                        'bg-emerald text-white hover:bg-emerald-dark' => $survey->isDraft(),
                        'border border-line bg-white hover:border-navy' => $survey->isOpen(),
                    ])>{{ $survey->isDraft() ? 'Open for answers' : 'Close survey' }}</button>
                </form>
            @endif
        @endcan
    </x-slot:actions>

    <p class="mb-6 text-sm"><a href="{{ route('officer.surveys.index') }}" class="font-medium text-slate-muted hover:text-emerald">&larr; All surveys</a></p>

    @error('survey')
        <p role="alert" class="mb-6 rounded-[12px] border border-warning/40 bg-warning/10 p-4 text-sm">{{ $message }}</p>
    @enderror

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <ol class="space-y-4">
            @forelse ($survey->questions as $question)
                @php($summary = $question->summary())
                <li class="rounded-[12px] border border-line bg-white p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs text-slate-muted">Question {{ $loop->iteration }} · {{ $question->type->label() }}</p>
                            <h2 class="mt-0.5 font-medium">{{ $question->prompt }}</h2>
                        </div>
                        @can('update', $survey)
                            <form method="POST" action="{{ route('officer.surveys.questions.destroy', [$survey, $question]) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-semibold text-slate-muted hover:text-error">Remove<span class="sr-only"> question {{ $loop->iteration }}</span></button>
                            </form>
                        @endcan
                    </div>

                    @if ($survey->isDraft())
                        @if ($question->options)
                            <ul class="mt-3 list-disc pl-5 text-sm text-navy/80">
                                @foreach ($question->options as $option)
                                    <li>{{ $option }}</li>
                                @endforeach
                            </ul>
                        @endif
                    @elseif ($summary['total'] === 0)
                        <p class="mt-3 text-sm text-slate-muted">No answers yet.</p>
                    @elseif ($summary['type'] === 'text')
                        <ul class="mt-3 max-h-64 space-y-2 overflow-y-auto text-sm">
                            @foreach ($summary['texts'] as $text)
                                <li class="rounded-[8px] bg-mist p-2.5">{{ $text }}</li>
                            @endforeach
                        </ul>
                    @else
                        @if ($summary['type'] === 'rating')
                            <p class="mt-2 text-sm">Average <span class="font-semibold">{{ $summary['average'] }} / 5</span></p>
                        @endif
                        <dl class="mt-3 space-y-2 text-sm">
                            @foreach ($summary['counts'] as $label => $count)
                                @php($share = (int) round($count / $summary['total'] * 100))
                                <div class="grid grid-cols-[8rem_minmax(0,1fr)_4.5rem] items-center gap-3">
                                    <dt class="truncate">{{ $summary['type'] === 'rating' ? $label.' ★' : $label }}</dt>
                                    <dd class="h-2.5 overflow-hidden rounded-full bg-mist" aria-hidden="true"><div class="h-full rounded-full bg-emerald" style="width: {{ $share }}%"></div></dd>
                                    <dd class="text-right text-xs text-slate-muted">{{ $count }} ({{ $share }}%)</dd>
                                </div>
                            @endforeach
                        </dl>
                    @endif
                </li>
            @empty
                <li class="rounded-[12px] border border-dashed border-line bg-white p-8 text-center text-sm text-slate-muted">No questions yet. Add the first one.</li>
            @endforelse
        </ol>

        @can('update', $survey)
            <section class="h-fit rounded-[12px] border border-line bg-white p-5" aria-labelledby="add-question">
                <h2 id="add-question" class="font-display text-lg font-semibold">Add a question</h2>
                <form method="POST" action="{{ route('officer.surveys.questions.store', $survey) }}" class="mt-4 space-y-4" novalidate>
                    @csrf
                    <x-form.input name="prompt" label="Question" placeholder="e.g. Which sport should the league add?" required />
                    <x-form.select name="type" label="Answer type" required>
                        @foreach ($types as $type)
                            <option value="{{ $type->value }}" @selected(old('type', 'choice') === $type->value)>{{ $type->label() }}</option>
                        @endforeach
                    </x-form.select>
                    <div>
                        <label for="options" class="block text-sm font-medium">Choices <span class="font-normal text-slate-muted">(multiple choice only, one per line)</span></label>
                        <textarea id="options" name="options" rows="4" class="mt-1.5 block w-full rounded-[8px] border border-line px-3.5 py-2.5 text-sm outline-none focus:border-emerald focus:ring-3 focus:ring-emerald/20">{{ old('options') }}</textarea>
                        @error('options')
                            <p class="mt-1.5 text-sm text-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <x-form.submit busy="Adding…">Add question</x-form.submit>
                </form>
            </section>
        @endcan
    </div>
</x-layouts.app>
