@php
    $finalPrice = (float)$product->final_price;
    $regularPrice = (float)$product->price;
    $discPrice = (float)($product->discount_price ?? 0);
    $hasDiscount = $discPrice > 0 && $discPrice < $regularPrice;
    $discountPercent = $product->discount_percent;
    $rating = (float)($product->rating ?? 4.8);
    $reviewsCount = (int)($product->reviews_count ?? 12);
    $imgUrl = $product->image_url;
    $productUrl = route('products.show', ['type' => $product->type, 'slug' => $product->slug]);
    $isInStock = (int)$product->stock > 0 && $product->stock_status !== 'out_of_stock';
@endphp

<div class="card h-100 w-100 border-0 shadow-sm rounded-4 d-flex flex-column position-relative bg-white idea-product-card transition-all hover-lift"
     style="padding: 10px; border: 1px solid #eef2f6 !important;">
    
    <!-- Image Box with Clean Discount Badge (Daraz Style) -->
    <div class="position-relative overflow-hidden rounded-3 mb-2 w-100 product-img-box d-flex align-items-center justify-content-center"
         style="aspect-ratio: 1 / 1; width: 100%; height: auto; background: #fafafa; border: 1px solid #f1f5f9; padding: 8px;">
        
        <a href="{{ $productUrl }}" class="d-flex align-items-center justify-content-center w-100 h-100 text-decoration-none">
            <img src="{{ $imgUrl }}" 
                 alt="{{ $product->title }}" 
                 class="w-100 h-100 object-fit-contain d-block product-thumb transition-all"
                 loading="lazy"
                 onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'300\' height=\'300\' viewBox=\'0 0 300 300\'><rect width=\'300\' height=\'300\' fill=\'%23f8fafc\'/><text x=\'50%25\' y=\'52%25\' dominant-baseline=\'middle\' text-anchor=\'middle\' fill=\'%230284c7\' font-size=\'18\' font-weight=\'bold\' font-family=\'sans-serif\'>Idea Shop</text></svg>';">
        </a>

        <!-- Discount or Stock Badge -->
        @if(!$isInStock)
            <span class="position-absolute top-0 start-0 m-1.5 badge bg-dark bg-opacity-75 text-white rounded-pill fw-bold px-2 py-0.5" style="font-size: 0.65rem;">
                স্টক শেষ
            </span>
        @elseif($hasDiscount)
            <span class="position-absolute top-0 start-0 m-1.5 badge bg-danger rounded-pill fw-bold px-2 py-0.5 shadow-xs" style="font-size: 0.68rem;">
                -{{ $discountPercent }}%
            </span>
        @endif

        <!-- Quick View Overlay Button -->
        <button type="button" 
                class="btn btn-light btn-sm rounded-circle position-absolute bottom-0 end-0 m-1.5 shadow-sm d-inline-flex align-items-center justify-content-center p-0 product-quickview-btn"
                style="width: 30px; height: 30px; z-index: 5; opacity: 0.9;"
                title="দ্রুত দেখুন"
                onclick="event.stopPropagation(); window.openProductQuickView({{ $product->id }});">
            <i class="fa-regular fa-eye text-dark" style="font-size: 11px;"></i>
        </button>
    </div>
    
    <!-- Info & Actions Body (Daraz Minimal Style) -->
    <div class="d-flex flex-column flex-grow-1 justify-content-between w-100 min-w-0 product-card-body px-1">
        
        <div>
            <!-- Product Title (Clean 2-line display, No Extra Words) -->
            <h6 class="fw-semibold w-100 mb-1.5 text-start" style="font-size: 13px; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 36px;" title="{{ $product->title }}">
                <a href="{{ $productUrl }}" class="text-dark text-decoration-none hover-primary d-block">
                    {{ $product->title }}
                </a>
            </h6>

            <!-- Price Row (Clean Daraz Display) -->
            <div class="d-flex align-items-baseline gap-1.5 w-100 mb-1 product-price-row text-start">
                <span class="fw-black text-primary" style="font-size: 1.15rem; line-height: 1;">
                    ৳@bn(round($finalPrice))
                </span>
                @if($hasDiscount)
                    <span class="text-muted text-decoration-line-through small" style="font-size: 0.78rem; line-height: 1;">
                        ৳@bn(round($regularPrice))
                    </span>
                @endif
            </div>

            <!-- Rating & Reviews (Daraz Style: ★ 4.9 (12)) -->
            <div class="d-flex align-items-center gap-1 w-100 mb-2.5 text-start" style="font-size: 11px;">
                <span class="text-warning"><i class="fa-solid fa-star" style="font-size: 10px;"></i></span>
                <span class="fw-bold text-dark">{{ number_format($rating, 1) }}</span>
                <span class="text-muted">(@bn($reviewsCount))</span>
            </div>
        </div>

        <!-- Add to Cart Action Button -->
        <div class="mt-auto w-100 product-card-action">
            @if($isInStock)
                <button type="button" 
                        class="btn btn-sm btn-outline-primary rounded-pill w-100 fw-bold d-inline-flex align-items-center justify-content-center gap-1.5 py-1.5 shadow-2xs hover-lift btn-add-cart-card"
                        style="font-size: 12px;"
                        data-product-id="{{ $product->id }}"
                        data-product-title="{{ $product->title }}"
                        data-product-price="{{ $finalPrice }}"
                        data-product-image="{{ $imgUrl }}"
                        data-product-type="{{ $product->type }}"
                        onclick="event.stopPropagation(); window.handleCardAddToCart(this);">
                    <i class="fa-solid fa-cart-shopping" style="font-size: 11px;"></i>
                    <span>কার্টে নিন</span>
                </button>
            @else
                <button type="button" 
                        class="btn btn-sm btn-light border text-muted rounded-pill w-100 fw-bold d-inline-flex align-items-center justify-content-center gap-1.5 py-1.5"
                        style="font-size: 12px;" disabled>
                    <i class="fa-solid fa-ban text-danger" style="font-size: 10px;"></i>
                    <span>স্টক শেষ</span>
                </button>
            @endif
        </div>

    </div>
</div>
