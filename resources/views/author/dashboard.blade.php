@extends('layouts.app')

@section('title', 'লেখক ড্যাশবোর্ড — ' . ($user->name ?? 'লেখক'))

@section('content')
<div class="container py-4 mb-5">

    {{-- Author Profile Hero Card --}}
    <div class="card p-4 p-md-5 mb-4 border-0 shadow-sm rounded-4 text-white position-relative overflow-hidden" 
         style="background: linear-gradient(135deg, #064e3b 0%, #047857 50%, #059669 100%);">
        <div class="position-absolute end-0 bottom-0 opacity-10 d-none d-md-block pe-4 pb-2" style="pointer-events: none;">
            <i class="fas fa-feather-pointed" style="font-size: 13rem;"></i>
        </div>

        <div class="row align-items-center position-relative z-1 g-3">
            <div class="col-md-8">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 bg-white bg-opacity-20 rounded-pill mb-2 backdrop-blur shadow-sm">
                    <i class="fas fa-certificate text-warning"></i>
                    <span class="small fw-semibold text-white">অনুমোদিত লেখক ও গবেষক পোর্টাল</span>
                </div>
                <h1 class="fw-bold display-6 mb-1 text-white">স্বাগতম, {{ $user->name }}!</h1>
                <p class="fs-6 opacity-90 mb-3 text-light">
                    আইডিয়া ব্লগে আপনার জ্ঞানগর্ভ প্রবন্ধ, গল্প, কবিতা ও গবেষণাধর্মী সাহিত্যকর্ম প্রকাশের কেন্দ্রীয় ড্যাশবোর্ড।
                </p>
                <div class="d-flex flex-wrap gap-2 text-white-50 small">
                    <span><i class="fas fa-phone me-1 text-warning"></i>{{ $user->phone ?? 'ফোন নম্বর সংরক্ষিত নেই' }}</span>
                    @if($user->email && !str_contains($user->email, '@author.ideaabd.com'))
                        <span class="ms-md-3"><i class="fas fa-envelope me-1 text-warning"></i>{{ $user->email }}</span>
                    @endif
                    <span class="ms-md-3 badge bg-success-subtle text-success px-2.5 py-1 rounded-pill">
                        <i class="fas fa-circle-check me-1"></i> একাউন্ট সক্রিয়
                    </span>
                </div>
            </div>

            <div class="col-md-4 text-md-end">
                <div class="d-flex flex-column flex-sm-row flex-md-column gap-2 justify-content-md-end">
                    <a href="#write-section" class="btn btn-warning text-dark px-4 py-2.5 rounded-pill fw-bold shadow-sm" onclick="switchTab('write')">
                        <i class="fas fa-pen-nib me-1.5"></i> নতুন লেখা পোস্ট করুন
                    </a>
                    <a href="{{ route('blog.index') }}" class="btn btn-outline-light px-4 py-2 rounded-pill fw-semibold">
                        <i class="fas fa-newspaper me-1.5"></i> মূল ব্লগে যান
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Metric Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center h-100 border-start border-4 border-primary">
                <div class="text-primary mb-1"><i class="fas fa-newspaper fs-4"></i></div>
                <span class="text-muted small fw-semibold d-block">মোট লেখা</span>
                <span class="fw-bold fs-4 text-dark">@bn($stats['total'])</span>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center h-100 border-start border-4 border-success">
                <div class="text-success mb-1"><i class="fas fa-circle-check fs-4"></i></div>
                <span class="text-muted small fw-semibold d-block">প্রকাশিত</span>
                <span class="fw-bold fs-4 text-success">@bn($stats['published'])</span>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center h-100 border-start border-4 border-warning">
                <div class="text-warning mb-1"><i class="fas fa-clock fs-4"></i></div>
                <span class="text-muted small fw-semibold d-block">পর্যালোচনায়</span>
                <span class="fw-bold fs-4 text-warning">@bn($stats['pending'])</span>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center h-100 border-start border-4 border-secondary">
                <div class="text-secondary mb-1"><i class="fas fa-bookmark fs-4"></i></div>
                <span class="text-muted small fw-semibold d-block">খসড়া (Draft)</span>
                <span class="fw-bold fs-4 text-secondary">@bn($stats['draft'])</span>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center h-100 border-start border-4 border-danger">
                <div class="text-danger mb-1"><i class="fas fa-triangle-exclamation fs-4"></i></div>
                <span class="text-muted small fw-semibold d-block">সংশোধন প্রয়োজন</span>
                <span class="fw-bold fs-4 text-danger">@bn($stats['rejected'])</span>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center h-100 border-start border-4 border-info">
                <div class="text-info mb-1"><i class="fas fa-eye fs-4"></i></div>
                <span class="text-muted small fw-semibold d-block">মোট পঠিত (Views)</span>
                <span class="fw-bold fs-4 text-info">@bn($stats['views'])</span>
            </div>
        </div>
    </div>

    {{-- Main Author Navigation Tabs --}}
    @php
        $activeTab = request('tab', $editPost ? 'write' : 'articles');
    @endphp

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-2 border-bottom">
        <ul class="nav nav-pills gap-2" id="authorTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4 py-2 fw-bold {{ $activeTab === 'articles' ? 'active' : '' }}" 
                        id="articles-tab" data-bs-toggle="pill" data-bs-target="#articles-pane" type="button" role="tab">
                    <i class="fas fa-list-check me-1.5"></i> আমার সকল লেখা (@bn($stats['total']))
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4 py-2 fw-bold {{ $activeTab === 'write' ? 'active' : '' }}" 
                        id="write-tab" data-bs-toggle="pill" data-bs-target="#write-pane" type="button" role="tab">
                    <i class="fas {{ $editPost ? 'fa-pen-to-square' : 'fa-feather' }} me-1.5"></i> 
                    {{ $editPost ? 'খসড়া সম্পাদনা' : 'নতুন লেখা পোস্ট করুন' }}
                </button>
            </li>
        </ul>

        @if($editPost)
            <a href="{{ route('author.dashboard', ['tab' => 'write']) }}" class="btn btn-sm btn-outline-secondary rounded-pill">
                <i class="fas fa-times me-1"></i> নতুন লেখায় ফিরুন
            </a>
        @endif
    </div>

    <div class="tab-content" id="authorTabContent">
        {{-- TAB 1: My Articles List --}}
        <div class="tab-pane fade {{ $activeTab === 'articles' ? 'show active' : '' }}" id="articles-pane" role="tabpanel">
            
            {{-- Filter Pills --}}
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <span class="text-muted small fw-semibold me-1"><i class="fas fa-filter me-1"></i>ফিল্টার:</span>
                <a href="{{ route('author.dashboard', ['tab' => 'articles', 'status' => 'all']) }}" 
                   class="btn btn-sm rounded-pill px-3 {{ $filterStatus === 'all' ? 'btn-dark' : 'btn-light border' }}">
                    সকল লেখা (@bn($stats['total']))
                </a>
                <a href="{{ route('author.dashboard', ['tab' => 'articles', 'status' => 'published']) }}" 
                   class="btn btn-sm rounded-pill px-3 {{ $filterStatus === 'published' ? 'btn-success' : 'btn-light border text-success' }}">
                    <i class="fas fa-check-circle me-1"></i> প্রকাশিত (@bn($stats['published']))
                </a>
                <a href="{{ route('author.dashboard', ['tab' => 'articles', 'status' => 'pending']) }}" 
                   class="btn btn-sm rounded-pill px-3 {{ $filterStatus === 'pending' ? 'btn-warning text-dark' : 'btn-light border text-warning' }}">
                    <i class="fas fa-clock me-1"></i> পর্যালোচনায় (@bn($stats['pending']))
                </a>
                <a href="{{ route('author.dashboard', ['tab' => 'articles', 'status' => 'draft']) }}" 
                   class="btn btn-sm rounded-pill px-3 {{ $filterStatus === 'draft' ? 'btn-secondary' : 'btn-light border text-secondary' }}">
                    <i class="fas fa-bookmark me-1"></i> খসড়া (@bn($stats['draft']))
                </a>
                <a href="{{ route('author.dashboard', ['tab' => 'articles', 'status' => 'rejected']) }}" 
                   class="btn btn-sm rounded-pill px-3 {{ $filterStatus === 'rejected' ? 'btn-danger' : 'btn-light border text-danger' }}">
                    <i class="fas fa-triangle-exclamation me-1"></i> সংশোধন (@bn($stats['rejected']))
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                <div class="card-body p-0">
                    @if($posts->isEmpty())
                        <div class="text-center py-5">
                            <div class="text-muted opacity-50 mb-3"><i class="fas fa-book-open fs-1"></i></div>
                            <h5 class="fw-bold text-muted">কোনো ব্লগ বা লেখা পাওয়া যায়নি</h5>
                            <p class="text-muted small">আপনি এখনো কোনো লেখা যোগ করেননি বা নির্বাচিত ফিল্টারে কোনো পোস্ট নেই।</p>
                            <button type="button" class="btn btn-success rounded-pill px-4" onclick="switchTab('write')">
                                <i class="fas fa-plus me-1"></i> প্রথম লেখাটি পোস্ট করুন
                            </button>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light small text-muted text-uppercase">
                                    <tr>
                                        <th class="ps-4 py-3">শিরোনাম ও সংক্ষিপ্ত বিবরণ</th>
                                        <th class="py-3">ক্যাটাগরি</th>
                                        <th class="py-3 text-center">অবস্থা (Status)</th>
                                        <th class="py-3 text-center">পঠিত</th>
                                        <th class="py-3">তারিখ</th>
                                        <th class="text-end pe-4 py-3">অ্যাকশন</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($posts as $post)
                                        @php
                                            $isLocked = ($post->status === 'pending' || $post->status === 'published' || $post->mod_status === 'approved');
                                            $isDraft = ($post->status === 'draft');
                                            $isRejected = ($post->status === 'rejected' || $post->mod_status === 'rejected');
                                        @endphp
                                        <tr>
                                            <td class="ps-4" style="max-width: 380px;">
                                                <div class="fw-bold text-dark fs-6 mb-1">
                                                    @if($post->status === 'published')
                                                        <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="text-decoration-none text-dark hover-primary">
                                                            {{ $post->title }} <i class="fas fa-arrow-up-right-from-square small text-primary ms-1"></i>
                                                        </a>
                                                    @else
                                                        {{ $post->title }}
                                                    @endif
                                                </div>
                                                <div class="text-muted small line-clamp-1">{{ $post->excerpt ?: Str::limit(strip_tags($post->content), 80) }}</div>

                                                @if($isRejected && $post->rejection_reason)
                                                    <div class="alert alert-danger p-2 py-1 rounded-3 mt-1 small mb-0">
                                                        <i class="fas fa-circle-exclamation me-1"></i> <strong>অ্যাডমিন ফিডব্যাক:</strong> {{ $post->rejection_reason }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    {{ $post->category->name ?? 'সাধারণ' }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @if($post->status === 'published' || $post->mod_status === 'approved')
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill">
                                                        <i class="fas fa-circle-check me-1"></i> প্রকাশিত
                                                    </span>
                                                @elseif($post->status === 'pending' || $post->mod_status === 'pending')
                                                    <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-3 py-1.5 rounded-pill">
                                                        <i class="fas fa-hourglass-half me-1"></i> অনুমোদনের অপেক্ষায়
                                                    </span>
                                                @elseif($isDraft)
                                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-1.5 rounded-pill">
                                                        <i class="fas fa-file-lines me-1"></i> খসড়া (Draft)
                                                    </span>
                                                @elseif($isRejected)
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1.5 rounded-pill">
                                                        <i class="fas fa-times-circle me-1"></i> সংশোধন প্রয়োজন
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center fw-bold text-muted">
                                                @bn($post->view_count ?? 0)
                                            </td>
                                            <td class="small text-muted">
                                                {{ $post->created_at ? $post->created_at->format('d M, Y') : '—' }}
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="d-inline-flex gap-1 align-items-center">
                                                    @if($post->status === 'published')
                                                        <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="btn btn-sm btn-outline-primary px-2.5 py-1" title="ব্লগে সরাসরি পড়ুন">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    @endif

                                                    @if(!$isLocked)
                                                        <a href="{{ route('author.dashboard', ['tab' => 'write', 'edit_id' => $post->id]) }}" 
                                                           class="btn btn-sm btn-outline-warning text-dark px-2.5 py-1" title="সম্পাদনা করুন">
                                                            <i class="fas fa-edit"></i>
                                                        </a>

                                                        <form method="POST" action="{{ route('author.blog.destroy', $post->id) }}" class="d-inline"
                                                              onsubmit="return confirm('আপনি কি নিশ্চিত যে এই লেখাটি মুছে ফেলতে চান?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger px-2.5 py-1" title="মুছে ফেলুন">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <span class="badge bg-light text-muted border px-2 py-1 small" title="অনুমোদিত বা প্রক্রিয়ায় থাকায় লক করা আছে">
                                                            <i class="fas fa-lock"></i> লক
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($posts->hasPages())
                            <div class="p-3 border-top d-flex justify-content-between align-items-center bg-light">
                                <small class="text-muted">মোট @bn($posts->total()) টির মধ্যে @bn($posts->firstItem())–@bn($posts->lastItem()) দেখানো হচ্ছে</small>
                                {{ $posts->links() }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        {{-- TAB 2: Write / Edit Article Form --}}
        <div class="tab-pane fade {{ $activeTab === 'write' ? 'show active' : '' }}" id="write-pane" role="tabpanel">
            <div id="write-section"></div>
            <div class="row g-4">
                <div class="col-12 col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                            <h5 class="fw-bold mb-0 text-success">
                                <i class="fas {{ $editPost ? 'fa-pen-to-square' : 'fa-feather-pointed' }} me-2"></i>
                                {{ $editPost ? 'খসড়া লেখা সম্পাদনা' : 'নতুন ব্লগ বা সাহিত্যকর্ম রচনা করুন' }}
                            </h5>
                            @if($editPost)
                                <span class="badge bg-warning-subtle text-dark border px-3 py-1 rounded-pill">এডিটিং মোড</span>
                            @endif
                        </div>

                        <div class="card-body p-4">
                            <form method="POST" action="{{ $editPost ? route('author.blog.update', $editPost->id) : route('author.blog.store') }}" enctype="multipart/form-data">
                                @csrf
                                @if($editPost)
                                    @method('PUT')
                                @endif

                                <div class="mb-3">
                                    <label class="form-label fw-bold text-dark">লেখার মূল শিরোনাম (Title) <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control form-control-lg fs-6 rounded-3 @error('title') is-invalid @enderror" 
                                           value="{{ old('title', $editPost->title ?? '') }}" required placeholder="এখানে আকর্ষণীয় ও স্পষ্ট শিরোনাম লিখুন...">
                                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold text-dark">
                                        উপ-শিরোনাম / ট্যাগলাইন (Subtitle / Tagline) <span class="text-muted small">(ঐচ্ছিক)</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fa-solid fa-quote-left text-primary"></i></span>
                                        <input type="text" name="subtitle" class="form-control rounded-3 @error('subtitle') is-invalid @enderror" 
                                               value="{{ old('subtitle', $editPost->subtitle ?? '') }}" placeholder="যেমন: 'একটি ঐতিহাসিক সাহিত্য পর্যালোচনা' বা বিশেষ সার-ট্যাগলাইন...">
                                    </div>
                                    <small class="text-muted" style="font-size: 0.76rem;">শিরোনামের নিচে দৃষ্টিনন্দনভাবে সাব-টাইটেল হিসেবে প্রদর্শিত হবে।</small>
                                    @error('subtitle')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-dark">ক্যাটাগরি বা বিষয় <span class="text-danger">*</span></label>
                                        <select name="category_id" class="form-select rounded-3" required>
                                            <option value="">-- ক্যাটাগরি বেছে নিন --</option>
                                            @foreach($blogCategories as $cat)
                                                <option value="{{ $cat->id }}" @selected(old('category_id', $editPost->category_id ?? '') == $cat->id)>
                                                    {{ $cat->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <label class="form-label fw-bold text-dark mb-0">
                                                ফিচার্ড ফটোকার্ড / কভার ছবি <span class="text-danger">*</span>
                                            </label>
                                            <button type="button" class="btn btn-sm btn-outline-success fw-bold rounded-pill px-2.5 py-0.5" 
                                                    style="font-size: 0.78rem;" onclick="openAiPhotocardGenerator()">
                                                <i class="fa-solid fa-wand-magic-sparkles me-1 text-warning"></i> এআই ফটোকার্ড তৈরি করুন
                                            </button>
                                        </div>

                                        <input type="hidden" name="ai_photocard_data" id="aiPhotocardDataInput" value="">
                                        
                                        <!-- Real File Input -->
                                        <input type="file" name="featured_image" id="featuredImageInput" class="form-control rounded-3 @error('featured_image') is-invalid @enderror" 
                                               accept="image/jpeg,image/png,image/webp" {{ $editPost && $editPost->featured_image ? '' : 'required' }} onchange="handlePhotocardSelection(this)">
                                        @error('featured_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        
                                        <!-- Error Notification Box -->
                                        <div id="photocardErrorAlert" class="alert alert-danger p-2 small mt-2 d-none rounded-3" style="font-size: 0.8rem;">
                                            <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                            <span id="photocardErrorText">ছবি প্রক্রিয়াকরণে সমস্যা হয়েছে। অনুগ্রহ করে পুনরায় সঠিক ফরম্যাটের ছবি আপলোড করুন অথবা এআই দিয়ে ফটোকার্ড তৈরি করুন।</span>
                                        </div>

                                        <!-- Mandatory Photocard Guidelines Badge -->
                                        <div class="mt-2 p-2.5 bg-light border rounded-3 text-muted small" style="font-size: 0.76rem;">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span class="fw-bold text-dark"><i class="fa-solid fa-crop-simple text-primary me-1"></i>অটো-ক্রপ ও ফিক্সড সাইজ:</span>
                                                <span class="badge bg-primary-subtle text-primary border rounded-pill">১৬:৯ ফরম্যাট (১২০০×৬৭৫ px)</span>
                                            </div>
                                            <div class="mt-1">
                                                যেকোনো ছবি দিলে তা স্বয়ংক্রিয়ভাবে ১৬:৯ রেশিওতে ফিক্সড হয়ে যাবে। ছবি না থাকলে উপরের <strong>"এআই ফটোকার্ড তৈরি করুন"</strong> বোতামে ক্লিক করুন।
                                            </div>
                                        </div>

                                        <!-- Selected / Cropped Photocard Preview Widget -->
                                        <div id="photocardPreviewWrapper" class="mt-2 p-2 bg-success bg-opacity-10 border border-success border-opacity-25 rounded-3 d-flex align-items-center gap-2.5" 
                                             style="{{ $editPost && $editPost->featured_image ? '' : 'display:none;' }}">
                                            <img id="photocardPreviewImg" src="{{ $editPost && $editPost->featured_image ? asset('storage/' . $editPost->featured_image) : '' }}" 
                                                 alt="Photocard Preview" class="rounded-2 border shadow-xs bg-white flex-shrink-0" style="height: 52px; width: 92px; object-fit: cover;">
                                            <div class="overflow-hidden me-auto">
                                                <span id="photocardStatusBadge" class="badge bg-success small"><i class="fa-solid fa-check-circle me-1"></i>ফটোকার্ড প্রস্তুত</span>
                                                <div id="photocardDimensionsText" class="small text-muted text-truncate mt-0.5" style="font-size: 11px;">সাইজ: ১৬:৯ ফিক্সড ক্রপ</div>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-danger py-0.5 px-2 rounded-pill fw-semibold" style="font-size: 11px;" onclick="resetPhotocardSelection()">
                                                <i class="fa-solid fa-arrow-rotate-left me-1"></i>পুনরায় আপলোড
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold text-dark">সংক্ষিপ্ত ভূমিকা / সারসংক্ষেপ (Excerpt) <span class="text-muted small">(ঐচ্ছিক)</span></label>
                                    <textarea name="excerpt" rows="2" class="form-control rounded-3" 
                                              placeholder="লেখার মূল ভাব বা সংক্ষেপ ১-২ বাক্যে লিখুন (ব্লগ কার্ডে প্রদর্শিত হবে)...">{{ old('excerpt', $editPost->excerpt ?? '') }}</textarea>
                                </div>

                                <div class="mb-4">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-1.5">
                                        <label class="form-label fw-bold text-dark mb-0">মূল বিষয়বস্তু ও রচনা (Content) <span class="text-danger">*</span></label>
                                        
                                        <!-- Rich Formatting Toolbar -->
                                        <div class="btn-group btn-group-sm bg-light border rounded-pill p-0.5 shadow-xs" role="group" aria-label="Formatting Toolbar">
                                            <button type="button" class="btn btn-light rounded-pill px-2.5 py-0.5 fw-bold text-dark" onclick="formatContent('bold')" title="বোল্ড (Bold)">
                                                <i class="fa-solid fa-bold"></i>
                                            </button>
                                            <button type="button" class="btn btn-light rounded-pill px-2.5 py-0.5 fst-italic text-dark" onclick="formatContent('italic')" title="ইটালিক (Italic)">
                                                <i class="fa-solid fa-italic"></i>
                                            </button>
                                            <button type="button" class="btn btn-light rounded-pill px-2.5 py-0.5 text-decoration-underline text-dark" onclick="formatContent('underline')" title="আন্ডারলাইন (Underline)">
                                                <i class="fa-solid fa-underline"></i>
                                            </button>
                                            <button type="button" class="btn btn-light rounded-pill px-2.5 py-0.5 text-dark fw-bold" onclick="formatContent('h3')" title="উপ-শিরোনাম (Heading 3)">
                                                H3
                                            </button>
                                            <button type="button" class="btn btn-light rounded-pill px-2.5 py-0.5 text-dark" onclick="formatContent('quote')" title="উদ্ধৃতি (Quote)">
                                                <i class="fa-solid fa-quote-left"></i>
                                            </button>
                                            <button type="button" class="btn btn-light rounded-pill px-2.5 py-0.5 text-dark" onclick="formatContent('list')" title="বুলেট তালিকা">
                                                <i class="fa-solid fa-list-ul"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <textarea name="content" id="blogContentTextarea" rows="12" class="form-control rounded-3 @error('content') is-invalid @enderror" 
                                              required placeholder="আপনার প্রবন্ধ, গল্প, কবিতা, বই পর্যালোচনা বা মতামত এখানে বিস্তারিত লিখুন... প্রয়োজনমতো উপরের টুলবার দিয়ে বোল্ড, ইটালিক ও হেডিং ব্যবহার করতে পারেন।">{{ old('content', $editPost->content ?? '') }}</textarea>
                                    @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="p-3 bg-light rounded-3 mb-4 border d-flex align-items-start gap-2 text-muted small">
                                    <i class="fas fa-info-circle text-primary fs-5 mt-0.5"></i>
                                    <div>
                                        <strong>প্রকাশনা নিয়মাবলী:</strong><br>
                                        • <strong>"খসড়া সংরক্ষণ করুন"</strong> চাপলে লেখাটি শুধুমাত্র আপনার কাছে ড্রাফট হিসেবে থাকবে এবং পরবর্তীতে এডিট করতে পারবেন।<br>
                                        • <strong>"অনুমোদনের জন্য জমা দিন"</strong> চাপলে তা প্রকাশকের পর্যালোচনা বোর্ডে চলে যাবে এবং যাচাই সম্পন্ন হলে স্বয়ংক্রিয়ভাবে ব্লগে প্রকাশিত হবে।
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap gap-2 justify-content-end">
                                    <button type="button" class="btn btn-outline-primary px-4 py-2.5 rounded-pill fw-semibold" onclick="openArticleLivePreview()">
                                        <i class="fas fa-eye me-1.5"></i> লাইভ প্রিভিউ (Preview)
                                    </button>
                                    <button type="submit" name="action_type" value="draft" class="btn btn-outline-secondary px-4 py-2.5 rounded-pill fw-semibold">
                                        <i class="fas fa-bookmark me-1.5"></i> খসড়া সংরক্ষণ করুন (Save Draft)
                                    </button>
                                    <button type="submit" name="action_type" value="submit" class="btn btn-success px-4 py-2.5 rounded-pill fw-bold shadow-sm">
                                        <i class="fas fa-paper-plane me-1.5"></i> অনুমোদনের জন্য জমা দিন (Submit)
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Side Info & Author Guidelines --}}
                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                        <h6 class="fw-bold text-dark mb-3"><i class="fas fa-lightbulb text-warning me-2"></i>লেখক নির্দেশিকা ও টিপস</h6>
                        <ul class="list-unstyled small text-secondary mb-0 d-flex flex-column gap-2.5">
                            <li class="d-flex align-items-start gap-2">
                                <i class="fas fa-check text-success mt-1"></i>
                                <span><strong>মৌলিক লেখা:</strong> আপনার লেখাটি স্বরচিত ও মৌলিক হওয়া বাঞ্ছনীয়।</span>
                            </li>
                            <li class="d-flex align-items-start gap-2">
                                <i class="fas fa-check text-success mt-1"></i>
                                <span><strong>ক্যাটাগরি নির্ধারণ:</strong> বিষয়ভিত্তিক সঠিক ক্যাটাগরি বেছে নিলে পাঠকদের কাছে দ্রুত পৌঁছে যাবে।</span>
                            </li>
                            <li class="d-flex align-items-start gap-2">
                                <i class="fas fa-check text-success mt-1"></i>
                                <span><strong>কভার ছবি / ফটোকার্ড:</strong> আকর্ষণীয় ফটোকার্ড পাঠকদের দৃষ্টি আকর্ষণ সহজ করে। ফটো না থাকলে <strong>এআই ফটোকার্ড</strong> দিয়ে তৈরি করে নিন।</span>
                            </li>
                            <li class="d-flex align-items-start gap-2">
                                <i class="fas fa-check text-success mt-1"></i>
                                <span><strong>স্বত্বাধিকার:</strong> প্রকাশিত লেখার পূর্ণ স্বত্ব লেখকের নিজের থাকবে।</span>
                            </li>
                        </ul>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-primary text-white">
                        <h6 class="fw-bold mb-2"><i class="fas fa-headset me-2"></i>লেখক সহায়তায় আইডিয়া প্রকাশন</h6>
                        <p class="small opacity-90 mb-3">
                            লেখা জমাদানে কোনো কারিগরি অসুবিধা হলে অথবা রিভিউ সংক্রান্ত তথ্যের জন্য সরাসরি আমাদের সম্পাদকীয় দলের সাথে যোগাযোগ করুন।
                        </p>
                        <a href="tel:01700000000" class="btn btn-warning text-dark btn-sm rounded-pill fw-bold px-3">
                            <i class="fas fa-phone me-1"></i> সম্পাদকীয় হেল্পলাইন
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Interactive Article Live Preview Modal --}}
<div class="modal fade" id="articlePreviewModal" tabindex="-1" aria-labelledby="articlePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-dark text-white py-3 px-4 border-0">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-newspaper text-warning fs-4"></i>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="articlePreviewModalLabel">ব্লগে কেমন দেখাবে (লাইভ প্রিভিউ)</h5>
                        <small class="text-white-50">প্রকাশিত হলে পাঠকরা যেভাবে দেখতে পাবেন</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 p-md-5 bg-white">
                <article>
                    <div id="prevCategoryBadge" class="badge bg-primary bg-gradient px-3 py-1.5 rounded-pill mb-3 d-inline-block">সাধারণ সাহিত্য</div>
                    <h1 id="prevTitle" class="fw-bold text-dark display-6 mb-1" style="line-height: 1.35;">শিরোনামের নমুনা</h1>
                    <div id="prevSubtitleBox" class="fs-6 text-muted mb-3 fst-italic" style="display: none;"></div>
                    
                    <div class="d-flex align-items-center gap-3 py-3 border-top border-bottom mb-4 text-muted small">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-sm fw-bold" style="width: 36px; height: 36px;">
                                {{ mb_substr($user->name, 0, 1) }}
                            </div>
                            <div>
                                <span class="fw-bold text-dark d-block">{{ $user->name }}</span>
                                <span class="text-muted" style="font-size: 0.72rem;">আইডিয়া সাহিত্যপত্র লেখক</span>
                            </div>
                        </div>
                        <span class="ms-auto"><i class="fa-regular fa-calendar text-primary me-1"></i>আজ</span>
                    </div>

                    <div id="prevCoverBox" class="rounded-4 overflow-hidden mb-4 shadow-sm" style="max-height: 380px; display: none;">
                        <img id="prevCoverImg" src="" alt="Cover" class="w-100 h-100 object-fit-cover">
                    </div>

                    <div id="prevExcerptBox" class="p-3 bg-light rounded-3 mb-4 border-start border-4 border-primary fs-6 fst-italic text-secondary" style="display: none;"></div>

                    <div id="prevContent" class="fs-6 text-dark leading-relaxed" style="line-height: 1.8; white-space: pre-line;"></div>
                </article>
            </div>
            <div class="modal-footer bg-light border-0 py-3 px-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">বন্ধ করুন</button>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL: AI / TITLE PHOTOCARD GENERATOR (এআই ফটোকার্ড জেনারেটর)            --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="aiPhotocardModal" tabindex="-1" aria-labelledby="aiPhotocardModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-gradient text-white py-3 px-4" style="background: linear-gradient(135deg, #064e3b 0%, #047857 100%);">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-wand-magic-sparkles text-warning fs-4"></i>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" id="aiPhotocardModalLabel">এআই ফটোকার্ড কভার জেনারেটর</h5>
                        <small class="text-white-50">লেখার শিরোনাম ও বিষয়ের ওপর ভিত্তি করে সরাসরি ফটোকার্ড তৈরি করুন</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4 bg-light">
                <div class="row g-3">
                    <!-- Live Canvas Preview Box -->
                    <div class="col-12 text-center">
                        <div class="p-2 bg-white rounded-4 shadow-sm border mx-auto" style="max-width: 650px;">
                            <div class="position-relative rounded-3 overflow-hidden" style="aspect-ratio: 16/9; background: #0f172a;">
                                <canvas id="aiPhotocardCanvas" width="1200" height="675" style="width: 100%; height: 100%; object-fit: contain;"></canvas>
                            </div>
                            <div class="d-flex align-items-center justify-content-between px-2 pt-2 text-muted" style="font-size: 0.75rem;">
                                <span><i class="fa-solid fa-expand me-1"></i> রেজোলিউশন: ১২০০ × ৬৭৫ px (আল্ট্রা এইচডি)</span>
                                <span><i class="fa-solid fa-shield-halved text-success me-1"></i> আইডিয়া সাহিত্যপত্র অফিশিয়াল ফরম্যাট</span>
                            </div>
                        </div>
                    </div>

                    <!-- Customization Controls -->
                    <div class="col-12">
                        <div class="card border-0 rounded-3 p-3 bg-white shadow-xs">
                            <label class="form-label small fw-bold text-dark mb-2">
                                <i class="fa-solid fa-palette text-primary me-1"></i> নান্দনিক কালার থিম নির্বাচন করুন:
                            </label>
                            <div class="d-flex flex-wrap gap-2 mb-3" id="aiThemeButtons">
                                <button type="button" class="btn btn-sm btn-outline-success active rounded-pill px-3" onclick="selectAiTheme('emerald')">
                                    🟢 রাজকীয় সবুজ (Emerald)
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="selectAiTheme('midnight')">
                                    🔵 মধ্যরাতের নীল (Midnight)
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="selectAiTheme('crimson')">
                                    🔴 সানসেট ক্রিমসন (Crimson)
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="selectAiTheme('purple')">
                                    🟣 রয়্যাল পার্পল (Violet)
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-3" onclick="selectAiTheme('vintage')">
                                    📜 ক্লাসিক ভিন্টেজ (Parchment)
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3" onclick="selectAiTheme('slate')">
                                    ⚫ স্লিক গ্রাফাইট (Onyx)
                                </button>
                            </div>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold mb-1">কার্ডে প্রদর্শিত শিরোনাম:</label>
                                    <input type="text" id="aiCardCustomTitle" class="form-control form-control-sm" oninput="renderAiPhotocard()">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold mb-1">উপ-শিরোনাম / সাব-ট্যাগলাইন:</label>
                                    <input type="text" id="aiCardCustomSubtitle" class="form-control form-control-sm" oninput="renderAiPhotocard()">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-white border-top py-3 px-4 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3" onclick="downloadGeneratedPhotocard()">
                    <i class="fa-solid fa-download me-1"></i> ছবি ডাউনলোড করুন
                </button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">বাতিল</button>
                    <button type="button" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm" onclick="applyGeneratedPhotocardToForm()">
                        <i class="fa-solid fa-check-circle me-1.5"></i> এই ফটোকার্ডটি ব্লগে ব্যবহার করুন
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentAiTheme = 'emerald';
    const authorNameGlobal = "{{ $user->name }}";

    function switchTab(tabName) {
        if (tabName === 'write') {
            const writeBtn = document.getElementById('write-tab');
            if (writeBtn) {
                const tab = new bootstrap.Tab(writeBtn);
                tab.show();
            }
        } else {
            const artBtn = document.getElementById('articles-tab');
            if (artBtn) {
                const tab = new bootstrap.Tab(artBtn);
                tab.show();
            }
        }
    }

    function openArticleLivePreview() {
        const title = document.querySelector('input[name="title"]')?.value.trim() || 'শিরোনাম ছাড়া খসড়া';
        const subtitle = document.querySelector('input[name="subtitle"]')?.value.trim() || '';
        const catSelect = document.querySelector('select[name="category_id"]');
        const catName = catSelect && catSelect.selectedIndex > 0 ? catSelect.options[catSelect.selectedIndex].text : 'সাহিত্যপত্র';
        const excerpt = document.querySelector('textarea[name="excerpt"]')?.value.trim() || '';
        const content = document.querySelector('textarea[name="content"]')?.value.trim() || 'এখানে কোনো বিষয়বস্তু লেখা হয়নি...';
        const fileInput = document.querySelector('input[name="featured_image"]');
        const aiInput = document.getElementById('aiPhotocardDataInput');

        document.getElementById('prevTitle').textContent = title;
        document.getElementById('prevCategoryBadge').textContent = catName;
        document.getElementById('prevContent').textContent = content;

        const subBox = document.getElementById('prevSubtitleBox');
        if (subtitle) {
            subBox.textContent = subtitle;
            subBox.style.display = 'block';
        } else {
            subBox.style.display = 'none';
        }

        const excerptBox = document.getElementById('prevExcerptBox');
        if (excerpt) {
            excerptBox.textContent = excerpt;
            excerptBox.style.display = 'block';
        } else {
            excerptBox.style.display = 'none';
        }

        const coverBox = document.getElementById('prevCoverBox');
        const coverImg = document.getElementById('prevCoverImg');

        if (aiInput && aiInput.value) {
            coverImg.src = aiInput.value;
            coverBox.style.display = 'block';
        } else if (fileInput && fileInput.files && fileInput.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                coverImg.src = e.target.result;
                coverBox.style.display = 'block';
            };
            reader.readAsDataURL(fileInput.files[0]);
        } else {
            @if($editPost && $editPost->featured_image)
                coverImg.src = "{{ asset('storage/' . $editPost->featured_image) }}";
                coverBox.style.display = 'block';
            @else
                coverBox.style.display = 'none';
            @endif
        }

        const modalEl = document.getElementById('articlePreviewModal');
        if (modalEl) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    // Auto-crop & 16:9 fixer for uploaded images
    function handlePhotocardSelection(input) {
        const errorAlert = document.getElementById('photocardErrorAlert');
        const errorText = document.getElementById('photocardErrorText');
        const previewWrapper = document.getElementById('photocardPreviewWrapper');
        const previewImg = document.getElementById('photocardPreviewImg');
        const statusBadge = document.getElementById('photocardStatusBadge');
        const dimText = document.getElementById('photocardDimensionsText');
        const aiInput = document.getElementById('aiPhotocardDataInput');

        if (errorAlert) errorAlert.classList.add('d-none');

        if (!input.files || !input.files[0]) {
            return;
        }

        const file = input.files[0];

        // Validate MIME type
        if (!file.type.startsWith('image/')) {
            if (errorAlert) {
                errorText.textContent = "নির্বাচিত ফাইলটি কোনো বৈধ ছবি নয়। অনুগ্রহ করে JPG, PNG অথবা WebP ফরম্যাটের ছবি নির্বাচন করুন।";
                errorAlert.classList.remove('d-none');
            }
            input.value = '';
            return;
        }

        // Validate max size 8MB
        if (file.size > 8 * 1024 * 1024) {
            if (errorAlert) {
                errorText.textContent = "ছবির সাইজ ৮ মেগাবাইটের বেশি। অনুগ্রহ করে ছোট সাইজের ছবি নির্বাচন করুন।";
                errorAlert.classList.remove('d-none');
            }
            input.value = '';
            return;
        }

        // Client-side 16:9 Canvas auto-crop & compression
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                try {
                    const canvas = document.createElement('canvas');
                    const targetWidth = 1200;
                    const targetHeight = 675; // exact 16:9
                    canvas.width = targetWidth;
                    canvas.height = targetHeight;
                    const ctx = canvas.getContext('2d');

                    // Calculate center crop
                    const srcAspect = img.width / img.height;
                    const targetAspect = targetWidth / targetHeight;
                    let renderWidth, renderHeight, offsetX, offsetY;

                    if (srcAspect > targetAspect) {
                        renderHeight = img.height;
                        renderWidth = img.height * targetAspect;
                        offsetX = (img.width - renderWidth) / 2;
                        offsetY = 0;
                    } else {
                        renderWidth = img.width;
                        renderHeight = img.width / targetAspect;
                        offsetX = 0;
                        offsetY = (img.height - renderHeight) / 2;
                    }

                    // Draw image centered and cropped
                    ctx.drawImage(img, offsetX, offsetY, renderWidth, renderHeight, 0, 0, targetWidth, targetHeight);

                    // High-quality JPEG
                    const croppedDataUrl = canvas.toDataURL('image/jpeg', 0.90);

                    // Clear any previously generated AI photocard
                    if (aiInput) aiInput.value = '';

                    if (previewImg) previewImg.src = croppedDataUrl;
                    if (statusBadge) statusBadge.innerHTML = '<i class="fa-solid fa-check-circle me-1"></i>অটো-ক্রপ সম্পন্ন (১৬:৯)';
                    if (dimText) dimText.textContent = `রেজোলিউশন: ১২০০×৬৭৫ px | সাইজ: ${(file.size / 1024).toFixed(0)} KB`;
                    if (previewWrapper) previewWrapper.style.display = 'flex';
                } catch (err) {
                    console.error("Auto crop error:", err);
                    if (previewImg) previewImg.src = e.target.result;
                    if (previewWrapper) previewWrapper.style.display = 'flex';
                }
            };
            img.onerror = function() {
                if (errorAlert) {
                    errorText.textContent = "ছবিটি লোড করা সম্ভব হয়নি বা ফাইলটি ত্রুটিপূর্ণ। অনুগ্রহ করে অন্য ছবি নির্বাচন করুন।";
                    errorAlert.classList.remove('d-none');
                }
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }

    function resetPhotocardSelection() {
        const input = document.getElementById('featuredImageInput');
        const aiInput = document.getElementById('aiPhotocardDataInput');
        const previewWrapper = document.getElementById('photocardPreviewWrapper');
        const previewImg = document.getElementById('photocardPreviewImg');
        const errorAlert = document.getElementById('photocardErrorAlert');

        if (input) {
            input.value = '';
            input.required = true;
        }
        if (aiInput) aiInput.value = '';
        if (previewImg) previewImg.src = '';
        if (previewWrapper) previewWrapper.style.display = 'none';
        if (errorAlert) errorAlert.classList.add('d-none');
    }

    // AI Photocard Generator
    function openAiPhotocardGenerator() {
        const titleInput = document.querySelector('input[name="title"]');
        const subtitleInput = document.querySelector('input[name="subtitle"]');
        const title = titleInput ? titleInput.value.trim() : '';
        const subtitle = subtitleInput ? subtitleInput.value.trim() : '';

        const customTitleInput = document.getElementById('aiCardCustomTitle');
        const customSubtitleInput = document.getElementById('aiCardCustomSubtitle');

        if (customTitleInput) {
            customTitleInput.value = title || 'আইডিয়া সাহিত্যপত্র প্রবন্ধ';
        }
        if (customSubtitleInput) {
            customSubtitleInput.value = subtitle || '';
        }

        renderAiPhotocard();

        const modalEl = document.getElementById('aiPhotocardModal');
        if (modalEl) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function selectAiTheme(themeName) {
        currentAiTheme = themeName;
        const container = document.getElementById('aiThemeButtons');
        if (container) {
            container.querySelectorAll('button').forEach(btn => btn.classList.remove('active'));
        }
        if (event && event.target) {
            event.target.classList.add('active');
        }
        renderAiPhotocard();
    }

    function renderAiPhotocard() {
        const canvas = document.getElementById('aiPhotocardCanvas');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        const W = 1200;
        const H = 675;
        canvas.width = W;
        canvas.height = H;

        const title = document.getElementById('aiCardCustomTitle')?.value.trim() || 'সাহিত্য ও মনন';
        const subtitle = document.getElementById('aiCardCustomSubtitle')?.value.trim() || '';
        const catSelect = document.querySelector('select[name="category_id"]');
        const category = catSelect && catSelect.selectedIndex > 0 ? catSelect.options[catSelect.selectedIndex].text : 'সাহিত্যপত্র ও ব্লগ';

        // Background Theme Gradients
        let bgGradient = ctx.createLinearGradient(0, 0, W, H);
        let accentColor = '#fbbf24';
        let badgeBg = 'rgba(255, 255, 255, 0.15)';

        switch (currentAiTheme) {
            case 'midnight':
                bgGradient.addColorStop(0, '#0f172a');
                bgGradient.addColorStop(0.5, '#1e3a8a');
                bgGradient.addColorStop(1, '#1e1b4b');
                accentColor = '#38bdf8';
                break;
            case 'crimson':
                bgGradient.addColorStop(0, '#450a0a');
                bgGradient.addColorStop(0.5, '#991b1b');
                bgGradient.addColorStop(1, '#7f1d1d');
                accentColor = '#fde047';
                break;
            case 'purple':
                bgGradient.addColorStop(0, '#2e1065');
                bgGradient.addColorStop(0.5, '#581c87');
                bgGradient.addColorStop(1, '#3b0764');
                accentColor = '#e879f9';
                break;
            case 'vintage':
                bgGradient.addColorStop(0, '#451a03');
                bgGradient.addColorStop(0.5, '#78350f');
                bgGradient.addColorStop(1, '#291104');
                accentColor = '#fef08a';
                break;
            case 'slate':
                bgGradient.addColorStop(0, '#090d16');
                bgGradient.addColorStop(0.5, '#1e293b');
                bgGradient.addColorStop(1, '#0f172a');
                accentColor = '#34d399';
                break;
            case 'emerald':
            default:
                bgGradient.addColorStop(0, '#022c22');
                bgGradient.addColorStop(0.5, '#065f46');
                bgGradient.addColorStop(1, '#064e3b');
                accentColor = '#fbbf24';
                break;
        }

        // 1. Draw Background
        ctx.fillStyle = bgGradient;
        ctx.fillRect(0, 0, W, H);

        // 2. Artistic Vignette & Decorative Lighting Circles
        const radGrad = ctx.createRadialGradient(W * 0.75, H * 0.3, 50, W * 0.75, H * 0.3, 450);
        radGrad.addColorStop(0, 'rgba(255, 255, 255, 0.12)');
        radGrad.addColorStop(1, 'rgba(0, 0, 0, 0)');
        ctx.fillStyle = radGrad;
        ctx.fillRect(0, 0, W, H);

        // 3. Elegant Decorative Corner Borders
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.18)';
        ctx.lineWidth = 3;
        ctx.strokeRect(35, 35, W - 70, H - 70);

        ctx.strokeStyle = accentColor;
        ctx.lineWidth = 4;
        // Top-left corner
        ctx.beginPath();
        ctx.moveTo(35, 85); ctx.lineTo(35, 35); ctx.lineTo(85, 35);
        ctx.stroke();
        // Bottom-right corner
        ctx.beginPath();
        ctx.moveTo(W - 35, H - 85); ctx.lineTo(W - 35, H - 35); ctx.lineTo(W - 85, H - 35);
        ctx.stroke();

        // 4. Header Badge: Category & Branding
        ctx.fillStyle = badgeBg;
        roundRect(ctx, 70, 65, 260, 48, 24, true, false);

        ctx.fillStyle = accentColor;
        ctx.font = 'bold 22px "Hind Siliguri", "Segoe UI", sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText("✦ " + category, 200, 97);

        // Right Branding Badge
        ctx.fillStyle = 'rgba(255, 255, 255, 0.75)';
        ctx.font = '600 20px "Hind Siliguri", sans-serif';
        ctx.textAlign = 'right';
        ctx.fillText("আইডিয়া সাহিত্যপত্র ও ব্লগ", W - 70, 97);

        // 5. Main Title (Auto Wrap Multi-Line)
        ctx.fillStyle = '#ffffff';
        ctx.textAlign = 'left';
        ctx.shadowColor = 'rgba(0, 0, 0, 0.6)';
        ctx.shadowBlur = 15;
        ctx.shadowOffsetX = 2;
        ctx.shadowOffsetY = 4;

        let fontSize = 54;
        if (title.length > 50) fontSize = 42;
        if (title.length > 80) fontSize = 36;
        ctx.font = `bold ${fontSize}px "Hind Siliguri", "Segoe UI", sans-serif`;

        const maxWidth = W - 140;
        const lineHeight = fontSize * 1.35;
        const startY = subtitle ? 230 : 270;
        wrapText(ctx, title, 70, startY, maxWidth, lineHeight);

        // Reset Shadow
        ctx.shadowColor = 'transparent';

        // 6. Subtitle / Tagline (If provided)
        if (subtitle) {
            ctx.fillStyle = accentColor;
            ctx.font = 'italic 28px "Hind Siliguri", sans-serif';
            ctx.fillText(subtitle, 70, startY + (lineHeight * 2.2));
        }

        // 7. Divider Line
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.25)';
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.moveTo(70, H - 140);
        ctx.lineTo(W - 70, H - 140);
        ctx.stroke();

        // 8. Footer Credit: Author Name & Idea Logo Mark
        ctx.fillStyle = '#ffffff';
        ctx.font = 'bold 26px "Hind Siliguri", sans-serif';
        ctx.textAlign = 'left';
        ctx.fillText("✍️ রচনা: " + authorNameGlobal, 70, H - 85);

        ctx.fillStyle = accentColor;
        ctx.font = 'bold 22px "Hind Siliguri", sans-serif';
        ctx.textAlign = 'right';
        ctx.fillText("www.ideaabd.com", W - 70, H - 85);
    }

    function applyGeneratedPhotocardToForm() {
        const canvas = document.getElementById('aiPhotocardCanvas');
        if (!canvas) return;

        const dataUrl = canvas.toDataURL('image/jpeg', 0.92);
        const aiInput = document.getElementById('aiPhotocardDataInput');
        const fileInput = document.getElementById('featuredImageInput');
        const previewImg = document.getElementById('photocardPreviewImg');
        const previewWrapper = document.getElementById('photocardPreviewWrapper');
        const statusBadge = document.getElementById('photocardStatusBadge');
        const dimText = document.getElementById('photocardDimensionsText');
        const errorAlert = document.getElementById('photocardErrorAlert');

        if (aiInput) aiInput.value = dataUrl;
        if (fileInput) {
            fileInput.value = '';
            fileInput.required = false; // File upload is optional since AI card is attached
        }
        if (previewImg) previewImg.src = dataUrl;
        if (statusBadge) statusBadge.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles me-1"></i>এআই ফটোকার্ড প্রস্তুত';
        if (dimText) dimText.textContent = 'রেজোলিউশন: ১২০০×৬৭৫ px (অটো-জেনারেটেড এইচডি)';
        if (previewWrapper) previewWrapper.style.display = 'flex';
        if (errorAlert) errorAlert.classList.add('d-none');

        const modalEl = document.getElementById('aiPhotocardModal');
        if (modalEl) {
            bootstrap.Modal.getInstance(modalEl)?.hide();
        }

        alert("✨ এআই ফটোকার্ডটি সফলভাবে আপনার ব্লগ পোস্টের জন্য যুক্ত হয়েছে!");
    }

    function downloadGeneratedPhotocard() {
        const canvas = document.getElementById('aiPhotocardCanvas');
        if (!canvas) return;
        const link = document.createElement('a');
        link.download = 'idea_photocard_' + Date.now() + '.jpg';
        link.href = canvas.toDataURL('image/jpeg', 0.95);
        link.click();
    }

    // Canvas Helpers
    function wrapText(ctx, text, x, y, maxWidth, lineHeight) {
        const words = text.split(' ');
        let line = '';
        let currentY = y;

        for (let n = 0; n < words.length; n++) {
            const testLine = line + words[n] + ' ';
            const metrics = ctx.measureText(testLine);
            const testWidth = metrics.width;
            if (testWidth > maxWidth && n > 0) {
                ctx.fillText(line, x, currentY);
                line = words[n] + ' ';
                currentY += lineHeight;
            } else {
                line = testLine;
            }
        }
        ctx.fillText(line, x, currentY);
    }

    function roundRect(ctx, x, y, width, height, radius, fill, stroke) {
        ctx.beginPath();
        ctx.moveTo(x + radius, y);
        ctx.lineTo(x + width - radius, y);
        ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
        ctx.lineTo(x + width, y + height - radius);
        ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
        ctx.lineTo(x + radius, y + height);
        ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
        ctx.lineTo(x, y + radius);
        ctx.quadraticCurveTo(x, y, x + radius, y);
        ctx.closePath();
        if (fill) ctx.fill();
        if (stroke) ctx.stroke();
    }

    function formatContent(type) {
        const textarea = document.getElementById('blogContentTextarea');
        if (!textarea) return;

        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const selectedText = textarea.value.substring(start, end);
        let replacement = '';

        switch(type) {
            case 'bold':
                replacement = selectedText ? `<b>${selectedText}</b>` : '<b>বোল্ড টেক্সট</b>';
                break;
            case 'italic':
                replacement = selectedText ? `<i>${selectedText}</i>` : '<i>ইটালিক টেক্সট</i>';
                break;
            case 'underline':
                replacement = selectedText ? `<u>${selectedText}</u>` : '<u>আন্ডারলাইন টেক্সট</u>';
                break;
            case 'h3':
                replacement = selectedText ? `\n<h3>${selectedText}</h3>\n` : '\n<h3>উপ-শিরোনাম</h3>\n';
                break;
            case 'quote':
                replacement = selectedText ? `\n<blockquote>${selectedText}</blockquote>\n` : '\n<blockquote>এখানে উদ্ধৃতি লিখুন...</blockquote>\n';
                break;
            case 'list':
                replacement = selectedText ? `\n<ul>\n  <li>${selectedText}</li>\n</ul>\n` : '\n<ul>\n  <li>প্রথম পয়েন্ট</li>\n  <li>দ্বিতীয় পয়েন্ট</li>\n</ul>\n';
                break;
            default:
                replacement = selectedText;
        }

        textarea.setRangeText(replacement, start, end, 'select');
        textarea.focus();
    }
</script>
@endsection
