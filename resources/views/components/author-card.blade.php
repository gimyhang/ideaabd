@php
    $photoUrl = $author->avatar_url;
    $initials = $author->initials ?? (mb_substr($author->name, 0, 1) ?: 'লে');
    $bgColor = $author->avatar_bg_color ?? 'linear-gradient(135deg, #0284c7 0%, #0369a1 100%)';
    $booksCount = $author->books_count ?? 0;
    $authorBooks = $author->relationLoaded('books') ? $author->books : collect();
    $penName = $author->pen_name;
    $authorUrl = route('authors.show', $author->slug ?: $author->id);

    // Bestseller / Latest Book
    $topBook = $authorBooks->first();
    $bookCoverUrl = null;
    if ($topBook && !empty($topBook->cover_image)) {
        $bookCoverUrl = str_starts_with($topBook->cover_image, 'http')
            ? $topBook->cover_image
            : asset('storage/' . ltrim($topBook->cover_image, '/'));
    }
@endphp

<div class="card h-100 border shadow-2xs rounded-3 overflow-hidden bg-white position-relative transition-all hover-lift author-compact-card p-2.5" 
     style="border-color: #e2e8f0 !important;">
    <div class="d-flex align-items-center justify-content-between gap-2.5 w-100">
        
        {{-- 1. LEFT: Author Avatar Icon (বামে লেখক ছবির আইকন) --}}
        <a href="{{ $authorUrl }}" class="text-decoration-none flex-shrink-0 position-relative" title="{{ $author->name }}">
            <div class="rounded-circle overflow-hidden shadow-xs position-relative border border-2 border-white" 
                 style="width: 52px; height: 52px; min-width: 52px; aspect-ratio: 1 / 1; background: {{ $bgColor }};">
                @if($photoUrl)
                    <img src="{{ $photoUrl }}" alt="{{ $author->name }}" class="w-100 h-100 object-fit-cover position-absolute top-0 start-0"
                         onerror="this.style.display='none'; this.nextElementSibling.classList.remove('d-none');">
                    <div class="w-100 h-100 d-none d-flex align-items-center justify-content-center text-white fs-5 fw-bold position-absolute top-0 start-0" style="background: {{ $bgColor }};">
                        {{ $initials }}
                    </div>
                @else
                    <div class="w-100 h-100 d-flex align-items-center justify-content-center text-white fs-5 fw-bold position-absolute top-0 start-0">
                        {{ $initials }}
                    </div>
                @endif
            </div>

            @if($author->is_verified)
                <span class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-0.5 d-flex align-items-center justify-content-center border border-white shadow-xs" 
                      style="width: 17px; height: 17px; font-size: 8px;" title="যাচাইকৃত লেখক">
                    <i class="fas fa-check"></i>
                </span>
            @endif
        </a>

        {{-- 2. CENTER: Author Name & Book Count (মাঝে লেখক নাম ও বই সংখ্যা - এলাইন মিডিল) --}}
        <div class="min-w-0 flex-grow-1 text-center d-flex flex-column align-items-center justify-content-center px-1">
            <h6 class="fw-bold text-dark mb-0.5 text-truncate w-100" style="font-size: 0.92rem;" title="{{ $author->name }}">
                <a href="{{ $authorUrl }}" class="text-decoration-none text-dark hover-primary">
                    {{ $author->name }}
                </a>
            </h6>

            @if($penName)
                <div class="text-muted small text-truncate w-100 mb-1" style="font-size: 0.72rem;">
                    <i class="fa-solid fa-feather-pointed text-warning me-0.5"></i>{{ $penName }}
                </div>
            @endif

            <div class="d-flex align-items-center justify-content-center gap-1.5 flex-wrap mt-0.5">
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 rounded-pill px-2 py-0.5 fw-semibold" style="font-size: 0.70rem;">
                    <i class="fas fa-book-open me-1"></i>@bn($booksCount)টি বই
                </span>

                @if($author->is_verified)
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 rounded-pill px-1.5 py-0.5" style="font-size: 0.68rem;">
                        যাচাইকৃত
                    </span>
                @endif
            </div>
        </div>

        {{-- 3. RIGHT: Bestseller / Latest Book Cover or Auto Blank Cover (ডানে বেস্টসেলার বইয়ের ছবি / ব্লাংক কভার) --}}
        <div class="flex-shrink-0 text-end">
            @if($topBook)
                <a href="{{ route('book.show', $topBook->slug ?: $topBook->id) }}" class="text-decoration-none d-inline-block position-relative" title="{{ $topBook->title }}">
                    <div class="rounded-2 overflow-hidden shadow-2xs border bg-light position-relative" 
                         style="width: 46px; height: 65px; border-left: 3px solid #1e293b !important;">
                        @if($bookCoverUrl)
                            <img src="{{ $bookCoverUrl }}" alt="{{ $topBook->title }}" class="w-100 h-100 object-fit-cover">
                        @else
                            {{-- Auto minimal cover for author book without uploaded cover image --}}
                            <div class="w-100 h-100 d-flex flex-column justify-content-between p-1 text-center" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                                <span class="text-white text-truncate fw-bold" style="font-size: 7.5px; line-height: 1.1;">{{ $topBook->title }}</span>
                                <span class="text-warning small" style="font-size: 7px;">আইডিয়া</span>
                            </div>
                        @endif
                    </div>
                </a>
            @else
                {{-- Automatic Clean Blank Book Cover Mockup (ব্লাংক অটোমেটিক কভার) --}}
                <a href="{{ $authorUrl }}" class="text-decoration-none d-inline-block position-relative" title="বইয়ের জন্য অপেক্ষায়">
                    <div class="rounded-2 overflow-hidden shadow-2xs border d-flex flex-column align-items-center justify-content-between p-1 position-relative" 
                         style="width: 46px; height: 65px; background: #f8fafc; border-color: #cbd5e1 !important; border-left: 3px solid #64748b !important;">
                        <i class="fa-solid fa-book-open text-muted opacity-40 mt-1" style="font-size: 11px;"></i>
                        <span class="text-muted fw-bold text-uppercase tracking-wider opacity-60" style="font-size: 7px;">IDEA</span>
                        <div class="w-100 rounded-pill bg-slate-200" style="height: 2px;"></div>
                    </div>
                </a>
            @endif
        </div>

    </div>
</div>

