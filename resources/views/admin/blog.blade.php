@extends('layouts.admin')

@section('title', 'আইডিয়াপত্র ও ব্লগ পোস্ট পরিচালনা')
@section('heading', 'আইডিয়াপত্র — ব্লগ ও কনটেন্ট মডারেশন')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">আইডিয়াপত্র ও ব্লগ</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css" />
<style>
    .banner-hero-card {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #fff;
    }
    .banner-thumb-box {
        width: 100%;
        max-width: 220px;
        aspect-ratio: 16 / 9;
        border-radius: 12px;
        overflow: hidden;
        background: #020617;
        position: relative;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    .banner-thumb-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    .banner-thumb-box:hover img {
        transform: scale(1.05);
    }
    .btn-featured-star {
        background: none;
        border: none;
        padding: 0;
        font-size: 1.15rem;
        cursor: pointer;
        color: #cbd5e1;
        transition: all 0.2s ease;
    }
    .btn-featured-star.active {
        color: #f59e0b;
        transform: scale(1.1);
    }
    .status-select-badge {
        font-size: 0.82rem;
        font-weight: 600;
        border-radius: 50rem;
        padding: 0.25rem 0.65rem;
        border: 1px solid transparent;
        cursor: pointer;
    }
    .status-select-badge:focus {
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
    }
    .cropper-view-box, .cropper-face {
        border-radius: 8px;
    }
    .hover-lift {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .hover-lift:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
    }
</style>
@endpush

@section('actions')
    <button type="button" class="btn btn-primary rounded-pill px-3 shadow-xs fw-semibold" data-bs-toggle="modal" data-bs-target="#blogCustomizerModal">
        <i class="fas fa-palette me-1.5"></i> ডিজাইন ও ব্যানার কাস্টমাইজার
    </button>
    <button type="button" class="btn btn-outline-success rounded-pill px-3 shadow-xs fw-semibold" data-bs-toggle="modal" data-bs-target="#bulkTypographyModal">
        <i class="fas fa-wand-magic-sparkles me-1.5"></i> স্পেস মেরামত ইঞ্জিন
    </button>
    <a href="{{ route('admin.blog-categories') }}" class="btn btn-outline-secondary rounded-pill px-3 shadow-xs">
        <i class="fas fa-shapes me-1"></i> ক্যাটাগরি
    </a>
    <a href="{{ route('admin.content.create', 'blog') }}" class="btn btn-dark rounded-pill px-3 shadow-xs fw-semibold">
        <i class="fas fa-plus me-1"></i> নতুন পোস্ট
    </a>
    <a href="{{ route('blog.index') }}" target="_blank" rel="noopener" class="btn btn-outline-primary rounded-pill px-3 shadow-xs">
        <i class="fas fa-arrow-up-right-from-square me-1"></i> ব্লগে দেখুন
    </a>
@endsection

@section('content')

{{-- Flash Alert Messages --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-3 rounded-4 shadow-sm" role="alert">
        <i class="fas fa-circle-check fs-5 me-2.5 text-success"></i>
        <div class="fw-semibold">{{ session('success') }}</div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-3 rounded-4 shadow-sm" role="alert">
        <i class="fas fa-circle-exclamation fs-5 me-2.5 text-danger"></i>
        <div class="fw-semibold">{{ session('error') }}</div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- 1. Dynamic Hero Masthead & Management Console --}}
<div class="card banner-hero-card border-0 shadow rounded-4 mb-4 overflow-hidden position-relative">
    <div class="card-body p-4">
        <div class="row g-4 align-items-center">
            
            <!-- Left: Current Active Social & Header Banner -->
            <div class="col-lg-3 col-md-4 text-center text-md-start">
                <div class="d-inline-block position-relative">
                    <div class="banner-thumb-box shadow">
                        <img src="{{ $blogOgBannerUrl }}" alt="Active Ideapatra Banner" id="heroBannerThumb">
                        <div class="position-absolute bottom-0 start-0 end-0 p-1.5 text-center bg-dark bg-opacity-75" style="font-size: 10px; backdrop-filter: blur(4px);">
                            <i class="fas fa-share-nodes text-warning me-1"></i> সোশ্যাল ও ব্লগ ব্যানার
                        </div>
                    </div>
                </div>
                <div class="mt-2">
                    <button type="button" class="btn btn-warning btn-sm rounded-pill px-3 py-1 fw-bold text-dark shadow-xs" 
                            data-bs-toggle="modal" data-bs-target="#blogCustomizerModal" onclick="switchToBannerTab()">
                        <i class="fas fa-crop-simple me-1"></i> ব্যানার পরিবর্তন
                    </button>
                </div>
            </div>

            <!-- Center: Title, Typography & Live Status -->
            <div class="col-lg-6 col-md-8">
                <div class="d-flex align-items-center gap-2 mb-1.5 flex-wrap">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 fw-bold">
                        {{ $blogSettings['hero_badge'] ?? 'সাহিত্য, শিল্প-সংস্কৃতি, গবেষণা ও মুক্তচিন্তা' }}
                    </span>
                    <span class="badge bg-white bg-opacity-10 text-white rounded-pill px-2.5 py-1">
                        <i class="fas fa-font me-1 text-warning"></i> ফন্ট: {{ explode(',', $blogSettings['font_family'] ?? '')[0] ?? 'Hind Siliguri' }}
                    </span>
                    <span class="badge bg-white bg-opacity-10 text-white rounded-pill px-2.5 py-1">
                        <i class="fas fa-arrows-up-down me-1 text-info"></i> লাইন স্পেস: {{ $blogSettings['line_height'] ?? '1.6' }}
                    </span>
                </div>

                <h4 class="fw-bold mb-1.5 text-white">
                    {{ $blogSettings['hero_title'] ?? 'আইডিয়াপত্র — সমকালীন সাহিত্য ও চিন্তা' }}
                </h4>
                <p class="text-white-50 small mb-3 line-clamp-2" style="font-size: 0.88rem; max-width: 600px;">
                    {{ $blogSettings['hero_subtitle'] ?? 'সমকালীন সাহিত্য আলোচনা, প্রবন্ধ, ছোটগল্প, কবিতা, নতুন বইয়ের প্রামাণ্য পর্যালোচনা ও গবেষণামূলক লেখার উন্মুক্ত ডিজিটাল সাময়িকী।' }}
                </p>

                <!-- Quick Action Pills -->
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" class="btn btn-outline-light btn-sm rounded-pill px-3 shadow-xs" data-bs-toggle="modal" data-bs-target="#blogCustomizerModal">
                        <i class="fas fa-palette text-warning me-1"></i> ডিজাইন পরিবর্তন
                    </button>
                    <button type="button" class="btn btn-outline-light btn-sm rounded-pill px-3 shadow-xs" data-bs-toggle="modal" data-bs-target="#bulkTypographyModal">
                        <i class="fas fa-wand-magic-sparkles text-success me-1"></i> লেখার স্পেস মেরামত
                    </button>
                    <a href="{{ route('blog.index') }}" target="_blank" class="btn btn-outline-info btn-sm rounded-pill px-3 shadow-xs">
                        <i class="fas fa-external-link-alt me-1"></i> লাইভ আইডিয়াপত্র
                    </a>
                </div>
            </div>

            <!-- Right: Quick Stat Summary Ring -->
            <div class="col-lg-3 text-center text-lg-end">
                <div class="p-3 rounded-4 bg-white bg-opacity-10 border border-white border-opacity-10 d-inline-block text-start" style="min-width: 200px;">
                    <div class="d-flex justify-content-between align-items-center mb-1.5">
                        <span class="small text-white-50">মোট পোস্ট:</span>
                        <span class="fw-bold fs-6 text-white">@bn($stats['total'] ?? 0)</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1.5">
                        <span class="small text-success"><i class="fas fa-check-circle me-1"></i>প্রকাশিত:</span>
                        <span class="fw-bold text-success">@bn($stats['published'] ?? 0)</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1.5">
                        <span class="small text-warning"><i class="fas fa-clock me-1"></i>অপেক্ষমাণ:</span>
                        <span class="fw-bold text-warning">@bn($stats['pending'] ?? 0)</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-info"><i class="fas fa-star me-1 text-warning"></i>নির্বাচিত:</span>
                        <span class="fw-bold text-info">@bn($stats['featured'] ?? 0)</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- 2. Stat Metric Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.blog') }}" class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary text-decoration-none hover-lift h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">মোট লেখা</span>
                    <h3 class="fw-bold mb-0 text-primary">@bn($stats['total'] ?? 0)</h3>
                </div>
                <div class="rounded-circle bg-primary-subtle text-primary p-3"><i class="fas fa-blog fs-4"></i></div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.blog', ['status' => 'published']) }}" class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success text-decoration-none hover-lift h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">প্রকাশিত (অনুমোদিত)</span>
                    <h3 class="fw-bold mb-0 text-success">@bn($stats['published'] ?? 0)</h3>
                </div>
                <div class="rounded-circle bg-success-subtle text-success p-3"><i class="fas fa-check-double fs-4"></i></div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.blog', ['status' => 'pending']) }}" class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-warning text-decoration-none hover-lift h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">অনুমোদন অপেক্ষমাণ</span>
                    <h3 class="fw-bold mb-0 text-warning">@bn($stats['pending'] ?? 0)</h3>
                </div>
                <div class="rounded-circle bg-warning-subtle text-warning p-3"><i class="fas fa-clock fs-4"></i></div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.blog', ['status' => 'featured']) }}" class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-info text-decoration-none hover-lift h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">নির্বাচিত ফিচার্ড পোস্ট</span>
                    <h3 class="fw-bold mb-0 text-info">@bn($stats['featured'] ?? 0)</h3>
                </div>
                <div class="rounded-circle bg-info-subtle text-info p-3"><i class="fas fa-star fs-4 text-warning"></i></div>
            </div>
        </a>
    </div>
</div>

{{-- 3. Advanced Multi-Filter Bar --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-3">
        <form action="{{ route('admin.blog') }}" method="GET" class="row g-2 align-items-center" id="blogFilterForm">
            <!-- Search Keyword -->
            <div class="col-lg-4 col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="search" name="search" class="form-control border-start-0" 
                           placeholder="পোস্টের শিরোনাম, বিষয়, লেখক বা slug..." value="{{ request('search') }}">
                </div>
            </div>

            <!-- Category Filter -->
            <div class="col-lg-2 col-md-3 col-6">
                <select name="category" class="form-select" onchange="this.form.submit()">
                    <option value="">সকল ক্যাটাগরি</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->slug }}" @selected(request('category') == $cat->slug || request('category') == $cat->id)>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div class="col-lg-2 col-md-3 col-6">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="all" @selected(request('status') === 'all' || !request('status'))>সকল অবস্থা</option>
                    <option value="published" @selected(request('status') === 'published')>প্রকাশিত (Published)</option>
                    <option value="pending" @selected(request('status') === 'pending')>অপেক্ষমাণ (Pending)</option>
                    <option value="featured" @selected(request('status') === 'featured')>নির্বাচিত (Featured)</option>
                    <option value="draft" @selected(request('status') === 'draft')>খসড়া (Draft)</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>বাতিল (Rejected)</option>
                </select>
            </div>

            <!-- Items Per Page -->
            <div class="col-lg-2 col-md-3 col-6">
                <select name="per_page" class="form-select" onchange="this.form.submit()">
                    <option value="20" @selected(($perPage ?? 20) == 20)>প্রতি পেজে ২০টি</option>
                    <option value="50" @selected(($perPage ?? 20) == 50)>প্রতি পেজে ৫০টি</option>
                    <option value="100" @selected(($perPage ?? 20) == 100)>প্রতি পেজে ১০০টি</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="col-lg-2 col-md-3 col-6 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 rounded-3"><i class="fas fa-filter me-1"></i> ফিল্টার</button>
                @if(request()->hasAny(['search', 'status', 'category', 'is_featured', 'per_page']))
                    <a href="{{ route('admin.blog') }}" class="btn btn-light border rounded-3" title="রিসেট"><i class="fas fa-rotate-left"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- 4. Batch Actions Bar & Interactive Post Table --}}
<form action="{{ route('admin.blog.bulk-action') }}" method="POST" id="bulkActionForm">
    @csrf
    
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 px-1">
        <div class="d-flex align-items-center gap-2">
            <div class="form-check me-2">
                <input class="form-check-input" type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)">
                <label class="form-check-label small fw-semibold text-muted" for="selectAllCheckbox">সব নির্বাচন</label>
            </div>
            
            <div class="input-group input-group-sm" style="max-width: 280px;">
                <select name="bulk_action" id="bulkActionSelect" class="form-select form-select-sm rounded-start-pill">
                    <option value="">বাল্ক অ্যাকশন বেছে নিন...</option>
                    <option value="publish">নির্বাচিতগুলো প্রকাশ ও অনুমোদন করুন</option>
                    <option value="draft">নির্বাচিতগুলো ড্রাফট করুন</option>
                    <option value="delete">নির্বাচিতগুলো মুছে ফেলুন</option>
                </select>
                <button type="submit" class="btn btn-dark btn-sm rounded-end-pill px-3 fw-semibold" onclick="return confirmBulkAction()">
                    প্রয়োগ
                </button>
            </div>
        </div>

        <div class="text-muted small">
            মোট @bn($posts->total())টি পোস্টের মধ্যে @bn($posts->firstItem() ?? 0)–@bn($posts->lastItem() ?? 0) দেখাচ্ছে
        </div>
    </div>

    <div class="adm-card shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
        @if ($posts->isEmpty())
            <div class="empty-state py-5 text-center">
                <i class="fas fa-newspaper fs-1 text-muted opacity-50 mb-3"></i>
                <h5 class="fw-bold text-muted">কোনো ব্লগ পোস্ট পাওয়া যায়নি</h5>
                <p class="text-muted small">নতুন পোস্ট তৈরি করুন অথবা অন্য ফিল্টার ব্যবহার করে দেখুন।</p>
                <a href="{{ route('admin.content.create', 'blog') }}" class="btn btn-primary btn-sm rounded-pill px-4">
                    <i class="fas fa-plus me-1"></i> নতুন পোস্ট লিখুন
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table adm-table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 40px;"></th>
                            <th style="width: 40px;">#</th>
                            <th style="width: 40px;" class="text-center" title="নির্বাচিত পোস্ট">★</th>
                            <th style="min-width: 260px;">পোস্টের শিরোনাম ও প্রচ্ছদ</th>
                            <th>লেখক / জমাদানকারী</th>
                            <th>ক্যাটাগরি</th>
                            <th>অবস্থা (Status)</th>
                            <th>ভিউ</th>
                            <th>তারিখ</th>
                            <th class="text-end pe-3" style="min-width: 170px;">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($posts as $n => $post)
                            @php
                                $isPublished = ($post->status === 'published' || $post->mod_status === 'approved');
                                $isPending = ($post->status === 'pending' || $post->mod_status === 'pending');
                                $isRejected = ($post->status === 'rejected' || $post->mod_status === 'rejected');
                                $isDraft = ($post->status === 'draft');
                                $coverImg = $post->cover_url ?: ($post->featured_image ? (str_starts_with($post->featured_image, 'http') ? $post->featured_image : asset('storage/' . ltrim($post->featured_image, '/'))) : null);
                            @endphp
                            <tr id="postRow{{ $post->id }}">
                                <!-- Checkbox -->
                                <td class="ps-3">
                                    <input class="form-check-input row-checkbox" type="checkbox" name="selected_ids[]" value="{{ $post->id }}">
                                </td>

                                <!-- Row Number -->
                                <td class="text-muted small">@bn($posts->firstItem() + $n)</td>

                                <!-- Featured Star Toggle -->
                                <td class="text-center">
                                    <button type="button" class="btn-featured-star {{ $post->is_featured ? 'active' : '' }}" 
                                            id="starBtn{{ $post->id }}"
                                            onclick="toggleFeatured({{ $post->id }})" 
                                            title="{{ $post->is_featured ? 'নির্বাচিত পোস্ট (ক্লিক করে আন-ফিচার্ড করুন)' : 'সাধারণ পোস্ট (ক্লিক করে ফিচার্ড করুন)' }}">
                                        <i class="fa-solid fa-star"></i>
                                    </button>
                                </td>

                                <!-- Post Title & Thumbnail -->
                                <td>
                                    <div class="d-flex align-items-center gap-2.5">
                                        @if($coverImg)
                                            <img src="{{ $coverImg }}" alt="{{ $post->title }}" 
                                                 class="rounded-3 flex-shrink-0 shadow-xs object-fit-cover" 
                                                 style="width: 48px; height: 48px; border: 1px solid #e2e8f0;">
                                        @else
                                            <div class="rounded-3 bg-light d-flex align-items-center justify-content-center flex-shrink-0 text-muted" 
                                                 style="width: 48px; height: 48px; border: 1px solid #e2e8f0;">
                                                <i class="fas fa-file-lines opacity-50"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <a href="{{ route('admin.content.edit', ['type' => 'blog', 'id' => $post->id]) }}" 
                                               class="fw-bold text-dark text-decoration-none hover-primary d-block line-clamp-1" title="{{ $post->title }}">
                                                {{ $post->title }}
                                            </a>
                                            <div class="text-muted small" style="font-size: 0.78rem;">
                                                <span class="font-monospace text-muted">{{ $post->slug }}</span>
                                            </div>
                                            @if($isRejected && $post->rejection_reason)
                                                <div class="small text-danger mt-0.5" style="font-size: 0.76rem;">
                                                    <i class="fas fa-info-circle me-1"></i>{{ $post->rejection_reason }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Author / Submitter -->
                                <td>
                                    <div class="fw-semibold text-dark">{{ $post->author?->name ?? $post->submitter?->name ?? $post->author_name ?? '—' }}</div>
                                    @if($post->author?->phone)
                                        <div class="text-muted small" style="font-size: 0.76rem;">
                                            <a href="https://wa.me/88{{ preg_replace('/[^0-9]/', '', $post->author->phone) }}" target="_blank" class="text-decoration-none text-muted">
                                                <i class="fab fa-whatsapp text-success me-1"></i>{{ $post->author->phone }}
                                            </a>
                                        </div>
                                    @endif
                                </td>

                                <!-- Category -->
                                <td>
                                    <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1">
                                        {{ $post->category?->name ?? 'সাধারণ' }}
                                    </span>
                                </td>

                                <!-- Dynamic Status Dropdown -->
                                <td>
                                    <select class="form-select form-select-sm status-select-badge 
                                            {{ $isPublished ? 'bg-success-subtle text-success border-success-subtle' : ($isPending ? 'bg-warning-subtle text-warning border-warning-subtle' : ($isRejected ? 'bg-danger-subtle text-danger border-danger-subtle' : 'bg-secondary-subtle text-secondary border-secondary-subtle')) }}" 
                                            id="statusSelect{{ $post->id }}"
                                            onchange="updatePostStatus({{ $post->id }}, this.value)">
                                        <option value="published" @selected($isPublished)>✓ প্রকাশিত</option>
                                        <option value="pending" @selected($isPending)>⏳ অপেক্ষমাণ</option>
                                        <option value="draft" @selected($isDraft)>📝 খসড়া</option>
                                        <option value="rejected" @selected($isRejected)>✕ বাতিল</option>
                                    </select>
                                </td>

                                <!-- Views -->
                                <td class="text-muted small">
                                    <i class="fas fa-eye text-primary me-1"></i>@bn($post->views_count ?? $post->view_count ?? 0)
                                </td>

                                <!-- Date -->
                                <td class="text-muted small">
                                    {{ $post->published_at ? $post->published_at->format('d M, Y') : ($post->created_at ? $post->created_at->format('d M, Y') : '—') }}
                                </td>

                                <!-- Actions -->
                                <td class="text-end pe-3">
                                    <div class="d-flex align-items-center justify-content-end gap-1.5">
                                        @if($isPending)
                                            <button type="button" class="btn btn-sm btn-success px-2 py-1 shadow-xs" 
                                                    onclick="updatePostStatus({{ $post->id }}, 'published')" title="অনুমোদন ও প্রকাশ করুন">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif

                                        <a href="{{ route('admin.content.edit', ['type' => 'blog', 'id' => $post->id]) }}" 
                                           class="btn btn-sm btn-outline-primary px-2 py-1 shadow-xs" title="সম্পাদনা করুন">
                                            <i class="fas fa-pen-to-square"></i>
                                        </a>

                                        @if($isPublished)
                                            <a href="{{ route('blog.show', $post->slug) }}" target="_blank" rel="noopener" 
                                               class="btn btn-sm btn-light border px-2 py-1 shadow-xs text-primary" title="সাইটে পড়ুন">
                                                <i class="fas fa-arrow-up-right-from-square"></i>
                                            </a>
                                        @endif

                                        <button type="button" class="btn btn-sm btn-outline-danger px-2 py-1 shadow-xs" 
                                                onclick="deletePost({{ $post->id }}, '{{ addslashes($post->title) }}')" title="মুছে ফেলুন">
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($posts->hasPages())
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 p-3 bg-white border-top">
                    <span class="text-muted small">
                        মোট @bn($posts->total())টির মধ্যে @bn($posts->firstItem())–@bn($posts->lastItem()) দেখানো হচ্ছে
                    </span>
                    {{ $posts->links() }}
                </div>
            @endif
        @endif
    </div>
</form>

{{-- ========================================================================= --}}
{{-- 5. BLOG DESIGN & BANNER CUSTOMIZER MODAL                                  --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="blogCustomizerModal" tabindex="-1" aria-labelledby="blogCustomizerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-3 px-4">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle bg-primary bg-opacity-25 text-primary p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="fas fa-palette text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold fs-6 mb-0 text-white" id="blogCustomizerModalLabel">আইডিয়াপত্র ও ব্লগ ডাইনামিক ডিজাইন ও ব্যানার কাস্টমাইজার</h5>
                        <span class="small text-white-50" style="font-size: 0.78rem;">সোশ্যাল ব্যানার, হেডার গ্র্যাডিয়েন্ট, ফন্ট, লাইন স্পেসিং ও রিডিং লেআউট নিয়ন্ত্রণ করুন</span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('admin.blog.settings.update') }}" method="POST" enctype="multipart/form-data" id="blogCustomizerForm">
                @csrf
                <div class="modal-body p-4 bg-light">
                    <div class="row g-4">
                        
                        <!-- Left Column: Controls & Tabs -->
                        <div class="col-lg-7">
                            
                            <!-- Customizer Navigation Tabs -->
                            <ul class="nav nav-pills nav-fill bg-white p-1.5 rounded-pill shadow-xs border mb-3" id="customizerTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active rounded-pill fw-semibold py-1.5 small" id="tab-banner-btn" data-bs-toggle="pill" data-bs-target="#tab-banner" type="button" role="tab">
                                        <i class="fas fa-image me-1 text-warning"></i> ব্যানার ও হেডার
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link rounded-pill fw-semibold py-1.5 small" id="tab-typography-btn" data-bs-toggle="pill" data-bs-target="#tab-typography" type="button" role="tab">
                                        <i class="fas fa-font me-1 text-primary"></i> ফন্ট ও লাইন স্পেস
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link rounded-pill fw-semibold py-1.5 small" id="tab-reading-btn" data-bs-toggle="pill" data-bs-target="#tab-reading" type="button" role="tab">
                                        <i class="fas fa-book-open me-1 text-success"></i> রিডার ও লেআউট
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content" id="customizerTabsContent">
                                
                                <!-- Tab 1: Header & Social Media Open Graph Banner -->
                                <div class="tab-pane fade show active" id="tab-banner" role="tabpanel">
                                    <div class="card border-0 shadow-xs rounded-3 p-3.5 bg-white mb-3">
                                        <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                                            <i class="fas fa-share-nodes text-primary"></i>
                                            <span>সোশ্যাল মিডিয়া ও আইডিয়াপত্র ব্যানার</span>
                                        </h6>

                                        <div class="row g-3">
                                            <!-- Current Banner Preview & Uploader -->
                                            <div class="col-12">
                                                <div class="p-3 bg-light rounded-4 border mb-3 text-center">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <span class="badge bg-white text-dark border small fw-bold">
                                                            <i class="fas fa-crop-simple text-primary me-1"></i> ১২০০ × ৬৩০ px (১৬:৯ রেশিও)
                                                        </span>
                                                        <span class="small text-muted">লাইভ ক্রপার সক্রিয়</span>
                                                    </div>

                                                    <div class="banner-thumb-box mx-auto mb-2 shadow-xs" style="max-width: 320px; aspect-ratio: 16/9;">
                                                        <img src="{{ $blogOgBannerUrl }}" alt="Banner Preview" id="modalBannerPreview">
                                                    </div>

                                                    <div class="text-start mt-3">
                                                        <label class="form-label small fw-bold text-dark mb-1">কাস্টম ব্যানার ফাইল নির্বাচন ও ক্রপ করুন:</label>
                                                        <input type="file" id="modalBannerInput" name="blog_og_banner" class="form-control rounded-3" accept="image/*" onchange="initBannerCropper(this)">
                                                        <input type="hidden" name="blog_og_banner_cropped" id="modalBannerCropped">
                                                        <div class="form-text small text-muted">ফাইল নির্বাচন করলে স্বয়ংক্রিয় ১৬:৯ ক্রপ উইন্ডো ওপেন হবে।</div>
                                                    </div>

                                                    <div class="form-check text-start mt-2">
                                                        <input class="form-check-input" type="checkbox" name="remove_blog_og_banner" value="1" id="rmBlogBannerModal">
                                                        <label class="form-check-label small text-danger fw-semibold" for="rmBlogBannerModal">কাস্টম ব্যানার মুছে ডিফল্ট ব্যানার ব্যবহার করুন</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Header Gradient Scheme -->
                                            <div class="col-12">
                                                <label class="form-label small fw-bold text-dark mb-1">হেডার কালার থিম (Header Banner Gradient):</label>
                                                <select name="header_gradient" id="custHeaderGradient" class="form-select form-select-sm" onchange="updateLivePreview()">
                                                    <option value="linear-gradient(135deg, #0c4a6e 0%, #0369a1 50%, #0284c7 100%)" @selected(($blogSettings['header_gradient'] ?? '') == 'linear-gradient(135deg, #0c4a6e 0%, #0369a1 50%, #0284c7 100%)')>নীল ও আকাশী (Ocean Blue Gradient)</option>
                                                    <option value="linear-gradient(135deg, #064e3b 0%, #047857 50%, #059669 100%)" @selected(($blogSettings['header_gradient'] ?? '') == 'linear-gradient(135deg, #064e3b 0%, #047857 50%, #059669 100%)')>সবুজ ও পান্না (Emerald Green Gradient)</option>
                                                    <option value="linear-gradient(135deg, #312e81 0%, #4338ca 50%, #6366f1 100%)" @selected(($blogSettings['header_gradient'] ?? '') == 'linear-gradient(135deg, #312e81 0%, #4338ca 50%, #6366f1 100%)')>রয়েল ইন্ডিগো (Royal Indigo Gradient)</option>
                                                    <option value="linear-gradient(135deg, #18181b 0%, #27272a 50%, #3f3f46 100%)" @selected(($blogSettings['header_gradient'] ?? '') == 'linear-gradient(135deg, #18181b 0%, #27272a 50%, #3f3f46 100%)')>ক্লাসিক চারকোল (Classic Dark Graphite)</option>
                                                    <option value="linear-gradient(135deg, #881337 0%, #9f1239 50%, #be123c 100%)" @selected(($blogSettings['header_gradient'] ?? '') == 'linear-gradient(135deg, #881337 0%, #9f1239 50%, #be123c 100%)')>ডিপ মেরুন (Deep Crimson Velvet)</option>
                                                </select>
                                            </div>

                                            <!-- Hero Badge -->
                                            <div class="col-12">
                                                <label class="form-label small fw-bold text-dark mb-1">ব্যানার ব্যাজ টেক্সট (Top Badge):</label>
                                                <input type="text" name="hero_badge" id="custHeroBadge" class="form-control form-control-sm" 
                                                       value="{{ $blogSettings['hero_badge'] ?? 'সাহিত্য, শিল্প-সংস্কৃতি, গবেষণা ও মুক্তচিন্তা' }}" oninput="updateLivePreview()">
                                            </div>

                                            <!-- Hero Title -->
                                            <div class="col-12">
                                                <label class="form-label small fw-bold text-dark mb-1">মূল শিরোনাম (Hero Title):</label>
                                                <input type="text" name="hero_title" id="custHeroTitle" class="form-control form-control-sm fw-bold" 
                                                       value="{{ $blogSettings['hero_title'] ?? 'আইডিয়াপত্র — সমকালীন সাহিত্য ও চিন্তা' }}" oninput="updateLivePreview()">
                                            </div>

                                            <!-- Hero Subtitle -->
                                            <div class="col-12">
                                                <label class="form-label small fw-bold text-dark mb-1">সাব-শিরোনাম ও বিবরণী (Hero Subtitle):</label>
                                                <textarea name="hero_subtitle" id="custHeroSubtitle" rows="2" class="form-control form-control-sm" oninput="updateLivePreview()">{{ $blogSettings['hero_subtitle'] ?? 'সমকালীন সাহিত্য আলোচনা, প্রবন্ধ, ছোটগল্প, কবিতা, নতুন বইয়ের প্রামাণ্য পর্যালোচনা ও গবেষণামূলক লেখার উন্মুক্ত ডিজিটাল সাময়িকী।' }}</textarea>
                                            </div>

                                            <!-- Write Button Text & URL -->
                                            <div class="col-6">
                                                <label class="form-label small fw-bold text-dark mb-1">লেখা জমা বাটন টেক্সট:</label>
                                                <input type="text" name="write_button_text" id="custWriteBtnText" class="form-control form-control-sm" 
                                                       value="{{ $blogSettings['write_button_text'] ?? 'নিজের লেখা পোস্ট করুন' }}" oninput="updateLivePreview()">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small fw-bold text-dark mb-1">বাটন লিংক (URL):</label>
                                                <input type="text" name="write_button_url" id="custWriteBtnUrl" class="form-control form-control-sm" 
                                                       value="{{ $blogSettings['write_button_url'] ?? '/blog/write' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab 2: Typography & Line Spacing -->
                                <div class="tab-pane fade" id="tab-typography" role="tabpanel">
                                    <div class="card border-0 shadow-xs rounded-3 p-3.5 bg-white mb-3">
                                        <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                                            <i class="fas fa-text-height text-primary"></i>
                                            <span>লেখার ফন্ট ও লাইন বিন্যাস</span>
                                        </h6>

                                        <div class="row g-3">
                                            <!-- Font Family -->
                                            <div class="col-12">
                                                <label class="form-label small fw-bold text-dark mb-1">সাহিত্যিক বাংলা ফন্ট (Font Family):</label>
                                                <select name="font_family" id="custFontFamily" class="form-select form-select-sm" onchange="updateLivePreview()">
                                                    <option value="'Hind Siliguri', 'Kalpurush', 'SolaimanLipi', sans-serif" @selected(($blogSettings['font_family'] ?? '') == "'Hind Siliguri', 'Kalpurush', 'SolaimanLipi', sans-serif")>হিন্দ শিলিগুড়ি (Hind Siliguri - আধুনিক ও প্রমিত)</option>
                                                    <option value="'Kalpurush', 'SolaimanLipi', Georgia, serif" @selected(($blogSettings['font_family'] ?? '') == "'Kalpurush', 'SolaimanLipi', Georgia, serif")>কালপুরুষ (Kalpurush - ক্লাসিক সাহিত্যপত্র ফন্ট)</option>
                                                    <option value="'SolaimanLipi', 'Hind Siliguri', sans-serif" @selected(($blogSettings['font_family'] ?? '') == "'SolaimanLipi', 'Hind Siliguri', sans-serif")>সোলায়মান লিপি (SolaimanLipi - স্পষ্ট প্রকাশনা)</option>
                                                    <option value="'Nikosh', 'Kalpurush', serif" @selected(($blogSettings['font_family'] ?? '') == "'Nikosh', 'Kalpurush', serif")>নিকোশ (Nikosh - প্রাতিষ্ঠানিক ফন্ট)</option>
                                                </select>
                                            </div>

                                            <!-- Base Font Size -->
                                            <div class="col-6">
                                                <label class="form-label small fw-bold text-dark mb-1">মূল ফন্ট সাইজ:</label>
                                                <select name="reading_font_size" id="custFontSize" class="form-select form-select-sm" onchange="updateLivePreview()">
                                                    <option value="1.0rem" @selected(($blogSettings['reading_font_size'] ?? '') == '1.0rem')>কম্প্যাক্ট (16px)</option>
                                                    <option value="1.08rem" @selected(($blogSettings['reading_font_size'] ?? '') == '1.08rem' || !isset($blogSettings['reading_font_size']))>আদর্শ রিডিং (17.5px)</option>
                                                    <option value="1.15rem" @selected(($blogSettings['reading_font_size'] ?? '') == '1.15rem')>মাঝারি বড় (18.5px)</option>
                                                    <option value="1.25rem" @selected(($blogSettings['reading_font_size'] ?? '') == '1.25rem')>বড় ফন্ট (20px)</option>
                                                </select>
                                            </div>

                                            <!-- Base Line Height -->
                                            <div class="col-6">
                                                <label class="form-label small fw-bold text-dark mb-1">গদ্য / প্রবন্ধ লাইন স্পেস:</label>
                                                <select name="line_height" id="custLineHeight" class="form-select form-select-sm" onchange="updateLivePreview()">
                                                    <option value="1.45" @selected(($blogSettings['line_height'] ?? '') == '1.45')>অতি ঘন (1.45)</option>
                                                    <option value="1.55" @selected(($blogSettings['line_height'] ?? '') == '1.55')>ঘন ও আঁটসাঁট (1.55)</option>
                                                    <option value="1.6" @selected(($blogSettings['line_height'] ?? '') == '1.6' || !isset($blogSettings['line_height']))>আদর্শ ক্লাসিক (1.60)</option>
                                                    <option value="1.75" @selected(($blogSettings['line_height'] ?? '') == '1.75')>স্বাভাবিক ফাঁকা (1.75)</option>
                                                    <option value="1.9" @selected(($blogSettings['line_height'] ?? '') == '1.9')>অধিক ফাঁকা (1.90)</option>
                                                </select>
                                            </div>

                                            <!-- Poetry Line Height -->
                                            <div class="col-6">
                                                <label class="form-label small fw-bold text-dark mb-1">কবিতার লাইনের ফাঁকা (Poetry Line Height):</label>
                                                <select name="poetry_line_height" id="custPoetryLineHeight" class="form-select form-select-sm" onchange="updateLivePreview()">
                                                    <option value="1.35" @selected(($blogSettings['poetry_line_height'] ?? '') == '1.35')>অতি কম ফাঁকা (1.35)</option>
                                                    <option value="1.45" @selected(($blogSettings['poetry_line_height'] ?? '') == '1.45' || !isset($blogSettings['poetry_line_height']))>আদর্শ কাব্যিক লাইন (1.45)</option>
                                                    <option value="1.55" @selected(($blogSettings['poetry_line_height'] ?? '') == '1.55')>মাঝারি ফাঁকা (1.55)</option>
                                                    <option value="1.75" @selected(($blogSettings['poetry_line_height'] ?? '') == '1.75')>অধিক ফাঁকা (1.75)</option>
                                                </select>
                                            </div>

                                            <!-- Poetry Alignment -->
                                            <div class="col-6">
                                                <label class="form-label small fw-bold text-dark mb-1">কবিতা সারিবদ্ধতা (Poetry Align):</label>
                                                <select name="poetry_align" id="custPoetryAlign" class="form-select form-select-sm" onchange="updateLivePreview()">
                                                    <option value="left" @selected(($blogSettings['poetry_align'] ?? '') == 'left')>বাম দিক থেকে শুরু (Left)</option>
                                                    <option value="center" @selected(($blogSettings['poetry_align'] ?? '') == 'center')>মাঝখানে কেন্দ্রিক (Center)</option>
                                                    <option value="justify" @selected(($blogSettings['poetry_align'] ?? '') == 'justify')>জাস্টিফাই (Justify)</option>
                                                </select>
                                            </div>

                                            <!-- Paragraph Spacing Margin -->
                                            <div class="col-12">
                                                <label class="form-label small fw-bold text-dark mb-1">প্রতিটি স্তবক ও প্যারার মধ্যবর্তী দূরত্ব (Gap):</label>
                                                <select name="paragraph_margin" id="custParaMargin" class="form-select form-select-sm" onchange="updateLivePreview()">
                                                    <option value="0.55rem" @selected(($blogSettings['paragraph_margin'] ?? '') == '0.55rem')>অতি সংকুচিত (0.55rem)</option>
                                                    <option value="0.85rem" @selected(($blogSettings['paragraph_margin'] ?? '') == '0.85rem' || !isset($blogSettings['paragraph_margin']))>আদর্শ কমপ্যাক্ট গ্যাপ (0.85rem)</option>
                                                    <option value="1.15rem" @selected(($blogSettings['paragraph_margin'] ?? '') == '1.15rem')>স্বাভাবিক গ্যাপ (1.15rem)</option>
                                                    <option value="1.5rem" @selected(($blogSettings['paragraph_margin'] ?? '') == '1.5rem')>বেশি ফাঁকা (1.5rem)</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab 3: Reader & Layout Controls -->
                                <div class="tab-pane fade" id="tab-reading" role="tabpanel">
                                    <div class="card border-0 shadow-xs rounded-3 p-3.5 bg-white mb-3">
                                        <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                                            <i class="fas fa-layer-group text-primary"></i>
                                            <span>রিডার ও সাইটের বাহ্যিক অপশন</span>
                                        </h6>

                                        <div class="row g-3">
                                            <!-- Paper Background Color -->
                                            <div class="col-12">
                                                <label class="form-label small fw-bold text-dark mb-1">বইয়ের পাতার ব্যাকগ্রাউন্ড (Article Sheet Background):</label>
                                                <select name="reading_bg" id="custReadingBg" class="form-select form-select-sm" onchange="updateLivePreview()">
                                                    <option value="#ffffff" @selected(($blogSettings['reading_bg'] ?? '') == '#ffffff')>পরিচ্ছন্ন সাদা (Clean White Paper)</option>
                                                    <option value="#fbf9f4" @selected(($blogSettings['reading_bg'] ?? '') == '#fbf9f4')>আইভরি বুক পেজ (Ivory Literary Book)</option>
                                                    <option value="#f8f4eb" @selected(($blogSettings['reading_bg'] ?? '') == '#f8f4eb')>নরম সেপিয়া (Soft Sepia Reading)</option>
                                                </select>
                                            </div>

                                            <!-- Toggles -->
                                            <div class="col-12 pt-2">
                                                <div class="form-check form-switch mb-2">
                                                    <input class="form-check-input" type="checkbox" role="switch" name="show_reading_bar" id="custShowReadingBar" value="1" 
                                                           @checked(($blogSettings['show_reading_bar'] ?? '1') == '1')>
                                                    <label class="form-check-label small fw-semibold text-dark" for="custShowReadingBar">
                                                        টপ রিডিং টুলবার প্রদর্শন (প্রিন্ট, ফন্ট জুম ও সেপিয়া মোড)
                                                    </label>
                                                </div>
                                                <div class="form-check form-switch mb-2">
                                                    <input class="form-check-input" type="checkbox" role="switch" name="enable_share_bar" id="custEnableShareBar" value="1" 
                                                           @checked(($blogSettings['enable_share_bar'] ?? '1') == '1')>
                                                    <label class="form-check-label small fw-semibold text-dark" for="custEnableShareBar">
                                                        সোশ্যাল শেয়ারিং ও ফটোকার্ড ডাউনলোড সুবিধা
                                                    </label>
                                                </div>
                                                <div class="form-check form-switch mb-0">
                                                    <input class="form-check-input" type="checkbox" role="switch" name="show_author_box" id="custShowAuthorBox" value="1" 
                                                           @checked(($blogSettings['show_author_box'] ?? '1') == '1')>
                                                    <label class="form-check-label small fw-semibold text-dark" for="custShowAuthorBox">
                                                        লেখার নিচে বিস্তারিত লেখক পরিচিতি ও সম্পর্কিত রচনা প্রদর্শন
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Right Column: Interactive Live Preview -->
                        <div class="col-lg-5">
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden sticky-top" style="top: 15px;">
                                <div class="card-header bg-dark text-white py-2 px-3 d-flex align-items-center justify-content-between">
                                    <span class="small fw-bold"><i class="fas fa-eye me-1 text-warning"></i> রিয়েল-টাইম লাইভ প্রিভিউ</span>
                                    <span class="badge bg-primary-subtle text-primary rounded-pill small" style="font-size: 10px;">লাইভ আপডেট</span>
                                </div>
                                <div class="card-body p-3" style="background-color: #f1f5f9; max-height: 520px; overflow-y: auto;">
                                    
                                    <!-- Preview Hero Masthead -->
                                    <div id="prevHeaderBox" class="p-3 rounded-3 text-white mb-3 shadow-xs position-relative overflow-hidden" 
                                         style="background: {{ $blogSettings['header_gradient'] ?? 'linear-gradient(135deg, #0c4a6e 0%, #0369a1 50%, #0284c7 100%)' }};">
                                        <span id="prevBadgeText" class="badge bg-white bg-opacity-25 rounded-pill mb-1.5 px-2 py-0.5" style="font-size: 10px;">
                                            {{ $blogSettings['hero_badge'] ?? 'সাহিত্য, শিল্প-সংস্কৃতি, গবেষণা ও মুক্তচিন্তা' }}
                                        </span>
                                        <h6 id="prevHeroTitle" class="fw-bold mb-1" style="font-size: 1.05rem;">
                                            {{ $blogSettings['hero_title'] ?? 'আইডিয়া ব্লগ ও সাহিত্যপত্র' }}
                                        </h6>
                                        <p id="prevHeroSubtitle" class="small opacity-90 mb-2" style="font-size: 11px; line-height: 1.4;">
                                            {{ $blogSettings['hero_subtitle'] ?? 'সমকালীন সাহিত্য আলোচনা, প্রবন্ধ, ছোটগল্প, কবিতা...' }}
                                        </p>
                                        <button type="button" id="prevWriteBtn" class="btn btn-warning btn-xs rounded-pill px-2.5 py-1 fw-bold text-dark">
                                            <i class="fas fa-feather-pointed me-1"></i> <span>{{ $blogSettings['write_button_text'] ?? 'নিজের লেখা পোস্ট করুন' }}</span>
                                        </button>
                                    </div>

                                    <!-- Preview Book Sheet -->
                                    <div id="prevBookSheet" class="p-3.5 rounded-3 border shadow-xs" 
                                         style="background-color: {{ $blogSettings['reading_bg'] ?? '#ffffff' }}; font-family: {{ $blogSettings['font_family'] ?? 'sans-serif' }};">
                                        
                                        <div class="border-bottom pb-2 mb-2.5">
                                            <span class="badge bg-primary-subtle text-primary mb-1" style="font-size: 10px;">কবিতা</span>
                                            <h5 class="fw-bold text-dark mb-0.5" style="font-size: 1.15rem;">নিঃসঙ্গতার প্রহর</h5>
                                            <small class="text-muted" style="font-size: 11px;">লেখক: আল আমিন ইসলাম • সাহিত্যপত্র সংস্করণ</small>
                                        </div>

                                        <!-- Preview Content -->
                                        <div id="prevArticleContent" style="font-size: {{ $blogSettings['reading_font_size'] ?? '1.08rem' }}; line-height: {{ $blogSettings['line_height'] ?? '1.6' }};">
                                            
                                            <!-- Poetry Sample -->
                                            <p id="prevPoetryVerse" class="poetry-verse p-2 border-start border-3 border-primary bg-primary bg-opacity-10 rounded-end mb-2" 
                                               style="line-height: {{ $blogSettings['poetry_line_height'] ?? '1.45' }}; margin-bottom: {{ $blogSettings['paragraph_margin'] ?? '0.85rem' }}; text-align: {{ $blogSettings['poetry_align'] ?? 'left' }}; font-size: 1.05em;">
                                                কেউ নাই এ নিশিযাপনে<br>
                                                ধূসর আকাশে চাঁদ যেন এক ক্লান্ত পথিক;<br>
                                                বাঁশবনের ভেতর দিয়ে বাতাস হেঁটে যায়...
                                            </p>

                                            <!-- Prose Sample -->
                                            <p id="prevProsePara" class="text-dark" style="margin-bottom: {{ $blogSettings['paragraph_margin'] ?? '0.85rem' }}; text-align: justify;">
                                                সাহিত্য কেবল শব্দের কারুকাজ নয়, মানুষের অনুভূতির গভীরতম রূপায়ণ। প্রতিটি পঙক্তি বয়ে আনে জীবনের নানা অব্যক্ত সুর ও দর্শন।
                                            </p>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer bg-white py-3 px-4 border-top d-flex justify-content-between">
                    <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">বন্ধ করুন</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-xs">
                        <i class="fas fa-save me-1.5"></i> সেটিংস সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- 6. BULK BLOG TYPOGRAPHY NORMALIZER MODAL                                  --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="bulkTypographyModal" tabindex="-1" aria-labelledby="bulkTypographyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-success text-white py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-wand-magic-sparkles fs-5"></i>
                    <h5 class="modal-title fw-bold fs-6 mb-0 text-white" id="bulkTypographyModalLabel">সকল লেখার লাইন ও প্যারা স্পেস মেরামত ইঞ্জিন</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div class="text-center mb-3">
                    <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 d-inline-flex align-items-center justify-content-center mb-2" style="width: 60px; height: 60px;">
                        <i class="fas fa-compress-alt fs-3"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">সাইটের সকল ব্লগ পোস্ট স্বয়ংক্রিয়ভাবে অপ্টিমাইজ করুন</h6>
                    <p class="small text-muted mb-0">
                        পূর্বে পোস্ট করা যেসব লেখার লাইনের ফাঁকা অতিরিক্ত বেশি বা এলোমেলো হয়ে আছে, এই ইঞ্জিন এক ক্লিকে সেগুলোর ইনলাইন স্টাইল ও স্তবক মার্জিন আদর্শ কমপ্যাক্ট মাপে রূপান্তর করবে।
                    </p>
                </div>

                <div id="bulkProcessNotice" class="alert alert-info p-2.5 small mb-3 rounded-3 d-flex align-items-center gap-2">
                    <i class="fas fa-circle-info fs-5 text-info"></i>
                    <div>কোনো ডাটা বা লেখা নষ্ট হবে না, শুধু অতিরিক্ত ফাঁকা লাইন স্পেসগুলো নিখুঁত করা হবে।</div>
                </div>

                <div id="bulkProgressBox" class="d-none mb-3">
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 100%"></div>
                    </div>
                    <div class="text-center small text-muted mt-2" id="bulkProgressText">
                        পোস্টগুলো প্রসেস হচ্ছে, অনুগ্রহ করে অপেক্ষা করুন...
                    </div>
                </div>

                <div id="bulkResultAlert"></div>

                <div class="mb-2">
                    <label class="form-label small fw-bold text-dark">কোন পোস্টগুলো ঠিক করবেন?</label>
                    <select id="bulkTargetSelect" class="form-select form-select-sm">
                        <option value="all">সকল পোস্ট (অনুমোদিত, ড্রাফট ও অপেক্ষমাণ)</option>
                        <option value="published">শুধুমাত্র প্রকাশিত ও অনুমোদিত পোস্ট</option>
                    </select>
                </div>
            </div>

            <div class="modal-footer bg-light py-2.5 px-4">
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">বাতিল</button>
                <button type="button" id="startBulkNormalizeBtn" class="btn btn-sm btn-success rounded-pill px-4 fw-bold shadow-xs" onclick="runBulkNormalizeTypography()">
                    <i class="fas fa-play me-1"></i> মেরামত শুরু করুন
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- 7. BANNER CROPPER MODAL                                                   --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="bannerCropperModal" tabindex="-1" aria-labelledby="bannerCropperModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-2.5 px-4">
                <h5 class="modal-title fw-bold fs-6 d-flex align-items-center gap-2" id="bannerCropperModalLabel">
                    <i class="fas fa-crop-simple text-warning"></i>
                    <span>আইডিয়াপত্র ও সোশ্যাল ব্যানার ক্রপ ও রিসাইজ (১৬:৯)</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 text-center bg-light">
                <div style="max-height: 440px; overflow: hidden; background: #000; border-radius: 8px;">
                    <img id="bannerCropperImageEl" src="" alt="Crop Target" style="max-width: 100%; max-height: 440px; display: block; margin: 0 auto;">
                </div>
                <div class="d-flex justify-content-center gap-2 mt-3 flex-wrap">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" onclick="blogCropper && blogCropper.zoom(0.1)" title="জুম ইন">
                        <i class="fas fa-magnifying-glass-plus me-1"></i> জুম ইন
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" onclick="blogCropper && blogCropper.zoom(-0.1)" title="জুম আউট">
                        <i class="fas fa-magnifying-glass-minus me-1"></i> জুম আউট
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" onclick="blogCropper && blogCropper.rotate(-90)" title="বামে ঘোরান">
                        <i class="fas fa-rotate-left me-1"></i> বামে ঘোরান
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" onclick="blogCropper && blogCropper.rotate(90)" title="ডানে ঘোরান">
                        <i class="fas fa-rotate-right me-1"></i> ডানে ঘোরান
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" onclick="blogCropper && blogCropper.reset()" title="রিসেট">
                        <i class="fas fa-arrows-rotate me-1"></i> রিসেট
                    </button>
                </div>
            </div>
            <div class="modal-footer bg-white py-2.5 px-4 border-top d-flex justify-content-between">
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">বাতিল</button>
                <button type="button" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-xs" onclick="applyBannerCrop()">
                    <i class="fas fa-check me-1"></i> ক্রপ নিশ্চিত করুন
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
<script>
let blogCropper = null;
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

function switchToBannerTab() {
    const tabBtn = document.getElementById('tab-banner-btn');
    if (tabBtn) {
        bootstrap.Tab.getOrCreateInstance(tabBtn).show();
    }
}

// Live Banner Cropper Logic
function initBannerCropper(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        reader.onload = function(e) {
            const imageEl = document.getElementById('bannerCropperImageEl');
            imageEl.src = e.target.result;

            const cropModalEl = document.getElementById('bannerCropperModal');
            const cropModal = bootstrap.Modal.getOrCreateInstance(cropModalEl);
            cropModal.show();

            cropModalEl.addEventListener('shown.bs.modal', function onShown() {
                if (blogCropper) {
                    blogCropper.destroy();
                }
                blogCropper = new Cropper(imageEl, {
                    aspectRatio: 16 / 9,
                    viewMode: 2,
                    dragMode: 'move',
                    autoCropArea: 1,
                    restore: false,
                    guides: true,
                    center: true,
                    highlight: false,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    toggleDragModeOnDblclick: false,
                });
                cropModalEl.removeEventListener('shown.bs.modal', onShown);
            });
        };
        reader.readAsDataURL(file);
    }
}

function applyBannerCrop() {
    if (!blogCropper) return;
    const canvas = blogCropper.getCroppedCanvas({
        width: 1200,
        height: 630,
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high',
    });

    const croppedBase64 = canvas.toDataURL('image/jpeg', 0.92);
    document.getElementById('modalBannerCropped').value = croppedBase64;
    
    // Update live previews
    const modalPrev = document.getElementById('modalBannerPreview');
    if (modalPrev) modalPrev.src = croppedBase64;
    
    const heroThumb = document.getElementById('heroBannerThumb');
    if (heroThumb) heroThumb.src = croppedBase64;

    const cropModalEl = document.getElementById('bannerCropperModal');
    bootstrap.Modal.getInstance(cropModalEl)?.hide();
}

// AJAX Toggle Post Featured Star
function toggleFeatured(postId) {
    const starBtn = document.getElementById('starBtn' + postId);
    starBtn.disabled = true;

    fetch(`/admin/blog/${postId}/toggle-featured`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(res => res.json())
    .then(data => {
        starBtn.disabled = false;
        if (data.success) {
            if (data.is_featured) {
                starBtn.classList.add('active');
                starBtn.title = 'নির্বাচিত পোস্ট (ক্লিক করে আন-ফিচার্ড করুন)';
            } else {
                starBtn.classList.remove('active');
                starBtn.title = 'সাধারণ পোস্ট (ক্লিক করে ফিচার্ড করুন)';
            }
        }
    })
    .catch(() => {
        starBtn.disabled = false;
        alert('সার্ভার এরর হয়েছে।');
    });
}

// AJAX Update Post Status
function updatePostStatus(postId, newStatus) {
    const select = document.getElementById('statusSelect' + postId);
    select.disabled = true;

    fetch(`/admin/blog/${postId}/toggle-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(res => res.json())
    .then(data => {
        select.disabled = false;
        if (data.success) {
            // Update select classes
            select.className = 'form-select form-select-sm status-select-badge';
            if (newStatus === 'published') {
                select.classList.add('bg-success-subtle', 'text-success', 'border-success-subtle');
            } else if (newStatus === 'pending') {
                select.classList.add('bg-warning-subtle', 'text-warning', 'border-warning-subtle');
            } else if (newStatus === 'rejected') {
                select.classList.add('bg-danger-subtle', 'text-danger', 'border-danger-subtle');
            } else {
                select.classList.add('bg-secondary-subtle', 'text-secondary', 'border-secondary-subtle');
            }
        }
    })
    .catch(() => {
        select.disabled = false;
        alert('সার্ভার এরর হয়েছে।');
    });
}

// Delete Post Action
function deletePost(postId, title) {
    if (!confirm(`আপনি কি নিশ্চিত যে "${title}" পোস্টটি মুছে ফেলতে চান?`)) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/blog/${postId}`;
    
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);

    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'DELETE';
    form.appendChild(methodInput);

    document.body.appendChild(form);
    form.submit();
}

// Bulk Actions Select All
function toggleSelectAll(masterCheckbox) {
    document.querySelectorAll('.row-checkbox').forEach(cb => {
        cb.checked = masterCheckbox.checked;
    });
}

function confirmBulkAction() {
    const action = document.getElementById('bulkActionSelect').value;
    if (!action) {
        alert('অনুগ্রহ করে একটি বাল্ক অ্যাকশন নির্বাচন করুন।');
        return false;
    }
    const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
    if (checkedCount === 0) {
        alert('অনুগ্রহ করে কমপক্ষে একটি পোস্ট নির্বাচন করুন।');
        return false;
    }
    return confirm(`আপনি কি নির্বাচিত ${checkedCount}টি পোস্টের ওপর এই অ্যাকশন প্রয়োগ করতে চান?`);
}

// Live Preview Controller for Blog Customizer
function updateLivePreview() {
    const font = document.getElementById('custFontFamily')?.value || 'sans-serif';
    const fontSize = document.getElementById('custFontSize')?.value || '1.08rem';
    const lineHeight = document.getElementById('custLineHeight')?.value || '1.6';
    const poetryLineHeight = document.getElementById('custPoetryLineHeight')?.value || '1.45';
    const poetryAlign = document.getElementById('custPoetryAlign')?.value || 'left';
    const paraMargin = document.getElementById('custParaMargin')?.value || '0.85rem';
    const headerGradient = document.getElementById('custHeaderGradient')?.value || '';
    const heroBadge = document.getElementById('custHeroBadge')?.value || '';
    const heroTitle = document.getElementById('custHeroTitle')?.value || '';
    const heroSubtitle = document.getElementById('custHeroSubtitle')?.value || '';
    const writeBtnText = document.getElementById('custWriteBtnText')?.value || '';
    const readingBg = document.getElementById('custReadingBg')?.value || '#ffffff';

    const prevHeaderBox = document.getElementById('prevHeaderBox');
    if (prevHeaderBox && headerGradient) prevHeaderBox.style.background = headerGradient;

    const prevBadgeText = document.getElementById('prevBadgeText');
    if (prevBadgeText) prevBadgeText.innerText = heroBadge;

    const prevHeroTitle = document.getElementById('prevHeroTitle');
    if (prevHeroTitle) prevHeroTitle.innerText = heroTitle;

    const prevHeroSubtitle = document.getElementById('prevHeroSubtitle');
    if (prevHeroSubtitle) prevHeroSubtitle.innerText = heroSubtitle;

    const prevWriteBtn = document.getElementById('prevWriteBtn');
    if (prevWriteBtn) prevWriteBtn.querySelector('span').innerText = writeBtnText;

    const prevBookSheet = document.getElementById('prevBookSheet');
    if (prevBookSheet) {
        prevBookSheet.style.backgroundColor = readingBg;
        prevBookSheet.style.fontFamily = font;
    }

    const prevArticleContent = document.getElementById('prevArticleContent');
    if (prevArticleContent) {
        prevArticleContent.style.fontSize = fontSize;
        prevArticleContent.style.lineHeight = lineHeight;
    }

    const prevPoetryVerse = document.getElementById('prevPoetryVerse');
    if (prevPoetryVerse) {
        prevPoetryVerse.style.lineHeight = poetryLineHeight;
        prevPoetryVerse.style.marginBottom = paraMargin;
        prevPoetryVerse.style.textAlign = poetryAlign;
    }

    const prevProsePara = document.getElementById('prevProsePara');
    if (prevProsePara) {
        prevProsePara.style.marginBottom = paraMargin;
    }
}

// Bulk Normalize AJAX Engine
function runBulkNormalizeTypography() {
    const btn = document.getElementById('startBulkNormalizeBtn');
    const target = document.getElementById('bulkTargetSelect')?.value || 'all';
    const progressBox = document.getElementById('bulkProgressBox');
    const resultAlert = document.getElementById('bulkResultAlert');

    if (!confirm('আপনি কি নিশ্চিত যে সমস্ত লেখার লাইন স্পেস এবং প্যারাগ্রাফ মার্জিন স্বয়ংক্রিয়ভাবে অপ্টিমাইজ করতে চান?')) {
        return;
    }

    btn.disabled = true;
    progressBox.classList.remove('d-none');
    resultAlert.innerHTML = '';

    fetch("{{ route('admin.blog.bulk-normalize-typography') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ target: target })
    })
    .then(res => res.json())
    .then(data => {
        progressBox.classList.add('d-none');
        btn.disabled = false;
        if (data.success) {
            resultAlert.innerHTML = `
                <div class="alert alert-success p-3 small mb-3 rounded-3">
                    <i class="fas fa-circle-check fs-5 text-success me-2"></i>
                    <strong>সফল হয়েছে!</strong> ${data.message}
                </div>`;
            setTimeout(() => {
                location.reload();
            }, 1200);
        } else {
            resultAlert.innerHTML = `
                <div class="alert alert-danger p-3 small mb-3 rounded-3">
                    <i class="fas fa-triangle-exclamation me-1"></i> ${data.message || 'ত্রুটি ঘটেছে'}
                </div>`;
        }
    })
    .catch(() => {
        progressBox.classList.add('d-none');
        btn.disabled = false;
        resultAlert.innerHTML = `
            <div class="alert alert-danger p-3 small mb-3 rounded-3">
                <i class="fas fa-triangle-exclamation me-1"></i> সার্ভার এরর হয়েছে। অনুগ্রহ করে পুনরায় চেষ্টা করুন।
            </div>`;
    });
}
</script>
@endpush

@endsection
