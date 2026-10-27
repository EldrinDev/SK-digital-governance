{{-- One toast. The toaster places it; resources/js/app.js times it out and closes it. --}}
<li data-toast role="status" class="toast pointer-events-auto flex items-start gap-3 rounded-[12px] bg-emerald py-3 pl-3.5 pr-2 text-sm text-white shadow-[0_14px_32px_-12px_rgba(16,106,73,0.6)]">
    <span class="mt-px flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-white text-emerald" aria-hidden="true">
        <svg viewBox="0 0 24 24" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5 9-10"/></svg>
    </span>
    <p data-toast-message class="min-w-0 flex-1 pt-px font-medium leading-snug">{{ $slot }}</p>
    <button type="button" data-toast-close class="-my-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-[6px] text-white/80 hover:bg-white/15 hover:text-white focus-visible:outline-2 focus-visible:outline-white" aria-label="Dismiss">
        <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
    </button>
</li>
