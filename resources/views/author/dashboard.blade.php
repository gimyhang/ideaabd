@extends('author.layout')

@section('title', 'লেখক ড্যাশবোর্ড — আইডিয়া প্রকাশন')
@section('heading', 'স্বাগতম, ' . auth()->user()->name)

@section('content')
<div class="d-flex flex-column gap-3.5">

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

<style>
.hover-lift {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-lift:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.12) !important;
}
</style>
@endsection
