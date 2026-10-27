@props([
    'route',
    'parameters' => [],
    'label' => 'Export',
])
@php
    use App\ExportFormat;

    $icons = [
        'xlsx' => ['#1d6f42', 'XLS'],
        'pdf' => ['#b91c1c', 'PDF'],
        'docx' => ['#2b579a', 'DOC'],
        'csv' => ['#475569', 'CSV'],
    ];
@endphp
{{-- A download menu offering the same report as Excel, PDF, Word or CSV. --}}
<details {{ $attributes->class(['relative']) }} data-dropdown data-export-menu>
    <summary class="inline-flex cursor-pointer list-none items-center gap-2 rounded-[8px] border border-line bg-white px-4 py-2.5 text-sm font-semibold hover:border-navy [&::-webkit-details-marker]:hidden">
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/></svg>
        {{ $label }}
        <svg viewBox="0 0 24 24" class="h-4 w-4 text-slate-muted" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
    </summary>
    <div class="absolute right-0 z-30 mt-2 w-72 rounded-[12px] border border-line bg-white p-1.5 shadow-lg shadow-navy/10">
        <p class="px-3 pb-1.5 pt-2 text-xs font-semibold text-slate-muted">Download as</p>
        @foreach (ExportFormat::cases() as $format)
            <a href="{{ route($route, [...$parameters, 'format' => $format->value]) }}" class="flex items-center gap-3 rounded-[8px] px-3 py-2 hover:bg-mist" download>
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-[6px] text-[0.6rem] font-bold tracking-wide text-white" style="background: {{ $icons[$format->value][0] }}" aria-hidden="true">{{ $icons[$format->value][1] }}</span>
                <span class="min-w-0">
                    <span class="block text-sm font-semibold text-navy">{{ $format->label() }} <span class="font-normal text-slate-muted">.{{ $format->value }}</span></span>
                    <span class="block text-xs text-slate-muted">{{ $format->hint() }}</span>
                </span>
            </a>
        @endforeach
    </div>
</details>
