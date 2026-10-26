@props(['event'])
{{-- How full the event is: a meter when there is a limit, a plain count when there isn't. --}}
@php
    $taken = $event->registrations_count ?? $event->registrations()->count();
    $percent = $event->capacity ? min(100, (int) round($taken / $event->capacity * 100)) : null;
    $almostFull = $percent !== null && $percent >= 80 && ! $event->isFull();
@endphp
<div {{ $attributes }}>
    @if ($event->capacity === null)
        <p class="text-sm text-slate-muted">{{ trans_choice('{0} Be the first to register|{1} 1 youth registered|[2,*] :count youth registered', $taken) }} · open registration</p>
    @else
        <div class="flex items-baseline justify-between gap-3 text-sm">
            <span @class([
                'font-semibold',
                'text-[#b91c1c]' => $event->isFull(),
                'text-[#b45309]' => $almostFull,
                'text-navy' => ! $event->isFull() && ! $almostFull,
            ])>
                @if ($event->isFull())
                    Full
                @elseif ($almostFull)
                    {{ trans_choice('{1} Only 1 place left|[2,*] Only :count places left', $event->spotsLeft()) }}
                @else
                    {{ $event->spotsLeft() }} places left
                @endif
            </span>
            <span class="text-xs tabular-nums text-slate-muted">{{ $taken }} / {{ $event->capacity }}</span>
        </div>
        <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-mist" role="progressbar" aria-valuenow="{{ $taken }}" aria-valuemin="0" aria-valuemax="{{ $event->capacity }}" aria-label="Places taken">
            <div @class(['h-full rounded-full', 'bg-[#b91c1c]' => $event->isFull(), 'bg-gold' => $almostFull, 'bg-emerald' => ! $event->isFull() && ! $almostFull]) style="width: {{ $percent }}%"></div>
        </div>
    @endif
</div>
