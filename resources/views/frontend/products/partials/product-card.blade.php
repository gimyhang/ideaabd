@php
    $finalPrice = (float) $product->final_price;
    $regularPrice = (float) $product->price;
    $discPrice = (float) ($product->discount_price ?? 0);
    $hasDiscount = $discPrice > 0 && $discPrice < $regularPrice;
    $discountPercent = $product->discount_percent;
    $rating = max(0, min(5, (float) ($product->rating ?? 5)));
    $reviewsCount = (int) ($product->reviews_count ?? 0);
    $imgUrl = $product->image_url;
    $productUrl = route('products.show', ['type' => $product->type, 'slug' => $product->slug]);
    $isInStock = (int) $product->stock > 0 && $product->stock_status !== 'out_of_stock';
@endphp

{{-- Marketplace-style card: image · name · offer price + small old price · ★★★★★ --}}
<article class="dz-card {{ $isInStock ? '' : 'is-soldout' }}">
    <a href="{{ $productUrl }}" class="dz-card__media" aria-label="{{ $product->title }}">
        <img src="{{ $imgUrl }}" alt="{{ $product->title }}" width="400" height="400" loading="lazy" decoding="async"
             onerror="this.onerror=null;this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'300\' height=\'300\'><rect width=\'300\' height=\'300\' fill=\'%23f1f5f9\'/></svg>';">
        @if(!$isInStock)<span class="dz-card__sold">স্টক শেষ</span>@endif
    </a>

    <div class="dz-card__body">
        <a href="{{ $productUrl }}" class="dz-card__name" title="{{ $product->title }}">{{ $product->title }}</a>

        <div class="dz-card__price">
            <span class="dz-card__now">৳@bn(round($finalPrice))</span>
            @if($hasDiscount)
                <s class="dz-card__old">৳@bn(round($regularPrice))</s>
                <span class="dz-card__off">-@bn($discountPercent)%</span>
            @endif
        </div>

        <div class="dz-card__stars" role="img" aria-label="{{ number_format($rating, 1) }} / 5">
            @for($i = 1; $i <= 5; $i++)
                <i class="fa-solid fa-star {{ $i <= round($rating) ? 'on' : '' }}"></i>
            @endfor
            @if($reviewsCount > 0)<span>(@bn($reviewsCount))</span>@endif
        </div>
    </div>

    <div class="dz-card__tools">
        <button type="button" class="dz-tool" title="দ্রুত দেখুন" aria-label="দ্রুত দেখুন"
                onclick="event.stopPropagation(); window.openProductQuickView({{ $product->id }});"><i class="fa-regular fa-eye"></i></button>
        @if($isInStock)
            <button type="button" class="dz-tool dz-tool--cart btn-add-cart-card" title="কার্টে নিন" aria-label="কার্টে নিন"
                    data-product-id="{{ $product->id }}"
                    data-product-title="{{ $product->title }}"
                    data-product-price="{{ $finalPrice }}"
                    data-product-image="{{ $imgUrl }}"
                    data-product-type="{{ $product->type }}"
                    onclick="event.stopPropagation(); window.handleCardAddToCart(this);"><i class="fa-solid fa-cart-plus"></i></button>
        @endif
    </div>
</article>
