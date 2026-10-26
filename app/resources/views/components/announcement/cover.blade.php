@props([
    'announcement',
    'size' => 'card',
])
@php
    use App\AnnouncementCategory;

    /*
     * Posts with a photo show it. Posts without one get a "pubmat" tile, the poster style
     * SK pages use on social media: the seal, the category, and the headline set large.
     */
    $tone = match ($announcement->category) {
        AnnouncementCategory::Reminder => ['bg' => 'bg-gold', 'text' => 'text-navy', 'muted' => 'text-navy/70', 'rule' => 'bg-navy'],
        AnnouncementCategory::Event => ['bg' => 'bg-emerald', 'text' => 'text-white', 'muted' => 'text-white/75', 'rule' => 'bg-gold'],
        AnnouncementCategory::Recognition => ['bg' => 'bg-navy-deep', 'text' => 'text-white', 'muted' => 'text-white/70', 'rule' => 'bg-gold'],
        default => ['bg' => 'bg-navy', 'text' => 'text-white', 'muted' => 'text-white/70', 'rule' => 'bg-gold'],
    };
    $headline = match ($size) {
        'feature' => 'text-2xl sm:text-[2rem] leading-[1.15]',
        'thumb' => '',
        default => 'text-xl leading-snug',
    };
@endphp
@if ($announcement->image_path)
    <img
        src="{{ route('announcements.image', $announcement) }}"
        alt=""
        loading="lazy"
        {{ $attributes->class(['h-full w-full object-cover']) }}
    >
@elseif ($size === 'thumb')
    <span {{ $attributes->class(['flex h-full w-full items-center justify-center', $tone['bg']]) }} aria-hidden="true">
        <img src="{{ asset('images/sk-logo.svg') }}" alt="" class="h-3/5 w-3/5">
    </span>
@else
    <div {{ $attributes->class(['relative flex h-full w-full flex-col justify-between overflow-hidden p-5 sm:p-6', $tone['bg'], $tone['text']]) }} aria-hidden="true">
        <img src="{{ asset('images/sk-logo.svg') }}" alt="" class="pointer-events-none absolute -bottom-10 -right-10 h-44 w-44 opacity-[0.12]">
        <div class="flex items-center gap-2.5">
            <img src="{{ asset('images/sk-logo.svg') }}" alt="" class="h-8 w-8 rounded-full bg-white/90 p-0.5">
            <span class="text-xs font-semibold leading-tight">SK {{ $announcement->barangay->name }}<span @class(['block font-medium', $tone['muted']])>Olongapo City</span></span>
        </div>
        <div class="relative">
            <span @class(['mb-3 block h-1 w-10 rounded-full', $tone['rule']])></span>
            <p @class(['text-sm font-semibold', $tone['muted']])>{{ $announcement->category->label() }}</p>
            <p @class(['mt-1 line-clamp-4 font-display font-semibold tracking-tight', $headline])>{{ $announcement->title }}</p>
        </div>
    </div>
@endif
