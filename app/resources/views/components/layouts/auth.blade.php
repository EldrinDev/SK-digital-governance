@props([
    'title',
    'heading',
    'subheading' => null,
])
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
    <body class="bg-white font-sans text-navy antialiased">
        <div class="grid min-h-dvh lg:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)]">
            <main class="flex flex-col bg-white">
                {{-- Phone header: a short, dimmed strip of the same Ulo ng Apo photo --}}
                <div class="relative isolate h-40 overflow-hidden bg-navy-deep lg:hidden">
                    <img
                        src="{{ asset('images/ulo-ng-apo.jpg') }}"
                        alt=""
                        width="1920"
                        height="1446"
                        class="absolute inset-0 -z-20 h-full w-full object-cover object-[50%_22%] brightness-[0.45] saturate-[0.7]"
                    >
                    <div class="absolute inset-0 -z-10 bg-gradient-to-r from-navy-deep/90 to-navy-deep/30"></div>
                    <div class="flex items-center justify-between gap-4 px-4 pt-5 sm:px-10">
                        <a href="{{ route('home') }}" class="flex items-center gap-3 rounded-[8px]">
                            <img src="{{ asset('images/sk-logo.svg') }}" alt="" width="40" height="40" class="h-10 w-10">
                            <span class="leading-tight">
                                <span class="block font-display text-sm font-semibold text-white">SK Digital Governance</span>
                                <span class="block text-[0.65rem] font-medium tracking-[0.18em] text-white/70">OLONGAPO CITY</span>
                            </span>
                        </a>
                        <a href="{{ route('home') }}" class="rounded-[8px] text-xs font-medium text-white/80 hover:text-white">Home</a>
                    </div>
                    <svg viewBox="0 0 800 140" preserveAspectRatio="none" class="absolute inset-x-0 -bottom-px h-12 w-full" aria-hidden="true">
                        <path d="M0 140V96C230 150 480 132 630 86 700 66 760 36 800 2v138Z" fill="#E5B64B"/>
                        <path d="M0 140v-34c240 50 480 34 640-6 70-17 120-42 160-72v112Z" fill="#16845B"/>
                        <path d="M0 140v-24c250 44 490 30 650 0 70-13 115-32 150-54v78Z" fill="#FFFFFF"/>
                    </svg>
                </div>

                {{-- Desktop header --}}
                <div class="hidden items-center justify-between gap-4 px-16 pt-8 lg:flex">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 rounded-[8px]">
                        <img src="{{ asset('images/sk-logo.svg') }}" alt="" width="44" height="44" class="h-10 w-10">
                        <span class="leading-tight">
                            <span class="block font-display text-base font-semibold text-navy">SK Digital Governance</span>
                            <span class="block text-[0.7rem] font-medium tracking-[0.18em] text-slate-muted">OLONGAPO CITY</span>
                        </span>
                    </a>
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 rounded-[8px] text-sm font-medium text-slate-muted hover:text-emerald">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5"/><path d="m11 6-6 6 6 6"/></svg>
                        Back to home
                    </a>
                </div>

                <div class="mx-auto flex w-full max-w-sm flex-1 flex-col justify-center px-4 pb-10 pt-6 sm:px-0 lg:py-12">
                    <h1 class="font-display text-[1.9rem] font-semibold tracking-tight text-navy">{{ $heading }}</h1>
                    @if ($subheading)
                        <p class="mt-2 leading-relaxed text-slate-muted">{{ $subheading }}</p>
                    @endif

                    <div class="mt-8">
                        @if (session('status'))
                            <div role="status" class="mb-6 flex items-start gap-3 rounded-[12px] border border-success/30 bg-success/10 p-4 text-sm leading-relaxed">
                                <svg viewBox="0 0 24 24" class="mt-0.5 h-5 w-5 shrink-0 text-success" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg>
                                <p>{{ session('status') }}</p>
                            </div>
                        @endif

                        {{ $slot }}
                    </div>
                </div>

                <p class="px-4 pb-6 text-xs text-slate-muted sm:px-10 lg:px-16">
                    &copy; {{ date('Y') }} SK Digital Governance ·
                    <a href="{{ route('privacy') }}" class="underline-offset-2 hover:text-emerald hover:underline">Privacy notice</a>
                </p>
            </main>

            <aside class="relative isolate hidden overflow-hidden bg-navy-deep lg:block" aria-hidden="true">
                <img
                    src="{{ asset('images/ulo-ng-apo.jpg') }}"
                    alt=""
                    width="1920"
                    height="1446"
                    class="absolute inset-0 -z-20 h-full w-full object-cover object-[50%_30%] brightness-[0.45] saturate-[0.7]"
                >
                <div class="absolute inset-0 -z-10 bg-gradient-to-t from-navy-deep via-navy-deep/40 to-navy-deep/10"></div>

                <p class="absolute right-8 top-8 text-xs text-white/60">Ulo ng Apo, Olongapo City</p>

                <div class="absolute inset-x-0 bottom-0 px-14 pb-36">
                    <p class="max-w-md font-display text-3xl leading-snug font-semibold text-white">
                        Empowering Youth.
                        <span class="block text-gold">Building Communities.</span>
                    </p>
                    <p class="mt-4 max-w-sm text-sm leading-relaxed text-white/70">
                        Programs, projects, and public records from participating barangays in Olongapo City.
                    </p>
                </div>

                <svg viewBox="0 0 800 140" preserveAspectRatio="none" class="absolute inset-x-0 -bottom-px h-24 w-full">
                    <path d="M0 140V96C230 150 480 132 630 86 700 66 760 36 800 2v138Z" fill="#E5B64B"/>
                    <path d="M0 140v-34c240 50 480 34 640-6 70-17 120-42 160-72v112Z" fill="#16845B"/>
                </svg>
            </aside>
        </div>
    </body>
</html>
