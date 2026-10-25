@props([
    'ppa',
])
@php
    use App\PpaStatus;

    $tone = match (true) {
        $ppa->isDelayed() => 'error',
        $ppa->status === PpaStatus::Completed => 'success',
        $ppa->status === PpaStatus::Ongoing => 'info',
        default => 'neutral',
    };
@endphp
<x-ui.badge :tone="$tone" {{ $attributes }}>
    {{ $ppa->status->label() }}@if ($ppa->isDelayed()) · Delayed @endif
</x-ui.badge>
