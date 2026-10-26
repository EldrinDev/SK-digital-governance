<x-layouts.app :title="'SK '.$barangay->name" :heading="'SK '.$barangay->name" description="The logo and Facebook page shown on this SK’s posts, events and transparency board.">
    <p class="mb-6 text-sm"><a href="{{ route('admin.barangays.index') }}" class="font-medium text-slate-muted hover:text-emerald">&larr; Barangays</a></p>

    <form method="POST" action="{{ route('admin.barangays.profile.update', $barangay) }}" enctype="multipart/form-data" class="grid max-w-4xl gap-6 lg:grid-cols-[16rem_minmax(0,1fr)]" novalidate>
        @csrf
        @method('PUT')

        <section class="rounded-[12px] border border-line bg-white p-5 text-center">
            <h2 class="text-sm font-semibold">Current logo</h2>
            <img src="{{ $barangay->logoUrl() }}" alt="Logo of SK {{ $barangay->name }}" class="mx-auto mt-4 h-32 w-32 rounded-full border border-line bg-white object-contain p-1">
            <p class="mt-3 text-xs text-slate-muted">{{ $barangay->logo_path ? 'Uploaded logo' : 'National SK logo, shown until you upload this SK’s own' }}</p>
        </section>

        <div class="space-y-5 rounded-[12px] border border-line bg-white p-6">
            <div>
                <label for="logo" class="block text-sm font-medium">SK logo</label>
                <input id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/webp" class="mt-1.5 block w-full rounded-[8px] border border-line bg-white text-sm text-navy/80 file:mr-3 file:border-0 file:border-r file:border-line file:bg-mist file:px-3 file:py-2 file:text-sm file:font-semibold file:text-navy">
                <p class="mt-1.5 text-xs text-slate-muted">A square PNG with a transparent background looks best. Up to 2 MB.</p>
                @error('logo')
                    <p class="mt-1.5 text-sm text-error">{{ $message }}</p>
                @enderror
                @if ($barangay->logo_path)
                    <label class="mt-3 flex items-center gap-2 text-sm">
                        <input type="checkbox" name="remove_logo" value="1" class="accent-error">
                        Remove the uploaded logo
                    </label>
                @endif
            </div>

            <x-form.input name="facebook_url" type="url" label="Facebook page" :value="$barangay->facebook_url" placeholder="https://www.facebook.com/…" hint="Youth see a “Follow on Facebook” button on this SK’s pages." />

            <p class="rounded-[10px] bg-gold/15 p-4 text-sm leading-relaxed">
                Upload the SK’s official logo only with the permission of its officers or the SK Federation. The logo represents the council, and the platform shows it on everything the SK publishes.
            </p>

            <div class="flex items-center gap-3 border-t border-line pt-5">
                <x-form.submit busy="Saving…" :block="false">Save profile</x-form.submit>
                <a href="{{ route('admin.barangays.index') }}" class="rounded-[8px] px-4 py-2.5 text-sm font-medium text-slate-muted hover:text-navy">Cancel</a>
            </div>
        </div>
    </form>
</x-layouts.app>
