@props([
    'announcement',
    'size' => 'card',
])
@php
    use App\AnnouncementCategory;

    /*
     * Posts with a photo show it. Posts without one get a "pubmat" tile, the poster style
     * SK pages use on social media. The category is set large as the poster's word, with
     * the date from the caption under it, so the headline beside the tile isn't repeated.
     */
    $tone = match ($announcement->category) {
        AnnouncementCategory::Reminder => ['bg' => 'bg-gold', 'text' => 'text-navy', 'muted' => 'text-navy/70', 'rule' => 'bg-navy'],
        AnnouncementCategory::Event => ['bg' => 'bg-emerald', 'text' => 'text-white', 'muted' => 'text-white/80', 'rule' => 'bg-gold'],
        AnnouncementCategory::Recognition => ['bg' => 'bg-navy-deep', 'text' => 'text-white', 'muted' => 'text-white/75', 'rule' => 'bg-gold'],
        default => ['bg' => 'bg-navy', 'text' => 'text-white', 'muted' => 'text-white/75', 'rule' => 'bg-gold'],
    };
    $date = collect($announcement->details())->firstWhere('kind', 'date')['text'] ?? null;
@endphp
@if ($announcement->image_path)
    <img
        src="{{ route('announcements.image', $announcement) }}"
        alt=""
        loading="lazy"
        {{ $attributes->class(['h-full w-full object-cover']) }}
    >
@elseif ($size === 'thumb')
    <span {{ $attributes->class(['flex h-full w-full flex-col items-center justify-center gap-1 p-1.5 text-center', $tone['bg'], $tone['text']]) }} aria-hidden="true">
        <img src="{{ $announcement->barangay->logoUrl() }}" alt="" class="h-1/2 max-h-12 w-auto object-contain">
        <span class="max-w-full truncate px-0.5 text-[0.55rem] font-semibold leading-none">{{ $announcement->category->label() }}</span>
    </span>
@else
    <div {{ $attributes->class(['relative flex h-full w-full flex-col justify-between overflow-hidden', $tone['bg'], $tone['text'], 'p-7 sm:p-8' => $size === 'feature', 'p-5' => $size !== 'feature']) }} aria-hidden="true">
        <img src="{{ $announcement->barangay->logoUrl() }}" alt="" @class([
            'pointer-events-none absolute opacity-[0.14]',
            '-right-16 -top-12 h-80 w-80' => $size === 'feature',
            '-right-10 -top-8 h-48 w-48' => $size !== 'feature',
        ])>
        <img src="{{ $announcement->barangay->logoUrl() }}" alt="" @class(['relative rounded-full bg-white object-contain p-0.5', 'h-11 w-11' => $size === 'feature', 'h-9 w-9' => $size !== 'feature'])>
        <div class="relative">
            <span @class(['block h-1.5 rounded-full', $tone['rule'], 'mb-4 w-14' => $size === 'feature', 'mb-3 w-10' => $size !== 'feature'])></span>
            <p @class([
                'font-display font-semibold leading-none tracking-tight',
                'text-6xl sm:text-7xl' => $size === 'feature',
                'text-4xl' => $size !== 'feature',
            ])>{{ $announcement->category->label() }}</p>
            @if ($date)
                <p @class(['mt-3 font-semibold', 'text-lg' => $size === 'feature', 'text-sm' => $size !== 'feature'])>{{ $date }}</p>
            @endif
        </div>
    </div>
@endif
