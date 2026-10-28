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

            <x-form.select name="cbydp_id" label="From CBYDP" placeholder="Choose the CBYDP" required>
                @foreach ($cbydps as $cbydp)
                    <option value="{{ $cbydp->id }}" @selected((string) old('cbydp_id', $selectedCbydp) === (string) $cbydp->id)>CBYDP {{ $cbydp->period() }}</option>
                @endforeach
            </x-form.select>

            <x-form.input name="year" type="number" label="Year" :value="now()->year" min="2020" max="2100" hint="Must fall within the CBYDP’s three years." required />

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
