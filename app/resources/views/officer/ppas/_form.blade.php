@php
    /** @var \App\Models\Ppa|null $ppa */
    $ppa ??= null;
    $year = $abyip->year;
@endphp
<div class="grid gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-form.select name="cbydp_priority_id" label="CBYDP priority it serves" placeholder="Choose a priority" required>
            @foreach ($priorities->groupBy(fn ($priority) => $priority->center->label()) as $center => $group)
                <optgroup label="{{ $center }}">
                    @foreach ($group as $priority)
                        <option value="{{ $priority->id }}" @selected((string) old('cbydp_priority_id', $ppa?->cbydp_priority_id) === (string) $priority->id)>{{ $priority->title }}</option>
                    @endforeach
                </optgroup>
            @endforeach
        </x-form.select>
    </div>

    <div class="sm:col-span-2">
        <x-form.input name="title" label="Name" :value="$ppa?->title" placeholder="e.g. Youth Leadership Summit" required />
    </div>

    <div class="sm:col-span-2">
        <label for="description" class="block text-sm font-medium">Description <span class="font-normal text-slate-muted">(optional)</span></label>
        <textarea id="description" name="description" rows="3" class="mt-1.5 block w-full rounded-[8px] border border-line px-3.5 py-2.5 text-sm outline-none focus:border-emerald focus:ring-3 focus:ring-emerald/20">{{ old('description', $ppa?->description) }}</textarea>
    </div>

    <x-form.input name="target" label="Target" :value="$ppa?->target" placeholder="e.g. 150 youth trained" required />
    <x-form.input name="budget" type="number" label="Budget (₱)" :value="$ppa?->budget" min="0" step="0.01" inputmode="decimal" required />

    <x-form.input name="starts_on" type="date" label="Starts" :value="$ppa?->starts_on?->toDateString()" :min="$year.'-01-01'" :max="$year.'-12-31'" required />
    <x-form.input name="ends_on" type="date" label="Ends" :value="$ppa?->ends_on?->toDateString()" :min="$year.'-01-01'" :max="$year.'-12-31'" required />
</div>
