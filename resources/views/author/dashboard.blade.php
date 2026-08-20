@extends('layouts.app')

@section('title', 'লেখক ড্যাশবোর্ড — ' . ($user->name ?? 'লেখক'))

@section('content')
<div class="container py-4 mb-5">

    {{-- Prominent Feedback Alerts (Success, Error with guidance, Validation errors) --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm p-3.5 mb-4 d-flex align-items-center gap-3 bg-success bg-opacity-10 border-start border-4 border-success" role="alert">
            <div class="rounded-circle bg-success text-white p-2 d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs" style="width: 44px; height: 44px;">
                <i class="fa-solid fa-circle-check fs-4"></i>
            </div>
            <div class="flex-grow-1">
                <div class="fw-bold text-success fs-6">🎉 আপনার পোস্ট সাবমিট হয়েছে!</div>
                <div class="text-dark small">{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm p-3 mb-4 d-flex align-items-start gap-3 bg-danger bg-opacity-10 border-start border-4 border-danger" role="alert">
            <div class="rounded-circle bg-danger text-white p-2 d-flex align-items-center justify-content-center flex-shrink-0 mt-1" style="width: 40px; height: 40px;">
                <i class="fa-solid fa-triangle-exclamation fs-5"></i>
            </div>
            <div class="flex-grow-1">
                <div class="fw-bold text-danger">পোস্ট সংরক্ষণের সময় সতর্কতা</div>
                <div class="text-dark small mb-2">{{ session('error') }}</div>
                <div class="p-2 bg-white rounded-3 small text-muted border">
                    <strong>সহজ সমাধান:</strong> শিরোনাম ও মূল লেখার বক্স সঠিকভাবে পূরণ করুন। ক্যাটাগরি স্বয়ংক্রিয়ভাবে সেট হয়ে যাবে এবং ছবি না দিলেও স্বয়ংক্রিয়ভাবে আকর্ষণীয় এআই ফটোকার্ড যুক্ত হয়ে যাবে।
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show rounded-4 border-0 shadow-sm p-3 mb-4 d-flex align-items-center gap-3 bg-warning bg-opacity-10 border-start border-4 border-warning" role="alert">
            <div class="rounded-circle bg-warning text-dark p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                <i class="fa-solid fa-circle-exclamation fs-5"></i>
            </div>
            <div class="flex-grow-1">
                <div class="fw-bold text-dark">সতর্কবার্তা</div>
                <div class="text-dark small">{{ session('warning') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm p-3 mb-4" role="alert">
            <div class="d-flex align-items-center gap-2 fw-bold text-danger mb-1">
                <i class="fa-solid fa-circle-xmark"></i> অনুগ্রহ করে নিচের বিষয়গুলো সংশোধন করুন:
            </div>
            <ul class="mb-0 small text-dark ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- LocalStorage Draft Auto-Recovery Notification --}}
    <div id="draftRecoveryAlert" class="alert alert-info alert-dismissible fade show rounded-4 border-0 shadow-sm p-3 mb-4 d-none align-items-center justify-content-between gap-3 bg-info bg-opacity-10 border-start border-4 border-info" role="alert">
        <div class="d-flex align-items-center gap-2.5">
            <i class="fa-solid fa-cloud-arrow-up text-primary fs-4"></i>
            <div>
                <strong class="text-dark">অপ্রকাশিত লেখার ড্রাফট সংরক্ষিত আছে!</strong>
                <div class="text-muted small">আপনার পূর্ববর্তী লেখাটি স্বয়ংক্রিয়ভাবে সংরক্ষিত হয়েছে। আপনি কি এটি পুনরুদ্ধার করতে চান?</div>
            </div>
        </div>
        <div class="d-flex gap-2 flex-shrink-0">
            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold" onclick="restoreLocalDraft()">
                <i class="fa-solid fa-rotate-left me-1"></i>ড্রাফট ফিরিয়ে আনুন
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="discardLocalDraft()">
                মুছে ফেলুন
            </button>
        </div>
    </div>

    {{-- Author Profile Hero Card --}}
    <div class="card p-4 p-md-5 mb-4 border-0 shadow-sm rounded-4 text-white position-relative overflow-hidden" 
         style="background: linear-gradient(135deg, #064e3b 0%, #047857 50%, #059669 100%);">
        <div class="position-absolute end-0 bottom-0 opacity-10 d-none d-md-block pe-4 pb-2" style="pointer-events: none;">
            <i class="fas fa-feather-pointed" style="font-size: 13rem;"></i>
        </div>

        <div class="row align-items-center position-relative z-1 g-3">
            <div class="col-md-8 d-flex align-items-center gap-3">
                @php
                    $uAvatar = $user->avatar;
                    $uAvatarUrl = null;
                    if ($uAvatar) {
                        $uAvatarUrl = str_starts_with($uAvatar, 'http') ? $uAvatar : (str_starts_with($uAvatar, 'storage/') ? asset($uAvatar) : asset('storage/' . $uAvatar));
                    }
                @endphp
                <div class="position-relative flex-shrink-0">
                    @if($uAvatarUrl)
                        <img src="{{ $uAvatarUrl }}" alt="{{ $user->name }}" 
                             class="rounded-circle border border-3 border-warning shadow-sm object-fit-cover" 
                             style="width: 75px; height: 75px;">
                    @else
                        <div class="rounded-circle bg-white text-success d-flex align-items-center justify-content-center shadow-sm fs-2 fw-bold border border-3 border-warning" 
                             style="width: 75px; height: 75px;">
                            {{ mb_substr($user->name, 0, 1) }}
                        </div>
                    @endif
                </div>
                <div>
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1 bg-white bg-opacity-20 rounded-pill mb-1.5 backdrop-blur shadow-sm">
                        <i class="fas fa-certificate text-warning"></i>
                        <span class="small fw-semibold text-white">অনুমোদিত লেখক ও গবেষক পোর্টাল</span>
                    </div>
                    <h1 class="fw-bold display-6 mb-1 text-white">স্বাগতম, {{ $user->name }}!</h1>
                    <p class="fs-6 opacity-90 mb-2 text-light">
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
                            <form id="authorBlogWriteForm" method="POST" action="{{ $editPost ? route('author.blog.update', $editPost->id) : route('author.blog.store') }}" enctype="multipart/form-data">
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
                                                ফিচার্ড ফটোকার্ড / কভার ছবি <span class="text-muted small fw-normal">(ঐচ্ছিক)</span>
                                            </label>
                                            <button type="button" class="btn btn-sm btn-outline-success fw-bold rounded-pill px-2.5 py-0.5" 
                                                    style="font-size: 0.78rem;" onclick="openAiPhotocardGenerator()">
                                                <i class="fa-solid fa-wand-magic-sparkles me-1 text-warning"></i> এআই ফটোকার্ড তৈরি করুন
                                            </button>
                                        </div>

                                        <input type="hidden" name="ai_photocard_data" id="aiPhotocardDataInput" value="">
                                        
                                        <!-- Real File Input (Always optional so mobile writers can submit effortlessly) -->
                                        <input type="file" name="featured_image" id="featuredImageInput" class="form-control rounded-3 @error('featured_image') is-invalid @enderror" 
                                               accept="image/jpeg,image/png,image/webp" onchange="handlePhotocardSelection(this)">
                                        @error('featured_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        
                                        <!-- Error Notification Box -->
                                        <div id="photocardErrorAlert" class="alert alert-danger p-2 small mt-2 d-none rounded-3" style="font-size: 0.8rem;">
                                            <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                            <span id="photocardErrorText">ছবি প্রক্রিয়াকরণে সমস্যা হয়েছে। অনুগ্রহ করে পুনরায় সঠিক ফরম্যাটের ছবি আপলোড করুন অথবা এআই দিয়ে ফটোকার্ড তৈরি করুন।</span>
                                        </div>

                                        <!-- Mobile-Friendly Photocard Tip -->
                                        <div class="mt-2 p-2 bg-light border rounded-3 text-muted small" style="font-size: 0.75rem;">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span class="fw-semibold text-dark"><i class="fa-solid fa-wand-magic-sparkles text-success me-1"></i>অটো-জেনারেটর সুবিধা:</span>
                                                <span class="badge bg-success-subtle text-success border rounded-pill">১৬:৯ এইচডি সাইজ</span>
                                            </div>
                                            <div class="mt-1">
                                                ছবি না দিলেও কোনো সমস্যা নেই! লেখার শিরোনাম অনুযায়ী আকর্ষণীয় সাহিত্যিক ফটোকার্ড স্বয়ংক্রিয়ভাবে তৈরি হয়ে যাবে।
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
                                        
                                        <!-- Mobile-Responsive Touch-Friendly Rich Formatting Toolbar -->
                                        <div class="d-flex align-items-center overflow-x-auto pb-1 gap-1" style="max-width: 100%;">
                                            <div class="btn-group btn-group-sm bg-light border rounded-pill p-0.5 shadow-xs flex-nowrap" role="group" aria-label="Formatting Toolbar">
                                                <button type="button" class="btn btn-light rounded-pill px-2.5 py-1 fw-bold text-dark" onclick="formatContent('bold')" title="বোল্ড (Bold)">
                                                    <i class="fa-solid fa-bold"></i>
                                                </button>
                                                <button type="button" class="btn btn-light rounded-pill px-2.5 py-1 fst-italic text-dark" onclick="formatContent('italic')" title="ইটালিক (Italic)">
                                                    <i class="fa-solid fa-italic"></i>
                                                </button>
                                                <button type="button" class="btn btn-light rounded-pill px-2.5 py-1 text-decoration-underline text-dark" onclick="formatContent('underline')" title="আন্ডারলাইন (Underline)">
                                                    <i class="fa-solid fa-underline"></i>
                                                </button>
                                                <button type="button" class="btn btn-light rounded-pill px-2.5 py-1 text-dark fw-bold" onclick="formatContent('h3')" title="উপ-শিরোনাম (Heading 3)">
                                                    H3
                                                </button>
                                                <button type="button" class="btn btn-light rounded-pill px-2.5 py-1 text-dark" onclick="formatContent('quote')" title="উদ্ধৃতি (Quote)">
                                                    <i class="fa-solid fa-quote-left"></i>
                                                </button>
                                                <button type="button" class="btn btn-light rounded-pill px-2.5 py-1 text-dark" onclick="formatContent('list')" title="বুলেট তালিকা">
                                                    <i class="fa-solid fa-list-ul"></i>
                                                </button>
                                                <button type="button" class="btn btn-light rounded-pill px-2.5 py-1 text-primary fw-semibold" onclick="formatContent('poetry')" title="কবিতার লাইন ও স্তবক বিন্যাস (Poetry Mode)">
                                                    <i class="fa-solid fa-feather-pointed me-1 text-primary"></i> কবিতা
                                                </button>
                                                <button type="button" class="btn btn-light rounded-pill px-2.5 py-1 text-dark fw-semibold" onclick="formatContent('tight_lines')" title="লাইনের স্পেস কমান (Tight Spacing)">
                                                    <i class="fa-solid fa-compress-alt me-1 text-primary"></i> স্পেস কমান
                                                </button>
                                                <button type="button" class="btn btn-warning rounded-pill px-2.5 py-1 text-dark fw-bold" onclick="runAuthorSpellCheck()" title="প্রমিত বাংলা একাডেমি ও ইংরেজি বানান পরীক্ষা">
                                                    <i class="fa-solid fa-spell-check text-dark me-1"></i> বানান পরীক্ষা
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <textarea name="content" id="blogContentTextarea" rows="12" class="form-control rounded-3 @error('content') is-invalid @enderror" 
                                              oninput="updateContentStats()" required 
                                              placeholder="আপনার প্রবন্ধ, গল্প, কবিতা, বই পর্যালোচনা বা মতামত এখানে বিস্তারিত লিখুন... প্রয়োজনমতো উপরের টুলবার দিয়ে বোল্ড, ইটালিক ও হেডিং ব্যবহার করতে পারেন।">{{ old('content', $editPost->content ?? '') }}</textarea>
                                    @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    
                                    <!-- Spell Checker Notification Box for Author -->
                                    <div id="spell-results-author" class="mt-2.5 d-none"></div>

                                    <div class="d-flex align-items-center justify-content-between mt-1 text-muted" style="font-size: 0.76rem;">
                                        <span id="contentStatsText"><i class="fa-solid fa-file-lines me-1"></i>শব্দ: ০ | বর্ণ: ০</span>
                                        <span class="text-success"><i class="fa-solid fa-check-double me-1"></i>প্রমিত বানান ও মোবাইল ফ্রেন্ডলি</span>
                                    </div>
                                </div>

                                {{-- Editorial Policy & Terms of Publication Agreement Box --}}
                                <div class="p-3.5 bg-white rounded-3 mb-4 border border-primary border-opacity-25 shadow-xs">
                                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 pb-2.5 mb-2.5 border-bottom">
                                        <div class="d-flex align-items-center gap-2.5">
                                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                                <i class="fas fa-file-contract fs-5"></i>
                                            </div>
                                            <div>
                                                <strong class="text-dark d-block" style="font-size: 0.95rem;">আইডিয়া প্রকাশন ব্লগে লেখা প্রকাশের শর্তাবলি ও সম্পাদকীয় নীতিমালা</strong>
                                                <span class="text-muted" style="font-size: 0.78rem;">কপিরাইট, মতপ্রকাশের স্বাধীনতা ও আইনি সুরক্ষার পূর্ণাঙ্গ সম্পাদকীয় নিয়মাবলি</span>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-xs" onclick="openEditorialPolicyA4Modal()">
                                            <i class="fas fa-file-lines text-primary"></i> <span>A4 পেজ সাইজে পূর্ণাঙ্গ নীতিমালা পড়ুন</span>
                                        </button>
                                    </div>

                                    <div class="form-check pt-1 mb-0">
                                        <input class="form-check-input @error('agree_policy') is-invalid @enderror" type="checkbox" name="agree_policy" id="agreeEditorialPolicyCheckbox" value="1" {{ old('agree_policy') ? 'checked' : '' }} style="cursor: pointer; width: 1.15em; height: 1.15em; margin-top: 0.15em;">
                                        <label class="form-check-label fw-bold text-dark small ms-1" for="agreeEditorialPolicyCheckbox" style="cursor: pointer; line-height: 1.6;">
                                            আমি <a href="javascript:void(0)" onclick="openEditorialPolicyA4Modal()" class="text-primary text-decoration-underline">আইডিয়া প্রকাশন ব্লগে লেখা প্রকাশের শর্তাবলি ও সম্পাদকীয় নীতিমালা</a> মনোযোগ সহকারে পড়েছি এবং এতে পূর্ণ সম্মতি জ্ঞাপন করছি। <span class="text-danger">*</span>
                                        </label>
                                        @error('agree_policy')
                                            <div class="invalid-feedback d-block mt-1"><i class="fas fa-triangle-exclamation me-1"></i>{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="p-3 bg-light rounded-3 mb-4 border d-flex align-items-start gap-2 text-muted small">
                                    <i class="fas fa-info-circle text-primary fs-5 mt-0.5"></i>
                                    <div>
                                        <strong>প্রকাশনা নিয়মাবলী:</strong><br>
                                        • <strong>"খসড়া সংরক্ষণ করুন"</strong> চাপলে লেখাটি শুধুমাত্র আপনার কাছে ড্রাফট হিসেবে থাকবে এবং পরবর্তীতে এডিট করতে পারবেন।<br>
                                        • <strong>"অনুমোদনের জন্য জমা দিন"</strong> চাপলে তা সরাসরি সম্পাদকীয় প্যানেলে রিভিউ ও অনুমোদনের জন্য চলে যাবে।
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap gap-2 justify-content-end">
                                    <button type="button" class="btn btn-outline-primary px-3 px-md-4 py-2.5 rounded-pill fw-semibold" onclick="openArticleLivePreview()">
                                        <i class="fas fa-eye me-1.5"></i> লাইভ প্রিভিউ
                                    </button>
                                    <button type="submit" name="action_type" value="draft" class="btn btn-outline-secondary px-3 px-md-4 py-2.5 rounded-pill fw-semibold" onclick="ensurePhotocardBeforeSubmit()">
                                        <i class="fas fa-bookmark me-1.5"></i> খসড়া সংরক্ষণ
                                    </button>
                                    <button type="button" class="btn btn-success px-4 px-md-5 py-2.5 rounded-pill fw-bold shadow-sm" onclick="handleAuthorPostSubmission(event)">
                                        <i class="fas fa-paper-plane me-1.5"></i> অনুমোদনের জন্য জমা দিন
                                    </button>
                                    <button type="submit" id="realSubmitBtn" name="action_type" value="submit" class="d-none"></button>
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
                        <div class="d-flex flex-wrap gap-2">
                            <a href="tel:+8801726976982" class="btn btn-warning text-dark btn-sm rounded-pill fw-bold px-3">
                                <i class="fas fa-phone me-1"></i> সম্পাদকীয় হেল্পলাইন
                            </a>
                            <a href="https://wa.me/8801726976982" target="_blank" class="btn btn-success btn-sm rounded-pill fw-bold px-3">
                                <i class="fab fa-whatsapp me-1"></i> হোয়াটসঅ্যাপ
                            </a>
                        </div>
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
{{-- ========================================================================= --}}
{{-- MODAL: A4 EDITORIAL POLICY & PUBLICATION GUIDELINES (এ৪ পেজ সাইজ সম্পাদকীয় নীতিমালা) --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="editorialPolicyA4Modal" tabindex="-1" aria-labelledby="editorialPolicyA4ModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            {{-- Modal Header --}}
            <div class="modal-header bg-dark text-white py-2.5 px-4 border-0 d-flex align-items-center justify-content-between no-print">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-warning text-dark p-2 d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px;">
                        <i class="fas fa-file-contract fs-6"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold text-white mb-0" id="editorialPolicyA4ModalLabel">আইডিয়া প্রকাশন — প্রকাশনার শর্তাবলি ও সম্পাদকীয় নীতিমালা</h6>
                        <small class="text-white-50">প্রমিত এ৪ পেজ সাইজ (A4 Paper Sheet View)</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-2.5 py-1" onclick="adjustPolicyFontSize(-1)" title="ফন্ট ছোট করুন">
                        <i class="fas fa-font me-1"></i>A-
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-2.5 py-1" onclick="adjustPolicyFontSize(1)" title="ফন্ট বড় করুন">
                        <i class="fas fa-font me-1"></i>A+
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 py-1 fw-semibold text-warning" onclick="window.print()" title="প্রিন্ট করুন">
                        <i class="fas fa-print me-1"></i>প্রিন্ট
                    </button>
                    <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            {{-- Modal Body: A4 Sheet Paper Container --}}
            <div class="modal-body p-3 p-md-4" style="background: #e2e8f0;">
                <div class="a4-paper-sheet mx-auto" id="a4PolicyDocBody" style="background: #ffffff; max-width: 860px; padding: 50px 55px; border-radius: 4px; box-shadow: 0 10px 30px rgba(0,0,0,0.12); font-family: 'Hind Siliguri', 'Kalpurush', sans-serif; line-height: 1.85; color: #1e293b; font-size: 15.5px;">
                    
                    {{-- Official Header & Seal --}}
                    <div class="text-center pb-3 mb-4 border-bottom border-2 border-primary border-opacity-25">
                        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-light border border-secondary border-opacity-25 mb-2">
                            <span class="fw-bold text-dark" style="font-size: 0.95rem; letter-spacing: 0.5px;">আইডিয়া প্রকাশন • ডিজিটাল সাহিত্য ও ব্লগ প্রকাশনা</span>
                        </div>
                        <h1 class="fw-bolder text-dark mb-2" style="font-size: 1.85rem; line-height: 1.35; color: #0f172a;">
                            আইডিয়া প্রকাশন ব্লগে লেখা প্রকাশের শর্তাবলি ও সম্পাদকীয় নীতিমালা
                        </h1>
                        <div class="text-muted small">
                            <span class="badge bg-primary-subtle text-primary border px-3 py-1.5 rounded-pill fw-semibold" style="font-size: 12.5px;">
                                <i class="fas fa-globe me-1"></i> প্রযোজ্য ক্ষেত্র: আইডিয়া প্রকাশন ব্লগ, ওয়েবসাইট ও সংশ্লিষ্ট ডিজিটাল প্রকাশনা প্ল্যাটফর্ম
                            </span>
                        </div>
                    </div>

                    {{-- Preamble & Constitutional Foundation --}}
                    <div class="p-3.5 rounded-3 mb-4 border-start border-4 border-primary" style="background: #f8fafc; font-size: 0.96rem; text-align: justify;">
                        <p class="mb-2">
                            আইডিয়া প্রকাশন মতপ্রকাশের স্বাধীনতা, জ্ঞানচর্চা, গবেষণা, সৃজনশীলতা, বহুমতের সহাবস্থান এবং দায়িত্বশীল নাগরিক আলোচনাকে উৎসাহিত করে। এই নীতিমালা বাংলাদেশের প্রচলিত আইন, সংবিধানে স্বীকৃত মতপ্রকাশের স্বাধীনতা এবং আন্তর্জাতিকভাবে স্বীকৃত মানবাধিকার ও সাংবাদিকতা-নৈতিকতার মৌলিক নীতির আলোকে প্রণীত।
                        </p>
                        <p class="mb-0 text-muted">
                            বাংলাদেশের সংবিধানের ৩৯ অনুচ্ছেদে মতপ্রকাশ ও সংবাদপত্রের স্বাধীনতা স্বীকৃত হলেও মানহানি, জনশৃঙ্খলা, রাষ্ট্রীয় নিরাপত্তা, শালীনতা, নৈতিকতা, আদালত অবমাননা ও অপরাধে প্ররোচনার মতো বিষয়ে আইনি সীমাবদ্ধতা রয়েছে। একইভাবে আন্তর্জাতিক মানবাধিকার কাঠামোতে মতপ্রকাশের স্বাধীনতার পাশাপাশি অন্যের অধিকার, সুনাম, গোপনীয়তা ও জনস্বার্থের সুরক্ষাকে গুরুত্ব দেওয়া হয়েছে।
                        </p>
                    </div>

                    {{-- Section 1 --}}
                    <h5 class="fw-bold text-dark mt-4 mb-2 pb-1 border-bottom d-flex align-items-center gap-2" style="color: #1e3a8a;">
                        <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size: 12px;">১</span> মতপ্রকাশের স্বাধীনতা
                    </h5>
                    <p class="mb-1.5 text-secondary"><strong>১.১.</strong> আইডিয়া প্রকাশন বিভিন্ন মত, দৃষ্টিভঙ্গি, রাজনৈতিক অবস্থান, সামাজিক বিশ্লেষণ, সাহিত্যিক অভিমত ও সমালোচনামূলক বক্তব্য প্রকাশের সুযোগকে স্বাগত জানায়।</p>
                    <p class="mb-1.5 text-secondary"><strong>১.২.</strong> কোনো লেখা আইডিয়া প্রকাশনের নিজস্ব মতামত নয়; লেখকের মতামত লেখকের নিজস্ব দায়িত্বে প্রকাশিত হবে।</p>
                    <p class="mb-1.5 text-secondary"><strong>১.৩.</strong> কোনো রাজনৈতিক দল, সরকার, প্রতিষ্ঠান, ব্যক্তি বা মতাদর্শের সমালোচনা করা নিষিদ্ধ নয়, যদি তা আইনসম্মত, যুক্তিনির্ভর ও দায়িত্বশীলভাবে উপস্থাপিত হয়।</p>
                    <p class="mb-3 text-secondary"><strong>১.৪.</strong> মতপ্রকাশের স্বাধীনতা অন্যের অধিকার, সুনাম, গোপনীয়তা এবং আইনসম্মত স্বার্থ ক্ষুণ্ন করার অবাধ অধিকার হিসেবে বিবেচিত হবে না।</p>

                    {{-- Section 2 --}}
                    <h5 class="fw-bold text-dark mt-4 mb-2 pb-1 border-bottom d-flex align-items-center gap-2" style="color: #1e3a8a;">
                        <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size: 12px;">২</span> মৌলিকতা ও কপিরাইট
                    </h5>
                    <p class="mb-1.5 text-secondary"><strong>২.১.</strong> জমাকৃত লেখা লেখকের নিজস্ব মৌলিক কাজ হতে হবে।</p>
                    <p class="mb-1.5 text-secondary"><strong>২.২.</strong> অন্যের লেখা, গবেষণা, অনুবাদ, ছবি, গ্রাফিক্স, তথ্যচিত্র, টেবিল, চার্ট বা সৃজনশীল উপাদান অনুমতি বা যথাযথ স্বীকৃতি ছাড়া ব্যবহার করা যাবে না।</p>
                    <p class="mb-1.5 text-secondary"><strong>২.৩.</strong> অন্যের বক্তব্য বা লেখা উদ্ধৃত করলে প্রয়োজন অনুযায়ী উদ্ধৃতি চিহ্ন এবং উৎস উল্লেখ করতে হবে।</p>
                    <p class="mb-1.5 text-secondary"><strong>২.৪.</strong> কপিরাইটযুক্ত উপাদান ব্যবহারের আইনগত অনুমতি লেখককে নিশ্চিত করতে হবে।</p>
                    <p class="mb-1.5 text-secondary"><strong>২.৫.</strong> কোনো লেখা অন্য কোথাও পূর্বে প্রকাশিত হলে তা জানাতে হবে। পুনঃপ্রকাশের ক্ষেত্রে সংশ্লিষ্ট স্বত্বাধিকার ও অনুমতির বিষয়টি লেখককে নিশ্চিত করতে হবে।</p>
                    <p class="mb-3 text-secondary"><strong>২.৬.</strong> কপিরাইট সংক্রান্ত বিরোধ দেখা দিলে লেখকের দেওয়া তথ্য, অনুমতি ও স্বত্বসংক্রান্ত দাবির ভিত্তিতে বিষয়টি পর্যালোচনা করা হবে।</p>

                    {{-- Section 3 --}}
                    <h5 class="fw-bold text-dark mt-4 mb-2 pb-1 border-bottom d-flex align-items-center gap-2" style="color: #1e3a8a;">
                        <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size: 12px;">৩</span> AI ও প্রযুক্তি-সহায়িত লেখা
                    </h5>
                    <p class="mb-1.5 text-secondary"><strong>৩.১.</strong> লেখক গবেষণা, ভাষা সম্পাদনা, অনুবাদ, তথ্য সংগঠন বা খসড়া তৈরিতে AI বা অন্য কোনো প্রযুক্তি ব্যবহার করতে পারবেন।</p>
                    <p class="mb-1.5 text-secondary"><strong>৩.২.</strong> AI ব্যবহার করা হলেও লেখার তথ্যগত যথার্থতা, মৌলিকতা, সূত্রের সত্যতা এবং আইনগত দায় লেখকের ওপর বর্তাবে।</p>
                    <p class="mb-1.5 text-secondary"><strong>৩.৩.</strong> AI দ্বারা তৈরি বা যাচাই না-করা কাল্পনিক সূত্র, উদ্ধৃতি, পরিসংখ্যান, ব্যক্তি, ঘটনা বা গবেষণার তথ্য প্রকাশ করা যাবে না।</p>
                    <p class="mb-3 text-secondary"><strong>৩.৪.</strong> কোনো ব্যক্তির লেখা বা গবেষণাকে AI-উৎপাদিত বলে মিথ্যাভাবে উপস্থাপন করা যাবে না।</p>

                    {{-- Section 4 --}}
                    <h5 class="fw-bold text-dark mt-4 mb-2 pb-1 border-bottom d-flex align-items-center gap-2" style="color: #1e3a8a;">
                        <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size: 12px;">৪</span> তথ্যের যথার্থতা ও যাচাই
                    </h5>
                    <p class="mb-1.5 text-secondary"><strong>৪.১.</strong> ঐতিহাসিক, রাজনৈতিক, সামাজিক, বৈজ্ঞানিক, চিকিৎসা, আইন, অর্থনীতি ও গবেষণাধর্মী লেখায় যথাসম্ভব নির্ভরযোগ্য ও যাচাইযোগ্য তথ্য ব্যবহার করতে হবে।</p>
                    <p class="mb-1.5 text-secondary"><strong>৪.২.</strong> গুরুত্বপূর্ণ দাবি, পরিসংখ্যান, তারিখ, উদ্ধৃতি, গবেষণার ফলাফল এবং ঐতিহাসিক ঘটনার ক্ষেত্রে উৎস উল্লেখ করা বাঞ্ছনীয় এবং প্রয়োজন অনুযায়ী বাধ্যতামূলক হতে পারে।</p>
                    <p class="mb-1.5 text-secondary"><strong>৪.৩.</strong> মতামত, অনুমান, বিশ্লেষণ, অভিযোগ বা রাজনৈতিক মূল্যায়নকে প্রতিষ্ঠিত তথ্য হিসেবে উপস্থাপন করা যাবে না।</p>
                    <p class="mb-1.5 text-secondary"><strong>৪.৪.</strong> তথ্যের ক্ষেত্রে সন্দেহ থাকলে লেখককে তা স্পষ্টভাবে উল্লেখ করতে হবে—যেমন “অভিযোগ রয়েছে”, “গবেষকের মতে”, “প্রতিবেদন অনুযায়ী”, “এ বিষয়ে মতভেদ রয়েছে” ইত্যাদি।</p>
                    <p class="mb-3 text-secondary"><strong>৪.৫.</strong> মিথ্যা, জাল, বিকৃত বা ইচ্ছাকৃতভাবে বিভ্রান্তিকর তথ্য প্রকাশ করা যাবে না।</p>

                    {{-- Section 5 --}}
                    <h5 class="fw-bold text-dark mt-4 mb-2 pb-1 border-bottom d-flex align-items-center gap-2" style="color: #1e3a8a;">
                        <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size: 12px;">৫</span> রাজনৈতিক, সামাজিক ও সমকালীন লেখা
                    </h5>
                    <p class="mb-1.5 text-secondary"><strong>৫.১.</strong> রাজনৈতিক ও সমকালীন বিষয়ে স্বাধীন মতামত প্রকাশ করা যাবে।</p>
                    <p class="mb-1.5 text-secondary"><strong>৫.২.</strong> সরকার, রাজনৈতিক দল, জনপ্রতিনিধি, সরকারি প্রতিষ্ঠান বা অন্য কোনো সংগঠনের সমালোচনা করা যেতে পারে।</p>
                    <p class="mb-1.5 text-secondary"><strong>৫.৩.</strong> তবে সমালোচনা যেন মিথ্যা তথ্য, উদ্দেশ্যপ্রণোদিত অপপ্রচার, মানহানি, ব্যক্তিগত আক্রমণ বা সহিংসতায় উসকানিতে পরিণত না হয়।</p>
                    <p class="mb-1.5 text-secondary"><strong>৫.৪.</strong> কোনো রাজনৈতিক বা সামাজিক দাবির ক্ষেত্রে সম্ভব হলে সংশ্লিষ্ট পক্ষের বক্তব্য বা নির্ভরযোগ্য উৎস বিবেচনা করা হবে।</p>
                    <p class="mb-3 text-secondary"><strong>৫.৫.</strong> বিতর্কিত ঐতিহাসিক বা রাজনৈতিক বিষয়ে একাধিক মত থাকলে লেখায় তা যথাসম্ভব স্বচ্ছভাবে উপস্থাপন করা উচিত।</p>

                    {{-- Section 6 --}}
                    <h5 class="fw-bold text-dark mt-4 mb-2 pb-1 border-bottom d-flex align-items-center gap-2" style="color: #1e3a8a;">
                        <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size: 12px;">৬</span> মানহানি ও ব্যক্তির সুনাম
                    </h5>
                    <p class="mb-1.5 text-secondary"><strong>৬.১.</strong> কোনো ব্যক্তির বিরুদ্ধে অপরাধ, দুর্নীতি, অনৈতিকতা, যৌন অপরাধ, প্রতারণা বা অন্য কোনো গুরুতর অভিযোগ প্রকাশের ক্ষেত্রে যথাযথ প্রমাণ ও নির্ভরযোগ্য উৎস থাকা প্রয়োজন।</p>
                    <p class="mb-1.5 text-secondary"><strong>৬.২.</strong> আদালতে অভিযোগ প্রমাণিত হওয়ার আগে কোনো ব্যক্তিকে নিশ্চিতভাবে “অপরাধী”, “দুর্নীতিবাজ”, “খুনি” বা অনুরূপ চূড়ান্ত অভিধায় অভিহিত করা থেকে বিরত থাকতে হবে।</p>
                    <p class="mb-1.5 text-secondary"><strong>৬.৩.</strong> অভিযোগ থাকলে “অভিযোগ করা হয়েছে”, “মামলায় অভিযোগ রয়েছে”, “তদন্তে বলা হয়েছে” বা “আদালতের নথি অনুযায়ী”—এ ধরনের যথাযথ ভাষা ব্যবহার করতে হবে।</p>
                    <p class="mb-3 text-secondary"><strong>৬.৪.</strong> বাংলাদেশের দণ্ডবিধির ৪৯৯ ধারায় মানহানির বিধান রয়েছে এবং ৫০০ ধারায় এর শাস্তির বিধান রয়েছে; একই সঙ্গে সত্য ও জনস্বার্থ এবং সদ্ভাবে করা কিছু বক্তব্য আইনে ব্যতিক্রম হিসেবে বিবেচিত হতে পারে।</p>

                    {{-- Section 7 --}}
                    <h5 class="fw-bold text-dark mt-4 mb-2 pb-1 border-bottom d-flex align-items-center gap-2" style="color: #1e3a8a;">
                        <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size: 12px;">৭</span> নির্দোষিতার পূর্বানুমান
                    </h5>
                    <p class="mb-1.5 text-secondary"><strong>৭.১.</strong> কোনো ব্যক্তি আদালতের চূড়ান্ত রায়ে দোষী সাব্যস্ত না হওয়া পর্যন্ত তাঁকে অপরাধী হিসেবে চূড়ান্তভাবে উপস্থাপন করা থেকে বিরত থাকতে হবে।</p>
                    <p class="mb-1.5 text-secondary"><strong>৭.২.</strong> এটি বিশেষভাবে অপরাধ, দুর্নীতি, যৌন অপরাধ, হত্যা, সন্ত্রাস, রাষ্ট্রবিরোধী কার্যক্রম ও অন্যান্য গুরুতর অভিযোগের ক্ষেত্রে প্রযোজ্য।</p>
                    <p class="mb-3 text-secondary"><strong>৭.৩.</strong> এই নীতি আন্তর্জাতিক মানবাধিকার কাঠামোর নির্দোষিতার পূর্বানুমানের নীতির সঙ্গে সামঞ্জস্যপূর্ণ।</p>

                    {{-- Section 8 --}}
                    <h5 class="fw-bold text-dark mt-4 mb-2 pb-1 border-bottom d-flex align-items-center gap-2" style="color: #1e3a8a;">
                        <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size: 12px;">৮</span> ব্যক্তিগত গোপনীয়তা ও ব্যক্তিগত তথ্য
                    </h5>
                    <p class="mb-1.5 text-secondary"><strong>৮.১.</strong> কারও ব্যক্তিগত ঠিকানা, ফোন নম্বর, জাতীয় পরিচয়পত্র নম্বর, ব্যাংক তথ্য, ব্যক্তিগত যোগাযোগ, চিকিৎসা-সংক্রান্ত তথ্য বা অন্যান্য সংবেদনশীল তথ্য অপ্রয়োজনে প্রকাশ করা যাবে না।</p>
                    <p class="mb-1.5 text-secondary"><strong>৮.২.</strong> জনস্বার্থের সঙ্গে সরাসরি সম্পর্ক না থাকলে ব্যক্তিগত জীবনের গোপন তথ্য প্রকাশ করা থেকে বিরত থাকতে হবে।</p>
                    <p class="mb-1.5 text-secondary"><strong>৮.৩.</strong> শিশু ও অপ্রাপ্তবয়স্ক ব্যক্তির পরিচয় প্রকাশের ক্ষেত্রে বিশেষ সতর্কতা অবলম্বন করতে হবে।</p>
                    <p class="mb-3 text-secondary"><strong>৮.৪.</strong> ভুক্তভোগী, যৌন অপরাধের শিকার ব্যক্তি ও শিশুদের পরিচয় প্রকাশের ক্ষেত্রে আইন, জনস্বার্থ ও মানবিক বিবেচনা অগ্রাধিকার পাবে।</p>

                    {{-- Section 9 --}}
                    <h5 class="fw-bold text-dark mt-4 mb-2 pb-1 border-bottom d-flex align-items-center gap-2" style="color: #1e3a8a;">
                        <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size: 12px;">৯</span> ধর্ম, জাতি, বর্ণ, লিঙ্গ ও সম্প্রদায়
                    </h5>
                    <p class="mb-1.5 text-secondary"><strong>৯.১.</strong> ধর্ম, জাতি, বর্ণ, ভাষা, লিঙ্গ, জন্মস্থান বা কোনো সামাজিক পরিচয়ের ভিত্তিতে কোনো জনগোষ্ঠীর বিরুদ্ধে ঘৃণা, বৈষম্য বা সহিংসতায় উসকানি দেওয়া যাবে না।</p>
                    <p class="mb-1.5 text-secondary"><strong>৯.২.</strong> ধর্ম বা মতাদর্শের সমালোচনা করা এবং কোনো ধর্মীয়/সামাজিক জনগোষ্ঠীর বিরুদ্ধে বিদ্বেষ ছড়ানো—দুটি বিষয়কে এক হিসেবে বিবেচনা করা হবে না।</p>
                    <p class="mb-3 text-secondary"><strong>৯.৩.</strong> আন্তর্জাতিক মানবাধিকার কাঠামোতে মতপ্রকাশের স্বাধীনতার পাশাপাশি জাতীয়, জাতিগত বা ধর্মীয় বিদ্বেষকে বৈষম্য, শত্রুতা বা সহিংসতায় উসকানি দেওয়ার ক্ষেত্রে বিশেষ সীমাবদ্ধতার কথা বলা হয়েছে।</p>

                    {{-- Section 10 --}}
                    <h5 class="fw-bold text-dark mt-4 mb-2 pb-1 border-bottom d-flex align-items-center gap-2" style="color: #1e3a8a;">
                        <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size: 12px;">১০</span> সহিংসতা, অপরাধ ও বেআইনি কর্মকাণ্ড
                    </h5>
                    <p class="mb-1.5 text-secondary">নিম্নোক্ত ধরনের কনটেন্ট প্রকাশ করা হবে না—</p>
                    <ul class="text-secondary mb-2" style="padding-left: 20px;">
                        <li>সহিংসতায় সরাসরি উসকানি;</li>
                        <li>কোনো ব্যক্তি বা গোষ্ঠীর বিরুদ্ধে হামলার আহ্বান;</li>
                        <li>সন্ত্রাসী বা সহিংস অপরাধকে উৎসাহিত করা;</li>
                        <li>অপরাধ সংঘটনের ব্যবহারিক নির্দেশনা;</li>
                        <li>বেআইনি কর্মকাণ্ডের প্রশিক্ষণ বা প্রচারণা;</li>
                        <li>সহিংসতা বা হত্যাকে মহিমান্বিত করে এমন কনটেন্ট।</li>
                    </ul>
                    <p class="mb-3 text-secondary small fst-italic">তবে ইতিহাস, গবেষণা, সাংবাদিকতা, সাহিত্য বা জনস্বার্থের আলোচনার প্রয়োজনে অপরাধ বা সহিংসতার ঘটনা তথ্যভিত্তিকভাবে আলোচনা করা যেতে পারে।</p>

                    {{-- Section 11 --}}
                    <h5 class="fw-bold text-dark mt-4 mb-2 pb-1 border-bottom d-flex align-items-center gap-2" style="color: #1e3a8a;">
                        <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size: 12px;">১১</span> যৌনতা, শিশু ও সংবেদনশীল কনটেন্ট
                    </h5>
                    <p class="mb-1.5 text-secondary"><strong>১১.১.</strong> পর্নোগ্রাফিক বা যৌন উত্তেজনামূলক কনটেন্ট প্রকাশ করা হবে না।</p>
                    <p class="mb-1.5 text-secondary"><strong>১১.২.</strong> যৌন অপরাধ, শিশু নির্যাতন বা মানবপাচারের মতো বিষয়ে তথ্যভিত্তিক ও জনস্বার্থসংশ্লিষ্ট লেখা প্রকাশ করা যেতে পারে।</p>
                    <p class="mb-3 text-secondary"><strong>১১.৩.</strong> ভুক্তভোগীর পরিচয়, ছবি বা ব্যক্তিগত তথ্য প্রকাশের ক্ষেত্রে সর্বোচ্চ সতর্কতা অবলম্বন করতে হবে।</p>

                    {{-- Section 12 --}}
                    <h5 class="fw-bold text-dark mt-4 mb-2 pb-1 border-bottom d-flex align-items-center gap-2" style="color: #1e3a8a;">
                        <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size: 12px;">১২</span> ছবি, ভিডিও ও অন্যান্য ডিজিটাল উপাদান
                    </h5>
                    <p class="mb-1.5 text-secondary"><strong>১২.১.</strong> লেখার সঙ্গে ব্যবহৃত ছবি, ভিডিও, অডিও, গ্রাফিক্স, চার্ট বা অন্য কোনো উপাদানের স্বত্ব ও ব্যবহার-অনুমতি লেখককে নিশ্চিত করতে হবে।</p>
                    <p class="mb-1.5 text-secondary"><strong>১২.২.</strong> কপিরাইটযুক্ত ছবি বা ভিডিও যথাযথ অনুমতি ছাড়া ব্যবহার করা যাবে না।</p>
                    <p class="mb-1.5 text-secondary"><strong>১২.৩.</strong> প্রয়োজন অনুযায়ী ছবির উৎস, আলোকচিত্রীর নাম, লাইসেন্স বা স্বত্বের তথ্য উল্লেখ করতে হবে।</p>
                    <p class="mb-3 text-secondary"><strong>১২.৪.</strong> AI-generated image বা altered image ব্যবহার করলে প্রয়োজনে তা উল্লেখ করতে হবে, বিশেষত যদি তা বাস্তব ঘটনা বা ব্যক্তিকে উপস্থাপন করে।</p>

                    {{-- Section 13 --}}
                    <h5 class="fw-bold text-dark mt-4 mb-2 pb-1 border-bottom d-flex align-items-center gap-2" style="color: #1e3a8a;">
                        <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size: 12px;">১৩</span> বিজ্ঞাপন, স্পনসরশিপ ও স্বার্থের সংঘাত
                    </h5>
                    <p class="mb-1.5 text-secondary"><strong>১৩.১.</strong> পণ্য, প্রতিষ্ঠান, ব্যক্তি বা সেবার প্রচারণামূলক লেখা সম্পাদকীয় লেখা হিসেবে গোপনে প্রকাশ করা যাবে না।</p>
                    <p class="mb-1.5 text-secondary"><strong>১৩.২.</strong> অর্থপ্রাপ্ত বা স্পনসরড কনটেন্ট হলে তা যথাযথভাবে চিহ্নিত করা হবে।</p>
                    <p class="mb-3 text-secondary"><strong>১৩.৩.</strong> লেখকের কোনো ব্যবসায়িক, রাজনৈতিক, পেশাগত বা ব্যক্তিগত স্বার্থ সংশ্লিষ্ট বিষয়ে লেখা হলে প্রয়োজন অনুযায়ী তা প্রকাশ করা উচিত।</p>

                    {{-- Section 14 --}}
                    <h5 class="fw-bold text-dark mt-4 mb-2 pb-1 border-bottom d-flex align-items-center gap-2" style="color: #1e3a8a;">
                        <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size: 12px;">১৪</span> লেখকের পরিচয় ও যোগাযোগ
                    </h5>
                    <p class="mb-1 text-secondary">লেখার সঙ্গে নিম্নোক্ত তথ্য প্রদান করা বাঞ্ছনীয়—</p>
                    <ul class="text-secondary mb-2" style="padding-left: 20px;">
                        <li>পূর্ণ নাম;</li>
                        <li>সংক্ষিপ্ত পরিচিতি;</li>
                        <li>পেশা/প্রতিষ্ঠান, প্রযোজ্য ক্ষেত্রে;</li>
                        <li>যোগাযোগের তথ্য;</li>
                        <li>লেখকের ছবি, প্রয়োজন অনুযায়ী;</li>
                        <li>তথ্যসূত্র ও রেফারেন্স।</li>
                    </ul>
                    <p class="mb-3 text-secondary small fst-italic">লেখকের অনুরোধ বা নিরাপত্তাজনিত যৌক্তিক কারণে কিছু পরিচয় গোপন রাখার বিষয় সম্পাদকীয়ভাবে বিবেচনা করা যেতে পারে।</p>

                    {{-- Section 15 --}}
                    <h5 class="fw-bold text-dark mt-4 mb-2 pb-1 border-bottom d-flex align-items-center gap-2" style="color: #1e3a8a;">
                        <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size: 12px;">১৫</span> সম্পাদনা ও তথ্য যাচাইয়ের অধিকার
                    </h5>
                    <p class="mb-1.5 text-secondary"><strong>১৫.১.</strong> আইডিয়া প্রকাশন বানান, ব্যাকরণ, ভাষা, শিরোনাম, উপশিরোনাম, বিন্যাস ও প্রয়োজনীয় সম্পাদকীয় সম্পাদনা করতে পারবে।</p>
                    <p class="mb-1.5 text-secondary"><strong>১৫.২.</strong> তথ্যগত অসঙ্গতি, অস্পষ্টতা বা গুরুতর অভিযোগ থাকলে প্রকাশের আগে লেখকের কাছে ব্যাখ্যা, সূত্র বা সংশোধন চাওয়া হতে পারে।</p>
                    <p class="mb-1.5 text-secondary"><strong>১৫.৩.</strong> প্রয়োজন হলে প্রকাশের পরও ভুল তথ্য সংশোধন, আপডেট, সংযোজন, প্রত্যাহার বা অপসারণ করা হতে পারে।</p>
                    <p class="mb-3 text-secondary"><strong>১৫.৪.</strong> কোনো লেখা সম্পাদকীয় নীতিমালা, আইন, জনস্বার্থ বা প্রকাশনার মানদণ্ডের সঙ্গে অসঙ্গতিপূর্ণ হলে প্রকাশ না করার অধিকার আইডিয়া প্রকাশন সংরক্ষণ করে।</p>

                    {{-- Section 16 --}}
                    <h5 class="fw-bold text-dark mt-4 mb-2 pb-1 border-bottom d-flex align-items-center gap-2" style="color: #1e3a8a;">
                        <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size: 12px;">১৬</span> সংশোধন ও প্রত্যাহার নীতি
                    </h5>
                    <p class="mb-1.5 text-secondary"><strong>১৬.১.</strong> প্রকাশিত লেখায় উল্লেখযোগ্য তথ্যগত ভুল প্রমাণিত হলে আইডিয়া প্রকাশন সংশোধনী প্রকাশ করতে পারে।</p>
                    <p class="mb-1.5 text-secondary"><strong>১৬.২.</strong> গুরুতর ভুল, কপিরাইট লঙ্ঘন, জালিয়াতি, মিথ্যা পরিচয় বা আইনগত ঝুঁকি প্রমাণিত হলে লেখা আংশিক বা সম্পূর্ণ প্রত্যাহার করা যেতে পারে।</p>
                    <p class="mb-3 text-secondary"><strong>১৬.৩.</strong> সংশোধনের ক্ষেত্রে মূল বক্তব্য অযথা পরিবর্তন না করে ভুল অংশ সংশোধন করা হবে এবং প্রয়োজন অনুযায়ী সংশোধনের নোট দেওয়া হবে।</p>

                    {{-- Section 17 --}}
                    <h5 class="fw-bold text-dark mt-4 mb-2 pb-1 border-bottom d-flex align-items-center gap-2" style="color: #1e3a8a;">
                        <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size: 12px;">১৭</span> লেখকের দায়বদ্ধতা
                    </h5>
                    <p class="mb-1.5 text-secondary"><strong>১৭.১.</strong> লেখক তাঁর জমা দেওয়া লেখার তথ্য, বক্তব্য, উদ্ধৃতি, ছবি, সূত্র এবং স্বত্বসংক্রান্ত দাবির জন্য দায়ী থাকবেন।</p>
                    <p class="mb-1.5 text-secondary"><strong>১৭.২.</strong> লেখকের মিথ্যা তথ্য, কপিরাইট লঙ্ঘন, মানহানিকর বক্তব্য বা অন্য কোনো বেআইনি উপাদানের কারণে তৃতীয় পক্ষের দাবি বা আইনি বিরোধ সৃষ্টি হলে তার দায় লেখকের ওপর বর্তাতে পারে।</p>
                    <p class="mb-3 text-secondary"><strong>১৭.৩.</strong> আইডিয়া প্রকাশন কোনো লেখকের ব্যক্তিগত মতামতকে প্রতিষ্ঠানের আনুষ্ঠানিক অবস্থান হিসেবে সমর্থন করে না, যদি না সম্পাদকীয়ভাবে তা স্পষ্টভাবে প্রতিষ্ঠানের বক্তব্য হিসেবে প্রকাশ করা হয়।</p>

                    {{-- Section 18 --}}
                    <h5 class="fw-bold text-dark mt-4 mb-2 pb-1 border-bottom d-flex align-items-center gap-2" style="color: #1e3a8a;">
                        <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size: 12px;">১৮</span> অভিযোগ ও প্রতিকার
                    </h5>
                    <p class="mb-1.5 text-secondary">কোনো প্রকাশিত লেখায় তথ্যগত ভুল, কপিরাইট লঙ্ঘন, মানহানি, গোপনীয়তা লঙ্ঘন বা অন্য কোনো গুরুতর সমস্যা থাকলে সংশ্লিষ্ট ব্যক্তি বা অধিকারী পক্ষ আইডিয়া প্রকাশনের কাছে যথাযথ প্রমাণসহ অভিযোগ জানাতে পারবেন।</p>
                    <p class="mb-3 text-secondary">অভিযোগ পাওয়ার পর বিষয়টি সম্পাদকীয়ভাবে পর্যালোচনা করা হবে এবং প্রয়োজন অনুযায়ী সংশোধন, বক্তব্য সংযোজন, প্রত্যাহার বা অন্যান্য উপযুক্ত ব্যবস্থা নেওয়া হতে পারে।</p>

                    {{-- Section 19 --}}
                    <h5 class="fw-bold text-dark mt-4 mb-2 pb-1 border-bottom d-flex align-items-center gap-2" style="color: #1e3a8a;">
                        <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size: 12px;">১৯</span> লেখা প্রকাশের নিশ্চয়তা নেই
                    </h5>
                    <p class="mb-1.5 text-secondary">লেখা জমা দেওয়া মানেই তা প্রকাশের নিশ্চয়তা নয়।</p>
                    <p class="mb-3 text-secondary">বিষয়বস্তুর মান, মৌলিকতা, তথ্যের নির্ভরযোগ্যতা, জনস্বার্থ, সম্পাদকীয় নীতি, আইনগত ঝুঁকি এবং ব্লগের বিষয়গত প্রাসঙ্গিকতা বিবেচনা করে প্রকাশের সিদ্ধান্ত নেওয়া হবে।</p>

                    {{-- Section 20 --}}
                    <h5 class="fw-bold text-dark mt-4 mb-2 pb-1 border-bottom d-flex align-items-center gap-2" style="color: #1e3a8a;">
                        <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size: 12px;">২০</span> আইন ও নীতিমালার প্রযোজ্যতা
                    </h5>
                    <p class="mb-1.5 text-secondary">এই নীতিমালা বাংলাদেশের প্রচলিত আইন, সংবিধান, কপিরাইট ও মানহানি-সংক্রান্ত বিধান এবং আন্তর্জাতিকভাবে স্বীকৃত মতপ্রকাশ ও মানবাধিকার নীতির সঙ্গে সামঞ্জস্য রেখে প্রয়োগ করা হবে।</p>
                    <p class="mb-3 text-secondary">তবে এই নীতিমালা কোনো আইনগত পরামর্শ বা আইনের বিকল্প নয়। কোনো নির্দিষ্ট বিষয়ে আইনগত প্রশ্ন দেখা দিলে প্রযোজ্য বাংলাদেশের আইন ও আদালতের সিদ্ধান্ত প্রাধান্য পাবে।</p>

                    {{-- Section 21 --}}
                    <h5 class="fw-bold text-dark mt-4 mb-2 pb-1 border-bottom d-flex align-items-center gap-2" style="color: #1e3a8a;">
                        <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size: 12px;">২১</span> নীতিমালা পরিবর্তন
                    </h5>
                    <p class="mb-4 text-secondary">আইন, প্রযুক্তি, সামাজিক বাস্তবতা ও সম্পাদকীয় প্রয়োজনের পরিবর্তনের সঙ্গে সামঞ্জস্য রেখে আইডিয়া প্রকাশন সময় সময় এই নীতিমালা সংশোধন, সংযোজন বা পরিবর্তন করতে পারে।</p>

                    {{-- Core Principles Box (মূলনীতি) --}}
                    <div class="p-4 rounded-4 shadow-xs" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff;">
                        <h4 class="fw-bold mb-3 d-flex align-items-center gap-2 text-warning">
                            <i class="fas fa-compass"></i> মূলনীতি
                        </h4>
                        <ul class="list-unstyled mb-0 d-flex flex-column gap-2" style="font-size: 1.05rem; line-height: 1.7;">
                            <li class="d-flex align-items-center gap-2">
                                <i class="fas fa-check-circle text-success"></i> <strong>মতপ্রকাশের স্বাধীনতা থাকবে, কিন্তু মিথ্যা তথ্যের স্বাধীনতা নয়।</strong>
                            </li>
                            <li class="d-flex align-items-center gap-2">
                                <i class="fas fa-check-circle text-success"></i> <strong>সমালোচনার অধিকার থাকবে, কিন্তু মানহানি বা ঘৃণা ছড়ানোর অধিকার নয়।</strong>
                            </li>
                            <li class="d-flex align-items-center gap-2">
                                <i class="fas fa-check-circle text-success"></i> <strong>বিতর্ক থাকবে, কিন্তু তথ্য বিকৃতির সুযোগ নয়।</strong>
                            </li>
                            <li class="d-flex align-items-center gap-2">
                                <i class="fas fa-check-circle text-success"></i> <strong>বহুমত থাকবে, কিন্তু সহিংসতায় উসকানি নয়।</strong>
                            </li>
                            <li class="d-flex align-items-center gap-2">
                                <i class="fas fa-check-circle text-success"></i> <strong>স্বাধীন লেখালেখি থাকবে, কিন্তু অন্যের অধিকার ও আইনের প্রতি সম্মান রেখেই।</strong>
                            </li>
                        </ul>
                        <div class="text-end mt-3 pt-2 border-top border-secondary border-opacity-50">
                            <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold" style="font-size: 13px;">— আইডিয়া প্রকাশন</span>
                        </div>
                    </div>

                    {{-- Document Sign-off --}}
                    <div class="text-center mt-4 pt-3 text-muted small border-top">
                        <span>আইডিয়া প্রকাশন সম্পাদকীয় বোর্ড কর্তৃক অনুমোদিত • সংস্করণ ২০২৬</span>
                    </div>

                </div>
            </div>

            {{-- Modal Sticky Footer Agreement Action --}}
            <div class="modal-footer bg-white border-top py-3 px-4 d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 no-print">
                <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" id="modalAgreePolicyCheckbox" style="cursor: pointer; width: 1.25em; height: 1.25em;">
                    <label class="form-check-label fw-bold text-dark small ms-1" for="modalAgreePolicyCheckbox" style="cursor: pointer;">
                        আমি আইডিয়া প্রকাশন ব্লগে লেখা প্রকাশের শর্তাবলি ও সম্পাদকীয় নীতিমালা পড়েছি এবং <strong>এতে সম্মত আছি</strong>।
                    </label>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">বন্ধ করুন</button>
                    <button type="button" class="btn btn-sm btn-success rounded-pill px-4 fw-bold shadow-xs" onclick="applyModalAgreementAndSubmit()">
                        <i class="fas fa-check-double me-1"></i> সম্মতি দিন ও লেখা জমা দিন
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL: AI / TITLE PHOTOCARD GENERATOR (এআই ফটোকার্ড জেনারেটর - ক্লাসিক ভার্সন)  --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="aiPhotocardModal" tabindex="-1" aria-labelledby="aiPhotocardModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header text-white py-3 px-4" style="background: linear-gradient(135deg, #064e3b 0%, #0f172a 100%);">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-warning bg-opacity-25 p-2 d-flex align-items-center justify-content-center text-warning">
                        <i class="fa-solid fa-wand-magic-sparkles fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" id="aiPhotocardModalLabel">এআই ফটোকার্ড ও কভার জেনারেটর</h5>
                        <small class="text-white-50">ক্লাসিক সাহিত্যিক স্টাইলে ফটোকার্ড তৈরি ও কালার কাস্টমাইজ করুন</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-3 p-md-4 bg-light">
                <div class="row g-3">
                    <!-- Live Canvas Preview Box -->
                    <div class="col-12 text-center">
                        <div class="p-2 bg-white rounded-4 shadow-sm border mx-auto" style="max-width: 650px;">
                            <div class="position-relative rounded-3 overflow-hidden" style="aspect-ratio: 16/9; background: #022c22;">
                                <canvas id="aiPhotocardCanvas" width="1200" height="675" style="width: 100%; height: 100%; object-fit: contain;"></canvas>
                            </div>
                            <div class="d-flex align-items-center justify-content-between px-2 pt-2 text-muted" style="font-size: 0.75rem;">
                                <span><i class="fa-solid fa-expand me-1 text-primary"></i> ১২০০ × ৬৭৫ px (১৬:৯ আল্ট্রা এইচডি)</span>
                                <span><i class="fa-solid fa-gem text-warning me-1"></i> ক্লাসিক সাহিত্যপত্র এডিশন</span>
                            </div>
                        </div>
                    </div>

                    <!-- Customization Controls Panel -->
                    <div class="col-12">
                        <div class="card border-0 rounded-4 p-3 bg-white shadow-xs">
                            
                            <!-- 1. Preset Themes -->
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark mb-1.5 d-flex align-items-center justify-content-between">
                                    <span><i class="fa-solid fa-palette text-success me-1"></i> ক্লাসিক কালার থিম:</span>
                                    <span class="text-muted fw-normal" style="font-size: 0.72rem;">পছন্দের থিমে ক্লিক করুন</span>
                                </label>
                                <div class="d-flex flex-wrap gap-1.5" id="aiThemeButtons">
                                    <button type="button" class="btn btn-sm btn-outline-success active rounded-pill px-2.5 py-1" onclick="selectAiTheme('emerald')">
                                        🌿 হেরিটেজ এমারেল্ড
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1" onclick="selectAiTheme('navy')">
                                        👑 রয়্যাল গোল্ড ও নেভি
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-2.5 py-1" onclick="selectAiTheme('parchment')">
                                        📜 ভিন্টেজ পার্চমেন্ট
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1" onclick="selectAiTheme('crimson')">
                                        🍷 ক্লাসিক বোরদক্স
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1" onclick="selectAiTheme('purple')">
                                        ✒️ ইম্পেরিয়াল ভায়োলেট
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-2.5 py-1" onclick="selectAiTheme('onyx')">
                                        🏛️ চারকোল ও সিলভার
                                    </button>
                                </div>
                            </div>

                            <!-- 2. Custom Color Picker & Alignment -->
                            <div class="row g-2 mb-3 align-items-center bg-light p-2.5 rounded-3 border">
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-bold text-dark mb-1">
                                        <i class="fa-solid fa-sliders text-primary me-1"></i> কালার কাস্টমাইজেশন:
                                    </label>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="d-flex align-items-center gap-1">
                                            <input type="color" id="aiColorBg1" value="#022c22" class="form-control form-control-color p-0.5 border rounded-2" style="width: 32px; height: 32px; cursor: pointer;" title="ব্যাকগ্রাউন্ড কালার ১" onchange="onCustomColorChange()">
                                            <span class="small text-muted" style="font-size: 0.72rem;">রং ১</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-1">
                                            <input type="color" id="aiColorBg2" value="#064e3b" class="form-control form-control-color p-0.5 border rounded-2" style="width: 32px; height: 32px; cursor: pointer;" title="ব্যাকগ্রাউন্ড কালার ২" onchange="onCustomColorChange()">
                                            <span class="small text-muted" style="font-size: 0.72rem;">রং ২</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-1">
                                            <input type="color" id="aiColorAccent" value="#fbbf24" class="form-control form-control-color p-0.5 border rounded-2" style="width: 32px; height: 32px; cursor: pointer;" title="বর্ডার ও হাইলাইট গোল্ডেন কালার" onchange="onCustomColorChange()">
                                            <span class="small text-muted" style="font-size: 0.72rem;">বর্ডার/অ্যাকসেন্ট</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-bold text-dark mb-1">
                                        <i class="fa-solid fa-align-center text-primary me-1"></i> শিরোনাম সেটিং ও অ্যালাইনমেন্ট:
                                    </label>
                                    <div class="btn-group btn-group-sm w-100" role="group" aria-label="Alignment">
                                        <button type="button" id="alignCenterBtn" class="btn btn-success active fw-semibold" onclick="setAiAlignment('center')">
                                            <i class="fa-solid fa-align-center me-1"></i> সেন্টার এলাইন (ক্লাসিক)
                                        </button>
                                        <button type="button" id="alignLeftBtn" class="btn btn-outline-secondary fw-semibold" onclick="setAiAlignment('left')">
                                            <i class="fa-solid fa-align-left me-1"></i> লেফট এলাইন
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Title & Subtitle Inputs -->
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold mb-1 text-dark">কার্ডের মূল শিরোনাম:</label>
                                    <input type="text" id="aiCardCustomTitle" class="form-control form-control-sm rounded-2" 
                                           placeholder="যেমন: আমাদের প্রেম ও কিছু কবিতা" oninput="renderAiPhotocard()">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold mb-1 text-dark">উপ-শিরোনাম / ট্যাগলাইন (ঐচ্ছিক):</label>
                                    <input type="text" id="aiCardCustomSubtitle" class="form-control form-control-sm rounded-2" 
                                           placeholder="যেমন: একটি সাহিত্যিক মূল্যায়ন..." oninput="renderAiPhotocard()">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-white border-top py-3 px-4 d-flex flex-wrap justify-content-between gap-2">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3" onclick="downloadGeneratedPhotocard()">
                    <i class="fa-solid fa-download me-1"></i> ইমেজ ডাউনলোড
                </button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">বন্ধ করুন</button>
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
    let aiAlignment = 'center'; // Center alignment is default classic
    let isCustomColor = false;
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
            input.required = false;
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
        isCustomColor = false;
        currentAiTheme = themeName;
        
        const container = document.getElementById('aiThemeButtons');
        if (container) {
            container.querySelectorAll('button').forEach(btn => btn.classList.remove('active', 'btn-success', 'btn-primary', 'btn-danger', 'btn-secondary', 'btn-dark', 'btn-warning'));
            container.querySelectorAll('button').forEach(btn => {
                if (!btn.className.includes('btn-outline-')) {
                    btn.classList.add('btn-outline-secondary');
                }
            });
        }
        if (event && event.currentTarget) {
            event.currentTarget.classList.add('active');
        }

        // Sync color pickers with preset
        const col1 = document.getElementById('aiColorBg1');
        const col2 = document.getElementById('aiColorBg2');
        const colAcc = document.getElementById('aiColorAccent');

        switch (themeName) {
            case 'navy':
                if (col1) col1.value = '#0a192f';
                if (col2) col2.value = '#1e3a8a';
                if (colAcc) colAcc.value = '#fbbf24';
                break;
            case 'parchment':
                if (col1) col1.value = '#38220f';
                if (col2) col2.value = '#582f0e';
                if (colAcc) colAcc.value = '#fef08a';
                break;
            case 'crimson':
                if (col1) col1.value = '#450a0a';
                if (col2) col2.value = '#7f1d1d';
                if (colAcc) colAcc.value = '#fde047';
                break;
            case 'purple':
                if (col1) col1.value = '#2e1065';
                if (col2) col2.value = '#581c87';
                if (colAcc) colAcc.value = '#f0abfc';
                break;
            case 'onyx':
                if (col1) col1.value = '#090d16';
                if (col2) col2.value = '#1e293b';
                if (colAcc) colAcc.value = '#38bdf8';
                break;
            case 'emerald':
            default:
                if (col1) col1.value = '#022c22';
                if (col2) col2.value = '#064e3b';
                if (colAcc) colAcc.value = '#fbbf24';
                break;
        }

        renderAiPhotocard();
    }

    function onCustomColorChange() {
        isCustomColor = true;
        const container = document.getElementById('aiThemeButtons');
        if (container) {
            container.querySelectorAll('button').forEach(btn => btn.classList.remove('active'));
        }
        renderAiPhotocard();
    }

    function setAiAlignment(align) {
        aiAlignment = align;
        const centerBtn = document.getElementById('alignCenterBtn');
        const leftBtn = document.getElementById('alignLeftBtn');

        if (align === 'center') {
            centerBtn.classList.add('btn-success', 'active');
            centerBtn.classList.remove('btn-outline-secondary');
            leftBtn.classList.remove('btn-success', 'active');
            leftBtn.classList.add('btn-outline-secondary');
        } else {
            leftBtn.classList.add('btn-success', 'active');
            leftBtn.classList.remove('btn-outline-secondary');
            centerBtn.classList.remove('btn-success', 'active');
            centerBtn.classList.add('btn-outline-secondary');
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

        // Colors calculation
        let bg1, bg2, accentColor;

        if (isCustomColor) {
            bg1 = document.getElementById('aiColorBg1')?.value || '#022c22';
            bg2 = document.getElementById('aiColorBg2')?.value || '#064e3b';
            accentColor = document.getElementById('aiColorAccent')?.value || '#fbbf24';
        } else {
            switch (currentAiTheme) {
                case 'navy':
                    bg1 = '#07152b'; bg2 = '#1e3a8a'; accentColor = '#fbbf24';
                    break;
                case 'parchment':
                    bg1 = '#321c0b'; bg2 = '#542c0d'; accentColor = '#fef08a';
                    break;
                case 'crimson':
                    bg1 = '#3b0606'; bg2 = '#7f1d1d'; accentColor = '#fde047';
                    break;
                case 'purple':
                    bg1 = '#200b47'; bg2 = '#581c87'; accentColor = '#f0abfc';
                    break;
                case 'onyx':
                    bg1 = '#090d16'; bg2 = '#1e293b'; accentColor = '#38bdf8';
                    break;
                case 'emerald':
                default:
                    bg1 = '#022c22'; bg2 = '#064e3b'; accentColor = '#fbbf24';
                    break;
            }
        }

        // 1. Background Gradient
        const bgGradient = ctx.createLinearGradient(0, 0, W, H);
        bgGradient.addColorStop(0, bg1);
        bgGradient.addColorStop(0.5, bg2);
        bgGradient.addColorStop(1, bg1);
        ctx.fillStyle = bgGradient;
        ctx.fillRect(0, 0, W, H);

        // 2. Artistic Center Glow
        const radGrad = ctx.createRadialGradient(W / 2, H * 0.45, 50, W / 2, H * 0.45, 550);
        radGrad.addColorStop(0, hexToRgba(accentColor, 0.15));
        radGrad.addColorStop(1, 'rgba(0, 0, 0, 0.45)');
        ctx.fillStyle = radGrad;
        ctx.fillRect(0, 0, W, H);

        // 3. Classic Royal Double Borders & Filigree Corners
        ctx.strokeStyle = hexToRgba(accentColor, 0.35);
        ctx.lineWidth = 1.5;
        ctx.strokeRect(30, 30, W - 60, H - 60);

        ctx.strokeStyle = accentColor;
        ctx.lineWidth = 3;
        ctx.strokeRect(42, 42, W - 84, H - 84);

        // Corner ornaments
        ctx.lineWidth = 2;
        ctx.strokeStyle = accentColor;
        // Top Left
        ctx.beginPath(); ctx.moveTo(42, 75); ctx.lineTo(75, 42); ctx.stroke();
        ctx.beginPath(); ctx.moveTo(42, 90); ctx.lineTo(90, 42); ctx.stroke();
        // Top Right
        ctx.beginPath(); ctx.moveTo(W - 42, 75); ctx.lineTo(W - 75, 42); ctx.stroke();
        ctx.beginPath(); ctx.moveTo(W - 42, 90); ctx.lineTo(W - 90, 42); ctx.stroke();
        // Bottom Left
        ctx.beginPath(); ctx.moveTo(42, H - 75); ctx.lineTo(75, H - 42); ctx.stroke();
        ctx.beginPath(); ctx.moveTo(42, H - 90); ctx.lineTo(90, H - 42); ctx.stroke();
        // Bottom Right
        ctx.beginPath(); ctx.moveTo(W - 42, H - 75); ctx.lineTo(W - 75, H - 42); ctx.stroke();
        ctx.beginPath(); ctx.moveTo(W - 42, H - 90); ctx.lineTo(W - 90, H - 42); ctx.stroke();

        // 4. Header Category Badge
        if (aiAlignment === 'center') {
            // Centered Header Badge
            const badgeW = 280;
            const badgeH = 42;
            const badgeX = (W - badgeW) / 2;
            const badgeY = 68;

            ctx.fillStyle = 'rgba(255, 255, 255, 0.12)';
            ctx.strokeStyle = hexToRgba(accentColor, 0.7);
            ctx.lineWidth = 1.5;
            roundRect(ctx, badgeX, badgeY, badgeW, badgeH, 21, true, true);

            ctx.fillStyle = accentColor;
            ctx.font = 'bold 20px "Hind Siliguri", "Segoe UI", sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText("✦ " + category + " ✦", W / 2, badgeY + 28);
        } else {
            // Left Aligned Header Badge
            ctx.fillStyle = 'rgba(255, 255, 255, 0.12)';
            ctx.strokeStyle = hexToRgba(accentColor, 0.7);
            ctx.lineWidth = 1.5;
            roundRect(ctx, 70, 68, 260, 42, 21, true, true);

            ctx.fillStyle = accentColor;
            ctx.font = 'bold 20px "Hind Siliguri", sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText("✦ " + category, 200, 96);

            ctx.fillStyle = 'rgba(255, 255, 255, 0.75)';
            ctx.font = '600 18px "Hind Siliguri", sans-serif';
            ctx.textAlign = 'right';
            ctx.fillText("আইডিয়া সাহিত্যপত্র", W - 70, 96);
        }

        // 5. Main Title Calculation & Multi-Line Wrapping
        let fontSize = 54;
        if (title.length > 35) fontSize = 46;
        if (title.length > 60) fontSize = 38;
        if (title.length > 90) fontSize = 32;

        ctx.font = `bold ${fontSize}px "Hind Siliguri", "Kalpurush", "SolaimanLipi", sans-serif`;
        ctx.fillStyle = '#ffffff';
        ctx.shadowColor = 'rgba(0, 0, 0, 0.75)';
        ctx.shadowBlur = 16;
        ctx.shadowOffsetX = 2;
        ctx.shadowOffsetY = 4;

        const maxTextWidth = W - 180;
        const lineHeight = fontSize * 1.35;
        const startY = subtitle ? (aiAlignment === 'center' ? 240 : 220) : (aiAlignment === 'center' ? 280 : 260);

        if (aiAlignment === 'center') {
            ctx.textAlign = 'center';
            wrapTextCenter(ctx, title, W / 2, startY, maxTextWidth, lineHeight);
        } else {
            ctx.textAlign = 'left';
            wrapText(ctx, title, 75, startY, maxTextWidth, lineHeight);
        }

        // Reset Shadow
        ctx.shadowColor = 'transparent';

        // 6. Subtitle / Tagline
        if (subtitle) {
            ctx.fillStyle = accentColor;
            ctx.font = 'italic 26px "Hind Siliguri", "Kalpurush", sans-serif';
            if (aiAlignment === 'center') {
                ctx.textAlign = 'center';
                ctx.fillText(`“ ${subtitle} ”`, W / 2, startY + (lineHeight * 1.8) + 15);
            } else {
                ctx.textAlign = 'left';
                ctx.fillText(subtitle, 75, startY + (lineHeight * 1.8) + 15);
            }
        }

        // 7. Center Classic Ornamental Divider
        const dividerY = H - 150;
        ctx.strokeStyle = hexToRgba(accentColor, 0.5);
        ctx.lineWidth = 1.5;

        if (aiAlignment === 'center') {
            ctx.beginPath(); ctx.moveTo(W / 2 - 250, dividerY); ctx.lineTo(W / 2 - 60, dividerY); ctx.stroke();
            ctx.fillStyle = accentColor;
            ctx.font = 'bold 20px "Hind Siliguri", sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText("❖ ─── ✦ ─── ❖", W / 2, dividerY + 6);
            ctx.beginPath(); ctx.moveTo(W / 2 + 60, dividerY); ctx.lineTo(W / 2 + 250, dividerY); ctx.stroke();
        } else {
            ctx.beginPath(); ctx.moveTo(75, dividerY); ctx.lineTo(W - 75, dividerY); ctx.stroke();
        }

        // 8. Footer Credit & Attribution
        const footerY = H - 85;
        ctx.fillStyle = '#ffffff';
        ctx.font = 'bold 24px "Hind Siliguri", "Kalpurush", sans-serif';
        ctx.textAlign = 'left';
        ctx.fillText("✍️ রচনা: " + authorNameGlobal, 75, footerY);

        ctx.fillStyle = accentColor;
        ctx.font = 'bold 20px "Hind Siliguri", sans-serif';
        ctx.textAlign = 'right';
        ctx.fillText("আইডিয়া প্রকাশন | www.ideaabd.com", W - 75, footerY);
    }

    function hexToRgba(hex, alpha) {
        let c;
        if (/^#([A-Fa-f0-9]{3}){1,2}$/.test(hex)) {
            c = hex.substring(1).split('');
            if (c.length === 3) {
                c = [c[0], c[0], c[1], c[1], c[2], c[2]];
            }
            c = '0x' + c.join('');
            return 'rgba(' + [(c >> 16) & 255, (c >> 8) & 255, c & 255].join(',') + ',' + alpha + ')';
        }
        return hex;
    }

    function wrapTextCenter(ctx, text, centerX, startY, maxWidth, lineHeight) {
        const words = text.split(' ');
        let line = '';
        const lines = [];

        for (let n = 0; n < words.length; n++) {
            const testLine = line + words[n] + ' ';
            const metrics = ctx.measureText(testLine);
            if (metrics.width > maxWidth && n > 0) {
                lines.push(line.trim());
                line = words[n] + ' ';
            } else {
                line = testLine;
            }
        }
        lines.push(line.trim());

        let currentY = startY;
        for (let i = 0; i < lines.length; i++) {
            ctx.fillText(lines[i], centerX, currentY);
            currentY += lineHeight;
        }
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
        if (statusBadge) statusBadge.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles me-1"></i>ক্লাসিক ফটোকার্ড প্রস্তুত';
        if (dimText) dimText.textContent = 'রেজোলিউশন: ১২০০×৬৭৫ px (১৬:৯ ক্লাসিক সাহিত্যপত্র)';
        if (previewWrapper) previewWrapper.style.display = 'flex';
        if (errorAlert) errorAlert.classList.add('d-none');

        const modalEl = document.getElementById('aiPhotocardModal');
        if (modalEl) {
            bootstrap.Modal.getInstance(modalEl)?.hide();
        }

        alert("✨ ক্লাসিক ফটোকার্ডটি সফলভাবে আপনার লেখার কভার ছবি হিসেবে যুক্ত হয়েছে!");
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
            case 'poetry':
                if (selectedText) {
                    const stanzas = selectedText.trim().split(/\r\n\r\n|\n\n+/);
                    replacement = stanzas.map(s => {
                        const lines = s.split(/\r\n|\n|\r/).map(l => l.trim()).join('<br>');
                        return `<p class="poetry-verse" style="line-height: 1.45; margin-bottom: 0.85rem;">${lines}</p>`;
                    }).join('\n');
                } else {
                    replacement = `<p class="poetry-verse" style="line-height: 1.45; margin-bottom: 0.85rem;">প্রথম চরণের চরণমালা...<br>দ্বিতীয় চরণের ধ্বনিমাধুর্য...</p>`;
                }
                break;
            case 'tight_lines':
                if (selectedText) {
                    replacement = `<div style="line-height: 1.4; margin-bottom: 0.75rem;">${selectedText.replace(/\n/g, '<br>')}</div>`;
                } else {
                    replacement = `<div style="line-height: 1.4; margin-bottom: 0.75rem;">এখানে কম স্পেসের লাইন লিখুন...</div>`;
                }
                break;
            default:
                replacement = selectedText;
        }

        textarea.setRangeText(replacement, start, end, 'select');
        textarea.focus();
        updateContentStats();
    }

    function updateContentStats() {
        const textarea = document.getElementById('blogContentTextarea');
        const statsEl = document.getElementById('contentStatsText');
        if (!textarea || !statsEl) return;

        const text = textarea.value.trim();
        const chars = text.length;
        const words = text ? text.split(/\s+/).filter(Boolean).length : 0;
        const readingTimeMinutes = Math.max(1, Math.ceil(words / 130));
        
        statsEl.innerHTML = `<i class="fa-solid fa-file-lines me-1"></i>শব্দ: ${words} | বর্ণ: ${chars} | <span class="text-primary fw-semibold"><i class="fa-regular fa-clock me-1"></i>পড়ার সময়: ~${readingTimeMinutes} মিনিট</span>`;
        
        // Trigger background draft save
        saveLocalDraft();
    }

    // Local Storage Draft Auto-Save System
    const DRAFT_KEY_PREFIX = 'idea_author_draft_';
    const isEditMode = {{ $editPost ? 'true' : 'false' }};

    function saveLocalDraft() {
        if (isEditMode) return; // Do not overwrite with server edit mode
        const titleInput = document.querySelector('input[name="title"]');
        const subtitleInput = document.querySelector('input[name="subtitle"]');
        const contentTextarea = document.getElementById('blogContentTextarea');
        const categorySelect = document.querySelector('select[name="category_id"]');

        if (!titleInput && !contentTextarea) return;

        const draftData = {
            title: titleInput ? titleInput.value : '',
            subtitle: subtitleInput ? subtitleInput.value : '',
            content: contentTextarea ? contentTextarea.value : '',
            category_id: categorySelect ? categorySelect.value : '',
            timestamp: Date.now()
        };

        if (draftData.title.trim().length > 3 || draftData.content.trim().length > 10) {
            localStorage.setItem(DRAFT_KEY_PREFIX + 'post', JSON.stringify(draftData));
        }
    }

    function checkLocalDraft() {
        if (isEditMode) return;
        try {
            const raw = localStorage.getItem(DRAFT_KEY_PREFIX + 'post');
            if (!raw) return;
            const draft = JSON.parse(raw);
            const titleInput = document.querySelector('input[name="title"]');
            const contentTextarea = document.getElementById('blogContentTextarea');

            // If current form is empty and draft has content
            if ((!titleInput || !titleInput.value.trim()) && (!contentTextarea || !contentTextarea.value.trim())) {
                if (draft.title || draft.content) {
                    const alertEl = document.getElementById('draftRecoveryAlert');
                    if (alertEl) alertEl.classList.remove('d-none');
                }
            }
        } catch (e) {
            console.warn("Draft check note:", e);
        }
    }

    function restoreLocalDraft() {
        try {
            const raw = localStorage.getItem(DRAFT_KEY_PREFIX + 'post');
            if (!raw) return;
            const draft = JSON.parse(raw);

            const titleInput = document.querySelector('input[name="title"]');
            const subtitleInput = document.querySelector('input[name="subtitle"]');
            const contentTextarea = document.getElementById('blogContentTextarea');
            const categorySelect = document.querySelector('select[name="category_id"]');

            if (titleInput && draft.title) titleInput.value = draft.title;
            if (subtitleInput && draft.subtitle) subtitleInput.value = draft.subtitle;
            if (contentTextarea && draft.content) contentTextarea.value = draft.content;
            if (categorySelect && draft.category_id) categorySelect.value = draft.category_id;

            updateContentStats();
            switchTab('write');

            const alertEl = document.getElementById('draftRecoveryAlert');
            if (alertEl) alertEl.classList.add('d-none');

            alert("✅ আপনার সংরক্ষিত খসড়াটি সফলভাবে ফিরিয়ে আনা হয়েছে!");
        } catch (e) {
            console.error("Draft restore error:", e);
        }
    }

    function discardLocalDraft() {
        localStorage.removeItem(DRAFT_KEY_PREFIX + 'post');
        const alertEl = document.getElementById('draftRecoveryAlert');
        if (alertEl) alertEl.classList.add('d-none');
    }

    // Auto-attach AI photocard if neither upload nor AI card exists before form submission
    function ensurePhotocardBeforeSubmit() {
        const fileInput = document.getElementById('featuredImageInput');
        const aiInput = document.getElementById('aiPhotocardDataInput');
        const titleInput = document.querySelector('input[name="title"]');

        if ((!fileInput || !fileInput.files || !fileInput.files[0]) && (!aiInput || !aiInput.value)) {
            try {
                const customTitleInput = document.getElementById('aiCardCustomTitle');
                if (customTitleInput && titleInput) {
                    customTitleInput.value = titleInput.value.trim() || 'আইডিয়া সাহিত্যপত্র প্রবন্ধ';
                }
                renderAiPhotocard();
                const canvas = document.getElementById('aiPhotocardCanvas');
                if (canvas && aiInput) {
                    aiInput.value = canvas.toDataURL('image/jpeg', 0.90);
                }
            } catch (err) {
                console.warn("Background auto photocard render:", err);
            }
        }

        // Clear local storage draft upon form submission
        if (!isEditMode) {
            localStorage.removeItem(DRAFT_KEY_PREFIX + 'post');
        }
    }

    // ══ Editorial Policy & A4 Modal Controller ═════════════════════════════════
    let policyCurrentFontSize = 15.5;
    function adjustPolicyFontSize(delta) {
        policyCurrentFontSize = Math.max(13, Math.min(22, policyCurrentFontSize + delta * 1.5));
        const bodyEl = document.getElementById('a4PolicyDocBody');
        if (bodyEl) {
            bodyEl.style.fontSize = policyCurrentFontSize + 'px';
        }
    }

    function openEditorialPolicyA4Modal() {
        const formCheckbox = document.getElementById('agreeEditorialPolicyCheckbox');
        const modalCheckbox = document.getElementById('modalAgreePolicyCheckbox');
        if (formCheckbox && modalCheckbox) {
            modalCheckbox.checked = formCheckbox.checked;
        }
        const modalEl = document.getElementById('editorialPolicyA4Modal');
        if (modalEl) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    }

    function applyModalAgreementAndSubmit() {
        const formCheckbox = document.getElementById('agreeEditorialPolicyCheckbox');
        const modalCheckbox = document.getElementById('modalAgreePolicyCheckbox');
        
        if (modalCheckbox) {
            modalCheckbox.checked = true;
        }
        if (formCheckbox) {
            formCheckbox.checked = true;
        }

        const modalEl = document.getElementById('editorialPolicyA4Modal');
        if (modalEl) {
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        }

        ensurePhotocardBeforeSubmit();
        const realBtn = document.getElementById('realSubmitBtn');
        if (realBtn) {
            realBtn.click();
        } else {
            const form = document.getElementById('authorBlogWriteForm');
            if (form) form.submit();
        }
    }

    function handleAuthorPostSubmission(event) {
        if (event) event.preventDefault();
        const formCheckbox = document.getElementById('agreeEditorialPolicyCheckbox');
        
        if (!formCheckbox || !formCheckbox.checked) {
            openEditorialPolicyA4Modal();
            return false;
        }

        ensurePhotocardBeforeSubmit();
        const realBtn = document.getElementById('realSubmitBtn');
        if (realBtn) {
            realBtn.click();
        } else {
            const form = document.getElementById('authorBlogWriteForm');
            if (form) form.submit();
        }
    }

    // Initialize stats and draft recovery on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateContentStats();
        checkLocalDraft();

        // Attach live input listeners for autosaving
        const titleInput = document.querySelector('input[name="title"]');
        const subtitleInput = document.querySelector('input[name="subtitle"]');
        const categorySelect = document.querySelector('select[name="category_id"]');

        if (titleInput) titleInput.addEventListener('input', saveLocalDraft);
        if (subtitleInput) subtitleInput.addEventListener('input', saveLocalDraft);
        if (categorySelect) categorySelect.addEventListener('change', saveLocalDraft);
    });

    // Author Spell Checker Bridge
    function runAuthorSpellCheck() {
        const textarea = document.getElementById('blogContentTextarea');
        const resultsBox = document.getElementById('spell-results-author');
        if (!textarea || !resultsBox) return;

        const text = textarea.value || '';
        if (!text.trim()) {
            resultsBox.classList.remove('d-none');
            resultsBox.innerHTML = '<div class="alert alert-info py-2 px-3 rounded-3 small">বক্সে কোনো লেখা নেই। লিখুন বা পেস্ট করে বানান পরীক্ষা করুন।</div>';
            return;
        }

        const detected = [];
        for (const [wrong, correct] of Object.entries(BENGALI_SPELL_DICT)) {
            const regex = new RegExp('(^|[\\s,।!?;:"\'()«»–—\\[\\]])(' + escapeRegExp(wrong) + ')(?=[\\s,।!?;:"\'()«»–—\\[\\]]|$)', 'g');
            if (regex.test(text)) {
                detected.push({ wrong, correct });
            }
        }
        for (const [wrong, correct] of Object.entries(ENGLISH_SPELL_DICT)) {
            const regex = new RegExp('\\b' + escapeRegExp(wrong) + '\\b', 'gi');
            if (regex.test(text)) {
                detected.push({ wrong, correct });
            }
        }

        resultsBox.classList.remove('d-none');
        if (detected.length === 0) {
            resultsBox.innerHTML = `
                <div class="alert alert-success py-2 px-3 rounded-3 small text-success fw-semibold d-flex align-items-center gap-2">
                    <i class="fas fa-circle-check fs-5"></i>
                    <span>চমৎকার! কোনো অপ্রমিত বা ভুল বানান শনাক্ত হয়নি। লেখা পোস্ট করার জন্য প্রস্তুত।</span>
                </div>`;
        } else {
            let chips = '';
            detected.forEach(item => {
                chips += `
                    <button type="button" class="btn btn-sm btn-light border d-inline-flex align-items-center gap-1 py-1 px-2 rounded-pill" 
                            onclick="fixAuthorMistake('${escapeHtmlAttr(item.wrong)}', '${escapeHtmlAttr(item.correct)}')">
                        <span class="text-danger text-decoration-line-through">${item.wrong}</span>
                        <i class="fas fa-arrow-right text-success small"></i>
                        <span class="text-success fw-bold">${item.correct}</span>
                    </button>`;
            });

            resultsBox.innerHTML = `
                <div class="alert alert-warning p-3 rounded-3 border-0 shadow-xs mb-0" style="background: #fffbeb; border-left: 4px solid #f59e0b !important;">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fw-bold text-dark small"><i class="fas fa-spell-check text-warning me-1"></i> পোস্ট করার আগে বানানগুলো চেক করে নিন (${detected.length}টি অপ্রমিত রূপ পাওয়া গেছে):</span>
                        <button type="button" class="btn btn-xs btn-success rounded-pill px-2.5 py-0.5 fw-bold" onclick="fixAllAuthorMistakes()">সবগুলো শুদ্ধ করুন</button>
                    </div>
                    <div class="d-flex flex-wrap gap-1.5">${chips}</div>
                </div>`;
        }
    }

    function fixAuthorMistake(wrongWord, rightWord) {
        const textarea = document.getElementById('blogContentTextarea');
        if (!textarea) return;
        const regex = new RegExp(escapeRegExp(wrongWord), 'g');
        textarea.value = textarea.value.replace(regex, rightWord);
        updateContentStats();
        runAuthorSpellCheck();
    }

    function fixAllAuthorMistakes() {
        const textarea = document.getElementById('blogContentTextarea');
        if (!textarea) return;
        let text = textarea.value;
        for (const [wrong, correct] of Object.entries(BENGALI_SPELL_DICT)) {
            const regex = new RegExp(escapeRegExp(wrong), 'g');
            text = text.replace(regex, correct);
        }
        for (const [wrong, correct] of Object.entries(ENGLISH_SPELL_DICT)) {
            const regex = new RegExp('\\b' + escapeRegExp(wrong) + '\\b', 'gi');
            text = text.replace(regex, correct);
        }
        textarea.value = text;
        updateContentStats();
        runAuthorSpellCheck();
    }
</script>
<script src="{{ asset('js/spellchecker.js') }}"></script>
@endsection
