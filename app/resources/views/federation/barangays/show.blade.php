<x-layouts.app :title="$barangay->name" :heading="$barangay->name" description="Read-only view of this barangay’s plans, PPAs, and sessions. Leave a remark to flag anything the SK should act on.">
    <p class="mb-6 text-sm"><a href="{{ route('federation.dashboard') }}" class="font-medium text-slate-muted hover:text-emerald">&larr; All barangays</a></p>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="space-y-6">
            <section class="rounded-[12px] border border-line bg-white p-5">
                <h2 class="font-display text-lg font-semibold">Plans</h2>
                @if ($cbydps->isEmpty())
                    <p class="mt-2 text-sm text-slate-muted">No CBYDP yet.</p>
                @else
                    <ul class="mt-3 space-y-2 text-sm">
                        @foreach ($cbydps as $cbydp)
                            <li class="flex flex-wrap items-center gap-2">
                                <span class="font-medium">CBYDP {{ $cbydp->period() }}</span>
                                <x-ui.plan-status :plan="$cbydp" />
                                <span class="text-slate-muted">· {{ trans_choice(':count priority|:count priorities', $cbydp->priorities_count) }}</span>
                                @if ($cbydp->resolution_number)
                                    <span class="text-slate-muted">· {{ $cbydp->resolution_number }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            @foreach ($abyips as $abyip)
                <section class="overflow-x-auto rounded-[12px] border border-line bg-white">
                    <div class="flex flex-wrap items-center gap-2 border-b border-line px-5 py-4">
                        <h2 class="font-display text-lg font-semibold">ABYIP {{ $abyip->year }}</h2>
                        <x-ui.plan-status :plan="$abyip" />
                        @if ($abyip->resolution_number)
                            <span class="text-sm text-slate-muted">· {{ $abyip->resolution_number }}</span>
                        @endif
                    </div>
                    @if ($abyip->ppas->isEmpty())
                        <p class="px-5 py-5 text-sm text-slate-muted">No PPAs listed.</p>
                    @else
                        <table class="w-full min-w-[44rem] text-left text-sm">
                            <thead class="border-b border-line bg-mist/60 text-xs font-semibold text-slate-muted">
                                <tr>
                                    <th scope="col" class="px-5 py-3">PPA</th>
                                    <th scope="col" class="px-5 py-3">Ends</th>
                                    <th scope="col" class="px-5 py-3">Progress</th>
                                    <th scope="col" class="px-5 py-3 text-right">Evidence</th>
                                    <th scope="col" class="px-5 py-3 text-right">Budget</th>
                                    <th scope="col" class="px-5 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line">
                                @foreach ($abyip->ppas as $ppa)
                                    <tr>
                                        <td class="px-5 py-3"><p class="font-medium">{{ $ppa->title }}</p><p class="text-xs text-slate-muted">{{ $ppa->priority->center->label() }}</p></td>
                                        <td class="whitespace-nowrap px-5 py-3">{{ $ppa->ends_on->format('M j') }}</td>
                                        <td class="px-5 py-3 text-xs text-slate-muted">{{ $ppa->progress() }}%</td>
                                        <td @class(['px-5 py-3 text-right tabular-nums', 'text-[#b45309]' => $ppa->status === \App\PpaStatus::Completed && $ppa->evidence_count === 0])>{{ $ppa->evidence_count }}</td>
                                        <td class="whitespace-nowrap px-5 py-3 text-right tabular-nums">₱{{ number_format($ppa->budget, 2) }}</td>
                                        <td class="px-5 py-3"><x-ui.ppa-status :ppa="$ppa" /></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </section>
            @endforeach

            <section class="rounded-[12px] border border-line bg-white p-5">
                <h2 class="font-display text-lg font-semibold">Recent sessions</h2>
                @if ($meetings->isEmpty())
                    <p class="mt-2 text-sm text-slate-muted">No sessions recorded.</p>
                @else
                    <ul class="mt-3 divide-y divide-line text-sm">
                        @foreach ($meetings as $meeting)
                            @php($quorum = $meeting->hasQuorum())
                            <li class="flex flex-wrap items-center justify-between gap-2 py-2.5">
                                <span><span class="font-medium">{{ $meeting->title }}</span> <span class="text-slate-muted">· {{ $meeting->held_on->format('M j, Y') }}</span></span>
                                <span class="flex items-center gap-2 text-xs text-slate-muted">
                                    @if ($quorum === true)
                                        <x-ui.badge tone="success">Quorum</x-ui.badge>
                                    @elseif ($quorum === false)
                                        <x-ui.badge tone="error">No quorum</x-ui.badge>
                                    @endif
                                    {{ trans_choice(':count resolution|:count resolutions', $meeting->resolutions_count) }}
                                    · minutes {{ $meeting->minutes_path ? 'uploaded' : 'missing' }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>

        <section class="h-fit rounded-[12px] border border-line bg-white p-5" aria-labelledby="remarks-heading">
            <h2 id="remarks-heading" class="font-display text-lg font-semibold">Monitoring remarks</h2>
            <form method="POST" action="{{ route('federation.barangays.remarks.store', $barangay) }}" class="mt-4 space-y-3" novalidate>
                @csrf
                <label for="body" class="sr-only">New remark</label>
                <textarea id="body" name="body" rows="4" placeholder="e.g. Two PPAs are marked completed without evidence. Please upload photos or reports." class="block w-full rounded-[8px] border border-line px-3.5 py-2.5 text-sm outline-none focus:border-emerald focus:ring-3 focus:ring-emerald/20">{{ old('body') }}</textarea>
                @error('body')
                    <p class="text-sm text-error">{{ $message }}</p>
                @enderror
                <x-form.submit busy="Sending…" tone="navy">Send remark</x-form.submit>
                <p class="text-xs text-slate-muted">The barangay’s SK officers see it on their overview.</p>
            </form>

            <ul class="mt-6 space-y-4 border-t border-line pt-5 text-sm">
                @forelse ($remarks as $remark)
                    <li>
                        <p class="leading-relaxed">{{ $remark->body }}</p>
                        <p class="mt-0.5 text-xs text-slate-muted">{{ $remark->author?->name ?? 'SK Federation' }} · {{ $remark->created_at->format('M j, Y') }}</p>
                    </li>
                @empty
                    <li class="text-slate-muted">No remarks yet.</li>
                @endforelse
            </ul>
        </section>
    </div>
</x-layouts.app>
