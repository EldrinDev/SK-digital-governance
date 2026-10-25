@props([
    'title',
])
@php
    $user = auth()->user();
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
    <body class="min-h-screen bg-mist font-sans text-navy antialiased">
        <header class="border-b border-line bg-white">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3.5 sm:px-6 lg:px-8">
                <div class="flex min-w-0 items-center gap-3">
                    <a href="{{ route('home') }}" class="shrink-0 rounded-[8px]">
                        <img src="{{ asset('images/sk-logo.svg') }}" alt="SK Digital Governance home" width="40" height="40" class="h-10 w-10">
                    </a>
                    <div class="min-w-0 leading-tight">
                        <p class="truncate font-display text-base font-semibold">{{ $user->barangay?->name ?? 'Olongapo City' }}</p>
                        <p class="truncate text-xs text-slate-muted">{{ $user->role->label() }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <p class="hidden text-sm text-navy/80 sm:block">{{ $user->name }}</p>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-[8px] border border-navy/80 px-4 py-2 text-sm font-semibold text-navy transition-colors hover:bg-navy hover:text-white">Log out</button>
                    </form>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            {{ $slot }}
        </main>
    </body>
</html>
