@props([
    'event',
    'size' => 'md',
])
@php
    use App\EventKind;

    // Consultations wear gold so youth can tell "have your say" apart from activities at a glance.
    $isConsultation = $event->kind === EventKind::Consultation;
@endphp
<span {{ $attributes->class([
    'flex shrink-0 flex-col items-center justify-center rounded-[10px] text-center leading-none',
    'bg-gold text-navy' => $isConsultation,
    'bg-emerald text-white' => ! $isConsultation,
    'h-14 w-14 gap-0.5' => $size === 'sm',
    'h-20 w-20 gap-1' => $size === 'md',
    'h-28 w-28 gap-1.5' => $size === 'lg',
]) }}>
    <span @class(['font-semibold', 'text-[0.6rem]' => $size === 'sm', 'text-xs' => $size === 'md', 'text-sm' => $size === 'lg'])>{{ $event->starts_at->format('D') }}</span>
    <span @class(['font-display font-semibold', 'text-xl' => $size === 'sm', 'text-3xl' => $size === 'md', 'text-5xl' => $size === 'lg'])>{{ $event->starts_at->format('j') }}</span>
    <span @class(['font-semibold', 'text-[0.6rem]' => $size === 'sm', 'text-xs' => $size === 'md', 'text-sm' => $size === 'lg'])>{{ $event->starts_at->format('M') }}</span>
</span>
