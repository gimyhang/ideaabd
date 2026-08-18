@php
    $cover = $book->cover_image;
    $coverUrl = null;
    if ($cover) {
        if (str_starts_with($cover, 'http')) {
            $coverUrl = $cover;
        } elseif (str_starts_with($cover, 'storage/')) {
            $coverUrl = asset($cover);
        } elseif (str_starts_with($cover, '/storage/')) {
            $coverUrl = asset(ltrim($cover, '/'));
        } else {
            $coverUrl = asset('storage/' . $cover);
        }
    }
    
    // Resolve Author from Authors relation or directory fallback
    $firstAuthor = $book->authors->first();
    if (!$firstAuthor && $book->author_link_id) {
        $firstAuthor = \Modules\Author\Models\Author::find($book->author_link_id);
    }
    if (!$firstAuthor && $book->author_name) {
        $firstAuthor = \Modules\Author\Models\Author::where('name', $book->author_name)->first();
    }
    
    $authorName = $firstAuthor ? $firstAuthor->name : ($book->author_name ?: 'আইডিয়া প্রকাশন');
    $authorUrl = $firstAuthor ? route('authors.show', $firstAuthor->slug ?? $firstAuthor->id) : null;
    
    $discountPercentage = ($book->price > 0 && $book->discount_price && $book->discount_price < $book->price)
        ? round((($book->price - $book->discount_price) / $book->price) * 100)
        : null;
    $isOutOfStock = isset($book->stock_quantity) && $book->stock_quantity <= 0;
    $finalPrice = ($book->discount_price && $book->discount_price < $book->price) ? $book->discount_price : $book->price;
@endphp

<div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden hover-lift p-2.5 d-flex flex-column text-center position-relative product-card-modern" 
     style="background: #ffffff; transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease; border: 1px solid #eef2f6 !important;">
    
    <!-- Top Badges Row -->
    <div class="position-absolute top-0 start-0 w-100 p-2 d-flex justify-content-between align-items-center z-2 pointer-events-none">
        @if($discountPercentage)
            <span class="badge bg-danger rounded-pill shadow-xs fw-bold" style="font-size: 0.72rem; letter-spacing: 0.3px;">
                -{{ $discountPercentage }}%
            </span>
        @else
            <span></span>
        @endif

        @php
            $hasHardcover = (isset($book->has_hardcover) && $book->has_hardcover)
                || in_array($book->cover_type ?? '', ['hardcover', 'both'], true)
                || (!empty($book->hardcover_price) && (float)$book->hardcover_price > 0)
                || (($book->format ?? '') === 'hardcover');
            $isEbook = (($book->format ?? '') === 'ebook' && !$hasHardcover && ($book->cover_type ?? '') !== 'paperback');
        @endphp

        @if($book->stock_status === 'pre_order')
            <span class="badge bg-warning text-dark border border-warning-subtle rounded-pill px-2 py-0.5 small fw-bold shadow-xs" style="font-size: 0.68rem;">
                <i class="fa-solid fa-clock-rotate-left me-0.5"></i> প্রি-অর্ডার
            </span>
        @elseif($isOutOfStock || $book->stock_status === 'out_of_stock')
            <span class="badge bg-dark bg-opacity-75 text-white rounded-pill px-2 py-0.5 small" style="font-size: 0.7rem;">
                স্টক আউট
            </span>
        @elseif($isEbook)
            <span class="badge bg-white text-dark shadow-xs border rounded-pill px-2 py-0.5 small" style="font-size: 0.68rem;">
                ই-বুক
            </span>
        @elseif($hasHardcover)
            <span class="badge bg-white text-dark shadow-xs border rounded-pill px-2 py-0.5 small" style="font-size: 0.68rem;">
                হার্ডকভার
            </span>
        @else
            <span class="badge bg-white text-dark shadow-xs border rounded-pill px-2 py-0.5 small" style="font-size: 0.68rem;">
                পেপারব্যাক
            </span>
        @endif
    </div>

    <!-- Book Cover Image Container (7:10 Aspect Ratio) -->
    <div class="position-relative overflow-hidden rounded-3 mb-2.5 mx-auto w-100 group-hover" 
         style="aspect-ratio: 7 / 10; background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);">
        
        <a href="{{ route('book.show', $book->slug) }}" class="d-block w-100 h-100 text-decoration-none">
            @if($coverUrl)
                <img src="{{ $coverUrl }}" 
                     alt="{{ $book->title }}" 
                     class="w-100 h-100 object-fit-cover transition-transform"
                     loading="lazy"
                     onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-100 h-100 d-flex flex-column align-items-center justify-content-center text-center p-2\' style=\'background: linear-gradient(145deg, #f8fafc 0%, #e2e8f0 100%);\'><div class=\'rounded-circle bg-white shadow-xs p-2 mb-1 text-muted d-flex align-items-center justify-content-center\' style=\'width: 40px; height: 40px;\'><i class=\'fa-solid fa-book text-secondary fs-5 opacity-50\'></i></div><span class=\'badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-0.5 fw-bold mb-1\' style=\'font-size: 0.7rem;\'><i class=\'fa-regular fa-image me-1\'></i>কভার নেই</span><span class=\'small fw-bold text-dark text-truncate w-100\' style=\'font-size: 0.72rem;\'>{{ addslashes($book->title) }}</span></div>';">
            @else
                <!-- Stylish Placeholder when Book has No Cover -->
                <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-center p-2" 
                     style="background: linear-gradient(145deg, #f8fafc 0%, #e2e8f0 100%);">
                    <div class="rounded-circle bg-white shadow-xs p-2 mb-1.5 text-muted d-flex align-items-center justify-content-center" 
                         style="width: 42px; height: 42px;">
                        <i class="fa-solid fa-book-open text-primary fs-5 opacity-60"></i>
                    </div>
                    <span class="badge bg-secondary text-white rounded-pill px-2.5 py-1 fw-bold shadow-xs mb-1" style="font-size: 0.72rem; letter-spacing: 0.3px;">
                        <i class="fa-regular fa-image me-1"></i> কভার নেই
                    </span>
                    <span class="small fw-semibold text-dark text-truncate w-100 px-1" style="font-size: 0.73rem;">
                        {{ $book->title }}
                    </span>
                </div>
            @endif
        </a>

        <!-- Look Inside / Quick Preview Float Button on Hover -->
        <a href="{{ route('book.show', $book->slug) }}#look-inside" 
           class="position-absolute bottom-0 start-50 translate-middle-x mb-2 badge bg-dark bg-opacity-75 text-white text-decoration-none rounded-pill px-2.5 py-1 small shadow-sm d-none d-md-inline-flex align-items-center gap-1 opacity-0 hover-opacity-100 transition-all" 
           style="font-size: 0.7rem; z-index: 3;">
            <i class="fa-regular fa-eye"></i> একটু পড়ুন
        </a>
    </div>
    
    <!-- Book Information & Details -->
    <div class="d-flex flex-column flex-grow-1 px-1">
        
        <!-- Book Title -->
        <h6 class="fw-bold text-dark mb-1 line-clamp-2" style="font-size: 0.88rem; line-height: 1.35; min-height: 2.35em;" title="{{ $book->title }}">
            <a href="{{ route('book.show', $book->slug) }}" class="text-decoration-none text-dark hover-primary">
                {{ $book->title }}
            </a>
        </h6>
        
        <!-- Author Name (Links directly to Author Directory) -->
        <p class="text-muted small text-truncate mb-1" style="font-size: 0.76rem;" title="{{ $authorName }}">
            <i class="fa-solid fa-pen-nib text-secondary opacity-60 me-1" style="font-size: 0.68rem;"></i>
            @if($authorUrl)
                <a href="{{ $authorUrl }}" class="text-decoration-none text-muted hover-primary fw-semibold">
                    {{ $authorName }}
                </a>
            @else
                <span class="fw-semibold">{{ $authorName }}</span>
            @endif
        </p>
        
        <!-- Customer Rating Stars -->
        <div class="d-flex align-items-center justify-content-center gap-1 mb-2" style="font-size: 11px;">
            <div class="d-inline-flex gap-0 text-warning" style="line-height: 1;">
                @php $ratingVal = round($book->reviews_avg_rating ?? 5); @endphp
                @for($i = 1; $i <= 5; $i++)
                    @if($i <= $ratingVal)
                        <i class="fa-solid fa-star" style="font-size: 10px; width: 11px;"></i>
                    @else
                        <i class="fa-regular fa-star text-secondary opacity-40" style="font-size: 10px; width: 11px;"></i>
                    @endif
                @endfor
            </div>
            <span class="text-muted" style="font-size: 10px;">({{ $book->reviews_count ?? 0 }})</span>
        </div>

        <!-- Pricing Row -->
        <div class="mt-auto d-flex align-items-center justify-content-center gap-2 mb-2.5">
            @if($book->discount_price && $book->discount_price < $book->price)
                <span class="text-muted text-decoration-line-through small" style="font-size: 0.78rem;">
                    ৳{{ round($book->price) }}
                </span>
                <span class="fw-bold text-danger fs-6">
                    ৳{{ round($book->discount_price) }}
                </span>
            @else
                <span class="fw-bold text-dark fs-6">
                    ৳{{ round($book->price) }}
                </span>
            @endif
        </div>
        
        <!-- Action Buttons: "কার্টে যোগ" & "বাই নাউ" / "প্রি-অর্ডার" -->
        <div class="d-flex align-items-center gap-1.5 mt-auto pt-1">
            <!-- Add to Cart Button -->
            <button type="button" 
                    class="btn btn-outline-primary btn-sm rounded-pill fw-bold flex-fill py-1.5 px-2 d-inline-flex align-items-center justify-content-center gap-1 transition-all" 
                    style="font-size: 0.76rem;" 
                    title="কার্টে যোগ করুন"
                    onclick="addToCartLive(this, {{ $book->id }}, '{{ addslashes($book->title) }}', {{ $finalPrice }}, '{{ $coverUrl }}')">
                <i class="fa-solid fa-cart-plus" style="font-size: 0.72rem;"></i>
                <span>কার্টে যোগ</span>
            </button>

            <!-- Buy Now / Pre-Order Button -->
            @if($book->stock_status === 'pre_order')
                <button type="button" 
                        class="btn btn-warning text-dark btn-sm rounded-pill fw-bold flex-fill py-1.5 px-2 d-inline-flex align-items-center justify-content-center gap-1 shadow-xs transition-all border border-warning-subtle" 
                        style="font-size: 0.76rem;"
                        title="বইটি প্রি-অর্ডার করুন"
                        onclick="buyNowLive({{ $book->id }}, '{{ addslashes($book->title) }}', {{ $finalPrice }}, '{{ $coverUrl }}', '{{ $book->slug }}')">
                    <i class="fa-solid fa-clock-rotate-left" style="font-size: 0.7rem;"></i>
                    <span>প্রি-অর্ডার</span>
                </button>
            @else
                <button type="button" 
                        class="btn btn-primary btn-sm rounded-pill fw-bold flex-fill py-1.5 px-2 d-inline-flex align-items-center justify-content-center gap-1 shadow-xs transition-all" 
                        style="font-size: 0.76rem;"
                        title="সরাসরি কিনুন"
                        onclick="buyNowLive({{ $book->id }}, '{{ addslashes($book->title) }}', {{ $finalPrice }}, '{{ $coverUrl }}', '{{ $book->slug }}')">
                    <i class="fa-solid fa-bolt" style="font-size: 0.7rem;"></i>
                    <span>বাই নাউ</span>
                </button>
            @endif
        </div>

    </div>
</div>
