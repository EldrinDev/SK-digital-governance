<x-layouts.app title="Choose the Facebook Page" heading="Which one is your SK’s Page?" description="Your Facebook account manages more than one Page. Pick the one SK {{ auth()->user()->barangay->name }} posts on.">
    <x-ui.form-page>
        <form method="POST" action="{{ route('officer.facebook.store') }}" class="space-y-5 rounded-[12px] border border-line bg-white p-6">
            @csrf
            <fieldset>
                <legend class="sr-only">Facebook Pages</legend>
                <div class="space-y-2">
                    @foreach ($pages as $page)
                        <label class="flex cursor-pointer items-center gap-3 rounded-[10px] border border-line p-3.5 hover:border-navy/30 has-[:checked]:border-[#1877f2] has-[:checked]:bg-[#1877f2]/[0.04]">
                            <input type="radio" name="page_id" value="{{ $page['id'] }}" @checked($loop->first) class="h-4 w-4 accent-[#1877f2]">
                            <span class="text-sm font-semibold">{{ $page['name'] }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
            <div class="flex flex-wrap items-center gap-3">
                <x-form.submit busy="Connecting…" :block="false">Connect this Page</x-form.submit>
                <a href="{{ $returnTo }}" class="rounded-[8px] px-4 py-2.5 text-sm font-medium text-slate-muted hover:text-navy">Cancel</a>
            </div>
        </form>
    </x-ui.form-page>
</x-layouts.app>
