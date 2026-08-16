@extends('layouts.app')

@section('title', 'প্রকাশক ডিরেক্টরি - দেশের শীর্ষ প্রকাশনীসমূহ | Idea Prokashon')

@php
    $letters = ['সব', 'অ', 'আ', 'ই', 'ক', 'খ', 'গ', 'ঘ', 'চ', 'ছ', 'জ', 'ত', 'থ', 'দ', 'ধ', 'ন', 'প', 'ফ', 'ব', 'ভ', 'ম', 'য', 'র', 'ল', 'শ', 'স', 'হ'];
@endphp

@section('content')
<div class="site-publishers-page bg-light py-4 py-md-5">
    <div class="container">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb bg-white px-3 py-2 rounded-pill shadow-xs border small mb-0 d-inline-flex align-items-center">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted"><i class="fa-solid fa-house me-1"></i>হোম</a></li>
                <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">প্রকাশক ডিরেক্টরি</li>
            </ol>
        </nav>

        <!-- Hero Section -->
        <div class="card border-0 rounded-4 shadow-sm text-white mb-5 overflow-hidden position-relative" 
             style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);">
            <div class="card-body p-4 p-md-5 position-relative z-1">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1.5 fw-bold mb-3 d-inline-flex align-items-center gap-1.5">
                            <i class="fa-solid fa-building-columns"></i> সমৃদ্ধ প্রকাশনা ভাণ্ডার
                        </span>
                        <h1 class="display-6 fw-bold mb-2">শীর্ষস্থানীয় প্রকাশক ও প্রকাশনী ডিরেক্টরি</h1>
                        <p class="text-light opacity-80 fs-6 mb-4" style="max-width: 650px;">
                            দেশের স্বনামধন্য ও মননশীল প্রকাশনা প্রতিষ্ঠানের সকল বই এক ঠিকানায়। আপনার পছন্দের প্রকাশনীর গ্রন্থসম্ভার আবিষ্কার করুন।
                        </p>

                        <!-- Live Stats Pills -->
                        <div class="d-flex flex-wrap gap-3">
                            <div class="d-flex align-items-center gap-2 bg-white bg-opacity-10 backdrop-blur px-3 py-2 rounded-3 border border-white border-opacity-10">
                                <i class="fa-solid fa-building text-warning fs-5"></i>
                                <div>
                                    <div class="small opacity-75 leading-none">মোট প্রকাশনী</div>
                                    <div class="fw-bold fs-6">@bn($stats['total'] ?? 0) টি</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 bg-white bg-opacity-10 backdrop-blur px-3 py-2 rounded-3 border border-white border-opacity-10">
                                <i class="fa-solid fa-circle-check text-success fs-5"></i>
                                <div>
                                    <div class="small opacity-75 leading-none">ভেরিফাইড প্রকাশনী</div>
                                    <div class="fw-bold fs-6">@bn($stats['verified'] ?? 0) টি</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 bg-white bg-opacity-10 backdrop-blur px-3 py-2 rounded-3 border border-white border-opacity-10">
                                <i class="fa-solid fa-book text-info fs-5"></i>
                                <div>
                                    <div class="small opacity-75 leading-none">প্রকাশিত বই</div>
                                    <div class="fw-bold fs-6">@bn($stats['total_books'] ?? 0)+ টি</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Graphic Icon -->
                    <div class="col-lg-4 text-center d-none d-lg-block">
                        <div class="display-1 text-white opacity-10">
                            <i class="fa-solid fa-city"></i>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Background circles decoration -->
            <div class="position-absolute end-0 top-0 translate-middle-y bg-primary opacity-20 rounded-circle" style="width: 350px; height: 350px; filter: blur(70px);"></div>
        </div>

        <!-- Search, Filter & Alphabet Navigation Bar -->
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
            <form action="{{ route('publishers.index') }}" method="GET" id="publisherFilterForm">
                
                <div class="row g-3 align-items-center mb-3">
                    
                    <!-- Search Input -->
                    <div class="col-md-5 col-lg-6">
                        <div class="input-group input-group-lg shadow-xs rounded-pill overflow-hidden border">
                            <span class="input-group-text bg-white border-0 ps-3 text-muted">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   class="form-control border-0 px-2 fs-6" 
                                   placeholder="প্রকাশনীর নাম, অবস্থান বা বিষয় খুঁজুন...">
                            @if(request('search'))
                                <a href="{{ route('publishers.index', request()->except('search')) }}" class="input-group-text bg-white border-0 text-muted text-decoration-none">
                                    <i class="fa-solid fa-xmark"></i>
                                </a>
                            @endif
                            <button type="submit" class="btn btn-primary px-4 fw-semibold rounded-pill m-1">
                                খুঁজুন
                            </button>
                        </div>
                    </div>

                    <!-- Verified Switch -->
                    <div class="col-sm-6 col-md-3 col-lg-3">
                        <div class="form-check form-switch p-2 bg-light rounded-pill px-3 d-flex align-items-center justify-content-between border">
                            <label class="form-check-label small fw-semibold text-dark cursor-pointer ms-0 me-2" for="verified_only">
                                <i class="fa-solid fa-circle-check text-primary me-1"></i> শুধুমাত্র ভেরিফাইড
                            </label>
                            <input class="form-check-input cursor-pointer" type="checkbox" role="switch" id="verified_only" name="verified_only" value="1" 
                                   {{ request('verified_only') ? 'checked' : '' }} onchange="this.form.submit()">
                        </div>
                    </div>

                    <!-- Sort Dropdown -->
                    <div class="col-sm-6 col-md-4 col-lg-3">
                        <div class="d-flex align-items-center gap-2">
                            <label class="small text-muted fw-semibold text-nowrap">সর্টিং:</label>
                            <select name="sort" class="form-select form-select-sm rounded-pill py-2 border shadow-xs" onchange="this.form.submit()">
                                <option value="most_books" {{ request('sort') == 'most_books' ? 'selected' : '' }}>সর্বাধিক বই</option>
                                <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>নাম অনুযায়ী (ক-হ)</option>
                                <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>নাম অনুযায়ী (হ-ক)</option>
                                <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>সর্বশেষ যুক্ত</option>
                            </select>
                        </div>
                    </div>

                </div>

                <!-- Bengali Alphabet Chips Bar -->
                <div class="pt-3 border-top">
                    <div class="d-flex align-items-center gap-2 overflow-x-auto pb-2 custom-scrollbar">
                        <span class="small fw-bold text-muted text-nowrap me-1"><i class="fa-solid fa-arrow-down-a-z text-primary me-1"></i>বর্ণানুক্রমিক:</span>
                        
                        @foreach($letters as $letter)
                            @php
                                $isActive = ($letter === 'সব' && (!request('letter') || request('letter') === 'all')) || request('letter') === $letter;
                                $letterParam = $letter === 'সব' ? 'all' : $letter;
                            @endphp
                            <a href="{{ route('publishers.index', array_merge(request()->except(['letter', 'page']), ['letter' => $letterParam])) }}" 
                               class="badge text-decoration-none px-2.5 py-1.5 rounded-pill transition-all {{ $isActive ? 'bg-primary text-white shadow-xs fw-bold' : 'bg-light text-dark hover-bg-light border' }}"
                               style="font-size: 0.82rem; min-width: 32px;">
                                {{ $letter }}
                            </a>
                        @endforeach
                    </div>
                </div>

            </form>
        </div>

        <!-- Publishers Directory Grid -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4 mb-5">
            @forelse($publishers as $publisher)
                @php
                    $logo = $publisher->logo;
                    if ($logo) {
                        $logoUrl = str_starts_with($logo, 'http') ? $logo : asset('storage/' . $logo);
                    } else {
                        $logoUrl = null;
                    }
                    $initials = mb_substr($publisher->name, 0, 2);
                @endphp
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm rounded-4 bg-white hover-lift transition-all position-relative overflow-hidden">
                        
                        <!-- Card Top Color Strip -->
                        <div class="w-100" style="height: 6px; background: linear-gradient(90deg, #4f46e5 0%, #06b6d4 100%);"></div>

                        <div class="card-body p-4 d-flex flex-column text-center">
                            
                            <!-- Publisher Logo / Initials Avatar -->
                            <div class="mb-3 position-relative d-inline-block mx-auto">
                                <div class="rounded-circle overflow-hidden shadow-sm mx-auto d-flex align-items-center justify-content-center border border-2 border-white" 
                                     style="width: 76px; height: 76px; background: radial-gradient(circle, #e0e7ff 0%, #c7d2fe 100%);">
                                    @if($logoUrl)
                                        <img src="{{ $logoUrl }}" alt="{{ $publisher->name }}" class="w-100 h-100 object-fit-cover">
                                    @else
                                        <span class="fs-4 fw-bold text-primary">{{ $initials }}</span>
                                    @endif
                                </div>
                                @if($publisher->is_verified)
                                    <span class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle shadow-xs d-flex align-items-center justify-content-center" 
                                          style="width: 22px; height: 22px; font-size: 0.75rem;" title="ভেরিফাইড প্রকাশনী">
                                        <i class="fa-solid fa-check"></i>
                                    </span>
                                @endif
                            </div>

                            <!-- Publisher Name & Country Tag -->
                            <h5 class="fw-bold text-dark mb-1 line-clamp-1">
                                <a href="{{ route('publishers.show', $publisher->slug) }}" class="text-decoration-none text-dark hover-primary">
                                    {{ $publisher->name }}
                                </a>
                            </h5>
                            
                            <div class="d-flex items-center justify-content-center gap-1.5 mb-2">
                                <span class="badge bg-light text-muted border small rounded-pill px-2.5 py-0.5">
                                    <i class="fa-solid fa-location-dot text-danger me-1"></i>{{ $publisher->country ?: 'বাংলাদেশ' }}
                                </span>
                            </div>

                            <!-- Description -->
                            <p class="text-muted small mb-3 line-clamp-2 lh-sm flex-grow-1" style="font-size: 0.83rem;">
                                {{ $publisher->description ?: 'মননশীল সাহিত্য, ইতিহাস ও গবেষণা গ্রন্থ প্রকাশে নিবেদিত প্রকাশনী।' }}
                            </p>

                            <!-- Books & Ebooks Count Pills -->
                            <div class="d-flex align-items-center justify-content-center gap-2 p-2 bg-light rounded-3 mb-3 border border-light-subtle">
                                <div class="d-flex align-items-center gap-1 small text-secondary">
                                    <i class="fa-solid fa-book text-primary"></i>
                                    <span class="fw-bold text-dark">@bn($publisher->books_count ?? 0)</span> বই
                                </div>
                                <span class="text-muted">•</span>
                                <div class="d-flex align-items-center gap-1 small text-secondary">
                                    <i class="fa-solid fa-file-lines text-info"></i>
                                    <span class="fw-bold text-dark">@bn($publisher->ebooks_count ?? 0)</span> ই-বুক
                                </div>
                            </div>

                            <!-- Explore Button -->
                            <a href="{{ route('publishers.show', $publisher->slug) }}" 
                               class="btn btn-outline-primary rounded-pill w-100 fw-semibold py-2 d-flex align-items-center justify-content-center gap-1.5 shadow-xs">
                                <span>বইসমূহ দেখুন</span>
                                <i class="fa-solid fa-arrow-right small"></i>
                            </a>

                        </div>

                        <!-- Card Footer Links (Website / Phone) -->
                        @if($publisher->website || $publisher->phone)
                        <div class="card-footer bg-light border-top py-2 px-3 d-flex justify-content-center gap-3 small">
                            @if($publisher->website)
                                <a href="{{ $publisher->website }}" target="_blank" class="text-decoration-none text-muted hover-primary" title="অফিসিয়াল ওয়েবসাইট">
                                    <i class="fa-solid fa-globe me-1 text-primary"></i>ওয়েবসাইট
                                </a>
                            @endif
                            @if($publisher->phone)
                                <a href="tel:{{ $publisher->phone }}" class="text-decoration-none text-muted hover-primary" title="যোগাযোগ">
                                    <i class="fa-solid fa-phone me-1 text-success"></i>কল করুন
                                </a>
                            @endif
                        </div>
                        @endif

                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <div class="p-5 bg-white rounded-4 shadow-sm border text-center">
                        <i class="fa-solid fa-building-circle-xmark display-3 text-muted opacity-50 mb-3"></i>
                        <h4 class="fw-bold text-dark mb-2">কোনো প্রকাশনী পাওয়া যায়নি</h4>
                        <p class="text-muted mb-4">আপনার অনুসন্ধান অনুযায়ী কোনো প্রকাশনা প্রতিষ্ঠান খুঁজে পাওয়া যায়নি।</p>
                        <a href="{{ route('publishers.index') }}" class="btn btn-primary rounded-pill px-4 fw-semibold">
                            <i class="fa-solid fa-rotate-left me-1"></i> সব প্রকাশনী দেখুন
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($publishers instanceof \Illuminate\Pagination\LengthAwarePaginator && $publishers->hasPages())
            <div class="d-flex justify-content-center mb-5">
                {{ $publishers->links('pagination::bootstrap-5') }}
            </div>
        @endif

        <!-- Publish With Us Banner -->
        <div class="card border-0 shadow-sm rounded-4 text-white p-4 p-md-5 position-relative overflow-hidden"
             style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%);">
            <div class="row align-items-center g-4 position-relative z-1">
                <div class="col-lg-8">
                    <h3 class="fw-bold mb-2">আপনার প্রকাশনা প্রতিষ্ঠান কি যুক্ত করতে চান?</h3>
                    <p class="text-light opacity-80 mb-0">
                        আইডিয়া প্রকাশন প্ল্যাটফর্মে যুক্ত হয়ে দেশের হাজারো বইপ্রেমী পাঠকের কাছে পৌঁছে দিন আপনার প্রকাশিত সকল বই ও ই-বুক।
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="{{ route('register.form', 'publisher') }}" class="btn btn-warning rounded-pill px-4 py-2.5 fw-bold shadow-sm d-inline-flex align-items-center gap-2">
                        <i class="fa-solid fa-plus"></i> প্রকাশনী হিসেবে যুক্ত হোন
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
