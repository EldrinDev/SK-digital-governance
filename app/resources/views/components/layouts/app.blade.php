@props([
    'title',
    'heading' => null,
    'description' => null,
])
@php
    use App\UserRole;

    $user = auth()->user();

    /**
     * Side menu entries for each role: label, route name, the route pattern
     * that marks it active, and an outline icon path.
     *
     * @var list<array{section?: string, label: string, route: string, active: string, icon: string}> $navigation
     */
    $navigation = match ($user->role) {
        UserRole::Administrator => [
            ['label' => 'Overview', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'icon' => '<path d="M3 12 12 4l9 8"/><path d="M5 10v10h14V10"/>'],
            ['section' => 'People', 'label' => 'Users', 'route' => 'admin.users.index', 'active' => 'admin.users.*', 'icon' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'],
            ['section' => 'People', 'label' => 'Barangays', 'route' => 'admin.barangays.index', 'active' => 'admin.barangays.*', 'icon' => '<path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21Z"/><circle cx="12" cy="9.5" r="2.5"/>'],
            ['section' => 'System', 'label' => 'Audit trail', 'route' => 'admin.audit-logs.index', 'active' => 'admin.audit-logs.*', 'icon' => '<path d="M12 8v4l3 2"/><circle cx="12" cy="12" r="9"/>'],
            ['section' => 'System', 'label' => 'Backups', 'route' => 'admin.backups.index', 'active' => 'admin.backups.*', 'icon' => '<ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v6c0 1.7 3.6 3 8 3s8-1.3 8-3V5"/><path d="M4 11v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6"/>'],
        ],
        UserRole::Officer => [
            ['label' => 'Overview', 'route' => 'officer.dashboard', 'active' => 'officer.dashboard', 'icon' => '<path d="M3 12 12 4l9 8"/><path d="M5 10v10h14V10"/>'],
            ['section' => 'Planning', 'label' => 'Plans', 'route' => 'officer.plans.index', 'active' => 'officer.plans.*', 'icon' => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 7h8M8 11h8M8 15h5"/>'],
            ['section' => 'Planning', 'label' => 'PPAs', 'route' => 'officer.ppas.index', 'active' => 'officer.ppas.*', 'icon' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/><path d="m9 15 2 2 4-4"/>'],
            ['section' => 'Council', 'label' => 'Sessions', 'route' => 'officer.meetings.index', 'active' => 'officer.meetings.*', 'icon' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'],
            ['section' => 'Council', 'label' => 'Resolutions', 'route' => 'officer.resolutions.index', 'active' => 'officer.resolutions.*', 'icon' => '<path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9l-6-6Z"/><path d="M14 3v6h6"/><path d="m9 15 2 2 4-4"/>'],
            ['section' => 'Youth', 'label' => 'Announcements', 'route' => 'officer.announcements.index', 'active' => 'officer.announcements.*', 'icon' => '<path d="m3 11 18-5v12L3 14v-3Z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>'],
            ['section' => 'Youth', 'label' => 'Events', 'route' => 'officer.events.index', 'active' => 'officer.events.*', 'icon' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/><path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01"/>'],
            ['section' => 'Youth', 'label' => 'Feedback', 'route' => 'officer.feedback.index', 'active' => 'officer.feedback.*', 'icon' => '<path d="M21 12a8 8 0 0 1-11.6 7.1L4 20.5l1.4-4.8A8 8 0 1 1 21 12Z"/>'],
            ['section' => 'Youth', 'label' => 'Surveys', 'route' => 'officer.surveys.index', 'active' => 'officer.surveys.*', 'icon' => '<path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/>'],
            ['section' => 'Public', 'label' => 'Disclosure', 'route' => 'officer.disclosure.index', 'active' => 'officer.disclosure.*', 'icon' => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>'],
        ],
        UserRole::Federation => [
            ['label' => 'Overview', 'route' => 'federation.dashboard', 'active' => 'federation.dashboard', 'icon' => '<path d="M3 12 12 4l9 8"/><path d="M5 10v10h14V10"/>'],
            ['label' => 'Reports', 'route' => 'federation.reports.index', 'active' => 'federation.reports.*', 'icon' => '<path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9l-6-6Z"/><path d="M14 3v6h6"/><path d="M8 13h8M8 17h5"/>'],
        ],
        UserRole::Youth => [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => 'dashboard', 'icon' => '<path d="M3 12 12 4l9 8"/><path d="M5 10v10h14V10"/>'],
            ['label' => 'Announcements', 'route' => 'announcements.index', 'active' => 'announcements.*', 'icon' => '<path d="m3 11 18-5v12L3 14v-3Z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>'],
            ['label' => 'Events', 'route' => 'events.index', 'active' => 'events.*', 'icon' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>'],
            ['label' => 'Transparency', 'route' => 'transparency.index', 'active' => 'transparency.*', 'icon' => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>'],
        ],
    };

    $navigation = array_values(array_filter($navigation, fn (array $item): bool => Route::has($item['route'])));
    $navigationSections = collect($navigation)->groupBy(fn (array $item): string => $item['section'] ?? '');
    $initials = collect(explode(' ', $user->name))->filter()->map(fn (string $part): string => mb_substr($part, 0, 1))->take(2)->implode('');
    $roleLine = $user->position?->label() ?? $user->role->label();
    $workspace = $user->barangay?->name ?? match ($user->role) {
        UserRole::Federation => 'SK Federation',
        UserRole::Administrator => 'Administration',
        default => 'Olongapo City',
    };
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title }} · SK Digital Governance</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('images/sk-logo.svg') }}">

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-dvh bg-mist font-sans text-navy antialiased">
        <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[60] focus:rounded-[8px] focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-semibold">Skip to content</a>

        <div class="lg:grid lg:min-h-dvh lg:grid-cols-[15.5rem_minmax(0,1fr)]">
            {{-- Side menu (desktop) --}}
            <aside class="hidden border-r border-line bg-white lg:sticky lg:top-0 lg:flex lg:h-dvh lg:flex-col lg:self-start lg:overflow-y-auto">
                <a href="{{ route($user->role->homeRoute()) }}" class="flex items-center gap-3 px-5 py-5">
                    <img src="{{ asset('images/sk-logo.svg') }}" alt="" width="40" height="40" class="h-10 w-10">
                    <span class="leading-tight">
                        <span class="block font-display text-sm font-semibold">SK Digital Governance</span>
                        <span class="block text-[0.65rem] font-medium tracking-[0.18em] text-slate-muted">OLONGAPO CITY</span>
                    </span>
                </a>

                <nav class="flex-1 space-y-5 px-3 py-2" aria-label="Workspace">
                    @foreach ($navigationSections as $section => $items)
                        <div>
                            @if ($section !== '')
                                <p class="mb-1.5 px-3 text-xs font-semibold text-slate-muted">{{ $section }}</p>
                            @endif
                            <ul class="space-y-0.5">
                                @foreach ($items as $item)
                                    @php($isActive = request()->routeIs($item['active']))
                                    <li>
                                        <a href="{{ route($item['route']) }}" @class([
                                            'relative flex items-center gap-3 rounded-[8px] px-3 py-2 text-sm font-medium transition-colors',
                                            'bg-emerald/10 text-emerald before:absolute before:inset-y-2 before:-left-3 before:w-1 before:rounded-r-full before:bg-emerald' => $isActive,
                                            'text-navy/75 hover:bg-mist hover:text-navy' => ! $isActive,
                                        ]) @if ($isActive) aria-current="page" @endif>
                                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $item['icon'] !!}</svg>
                                            {{ $item['label'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </nav>

                <div class="border-t border-line p-3">
                    <a href="{{ route('account.edit') }}" class="flex items-center gap-3 rounded-[10px] p-2 hover:bg-mist">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-navy text-xs font-semibold text-white" aria-hidden="true">{{ $initials }}</span>
                        <span class="min-w-0 leading-tight">
                            <span class="block truncate text-sm font-semibold">{{ $user->name }}</span>
                            <span class="block truncate text-xs text-slate-muted">{{ $roleLine }}</span>
                        </span>
                    </a>
                    <p class="mt-2 px-2 text-xs text-slate-muted">
                        <a href="{{ route('home') }}" class="hover:text-emerald">Public site</a>
                        <span aria-hidden="true">·</span>
                        <a href="{{ route('privacy') }}" class="hover:text-emerald">Privacy</a>
                    </p>
                </div>
            </aside>

            <div class="flex min-w-0 flex-col">
                <header class="sticky top-0 z-40 border-b border-line bg-white/95 backdrop-blur">
                    <div class="flex items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
                        <div class="flex min-w-0 items-center gap-3">
                            <details class="relative lg:hidden">
                                <summary class="flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-[8px] border border-line [&::-webkit-details-marker]:hidden">
                                    <span class="sr-only">Open menu</span>
                                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                                </summary>
                                <ul class="absolute left-0 mt-2 max-h-[75dvh] w-60 overflow-y-auto rounded-[12px] border border-line bg-white p-2 shadow-lg shadow-navy/10">
                                    @foreach ($navigationSections as $section => $items)
                                        @if ($section !== '')
                                            <li class="px-3 pb-1 pt-2.5 text-xs font-semibold text-slate-muted">{{ $section }}</li>
                                        @endif
                                        @foreach ($items as $item)
                                            <li><a href="{{ route($item['route']) }}" @class(['flex items-center gap-2.5 rounded-[8px] px-3 py-2 text-sm font-medium hover:bg-mist', 'bg-emerald/10 text-emerald' => request()->routeIs($item['active'])])><svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $item['icon'] !!}</svg>{{ $item['label'] }}</a></li>
                                        @endforeach
                                    @endforeach
                                    <li class="mt-1 border-t border-line pt-1"><a href="{{ route('account.edit') }}" class="block rounded-[8px] px-3 py-2 text-sm hover:bg-mist">Account settings</a></li>
                                    <li><a href="{{ route('home') }}" class="block rounded-[8px] px-3 py-2 text-sm text-slate-muted hover:bg-mist">Public site</a></li>
                                </ul>
                            </details>
                            <div class="min-w-0 leading-tight">
                                <p class="truncate font-display text-base font-semibold">{{ $workspace }}</p>
                                <p class="truncate text-xs text-slate-muted">{{ $user->role->label() }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            @if ($user->isOfficer())
                                <form method="GET" action="{{ route('officer.search') }}" role="search" class="hidden md:block">
                                    <label for="workspace-search" class="sr-only">Search your workspace</label>
                                    <input id="workspace-search" name="q" type="search" value="{{ request()->routeIs('officer.search') ? request('q') : '' }}" placeholder="Search records…" class="w-56 rounded-[8px] border border-line bg-mist/60 px-3 py-2 text-sm outline-none focus:border-emerald focus:bg-white focus:ring-3 focus:ring-emerald/20">
                                </form>
                                <a href="{{ route('officer.search') }}" class="flex h-10 w-10 items-center justify-center rounded-[8px] border border-line hover:border-navy md:hidden" aria-label="Search your workspace">
                                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                                </a>
                            @endif
                            @php($unread = $user->unreadNotifications()->count())
                            <a href="{{ route('notifications.index') }}" class="relative flex h-10 w-10 items-center justify-center rounded-[8px] border border-line hover:border-navy" aria-label="Notifications{{ $unread ? ', '.$unread.' unread' : '' }}">
                                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                                @if ($unread)
                                    <span class="absolute -right-1.5 -top-1.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-error px-1 text-[0.65rem] font-semibold text-white" aria-hidden="true">{{ $unread > 9 ? '9+' : $unread }}</span>
                                @endif
                            </a>
                            <details class="relative">
                                <summary class="flex cursor-pointer list-none items-center gap-2 rounded-full border border-line py-1 pl-1 pr-3 hover:border-navy [&::-webkit-details-marker]:hidden">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-navy text-xs font-semibold text-white" aria-hidden="true">{{ $initials }}</span>
                                    <span class="hidden text-sm font-medium sm:inline">{{ str($user->name)->before(' ') }}</span>
                                    <svg viewBox="0 0 24 24" class="h-4 w-4 text-slate-muted" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                                    <span class="sr-only">Account menu</span>
                                </summary>
                                <div class="absolute right-0 mt-2 w-64 rounded-[12px] border border-line bg-white p-2 shadow-lg shadow-navy/10">
                                    <div class="border-b border-line px-3 pb-3 pt-2">
                                        <p class="truncate text-sm font-semibold">{{ $user->name }}</p>
                                        <p class="truncate text-xs text-slate-muted">{{ $user->email }}</p>
                                        <p class="mt-1 text-xs text-slate-muted">{{ $roleLine }}{{ $user->barangay ? ', '.$user->barangay->name : '' }}</p>
                                    </div>
                                    <a href="{{ route('account.edit') }}" class="mt-1 block rounded-[8px] px-3 py-2 text-sm hover:bg-mist">Account settings</a>
                                    <a href="{{ route('notifications.index') }}" class="block rounded-[8px] px-3 py-2 text-sm hover:bg-mist">Notifications</a>
                                    <a href="{{ route('home') }}" class="block rounded-[8px] px-3 py-2 text-sm hover:bg-mist">Public site</a>
                                    <form method="POST" action="{{ route('logout') }}" class="mt-1 border-t border-line pt-1">
                                        @csrf
                                        <button type="submit" class="block w-full rounded-[8px] px-3 py-2 text-left text-sm font-semibold text-[#b91c1c] hover:bg-error/5">Log out</button>
                                    </form>
                                </div>
                            </details>
                        </div>
                    </div>
                </header>

                <main id="main" class="flex-1 px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                    @if (session('status'))
                        <div role="status" class="mb-6 flex items-start gap-3 rounded-[12px] border border-success/30 bg-success/10 p-4 text-sm">
                            <svg viewBox="0 0 24 24" class="mt-0.5 h-5 w-5 shrink-0 text-success" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg>
                            <p>{{ session('status') }}</p>
                        </div>
                    @endif

                    @if ($heading)
                        <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
                            <div>
                                <h1 class="font-display text-2xl font-semibold tracking-tight sm:text-3xl">{{ $heading }}</h1>
                                @if ($description)
                                    <p class="mt-2 max-w-2xl text-slate-muted">{{ $description }}</p>
                                @endif
                            </div>
                            {{ $actions ?? '' }}
                        </div>
                    @endif

                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
