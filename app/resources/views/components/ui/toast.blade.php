{{-- One toast. The toaster places it; resources/js/app.js times it out and closes it. --}}
<li data-toast role="status" class="toast pointer-events-auto flex items-start gap-3 rounded-[12px] border border-line bg-white py-3 pl-3.5 pr-2 text-sm text-navy shadow-[0_12px_32px_-12px_rgba(13,31,56,0.35)]">
    <span class="mt-px flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald text-white" aria-hidden="true">
        <svg viewBox="0 0 24 24" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5 9-10"/></svg>
    </span>
    <p data-toast-message class="min-w-0 flex-1 pt-px font-medium leading-snug">{{ $slot }}</p>
    <button type="button" data-toast-close class="-my-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-[6px] text-slate-muted hover:bg-mist hover:text-navy" aria-label="Dismiss">
        <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
    </button>
</li>
