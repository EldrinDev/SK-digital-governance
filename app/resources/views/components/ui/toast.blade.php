@props(['kind' => 'success'])
@php
    // Each kind of message has its own color and mark, so people can tell at a glance whether
    // something worked, simply changed, needs a second look, or failed.
    $styles = [
        'success' => ['surface' => 'bg-emerald text-white', 'mark' => 'bg-white text-emerald', 'close' => 'text-white/80 hover:bg-white/15 hover:text-white focus-visible:outline-white', 'timer' => 'bg-white/45', 'duration' => 5000],
        'info' => ['surface' => 'bg-navy text-white', 'mark' => 'bg-white text-navy', 'close' => 'text-white/75 hover:bg-white/15 hover:text-white focus-visible:outline-white', 'timer' => 'bg-white/40', 'duration' => 5000],
        'warning' => ['surface' => 'bg-gold text-navy', 'mark' => 'bg-navy text-gold', 'close' => 'text-navy/70 hover:bg-navy/10 hover:text-navy focus-visible:outline-navy', 'timer' => 'bg-navy/30', 'duration' => 6500],
        'error' => ['surface' => 'bg-[#b91c1c] text-white', 'mark' => 'bg-white text-[#b91c1c]', 'close' => 'text-white/80 hover:bg-white/15 hover:text-white focus-visible:outline-white', 'timer' => 'bg-white/45', 'duration' => 8000],
    ];
    $style = $styles[$kind] ?? $styles['success'];
@endphp
{{-- One toast. The toaster places it; resources/js/app.js times it out and closes it. --}}
<li data-toast data-kind="{{ $kind }}" data-duration="{{ $style['duration'] }}" role="{{ $kind === 'error' ? 'alert' : 'status' }}" style="--toast-duration: {{ $style['duration'] }}ms" class="toast pointer-events-auto relative flex items-start gap-3 overflow-hidden rounded-[12px] py-3 pl-3.5 pr-2 text-sm shadow-[0_14px_32px_-12px_rgba(13,31,56,0.55)] {{ $style['surface'] }}">
    <span class="mt-px flex h-5 w-5 shrink-0 items-center justify-center rounded-full {{ $style['mark'] }}" aria-hidden="true">
        @switch($kind)
            @case('info')
                <svg viewBox="0 0 24 24" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round"><path d="M12 11v6M12 7h.01"/></svg>
                @break
            @case('warning')
                <svg viewBox="0 0 24 24" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round"><path d="M12 6v7M12 18h.01"/></svg>
                @break
            @case('error')
                <svg viewBox="0 0 24 24" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round"><path d="M7 7l10 10M17 7 7 17"/></svg>
                @break
            @default
                <svg viewBox="0 0 24 24" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5 9-10"/></svg>
        @endswitch
    </span>
    <p data-toast-message class="min-w-0 flex-1 pt-px font-medium leading-snug">{{ $slot }}</p>
    <button type="button" data-toast-close class="-my-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-[6px] focus-visible:outline-2 {{ $style['close'] }}" aria-label="Dismiss">
        <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
    </button>
    {{-- Time left before it closes; pauses while the pointer or focus is on the toast --}}
    <span class="toast-timer absolute inset-x-0 bottom-0 h-[3px] origin-left {{ $style['timer'] }}" aria-hidden="true"></span>
</li>
