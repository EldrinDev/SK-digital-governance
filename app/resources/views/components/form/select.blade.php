@props([
    'name',
    'label',
    'placeholder' => 'Choose one',
])
@php
    $hasError = $errors->has($name);
@endphp
<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-navy">{{ $label }}</label>
    <select
        id="{{ $name }}"
        name="{{ $name }}"
        @if ($hasError) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
        {{ $attributes->class([
            'mt-1.5 block w-full rounded-[8px] border bg-white px-3.5 py-2.5 text-navy shadow-xs outline-none transition focus:ring-3',
            'border-line focus:border-emerald focus:ring-emerald/20' => ! $hasError,
            'border-error focus:border-error focus:ring-error/20' => $hasError,
        ]) }}
    >
        <option value="" disabled @selected(old($name) === null)>{{ $placeholder }}</option>
        {{ $slot }}
    </select>
    @error($name)
        <p id="{{ $name }}-error" class="mt-1.5 flex items-start gap-1.5 text-sm text-error">
            <svg viewBox="0 0 24 24" class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
            {{ $message }}
        </p>
    @enderror
</div>
