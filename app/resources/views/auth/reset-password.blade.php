<x-layouts.auth title="Choose a new password" heading="Choose a new password" subheading="Pick a password you don’t use anywhere else.">
    <x-form.alert />

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label for="email" class="block text-sm font-medium text-navy">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $email) }}" readonly class="mt-1.5 block w-full rounded-[8px] border border-line bg-mist px-3.5 py-2.5 text-navy/80">
        </div>

        <x-form.input name="password" type="password" label="New password" autocomplete="new-password" hint="Use at least 8 characters." required autofocus />
        <x-form.input name="password_confirmation" type="password" label="Confirm new password" autocomplete="new-password" required />

        <x-form.submit busy="Saving…">Save new password</x-form.submit>
    </form>
</x-layouts.auth>
