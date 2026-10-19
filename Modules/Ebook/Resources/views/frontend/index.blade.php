@extends('layouts.app')

@php
    $S = \Illuminate\Support\Str::class;
    $filterKeys = ['search', 'q', 'category', 'author', 'publisher', 'format', 'free_only', 'min_price', 'max_price', 'discount_min', 'letter', 'sort'];
    $filtered = request()->anyFilled($filterKeys) || (int) request('page', 1) > 1;
    $keep = fn (array $except = []) => request()->except(array_merge(['page'], $except));

    // flat category list (parents then children) with counts
    $flatCats = collect();
    foreach ($categories as $c) {
        $flatCats->push(['c' => $c, 'child' => false]);
        foreach (($c->children ?? collect()) as $ch) { $flatCats->push(['c' => $ch, 'child' => true]); }
    }
    $curCatKey = request('category');
    $curCat = $curCatKey ? $flatCats->first(fn ($r) => (string) $r['c']->slug === (string) $curCatKey || (string) $r['c']->id === (string) $curCatKey) : null;

    $formats = ['' => 'All', 'epub' => 'EPUB', 'pdf' => 'PDF', 'free' => 'Free', 'paid' => 'Paid'];
    $sorts = ['' => 'Latest', 'popular' => 'Popular', 'bestselling' => 'Bestselling', 'price_low' => 'Price: Low', 'price_high' => 'Price: High', 'discount_high' => 'Discount'];
    $curFmt = request('format', '');
    $curSort = request('sort', '');
    $curAuthor = request('author') ? $sidebarAuthors->first(fn ($a) => (string) $a->slug === (string) request('author') || (string) $a->id === (string) request('author')) : null;
    $curPub = request('publisher') ? $sidebarPublishers->first(fn ($p) => (string) $p->slug === (string) request('publisher') || (string) $p->id === (string) request('publisher')) : null;
    $term = trim((string) (request('search') ?: request('q')));
@endphp

@section('title', 'ই-বুক — আইডিয়া প্রকাশন')
@section('og_type', 'website')
@section('og_title', 'ডিজিটাল পাঠাগার ও ই-বুক সংগ্রহ — আইডিয়া প্রকাশন')
@section('og_description', 'আইডিয়া প্রকাশনের ডিজিটাল পাঠাগার থেকে EPUB ও PDF ই-বুক পড়ুন।')
@section('og_image', asset('images/og-banner.jpg'))
@section('og_url', route('ebook.index'))

@push('styles')
<link rel="stylesheet" href="{{ asset('css/ebook-modern.css') }}?v={{ @filemtime(public_path('css/ebook-modern.css')) ?: 1 }}">
@endpush

@section('content')
<div class="eb-page">

    <header class="eb-head container">
        <div class="eb-head__title"><h1>E-Books</h1><span>{{ number_format($stats['total'] ?? 0) }}</span></div>
        <form action="{{ route('ebook.index') }}" method="GET" class="eb-search" role="search">
            @foreach(['category', 'author', 'publisher', 'format', 'sort'] as $k)@if(request($k))<input type="hidden" name="{{ $k }}" value="{{ request($k) }}">@endif @endforeach
            <input type="search" name="search" value="{{ $term }}" placeholder="Search" aria-label="Search e-books" autocomplete="off" enterkeyhint="search" id="ebSearch">
            <button type="submit" aria-label="Search"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>
    </header>

    {{-- Toolbar: Category · Format · Author · Publisher · Sort --}}
    <div class="sf-bar eb-bar">
        <div class="container sf-bar__in">

            <div class="sf-dd" data-sf-dd>
                <button type="button" class="sf-dd__btn" aria-haspopup="true" aria-expanded="false">
                    <span class="sf-dd__label">Category</span><strong class="sf-dd__value">{{ $curCat['c']->name ?? 'All' }}</strong><i class="fa-solid fa-chevron-down sf-dd__caret"></i>
                </button>
                <div class="sf-dd__menu {{ $flatCats->count() > 8 ? 'sf-dd__menu--cols' : '' }}" role="menu" style="--sf-cols: {{ $flatCats->count() > 16 ? 3 : 2 }};">
                    <div class="sf-dd__sheet-head"><b>Category</b><button type="button" class="sf-dd__close" aria-label="Close">&times;</button></div>
                    @if($flatCats->count() > 8)<div class="sf-dd__find"><input type="search" placeholder="Find" data-sf-find autocomplete="off" aria-label="Find"></div>@endif
                    <div class="sf-dd__list">
                        <a href="{{ route('ebook.index', $keep(['category'])) }}" class="sf-dd__item {{ $curCat ? '' : 'is-active' }}" role="menuitem"><span class="sf-dd__name">All</span><small>{{ $stats['total'] ?? 0 }}</small></a>
                        @foreach($flatCats as $row)
                            @php $c = $row['c']; @endphp
                            <a href="{{ route('ebook.index', array_merge($keep(['category']), ['category' => $c->slug])) }}" role="menuitem"
                               class="sf-dd__item {{ $curCat && $curCat['c']->id === $c->id ? 'is-active' : '' }} {{ $row['child'] ? 'is-child' : '' }}" data-name="{{ \Illuminate\Support\Str::lower($c->name) }}">
                                <span class="sf-dd__name">{{ $c->name }}</span><small>{{ $c->ebooks_count ?? 0 }}</small>
                            </a>
                        @endforeach
                        <div class="sf-dd__none d-none">No match</div>
                    </div>
                </div>
            </div>

            <div class="sf-dd" data-sf-dd>
                <button type="button" class="sf-dd__btn" aria-haspopup="true" aria-expanded="false">
                    <span class="sf-dd__label">Format</span><strong class="sf-dd__value">{{ $formats[$curFmt] ?? 'All' }}</strong><i class="fa-solid fa-chevron-down sf-dd__caret"></i>
                </button>
                <div class="sf-dd__menu sf-dd__menu--sm" role="menu">
                    <div class="sf-dd__sheet-head"><b>Format</b><button type="button" class="sf-dd__close" aria-label="Close">&times;</button></div>
                    <div class="sf-dd__list">
                        @foreach($formats as $v => $l)
                            <a href="{{ route('ebook.index', $v === '' ? $keep(['format']) : array_merge($keep(['format']), ['format' => $v])) }}" class="sf-dd__item {{ $curFmt === $v ? 'is-active' : '' }}" role="menuitem"><span class="sf-dd__name">{{ $l }}</span></a>
                        @endforeach
                    </div>
                </div>
            </div>

            @if($sidebarAuthors->isNotEmpty())
            <div class="sf-dd" data-sf-dd>
                <button type="button" class="sf-dd__btn" aria-haspopup="true" aria-expanded="false">
                    <span class="sf-dd__label">Author</span><strong class="sf-dd__value">{{ $curAuthor->name ?? 'All' }}</strong><i class="fa-solid fa-chevron-down sf-dd__caret"></i>
                </button>
                <div class="sf-dd__menu {{ $sidebarAuthors->count() > 8 ? 'sf-dd__menu--cols' : '' }}" role="menu" style="--sf-cols: {{ $sidebarAuthors->count() > 16 ? 3 : 2 }};">
                    <div class="sf-dd__sheet-head"><b>Author</b><button type="button" class="sf-dd__close" aria-label="Close">&times;</button></div>
                    <div class="sf-dd__find"><input type="search" placeholder="Find" data-sf-find autocomplete="off" aria-label="Find"></div>
                    <div class="sf-dd__list">
                        <a href="{{ route('ebook.index', $keep(['author'])) }}" class="sf-dd__item {{ $curAuthor ? '' : 'is-active' }}" role="menuitem"><span class="sf-dd__name">All</span></a>
                        @foreach($sidebarAuthors as $a)
                            <a href="{{ route('ebook.index', array_merge($keep(['author']), ['author' => $a->slug ?: $a->id])) }}" role="menuitem" class="sf-dd__item {{ $curAuthor && $curAuthor->id === $a->id ? 'is-active' : '' }}" data-name="{{ \Illuminate\Support\Str::lower($a->name) }}">
                                <span class="sf-dd__name">{{ $a->name }}</span><small>{{ $a->ebooks_count }}</small>
                            </a>
                        @endforeach
                        <div class="sf-dd__none d-none">No match</div>
                    </div>
                </div>
            </div>
            @endif

            @if($sidebarPublishers->isNotEmpty())
            <div class="sf-dd" data-sf-dd>
                <button type="button" class="sf-dd__btn" aria-haspopup="true" aria-expanded="false">
                    <span class="sf-dd__label">Publisher</span><strong class="sf-dd__value">{{ $curPub->name ?? 'All' }}</strong><i class="fa-solid fa-chevron-down sf-dd__caret"></i>
                </button>
                <div class="sf-dd__menu {{ $sidebarPublishers->count() > 8 ? 'sf-dd__menu--cols' : '' }}" role="menu" style="--sf-cols: {{ $sidebarPublishers->count() > 16 ? 3 : 2 }};">
                    <div class="sf-dd__sheet-head"><b>Publisher</b><button type="button" class="sf-dd__close" aria-label="Close">&times;</button></div>
                    <div class="sf-dd__find"><input type="search" placeholder="Find" data-sf-find autocomplete="off" aria-label="Find"></div>
                    <div class="sf-dd__list">
                        <a href="{{ route('ebook.index', $keep(['publisher'])) }}" class="sf-dd__item {{ $curPub ? '' : 'is-active' }}" role="menuitem"><span class="sf-dd__name">All</span></a>
                        @foreach($sidebarPublishers as $p)
                            <a href="{{ route('ebook.index', array_merge($keep(['publisher']), ['publisher' => $p->slug ?: $p->id])) }}" role="menuitem" class="sf-dd__item {{ $curPub && $curPub->id === $p->id ? 'is-active' : '' }}" data-name="{{ \Illuminate\Support\Str::lower($p->name) }}">
                                <span class="sf-dd__name">{{ $p->name }}</span><small>{{ $p->ebooks_count }}</small>
                            </a>
                        @endforeach
                        <div class="sf-dd__none d-none">No match</div>
                    </div>
                </div>
            </div>
            @endif

            <div class="sf-dd" data-sf-dd>
                <button type="button" class="sf-dd__btn" aria-haspopup="true" aria-expanded="false">
                    <span class="sf-dd__label">Sort</span><strong class="sf-dd__value">{{ $sorts[$curSort] ?? 'Latest' }}</strong><i class="fa-solid fa-chevron-down sf-dd__caret"></i>
                </button>
                <div class="sf-dd__menu sf-dd__menu--sm" role="menu">
                    <div class="sf-dd__sheet-head"><b>Sort</b><button type="button" class="sf-dd__close" aria-label="Close">&times;</button></div>
                    <div class="sf-dd__list">
                        @foreach($sorts as $v => $l)
                            <a href="{{ route('ebook.index', $v === '' ? $keep(['sort']) : array_merge($keep(['sort']), ['sort' => $v])) }}" class="sf-dd__item {{ $curSort === $v ? 'is-active' : '' }}" role="menuitem"><span class="sf-dd__name">{{ $l }}</span></a>
                        @endforeach
                    </div>
                </div>
            </div>

            @if(request()->anyFilled($filterKeys))<a href="{{ route('ebook.index') }}" class="eb-clear">Clear</a>@endif
        </div>
    </div>
    <div class="sf-dd__scrim" data-sf-scrim></div>

    <main class="container eb-main">

        @if(!$isSearchMode)
            {{-- Spotlight --}}
            @if($spotlightEbook)
                @php
                    $sp = $spotlightEbook; $spReg = (float) $sp->price; $spOff = ($sp->discount_price > 0 && $sp->discount_price < $spReg) ? (float) $sp->discount_price : null;
                    $spFree = !empty($sp->is_free) || $spReg <= 0;
                @endphp
                <section class="eb-spot">
                    <a href="{{ route('ebook.show', $sp->slug ?: $sp->id) }}" class="eb-spot__cover">
                        @if($sp->cover_url)<img src="{{ $sp->cover_url }}" alt="" width="300" height="450" decoding="async" fetchpriority="high" onerror="this.remove()">@endif
                        <span class="eb-card__fallback">{{ mb_substr($sp->title, 0, 1) }}</span>
                    </a>
                    <div class="eb-spot__info">
                        @if($sp->category)<em>{{ $sp->category->name }}</em>@endif
                        <h2><a href="{{ route('ebook.show', $sp->slug ?: $sp->id) }}">{{ $sp->title }}</a></h2>
                        <p>{{ optional($sp->author)->name ?: ($sp->author_name ?: 'আইডিয়া প্রকাশন') }}</p>
                        <div class="dz-card__price">
                            <span class="dz-card__now">{{ $spFree ? 'Free' : '৳' . number_format($spOff ?? $spReg) }}</span>
                            @if($spOff && !$spFree)<s class="dz-card__old">৳{{ number_format($spReg) }}</s>@endif
                        </div>
                        <div class="eb-spot__act">
                            <a href="{{ route('ebook.show', $sp->slug ?: $sp->id) }}" class="btn btn-primary">Open</a>
                            @if($spFree)<a href="{{ route('ebook.read', $sp->slug ?: $sp->id) }}" class="btn btn-outline-dark">Read</a>@endif
                        </div>
                    </div>
                </section>
            @endif

            @php
                $shelves = [
                    ['Sale', $flashSales, route('ebook.index', ['discount_min' => 1]), 'eb-flash'],
                    ['Bestsellers', $bestsellingEbooks, route('ebook.index', ['sort' => 'bestselling']), 'eb-best'],
                    ['New', $newReleaseEbooks, route('ebook.index'), 'eb-new'],
                    ['Free', $freeEbooks, route('ebook.index', ['format' => 'free']), 'eb-free'],
                ];
            @endphp
            @foreach($shelves as [$label, $list, $more, $sid])
                @if($list->isNotEmpty())
                    <section class="dz-row">
                        <div class="dz-row__head">
                            <h2 class="dz-row__title"><a href="{{ $more }}">{{ $label }}</a><small>{{ $list->count() }}</small></h2>
                            <div class="dz-row__tools">
                                <button type="button" class="dz-arrow" onclick="scrollIdeaSlider('{{ $sid }}', -1)" aria-label="Prev"><i class="fa-solid fa-chevron-left"></i></button>
                                <button type="button" class="dz-arrow" onclick="scrollIdeaSlider('{{ $sid }}', 1)" aria-label="Next"><i class="fa-solid fa-chevron-right"></i></button>
                                <a href="{{ $more }}" class="dz-shopall">All</a>
                            </div>
                        </div>
                        <div class="idea-book-slider dz-track eb-track" id="{{ $sid }}">
                            @foreach($list as $eb)<div class="idea-slider-item eb-item">@include('ebook::frontend.partials.shelf-card', ['ebook' => $eb, 'userLibraryIds' => $userLibraryIds])</div>@endforeach
                        </div>
                    </section>
                @endif
            @endforeach

            {{-- One row per category --}}
            @foreach($categoryShelves as $cat)
                <section class="dz-row">
                    <div class="dz-row__head">
                        <h2 class="dz-row__title"><a href="{{ route('ebook.index', ['category' => $cat->slug]) }}">{{ $cat->name }}</a><small>{{ $cat->ebooks_count }}</small></h2>
                        <div class="dz-row__tools">
                            <button type="button" class="dz-arrow" onclick="scrollIdeaSlider('ebCat{{ $cat->id }}', -1)" aria-label="Prev"><i class="fa-solid fa-chevron-left"></i></button>
                            <button type="button" class="dz-arrow" onclick="scrollIdeaSlider('ebCat{{ $cat->id }}', 1)" aria-label="Next"><i class="fa-solid fa-chevron-right"></i></button>
                            <a href="{{ route('ebook.index', ['category' => $cat->slug]) }}" class="dz-shopall">All</a>
                        </div>
                    </div>
                    <div class="idea-book-slider dz-track eb-track" id="ebCat{{ $cat->id }}">
                        @foreach($cat->shelf as $eb)<div class="idea-slider-item eb-item">@include('ebook::frontend.partials.shelf-card', ['ebook' => $eb, 'userLibraryIds' => $userLibraryIds])</div>@endforeach
                    </div>
                </section>
            @endforeach

            @if($newReleaseEbooks->isEmpty() && $categoryShelves->isEmpty())
                <div class="sf-empty"><strong>No e-books</strong></div>
            @endif
        @else
            @if($activeFilterTitle || $term)
                <h2 class="eb-result">{{ $activeFilterTitle ?: '“' . $term . '”' }}<small>{{ method_exists($ebooks, 'total') ? number_format($ebooks->total()) : '' }}</small></h2>
            @endif
            @if(method_exists($ebooks, 'total') && $ebooks->count())
                <div class="eb-grid">
                    @foreach($ebooks as $eb)@include('ebook::frontend.partials.shelf-card', ['ebook' => $eb, 'userLibraryIds' => $userLibraryIds])@endforeach
                </div>
                @if($ebooks->hasPages())<div class="eb-pager">{{ $ebooks->links() }}</div>@endif
            @else
                <div class="sf-empty"><strong>No e-books</strong><a href="{{ route('ebook.index') }}" class="dz-shopall">Reset</a></div>
            @endif
        @endif
    </main>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('keydown', function (e) {
    var t = e.target, typing = t && (/^(input|textarea|select)$/i.test(t.tagName) || t.isContentEditable);
    if (e.key === '/' && !typing && !e.ctrlKey && !e.metaKey) { var s = document.getElementById('ebSearch'); if (s) { e.preventDefault(); s.focus(); s.select(); } }
});
</script>
@endpush
