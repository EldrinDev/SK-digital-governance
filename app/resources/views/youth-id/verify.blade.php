<x-layouts.app title="Check a youth ID" heading="Check a youth ID">
    <div class="max-w-xl">
        @if ($youth === null)
            <section class="rounded-[16px] border border-error/30 bg-white p-6 text-center">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-error/10 text-[#b91c1c]" aria-hidden="true">
                    <svg viewBox="0 0 24 24" class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </span>
                <h2 class="mt-4 font-display text-xl font-semibold">This ID isn’t valid</h2>
                <p class="mt-2 text-sm leading-relaxed text-slate-muted">No youth has the code <span class="font-semibold text-navy">{{ $code }}</span>. It may have been replaced by a newer ID, or the account was closed. Ask the youth to open their ID again on their phone.</p>
            </section>
        @else
            <section @class(['overflow-hidden rounded-[16px] border bg-white', 'border-emerald/40' => $youth->isActive(), 'border-error/30' => ! $youth->isActive()])>
                <div @class(['flex items-center gap-3 px-5 py-3 text-sm font-semibold', 'bg-emerald text-white' => $youth->isActive(), 'bg-error/10 text-[#b91c1c]' => ! $youth->isActive()])>
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        @if ($youth->isActive())
                            <circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>
                        @else
                            <circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/>
                        @endif
                    </svg>
                    {{ $youth->isActive() ? 'Valid youth member ID' : 'This account is deactivated' }}
                </div>
                <div class="flex items-center gap-4 p-5">
                    <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-navy font-display text-xl font-semibold text-white" aria-hidden="true">{{ $youth->initials() }}</span>
                    <div class="min-w-0">
                        <p class="font-display text-2xl font-semibold leading-tight">{{ $youth->name }}</p>
                        <p class="text-slate-muted">Barangay {{ $youth->barangay?->name }}</p>
                    </div>
                </div>
                <dl class="grid grid-cols-3 gap-3 border-t border-line px-5 py-4 text-sm">
                    <div><dt class="text-xs text-slate-muted">ID number</dt><dd class="font-semibold">{{ $code }}</dd></div>
                    <div><dt class="text-xs text-slate-muted">Member since</dt><dd class="font-semibold">{{ $youth->created_at->format('M Y') }}</dd></div>
                    <div><dt class="text-xs text-slate-muted">Events attended</dt><dd class="font-semibold tabular-nums">{{ $attendedCount }}</dd></div>
                </dl>
            </section>

            @if ($canCheckIn)
                <section class="mt-6 rounded-[16px] border border-line bg-white" aria-labelledby="checkin-heading">
                    <h2 id="checkin-heading" class="border-b border-line px-5 py-4 font-display text-lg font-semibold">Check in to today’s event</h2>
                    @error('attendance')
                        <p role="alert" class="mx-5 mt-4 rounded-[8px] bg-error/5 p-3 text-sm text-[#b91c1c]">{{ $message }}</p>
                    @enderror
                    <ul class="divide-y divide-line">
                        @forelse ($checkIns as $registration)
                            <li class="flex flex-wrap items-center justify-between gap-3 px-5 py-4">
                                <div>
                                    <p class="font-medium">{{ $registration->event->title }}</p>
                                    <p class="text-xs text-slate-muted">{{ $registration->event->starts_at->format('g:i A') }}, {{ $registration->event->venue }}</p>
                                </div>
                                @if ($registration->attended())
                                    <span class="rounded-full bg-emerald/10 px-3 py-1 text-sm font-semibold text-emerald">Checked in {{ $registration->attended_at->format('g:i A') }}</span>
                                @else
                                    <form method="POST" action="{{ route('officer.events.registrations.update', [$registration->event, $registration]) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="attended" value="1">
                                        <x-form.submit busy="Checking in…" :block="false" class="py-2">Check in</x-form.submit>
                                    </form>
                                @endif
                            </li>
                        @empty
                            <li class="px-5 py-5 text-sm text-slate-muted">{{ str($youth->name)->before(' ') }} isn’t registered for any of your SK’s events today.</li>
                        @endforelse
                    </ul>
                </section>
            @endif
        @endif
    </div>
</x-layouts.app>
