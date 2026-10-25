<x-layouts.app title="Post an event" heading="Post an event" description="It appears on the public events page as soon as you post it.">
    <form method="POST" action="{{ route('officer.events.store') }}" class="max-w-3xl space-y-5 rounded-[12px] border border-line bg-white p-6" novalidate>
        @csrf
        <div class="grid gap-5 sm:grid-cols-2">
            <x-form.select name="kind" label="Kind" required>
                @foreach ($kinds as $kind)
                    <option value="{{ $kind->value }}" @selected(old('kind', 'event') === $kind->value)>{{ $kind->label() }}</option>
                @endforeach
            </x-form.select>
            <x-form.input name="capacity" type="number" label="Places available" min="1" hint="Leave empty for no limit." />
            <div class="sm:col-span-2">
                <x-form.input name="title" label="Name" placeholder="e.g. Basketball league opening" required autofocus />
            </div>
            <x-form.input name="starts_at" type="datetime-local" label="Starts" required />
            <x-form.input name="venue" label="Venue" placeholder="e.g. Barangay covered court" required />
            <div class="sm:col-span-2">
                <label for="description" class="block text-sm font-medium">Details <span class="font-normal text-slate-muted">(optional)</span></label>
                <textarea id="description" name="description" rows="4" class="mt-1.5 block w-full rounded-[8px] border border-line px-3.5 py-2.5 text-sm outline-none focus:border-emerald focus:ring-3 focus:ring-emerald/20">{{ old('description') }}</textarea>
            </div>
            <div class="sm:col-span-2">
                <x-form.select name="ppa_id" label="Part of a PPA (optional)" placeholder="Not linked to a PPA" placeholder-selectable>
                    @foreach ($ppas as $ppa)
                        <option value="{{ $ppa->id }}" @selected((string) old('ppa_id') === (string) $ppa->id)>{{ $ppa->title }}</option>
                    @endforeach
                </x-form.select>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-3 border-t border-line pt-5">
            <x-form.submit busy="Posting…" :block="false">Post event</x-form.submit>
            <a href="{{ route('officer.events.index') }}" class="rounded-[8px] px-4 py-2.5 text-sm font-medium text-slate-muted hover:text-navy">Cancel</a>
        </div>
    </form>
</x-layouts.app>
