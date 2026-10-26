@props([
    'items',
    'label',
    'max' => 100,
    'limit' => null,
])
{{--
    Horizontal bars, one per row, for comparing a single measure across barangays.
    Each item: ['label' => string, 'value' => int|float, 'display' => string].
    Values are printed as text so the bar is never the only way to read them.
    With a limit, the highest values are shown and the rest are summed up in one line.
--}}
@php
    $max = max($max, 1);
    $items = collect($items);
    $hidden = collect();

    if ($limit !== null && $items->count() > $limit) {
        $items = $items->sortByDesc('value')->values();
        $hidden = $items->slice($limit);
        $items = $items->take($limit);
    }
@endphp
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
@if ($hidden->isNotEmpty())
    <p class="mt-3 text-xs text-slate-muted" title="{{ $hidden->pluck('label')->implode(', ') }}">
        {{ trans_choice('{1} and 1 more barangay|[2,*] and :count more barangays', $hidden->count()) }}{{ $hidden->every(fn ($item) => $item['value'] == 0) ? ' at '.$hidden->first()['display'] : '' }}
    </p>
@endif
