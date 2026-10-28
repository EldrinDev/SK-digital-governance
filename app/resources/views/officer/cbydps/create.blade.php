<x-layouts.app title="New CBYDP" heading="New CBYDP" description="The Comprehensive Barangay Youth Development Plan covers three years. You’ll add the youth priorities next.">
    <x-ui.form-page>
    <form method="POST" action="{{ route('officer.cbydps.store') }}" class="rounded-[12px] border border-line bg-white p-6" novalidate>
        @csrf

        <x-form.input name="start_year" type="number" label="First year of the plan" :value="$suggestedStartYear" min="2020" max="2100" hint="The plan runs for three years, e.g. 2026 to 2028." required autofocus />

        <div class="mt-6 flex flex-wrap items-center gap-3">
            <x-form.submit busy="Starting…" :block="false">Start CBYDP</x-form.submit>
            <a href="{{ route('officer.plans.index') }}" class="rounded-[8px] px-4 py-2.5 text-sm font-medium text-slate-muted hover:text-navy">Cancel</a>
        </div>
    </form>

        <x-slot:guide>
            <x-ui.guide-steps title="From plan to programs" :steps="[
                ['Start the CBYDP', 'Pick its first year. It covers three years.'],
                ['Add the youth priorities', 'What the youth need, grouped by center of participation.'],
                ['Record the council’s approval', 'Upload the signed resolution. The plan locks.'],
                ['Start an ABYIP for each year', 'Each year’s programs, projects and activities come from the approved plan.'],
            ]" />
        </x-slot:guide>
    </x-ui.form-page>
</x-layouts.app>
