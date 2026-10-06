@extends('layouts.app')

@section('title', $product->title . ' | আইডিয়া প্রকাশন')

@section('content')
<div class="product-detail-wrapper bg-light min-vh-100 py-3 py-md-4">
    <div class="container">

        <!-- ═══ BREADCRUMB ═══ -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">হোম</a></li>
                <li class="breadcrumb-item">
                    <a href="{{ route($product->type === 'electronics' ? 'products.electronics' : 'products.stationery') }}" class="text-decoration-none text-muted">
                        {{ $product->type === 'electronics' ? 'ইলেক্ট্রনিক্স' : 'স্টেশনারি' }}
                    </a>
                </li>
                @if($product->category)
                    <li class="breadcrumb-item">
                        <a href="{{ route($product->type === 'electronics' ? 'products.electronics' : 'products.stationery', ['category' => $product->category->slug]) }}" class="text-decoration-none text-muted">
                            {{ $product->category->name }}
                        </a>
                    </li>
                @endif
                <li class="breadcrumb-item active text-primary fw-semibold text-truncate" aria-current="page" style="max-width: 250px;">
                    {{ $product->title }}
                </li>
            </ol>
        </nav>

        <!-- ═══ MAIN PRODUCT CONTAINER ═══ -->
        <div class="bg-white rounded-4 border shadow-sm p-3 p-md-4 mb-4">
            <div class="row g-4 g-lg-5">
                
                <!-- Left: Product Image Gallery -->
                <div class="col-lg-5">
                    <div class="product-gallery-sticky sticky-top" style="top: 80px;">
                        <div class="position-relative overflow-hidden rounded-4 bg-light border p-2 mb-3 text-center" 
                             style="aspect-ratio: 1 / 1.1; max-height: 440px;">
                            <img id="mainProductImage" 
                                 src="{{ $product->image_url }}" 
                                 alt="{{ $product->title }}" 
                                 class="w-100 h-100 object-fit-contain d-block rounded-3 transition-all"
                                 onerror="this.onerror=null; this.src='https://placehold.co/500x500/f8fafc/0284c7?text={{ urlencode(mb_substr($product->title, 0, 10)) }}';">
                            
                            @if($product->discount_price && $product->discount_price < $product->price)
                                <span class="position-absolute top-0 start-0 m-2.5 badge bg-danger text-white rounded-pill shadow-xs fw-bold px-2.5 py-1" style="font-size: 13px; z-index: 3;">
                                    -{{ $product->discount_percent }}% ছাড়
                                </span>
                            @endif

                            @if(!empty($product->badge))
                                <span class="position-absolute top-0 end-0 m-2.5 badge bg-primary text-white rounded-pill shadow-xs fw-bold px-2.5 py-1">
                                    {{ $product->badge }}
                                </span>
                            @endif
                        </div>

                        <!-- Gallery Thumbnails if any -->
                        @php
                            $galleryList = $product->gallery_list;
                            if (empty($galleryList) && !empty($product->cover_image)) {
                                $galleryList = [$product->image_url];
                            }
                        @endphp
                        @if(count($galleryList) > 1)
                            <div class="d-flex align-items-center gap-2 overflow-x-auto no-scrollbar py-1">
                                @foreach($galleryList as $gImg)
                                    <button type="button" class="btn p-1 border rounded-3 bg-light flex-shrink-0" 
                                            style="width: 64px; height: 64px;" 
                                            onclick="document.getElementById('mainProductImage').src='{{ $gImg }}'">
                                        <img src="{{ $gImg }}" alt="Thumbnail" class="w-100 h-100 object-fit-cover rounded-2">
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Right: Product Info & Purchase Actions -->
                <div class="col-lg-7">
                    <div class="d-flex flex-column h-100">
                        
                        <!-- Category & Brand Badge -->
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1 fw-bold" style="font-size: 11px;">
                                <i class="{{ $product->type === 'electronics' ? 'fas fa-blender' : 'fas fa-pen-nib' }} me-1"></i>
                                {{ $product->category?->name ?? ($product->type === 'electronics' ? 'হোম অ্যাপ্লায়েন্স' : 'স্টেশনারি') }}
                            </span>
                            @if(!empty($product->brand))
                                <span class="badge bg-secondary bg-opacity-10 text-dark rounded-pill px-2.5 py-1 small fw-semibold">
                                    ব্র্যান্ড: {{ $product->brand }}
                                </span>
                            @endif
                            @if($product->stock > 0)
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2.5 py-1 small fw-semibold">
                                    <i class="fa-solid fa-check-circle me-1"></i>স্টকে আছে ({{ $product->stock }} টি)
                                </span>
                            @else
                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2.5 py-1 small fw-semibold">
                                    <i class="fa-solid fa-circle-xmark me-1"></i>স্টক আউট
                                </span>
                            @endif
                        </div>

                        <!-- 1. প্রোডাক্ট নাম -->
                        <h1 class="fw-bold text-dark mb-2.5 fs-3" style="line-height: 1.35;">
                            {{ $product->title }}
                        </h1>

                        <!-- 2. মূল্য (ছাড় ব্যাজ প্রোডাক্টের মাথায় স্থানান্তরিত) -->
                        <div class="d-flex flex-wrap align-items-center gap-3 mb-2.5 p-3 bg-light rounded-4 border">
                            <div class="d-flex align-items-baseline gap-2">
                                <span class="fs-2 fw-black text-primary" style="line-height: 1;">
                                    ৳@bn(round($product->final_price))
                                </span>
                                @if($product->discount_price && $product->discount_price < $product->price)
                                    <span class="fs-5 text-muted text-decoration-line-through">
                                        ৳@bn(round($product->price))
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- 3. ***** (শুধু ৫টি গোল্ডেন স্টার - 4.8 ও (৪৯) বাদ) -->
                        <div class="d-flex flex-wrap align-items-center gap-3 pb-3 mb-3 border-bottom text-muted small">
                            <div class="d-inline-flex align-items-center text-warning" style="letter-spacing: 2px; font-size: 14px;">
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                            </div>
                            @if(!empty($product->sku))
                                <span>SKU: <strong class="text-dark font-monospace">{{ $product->sku }}</strong></span>
                            @endif
                            @if(!empty($product->warranty))
                                <span class="text-success fw-semibold"><i class="fa-solid fa-shield-halved me-1"></i>{{ $product->warranty }}</span>
                            @endif
                        </div>

                        <!-- Summary -->
                        @if(!empty($product->summary))
                            <p class="text-muted small mb-4" style="line-height: 1.6; font-size: 14px;">
                                {{ $product->summary }}
                            </p>
                        @endif

                        <!-- Quantity & Purchase Buttons -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark small text-uppercase tracking-wider mb-2">পরিমাণ</label>
                            <div class="d-flex flex-wrap align-items-center gap-3">
                                <div class="input-group rounded-pill border bg-light p-1 shadow-2xs" style="width: 140px;">
                                    <button class="btn btn-sm btn-white rounded-circle border shadow-2xs" type="button" 
                                            onclick="let q=document.getElementById('itemQty'); if(parseInt(q.value)>1) q.value=parseInt(q.value)-1;">
                                        <i class="fa-solid fa-minus" style="font-size: 10px;"></i>
                                    </button>
                                    <input type="text" id="itemQty" value="1" class="form-control form-control-sm text-center border-0 bg-transparent fw-bold" readonly>
                                    <button class="btn btn-sm btn-white rounded-circle border shadow-2xs" type="button" 
                                            onclick="let q=document.getElementById('itemQty'); q.value=parseInt(q.value)+1;">
                                        <i class="fa-solid fa-plus" style="font-size: 10px;"></i>
                                    </button>
                                </div>

                                <button type="button" 
                                        class="btn btn-primary rounded-pill px-4 py-2.5 fw-bold shadow-xs d-inline-flex align-items-center gap-2 hover-lift"
                                        onclick="window.addToCartLive(this, {{ $product->id }}, '{{ addslashes($product->title) }}', {{ $product->final_price }}, '{{ $product->image_url }}', parseInt(document.getElementById('itemQty').value), '{{ $product->type }}');">
                                    <i class="fa-solid fa-cart-shopping"></i>
                                    <span>কার্টে যোগ করুন</span>
                                </button>

                                <button type="button" 
                                        class="btn btn-outline-dark rounded-pill px-4 py-2.5 fw-bold shadow-2xs d-inline-flex align-items-center gap-2 hover-lift"
                                        onclick="window.addToCartLive(null, {{ $product->id }}, '{{ addslashes($product->title) }}', {{ $product->final_price }}, '{{ $product->image_url }}', parseInt(document.getElementById('itemQty').value), '{{ $product->type }}'); window.location.href='{{ route('cart') }}';">
                                    <i class="fa-solid fa-bolt text-warning"></i>
                                    <span>এখনই কিনুন</span>
                                </button>
                            </div>
                        </div>

                        <!-- Trust Guarantees -->
                        <div class="row g-2 pt-3 border-top mt-auto">
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center gap-2 p-2 rounded-3 bg-light border small">
                                    <i class="fa-solid fa-truck text-primary"></i>
                                    <span>সারা দেশে ক্যাশ অন হোম ডেলিভারি</span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center gap-2 p-2 rounded-3 bg-light border small">
                                    <i class="fa-solid fa-shield-halved text-success"></i>
                                    <span>{{ $product->type === 'stationery' ? '১০০% প্রিমিয়াম কাগজ ও উন্নত বাঁধাই নিশ্চয়তা' : '১০০% আসল প্রোডাক্ট ও রিপ্লেসমেন্ট সুবিধা' }}</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        <!-- ═══ SPECIFICATIONS & DESCRIPTION TABS ═══ -->
        <div class="bg-white rounded-4 border shadow-sm p-3 p-md-4 mb-4">
            <ul class="nav nav-pills mb-3 border-bottom pb-2 gap-2" id="prodTabs" role="tablist">
                @if(!empty($product->specifications) && count($product->specifications) > 0)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill fw-bold px-4" id="specs-tab" data-bs-toggle="pill" data-bs-target="#specs-pane" type="button" role="tab">
                            <i class="fa-solid fa-list-check me-1"></i>টেকনিক্যাল স্পেসিফিকেশন
                        </button>
                    </li>
                @endif
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ empty($product->specifications) ? 'active' : '' }} rounded-pill fw-bold px-4" id="desc-tab" data-bs-toggle="pill" data-bs-target="#desc-pane" type="button" role="tab">
                        <i class="fa-solid fa-circle-info me-1"></i>বিস্তারিত বিবরণ
                    </button>
                </li>
            </ul>
            <div class="tab-content" id="prodTabsContent">
                @if(!empty($product->specifications) && count($product->specifications) > 0)
                    <div class="tab-pane fade show active py-2" id="specs-pane" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered rounded-3 overflow-hidden mb-0" style="font-size: 13.5px;">
                                <tbody>
                                    @foreach($product->specifications as $key => $val)
                                        <tr>
                                            <th class="bg-light text-dark fw-bold w-25 py-2.5 px-3">{{ $key }}</th>
                                            <td class="text-secondary py-2.5 px-3">{{ $val }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
                <div class="tab-pane fade {{ empty($product->specifications) ? 'show active' : '' }} py-2" id="desc-pane" role="tabpanel">
                    <div class="prose text-dark lh-base" style="font-size: 14.5px;">
                        {!! nl2br(e($product->description ?: $product->summary)) !!}
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══ RELATED PRODUCTS ═══ -->
        @if($relatedProducts->count() > 0)
            <div class="mt-5">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-layer-group text-primary"></i>
                        <span>সম্পর্কিত অন্যান্য আকর্ষণীয় পণ্য</span>
                    </h4>
                    <a href="{{ route($product->type === 'electronics' ? 'products.electronics' : 'products.stationery') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold">
                        সব দেখুন →
                    </a>
                </div>
                <div class="row row-cols-2 row-cols-sm-3 row-cols-md-3 row-cols-xl-6 g-3">
                    @foreach($relatedProducts as $rel)
                        <div class="col d-flex">
                            @include('frontend.products.partials.product-card', ['product' => $rel])
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</div>

<!-- Mobile Sticky Purchase Bar (Device Friendly UX) -->
<div class="d-md-none fixed-bottom bg-white border-top shadow-lg p-2.5 z-3 d-flex align-items-center justify-content-between gap-2" style="z-index: 1050;">
    <div class="d-flex align-items-center gap-2 overflow-hidden">
        <img src="{{ $product->image_url }}" alt="{{ $product->title }}" class="rounded-2 object-fit-contain border bg-light" style="width: 42px; height: 42px;">
        <div class="overflow-hidden">
            <div class="fw-bold text-dark text-truncate small" style="max-width: 155px;">{{ $product->title }}</div>
            <div class="fw-black text-primary small">৳@bn(round($product->final_price))</div>
        </div>
    </div>
    <button type="button" 
            class="btn btn-primary rounded-pill px-3 py-2 fw-bold text-nowrap shadow-xs d-inline-flex align-items-center gap-1.5" 
            onclick="window.addToCartLive(this, {{ $product->id }}, '{{ addslashes($product->title) }}', {{ $product->final_price }}, '{{ $product->image_url }}', 1, '{{ $product->type }}');">
        <i class="fa-solid fa-cart-shopping"></i>
        <span>অর্ডার করুন</span>
    </button>
</div>
@endsection
