@props([
    'items',
    'label',
    'max' => 100,
])
{{--
    Horizontal bars, one per row, for comparing a single measure across barangays.
    Each item: ['label' => string, 'value' => int|float, 'display' => string].
    Values are printed as text so the bar is never the only way to read them.
--}}
@php($max = max($max, 1))
<ul class="space-y-3" aria-label="{{ $label }}">
    @foreach ($items as $item)
        @php($width = max(0, min(100, $item['value'] / $max * 100)))
        <li class="grid grid-cols-[minmax(0,8rem)_minmax(0,1fr)_4.5rem] items-center gap-3 text-sm" title="{{ $item['label'] }}: {{ $item['display'] }}">
            <span class="truncate text-navy/80">{{ $item['label'] }}</span>
            <span class="h-2.5 overflow-hidden rounded-full bg-mist" aria-hidden="true">
                <span class="block h-full rounded-full bg-emerald" style="width: {{ $width }}%"></span>
            </span>
            <span class="text-right font-medium tabular-nums">{{ $item['display'] }}</span>
        </li>
    @endforeach
</ul>
