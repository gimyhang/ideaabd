@extends('layouts.app')
@section('title', 'ideaabd — বাংলাদেশের বিশ্বস্ত বই মার্কেটপ্লেস')

@section('content')

{{-- ══ HERO CAROUSEL ═══════════════════════════════════════════════════════════ --}}
<section class="mb-4">
    <div class="container-fluid px-0 px-md-3 mt-3">
        <div id="homeHeroCarousel" class="carousel slide shadow-sm rounded-xl overflow-hidden" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#homeHeroCarousel" data-bs-slide-to="0" class="active"></button>
                <button type="button" data-bs-target="#homeHeroCarousel" data-bs-slide-to="1"></button>
                <button type="button" data-bs-target="#homeHeroCarousel" data-bs-slide-to="2"></button>
            </div>
            <div class="carousel-inner">
                <div class="carousel-item active" style="background: linear-gradient(135deg, #e0c3fc 0%, #8ec5fc 100%);">
                    <div class="row align-items-center h-100 p-4 md:p-5" style="min-height: 300px;">
                        <div class="col-md-7 text-center text-md-start">
                            <span class="badge bg-indigo-600 mb-2 px-3 py-1">বইমেলা ধামাকা</span>
                            <h2 class="fw-bold text-slate-800 display-5 mb-2">উপন্যাসে ৪০% পর্যন্ত ছাড়!</h2>
                            <p class="text-slate-700 fs-5 mb-4">সেরা লেখকদের নতুন সব বই পেয়ে যান আকর্ষণীয় মূল্যে।</p>
                            <a href="{{ route('book.index') }}" class="btn btn-dark px-4 py-2 rounded-pill fw-semibold">বইগুলো দেখুন</a>
                        </div>
                        <div class="col-md-5 d-none d-md-block text-center">
                            <i class="fa-solid fa-book-open-reader" style="font-size: 8rem; color: rgba(255,255,255,0.7)"></i>
                        </div>
                    </div>
                </div>
                <div class="carousel-item" style="background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);">
                    <div class="row align-items-center h-100 p-4 md:p-5" style="min-height: 300px;">
                        <div class="col-md-7 text-center text-md-start">
                            <span class="badge bg-rose-600 mb-2 px-3 py-1">নতুন প্রকাশনা</span>
                            <h2 class="fw-bold text-slate-900 display-5 mb-2">ইসলামী বইয়ের বিশাল কালেকশন</h2>
                            <p class="text-slate-800 fs-5 mb-4">জীবন গড়ার বইগুলো এখন হাতের নাগালে।</p>
                            <a href="{{ route('book.index') }}" class="btn btn-dark px-4 py-2 rounded-pill fw-semibold">বইগুলো দেখুন</a>
                        </div>
                        <div class="col-md-5 d-none d-md-block text-center">
                            <i class="fa-solid fa-mosque" style="font-size: 8rem; color: rgba(255,255,255,0.7)"></i>
                        </div>
                    </div>
                </div>
                <div class="carousel-item" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
                    <div class="row align-items-center h-100 p-4 md:p-5" style="min-height: 300px;">
                        <div class="col-md-7 text-center text-md-start">
                            <span class="badge bg-teal-600 mb-2 px-3 py-1">ডিজিটাল পাঠ</span>
                            <h2 class="fw-bold text-slate-800 display-5 mb-2">হাজারো ই-বুক</h2>
                            <p class="text-slate-700 fs-5 mb-4">যেকোনো জায়গায়, যেকোনো ডিভাইসে পড়ুন।</p>
                            <a href="{{ route('ebook.index') }}" class="btn btn-dark px-4 py-2 rounded-pill fw-semibold">ই-বুক পড়ুন</a>
                        </div>
                        <div class="col-md-5 d-none d-md-block text-center">
                            <i class="fa-solid fa-tablet-screen-button" style="font-size: 8rem; color: rgba(255,255,255,0.7)"></i>
                        </div>
                    </div>
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#homeHeroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon bg-dark rounded-circle p-2" aria-hidden="true"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#homeHeroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon bg-dark rounded-circle p-2" aria-hidden="true"></span>
            </button>
        </div>
    </div>
</section>

{{-- ══ FEATURES STRIP ════════════════════════════════════════════════════════ --}}
<section class="mb-5">
    <div class="container">
        <div class="row g-3 text-center">
            @foreach([['fa-truck','দ্রুত ডেলিভারি','সারাদেশে ৩–৫ দিনে','#e8f4f8'],['fa-shield-halved','নিরাপদ পেমেন্ট','বিকাশ, নগদ, কার্ড','#fff5e6'],['fa-rotate-left','সহজ রিটার্ন','৭ দিনের নিশ্চয়তা','#e8f8ee'],['fa-headset','২৪/৭ সাপোর্ট','সর্বদা আপনার পাশে','#f5e8f8']] as $f)
            <div class="col-6 col-md-3">
                <div class="rounded-3 p-3 h-100 d-flex flex-column justify-content-center border border-slate-100 shadow-sm" style="background:{{ $f[3] }};">
                    <i class="fa-solid {{ $f[0] }} fs-3 mb-2" style="color:#0066cc;"></i>
                    <div class="fw-bold text-slate-800" style="font-size:.95rem;">{{ $f[1] }}</div>
                    <div class="text-slate-500" style="font-size:.78rem;">{{ $f[2] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<div class="container mb-5">
    <div class="flex flex-col md:flex-row gap-4 w-full">
        <!-- Sidebar Column (For Bestseller and Filters later if needed) -->
        <aside class="w-full md:w-64 shrink-0 d-none d-md-block">
            <!-- Top Seller Sidebar -->
            @if(isset($recentlySold) && $recentlySold->isNotEmpty())
            @php $topSeller = $recentlySold->first(); @endphp
            <div class="bg-white rounded-xl p-3 shadow-sm border border-amber-200 bg-gradient-to-b from-amber-50 to-white relative overflow-hidden mb-4">
                <div class="absolute top-0 right-0 bg-amber-500 text-white text-[9px] font-black px-2 py-1 rounded-bl-lg tracking-wider uppercase shadow-sm z-10">Bestseller</div>
                <h3 class="font-bold text-slate-800 text-[13px] mb-3 flex items-center gap-1.5 text-amber-700">
                    <i class="fa-solid fa-crown text-amber-500"></i> টপ সেল বুক
                </h3>
                <a href="{{ route('book.show', $topSeller->slug) }}" class="flex flex-col gap-3 group items-center text-center text-decoration-none">
                    <div class="w-[120px] aspect-[7/10] bg-slate-100 rounded-md shadow-sm overflow-hidden mx-auto border border-amber-100">
                        @if($topSeller->cover_image)
                            <img src="{{ str_starts_with($topSeller->cover_image, 'http') ? $topSeller->cover_image : asset('storage/' . $topSeller->cover_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-3xl bg-slate-100 text-slate-300">📘</div>
                        @endif
                    </div>
                    <div class="flex flex-col w-full">
                        <h4 class="text-[14px] font-bold text-slate-800 group-hover:text-indigo-600 line-clamp-2 leading-snug">{{ $topSeller->title }}</h4>
                        <p class="text-[12px] text-slate-500 line-clamp-1 mt-1">{{ $topSeller->authors->isNotEmpty() ? $topSeller->authors->pluck('name')->join(', ') : ($topSeller->author_name ?: 'অজানা লেখক') }}</p>
                        <div class="flex items-center justify-center gap-1.5 text-[14px] font-black text-slate-900 mt-1.5">
                            @if($topSeller->discount_price && $topSeller->discount_price < $topSeller->price)
                                <span class="text-[11px] text-rose-500 line-through font-medium">৳{{ round($topSeller->price) }}</span>
                            @endif
                            ৳{{ round($topSeller->discount_price ?? $topSeller->price) }}
                        </div>
                    </div>
                </a>
            </div>
            @endif

            <!-- Promo Sidebar Banners -->
            @php
                $banner1 = class_exists(\App\Models\AdminDashboardSetting::class) ? \App\Models\AdminDashboardSetting::where('key', 'home_banner_1')->value('value') : null;
                $banner2 = class_exists(\App\Models\AdminDashboardSetting::class) ? \App\Models\AdminDashboardSetting::where('key', 'home_banner_2')->value('value') : null;
            @endphp

            @if($banner1)
                <a href="{{ route('book.index') }}" class="block mb-3 rounded-3 overflow-hidden shadow-sm">
                    <img src="{{ asset($banner1) }}" alt="Special Offer" class="w-full h-auto object-cover hover:scale-105 transition-transform duration-300">
                </a>
            @else
                <div class="rounded-3 p-4 mb-3 shadow-sm relative overflow-hidden" style="background:linear-gradient(135deg,#ffecd2,#fcb69f);">
                    <div class="fs-2 mb-2">🎁</div>
                    <h5 class="fw-bold mb-1" style="color:#7b3f00;">বিশেষ ছাড়!</h5>
                    <p style="color:#8b4513; font-size:.88rem; margin-bottom:1rem;">নির্বাচিত বইয়ে ৩০% পর্যন্ত ছাড়।</p>
                    <a href="{{ route('book.index') }}" class="btn btn-sm fw-semibold rounded-pill px-3" style="background:#7b3f00; color:#fff;">অফার দেখুন</a>
                </div>
            @endif
            
            @if($banner2)
                <a href="{{ route('ebook.index') }}" class="block mb-3 rounded-3 overflow-hidden shadow-sm">
                    <img src="{{ asset($banner2) }}" alt="Digital Books" class="w-full h-auto object-cover hover:scale-105 transition-transform duration-300">
                </a>
            @else
                <div class="rounded-3 p-4 mb-3 shadow-sm relative overflow-hidden" style="background:linear-gradient(135deg,#a1c4fd,#c2e9fb);">
                    <div class="fs-2 mb-2">📱</div>
                    <h5 class="fw-bold mb-1" style="color:#0a3d62;">ডিজিটাল বই</h5>
                    <p style="color:#1a5276; font-size:.88rem; margin-bottom:1rem;">EPUB, PDF — যেকোনো ডিভাইসে পড়ুন।</p>
                    <a href="{{ route('ebook.index') }}" class="btn btn-sm fw-semibold rounded-pill px-3" style="background:#0a3d62; color:#fff;">ই-বুক দেখুন</a>
                </div>
            @endif
        </aside>

        <!-- Main Content Column -->
        <div class="flex-1 flex flex-col gap-4 min-w-0 w-full">
            
            <!-- Flash Sales -->
            @if(isset($flashSales) && $flashSales->isNotEmpty())
            <div class="bg-white rounded-xl p-4 md:p-5 shadow-sm ring-1 ring-slate-100 border-2 border-indigo-100 relative overflow-hidden">
                <div class="absolute -right-10 -top-10 w-32 h-32 bg-indigo-50 rounded-full opacity-50 pointer-events-none"></div>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 border-b border-indigo-50 pb-3 gap-3">
                    <h2 class="text-lg md:text-xl font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-bolt text-amber-500"></i>
                        ফ্ল্যাশ সেলস
                    </h2>
                    <div class="flex items-center gap-2 bg-indigo-50 px-3 py-1.5 rounded-lg border border-indigo-100">
                        <span class="text-xs font-semibold text-slate-600">অফার শেষ হতে বাকি:</span>
                        <div class="flex items-center gap-1 text-indigo-700 font-bold" id="flash-countdown">
                            <span class="bg-white px-1.5 py-0.5 rounded shadow-sm text-sm" id="cd-h">03</span>:
                            <span class="bg-white px-1.5 py-0.5 rounded shadow-sm text-sm" id="cd-m">45</span>:
                            <span class="bg-white px-1.5 py-0.5 rounded shadow-sm text-sm" id="cd-s">12</span>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2 md:gap-3 w-full">
                    @foreach($flashSales->take(5) as $book)
                        @include('book::frontend.partials.book-card', ['book' => $book])
                    @endforeach
                </div>
            </div>
            @endif

            <!-- New Arrivals -->
            @if(isset($books) && $books->isNotEmpty())
            <div class="bg-white rounded-xl p-4 md:p-5 shadow-sm ring-1 ring-slate-100 mt-4">
                <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-2">
                    <h2 class="text-lg md:text-xl font-bold text-slate-800 flex items-center gap-2">
                        <span class="w-1.5 h-6 bg-emerald-500 rounded-full inline-block"></span>
                        নতুন কালেকশন
                    </h2>
                    <a href="{{ route('book.index', ['sort' => 'latest']) }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded hover:bg-indigo-100 transition-colors text-decoration-none">সবগুলো দেখুন</a>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2 md:gap-3 w-full">
                    @foreach($books->take(5) as $book)
                        @include('book::frontend.partials.book-card', ['book' => $book])
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Bestsellers -->
            @if(isset($recentlySold) && $recentlySold->isNotEmpty())
            <div class="bg-white rounded-xl p-4 md:p-5 shadow-sm ring-1 ring-slate-100 mt-4">
                <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-2">
                    <h2 class="text-lg md:text-xl font-bold text-slate-800 flex items-center gap-2">
                        <span class="w-1.5 h-6 bg-rose-500 rounded-full inline-block"></span>
                        সর্বাধিক বিক্রিত বই
                    </h2>
                    <a href="{{ route('book.index', ['sort' => 'bestselling']) }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded hover:bg-indigo-100 transition-colors text-decoration-none">সবগুলো দেখুন</a>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2 md:gap-3 w-full">
                    @foreach($recentlySold->take(10) as $book)
                        @include('book::frontend.partials.book-card', ['book' => $book])
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Top Authors Carousel -->
            @if(isset($sidebarAuthors) && $sidebarAuthors->isNotEmpty())
            <div class="bg-white rounded-xl p-4 md:p-5 shadow-sm ring-1 ring-slate-100 mt-4">
                <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-2">
                    <h2 class="text-lg md:text-xl font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-feather text-purple-500"></i>
                        জনপ্রিয় লেখক
                    </h2>
                    <a href="{{ route('authors.index') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded hover:bg-indigo-100 transition-colors text-decoration-none">সবগুলো দেখুন</a>
                </div>
                <div class="flex overflow-x-auto gap-4 pb-2 custom-scrollbar snap-x">
                    @foreach($sidebarAuthors as $author)
                    <a href="{{ route('book.index', ['author' => $author->slug]) }}" class="flex flex-col items-center gap-2 min-w-[90px] snap-start group text-decoration-none">
                        <div class="w-20 h-20 rounded-full bg-slate-100 overflow-hidden ring-2 ring-transparent group-hover:ring-indigo-500 transition-all shadow-sm">
                            @if(isset($author->photo) && $author->photo)
                                <img src="{{ asset('storage/' . $author->photo) }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-slate-400 bg-slate-200 text-3xl"><i class="fa-solid fa-user"></i></div>
                            @endif
                        </div>
                        <span class="text-[13px] font-bold text-slate-700 group-hover:text-indigo-600 text-center line-clamp-1">{{ $author->name }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Recently Viewed -->
            @if(isset($recentlyViewedBooks) && $recentlyViewedBooks->isNotEmpty())
            <div class="bg-slate-50 rounded-xl p-4 md:p-5 shadow-sm ring-1 ring-slate-200 mt-4">
                <div class="flex items-center justify-between mb-4 border-b border-slate-200 pb-2">
                    <h2 class="text-lg md:text-xl font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-indigo-500"></i>
                        আপনি সম্প্রতি দেখেছেন
                    </h2>
                </div>
                <div class="flex overflow-x-auto gap-3 pb-2 custom-scrollbar snap-x">
                    @foreach($recentlyViewedBooks as $book)
                    <div class="min-w-[140px] max-w-[150px] snap-start">
                        @include('book::frontend.partials.book-card', ['book' => $book])
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Newsletter Subscription -->
            <div class="bg-indigo-600 rounded-xl p-6 md:p-8 mt-4 text-center relative overflow-hidden shadow-md">
                <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 20px 20px;"></div>
                <div class="relative z-10 max-w-lg mx-auto">
                    <i class="fa-regular fa-envelope-open mb-3 text-4xl text-indigo-200"></i>
                    <h2 class="text-xl md:text-3xl font-bold text-white mb-2">আমাদের নিউজলেটারে সাবস্ক্রাইব করুন</h2>
                    <p class="text-indigo-100 text-[15px] mb-5">নতুন বইয়ের আপডেট, রিভিউ এবং এক্সক্লুসিভ অফার পেতে ইমেইল দিয়ে যুক্ত থাকুন!</p>
                    <form action="{{ route('newsletter.subscribe') }}" method="POST" class="flex flex-col sm:flex-row gap-2">
                        @csrf
                        <input type="email" name="email" placeholder="আপনার ইমেইল অ্যাড্রেস লিখুন..." required class="flex-1 rounded-lg border-0 py-3 px-4 focus:ring-2 focus:ring-white bg-white/10 text-white placeholder-indigo-200">
                        <button type="submit" class="bg-white text-indigo-600 font-bold px-6 py-3 rounded-lg hover:bg-indigo-50 transition-colors shadow-sm">সাবস্ক্রাইব</button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Dummy Countdown Timer for Flash Sales
        let elH = document.getElementById('cd-h');
        if (elH) {
            let endTime = new Date().getTime() + (3 * 60 * 60 * 1000) + (45 * 60 * 1000) + (12 * 1000);
            const timer = setInterval(function() {
                let now = new Date().getTime();
                let distance = endTime - now;
                if (distance < 0) {
                    clearInterval(timer);
                    return;
                }
                let h = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                let m = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                let s = Math.floor((distance % (1000 * 60)) / 1000);
                const format = val => val < 10 ? '0' + val : val;
                
                document.getElementById('cd-h').innerText = format(h);
                document.getElementById('cd-m').innerText = format(m);
                document.getElementById('cd-s').innerText = format(s);
            }, 1000);
        }
    });
</script>

@endsection
