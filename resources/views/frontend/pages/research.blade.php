@extends('layouts.app')

@section('title', 'গবেষণা ও নিবন্ধ — আইডিয়া প্রকাশন')

@section('content')
<div class="container py-4 mb-5">
    <!-- Hero Banner -->
    <div class="card p-4 p-md-5 mb-4 border-0 shadow-sm rounded-4 position-relative overflow-hidden text-white" 
         style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #0d9488 100%);">
        <div class="position-absolute end-0 bottom-0 opacity-10 d-none d-md-block pe-4 pb-2" style="pointer-events: none;">
            <i class="fa-solid fa-flask" style="font-size: 14rem;"></i>
        </div>
        <div class="position-relative z-1" style="max-width: 650px;">
            <span class="badge bg-warning text-dark fw-bold px-3 py-1 mb-2 rounded-pill shadow-sm">একাডেমিক ও মুক্ত গবেষণা</span>
            <h1 class="fw-bold display-6 mb-2">গবেষণা ও সমকালীন নিবন্ধ</h1>
            <p class="fs-6 opacity-90 mb-4">সাহিত্য, ইতিহাস, দর্শন, বিজ্ঞান ও সমাজবিজ্ঞানের গুরুত্বপূর্ণ প্রবন্ধ, গবেষণাপত্র ও পর্যালোচনার উন্মুক্ত সংকলন।</p>
            
            <!-- Search Form -->
            <form action="{{ route('research.index') }}" method="GET" class="d-flex flex-column flex-sm-row gap-2">
                <div class="input-group shadow-sm rounded-pill overflow-hidden bg-white p-1">
                    <span class="input-group-text bg-transparent border-0 text-muted ps-3">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="form-control border-0 shadow-none ps-2" 
                           placeholder="গবেষণাপত্রের শিরোনাম বা বিষয়বস্তু লিখে খুঁজুন...">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                        খুঁজুন
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Header bar -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
            <i class="fa-solid fa-scroll text-primary"></i> প্রকাশিত সকল গবেষণা ও প্রবন্ধ
        </h5>
        @if(isset($papers) && $papers instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <span class="badge bg-light text-muted border">মোট @bn($papers->total())টি নিবন্ধ</span>
        @endif
    </div>

    <!-- Research Papers Grid (Blog Style) -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-4">
        @forelse($papers as $paper)
            <div class="col">
                <article class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden hover-lift p-3 p-md-4" style="transition: all 0.25s ease;">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="rounded-circle bg-teal-subtle text-teal-emphasis d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px; background: #ccfbf1; color: #0f766e;">
                            <i class="fa-solid fa-flask"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="small fw-bold text-dark d-block text-truncate">
                                {{ $paper->author ? $paper->author->name : ($paper->author_name ?: 'আইডিয়া গবেষক') }}
                            </span>
                            <span class="text-muted" style="font-size: 0.75rem;">
                                {{ $paper->published_at ? date('d M, Y', strtotime($paper->published_at)) : ($paper->created_at ? date('d M, Y', strtotime($paper->created_at)) : 'সম্প্রতি') }}
                            </span>
                        </div>
                    </div>

                    <h5 class="fw-bold text-dark mb-2 line-clamp-2" style="font-size: 1.12rem; line-height: 1.45;">
                        <a href="{{ route('research.show', $paper->slug ?? $paper->id) }}" class="text-decoration-none text-dark hover-primary">
                            {{ $paper->title }}
                        </a>
                    </h5>

                    <p class="text-muted small line-clamp-3 mb-4" style="font-size: 0.88rem; line-height: 1.6;">
                        {{ $paper->abstract ? Str::limit(strip_tags($paper->abstract), 130) : 'এই গবেষণাপত্রের পূর্ণাঙ্গ সংস্করণ ও পর্যালোচনা অনলাইনে পড়ার জন্য উপলব্ধ।' }}
                    </p>

                    <div class="mt-auto pt-3 border-top d-flex align-items-center justify-content-between">
                        <span class="badge bg-light text-secondary border small">
                            <i class="fa-regular fa-file-lines me-1"></i> গবেষণাপত্র
                        </span>
                        <a href="{{ route('research.show', $paper->slug ?? $paper->id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold">
                            সম্পূর্ণ পড়ুন →
                        </a>
                    </div>
                </article>
            </div>
        @empty
            <div class="col-12 w-100">
                <div class="card p-5 text-center border-0 shadow-sm rounded-4 bg-light">
                    <i class="fa-solid fa-flask-vial fs-1 text-muted mb-3 opacity-50"></i>
                    <h5 class="fw-bold text-dark">কোনো গবেষণাপত্র পাওয়া যায়নি</h5>
                    <p class="text-muted small mb-3">নতুন গবেষণা ও নিবন্ধ নিয়মিত যুক্ত করা হচ্ছে।</p>
                    <a href="{{ route('research.index') }}" class="btn btn-primary btn-sm rounded-pill px-4 align-self-center">সকল নিবন্ধ দেখুন</a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if(isset($papers) && $papers instanceof \Illuminate\Pagination\LengthAwarePaginator && $papers->hasPages())
        <div class="d-flex justify-content-center mb-5">
            {{ $papers->links() }}
        </div>
    @endif
</div>
@endsection
