@php
    use App\Http\Requests\Auth\StaffLoginRequest;
@endphp
<x-layouts.auth title="SK access" heading="SK access" subheading="For barangay SK officers, the SK Federation, and system administrators.">
    <p class="mb-6 flex items-start gap-3 rounded-[12px] bg-mist p-4 text-sm leading-relaxed text-navy/75">
        <svg viewBox="0 0 24 24" class="mt-0.5 h-5 w-5 shrink-0 text-navy" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 4 6v6c0 5 3.5 8.5 8 9.5 4.5-1 8-4.5 8-9.5V6l-8-3Z"/><path d="m9 12 2 2 4-4"/></svg>
        Choose the workspace your account was set up for. Officers can only open their own barangay.
    </p>

    <x-form.alert />

    <form method="POST" action="{{ route('staff.login.store') }}" class="space-y-5" novalidate>
        @csrf

        <x-form.select name="workspace" label="Workspace" placeholder="Choose your workspace" required autofocus>
            <optgroup label="Barangay SK office">
                @foreach ($barangays as $barangay)
                    <option value="{{ $barangay->id }}" @selected(old('workspace') === (string) $barangay->id)>{{ $barangay->name }}</option>
                @endforeach
            </optgroup>
            <optgroup label="City-wide">
                <option value="{{ StaffLoginRequest::FEDERATION_WORKSPACE }}" @selected(old('workspace') === StaffLoginRequest::FEDERATION_WORKSPACE)>SK Federation</option>
                <option value="{{ StaffLoginRequest::ADMINISTRATION_WORKSPACE }}" @selected(old('workspace') === StaffLoginRequest::ADMINISTRATION_WORKSPACE)>System administration</option>
            </optgroup>
        </x-form.select>

        <x-form.input name="email" type="email" label="Email" autocomplete="email" inputmode="email" required />
        <x-form.input name="password" type="password" label="Password" autocomplete="current-password" required />
        <p class="-mt-2 text-right text-sm">
            <a href="{{ route('password.request') }}" class="font-medium text-emerald underline-offset-4 hover:underline">Forgot password?</a>
        </p>

        <label class="flex items-center gap-2.5 text-sm text-navy/80">
            <input type="checkbox" name="remember" value="1" @checked(old('remember')) class="h-4 w-4 rounded border-line accent-navy">
            Keep me logged in on this device
        </label>

        <x-form.submit busy="Logging in…" tone="navy">Log in to workspace</x-form.submit>
    </form>

    <p class="mt-8 text-sm text-slate-muted">
        Not SK staff?
        <a href="{{ route('login') }}" class="font-semibold text-emerald underline-offset-4 hover:underline">Go to the regular login</a>
    </p>
</x-layouts.auth>
