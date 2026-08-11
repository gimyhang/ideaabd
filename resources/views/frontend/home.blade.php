@extends('layouts.app')
@section('title', 'ideaabd — বাংলাদেশের বিশ্বস্ত বই মার্কেটপ্লেস')

@section('content')

{{-- ══ HERO BANNER ═══════════════════════════════════════════════════════════ --}}
<section style="background:linear-gradient(135deg,#e8f4f8 0%,#d4ecf7 100%); padding:48px 0 40px;">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <p class="text-uppercase fw-semibold mb-1" style="color:#0099ff; font-size:.8rem; letter-spacing:.1em;">WELCOME TO IDEAABD</p>
                <h1 class="fw-bold mb-3" style="color:#1a3a52; font-size:2.2rem; line-height:1.35;">
                    হাজারো বই এর জগতে<br>
                    <span style="color:#0066cc;">স্বাগতম!</span>
                </h1>
                <p class="mb-4" style="color:#456; font-size:1rem; line-height:1.7;">
                    বই, ই-বুক, ওয়েবজিন — সব এক জায়গায়। লেখক ও পাঠকের মেলবন্ধন।
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('book.index') }}" class="btn btn-primary px-4 py-2 rounded-pill fw-semibold">
                        <i class="fas fa-book me-2"></i>বই দেখুন
                    </a>
                    <a href="{{ route('register.form', 'seller') }}" class="btn btn-outline-primary px-4 py-2 rounded-pill fw-semibold">
                        বিক্রেতা হিসেবে যোগ দিন
                    </a>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    @foreach([['📚','৫০,০০০+','বই','#e8f4f8'],['📱','১০,০০০+','ই-বুক','#fff5e6'],['✍️','১,০০০+','লেখক','#e8f8ee'],['🏢','৫০০+','প্রকাশক','#f5e8f8']] as $s)
                    <div class="text-center p-3 rounded-3 shadow-sm" style="background:{{ $s[3] }}; min-width:110px;">
                        <div style="font-size:1.8rem; line-height:1;">{{ $s[0] }}</div>
                        <div class="fw-bold mt-1" style="color:#1a3a52; font-size:1.15rem;">{{ $s[1] }}</div>
                        <div style="color:#678; font-size:.8rem;">{{ $s[2] }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ══ FEATURES STRIP ════════════════════════════════════════════════════════ --}}
<section style="background:#fff; border-bottom:1px solid #eef5fa;">
    <div class="container py-4">
        <div class="row g-3 text-center">
            @foreach([['fas fa-truck','দ্রুত ডেলিভারি','সারাদেশে ৩–৫ দিনে','#e8f4f8'],['fas fa-shield-alt','নিরাপদ পেমেন্ট','বিকাশ, নগদ, কার্ড','#fff5e6'],['fas fa-undo','সহজ রিটার্ন','৭ দিনের নিশ্চয়তা','#e8f8ee'],['fas fa-headset','২৪/৭ সাপোর্ট','সর্বদা আপনার পাশে','#f5e8f8']] as $f)
            <div class="col-6 col-md-3">
                <div class="rounded-3 p-3" style="background:{{ $f[3] }};">
                    <i class="fas {{ $f[0] }} fs-4 mb-2" style="color:#0066cc;"></i>
                    <div class="fw-semibold" style="color:#1a3a52; font-size:.9rem;">{{ $f[1] }}</div>
                    <div style="color:#789; font-size:.78rem;">{{ $f[2] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══ CATEGORY GRID ══════════════════════════════════════════════════════════ --}}
<section class="py-5" style="background:#f8fbfd;">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0" style="color:#1a3a52; font-size:1.4rem;">
                <i class="fas fa-th-large me-2" style="color:#0066cc;"></i>ক্যাটাগরি অনুযায়ী কেনাকাটা
            </h2>
        </div>
        <div class="row g-3">
            @foreach([
                ['fas fa-book','বই','book.index','#e8f4f8','#0066cc','৫০,০০০+'],
                ['fas fa-tablet-alt','ই-বুক','ebook.index','#fff5e6','#ff9500','১০,০০০+'],
                ['fas fa-newspaper','ওয়েবজিন','webzine.index','#e8f8ee','#198754','৩০০+'],
                ['fas fa-pen-fancy','লেখকগণ','authors.index','#f5e8f8','#9b59b6','১,০০০+'],
                ['fas fa-print','প্রকাশকগণ','publishers.index','#fde8e8','#e74c3c','৫০০+'],
                ['fas fa-graduation-cap','শিক্ষা','book.index','#e8fde8','#27ae60','৮,০০০+'],
            ] as $c)
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route($c[2]) }}" class="text-decoration-none">
                    <div class="rounded-3 p-3 text-center h-100 border-0 cat-card"
                        style="background:{{ $c[3] }}; transition:all .2s;"
                       
                       >
                        <i class="{{ $c[0] }} fs-3 mb-2" style="color:{{ $c[4] }};"></i>
                        <div class="fw-semibold" style="color:#1a3a52; font-size:.9rem;">{{ $c[1] }}</div>
                        <div style="color:#888; font-size:.75rem;">{{ $c[5] }}</div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══ NEW ARRIVALS ════════════════════════════════════════════════════════════ --}}
<section class="py-5" style="background:#fff;">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0" style="color:#1a3a52; font-size:1.4rem;">
                <i class="fas fa-fire me-2" style="color:#ff6b35;"></i>নতুন বই এলো
            </h2>
            <a href="{{ route('book.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">সব দেখুন →</a>
        </div>
        <div class="row g-3">
            @forelse($books as $book)
            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                <a href="{{ route('book.show', $book->slug ?? $book->id) }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 hover-lift" style="border-radius:10px; overflow:hidden;">
                        <div class="text-center py-3" style="background:#f0f7fc; min-height:120px; display:flex; align-items:center; justify-content:center;">
                            <span style="font-size:3rem;">📗</span>
                        </div>
                        <div class="card-body p-2">
                            <p class="fw-semibold mb-1 text-truncate" style="color:#1a3a52; font-size:.82rem;">{{ $book->title }}</p>
                            <p class="text-muted mb-1" style="font-size:.75rem;">{{ $book->author_name ?? 'লেখক' }}</p>
                            <div class="d-flex align-items-center gap-1">
                                @if(!empty($book->discount) && $book->discount > 0)
                                    <span class="fw-bold" style="color:#0066cc; font-size:.88rem;">৳{{ round($book->price * (1 - $book->discount/100)) }}</span>
                                    <span class="text-decoration-line-through text-muted" style="font-size:.73rem;">৳{{ $book->price }}</span>
                                @else
                                    <span class="fw-bold" style="color:#0066cc; font-size:.88rem;">৳{{ $book->price ?? '—' }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            @empty
            {{-- Demo cards when DB is empty --}}
            @foreach([
                ['পদ্মা নদীর মাঝি','মানিক বন্দ্যোপাধ্যায়',350,0],
                ['চাঁদের পাহাড়','বিভূতিভূষণ',320,15],
                ['গীতাঞ্জলি','রবীন্দ্রনাথ ঠাকুর',299,20],
                ['অপু ত্রয়ী','বিভূতিভূষণ',450,10],
                ['শেষের কবিতা','রবীন্দ্রনাথ',280,0],
                ['মৃত্যুক্ষুধা','কাজী নজরুল',320,12],
            ] as $i => $d)
            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                <div class="card border-0 shadow-sm h-100 hover-lift" style="border-radius:10px; overflow:hidden;">
                    <div class="text-center py-3" style="background:#f0f7fc; min-height:120px; display:flex; align-items:center; justify-content:center;">
                        <span style="font-size:3rem;">{{ ['📗','📘','📕','📙','📒','📔'][$i] }}</span>
                    </div>
                    <div class="card-body p-2">
                        <p class="fw-semibold mb-1 text-truncate" style="color:#1a3a52; font-size:.82rem;">{{ $d[0] }}</p>
                        <p class="text-muted mb-1" style="font-size:.75rem;">{{ $d[1] }}</p>
                        <div class="d-flex align-items-center gap-1">
                            @if($d[3] > 0)
                                <span class="fw-bold" style="color:#0066cc; font-size:.88rem;">৳{{ round($d[2]*(1-$d[3]/100)) }}</span>
                                <span class="text-decoration-line-through text-muted" style="font-size:.73rem;">৳{{ $d[2] }}</span>
                                <span class="badge ms-1" style="background:#e8fde8; color:#198754; font-size:.68rem; padding:2px 5px; border-radius:10px;">-{{ $d[3] }}%</span>
                            @else
                                <span class="fw-bold" style="color:#0066cc; font-size:.88rem;">৳{{ $d[2] }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
            @endforelse
        </div>
    </div>
</section>

{{-- ══ PROMO BANNERS ══════════════════════════════════════════════════════════ --}}
<section class="py-5" style="background:#f8fbfd;">
    <div class="container">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="rounded-3 p-4 h-100" style="background:linear-gradient(135deg,#ffecd2,#fcb69f);">
                    <div class="fs-2 mb-2">🎁</div>
                    <h5 class="fw-bold mb-1" style="color:#7b3f00;">বিশেষ ছাড়!</h5>
                    <p style="color:#8b4513; font-size:.88rem; margin-bottom:1rem;">নির্বাচিত বইয়ে ৩০% পর্যন্ত ছাড়।</p>
                    <a href="{{ route('book.index') }}" class="btn btn-sm fw-semibold rounded-pill px-3" style="background:#7b3f00; color:#fff;">অফার দেখুন</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="rounded-3 p-4 h-100" style="background:linear-gradient(135deg,#a1c4fd,#c2e9fb);">
                    <div class="fs-2 mb-2">📱</div>
                    <h5 class="fw-bold mb-1" style="color:#0a3d62;">ডিজিটাল বই</h5>
                    <p style="color:#1a5276; font-size:.88rem; margin-bottom:1rem;">EPUB, PDF — যেকোনো ডিভাইসে পড়ুন।</p>
                    <a href="{{ route('ebook.index') }}" class="btn btn-sm fw-semibold rounded-pill px-3" style="background:#0a3d62; color:#fff;">ই-বুক দেখুন</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="rounded-3 p-4 h-100" style="background:linear-gradient(135deg,#d4fc79,#96e6a1);">
                    <div class="fs-2 mb-2">📦</div>
                    <h5 class="fw-bold mb-1" style="color:#1a5632;">বাল্ক অর্ডার</h5>
                    <p style="color:#145a32; font-size:.88rem; margin-bottom:1rem;">স্কুল ও লাইব্রেরির জন্য বিশেষ ছাড়।</p>
                    <a href="{{ route('register.form', 'seller') }}" class="btn btn-sm fw-semibold rounded-pill px-3" style="background:#1a5632; color:#fff;">জানুন</a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ══ TOP AUTHORS ════════════════════════════════════════════════════════════ --}}
<section class="py-5" style="background:#fff;">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0" style="color:#1a3a52; font-size:1.4rem;">
                <i class="fas fa-pen-fancy me-2" style="color:#9b59b6;"></i>শীর্ষ লেখকগণ
            </h2>
            <a href="{{ route('authors.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">সব দেখুন →</a>
        </div>
        <div class="row g-3">
            @forelse($authors as $author)
            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                <a href="{{ route('author.show', $author->slug ?? $author->id) }}" class="text-decoration-none">
                    <div class="text-center p-3 rounded-3" style="background:#f8f5fc; transition:all .2s;"
                       >
                        <div class="rounded-circle bg-white border d-inline-flex align-items-center justify-content-center mb-2 shadow-sm"
                            style="width:56px;height:56px;">
                            <span style="font-size:1.6rem;">✍️</span>
                        </div>
                        <p class="fw-semibold mb-0 text-truncate" style="color:#1a3a52; font-size:.85rem;">{{ $author->name }}</p>
                        <p style="color:#9b59b6; font-size:.75rem;" class="mb-0">লেখক</p>
                    </div>
                </a>
            </div>
            @empty
            @foreach(['রবীন্দ্রনাথ ঠাকুর','কাজী নজরুল','মানিক বন্দ্যো.','বিভূতিভূষণ','হুমায়ুন আহমেদ','সুনীল গঙ্গো.'] as $a)
            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                <div class="text-center p-3 rounded-3 author-card" style="background:#f8f5fc;">
                    <div class="rounded-circle bg-white border d-inline-flex align-items-center justify-content-center mb-2 shadow-sm" style="width:56px;height:56px;">
                        <span style="font-size:1.6rem;">✍️</span>
                    </div>
                    <p class="fw-semibold mb-0 text-truncate" style="color:#1a3a52; font-size:.85rem;">{{ $a }}</p>
                    <p style="color:#9b59b6; font-size:.75rem;" class="mb-0">লেখক</p>
                </div>
            </div>
            @endforeach
            @endforelse
        </div>
    </div>
</section>

{{-- ══ KIDS ZONE + EBOOK ════════════════════════════════════════════════════ --}}
<section class="py-5" style="background:#f8fbfd;">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="rounded-3 p-4 h-100 d-flex align-items-center gap-4" style="background:linear-gradient(135deg,#fff9e6,#ffeebb);">
                    <div style="font-size:4rem; flex-shrink:0;">👶</div>
                    <div>
                        <h4 class="fw-bold mb-1" style="color:#7b6000;">কিডস জোন</h4>
                        <p style="color:#856404; font-size:.88rem; margin-bottom:1rem; line-height:1.6;">
                            ৫টি বয়স গ্রুপের জন্য বিশেষভাবে বাছাই করা বই।
                        </p>
                        <a href="{{ route('book.index') }}" class="btn btn-sm rounded-pill fw-semibold px-4" style="background:#f0ad00; color:#fff; border:none;">শিশুদের বই →</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="rounded-3 p-4 h-100 d-flex align-items-center gap-4" style="background:linear-gradient(135deg,#e8f4ff,#cce4ff);">
                    <div style="font-size:4rem; flex-shrink:0;">📲</div>
                    <div>
                        <h4 class="fw-bold mb-1" style="color:#0a3d62;">ডিজিটাল পাঠ</h4>
                        <p style="color:#1a5276; font-size:.88rem; margin-bottom:1rem; line-height:1.6;">
                            EPUB ও PDF ফরম্যাটে ই-বুক — যেকোনো সময়।
                        </p>
                        <a href="{{ route('ebook.index') }}" class="btn btn-sm rounded-pill fw-semibold px-4" style="background:#0066cc; color:#fff; border:none;">ই-বুক দেখুন →</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ══ NEWSLETTER CTA ════════════════════════════════════════════════════════ --}}
<section style="background:linear-gradient(135deg,#0066cc,#0099ff); padding:48px 0;">
    <div class="container text-center text-white">
        <h3 class="fw-bold mb-2">নতুন বই ও অফার প্রথমে পান</h3>
        <p class="mb-4 opacity-75">আমাদের নিউজলেটারে সাবস্ক্রাইব করুন — স্প্যাম নেই।</p>
        <form action="{{ route('newsletter.subscribe') }}" method="POST" class="d-flex justify-content-center gap-2" style="max-width:420px; margin:0 auto;">
            @csrf
            <input type="email" name="email" class="form-control rounded-pill border-0 px-4"
                placeholder="আপনার ইমেইল ঠিকানা..." required style="max-width:280px;">
            <button type="submit" class="btn fw-semibold rounded-pill px-4"
                style="background:#fff; color:#0066cc; white-space:nowrap;">সাবস্ক্রাইব করুন</button>
        </form>
    </div>
</section>

@endsection


