@extends('author.layout')

@section('title', 'ড্যাশবোর্ড — লেখক সেলফ-পাবলিশিং')
@section('heading', 'স্বাগতম, ' . auth()->user()->name)

@section('content')
<div class="d-flex flex-column gap-4">

    {{-- Welcome Banner / Quick CTA --}}
    <div class="author-card p-4 text-white position-relative overflow-hidden" 
         style="background: linear-gradient(135deg, #3730a3 0%, #1e1b4b 100%);">
        <div class="row align-items-center position-relative" style="z-index: 2;">
            <div class="col-12 col-md-8">
                <span class="badge bg-warning text-dark fw-bold px-3 py-1 rounded-pill mb-2">
                    <i class="fas fa-hand-holding-dollar me-1"></i> ৫০% রয়্যালটি শেয়ার মডেল (50% Author Royalty)
                </span>
                <h3 class="fw-bold mb-1">আপনার ই-বুক প্রকাশ করুন ও সরাসরি আয় করুন</h3>
                <p class="text-white-50 small mb-3">
                    আইডিয়া প্রকাশনের সেলফ-পাবলিশিং প্ল্যাটফর্মের মাধ্যমে আপনার পাণ্ডুলিপি সরাসরি বিশ্বব্যাপী পাঠকদের কাছে পৌঁছে দিন। প্রতিটি বিক্রির ৫০% রয়্যালটি তাৎক্ষণিক আপনার ওয়ালেটে জমা হবে।
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('author.ebooks.create') }}" class="btn btn-warning rounded-pill px-4 py-2 fw-bold text-dark shadow-sm">
                        <i class="fas fa-cloud-arrow-up me-1.5"></i> নতুন ই-বুক আপলোড করুন
                    </a>
                    <a href="{{ route('author.posts.create') }}" class="btn btn-outline-warning rounded-pill px-3.5 py-2 fw-bold text-white">
                        <i class="fas fa-pen-nib me-1.5"></i> নতুন আইডিয়াপত্র লিখুন
                    </a>
                    <a href="{{ route('author.royalties') }}" class="btn btn-outline-light rounded-pill px-3.5 py-2 fw-semibold">
                        <i class="fas fa-receipt me-1.5"></i> রয়্যালটি হিস্ট্রি দেখুন
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- KPI Cards Grid --}}
    <div class="row g-3">
        {{-- 1. Total E-Books --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="author-card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">মোট ই-বুক</span>
                    <span class="p-2 bg-primary-subtle text-primary rounded-3"><i class="fas fa-book"></i></span>
                </div>
                <h3 class="fw-bold mb-1 text-dark">{{ number_format($totalEbooks) }}</h3>
                <div class="small text-muted d-flex align-items-center gap-1.5">
                    <span class="badge bg-success-subtle text-success">{{ $publishedEbooks }} লাইভ</span>
                    @if($pendingEbooks > 0)
                        <span class="badge bg-warning-subtle text-warning">{{ $pendingEbooks }} রিভিউতে</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- 2. Total Sold --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="author-card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">মোট বিক্রি (Copies Sold)</span>
                    <span class="p-2 bg-info-subtle text-info rounded-3"><i class="fas fa-bag-shopping"></i></span>
                </div>
                <h3 class="fw-bold mb-1 text-dark">{{ number_format($totalCopiesSold) }} <small class="fs-6 fw-normal text-muted">কপি</small></h3>
                <span class="small text-muted">পাঠকদের দ্বারা ক্রীত ই-বুক</span>
            </div>
        </div>

        {{-- 3. Total Royalty Earned --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="author-card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">মোট অর্জিত রয়্যালটি (৫০%)</span>
                    <span class="p-2 bg-success-subtle text-success rounded-3"><i class="fas fa-sack-dollar"></i></span>
                </div>
                <h3 class="fw-bold mb-1 text-success font-monospace">৳{{ number_format($totalRoyaltyEarned, 2) }}</h3>
                <span class="small text-muted">সর্বমোট আয়ের ৫০% শেয়ার</span>
            </div>
        </div>

        {{-- 4. Available Balance --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="author-card p-3 h-100 bg-warning-subtle bg-opacity-25 border-warning border-opacity-50">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-dark small fw-bold">উত্তোলনযোগ্য ব্যালেন্স</span>
                    <span class="p-2 bg-warning text-dark rounded-3"><i class="fas fa-wallet"></i></span>
                </div>
                <h3 class="fw-bold mb-1 text-dark font-monospace">৳{{ number_format($availableBalance, 2) }}</h3>
                <div class="d-flex align-items-center justify-content-between mt-1">
                    <span class="small text-muted" style="font-size: 11px;">মিনিমাম ১,০০০৳</span>
                    <a href="{{ route('author.payouts.index') }}" class="btn btn-xs btn-primary rounded-pill px-2 py-0.5" style="font-size: 11px;">উত্তোলন করুন</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Sections: Recent Royalties & My Ebooks --}}
    <div class="row g-4">
        {{-- Left: Recent Sales & Royalty Earnings --}}
        <div class="col-12 col-lg-8">
            <div class="author-card p-3 p-md-4">
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
                                <th>বিক্রয় মূল্য</th>
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
                                        এখনও কোনো বিক্রয় ট্রানজ্যাকশন নেই। বই পাবলিশ হওয়ার পর এখানে সেলস ও রয়্যালটি দেখা যাবে।
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
            <div class="author-card p-3 p-md-4">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-book text-info me-1.5"></i> আমার ই-বুকসমূহ
                    </h6>
                    <a href="{{ route('author.ebooks.index') }}" class="small text-primary text-decoration-none fw-semibold">
                        ম্যানেজ করুন
                    </a>
                </div>

                <div class="d-flex flex-column gap-3">
                    @forelse($recentEbooks as $eb)
                        <div class="d-flex align-items-center gap-2.5 p-2 rounded-3 border bg-light bg-opacity-50">
                            <img src="{{ $eb->cover_url ?? 'https://placehold.co/100x140?text=Cover' }}" 
                                 alt="Cover" class="rounded object-fit-cover flex-shrink-0" style="width: 42px; height: 60px;">
                            <div class="overflow-hidden flex-grow-1">
                                <h6 class="small fw-bold mb-0 text-truncate text-dark">{{ $eb->title }}</h6>
                                <div class="font-monospace small text-primary fw-semibold">৳{{ number_format($eb->price, 2) }}</div>
                                <div class="mt-1">
                                    @if($eb->mod_status === 'approved')
                                        <span class="badge bg-success-subtle text-success" style="font-size: 10px;">লাইভ</span>
                                    @elseif($eb->mod_status === 'rejected')
                                        <span class="badge bg-danger-subtle text-danger" style="font-size: 10px;">সংশোধন প্রয়োজন</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning" style="font-size: 10px;">পেন্ডিং রিভিউ</span>
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

    {{-- SECOND MAIN ROW: IDEAPATRA (ARTICLES & BLOG POSTS) --}}
    <div class="author-card p-3 p-md-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 pb-2 border-bottom">
            <div>
                <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    <span class="badge bg-warning bg-opacity-25 text-dark rounded-circle p-2">
                        <i class="fas fa-pen-nib"></i>
                    </span>
                    <span>আইডিয়াপত্র (আমার ব্লগ ও কলামসমূহ)</span>
                </h6>
                <small class="text-muted">আপনার রচিত সকল ব্লগ পোস্ট, সাহিত্য রচনা ও কলামের স্ট্যাটাস</small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark border">{{ $totalPosts }} টি লেখা</span>
                <span class="badge bg-success-subtle text-success">{{ $publishedPosts }} প্রকাশিত</span>
                <a href="{{ route('author.posts.create') }}" class="btn btn-sm btn-warning text-dark fw-bold rounded-pill px-3 shadow-xs">
                    <i class="fas fa-feather-pointed me-1"></i> নতুন আইডিয়াপত্র লিখুন
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small fw-bold text-secondary">
                    <tr>
                        <th style="width: 60px;">ছবি</th>
                        <th>লেখার শিরোনাম</th>
                        <th>ক্যাটাগরি</th>
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
                                <img src="{{ $post->featured_image ? asset('storage/' . $post->featured_image) : 'https://placehold.co/80x60?text=Post' }}" 
                                     alt="Post" class="rounded object-fit-cover shadow-xs" style="width: 44px; height: 34px;">
                            </td>
                            <td>
                                <strong class="text-dark">{{ $post->title }}</strong>
                                @if($post->excerpt)
                                    <small class="text-muted d-block text-truncate" style="max-width: 300px;">{{ $post->excerpt }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $post->category?->name ?? 'সাধারণ' }}</span>
                            </td>
                            <td class="text-muted">{{ $post->created_at->format('d M, Y') }}</td>
                            <td>
                                <span class="text-muted font-monospace"><i class="fas fa-eye me-1"></i>{{ number_format($post->views_count ?? 0) }}</span>
                            </td>
                            <td>
                                @if($post->status === 'published')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5">লাইভ</span>
                                @elseif($post->status === 'pending')
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5">রিভিউতে আছে</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-0.5">ড্রাফট</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('blog.show', $post->slug ?: $post->id) }}" target="_blank" class="btn btn-outline-primary" title="পড়ুন">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('author.posts.edit', $post->id) }}" class="btn btn-outline-secondary" title="এডিট করুন">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fas fa-newspaper fs-3 opacity-25 d-block mb-1"></i>
                                আপনি এখনও কোনো আইডিয়াপত্র বা ব্লগ আর্টিকেল প্রকাশ করেননি।
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
