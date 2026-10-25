@props([
    'plan',
])
@if ($plan->isApproved())
    <x-ui.badge tone="success" {{ $attributes }}>
        <svg viewBox="0 0 24 24" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
        Approved
    </x-ui.badge>
@else
    <x-ui.badge tone="warning" {{ $attributes }}>Draft</x-ui.badge>
@endif
