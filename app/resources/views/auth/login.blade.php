<x-layouts.auth title="Log in" heading="Log in" subheading="Welcome back. Enter your email and password to continue.">
    <x-form.alert />

    <form method="POST" action="{{ route('login.store') }}" class="space-y-5" novalidate>
        @csrf

        <x-form.input name="email" type="email" label="Email" autocomplete="email" inputmode="email" required :autofocus="! old('email')" />
        <x-form.input name="password" type="password" label="Password" autocomplete="current-password" required :autofocus="(bool) old('email')" />
        <p class="-mt-2 text-right text-sm">
            <a href="{{ route('password.request') }}" class="font-medium text-emerald underline-offset-4 hover:underline">Forgot password?</a>
        </p>

        <label class="flex items-center gap-2.5 text-sm text-navy/80">
            <input type="checkbox" name="remember" value="1" @checked(old('remember')) class="h-4 w-4 rounded border-line accent-emerald">
            Keep me logged in on this device
        </label>

        <x-form.submit busy="Logging in…">Log in</x-form.submit>
    </form>

    <div class="mt-8 flex flex-wrap items-center justify-between gap-3 border-t border-line pt-6">
        <p class="text-sm text-slate-muted">
            New here?
            <a href="{{ route('register') }}" class="font-semibold text-emerald underline-offset-4 hover:underline">Create an account</a>
        </p>

        <a href="{{ route('staff.login') }}" class="inline-flex items-center gap-1.5 rounded-full border border-line px-3 py-1 text-xs font-medium text-slate-muted transition-colors hover:border-navy hover:text-navy">
            <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 4 6v6c0 5 3.5 8.5 8 9.5 4.5-1 8-4.5 8-9.5V6l-8-3Z"/></svg>
            SK access
        </a>
    </div>
</x-layouts.auth>
