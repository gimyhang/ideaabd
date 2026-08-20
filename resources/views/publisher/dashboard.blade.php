@extends('layouts.app')

@section('title', 'পাবলিশার ড্যাশবোর্ড ও ক্যাটালগ পোর্টাল — ' . ($publisher->name ?? 'আইডিয়া প্রকাশন'))

@section('content')
<div class="container-fluid py-4 px-md-4" style="max-width: 1400px;">
    
    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4 rounded-3 shadow-xs" role="alert">
            <i class="fas fa-circle-check fs-5 me-2 text-success"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4 rounded-3 shadow-xs" role="alert">
            <i class="fas fa-triangle-exclamation fs-5 me-2 text-danger"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- 1. PUBLISHER HEADER BANNER                                                --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 text-white overflow-hidden" 
         style="background: linear-gradient(135deg, #064e3b 0%, #047857 50%, #059669 100%);">
        <div class="card-body p-4 p-md-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="position-relative flex-shrink-0 bg-white rounded-circle p-1 shadow-sm d-flex align-items-center justify-content-center" 
                     style="width: 72px; height: 72px;">
                    @if($publisher->logo)
                        <img src="{{ str_starts_with($publisher->logo, 'http') ? $publisher->logo : asset('storage/' . ltrim($publisher->logo, '/')) }}" 
                             alt="{{ $publisher->name }}" class="w-100 h-100 rounded-circle object-fit-cover">
                    @else
                        <div class="w-100 h-100 rounded-circle bg-success-subtle text-success fw-bold fs-3 d-flex align-items-center justify-content-center">
                            {{ mb_substr($publisher->name, 0, 1) }}
                        </div>
                    @endif
                    <span class="position-absolute bottom-0 end-0 bg-warning text-dark p-1 rounded-circle shadow-xs" title="অনুমোদিত ও ভেরিফাইড পাবলিশার" style="font-size: 10px; width: 22px; height: 22px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-check"></i>
                    </span>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <h4 class="fw-bold mb-0 text-white">{{ $publisher->name }}</h4>
                        <span class="badge bg-white bg-opacity-25 rounded-pill small px-2.5 py-0.5">পাবলিশার ড্যাশবোর্ড</span>
                    </div>
                    <div class="small opacity-90 text-light mt-1 d-flex flex-wrap align-items-center gap-3">
                        @if($publisher->phone)
                            <span><i class="fas fa-phone me-1"></i>{{ $publisher->phone }}</span>
                        @endif
                        @if($publisher->email)
                            <span><i class="fas fa-envelope me-1"></i>{{ $publisher->email }}</span>
                        @endif
                        @if($publisher->address)
                            <span><i class="fas fa-location-dot me-1"></i>{{ $publisher->address }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <button type="button" class="btn btn-warning text-dark rounded-pill px-4 py-2 fw-bold shadow-sm" onclick="switchPublisherTab('add-book')">
                    <i class="fas fa-plus-circle me-1.5"></i> নতুন বই এন্ট্রি দিন
                </button>
                <a href="{{ route('publishers.show', $publisher->slug ?? $publisher->id) }}" target="_blank" class="btn btn-outline-light rounded-pill px-3 py-2 fw-semibold">
                    <i class="fas fa-arrow-up-right-from-square me-1.5"></i> পাবলিক পেজ
                </a>
            </div>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- 2. KPI METRICS STRIP                                                      --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">মোট প্রকাশিত বই</span>
                        <h3 class="fw-bold mb-0 text-primary">@bn($totalBooks) <small class="fs-6 text-muted">টি</small></h3>
                    </div>
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3"><i class="fas fa-book fs-4"></i></div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">শপে সক্রিয় ও লাইভ</span>
                        <h3 class="fw-bold mb-0 text-success">@bn($activeBooks) <small class="fs-6 text-muted">টি</small></h3>
                    </div>
                    <div class="rounded-circle bg-success bg-opacity-10 text-success p-3"><i class="fas fa-circle-check fs-4"></i></div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-info h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">মোট ইনভেন্টরি স্টক</span>
                        <h3 class="fw-bold mb-0 text-info">@bn($totalStockUnits) <small class="fs-6 text-muted">কপি</small></h3>
                    </div>
                    <div class="rounded-circle bg-info bg-opacity-10 text-info p-3"><i class="fas fa-boxes-stacked fs-4"></i></div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-warning h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">পরিশোধকৃত / সেটেলমেন্ট</span>
                        <h3 class="fw-bold mb-0 text-warning">@taka($totalPaidAmount)</h3>
                    </div>
                    <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3"><i class="fas fa-hand-holding-dollar fs-4"></i></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- 3. INTERACTIVE NAVIGATION TABS                                            --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <ul class="nav nav-pills bg-white p-1.5 rounded-pill shadow-xs border mb-4 d-inline-flex" id="publisherTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ request('tab', 'books') === 'books' && !$editBook ? 'active' : '' }} rounded-pill px-4 py-2 fw-semibold" 
                    id="tab-books-btn" data-bs-toggle="pill" data-bs-target="#tab-books" type="button" role="tab">
                <i class="fas fa-book-open me-1.5"></i> আমার বই ক্যাটালগ (@bn($totalBooks))
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ request('tab') === 'add-book' || $editBook ? 'active' : '' }} rounded-pill px-4 py-2 fw-semibold" 
                    id="tab-add-book-btn" data-bs-toggle="pill" data-bs-target="#tab-add-book" type="button" role="tab">
                <i class="fas {{ $editBook ? 'fa-pen-to-square text-warning' : 'fa-plus-circle text-success' }} me-1.5"></i> 
                {{ $editBook ? 'বই সম্পাদনা করুন' : 'নতুন বই এন্ট্রি' }}
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ request('tab') === 'orders' ? 'active' : '' }} rounded-pill px-4 py-2 fw-semibold" 
                    id="tab-orders-btn" data-bs-toggle="pill" data-bs-target="#tab-orders" type="button" role="tab">
                <i class="fas fa-file-invoice-dollar me-1.5"></i> পারচেজ অর্ডার ও বিল (@bn($purchases->count()))
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ request('tab') === 'settings' ? 'active' : '' }} rounded-pill px-4 py-2 fw-semibold" 
                    id="tab-settings-btn" data-bs-toggle="pill" data-bs-target="#tab-settings" type="button" role="tab">
                <i class="fas fa-gear me-1.5"></i> প্রকাশনী প্রোফাইল
            </button>
        </li>
    </ul>

    <div class="tab-content" id="publisherTabsContent">

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- TAB 1: BOOKS CATALOG LISTING & FILTERING                          --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        <div class="tab-pane fade {{ request('tab', 'books') === 'books' && !$editBook ? 'show active' : '' }}" id="tab-books" role="tabpanel">
            
            {{-- Search & Filter Toolbar --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                <div class="card-body p-3">
                    <form action="{{ route('publisher.dashboard') }}" method="GET" id="pubBooksFilterForm" class="row g-2 align-items-center">
                        <input type="hidden" name="tab" value="books">

                        <!-- Search Bar -->
                        <div class="col-12 col-md-5">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-search"></i></span>
                                <input type="text" name="search" id="pubSearchInput" value="{{ request('search') }}" 
                                       class="form-control border-start-0 ps-0" placeholder="বইয়ের নাম, লেখক, ISBN দিয়ে খুঁজুন..." autocomplete="off">
                                @if(request('search'))
                                    <a href="{{ route('publisher.dashboard', ['tab' => 'books']) }}" class="input-group-text bg-light border-start-0 text-muted hover-danger">
                                        <i class="fas fa-times"></i>
                                    </a>
                                @endif
                                <button type="submit" class="btn btn-success px-3 fw-semibold">খুঁজুন</button>
                            </div>
                        </div>

                        <!-- Category Filter -->
                        <div class="col-6 col-md-3">
                            <select name="category_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">— সকল ক্যাটাগরি —</option>
                                @foreach($categories as $cId => $cName)
                                    <option value="{{ $cId }}" @selected(request('category_id') == $cId)>{{ $cName }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Stock Status -->
                        <div class="col-6 col-md-2">
                            <select name="stock" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">— সকল স্টক —</option>
                                <option value="in_stock" @selected(request('stock') === 'in_stock')>🟢 ইন-স্টক</option>
                                <option value="low" @selected(request('stock') === 'low')>🟡 লো-স্টক (&le;৫)</option>
                                <option value="out" @selected(request('stock') === 'out')>🔴 স্টক শেষ (০)</option>
                                <option value="pre_order" @selected(request('stock') === 'pre_order')>⏳ প্রি-অর্ডার</option>
                            </select>
                        </div>

                        <!-- Sort By -->
                        <div class="col-6 col-md-2">
                            <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="latest" @selected(request('sort') === 'latest' || !request('sort'))>নতুন বই প্রথমে</option>
                                <option value="oldest" @selected(request('sort') === 'oldest')>পুরাতন বই প্রথমে</option>
                                <option value="price_low" @selected(request('sort') === 'price_low')>মূল্য: কম থেকে বেশি</option>
                                <option value="price_high" @selected(request('sort') === 'price_high')>মূল্য: বেশি থেকে কম</option>
                                <option value="stock_high" @selected(request('sort') === 'stock_high')>স্টক: বেশি থেকে কম</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Books Table --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                @if($books->isEmpty())
                    <div class="text-center py-5">
                        <i class="fas fa-book-open fs-1 text-muted opacity-50 mb-3"></i>
                        <h5 class="fw-bold text-dark mb-1">কোনো বই পাওয়া যায়নি</h5>
                        <p class="text-muted small mb-3">আপনার প্রকাশনীর নতুন বই যুক্ত করতে নিচের বাটনে ক্লিক করুন।</p>
                        <button type="button" class="btn btn-success rounded-pill px-4 fw-semibold" onclick="switchPublisherTab('add-book')">
                            <i class="fas fa-plus me-1"></i> নতুন বই এন্ট্রি দিন
                        </button>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="pubBooksTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>বইয়ের নাম ও কভার</th>
                                    <th>লেখক / রচয়িতা</th>
                                    <th>ক্যাটাগরি</th>
                                    <th>মুদ্রিত মূল্য (MRP)</th>
                                    <th>বিক্রয় মূল্য</th>
                                    <th>পাইকারি দর (Cost)</th>
                                    <th>স্টক</th>
                                    <th>অবস্থা</th>
                                    <th class="text-end pe-3" style="min-width: 140px;">অ্যাকশন</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($books as $idx => $b)
                                    @php
                                        $cover = $b->cover_image;
                                        $coverUrl = $cover 
                                            ? (str_starts_with($cover, 'http') ? $cover : (str_starts_with($cover, 'storage/') ? asset($cover) : asset('storage/' . ltrim($cover, '/'))))
                                            : 'https://placehold.co/100x150/e2e8f0/475569?text=Cover';
                                    @endphp
                                    <tr id="pubBookRow_{{ $b->id }}">
                                        <td class="ps-3 text-muted small">@bn($books->firstItem() + $idx)</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2.5">
                                                <img src="{{ $coverUrl }}" alt="{{ $b->title }}" class="rounded border shadow-xs flex-shrink-0" style="width: 40px; height: 56px; object-fit: cover;">
                                                <div style="max-width: 220px;">
                                                    <a href="{{ route('book.show', $b->slug ?? $b->id) }}" target="_blank" class="fw-bold text-dark text-decoration-none hover-primary d-block text-truncate mb-0.5">
                                                        {{ $b->title }}
                                                    </a>
                                                    @if($b->edition)
                                                        <span class="badge bg-light text-dark border py-0.5 px-1.5" style="font-size: 10px;">{{ $b->edition }}</span>
                                                    @endif
                                                    @if($b->isbn)
                                                        <small class="text-muted d-block" style="font-size: 10px;">ISBN: {{ $b->isbn }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-dark">{{ $b->author_name ?: ($b->authorLink?->name ?? '—') }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $b->category?->name ?? 'সাধারণ' }}</span>
                                        </td>
                                        <td class="fw-bold text-dark">
                                            @taka($b->price > 0 ? $b->price : $b->hardcover_price)
                                        </td>
                                        <td>
                                            @if($b->discount_price > 0)
                                                <span class="fw-bold text-success">@taka($b->discount_price)</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="fw-semibold text-success">
                                            @taka($b->cost_price ?? 0)
                                        </td>
                                        <td>
                                            @if($b->stock_status === 'pre_order')
                                                <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-0.5 rounded-pill">প্রি-অর্ডার</span>
                                            @elseif($b->stock_quantity <= 0)
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-0.5 rounded-pill">স্টক শেষ (০)</span>
                                            @elseif($b->stock_quantity <= 5)
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-0.5 rounded-pill">লো-স্টক (@bn($b->stock_quantity))</span>
                                            @else
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 rounded-pill">স্টক: @bn($b->stock_quantity)টি</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($b->is_active)
                                                <span class="badge bg-success text-white px-2 py-0.5 rounded-pill" style="font-size: 10px;"><i class="fas fa-check-circle me-0.5"></i> লাইভ</span>
                                            @else
                                                <span class="badge bg-secondary text-white px-2 py-0.5 rounded-pill" style="font-size: 10px;">খসড়া</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-3">
                                            <div class="d-flex align-items-center justify-content-end gap-1.5">
                                                <a href="{{ route('publisher.dashboard', ['tab' => 'add-book', 'edit_id' => $b->id]) }}" class="btn btn-sm btn-outline-primary px-2.5 py-1" title="সম্পাদনা করুন">
                                                    <i class="fas fa-pen-to-square"></i>
                                                </a>
                                                <a href="{{ route('book.show', $b->slug ?? $b->id) }}" target="_blank" class="btn btn-sm btn-light border px-2.5 py-1" title="শপে দেখুন">
                                                    <i class="fas fa-eye text-muted"></i>
                                                </a>
                                                <form action="{{ route('publisher.books.destroy', $b->id) }}" method="POST" class="d-inline" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই বইটি মুছে ফেলতে চান?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger px-2.5 py-1" title="মুছে ফেলুন">
                                                        <i class="fas fa-trash-can"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($books->hasPages())
                        <div class="p-3 border-top d-flex justify-content-between align-items-center bg-light">
                            <span class="small text-muted">
                                মোট @bn($books->total())টির মধ্যে @bn($books->firstItem())–@bn($books->lastItem()) দেখানো হচ্ছে
                            </span>
                            {{ $books->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- TAB 2: DYNAMIC BOOK ENTRY & EDIT FORM (বইয়ের এন্ট্রি)             --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        <div class="tab-pane fade {{ request('tab') === 'add-book' || $editBook ? 'show active' : '' }}" id="tab-add-book" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle {{ $editBook ? 'bg-warning bg-opacity-25 text-dark' : 'bg-success bg-opacity-10 text-success' }} p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                            <i class="fas {{ $editBook ? 'fa-pen-to-square' : 'fa-book-medical' }}"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">{{ $editBook ? "বই সম্পাদনা — {$editBook->title}" : "নতুন বইয়ের বিস্তারিত এন্ট্রি" }}</h5>
                            <span class="small text-muted" style="font-size: 0.78rem;">অনলাইন শপ ক্যাটালগ ও ইনভেন্টরির জন্য প্রয়োজনীয় তথ্য প্রদান করুন</span>
                        </div>
                    </div>
                    @if($editBook)
                        <a href="{{ route('publisher.dashboard', ['tab' => 'add-book']) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                            <i class="fas fa-plus me-1"></i> নতুন বই এন্ট্রিতে ফিরুন
                        </a>
                    @endif
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ $editBook ? route('publisher.books.update', $editBook->id) : route('publisher.books.store') }}" enctype="multipart/form-data" id="pubBookForm">
                        @csrf
                        @if($editBook)
                            @method('PUT')
                        @endif

                        <div class="row g-3">
                            
                            {{-- 1. Main Title & Subtitle --}}
                            <div class="col-md-8">
                                <label class="form-label fw-bold text-dark small mb-1">বইয়ের পূর্ণাঙ্গ শিরোনাম (Title) <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control rounded-3" value="{{ old('title', $editBook->title ?? '') }}" required placeholder="বইয়ের নাম লিখুন (যেমন: নিঃসঙ্গতার প্রহর)">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-dark small mb-1">উপ-শিরোনাম / বিষয় (Subtitle)</label>
                                <input type="text" name="subtitle" class="form-control rounded-3" value="{{ old('subtitle', $editBook->subtitle ?? '') }}" placeholder="যেমন: সমকালীন ছোটগল্প সংকলন">
                            </div>

                            {{-- 2. Category & Author --}}
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-dark small mb-1">বইয়ের ক্যাটাগরি / বিষয় <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-select rounded-3" required>
                                    <option value="">— ক্যাটাগরি নির্বাচন করুন —</option>
                                    @foreach($categories as $cId => $cName)
                                        <option value="{{ $cId }}" @selected(old('category_id', $editBook->category_id ?? '') == $cId)>{{ $cName }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold text-dark small mb-1">লেখক ডিরেক্টরি (Author Link)</label>
                                <select name="author_id" class="form-select rounded-3" id="pubAuthorSelect">
                                    <option value="">— তালিকা থেকে লেখক বেছে নিন —</option>
                                    @foreach($authors as $aId => $aName)
                                        <option value="{{ $aId }}" @selected(old('author_id', $editBook->author_link_id ?? '') == $aId)>{{ $aName }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold text-dark small mb-1">লেখকের নাম (যদি তালিকায় না থাকে)</label>
                                <input type="text" name="author_name" class="form-control rounded-3" value="{{ old('author_name', $editBook->author_name ?? '') }}" placeholder="লেখকের নাম লিখুন...">
                            </div>

                            {{-- 3. Translator & Editor --}}
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark mb-1">অনুবাদক (Translator - যদি থাকে)</label>
                                <input type="text" name="translator_name" class="form-control rounded-3" value="{{ old('translator_name', $editBook->translator_name ?? '') }}" placeholder="অনুবাদকের নাম...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark mb-1">সম্পাদক / সংকলক (Editor - যদি থাকে)</label>
                                <input type="text" name="editor_name" class="form-control rounded-3" value="{{ old('editor_name', $editBook->editor_name ?? '') }}" placeholder="সম্পাদকের নাম...">
                            </div>

                            {{-- 4. Binding & Pricing Card --}}
                            <div class="col-12">
                                <div class="card border rounded-3 p-3.5 bg-light">
                                    <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                                        <i class="fas fa-tags text-success"></i>
                                        <span>কভার সংস্করণ ও মূল্য নির্ধারণ</span>
                                    </h6>

                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold text-dark mb-1">বাঁধাই / কভার টাইপ <span class="text-danger">*</span></label>
                                            <select name="cover_type" id="pubCoverType" class="form-select rounded-3" required onchange="toggleCoverPricing()">
                                                <option value="paperback" @selected(old('cover_type', $editBook->cover_type ?? 'paperback') === 'paperback')>পেপারব্যাক (Paperback)</option>
                                                <option value="hardcover" @selected(old('cover_type', $editBook->cover_type ?? '') === 'hardcover')>হার্ডকভার (Hardcover)</option>
                                                <option value="both" @selected(old('cover_type', $editBook->cover_type ?? '') === 'both')>উভয় সংস্করণ (Both)</option>
                                            </select>
                                        </div>

                                        <div class="col-md-4" id="paperPriceBlock">
                                            <label class="form-label small fw-bold text-dark mb-1">পেপারব্যাক মুদ্রিত মূল্য (MRP ৳) <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">৳</span>
                                                <input type="number" name="price" id="pubPrice" value="{{ old('price', $editBook->price ?? '') }}" min="0" step="1" class="form-control fw-bold" placeholder="300">
                                            </div>
                                        </div>

                                        <div class="col-md-4" id="paperDiscountBlock">
                                            <label class="form-label small fw-bold text-dark mb-1">পেপারব্যাক বিক্রয় মূল্য (ছাড়ের পর ৳)</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">৳</span>
                                                <input type="number" name="discount_price" id="pubDiscountPrice" value="{{ old('discount_price', $editBook->discount_price ?? '') }}" min="0" step="1" class="form-control text-success fw-bold" placeholder="240">
                                            </div>
                                        </div>

                                        <div class="col-md-4" id="hardPriceBlock" style="display: none;">
                                            <label class="form-label small fw-bold text-dark mb-1">হার্ডকভার মুদ্রিত মূল্য (MRP ৳)</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">৳</span>
                                                <input type="number" name="hardcover_price" id="pubHardPrice" value="{{ old('hardcover_price', $editBook->hardcover_price ?? '') }}" min="0" step="1" class="form-control fw-bold" placeholder="450">
                                            </div>
                                        </div>

                                        <div class="col-md-4" id="hardDiscountBlock" style="display: none;">
                                            <label class="form-label small fw-bold text-dark mb-1">হার্ডকভার বিক্রয় মূল্য (৳)</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">৳</span>
                                                <input type="number" name="hardcover_discount_price" id="pubHardDiscountPrice" value="{{ old('hardcover_discount_price', $editBook->hardcover_discount_price ?? '') }}" min="0" step="1" class="form-control text-success fw-bold" placeholder="380">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold text-dark mb-1">পাইকারি সরবরাহ খরচ / দর (Wholesale Cost ৳)</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white text-success fw-bold">৳</span>
                                                <input type="number" name="cost_price" value="{{ old('cost_price', $editBook->cost_price ?? '') }}" min="0" step="1" class="form-control text-success fw-bold" placeholder="180">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- 5. Inventory & Stock --}}
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-dark mb-1">প্রাথমিক স্টক সংখ্যা (Quantity) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="stock_quantity" value="{{ old('stock_quantity', $editBook->stock_quantity ?? 50) }}" min="0" max="100000" class="form-control fw-bold" required>
                                    <span class="input-group-text">কপি</span>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-dark mb-1">স্টক অবস্থা (Stock Status) <span class="text-danger">*</span></label>
                                <select name="stock_status" class="form-select rounded-3" required>
                                    <option value="in_stock" @selected(old('stock_status', $editBook->stock_status ?? 'in_stock') === 'in_stock')>🟢 ইন-স্টক (In Stock)</option>
                                    <option value="low" @selected(old('stock_status', $editBook->stock_status ?? '') === 'low')>🟡 লো-স্টক (&le;৫)</option>
                                    <option value="out" @selected(old('stock_status', $editBook->stock_status ?? '') === 'out')>🔴 স্টক শেষ (Out of Stock)</option>
                                    <option value="pre_order" @selected(old('stock_status', $editBook->stock_status ?? '') === 'pre_order')>⏳ প্রি-অর্ডার চলছে</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-dark mb-1">সংস্করণ / প্রকাশ সাল (Edition)</label>
                                <input type="text" name="edition" class="form-control rounded-3" value="{{ old('edition', $editBook->edition ?? '১ম প্রকাশ ২০২৬') }}" placeholder="যেমন: ১ম প্রকাশ ২০২৬">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-dark mb-1">আইএসবিএন (ISBN) / বারকোড</label>
                                <input type="text" name="isbn" class="form-control rounded-3" value="{{ old('isbn', $editBook->isbn ?? '') }}" placeholder="978-984-...">
                            </div>

                            {{-- 6. Page count, Cover Image & PDF Sample --}}
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark mb-1">পৃষ্ঠা সংখ্যা (Number of Pages)</label>
                                <input type="number" name="number_of_pages" value="{{ old('number_of_pages', $editBook->number_of_pages ?? '') }}" min="1" class="form-control rounded-3" placeholder="যেমন: ১৬০">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark mb-1">বইয়ের কভার ছবি (Cover Image)</label>
                                <input type="file" name="cover_image" class="form-control rounded-3" accept="image/*" onchange="previewBookCover(this)">
                                @if($editBook && $editBook->cover_image)
                                    <div class="mt-2">
                                        <img src="{{ str_starts_with($editBook->cover_image, 'http') ? $editBook->cover_image : asset('storage/' . ltrim($editBook->cover_image, '/')) }}" 
                                             alt="Cover" id="pubCoverPreview" class="rounded border shadow-xs" style="max-height: 70px;">
                                    </div>
                                @else
                                    <div class="mt-2 d-none" id="pubCoverPreviewContainer">
                                        <img src="" alt="Cover" id="pubCoverPreview" class="rounded border shadow-xs" style="max-height: 70px;">
                                    </div>
                                @endif
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark mb-1">নমুনা পৃষ্ঠা (Sample Read PDF)</label>
                                <input type="file" name="pdf_sample" class="form-control rounded-3" accept=".pdf">
                                @if($editBook && $editBook->pdf_sample)
                                    <small class="text-success d-block mt-1"><i class="fas fa-check me-1"></i>পূর্বের PDF সংরক্ষিত রয়েছে</small>
                                @endif
                            </div>

                            {{-- 7. Summary & Description --}}
                            <div class="col-12">
                                <label class="form-label small fw-bold text-dark mb-1">সংক্ষিপ্ত সারসংক্ষেপ ও ব্লার্ব (Book Summary)</label>
                                <textarea name="summary" rows="2" class="form-control rounded-3" placeholder="বইয়ের মূল ভাব, ফ্লাপের লেখা বা সংক্ষিপ্ত পরিচয়...">{{ old('summary', $editBook->summary ?? '') }}</textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold text-dark mb-1">বিস্তারিত বিবরণ ও সূচিপত্র (Book Description & Table of Contents)</label>
                                <textarea name="description" rows="5" class="form-control rounded-3" placeholder="বইয়ের পূর্ণাঙ্গ আলোচনা, সূচিপত্র ও পর্যালোচনা...">{{ old('description', $editBook->description ?? '') }}</textarea>
                            </div>

                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-light border rounded-pill px-4" onclick="switchPublisherTab('books')">বাতিল</button>
                            <button type="submit" class="btn btn-success rounded-pill px-5 fw-bold shadow-sm">
                                <i class="fas fa-check-circle me-1.5"></i> {{ $editBook ? "বইয়ের তথ্য হালনাগাদ করুন" : "বইটি ক্যাটালগে প্রকাশ করুন" }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- TAB 3: PURCHASE ORDERS & BILLS (পারচেজ অর্ডার ও বিল)              --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        <div class="tab-pane fade {{ request('tab') === 'orders' ? 'show active' : '' }}" id="tab-orders" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">আইডিয়া প্রকাশন কর্তৃক ক্রয়াদেশ ও ইনভয়েসসমূহ</h5>
                        <span class="small text-muted" style="font-size: 0.78rem;">আপনার প্রকাশনী থেকে সাপ্লাই ও সেটেলমেন্ট রিপোর্ট</span>
                    </div>
                    <div class="text-end">
                        <span class="small text-muted d-block">মোট বকেয়া ব্যালেন্স:</span>
                        <h6 class="fw-bold text-danger mb-0">@taka($totalDueAmount)</h6>
                    </div>
                </div>

                <div class="card-body p-0">
                    @if($purchases->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-file-invoice-dollar fs-1 text-muted opacity-50 mb-3"></i>
                            <h6 class="fw-bold text-dark">এখনো কোনো পারচেজ অর্ডার বা ইনভয়েস ইস্যু হয়নি</h6>
                            <p class="small text-muted">আইডিয়া প্রকাশন থেকে বই ক্রয়াদেশ প্রদান করা হলে এখানে স্বয়ংক্রিয়ভাবে ইনভয়েস ও রসিদ যুক্ত হবে।</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">ইনভয়েস #</th>
                                        <th>তারিখ</th>
                                        <th>আইটেম সংখ্যা</th>
                                        <th>মোট বিল</th>
                                        <th>পরিশোধিত</th>
                                        <th>বকেয়া</th>
                                        <th>পেমেন্ট স্ট্যাটাস</th>
                                        <th class="text-end pe-3">রসিদ / প্রিন্ট</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchases as $p)
                                        @php
                                            $due = max(0, $p->total_amount - $p->paid_amount);
                                        @endphp
                                        <tr>
                                            <td class="ps-3 fw-bold text-dark">
                                                <span class="badge bg-light text-dark border">#{{ $p->invoice_number ?: $p->id }}</span>
                                            </td>
                                            <td class="small text-muted">{{ $p->purchase_date ? date('d M, Y', strtotime($p->purchase_date)) : '—' }}</td>
                                            <td>@bn($p->items_count ?? 1)টি</td>
                                            <td class="fw-bold text-dark">@taka($p->total_amount)</td>
                                            <td class="fw-bold text-success">@taka($p->paid_amount)</td>
                                            <td class="fw-bold text-danger">@taka($due)</td>
                                            <td>
                                                @if($p->payment_status === 'paid' || $due <= 0)
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">পরিশোধিত (Paid)</span>
                                                @elseif($p->paid_amount > 0)
                                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1 rounded-pill">আংশিক (Partial)</span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-pill">বকেয়া (Due)</span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-3">
                                                <a href="{{ route('admin.purchases.show', $p->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                                    <i class="fas fa-print me-1"></i> ইনভয়েস
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- TAB 4: PUBLISHER PROFILE & SETTINGS                               --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        <div class="tab-pane fade {{ request('tab') === 'settings' ? 'show active' : '' }}" id="tab-settings" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4" style="max-width: 800px;">
                <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                    <i class="fas fa-building text-success"></i>
                    <span>প্রকাশনীর প্রাতিষ্ঠানিক তথ্য ও প্রোফাইল</span>
                </h5>

                <form method="POST" action="{{ route('publisher.profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark mb-1">প্রকাশনীর নাম <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control rounded-3" value="{{ old('name', $publisher->name) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark mb-1">অফিসিয়াল মোবাইল / হটলাইন <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control rounded-3" value="{{ old('phone', $publisher->phone) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark mb-1">ইমেইল এড্রেস</label>
                            <input type="email" name="email" class="form-control rounded-3" value="{{ old('email', $publisher->email) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark mb-1">ওয়েবসাইট (Website)</label>
                            <input type="url" name="website" class="form-control rounded-3" value="{{ old('website', $publisher->website) }}" placeholder="https://...">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark mb-1">অফিস / শোরুমের পূর্ণাঙ্গ ঠিকানা</label>
                            <textarea name="address" rows="2" class="form-control rounded-3" placeholder="যেমন: ৩৮ বাংলাবাজার, ঢাকা">{{ old('address', $publisher->address) }}</textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark mb-1">প্রকাশনী পরিচিতি ও বিবরণ</label>
                            <textarea name="description" rows="3" class="form-control rounded-3" placeholder="প্রকাশনীর ইতিহাস ও পরিচিতি...">{{ old('description', $publisher->description) }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark mb-1">প্রকাশনীর অফিশিয়াল লোগো</label>
                            <input type="file" name="logo" class="form-control rounded-3" accept="image/*">
                        </div>
                    </div>

                    <div class="mt-4 pt-2 border-top d-flex justify-content-end">
                        <button type="submit" class="btn btn-success rounded-pill px-5 fw-bold shadow-sm">
                            <i class="fas fa-save me-1.5"></i> প্রোফাইল সংরক্ষণ করুন
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
function switchPublisherTab(tabName) {
    if (tabName === 'add-book') {
        const btn = document.getElementById('tab-add-book-btn');
        if (btn) btn.click();
    } else if (tabName === 'books') {
        const btn = document.getElementById('tab-books-btn');
        if (btn) btn.click();
    }
}

function toggleCoverPricing() {
    const cType = document.getElementById('pubCoverType')?.value || 'paperback';
    const paperBlock = document.getElementById('paperPriceBlock');
    const paperDiscBlock = document.getElementById('paperDiscountBlock');
    const hardBlock = document.getElementById('hardPriceBlock');
    const hardDiscBlock = document.getElementById('hardDiscountBlock');

    if (cType === 'both') {
        if (paperBlock) paperBlock.style.display = 'block';
        if (paperDiscBlock) paperDiscBlock.style.display = 'block';
        if (hardBlock) hardBlock.style.display = 'block';
        if (hardDiscBlock) hardDiscBlock.style.display = 'block';
    } else if (cType === 'hardcover') {
        if (paperBlock) paperBlock.style.display = 'none';
        if (paperDiscBlock) paperDiscBlock.style.display = 'none';
        if (hardBlock) hardBlock.style.display = 'block';
        if (hardDiscBlock) hardDiscBlock.style.display = 'block';
    } else {
        if (paperBlock) paperBlock.style.display = 'block';
        if (paperDiscBlock) paperDiscBlock.style.display = 'block';
        if (hardBlock) hardBlock.style.display = 'none';
        if (hardDiscBlock) hardDiscBlock.style.display = 'none';
    }
}

function previewBookCover(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('pubCoverPreview');
            const container = document.getElementById('pubCoverPreviewContainer');
            if (preview) {
                preview.src = e.target.result;
            }
            if (container) {
                container.classList.remove('d-none');
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    toggleCoverPricing();
});
</script>
@endpush

@endsection
