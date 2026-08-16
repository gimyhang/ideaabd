@extends('layouts.app')

@section('title', 'লেখক ডিরেক্টরি — আইডিয়া প্রকাশন')

@section('content')
<div class="container py-4 mb-5">
    <!-- Hero Banner -->
    <div class="card p-4 p-md-5 mb-4 border-0 shadow-sm rounded-4 position-relative overflow-hidden text-white" 
         style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #4338ca 100%);">
        <div class="position-absolute end-0 bottom-0 opacity-10 d-none d-md-block pe-4 pb-2" style="pointer-events: none;">
            <i class="fa-solid fa-pen-fancy" style="font-size: 14rem;"></i>
        </div>
        <div class="position-relative z-1" style="max-width: 650px;">
            <span class="badge bg-warning text-dark fw-bold px-3 py-1 mb-2 rounded-pill shadow-sm">লেখক ও গবেষক ডিরেক্টরি</span>
            <h1 class="fw-bold display-6 mb-2">আমাদের সম্মানিত লেখকগণ</h1>
            <p class="fs-6 opacity-90 mb-4">দেশ-বিদেশের প্রথিতযশা ও উদীয়মান লেখকদের পূর্ণাঙ্গ প্রোফাইল, পরিচিতি এবং প্রকাশিত সকল বইয়ের তালিকা অন্বেষণ করুন।</p>
            
            <!-- Search Form -->
            <form action="{{ route('authors.index') }}" method="GET" class="d-flex flex-column flex-sm-row gap-2">
                <div class="input-group shadow-sm rounded-pill overflow-hidden bg-white p-1">
                    <span class="input-group-text bg-transparent border-0 text-muted ps-3">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="q" value="{{ request('q') }}" 
                           class="form-control border-0 shadow-none ps-2" 
                           placeholder="লেখকের নাম বা পরিচিতি লিখে খুঁজুন...">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                        খুঁজুন
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Alphabet Filter Bar -->
    <div class="card p-3 mb-4 border-0 shadow-sm rounded-4 bg-white">
        <div class="d-flex align-items-center gap-2 mb-2 pb-2 border-bottom">
            <i class="fa-solid fa-arrow-down-a-z text-primary"></i>
            <span class="small fw-bold text-dark">বর্ণানুক্রমিক ফিল্টার:</span>
            @if(request('letter') || request('q') || request('filter'))
                <a href="{{ route('authors.index') }}" class="btn btn-sm btn-link text-danger text-decoration-none ms-auto p-0 small">
                    <i class="fa-solid fa-rotate-left me-1"></i> ফিল্টার মুছুন
                </a>
            @endif
        </div>
        
        <!-- Bengali letters -->
        <div class="d-flex flex-wrap gap-1 mb-2">
            <a href="{{ route('authors.index', array_merge(request()->except('page', 'letter'))) }}" 
               class="btn btn-sm {{ !request('letter') ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-3 py-1 fw-semibold small">
                সকল
            </a>
            @foreach(['অ','আ','ই','উ','এ','ও','ক','খ','গ','ঘ','চ','ছ','জ','ঝ','ট','ঠ','ড','ঢ','ত','থ','দ','ধ','ন','প','ফ','ব','ভ','ম','য','র','ল','শ','ষ','স','হ'] as $bnChar)
                <a href="{{ route('authors.index', array_merge(request()->except('page'), ['letter' => $bnChar])) }}" 
                   class="btn btn-sm {{ request('letter') === $bnChar ? 'btn-primary' : 'btn-light border' }} rounded-pill px-2 py-1 small fw-semibold"
                   style="min-width: 32px;">
                    {{ $bnChar }}
                </a>
            @endforeach
        </div>

        <!-- English letters -->
        <div class="d-flex flex-wrap gap-1 pt-2 border-top">
            @foreach(range('A','Z') as $char)
                <a href="{{ route('authors.index', array_merge(request()->except('page'), ['letter' => $char])) }}" 
                   class="btn btn-sm {{ request('letter') === $char ? 'btn-primary' : 'btn-light border' }} rounded-pill px-2 py-1 small"
                   style="min-width: 30px; font-size: 0.75rem;">
                    {{ $char }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- Main Content Layout -->
    <div class="row g-4">
        <!-- Main Authors List -->
        <div class="col-lg-9">
            <!-- Header bar -->
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4 pb-2 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="fw-bold text-dark mb-0">লেখক তালিকা</h5>
                    @if(isset($authors) && $authors instanceof \Illuminate\Pagination\LengthAwarePaginator)
                        <span class="badge bg-light text-muted border">@bn($authors->total()) জন লেখক</span>
                    @endif
                </div>

                <form method="GET" action="{{ route('authors.index') }}" class="d-flex align-items-center gap-2">
                    @if(request('q')) <input type="hidden" name="q" value="{{ request('q') }}"> @endif
                    @if(request('letter')) <input type="hidden" name="letter" value="{{ request('letter') }}"> @endif
                    @if(request('filter')) <input type="hidden" name="filter" value="{{ request('filter') }}"> @endif
                    
                    <label for="sort" class="small text-muted text-nowrap fw-semibold">সাজান:</label>
                    <select name="sort" id="sort" class="form-select form-select-sm rounded-pill border shadow-sm px-3" onchange="this.form.submit()">
                        <option value="name" @selected(request('sort') === 'name' || !request('sort'))>নামের ক্রমানুসারে</option>
                        <option value="books_desc" @selected(request('sort') === 'books_desc')>সর্বাধিক বই</option>
                        <option value="latest" @selected(request('sort') === 'latest')>সর্বশেষ যোগকৃত</option>
                    </select>
                </form>
            </div>

            <!-- Authors Cards Grid -->
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3 mb-4">
                @forelse($authors as $author)
                    <div class="col">
                        @include('components.author-card', ['author' => $author])
                    </div>
                @empty
                    <div class="col-12 w-100">
                        <div class="card p-5 text-center border-0 shadow-sm rounded-4 bg-light">
                            <i class="fa-solid fa-user-xmark fs-1 text-muted mb-3 opacity-50"></i>
                            <h5 class="fw-bold text-dark">কোনো লেখক পাওয়া যায়নি</h5>
                            <p class="text-muted small mb-3">অনুগ্রহ করে ভিন্ন কোনো নাম বা বর্ণ দিয়ে চেষ্টা করুন।</p>
                            <a href="{{ route('authors.index') }}" class="btn btn-primary btn-sm rounded-pill px-4 align-self-center">সকল লেখক দেখুন</a>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if(isset($authors) && $authors instanceof \Illuminate\Pagination\LengthAwarePaginator && $authors->hasPages())
                <div class="d-flex justify-content-center mb-5">
                    {{ $authors->links() }}
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-3">
            <!-- Filter Options Box -->
            <div class="card p-3 mb-4 border-0 shadow-sm rounded-4">
                <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom">
                    <i class="fa-solid fa-sliders text-primary me-2"></i>দ্রুত ফিল্টার
                </h6>
                <div class="d-flex flex-column gap-2">
                    <a href="{{ route('authors.index', array_merge(request()->except('page'), ['filter' => 'most_books'])) }}" 
                       class="btn btn-sm text-start {{ request('filter') === 'most_books' ? 'btn-primary' : 'btn-light' }} rounded-3 fw-semibold">
                        <i class="fa-solid fa-trophy me-2 text-warning"></i>সর্বোচ্চ সংখ্যক বই
                    </a>
                    <a href="{{ route('authors.index', array_merge(request()->except('page'), ['filter' => 'verified'])) }}" 
                       class="btn btn-sm text-start {{ request('filter') === 'verified' ? 'btn-primary' : 'btn-light' }} rounded-3 fw-semibold">
                        <i class="fa-solid fa-circle-check me-2 text-primary"></i>যাচাইকৃত লেখক
                    </a>
                    <a href="{{ route('authors.index', array_merge(request()->except('page'), ['filter' => 'recent_active'])) }}" 
                       class="btn btn-sm text-start {{ request('filter') === 'recent_active' ? 'btn-primary' : 'btn-light' }} rounded-3 fw-semibold">
                        <i class="fa-solid fa-bolt me-2 text-success"></i>সাম্প্রতিক সক্রিয়
                    </a>
                </div>
            </div>

            <!-- Top Authors Sidebar List -->
            @if(isset($topAuthors) && $topAuthors->isNotEmpty())
            <div class="card p-3 mb-4 border-0 shadow-sm rounded-4">
                <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom">
                    <i class="fa-solid fa-crown text-warning me-2"></i>শীর্ষ লেখকগণ
                </h6>
                <div class="d-flex flex-column gap-3">
                    @foreach($topAuthors as $top)
                        <a href="{{ route('authors.show', $top->id ?? $top->slug) }}" class="d-flex align-items-center gap-2 text-decoration-none text-dark hover-primary">
                            <div class="rounded-circle overflow-hidden shadow-sm flex-shrink-0" style="width: 42px; height: 42px; background: #e2e8f0;">
                                @php $tPhoto = $top->avatar ?? $top->photo ?? null; @endphp
                                @if($tPhoto)
                                    <img src="{{ str_starts_with($tPhoto, 'http') ? $tPhoto : asset('storage/' . $tPhoto) }}" class="w-100 h-100 object-fit-cover">
                                @else
                                    <div class="w-100 h-100 d-flex align-items-center justify-content-center text-primary fw-bold">
                                        {{ mb_substr($top->name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <div class="fw-bold small text-truncate">{{ $top->name }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">@bn($top->books_count ?? 0)টি বই প্রকাশিত</div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Author Registration CTA -->
            <div class="card p-4 border-0 shadow-sm rounded-4 text-center text-white position-relative overflow-hidden" 
                 style="background: linear-gradient(135deg, #4338ca 0%, #3b82f6 100%);">
                <i class="fa-solid fa-feather-pointed fs-2 mb-2 opacity-75"></i>
                <h6 class="fw-bold mb-2">আপনি কি একজন লেখক?</h6>
                <p class="small opacity-90 mb-3">আইডিয়া প্রকাশন প্ল্যাটফর্মে যুক্ত হয়ে আপনার সকল বই ও প্রকাশনা সারা দেশের পাঠকদের কাছে পৌঁছে দিন।</p>
                <a href="{{ route('register.form', 'author') }}" class="btn btn-light btn-sm fw-bold rounded-pill px-4 text-primary shadow-sm">
                    লেখক হিসেবে যোগ দিন
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
