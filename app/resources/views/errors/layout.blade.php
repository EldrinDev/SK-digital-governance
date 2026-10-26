{{-- Standalone on purpose: error pages must render even when the database or session is down. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex">
        <title>@yield('title') · SK Digital Governance</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('images/sk-logo.svg') }}">
        @vite(['resources/css/app.css'])
    </head>
    <body class="flex min-h-dvh items-center justify-center bg-mist px-4 py-16 font-sans text-navy antialiased">
        <main class="w-full max-w-md text-center">
            <img src="{{ asset('images/sk-logo.svg') }}" alt="SK Olongapo" width="64" height="64" class="mx-auto h-16 w-16">
            <p class="mt-8 font-display text-sm font-semibold tracking-[0.18em] text-emerald">ERROR @yield('code')</p>
            <h1 class="mt-2 font-display text-3xl font-semibold tracking-tight">@yield('title')</h1>
            <p class="mt-3 leading-relaxed text-slate-muted">@yield('message')</p>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ url('/') }}" class="rounded-[8px] bg-navy px-5 py-2.5 text-sm font-semibold text-white hover:bg-navy-deep">Go to the home page</a>
                <a href="javascript:history.back()" class="rounded-[8px] border border-line bg-white px-5 py-2.5 text-sm font-semibold hover:border-navy">Go back</a>
            </div>
        </main>
    </body>
</html>
