<x-layouts.app :title="'CBYDP '.$cbydp->period()" :heading="'CBYDP '.$cbydp->period()" description="Youth needs and priorities for these three years, grouped by center of participation.">
    <x-slot:actions>
        <div class="flex flex-wrap items-center gap-3 max-sm:w-full">
            <x-ui.plan-status :plan="$cbydp" />
            @if ($cbydp->isApproved())
                <a href="{{ route('officer.abyips.create', ['cbydp' => $cbydp->id]) }}" class="inline-flex items-center justify-center gap-2 rounded-[8px] bg-emerald px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-dark max-sm:w-full max-sm:py-3">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    New ABYIP
                </a>
            @endif
        </div>
    </x-slot:actions>

    <p class="mb-6 text-sm"><a href="{{ route('officer.plans.index') }}" class="font-medium text-slate-muted hover:text-emerald">&larr; All plans</a></p>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_24rem]">
        <section class="rounded-[12px] border border-line bg-white">
            <h2 class="border-b border-line px-5 py-4 font-display text-lg font-semibold">
                Priorities <span class="text-sm font-normal text-slate-muted">({{ $cbydp->priorities->count() }})</span>
            </h2>
            @if ($cbydp->priorities->isEmpty())
                <p class="px-5 py-8 text-sm text-slate-muted">No priorities yet. Add the youth needs your consultations identified.</p>
            @else
                <ul class="divide-y divide-line">
                    @foreach ($cbydp->priorities as $priority)
                        <li class="flex items-start justify-between gap-4 px-5 py-4">
                            <div>
                                <x-ui.badge>{{ $priority->center->label() }}</x-ui.badge>
                                <p class="mt-1.5 font-medium">{{ $priority->title }}</p>
                                @if ($priority->description)
                                    <p class="mt-1 text-sm leading-relaxed text-slate-muted">{{ $priority->description }}</p>
                                @endif
                            </div>
                            @can('update', $cbydp)
                                <form method="POST" action="{{ route('officer.cbydps.priorities.destroy', [$cbydp, $priority]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-slate-muted hover:text-error">Remove<span class="sr-only"> {{ $priority->title }}</span></button>
                                </form>
                            @endcan
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <div class="space-y-6">
            @can('update', $cbydp)
                <section class="rounded-[12px] border border-line bg-white p-5" aria-labelledby="add-priority">
                    <h2 id="add-priority" class="font-display text-lg font-semibold">Add a priority</h2>
                    <form method="POST" action="{{ route('officer.cbydps.priorities.store', $cbydp) }}" class="mt-4 space-y-4" novalidate>
                        @csrf
                        <x-form.select name="center" label="Center of participation" placeholder="Choose one" required>
                            @foreach ($centers as $center)
                                <option value="{{ $center->value }}" @selected(old('center') === $center->value)>{{ $center->label() }}</option>
                            @endforeach
                        </x-form.select>
                        <x-form.input name="title" label="Priority" placeholder="e.g. Reduce out-of-school youth" required />
                        <div>
                            <label for="description" class="block text-sm font-medium">Details <span class="font-normal text-slate-muted">(optional)</span></label>
                            <textarea id="description" name="description" rows="3" class="mt-1.5 block w-full rounded-[8px] border border-line px-3.5 py-2.5 text-sm outline-none focus:border-emerald focus:ring-3 focus:ring-emerald/20">{{ old('description') }}</textarea>
                        </div>
                        <x-form.submit busy="Adding…" :block="false">Add priority</x-form.submit>
                    </form>
                </section>
            @endcan

            @include('officer._approval', [
                'plan' => $cbydp,
                'action' => route('officer.cbydps.approval.store', $cbydp),
                'resolutionRoute' => route('officer.cbydps.resolution', $cbydp),
                'planName' => 'this CBYDP',
                'lockNote' => 'Its priorities will be locked, and you can then start ABYIPs for '.$cbydp->period().'.',
                'emptyNote' => $cbydp->priorities->isEmpty() ? 'Add at least one priority before recording the approval.' : null,
            ])
        </div>
    </div>
</x-layouts.app>
