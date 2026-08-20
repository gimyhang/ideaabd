@extends('layouts.app')

@section('title', 'আমার একাউন্ট ও অর্ডার ড্যাশবোর্ড — ' . ($user->name ?? 'ideaabd'))

@section('content')
<div class="container py-4 py-md-5" style="max-width: 1300px;">
    
    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4 rounded-3 shadow-xs d-flex align-items-center gap-2" role="alert">
            <i class="fas fa-circle-check fs-5 text-success"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4 rounded-3 shadow-xs d-flex align-items-center gap-2" role="alert">
            <i class="fas fa-triangle-exclamation fs-5 text-danger"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- 1. BUYER PROFILE & KPI HERO                                               --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 text-white overflow-hidden" 
         style="background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 50%, #0284c7 100%);">
        <div class="card-body p-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="position-relative flex-shrink-0 bg-white rounded-circle p-1 shadow-sm d-flex align-items-center justify-content-center" 
                     style="width: 72px; height: 72px;">
                    @if($user->avatar)
                        <img src="{{ str_starts_with($user->avatar, 'http') ? $user->avatar : asset('storage/' . ltrim($user->avatar, '/')) }}" 
                             alt="{{ $user->name }}" class="w-100 h-100 rounded-circle object-fit-cover">
                    @else
                        <div class="w-100 h-100 rounded-circle bg-primary-subtle text-primary fw-bold fs-3 d-flex align-items-center justify-content-center">
                            {{ mb_substr($user->name, 0, 1) }}
                        </div>
                    @endif
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <h4 class="fw-bold mb-0 text-white">{{ $user->name }}</h4>
                        <span class="badge bg-warning text-dark rounded-pill small px-2.5 py-0.5 fw-bold">
                            {{ $user->role === 'author' ? 'লেখক সদস্য' : ($user->role === 'publisher' ? 'প্রকাশক সদস্য' : 'সম্মানিত পাঠক') }}
                        </span>
                    </div>
                    <div class="small opacity-90 text-light mt-1 d-flex flex-wrap align-items-center gap-3">
                        <span><i class="fas fa-phone me-1"></i>{{ $user->phone }}</span>
                        @if($user->email && !str_contains($user->email, '@buyer.ideaabd.com'))
                            <span><i class="fas fa-envelope me-1"></i>{{ $user->email }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="{{ route('book.index') }}" class="btn btn-warning text-dark rounded-pill px-4 py-2 fw-bold shadow-sm">
                    <i class="fas fa-bag-shopping me-1.5"></i> বই কিনুন (Shop)
                </a>
            </div>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">মোট অর্ডার</span>
                        <h3 class="fw-bold mb-0 text-primary">@bn($totalOrdersCount) <small class="fs-6 text-muted">টি</small></h3>
                    </div>
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3"><i class="fas fa-box fs-4"></i></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">ডেলিভারিকৃত</span>
                        <h3 class="fw-bold mb-0 text-success">@bn($deliveredOrdersCount) <small class="fs-6 text-muted">টি</small></h3>
                    </div>
                    <div class="rounded-circle bg-success bg-opacity-10 text-success p-3"><i class="fas fa-truck-ramp-box fs-4"></i></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-info h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">মোট কেনাকাটা</span>
                        <h3 class="fw-bold mb-0 text-info">@taka($totalSpentAmount)</h3>
                    </div>
                    <div class="rounded-circle bg-info bg-opacity-10 text-info p-3"><i class="fas fa-receipt fs-4"></i></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-warning h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">রিডার্স পয়েন্ট</span>
                        <h3 class="fw-bold mb-0 text-warning">@bn($user->loyalty_points ?? 0) <small class="fs-6 text-muted">পয়েন্ট</small></h3>
                    </div>
                    <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3"><i class="fas fa-star fs-4"></i></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- 2. MAIN LAYOUT: SIDEBAR TABS & TAB CONTENT                                --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <div class="row g-4">
        
        <!-- Sidebar Navigation -->
        <div class="col-lg-3">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden sticky-top" style="top: 20px;">
                <div class="p-3 bg-light border-bottom">
                    <span class="small fw-bold text-dark text-uppercase letter-spacing-1">আমার একাউন্ট মেনু</span>
                </div>
                <div class="nav flex-column nav-pills p-2 gap-1" id="v-pills-tab" role="tablist">
                    <button class="nav-link {{ request('tab', 'orders') === 'orders' ? 'active' : '' }} text-start rounded-3 py-2.5 px-3 fw-semibold" 
                            id="v-pills-orders-tab" data-bs-toggle="pill" data-bs-target="#v-pills-orders" type="button" role="tab">
                        <i class="fas fa-shopping-bag me-2 text-primary"></i> আমার অর্ডারসমূহ
                        <span class="badge bg-primary float-end rounded-pill">@bn($totalOrdersCount)</span>
                    </button>
                    
                    <button class="nav-link {{ request('tab') === 'wishlist' ? 'active' : '' }} text-start rounded-3 py-2.5 px-3 fw-semibold" 
                            id="v-pills-wishlist-tab" data-bs-toggle="pill" data-bs-target="#v-pills-wishlist" type="button" role="tab">
                        <i class="fas fa-heart me-2 text-danger"></i> পছন্দের তালিকা (Wishlist)
                        @if($wishlistItems->count() > 0)
                            <span class="badge bg-danger float-end rounded-pill">@bn($wishlistItems->count())</span>
                        @endif
                    </button>

                    <button class="nav-link {{ request('tab') === 'address' ? 'active' : '' }} text-start rounded-3 py-2.5 px-3 fw-semibold" 
                            id="v-pills-address-tab" data-bs-toggle="pill" data-bs-target="#v-pills-address" type="button" role="tab">
                        <i class="fas fa-map-location-dot me-2 text-success"></i> ডেলিভারি ঠিকানা
                    </button>

                    <button class="nav-link {{ request('tab') === 'affiliate' ? 'active' : '' }} text-start rounded-3 py-2.5 px-3 fw-semibold" 
                            id="v-pills-affiliate-tab" data-bs-toggle="pill" data-bs-target="#v-pills-affiliate" type="button" role="tab">
                        <i class="fas fa-star me-2 text-warning"></i> পয়েন্ট ও রিওয়ার্ড হাব
                    </button>

                    @if($user->role === 'author' || $user->reg_type === 'author' || $authorPosts->count() > 0)
                        <button class="nav-link {{ request('tab') === 'author-blogs' ? 'active' : '' }} text-start rounded-3 py-2.5 px-3 fw-semibold" 
                                id="v-pills-author-tab" data-bs-toggle="pill" data-bs-target="#v-pills-author" type="button" role="tab">
                            <i class="fas fa-pen-nib me-2 text-success"></i> আমার ব্লগ ও সাহিত্যপত্র
                            <span class="badge bg-success float-end rounded-pill">@bn($authorPosts->count())</span>
                        </button>
                    @endif

                    <button class="nav-link {{ request('tab') === 'settings' ? 'active' : '' }} text-start rounded-3 py-2.5 px-3 fw-semibold" 
                            id="v-pills-settings-tab" data-bs-toggle="pill" data-bs-target="#v-pills-settings" type="button" role="tab">
                        <i class="fas fa-user-gear me-2 text-secondary"></i> প্রোফাইল ও নিরাপত্তা
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Tab Content Area -->
        <div class="col-lg-9">
            <div class="tab-content" id="v-pills-tabContent">
                
                {{-- ───────────────────────────────────────────────────────── --}}
                {{-- TAB 1: MY ORDERS & LIVE COURIER TRACKING                  --}}
                {{-- ───────────────────────────────────────────────────────── --}}
                <div class="tab-pane fade {{ request('tab', 'orders') === 'orders' ? 'show active' : '' }}" id="v-pills-orders" role="tabpanel">
                    
                    {{-- Order Search & Status Filter --}}
                    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                        <div class="card-body p-3">
                            <form action="{{ route('my-account') }}" method="GET" class="row g-2 align-items-center">
                                <input type="hidden" name="tab" value="orders">
                                <div class="col-12 col-md-7">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-search"></i></span>
                                        <input type="text" name="order_search" value="{{ request('order_search') }}" 
                                               class="form-control border-start-0 ps-0" placeholder="অর্ডার আইডি বা বইয়ের নাম দিয়ে খুঁজুন...">
                                        <button type="submit" class="btn btn-primary px-3 fw-semibold">খুঁজুন</button>
                                    </div>
                                </div>
                                <div class="col-12 col-md-5">
                                    <select name="order_status" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <option value="all" @selected(request('order_status') === 'all' || !request('order_status'))>— সকল অর্ডার —</option>
                                        <option value="pending" @selected(request('order_status') === 'pending')>অপেক্ষমাণ (Pending)</option>
                                        <option value="processing" @selected(request('order_status') === 'processing')>প্রসেসিং (Processing)</option>
                                        <option value="shipped" @selected(request('order_status') === 'shipped')>কুরিয়ারে পাঠানো হয়েছে (Shipped)</option>
                                        <option value="delivered" @selected(request('order_status') === 'delivered')>ডেলিভারিকৃত (Delivered)</option>
                                        <option value="cancelled" @selected(request('order_status') === 'cancelled')>বাতিল (Cancelled)</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Orders Listing Card --}}
                    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                        <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                            <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                                <i class="fas fa-list-check text-primary"></i>
                                <span>অর্ডার তালিকা ও ট্র্যাকিং</span>
                            </h5>
                            <span class="badge bg-light text-dark border px-2.5 py-1">মোট @bn($totalOrdersCount)টি অর্ডার</span>
                        </div>

                        <div class="card-body p-0">
                            @if($myOrders->isEmpty())
                                <div class="text-center py-5">
                                    <i class="fas fa-bag-shopping fs-1 text-muted opacity-50 mb-3"></i>
                                    <h6 class="fw-bold text-dark">কোনো অর্ডার পাওয়া যায়নি</h6>
                                    <p class="small text-muted mb-3">পছন্দের বই খুঁজে অর্ডার করুন।</p>
                                    <a href="{{ route('book.index') }}" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-xs">
                                        <i class="fas fa-book-open me-1"></i> বইয়ের ক্যাটালগ দেখুন
                                    </a>
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-4">অর্ডার আইডি</th>
                                                <th>বইয়ের বিবরণ</th>
                                                <th>মোট টাকা</th>
                                                <th>পেমেন্ট</th>
                                                <th>ডেলিভারি অবস্থা</th>
                                                <th class="text-end pe-4">ট্র্যাকিং ও ইনভয়েস</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($myOrders as $order)
                                                @php
                                                    $book = $order->book;
                                                    $coverUrl = $book && $book->cover_image 
                                                        ? (str_starts_with($book->cover_image, 'http') ? $book->cover_image : asset('storage/' . ltrim($book->cover_image, '/')))
                                                        : 'https://placehold.co/80x110/e2e8f0/475569?text=Cover';
                                                @endphp
                                                <tr>
                                                    <td class="ps-4">
                                                        <span class="badge bg-light text-dark border fw-bold">
                                                            #{{ $order->order_number ?: $order->id }}
                                                        </span>
                                                        <div class="text-muted small mt-0.5" style="font-size: 11px;">
                                                            {{ $order->created_at ? $order->created_at->format('d M, Y - h:i A') : '—' }}
                                                        </div>
                                                    </td>

                                                    <td>
                                                        <div class="d-flex align-items-center gap-2.5">
                                                            @if($book)
                                                                <img src="{{ $coverUrl }}" alt="{{ $book->title }}" class="rounded border shadow-xs" style="width: 38px; height: 52px; object-fit: cover;">
                                                                <div style="max-width: 200px;">
                                                                    <a href="{{ route('book.show', $book->slug ?? $book->id) }}" target="_blank" class="fw-bold text-dark text-decoration-none hover-primary d-block text-truncate mb-0.5">
                                                                        {{ $book->title }}
                                                                    </a>
                                                                    <span class="small text-muted" style="font-size: 11px;">পরিমাণ: @bn($order->quantity ?? 1) কপি</span>
                                                                </div>
                                                            @else
                                                                <span class="fw-semibold text-dark">কাস্টম বুক অর্ডার (#{{ $order->id }})</span>
                                                            @endif
                                                        </div>
                                                    </td>

                                                    <td>
                                                        <div class="fw-bold text-dark">@taka($order->total_amount)</div>
                                                        @if($order->points_earned > 0)
                                                            <small class="text-success fw-semibold" style="font-size: 10px;">+@bn($order->points_earned) পয়েন্ট</small>
                                                        @endif
                                                    </td>

                                                    <td>
                                                        @if($order->payment_status === 'paid')
                                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 rounded-pill" style="font-size: 10px;">পরিশোধিত</span>
                                                        @else
                                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-0.5 rounded-pill" style="font-size: 10px;">ক্যাশ অন ডেলিভারি</span>
                                                        @endif
                                                    </td>

                                                    <td>
                                                        @if($order->status === 'delivered')
                                                            <span class="badge bg-success text-white px-2.5 py-1 rounded-pill small">
                                                                <i class="fas fa-check-double me-1"></i>ডেলিভারি সম্পন্ন
                                                            </span>
                                                        @elseif($order->status === 'shipped')
                                                            <span class="badge bg-info text-white px-2.5 py-1 rounded-pill small">
                                                                <i class="fas fa-truck-fast me-1"></i>কুরিয়ারে রওয়ানা
                                                            </span>
                                                        @elseif($order->status === 'processing' || $order->status === 'packaging')
                                                            <span class="badge bg-primary text-white px-2.5 py-1 rounded-pill small">
                                                                <i class="fas fa-box-open me-1"></i>প্যাকেজিং হচ্ছে
                                                            </span>
                                                        @elseif($order->status === 'cancelled' || $order->status === 'rejected')
                                                            <span class="badge bg-danger text-white px-2.5 py-1 rounded-pill small">
                                                                <i class="fas fa-ban me-1"></i>বাতিল
                                                            </span>
                                                        @else
                                                            <span class="badge bg-warning text-dark px-2.5 py-1 rounded-pill small">
                                                                <i class="fas fa-clock me-1"></i>অপেক্ষমাণ
                                                            </span>
                                                        @endif
                                                    </td>

                                                    <td class="text-end pe-4">
                                                        <div class="d-flex align-items-center justify-content-end gap-1.5">
                                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1 shadow-xs" 
                                                                    onclick="openOrderTrackingModal({{ $order->id }}, '{{ $order->order_number ?: $order->id }}', '{{ $order->status }}', '{{ $order->courier_name ?? 'সুন্দরবন কুরিয়ার' }}', '{{ $order->tracking_code ?? '' }}', '{{ $order->customer_address }}', '{{ $order->total_amount }}')" 
                                                                    title="অর্ডার ট্র্যাকিং দেখুন">
                                                                <i class="fas fa-location-crosshairs me-1"></i> ট্র্যাক
                                                            </button>
                                                            <a href="{{ route('invoices.public.show', $order->id) }}" target="_blank" class="btn btn-sm btn-light border rounded-pill px-2.5 py-1" title="ইনভয়েস রসিদ দেখুন">
                                                                <i class="fas fa-receipt text-muted"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                @if($myOrders->hasPages())
                                    <div class="p-3 border-top d-flex justify-content-between align-items-center bg-light">
                                        <span class="small text-muted">
                                            মোট @bn($myOrders->total())টির মধ্যে @bn($myOrders->firstItem())–@bn($myOrders->lastItem()) দেখানো হচ্ছে
                                        </span>
                                        {{ $myOrders->links() }}
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ───────────────────────────────────────────────────────── --}}
                {{-- TAB 2: WISHLIST / SAVED BOOKS                             --}}
                {{-- ───────────────────────────────────────────────────────── --}}
                <div class="tab-pane fade {{ request('tab') === 'wishlist' ? 'show active' : '' }}" id="v-pills-wishlist" role="tabpanel">
                    <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
                        <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                            <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                                <i class="fas fa-heart text-danger"></i>
                                <span>পছন্দের বইয়ের তালিকা (Wishlist)</span>
                            </h5>
                            <span class="badge bg-danger-subtle text-danger px-3 py-1 rounded-pill">@bn($wishlistItems->count())টি বই সংরক্ষিত</span>
                        </div>

                        @if($wishlistItems->isEmpty())
                            <div class="text-center py-5">
                                <i class="fas fa-heart-crack fs-1 text-muted opacity-50 mb-3"></i>
                                <h6 class="fw-bold text-dark">আপনার পছন্দের তালিকায় কোনো বই নেই</h6>
                                <p class="small text-muted mb-3">পছন্দের বইগুলোর পাশে হার্ট বাটনে ক্লিক করে এখানে সহজে সংরক্ষণ করুন।</p>
                                <a href="{{ route('book.index') }}" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-xs">
                                    <i class="fas fa-book me-1"></i> বইয়ের শপ ব্রাউজ করুন
                                </a>
                            </div>
                        @else
                            <div class="row row-cols-1 row-cols-md-2 g-3">
                                @foreach($wishlistItems as $item)
                                    @php
                                        $wb = $item->book;
                                        if (!$wb) continue;
                                        $wCoverUrl = $wb->cover_image 
                                            ? (str_starts_with($wb->cover_image, 'http') ? $wb->cover_image : asset('storage/' . ltrim($wb->cover_image, '/')))
                                            : 'https://placehold.co/100x150/e2e8f0/475569?text=Cover';
                                    @endphp
                                    <div class="col">
                                        <div class="card h-100 border rounded-3 p-3 shadow-xs">
                                            <div class="d-flex gap-3">
                                                <img src="{{ $wCoverUrl }}" alt="{{ $wb->title }}" class="rounded border shadow-xs flex-shrink-0" style="width: 60px; height: 85px; object-fit: cover;">
                                                <div class="flex-grow-1">
                                                    <a href="{{ route('book.show', $wb->slug ?? $wb->id) }}" class="fw-bold text-dark text-decoration-none hover-primary line-clamp-1 mb-1">
                                                        {{ $wb->title }}
                                                    </a>
                                                    <div class="small text-muted mb-1">{{ $wb->author_name ?: ($wb->authorLink?->name ?? '—') }}</div>
                                                    <div class="fw-bold text-success mb-2">
                                                        @taka($wb->discount_price > 0 ? $wb->discount_price : $wb->price)
                                                        @if($wb->discount_price > 0 && $wb->price > $wb->discount_price)
                                                            <small class="text-decoration-line-through text-muted ms-1">@taka($wb->price)</small>
                                                        @endif
                                                    </div>

                                                    <div class="d-flex gap-2">
                                                        <a href="{{ route('book.show', $wb->slug ?? $wb->id) }}" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold">
                                                            <i class="fas fa-cart-shopping me-1"></i> কিনুন
                                                        </a>
                                                        <form action="{{ route('my-account.wishlist.remove', $item->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2.5" title="পছন্দের তালিকা থেকে মুছুন">
                                                                <i class="fas fa-trash-can"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- ───────────────────────────────────────────────────────── --}}
                {{-- TAB 3: DEFAULT SHIPPING ADDRESS BOOK                      --}}
                {{-- ───────────────────────────────────────────────────────── --}}
                <div class="tab-pane fade {{ request('tab') === 'address' ? 'show active' : '' }}" id="v-pills-address" role="tabpanel">
                    <div class="card border-0 shadow-sm rounded-4 bg-white p-4" style="max-width: 750px;">
                        <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                            <i class="fas fa-map-location-dot text-success"></i>
                            <span>ডিফল্ট ডেলিভারি ও শিপিং ঠিকানা</span>
                        </h5>

                        <form method="POST" action="{{ route('my-account.address.update') }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark mb-1">প্রাপকের পুরো নাম <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control rounded-3" value="{{ old('name', $defaultAddress['name'] ?? $user->name) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark mb-1">যোগাযোগের মোবাইল নম্বর <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" class="form-control rounded-3" value="{{ old('phone', $defaultAddress['phone'] ?? $user->phone) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark mb-1">জেলা (District) <span class="text-danger">*</span></label>
                                    <input type="text" name="district" class="form-control rounded-3" value="{{ old('district', $defaultAddress['district']) }}" required placeholder="যেমন: ঢাকা, রংপুর, চট্টগ্রাম...">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark mb-1">থানা / উপজেলা (Thana/Upazila)</label>
                                    <input type="text" name="thana" class="form-control rounded-3" value="{{ old('thana', $defaultAddress['thana']) }}" placeholder="যেমন: কোতোয়ালী, ধানমন্ডি...">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-dark mb-1">পূর্ণাঙ্গ ঠিকানা (বাসা/রোড/এলাকা) <span class="text-danger">*</span></label>
                                    <textarea name="address" rows="3" class="form-control rounded-3" required placeholder="বাড়ি নম্বর, রোড নম্বর, এলাকা ও পোস্ট কোড...">{{ old('address', $defaultAddress['address']) }}</textarea>
                                </div>
                            </div>

                            <div class="mt-4 pt-2 border-top d-flex justify-content-end">
                                <button type="submit" class="btn btn-success rounded-pill px-5 fw-bold shadow-sm">
                                    <i class="fas fa-save me-1.5"></i> ঠিকানা সংরক্ষণ করুন
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- ───────────────────────────────────────────────────────── --}}
                {{-- TAB 4: LOYALTY POINTS & AFFILIATE REWARDS                 --}}
                {{-- ───────────────────────────────────────────────────────── --}}
                <div class="tab-pane fade {{ request('tab') === 'affiliate' ? 'show active' : '' }}" id="v-pills-affiliate" role="tabpanel">
                    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                        <div class="row g-4 align-items-center">
                            <div class="col-md-6">
                                <div class="p-4 rounded-4 text-white" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                                    <span class="small fw-semibold text-white-50 d-block mb-1">মোট রিডার্স লয়্যালটি পয়েন্ট</span>
                                    <h2 class="fw-bold mb-2">@bn($user->loyalty_points ?? 0) <small class="fs-6">পয়েন্ট</small></h2>
                                    <p class="small mb-0 opacity-90">প্রতি ১০০ টাকার অর্ডারে ৫ পয়েন্ট জমা হয়। পয়েন্ট ব্যবহার করে পরবর্তী অর্ডারে ছাড় পাওয়া যাবে।</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-4 rounded-4 text-white" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                    <span class="small fw-semibold text-white-50 d-block mb-1">অ্যাফিলিয়েট ব্যালেন্স</span>
                                    <h2 class="fw-bold mb-2">@taka($user->affiliate_balance ?? 0)</h2>
                                    <p class="small mb-0 opacity-90">আপনার শেয়ারকৃত লিংক থেকে বই বিক্রির অর্জিত কমিশন সরাসরি আপনার ওয়ালেটে জমা হয়।</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top">
                            <h6 class="fw-bold text-dark mb-2">আপনার ব্যক্তিগত অ্যাফিলিয়েট লিংক</h6>
                            <div class="input-group">
                                <input type="text" id="affiliateShareUrl" class="form-control fw-semibold text-muted bg-light" value="{{ url('/?ref=' . $user->id) }}" readonly>
                                <button type="button" class="btn btn-primary px-4 fw-semibold" onclick="copyAffiliateLink()">
                                    <i class="fas fa-copy me-1"></i> কপি করুন
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ───────────────────────────────────────────────────────── --}}
                {{-- TAB 5: AUTHOR BLOGS PORTAL (IF AUTHOR)                    --}}
                {{-- ───────────────────────────────────────────────────────── --}}
                @if($user->role === 'author' || $user->reg_type === 'author' || $authorPosts->count() > 0)
                <div class="tab-pane fade {{ request('tab') === 'author-blogs' ? 'show active' : '' }}" id="v-pills-author" role="tabpanel">
                    <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
                        <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-pen-nib text-success me-2"></i>লেখক পোর্টাল ও প্রকাশিত রচনা</h5>
                            <a href="{{ route('author.dashboard') }}" class="btn btn-success rounded-pill px-4 fw-semibold shadow-xs">
                                <i class="fas fa-gauge-high me-1"></i> পূর্ণাঙ্গ লেখক ড্যাশবোর্ড
                            </a>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>শিরোনাম</th>
                                        <th>ক্যাটাগরি</th>
                                        <th>অবস্থা</th>
                                        <th>তারিখ</th>
                                        <th class="text-end">অ্যাকশন</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($authorPosts as $p)
                                        <tr>
                                            <td class="fw-bold text-dark">{{ $p->title }}</td>
                                            <td><span class="badge bg-light text-dark border">{{ $p->category?->name ?? 'সাধারণ' }}</span></td>
                                            <td>
                                                @if($p->status === 'published' || $p->mod_status === 'approved')
                                                    <span class="badge bg-success text-white rounded-pill px-2 py-0.5">প্রকাশিত</span>
                                                @elseif($p->status === 'pending' || $p->mod_status === 'pending')
                                                    <span class="badge bg-warning text-dark rounded-pill px-2 py-0.5">অনুমোদন অপেক্ষমাণ</span>
                                                @else
                                                    <span class="badge bg-secondary text-white rounded-pill px-2 py-0.5">খসড়া</span>
                                                @endif
                                            </td>
                                            <td class="small text-muted">{{ $p->created_at ? $p->created_at->format('d M, Y') : '—' }}</td>
                                            <td class="text-end">
                                                <a href="{{ route('blog.show', $p->slug) }}" target="_blank" class="btn btn-sm btn-light border rounded-pill px-3">
                                                    <i class="fas fa-eye me-1"></i> দেখুন
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center py-4 text-muted">কোনো লেখা পাওয়া যায়নি।</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                {{-- ───────────────────────────────────────────────────────── --}}
                {{-- TAB 6: PROFILE & PASSWORD SECURITY                        --}}
                {{-- ───────────────────────────────────────────────────────── --}}
                <div class="tab-pane fade {{ request('tab') === 'settings' ? 'show active' : '' }}" id="v-pills-settings" role="tabpanel">
                    
                    <!-- Profile Update Form -->
                    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4" style="max-width: 750px;">
                        <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                            <i class="fas fa-user-pen text-primary"></i>
                            <span>ব্যক্তিগত প্রোফাইল তথ্য হালনাগাদ</span>
                        </h5>

                        <form method="POST" action="{{ route('my-account.profile.update') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark mb-1">আপনার পুরো নাম <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control rounded-3" value="{{ old('name', $user->name) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark mb-1">মোবাইল নম্বর <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" class="form-control rounded-3" value="{{ old('phone', $user->phone) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark mb-1">ইমেইল এড্রেস</label>
                                    <input type="email" name="email" class="form-control rounded-3" value="{{ old('email', str_contains($user->email, '@buyer.ideaabd.com') ? '' : $user->email) }}" placeholder="your-email@gmail.com">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark mb-1">প্রোফাইল ছবি (Avatar)</label>
                                    <input type="file" name="avatar" class="form-control rounded-3" accept="image/*">
                                </div>
                            </div>
                            <div class="mt-4 pt-2 border-top d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                                    <i class="fas fa-save me-1.5"></i> প্রোফাইল সংরক্ষণ করুন
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Password Change Form -->
                    <div class="card border-0 shadow-sm rounded-4 bg-white p-4" style="max-width: 750px;">
                        <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                            <i class="fas fa-lock text-danger"></i>
                            <span>পাসওয়ার্ড পরিবর্তন করুন</span>
                        </h5>

                        <form method="POST" action="{{ route('my-account.password.update') }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-dark mb-1">বর্তমান পাসওয়ার্ড <span class="text-danger">*</span></label>
                                    <input type="password" name="current_password" class="form-control rounded-3" required placeholder="বর্তমান পাসওয়ার্ড দিন...">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark mb-1">নতুন পাসওয়ার্ড <span class="text-danger">*</span></label>
                                    <input type="password" name="password" class="form-control rounded-3" required placeholder="সর্বনিম্ন ৮ অক্ষর (যেমন: Pass@123)">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark mb-1">নতুন পাসওয়ার্ড নিশ্চিত করুন <span class="text-danger">*</span></label>
                                    <input type="password" name="password_confirmation" class="form-control rounded-3" required placeholder="পুনরায় নতুন পাসওয়ার্ড লিখুন...">
                                </div>
                            </div>
                            <div class="mt-4 pt-2 border-top d-flex justify-content-end">
                                <button type="submit" class="btn btn-danger rounded-pill px-5 fw-bold shadow-sm">
                                    <i class="fas fa-key me-1.5"></i> পাসওয়ার্ড পরিবর্তন করুন
                                </button>
                            </div>
                        </form>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>

{{-- ═════════════════════════════════════════════════════════════════════════ --}}
{{-- 3. LIVE ORDER TRACKING MODAL                                              --}}
{{-- ═════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="orderTrackingModal" tabindex="-1" aria-labelledby="orderTrackingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-primary text-white py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-truck-fast fs-5"></i>
                    <h5 class="modal-title fw-bold fs-6 mb-0 text-white" id="orderTrackingModalLabel">লাইভ অর্ডার ও কুরিয়ার ট্র্যাকিং</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 bg-light">
                
                <!-- Order Header Box -->
                <div class="card border-0 shadow-xs rounded-3 p-3 bg-white mb-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div>
                            <span class="small text-muted d-block">অর্ডার নম্বর:</span>
                            <h5 class="fw-bold text-dark mb-0" id="trackOrderNum">#IDP-2026-XXXX</h5>
                        </div>
                        <div>
                            <span class="small text-muted d-block">মোট মূল্য:</span>
                            <h5 class="fw-bold text-success mb-0" id="trackOrderTotal">৳ 0</h5>
                        </div>
                        <div>
                            <span class="small text-muted d-block">ডেলিভারি ঠিকানা:</span>
                            <div class="small fw-semibold text-dark" id="trackOrderAddress">—</div>
                        </div>
                    </div>
                </div>

                <!-- Courier Tracking Code Box -->
                <div class="alert alert-info rounded-3 p-3 d-flex align-items-center justify-content-between mb-4 shadow-xs" id="trackCourierBox">
                    <div class="d-flex align-items-center gap-2.5">
                        <i class="fas fa-shipping-fast fs-4 text-info"></i>
                        <div>
                            <strong class="d-block text-dark" id="trackCourierName">কুরিয়ার: সুন্দরবন কুরিয়ার সার্ভিস</strong>
                            <span class="small text-muted">ট্র্যাকিং / মেমো কোড: <strong class="text-primary font-monospace" id="trackCourierCode">SR-XXXXX</strong></span>
                        </div>
                    </div>
                </div>

                <!-- 5-Step Order Progress Stepper -->
                <div class="card border-0 shadow-xs rounded-3 p-4 bg-white">
                    <h6 class="fw-bold text-dark mb-4 pb-2 border-bottom">অর্ডারের লাইভ অগ্রগতি (Live Progress):</h6>
                    
                    <div class="d-flex justify-content-between position-relative text-center">
                        <div class="position-absolute top-50 start-0 translate-middle-y w-100 bg-secondary bg-opacity-25" style="height: 4px; z-index: 1;"></div>
                        
                        <!-- Step 1: Placed -->
                        <div class="position-relative z-2" id="stepPlaced">
                            <div class="rounded-circle bg-success text-white p-2 d-flex align-items-center justify-content-center mx-auto shadow-sm" style="width: 44px; height: 44px;">
                                <i class="fas fa-receipt"></i>
                            </div>
                            <span class="small fw-bold d-block mt-2 text-success">অর্ডার সম্পন্ন</span>
                            <small class="text-muted" style="font-size: 10px;">গৃহীত হয়েছে</small>
                        </div>

                        <!-- Step 2: Confirmed -->
                        <div class="position-relative z-2" id="stepConfirmed">
                            <div class="rounded-circle bg-light border text-muted p-2 d-flex align-items-center justify-content-center mx-auto" style="width: 44px; height: 44px;">
                                <i class="fas fa-check"></i>
                            </div>
                            <span class="small fw-bold d-block mt-2 text-muted">নিশ্চিতকরণ</span>
                            <small class="text-muted" style="font-size: 10px;">কনফার্মড</small>
                        </div>

                        <!-- Step 3: Packaging -->
                        <div class="position-relative z-2" id="stepPackaging">
                            <div class="rounded-circle bg-light border text-muted p-2 d-flex align-items-center justify-content-center mx-auto" style="width: 44px; height: 44px;">
                                <i class="fas fa-box-open"></i>
                            </div>
                            <span class="small fw-bold d-block mt-2 text-muted">প্যাকেজিং</span>
                            <small class="text-muted" style="font-size: 10px;">প্রস্তুতি চলছে</small>
                        </div>

                        <!-- Step 4: Shipped -->
                        <div class="position-relative z-2" id="stepShipped">
                            <div class="rounded-circle bg-light border text-muted p-2 d-flex align-items-center justify-content-center mx-auto" style="width: 44px; height: 44px;">
                                <i class="fas fa-truck-fast"></i>
                            </div>
                            <span class="small fw-bold d-block mt-2 text-muted">কুরিয়ারে রওয়ানা</span>
                            <small class="text-muted" style="font-size: 10px;">অন দ্য ওয়ে</small>
                        </div>

                        <!-- Step 5: Delivered -->
                        <div class="position-relative z-2" id="stepDelivered">
                            <div class="rounded-circle bg-light border text-muted p-2 d-flex align-items-center justify-content-center mx-auto" style="width: 44px; height: 44px;">
                                <i class="fas fa-house-chimney-check"></i>
                            </div>
                            <span class="small fw-bold d-block mt-2 text-muted">ডেলিভারি সম্পন্ন</span>
                            <small class="text-muted" style="font-size: 10px;">হাতে পৌঁছেছে</small>
                        </div>
                    </div>
                </div>

            </div>

            <div class="modal-footer bg-white py-3 px-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">বন্ধ করুন</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openOrderTrackingModal(orderId, orderNum, status, courierName, trackingCode, address, total) {
    document.getElementById('trackOrderNum').innerText = '#' + orderNum;
    document.getElementById('trackOrderTotal').innerText = '৳ ' + total;
    document.getElementById('trackOrderAddress').innerText = address || 'প্রধান ঠিকানা';

    const courierBox = document.getElementById('trackCourierBox');
    const courierNameEl = document.getElementById('trackCourierName');
    const courierCodeEl = document.getElementById('trackCourierCode');

    if (courierName) {
        courierNameEl.innerText = 'কুরিয়ার: ' + courierName;
    }
    if (trackingCode) {
        courierCodeEl.innerText = trackingCode;
        courierBox.classList.remove('d-none');
    } else {
        courierCodeEl.innerText = 'শিগগিরই প্রদান করা হবে';
    }

    // Reset Stepper Styles
    const steps = ['stepPlaced', 'stepConfirmed', 'stepPackaging', 'stepShipped', 'stepDelivered'];
    steps.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            const iconDiv = el.querySelector('div');
            const span = el.querySelector('span');
            iconDiv.className = 'rounded-circle bg-light border text-muted p-2 d-flex align-items-center justify-content-center mx-auto';
            span.className = 'small fw-bold d-block mt-2 text-muted';
        }
    });

    function markStepActive(id) {
        const el = document.getElementById(id);
        if (el) {
            const iconDiv = el.querySelector('div');
            const span = el.querySelector('span');
            iconDiv.className = 'rounded-circle bg-success text-white p-2 d-flex align-items-center justify-content-center mx-auto shadow-sm';
            span.className = 'small fw-bold d-block mt-2 text-success';
        }
    }

    markStepActive('stepPlaced');
    if (status === 'confirmed' || status === 'processing' || status === 'packaging' || status === 'shipped' || status === 'delivered') {
        markStepActive('stepConfirmed');
    }
    if (status === 'processing' || status === 'packaging' || status === 'shipped' || status === 'delivered') {
        markStepActive('stepPackaging');
    }
    if (status === 'shipped' || status === 'delivered') {
        markStepActive('stepShipped');
    }
    if (status === 'delivered') {
        markStepActive('stepDelivered');
    }

    const modalEl = document.getElementById('orderTrackingModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}

function copyAffiliateLink() {
    const input = document.getElementById('affiliateShareUrl');
    if (input) {
        input.select();
        navigator.clipboard.writeText(input.value);
        alert('✅ আপনার রেফারাল ও অ্যাফিলিয়েট লিংকটি কপি হয়েছে!');
    }
}
</script>
@endpush

@endsection
