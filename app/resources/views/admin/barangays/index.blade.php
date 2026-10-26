<x-layouts.app title="Barangays" heading="Barangays" description="Only participating barangays appear on the register and SK access pages. Turn on the pilot barangays and turn off the rest.">
    <div class="overflow-x-auto rounded-[12px] border border-line bg-white">
        <table class="w-full min-w-[44rem] text-left text-sm">
            <thead class="border-b border-line bg-mist/60 text-xs font-semibold text-slate-muted">
                <tr>
                    <th scope="col" class="px-5 py-3">Barangay</th>
                    <th scope="col" class="px-5 py-3 text-right">SK officers</th>
                    <th scope="col" class="px-5 py-3 text-right">Youth</th>
                    <th scope="col" class="px-5 py-3">Participation</th>
                    <th scope="col" class="px-5 py-3"><span class="sr-only">Profile</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @foreach ($barangays as $barangay)
                    <tr>
                        <th scope="row" class="px-5 py-3 font-medium">
                            <span class="flex items-center gap-3">
                                <img src="{{ $barangay->logoUrl() }}" alt="" class="h-8 w-8 rounded-full border border-line bg-white object-contain p-0.5">
                                {{ $barangay->name }}
                                @if ($barangay->facebook_url)
                                    <span class="rounded-full bg-info/10 px-2 py-0.5 text-xs font-semibold text-info">Facebook</span>
                                @endif
                            </span>
                        </th>
                        <td class="px-5 py-3 text-right tabular-nums">{{ $barangay->officers_count }}</td>
                        <td class="px-5 py-3 text-right tabular-nums">{{ $barangay->youth_count }}</td>
                        <td class="px-5 py-3">
                            <form method="POST" action="{{ route('admin.barangays.update', $barangay) }}" class="flex items-center gap-3">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="is_participating" value="{{ $barangay->is_participating ? 0 : 1 }}">
                                @if ($barangay->is_participating)
                                    <x-ui.badge tone="success">Participating</x-ui.badge>
                                    <button type="submit" class="text-xs font-semibold text-slate-muted hover:text-error">Turn off<span class="sr-only"> {{ $barangay->name }}</span></button>
                                @else
                                    <x-ui.badge>Not participating</x-ui.badge>
                                    <button type="submit" class="text-xs font-semibold text-emerald hover:underline">Turn on<span class="sr-only"> {{ $barangay->name }}</span></button>
                                @endif
                            </form>
                        </td>
                        <td class="px-5 py-3 text-right"><a href="{{ route('admin.barangays.edit', $barangay) }}" class="text-xs font-semibold text-emerald hover:underline">Logo and Facebook<span class="sr-only"> of {{ $barangay->name }}</span></a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <p class="mt-3 text-xs text-slate-muted">Turning a barangay off doesn’t delete anything. Its existing accounts keep their data but can’t sign in through that workspace until it’s turned back on.</p>
</x-layouts.app>
