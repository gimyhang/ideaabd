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
    
    if (($book->cover_type ?? '') === 'hardcover') {
        $cardRegularPrice = (float)($book->hardcover_price ?: ($book->price ?: 0));
        $cardDiscPrice = ($book->hardcover_discount_price > 0 && $book->hardcover_discount_price < $cardRegularPrice) 
            ? (float)$book->hardcover_discount_price 
            : (($book->discount_price > 0 && $book->discount_price < $cardRegularPrice) ? (float)$book->discount_price : null);
    } else {
        $cardRegularPrice = (float)($book->price ?: ($book->hardcover_price ?: 0));
        $cardDiscPrice = ($book->discount_price > 0 && $book->discount_price < $cardRegularPrice) 
            ? (float)$book->discount_price 
            : (($book->hardcover_discount_price > 0 && $book->hardcover_discount_price < $cardRegularPrice) ? (float)$book->hardcover_discount_price : null);
    }

    $discountPercentage = ($cardRegularPrice > 0 && $cardDiscPrice && $cardDiscPrice < $cardRegularPrice)
        ? round((($cardRegularPrice - $cardDiscPrice) / $cardRegularPrice) * 100)
        : null;
    $isOutOfStock = isset($book->stock_quantity) && $book->stock_quantity <= 0;
    $finalPrice = ($cardDiscPrice && $cardDiscPrice < $cardRegularPrice) ? $cardDiscPrice : $cardRegularPrice;
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
    <div class="position-relative overflow-hidden rounded-3 mb-3 mx-auto w-100 group-hover shadow-xs" 
         style="aspect-ratio: 7 / 10; background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
        
        <a href="{{ route('book.show', $book->slug ?: $book->id) }}" class="d-block w-100 h-100 text-decoration-none">
            @if($coverUrl)
                <img src="{{ $coverUrl }}" 
                     alt="{{ $book->title }}" 
                     class="w-100 h-100 object-fit-cover transition-transform"
                     loading="lazy"
                     onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'140\' height=\'200\' viewBox=\'0 0 140 200\'><rect width=\'140\' height=\'200\' fill=\'%231e293b\'/><text x=\'50%25\' y=\'50%25\' dominant-baseline=\'middle\' text-anchor=\'middle\' fill=\'%2338bdf8\' font-size=\'32\' font-weight=\'bold\' font-family=\'sans-serif\'>{{ mb_substr($book->title ?? 'বই', 0, 1, 'UTF-8') }}</text></svg>';">
            @else
                <!-- Elegant Book Spine & Title Fallback Mockup -->
                <div class="w-100 h-100 d-flex flex-column justify-content-between p-2.5 text-start position-relative overflow-hidden" 
                     style="background: linear-gradient(145deg, #1e293b 0%, #0f172a 100%); border-left: 4px solid #38bdf8;">
                    
                    <div class="d-flex justify-content-between align-items-start z-1">
                        <span class="badge bg-primary bg-opacity-25 text-info border border-info border-opacity-25 px-1.5 py-0.5 rounded-pill" style="font-size: 0.62rem;">
                            {{ $book->category->name ?? 'আইডিয়া' }}
                        </span>
                        <i class="fa-solid fa-bookmark text-warning opacity-75" style="font-size: 0.75rem;"></i>
                    </div>

                    <div class="my-auto z-1 py-1">
                        <h6 class="fw-bold text-white mb-1 text-truncate-2" style="font-size: 0.82rem; line-height: 1.35; font-family: 'Hind Siliguri', serif; color: #f8fafc !important;">
                            {{ $book->title }}
                        </h6>
                        <p class="text-white-50 small mb-0 text-truncate" style="font-size: 0.72rem;">
                            {{ $authorName }}
                        </p>
                    </div>

                    <div class="d-flex justify-content-between align-items-center z-1 pt-1.5 border-top border-secondary border-opacity-25">
                        <span class="text-white-50 small" style="font-size: 0.62rem;">আইডিয়া প্রকাশন</span>
                        <i class="fa-solid fa-feather-pointed text-info opacity-75" style="font-size: 0.65rem;"></i>
                    </div>
                </div>
            @endif
        </a>

        <!-- Look Inside / Quick Preview Float Button on Hover -->
        <a href="{{ route('book.show', $book->slug ?: $book->id) }}#look-inside" 
           class="position-absolute bottom-0 start-50 translate-middle-x mb-2 badge bg-dark bg-opacity-75 text-white text-decoration-none rounded-pill px-2.5 py-1 small shadow-sm d-none d-md-inline-flex align-items-center gap-1 opacity-0 hover-opacity-100 transition-all" 
           style="font-size: 0.7rem; z-index: 3;">
            <i class="fa-regular fa-eye"></i> একটু পড়ুন
        </a>
    </div>
    
    <!-- Book Information & Details -->
    <div class="d-flex flex-column flex-grow-1 px-1 pt-1">
        
        <!-- Book Title (Dynamic 2-line clamp with responsive typography) -->
        <h6 class="fw-bold text-dark mb-1 d-flex align-items-center justify-content-center" 
            style="font-size: clamp(0.84rem, 1.5vw, 0.93rem); line-height: 1.35; min-height: 2.7rem; max-height: 2.7rem;" 
            title="{{ $book->title }}">
            <a href="{{ route('book.show', $book->slug ?: $book->id) }}" 
               class="text-decoration-none text-dark hover-primary"
               style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; word-break: break-word;">
                {{ $book->title }}
            </a>
        </h6>
        
        <!-- Author Name (Links directly to Author Directory) -->
        <p class="text-muted small text-truncate mb-2" style="font-size: 0.76rem;" title="{{ $authorName }}">
            <i class="fa-solid fa-pen-nib text-secondary opacity-60 me-1" style="font-size: 0.68rem;"></i>
            @if($authorUrl)
                <a href="{{ $authorUrl }}" class="text-decoration-none text-muted hover-primary fw-semibold">
                    {{ $authorName }}
                </a>
            @else
                <span class="fw-semibold">{{ $authorName }}</span>
            @endif
        </p>

        <!-- Pricing Row (Regular & Offer Price) -->
        <div class="d-flex align-items-center justify-content-center gap-2 mb-1.5 flex-wrap">
            @if($cardDiscPrice && $cardDiscPrice < $cardRegularPrice)
                <span class="text-muted text-decoration-line-through small" style="font-size: 0.78rem;">
                    ৳@bn(round($cardRegularPrice))
                </span>
                <span class="fw-bold text-danger" style="font-size: 1.05rem;">
                    ৳@bn(round($cardDiscPrice))
                </span>
            @else
                <span class="fw-bold text-dark" style="font-size: 1.05rem;">
                    ৳@bn(round($cardRegularPrice))
                </span>
            @endif
        </div>

        <!-- 5 Dynamic Customer Rating Stars -->
        @php
            $avgRating = isset($book->reviews_avg_rating) ? (float)$book->reviews_avg_rating : null;
            if ($avgRating === null && method_exists($book, 'reviews') && $book->relationLoaded('reviews')) {
                $avgRating = $book->reviews->avg('rating');
            }
            // Dynamic star rating (1.0 to 5.0). Defaults gracefully to 5.0 if new/unrated
            $ratingScore = ($avgRating !== null && $avgRating > 0) ? round($avgRating, 1) : 5.0;
            $reviewsCount = $book->reviews_count ?? ($book->relationLoaded('reviews') ? $book->reviews->count() : 0);
        @endphp
        <div class="d-flex align-items-center justify-content-center gap-1 mb-2.5" style="font-size: 11px;" title="রেটিং: {{ $ratingScore }} / ৫">
            <div class="d-inline-flex gap-0.5 text-warning" style="line-height: 1;">
                @for($s = 1; $s <= 5; $s++)
                    @if($ratingScore >= $s)
                        <i class="fa-solid fa-star" style="font-size: 11px;"></i>
                    @elseif($ratingScore >= ($s - 0.5))
                        <i class="fa-solid fa-star-half-stroke" style="font-size: 11px;"></i>
                    @else
                        <i class="fa-regular fa-star text-secondary opacity-35" style="font-size: 11px;"></i>
                    @endif
                @endfor
            </div>
            <span class="text-muted fw-semibold" style="font-size: 10.5px;">
                @if($reviewsCount > 0)
                    (@bn($reviewsCount))
                @else
                    (@bn(number_format($ratingScore, 1)))
                @endif
            </span>
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
