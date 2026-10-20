@extends('layouts.app')

@php
    use Illuminate\Support\Str;

    $ogBlogImage = \App\Support\SiteSetting::blogOgBannerUrl();
    $imgOf = function ($p) {
        $img = $p->featured_image;
        if (!$img) return null;
        return str_starts_with($img, 'http') ? $img : (str_starts_with($img, 'storage/') ? asset($img) : asset('storage/' . ltrim($img, '/')));
    };
    $heroImg = $heroPost ? $imgOf($heroPost) : null;
    if ($heroImg) { $ogBlogImage = $heroImg; }

    $filtered = request()->anyFilled(['search', 'category', 'sort']);
    $curSort = request('sort') === 'popular' ? 'popular' : 'latest';
    $curCat = request('category') ? $categories->first(fn ($c) => $c->slug === request('category') || (string) $c->id === (string) request('category') || $c->name === request('category')) : null;
    $curCatLabel = $curCat->name ?? 'All';
    $totalPosts = $categories->sum('posts_count');
    $keep = fn (array $except = []) => request()->except(array_merge(['page'], $except));
    $sideFeatured = $featured->reject(fn ($p) => $heroPost && $p->id === $heroPost->id)->take(3);
    $heroSlides = collect([$heroPost])->filter()->merge($sideFeatured)->unique('id')->take(4)->values();
@endphp

@section('title', 'আইডিয়াপত্র — মুক্তচিন্তার অসীম আকাশ (Ideapatra)')
@section('og_type', 'website')
@section('og_title', 'আইডিয়াপত্র (Ideapatra) — মুক্তচিন্তার অসীম আকাশ')
@section('og_description', 'আইডিয়াপত্র — মুক্তচিন্তার অসীম আকাশ। নিয়মিত গল্প, কবিতা, প্রবন্ধ ও সাহিত্যচর্চা।')
@section('og_image', $ogBlogImage)
@section('og_url', route('blog.index'))

@push('styles')
<link rel="stylesheet" href="{{ asset('css/blog-modern.css') }}?v={{ @filemtime(public_path('css/blog-modern.css')) ?: 1 }}">
@endpush

@section('content')
<div class="bl-page">

    {{-- Title · search · write --}}
    <header class="bl-head container">
        <div class="bl-head__title">
            <h1>Ideapatra</h1>
            <span>{{ number_format($posts->total()) }}</span>
        </div>
        <form action="{{ route('blog.index') }}" method="GET" class="bl-search" role="search">
            @if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
            @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search" aria-label="Search" autocomplete="off" enterkeyhint="search" id="blSearch">
            <button type="submit" aria-label="Search"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>
        <a href="{{ route('blog.write') }}" class="bl-write">Write</a>
    </header>

    {{-- Sticky toolbar: Category ▾ · Sort ▾ --}}
    <div class="sf-bar bl-bar">
        <div class="container sf-bar__in">
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
                    <div class="sf-dd__list">
                        <a href="{{ route('blog.index', $keep(['category'])) }}" class="sf-dd__item {{ $curCat ? '' : 'is-active' }}" role="menuitem">
                            <span class="sf-dd__name">All</span><small>{{ $totalPosts }}</small>
                        </a>
                        @foreach($categories as $cat)
                            <a href="{{ route('blog.index', array_merge($keep(['category']), ['category' => $cat->slug])) }}"
                               class="sf-dd__item {{ $curCat && $curCat->id === $cat->id ? 'is-active' : '' }}" role="menuitem" data-name="{{ \Illuminate\Support\Str::lower($cat->name) }}">
                                <span class="sf-dd__name">{{ $cat->name }}</span><small>{{ $cat->posts_count }}</small>
                            </a>
                        @endforeach
                        <div class="sf-dd__none d-none">No match</div>
                    </div>
                </div>
            </div>

            <div class="sf-dd" data-sf-dd>
                <button type="button" class="sf-dd__btn" aria-haspopup="true" aria-expanded="false">
                    <span class="sf-dd__label">Sort</span>
                    <strong class="sf-dd__value">{{ $curSort === 'popular' ? 'Popular' : 'Latest' }}</strong>
                    <i class="fa-solid fa-chevron-down sf-dd__caret"></i>
                </button>
                <div class="sf-dd__menu sf-dd__menu--sm" role="menu">
                    <div class="sf-dd__sheet-head"><b>Sort</b><button type="button" class="sf-dd__close" aria-label="Close">&times;</button></div>
                    <div class="sf-dd__list">
                        <a href="{{ route('blog.index', $keep(['sort'])) }}" class="sf-dd__item {{ $curSort === 'latest' ? 'is-active' : '' }}" role="menuitem"><span class="sf-dd__name">Latest</span></a>
                        <a href="{{ route('blog.index', array_merge($keep(['sort']), ['sort' => 'popular'])) }}" class="sf-dd__item {{ $curSort === 'popular' ? 'is-active' : '' }}" role="menuitem"><span class="sf-dd__name">Popular</span></a>
                    </div>
                </div>
            </div>

            @if($filtered)
                <a href="{{ route('blog.index') }}" class="bl-clear">Clear</a>
            @endif
        </div>
    </div>
    <div class="sf-dd__scrim" data-sf-scrim></div>

    <main class="container bl-main">

        {{-- Featured posts carousel --}}
        @if(!$filtered && $heroPost && (int) request('page', 1) === 1)
            <section id="blogFeaturedCarousel" class="bl-hero carousel slide" aria-label="Featured posts" data-bs-ride="carousel" data-bs-interval="5500" data-bs-pause="hover">
                <div class="carousel-inner">
                    @foreach($heroSlides as $slideIndex => $slidePost)
                        @php $slideImage = $imgOf($slidePost); @endphp
                        <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                            <a href="{{ route('blog.show', $slidePost->slug) }}" class="bl-hero__main">
                                @if($slideImage)
                                    <img src="{{ $slideImage }}" alt="" width="1200" height="560" @if($loop->first) fetchpriority="high" @else loading="lazy" @endif decoding="async">
                                @else
                                    <span class="bl-hero__ph"></span>
                                @endif
                                <span class="bl-hero__shade"></span>
                                <span class="bl-hero__text">
                                    @if($slidePost->category)<em>{{ $slidePost->category->name }}</em>@endif
                                    <strong>{{ $slidePost->title }}</strong>
                                    <small>{{ $slidePost->author_name ?: 'সম্পাদকীয়' }} · {{ optional($slidePost->published_at)->format('d M Y') }}</small>
                                </span>
                            </a>
                        </div>
                    @endforeach
                </div>
                @if($heroSlides->count() > 1)
                    <div class="carousel-indicators">
                        @foreach($heroSlides as $slideIndex => $slidePost)
                            <button type="button" data-bs-target="#blogFeaturedCarousel" data-bs-slide-to="{{ $slideIndex }}" class="{{ $loop->first ? 'active' : '' }}" @if($loop->first) aria-current="true" @endif aria-label="Go to featured post {{ $slideIndex + 1 }}"></button>
                        @endforeach
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#blogFeaturedCarousel" data-bs-slide="prev" aria-label="Previous featured post">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#blogFeaturedCarousel" data-bs-slide="next" aria-label="Next featured post">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    </button>
                @endif
            </section>
        @endif

        @php
            $card = function ($p) use ($imgOf) { return ['p' => $p, 'img' => $imgOf($p)]; };
        @endphp

        @if(!$filtered && $categoriesWithPosts->isNotEmpty())
            {{-- One sliding row per category --}}
            @foreach($categoriesWithPosts as $sec)
                <section class="dz-row">
                    <div class="dz-row__head">
                        <h2 class="dz-row__title">
                            <a href="{{ route('blog.category', $sec->slug) }}">{{ $sec->name }}</a>
                            <small>{{ $sec->posts_count }}</small>
                        </h2>
                        <div class="dz-row__tools">
                            <button type="button" class="dz-arrow" onclick="scrollIdeaSlider('blRow{{ $sec->id }}', -1)" aria-label="Prev"><i class="fa-solid fa-chevron-left"></i></button>
                            <button type="button" class="dz-arrow" onclick="scrollIdeaSlider('blRow{{ $sec->id }}', 1)" aria-label="Next"><i class="fa-solid fa-chevron-right"></i></button>
                            <a href="{{ route('blog.category', $sec->slug) }}" class="dz-shopall">All</a>
                        </div>
                    </div>
                    <div class="idea-book-slider dz-track bl-track" id="blRow{{ $sec->id }}" data-autoplay="1">
                        @foreach($sec->posts as $p)
                            <div class="idea-slider-item bl-item">@include('blog::partials.post-card', ['post' => $p, 'imageUrl' => $imgOf($p)])</div>
                        @endforeach
                    </div>
                </section>
            @endforeach
        @elseif($posts->count())
            <section class="dz-row bl-results-row">
                <div class="dz-row__head">
                    <h2 class="dz-row__title">
                        @if(request()->filled('search'))
                            Search results
                        @elseif($curCat)
                            {{ $curCat->name }}
                        @elseif($curSort === 'popular')
                            Popular posts
                        @else
                            All posts
                        @endif
                        <small>{{ number_format($posts->total()) }}</small>
                    </h2>
                    <div class="dz-row__tools">
                        <button type="button" class="dz-arrow" onclick="scrollIdeaSlider('blResultsTrack', -1)" aria-label="Previous posts"><i class="fa-solid fa-chevron-left"></i></button>
                        <button type="button" class="dz-arrow" onclick="scrollIdeaSlider('blResultsTrack', 1)" aria-label="Next posts"><i class="fa-solid fa-chevron-right"></i></button>
                    </div>
                </div>
                <div class="idea-book-slider dz-track bl-track" id="blResultsTrack" data-autoplay="1">
                @foreach($posts as $p)
                    <div class="idea-slider-item bl-item">
                        @include('blog::partials.post-card', ['post' => $p, 'imageUrl' => $imgOf($p)])
                    </div>
                @endforeach
                </div>
            </section>
            @if($posts->hasPages())<div class="bl-pager">{{ $posts->links() }}</div>@endif
        @else
            <div class="sf-empty"><strong>No posts</strong><a href="{{ route('blog.index') }}" class="dz-shopall">Reset</a></div>
        @endif
    </main>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('keydown', function (e) {
    var t = e.target, typing = t && (/^(input|textarea|select)$/i.test(t.tagName) || t.isContentEditable);
    if (e.key === '/' && !typing && !e.ctrlKey && !e.metaKey) { var s = document.getElementById('blSearch'); if (s) { e.preventDefault(); s.focus(); s.select(); } }
});
</script>
@endpush
