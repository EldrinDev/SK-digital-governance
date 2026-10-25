<x-layouts.app title="Add PPA" heading="Add PPA" :description="'A program, project, or activity in ABYIP '.$abyip->year.'.'">
    <form method="POST" action="{{ route('officer.ppas.store', $abyip) }}" class="max-w-3xl rounded-[12px] border border-line bg-white p-6" novalidate>
        @csrf

        @include('officer.ppas._form')

        <div class="mt-8 flex flex-wrap items-center gap-3 border-t border-line pt-6">
            <x-form.submit busy="Adding…" :block="false">Add PPA</x-form.submit>
            <a href="{{ route('officer.abyips.show', $abyip) }}" class="rounded-[8px] px-4 py-2.5 text-sm font-medium text-slate-muted hover:text-navy">Cancel</a>
        </div>
    </form>
</x-layouts.app>
