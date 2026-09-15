@php
    $isOwned = !empty($userLibraryIds) && in_array($ebook->id, $userLibraryIds);
    $authorName = $ebook->author?->name ?: ($ebook->author_name ?: 'আইডিয়া লেখক');
    $authorUrl = $ebook->author ? route('authors.show', $ebook->author->id ?? $ebook->author->slug) : '#';
    $cleanDesc = $ebook->description ? Str::limit(trim(strip_tags(html_entity_decode((string)$ebook->description, ENT_QUOTES | ENT_HTML5, 'UTF-8'))), 160) : ($ebook->title . ' — ' . $authorName);
@endphp

<div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden ebook-card-modern position-relative bg-white d-flex flex-column">
    
    {{-- 3D Book Cover & Action Overlay Area --}}
    <div class="position-relative overflow-hidden ebook-cover-container" style="aspect-ratio: 7/10;">
        <a href="{{ route('ebook.show', $ebook->slug) }}" class="d-block w-100 h-100 text-decoration-none">
            @if($ebook->cover_url)
                <img src="{{ $ebook->cover_url }}" alt="{{ $ebook->title }}" 
                     class="w-100 h-100 object-fit-cover ebook-cover-img" loading="lazy">
            @else
                <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-muted p-3 text-center bg-light">
                    <i class="fa-solid fa-tablet-screen-button fs-1 text-primary opacity-40 mb-2"></i>
                    <span class="small fw-bold text-dark line-clamp-2">{{ $ebook->title }}</span>
                </div>
            @endif

            {{-- 3D Spine Lighting Overlay --}}
            <div class="book-spine-lighting"></div>
        </a>

        {{-- Format Badge (EPUB vs PDF) --}}
        <span class="badge position-absolute top-0 start-0 m-2 shadow-xs rounded-pill px-2.5 py-1 font-monospace z-1 {{ $ebook->format_badge === 'EPUB' ? 'bg-info text-white' : ($ebook->format_badge === 'EPUB + PDF' ? 'bg-primary text-white' : 'bg-dark bg-opacity-80 text-white') }}" style="font-size: 0.68rem; letter-spacing: 0.3px;">
            @if(str_contains($ebook->format_badge, 'EPUB'))
                <i class="fa-solid fa-book-open me-1"></i>
            @else
                <i class="fa-solid fa-file-pdf me-1 text-warning"></i>
            @endif
            {{ $ebook->format_badge }}
        </span>

        {{-- Top Right Status Badges --}}
        <div class="position-absolute top-0 end-0 m-2 d-flex flex-column gap-1 align-items-end z-1">
            @if($isOwned)
                <span class="badge bg-success text-white shadow-xs rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">
                    <i class="fa-solid fa-check-double me-0.5"></i> সংগৃহীত
                </span>
            @elseif($ebook->is_free)
                <span class="badge bg-success text-white shadow-xs rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">
                    <i class="fa-solid fa-gift me-0.5"></i> ফ্রি
                </span>
            @elseif($ebook->discount_percentage > 0)
                <span class="badge bg-danger text-white shadow-xs rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">
                    -{{ $ebook->discount_percentage }}%
                </span>
            @endif
        </div>

        {{-- Hover Action Overlay Bar --}}
        <div class="ebook-card-overlay position-absolute start-0 end-0 bottom-0 p-2 d-flex align-items-center justify-content-center gap-1.5 z-2">
            <button type="button" class="btn btn-sm btn-light rounded-pill shadow-sm fw-bold px-2.5 py-1 text-primary d-inline-flex align-items-center gap-1"
                    style="font-size: 0.75rem;"
                    onclick="openQuickLookModal(@js($ebook->id), @js($ebook->title), @js($authorName), @js($ebook->category?->name ?? 'সাধারণ'), @js($ebook->price), @js($ebook->discount_price), @js($ebook->is_free), @js($ebook->cover_url), @js($ebook->format_badge), @js(route('ebook.show', $ebook->slug)), @js(route('ebook.read', $ebook->slug)), @js(route('ebook.preview', $ebook->slug)), @js($ebook->pages), @js($cleanDesc))"
                    title="একনজরে পড়ুন">
                <i class="fa-regular fa-eye"></i>
                <span class="d-none d-sm-inline">একনজরে</span>
            </button>
            <a href="{{ route('ebook.read', $ebook->slug) }}" class="btn btn-sm btn-primary rounded-pill shadow-sm fw-bold px-3 py-1 text-white d-inline-flex align-items-center gap-1" style="font-size: 0.75rem;">
                <i class="fa-solid fa-book-open-reader"></i>
                <span>পড়ুন</span>
            </a>
        </div>
    </div>

    {{-- Card Body: Title, Author, Category & Price --}}
    <div class="card-body p-2.5 p-md-3 d-flex flex-column flex-grow-1">
        
        {{-- Category --}}
        @if($ebook->category)
            <div class="small text-muted mb-1 text-truncate" style="font-size: 0.72rem;">
                <span class="text-primary opacity-75 fw-semibold">{{ $ebook->category->name }}</span>
            </div>
        @else
            <div class="small text-muted mb-1" style="font-size: 0.72rem;">ডিজিটাল বই</div>
        @endif

        {{-- Title --}}
        <h6 class="fw-bold text-dark mb-1 line-clamp-2" style="font-size: 0.92rem; min-height: 2.5em; line-height: 1.3;" title="{{ $ebook->title }}">
            <a href="{{ route('ebook.show', $ebook->slug) }}" class="text-decoration-none text-dark hover-text-primary">
                {{ $ebook->title }}
            </a>
        </h6>

        {{-- Author --}}
        <div class="small text-muted text-truncate mb-2.5" style="font-size: 0.8rem;">
            <i class="fa-solid fa-feather-pointed opacity-50 me-1"></i>
            @if($ebook->author)
                <a href="{{ $authorUrl }}" class="text-decoration-none text-muted hover-text-primary">
                    {{ $authorName }}
                </a>
            @else
                <span>{{ $authorName }}</span>
            @endif
        </div>

        {{-- Price & Bottom Actions --}}
        <div class="mt-auto pt-2 border-top d-flex align-items-center justify-content-between">
            <div>
                @if($isOwned)
                    <span class="badge bg-success-subtle text-success border border-success-subtle fw-semibold px-2 py-0.5 rounded-pill" style="font-size: 0.75rem;">
                        <i class="fa-solid fa-circle-check me-1"></i> আপনার বই
                    </span>
                @elseif($ebook->is_free)
                    <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold px-2.5 py-1 rounded-pill" style="font-size: 0.78rem;">
                        বিনামূল্যে
                    </span>
                @else
                    @if($ebook->discount_price && $ebook->discount_price < $ebook->price)
                        <div class="d-flex flex-column" style="line-height: 1.15;">
                            <span class="text-muted text-decoration-line-through small font-monospace" style="font-size: 0.72rem;">৳{{ round($ebook->price) }}</span>
                            <span class="fw-bold text-primary font-monospace fs-6">৳{{ round($ebook->discount_price) }}</span>
                        </div>
                    @else
                        <span class="fw-bold text-primary font-monospace fs-6">৳{{ round($ebook->price) }}</span>
                    @endif
                @endif
            </div>

            {{-- Quick Audio & Read Action --}}
            <div class="d-flex align-items-center gap-1">
                <button type="button" class="btn btn-sm btn-light border text-primary rounded-circle d-inline-flex align-items-center justify-content-center btn-quick-audio shadow-xs"
                        style="width: 28px; height: 28px; font-size: 0.72rem;"
                        onclick="if(window.IdeaAudiobook) IdeaAudiobook.speakExcerpt(this, @js($ebook->title), @js($cleanDesc), @js(route('ebook.show', $ebook->slug)))"
                        title="ই-বুক বিবরণ শুনুন">
                    <i class="fa-solid fa-volume-high text-primary"></i>
                </button>
                <a href="{{ route('ebook.read', $ebook->slug) }}" 
                   class="btn btn-sm btn-primary rounded-pill px-2.5 py-1 fw-semibold shadow-xs text-nowrap" 
                   style="font-size: 0.78rem;">
                    <i class="fa-solid fa-book-open me-1"></i> পড়ুন
                </a>
            </div>
        </div>

    </div>
</div>
