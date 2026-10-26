@php($editing = $announcement->exists)
<x-layouts.app :title="$editing ? 'Edit announcement' : 'Post an announcement'" :heading="$editing ? 'Edit announcement' : 'Post an announcement'" :description="$editing ? 'Changes show on the public portal right away.' : 'It appears on the public portal as soon as you post it.'">
    <form method="POST" action="{{ $editing ? route('officer.announcements.update', $announcement) : route('officer.announcements.store') }}" enctype="multipart/form-data" class="grid max-w-5xl gap-6 lg:grid-cols-[minmax(0,1fr)_18rem]" novalidate>
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <div class="space-y-5 rounded-[12px] border border-line bg-white p-6">
            <fieldset>
                <legend class="text-sm font-medium">Kind of post</legend>
                <div class="mt-2 grid gap-2 sm:grid-cols-2">
                    @foreach ($categories as $category)
                        <label class="flex cursor-pointer items-start gap-3 rounded-[8px] border border-line p-3 has-[:checked]:border-emerald has-[:checked]:bg-emerald/5">
                            <input type="radio" name="category" value="{{ $category->value }}" @checked(old('category', $announcement->category?->value) === $category->value) class="mt-0.5 accent-emerald">
                            <span class="leading-tight">
                                <span class="block text-sm font-semibold">{{ $category->label() }}</span>
                                <span class="text-xs text-slate-muted">{{ $category->hint() }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('category')
                    <p class="mt-1.5 text-sm text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <x-form.input name="title" label="Headline" :value="$announcement->title" placeholder="e.g. PAALALA: Katipunan ng Kabataan Assembly ngayong Sabado" maxlength="150" required />

            <div>
                <label for="body" class="block text-sm font-medium">Caption</label>
                <textarea id="body" name="body" rows="9" maxlength="5000" required placeholder="Write it the way you would on your SK page. Hashtags like #LinggoNgKabataan are highlighted." @class([
                    'mt-1.5 block w-full rounded-[8px] border px-3.5 py-2.5 text-sm leading-relaxed outline-none focus:ring-3',
                    'border-line focus:border-emerald focus:ring-emerald/20' => ! $errors->has('body'),
                    'border-error focus:border-error focus:ring-error/20' => $errors->has('body'),
                ])>{{ old('body', $announcement->body) }}</textarea>
                @error('body')
                    <p class="mt-1.5 text-sm text-error">{{ $message }}</p>
                @enderror
                <p class="mt-1.5 text-xs text-slate-muted">Don’t include personal details of youth, like phone numbers, home addresses or photos of minors without consent.</p>
            </div>

            <div class="flex flex-wrap items-center gap-3 border-t border-line pt-5">
                <x-form.submit :busy="$editing ? 'Saving…' : 'Posting…'" :block="false">{{ $editing ? 'Save changes' : 'Post announcement' }}</x-form.submit>
                <a href="{{ route('officer.announcements.index') }}" class="rounded-[8px] px-4 py-2.5 text-sm font-medium text-slate-muted hover:text-navy">Cancel</a>
            </div>
        </div>

        <div class="space-y-5">
            <section class="rounded-[12px] border border-line bg-white p-5">
                <h2 class="text-sm font-semibold">Photo or pubmat</h2>
                <div class="mt-3 aspect-[4/3] overflow-hidden rounded-[8px] bg-mist">
                    <x-announcement.cover :announcement="$announcement->exists ? $announcement : $announcement->fill(['title' => old('title') ?: 'Your headline appears here'])" />
                </div>
                <p class="mt-2 text-xs text-slate-muted">{{ $announcement->image_path ? 'Your uploaded image.' : 'Without an image, the post gets a poster in your SK’s colours, like this one.' }}</p>
                <label for="image" class="sr-only">Upload an image</label>
                <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" class="mt-3 block w-full rounded-[8px] border border-line bg-white text-sm text-navy/80 file:mr-3 file:border-0 file:border-r file:border-line file:bg-mist file:px-3 file:py-2 file:text-sm file:font-semibold file:text-navy">
                <p class="mt-1.5 text-xs text-slate-muted">JPG, PNG or WebP, up to 5 MB.</p>
                @error('image')
                    <p class="mt-1.5 text-sm text-error">{{ $message }}</p>
                @enderror
                @if ($announcement->image_path)
                    <label class="mt-3 flex items-center gap-2 text-sm">
                        <input type="checkbox" name="remove_image" value="1" class="accent-error">
                        Remove the image
                    </label>
                @endif
            </section>

            <section class="rounded-[12px] border border-line bg-white p-5">
                <label class="flex items-start gap-3">
                    <input type="checkbox" name="pinned" value="1" @checked(old('pinned', $announcement->isPinned())) class="mt-0.5 accent-emerald">
                    <span class="leading-tight">
                        <span class="block text-sm font-semibold">Pin to the top</span>
                        <span class="text-xs text-slate-muted">Keeps it first on the portal and on youth dashboards until you unpin it.</span>
                    </span>
                </label>
            </section>
        </div>
    </form>
</x-layouts.app>
