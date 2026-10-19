@extends('layouts.app')

@php
    $labels = ['electronics' => 'Electronics', 'stationery' => 'Stationery', 'appliance' => 'Appliances'];
    $pageName = $labels[$type] ?? ucfirst($type);
    $shopRoute = ['electronics' => 'products.electronics', 'stationery' => 'products.stationery', 'appliance' => 'products.appliance'][$type] ?? 'products.stationery';
    $filterKeys = ['category', 'q', 'min_price', 'max_price', 'brand', 'in_stock', 'discount'];
    $hasFilters = request()->anyFilled($filterKeys);
    $sliderMode = !$hasFilters && request('view') !== 'grid' && isset($categoriesWithProducts) && $categoriesWithProducts->isNotEmpty();
    $advCount = collect(['min_price', 'max_price', 'brand', 'in_stock', 'discount'])->filter(fn ($k) => request()->filled($k))->count();
    $keep = fn (array $except = []) => request()->except(array_merge(['page'], $except));
@endphp

@section('title', $pageName . ' | আইডিয়া প্রকাশন')
@section('meta_description', $currentMeta['subtitle'])

@section('content')
<div class="sf-page" style="--shop-theme-primary: {{ $currentMeta['theme_color'] }};">

    {{-- Title + search --}}
    <header class="sf-head container">
        <div class="sf-head__title">
            <h1>{{ $pageName }}</h1>
            <span>{{ number_format($products->total()) }}</span>
        </div>
        <form action="{{ route($shopRoute) }}" method="GET" class="sf-search" role="search">
            @foreach(request()->except(['q', 'page']) as $k => $v)
                @if(!is_array($v))<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endif
            @endforeach
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search" aria-label="Search {{ $pageName }}" autocomplete="off" enterkeyhint="search" id="sfSearch">
            <button type="submit" aria-label="Search"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>
    </header>

    {{-- Sticky toolbar: Categories ▾ · Sort ▾ · Filters --}}
    @php
        $sorts = ['latest' => 'Latest', 'popular' => 'Popular', 'price_low' => 'Price: Low', 'price_high' => 'Price: High', 'discount' => 'Discount'];
        $curSort = request('sort', 'latest');
        $curCatLabel = request('category') === 'others' ? 'Others' : ($selectedCategory->name ?? 'All');
        $totalAll = $categories->sum('products_count');
    @endphp
    <div class="sf-bar">
        <div class="container sf-bar__in">

            {{-- Categories dropdown --}}
            <div class="sf-dd" data-sf-dd>
                <button type="button" class="sf-dd__btn" aria-haspopup="true" aria-expanded="false">
                    <span class="sf-dd__label">Category</span>
                    <strong class="sf-dd__value">{{ $curCatLabel }}</strong>
                    <i class="fa-solid fa-chevron-down sf-dd__caret"></i>
                </button>
                <div class="sf-dd__menu {{ $categories->count() > 8 ? 'sf-dd__menu--cols' : '' }}" role="menu" style="--sf-cols: {{ $categories->count() > 16 ? 3 : 2 }};">
                    <div class="sf-dd__sheet-head"><b>Category</b><button type="button" class="sf-dd__close" aria-label="Close">&times;</button></div>
                    @if($categories->count() > 8)
                        <div class="sf-dd__find"><input type="search" placeholder="Find" aria-label="Find category" data-sf-find autocomplete="off"></div>
                    @endif
                    <div class="sf-dd__list" data-sf-list>
                        <a href="{{ route($shopRoute, $keep(['category'])) }}" class="sf-dd__item {{ request('category') ? '' : 'is-active' }}" role="menuitem">
                            <span class="sf-dd__name">All</span><small>{{ $totalAll }}</small>
                        </a>
                        @foreach($categories as $cat)
                            @php $isCur = ($selectedCategory && $selectedCategory->id === $cat->id) || (request('category') === 'others' && $cat->slug === 'others'); @endphp
                            <a href="{{ route($shopRoute, array_merge($keep(['category']), ['category' => $cat->slug])) }}"
                               class="sf-dd__item {{ $isCur ? 'is-active' : '' }}" role="menuitem" data-name="{{ \Illuminate\Support\Str::lower($cat->name) }}">
                                <span class="sf-dd__name">{{ $cat->name }}</span><small>{{ $cat->products_count }}</small>
                            </a>
                        @endforeach
                        <div class="sf-dd__none d-none">No match</div>
                    </div>
                </div>
            </div>

            {{-- Sort dropdown --}}
            <div class="sf-dd" data-sf-dd>
                <button type="button" class="sf-dd__btn" aria-haspopup="true" aria-expanded="false">
                    <span class="sf-dd__label">Sort</span>
                    <strong class="sf-dd__value">{{ $sorts[$curSort] ?? 'Latest' }}</strong>
                    <i class="fa-solid fa-chevron-down sf-dd__caret"></i>
                </button>
                <div class="sf-dd__menu sf-dd__menu--sm" role="menu">
                    <div class="sf-dd__sheet-head"><b>Sort</b><button type="button" class="sf-dd__close" aria-label="Close">&times;</button></div>
                    <div class="sf-dd__list">
                        @foreach($sorts as $v => $l)
                            <a href="{{ route($shopRoute, array_merge($keep(['sort']), ['sort' => $v])) }}" class="sf-dd__item {{ $curSort === $v ? 'is-active' : '' }}" role="menuitem">
                                <span class="sf-dd__name">{{ $l }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <button type="button" class="sf-filter-btn" data-bs-toggle="offcanvas" data-bs-target="#sfFilters" aria-controls="sfFilters">
                Filters @if($advCount)<b>{{ $advCount }}</b>@endif
            </button>
        </div>
    </div>
    <div class="sf-dd__scrim" data-sf-scrim></div>

    <main class="container sf-main">

        {{-- Active filters --}}
        @if($hasFilters)
            <div class="sf-active">
                @if(request('q'))<a href="{{ route($shopRoute, $keep(['q'])) }}">“{{ request('q') }}” ×</a>@endif
                @if($selectedCategory)<a href="{{ route($shopRoute, $keep(['category'])) }}">{{ $selectedCategory->name }} ×</a>@endif
                @if(request('brand'))<a href="{{ route($shopRoute, $keep(['brand'])) }}">{{ is_array(request('brand')) ? implode(', ', request('brand')) : request('brand') }} ×</a>@endif
                @if(request('min_price') || request('max_price'))<a href="{{ route($shopRoute, $keep(['min_price', 'max_price'])) }}">৳{{ request('min_price', 0) }}–{{ request('max_price', '∞') }} ×</a>@endif
                @if(request('in_stock') === '1')<a href="{{ route($shopRoute, $keep(['in_stock'])) }}">In stock ×</a>@endif
                @if(request('discount') === '1')<a href="{{ route($shopRoute, $keep(['discount'])) }}">Sale ×</a>@endif
                <a href="{{ route($shopRoute) }}" class="sf-active__clear">Clear</a>
            </div>
        @endif

        @if($sliderMode)
            {{-- One sliding row per category --}}
            @foreach($categoriesWithProducts as $cat)
                <section class="dz-row">
                    <div class="dz-row__head">
                        <h2 class="dz-row__title">
                            <a href="{{ route($shopRoute, ['category' => $cat->slug]) }}">{{ $cat->name }}</a>
                            <small>{{ $cat->products_count ?? $cat->products->count() }}</small>
                        </h2>
                        <div class="dz-row__tools">
                            <button type="button" class="dz-arrow" onclick="scrollIdeaSlider('sfRow{{ $cat->id }}', -1)" aria-label="Prev"><i class="fa-solid fa-chevron-left"></i></button>
                            <button type="button" class="dz-arrow" onclick="scrollIdeaSlider('sfRow{{ $cat->id }}', 1)" aria-label="Next"><i class="fa-solid fa-chevron-right"></i></button>
                            <a href="{{ route($shopRoute, ['category' => $cat->slug]) }}" class="dz-shopall">All</a>
                        </div>
                    </div>
                    <div class="idea-book-slider dz-track" id="sfRow{{ $cat->id }}" data-autoplay="1">
                        @foreach($cat->products as $p)
                            <div class="idea-slider-item idea-product-slider-item">@include('frontend.products.partials.product-card', ['product' => $p])</div>
                        @endforeach
                    </div>
                </section>
            @endforeach
        @elseif($products->count())
            <div class="sf-grid" id="catalogProductsContainer">
                @foreach($products as $p)
                    @include('frontend.products.partials.product-card', ['product' => $p])
                @endforeach
            </div>
            @if($products->hasPages())<div class="sf-pager">{{ $products->links() }}</div>@endif
        @else
            <div class="sf-empty">
                <strong>No products</strong>
                <a href="{{ route($shopRoute) }}" class="dz-shopall">Reset</a>
            </div>
        @endif
    </main>
</div>

{{-- Filters drawer --}}
<div class="offcanvas offcanvas-end sf-drawer" tabindex="-1" id="sfFilters" aria-labelledby="sfFiltersLabel">
    <div class="offcanvas-header">
        <h5 id="sfFiltersLabel">Filters</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <form action="{{ route($shopRoute) }}" method="GET" class="offcanvas-body sf-drawer__body">
        @foreach(['q', 'category', 'sort'] as $k)
            @if(request($k) && !is_array(request($k)))<input type="hidden" name="{{ $k }}" value="{{ request($k) }}">@endif
        @endforeach

        <fieldset>
            <legend>Price</legend>
            <div class="sf-price">
                <input type="number" min="0" name="min_price" value="{{ request('min_price') }}" placeholder="{{ (int) ($priceStats['min'] ?? 0) }}" inputmode="numeric" aria-label="Min price">
                <span>–</span>
                <input type="number" min="0" name="max_price" value="{{ request('max_price') }}" placeholder="{{ (int) ($priceStats['max'] ?? 0) }}" inputmode="numeric" aria-label="Max price">
            </div>
        </fieldset>

        @if($brandsWithCount->isNotEmpty())
            <fieldset>
                <legend>Brand</legend>
                <div class="sf-brands">
                    @php $picked = (array) request('brand', []); @endphp
                    @foreach($brandsWithCount as $b)
                        <label class="sf-check">
                            <input type="checkbox" name="brand[]" value="{{ $b->brand }}" @checked(in_array($b->brand, $picked, true))>
                            <span>{{ $b->brand }}</span><small>{{ $b->products_count }}</small>
                        </label>
                    @endforeach
                </div>
            </fieldset>
        @endif

        <fieldset>
            <legend>Show</legend>
            <label class="sf-switch"><input type="checkbox" name="in_stock" value="1" @checked(request('in_stock') === '1')><span>In stock</span></label>
            <label class="sf-switch"><input type="checkbox" name="discount" value="1" @checked(request('discount') === '1')><span>Sale</span></label>
        </fieldset>

        <div class="sf-drawer__foot">
            <a href="{{ route($shopRoute) }}" class="btn btn-outline-secondary">Reset</a>
            <button type="submit" class="btn btn-primary">Apply</button>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
// "/" focuses search; auto-submit price/brand toggles are applied from the drawer
document.addEventListener('keydown', function (e) {
    var t = e.target, typing = t && (/^(input|textarea|select)$/i.test(t.tagName) || t.isContentEditable);
    if (e.key === '/' && !typing && !e.ctrlKey && !e.metaKey) { var s = document.getElementById('sfSearch'); if (s) { e.preventDefault(); s.focus(); s.select(); } }
});
</script>
@endpush
