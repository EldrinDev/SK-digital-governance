@props([
    'name' => 'credentials',
])
@error($name)
    <div role="alert" {{ $attributes->class('mb-6 flex items-start gap-3 rounded-[12px] border border-error/30 bg-error/5 p-4 text-sm leading-relaxed text-navy') }}>
        <svg viewBox="0 0 24 24" class="mt-0.5 h-5 w-5 shrink-0 text-error" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
        <p>{{ $message }}</p>
    </div>
@enderror
