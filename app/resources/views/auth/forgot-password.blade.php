<x-layouts.auth title="Forgot password" heading="Forgot your password?" subheading="Enter the email you use to log in and we’ll send you a link to choose a new password.">
    <form method="POST" action="{{ route('password.email') }}" class="space-y-5" novalidate>
        @csrf

        <x-form.input name="email" type="email" label="Email" autocomplete="email" inputmode="email" required autofocus />

        <x-form.submit busy="Sending link…">Send reset link</x-form.submit>
    </form>

    <p class="mt-8 border-t border-line pt-6 text-sm text-slate-muted">
        Remembered it?
        <a href="{{ route('login') }}" class="font-semibold text-emerald underline-offset-4 hover:underline">Back to login</a>
        ·
        <a href="{{ route('staff.login') }}" class="font-semibold text-emerald underline-offset-4 hover:underline">SK access</a>
    </p>
</x-layouts.auth>
