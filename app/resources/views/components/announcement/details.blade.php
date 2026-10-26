@props(['announcement'])
{{-- The when and where of a post, pulled from its caption's 🗓️ / 🕑 / 📍 lines. --}}
@php($details = $announcement->details())
@if ($details !== [])
    <dl {{ $attributes->class(['grid gap-2 rounded-[12px] bg-mist p-4 text-sm']) }}>
        @foreach ($details as $detail)
            <div class="flex items-start gap-3">
                <dt class="mt-0.5 shrink-0 text-emerald">
                    <span class="sr-only">{{ ['date' => 'Date', 'time' => 'Time', 'place' => 'Place'][$detail['kind']] }}</span>
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        @switch($detail['kind'])
                            @case('date')
                                <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
                                @break
                            @case('time')
                                <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>
                                @break
                            @default
                                <path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21Z"/><circle cx="12" cy="9.5" r="2.5"/>
                        @endswitch
                    </svg>
                </dt>
                <dd class="font-medium text-navy">{{ $detail['text'] }}</dd>
            </div>
        @endforeach
    </dl>
@endif
