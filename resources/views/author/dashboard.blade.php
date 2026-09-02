@extends('author.layout')

@section('title', 'লেখক ড্যাশবোর্ড — আইডিয়া প্রকাশন')
@section('heading', 'স্বাগতম, ' . auth()->user()->name)

@section('content')
<div class="d-flex flex-column gap-3.5">

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- 0. AUTHOR PROFILE & DYNAMIC PHOTO STUDIO BANNER                            --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    @php
        $dashAvatar = auth()->user()->avatar ?: (auth()->user()->reg_data['avatar'] ?? ($author?->avatar ?? null));
        $dashAvatarUrl = $dashAvatar ? (str_starts_with($dashAvatar, 'http') ? $dashAvatar : asset('storage/' . ltrim($dashAvatar, '/'))) : null;
        $authorPenName = auth()->user()->reg_data['pen_name'] ?? ($author?->name != auth()->user()->name ? $author?->name : null);
        $authorBioText = auth()->user()->reg_data['bio'] ?? ($author?->bio ?? '');
    @endphp
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden text-white position-relative" 
         style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);">
        
        <div class="p-3.5 p-md-4 position-relative z-1">
            <div class="d-flex flex-column flex-md-row align-items-center align-items-md-start gap-3.5">
                
                {{-- Interactive Avatar with Quick Edit Badge --}}
                <div class="position-relative flex-shrink-0 cursor-pointer" onclick="openPhotoStudioModal()" title="ছবি পরিবর্তন করতে ক্লিক করুন">
                    <div class="rounded-circle overflow-hidden shadow-md border border-3 border-white position-relative bg-white" 
                         style="width: 96px; height: 96px; min-width: 96px; min-height: 96px; aspect-ratio: 1 / 1;" id="dashAvatarMainBox">
                        @if($dashAvatarUrl)
                            <img src="{{ $dashAvatarUrl }}" alt="{{ auth()->user()->name }}" class="w-100 h-100 object-fit-cover current-author-avatar-img">
                        @else
                            <div class="w-100 h-100 d-flex align-items-center justify-content-center text-primary fs-1 fw-bold bg-light">
                                {{ mb_substr(auth()->user()->name, 0, 1) }}
                            </div>
                        @endif
                    </div>
                    
                    {{-- Camera Change Badge --}}
                    <button type="button" class="btn btn-warning btn-sm rounded-circle position-absolute bottom-0 end-0 p-0 d-flex align-items-center justify-content-center shadow-sm border border-2 border-white" 
                            style="width: 30px; height: 30px; transform: translate(2px, 2px);" title="ছবি পরিবর্তন করুন">
                        <i class="fas fa-camera text-dark" style="font-size: 12px;"></i>
                    </button>
                </div>

                {{-- Author Details & Quick Profile Actions --}}
                <div class="text-center text-md-start flex-grow-1 min-w-0">
                    <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-start gap-2 mb-1">
                        <h4 class="fw-bold mb-0 text-white" id="dashAuthorNameDisplay">{{ auth()->user()->name }}</h4>
                        @if($authorPenName)
                            <span class="badge bg-light text-dark rounded-pill px-2.5 py-1 small fw-semibold">
                                <i class="fas fa-feather-pointed me-1 text-primary"></i>কলমনাম: {{ $authorPenName }}
                            </span>
                        @endif
                        <span class="badge bg-success bg-opacity-75 text-white rounded-pill px-2.5 py-1 small">
                            <i class="fas fa-circle-check me-1"></i>লেখক অ্যাকাউন্ট
                        </span>
                    </div>

                    {{-- Bio preview --}}
                    <p class="text-white-50 small mb-2.5 line-clamp-2" style="font-size: 0.85rem; line-height: 1.5;" id="dashAuthorBioDisplay">
                        {{ $authorBioText ?: 'আপনার লেখক পরিচিতি ও সাহিত্য জীবনবৃত্তান্ত যুক্ত করুন যাতে পাঠকরা আপনার সম্পর্কে জানতে পারে।' }}
                    </p>

                    {{-- Action buttons --}}
                    <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-start gap-2">
                        <button type="button" class="btn btn-warning btn-sm rounded-pill px-3 py-1.5 fw-bold text-dark shadow-xs" onclick="openPhotoStudioModal()">
                            <i class="fas fa-camera me-1"></i> ছবি পরিবর্তন ও ক্রপ স্টুডিও
                        </button>
                        <button type="button" class="btn btn-outline-light btn-sm rounded-pill px-3 py-1.5 fw-semibold" onclick="openBioEditModal()">
                            <i class="fas fa-user-pen me-1"></i> পরিচিতি ও বায়ো এডিট
                        </button>
                        @if($author && $author->slug)
                            <a href="{{ route('authors.show', $author->slug) }}" target="_blank" class="btn btn-outline-info btn-sm rounded-pill px-3 py-1.5 fw-semibold text-white">
                                <i class="fas fa-arrow-up-right-from-square me-1"></i> পাবলিক প্রোফাইল দেখুন
                            </a>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- 1. TOP QUICK ACTION BUTTONS BAR (৩টি বড় অ্যাকশন বাটন)                         --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
        <div class="row g-2">
            {{-- Action 1: New Ideapatra Post --}}
            <div class="col-12 col-md-4">
                <a href="{{ route('author.posts.create') }}" class="btn btn-warning w-100 py-2.5 rounded-3 fw-bold text-dark d-flex align-items-center justify-content-center gap-2 shadow-xs hover-lift text-decoration-none">
                    <i class="fas fa-feather-pointed fs-5"></i>
                    <span>+ নতুন আইডিয়াপত্র পোস্ট</span>
                </a>
            </div>

            {{-- Action 2: Upload E-Book --}}
            <div class="col-12 col-md-4">
                <a href="{{ route('author.ebooks.create') }}" class="btn btn-primary w-100 py-2.5 rounded-3 fw-bold text-white d-flex align-items-center justify-content-center gap-2 shadow-xs hover-lift text-decoration-none">
                    <i class="fas fa-cloud-arrow-up fs-5"></i>
                    <span>+ ই-বুক আপলোড করুন</span>
                </a>
            </div>

            {{-- Action 3: Submit Cover / Manuscript --}}
            <div class="col-12 col-md-4">
                <a href="{{ route('author.ebooks.create') }}" class="btn btn-success w-100 py-2.5 rounded-3 fw-bold text-white d-flex align-items-center justify-content-center gap-2 shadow-xs hover-lift text-decoration-none">
                    <i class="fas fa-file-arrow-up fs-5"></i>
                    <span>+ প্রচ্ছদ / পাণ্ডুলিপি সাবমিট</span>
                </a>
            </div>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- 2. DYNAMIC STATUS CARDS (DASHBOARD OVERVIEW)                               --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <div class="row g-3">
        {{-- Card 1: Ideapatra Posts Counter --}}
        <div class="col-6 col-lg-3">
            <div class="author-card p-3 h-100 border-start border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">আইডিয়াপত্র পোস্ট</span>
                    <span class="p-2 bg-warning-subtle text-warning-emphasis rounded-3"><i class="fas fa-pen-nib"></i></span>
                </div>
                <h3 class="fw-bold mb-1 text-dark">@bn($totalPosts)</h3>
                <div class="small text-muted d-flex align-items-center gap-1">
                    <span class="badge bg-success-subtle text-success">@bn($publishedPosts) লাইভ</span>
                    @if($pendingPosts > 0)
                        <span class="badge bg-warning-subtle text-warning-emphasis">@bn($pendingPosts) রিভিউতে</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Card 2: E-Books & Sales Counter --}}
        <div class="col-6 col-lg-3">
            <div class="author-card p-3 h-100 border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">ই-বুক ও সেলস</span>
                    <span class="p-2 bg-primary-subtle text-primary rounded-3"><i class="fas fa-book-bookmark"></i></span>
                </div>
                <h3 class="fw-bold mb-1 text-dark">@bn($totalEbooks)</h3>
                <div class="small text-muted d-flex align-items-center gap-1">
                    <span class="badge bg-primary-subtle text-primary">@bn($totalCopiesSold) বিক্রি</span>
                    <span class="badge bg-success-subtle text-success">@bn($publishedEbooks) লাইভ</span>
                </div>
            </div>
        </div>

        {{-- Card 3: Total Royalty Share (50%) --}}
        <div class="col-6 col-lg-3">
            <div class="author-card p-3 h-100 border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">ই-বুক রয়্যালটি (৫০%)</span>
                    <span class="p-2 bg-success-subtle text-success rounded-3"><i class="fas fa-sack-dollar"></i></span>
                </div>
                <h3 class="fw-bold mb-1 text-success font-monospace">৳{{ number_format($totalRoyaltyEarned, 2) }}</h3>
                <div class="small text-muted">সর্বমোট ইবুক সেলস শেয়ার</div>
            </div>
        </div>

        {{-- Card 4: Reader Honorarium / পাঠক সম্মানি --}}
        <div class="col-6 col-lg-3">
            <div class="author-card p-3 h-100 border-start border-4 border-danger">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">পাঠক সম্মানি অর্জন</span>
                    <span class="p-2 bg-danger bg-opacity-10 text-danger rounded-3"><i class="fas fa-heart"></i></span>
                </div>
                <h3 class="fw-bold mb-1 text-danger font-monospace">৳{{ number_format($totalHonorariumEarned, 2) }}</h3>
                <div class="small text-muted d-flex align-items-center justify-content-between">
                    <span>@bn($totalHonorariumCount) জন পাঠক</span>
                    <a href="{{ route('author.honorariums') }}" class="text-danger text-decoration-none fw-bold" style="font-size: 11px;">বিস্তারিত →</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Card 5: Wallet Overview Highlight Banner --}}
    <div class="card border-0 shadow-sm rounded-4 p-3 text-white" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 60%, #4338ca 100%);">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center fw-bold fs-4 flex-shrink-0" style="width: 48px; height: 48px;">
                    <i class="fas fa-wallet"></i>
                </div>
                <div>
                    <span class="text-white-50 small text-uppercase fw-semibold" style="letter-spacing: 0.5px;">উত্তোলনযোগ্য মোট ওয়ালেট ব্যালেন্স (রয়্যালটি + সম্মানি)</span>
                    <h2 class="fw-bold mb-0 text-warning font-monospace">৳{{ number_format($availableBalance, 2) }}</h2>
                </div>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="{{ route('author.payouts.index') }}" class="btn btn-warning btn-sm rounded-pill px-4 py-2 fw-bold text-dark shadow-sm">
                    <i class="fas fa-hand-holding-dollar me-1"></i> রয়্যালটি ও সম্মানি উত্তোলন করুন
                </a>
                <a href="{{ route('author.honorariums') }}" class="btn btn-outline-light btn-sm rounded-pill px-3 py-2 fw-semibold">
                    <i class="fas fa-receipt me-1"></i> সম্মানি লেজার
                </a>
            </div>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- 3. QUICK MOBILE WRITING / DRAFTING WIDGET (সহজ রাইটিং উইজেট)              --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <div class="author-card p-3 p-md-4 bg-white">
        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
            <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <span class="badge bg-warning bg-opacity-20 text-warning-emphasis rounded-circle p-2">
                    <i class="fas fa-pen-to-square"></i>
                </span>
                <span>মোবাইল কুইক রাইটার (Quick Ideapatra Draft)</span>
            </h6>
            <a href="{{ route('author.posts.create') }}" class="btn btn-sm btn-outline-warning text-dark fw-semibold rounded-pill px-3">
                পূর্ণাঙ্গ এডিটরে যান →
            </a>
        </div>

        <form action="{{ route('author.posts.store') }}" method="POST">
            @csrf
            <input type="hidden" name="action_type" value="draft">

            <div class="row g-2">
                <div class="col-12 col-md-8">
                    <input type="text" name="title" class="form-control form-control-sm rounded-3 fw-semibold" placeholder="আপনার লেখার শিরোনাম বা চিন্তা লিখুন..." required>
                </div>
                <div class="col-12 col-md-4">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-warning text-dark fw-bold rounded-pill px-3 w-100">
                            <i class="fas fa-save me-1"></i> ড্রাফট সংরক্ষণ
                        </button>
                    </div>
                </div>
                <div class="col-12 mt-2">
                    <textarea name="content" rows="3" class="form-control rounded-3 small" placeholder="লেখা বা কবিতার অংশ বিশেষ এখানে টাইপ করে রাখুন, পরবর্তীতে পূর্ণাঙ্গ রূপ দিতে পারবেন..." required></textarea>
                </div>
            </div>
        </form>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- 4. RECENT SALES / ROYALTIES & E-BOOKS GRID                                 --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <div class="row g-3">
        {{-- Left: Recent Sales & Royalty Earnings --}}
        <div class="col-12 col-lg-8">
            <div class="author-card p-3 p-md-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-receipt text-primary me-1.5"></i> সাম্প্রতিক বিক্রয় ও রয়্যালটি (Recent Sales)
                    </h6>
                    <a href="{{ route('author.royalties') }}" class="small text-primary text-decoration-none fw-semibold">
                        সবগুলো দেখুন <i class="fas fa-arrow-right small"></i>
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small fw-bold text-secondary">
                            <tr>
                                <th>তারিখ</th>
                                <th>ই-বুক শিরোনাম</th>
                                <th>মূল্য</th>
                                <th>রয়্যালটি (৫০%)</th>
                                <th>স্ট্যাটাস</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse($recentRoyalties as $royalty)
                                <tr>
                                    <td class="text-muted">{{ $royalty->created_at->format('d M, Y') }}</td>
                                    <td>
                                        <strong class="text-dark">{{ $royalty->ebook?->title ?? 'ই-বুক' }}</strong>
                                        <small class="d-block text-muted">অর্ডার: #{{ $royalty->order?->order_number ?? $royalty->order_id }}</small>
                                    </td>
                                    <td class="font-monospace">৳{{ number_format($royalty->sale_price, 2) }}</td>
                                    <td class="fw-bold text-success font-monospace">+৳{{ number_format($royalty->royalty_amount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5">অর্জিত</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="fas fa-receipt fs-3 opacity-25 d-block mb-1"></i>
                                        এখনও কোনো বিক্রয় ট্রানজ্যাকশন নেই। বই বিক্রি হলে ৫০% রয়্যালটি এখানে দেখতে পাবেন।
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Right: My Ebooks Quick Status --}}
        <div class="col-12 col-lg-4">
            <div class="author-card p-3 p-md-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-book text-info me-1.5"></i> আমার ই-বুকসমূহ
                    </h6>
                    <a href="{{ route('author.ebooks.index') }}" class="small text-primary text-decoration-none fw-semibold">
                        ম্যানেজ করুন
                    </a>
                </div>

                <div class="d-flex flex-column gap-2.5">
                    @forelse($recentEbooks as $eb)
                        <div class="d-flex align-items-center gap-2 p-2 rounded-3 border bg-light bg-opacity-50">
                            <img src="{{ $eb->cover_url ?? 'https://placehold.co/100x140?text=Cover' }}" 
                                 alt="Cover" class="rounded object-fit-cover flex-shrink-0" style="width: 38px; height: 52px;">
                            <div class="overflow-hidden flex-grow-1">
                                <h6 class="small fw-bold mb-0 text-truncate text-dark">{{ $eb->title }}</h6>
                                <div class="font-monospace small text-primary fw-semibold">৳{{ number_format($eb->price, 2) }}</div>
                                <div class="mt-0.5">
                                    @if($eb->mod_status === 'approved')
                                        <span class="badge bg-success-subtle text-success" style="font-size: 10px;">লাইভ</span>
                                    @elseif($eb->mod_status === 'rejected')
                                        <span class="badge bg-danger-subtle text-danger" style="font-size: 10px;">সংশোধন প্রয়োজন</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning-emphasis" style="font-size: 10px;">পেন্ডিং রিভিউ</span>
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('author.ebooks.edit', $eb->id) }}" class="btn btn-xs btn-outline-secondary rounded-pill p-1">
                                <i class="fas fa-pen"></i>
                            </a>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <p class="small mb-2">আপনি এখনও কোনো ই-বুক আপলোড করেননি।</p>
                            <a href="{{ route('author.ebooks.create') }}" class="btn btn-sm btn-primary rounded-pill px-3">
                                <i class="fas fa-plus me-1"></i> প্রথম ই-বুক আপলোড
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- 5. RECENT READER HONORARIUMS & APPRECIATION NOTES (পাঠক সম্মানি ও শুভেচ্ছা) --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    @if(isset($recentHonorariums) && $recentHonorariums->isNotEmpty())
        <div class="author-card p-3 p-md-4 bg-white border-start border-4 border-danger">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-circle p-2">
                        <i class="fas fa-heart"></i>
                    </span>
                    <span>সাম্প্রতিক পাঠক সম্মানি ও শুভেচ্ছা বার্তা</span>
                </h6>
                <a href="{{ route('author.honorariums') }}" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-semibold">
                    সকল সম্মানি (@bn($totalHonorariumCount)) →
                </a>
            </div>

            <div class="row g-3">
                @foreach($recentHonorariums as $rh)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="p-3 rounded-3 border h-100 position-relative" style="background: #fafaf9;">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="fw-bold text-dark text-truncate" style="max-width: 65%;">
                                    <i class="fas fa-user-circle text-muted me-1"></i>{{ $rh->display_name }}
                                </span>
                                <span class="badge bg-danger text-white font-monospace rounded-pill px-2.5 py-1">
                                    +৳{{ number_format($rh->author_amount, 2) }}
                                </span>
                            </div>
                            @if($rh->post)
                                <a href="{{ route('blog.show', $rh->post->slug ?: $rh->post->id) }}" target="_blank" class="small text-muted text-decoration-none d-block text-truncate mb-2 hover-primary" title="{{ $rh->post->title }}">
                                    <i class="fas fa-newspaper me-1 text-primary"></i>{{ $rh->post->title }}
                                </a>
                            @endif
                            @if($rh->message)
                                <div class="small text-secondary bg-white p-2 rounded-2 border fst-italic mb-2" style="font-size: 11.5px; line-height: 1.4;">
                                    "{{ \Illuminate\Support\Str::limit($rh->message, 90) }}"
                                </div>
                            @endif
                            <div class="small text-muted d-flex align-items-center justify-content-between mt-auto" style="font-size: 11px;">
                                <span class="badge {{ $rh->method_badge_class }} rounded-pill px-2 py-0.5" style="font-size: 9.5px;">{{ strtoupper($rh->payment_method) }}</span>
                                <span>{{ $rh->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- 6. IDEAPATRA (ARTICLES & BLOG POSTS LIST)                                 --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <div class="author-card p-3 p-md-4 bg-white">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 pb-2 border-bottom">
            <div>
                <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    <span class="badge bg-warning bg-opacity-20 text-warning-emphasis rounded-circle p-2">
                        <i class="fas fa-feather-pointed"></i>
                    </span>
                    <span>আইডিয়াপত্র (আমার ব্লগ ও সাহিত্য রচনা)</span>
                </h6>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('author.posts.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    সকল রচনা (@bn($totalPosts))
                </a>
                <a href="{{ route('author.posts.create') }}" class="btn btn-sm btn-warning text-dark fw-bold rounded-pill px-3 shadow-xs">
                    <i class="fas fa-plus me-1"></i> নতুন লিখুন
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small fw-bold text-secondary">
                    <tr>
                        <th style="width: 50px;">ছবি</th>
                        <th>লেখার শিরোনাম</th>
                        <th>তারিখ</th>
                        <th>ভিউ সংখ্যা</th>
                        <th>স্ট্যাটাস</th>
                        <th class="text-end">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse($recentPosts as $post)
                        <tr>
                            <td>
                                @php
                                    $imgUrl = $post->cover_url ?: ($post->featured_image ? (str_starts_with($post->featured_image, 'http') ? $post->featured_image : asset('storage/' . ltrim($post->featured_image, '/'))) : 'https://placehold.co/80x60?text=Post');
                                @endphp
                                <img src="{{ $imgUrl }}" alt="Post" class="rounded object-fit-cover shadow-xs" style="width: 40px; height: 30px;">
                            </td>
                            <td>
                                <strong class="text-dark">{{ $post->title }}</strong>
                                @if($post->excerpt)
                                    <small class="text-muted d-block text-truncate" style="max-width: 280px;">{{ $post->excerpt }}</small>
                                @endif
                            </td>
                            <td class="text-muted">{{ $post->created_at->format('d M, Y') }}</td>
                            <td>
                                <span class="text-muted font-monospace"><i class="fas fa-eye me-1"></i>@bn($post->view_count ?? 0)</span>
                            </td>
                            <td>
                                @if($post->status === 'published')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5">লাইভ</span>
                                @elseif($post->status === 'pending')
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5">রিভিউতে</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-0.5">ড্রাফট</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('blog.show', $post->slug ?: $post->id) }}" target="_blank" class="btn btn-outline-primary" title="পড়ুন">
                                        <i class="fas fa-eye me-1"></i> পড়ুন
                                    </a>
                                    @if($post->status !== 'published' && $post->mod_status !== 'approved' && $post->status !== 'pending')
                                        <a href="{{ route('author.posts.edit', $post->id) }}" class="btn btn-outline-secondary" title="এডিট">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="fas fa-newspaper fs-3 opacity-25 d-block mb-1"></i>
                                আপনি এখনও কোনো রচনা প্রকাশ করেননি। উপরের বাটনে ক্লিক করে প্রথম রচনা লিখুন।
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- ═════════════════════════════════════════════════════════════════════════ --}}
{{-- 7. DYNAMIC AUTHOR PHOTO STUDIO MODAL (MOBILE & DESKTOP TOUCH-FRIENDLY)     --}}
{{-- ═════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="authorPhotoStudioModal" tabindex="-1" aria-labelledby="authorPhotoStudioModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 pb-0 pt-3.5 px-4 bg-light">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="authorPhotoStudioModalLabel">
                    <i class="fas fa-camera text-primary"></i>
                    <span>লেখক ছবি স্টুডিও (Dynamic Photo Cropper)</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4">
                <p class="text-muted small mb-3" style="font-size: 12.5px;">
                    মোবাইল বা কম্পিউটার থেকে ছবি নির্বাচন করুন। ছবির ওপর ড্র্যাগ করে ও জুম স্লাইডার দিয়ে সঠিকভাবে ১:১ গোলাকার ফ্রেমে অ্যাডজাস্ট করে সেভ করুন।
                </p>

                {{-- Interactive Crop Canvas Container --}}
                <div class="text-center mb-3">
                    <div class="position-relative mx-auto rounded-4 overflow-hidden border border-2 border-primary shadow-xs bg-white" 
                         style="width: 220px; height: 220px; cursor: grab; touch-action: none;" id="modalCanvasWrapper">
                        <canvas id="modalCropCanvas" width="220" height="220" style="display:block; width:100%; height:100%;"></canvas>
                        
                        {{-- Circular Overlay Mask Guide --}}
                        <div class="position-absolute top-0 start-0 w-100 h-100 pointer-events-none d-flex align-items-center justify-content-center" 
                             style="box-shadow: 0 0 0 9999px rgba(0,0,0,0.45); border-radius: 50%; pointer-events: none;">
                            <div class="border border-white border-opacity-75 rounded-circle w-100 h-100" style="border-style: dashed !important;"></div>
                        </div>

                        {{-- Initial placeholder when no image uploaded --}}
                        <div id="modalCanvasPlaceholder" class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-light text-muted p-2 pointer-events-none">
                            <i class="fas fa-cloud-arrow-up text-primary fs-2 mb-1"></i>
                            <span style="font-size: 11.5px;" class="fw-semibold">গ্যালারি / ক্যামেরা থেকে ছবি বাছুন</span>
                        </div>
                    </div>
                </div>

                {{-- File Picker --}}
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark mb-1">
                        <i class="fas fa-image text-primary me-1"></i> নতুন ছবি নির্বাচন করুন:
                    </label>
                    <input type="file" id="modalAuthorAvatarInput" 
                           accept="image/jpeg,image/png,image/jpg,image/webp" 
                           class="form-control form-control-sm rounded-3"
                           onchange="loadModalAuthorImage(this)">
                </div>

                {{-- Interactive Controls: Zoom Slider, Rotate, Reset --}}
                <div id="modalCropControls" class="p-3 bg-light rounded-3 border mb-3" style="display: none;">
                    <div class="d-flex align-items-center justify-content-between mb-1.5" style="font-size: 11.5px;">
                        <span class="text-muted fw-semibold"><i class="fas fa-magnifying-glass-plus text-primary me-1"></i>জুম অ্যাডজাস্ট:</span>
                        <span class="badge bg-white text-dark border font-monospace" id="modalZoomValBadge">100%</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <button type="button" class="btn btn-sm btn-white border rounded-circle p-1" style="width:26px;height:26px;" onclick="adjustModalZoom(-0.1)" title="Zoom Out"><i class="fa-solid fa-minus" style="font-size:10px;"></i></button>
                        <input type="range" class="form-range flex-grow-1" id="modalZoomSlider" min="0.2" max="3.5" step="0.05" value="1" oninput="onModalZoomChange(this.value)">
                        <button type="button" class="btn btn-sm btn-white border rounded-circle p-1" style="width:26px;height:26px;" onclick="adjustModalZoom(0.1)" title="Zoom In"><i class="fa-solid fa-plus" style="font-size:10px;"></i></button>
                    </div>

                    <div class="d-flex align-items-center gap-2 flex-wrap justify-content-center">
                        <button type="button" class="btn btn-white btn-sm border rounded-pill px-3 py-1 text-dark small" onclick="rotateModalImage(90)">
                            <i class="fas fa-rotate-right me-1 text-primary"></i> ঘোরান (Rotate)
                        </button>
                        <button type="button" class="btn btn-white btn-sm border rounded-pill px-3 py-1 text-dark small" onclick="resetModalCrop()">
                            <i class="fas fa-arrows-to-circle me-1 text-secondary"></i> রিসেট
                        </button>
                    </div>
                </div>

                {{-- Alert Box --}}
                <div id="modalPhotoUploadAlert" class="alert d-none small py-2 px-3 rounded-3 mb-0"></div>
            </div>

            <div class="modal-footer border-0 pt-0 px-4 pb-3.5 d-flex justify-content-between">
                <button type="button" class="btn btn-light rounded-pill px-3.5" data-bs-dismiss="modal">বন্ধ করুন</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" id="modalSavePhotoBtn" onclick="submitDynamicAuthorPhoto()" disabled>
                    <span class="spinner-border spinner-border-sm d-none me-1" id="modalPhotoSpinner" role="status"></span>
                    <i class="fas fa-save me-1" id="modalPhotoSaveIcon"></i> ছবি সেভ করুন
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ═════════════════════════════════════════════════════════════════════════ --}}
{{-- 8. AUTHOR LITERARY PROFILE & BIO EDIT MODAL                                --}}
{{-- ═════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="authorBioEditModal" tabindex="-1" aria-labelledby="authorBioEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 pb-0 pt-3.5 px-4 bg-light">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="authorBioEditModalLabel">
                    <i class="fas fa-user-pen text-primary"></i>
                    <span>লেখক পরিচিতি ও বায়ো এডিট</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="authorBioEditForm" onsubmit="submitAuthorBio(event)">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">লেখকের নাম <span class="text-danger">*</span></label>
                        <input type="text" id="editAuthorNameInput" class="form-control rounded-3" value="{{ auth()->user()->name }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">ছদ্মনাম / কলমনাম <span class="text-muted small">(যদি থাকে)</span></label>
                        <input type="text" id="editAuthorPenNameInput" class="form-control rounded-3" value="{{ $authorPenName }}" placeholder="ঐচ্ছিক">
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-bold text-dark mb-0">লেখক পরিচিতি ও সাহিত্য জীবনবৃত্তান্ত (Bio)</label>
                            <span class="text-muted small" id="editBioCounter" style="font-size: 11px;">0 অক্ষর</span>
                        </div>
                        <textarea id="editAuthorBioInput" rows="4" class="form-control rounded-3 small" placeholder="আপনার সাহিত্যকর্ম, অর্জন ও সংক্ষিপ্ত পরিচিতি লিখুন..." oninput="document.getElementById('editBioCounter').textContent = this.value.length + ' অক্ষর'">{{ $authorBioText }}</textarea>
                    </div>

                    <div id="editBioAlert" class="alert d-none small py-2 px-3 rounded-3 mb-0"></div>
                </div>

                <div class="modal-footer border-0 pt-0 px-4 pb-3.5 d-flex justify-content-between">
                    <button type="button" class="btn btn-light rounded-pill px-3.5" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" id="saveBioSubmitBtn">
                        <span class="spinner-border spinner-border-sm d-none me-1" id="saveBioSpinner"></span>
                        <i class="fas fa-save me-1" id="saveBioIcon"></i> আপডেট করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.hover-lift {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-lift:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.12) !important;
}
</style>

<script>
/* =========================================================================
   DYNAMIC AUTHOR PHOTO STUDIO CROPPER & TOUCH ENGINE
   ========================================================================= */
let modalCanvas = document.getElementById('modalCropCanvas');
let modalCtx = modalCanvas ? modalCanvas.getContext('2d') : null;
let modalCurrentImg = null;
let modalImgX = 110;
let modalImgY = 110;
let modalScale = 1;
let modalRotation = 0;
let modalIsDragging = false;
let modalStartX, modalStartY;
let modalCroppedDataUrl = null;

function openPhotoStudioModal() {
    const modalEl = document.getElementById('authorPhotoStudioModal');
    if (modalEl) {
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }
}

function openBioEditModal() {
    const modalEl = document.getElementById('authorBioEditModal');
    if (modalEl) {
        const bio = document.getElementById('editAuthorBioInput');
        if (bio) {
            document.getElementById('editBioCounter').textContent = bio.value.length + ' অক্ষর';
        }
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }
}

function loadModalAuthorImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            modalCurrentImg = new Image();
            modalCurrentImg.onload = function() {
                document.getElementById('modalCanvasPlaceholder').style.display = 'none';
                document.getElementById('modalCropControls').style.display = 'block';
                document.getElementById('modalSavePhotoBtn').disabled = false;
                
                const canvasW = modalCanvas.width;
                const canvasH = modalCanvas.height;
                const scaleW = canvasW / modalCurrentImg.width;
                const scaleH = canvasH / modalCurrentImg.height;
                modalScale = Math.max(scaleW, scaleH);
                
                document.getElementById('modalZoomSlider').value = modalScale;
                document.getElementById('modalZoomValBadge').textContent = `${Math.round(modalScale * 100)}%`;
                
                modalImgX = canvasW / 2;
                modalImgY = canvasH / 2;
                modalRotation = 0;
                
                renderModalCanvas();
            };
            modalCurrentImg.src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function renderModalCanvas() {
    if (!modalCurrentImg || !modalCtx) return;
    
    modalCtx.clearRect(0, 0, modalCanvas.width, modalCanvas.height);
    modalCtx.save();
    
    modalCtx.translate(modalImgX, modalImgY);
    modalCtx.rotate((modalRotation * Math.PI) / 180);
    modalCtx.scale(modalScale, modalScale);
    
    modalCtx.drawImage(modalCurrentImg, -modalCurrentImg.width / 2, -modalCurrentImg.height / 2);
    modalCtx.restore();
    
    exportModalCroppedAvatar();
}

function exportModalCroppedAvatar() {
    if (!modalCurrentImg) return;
    const highRes = document.createElement('canvas');
    highRes.width = 400;
    highRes.height = 400;
    const hrCtx = highRes.getContext('2d');
    
    const ratio = 400 / modalCanvas.width;
    hrCtx.save();
    hrCtx.translate(modalImgX * ratio, modalImgY * ratio);
    hrCtx.rotate((modalRotation * Math.PI) / 180);
    hrCtx.scale(modalScale * ratio, modalScale * ratio);
    hrCtx.drawImage(modalCurrentImg, -modalCurrentImg.width / 2, -modalCurrentImg.height / 2);
    hrCtx.restore();
    
    modalCroppedDataUrl = highRes.toDataURL('image/jpeg', 0.92);
}

function onModalZoomChange(val) {
    modalScale = parseFloat(val);
    document.getElementById('modalZoomValBadge').textContent = `${Math.round(modalScale * 100)}%`;
    renderModalCanvas();
}

function adjustModalZoom(delta) {
    const slider = document.getElementById('modalZoomSlider');
    let newVal = parseFloat(slider.value) + delta;
    newVal = Math.max(parseFloat(slider.min), Math.min(parseFloat(slider.max), newVal));
    slider.value = newVal;
    onModalZoomChange(newVal);
}

function rotateModalImage(deg) {
    modalRotation = (modalRotation + deg) % 360;
    renderModalCanvas();
}

function resetModalCrop() {
    if (!modalCurrentImg) return;
    modalImgX = modalCanvas.width / 2;
    modalImgY = modalCanvas.height / 2;
    const scaleW = modalCanvas.width / modalCurrentImg.width;
    const scaleH = modalCanvas.height / modalCurrentImg.height;
    modalScale = Math.max(scaleW, scaleH);
    modalRotation = 0;
    document.getElementById('modalZoomSlider').value = modalScale;
    document.getElementById('modalZoomValBadge').textContent = `${Math.round(modalScale * 100)}%`;
    renderModalCanvas();
}

// Touch & Mouse Drag Handlers
const modalWrapper = document.getElementById('modalCanvasWrapper');

function getModalPos(e) {
    const rect = modalCanvas.getBoundingClientRect();
    const clientX = e.touches ? e.touches[0].clientX : e.clientX;
    const clientY = e.touches ? e.touches[0].clientY : e.clientY;
    return {
        x: clientX - rect.left,
        y: clientY - rect.top
    };
}

if (modalWrapper) {
    const startModalDrag = (e) => {
        if (!modalCurrentImg) return;
        modalIsDragging = true;
        modalWrapper.style.cursor = 'grabbing';
        const pos = getModalPos(e);
        modalStartX = pos.x - modalImgX;
        modalStartY = pos.y - modalImgY;
    };

    const onModalDrag = (e) => {
        if (!modalIsDragging || !modalCurrentImg) return;
        if (e.cancelable) e.preventDefault();
        const pos = getModalPos(e);
        modalImgX = pos.x - modalStartX;
        modalImgY = pos.y - modalStartY;
        renderModalCanvas();
    };

    const stopModalDrag = () => {
        if (modalIsDragging) {
            modalIsDragging = false;
            modalWrapper.style.cursor = 'grab';
            exportModalCroppedAvatar();
        }
    };

    modalWrapper.addEventListener('mousedown', startModalDrag);
    window.addEventListener('mousemove', onModalDrag);
    window.addEventListener('mouseup', stopModalDrag);

    modalWrapper.addEventListener('touchstart', startModalDrag, { passive: false });
    window.addEventListener('touchmove', onModalDrag, { passive: false });
    window.addEventListener('touchend', stopModalDrag);
}

// AJAX SUBMIT DYNAMIC AUTHOR PHOTO
function submitDynamicAuthorPhoto() {
    exportModalCroppedAvatar();
    
    const fileInput = document.getElementById('modalAuthorAvatarInput');
    const formData = new FormData();
    
    if (modalCroppedDataUrl) {
        formData.append('avatar_cropped', modalCroppedDataUrl);
    }
    if (fileInput && fileInput.files && fileInput.files[0]) {
        formData.append('avatar', fileInput.files[0]);
    }
    
    const saveBtn = document.getElementById('modalSavePhotoBtn');
    const spinner = document.getElementById('modalPhotoSpinner');
    const icon = document.getElementById('modalPhotoSaveIcon');
    const alertBox = document.getElementById('modalPhotoUploadAlert');
    
    saveBtn.disabled = true;
    spinner.classList.remove('d-none');
    icon.classList.add('d-none');
    alertBox.className = 'alert d-none';
    
    fetch('{{ route("author.profile.avatar") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        saveBtn.disabled = false;
        spinner.classList.add('d-none');
        icon.classList.remove('d-none');
        
        if (data.success) {
            alertBox.className = 'alert alert-success small py-2 px-3 rounded-3 mb-0';
            alertBox.textContent = data.message;
            alertBox.classList.remove('d-none');
            
            // Update all avatar images on the page instantly!
            const newUrl = data.avatar_url;
            const mainBox = document.getElementById('dashAvatarMainBox');
            if (mainBox && newUrl) {
                mainBox.innerHTML = `<img src="${newUrl}" alt="Author Avatar" class="w-100 h-100 object-fit-cover current-author-avatar-img">`;
            }
            document.querySelectorAll('.header-author-avatar-img').forEach(img => {
                img.src = newUrl;
            });
            
            setTimeout(() => {
                const modalEl = document.getElementById('authorPhotoStudioModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }, 1200);
        } else {
            alertBox.className = 'alert alert-danger small py-2 px-3 rounded-3 mb-0';
            alertBox.textContent = data.message || 'ছবি আপলোড ব্যর্থ হয়েছে।';
            alertBox.classList.remove('d-none');
        }
    })
    .catch(err => {
        saveBtn.disabled = false;
        spinner.classList.add('d-none');
        icon.classList.remove('d-none');
        alertBox.className = 'alert alert-danger small py-2 px-3 rounded-3 mb-0';
        alertBox.textContent = 'সার্ভার সংযোগ সমস্যা। অনুগ্রহ করে আবার চেষ্টা করুন।';
        alertBox.classList.remove('d-none');
    });
}

// AJAX SUBMIT AUTHOR BIO & PROFILE
function submitAuthorBio(e) {
    e.preventDefault();
    const name = document.getElementById('editAuthorNameInput').value.trim();
    const penName = document.getElementById('editAuthorPenNameInput').value.trim();
    const bio = document.getElementById('editAuthorBioInput').value.trim();
    
    const saveBtn = document.getElementById('saveBioSubmitBtn');
    const spinner = document.getElementById('saveBioSpinner');
    const icon = document.getElementById('saveBioIcon');
    const alertBox = document.getElementById('editBioAlert');
    
    saveBtn.disabled = true;
    spinner.classList.remove('d-none');
    icon.classList.add('d-none');
    alertBox.className = 'alert d-none';
    
    const payload = {
        name: name,
        pen_name: penName,
        bio: bio
    };
    
    fetch('{{ route("author.profile.update") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        saveBtn.disabled = false;
        spinner.classList.add('d-none');
        icon.classList.remove('d-none');
        
        if (data.success) {
            alertBox.className = 'alert alert-success small py-2 px-3 rounded-3 mb-0';
            alertBox.textContent = data.message;
            alertBox.classList.remove('d-none');
            
            // Update displayed values
            const nameDisplay = document.getElementById('dashAuthorNameDisplay');
            if (nameDisplay) nameDisplay.textContent = name;
            const bioDisplay = document.getElementById('dashAuthorBioDisplay');
            if (bioDisplay) bioDisplay.textContent = bio || 'আপনার লেখক পরিচিতি ও সাহিত্য জীবনবৃত্তান্ত যুক্ত করুন যাতে পাঠকরা আপনার সম্পর্কে জানতে পারে।';
            
            setTimeout(() => {
                const modalEl = document.getElementById('authorBioEditModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }, 1000);
        } else {
            alertBox.className = 'alert alert-danger small py-2 px-3 rounded-3 mb-0';
            alertBox.textContent = data.message || 'আপডেট করা সম্ভব হয়নি।';
            alertBox.classList.remove('d-none');
        }
    })
    .catch(err => {
        saveBtn.disabled = false;
        spinner.classList.add('d-none');
        icon.classList.remove('d-none');
        alertBox.className = 'alert alert-danger small py-2 px-3 rounded-3 mb-0';
        alertBox.textContent = 'সার্ভার ত্রুটি। আবার চেষ্টা করুন।';
        alertBox.classList.remove('d-none');
    });
}
</script>
@endsection
