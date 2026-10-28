@props([
    'title',
    'steps' => [],
    'current' => 1,
])
{{-- A short numbered guide to a process. The step this page handles is highlighted. --}}
<section {{ $attributes }} aria-label="{{ $title }}">
    <h2 class="font-display text-base font-semibold text-navy">{{ $title }}</h2>
    <ol class="mt-4">
        @foreach ($steps as $step)
            @php
                $isCurrent = $loop->iteration === $current;
            @endphp
            <li class="relative flex gap-3.5 pb-5 last:pb-0" @if ($isCurrent) aria-current="step" @endif>
                @unless ($loop->last)
                    <span class="absolute left-3 top-7 bottom-0 w-px bg-line" aria-hidden="true"></span>
                @endunless
                <span @class([
                    'relative flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-semibold',
                    'bg-emerald text-white' => $isCurrent,
                    'border border-line bg-white text-slate-muted' => ! $isCurrent,
                ])>{{ $loop->iteration }}</span>
                <span class="pt-0.5 text-sm leading-relaxed">
                    <span @class(['font-semibold', 'text-navy' => $isCurrent, 'text-navy/80' => ! $isCurrent])>{{ $step[0] }}</span>
                    <span class="block text-slate-muted">{{ $step[1] }}</span>
                </span>
            </li>
        @endforeach
    </ol>
</section>
