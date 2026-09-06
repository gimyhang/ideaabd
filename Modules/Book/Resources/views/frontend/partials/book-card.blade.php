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

    $hasHardcover = (isset($book->has_hardcover) && $book->has_hardcover)
        || in_array($book->cover_type ?? '', ['hardcover', 'both'], true)
        || (!empty($book->hardcover_price) && (float)$book->hardcover_price > 0)
        || (($book->format ?? '') === 'hardcover');
    $isEbook = (($book->format ?? '') === 'ebook' && !$hasHardcover && ($book->cover_type ?? '') !== 'paperback');

    $avgRating = isset($book->reviews_avg_rating) ? (float)$book->reviews_avg_rating : null;
    if ($avgRating === null && method_exists($book, 'reviews') && $book->relationLoaded('reviews')) {
        $avgRating = $book->reviews->avg('rating');
    }
    $ratingScore = ($avgRating !== null && $avgRating > 0) ? round($avgRating, 1) : 4.8;
    $reviewsCount = $book->reviews_count ?? ($book->relationLoaded('reviews') ? $book->reviews->count() : 0);
@endphp

<div class="card h-100 w-100 border-0 shadow-none rounded-3 p-1.5 p-sm-2 d-flex flex-column text-center position-relative bg-white amz-bookshelf-item hover-lift" 
     style="transition: transform 0.2s ease, box-shadow 0.2s ease; min-width: 0; overflow: hidden;">
    
    <!-- 1. Book Cover Image (Clickable Link to Detail Page - Fluid Aspect Ratio & Responsive Height) -->
    <div class="position-relative overflow-hidden rounded-2 mb-1.5 w-100 mx-auto book-cover-frame shadow-xs" 
         style="aspect-ratio: 1 / 1.48; width: 100%; height: auto; max-height: 350px; background: #0f172a;">
        
        <a href="{{ route('book.show', $book->slug ?: $book->id) }}" class="d-block w-100 h-100 text-decoration-none">
            @if($coverUrl)
                <img src="{{ $coverUrl }}" 
                     alt="{{ $book->title }}" 
                     class="w-100 h-100 object-fit-cover d-block"
                     loading="lazy"
                     onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'140\' height=\'200\' viewBox=\'0 0 140 200\'><rect width=\'140\' height=\'200\' fill=\'%231e293b\'/><text x=\'50%25\' y=\'50%25\' dominant-baseline=\'middle\' text-anchor=\'middle\' fill=\'%2338bdf8\' font-size=\'28\' font-weight=\'bold\' font-family=\'sans-serif\'>{{ mb_substr($book->title ?? 'বই', 0, 1, 'UTF-8') }}</text></svg>';">
            @else
                <div class="w-100 h-100 d-flex flex-column justify-content-between p-2 text-center position-relative" 
                     style="background: linear-gradient(145deg, #1e293b 0%, #0f172a 100%); border-top: 3px solid #38bdf8;">
                    <span class="badge bg-primary bg-opacity-25 text-info px-1.5 py-0.5 rounded-pill mx-auto" style="font-size: 0.60rem;">
                        {{ $book->category->name ?? 'আইডিয়া' }}
                    </span>
                    <div class="my-auto py-1">
                        <h6 class="fw-bold text-white text-truncate" style="font-size: 0.78rem; line-height: 1.3; font-family: 'Hind Siliguri', serif; margin: 0; padding: 0;">
                            {{ $book->title }}
                        </h6>
                        <p class="text-white-50 small mb-0 text-truncate" style="font-size: 0.68rem; margin: 0; padding: 0;">
                            {{ $authorName }}
                        </p>
                    </div>
                    <span class="text-white-50 small" style="font-size: 0.60rem;">আইডিয়া প্রকাশন</span>
                </div>
            @endif
        </a>

        @if($discountPercentage)
            <span class="position-absolute top-0 start-0 m-1 m-sm-1.5 badge bg-danger rounded-pill shadow-xs fw-bold px-1.5 py-0.5" style="font-size: 0.62rem;">
                -{{ $discountPercentage }}%
            </span>
        @endif

        @if($book->stock_status === 'pre_order')
            <span class="position-absolute top-0 end-0 m-1 m-sm-1.5 badge bg-warning text-dark rounded-pill shadow-xs fw-bold px-1.5 py-0.5" style="font-size: 0.62rem;">
                <i class="fa-solid fa-clock-rotate-left me-0.5"></i> প্রি-অর্ডার
            </span>
        @endif
    </div>
    
    <!-- 2. Book Info (Centered, Reviews, Title, Author, Price - ZERO GAP MARGINS) -->
    <div class="d-flex flex-column flex-grow-1 justify-content-start align-items-center text-center w-100 min-w-0" style="margin: 0; padding: 0; gap: 0 !important;">
        
        <!-- A. Customer Rating Stars & Reader Review Count (Centered) -->
        <div class="d-flex align-items-center justify-content-center gap-1 w-100" style="font-size: 11px; line-height: 1.1; margin: 0; padding: 0; margin-bottom: 2px !important;">
            <div class="d-inline-flex gap-0.5 text-warning">
                @for($s = 1; $s <= 5; $s++)
                    @if($ratingScore >= $s)
                        <i class="fa-solid fa-star" style="font-size: 9px;"></i>
                    @elseif($ratingScore >= ($s - 0.5))
                        <i class="fa-solid fa-star-half-stroke" style="font-size: 9px;"></i>
                    @else
                        <i class="fa-regular fa-star text-secondary opacity-35" style="font-size: 9px;"></i>
                    @endif
                @endfor
            </div>
            <a href="{{ route('book.show', $book->slug ?: $book->id) }}#tab-reviews" class="text-secondary text-decoration-none hover-underline" style="font-size: 10px; line-height: 1; margin: 0; padding: 0;">
                @if($reviewsCount > 0)
                    @bn($reviewsCount)
                @else
                    @bn(number_format($ratingScore, 1))
                @endif
            </a>
        </div>

        @if(!isset($hideTitleAuthor) || !$hideTitleAuthor)
            <!-- B. Book Title (Centered, Single line with ellipsis, zero margin/padding) -->
            <h6 class="fw-bold text-truncate w-100" style="font-size: clamp(0.78rem, 2.8vw, 0.86rem); line-height: 1.25; margin: 0 !important; padding: 0 !important;">
                <a href="{{ route('book.show', $book->slug ?: $book->id) }}" class="text-dark text-decoration-none hover-primary d-block text-truncate" title="{{ $book->title }}">
                    {{ $book->title }}
                </a>
            </h6>
            
            <!-- C. Author Name (Centered, Single line with ellipsis, tight zero margin) -->
            <div class="text-truncate w-100" style="font-size: clamp(0.68rem, 2.2vw, 0.73rem); line-height: 1.15; margin: 0 !important; padding: 0 !important;">
                @if($authorUrl)
                    <a href="{{ $authorUrl }}" class="text-secondary text-decoration-none hover-primary d-block text-truncate">
                        {{ $authorName }}
                    </a>
                @else
                    <span class="text-secondary d-block text-truncate">{{ $authorName }}</span>
                @endif
            </div>
        @endif

        <!-- D. Price Row (Centered, Zero Margin) -->
        <div class="d-flex align-items-baseline justify-content-center gap-1 w-100 mt-0.5" style="line-height: 1.1; margin: 0 !important; padding: 0 !important;">
            @if($cardDiscPrice && $cardDiscPrice < $cardRegularPrice)
                <span class="fw-bold text-dark" style="font-size: clamp(0.88rem, 3.2vw, 1rem); line-height: 1.1;">
                    ৳@bn(round($cardDiscPrice))
                </span>
                <span class="text-muted text-decoration-line-through small" style="font-size: clamp(0.70rem, 2.4vw, 0.75rem); line-height: 1.1;">
                    ৳@bn(round($cardRegularPrice))
                </span>
            @else
                <span class="fw-bold text-dark" style="font-size: clamp(0.88rem, 3.2vw, 1rem); line-height: 1.1;">
                    ৳@bn(round($cardRegularPrice))
                </span>
            @endif
        </div>

    </div>
</div>
