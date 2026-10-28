<x-layouts.app title="Add PPA" heading="Add PPA" :description="'A program, project, or activity in ABYIP '.$abyip->year.'.'">
    <x-ui.form-page>
    <form method="POST" action="{{ route('officer.ppas.store', $abyip) }}" class="rounded-[12px] border border-line bg-white p-6" novalidate>
        @csrf

        @include('officer.ppas._form')

        <div class="mt-8 flex flex-wrap items-center gap-3 border-t border-line pt-6">
            <x-form.submit busy="Adding…" :block="false">Add PPA</x-form.submit>
            <a href="{{ route('officer.abyips.show', $abyip) }}" class="rounded-[8px] px-4 py-2.5 text-sm font-medium text-slate-muted hover:text-navy">Cancel</a>
        </div>
    </form>

        <x-slot:guide>
            <x-ui.guide-steps title="A PPA from plan to report" :steps="[
                ['Draft the PPAs', 'Add every program, project and activity while the ABYIP is a draft.'],
                ['Record the council’s approval', 'The PPAs, budgets and schedules lock.'],
                ['Carry them out', 'Add milestones, record spending, and upload photos or documents as evidence.'],
                ['Report', 'Submit the accomplishment report to the SK Federation.'],
            ]" />
        </x-slot:guide>
    </x-ui.form-page>
</x-layouts.app>
