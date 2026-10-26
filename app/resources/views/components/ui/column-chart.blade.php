@props([
    'items',
    'label',
    'unit' => '',
])
{{--
    Single-series columns over time. Each item: ['label' => string, 'value' => int].
    A visually hidden table carries the same numbers for screen readers.
--}}
@php($max = max(1, collect($items)->max('value')))
<figure>
    <div class="flex h-40 items-end gap-2 border-b border-line" aria-hidden="true">
        @foreach ($items as $item)
            <div class="group flex h-full flex-1 flex-col items-center justify-end" title="{{ $item['label'] }}: {{ $item['value'] }} {{ $unit }}">
                <span class="mb-1 text-xs font-medium tabular-nums text-navy/70 opacity-0 transition-opacity group-hover:opacity-100 @if ($loop->last) opacity-100 @endif">{{ $item['value'] }}</span>
                <span class="w-full max-w-10 rounded-t-[4px] bg-emerald/80 transition-colors group-hover:bg-emerald" style="height: {{ $item['value'] > 0 ? max(3, $item['value'] / $max * 100) : 0 }}%"></span>
            </div>
        @endforeach
    </div>
    <div class="mt-1.5 flex gap-2 text-center text-xs text-slate-muted" aria-hidden="true">
        @foreach ($items as $item)
            <span class="flex-1">{{ $item['label'] }}</span>
        @endforeach
    </div>
    <table class="sr-only">
        <caption>{{ $label }}</caption>
        <thead><tr><th scope="col">Month</th><th scope="col">{{ ucfirst($unit) }}</th></tr></thead>
        <tbody>
            @foreach ($items as $item)
                <tr><td>{{ $item['label'] }}</td><td>{{ $item['value'] }}</td></tr>
            @endforeach
        </tbody>
    </table>
</figure>
