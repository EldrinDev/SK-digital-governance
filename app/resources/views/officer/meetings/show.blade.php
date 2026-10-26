@php
    use App\AttendanceStatus;

    $statusFor = $meeting->attendees->mapWithKeys(fn ($member) => [$member->id => $member->pivot->status]);
    $quorum = $meeting->hasQuorum();
@endphp
<x-layouts.app :title="$meeting->title" :heading="$meeting->title" :description="$meeting->kind->label().' · '.$meeting->held_on->format('F j, Y').($meeting->venue ? ' · '.$meeting->venue : '')">
    <p class="mb-6 text-sm"><a href="{{ route('officer.meetings.index') }}" class="font-medium text-slate-muted hover:text-emerald">&larr; All meetings</a></p>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="space-y-6">
            <section class="rounded-[12px] border border-line bg-white" aria-labelledby="attendance-heading">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-5 py-4">
                    <h2 id="attendance-heading" class="font-display text-lg font-semibold">Attendance</h2>
                    @if ($quorum === true)
                        <x-ui.badge tone="success">Quorum: {{ $meeting->presentCount() }} of {{ $meeting->attendees->count() }} present</x-ui.badge>
                    @elseif ($quorum === false)
                        <x-ui.badge tone="error">No quorum: {{ $meeting->presentCount() }} of {{ $meeting->attendees->count() }} present</x-ui.badge>
                    @endif
                </div>

                @if ($members->isEmpty())
                    <p class="px-5 py-6 text-sm text-slate-muted">No officer accounts in this barangay yet. Ask the administrator to add the council members.</p>
                @else
                    <form method="POST" action="{{ route('officer.meetings.attendance.update', $meeting) }}">
                        @csrf
                        @method('PUT')
                        <ul class="divide-y divide-line">
                            @foreach ($members as $member)
                                <li class="flex flex-wrap items-center justify-between gap-3 px-5 py-3">
                                    <span class="text-sm font-medium">{{ $member->name }}</span>
                                    <fieldset class="flex gap-1.5">
                                        <legend class="sr-only">Attendance of {{ $member->name }}</legend>
                                        @foreach (AttendanceStatus::cases() as $status)
                                            <label class="cursor-pointer rounded-[8px] border border-line px-3 py-1.5 text-xs font-medium has-[:checked]:border-emerald has-[:checked]:bg-emerald/10 has-[:checked]:text-emerald">
                                                <input type="radio" name="attendance[{{ $member->id }}]" value="{{ $status->value }}" @checked(($statusFor[$member->id] ?? null) === $status->value) class="sr-only">
                                                {{ $status->label() }}
                                            </label>
                                        @endforeach
                                    </fieldset>
                                </li>
                            @endforeach
                        </ul>
                        @error('attendance')
                            <p class="px-5 pt-3 text-sm text-error">{{ $message }}</p>
                        @enderror
                        <div class="border-t border-line p-5">
                            <x-form.submit busy="Saving…" :block="false">Save attendance</x-form.submit>
                        </div>
                    </form>
                @endif
            </section>

            <section class="rounded-[12px] border border-line bg-white" aria-labelledby="resolutions-heading">
                <h2 id="resolutions-heading" class="border-b border-line px-5 py-4 font-display text-lg font-semibold">Resolutions from this session</h2>
                @if ($meeting->resolutions->isEmpty())
                    <p class="px-5 py-6 text-sm text-slate-muted">None recorded.</p>
                @else
                    <ul class="divide-y divide-line">
                        @foreach ($meeting->resolutions as $resolution)
                            <li class="flex flex-wrap items-start justify-between gap-3 px-5 py-3 text-sm">
                                <div>
                                    <p class="font-medium">{{ $resolution->number }}</p>
                                    <p class="text-navy/75">{{ $resolution->title }}</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <x-ui.badge :tone="$resolution->outcome->tone()">{{ $resolution->outcome->label() }}</x-ui.badge>
                                    @if ($resolution->file_path)
                                        <a href="{{ route('officer.resolutions.show', $resolution) }}" class="text-xs font-semibold text-emerald hover:underline">PDF</a>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif

                @can('update', $meeting)
                    <form method="POST" action="{{ route('officer.resolutions.store') }}" enctype="multipart/form-data" class="grid gap-4 border-t border-line p-5 sm:grid-cols-2" novalidate>
                        @csrf
                        <input type="hidden" name="meeting_id" value="{{ $meeting->id }}">
                        <x-form.input name="number" label="Resolution number" placeholder="e.g. Res. No. 12, s. {{ now()->year }}" required />
                        <x-form.select name="outcome" label="Outcome" required>
                            @foreach ($outcomes as $outcome)
                                <option value="{{ $outcome->value }}" @selected(old('outcome', 'approved') === $outcome->value)>{{ $outcome->label() }}</option>
                            @endforeach
                        </x-form.select>
                        <div class="sm:col-span-2">
                            <x-form.input name="title" label="What it’s about" placeholder="e.g. Approving the 2027 ABYIP" required />
                        </div>
                        <x-form.input name="adopted_on" type="date" label="Date" :value="$meeting->held_on->toDateString()" required />
                        <div>
                            <label for="file" class="block text-sm font-medium">Signed copy <span class="font-normal text-slate-muted">(PDF, optional)</span></label>
                            <input id="file" name="file" type="file" accept="application/pdf" class="mt-1.5 block w-full rounded-[8px] border border-line bg-white text-sm text-navy/80 file:mr-3 file:border-0 file:border-r file:border-line file:bg-mist file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-navy">
                            @error('file')
                                <p class="mt-1.5 text-sm text-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <x-form.submit busy="Adding…" :block="false">Add to register</x-form.submit>
                        </div>
                    </form>
                @endcan
            </section>
        </div>

        <div class="space-y-6">
            <section class="rounded-[12px] border border-line bg-white p-5">
                <h2 class="font-display text-lg font-semibold">Minutes</h2>
                @if ($meeting->minutes_path)
                    <a href="{{ route('officer.meetings.minutes.show', $meeting) }}" class="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-emerald hover:underline">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/></svg>
                        Download minutes (PDF)
                    </a>
                @else
                    <p class="mt-2 text-sm text-slate-muted">Not uploaded yet.</p>
                @endif
                @can('recordMinutes', $meeting)
                    <form method="POST" action="{{ route('officer.meetings.minutes.store', $meeting) }}" enctype="multipart/form-data" class="mt-4 space-y-3" novalidate>
                        @csrf
                        <label for="minutes" class="sr-only">Minutes PDF</label>
                        <input id="minutes" name="minutes" type="file" accept="application/pdf" required class="block w-full rounded-[8px] border border-line bg-white text-sm text-navy/80 file:mr-3 file:border-0 file:border-r file:border-line file:bg-mist file:px-3 file:py-2 file:text-sm file:font-semibold file:text-navy">
                        @error('minutes')
                            <p class="text-sm text-error">{{ $message }}</p>
                        @enderror
                        <x-form.submit busy="Uploading…" tone="navy">{{ $meeting->minutes_path ? 'Replace minutes' : 'Upload minutes' }}</x-form.submit>
                    </form>
                @endcan
            </section>

            @if ($meeting->agenda)
                <section class="rounded-[12px] border border-line bg-white p-5">
                    <h2 class="font-display text-lg font-semibold">Agenda</h2>
                    <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-navy/80">{{ $meeting->agenda }}</p>
                </section>
            @endif
        </div>
    </div>
</x-layouts.app>
