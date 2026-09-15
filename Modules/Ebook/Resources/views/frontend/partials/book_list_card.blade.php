@php
    $isOwned = !empty($userLibraryIds) && in_array($ebook->id, $userLibraryIds);
    $authorName = $ebook->author?->name ?: ($ebook->author_name ?: 'আইডিয়া লেখক');
    $authorUrl = $ebook->author ? route('authors.show', $ebook->author->id ?? $ebook->author->slug) : '#';
    $cleanDesc = $ebook->description ? Str::limit(trim(strip_tags(html_entity_decode((string)$ebook->description, ENT_QUOTES | ENT_HTML5, 'UTF-8'))), 220) : ($ebook->title . ' — ' . $authorName);
@endphp

<div class="card border-0 shadow-sm rounded-4 overflow-hidden ebook-card-modern bg-white p-3 p-md-3.5 position-relative">
    <div class="row g-3 align-items-center">
        
        {{-- Cover Image --}}
        <div class="col-4 col-sm-3 col-md-2 text-center flex-shrink-0">
            <a href="{{ route('ebook.show', $ebook->slug) }}" class="d-block position-relative mx-auto book-3d-wrapper" style="width: 90px; aspect-ratio: 2/3;">
                <img src="{{ $ebook->cover_url ?: 'https://placehold.co/180x270?text=E-Book' }}" 
                     alt="{{ $ebook->title }}" 
                     class="w-100 h-100 object-fit-cover rounded-2 shadow-xs">
                <div class="book-spine-lighting"></div>
            </a>
        </div>

        {{-- Book Metadata & Synopsis --}}
        <div class="col-8 col-sm-9 col-md-7">
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                @if($ebook->category)
                    <span class="badge bg-light text-primary border rounded-pill px-2.5 py-0.5 small" style="font-size: 11px;">
                        <i class="fa-solid fa-tag me-1"></i>{{ $ebook->category->name }}
                    </span>
                @endif
                <span class="badge bg-light text-dark border rounded-pill px-2 py-0.5 font-monospace small" style="font-size: 11px;">
                    {{ $ebook->format_badge }}
                </span>
                @if($isOwned)
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 small" style="font-size: 11px;">
                        <i class="fa-solid fa-check-double me-1"></i> লাইব্রেরিতে আছে
                    </span>
                @elseif($ebook->is_free)
                    <span class="badge bg-success text-white rounded-pill px-2 py-0.5 small" style="font-size: 11px;">
                        বিনামূল্যে
                    </span>
                @endif
            </div>

            <h5 class="fw-bold text-dark mb-1">
                <a href="{{ route('ebook.show', $ebook->slug) }}" class="text-decoration-none text-dark hover-text-primary">
                    {{ $ebook->title }}
                </a>
            </h5>

            <div class="small text-muted mb-2">
                <i class="fa-solid fa-feather-pointed me-1"></i>
                @if($ebook->author)
                    <a href="{{ $authorUrl }}" class="text-decoration-none text-muted hover-text-primary fw-semibold">
                        {{ $authorName }}
                    </a>
                @else
                    <span>{{ $authorName }}</span>
                @endif
                @if($ebook->publisher)
                    <span class="mx-1.5">•</span>
                    <i class="fa-solid fa-building me-1"></i>{{ $ebook->publisher->name }}
                @endif
                @if($ebook->pages)
                    <span class="mx-1.5">•</span>
                    <i class="fa-solid fa-file-lines me-1"></i>{{ $ebook->pages }} পৃ.
                @endif
            </div>

            <p class="text-secondary small mb-0 line-clamp-2 d-none d-md-block" style="line-height: 1.55;">
                {{ $cleanDesc }}
            </p>
        </div>

        {{-- Price & Actions --}}
        <div class="col-12 col-md-3 text-md-end border-top border-md-top-0 pt-2 pt-md-0 d-flex flex-row flex-md-column justify-content-between align-items-center align-items-md-end gap-2">
            <div>
                @if($ebook->is_free)
                    <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold px-3 py-1 rounded-pill" style="font-size: 0.85rem;">
                        ফ্রি পাঠ্য
                    </span>
                @else
                    @if($ebook->discount_price && $ebook->discount_price < $ebook->price)
                        <div class="d-flex flex-column align-items-start align-items-md-end">
                            <span class="text-muted text-decoration-line-through small font-monospace">৳{{ round($ebook->price) }}</span>
                            <span class="fw-bold text-primary font-monospace fs-5">৳{{ round($ebook->discount_price) }}</span>
                        </div>
                    @else
                        <span class="fw-bold text-primary font-monospace fs-5">৳{{ round($ebook->price) }}</span>
                    @endif
                @endif
            </div>

            <div class="d-flex align-items-center gap-1.5">
                <button type="button" class="btn btn-sm btn-light border text-primary rounded-circle d-inline-flex align-items-center justify-content-center shadow-xs"
                        style="width: 32px; height: 32px;"
                        onclick="openQuickLookModal(@js($ebook->id), @js($ebook->title), @js($authorName), @js($ebook->category?->name ?? 'সাধারণ'), @js($ebook->price), @js($ebook->discount_price), @js($ebook->is_free), @js($ebook->cover_url), @js($ebook->format_badge), @js(route('ebook.show', $ebook->slug)), @js(route('ebook.read', $ebook->slug)), @js(route('ebook.preview', $ebook->slug)), @js($ebook->pages), @js($cleanDesc))"
                        title="একনজরে পড়ুন">
                    <i class="fa-regular fa-eye"></i>
                </button>
                <a href="{{ route('ebook.read', $ebook->slug) }}" class="btn btn-sm btn-primary rounded-pill px-3.5 py-1.5 fw-bold shadow-xs d-inline-flex align-items-center gap-1.5">
                    <i class="fa-solid fa-book-open-reader"></i>
                    <span>পড়ুন</span>
                </a>
            </div>
        </div>

    </div>
</div>
