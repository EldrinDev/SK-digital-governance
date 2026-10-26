<x-layouts.app title="Backups" heading="Backups" description="Each backup holds the whole database and every uploaded file. One is made automatically every night at 2:00 AM, and the newest 14 are kept.">
    @if ($supported)
        <x-slot:actions>
            <form method="POST" action="{{ route('admin.backups.store') }}">
                @csrf
                <x-form.submit busy="Backing up…" :block="false">Back up now</x-form.submit>
            </form>
        </x-slot:actions>
    @endif

    @error('backup')
        <p role="alert" class="mb-6 rounded-[12px] border border-error/30 bg-error/5 p-4 text-sm">{{ $message }}</p>
    @enderror

    @unless ($supported)
        <p class="mb-6 rounded-[12px] border border-warning/40 bg-warning/10 p-4 text-sm">This server uses a database engine the platform can’t back up by itself. Use the database server’s own backup tools.</p>
    @endunless

    <p class="mb-6 max-w-3xl rounded-[12px] border border-info/30 bg-info/5 p-4 text-sm leading-relaxed">
        Backups stay on this server. Download one now and then and keep it somewhere else, like an external drive, in case the server itself fails.
    </p>

    <ul class="max-w-3xl space-y-3">
        @forelse ($backups as $backup)
            <li class="rounded-[12px] border border-line bg-white p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="font-medium">{{ $backup['created_at']->format('F j, Y · g:i A') }}</p>
                        <p class="text-xs text-slate-muted">{{ $backup['name'] }} · {{ number_format($backup['size'] / 1048576, 1) }} MB</p>
                    </div>
                    <div class="flex items-center gap-4 text-sm font-semibold">
                        <a href="{{ route('admin.backups.show', $backup['name']) }}" class="text-emerald hover:underline">Download</a>
                        <form method="POST" action="{{ route('admin.backups.destroy', $backup['name']) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-slate-muted hover:text-error">Delete</button>
                        </form>
                    </div>
                </div>

                @if ($supported)
                    <details class="mt-3 border-t border-line pt-3">
                        <summary class="cursor-pointer text-sm font-semibold text-[#b91c1c]">Restore from this backup…</summary>
                        <form method="POST" action="{{ route('admin.backups.restore', $backup['name']) }}" class="mt-3 space-y-3">
                            @csrf
                            <p class="text-sm leading-relaxed">
                                Everything changed since this backup was made will be replaced: accounts, plans, PPAs, files, and the audit trail. A backup of the current data is taken first, and everyone is signed out. Passwords and account status (active or deactivated) also go back to how they were, so check recent account changes afterwards.
                            </p>
                            <label for="confirm-{{ $loop->index }}" class="block text-sm font-medium">Type RESTORE to confirm</label>
                            <input id="confirm-{{ $loop->index }}" name="confirmation" autocomplete="off" class="block w-48 rounded-[8px] border border-line px-3 py-2 text-sm outline-none focus:border-error focus:ring-3 focus:ring-error/20">
                            @error('confirmation')
                                <p class="text-sm text-error">{{ $message }}</p>
                            @enderror
                            <button type="submit" class="rounded-[8px] bg-[#b91c1c] px-4 py-2 text-sm font-semibold text-white hover:bg-[#991b1b]">Restore</button>
                        </form>
                    </details>
                @endif
            </li>
        @empty
            <li class="rounded-[12px] border border-dashed border-line bg-white p-8 text-center text-sm text-slate-muted">No backups yet.</li>
        @endforelse
    </ul>
</x-layouts.app>
