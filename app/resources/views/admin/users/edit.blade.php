<x-layouts.app :title="$user->name" :heading="$user->name" description="Change this account’s details, role, workspace, or whether it can sign in.">
    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="max-w-3xl rounded-[12px] border border-line bg-white p-6" novalidate>
        @csrf
        @method('PUT')

        @include('admin.users._form', ['user' => $user])

        <fieldset class="mt-8 border-t border-line pt-6">
            <legend class="sr-only">Account status</legend>
            <input type="hidden" name="is_active" value="0">
            <label class="flex items-start gap-3">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->isActive())) class="mt-1 h-4 w-4 rounded border-line accent-emerald">
                <span>
                    <span class="block text-sm font-medium">Account can sign in</span>
                    <span class="block text-sm text-slate-muted">Untick to deactivate. The person is signed out on their next click and can’t log in until you turn this back on. Their records stay.</span>
                </span>
            </label>
            @error('is_active')
                <p class="mt-2 text-sm text-error">{{ $message }}</p>
            @enderror
        </fieldset>

        <div class="mt-8 flex flex-wrap items-center gap-3 border-t border-line pt-6">
            <x-form.submit busy="Saving…" :block="false">Save changes</x-form.submit>
            <a href="{{ route('admin.users.index') }}" class="rounded-[8px] px-4 py-2.5 text-sm font-medium text-slate-muted hover:text-navy">Cancel</a>
        </div>
    </form>

    <p class="mt-4 max-w-3xl text-xs text-slate-muted">
        Account created {{ $user->created_at->format('M j, Y') }}.
        @if ($user->privacy_consented_at)
            Privacy notice accepted {{ $user->privacy_consented_at->format('M j, Y') }}.
        @endif
    </p>
</x-layouts.app>
