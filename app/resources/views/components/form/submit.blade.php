@props([
    'busy',
    'tone' => 'emerald',
    'block' => true,
])
<button
    type="submit"
    data-busy-label="{{ $busy }}"
    {{ $attributes->class([
        'flex items-center justify-center gap-2 rounded-[8px] px-5 py-3 text-sm font-semibold text-white transition-colors disabled:cursor-wait disabled:opacity-80',
        'w-full' => $block,
        'bg-emerald shadow-sm shadow-emerald/30 hover:bg-emerald-dark' => $tone === 'emerald',
        'bg-navy hover:bg-navy-deep' => $tone === 'navy',
    ]) }}
>
    <svg data-busy-spinner hidden viewBox="0 0 24 24" class="h-4 w-4 animate-spin" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".3" stroke-width="3"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
    <span data-busy-text>{{ $slot }}</span>
</button>
