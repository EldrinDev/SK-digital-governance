{{--
    Records the SK council's approval of a plan. Expects:
    $plan (Cbydp|Abyip), $action (form URL), $resolutionRoute (download URL), $planName (e.g. "ABYIP 2026"),
    $lockNote (what approval locks), $emptyNote (shown when the plan can't be approved yet, or null).
--}}
@if ($plan->isApproved())
    <section class="rounded-[12px] border border-success/30 bg-success/5 p-5">
        <h2 class="flex items-center gap-2 font-display text-lg font-semibold">
            <svg viewBox="0 0 24 24" class="h-5 w-5 text-success" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
            Council approval recorded
        </h2>
        <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-3">
            <div><dt class="text-slate-muted">Resolution</dt><dd class="font-medium">{{ $plan->resolution_number }}</dd></div>
            <div><dt class="text-slate-muted">Approved on</dt><dd class="font-medium">{{ $plan->approved_on->format('F j, Y') }}</dd></div>
            <div><dt class="text-slate-muted">Recorded by</dt><dd class="font-medium">{{ $plan->approvalRecorder?->name ?? '—' }}</dd></div>
        </dl>
        @if ($plan->resolution_path)
            <a href="{{ $resolutionRoute }}" class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-emerald hover:underline">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/></svg>
                Download signed resolution (PDF)
            </a>
        @endif
    </section>
@else
    <section class="rounded-[12px] border border-line bg-white p-5" aria-labelledby="approval-heading">
        <h2 id="approval-heading" class="font-display text-lg font-semibold">Record council approval</h2>
        <p class="mt-1 text-sm leading-relaxed text-slate-muted">
            After the SK council approves {{ $planName }} in session, record its resolution here. {{ $lockNote }}
        </p>

        @if ($emptyNote)
            <p class="mt-4 rounded-[8px] bg-mist p-3 text-sm text-navy/80">{{ $emptyNote }}</p>
        @else
            @error('plan')
                <p class="mt-4 text-sm text-error">{{ $message }}</p>
            @enderror
            <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="mt-5 space-y-4" novalidate>
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.input name="resolution_number" label="Resolution number" placeholder="e.g. Res. No. 12, s. {{ now()->year }}" required />
                    <x-form.input name="approved_on" type="date" label="Date approved" :max="now()->toDateString()" required />
                </div>
                <div>
                    <label for="resolution_file" class="block text-sm font-medium text-navy">Signed resolution (PDF, up to 10 MB)</label>
                    <input id="resolution_file" name="resolution_file" type="file" accept="application/pdf" required
                        @error('resolution_file') aria-invalid="true" aria-describedby="resolution_file-error" @enderror
                        class="mt-1.5 block w-full rounded-[8px] border border-line bg-white text-sm text-navy/80 file:mr-3 file:border-0 file:border-r file:border-line file:bg-mist file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-navy">
                    @error('resolution_file')
                        <p id="resolution_file-error" class="mt-1.5 text-sm text-error">{{ $message }}</p>
                    @enderror
                </div>
                <x-form.submit busy="Recording…" tone="navy" :block="false">Record approval and lock</x-form.submit>
            </form>
        @endif
    </section>
@endif
