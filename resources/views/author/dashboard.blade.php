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
                                    <label class="form-label fw-bold text-dark">লেখার শিরোনাম (Title) <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control form-control-lg fs-6 rounded-3 @error('title') is-invalid @enderror" 
                                           value="{{ old('title', $editPost->title ?? '') }}" required placeholder="এখানে আকর্ষণীয় ও স্পষ্ট শিরোনাম লিখুন...">
                                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-dark">ক্যাটাগরি বা বিষয়</label>
                                        <select name="category_id" class="form-select rounded-3">
                                            <option value="">-- ক্যাটাগরি বেছে নিন (ঐচ্ছিক) --</option>
                                            @foreach($blogCategories as $cat)
                                                <option value="{{ $cat->id }}" @selected(old('category_id', $editPost->category_id ?? '') == $cat->id)>
                                                    {{ $cat->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-dark">ফিচার্ড কভার ছবি <span class="text-muted small">(ঐচ্ছিক)</span></label>
                                        <input type="file" name="featured_image" class="form-control rounded-3" accept="image/*">
                                        @if($editPost && $editPost->featured_image)
                                            <div class="mt-2 d-flex align-items-center gap-2">
                                                <img src="{{ asset('storage/' . $editPost->featured_image) }}" alt="Preview" class="rounded-3 border" style="height: 45px; width: 60px; object-fit: cover;">
                                                <small class="text-muted">বর্তমান কভার ছবি সংযুক্ত আছে</small>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold text-dark">সংক্ষিপ্ত ভূমিকা / সারসংক্ষেপ (Excerpt) <span class="text-muted small">(ঐচ্ছিক)</span></label>
                                    <textarea name="excerpt" rows="2" class="form-control rounded-3" 
                                              placeholder="লেখার মূল ভাব বা সংক্ষেপ ১-২ বাক্যে লিখুন (ব্লগ কার্ডে প্রদর্শিত হবে)...">{{ old('excerpt', $editPost->excerpt ?? '') }}</textarea>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold text-dark">মূল বিষয়বস্তু ও রচনা (Content) <span class="text-danger">*</span></label>
                                    <textarea name="content" rows="12" class="form-control rounded-3 @error('content') is-invalid @enderror" 
                                              required placeholder="আপনার প্রবন্ধ, গল্প, কবিতা, বই পর্যালোচনা বা মতামত এখানে বিস্তারিত লিখুন...">{{ old('content', $editPost->content ?? '') }}</textarea>
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
                                <span><strong>কভার ছবি:</strong> আকর্ষণীয় কভার ছবি যুক্ত করলে পাঠকদের দৃষ্টি আকর্ষণ সহজ হয়।</span>
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
                            কোনো বিষয়ে টেকনিক্যাল সমস্যা বা লেখা সম্পাদনায় সহযোগিতার প্রয়োজন হলে আমাদের সম্পাদকীয় টিমের সাথে যোগাযোগ করুন।
                        </p>
                        <div class="small fw-semibold"><i class="fas fa-phone me-1.5"></i> +৮৮০ ১৩১৮ ৬৯২ ৬৯২</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
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
</script>
@endsection
