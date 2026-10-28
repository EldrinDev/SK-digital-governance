<x-layouts.app :title="'Edit '.$ppa->title" :heading="'Edit '.$ppa->title" :description="'Planned details can change until the council approves ABYIP '.$abyip->year.'.'">
    <x-ui.form-page>
    <form method="POST" action="{{ route('officer.ppas.update', $ppa) }}" class="rounded-[12px] border border-line bg-white p-6" novalidate>
        @csrf
        @method('PUT')

        @include('officer.ppas._form', ['ppa' => $ppa])

        <div class="mt-8 flex flex-wrap items-center gap-3 border-t border-line pt-6">
            <x-form.submit busy="Saving…" :block="false">Save changes</x-form.submit>
            <a href="{{ route('officer.ppas.show', $ppa) }}" class="rounded-[8px] px-4 py-2.5 text-sm font-medium text-slate-muted hover:text-navy">Cancel</a>
        </div>
    </form>

    <form method="POST" action="{{ route('officer.ppas.destroy', $ppa) }}" class="mt-6 rounded-[12px] border border-error/30 bg-white p-6">
        @csrf
        @method('DELETE')
        <h2 class="font-display text-base font-semibold">Remove this PPA</h2>
        <p class="mt-1 text-sm text-slate-muted">Takes it out of the draft ABYIP. The removal is kept in the audit trail.</p>
        <button type="submit" class="mt-4 rounded-[8px] border border-error/50 px-4 py-2 text-sm font-semibold text-[#b91c1c] hover:bg-error/5">Remove PPA</button>
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
