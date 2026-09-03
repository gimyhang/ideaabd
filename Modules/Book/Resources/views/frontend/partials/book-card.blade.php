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

<div class="card h-100 w-100 border-0 shadow-none rounded-3 p-2 d-flex flex-column text-start position-relative bg-white amz-bookshelf-item hover-lift" 
     style="transition: transform 0.2s ease, box-shadow 0.2s ease;">
    
    <!-- 1. Book Cover Image (Clickable Link to Detail Page) -->
    <div class="position-relative overflow-hidden rounded-2 mb-2 w-100 mx-auto book-cover-frame shadow-xs" 
         style="aspect-ratio: 7 / 10; width: 100%; max-height: 260px; background: #0f172a;">
        
        <a href="{{ route('book.show', $book->slug ?: $book->id) }}" class="d-block w-100 h-100 text-decoration-none">
            @if($coverUrl)
                <img src="{{ $coverUrl }}" 
                     alt="{{ $book->title }}" 
                     class="w-100 h-100 object-fit-cover"
                     loading="lazy"
                     onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'140\' height=\'200\' viewBox=\'0 0 140 200\'><rect width=\'140\' height=\'200\' fill=\'%231e293b\'/><text x=\'50%25\' y=\'50%25\' dominant-baseline=\'middle\' text-anchor=\'middle\' fill=\'%2338bdf8\' font-size=\'28\' font-weight=\'bold\' font-family=\'sans-serif\'>{{ mb_substr($book->title ?? 'বই', 0, 1, 'UTF-8') }}</text></svg>';">
            @else
                <div class="w-100 h-100 d-flex flex-column justify-content-between p-2.5 text-start position-relative" 
                     style="background: linear-gradient(145deg, #1e293b 0%, #0f172a 100%); border-left: 3px solid #38bdf8;">
                    <span class="badge bg-primary bg-opacity-25 text-info px-1.5 py-0.5 rounded-pill" style="font-size: 0.60rem;">
                        {{ $book->category->name ?? 'আইডিয়া' }}
                    </span>
                    <div class="my-auto py-1">
                        <h6 class="fw-bold text-white mb-1 line-clamp-2" style="font-size: 0.78rem; line-height: 1.35; font-family: 'Hind Siliguri', serif;">
                            {{ $book->title }}
                        </h6>
                        <p class="text-white-50 small mb-0 text-truncate" style="font-size: 0.68rem;">
                            {{ $authorName }}
                        </p>
                    </div>
                    <span class="text-white-50 small" style="font-size: 0.60rem;">আইডিয়া প্রকাশন</span>
                </div>
            @endif
        </a>

        @if($discountPercentage)
            <span class="position-absolute top-0 start-0 m-1.5 badge bg-danger rounded-pill shadow-xs fw-bold px-1.5 py-0.5" style="font-size: 0.65rem;">
                -{{ $discountPercentage }}%
            </span>
        @endif
    </div>
    
    <!-- 2. Book Info (Reviews, Title, Author, Format, Price) -->
    <div class="d-flex flex-column flex-grow-1 justify-content-start text-start">
        
        <!-- A. Customer Rating Stars & Reader Review Count (Amazon Format) -->
        <div class="d-flex align-items-center gap-1 mb-0.5" style="font-size: 11px; line-height: 1.1;">
            <div class="d-inline-flex gap-0.5 text-warning">
                @for($s = 1; $s <= 5; $s++)
                    @if($ratingScore >= $s)
                        <i class="fa-solid fa-star" style="font-size: 9.5px;"></i>
                    @elseif($ratingScore >= ($s - 0.5))
                        <i class="fa-solid fa-star-half-stroke" style="font-size: 9.5px;"></i>
                    @else
                        <i class="fa-regular fa-star text-secondary opacity-35" style="font-size: 9.5px;"></i>
                    @endif
                @endfor
            </div>
            <a href="{{ route('book.show', $book->slug ?: $book->id) }}#tab-reviews" class="text-secondary text-decoration-none hover-underline" style="font-size: 10.5px;">
                @if($reviewsCount > 0)
                    @bn($reviewsCount)
                @else
                    @bn(number_format($ratingScore, 1))
                @endif
            </a>
        </div>

        @if(!isset($hideTitleAuthor) || !$hideTitleAuthor)
            <!-- B. Book Title (Tight line spacing, No prefix) -->
            <h6 class="fw-bold mb-0.5" style="font-size: 0.86rem; line-height: 1.25; min-height: 2.15rem; max-height: 2.15rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                <a href="{{ route('book.show', $book->slug ?: $book->id) }}" class="text-dark text-decoration-none hover-primary">
                    {{ $book->title }}
                </a>
            </h6>
            
            <!-- C. Author Name (Direct clean text, No prefix) -->
            <div class="mb-1 text-truncate" style="font-size: 0.73rem; line-height: 1.2;">
                @if($authorUrl)
                    <a href="{{ $authorUrl }}" class="text-secondary text-decoration-none hover-primary">
                        {{ $authorName }}
                    </a>
                @else
                    <span class="text-secondary">{{ $authorName }}</span>
                @endif
            </div>
        @endif

        <!-- D. Format & Price (Directly under stars when title/author hidden) -->
        <div class="{{ (!isset($hideTitleAuthor) || !$hideTitleAuthor) ? 'mt-auto' : '' }} pt-0.5">
            <div class="text-muted small mb-0.5" style="font-size: 0.68rem; line-height: 1.1;">
                @if($book->stock_status === 'pre_order')
                    <span class="text-warning-emphasis fw-bold">প্রি-অর্ডার</span>
                @elseif($isEbook)
                    <span>ই-বুক</span>
                @elseif($hasHardcover)
                    <span>হার্ডকভার</span>
                @else
                    <span>পেপারব্যাক</span>
                @endif
            </div>

            <div class="d-flex align-items-baseline gap-1" style="line-height: 1.1;">
                @if($cardDiscPrice && $cardDiscPrice < $cardRegularPrice)
                    <span class="fw-bold text-dark" style="font-size: 1rem;">
                        ৳@bn(round($cardDiscPrice))
                    </span>
                    <span class="text-muted text-decoration-line-through small" style="font-size: 0.75rem;">
                        ৳@bn(round($cardRegularPrice))
                    </span>
                @else
                    <span class="fw-bold text-dark" style="font-size: 1rem;">
                        ৳@bn(round($cardRegularPrice))
                    </span>
                @endif
            </div>
        </div>

    </div>
</div>
