<x-layouts.app title="Disclosure" heading="Disclosure" description="What the public can see on the transparency board. Only approved ABYIPs can be published, and only evidence you mark public is shown.">
    <x-slot:actions>
        <a href="{{ route('transparency.show', $barangay) }}" target="_blank" class="rounded-[8px] border border-line bg-white px-4 py-2.5 text-sm font-semibold hover:border-navy">View public page</a>
    </x-slot:actions>

    <div class="grid gap-6 lg:grid-cols-3">
        @foreach ([
            ['title' => 'Ready to publish', 'items' => $ready, 'empty' => 'Nothing waiting. Approved ABYIPs appear here until you publish them.', 'tone' => 'warning'],
            ['title' => 'Published', 'items' => $published, 'empty' => 'Nothing published yet.', 'tone' => 'success'],
            ['title' => 'Waiting for council approval', 'items' => $pending, 'empty' => 'No drafts.', 'tone' => 'neutral'],
        ] as $column)
            <section class="rounded-[12px] border border-line bg-white" aria-labelledby="col-{{ $loop->index }}">
                <h2 id="col-{{ $loop->index }}" class="flex items-center justify-between border-b border-line px-5 py-4 font-display text-lg font-semibold">
                    {{ $column['title'] }}
                    <x-ui.badge :tone="$column['tone']">{{ $column['items']->count() }}</x-ui.badge>
                </h2>
                <ul class="divide-y divide-line">
                    @forelse ($column['items'] as $abyip)
                        <li class="flex items-center justify-between gap-3 px-5 py-3.5">
                            <div>
                                <a href="{{ route('officer.abyips.show', $abyip) }}" class="font-medium hover:text-emerald">ABYIP {{ $abyip->year }}</a>
                                <p class="text-xs text-slate-muted">
                                    {{ trans_choice(':count PPA|:count PPAs', $abyip->ppas_count) }} · ₱{{ number_format($abyip->ppas_sum_budget ?? 0) }}
                                    @if ($abyip->isPublished())
                                        · since {{ $abyip->published_at->format('M j') }}
                                    @endif
                                </p>
                            </div>
                            @can('publish', $abyip)
                                <form method="POST" action="{{ $abyip->isPublished() ? route('officer.abyips.publication.destroy', $abyip) : route('officer.abyips.publication.store', $abyip) }}">
                                    @csrf
                                    @if ($abyip->isPublished())
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-slate-muted hover:text-navy">Unpublish</button>
                                    @else
                                        <button type="submit" class="rounded-[8px] bg-emerald px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-dark">Publish</button>
                                    @endif
                                </form>
                            @endcan
                        </li>
                    @empty
                        <li class="px-5 py-5 text-sm text-slate-muted">{{ $column['empty'] }}</li>
                    @endforelse
                </ul>
            </section>
        @endforeach
    </div>

    <p class="mt-6 text-sm text-slate-muted">
        {{ trans_choice(':count evidence file is|:count evidence files are', $publicEvidence) }} marked public. It only shows once its ABYIP is published. Change a file’s visibility from its PPA page.
    </p>
</x-layouts.app>
