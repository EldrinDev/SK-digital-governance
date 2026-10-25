@php
    /**
     * Outline icon paths (24x24 viewBox, stroke-based) used across the landing page.
     *
     * @var array<string, string> $icons
     */
    $icons = [
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'wallet' => '<path d="M19 7V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-3"/><path d="M21 11h-5a2 2 0 0 0 0 4h5v-4Z"/><path d="M3 7h16"/>',
        'pin' => '<path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21Z"/><circle cx="12" cy="9.5" r="2.5"/>',
        'chat' => '<path d="M21 12a8 8 0 0 1-11.6 7.1L4 20.5l1.4-4.8A8 8 0 1 1 21 12Z"/><path d="M8.5 11h.01M12 11h.01M15.5 11h.01"/>',
        'file' => '<path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9l-6-6Z"/><path d="M14 3v6h6"/><path d="M8 13h8M8 17h5"/>',
        'shield' => '<path d="M12 3 4 6v6c0 5 3.5 8.5 8 9.5 4.5-1 8-4.5 8-9.5V6l-8-3Z"/><path d="m9 12 2 2 4-4"/>',
        'sprout' => '<path d="M12 21v-9"/><path d="M12 12c0-4-3-7-8-7 0 4 3 7 8 7Z"/><path d="M12 14c0-3.5 2.5-6 7-6 0 3.5-2.5 6-7 6Z"/>',
        'arrow' => '<path d="M5 12h14"/><path d="m13 6 6 6-6 6"/>',
        'login' => '<circle cx="12" cy="8" r="4"/><path d="M4 21v-1a7 7 0 0 1 14 0v1"/>',
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
    ];

    /**
     * What youth and residents can do on the public side of the platform.
     *
     * @var list<array{title: string, description: string, icon: string}> $participation
     */
    $participation = [
        ['title' => 'Join youth programs', 'description' => 'Sign up for sports leagues, skills training, and community events run by your barangay SK.', 'icon' => 'users'],
        ['title' => 'See where the SK fund goes', 'description' => 'Read the approved budget and spending reports for your barangay, in plain numbers.', 'icon' => 'wallet'],
        ['title' => 'Follow projects near you', 'description' => 'Check what is planned, what is underway, and what is finished, with photos as proof.', 'icon' => 'pin'],
        ['title' => 'Have your say', 'description' => 'Answer consultations and send feedback straight to your SK council.', 'icon' => 'chat'],
    ];

    /**
     * Documents each SK council publishes once they are approved.
     *
     * @var list<array{title: string, description: string}> $publicRecords
     */
    $publicRecords = [
        ['title' => 'Youth development plan (CBYDP)', 'description' => 'The three-year plan for youth priorities in the barangay.'],
        ['title' => 'Annual investment program (ABYIP)', 'description' => 'This year’s projects and how much each one is budgeted.'],
        ['title' => 'Financial reports', 'description' => 'What was spent, on what, and when.'],
        ['title' => 'Accomplishment reports', 'description' => 'Finished projects with photos and results.'],
        ['title' => 'Resolutions and minutes', 'description' => 'Decisions made by the SK council in session.'],
    ];

    /**
     * The yearly cycle every barangay SK follows, in order.
     *
     * @var list<array{title: string, description: string}> $steps
     */
    $steps = [
        ['title' => 'Plan', 'description' => 'The SK council turns youth priorities into a development plan and a yearly budget.'],
        ['title' => 'Carry out', 'description' => 'Projects and events move forward, with progress and photos recorded as they happen.'],
        ['title' => 'Report', 'description' => 'Spending and results are reviewed, approved, and published for the public.'],
        ['title' => 'Listen', 'description' => 'Youth and residents give feedback that shapes next year’s plan.'],
    ];

    /** @var list<array{label: string, href: string}> $navLinks */
    $navLinks = [
        ['label' => 'Home', 'href' => '#home'],
        ['label' => 'Programs', 'href' => '#participate'],
        ['label' => 'Transparency', 'href' => '#transparency'],
        ['label' => 'How it works', 'href' => '#how-it-works'],
        ['label' => 'Contact', 'href' => '#contact'],
    ];

    $loginUrl = route('login');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="SK Digital Governance for Olongapo City: join youth programs, see where the SK fund goes, and follow projects in your barangay.">
        <title>SK Digital Governance · Olongapo City</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('images/sk-logo.svg') }}">

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-mist font-sans text-navy antialiased">
        <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[60] focus:rounded-[8px] focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-semibold">Skip to content</a>

        <header class="sticky top-0 z-50 border-b border-line/70 bg-white/90 backdrop-blur-md">
            <nav class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-4 py-3.5 sm:px-6 lg:px-8" aria-label="Main">
                <a href="#home" class="flex items-center gap-3 rounded-[8px]">
                    <img src="{{ asset('images/sk-logo.svg') }}" alt="" width="44" height="44" class="h-9 w-9 sm:h-11 sm:w-11">
                    <span class="leading-tight">
                        <span class="block font-display text-sm font-semibold text-navy sm:text-[1.05rem]">SK Digital Governance</span>
                        <span class="block text-[0.7rem] font-medium tracking-[0.18em] text-slate-muted">OLONGAPO CITY</span>
                    </span>
                </a>

                <ul class="hidden items-center gap-8 text-sm font-medium text-navy/80 lg:flex">
                    @foreach ($navLinks as $link)
                        <li>
                            <a href="{{ $link['href'] }}" @class([
                                'relative py-2 transition-colors hover:text-emerald',
                                'text-emerald after:absolute after:inset-x-0 after:-bottom-0.5 after:h-0.5 after:rounded-full after:bg-emerald' => $loop->first,
                            ]) @if ($loop->first) aria-current="page" @endif>{{ $link['label'] }}</a>
                        </li>
                    @endforeach
                </ul>

                <div class="flex items-center gap-2">
                    <a href="{{ $loginUrl }}" class="inline-flex items-center gap-2 whitespace-nowrap rounded-full bg-navy px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-navy-deep sm:px-5">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons['login'] !!}</svg>
                        Log in
                    </a>

                    <details class="group relative lg:hidden">
                        <summary class="flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-[8px] border border-line text-navy [&::-webkit-details-marker]:hidden">
                            <span class="sr-only">Open menu</span>
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">{!! $icons['menu'] !!}</svg>
                        </summary>
                        <ul class="absolute right-0 mt-2 w-52 rounded-[12px] border border-line bg-white p-2 shadow-lg shadow-navy/10">
                            @foreach ($navLinks as $link)
                                <li><a href="{{ $link['href'] }}" class="block rounded-[8px] px-3 py-2 text-sm font-medium hover:bg-mist hover:text-emerald">{{ $link['label'] }}</a></li>
                            @endforeach
                        </ul>
                    </details>
                </div>
            </nav>
        </header>

        <main id="main">
            {{-- Hero: text on the left, dimmed Ulo ng Apo photo on the right --}}
            <section id="home" class="relative overflow-hidden bg-white">
                <div class="relative z-10 mx-auto max-w-7xl px-4 pb-10 pt-14 sm:px-6 lg:min-h-[36rem] lg:px-8 lg:pb-32 lg:pt-20">
                    <div class="max-w-xl">
                        <p class="text-xs font-semibold tracking-[0.2em] text-navy/80">SANGGUNIANG KABATAAN</p>
                        <h1 class="mt-4 font-display text-[2.4rem] leading-[1.1] font-bold tracking-tight sm:text-5xl lg:text-[3.1rem] lg:whitespace-nowrap xl:text-[3.4rem]">
                            <span class="block text-navy">Empowering Youth.</span>
                            <span class="block text-emerald">Building Communities.</span>
                        </h1>
                        <p class="mt-6 max-w-md text-lg leading-relaxed text-navy/75">
                            Join programs, see where the SK fund goes, and follow the projects in your barangay, all in one place.
                        </p>

                        <div class="mt-8 flex flex-wrap gap-3">
                            <a href="{{ route('events.index') }}" class="inline-flex items-center gap-2 rounded-[8px] bg-emerald px-6 py-3 text-sm font-semibold text-white shadow-sm shadow-emerald/30 transition-colors hover:bg-emerald-dark">
                                Find a program
                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons['arrow'] !!}</svg>
                            </a>
                            <a href="{{ route('transparency.index') }}" class="inline-flex items-center rounded-[8px] border border-navy/80 bg-white px-6 py-3 text-sm font-semibold text-navy transition-colors hover:bg-navy hover:text-white">
                                See public records
                            </a>
                        </div>

                        <ul class="mt-10 grid max-w-md grid-cols-3 gap-3 text-[0.8rem] leading-snug text-navy/80 sm:gap-4">
                            <li class="flex flex-col items-start gap-1.5 sm:flex-row sm:items-center sm:gap-2.5">
                                <svg viewBox="0 0 24 24" class="h-6 w-6 shrink-0 text-emerald" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons['shield'] !!}</svg>
                                Transparent governance
                            </li>
                            <li class="flex flex-col items-start gap-1.5 sm:flex-row sm:items-center sm:gap-2.5">
                                <svg viewBox="0 0 24 24" class="h-6 w-6 shrink-0 text-emerald" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons['users'] !!}</svg>
                                Youth engagement
                            </li>
                            <li class="flex flex-col items-start gap-1.5 sm:flex-row sm:items-center sm:gap-2.5">
                                <svg viewBox="0 0 24 24" class="h-6 w-6 shrink-0 text-emerald" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons['sprout'] !!}</svg>
                                Community development
                            </li>
                        </ul>
                    </div>
                </div>

                <figure class="relative h-72 sm:h-96 lg:absolute lg:inset-y-0 lg:right-0 lg:h-auto lg:w-[58%]">
                    <img
                        src="{{ asset('images/ulo-ng-apo.jpg') }}"
                        alt="The Ulo ng Apo monument in Olongapo City"
                        width="1920"
                        height="1446"
                        fetchpriority="high"
                        class="h-full w-full object-cover object-[50%_12%] brightness-[0.55] lg:object-[50%_30%] saturate-[0.75] [mask-image:linear-gradient(to_bottom,transparent,#000_18%)] lg:[mask-image:linear-gradient(to_right,transparent,#000_30%)]"
                    >
                    <figcaption class="absolute bottom-20 right-4 text-[0.7rem] text-white/80 sm:bottom-28 lg:bottom-32 lg:right-8">Ulo ng Apo, Olongapo City</figcaption>
                </figure>

                {{-- Flag-colored ribbon that closes the hero across its full width --}}
                <svg viewBox="0 0 1440 140" preserveAspectRatio="none" class="absolute inset-x-0 -bottom-px z-20 h-16 w-full sm:h-24 lg:h-28" aria-hidden="true">
                    <path d="M0 140V96C420 150 860 132 1140 86c120-20 220-50 300-84v138Z" fill="#E5B64B"/>
                    <path d="M0 140v-34c430 50 870 34 1150-6 120-17 215-42 290-72v112Z" fill="#16845B"/>
                    <path d="M0 140v-26c440 44 880 30 1160 0 120-13 210-32 280-54v80Z" fill="#F5F7FA"/>
                </svg>
            </section>

            {{-- What youth and residents can do --}}
            <section id="participate" class="scroll-mt-20">
                <div class="mx-auto grid max-w-7xl gap-10 px-4 py-16 sm:px-6 lg:grid-cols-[0.8fr_1.2fr] lg:gap-16 lg:px-8 lg:py-24">
                    <div>
                        <h2 class="font-display text-3xl font-semibold tracking-tight text-navy sm:text-[2.1rem]">For the youth of Olongapo</h2>
                        <p class="mt-4 max-w-md leading-relaxed text-slate-muted">
                            If you are 15 to 30 and live in the city, your barangay SK works for you. Here is what you can do without visiting the barangay hall.
                        </p>
                        <a href="{{ route('register') }}" class="mt-6 inline-flex items-center gap-2 rounded-[8px] bg-emerald px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-emerald-dark">
                            Create an account
                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons['arrow'] !!}</svg>
                        </a>
                    </div>

                    <ul class="grid gap-x-10 gap-y-8 sm:grid-cols-2">
                        @foreach ($participation as $item)
                            <li class="border-t-2 border-emerald/70 pt-5">
                                <svg viewBox="0 0 24 24" class="h-7 w-7 text-emerald" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons[$item['icon']] !!}</svg>
                                <h3 class="mt-4 font-display text-lg font-semibold text-navy">{{ $item['title'] }}</h3>
                                <p class="mt-2 text-[0.95rem] leading-relaxed text-navy/70">{{ $item['description'] }}</p>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>

            {{-- Public records --}}
            <section id="transparency" class="scroll-mt-20 bg-white">
                <div class="mx-auto grid max-w-7xl gap-10 px-4 py-16 sm:px-6 lg:grid-cols-2 lg:gap-16 lg:px-8 lg:py-24">
                    <div>
                        <h2 class="font-display text-3xl font-semibold tracking-tight text-navy sm:text-[2.1rem]">Every peso, out in the open</h2>
                        <p class="mt-4 max-w-md leading-relaxed text-slate-muted">
                            Once the SK council approves a plan or report, it can be published here for anyone to read. Pick your barangay to see what its council has released.
                        </p>
                        <p class="mt-6 flex max-w-md items-start gap-3 rounded-[12px] bg-mist p-4 text-sm leading-relaxed text-navy/75">
                            <svg viewBox="0 0 24 24" class="mt-0.5 h-5 w-5 shrink-0 text-info" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/></svg>
                            Personal details of youth members are never published. Only approved, public documents appear here.
                        </p>
                        <a href="{{ route('transparency.index') }}" class="mt-6 inline-flex items-center gap-2 rounded-[8px] bg-navy px-5 py-2.5 text-sm font-semibold text-white hover:bg-navy-deep">
                            Browse the transparency board
                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons['arrow'] !!}</svg>
                        </a>
                    </div>

                    <ul class="divide-y divide-line rounded-[12px] border border-line">
                        @foreach ($publicRecords as $record)
                            <li class="flex gap-4 p-5">
                                <svg viewBox="0 0 24 24" class="h-6 w-6 shrink-0 text-navy" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons['file'] !!}</svg>
                                <div>
                                    <h3 class="font-semibold text-navy">{{ $record['title'] }}</h3>
                                    <p class="mt-1 text-sm text-slate-muted">{{ $record['description'] }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>

            {{-- Yearly cycle --}}
            <section id="how-it-works" class="scroll-mt-20">
                <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
                    <h2 class="font-display text-3xl font-semibold tracking-tight text-navy sm:text-[2.1rem]">How a year of SK work runs</h2>
                    <p class="mt-4 max-w-xl leading-relaxed text-slate-muted">
                        Every barangay SK follows the same cycle. Participating barangays share each step here as it happens.
                    </p>

                    <ol class="mt-12 grid gap-10 sm:grid-cols-2 lg:grid-cols-4 lg:gap-8">
                        @foreach ($steps as $step)
                            <li class="relative">
                                <div class="flex items-center gap-4">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-navy font-display text-sm font-semibold text-white">{{ $loop->iteration }}</span>
                                    @unless ($loop->last)
                                        <span class="hidden h-px flex-1 bg-line lg:block" aria-hidden="true"></span>
                                    @endunless
                                </div>
                                <h3 class="mt-5 font-display text-lg font-semibold text-navy">{{ $step['title'] }}</h3>
                                <p class="mt-2 text-[0.95rem] leading-relaxed text-navy/70">{{ $step['description'] }}</p>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </section>

            {{-- Statement band with the call for SK officers --}}
            <section class="pb-16 lg:pb-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="relative isolate overflow-hidden rounded-[12px] bg-navy px-6 py-12 sm:px-12 lg:py-16">
                        <img
                            src="{{ asset('images/skyline-freeport.jpg') }}"
                            alt=""
                            width="1920"
                            height="1440"
                            loading="lazy"
                            class="absolute inset-0 -z-10 h-full w-full object-cover object-[50%_35%] opacity-30 mix-blend-luminosity"
                        >
                        <div class="absolute inset-0 -z-10 bg-gradient-to-r from-navy via-navy/90 to-navy/40"></div>
                        <svg viewBox="0 0 600 200" preserveAspectRatio="none" class="absolute -right-10 bottom-0 -z-10 h-28 w-[70%] sm:h-36" aria-hidden="true">
                            <path d="M0 200C180 190 360 150 600 20v14C380 160 200 196 0 200Z" fill="#E5B64B"/>
                            <path d="M60 200C240 186 420 140 600 56v144Z" fill="#16845B"/>
                        </svg>

                        <figure>
                            <svg viewBox="0 0 32 24" class="h-7 w-9 text-gold" fill="currentColor" aria-hidden="true"><path d="M0 24V14C0 6 4 1.5 12 0l1.5 3C9 4.5 7 7.5 7 11h6v13H0Zm19 0V14c0-8 4-12.5 12-14l1 3c-4.5 1.5-6.5 4.5-6.5 8H32v13H19Z"/></svg>
                            <blockquote class="mt-5 max-w-2xl font-display text-xl leading-snug font-medium text-white text-balance sm:text-[1.65rem]">
                                Young people are not only the leaders of tomorrow. They are our partners today.
                            </blockquote>
                        </figure>

                        <div class="mt-8 flex max-w-2xl flex-col gap-4 sm:flex-row sm:items-center">
                            <p class="text-sm leading-relaxed text-white/70">Are you an SK official? Log in to update your barangay’s plans, projects, and reports.</p>
                            <a href="{{ $loginUrl }}" class="inline-flex shrink-0 items-center justify-center rounded-[8px] bg-white px-5 py-2.5 text-sm font-semibold text-navy transition-colors hover:bg-gold">Log in as SK official</a>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer id="contact" class="border-t-4 border-emerald bg-navy text-white">
            <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 md:grid-cols-[1.4fr_1fr] lg:px-8">
                <div>
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/sk-logo.svg') }}" alt="" width="48" height="48" class="h-12 w-12">
                        <span class="leading-tight">
                            <span class="block font-display text-lg font-semibold">SK Digital Governance</span>
                            <span class="block text-[0.7rem] font-medium tracking-[0.18em] text-white/60">OLONGAPO CITY</span>
                        </span>
                    </div>
                    <p class="mt-6 text-white/75">Youth. Governance. A stronger tomorrow.</p>
                </div>

                <nav aria-label="Footer">
                    <ul class="grid grid-cols-2 gap-3 text-sm text-white/75">
                        @foreach ($navLinks as $link)
                            <li><a href="{{ $link['href'] }}" class="hover:text-gold">{{ $link['label'] }}</a></li>
                        @endforeach
                        <li><a href="{{ $loginUrl }}" class="hover:text-gold">Log in</a></li>
                    </ul>
                </nav>
            </div>
            <div class="border-t border-white/10">
                <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-5 text-xs text-white/55 sm:px-6 md:flex-row md:justify-between lg:px-8">
                    <p>&copy; {{ date('Y') }} Integrated Sangguniang Kabataan Digital Governance Platform</p>
                    <p>
                        Photos:
                        <a href="https://commons.wikimedia.org/wiki/File:Ulo_ng_Apo,_Olongapo_City,_Jan_2023.jpg" class="underline decoration-white/30 underline-offset-2 hover:text-white" rel="noopener">Ralff Nestor Nacor</a>
                        and
                        <a href="https://commons.wikimedia.org/wiki/File:Olongapo_City-Subic_Freeport_skyline_Santa_Rita_(Olongapo,_Zambales;_05-21-2023).jpg" class="underline decoration-white/30 underline-offset-2 hover:text-white" rel="noopener">Patrickroque01</a>,
                        <a href="https://creativecommons.org/licenses/by-sa/4.0/" class="underline decoration-white/30 underline-offset-2 hover:text-white" rel="noopener">CC BY-SA 4.0</a>
                    </p>
                </div>
            </div>
        </footer>
    </body>
</html>
