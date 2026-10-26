@php($editing = $announcement->exists)
<x-layouts.app :title="$editing ? 'Edit announcement' : 'Post an announcement'" :heading="$editing ? 'Edit announcement' : 'Post an announcement'" :description="$editing ? 'Changes show on the public portal right away.' : 'It appears on the public portal as soon as you post it.'">
    <form method="POST" action="{{ $editing ? route('officer.announcements.update', $announcement) : route('officer.announcements.store') }}" class="max-w-3xl space-y-5 rounded-[12px] border border-line bg-white p-6" novalidate>
        @csrf
        @if ($editing)
            @method('PUT')
        @endif
        <x-form.input name="title" label="Headline" :value="$announcement->title" placeholder="e.g. Registration for the summer league is open" maxlength="150" required autofocus />
        <div>
            <label for="body" class="block text-sm font-medium">Announcement</label>
            <textarea id="body" name="body" rows="8" maxlength="5000" required @class([
                'mt-1.5 block w-full rounded-[8px] border px-3.5 py-2.5 text-sm outline-none focus:ring-3',
                'border-line focus:border-emerald focus:ring-emerald/20' => ! $errors->has('body'),
                'border-error focus:border-error focus:ring-error/20' => $errors->has('body'),
            ])>{{ old('body', $announcement->body) }}</textarea>
            @error('body')
                <p class="mt-1.5 text-sm text-error">{{ $message }}</p>
            @enderror
            <p class="mt-1.5 text-xs text-slate-muted">Don’t include personal details of youth, like phone numbers or home addresses.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3 border-t border-line pt-5">
            <x-form.submit :busy="$editing ? 'Saving…' : 'Posting…'" :block="false">{{ $editing ? 'Save changes' : 'Post announcement' }}</x-form.submit>
            <a href="{{ route('officer.announcements.index') }}" class="rounded-[8px] px-4 py-2.5 text-sm font-medium text-slate-muted hover:text-navy">Cancel</a>
        </div>
    </form>
</x-layouts.app>
