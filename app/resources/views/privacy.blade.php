<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Privacy notice · SK Digital Governance</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('images/sk-logo.svg') }}">

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-white font-sans text-navy antialiased">
        <header class="border-b border-line">
            <div class="mx-auto flex max-w-3xl items-center gap-3 px-4 py-4 sm:px-6">
                <a href="{{ route('home') }}" class="flex items-center gap-3 rounded-[8px]">
                    <img src="{{ asset('images/sk-logo.svg') }}" alt="" width="40" height="40" class="h-10 w-10">
                    <span class="font-display text-base font-semibold">SK Digital Governance</span>
                </a>
            </div>
        </header>

        <main class="mx-auto max-w-3xl px-4 py-12 sm:px-6">
            <p class="mb-8 flex items-start gap-3 rounded-[12px] border border-warning/40 bg-warning/10 p-4 text-sm leading-relaxed">
                <svg viewBox="0 0 24 24" class="mt-0.5 h-5 w-5 shrink-0 text-warning" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 2 21h20L12 3Z"/><path d="M12 10v5M12 18h.01"/></svg>
                Draft for the pilot. This notice will be reviewed with the SK Federation and the participating barangay SK offices before real accounts are created.
            </p>

            <h1 class="font-display text-3xl font-semibold tracking-tight">Privacy notice</h1>
            <p class="mt-3 text-slate-muted">How the SK Platform handles the information you give when you create a youth account, under the Data Privacy Act of 2012 (RA 10173).</p>

            <div class="mt-10 space-y-8 leading-relaxed text-navy/80 [&_h2]:font-display [&_h2]:text-lg [&_h2]:font-semibold [&_h2]:text-navy [&_ul]:mt-2 [&_ul]:list-disc [&_ul]:space-y-1 [&_ul]:pl-5">
                <section>
                    <h2>What we collect</h2>
                    <ul>
                        <li>Your name, email address, and barangay.</li>
                        <li>The events and consultations you register for, and your attendance.</li>
                        <li>Your survey answers and the feedback you send.</li>
                    </ul>
                    <p class="mt-2">We only ask for what the SK needs to run its programs and to reach you about them.</p>
                </section>

                <section>
                    <h2>Why we use it</h2>
                    <p>To register you for youth programs, confirm your attendance, send you reminders, and let your barangay SK read and answer your feedback.</p>
                </section>

                <section>
                    <h2>Who can see it</h2>
                    <ul>
                        <li>The SK officers of your own barangay.</li>
                        <li>Authorized SK Federation users, in read-only form, for monitoring.</li>
                        <li>System administrators, only to keep the platform running.</li>
                    </ul>
                    <p class="mt-2">Your personal details are never shown on the public pages of this site.</p>
                </section>

                <section>
                    <h2>How it is protected</h2>
                    <p>Access depends on each person’s role and barangay, connections are encrypted, and sign-ins and changes are recorded in an audit trail.</p>
                </section>

                <section>
                    <h2>Your rights</h2>
                    <p>You may ask to see, correct, or delete your information, or withdraw your consent, by contacting your barangay SK office. You may also file a complaint with the National Privacy Commission.</p>
                </section>
            </div>

            <p class="mt-12 text-sm">
                <a href="{{ url()->previous() === url()->current() ? route('home') : url()->previous() }}" class="font-semibold text-emerald hover:underline">&larr; Go back</a>
            </p>
        </main>
    </body>
</html>
