@props([
    'label',
    'value',
    'icon' => 'chart',
    'hint' => null,
    'href' => null,
    'tone' => 'neutral',
])
@php
    /*
     * One headline number for a dashboard. The icon names what is counted; the tone only
     * changes when the number needs attention (delays, failed logins), never for decoration.
     */
    $icons = [
        'chart' => '<path d="M3 3v18h18"/><path d="M7 15v2M12 10v7M17 6v11"/>',
        'clipboard' => '<path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="m9 14 2 2 4-4"/>',
        'check' => '<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'peso' => '<path d="M7 20V4h6a4 4 0 0 1 0 8H7"/><path d="M4 8h14M4 11h14"/>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
        'shield' => '<path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6l-8-3Z"/>',
        'building' => '<path d="M3 21h18M5 21V7l7-4 7 4v14"/><path d="M9 21v-6h6v6"/>',
        'survey' => '<path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/>',
    ];
    $isAlert = $tone === 'danger';
    $tag = $href ? 'a' : 'div';
@endphp
<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->class([
    'flex flex-col rounded-[14px] border bg-white p-5',
    'border-error/40' => $isAlert,
    'border-line' => ! $isAlert,
    'transition-colors hover:border-navy/30' => $href && ! $isAlert,
    'transition-colors hover:border-error' => $href && $isAlert,
]) }}>
    <span class="flex items-center justify-between gap-3">
        <span class="text-sm text-slate-muted">{{ $label }}</span>
        <span @class([
            'flex h-9 w-9 items-center justify-center rounded-[10px]',
            'bg-error/10 text-[#b91c1c]' => $isAlert,
            'bg-emerald/10 text-emerald' => ! $isAlert,
        ])>
            <svg viewBox="0 0 24 24" class="h-[1.15rem] w-[1.15rem]" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons[$icon] ?? $icons['chart'] !!}</svg>
        </span>
    </span>
    <span @class(['mt-2 font-display text-3xl font-semibold tabular-nums', 'text-[#b91c1c]' => $isAlert])>{{ $value }}</span>
    @if ($hint)
        <span class="mt-1 text-xs text-slate-muted">{{ $hint }}</span>
    @endif
    {{ $slot }}
</{{ $tag }}>
