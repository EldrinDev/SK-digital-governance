@props([
    'tone' => 'neutral',
])
<span {{ $attributes->class([
    'inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold',
    'bg-mist text-navy/80' => $tone === 'neutral',
    'bg-success/15 text-[#047857]' => $tone === 'success',
    'bg-warning/15 text-[#b45309]' => $tone === 'warning',
    'bg-error/10 text-[#b91c1c]' => $tone === 'error',
    'bg-info/10 text-[#1d4ed8]' => $tone === 'info',
    'bg-navy text-white' => $tone === 'navy',
]) }}>{{ $slot }}</span>
