@props([
    'title',
    'description' => null,
])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title }} · SK Digital Governance</title>
        @if ($description)
            <meta name="description" content="{{ $description }}">
        @endif
        <link rel="icon" type="image/svg+xml" href="{{ asset('images/sk-logo.svg') }}">

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="flex min-h-dvh flex-col bg-mist font-sans text-navy antialiased">
        <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[60] focus:rounded-[8px] focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-semibold">Skip to content</a>

        <header class="border-b border-line bg-white">
            <nav class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3.5 sm:px-6 lg:px-8" aria-label="Main">
                <a href="{{ route('home') }}" class="flex items-center gap-3 rounded-[8px]">
                    <img src="{{ asset('images/sk-logo.svg') }}" alt="" width="44" height="44" class="h-9 w-9 sm:h-11 sm:w-11">
                    <span class="leading-tight">
                        <span class="block font-display text-sm font-semibold sm:text-[1.05rem]">SK Digital Governance</span>
                        <span class="block text-[0.7rem] font-medium tracking-[0.18em] text-slate-muted">OLONGAPO CITY</span>
                    </span>
                </a>
                <div class="flex items-center gap-5 text-sm font-medium">
                    <a href="{{ route('events.index') }}" @class(['hidden sm:inline', 'text-emerald' => request()->routeIs('events.*'), 'text-navy/80 hover:text-emerald' => ! request()->routeIs('events.*')])>Events</a>
                    <a href="{{ route('transparency.index') }}" @class(['hidden sm:inline', 'text-emerald' => request()->routeIs('transparency.*'), 'text-navy/80 hover:text-emerald' => ! request()->routeIs('transparency.*')])>Transparency</a>
                    @auth
                        <a href="{{ route(auth()->user()->role->homeRoute()) }}" class="rounded-full bg-navy px-4 py-2 font-semibold text-white hover:bg-navy-deep">My dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-full bg-navy px-4 py-2 font-semibold text-white hover:bg-navy-deep">Log in</a>
                    @endauth
                </div>
            </nav>
        </header>

        <main id="main" class="mx-auto w-full max-w-7xl flex-1 px-4 py-10 sm:px-6 lg:px-8">
            {{ $slot }}
        </main>

        <footer class="border-t-4 border-emerald bg-navy text-white">
            <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-6 text-xs text-white/60 sm:px-6 md:flex-row md:justify-between lg:px-8">
                <p>&copy; {{ date('Y') }} Integrated Sangguniang Kabataan Digital Governance Platform</p>
                <p>
                    <a href="{{ route('home') }}" class="hover:text-white">Home</a> ·
                    <a href="{{ route('privacy') }}" class="hover:text-white">Privacy notice</a>
                </p>
            </div>
        </footer>
    </body>
</html>
