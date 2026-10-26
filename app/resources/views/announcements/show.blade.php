<x-layouts.public :title="$announcement->title" :description="str($announcement->body)->limit(150)->toString()">
    <p class="text-sm"><a href="{{ route('announcements.index') }}" class="font-medium text-slate-muted hover:text-emerald">&larr; All announcements</a></p>

    <div class="mt-6 grid gap-10 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <article class="overflow-hidden rounded-[12px] border border-line bg-white">
            <header class="flex items-center gap-3 px-5 py-4 sm:px-6">
                <img src="{{ asset('images/sk-logo.svg') }}" alt="" class="h-11 w-11 rounded-full border border-line p-0.5">
                <div class="leading-tight">
                    <p class="font-semibold">Sangguniang Kabataan ng {{ $announcement->barangay->name }}</p>
                    <p class="text-sm text-slate-muted">
                        {{ $announcement->category->label() }} ·
                        <time datetime="{{ $announcement->created_at->toIso8601String() }}">{{ $announcement->created_at->format('F j, Y · g:i A') }}</time>
                    </p>
                </div>
            </header>

            <div @class(['bg-mist', 'aspect-[4/3]' => ! $announcement->image_path])>
                <x-announcement.cover :announcement="$announcement" size="feature" class="max-h-[36rem]" />
            </div>

            <div class="px-5 py-6 sm:px-8 sm:py-8">
                <h1 class="font-display text-2xl font-semibold tracking-tight sm:text-3xl">{{ $announcement->title }}</h1>
                <p class="mt-4 max-w-[68ch] whitespace-pre-line leading-relaxed text-navy/85">{{ $announcement->captionHtml() }}</p>
            </div>
        </article>

        <aside>
            <h2 class="font-display text-lg font-semibold">More from SK {{ $announcement->barangay->name }}</h2>
            <ul class="mt-4 space-y-3">
                @forelse ($more as $other)
                    <li>
                        <a href="{{ route('announcements.show', $other) }}" class="flex gap-3 rounded-[12px] border border-line bg-white p-2.5 hover:border-emerald/50">
                            <span class="block h-16 w-16 shrink-0 overflow-hidden rounded-[8px]">
                                <x-announcement.cover :announcement="$other" size="thumb" />
                            </span>
                            <span class="min-w-0 py-0.5">
                                <span class="line-clamp-2 text-sm font-semibold leading-snug">{{ $other->title }}</span>
                                <span class="mt-1 block text-xs text-slate-muted">{{ $other->created_at->format('M j, Y') }}</span>
                            </span>
                        </a>
                    </li>
                @empty
                    <li class="text-sm text-slate-muted">No other posts yet.</li>
                @endforelse
            </ul>
            <a href="{{ route('announcements.index', ['barangay' => $announcement->barangay_id]) }}" class="mt-4 inline-block text-sm font-semibold text-emerald hover:underline">All posts from this SK</a>
        </aside>
    </div>
</x-layouts.public>
