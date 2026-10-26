<x-layouts.public :title="$announcement->title" :description="str($announcement->body)->limit(150)->toString()">
    <p class="text-sm"><a href="{{ route('announcements.index') }}" class="font-medium text-slate-muted hover:text-emerald">&larr; All announcements</a></p>

    <div class="mt-6 grid gap-10 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <article class="overflow-hidden rounded-[12px] border border-line bg-white">
            <header class="flex items-center gap-3 px-5 py-4 sm:px-6">
                <img src="{{ $announcement->barangay->logoUrl() }}" alt="" class="h-11 w-11 rounded-full border border-line bg-white object-contain p-0.5">
                <div class="leading-tight">
                    <p class="font-semibold">Sangguniang Kabataan ng {{ $announcement->barangay->name }}</p>
                    <p class="text-sm text-slate-muted">
                        {{ $announcement->category->label() }} ·
                        <time datetime="{{ $announcement->postedAt()->toIso8601String() }}">{{ $announcement->postedAt()->format('F j, Y · g:i A') }}</time>
                    </p>
                </div>
            </header>

            <div @class(['bg-mist', 'aspect-[4/3]' => ! $announcement->image_path])>
                <x-announcement.cover :announcement="$announcement" size="feature" class="max-h-[36rem]" />
            </div>
            @if ($announcement->image_path && $announcement->image_credit)
                <p class="border-b border-line px-5 py-2 text-xs text-slate-muted sm:px-8">Photo: {{ $announcement->image_credit }}</p>
            @endif

            <div class="px-5 py-6 sm:px-8 sm:py-8">
                <h1 class="font-display text-2xl font-semibold tracking-tight sm:text-3xl">{{ $announcement->title }}</h1>
                <x-announcement.details :announcement="$announcement" class="mt-5 max-w-md" />
                <p class="mt-4 max-w-[68ch] whitespace-pre-line leading-relaxed text-navy/85">{{ $announcement->captionHtml(withoutDetails: true) }}</p>
                @if ($announcement->isFromFacebook())
                    <a href="{{ $announcement->source_url }}" target="_blank" rel="noopener noreferrer nofollow" class="mt-6 inline-flex items-center gap-2 rounded-[10px] border border-line bg-mist px-4 py-2.5 text-sm hover:border-[#1877f2]">
                        <svg viewBox="0 0 24 24" class="h-4 w-4 text-[#1877f2]" fill="currentColor" aria-hidden="true"><path d="M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.05V9.41c0-3.02 1.79-4.69 4.53-4.69 1.31 0 2.68.24 2.68.24v2.97h-1.51c-1.49 0-1.96.93-1.96 1.89v2.25h3.33l-.53 3.49h-2.8V24C19.61 23.1 24 18.1 24 12.07Z"/></svg>
                        <span>Originally posted on Facebook, {{ $announcement->postedAt()->format('F j, Y') }}. <span class="font-semibold text-[#1877f2]">See the original post</span></span>
                        <span class="sr-only">(opens in a new tab)</span>
                    </a>
                @endif
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
                                <span class="mt-1 block text-xs text-slate-muted">{{ $other->postedAt()->format('M j, Y') }}</span>
                            </span>
                        </a>
                    </li>
                @empty
                    <li class="text-sm text-slate-muted">No other posts yet.</li>
                @endforelse
            </ul>
            <a href="{{ route('announcements.index', ['barangay' => $announcement->barangay_id]) }}" class="mt-4 inline-block text-sm font-semibold text-emerald hover:underline">All posts from this SK</a>
            <x-barangay.facebook-link :barangay="$announcement->barangay" class="mt-4 flex w-full justify-center" />
        </aside>
    </div>
</x-layouts.public>
