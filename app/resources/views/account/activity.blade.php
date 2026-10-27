@php
    $tabs = [null => 'All', 'events' => 'Events', 'surveys' => 'Surveys', 'messages' => 'Messages'];
    $kinds = [
        'attended' => ['label' => 'Attended', 'tone' => 'bg-emerald/10 text-emerald', 'icon' => '<path d="m5 12 5 5 9-10"/>'],
        'missed' => ['label' => 'Missed', 'tone' => 'bg-error/10 text-[#b91c1c]', 'icon' => '<path d="M18 6 6 18M6 6l12 12"/>'],
        'upcoming' => ['label' => 'Registered', 'tone' => 'bg-gold/20 text-[#92400e]', 'icon' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>'],
        'survey' => ['label' => 'Survey', 'tone' => 'bg-navy/10 text-navy', 'icon' => '<path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/>'],
        'replied' => ['label' => 'Replied', 'tone' => 'bg-info/10 text-info', 'icon' => '<path d="M21 12a8 8 0 0 1-11.6 7.1L4 20.5l1.4-4.8A8 8 0 1 1 21 12Z"/>'],
        'waiting' => ['label' => 'Waiting', 'tone' => 'bg-mist text-slate-muted', 'icon' => '<path d="M21 12a8 8 0 0 1-11.6 7.1L4 20.5l1.4-4.8A8 8 0 1 1 21 12Z"/>'],
    ];
@endphp
<x-layouts.app title="My activity" heading="My activity" description="Everything you’ve joined, answered and sent to your SK, newest first.">
    {{-- The numbers that sum it up --}}
    <dl class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-[14px] border border-line bg-white p-5">
            <dt class="text-sm text-slate-muted">Events attended</dt>
            <dd class="mt-1 font-display text-3xl font-semibold tabular-nums">{{ $summary['attended'] }}</dd>
            <dd class="text-xs text-slate-muted">{{ $summary['byQr'] }} checked in by QR scan</dd>
        </div>
        <div class="rounded-[14px] border border-line bg-white p-5">
            <dt class="text-sm text-slate-muted">Attendance rate</dt>
            <dd class="mt-1 font-display text-3xl font-semibold tabular-nums">{{ $summary['rate'] !== null ? $summary['rate'].'%' : '—' }}</dd>
            <dd class="text-xs text-slate-muted">{{ $summary['missed'] ? $summary['missed'].' registered but missed' : 'of events you registered for' }}</dd>
        </div>
        <div class="rounded-[14px] border border-line bg-white p-5">
            <dt class="text-sm text-slate-muted">Surveys answered</dt>
            <dd class="mt-1 font-display text-3xl font-semibold tabular-nums">{{ $summary['surveys'] }}</dd>
        </div>
        <div class="rounded-[14px] border border-line bg-white p-5">
            <dt class="text-sm text-slate-muted">Messages to your SK</dt>
            <dd class="mt-1 font-display text-3xl font-semibold tabular-nums">{{ $summary['messages'] }}</dd>
        </div>
    </dl>

    <nav class="mt-6 flex flex-wrap gap-1.5" aria-label="Show">
        @foreach ($tabs as $value => $label)
            @php
                $isActive = $type === ($value ?: null);
                $count = $value ? ($counts[$value] ?? 0) : $counts->sum();
            @endphp
            <a href="{{ route('account.activity', array_filter(['type' => $value ?: null])) }}" @class([
                'inline-flex items-center gap-1.5 rounded-full border px-3.5 py-1.5 text-sm font-semibold',
                'border-navy bg-navy text-white' => $isActive,
                'border-line bg-white text-navy/75 hover:border-navy/40' => ! $isActive,
            ]) @if ($isActive) aria-current="page" @endif>
                {{ $label }}
                <span @class(['rounded-full px-1.5 text-xs tabular-nums', 'bg-white/20' => $isActive, 'bg-mist text-slate-muted' => ! $isActive])>{{ $count }}</span>
            </a>
        @endforeach
    </nav>

    {{-- The timeline, by month --}}
    <div class="mt-6 max-w-3xl space-y-8">
        @forelse ($months as $month => $items)
            <section aria-labelledby="month-{{ $loop->index }}">
                <h2 id="month-{{ $loop->index }}" class="font-display text-lg font-semibold">{{ $month }}</h2>
                <ol class="mt-3 overflow-hidden rounded-[14px] border border-line bg-white">
                    @foreach ($items as $item)
                        @php($kind = $kinds[$item['kind']])
                        <li class="flex gap-4 border-b border-line px-5 py-4 last:border-b-0">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-[10px] {{ $kind['tone'] }}" aria-hidden="true">
                                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $kind['icon'] !!}</svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="flex flex-wrap items-center justify-between gap-2 text-xs">
                                    <span class="rounded-full px-2 py-0.5 font-semibold {{ $kind['tone'] }}">{{ $kind['label'] }}</span>
                                    <time datetime="{{ $item['date']->toIso8601String() }}" class="text-slate-muted">{{ $item['date']->format('M j, Y') }}</time>
                                </p>
                                <p class="mt-1 font-medium leading-snug">
                                    @if ($item['url'])
                                        <a href="{{ $item['url'] }}" class="hover:text-emerald">{{ $item['title'] }}</a>
                                    @else
                                        {{ $item['title'] }}
                                    @endif
                                </p>
                                @if ($item['detail'])
                                    <p class="mt-0.5 text-sm text-slate-muted">{{ $item['detail'] }}</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>
        @empty
            <div class="rounded-[16px] border border-dashed border-line bg-white p-10 text-center">
                <p class="font-display text-lg font-semibold">Nothing here yet</p>
                <p class="mt-1 text-sm text-slate-muted">Register for an event, answer a survey or message your SK, and it shows up here.</p>
                <a href="{{ route('events.index') }}" class="mt-5 inline-flex rounded-[8px] bg-emerald px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-dark">Find an event</a>
            </div>
        @endforelse
    </div>
</x-layouts.app>
