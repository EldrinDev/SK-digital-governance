<x-layouts.app title="My digital ID" heading="My digital ID" description="Show this at SK events and the barangay hall. An SK officer scans the QR to check you in.">
    <x-slot:actions>
        <div class="flex flex-wrap gap-2 print:hidden">
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-[8px] bg-navy px-4 py-2.5 text-sm font-semibold text-white hover:bg-navy-deep">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Print or save as PDF
            </button>
            <a href="{{ route('account.edit') }}" class="inline-flex items-center rounded-[8px] border border-line bg-white px-4 py-2.5 text-sm font-semibold hover:border-navy">Back to my profile</a>
        </div>
    </x-slot:actions>

    <div class="grid items-start gap-8 lg:grid-cols-[minmax(0,32rem)_minmax(0,1fr)]">
        <x-youth.id-card :user="$user" :code="$code" class="print:w-[85.6mm]" />

        <div class="space-y-4 print:hidden">
            <section class="rounded-[14px] border border-line bg-white p-5">
                <h2 class="font-display text-lg font-semibold">How to use it</h2>
                <ol class="mt-3 space-y-3 text-sm leading-relaxed">
                    <li class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald/10 text-xs font-semibold text-emerald">1</span><span>Open this page on your phone at the event, or bring a printed copy.</span></li>
                    <li class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald/10 text-xs font-semibold text-emerald">2</span><span>An SK officer scans the QR with their phone camera. It shows them your name and barangay.</span></li>
                    <li class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald/10 text-xs font-semibold text-emerald">3</span><span>If you registered for today’s event, they check you in right there. Then you can rate the event on your dashboard.</span></li>
                </ol>
            </section>

            <section class="rounded-[14px] border border-line bg-white p-5">
                <h2 class="font-display text-lg font-semibold">Keep it safe</h2>
                <p class="mt-2 text-sm leading-relaxed text-slate-muted">
                    The QR holds only a random code, not your details, and only SK officers can open what it points to. Still, don’t post it online. If you lose your phone or shared a screenshot, replace your ID: the old QR stops working right away.
                </p>
                <form method="POST" action="{{ route('account.id.renew') }}" class="mt-4" onsubmit="return confirm('Replace your digital ID? Printed copies and screenshots of the old one will stop working.')">
                    @csrf
                    <button type="submit" class="rounded-[8px] border border-line px-4 py-2 text-sm font-semibold hover:border-error hover:text-[#b91c1c]">Replace my ID</button>
                </form>
            </section>
        </div>
    </div>
</x-layouts.app>
