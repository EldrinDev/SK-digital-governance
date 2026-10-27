@props([
    'event',
    'memberCode',
    'label' => 'Your next event',
    'surface' => 'bg-white',
])
@php
    use App\QrCode;

    $starts = $event->starts_at;
    $when = $starts->isToday() ? 'Today' : ($starts->isTomorrow() ? 'Tomorrow' : 'In '.(int) ceil(now()->diffInDays($starts)).' days');
@endphp
{{--
    The youth's pass for an event they registered for: the event on the left, and a tear-off stub
    with the QR of their digital ID, which an officer scans at the registration table.
--}}
<div {{ $attributes->class(['drop-shadow-[0_18px_28px_rgba(13,31,56,0.22)]']) }}>
    <div class="ticket-notched flex min-w-0 rounded-[14px] {{ $surface }}">
        <a href="{{ route('events.show', $event) }}" class="group min-w-0 flex-1 px-5 py-4">
            <span class="flex items-center justify-between gap-3 text-xs font-semibold">
                <span class="text-slate-muted">{{ $label }}</span>
                @unless ($event->hasStarted())
                    <span class="text-emerald">{{ $when }}</span>
                @endunless
            </span>
            <span class="mt-2 line-clamp-2 block font-display text-[1.05rem] font-semibold leading-snug text-navy group-hover:text-emerald">{{ $event->title }}</span>
            <span class="mt-2 block text-sm font-medium text-navy/80">{{ $starts->format('D, M j') }} at {{ $starts->format('g:i A') }}</span>
            <span class="block truncate text-sm text-slate-muted">{{ $event->venue }}</span>
        </a>
        <div class="flex w-[7.5rem] shrink-0 flex-col items-center justify-center gap-1.5 border-l-2 border-dashed border-line px-3 py-4">
            <a href="{{ route('account.id') }}" class="block w-[4.6rem] rounded-[6px] bg-white p-1 [&_svg]:h-auto [&_svg]:w-full" title="Open my digital ID">
                {{ QrCode::svg(route('youth-id.verify', $memberCode), 160) }}
                <span class="sr-only">Open my digital ID to show at check-in</span>
            </a>
            <span class="text-center text-[0.65rem] font-medium leading-tight text-slate-muted">Show at check-in</span>
        </div>
    </div>
</div>
