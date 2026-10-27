@props(['program'])
{{-- One SK program as a card: a photo when the SK shared one, what it's for, and how far along it is. --}}
@php($photo = $program->evidence->first())
<a href="{{ route('programs.show', $program) }}" {{ $attributes->class(['group flex flex-col overflow-hidden rounded-[16px] border border-line bg-white transition-colors hover:border-emerald/50']) }}>
    <span class="relative block aspect-[16/9] overflow-hidden bg-navy">
        @if ($photo)
            <img src="{{ route('transparency.evidence', $photo) }}" alt="" loading="lazy" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.03]">
        @else
            <span class="flex h-full w-full flex-col justify-end bg-[radial-gradient(circle_at_85%_20%,rgba(22,132,91,0.6),transparent_60%)] p-5 text-white">
                <img src="{{ $program->barangay->logoUrl() }}" alt="" class="absolute -right-6 -top-6 h-32 w-32 opacity-[0.12]">
                <span class="font-display text-2xl font-semibold leading-tight">{{ $program->priority->center->label() }}</span>
            </span>
        @endif
        <span class="absolute left-3 top-3 rounded-full bg-white shadow-sm"><x-ui.ppa-status :ppa="$program" /></span>
    </span>
    <span class="flex flex-1 flex-col p-5">
        <span class="flex items-center gap-2 text-xs">
            <img src="{{ $program->barangay->logoUrl() }}" alt="" class="h-5 w-5 rounded-full border border-line bg-white object-contain">
            <span class="font-semibold text-emerald">SK {{ $program->barangay->name }}</span>
            <span class="text-slate-muted">{{ $program->priority->center->label() }}</span>
        </span>
        <span class="mt-2 font-display text-lg font-semibold leading-snug group-hover:text-emerald">{{ $program->title }}</span>
        <span class="mt-1 text-sm text-slate-muted">Goal: {{ $program->target }}</span>
        <span class="mt-auto block pt-4">
            <span class="flex items-center justify-between text-xs">
                <span class="text-slate-muted">{{ $program->starts_on->format('M j') }} – {{ $program->ends_on->format('M j, Y') }}</span>
                <span class="font-semibold tabular-nums">{{ $program->progress() }}% done</span>
            </span>
            <span class="mt-1.5 block h-1.5 overflow-hidden rounded-full bg-mist" aria-hidden="true"><span class="block h-full rounded-full bg-emerald" style="width: {{ $program->progress() }}%"></span></span>
        </span>
        @if (($program->open_events_count ?? 0) > 0)
            <span class="mt-3 inline-flex w-fit items-center gap-1.5 rounded-full bg-gold/20 px-2.5 py-1 text-xs font-semibold">
                <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                {{ trans_choice('{1} 1 event you can join|[2,*] :count events you can join', $program->open_events_count) }}
            </span>
        @endif
    </span>
</a>
