{{--
    Page links for every list in the platform: Previous / Next with the page numbers between,
    and "Showing 1–9 of 17" so people know how much there is. Phones get Previous / Next only.
--}}
@if ($paginator->hasPages())
    @php
        $arrowLeft = '<svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>';
        $arrowRight = '<svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>';
        $button = 'inline-flex h-10 items-center gap-1.5 rounded-[8px] border px-3.5 text-sm font-semibold transition-colors';
    @endphp
    <nav role="navigation" aria-label="Pages" class="flex flex-col items-center gap-3 sm:flex-row sm:justify-between">
        <p class="order-2 text-sm text-slate-muted sm:order-1">
            Showing <span class="font-semibold text-navy tabular-nums">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</span>
            of <span class="font-semibold text-navy tabular-nums">{{ $paginator->total() }}</span>
        </p>

        <div class="order-1 flex w-full items-center justify-between gap-2 sm:order-2 sm:w-auto sm:justify-end">
            @if ($paginator->onFirstPage())
                <span class="{{ $button }} cursor-not-allowed border-line bg-white text-slate-muted/60" aria-disabled="true">{!! $arrowLeft !!} Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $button }} border-line bg-white text-navy hover:border-navy">{!! $arrowLeft !!} Previous</a>
            @endif

            <span class="text-sm text-slate-muted sm:hidden">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>

            <ul class="hidden items-center gap-1 sm:flex">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <li><span class="flex h-10 w-8 items-center justify-center text-sm text-slate-muted" aria-hidden="true">…</span></li>
                    @endif
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            <li>
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page" class="flex h-10 min-w-10 items-center justify-center rounded-[8px] bg-navy px-2 text-sm font-semibold text-white tabular-nums">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" class="flex h-10 min-w-10 items-center justify-center rounded-[8px] px-2 text-sm font-semibold text-navy/75 tabular-nums hover:bg-white hover:text-navy" aria-label="Page {{ $page }}">{{ $page }}</a>
                                @endif
                            </li>
                        @endforeach
                    @endif
                @endforeach
            </ul>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $button }} border-navy bg-navy text-white hover:bg-navy-deep">Next {!! $arrowRight !!}</a>
            @else
                <span class="{{ $button }} cursor-not-allowed border-line bg-white text-slate-muted/60" aria-disabled="true">Next {!! $arrowRight !!}</span>
            @endif
        </div>
    </nav>
@endif
