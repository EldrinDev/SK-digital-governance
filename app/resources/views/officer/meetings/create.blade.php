<x-layouts.app title="Record a session" heading="Record a session" description="Add the session first. You’ll mark attendance, upload the minutes, and add resolutions on the next page.">
    <x-ui.form-page>
    <form method="POST" action="{{ route('officer.meetings.store') }}" class="space-y-5 rounded-[12px] border border-line bg-white p-6" novalidate>
        @csrf

        <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <x-form.input name="title" label="Session name" placeholder="e.g. 3rd regular session" required autofocus />
            </div>
            <x-form.select name="kind" label="Kind" required>
                @foreach ($kinds as $kind)
                    <option value="{{ $kind->value }}" @selected(old('kind', 'regular') === $kind->value)>{{ $kind->label() }}</option>
                @endforeach
            </x-form.select>
            <x-form.input name="held_on" type="date" label="Date" :value="now()->toDateString()" required />
            <div class="sm:col-span-2">
                <x-form.input name="venue" label="Venue" placeholder="e.g. Barangay hall" />
            </div>
            <div class="sm:col-span-2">
                <label for="agenda" class="block text-sm font-medium">Agenda <span class="font-normal text-slate-muted">(optional)</span></label>
                <textarea id="agenda" name="agenda" rows="4" class="mt-1.5 block w-full rounded-[8px] border border-line px-3.5 py-2.5 text-sm outline-none focus:border-emerald focus:ring-3 focus:ring-emerald/20">{{ old('agenda') }}</textarea>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 border-t border-line pt-5">
            <x-form.submit busy="Saving…" :block="false">Record session</x-form.submit>
            <a href="{{ route('officer.meetings.index') }}" class="rounded-[8px] px-4 py-2.5 text-sm font-medium text-slate-muted hover:text-navy">Cancel</a>
        </div>
    </form>

        <x-slot:guide>
            <x-ui.guide-steps title="Recording a session" :steps="[
                ['Add the session', 'Its name, kind, date and venue.'],
                ['Mark attendance', 'Who was present, absent or excused. This shows whether there was a quorum.'],
                ['Upload the minutes', 'The signed minutes as a PDF.'],
                ['Add its resolutions', 'Each one goes into your register of resolutions.'],
            ]" />
        </x-slot:guide>
    </x-ui.form-page>
</x-layouts.app>
