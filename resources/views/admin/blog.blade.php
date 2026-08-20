@extends('layouts.admin')

@section('title', 'ব্লগ পোস্ট পরিচালনা')
@section('heading', 'ব্লগ পোস্ট ও কনটেন্ট মডারেশন')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">ব্লগ পোস্ট</li>
@endsection

@section('actions')
    <button type="button" class="btn btn-primary rounded-pill px-3 shadow-xs fw-semibold" data-bs-toggle="modal" data-bs-target="#blogCustomizerModal">
        <i class="fas fa-palette me-1.5"></i> ব্লগের ডিজাইন ও কাস্টমাইজেশন
    </button>
    <button type="button" class="btn btn-outline-success rounded-pill px-3 shadow-xs fw-semibold" data-bs-toggle="modal" data-bs-target="#bulkTypographyModal">
        <i class="fas fa-wand-magic-sparkles me-1.5"></i> সকল লেখার স্পেস ঠিক করুন
    </button>
    <a href="{{ route('admin.blog-categories') }}" class="btn btn-outline-secondary rounded-pill px-3 shadow-xs">
        <i class="fas fa-shapes me-1"></i> ক্যাটাগরি
    </a>
    <a href="{{ route('admin.content.create', 'blog') }}" class="btn btn-dark rounded-pill px-3 shadow-xs fw-semibold">
        <i class="fas fa-plus me-1"></i> নতুন পোস্ট
    </a>
    <a href="{{ route('blog.index') }}" target="_blank" rel="noopener" class="btn btn-outline-dark rounded-pill px-3 shadow-xs">
        <i class="fas fa-arrow-up-right-from-square me-1"></i> ব্লগে দেখুন
    </a>
@endsection

@section('content')

{{-- Flash Alert Messages --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-3 rounded-3 shadow-xs" role="alert">
        <i class="fas fa-circle-check fs-5 me-2 text-success"></i>
        <div>{{ session('success') }}</div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- Quick Customizer Summary Banner --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden border-start border-4 border-primary">
    <div class="card-body p-3.5 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 50px; height: 50px;">
                <i class="fas fa-sliders fs-4"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-1 text-dark">ব্লগ ডিজাইন ও রিডার কাস্টমাইজার কনসোল</h6>
                <div class="small text-muted">
                    বর্তমান ফন্ট: <span class="badge bg-light text-dark border">{{ explode(',', $blogSettings['font_family'] ?? '')[0] ?? 'Default' }}</span> | 
                    লাইভ লাইন স্পেস: <span class="badge bg-light text-primary border">{{ $blogSettings['line_height'] ?? '1.6' }}</span> | 
                    কবিতা লাইন স্পেস: <span class="badge bg-light text-success border">{{ $blogSettings['poetry_line_height'] ?? '1.45' }}</span>
                </div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold shadow-xs" data-bs-toggle="modal" data-bs-target="#blogCustomizerModal">
                <i class="fas fa-pen-nib me-1"></i> ডিজাইন পরিবর্তন করুন
            </button>
            <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold shadow-xs" data-bs-toggle="modal" data-bs-target="#bulkTypographyModal">
                <i class="fas fa-compress-alt me-1"></i> লেখার স্পেস মেরামত
            </button>
        </div>
    </div>
</div>

{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">মোট ব্লগ পোস্ট</span>
                    <h3 class="fw-bold mb-0 text-primary">@bn($stats['total'] ?? 0)</h3>
                </div>
                <div class="rounded-circle bg-primary-subtle text-primary p-3"><i class="fas fa-blog fs-4"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">প্রকাশিত (অনুমোদিত)</span>
                    <h3 class="fw-bold mb-0 text-success">@bn($stats['published'] ?? 0)</h3>
                </div>
                <div class="rounded-circle bg-success-subtle text-success p-3"><i class="fas fa-check-double fs-4"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-warning">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">অপেক্ষমাণ (Pending)</span>
                    <h3 class="fw-bold mb-0 text-warning">@bn($stats['pending'] ?? 0)</h3>
                </div>
                <div class="rounded-circle bg-warning-subtle text-warning p-3"><i class="fas fa-clock fs-4"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-danger">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">বাতিল (প্রত্যাখ্যাত)</span>
                    <h3 class="fw-bold mb-0 text-danger">@bn($stats['rejected'] ?? 0)</h3>
                </div>
                <div class="rounded-circle bg-danger-subtle text-danger p-3"><i class="fas fa-circle-xmark fs-4"></i></div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('admin.blog') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="search" name="search" class="form-control border-start-0" 
                           placeholder="পোস্টের শিরোনাম, বিষয় বা slug দিয়ে খুঁজুন..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-4">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="all" @selected(request('status') === 'all' || !request('status'))>সকল পোস্ট</option>
                    <option value="published" @selected(request('status') === 'published')>প্রকাশিত (Published)</option>
                    <option value="pending" @selected(request('status') === 'pending')>অপেক্ষমাণ (Pending Review)</option>
                    <option value="draft" @selected(request('status') === 'draft')>খসড়া (Draft)</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>বাতিল (Rejected)</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> ফিল্টার</button>
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.blog') }}" class="btn btn-light border" title="রিসেট"><i class="fas fa-rotate-left"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Data Table --}}
<div class="adm-card shadow-sm rounded-4 overflow-hidden">
    @if ($posts->isEmpty())
        <div class="empty-state py-5 text-center">
            <i class="fas fa-blog fs-1 text-muted opacity-50 mb-3"></i>
            <h5 class="fw-bold text-muted">কোনো ব্লগ পোস্ট পাওয়া যায়নি</h5>
            <p class="text-muted small">নতুন পোস্ট তৈরি করুন অথবা অন্য ফিল্টার ব্যবহার করুন।</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table adm-table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>পোস্টের শিরোনাম</th>
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
                        @endphp
                        <tr>
                            <td class="ps-3 text-muted small">@bn($posts->firstItem() + $n)</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $post->title }}</div>
                                <div class="text-muted small">{{ $post->slug }}</div>
                                @if($isRejected && $post->rejection_reason)
                                    <div class="small text-danger mt-1">
                                        <i class="fas fa-info-circle me-1"></i>বাতিলের কারণ: {{ $post->rejection_reason }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $post->author?->name ?? $post->submitter?->name ?? '—' }}</div>
                                @if($post->author?->phone)
                                    <div class="text-muted small"><i class="fas fa-phone me-1"></i>{{ $post->author->phone }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $post->category?->name ?? 'সাধারণ' }}</span>
                            </td>
                            <td>
                                @if($isPublished)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">
                                        <i class="fas fa-check-circle me-1"></i>প্রকাশিত
                                    </span>
                                @elseif($isPending)
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1 rounded-pill">
                                        <i class="fas fa-clock me-1"></i>অনুমোদন অপেক্ষমাণ
                                    </span>
                                @elseif($isRejected)
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-pill">
                                        <i class="fas fa-circle-xmark me-1"></i>বাতিল
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary px-2.5 py-1 rounded-pill">খসড়া</span>
                                @endif
                            </td>
                            <td class="text-muted small">
                                <i class="fas fa-eye me-1"></i>@bn($post->views_count ?? 0)
                            </td>
                            <td class="text-muted small">
                                {{ $post->created_at ? $post->created_at->format('d M, Y') : '—' }}
                            </td>
                            <td class="text-end pe-3">
                                <div class="d-flex align-items-center justify-content-end gap-1.5">
                                    {{-- Quick Approve/Reject buttons for pending posts --}}
                                    @if($isPending)
                                        <form action="{{ route('admin.content.approve', ['type' => 'blog', 'id' => $post->id]) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-success px-2 py-1" title="অনুমোদন ও প্রকাশ করুন">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>

                                        <button type="button" class="btn btn-sm btn-outline-danger px-2 py-1" 
                                                data-bs-toggle="modal" data-bs-target="#rejectModal{{ $post->id }}" title="বাতিল করুন">
                                            <i class="fas fa-xmark"></i>
                                        </button>

                                        {{-- Reject Reason Modal --}}
                                        <div class="modal fade text-start" id="rejectModal{{ $post->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content rounded-4 border-0 shadow">
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title fs-6 fw-bold"><i class="fas fa-ban me-2"></i>পোস্ট বাতিল করুন</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('admin.content.reject', ['type' => 'blog', 'id' => $post->id]) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <div class="modal-body p-4">
                                                            <p class="mb-2 fw-semibold">আপনি কি "<strong>{{ $post->title }}</strong>" পোস্টটি বাতিল করতে চান?</p>
                                                            <div class="mb-3">
                                                                <label class="form-label small text-muted">বাতিলকরণের কারণ / মন্তব্য (ঐচ্ছিক):</label>
                                                                <textarea name="reason" rows="3" class="form-control rounded-3" placeholder="কেন বাতিল করা হলো লিখুন..."></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer bg-light py-2">
                                                            <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">ফিরে যান</button>
                                                            <button type="submit" class="btn btn-danger rounded-pill px-4">বাতিল করুন</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Edit action --}}
                                    <a href="{{ route('admin.content.edit', ['type' => 'blog', 'id' => $post->id]) }}" 
                                       class="btn btn-sm btn-outline-primary px-2 py-1" title="সম্পাদনা করুন">
                                        <i class="fas fa-pen-to-square"></i>
                                    </a>

                                    {{-- Delete action --}}
                                    <form action="{{ route('admin.content.destroy', ['type' => 'blog', 'id' => $post->id]) }}" 
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('আপনি কি নিশ্চিত যে এই ব্লগ পোস্টটি মুছে ফেলতে চান?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger px-2 py-1" title="মুছে ফেলুন">
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                    </form>

                                    @if($isPublished)
                                        <a href="{{ route('blog.show', $post->slug) }}" target="_blank" rel="noopener" 
                                           class="btn btn-sm btn-light border px-2 py-1" title="সাইটে দেখুন">
                                            <i class="fas fa-eye text-muted"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($posts->hasPages())
            <div class="adm-card__foot d-flex flex-wrap justify-content-between align-items-center gap-2 p-3 bg-white border-top">
                <span class="text-muted small">
                    মোট @bn($posts->total())টির মধ্যে @bn($posts->firstItem())–@bn($posts->lastItem()) দেখানো হচ্ছে
                </span>
                {{ $posts->links() }}
            </div>
        @endif
    @endif
</div>

{{-- ========================================================================= --}}
{{-- 1. BLOG DESIGN & TYPOGRAPHY CUSTOMIZER MODAL                              --}}
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
                        <h5 class="modal-title fw-bold fs-6 mb-0 text-white" id="blogCustomizerModalLabel">ব্লগ ও সাহিত্যপত্র ডাইনামিক ডিজাইন কাস্টমাইজার</h5>
                        <span class="small text-white-50" style="font-size: 0.78rem;">হেডার ব্যানার, লেখার ফন্ট, লাইন স্পেসিং ও রিডিং লেআউট নিয়ন্ত্রণ করুন</span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('admin.blog.settings.update') }}" method="POST" id="blogCustomizerForm">
                @csrf
                <div class="modal-body p-4 bg-light">
                    <div class="row g-4">
                        
                        <!-- Left Column: Controls & Tabs -->
                        <div class="col-lg-7">
                            
                            <!-- Customizer Navigation Tabs -->
                            <ul class="nav nav-pills nav-fill bg-white p-1.5 rounded-pill shadow-xs border mb-3" id="customizerTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active rounded-pill fw-semibold py-1.5 small" id="tab-typography-btn" data-bs-toggle="pill" data-bs-target="#tab-typography" type="button" role="tab">
                                        <i class="fas fa-font me-1"></i> ফন্ট ও লাইন স্পেসিং
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link rounded-pill fw-semibold py-1.5 small" id="tab-header-btn" data-bs-toggle="pill" data-bs-target="#tab-header" type="button" role="tab">
                                        <i class="fas fa-image me-1"></i> হেডার ও ব্যানার
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link rounded-pill fw-semibold py-1.5 small" id="tab-reading-btn" data-bs-toggle="pill" data-bs-target="#tab-reading" type="button" role="tab">
                                        <i class="fas fa-book-open me-1"></i> রিডার ও লেআউট
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content" id="customizerTabsContent">
                                
                                <!-- Tab 1: Typography & Line Height -->
                                <div class="tab-pane fade show active" id="tab-typography" role="tabpanel">
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

                                <!-- Tab 2: Header & Masthead Banner -->
                                <div class="tab-pane fade" id="tab-header" role="tabpanel">
                                    <div class="card border-0 shadow-xs rounded-3 p-3.5 bg-white mb-3">
                                        <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                                            <i class="fas fa-heading text-primary"></i>
                                            <span>ব্লগ ব্যানার ও হেডার কাস্টমাইজেশন</span>
                                        </h6>

                                        <div class="row g-3">
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
                                                       value="{{ $blogSettings['hero_title'] ?? 'আইডিয়া ব্লগ ও সাহিত্যপত্র' }}" oninput="updateLivePreview()">
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
{{-- 2. BULK BLOG TYPOGRAPHY NORMALIZER MODAL                                  --}}
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

@push('scripts')
<script>
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

    // Update Masthead Box
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

    // Update Book Sheet & Typography
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
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

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
    .catch(err => {
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
