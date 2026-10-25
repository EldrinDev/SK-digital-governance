<x-layouts.app title="Add account" heading="Add account" description="We’ll email the person a link to choose their own password. You never see or set it.">
    <form method="POST" action="{{ route('admin.users.store') }}" class="max-w-3xl rounded-[12px] border border-line bg-white p-6" novalidate>
        @csrf

        @include('admin.users._form')

        <div class="mt-8 flex flex-wrap items-center gap-3 border-t border-line pt-6">
            <x-form.submit busy="Creating…" :block="false">Create account and send link</x-form.submit>
            <a href="{{ route('admin.users.index') }}" class="rounded-[8px] px-4 py-2.5 text-sm font-medium text-slate-muted hover:text-navy">Cancel</a>
        </div>
    </form>
</x-layouts.app>
