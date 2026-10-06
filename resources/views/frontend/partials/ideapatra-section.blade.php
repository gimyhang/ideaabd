{{-- ══ SECTION: আইডিয়াপত্র (LITERARY BLOG - PHOTO CARDS, NO NUMBERS, NO ICONS) ═══════════════ --}}
@php
    $latestBlogPosts = $latestBlogPosts ?? collect();
    $mostReadBlogPosts = $mostReadBlogPosts ?? collect();
    $topHonorariumBlogPosts = $topHonorariumBlogPosts ?? collect();
    $blogCategories = $blogCategories ?? collect();

    // Curated high-res literary photo fallbacks
    $literaryPhotos = [
        'https://images.unsplash.com/photo-1457369804613-52c61a468e7d?auto=format&fit=crop&w=700&q=80',
        'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?auto=format&fit=crop&w=700&q=80',
        'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=700&q=80',
        'https://images.unsplash.com/photo-1476275466078-4007374efbbe?auto=format&fit=crop&w=700&q=80',
        'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?auto=format&fit=crop&w=700&q=80',
        'https://images.unsplash.com/photo-1516979187457-637abb4f9353?auto=format&fit=crop&w=700&q=80',
        'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?auto=format&fit=crop&w=700&q=80',
        'https://images.unsplash.com/photo-1495640388908-05fa85288e61?auto=format&fit=crop&w=700&q=80'
    ];
@endphp
<section class="ideapatra-literary-section mb-4">
    <div class="container">
        
        {{-- Section Header: টাইপোগ্রাফিক ও ফন্ট ফোকাস (নো আইকন, নো নম্বর) --}}
        <div class="ideapatra-hero-header">
            <div>
                <h2 class="ideapatra-hero-title">
                    আইডিয়াপত্র
                </h2>
                <p class="ideapatra-hero-subtitle">
                    মুক্তচিন্তার অসীম আকাশ
                </p>
            </div>
            
            <div class="d-flex align-items-center gap-2.5 flex-wrap">
                <a href="{{ route('author.posts.create') }}" class="btn btn-outline-dark btn-sm rounded-pill px-3.5 py-2 fw-bold" style="font-size: 0.96rem;">
                    লেখা পাঠান
                </a>
                <a href="{{ route('blog.index') }}" class="btn btn-primary btn-sm rounded-pill px-4 py-2 fw-bold shadow-xs" style="font-size: 0.96rem;">
                    সকল লেখা পড়ুন →
                </a>
            </div>
        </div>

        {{-- 3-Column Balanced Photo Cards Magazine Grid with Clear Thin-Line Column Dividers --}}
        <div class="ideapatra-grid-wrapper">
            <div class="row g-0 align-items-stretch ideapatra-grid-row">
                
                {{-- ══ 1st COLUMN: সর্বশেষ প্রকাশিত লেখা (NO NUMBER, NO ICONS) ══════ --}}
                <div class="col-lg-4 col-md-6 col-12 d-flex ideapatra-grid-col ideapatra-grid-col-1">
                    <div class="ideapatra-column-box w-100">
                        <div>
                            {{-- Minimalist Header --}}
                            <div class="ideapatra-box-header col-theme-latest">
                                <h6 class="ideapatra-box-header-title">
                                    সর্বশেষ প্রকাশিত লেখা
                                </h6>
                                <a href="{{ route('blog.index') }}" class="ideapatra-header-link">
                                    সব দেখুন →
                                </a>
                            </div>

                            @php
                                $leadPost = $latestBlogPosts->first();
                                $restLatest = $latestBlogPosts->slice(1, 3);
                            @endphp

                            @if($leadPost)
                                @php
                                    $leadImg = $leadPost->featured_image ?: $literaryPhotos[0];
                                    if (!str_starts_with($leadImg, 'http')) {
                                        $leadImg = asset('storage/' . ltrim($leadImg, '/'));
                                    }
                                    $leadCat = $leadPost->category?->name ?: 'সাহিত্য ও প্রবন্ধ';
                                    $leadAuthor = $leadPost->author?->name ?: ($leadPost->owner_name ?: 'আইডিয়া প্রকাশন');
                                    $readingTime = max(2, ceil(mb_strlen(strip_tags($leadPost->content ?? '')) / 500));
                                @endphp
                                
                                {{-- Featured Lead Photo Card --}}
                                <div class="ideapatra-lead-photocard">
                                    <a href="{{ route('blog.show', $leadPost->slug) }}" class="d-block ideapatra-photocard-img">
                                        <img src="{{ $leadImg }}" alt="{{ $leadPost->title }}">
                                        <div class="position-absolute top-0 start-0 m-2.5">
                                            <span class="badge bg-primary text-white fw-bold px-2.5 py-1 rounded-pill" style="font-size: 12.5px;">
                                                {{ $leadCat }}
                                            </span>
                                        </div>
                                        <div class="position-absolute bottom-0 end-0 m-2.5">
                                            <span class="badge bg-dark bg-opacity-75 text-white fw-medium px-2 py-0.5 rounded-pill" style="font-size: 12px; backdrop-filter: blur(4px);">
                                                @bn($readingTime) মি. পাঠ
                                            </span>
                                        </div>
                                    </a>

                                    <div class="ideapatra-photocard-body">
                                        <h5 class="ideapatra-photocard-title">
                                            <a href="{{ route('blog.show', $leadPost->slug) }}">
                                                {{ $leadPost->title }}
                                            </a>
                                        </h5>
                                        @if($leadPost->excerpt)
                                            <p class="ideapatra-photocard-excerpt">
                                                {{ Str::limit(strip_tags($leadPost->excerpt), 85) }}
                                            </p>
                                        @endif
                                        <div class="ideapatra-photocard-footer">
                                            <span class="ideapatra-photocard-author text-truncate" style="max-width: 65%;">
                                                {{ $leadAuthor }}
                                            </span>
                                            <span>
                                                {{ $leadPost->published_at ? $leadPost->published_at->format('d M, Y') : $leadPost->created_at->format('d M, Y') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Compact Photo Cards with Thin-Line Separation --}}
                        <div class="ideapatra-compact-list d-flex flex-column gap-2 mt-auto pt-3 border-top">
                            @foreach($restLatest as $rIdx => $rPost)
                                @php
                                    $rImg = $rPost->featured_image ?: $literaryPhotos[($rIdx + 1) % count($literaryPhotos)];
                                    if (!str_starts_with($rImg, 'http')) {
                                        $rImg = asset('storage/' . ltrim($rImg, '/'));
                                    }
                                    $rAuthor = $rPost->author?->name ?: ($rPost->owner_name ?: 'আইডিয়া প্রকাশন');
                                    $rCat = $rPost->category?->name ?: 'নিবন্ধ';
                                @endphp
                                <a href="{{ route('blog.show', $rPost->slug) }}" class="ideapatra-compact-photocard">
                                    <div class="ideapatra-photocard-thumb">
                                        <img src="{{ $rImg }}" alt="{{ $rPost->title }}">
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden min-w-0">
                                        <div class="d-flex align-items-center justify-content-between mb-0.5">
                                            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-0.5" style="font-size: 11.5px;">{{ $rCat }}</span>
                                            <span class="text-muted small" style="font-size: 12px;">{{ $rPost->published_at ? $rPost->published_at->format('d M') : $rPost->created_at->format('d M') }}</span>
                                        </div>
                                        <h6 class="ideapatra-compact-headline">{{ $rPost->title }}</h6>
                                        <div class="text-muted small text-truncate" style="font-size: 13px;">{{ $rAuthor }}</div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- ══ 2nd COLUMN: পঠিত ও নির্বাচিত সেরা লেখা (NO NUMBERS, PHOTO CARDS) ══ --}}
                <div class="col-lg-4 col-md-6 col-12 d-flex ideapatra-grid-col ideapatra-grid-col-2">
                    <div class="ideapatra-column-box w-100">
                        <div>
                            {{-- Minimalist Header with Clean Tab Switcher --}}
                            <div class="ideapatra-box-header col-theme-read">
                                <h6 class="ideapatra-box-header-title">
                                    পঠিত ও নির্বাচিত সেরা
                                </h6>
                                <div class="d-inline-flex align-items-center gap-1 bg-white bg-opacity-10 p-0.5 rounded-pill">
                                    <button type="button" class="ideapatra-clean-tab active" id="tabBtnMostRead" onclick="switchIdeapatraTab('mostread')">
                                        পঠিত
                                    </button>
                                    <button type="button" class="ideapatra-clean-tab" id="tabBtnFeatured" onclick="switchIdeapatraTab('featured')">
                                        সম্মানিপ্রাপ্ত
                                    </button>
                                </div>
                            </div>

                            {{-- TAB 1: সর্বাধিক পঠিত লেখা (Lead Photo Card + Compact Photo Cards) --}}
                            <div id="ideapatraSecMostRead" class="d-flex flex-column justify-content-between h-100">
                                @php
                                    $leadReadPost = $mostReadBlogPosts->first();
                                    $restReadPosts = $mostReadBlogPosts->slice(1, 3);
                                @endphp

                                @if($leadReadPost)
                                    @php
                                        $mrImg = $leadReadPost->featured_image ?: $literaryPhotos[2];
                                        if (!str_starts_with($mrImg, 'http')) {
                                            $mrImg = asset('storage/' . ltrim($mrImg, '/'));
                                        }
                                        $mrAuthor = $leadReadPost->author?->name ?: ($leadReadPost->owner_name ?: 'আইডিয়া প্রকাশন');
                                        $mrCat = $leadReadPost->category?->name ?: 'প্রবন্ধ';
                                        $mrTime = max(2, ceil(mb_strlen(strip_tags($leadReadPost->content ?? '')) / 500));
                                    @endphp
                                    <div class="ideapatra-lead-photocard">
                                        <a href="{{ route('blog.show', $leadReadPost->slug) }}" class="d-block ideapatra-photocard-img">
                                            <img src="{{ $mrImg }}" alt="{{ $leadReadPost->title }}">
                                            <div class="position-absolute top-0 start-0 m-2.5">
                                                <span class="badge bg-primary text-white fw-bold px-2.5 py-1 rounded-pill" style="font-size: 12.5px;">
                                                    {{ $mrCat }}
                                                </span>
                                            </div>
                                            <div class="position-absolute bottom-0 end-0 m-2.5">
                                                <span class="badge bg-dark bg-opacity-75 text-white fw-medium px-2 py-0.5 rounded-pill" style="font-size: 12px; backdrop-filter: blur(4px);">
                                                    @bn($leadReadPost->view_count ?: 45) বার পঠিত
                                                </span>
                                            </div>
                                        </a>
                                        <div class="ideapatra-photocard-body">
                                            <h5 class="ideapatra-photocard-title">
                                                <a href="{{ route('blog.show', $leadReadPost->slug) }}">
                                                    {{ $leadReadPost->title }}
                                                </a>
                                            </h5>
                                            @if($leadReadPost->excerpt)
                                                <p class="ideapatra-photocard-excerpt">
                                                    {{ Str::limit(strip_tags($leadReadPost->excerpt), 85) }}
                                                </p>
                                            @endif
                                            <div class="ideapatra-photocard-footer">
                                                <span class="ideapatra-photocard-author text-truncate" style="max-width: 65%;">
                                                    {{ $mrAuthor }}
                                                </span>
                                                <span>
                                                    {{ $leadReadPost->published_at ? $leadReadPost->published_at->format('d M, Y') : $leadReadPost->created_at->format('d M, Y') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Compact Photo Cards for Column 2 with Thin-Line Separation --}}
                                <div class="ideapatra-compact-list d-flex flex-column gap-2 mt-auto pt-3 border-top">
                                    @foreach($restReadPosts as $mIdx => $mPost)
                                        @php
                                            $mImg = $mPost->featured_image ?: $literaryPhotos[($mIdx + 3) % count($literaryPhotos)];
                                            if (!str_starts_with($mImg, 'http')) {
                                                $mImg = asset('storage/' . ltrim($mImg, '/'));
                                            }
                                            $mAuthor = $mPost->author?->name ?: ($mPost->owner_name ?: 'আইডিয়া প্রকাশন');
                                            $mCat = $mPost->category?->name ?: 'প্রবন্ধ';
                                        @endphp
                                        <a href="{{ route('blog.show', $mPost->slug) }}" class="ideapatra-compact-photocard">
                                            <div class="ideapatra-photocard-thumb">
                                                <img src="{{ $mImg }}" alt="{{ $mPost->title }}">
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden min-w-0">
                                                <div class="d-flex align-items-center justify-content-between mb-0.5">
                                                    <span class="badge bg-light text-muted border rounded-pill px-2 py-0.5" style="font-size: 11.5px;">{{ $mCat }}</span>
                                                    <span class="text-secondary small fw-medium" style="font-size: 12px;">
                                                        @bn($mPost->view_count ?: 30) বার পঠিত
                                                    </span>
                                                </div>
                                                <h6 class="ideapatra-compact-headline">{{ $mPost->title }}</h6>
                                                <div class="d-flex align-items-center justify-content-between text-muted small" style="font-size: 13px;">
                                                    <span class="text-truncate">{{ $mAuthor }}</span>
                                                    <span class="text-primary fw-semibold" style="font-size: 12.5px;">পড়ুন →</span>
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>

                            {{-- TAB 2: সম্মানিপ্রাপ্ত / নির্বাচিত সেরা লেখা (PHOTO CARDS, NO NUMBERS) --}}
                            <div id="ideapatraSecFeatured" class="d-flex flex-column justify-content-between h-100" style="display: none !important;">
                                @php
                                    $leadHonPost = $topHonorariumBlogPosts->first();
                                    $restHonPosts = $topHonorariumBlogPosts->slice(1, 3);
                                @endphp

                                @if($leadHonPost)
                                    @php
                                        $lhImg = $leadHonPost->featured_image ?: $literaryPhotos[4];
                                        if (!str_starts_with($lhImg, 'http')) {
                                            $lhImg = asset('storage/' . ltrim($lhImg, '/'));
                                        }
                                        $lhAuthor = $leadHonPost->author?->name ?: ($leadHonPost->owner_name ?: 'আইডিয়া প্রকাশন');
                                        $lhHonorarium = (float)($leadHonPost->honorariums_sum_amount ?? 0);
                                        $lhCat = $leadHonPost->category?->name ?: 'সাহিত্য';
                                    @endphp
                                    <div class="ideapatra-lead-photocard">
                                        <a href="{{ route('blog.show', $leadHonPost->slug) }}" class="d-block ideapatra-photocard-img">
                                            <img src="{{ $lhImg }}" alt="{{ $leadHonPost->title }}">
                                            <div class="position-absolute top-0 start-0 m-2.5">
                                                <span class="badge bg-success text-white fw-bold px-2.5 py-1 rounded-pill" style="font-size: 12.5px;">
                                                    {{ $lhHonorarium > 0 ? '৳' . number_format($lhHonorarium, 0) . ' সম্মানি' : 'সম্মানিপ্রাপ্ত' }}
                                                </span>
                                            </div>
                                        </a>
                                        <div class="ideapatra-photocard-body">
                                            <h5 class="ideapatra-photocard-title">
                                                <a href="{{ route('blog.show', $leadHonPost->slug) }}">
                                                    {{ $leadHonPost->title }}
                                                </a>
                                            </h5>
                                            @if($leadHonPost->excerpt)
                                                <p class="ideapatra-photocard-excerpt">
                                                    {{ Str::limit(strip_tags($leadHonPost->excerpt), 85) }}
                                                </p>
                                            @endif
                                            <div class="ideapatra-photocard-footer">
                                                <span class="ideapatra-photocard-author text-truncate" style="max-width: 65%;">
                                                    {{ $lhAuthor }}
                                                </span>
                                                <span>
                                                    {{ $leadHonPost->published_at ? $leadHonPost->published_at->format('d M, Y') : $leadHonPost->created_at->format('d M, Y') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Compact Photo Cards for Tab 2 with Thin-Line Separation --}}
                                <div class="ideapatra-compact-list d-flex flex-column gap-2 mt-auto pt-3 border-top">
                                    @foreach($restHonPosts as $hIdx => $hPost)
                                        @php
                                            $hImg = $hPost->featured_image ?: $literaryPhotos[($hIdx + 5) % count($literaryPhotos)];
                                            if (!str_starts_with($hImg, 'http')) {
                                                $hImg = asset('storage/' . ltrim($hImg, '/'));
                                            }
                                            $hAuthor = $hPost->author?->name ?: ($hPost->owner_name ?: 'আইডিয়া প্রকাশন');
                                            $hHonorarium = (float)($hPost->honorariums_sum_amount ?? 0);
                                            $hCat = $hPost->category?->name ?: 'সাহিত্য';
                                        @endphp
                                        <a href="{{ route('blog.show', $hPost->slug) }}" class="ideapatra-compact-photocard">
                                            <div class="ideapatra-photocard-thumb">
                                                <img src="{{ $hImg }}" alt="{{ $hPost->title }}">
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden min-w-0">
                                                <div class="d-flex align-items-center justify-content-between mb-0.5">
                                                    <span class="badge bg-success-subtle text-success rounded-pill fw-semibold px-2 py-0.5" style="font-size: 11.5px;">
                                                        {{ $hHonorarium > 0 ? '৳' . number_format($lhHonorarium, 0) . ' সম্মানি' : 'সম্মানিপ্রাপ্ত' }}
                                                    </span>
                                                    <span class="text-muted small" style="font-size: 12px;">{{ $hCat }}</span>
                                                </div>
                                                <h6 class="ideapatra-compact-headline">{{ $hPost->title }}</h6>
                                                <div class="d-flex align-items-center justify-content-between text-muted small" style="font-size: 13px;">
                                                    <span class="text-truncate">{{ $hAuthor }}</span>
                                                    <span class="text-primary fw-semibold" style="font-size: 12.5px;">পড়ুন →</span>
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ══ 3rd COLUMN: বিষয়ভিত্তিক সাহিত্য ও সাময়িকী (PHOTO CARDS, NO NUMBERS) ══ --}}
                <div class="col-lg-4 col-md-12 col-12 d-flex ideapatra-grid-col ideapatra-grid-col-3">
                    <div class="ideapatra-column-box w-100">
                        <div>
                            {{-- Minimalist Header --}}
                            <div class="ideapatra-box-header col-theme-category">
                                <h6 class="ideapatra-box-header-title">
                                    বিষয়ভিত্তিক সাহিত্য ও সাময়িকী
                                </h6>
                                <a href="{{ route('blog.index') }}" class="ideapatra-header-link">
                                    সব বিষয় →
                                </a>
                            </div>

                            @php
                                $leadCat = $blogCategories->first();
                                $restCats = $blogCategories->slice(1, 4);
                                $leadCatPost = $leadCat?->posts?->first();
                            @endphp

                            {{-- Lead Topic Photo Card --}}
                            @if($leadCat)
                                @php
                                    $lcImg = $leadCatPost?->featured_image ?: $literaryPhotos[6];
                                    if (!str_starts_with($lcImg, 'http')) {
                                        $lcImg = asset('storage/' . ltrim($lcImg, '/'));
                                    }
                                @endphp
                                <div class="ideapatra-lead-photocard">
                                    <a href="{{ route('blog.category', $leadCat->slug) }}" class="d-block ideapatra-photocard-img">
                                        <img src="{{ $lcImg }}" alt="{{ $leadCat->name }}">
                                        <div class="position-absolute top-0 start-0 m-2.5">
                                            <span class="badge bg-success text-white fw-bold px-2.5 py-1 rounded-pill" style="font-size: 12.5px;">
                                                {{ $leadCat->name }}
                                            </span>
                                        </div>
                                        <div class="position-absolute bottom-0 end-0 m-2.5">
                                            <span class="badge bg-dark bg-opacity-75 text-white fw-medium px-2 py-0.5 rounded-pill" style="font-size: 12px; backdrop-filter: blur(4px);">
                                                @bn($leadCat->posts_count)টি লেখা
                                            </span>
                                        </div>
                                    </a>
                                    @if($leadCatPost)
                                        <div class="ideapatra-photocard-body">
                                            <h5 class="ideapatra-photocard-title">
                                                <a href="{{ route('blog.show', $leadCatPost->slug) }}">
                                                    {{ $leadCatPost->title }}
                                                </a>
                                            </h5>
                                            <div class="ideapatra-photocard-footer">
                                                <span class="ideapatra-photocard-author text-truncate">
                                                    {{ $leadCatPost->author?->name ?: ($leadCatPost->owner_name ?: 'আইডিয়া প্রকাশন') }}
                                                </span>
                                                <span class="text-primary fw-semibold" style="font-size: 13px;">পড়ুন →</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            {{-- Compact Topic Photo Cards with Thin-Line Separation --}}
                            <div class="ideapatra-compact-list d-flex flex-column gap-2 mt-auto pt-3 border-top">
                                @foreach($restCats as $cIdx => $bCat)
                                    @php
                                        $samplePost = $bCat->posts?->first();
                                        $cImg = $samplePost?->featured_image ?: $literaryPhotos[($cIdx + 1) % count($literaryPhotos)];
                                        if (!str_starts_with($cImg, 'http')) {
                                            $cImg = asset('storage/' . ltrim($cImg, '/'));
                                        }
                                    @endphp
                                    <a href="{{ route('blog.category', $bCat->slug) }}" class="ideapatra-compact-photocard">
                                        <div class="ideapatra-photocard-thumb">
                                            <img src="{{ $cImg }}" alt="{{ $bCat->name }}">
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden min-w-0">
                                            <div class="d-flex align-items-center justify-content-between mb-0.5">
                                                <span class="fw-bold text-dark text-truncate" style="font-size: 1.02rem;">{{ $bCat->name }}</span>
                                                <span class="badge bg-light text-muted border rounded-pill px-2 py-0.5" style="font-size: 11.5px;">@bn($bCat->posts_count)টি লেখা</span>
                                            </div>
                                            @if($samplePost)
                                                <div class="text-secondary small text-truncate" style="font-size: 13.5px; line-height: 1.4;">
                                                    {{ $samplePost->title }}
                                                </div>
                                            @else
                                                <div class="text-muted small" style="font-size: 13px;">লেখাগুলো ব্রাউজ করুন</div>
                                            @endif
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        <div class="mt-3 pt-2 text-center">
                            <a href="{{ route('blog.index') }}" class="btn btn-outline-primary btn-sm rounded-pill w-100 fw-bold py-2" style="font-size: 0.98rem;">
                                সকল বিষয় ও সাময়িকী ব্রাউজ করুন →
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
