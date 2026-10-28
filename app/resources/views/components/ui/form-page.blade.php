{{--
    A form with a guide beside it on wide screens, so a short form doesn't leave the rest of the
    page empty. The guide goes in the "guide" slot and drops below the form on phones.
--}}
<div {{ $attributes->class(['grid items-start gap-x-10 gap-y-8 lg:grid-cols-[minmax(0,1fr)_20rem] xl:grid-cols-[minmax(0,1fr)_23rem]']) }}>
    <div class="min-w-0">{{ $slot }}</div>
    @isset($guide)
        <aside class="min-w-0 space-y-8 lg:sticky lg:top-24">{{ $guide }}</aside>
    @endisset
</div>
