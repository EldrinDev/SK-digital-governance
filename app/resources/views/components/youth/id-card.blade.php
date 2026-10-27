@props([
    'user',
    'code',
])
@php
    use App\QrCode;

    $barangay = $user->barangay;
@endphp
{{--
    The digital Katipunan ng Kabataan ID: ID-card proportions (85.6 × 54 mm), the member's name,
    barangay and code, and a QR that opens the check page for SK officers. The QR holds only a
    random code, never personal details.
--}}
<div {{ $attributes->class(['relative aspect-[1.586] w-full overflow-hidden rounded-[18px] bg-navy text-white shadow-xl shadow-navy/20 [container-type:inline-size] print:shadow-none']) }}>
    {{-- Brand stripe and a faint seal --}}
    <div class="absolute inset-y-0 right-0 w-[38%] bg-gradient-to-b from-emerald to-emerald-dark" aria-hidden="true"></div>
    <div class="absolute inset-y-0 right-[38%] w-[2.2%] bg-gold" aria-hidden="true"></div>
    <img src="{{ asset('images/sk-logo.svg') }}" alt="" class="pointer-events-none absolute -bottom-[18%] -left-[8%] w-[45%] opacity-[0.07]">

    <div class="relative grid h-full grid-cols-[1fr_38%]">
        <div class="flex min-w-0 flex-col justify-between p-[5cqw]">
            <div class="flex items-center gap-[2.5cqw]">
                <img src="{{ asset('images/sk-logo.svg') }}" alt="" class="h-[9cqw] w-[9cqw] shrink-0 rounded-full bg-white p-[0.6cqw]">
                <div class="min-w-0 leading-tight">
                    <p class="whitespace-nowrap font-display text-[3cqw] font-semibold">Katipunan ng Kabataan</p>
                    <p class="truncate text-[2.5cqw] text-white/70">SK {{ $barangay?->name }}, Olongapo City</p>
                </div>
            </div>

            <div class="min-w-0">
                <p class="text-[2.3cqw] font-medium text-gold">Youth member</p>
                <p class="mt-[0.4cqw] line-clamp-2 font-display text-[5.6cqw] font-semibold leading-[1.1]">{{ $user->name }}</p>
            </div>

            <dl class="grid grid-cols-2 gap-x-[3cqw] text-[2.3cqw] leading-tight">
                <div>
                    <dt class="text-white/60">ID number</dt>
                    <dd class="mt-[0.3cqw] font-semibold tracking-wide">{{ $code }}</dd>
                </div>
                <div>
                    <dt class="text-white/60">Member since</dt>
                    <dd class="mt-[0.3cqw] font-semibold">{{ $user->created_at->format('M Y') }}</dd>
                </div>
            </dl>
        </div>

        <div class="flex flex-col items-center justify-center gap-[2cqw] p-[3.5cqw]">
            <div class="w-full rounded-[2.2cqw] bg-white p-[1.8cqw] [&_svg]:h-auto [&_svg]:w-full">
                {{ QrCode::svg(route('youth-id.verify', $code), 240) }}
            </div>
            <p class="text-center text-[2.1cqw] font-medium leading-tight text-white/90">Scan to check this ID</p>
            <img src="{{ $barangay?->logoUrl() ?? asset('images/sk-logo.svg') }}" alt="" class="h-[7cqw] w-[7cqw] rounded-full bg-white object-contain p-[0.5cqw]">
        </div>
    </div>
</div>
