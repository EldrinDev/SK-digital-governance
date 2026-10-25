<x-layouts.auth title="Create an account" heading="Create an account" subheading="Join programs, follow projects, and send feedback to your barangay SK.">
    <form method="POST" action="{{ route('register.store') }}" class="space-y-5" novalidate>
        @csrf

        <x-form.input name="name" label="Full name" autocomplete="name" required autofocus />
        <x-form.input name="email" type="email" label="Email" autocomplete="email" inputmode="email" required />

        <x-form.select name="barangay_id" label="Barangay" placeholder="Choose your barangay" required>
            @foreach ($barangays as $barangay)
                <option value="{{ $barangay->id }}" @selected((string) old('barangay_id') === (string) $barangay->id)>{{ $barangay->name }}</option>
            @endforeach
        </x-form.select>

        <x-form.input name="password" type="password" label="Password" autocomplete="new-password" hint="Use at least 8 characters." required />
        <x-form.input name="password_confirmation" type="password" label="Confirm password" autocomplete="new-password" required />

        <div>
            <label class="flex items-start gap-2.5 text-sm leading-relaxed text-navy/80">
                <input
                    type="checkbox"
                    name="privacy_consent"
                    value="1"
                    @checked(old('privacy_consent'))
                    @error('privacy_consent') aria-invalid="true" aria-describedby="privacy_consent-error" @enderror
                    class="mt-1 h-4 w-4 shrink-0 rounded border-line accent-emerald"
                >
                <span>
                    I have read the
                    <a href="{{ route('privacy') }}" target="_blank" class="font-semibold text-emerald underline underline-offset-2">privacy notice</a>
                    and agree to how my information will be used.
                </span>
            </label>
            @error('privacy_consent')
                <p id="privacy_consent-error" class="mt-1.5 flex items-start gap-1.5 text-sm text-error">
                    <svg viewBox="0 0 24 24" class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <x-form.submit busy="Creating your account…">Create account</x-form.submit>
    </form>

    <p class="mt-8 text-sm text-slate-muted">
        Already have an account?
        <a href="{{ route('login') }}" class="font-semibold text-emerald underline-offset-4 hover:underline">Log in</a>
    </p>
</x-layouts.auth>
