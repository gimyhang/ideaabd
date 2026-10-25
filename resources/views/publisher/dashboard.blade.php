@extends('layouts.app')

@section('title', 'ড্যাশবোর্ড — ' . $publisher->name)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/seller-panel.css') }}?v={{ @filemtime(public_path('css/seller-panel.css')) ?: 1 }}">
<link rel="stylesheet" href="{{ asset('css/publisher-panel.css') }}?v={{ @filemtime(public_path('css/publisher-panel.css')) ?: 1 }}">
@endpush

@php
    $tk      = fn ($n, $d = 0) => '৳' . \App\Support\Bn::num($n, $d);
    $bn      = fn ($n) => \App\Support\Bn::num($n);
    $payTone = fn ($s) => match ($s) { 'paid' => ['success', 'পরিশোধিত'], 'partial' => ['warning', 'আংশিক'], default => ['danger', 'বকেয়া'] };
    $amountOf = fn ($p) => (float) ($p->grand_total > 0 ? $p->grand_total : $p->total_amount);
    $logo    = $publisher->logo ? (str_starts_with($publisher->logo, 'http') ? $publisher->logo : asset('storage/' . ltrim($publisher->logo, '/'))) : null;
    $tabs = [
        'overview'        => ['fa-chart-pie',          'সারসংক্ষেপ', null],
        'today-purchases' => ['fa-truck-fast',         'অর্ডার',     $purchaseStats->orders ?: null],
        'books'           => ['fa-book-open',          'বই',         $bookStats->total ?: null],
        'add-book'        => [$editBook ? 'fa-pen' : 'fa-plus', $editBook ? 'বই সম্পাদনা' : 'নতুন বই', null],
        'orders'          => ['fa-file-invoice-dollar', 'বিল ও হিসাব', null],
        'settings'        => ['fa-gear',               'প্রোফাইল',   null],
    ];
    $pane = fn ($key) => $activeTab === $key ? 'show active' : '';
@endphp

@section('content')
<div class="container-fluid py-4 px-md-4" style="max-width: 1280px;">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="বন্ধ"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="বন্ধ"></button></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <strong>ফর্মে সমস্যা আছে:</strong>
            <ul class="mb-0 ps-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- Header + tabs --}}
    <header class="sp-head">
        <div class="sp-head-top">
            @if($logo)
                <img src="{{ $logo }}" alt="{{ $publisher->name }}" class="rounded-circle border flex-shrink-0" style="width:52px;height:52px;object-fit:cover">
            @else
                <div class="sp-logo fw-bold">{{ mb_substr($publisher->name, 0, 1) }}</div>
            @endif
            <div class="me-auto min-w-0">
                <h1 class="sp-shop text-truncate">{{ $publisher->name }}</h1>
                <div class="sp-meta">
                    @if($publisher->phone)<span><i class="fas fa-phone me-1"></i>{{ $publisher->phone }}</span>@endif
                    @if($publisher->email)<span><i class="fas fa-envelope me-1"></i>{{ $publisher->email }}</span>@endif
                    @if($publisher->address)<span class="d-none d-sm-inline"><i class="fas fa-location-dot me-1"></i>{{ \Illuminate\Support\Str::limit($publisher->address, 40) }}</span>@endif
                </div>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                @if($isAdmin && count($publishers) > 1)
                    <form method="GET" action="{{ route('publisher.dashboard') }}">
                        <select name="publisher_id" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="প্রকাশক বেছে নিন">
                            @foreach($publishers as $id => $name)
                                <option value="{{ $id }}" @selected($id === $publisher->id)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </form>
                @endif
                <a href="{{ route('publishers.show', $publisher->slug ?? $publisher->id) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill px-3"><i class="fas fa-arrow-up-right-from-square me-1"></i>পাবলিক পেজ</a>
                <a href="{{ route('my-account') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3"><i class="fas fa-user me-1"></i>আমার অ্যাকাউন্ট</a>
                <button type="button" class="btn btn-sm btn-success rounded-pill px-3" onclick="switchPublisherTab('add-book')"><i class="fas fa-plus me-1"></i>নতুন বই</button>
            </div>
        </div>

        <nav class="sp-nav" id="publisherTabs" role="tablist" aria-label="প্রকাশক মেনু">
            @foreach($tabs as $key => [$icon, $label, $count])
                <button type="button" role="tab" id="tab-{{ $key }}-btn"
                        class="{{ $activeTab === $key ? 'active' : '' }}"
                        data-bs-toggle="pill" data-bs-target="#tab-{{ $key }}" aria-selected="{{ $activeTab === $key ? 'true' : 'false' }}">
                    <i class="fas {{ $icon }}"></i>{{ $label }}@if($count)<span class="sp-count">{{ $bn($count) }}</span>@endif
                </button>
            @endforeach
        </nav>
    </header>

    {{-- Numbers --}}
    <div class="sp-grid">
        <div class="sp-tile"><span class="sp-label">মোট বই</span><span class="sp-value">@bn($bookStats->total)</span><span class="sp-sub">প্রকাশিত @bn($bookStats->active)টি</span></div>
        <div class="sp-tile"><span class="sp-label">স্টকে কপি</span><span class="sp-value">@bn($bookStats->units)</span><span class="sp-sub">সব বই মিলিয়ে</span></div>
        <div class="sp-tile">
            <span class="sp-label">কম বা শেষ স্টক</span>
            <span class="sp-value {{ ($bookStats->low + $bookStats->out_of_stock) > 0 ? 'text-danger' : '' }}">@bn($bookStats->low + $bookStats->out_of_stock)</span>
            <span class="sp-sub">কম @bn($bookStats->low) · শেষ @bn($bookStats->out_of_stock)</span>
        </div>
        <div class="sp-tile"><span class="sp-label">মোট ক্রয়</span><span class="sp-value">{{ $tk($purchaseStats->total) }}</span><span class="sp-sub">@bn($purchaseStats->orders)টি অর্ডার</span></div>
        <div class="sp-tile"><span class="sp-label">পরিশোধিত</span><span class="sp-value text-success">{{ $tk($purchaseStats->paid) }}</span><span class="sp-sub">আজকের অর্ডার {{ $tk($purchaseStats->today_amount) }}</span></div>
        <div class="sp-tile"><span class="sp-label">বকেয়া</span><span class="sp-value {{ $purchaseStats->due > 0 ? 'text-danger' : '' }}">{{ $tk($purchaseStats->due) }}</span><span class="sp-sub">{{ $purchaseStats->due > 0 ? 'পাওনা আছে' : 'কোনো বকেয়া নেই' }}</span></div>
    </div>

    <div class="tab-content" id="publisherTabsContent">

        {{-- ───────── সারসংক্ষেপ ───────── --}}
        <div class="tab-pane fade {{ $pane('overview') }}" id="tab-overview" role="tabpanel">
            <div class="row g-3">
                <div class="col-12 col-lg-8">
                    <section class="sp-card h-100">
                        <div class="sp-card-head">
                            <h2>সাম্প্রতিক অর্ডার</h2>
                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" onclick="switchPublisherTab('orders')">সব দেখুন →</button>
                        </div>
                        @include('publisher.partials.purchase-table', ['rows' => $recentPurchases, 'empty' => 'এখনো কোনো অর্ডার নেই। শোরুম থেকে অর্ডার দিলে এখানে চালানসহ দেখা যাবে।'])
                    </section>
                </div>

                <div class="col-12 col-lg-4 d-flex flex-column gap-3">
                    <section class="sp-card">
                        <div class="sp-card-head"><h2>হিসাব</h2></div>
                        <div class="sp-book"><span class="text-muted">মোট ক্রয়</span><strong>{{ $tk($purchaseStats->total, 2) }}</strong></div>
                        <div class="sp-book"><span class="text-muted">পরিশোধিত</span><strong class="text-success">{{ $tk($purchaseStats->paid, 2) }}</strong></div>
                        <div class="sp-book"><span class="fw-semibold">বকেয়া</span><strong class="{{ $purchaseStats->due > 0 ? 'text-danger' : '' }}">{{ $tk($purchaseStats->due, 2) }}</strong></div>
                    </section>

                    <section class="sp-card">
                        <div class="sp-card-head">
                            <h2>কম স্টকের বই</h2>
                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" onclick="switchPublisherTab('books')">সব বই →</button>
                        </div>
                        @forelse($lowStockItems as $item)
                            <div class="sp-book">
                                <div class="min-w-0">
                                    <div class="fw-semibold text-truncate">{{ $item->title }}</div>
                                    <small class="text-muted">{{ $tk($item->price ?: $item->hardcover_price) }}</small>
                                </div>
                                <span class="badge rounded-pill text-bg-{{ $item->stock_quantity <= 0 ? 'danger' : 'warning' }} flex-shrink-0">
                                    {{ $item->stock_quantity <= 0 ? 'স্টক নেই' : $bn($item->stock_quantity) . 'টি বাকি' }}
                                </span>
                            </div>
                        @empty
                            <div class="sp-empty"><i class="fas fa-circle-check text-success fs-4 d-block mb-1"></i>সব বইয়ের স্টক ঠিক আছে।</div>
                        @endforelse
                    </section>
                </div>
            </div>
        </div>

        {{-- ───────── অর্ডার (চালান) ───────── --}}
        <div class="tab-pane fade {{ $pane('today-purchases') }}" id="tab-today-purchases" role="tabpanel">
            <section class="sp-card">
                <div class="sp-card-head flex-wrap">
                    <h2>অর্ডার ও চালান</h2>
                    <form action="{{ route('publisher.dashboard') }}" method="GET" class="d-flex flex-wrap align-items-center gap-2">
                        <input type="hidden" name="tab" value="today-purchases">
                        <select name="date_filter" class="form-select form-select-sm w-auto" onchange="this.form.submit()" aria-label="তারিখ">
                            <option value="all" @selected($dateFilter === 'all')>সব</option>
                            <option value="today" @selected($dateFilter === 'today')>আজ</option>
                            <option value="yesterday" @selected($dateFilter === 'yesterday')>গতকাল</option>
                            <option value="custom" @selected($dateFilter === 'custom')>নির্দিষ্ট তারিখ</option>
                        </select>
                        @if($dateFilter === 'custom')
                            <input type="date" name="date" value="{{ $selectedDate }}" class="form-control form-control-sm w-auto" onchange="this.form.submit()">
                        @endif
                        <span class="text-muted small">@bn($dayList->total())টি অর্ডার</span>
                    </form>
                </div>
                @include('publisher.partials.purchase-table', ['rows' => $dayList, 'empty' => 'এই তারিখে কোনো অর্ডার নেই।', 'items' => true])
                @if($dayList->hasPages())<div class="p-3 border-top">{{ $dayList->links() }}</div>@endif
            </section>
        </div>

        {{-- ───────── বই ───────── --}}
        <div class="tab-pane fade {{ $pane('books') }}" id="tab-books" role="tabpanel">
            <section class="sp-card">
                <form action="{{ route('publisher.dashboard') }}" method="GET" id="pubBooksFilterForm" class="sp-filters">
                    <input type="hidden" name="tab" value="books">
                    <input type="search" name="search" id="pubSearchInput" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="নাম, লেখক, ISBN, SKU" autocomplete="off" aria-label="বই খুঁজুন">
                    <select name="category_id" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="বিভাগ">
                        <option value="">সব বিভাগ</option>
                        @foreach($categories as $cId => $cName)
                            <option value="{{ $cId }}" @selected((int) request('category_id') === $cId)>{{ $cName }}</option>
                        @endforeach
                    </select>
                    <select name="stock" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="স্টক">
                        <option value="">সব স্টক</option>
                        <option value="in_stock" @selected(request('stock') === 'in_stock')>স্টকে আছে</option>
                        <option value="low" @selected(request('stock') === 'low')>কম (৫ বা কম)</option>
                        <option value="out" @selected(request('stock') === 'out')>শেষ</option>
                        <option value="pre_order" @selected(request('stock') === 'pre_order')>প্রি-অর্ডার</option>
                    </select>
                    <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="সাজান">
                        @if($isIdeaPub)
                            <option value="idea_serial_asc" @selected($sort === 'idea_serial_asc')>সিরিয়াল (ছোট থেকে)</option>
                            <option value="idea_serial_desc" @selected($sort === 'idea_serial_desc')>সিরিয়াল (বড় থেকে)</option>
                        @endif
                        <option value="latest" @selected($sort === 'latest')>নতুন আগে</option>
                        <option value="oldest" @selected($sort === 'oldest')>পুরনো আগে</option>
                        <option value="title_asc" @selected($sort === 'title_asc')>নাম (ক–হ)</option>
                        <option value="price_low" @selected($sort === 'price_low')>দাম কম থেকে</option>
                        <option value="price_high" @selected($sort === 'price_high')>দাম বেশি থেকে</option>
                        <option value="stock_low" @selected($sort === 'stock_low')>স্টক কম থেকে</option>
                        <option value="stock_high" @selected($sort === 'stock_high')>স্টক বেশি থেকে</option>
                    </select>
                    <button class="btn btn-sm btn-success rounded-pill px-3" type="submit">খুঁজুন</button>
                    @if(request()->hasAny(['search', 'category_id', 'stock']))
                        <a href="{{ route('publisher.dashboard', ['tab' => 'books']) }}" class="btn btn-sm btn-link text-muted">মুছুন</a>
                    @endif
                </form>

                @if($books->isEmpty())
                    <div class="sp-empty">
                        <i class="fas fa-book-open fs-3 d-block mb-2 opacity-50"></i>কোনো বই পাওয়া যায়নি।
                        <div class="mt-2"><button type="button" class="btn btn-sm btn-success rounded-pill px-3" onclick="switchPublisherTab('add-book')">প্রথম বই যোগ করুন</button></div>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table sp-table align-middle mb-0" id="pubBooksTable">
                            <thead>
                                <tr><th>বই</th><th>লেখক</th><th class="text-end">দাম</th><th class="text-end">ক্রয়মূল্য</th><th>স্টক</th><th>অবস্থা</th><th class="text-end">অ্যাকশন</th></tr>
                            </thead>
                            <tbody>
                                @foreach($books as $b)
                                    @php
                                        $cover = $b->cover_image;
                                        $coverUrl = $cover ? (str_starts_with($cover, 'http') ? $cover : (str_starts_with($cover, 'storage/') ? asset($cover) : asset('storage/' . ltrim($cover, '/')))) : null;
                                        $price = $b->price > 0 ? $b->price : $b->hardcover_price;
                                    @endphp
                                    <tr id="pubBookRow_{{ $b->id }}">
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                @if($coverUrl)
                                                    <img src="{{ $coverUrl }}" alt="" loading="lazy" class="rounded border flex-shrink-0" style="width:38px;height:54px;object-fit:cover">
                                                @else
                                                    <div class="rounded border bg-light flex-shrink-0 d-flex align-items-center justify-content-center text-muted" style="width:38px;height:54px"><i class="fas fa-book"></i></div>
                                                @endif
                                                <div class="min-w-0" style="max-width:260px">
                                                    <a href="{{ route('book.show', $b->slug ?? $b->id) }}" target="_blank" rel="noopener" class="fw-semibold text-decoration-none text-truncate d-block">{{ $b->title }}</a>
                                                    <small class="text-muted font-monospace">
                                                        @if($b->idea_serial_no)★ {{ $b->idea_serial_no }} · @endif{{ $b->sku }}@if($b->edition) · {{ $b->edition }}@endif
                                                    </small>
                                                    <small class="d-block text-muted">{{ $b->category?->name ?? 'সাধারণ' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $b->author_name ?: ($b->authorLink?->name ?? '—') }}</td>
                                        <td class="text-end text-nowrap">
                                            {{ $tk($price) }}
                                            @if($b->discount_price > 0)<small class="d-block text-success">বিক্রয় {{ $tk($b->discount_price) }}</small>@endif
                                        </td>
                                        <td class="text-end text-muted">{{ $tk($b->cost_price ?? 0) }}</td>
                                        <td>
                                            @if($b->stock_status === 'pre_order')
                                                <span class="badge rounded-pill text-bg-info">প্রি-অর্ডার</span>
                                            @elseif($b->stock_quantity <= 0)
                                                <span class="badge rounded-pill text-bg-danger">শেষ</span>
                                            @elseif($b->stock_quantity <= 5)
                                                <span class="badge rounded-pill text-bg-warning">কম · @bn($b->stock_quantity)</span>
                                            @else
                                                <span class="badge rounded-pill text-bg-success">@bn($b->stock_quantity)টি</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($b->mod_status === 'pending')
                                                <span class="badge rounded-pill text-bg-warning">অপেক্ষমান</span>
                                            @elseif($b->mod_status === 'rejected')
                                                <span class="badge rounded-pill text-bg-danger" title="{{ $b->rejection_reason ?? 'সংশোধন প্রয়োজন' }}">সংশোধন প্রয়োজন</span>
                                            @elseif($b->is_active)
                                                <span class="badge rounded-pill text-bg-success">প্রকাশিত</span>
                                            @else
                                                <span class="badge rounded-pill text-bg-secondary">খসড়া</span>
                                            @endif
                                        </td>
                                        <td class="text-end text-nowrap">
                                            <a href="{{ route('publisher.dashboard', ['tab' => 'add-book', 'edit_id' => $b->id]) }}" class="btn btn-sm btn-light rounded-circle" title="সম্পাদনা"><i class="fas fa-pen"></i></a>
                                            <form action="{{ route('publisher.books.destroy', $b->id) }}" method="POST" class="d-inline" onsubmit="return confirm('বইটি তালিকা থেকে মুছে ফেলবেন?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" title="মুছুন"><i class="fas fa-trash-can"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($books->hasPages())
                        <div class="p-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <span class="small text-muted">@bn($books->firstItem())–@bn($books->lastItem()) / @bn($books->total())</span>
                            {{ $books->links() }}
                        </div>
                    @endif
                @endif
            </section>
        </div>

        {{-- ───────── নতুন বই / সম্পাদনা ───────── --}}
        @include('publisher.partials.book-form')

        {{-- ───────── বিল ও হিসাব ───────── --}}
        <div class="tab-pane fade {{ $pane('orders') }}" id="tab-orders" role="tabpanel">
            <div class="row g-3">
                <div class="col-12 col-xl-8">
                    <section class="sp-card">
                        <div class="sp-card-head">
                            <h2>বিল ও চালান</h2>
                            <span class="small text-muted">বকেয়া <strong class="{{ $purchaseStats->due > 0 ? 'text-danger' : '' }}">{{ $tk($purchaseStats->due, 2) }}</strong></span>
                        </div>
                        @include('publisher.partials.purchase-table', ['rows' => $allPurchases, 'empty' => 'এখনো কোনো বিল নেই।', 'ledger' => true])
                        @if($allPurchases->hasPages())<div class="p-3 border-top">{{ $allPurchases->links() }}</div>@endif
                    </section>
                </div>
                <div class="col-12 col-xl-4">
                    <section class="sp-card">
                        <div class="sp-card-head"><h2>সাম্প্রতিক পেমেন্ট</h2></div>
                        @forelse($payments as $pay)
                            <div class="sp-book">
                                <div class="min-w-0">
                                    <div class="fw-semibold">{{ $tk($pay->amount, 2) }}</div>
                                    <small class="text-muted">{{ $pay->payment_date?->format('d M Y') }}@if($pay->payment_method) · {{ ucfirst($pay->payment_method) }}@endif</small>
                                </div>
                                @if($pay->payment_no)<span class="badge text-bg-light border font-monospace flex-shrink-0">{{ $pay->payment_no }}</span>@endif
                            </div>
                        @empty
                            <div class="sp-empty">এখনো কোনো পেমেন্ট নেই।</div>
                        @endforelse
                    </section>
                </div>
            </div>
        </div>

        {{-- ───────── প্রোফাইল ───────── --}}
        <div class="tab-pane fade {{ $pane('settings') }}" id="tab-settings" role="tabpanel">
            <section class="sp-card" style="max-width: 820px;">
                <div class="sp-card-head"><h2>প্রকাশনীর প্রোফাইল</h2></div>
                <form method="POST" action="{{ route('publisher.profile.update') }}" enctype="multipart/form-data" class="p-3 p-md-4">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold" for="pfName">নাম <span class="text-danger">*</span></label>
                            <input type="text" id="pfName" name="name" class="form-control" value="{{ old('name', $publisher->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold" for="pfPhone">ফোন <span class="text-danger">*</span></label>
                            <input type="text" id="pfPhone" name="phone" class="form-control" value="{{ old('phone', $publisher->phone) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold" for="pfEmail">ইমেইল</label>
                            <input type="email" id="pfEmail" name="email" class="form-control" value="{{ old('email', $publisher->email) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold" for="pfWeb">ওয়েবসাইট</label>
                            <input type="url" id="pfWeb" name="website" class="form-control" value="{{ old('website', $publisher->website) }}" placeholder="https://">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold" for="pfAddr">ঠিকানা</label>
                            <textarea id="pfAddr" name="address" rows="2" class="form-control">{{ old('address', $publisher->address) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold" for="pfDesc">পরিচিতি</label>
                            <textarea id="pfDesc" name="description" rows="3" class="form-control">{{ old('description', $publisher->description) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold" for="pfLogo">লোগো</label>
                            <input type="file" id="pfLogo" name="logo" class="form-control" accept="image/*">
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-top text-end">
                        <button type="submit" class="btn btn-success rounded-pill px-4"><i class="fas fa-floppy-disk me-1"></i>সংরক্ষণ</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</div>

@include('publisher.partials.modals-scripts')
@endsection
