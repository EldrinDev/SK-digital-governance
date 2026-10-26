@php
    use App\QuestionType;
@endphp
<x-layouts.app :title="$survey->title" :heading="$survey->title" :description="$survey->description ?? 'From your barangay SK. Your answers are anonymous.'">
    <form method="POST" action="{{ route('surveys.responses.store', $survey) }}" class="max-w-2xl space-y-5" novalidate>
        @csrf
        @foreach ($survey->questions as $question)
            @php($field = "answers.{$question->id}")
            <fieldset class="rounded-[12px] border border-line bg-white p-5">
                <legend class="sr-only">Question {{ $loop->iteration }}</legend>
                <p class="text-xs text-slate-muted">Question {{ $loop->iteration }} of {{ $loop->count }}</p>
                <p class="mt-0.5 font-medium" id="q-{{ $question->id }}">{{ $question->prompt }}</p>

                <div class="mt-3">
                    @switch($question->type)
                        @case(QuestionType::Text)
                            <textarea name="answers[{{ $question->id }}]" rows="3" aria-labelledby="q-{{ $question->id }}" class="block w-full rounded-[8px] border border-line px-3.5 py-2.5 text-sm outline-none focus:border-emerald focus:ring-3 focus:ring-emerald/20">{{ old($field) }}</textarea>
                            @break
                        @case(QuestionType::Choice)
                            <div class="space-y-2">
                                @foreach ($question->options as $option)
                                    <label class="flex cursor-pointer items-center gap-2.5 rounded-[8px] border border-line px-3 py-2 text-sm has-[:checked]:border-emerald has-[:checked]:bg-emerald/10">
                                        <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option }}" @checked(old($field) === $option) class="accent-emerald">
                                        {{ $option }}
                                    </label>
                                @endforeach
                            </div>
                            @break
                        @case(QuestionType::Rating)
                            <div class="flex gap-1.5">
                                @foreach (range(1, 5) as $score)
                                    <label class="cursor-pointer rounded-[8px] border border-line px-3.5 py-2 text-sm font-semibold has-[:checked]:border-gold has-[:checked]:bg-gold/20">
                                        <input type="radio" name="answers[{{ $question->id }}]" value="{{ $score }}" @checked((string) old($field) === (string) $score) class="sr-only">
                                        {{ $score }}<span aria-hidden="true">★</span><span class="sr-only"> out of 5</span>
                                    </label>
                                @endforeach
                            </div>
                            @break
                    @endswitch
                </div>
                @error($field)
                    <p class="mt-2 text-sm text-error">{{ $message }}</p>
                @enderror
            </fieldset>
        @endforeach

        <x-form.submit busy="Sending…" :block="false">Send my answers</x-form.submit>
    </form>
</x-layouts.app>
