@props([
    'name',
    'label',
    'type' => 'text',
    'hint' => null,
])
@php
    $hasError = $errors->has($name);
    $isPassword = $type === 'password';
    $describedBy = collect([
        $hint ? "{$name}-hint" : null,
        $hasError ? "{$name}-error" : null,
    ])->filter()->implode(' ');
@endphp
<div @if ($isPassword) data-password-field @endif>
    <label for="{{ $name }}" class="block text-sm font-medium text-navy">{{ $label }}</label>
    <div class="relative mt-1.5">
        <input
            id="{{ $name }}"
            name="{{ $name }}"
            type="{{ $type }}"
            @if (! $isPassword) value="{{ old($name) }}" @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            @if ($hasError) aria-invalid="true" @endif
            {{ $attributes->class([
                'block w-full rounded-[8px] border bg-white px-3.5 py-2.5 text-navy shadow-xs outline-none transition placeholder:text-slate-muted/70 focus:ring-3',
                'pr-16' => $isPassword,
                'border-line focus:border-emerald focus:ring-emerald/20' => ! $hasError,
                'border-error focus:border-error focus:ring-error/20' => $hasError,
            ]) }}
        >
        @if ($isPassword)
            <button
                type="button"
                data-password-toggle
                aria-controls="{{ $name }}"
                aria-pressed="false"
                class="absolute inset-y-1.5 right-1.5 rounded-[6px] px-2.5 text-xs font-semibold text-navy/70 transition-colors hover:bg-mist hover:text-navy"
            >Show</button>
        @endif
    </div>
    @if ($isPassword)
        <p data-caps-warning hidden aria-live="polite" class="mt-1.5 flex items-center gap-1.5 text-xs font-medium text-warning">
            <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 4-8 8h5v4h6v-4h5l-8-8Z"/><path d="M9 20h6"/></svg>
            Caps Lock is on
        </p>
    @endif
    @if ($hint)
        <p id="{{ $name }}-hint" class="mt-1.5 text-xs text-slate-muted">{{ $hint }}</p>
    @endif
    @error($name)
        <p id="{{ $name }}-error" class="mt-1.5 flex items-start gap-1.5 text-sm text-error">
            <svg viewBox="0 0 24 24" class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
            {{ $message }}
        </p>
    @enderror
</div>
