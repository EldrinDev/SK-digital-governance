<x-layouts.app title="New ABYIP" heading="New ABYIP" description="The Annual Barangay Youth Investment Program lists the year’s PPAs, taken from an approved CBYDP.">
    <x-ui.form-page>
    @if ($cbydps->isEmpty())
        <section class="rounded-[12px] border border-dashed border-line bg-white p-6">
            <p class="text-sm leading-relaxed text-slate-muted">An ABYIP comes from an approved CBYDP, and your barangay doesn’t have one yet. Record the council’s approval of your CBYDP first.</p>
            <a href="{{ route('officer.plans.index') }}" class="mt-4 inline-block text-sm font-semibold text-emerald hover:underline">Go to plans</a>
        </section>
    @else
        <form method="POST" action="{{ route('officer.abyips.store') }}" class="space-y-5 rounded-[12px] border border-line bg-white p-6" novalidate>
            @csrf

            @php
                $chosenCbydp = $cbydps->firstWhere('id', (int) old('cbydp_id', $selectedCbydp));
                $chosenYear = (int) old('year');

                if ($chosenCbydp && ! $chosenYear) {
                    $freeYears = collect(range($chosenCbydp->start_year, $chosenCbydp->end_year))->diff($takenYears);
                    $chosenYear = $freeYears->contains(now()->year) ? now()->year : $freeYears->first();
                }
            @endphp

            <x-form.select name="cbydp_id" label="From CBYDP" placeholder="Choose the CBYDP" required data-controls="year">
                @foreach ($cbydps as $cbydp)
                    @php
                        $years = range($cbydp->start_year, $cbydp->end_year);
                        $doneYears = count(array_intersect($years, $takenYears));
                        $progress = match ($doneYears) {
                            0 => 'No ABYIP yet',
                            count($years) => 'Every year has an ABYIP',
                            default => "{$doneYears} of ".count($years).($doneYears === 1 ? ' years has an ABYIP' : ' years have an ABYIP'),
                        };
                    @endphp
                    <option value="{{ $cbydp->id }}" data-label="CBYDP {{ $cbydp->period() }}" data-hint="{{ $progress }}" @selected($chosenCbydp?->id === $cbydp->id)>CBYDP {{ $cbydp->period() }}</option>
                @endforeach
            </x-form.select>

            {{-- Only the chosen CBYDP's three years; years that already have an ABYIP can't be picked. --}}
            <div>
                <x-form.select name="year" label="Year" :placeholder="$chosenCbydp ? 'Choose the year' : 'Choose the CBYDP first'" :disabled="! $chosenCbydp" data-ready-label="Choose the year" data-waiting-label="Choose the CBYDP first" required>
                    @foreach ($cbydps as $cbydp)
                        @foreach (range($cbydp->start_year, $cbydp->end_year) as $year)
                            @php
                                $isTaken = in_array($year, $takenYears, true);
                            @endphp
                            <option value="{{ $year }}" data-for="{{ $cbydp->id }}" data-label="{{ $year }}" data-hint="Year {{ $loop->iteration }} of {{ $loop->count }}" @if ($isTaken) data-taken data-note="Has an ABYIP" @endif @selected($chosenCbydp?->id === $cbydp->id && $chosenYear === $year) @disabled($isTaken || $chosenCbydp?->id !== $cbydp->id) @if ($chosenCbydp?->id !== $cbydp->id) hidden @endif>{{ $year }}{{ $isTaken ? ', already has an ABYIP' : '' }}</option>
                        @endforeach
                    @endforeach
                </x-form.select>
                <p class="mt-1.5 text-xs text-slate-muted">One ABYIP for each year of the CBYDP.</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <x-form.submit busy="Starting…" :block="false">Start ABYIP</x-form.submit>
                <a href="{{ route('officer.plans.index') }}" class="rounded-[8px] px-4 py-2.5 text-sm font-medium text-slate-muted hover:text-navy">Cancel</a>
            </div>
        </form>
    @endif

        <x-slot:guide>
            <x-ui.guide-steps title="How an ABYIP comes together" :steps="[
                ['Start the ABYIP', 'Pick the approved CBYDP and the year.'],
                ['Add its PPAs', 'Each one linked to a CBYDP priority, with a budget, target and schedule.'],
                ['Record the council’s approval', 'Upload the signed resolution. Budgets and schedules lock.'],
                ['Publish and track progress', 'Put it on the transparency board, then record milestones, spending and evidence.'],
            ]" />
        </x-slot:guide>
    </x-ui.form-page>
</x-layouts.app>
