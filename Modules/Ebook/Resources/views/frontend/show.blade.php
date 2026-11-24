@extends('layouts.app')

@php
    use Illuminate\Support\Str;
    $S = $ebook->slug ?: $ebook->id;
    $cover = $ebook->cover_url;
    $isFree = (bool) $ebook->is_free;
    $regular = (float) $ebook->price;
    $hasOff = !$isFree && $price < $regular;
    $offPct = $hasOff && $regular > 0 ? (int) round(($regular - $price) / $regular * 100) : 0;
    $authorName = $ebook->author?->name ?: ($ebook->author_name ?: 'আইডিয়া প্রকাশন');
    $authorUrl = $ebook->author?->slug ? route('author.show', $ebook->author->slug) : null;
    $pubName = $ebook->publisher?->name;
    $pubUrl = $ebook->publisher?->slug ? route('publisher.show', $ebook->publisher->slug) : null;
    $desc = trim(strip_tags((string) $ebook->description));
    $metaDesc = Str::limit($desc ?: ($ebook->title . ' — ' . $authorName . '। আইডিয়া প্রকাশনের ই-বুক।'), 160);
    $progress = (int) ($entry?->progress_percent ?? 0);
    $formatLabel = $kind === 'pdf' ? 'PDF' : ($kind === 'epub' ? 'EPUB' : ($ebook->format_badge ?: '—'));
    $credits = array_filter([
        'সম্পাদক' => $ebook->editor_name, 'অনুবাদক' => $ebook->translator_name, 'পুনর্লিখন' => $ebook->rewriter_name,
    ]);
    $cartItem = ['id' => 'ebook_' . $ebook->id, 'title' => $ebook->title, 'price' => $price, 'image' => $cover, 'quantity' => 1, 'type' => 'ebook'];
@endphp

@section('title', $ebook->title . ' — ' . $authorName . ' | ই-বুক')
@section('meta_description', $metaDesc)
@section('og_type', 'book')
@section('og_title', $ebook->title . ' — ' . $authorName)
@section('og_description', $metaDesc)
@if($cover)@section('og_image', $cover)@endif
@section('og_url', route('ebook.show', $S))

@section('schema_json')
@php
    $schema = [
        '@context' => 'https://schema.org', '@type' => 'Book', 'name' => $ebook->title,
        'author' => ['@type' => 'Person', 'name' => $authorName],
        'url' => route('ebook.show', $S), 'bookFormat' => 'https://schema.org/EBook', 'inLanguage' => 'bn',
        'offers' => ['@type' => 'Offer', 'price' => number_format($price, 2, '.', ''), 'priceCurrency' => 'BDT', 'availability' => 'https://schema.org/InStock', 'url' => route('ebook.show', $S)],
    ];
    if ($cover) $schema['image'] = $cover;
    if ($pubName) $schema['publisher'] = ['@type' => 'Organization', 'name' => $pubName];
    if ($ebook->isbn) $schema['isbn'] = $ebook->isbn;
    if ($ebook->pages) $schema['numberOfPages'] = (int) $ebook->pages;
    if ($desc) $schema['description'] = Str::limit($desc, 300);
    if ($reviewCount > 0) $schema['aggregateRating'] = ['@type' => 'AggregateRating', 'ratingValue' => (string) $avgRating, 'reviewCount' => $reviewCount, 'bestRating' => '5', 'worstRating' => '1'];
@endphp
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/ebook-modern.css') }}?v={{ @filemtime(public_path('css/ebook-modern.css')) }}">
<link rel="stylesheet" href="{{ asset('css/ebook-detail.css') }}?v={{ @filemtime(public_path('css/ebook-detail.css')) }}">
@endpush

@section('content')
<div class="ebd">
<div class="container">

    <nav class="ebd-crumb" aria-label="breadcrumb">
        <a href="{{ route('ebook.index') }}">E-Books</a>
        @if($ebook->category)<span aria-hidden="true">/</span><a href="{{ route('ebook.index', ['category' => $ebook->category->slug]) }}">{{ $ebook->category->name }}</a>@endif
        <span aria-hidden="true">/</span><span aria-current="page">{{ $ebook->title }}</span>
    </nav>

    @foreach(['success' => 'success', 'info' => 'info', 'error' => 'danger'] as $k => $cls)
        @if(session($k))<div class="alert alert-{{ $cls }} rounded-4 border-0">{{ session($k) }}</div>@endif
    @endforeach

    <section class="ebd-hero">
        <div class="ebd-side">
            <div class="ebd-cover">
                @if($cover)<img src="{{ $cover }}" alt="{{ $ebook->title }}" width="400" height="600" fetchpriority="high" decoding="async" onerror="this.remove()">@endif
                <span class="eb-card__fallback">{{ mb_substr($ebook->title, 0, 1) }}</span>
                @if($owns)<span class="eb-flag eb-flag--own">Owned</span>
                @elseif($isFree)<span class="eb-flag eb-flag--free">Free</span>
                @elseif($offPct)<span class="eb-flag">-{{ $offPct }}%</span>@endif
            </div>
            <div class="ebd-mini">
                <x-share type="ebook" :id="$ebook->id" :url="route('ebook.show', $S)" :title="$ebook->title" :text="$ebook->title . ' — ' . $authorName" :image="$cover" :subtitle="$authorName" heading="ই-বুকটি শেয়ার করুন" />
            </div>
        </div>

        <div class="ebd-info">
            <div class="ebd-tags">
                @if($ebook->category)<a class="ebd-tag" href="{{ route('ebook.index', ['category' => $ebook->category->slug]) }}">{{ $ebook->category->name }}</a>@endif
                <span class="ebd-tag ebd-tag--muted">{{ $formatLabel }}</span>
                @if($ebook->is_preorder)<span class="ebd-tag ebd-tag--warn">Pre-order{{ $ebook->preorder_release_date ? ' · ' . $ebook->preorder_release_date->format('d M Y') : '' }}</span>@endif
                @if(!$ebook->is_active)<span class="ebd-tag ebd-tag--warn">Hidden (only you can see)</span>@endif
            </div>

            <h1 class="ebd-title">{{ $ebook->title }}</h1>
            @if($ebook->subtitle)<p class="ebd-sub">{{ $ebook->subtitle }}</p>@endif

            <div class="ebd-by">
                <span>লেখক: @if($authorUrl)<a href="{{ $authorUrl }}">{{ $authorName }}</a>@else<strong>{{ $authorName }}</strong>@endif</span>
                @foreach($credits as $label => $name)<span>{{ $label }}: <strong>{{ $name }}</strong></span>@endforeach
                @if($pubName)<span>প্রকাশক: @if($pubUrl)<a href="{{ $pubUrl }}">{{ $pubName }}</a>@else<strong>{{ $pubName }}</strong>@endif</span>@endif
                @if($reviewCount)
                    <a class="ebd-rate" href="#reviews"><i class="fa-solid fa-star"></i>{{ number_format($avgRating, 1) }} <small>({{ $reviewCount }} রিভিউ)</small></a>
                @endif
            </div>

            {{-- buy / read --}}
            <div class="ebd-buy">
                @if($owns)
                    <div class="ebd-owned"><i class="fa-solid fa-circle-check"></i> {{ $isStaff ? 'লেখক / অ্যাডমিন এক্সেস' : 'আপনার লাইব্রেরিতে আছে' }}</div>
                    @if($progress > 0)
                        <div>
                            <div class="d-flex justify-content-between small text-secondary mb-1"><span>পড়া হয়েছে</span><strong>{{ $progress }}%</strong></div>
                            <div class="ebd-bar"><span style="width: {{ $progress }}%"></span></div>
                        </div>
                    @endif
                @else
                    <div class="ebd-price">
                        <b>{{ $isFree ? 'ফ্রি' : '৳' . number_format($price) }}</b>
                        @if($hasOff)<s>৳{{ number_format($regular) }}</s><span class="ebd-off">{{ $offPct }}% ছাড়</span>@endif
                    </div>
                @endif

                <div class="ebd-acts">
                    @if(!$hasFile)
                        <span class="btn btn-light disabled" aria-disabled="true">ফাইল শিগগিরই যুক্ত হবে</span>
                    @elseif($canRead)
                        <a href="{{ route('ebook.read', $S) }}" class="btn btn-primary"><i class="fa-solid fa-book-open-reader"></i> {{ $progress > 0 ? 'পড়া চালিয়ে যান' : 'পড়ুন' }}</a>
                        @if($isFree && !$owns)
                            <form action="{{ route('ebook.claim', $S) }}" method="POST">@csrf<button type="submit" class="btn btn-outline-dark"><i class="fa-solid fa-plus"></i> লাইব্রেরিতে যুক্ত করুন</button></form>
                        @endif
                        @if($canDownload)
                            <a href="{{ route('ebook.download', $S) }}" class="btn btn-outline-dark"><i class="fa-solid fa-download"></i> EPUB ডাউনলোড</a>
                        @endif
                    @else
                        <button type="button" class="btn btn-primary" data-eb-buy='@json($cartItem)'><i class="fa-solid fa-bag-shopping"></i> কিনুন</button>
                        <button type="button" class="btn btn-outline-dark" data-eb-cart='@json($cartItem)'><i class="fa-solid fa-cart-plus"></i> কার্টে রাখুন</button>
                        @if($hasSample)
                            <a href="{{ route('ebook.preview', $S) }}" class="btn btn-outline-secondary"><i class="fa-solid fa-eye"></i> নমুনা পড়ুন</a>
                        @endif
                    @endif
                </div>

                <div class="ebd-note">
                    @if(!$canRead)<span><i class="fa-solid fa-bolt text-success"></i> পেমেন্ট নিশ্চিত হলেই লাইব্রেরিতে যুক্ত হবে</span><span>বিকাশ · নগদ · কার্ড</span>@endif
                    <span><i class="fa-solid fa-mobile-screen"></i> ফোন, ট্যাব ও কম্পিউটারে পড়া যায়</span>
                    @if($kind === 'pdf')<span>PDF শুধু অনলাইনে পড়া যায়</span>@endif
                </div>
            </div>

            <div class="ebd-facts">
                <div class="ebd-fact"><span>Format</span><b>{{ $formatLabel }}</b></div>
                <div class="ebd-fact"><span>Pages</span><b>{{ $ebook->pages ? number_format($ebook->pages) : '—' }}</b></div>
                <div class="ebd-fact"><span>Language</span><b>বাংলা</b></div>
                <div class="ebd-fact"><span>Size</span><b>{{ $ebook->formatted_file_size ?: '—' }}</b></div>
            </div>
        </div>
    </section>

    @if($desc)
        <section class="ebd-sec">
            <h2>বইটি সম্পর্কে</h2>
            <div class="ebd-desc {{ mb_strlen($desc) > 700 ? 'is-clamped' : '' }}" id="ebdDesc">{{ $desc }}</div>
            @if(mb_strlen($desc) > 700)<button type="button" class="ebd-more" data-ebd-more aria-controls="ebdDesc" aria-expanded="false">আরও পড়ুন</button>@endif
        </section>
    @endif

    <section class="ebd-sec">
        <h2>বিস্তারিত</h2>
        <table class="ebd-specs">
            <tbody>
                <tr><th>শিরোনাম</th><td>{{ $ebook->title }}</td></tr>
                <tr><th>লেখক</th><td>{{ $authorName }}</td></tr>
                @foreach($credits as $label => $name)<tr><th>{{ $label }}</th><td>{{ $name }}</td></tr>@endforeach
                @if($pubName)<tr><th>প্রকাশক</th><td>{{ $pubName }}</td></tr>@endif
                @if($ebook->category)<tr><th>বিষয়</th><td>{{ $ebook->category->name }}</td></tr>@endif
                @if($ebook->isbn)<tr><th>ISBN</th><td>{{ $ebook->isbn }}</td></tr>@endif
                @if($ebook->pages)<tr><th>পৃষ্ঠা</th><td>{{ number_format($ebook->pages) }}</td></tr>@endif
                <tr><th>ফরম্যাট</th><td>{{ $formatLabel }}{{ $kind === 'epub' ? ' — লেখা ফোনের পর্দায় মানিয়ে যায়, ফন্ট বড়-ছোট করা যায়' : '' }}</td></tr>
                @if($ebook->formatted_file_size)<tr><th>ফাইল সাইজ</th><td>{{ $ebook->formatted_file_size }}</td></tr>@endif
                <tr><th>প্রকাশ</th><td>{{ optional($ebook->created_at)->format('d M Y') }}</td></tr>
            </tbody>
        </table>
    </section>

    @if($ebook->author)
        <section class="ebd-sec">
            <h2>লেখক</h2>
            <div class="ebd-author">
                <div class="ebd-avatar">@if($ebook->author->avatar_url ?? null)<img src="{{ $ebook->author->avatar_url }}" alt="" loading="lazy" onerror="this.remove()">@endif{{ mb_substr($authorName, 0, 1) }}</div>
                <div>
                    <h3>@if($authorUrl)<a href="{{ $authorUrl }}">{{ $authorName }}</a>@else{{ $authorName }}@endif</h3>
                    @if($ebook->author->bio)<p>{{ Str::limit(strip_tags($ebook->author->bio), 420) }}</p>@endif
                </div>
            </div>
            @if($byAuthor->isNotEmpty())
                <div class="idea-book-slider dz-track eb-track mt-3" id="ebdByAuthor">
                    @foreach($byAuthor as $eb)<div class="idea-slider-item eb-item">@include('ebook::frontend.partials.shelf-card', ['ebook' => $eb, 'userLibraryIds' => []])</div>@endforeach
                </div>
            @endif
        </section>
    @endif

    <section class="ebd-sec" id="reviews">
        <h2>রিভিউ <small>{{ $reviewCount }}</small></h2>
        <div class="ebd-reviews">
            <div class="ebd-score">
                @if($reviewCount)
                    <b>{{ number_format($avgRating, 1) }}</b>
                    <div class="ebd-stars" aria-hidden="true">@for($i = 1; $i <= 5; $i++)<i class="fa-solid fa-star {{ $i <= round($avgRating) ? '' : 'off' }}"></i>@endfor</div>
                    <small>{{ $reviewCount }} জন পাঠকের রেটিং</small>
                @else
                    <small>এখনো কোনো রিভিউ নেই</small>
                @endif
            </div>
            <div>
                @if(!$userReviewed)
                    <form class="ebd-form" id="ebdReview" novalidate>
                        @csrf
                        <input type="hidden" name="ebook_id" value="{{ $ebook->id }}">
                        <div class="ebd-hp" aria-hidden="true"><input type="text" name="review_hp_field" tabindex="-1" autocomplete="off"></div>
                        <div>
                            <div class="small fw-semibold mb-1">আপনার রেটিং</div>
                            <div class="ebd-pick" role="radiogroup" aria-label="Rating">
                                @for($i = 5; $i >= 1; $i--)
                                    <input type="radio" name="rating" id="ebdR{{ $i }}" value="{{ $i }}" @checked($i === 5)><label for="ebdR{{ $i }}" title="{{ $i }}/5"><i class="fa-solid fa-star"></i></label>
                                @endfor
                            </div>
                        </div>
                        @guest
                            <div class="row g-2">
                                <div class="col-sm-6"><input type="text" name="reviewer_name" class="form-control" placeholder="আপনার নাম" maxlength="100" required></div>
                                <div class="col-sm-6"><input type="text" name="reviewer_phone" class="form-control" placeholder="ফোন (ঐচ্ছিক)" maxlength="30"></div>
                            </div>
                        @endguest
                        <textarea name="comment" class="form-control" rows="3" maxlength="2000" placeholder="বইটি কেমন লাগল?" required></textarea>
                        <div class="d-flex align-items-center gap-3">
                            <button type="submit" class="btn btn-primary rounded-pill px-4">পাঠান</button>
                            <span class="small" data-ebd-msg role="status"></span>
                        </div>
                    </form>
                @endif
                <div id="ebdReviewList">
                    @forelse($reviews as $r)
                        @php $rn = $r->user?->name ?: ($r->reviewer_name ?: 'পাঠক'); $rr = max(1, min(5, (int) $r->rating)); @endphp
                        <article class="ebd-review">
                            <header>
                                <strong>{{ $rn }}</strong>
                                <span class="ebd-stars" aria-label="{{ $rr }}/5">@for($i = 1; $i <= 5; $i++)<i class="fa-solid fa-star {{ $i <= $rr ? '' : 'off' }}"></i>@endfor</span>
                            </header>
                            <p>{{ $r->comment }}</p>
                            <time datetime="{{ optional($r->created_at)->toIso8601String() }}">{{ optional($r->created_at)->diffForHumans() }}</time>
                        </article>
                    @empty
                        <p class="text-secondary mb-0" data-ebd-empty>প্রথম রিভিউটি আপনিই লিখুন।</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    @if($related->isNotEmpty())
        <section class="dz-row mt-4">
            <div class="dz-row__head">
                <h2 class="dz-row__title">@if($ebook->category)<a href="{{ route('ebook.index', ['category' => $ebook->category->slug]) }}">More in {{ $ebook->category->name }}</a>@else More e-books @endif</h2>
                <div class="dz-row__tools">
                    <button type="button" class="dz-arrow" onclick="scrollIdeaSlider('ebdRelated', -1)" aria-label="Prev"><i class="fa-solid fa-chevron-left"></i></button>
                    <button type="button" class="dz-arrow" onclick="scrollIdeaSlider('ebdRelated', 1)" aria-label="Next"><i class="fa-solid fa-chevron-right"></i></button>
                </div>
            </div>
            <div class="idea-book-slider dz-track eb-track" id="ebdRelated">
                @foreach($related as $eb)<div class="idea-slider-item eb-item">@include('ebook::frontend.partials.shelf-card', ['ebook' => $eb, 'userLibraryIds' => []])</div>@endforeach
            </div>
        </section>
    @endif
</div>
</div>

{{-- phones: the main action stays in reach --}}
@if($hasFile)
<div class="ebd-sticky">
    <div><strong>{{ $ebook->title }}</strong><span>{{ $owns ? 'Owned' : ($isFree ? 'ফ্রি' : '৳' . number_format($price)) }}</span></div>
    @if($canRead)
        <a href="{{ route('ebook.read', $S) }}" class="btn btn-primary">পড়ুন</a>
    @else
        <button type="button" class="btn btn-primary" data-eb-buy='@json($cartItem)'>কিনুন</button>
    @endif
</div>
@endif
@endsection

@push('scripts')
<script>
(function () {
    function put(item) {
        var cart = [];
        try { cart = JSON.parse(localStorage.getItem('idea_cart') || '[]'); } catch (e) {}
        var inCart = cart.some(function (c) { return String(c.id) === String(item.id); });
        if (!inCart) {
            if (typeof window.addToCartLive === 'function') window.addToCartLive(item);
            else { cart.push(item); localStorage.setItem('idea_cart', JSON.stringify(cart)); }
        }
        return inCart;
    }
    document.addEventListener('click', function (e) {
        var buy = e.target.closest('[data-eb-buy]'), add = e.target.closest('[data-eb-cart]');
        if (!buy && !add) return;
        var item = JSON.parse((buy || add).getAttribute(buy ? 'data-eb-buy' : 'data-eb-cart'));
        var already = put(item);
        if (buy) {
            if (typeof window.openCartDrawer === 'function') window.openCartDrawer({ checkout: true });
            else location.href = @json(route('cart'));
        } else if (already && typeof window.openCartDrawer === 'function') {
            window.openCartDrawer();
        }
    });

    var more = document.querySelector('[data-ebd-more]');
    if (more) more.addEventListener('click', function () {
        var d = document.getElementById('ebdDesc'), open = d.classList.toggle('is-clamped') === false;
        more.textContent = open ? 'কম দেখান' : 'আরও পড়ুন';
        more.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    var form = document.getElementById('ebdReview');
    if (form) form.addEventListener('submit', function (e) {
        e.preventDefault();
        var msg = form.querySelector('[data-ebd-msg]'), btn = form.querySelector('button[type=submit]');
        if (!form.comment.value.trim() || (form.reviewer_name && !form.reviewer_name.value.trim())) { msg.textContent = 'নাম ও মতামত লিখুন।'; msg.className = 'small text-danger'; return; }
        btn.disabled = true; msg.textContent = 'পাঠানো হচ্ছে…'; msg.className = 'small text-secondary';
        fetch(@json(route('reviews.store')), { method: 'POST', body: new FormData(form), headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(function (r) { return r.json().catch(function () { return {}; }).then(function (d) { return { ok: r.ok, d: d }; }); })
            .then(function (res) {
                btn.disabled = false;
                if (!res.ok || res.d.success === false) {
                    var err = res.d.message || (res.d.errors ? Object.values(res.d.errors)[0][0] : 'পাঠানো যায়নি।');
                    msg.textContent = err; msg.className = 'small text-danger'; return;
                }
                var stars = parseInt((form.querySelector('input[name=rating]:checked') || {}).value || 5, 10);
                var a = document.createElement('article'); a.className = 'ebd-review';
                var h = document.createElement('header'), n = document.createElement('strong'), s = document.createElement('span'), p = document.createElement('p');
                n.textContent = (res.d.review && res.d.review.reviewer_name) || (form.reviewer_name ? form.reviewer_name.value : 'আপনি');
                s.className = 'ebd-stars'; s.innerHTML = '<i class="fa-solid fa-star"></i>'.repeat(stars) + '<i class="fa-solid fa-star off"></i>'.repeat(5 - stars);
                p.textContent = form.comment.value.trim();
                h.append(n, s); a.append(h, p);
                var list = document.getElementById('ebdReviewList'), empty = list.querySelector('[data-ebd-empty]');
                if (empty) empty.remove();
                list.prepend(a);
                form.remove();
            })
            .catch(function () { btn.disabled = false; msg.textContent = 'নেটওয়ার্ক সমস্যা। আবার চেষ্টা করুন।'; msg.className = 'small text-danger'; });
    });
})();
</script>
@endpush
