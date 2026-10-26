<x-layouts.app title="Account settings" heading="Account settings" description="Your name, email and password.">
    <div class="grid max-w-3xl gap-6">
        <section class="rounded-[12px] border border-line bg-white p-6">
            <h2 class="font-display text-lg font-semibold">Your details</h2>
            <dl class="mt-3 grid gap-x-6 gap-y-1 text-sm text-slate-muted sm:grid-cols-2">
                <div><dt class="inline">Role:</dt> <dd class="inline text-navy">{{ $user->role->label() }}</dd></div>
                @if ($user->barangay)
                    <div><dt class="inline">Barangay:</dt> <dd class="inline text-navy">{{ $user->barangay->name }}</dd></div>
                @endif
                @if ($user->position)
                    <div><dt class="inline">Position:</dt> <dd class="inline text-navy">{{ $user->position->label() }}</dd></div>
                @endif
                @if ($user->privacy_consented_at)
                    <div><dt class="inline">Privacy notice accepted:</dt> <dd class="inline text-navy">{{ $user->privacy_consented_at->format('F j, Y') }}</dd></div>
                @endif
            </dl>
            <p class="mt-2 text-xs text-slate-muted">Only an administrator can change your role, barangay or position.</p>

            <form method="POST" action="{{ route('account.update') }}" class="mt-5 space-y-4">
                @csrf
                @method('PATCH')
                <x-form.input name="name" label="Full name" :value="$user->name" autocomplete="name" required />
                @if ($user->role->isStaff())
                    <x-form.input name="email" type="email" label="Email" :value="$user->email" hint="Staff emails are managed by an administrator." readonly class="bg-mist text-slate-muted" />
                @else
                    <x-form.input name="email" type="email" label="Email" :value="$user->email" autocomplete="email" required />
                    <x-form.input name="email_password" type="password" label="Current password" hint="Needed only when you change your email." autocomplete="current-password" />
                @endif
                <x-form.submit busy="Saving…" :block="false">Save details</x-form.submit>
            </form>
        </section>

        <section class="rounded-[12px] border border-line bg-white p-6">
            <h2 class="font-display text-lg font-semibold">Change password</h2>
            <p class="mt-1 text-sm text-slate-muted">You stay signed in here. Every other device is signed out.</p>

            <form method="POST" action="{{ route('account.password.update') }}" class="mt-5 space-y-4">
                @csrf
                @method('PUT')
                <x-form.input name="current_password" type="password" label="Current password" autocomplete="current-password" required />
                <x-form.input name="password" type="password" label="New password" hint="At least 8 characters." autocomplete="new-password" required />
                <x-form.input name="password_confirmation" type="password" label="Type the new password again" autocomplete="new-password" required />
                <x-form.submit busy="Changing…" tone="navy" :block="false">Change password</x-form.submit>
            </form>
        </section>

        @if ($user->role === \App\UserRole::Youth)
            <section class="rounded-[12px] border border-line bg-white p-6">
                <h2 class="font-display text-lg font-semibold">Your data</h2>
                <p class="mt-1 text-sm leading-relaxed text-slate-muted">
                    Under the Data Privacy Act of 2012 you may get a copy of what the platform keeps about you: your account, event registrations and attendance, feedback, and survey answers.
                </p>
                <a href="{{ route('account.data') }}" class="mt-4 inline-flex rounded-[8px] border border-line px-4 py-2.5 text-sm font-semibold hover:border-navy">Download my data</a>
            </section>

            <section class="rounded-[12px] border border-error/30 bg-white p-6">
                <h2 class="font-display text-lg font-semibold text-[#b91c1c]">Close your account</h2>
                <p class="mt-1 text-sm leading-relaxed text-slate-muted">
                    Your name and email are erased and you can no longer sign in. Attendance counts and survey tallies stay, without your name. This can’t be undone.
                </p>
                <details class="mt-4" @if ($errors->has('confirm_password')) open @endif>
                    <summary class="cursor-pointer text-sm font-semibold text-[#b91c1c]">Close my account…</summary>
                    <form method="POST" action="{{ route('account.destroy') }}" class="mt-4 max-w-sm space-y-4">
                        @csrf
                        @method('DELETE')
                        <x-form.input name="confirm_password" type="password" label="Enter your password to confirm" autocomplete="current-password" required />
                        <button type="submit" class="rounded-[8px] bg-[#b91c1c] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#991b1b]">Close my account</button>
                    </form>
                </details>
            </section>
        @endif
    </div>
</x-layouts.app>
