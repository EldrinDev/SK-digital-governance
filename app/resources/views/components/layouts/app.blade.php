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
     * @var list<array{label: string, route: string, active: string, icon: string}> $navigation
     */
    $navigation = match ($user->role) {
        UserRole::Administrator => [
            ['label' => 'Overview', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'icon' => '<path d="M3 12 12 4l9 8"/><path d="M5 10v10h14V10"/>'],
            ['label' => 'Users', 'route' => 'admin.users.index', 'active' => 'admin.users.*', 'icon' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'],
            ['label' => 'Barangays', 'route' => 'admin.barangays.index', 'active' => 'admin.barangays.*', 'icon' => '<path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21Z"/><circle cx="12" cy="9.5" r="2.5"/>'],
            ['label' => 'Audit trail', 'route' => 'admin.audit-logs.index', 'active' => 'admin.audit-logs.*', 'icon' => '<path d="M12 8v4l3 2"/><circle cx="12" cy="12" r="9"/>'],
        ],
        UserRole::Officer => [
            ['label' => 'Overview', 'route' => 'officer.dashboard', 'active' => 'officer.dashboard', 'icon' => '<path d="M3 12 12 4l9 8"/><path d="M5 10v10h14V10"/>'],
            ['label' => 'Plans', 'route' => 'officer.plans.index', 'active' => 'officer.plans.*', 'icon' => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 7h8M8 11h8M8 15h5"/>'],
            ['label' => 'PPAs', 'route' => 'officer.ppas.index', 'active' => 'officer.ppas.*', 'icon' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/><path d="m9 15 2 2 4-4"/>'],
            ['label' => 'Meetings', 'route' => 'officer.meetings.index', 'active' => 'officer.meetings.*', 'icon' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'],
            ['label' => 'Resolutions', 'route' => 'officer.resolutions.index', 'active' => 'officer.resolutions.*', 'icon' => '<path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9l-6-6Z"/><path d="M14 3v6h6"/><path d="m9 15 2 2 4-4"/>'],
            ['label' => 'Events', 'route' => 'officer.events.index', 'active' => 'officer.events.*', 'icon' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/><path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01"/>'],
            ['label' => 'Feedback', 'route' => 'officer.feedback.index', 'active' => 'officer.feedback.*', 'icon' => '<path d="M21 12a8 8 0 0 1-11.6 7.1L4 20.5l1.4-4.8A8 8 0 1 1 21 12Z"/>'],
            ['label' => 'Disclosure', 'route' => 'officer.disclosure.index', 'active' => 'officer.disclosure.*', 'icon' => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>'],
        ],
        UserRole::Federation => [
            ['label' => 'Overview', 'route' => 'federation.dashboard', 'active' => 'federation.dashboard', 'icon' => '<path d="M3 12 12 4l9 8"/><path d="M5 10v10h14V10"/>'],
            ['label' => 'Reports', 'route' => 'federation.reports.index', 'active' => 'federation.reports.*', 'icon' => '<path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9l-6-6Z"/><path d="M14 3v6h6"/><path d="M8 13h8M8 17h5"/>'],
        ],
        UserRole::Youth => [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => 'dashboard', 'icon' => '<path d="M3 12 12 4l9 8"/><path d="M5 10v10h14V10"/>'],
            ['label' => 'Events', 'route' => 'events.index', 'active' => 'events.*', 'icon' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>'],
            ['label' => 'Transparency', 'route' => 'transparency.index', 'active' => 'transparency.*', 'icon' => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>'],
        ],
    };

    $navigation = array_values(array_filter($navigation, fn (array $item): bool => Route::has($item['route'])));
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

                <nav class="flex-1 px-3 py-2" aria-label="Workspace">
                    <ul class="space-y-1">
                        @foreach ($navigation as $item)
                            @php($isActive = request()->routeIs($item['active']))
                            <li>
                                <a href="{{ route($item['route']) }}" @class([
                                    'flex items-center gap-3 rounded-[8px] px-3 py-2.5 text-sm font-medium transition-colors',
                                    'bg-emerald/10 text-emerald' => $isActive,
                                    'text-navy/75 hover:bg-mist hover:text-navy' => ! $isActive,
                                ]) @if ($isActive) aria-current="page" @endif>
                                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $item['icon'] !!}</svg>
                                    {{ $item['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>

                <div class="border-t border-line px-5 py-4 text-xs text-slate-muted">
                    <a href="{{ route('home') }}" class="hover:text-emerald">Public site</a>
                    ·
                    <a href="{{ route('privacy') }}" class="hover:text-emerald">Privacy</a>
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
                                <ul class="absolute left-0 mt-2 w-56 rounded-[12px] border border-line bg-white p-2 shadow-lg shadow-navy/10">
                                    @foreach ($navigation as $item)
                                        <li><a href="{{ route($item['route']) }}" @class(['block rounded-[8px] px-3 py-2 text-sm font-medium hover:bg-mist', 'text-emerald' => request()->routeIs($item['active'])])>{{ $item['label'] }}</a></li>
                                    @endforeach
                                    <li class="mt-1 border-t border-line pt-1"><a href="{{ route('home') }}" class="block rounded-[8px] px-3 py-2 text-sm text-slate-muted hover:bg-mist">Public site</a></li>
                                </ul>
                            </details>
                            <div class="min-w-0 leading-tight">
                                <p class="truncate font-display text-base font-semibold">{{ $workspace }}</p>
                                <p class="truncate text-xs text-slate-muted">{{ $user->role->label() }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            @php($unread = $user->unreadNotifications()->count())
                            <a href="{{ route('notifications.index') }}" class="relative flex h-10 w-10 items-center justify-center rounded-[8px] border border-line hover:border-navy" aria-label="Notifications{{ $unread ? ', '.$unread.' unread' : '' }}">
                                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                                @if ($unread)
                                    <span class="absolute -right-1.5 -top-1.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-error px-1 text-[0.65rem] font-semibold text-white" aria-hidden="true">{{ $unread > 9 ? '9+' : $unread }}</span>
                                @endif
                            </a>
                            <p class="hidden text-sm text-navy/80 sm:block">{{ $user->name }}</p>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="rounded-[8px] border border-line px-3.5 py-2 text-sm font-semibold text-navy transition-colors hover:border-navy">Log out</button>
                            </form>
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
