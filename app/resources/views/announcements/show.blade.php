<x-layouts.public :title="$announcement->title" :description="str($announcement->body)->limit(150)->toString()">
    <article class="max-w-3xl">
        <p class="text-sm"><a href="{{ route('announcements.index') }}" class="font-medium text-slate-muted hover:text-emerald">&larr; All announcements</a></p>
        <p class="mt-6 text-sm font-semibold text-emerald">{{ $announcement->barangay->name }} SK</p>
        <h1 class="mt-1 font-display text-3xl font-semibold tracking-tight sm:text-4xl">{{ $announcement->title }}</h1>
        <p class="mt-2 text-sm text-slate-muted">
            Posted <time datetime="{{ $announcement->created_at->toIso8601String() }}">{{ $announcement->created_at->format('F j, Y') }}</time>
        </p>
        <div class="mt-8 whitespace-pre-line rounded-[12px] border border-line bg-white p-6 leading-relaxed text-navy/85">{{ $announcement->body }}</div>
    </article>
</x-layouts.public>
