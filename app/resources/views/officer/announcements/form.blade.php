@php
    use App\AnnouncementCategory;
    use App\FacebookPages;
    use App\OfficerPosition;

    $editing = $announcement->exists;
    $barangay = $announcement->barangay;
    $offersFacebookImport = ! $editing && FacebookPages::isConfigured();
    $canImportByLink = $offersFacebookImport && $barangay->hasFacebookPage();
    $isChairperson = auth()->user()->holdsPosition(OfficerPosition::Chairperson);
    $facebookIcon = '<path d="M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.05V9.41c0-3.02 1.79-4.69 4.53-4.69 1.31 0 2.68.24 2.68.24v2.97h-1.51c-1.49 0-1.96.93-1.96 1.89v2.25h3.33l-.53 3.49h-2.8V24C19.61 23.1 24 18.1 24 12.07Z"/>';
    $chosenCategory = old('category', $announcement->category?->value ?? AnnouncementCategory::Announcement->value);
    $swatches = [
        AnnouncementCategory::Announcement->value => 'bg-navy',
        AnnouncementCategory::Reminder->value => 'bg-gold',
        AnnouncementCategory::Event->value => 'bg-emerald',
        AnnouncementCategory::Recognition->value => 'bg-navy-deep',
    ];
    $detailButtons = [
        ['insert' => '🗓️ ', 'label' => 'Date', 'example' => 'November 7, 2026 (Saturday)', 'icon' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>'],
        ['insert' => '🕑 ', 'label' => 'Time', 'example' => '2:00 PM', 'icon' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>'],
        ['insert' => '📍 ', 'label' => 'Place', 'example' => 'Barangay covered court', 'icon' => '<path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21Z"/><circle cx="12" cy="9.5" r="2.5"/>'],
    ];
@endphp
<x-layouts.app :title="$editing ? 'Edit announcement' : 'Post an announcement'" :heading="$editing ? 'Edit announcement' : 'Post an announcement'" :description="$editing ? 'Changes show on the public portal right away.' : 'It appears on the public portal as soon as you post it.'">
    <form method="POST" action="{{ $editing ? route('officer.announcements.update', $announcement) : route('officer.announcements.store') }}" enctype="multipart/form-data" class="grid items-start gap-x-10 gap-y-6 lg:grid-cols-[minmax(0,1fr)_21rem] xl:grid-cols-[minmax(0,1fr)_24rem]" data-announcement-form novalidate>
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <input type="hidden" name="imported_image" value="" data-imported-image>

        <div class="space-y-6">
            @unless ($editing)
                {{-- Already on the SK's Facebook Page: bring it over instead of typing it again. --}}
                <section class="rounded-[12px] border border-[#1877f2]/25 bg-[#1877f2]/[0.04] p-5 sm:p-6" aria-labelledby="import-heading">
                    <div class="flex items-start gap-3">
                        <svg viewBox="0 0 24 24" class="mt-0.5 h-6 w-6 shrink-0 text-[#1877f2]" fill="currentColor" aria-hidden="true">{!! $facebookIcon !!}</svg>
                        <div class="min-w-0 flex-1">
                            <h2 id="import-heading" class="font-display text-base font-semibold">Already posted it on Facebook?</h2>
                            <p class="mt-0.5 text-sm text-slate-muted">
                                {{ $canImportByLink ? 'Paste the post’s link. The caption, photo, kind and original date fill in for you to check.' : 'Copy the post from Facebook and paste it here. The headline, caption, kind and date fill in for you to check.' }}
                            </p>
                        </div>
                    </div>

                    @if ($canImportByLink)
                        <div class="mt-4 flex flex-col gap-2 sm:flex-row" data-facebook-import data-endpoint="{{ route('officer.announcements.facebook-import') }}">
                            <label for="facebook_link" class="sr-only">Link to the Facebook post</label>
                            <input id="facebook_link" type="url" inputmode="url" autocomplete="off" placeholder="https://www.facebook.com/{{ Str::slug($barangay->facebook_page_name, '') }}/posts/…" class="min-w-0 flex-1 rounded-[8px] border border-line bg-white px-3.5 py-2.5 text-sm outline-none focus:border-[#1877f2] focus:ring-3 focus:ring-[#1877f2]/20">
                            <button type="button" class="inline-flex items-center justify-center gap-2 rounded-[8px] bg-[#1877f2] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#1465d0] disabled:cursor-wait disabled:opacity-70" data-import-button>
                                <svg viewBox="0 0 24 24" class="hidden h-4 w-4 animate-spin" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true" data-import-spinner><path d="M21 12a9 9 0 1 1-6.2-8.6" stroke-linecap="round"/></svg>
                                <span data-import-label>Bring it over</span>
                            </button>
                        </div>
                        <p class="mt-2 min-h-5 text-sm" role="status" aria-live="polite" data-import-status></p>
                        <p class="mt-1 text-xs text-slate-muted">
                            Connected to <span class="font-medium text-navy">{{ $barangay->facebook_page_name }}</span>.
                            @if ($isChairperson)
                                <button type="submit" form="facebook-disconnect" class="font-medium text-slate-muted underline underline-offset-2 hover:text-navy">Disconnect</button>
                            @endif
                        </p>
                    @else
                        <div class="mt-4 space-y-3" data-facebook-paste>
                            <div>
                                <label for="facebook_text" class="block text-sm font-medium">The post, copied from Facebook</label>
                                <textarea id="facebook_text" rows="4" class="mt-1.5 block w-full rounded-[8px] border border-line bg-white px-3.5 py-2.5 text-sm leading-relaxed outline-none focus:border-[#1877f2] focus:ring-3 focus:ring-[#1877f2]/20" placeholder="Select the whole post on Facebook, from the Page’s name down to its last line, then copy and paste it here. Likes, comments and “See more” are cleaned out."></textarea>
                            </div>
                            <div>
                                <label for="facebook_post_link" class="block text-sm font-medium">Link to the post <span class="font-normal text-slate-muted">(optional)</span></label>
                                <input id="facebook_post_link" type="url" inputmode="url" autocomplete="off" placeholder="https://www.facebook.com/…" class="mt-1.5 block w-full rounded-[8px] border border-line bg-white px-3.5 py-2.5 text-sm outline-none focus:border-[#1877f2] focus:ring-3 focus:ring-[#1877f2]/20">
                            </div>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                                <button type="button" class="inline-flex items-center gap-2 rounded-[8px] bg-[#1877f2] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#1465d0]" data-paste-fill>Fill in the form</button>
                                <p class="text-sm" role="status" aria-live="polite" data-paste-status></p>
                            </div>
                            <p class="flex items-start gap-2 rounded-[8px] bg-white px-3.5 py-2.5 text-xs leading-relaxed text-slate-muted">
                                <svg viewBox="0 0 24 24" class="mt-0.5 h-4 w-4 shrink-0 text-[#1877f2]" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.1-3.1a2 2 0 0 0-2.8 0L6 21"/></svg>
                                <span>For the photo: right-click it on Facebook and choose <span class="font-medium text-navy">Copy image</span>, then press <kbd class="rounded border border-line bg-mist px-1 font-sans text-[0.7rem] text-navy">Ctrl</kbd> + <kbd class="rounded border border-line bg-mist px-1 font-sans text-[0.7rem] text-navy">V</kbd> anywhere on this page.</span>
                            </p>
                        </div>

                        @if ($offersFacebookImport && $isChairperson)
                            <p class="mt-3 text-xs text-slate-muted">
                                Or <a href="{{ route('officer.facebook.connect') }}" class="font-medium text-[#1877f2] hover:underline">connect SK {{ $barangay->name }}’s Facebook Page</a> to bring posts over from just their link.
                            </p>
                        @endif
                    @endif
                </section>
            @endunless

            <section class="space-y-5 rounded-[12px] border border-line bg-white p-6">
                <fieldset>
                    <legend class="text-sm font-medium">Kind of post</legend>
                    <div class="mt-2 grid gap-2 sm:grid-cols-2">
                        @foreach ($categories as $category)
                            <label class="flex cursor-pointer items-center gap-3 rounded-[10px] border border-line p-3 transition-colors hover:border-navy/30 has-[:checked]:border-emerald has-[:checked]:bg-emerald/5 has-[:focus-visible]:ring-3 has-[:focus-visible]:ring-emerald/20">
                                <input type="radio" name="category" value="{{ $category->value }}" @checked($chosenCategory === $category->value) class="sr-only">
                                <span class="flex h-10 w-10 shrink-0 items-end rounded-[6px] p-1.5 {{ $swatches[$category->value] }}" aria-hidden="true">
                                    <span @class(['block h-1 w-4 rounded-full', 'bg-navy' => $category === AnnouncementCategory::Reminder, 'bg-gold' => $category !== AnnouncementCategory::Reminder])></span>
                                </span>
                                <span class="min-w-0 flex-1 leading-tight">
                                    <span class="block text-sm font-semibold">{{ $category->label() }}</span>
                                    <span class="text-xs text-slate-muted">{{ $category->hint() }}</span>
                                </span>
                                <svg viewBox="0 0 24 24" class="hidden h-5 w-5 shrink-0 text-emerald [label:has(:checked)_&]:block" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                            </label>
                        @endforeach
                    </div>
                    @error('category')
                        <p class="mt-1.5 text-sm text-error">{{ $message }}</p>
                    @enderror
                </fieldset>

                <div>
                    <x-form.input name="title" label="Headline" :value="$announcement->title" placeholder="e.g. PAALALA: Katipunan ng Kabataan Assembly ngayong Sabado" maxlength="150" required />
                    <p class="mt-1 text-right text-xs tabular-nums text-slate-muted" data-count-for="title" aria-hidden="true"></p>
                </div>

                <div>
                    <div class="flex flex-wrap items-end justify-between gap-2">
                        <label for="body" class="block text-sm font-medium">Caption</label>
                        <div class="flex flex-wrap gap-1.5" role="group" aria-label="Add a detail line">
                            @foreach ($detailButtons as $button)
                                <button type="button" data-insert-line="{{ $button['insert'] }}" data-example="{{ $button['example'] }}" class="inline-flex items-center gap-1.5 rounded-[6px] border border-line bg-white px-2.5 py-1 text-xs font-medium text-navy/80 hover:border-emerald hover:text-emerald">
                                    <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $button['icon'] !!}</svg>
                                    {{ $button['label'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <textarea id="body" name="body" rows="10" maxlength="5000" required aria-describedby="body-hint" placeholder="Write it the way you would on your SK page. Hashtags like #LinggoNgKabataan are highlighted." @class([
                        'mt-1.5 block w-full rounded-[8px] border px-3.5 py-2.5 text-sm leading-relaxed outline-none focus:ring-3',
                        'border-line focus:border-emerald focus:ring-emerald/20' => ! $errors->has('body'),
                        'border-error focus:border-error focus:ring-error/20' => $errors->has('body'),
                    ])>{{ old('body', $announcement->body) }}</textarea>
                    @error('body')
                        <p class="mt-1.5 text-sm text-error">{{ $message }}</p>
                    @enderror
                    <div class="mt-1.5 flex items-start justify-between gap-4 text-xs text-slate-muted">
                        <p id="body-hint" class="leading-relaxed">Date, time and place lines go on the poster and into the youth’s calendar. Don’t include personal details of youth, like phone numbers, home addresses or photos of minors without consent.</p>
                        <p class="shrink-0 tabular-nums" data-count-for="body" aria-hidden="true"></p>
                    </div>
                </div>
            </section>

            <section class="rounded-[12px] border border-line bg-white" aria-labelledby="options-heading">
                <h2 id="options-heading" class="px-6 pt-5 text-sm font-semibold">Options</h2>
                <label class="flex cursor-pointer items-start gap-3 px-6 py-4">
                    <input type="checkbox" name="pinned" value="1" @checked(old('pinned', $announcement->isPinned())) class="mt-0.5 h-4 w-4 accent-emerald" data-preview-pin-toggle>
                    <span class="leading-tight">
                        <span class="block text-sm font-medium">Pin to the top</span>
                        <span class="text-xs text-slate-muted">Keeps it first on the portal and on youth dashboards until you unpin it.</span>
                    </span>
                </label>
                <details class="group border-t border-line px-6 py-4" @if (old('source_url', $announcement->source_url) || $errors->hasAny(['source_url', 'originally_posted_at'])) open @endif>
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 text-sm font-medium [&::-webkit-details-marker]:hidden">
                        <span class="flex items-center gap-2.5">
                            <svg viewBox="0 0 24 24" class="h-4 w-4 text-[#1877f2]" fill="currentColor" aria-hidden="true">{!! $facebookIcon !!}</svg>
                            Link to the Facebook post
                        </span>
                        <svg viewBox="0 0 24 24" class="h-4 w-4 text-slate-muted transition-transform group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                    </summary>
                    <p class="mt-2 text-xs leading-relaxed text-slate-muted">Paste the link to your SK’s Facebook post and when it went up. The post keeps its original date and links back to Facebook. Posts older than three days don’t notify youth again.</p>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <x-form.input name="source_url" type="url" label="Facebook post link" :value="$announcement->source_url" placeholder="https://www.facebook.com/…" />
                        <x-form.input name="originally_posted_at" type="datetime-local" label="Posted on Facebook" :value="$announcement->originally_posted_at?->format('Y-m-d\TH:i')" />
                    </div>
                </details>
            </section>
        </div>

        {{-- The post as youth will see it, kept up to date while the form is filled in. --}}
        <aside class="space-y-4 lg:sticky lg:top-6 lg:col-start-2 lg:row-span-2 lg:row-start-1" aria-labelledby="preview-heading">
            <div class="flex items-baseline justify-between gap-3">
                <h2 id="preview-heading" class="text-sm font-semibold">Preview</h2>
                <p class="text-xs text-slate-muted">How youth will see it</p>
            </div>

            <article class="overflow-hidden rounded-[12px] border border-line bg-white shadow-lg shadow-navy/5">
                <header class="flex items-center gap-3 px-4 py-3">
                    <img src="{{ $announcement->barangay->logoUrl() }}" alt="" class="h-9 w-9 rounded-full border border-line bg-white object-contain p-0.5">
                    <div class="min-w-0 flex-1 leading-tight">
                        <p class="truncate text-sm font-semibold">SK {{ $announcement->barangay->name }}</p>
                        <p class="text-xs text-slate-muted">{{ $editing ? $announcement->postedAt()->diffForHumans() : 'Just now' }}</p>
                    </div>
                    <span class="inline-flex items-center gap-1 rounded-full bg-gold/20 px-2 py-0.5 text-xs font-semibold text-navy" data-preview-pin @unless (old('pinned', $announcement->isPinned())) hidden @endunless>
                        <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 17v5"/><path d="M9 3h6l-1 7 4 3v2H6v-2l4-3-1-7Z"/></svg>
                        Pinned
                    </span>
                </header>

                <div class="relative aspect-[4/3] bg-mist">
                    @foreach ($categories as $category)
                        <div class="absolute inset-0" data-poster="{{ $category->value }}" @if ($chosenCategory !== $category->value || $announcement->image_path) hidden @endif>
                            <x-announcement.cover :announcement="(clone $announcement)->forceFill(['category' => $category, 'image_path' => null])" live />
                        </div>
                    @endforeach
                    <img src="{{ $announcement->image_path ? route('announcements.image', $announcement) : '' }}" alt="" class="absolute inset-0 h-full w-full object-cover" data-preview-image data-saved-image="{{ $announcement->image_path ? route('announcements.image', $announcement) : '' }}" @unless ($announcement->image_path) hidden @endunless>
                </div>

                <div class="px-4 pb-4 pt-3.5">
                    <h3 class="font-display text-base font-semibold leading-snug" data-preview-title data-placeholder="Your headline appears here">{{ old('title', $announcement->title) ?: 'Your headline appears here' }}</h3>
                    <dl class="mt-2.5 grid gap-1.5 rounded-[10px] bg-mist p-3 text-sm" data-preview-details hidden></dl>
                    <p class="mt-1.5 whitespace-pre-line text-sm leading-relaxed text-navy/75" data-preview-caption data-placeholder="Your caption appears here."></p>
                </div>
            </article>

            <section class="rounded-[12px] border border-line bg-white p-4" aria-labelledby="photo-heading">
                <h2 id="photo-heading" class="text-sm font-semibold">Photo or pubmat <span class="font-normal text-slate-muted">(optional)</span></h2>
                <label for="image" class="mt-3 flex cursor-pointer items-center gap-3 rounded-[10px] border-2 border-dashed border-line p-4 transition-colors hover:border-emerald/60 hover:bg-emerald/5 has-[:focus-visible]:border-emerald data-dragging:border-emerald data-dragging:bg-emerald/5" data-dropzone>
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-mist text-emerald">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.1-3.1a2 2 0 0 0-2.8 0L6 21"/></svg>
                    </span>
                    <span class="min-w-0 flex-1 leading-tight">
                        <span class="block truncate text-sm font-medium" data-file-name data-empty="{{ $announcement->image_path ? 'Replace the image' : 'Drop an image or choose one' }}">{{ $announcement->image_path ? 'Replace the image' : 'Drop an image or choose one' }}</span>
                        <span class="text-xs text-slate-muted">Or paste one with Ctrl + V. JPG, PNG or WebP, up to 5 MB. Without one, the post gets the poster above.</span>
                    </span>
                    <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only">
                </label>
                <button type="button" class="mt-2 text-xs font-semibold text-slate-muted hover:text-navy" data-clear-image hidden>Use the poster instead</button>
                @error('image')
                    <p class="mt-1.5 text-sm text-error">{{ $message }}</p>
                @enderror
                @if ($announcement->image_path)
                    <label class="mt-3 flex items-center gap-2 text-sm">
                        <input type="checkbox" name="remove_image" value="1" class="accent-error" data-remove-image>
                        Remove the image and use the poster
                    </label>
                @endif
                <div class="mt-3">
                    <x-form.input name="image_credit" label="Photo credit (optional)" :value="$announcement->image_credit" placeholder="e.g. SK {{ $announcement->barangay->name }}" maxlength="150" />
                </div>
            </section>
        </aside>

        {{-- After the photo on phones, under the form on wide screens. --}}
        <div class="flex flex-wrap items-center gap-3 lg:col-start-1">
            <x-form.submit :busy="$editing ? 'Saving…' : 'Posting…'" :block="false">{{ $editing ? 'Save changes' : 'Post announcement' }}</x-form.submit>
            <a href="{{ route('officer.announcements.index') }}" class="rounded-[8px] px-4 py-2.5 text-sm font-medium text-slate-muted hover:text-navy">Cancel</a>
        </div>
    </form>

    @if ($canImportByLink && $isChairperson)
        <form id="facebook-disconnect" method="POST" action="{{ route('officer.facebook.destroy') }}" hidden>
            @csrf
            @method('DELETE')
        </form>
    @endif
</x-layouts.app>
