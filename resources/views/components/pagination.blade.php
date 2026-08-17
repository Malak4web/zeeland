@if ($paginator->hasPages())
    <nav role="navigation" aria-label="التنقّل بين الصفحات" class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-[0.82rem] text-cream-3 zl-page-count">
            <span class="num">{{ $paginator->firstItem() ?? 0 }}</span>–<span class="num">{{ $paginator->lastItem() ?? 0 }}</span>
            من <span class="num">{{ $paginator->total() }}</span>
        </p>

        <div class="flex items-center gap-1.5">
            @if ($paginator->onFirstPage())
                <span class="grid size-10 place-items-center rounded-xl border border-navy-2 text-cream-3/50" aria-disabled="true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 6 6 6-6 6"/></svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="السابق"
                   class="grid size-10 place-items-center rounded-xl border border-navy-2 text-cream-2 transition-colors hover:border-navy-3 hover:text-cream">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 6 6 6-6 6"/></svg>
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-1.5 text-cream-3">{{ $element }}</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="num grid size-10 place-items-center rounded-xl bg-flame text-[0.82rem] font-semibold text-on-flame">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="num grid size-10 place-items-center rounded-xl border border-navy-2 text-[0.82rem] text-cream-2 transition-colors hover:border-navy-3 hover:text-cream">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="التالي"
                   class="grid size-10 place-items-center rounded-xl border border-navy-2 text-cream-2 transition-colors hover:border-navy-3 hover:text-cream">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 6-6 6 6 6"/></svg>
                </a>
            @else
                <span class="grid size-10 place-items-center rounded-xl border border-navy-2 text-cream-3/50" aria-disabled="true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 6-6 6 6 6"/></svg>
                </span>
            @endif
        </div>
    </nav>
@endif
