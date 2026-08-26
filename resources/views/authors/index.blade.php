@extends('layouts.app')

@section('title', 'সম্মানিত লেখক ও গবেষক ডিরেক্টরি — আইডিয়া প্রকাশন')

@section('content')
<div class="container py-4 mb-5">

    {{-- ========================================================================= --}}
    {{-- 1. DYNAMIC HERO SEARCH & SPOTLIGHT BANNER                                 --}}
    {{-- ========================================================================= --}}
    <div class="card p-4 p-md-5 mb-4 border-0 shadow-lg rounded-4 position-relative overflow-hidden text-white hero-author-banner" 
         style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 40%, #312e81 80%, #4338ca 100%);">
        
        {{-- Background Artistic Icon --}}
        <div class="position-absolute end-0 bottom-0 opacity-10 d-none d-lg-block pe-4 pb-2" style="pointer-events: none;">
            <i class="fa-solid fa-feather-pointed" style="font-size: 16rem;"></i>
        </div>

        <div class="position-relative z-1" style="max-width: 720px;">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <span class="badge bg-warning text-dark fw-bold px-3 py-1.5 rounded-pill shadow-sm">
                    <i class="fas fa-pen-fancy me-1"></i>লেখক ও গবেষক ডিরেক্টরি
                </span>
                <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-1.5 shadow-sm small">
                    <i class="fas fa-book-open me-1"></i>@bn($stats['total_authors'] ?? 0) জন লেখক ও @bn($stats['total_books'] ?? 0)টি ক্যাটালগ বই
                </span>
            </div>

            <h1 class="fw-bold mb-2 text-white" style="font-size: clamp(1.35rem, 4.5vw, 2.2rem);">আমাদের সম্মানিত লেখকগণ</h1>
            <p class="fs-6 opacity-90 mb-4" style="line-height: 1.6; font-size: clamp(0.85rem, 2.8vw, 1rem) !important;">
                দেশ-বিদেশের প্রথিতযশা ও উদীয়মান লেখকদের পূর্ণাঙ্গ প্রোফাইল, জীবন ও সাহিত্য পরিচিতি এবং প্রকাশিত সকল বইয়ের তালিকা অন্বেষণ করুন।
            </p>
            
            {{-- Live Search Form --}}
            <form action="{{ route('authors.index') }}" method="GET" class="position-relative mb-3" id="authorSearchForm">
                <div class="input-group shadow-lg rounded-pill overflow-hidden bg-white p-1 border border-2 border-white">
                    <span class="input-group-text bg-transparent border-0 text-muted ps-2.5 ps-sm-3">
                        <i class="fas fa-search fs-6 text-primary"></i>
                    </span>
                    <input type="text" name="q" id="authorSearchInput" value="{{ request('q') }}" 
                           class="form-control border-0 shadow-none ps-1.5 ps-sm-2 text-dark" 
                           placeholder="লেখকের নাম, বইয়ের নাম বা বিষয় খুঁজুন..."
                           autocomplete="off"
                           style="font-size: 0.9rem;">
                    @if(request('q'))
                        <a href="{{ route('authors.index') }}" class="btn btn-link text-muted border-0 p-1 me-1" title="মুছুন">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                    <button type="submit" class="btn btn-primary rounded-pill px-3 px-sm-4 fw-bold shadow-xs d-inline-flex align-items-center gap-1">
                        <i class="fas fa-magnifying-glass"></i>
                        <span class="d-none d-sm-inline">খুঁজুন</span>
                    </button>
                </div>
            </form>

            {{-- Quick Filter Chips (Enhanced Contrast & Mobile Responsive) --}}
            <div class="d-flex flex-wrap align-items-center gap-1.5 pt-1">
                <span class="small opacity-85 me-1 text-white fw-semibold" style="font-size: 12px;">জনপ্রিয় ফিল্টার:</span>
                <a href="{{ route('authors.index', ['filter' => 'most_books']) }}" 
                   class="badge rounded-pill text-decoration-none px-2.5 py-1.5 d-inline-flex align-items-center gap-1 shadow-2xs {{ request('filter') === 'most_books' ? 'bg-warning text-dark fw-bold' : 'bg-dark bg-opacity-40 text-white border border-white border-opacity-30' }}"
                   style="font-size: 11.5px;">
                    <i class="fas fa-trophy text-warning"></i>
                    <span>শীর্ষ লেখক</span>
                </a>
                <a href="{{ route('authors.index', ['filter' => 'verified']) }}" 
                   class="badge rounded-pill text-decoration-none px-2.5 py-1.5 d-inline-flex align-items-center gap-1 shadow-2xs {{ request('filter') === 'verified' ? 'bg-info text-dark fw-bold' : 'bg-dark bg-opacity-40 text-white border border-white border-opacity-30' }}"
                   style="font-size: 11.5px;">
                    <i class="fas fa-circle-check text-info"></i>
                    <span>ভেরিফাইড লেখক</span>
                </a>
                <a href="{{ route('authors.index', ['filter' => 'recent_active']) }}" 
                   class="badge rounded-pill text-decoration-none px-2.5 py-1.5 d-inline-flex align-items-center gap-1 shadow-2xs {{ request('filter') === 'recent_active' ? 'bg-success text-white fw-bold' : 'bg-dark bg-opacity-40 text-white border border-white border-opacity-30' }}"
                   style="font-size: 11.5px;">
                    <i class="fas fa-bolt text-success"></i>
                    <span>সাম্প্রতিক সক্রিয়</span>
                </a>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 2. ALPHABETICAL & BENGALI CHARACTER FILTER BAR                            --}}
    {{-- ========================================================================= --}}
    <div class="card p-3 mb-4 border-0 shadow-sm rounded-4 bg-white">
        <div class="d-flex align-items-center gap-2 mb-2 pb-2 border-bottom">
            <i class="fa-solid fa-arrow-down-a-z text-primary fs-5"></i>
            <span class="small fw-bold text-dark">বর্ণানুক্রমিক ফিল্টার:</span>
            
            @if(request('letter') || request('q') || request('filter') || request('category_id'))
                <a href="{{ route('authors.index') }}" class="btn btn-sm btn-link text-danger text-decoration-none ms-auto p-0 small fw-bold">
                    <i class="fa-solid fa-rotate-left me-1"></i>সকল ফিল্টার মুছুন
                </a>
            @endif
        </div>
        
        {{-- Bengali Letters --}}
        <div class="d-flex flex-wrap gap-1 mb-2">
            <a href="{{ route('authors.index', array_merge(request()->except(['page', 'letter']))) }}" 
               class="btn btn-sm {{ !request('letter') ? 'btn-primary' : 'btn-light border' }} rounded-pill px-3 py-1 fw-bold small">
                সকল
            </a>
            @foreach(['অ','আ','ই','উ','এ','ও','ক','খ','গ','ঘ','চ','ছ','জ','ঝ','ট','ঠ','ড','ঢ','ত','থ','দ','ধ','ন','প','ফ','ব','ভ','ম','য','র','ল','শ','ষ','স','হ'] as $bnChar)
                <a href="{{ route('authors.index', array_merge(request()->except(['page']), ['letter' => $bnChar])) }}" 
                   class="btn btn-sm {{ request('letter') === $bnChar ? 'btn-primary' : 'btn-light border' }} rounded-pill px-2 py-1 small fw-semibold transition-all"
                   style="min-width: 32px;">
                    {{ $bnChar }}
                </a>
            @endforeach
        </div>

        {{-- English Letters --}}
        <div class="d-flex flex-wrap gap-1 pt-2 border-top">
            <span class="small text-muted align-self-center me-1" style="font-size: 0.75rem;">A-Z:</span>
            @foreach(range('A','Z') as $char)
                <a href="{{ route('authors.index', array_merge(request()->except(['page']), ['letter' => $char])) }}" 
                   class="btn btn-sm {{ request('letter') === $char ? 'btn-primary' : 'btn-light border' }} rounded-pill px-2 py-1 small"
                   style="min-width: 30px; font-size: 0.75rem;">
                    {{ $char }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 3. MAIN CONTENT LAYOUT                                                    --}}
    {{-- ========================================================================= --}}
    <div class="row g-4">
        {{-- Authors Main Column --}}
        <div class="col-lg-8 col-xl-9">
            
            {{-- Toolbar: Results count & Sort/View Switcher --}}
            <div class="card p-3 mb-4 border-0 shadow-sm rounded-4 bg-white">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="fw-bold text-dark mb-0">লেখক তালিকা</h5>
                        @if(isset($authors) && $authors instanceof \Illuminate\Pagination\LengthAwarePaginator)
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1">
                                @bn($authors->total()) জন লেখক
                            </span>
                        @endif
                    </div>

                    <div class="d-flex align-items-center gap-2 ms-sm-auto">
                        <form method="GET" action="{{ route('authors.index') }}" class="d-flex align-items-center gap-2" id="sortForm">
                            @if(request('q')) <input type="hidden" name="q" value="{{ request('q') }}"> @endif
                            @if(request('letter')) <input type="hidden" name="letter" value="{{ request('letter') }}"> @endif
                            @if(request('filter')) <input type="hidden" name="filter" value="{{ request('filter') }}"> @endif
                            @if(request('category_id')) <input type="hidden" name="category_id" value="{{ request('category_id') }}"> @endif
                            
                            <label for="sortSelect" class="small text-muted text-nowrap fw-semibold">সাজান:</label>
                            <select name="sort" id="sortSelect" class="form-select form-select-sm rounded-pill border shadow-xs px-3" onchange="this.form.submit()">
                                <option value="popular" @selected(request('sort') === 'popular' || !request('sort'))>জনপ্রিয় ও সর্বাধিক বই</option>
                                <option value="name_asc" @selected(request('sort') === 'name_asc')>নামের ক্রমানুসারে (ক-হ / A-Z)</option>
                                <option value="name_desc" @selected(request('sort') === 'name_desc')>নামের ক্রমানুসারে (হ-ক / Z-A)</option>
                                <option value="latest" @selected(request('sort') === 'latest')>সর্বশেষ যোগকৃত</option>
                            </select>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Authors Grid --}}
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-2 row-cols-xl-3 g-3 g-xl-4 mb-4">
                @forelse($authors as $author)
                    <div class="col">
                        @include('components.author-card', ['author' => $author])
                    </div>
                @empty
                    <div class="col-12 w-100">
                        <div class="card p-5 text-center border-0 shadow-sm rounded-4 bg-white">
                            <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px;">
                                <i class="fa-solid fa-user-xmark fs-2 text-muted opacity-50"></i>
                            </div>
                            <h5 class="fw-bold text-dark">কোনো লেখক পাওয়া যায়নি</h5>
                            <p class="text-muted small mb-3">অনুগ্রহ করে ভিন্ন কোনো নাম বা বর্ণ দিয়ে চেষ্টা করুন।</p>
                            <a href="{{ route('authors.index') }}" class="btn btn-primary btn-sm rounded-pill px-4 align-self-center fw-bold">
                                সকল লেখক দেখুন
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if(isset($authors) && $authors instanceof \Illuminate\Pagination\LengthAwarePaginator && $authors->hasPages())
                <div class="d-flex justify-content-center mb-5">
                    {{ $authors->links() }}
                </div>
            @endif
        </div>

        {{-- ===================================================================== --}}
        {{-- 4. SIDEBAR WIDGETS                                                    --}}
        {{-- ===================================================================== --}}
        <div class="col-lg-4 col-xl-3">
            <div class="d-flex flex-column gap-4">

                {{-- Quick Filter Box --}}
                <div class="card p-3 border-0 shadow-sm rounded-4 bg-white">
                    <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center justify-content-between">
                        <span><i class="fa-solid fa-sliders text-primary me-2"></i>ফিল্টার মেনু</span>
                    </h6>
                    <div class="d-flex flex-column gap-2">
                        <a href="{{ route('authors.index', array_merge(request()->except('page'), ['filter' => 'most_books'])) }}" 
                           class="btn btn-sm text-start {{ request('filter') === 'most_books' ? 'btn-primary' : 'btn-light border' }} rounded-3 fw-semibold d-flex align-items-center justify-content-between">
                            <span><i class="fa-solid fa-trophy me-2 text-warning"></i>সর্বোচ্চ সংখ্যক বই</span>
                            <i class="fas fa-chevron-right small opacity-50"></i>
                        </a>
                        <a href="{{ route('authors.index', array_merge(request()->except('page'), ['filter' => 'verified'])) }}" 
                           class="btn btn-sm text-start {{ request('filter') === 'verified' ? 'btn-primary' : 'btn-light border' }} rounded-3 fw-semibold d-flex align-items-center justify-content-between">
                            <span><i class="fa-solid fa-circle-check me-2 text-info"></i>যাচাইকৃত লেখক</span>
                            <i class="fas fa-chevron-right small opacity-50"></i>
                        </a>
                        <a href="{{ route('authors.index', array_merge(request()->except('page'), ['filter' => 'recent_active'])) }}" 
                           class="btn btn-sm text-start {{ request('filter') === 'recent_active' ? 'btn-primary' : 'btn-light border' }} rounded-3 fw-semibold d-flex align-items-center justify-content-between">
                            <span><i class="fa-solid fa-bolt me-2 text-success"></i>সাম্প্রতিক সক্রিয়</span>
                            <i class="fas fa-chevron-right small opacity-50"></i>
                        </a>
                    </div>
                </div>

                {{-- Top Authors Ranking Widget --}}
                @if(isset($topAuthors) && $topAuthors->isNotEmpty())
                <div class="card p-3 border-0 shadow-sm rounded-4 bg-white">
                    <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center">
                        <i class="fa-solid fa-crown text-warning me-2 fs-5"></i>
                        <span>শীর্ষ লেখক র‍্যাঙ্কিং</span>
                    </h6>
                    <div class="d-flex flex-column gap-2.5">
                        @foreach($topAuthors as $idx => $top)
                            <a href="{{ route('authors.show', $top->slug ?: $top->id) }}" class="d-flex align-items-center gap-2.5 p-2 rounded-3 text-decoration-none text-dark hover-bg-light transition-all border border-transparent hover-border">
                                <span class="badge rounded-circle p-0 d-flex align-items-center justify-content-center fw-bold {{ $idx === 0 ? 'bg-warning text-dark' : ($idx === 1 ? 'bg-secondary text-white' : ($idx === 2 ? 'bg-bronze text-white' : 'bg-light text-muted border')) }}" 
                                      style="width: 22px; height: 22px; font-size: 11px;">
                                    @bn($idx + 1)
                                </span>
                                
                                <div class="rounded-circle overflow-hidden shadow-xs flex-shrink-0 position-relative border" style="width: 40px; height: 40px; background: {{ $top->avatar_bg_color ?? '#e2e8f0' }};">
                                    @if($top->avatar_url)
                                        <img src="{{ $top->avatar_url }}" class="w-100 h-100 object-fit-cover"
                                             onerror="this.style.display='none'; this.nextElementSibling.classList.remove('d-none');">
                                        <div class="w-100 h-100 d-none d-flex align-items-center justify-content-center text-white fw-bold small" style="background: {{ $top->avatar_bg_color ?? '#4f46e5' }};">
                                            {{ $top->initials ?? mb_substr($top->name, 0, 1) }}
                                        </div>
                                    @else
                                        <div class="w-100 h-100 d-flex align-items-center justify-content-center text-white fw-bold small">
                                            {{ $top->initials ?? mb_substr($top->name, 0, 1) }}
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="min-w-0 flex-grow-1">
                                    <div class="fw-bold small text-truncate text-dark">{{ $top->name }}</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">@bn($top->books_count ?? 0)টি প্রকাশিত বই</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Popular Book Categories Filter --}}
                @if(isset($popularCategories) && $popularCategories->isNotEmpty())
                <div class="card p-3 border-0 shadow-sm rounded-4 bg-white">
                    <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center">
                        <i class="fa-solid fa-layer-group text-primary me-2"></i>
                        <span>বইয়ের ক্যাটাগরি</span>
                    </h6>
                    <div class="d-flex flex-wrap gap-1.5">
                        @foreach($popularCategories as $pCat)
                            <a href="{{ route('authors.index', array_merge(request()->except('page'), ['category_id' => $pCat->id])) }}" 
                               class="badge text-decoration-none rounded-pill px-2.5 py-1.5 {{ request('category_id') == $pCat->id ? 'bg-primary text-white' : 'bg-light text-dark border hover-primary' }}"
                               style="font-size: 0.75rem;">
                                {{ $pCat->name }} <span class="opacity-75">(@bn($pCat->total_books))</span>
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Author Registration CTA Card --}}
                <div class="card p-4 border-0 shadow-sm rounded-4 text-center text-white position-relative overflow-hidden" 
                     style="background: linear-gradient(135deg, #1e1b4b 0%, #4338ca 60%, #3b82f6 100%);">
                    <div class="position-relative z-1">
                        <div class="rounded-circle bg-white bg-opacity-20 d-inline-flex align-items-center justify-content-center p-3 mb-2">
                            <i class="fa-solid fa-feather-pointed fs-3 text-warning"></i>
                        </div>
                        <h6 class="fw-bold mb-2">আপনি কি একজন লেখক?</h6>
                        <p class="small opacity-90 mb-3" style="font-size: 0.82rem; line-height: 1.5;">
                            আইডিয়া প্রকাশন প্ল্যাটফর্মে যুক্ত হয়ে আপনার সকল বই সারা দেশের পাঠকদের কাছে পৌঁছে দিন।
                        </p>
                        <a href="{{ route('register.form', 'author') }}" class="btn btn-warning btn-sm fw-bold rounded-pill px-4 text-dark shadow-sm">
                            লেখক হিসেবে যুক্ত হোন →
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
.hover-lift {
    transition: transform 0.22s ease, box-shadow 0.22s ease;
}
.hover-lift:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08) !important;
}
.hover-bg-opacity:hover {
    background-color: rgba(255, 255, 255, 0.35) !important;
}
.hover-bg-light:hover {
    background-color: #f8fafc !important;
}
.hover-border:hover {
    border-color: #e2e8f0 !important;
}
.bg-bronze {
    background-color: #cd7f32;
}
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.shadow-xs {
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}
</style>
@endsection
