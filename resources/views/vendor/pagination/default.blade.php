@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="d-flex align-items-center justify-content-center justify-content-md-end gap-1.5 flex-wrap font-sans">
        
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="btn btn-sm btn-light text-muted border rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-1.5 opacity-50 cursor-not-allowed" aria-disabled="true" aria-label="পূর্ববর্তী পৃষ্ঠা">
                <i class="fas fa-chevron-left small"></i>
                <span class="fw-semibold">পূর্ববর্তী</span>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-1.5 shadow-xs fw-semibold hover-shadow" aria-label="পূর্ববর্তী পৃষ্ঠা">
                <i class="fas fa-chevron-left small"></i>
                <span>পূর্ববর্তী</span>
            </a>
        @endif

        {{-- Pagination Elements --}}
        <div class="d-none d-sm-inline-flex align-items-center gap-1">
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="btn btn-sm btn-link text-muted disabled px-1.5" aria-disabled="true">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="btn btn-sm btn-primary rounded-circle fw-bold shadow-xs d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" aria-current="page">
                                @bn($page)
                            </span>
                        @else
                            <a href="{{ $url }}" class="btn btn-sm btn-outline-secondary rounded-circle fw-semibold d-inline-flex align-items-center justify-content-center text-dark border-light-subtle hover-primary" style="width: 32px; height: 32px;">
                                @bn($page)
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        {{-- Mobile Indicator for small screens --}}
        <span class="d-inline-flex d-sm-none badge bg-light text-dark border px-2.5 py-1.5 font-monospace">
            পৃষ্ঠা @bn($paginator->currentPage()) / @bn($paginator->lastPage())
        </span>

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-1.5 shadow-xs fw-semibold hover-shadow" aria-label="পরবর্তী পৃষ্ঠা">
                <span>পরবর্তী</span>
                <i class="fas fa-chevron-right small"></i>
            </a>
        @else
            <span class="btn btn-sm btn-light text-muted border rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-1.5 opacity-50 cursor-not-allowed" aria-disabled="true" aria-label="পরবর্তী পৃষ্ঠা">
                <span class="fw-semibold">পরবর্তী</span>
                <i class="fas fa-chevron-right small"></i>
            </span>
        @endif

    </nav>
@endif
