@php
    use App\PpaStatus;
@endphp
<x-layouts.app :title="$ppa->title" :heading="$ppa->title" :description="$ppa->description">
    <x-slot:actions>
        <div class="flex items-center gap-3">
            <x-ui.ppa-status :ppa="$ppa" />
            @can('update', $ppa)
                <a href="{{ route('officer.ppas.edit', $ppa) }}" class="rounded-[8px] border border-line px-3.5 py-2 text-sm font-semibold hover:border-navy">Edit</a>
            @endcan
        </div>
    </x-slot:actions>

    <p class="mb-6 text-sm"><a href="{{ route('officer.abyips.show', $ppa->abyip) }}" class="font-medium text-slate-muted hover:text-emerald">&larr; ABYIP {{ $ppa->abyip->year }}</a></p>

    @if ($ppa->isDelayed())
        <div role="alert" class="mb-6 flex items-start gap-3 rounded-[12px] border border-error/30 bg-error/5 p-4 text-sm">
            <svg viewBox="0 0 24 24" class="mt-0.5 h-5 w-5 shrink-0 text-error" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
            <p>This PPA was due to end on {{ $ppa->ends_on->format('F j, Y') }} and is still open. Update its status, or mark the remaining milestones done.</p>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="space-y-6">
            <section class="grid gap-4 rounded-[12px] border border-line bg-white p-5 text-sm sm:grid-cols-2">
                <div><p class="text-slate-muted">CBYDP priority</p><p class="font-medium">{{ $ppa->priority->title }}</p><p class="text-xs text-slate-muted">{{ $ppa->priority->center->label() }}</p></div>
                <div><p class="text-slate-muted">Target</p><p class="font-medium">{{ $ppa->target }}</p></div>
                <div>
                    <p class="text-slate-muted">Budget</p>
                    <p class="font-medium tabular-nums">₱{{ number_format($ppa->budget, 2) }}</p>
                    @if ($ppa->amount_spent !== null)
                        <p @class(['text-xs tabular-nums', 'font-semibold text-[#b91c1c]' => $ppa->isOverBudget(), 'text-slate-muted' => ! $ppa->isOverBudget()])>
                            ₱{{ number_format($ppa->amount_spent, 2) }} spent ({{ $ppa->budgetUsedPercent() ?? 0 }}%){{ $ppa->isOverBudget() ? ' · over budget' : '' }}
                        </p>
                    @endif
                </div>
                <div><p class="text-slate-muted">Schedule</p><p class="font-medium">{{ $ppa->starts_on->format('M j') }} – {{ $ppa->ends_on->format('M j, Y') }}</p></div>
                @can('recordSpending', $ppa)
                    <form method="POST" action="{{ route('officer.ppas.spending.update', $ppa) }}" class="flex flex-wrap items-end gap-3 border-t border-line pt-4 sm:col-span-2">
                        @csrf
                        @method('PATCH')
                        <div class="w-48">
                            <x-form.input name="amount_spent" type="number" step="0.01" min="0" label="Amount spent so far (₱)" :value="$ppa->amount_spent" required />
                        </div>
                        <x-form.submit busy="Saving…" tone="navy" :block="false">Record spending</x-form.submit>
                        @if ($ppa->spending_recorded_at)
                            <p class="text-xs text-slate-muted">Last recorded {{ $ppa->spending_recorded_at->format('M j, Y') }}</p>
                        @endif
                    </form>
                @endcan
            </section>

            <section class="rounded-[12px] border border-line bg-white">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-5 py-4">
                    <h2 class="font-display text-lg font-semibold">Milestones</h2>
                    <div class="flex items-center gap-3 text-sm text-slate-muted">
                        <div class="h-2 w-28 overflow-hidden rounded-full bg-mist" role="progressbar" aria-valuenow="{{ $ppa->progress() }}" aria-valuemin="0" aria-valuemax="100" aria-label="Milestones done">
                            <div class="h-full rounded-full bg-emerald" style="width: {{ $ppa->progress() }}%"></div>
                        </div>
                        {{ $ppa->progress() }}% done
                    </div>
                </div>

                @if ($ppa->milestones->isEmpty())
                    <p class="px-5 py-6 text-sm text-slate-muted">No milestones yet. Break the PPA into checkpoints so progress is easy to follow.</p>
                @else
                    <ul class="divide-y divide-line">
                        @foreach ($ppa->milestones as $milestone)
                            <li class="flex items-center justify-between gap-4 px-5 py-3">
                                <div class="flex items-center gap-3">
                                    @can('track', $ppa)
                                        <form method="POST" action="{{ route('officer.ppas.milestones.update', [$ppa, $milestone]) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="completed" value="{{ $milestone->isCompleted() ? 0 : 1 }}">
                                            <button type="submit" @class([
                                                'flex h-6 w-6 items-center justify-center rounded-[6px] border-2 transition-colors',
                                                'border-emerald bg-emerald text-white' => $milestone->isCompleted(),
                                                'border-line hover:border-emerald' => ! $milestone->isCompleted(),
                                            ]) aria-pressed="{{ $milestone->isCompleted() ? 'true' : 'false' }}">
                                                <span class="sr-only">{{ $milestone->isCompleted() ? 'Reopen' : 'Mark done:' }} {{ $milestone->title }}</span>
                                                @if ($milestone->isCompleted())
                                                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg>
                                                @endif
                                            </button>
                                        </form>
                                    @else
                                        <span @class(['h-2.5 w-2.5 rounded-full', 'bg-emerald' => $milestone->isCompleted(), 'bg-line' => ! $milestone->isCompleted()]) aria-hidden="true"></span>
                                    @endcan
                                    <span @class(['text-sm', 'text-slate-muted line-through' => $milestone->isCompleted()])>{{ $milestone->title }}</span>
                                </div>
                                <span @class(['shrink-0 text-xs', 'font-semibold text-[#b91c1c]' => $milestone->isOverdue(), 'text-slate-muted' => ! $milestone->isOverdue()])>
                                    @if ($milestone->isCompleted())
                                        Done {{ $milestone->completed_at->format('M j') }}
                                    @elseif ($milestone->isOverdue())
                                        Overdue · {{ $milestone->due_on->format('M j') }}
                                    @else
                                        Due {{ $milestone->due_on->format('M j') }}
                                    @endif
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif

                @can('planMilestones', $ppa)
                    <form method="POST" action="{{ route('officer.ppas.milestones.store', $ppa) }}" class="grid gap-3 border-t border-line p-5 sm:grid-cols-[minmax(0,1fr)_11rem_auto] sm:items-end" novalidate>
                        @csrf
                        <x-form.input name="title" label="New milestone" placeholder="e.g. Venue booked" required />
                        <x-form.input name="due_on" type="date" label="Due" required />
                        <x-form.submit busy="Adding…" :block="false" class="py-2.5">Add</x-form.submit>
                    </form>
                @endcan

                @cannot('track', $ppa)
                    @if (! $ppa->abyip->isApproved())
                        <p class="border-t border-line px-5 py-3 text-xs text-slate-muted">You can tick milestones off once the council approves ABYIP {{ $ppa->abyip->year }}.</p>
                    @endif
                @endcannot
            </section>

            <section class="rounded-[12px] border border-line bg-white" aria-labelledby="evidence-heading">
                <div class="border-b border-line px-5 py-4">
                    <h2 id="evidence-heading" class="font-display text-lg font-semibold">Evidence</h2>
                    <p class="mt-0.5 text-sm text-slate-muted">Photos and documents that show the work was done. Files are internal unless you mark them public.</p>
                </div>

                @if ($ppa->evidence->isEmpty())
                    <p class="px-5 py-6 text-sm text-slate-muted">No evidence yet.</p>
                @else
                    <ul class="grid gap-4 p-5 sm:grid-cols-2">
                        @foreach ($ppa->evidence as $file)
                            <li class="overflow-hidden rounded-[12px] border border-line">
                                <a href="{{ route('officer.ppas.evidence.show', [$ppa, $file]) }}" target="_blank" class="block aspect-[4/3] bg-mist">
                                    @if ($file->isImage())
                                        <img src="{{ route('officer.ppas.evidence.show', [$ppa, $file]) }}" alt="{{ $file->title }}" loading="lazy" class="h-full w-full object-cover">
                                    @else
                                        <span class="flex h-full flex-col items-center justify-center gap-2 text-slate-muted">
                                            <svg viewBox="0 0 24 24" class="h-10 w-10" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9l-6-6Z"/><path d="M14 3v6h6"/></svg>
                                            <span class="text-xs font-semibold">PDF · {{ $file->readableSize() }}</span>
                                        </span>
                                    @endif
                                </a>
                                <div class="space-y-2 p-3">
                                    <div class="flex items-start justify-between gap-2">
                                        <p class="text-sm font-medium">{{ $file->title }}</p>
                                        <x-ui.badge :tone="$file->is_public ? 'info' : 'neutral'">{{ $file->is_public ? 'Public' : 'Internal' }}</x-ui.badge>
                                    </div>
                                    <p class="text-xs text-slate-muted">{{ $file->uploader?->name ?? 'Someone' }} · {{ $file->created_at->format('M j, Y') }}</p>
                                    @can('track', $ppa)
                                        <div class="flex gap-4 text-xs font-semibold">
                                            <form method="POST" action="{{ route('officer.ppas.evidence.update', [$ppa, $file]) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="is_public" value="{{ $file->is_public ? 0 : 1 }}">
                                                <button type="submit" class="text-emerald hover:underline">{{ $file->is_public ? 'Make internal' : 'Make public' }}</button>
                                            </form>
                                            <form method="POST" action="{{ route('officer.ppas.evidence.destroy', [$ppa, $file]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-slate-muted hover:text-error">Delete<span class="sr-only"> {{ $file->title }}</span></button>
                                            </form>
                                        </div>
                                    @endcan
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif

                @can('track', $ppa)
                    <form method="POST" action="{{ route('officer.ppas.evidence.store', $ppa) }}" enctype="multipart/form-data" class="space-y-4 border-t border-line p-5" novalidate>
                        @csrf
                        <x-form.input name="title" label="What it shows" placeholder="e.g. Day 1 attendance sheet" required />
                        <div>
                            <label for="file" class="block text-sm font-medium">Photo or PDF (up to 10 MB)</label>
                            <input id="file" name="file" type="file" accept="image/jpeg,image/png,image/webp,application/pdf" required
                                @error('file') aria-invalid="true" aria-describedby="file-error" @enderror
                                class="mt-1.5 block w-full rounded-[8px] border border-line bg-white text-sm text-navy/80 file:mr-3 file:border-0 file:border-r file:border-line file:bg-mist file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-navy">
                            @error('file')
                                <p id="file-error" class="mt-1.5 text-sm text-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <label class="flex items-start gap-2.5 text-sm text-navy/80">
                            <input type="checkbox" name="is_public" value="1" @checked(old('is_public')) class="mt-1 h-4 w-4 rounded border-line accent-emerald">
                            <span>Show on the public portal <span class="block text-xs text-slate-muted">Only when it has no personal details, like faces of minors or ID numbers.</span></span>
                        </label>
                        <x-form.submit busy="Uploading…" :block="false">Upload evidence</x-form.submit>
                    </form>
                @endcan
            </section>
        </div>

        <div class="space-y-6">
            @can('track', $ppa)
                <section class="rounded-[12px] border border-line bg-white p-5">
                    <h2 class="font-display text-lg font-semibold">Status</h2>
                    <form method="POST" action="{{ route('officer.ppas.status.update', $ppa) }}" class="mt-4 space-y-3">
                        @csrf
                        @method('PATCH')
                        <fieldset>
                            <legend class="sr-only">Implementation status</legend>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach (PpaStatus::cases() as $status)
                                    <label class="flex cursor-pointer items-center gap-2 rounded-[8px] border border-line px-3 py-2 text-sm has-[:checked]:border-emerald has-[:checked]:bg-emerald/10 has-[:checked]:font-semibold">
                                        <input type="radio" name="status" value="{{ $status->value }}" @checked($ppa->status === $status) class="accent-emerald">
                                        {{ $status->label() }}
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                        <x-form.submit busy="Saving…" tone="navy">Save status</x-form.submit>
                    </form>
                </section>
            @endcan

            <section class="rounded-[12px] border border-line bg-white p-5">
                <h2 class="font-display text-lg font-semibold">History</h2>
                <ul class="mt-3 space-y-2.5 text-sm">
                    @forelse ($activity as $entry)
                        <li>
                            <span class="font-medium">{{ $entry->user?->name ?? 'Someone' }}</span>
                            <span class="text-navy/70">{{ Str::lcfirst($entry->label()) }}</span>
                            @if (isset($entry->properties['to']))
                                <span class="text-navy/70">to {{ $entry->properties['to'] }}</span>
                            @elseif (isset($entry->properties['milestone']))
                                <span class="text-navy/70">“{{ $entry->properties['milestone'] }}”</span>
                            @endif
                            <span class="block text-xs text-slate-muted">{{ $entry->created_at->format('M j, Y g:i A') }}</span>
                        </li>
                    @empty
                        <li class="text-slate-muted">No changes recorded yet.</li>
                    @endforelse
                </ul>
            </section>
        </div>
    </div>
</x-layouts.app>
