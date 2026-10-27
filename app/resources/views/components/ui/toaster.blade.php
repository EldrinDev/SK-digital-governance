{{--
    Short confirmations that slide in at the corner and leave on their own: the "status" flashed
    by any action, plus anything scripts add with window.toast('Message').
--}}
<section aria-label="Notifications" class="pointer-events-none fixed inset-x-0 top-[4.5rem] z-[100] flex justify-center px-4 sm:inset-x-auto sm:right-0 sm:top-20 sm:justify-end sm:px-6 print:hidden">
    <ol data-toaster class="flex w-full max-w-sm flex-col gap-2" aria-live="polite" aria-relevant="additions">
        @if (session('status'))
            <x-ui.toast>{{ session('status') }}</x-ui.toast>
        @endif
    </ol>

    {{-- Copied by scripts for new toasts --}}
    <template data-toast-template>
        <x-ui.toast></x-ui.toast>
    </template>
</section>
