<x-layouts.app :title="$isYouth ? 'My profile' : 'Account settings'">
    {{-- Who they are, at a glance --}}
    <section class="overflow-hidden rounded-[16px] border border-line bg-white">
        <div class="h-20 bg-navy sm:h-24" aria-hidden="true">
            <div class="h-full w-full bg-[radial-gradient(circle_at_85%_30%,rgba(22,132,91,0.55),transparent_55%)]"></div>
        </div>
        <div class="flex flex-col gap-5 px-5 pb-5 sm:flex-row sm:items-end sm:justify-between sm:px-7">
            <div class="flex items-end gap-4">
                <span class="-mt-10 flex h-20 w-20 shrink-0 items-center justify-center rounded-full border-4 border-white bg-emerald font-display text-2xl font-semibold text-white sm:h-24 sm:w-24 sm:text-3xl" aria-hidden="true">{{ $user->initials() }}</span>
                <div class="min-w-0 pb-1">
                    <h1 class="truncate font-display text-2xl font-semibold tracking-tight sm:text-3xl">{{ $user->name }}</h1>
                    <p class="flex flex-wrap items-center gap-x-2 text-sm text-slate-muted">
                        <span>{{ $user->position?->label() ?? $user->role->label() }}</span>
                        @if ($user->barangay)
                            <span aria-hidden="true">·</span>
                            <span>Barangay {{ $user->barangay->name }}</span>
                        @endif
                        <span aria-hidden="true">·</span>
                        <span>Member since {{ $user->created_at->format('F Y') }}</span>
                    </p>
                </div>
            </div>
            @if ($isYouth)
                <a href="{{ route('account.id') }}" class="inline-flex items-center justify-center gap-2 rounded-[10px] bg-emerald px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-dark">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h3v3h-3zM20 14v.01M14 20h.01M17 20h4v-3"/></svg>
                    Show my digital ID
                </a>
            @endif
        </div>

        @if ($stats !== [])
            <dl class="grid grid-cols-2 border-t border-line sm:grid-cols-4">
                @foreach ($stats as $label => $value)
                    <div @class(['px-5 py-4 sm:px-7', 'border-l border-line' => ! $loop->first && $loop->index !== 2, 'sm:border-l' => $loop->index === 2, 'border-t border-line sm:border-t-0' => $loop->index >= 2])>
                        <dt class="text-xs text-slate-muted">{{ $label }}</dt>
                        <dd class="mt-0.5 font-display text-2xl font-semibold tabular-nums">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        @endif
    </section>

    <div @class(['mt-6 grid items-start gap-6', 'lg:grid-cols-[minmax(0,1fr)_24rem]' => $isYouth, 'max-w-3xl' => ! $isYouth])>
        <div class="min-w-0 space-y-6">
            {{-- Personal details --}}
            <section class="rounded-[16px] border border-line bg-white" aria-labelledby="details-heading">
                <div class="border-b border-line px-5 py-4 sm:px-6">
                    <h2 id="details-heading" class="font-display text-lg font-semibold">Personal details</h2>
                    <p class="text-sm text-slate-muted">This is how your SK sees you on registrations and messages.</p>
                </div>
                <form method="POST" action="{{ route('account.update') }}" class="space-y-4 p-5 sm:p-6">
                    @csrf
                    @method('PATCH')
                    <x-form.input name="name" label="Full name" :value="$user->name" autocomplete="name" required />
                    @if ($user->role->isStaff())
                        <x-form.input name="email" type="email" label="Email" :value="$user->email" hint="Staff emails are managed by an administrator." readonly class="bg-mist text-slate-muted" />
                    @else
                        <x-form.input name="email" type="email" label="Email" :value="$user->email" autocomplete="email" required />
                        <x-form.input name="email_password" type="password" label="Current password" hint="Needed only when you change your email." autocomplete="current-password" />
                    @endif

                    <div class="grid gap-3 rounded-[12px] bg-mist p-4 text-sm sm:grid-cols-2">
                        <div>
                            <p class="text-xs text-slate-muted">Barangay</p>
                            <p class="font-medium">{{ $user->barangay?->name ?? 'City-wide' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-muted">{{ $user->position ? 'Position' : 'Account type' }}</p>
                            <p class="font-medium">{{ $user->position?->label() ?? $user->role->label() }}</p>
                        </div>
                        <p class="text-xs text-slate-muted sm:col-span-2">
                            @if ($isYouth)
                                Moved to another barangay? Ask your new SK; only an administrator can change it.
                            @else
                                Only an administrator can change your role, barangay or position.
                            @endif
                        </p>
                    </div>

                    <x-form.submit busy="Saving…" :block="false">Save details</x-form.submit>
                </form>
            </section>

            {{-- Password --}}
            <section class="rounded-[16px] border border-line bg-white" aria-labelledby="password-heading">
                <div class="border-b border-line px-5 py-4 sm:px-6">
                    <h2 id="password-heading" class="font-display text-lg font-semibold">Password</h2>
                    <p class="text-sm text-slate-muted">You stay signed in here. Every other device is signed out.</p>
                </div>
                <form method="POST" action="{{ route('account.password.update') }}" class="grid gap-4 p-5 sm:grid-cols-2 sm:p-6">
                    @csrf
                    @method('PUT')
                    <div class="sm:col-span-2">
                        <x-form.input name="current_password" type="password" label="Current password" autocomplete="current-password" required />
                    </div>
                    <x-form.input name="password" type="password" label="New password" hint="At least 8 characters." autocomplete="new-password" required />
                    <x-form.input name="password_confirmation" type="password" label="Type it again" autocomplete="new-password" required />
                    <div class="sm:col-span-2">
                        <x-form.submit busy="Changing…" tone="navy" :block="false">Change password</x-form.submit>
                    </div>
                </form>
            </section>

            @if ($isYouth)
                {{-- Privacy and data --}}
                <section class="rounded-[16px] border border-line bg-white" aria-labelledby="privacy-heading">
                    <div class="border-b border-line px-5 py-4 sm:px-6">
                        <h2 id="privacy-heading" class="font-display text-lg font-semibold">Privacy and your data</h2>
                        <p class="text-sm text-slate-muted">Your rights under the Data Privacy Act of 2012.</p>
                    </div>
                    <div class="divide-y divide-line">
                        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 sm:px-6">
                            <div class="min-w-0">
                                <p class="font-medium">Privacy notice</p>
                                <p class="text-sm text-slate-muted">You accepted it on {{ $user->privacy_consented_at?->format('F j, Y') ?? 'sign-up' }}.</p>
                            </div>
                            <a href="{{ route('privacy') }}" class="text-sm font-semibold text-emerald hover:underline">Read it again</a>
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 sm:px-6">
                            <div class="min-w-0">
                                <p class="font-medium">Download my data</p>
                                <p class="text-sm text-slate-muted">Your account, registrations, attendance, feedback and survey answers.</p>
                            </div>
                            <a href="{{ route('account.data') }}" class="rounded-[8px] border border-line px-4 py-2 text-sm font-semibold hover:border-navy">Download</a>
                        </div>
                        <details class="group px-5 py-4 sm:px-6" @if ($errors->has('confirm_password')) open @endif>
                            <summary class="flex cursor-pointer list-none flex-wrap items-center justify-between gap-3 [&::-webkit-details-marker]:hidden">
                                <span class="min-w-0">
                                    <span class="block font-medium text-[#b91c1c]">Close my account</span>
                                    <span class="block text-sm text-slate-muted">Your name and email are erased. Attendance counts stay, without your name.</span>
                                </span>
                                <span class="rounded-[8px] border border-error/40 px-4 py-2 text-sm font-semibold text-[#b91c1c] group-open:hidden">Close account…</span>
                            </summary>
                            <form method="POST" action="{{ route('account.destroy') }}" class="mt-4 max-w-sm space-y-4 rounded-[12px] bg-error/5 p-4">
                                @csrf
                                @method('DELETE')
                                <p class="text-sm font-medium">This can’t be undone.</p>
                                <x-form.input name="confirm_password" type="password" label="Enter your password to confirm" autocomplete="current-password" required />
                                <button type="submit" class="rounded-[8px] bg-[#b91c1c] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#991b1b]">Close my account</button>
                            </form>
                        </details>
                    </div>
                </section>
            @endif
        </div>

        @if ($isYouth)
            {{-- Their digital ID, always within reach --}}
            <aside class="space-y-3 lg:sticky lg:top-24" aria-labelledby="id-heading">
                <div class="flex items-center justify-between gap-3">
                    <h2 id="id-heading" class="font-display text-lg font-semibold">My digital ID</h2>
                    <a href="{{ route('account.id') }}" class="text-sm font-semibold text-emerald hover:underline">Full screen</a>
                </div>
                <a href="{{ route('account.id') }}" class="block transition-transform hover:-translate-y-0.5" aria-label="Open my digital ID full screen">
                    <x-youth.id-card :user="$user" :code="$code" />
                </a>
                <p class="text-xs leading-relaxed text-slate-muted">Show it at SK events so an officer can check you in by scanning the QR.</p>
            </aside>
        @endif
    </div>
</x-layouts.app>
