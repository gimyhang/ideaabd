@extends('layouts.admin')

@section('title', 'Cache Management & Optimization')
@section('heading', 'সিস্টেম ক্যাশ ব্যবস্থাপনা ও অপ্টিমাইজেশন')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">ড্যাশবোর্ড</a></li>
    <li class="breadcrumb-item active">ক্যাশ ম্যানেজমেন্ট</li>
@endsection

@section('actions')
    <form action="{{ route('admin.cache.clear-all') }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-danger btn-sm rounded-pill px-4 shadow-sm fw-bold">
            <i class="fas fa-trash-can me-1.5"></i> সমস্ত ক্যাশ ক্লিয়ার করুন (Clear All Cache)
        </button>
    </form>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-0 rounded-4 shadow-xs" role="alert">
            <i class="fas fa-circle-check me-2 text-success fs-5"></i>
            <div class="fw-semibold">{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-0 rounded-4 shadow-xs" role="alert">
            <i class="fas fa-triangle-exclamation me-2 text-danger fs-5"></i>
            <div class="fw-semibold">{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Cache Health & Stats Grid -->
    <div class="row g-3">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card bg-white rounded-4 shadow-sm border-0 p-3.5 border-start border-4 border-primary h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-muted fw-semibold">Blade View Cache</span>
                    <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                        <i class="fas fa-file-code"></i>
                    </div>
                </div>
                <h3 class="text-dark fs-4 fw-bold mb-1">{{ $stats['view_files_count'] }} টি ফাইল</h3>
                <p class="text-muted small mb-0 font-monospace">সাইজ: {{ $stats['view_cache_size'] }}</p>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card bg-white rounded-4 shadow-sm border-0 p-3.5 border-start border-4 border-info h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-muted fw-semibold">Application Data Cache</span>
                    <div class="rounded-circle bg-info-subtle text-info p-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                        <i class="fas fa-database"></i>
                    </div>
                </div>
                <h3 class="text-dark fs-4 fw-bold mb-1">{{ $stats['data_cache_size'] }}</h3>
                <p class="text-muted small mb-0">ড্রাইভার: <span class="badge bg-light text-dark border">{{ $stats['cache_driver'] }}</span></p>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card bg-white rounded-4 shadow-sm border-0 p-3.5 border-start border-4 border-success h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-muted fw-semibold">PHP OPcache Status</span>
                    <div class="rounded-circle bg-success-subtle text-success p-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                        <i class="fas fa-bolt"></i>
                    </div>
                </div>
                <h3 class="text-dark fs-4 fw-bold mb-1">
                    @if($stats['opcache_enabled'])
                        <span class="badge bg-success text-white">সক্রিয় (Active)</span>
                    @else
                        <span class="badge bg-secondary text-white">নিষ্ক্রিয় (Off)</span>
                    @endif
                </h3>
                <p class="text-muted small mb-0">মেমোরি: {{ $stats['opcache_memory'] }}</p>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card bg-white rounded-4 shadow-sm border-0 p-3.5 border-start border-4 border-warning h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-muted fw-semibold">Environment & PHP</span>
                    <div class="rounded-circle bg-warning-subtle text-warning p-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                        <i class="fas fa-server"></i>
                    </div>
                </div>
                <h3 class="text-dark fs-4 fw-bold mb-1">PHP {{ $stats['php_version'] }}</h3>
                <p class="text-muted small mb-0">সেশন: {{ $stats['session_driver'] }}</p>
            </div>
        </div>
    </div>

    <!-- Individual Cache Actions Card -->
    <div class="card bg-white rounded-4 shadow-sm border-0 p-4">
        <h5 class="fw-bold text-dark mb-1"><i class="fas fa-sliders text-primary me-2"></i> সুনির্দিষ্ট ক্যাশ ক্লিয়ার ও অপ্টিমাইজেশন</h5>
        <p class="text-muted small mb-4">সাইটের কোনো ডিজাইন বা কোড পরিবর্তন লাইভ না হলে সংশ্লিষ্ট ক্যাশ ক্লিয়ার করুন।</p>

        <div class="row g-3">
            
            <!-- 1. Blade Views -->
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card bg-light border rounded-4 p-3 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary text-white rounded-circle p-2"><i class="fas fa-tv"></i></span>
                            <h6 class="fw-bold text-dark mb-0">Blade View Cache</h6>
                        </div>
                        <p class="small text-muted mb-3">
                            ওয়েবসাইটের ডিজাইন, HTML বা Blade ফাইলে করা পরিবর্তন না দেখা গেলে এই ক্যাশ ক্লিয়ার করুন।
                        </p>
                    </div>
                    <form action="{{ route('admin.cache.clear-views') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary btn-sm rounded-pill w-100 fw-semibold">
                            <i class="fas fa-broom me-1"></i> ক্লিয়ার ভিউ ক্যাশ
                        </button>
                    </form>
                </div>
            </div>

            <!-- 2. Application Cache -->
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card bg-light border rounded-4 p-3 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-info text-white rounded-circle p-2"><i class="fas fa-layer-group"></i></span>
                            <h6 class="fw-bold text-dark mb-0">App Data Cache</h6>
                        </div>
                        <p class="small text-muted mb-3">
                            ডাটাবেজ কুয়েরি রেজাল্ট, সাইট সেটিংস ও সাময়িক সংরক্ষিত ডেটা ক্যাশ ক্লিয়ার করে।
                        </p>
                    </div>
                    <form action="{{ route('admin.cache.clear-app') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-info btn-sm rounded-pill w-100 fw-semibold">
                            <i class="fas fa-broom me-1"></i> ক্লিয়ার ডেটা ক্যাশ
                        </button>
                    </form>
                </div>
            </div>

            <!-- 3. Config Cache -->
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card bg-light border rounded-4 p-3 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-warning text-dark rounded-circle p-2"><i class="fas fa-gear"></i></span>
                            <h6 class="fw-bold text-dark mb-0">Config Cache</h6>
                        </div>
                        <p class="small text-muted mb-3">
                            <code>.env</code> বা <code>config/</code> ফাইলের কোনো পরিবর্তন তাৎক্ষণিক কার্যকর করার জন্য।
                        </p>
                    </div>
                    <form action="{{ route('admin.cache.clear-config') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-warning text-dark btn-sm rounded-pill w-100 fw-semibold">
                            <i class="fas fa-broom me-1"></i> ক্লিয়ার কনফিগ ক্যাশ
                        </button>
                    </form>
                </div>
            </div>

            <!-- 4. Route Cache -->
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card bg-light border rounded-4 p-3 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-secondary text-white rounded-circle p-2"><i class="fas fa-route"></i></span>
                            <h6 class="fw-bold text-dark mb-0">Route Cache</h6>
                        </div>
                        <p class="small text-muted mb-3">
                            নতুন রুট বা লিঙ্ক 404 দেখালে রুট ক্যাশ ক্লিয়ার ও রিফ্রেশ করুন।
                        </p>
                    </div>
                    <form action="{{ route('admin.cache.clear-routes') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm rounded-pill w-100 fw-semibold">
                            <i class="fas fa-broom me-1"></i> ক্লিয়ার রুট ক্যাশ
                        </button>
                    </form>
                </div>
            </div>

            <!-- 5. Optimize for Production -->
            <div class="col-12 col-md-6 col-xl-8">
                <div class="card bg-light border rounded-4 p-3 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-success text-white rounded-circle p-2"><i class="fas fa-gauge-high"></i></span>
                            <h6 class="fw-bold text-dark mb-0">Optimize Application (উৎপাদন পরিবেশ গতিবৃদ্ধি)</h6>
                        </div>
                        <p class="small text-muted mb-3">
                            রুট এবং কনফিগারেশন ফাইল একীভূত করে সাইটের লোডিং স্পিড উল্লেখযোগ্যভাবে বাড়িয়ে দেয়।
                        </p>
                    </div>
                    <form action="{{ route('admin.cache.optimize') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm rounded-pill px-4 fw-bold">
                            <i class="fas fa-bolt me-1"></i> অপ্টিমাইজ করুন (Run Optimize)
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
