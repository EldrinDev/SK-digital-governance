<x-layouts.app title="Search" heading="Search your workspace" description="Find PPAs, evidence, sessions, resolutions, events and announcements of your barangay.">
    <form method="GET" action="{{ route('officer.search') }}" class="flex max-w-2xl gap-2" role="search">
        <label for="q" class="sr-only">Search</label>
        <input id="q" name="q" type="search" value="{{ $term }}" placeholder="e.g. clean-up, Res. No. 7, attendance sheet" autofocus class="block w-full rounded-[8px] border border-line bg-white px-3.5 py-2.5 text-sm outline-none focus:border-emerald focus:ring-3 focus:ring-emerald/20">
        <button type="submit" class="rounded-[8px] bg-navy px-5 py-2.5 text-sm font-semibold text-white hover:bg-navy-deep">Search</button>
    </form>

    @if (mb_strlen($term) >= 2)
        <div class="mt-8 grid items-start gap-6 lg:grid-cols-2">
            @forelse ($results as $group => $items)
                <section class="rounded-[12px] border border-line bg-white" aria-labelledby="group-{{ $loop->index }}">
                    <h2 id="group-{{ $loop->index }}" class="flex items-center justify-between border-b border-line px-5 py-3 font-display font-semibold">
                        {{ $group }}
                        <span class="text-xs font-normal text-slate-muted">{{ $items->count() }}{{ $items->count() === 8 ? '+' : '' }}</span>
                    </h2>
                    <ul class="divide-y divide-line">
                        @foreach ($items as $item)
                            <li>
                                <a href="{{ $item['url'] }}" class="block px-5 py-3 hover:bg-mist">
                                    <span class="block text-sm font-medium">{{ $item['title'] }}</span>
                                    <span class="block text-xs text-slate-muted">{{ $item['meta'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @empty
                <p class="rounded-[12px] border border-dashed border-line bg-white p-8 text-center text-sm text-slate-muted lg:col-span-2">Nothing in your workspace matches “{{ $term }}”.</p>
            @endforelse
        </div>
    @elseif ($term !== '')
        <p class="mt-6 text-sm text-slate-muted">Type at least two characters.</p>
    @endif
</x-layouts.app>
