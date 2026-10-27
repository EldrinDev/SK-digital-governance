@php
    // What the last action flashed: its message, and what kind it was (success unless the action said otherwise).
    $kind = in_array(session('status_kind'), ['success', 'info', 'warning', 'error'], true) ? session('status_kind') : 'success';
    // A failed action's validation errors, read from the session so this works inside any layout.
    $failedWith = session('errors')?->getBag('default');
    $failure = $failedWith?->any() ? ($failedWith->count() === 1 ? $failedWith->first() : 'Some details need fixing. Check the highlighted fields.') : null;
@endphp
{{--
    Short messages that swipe in at the top right and leave on their own: the "status" flashed
    by any action, a failed action's error, plus anything scripts add with window.toast().
--}}
<section aria-label="Notifications" class="pointer-events-none fixed inset-x-0 top-0 z-[100] flex justify-center overflow-hidden p-3 sm:inset-x-auto sm:right-0 sm:justify-end sm:p-4 print:hidden">
    <ol data-toaster class="flex w-full max-w-sm flex-col gap-2" aria-live="polite" aria-relevant="additions">
        @if (session('status'))
            <x-ui.toast :kind="$kind">{{ session('status') }}</x-ui.toast>
        @endif
        @if ($failure)
            <x-ui.toast kind="error">{{ $failure }}</x-ui.toast>
        @endif
    </ol>

    {{-- Copied by scripts for new toasts, one per kind --}}
    @foreach (['success', 'info', 'warning', 'error'] as $templateKind)
        <template data-toast-template="{{ $templateKind }}">
            <x-ui.toast :kind="$templateKind"></x-ui.toast>
        </template>
    @endforeach
</section>
