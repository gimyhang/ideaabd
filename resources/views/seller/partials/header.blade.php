@php
    $sellerUser = auth()->user();
    $isSellerAdmin = $sellerUser ? $sellerUser->isAdmin() : false;
    $currentShopName = $shopName ?? ($sellerUser->reg_data['shop_name'] ?? ($sellerUser->name . ' - Bookshop'));
    $currentShopAddress = $shopAddress ?? ($sellerUser->reg_data['address'] ?? ($sellerUser->address ?? 'বাংলাদেশ'));
    $currentTradeLicense = $tradeLicense ?? ($sellerUser->reg_data['trade_license'] ?? null);
@endphp

<div class="card border-0 shadow-sm rounded-4 mb-4 text-white overflow-hidden position-relative" 
     style="background: linear-gradient(135deg, #0f172a 0%, #064e3b 50%, #047857 100%);">
    <div class="card-body p-3.5 p-md-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 position-relative z-1">
        <div class="d-flex align-items-center gap-3">
            <div class="position-relative flex-shrink-0 bg-white rounded-circle p-1 shadow-sm d-flex align-items-center justify-content-center" 
                 style="width: 70px; height: 70px; min-width: 70px;">
                <div class="w-100 h-100 rounded-circle bg-success text-white fw-bold fs-2 d-flex align-items-center justify-content-center">
                    <i class="fas fa-store"></i>
                </div>
                <span class="position-absolute bottom-0 end-0 bg-warning text-dark p-1 rounded-circle shadow-xs" title="অনুমোদিত বিক্রেতা" style="font-size: 10px; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-check"></i>
                </span>
            </div>
            <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <h3 class="fw-bold mb-0 text-white fs-4 fs-md-3">{{ $currentShopName }}</h3>
                    <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-2.5 py-1 small fw-semibold">
                        <i class="fas fa-circle-check text-warning me-1"></i>সেলার ও ডিলার পোর্টাল
                    </span>
                    @if($isSellerAdmin)
                        <a href="{{ route('admin.dashboard') }}" class="badge bg-warning text-dark rounded-pill px-2.5 py-1 text-decoration-none fw-bold small">
                            <i class="fas fa-gauge-high me-1"></i>অ্যাডমিন প্যানেল
                        </a>
                    @endif
                </div>
                <div class="small opacity-90 text-light d-flex flex-wrap align-items-center gap-3" style="font-size: 12px;">
                    <span><i class="fas fa-user-circle me-1"></i>{{ $sellerUser->name }}</span>
                    @if($sellerUser->phone)
                        <span><i class="fas fa-phone me-1"></i>{{ $sellerUser->phone }}</span>
                    @endif
                    @if($currentShopAddress)
                        <span><i class="fas fa-location-dot me-1"></i>{{ $currentShopAddress }}</span>
                    @endif
                    @if($currentTradeLicense)
                        <span><i class="fas fa-id-card me-1"></i>লাইসেন্স: {{ $currentTradeLicense }}</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Action Buttons Navigation --}}
        <div class="d-flex flex-wrap align-items-center gap-2 w-100 w-md-auto justify-content-start justify-content-md-end">
            <a href="{{ route('subadmin.dashboard') }}" 
               class="btn {{ request()->routeIs('subadmin.dashboard') ? 'btn-light text-dark fw-bold' : 'btn-outline-light' }} rounded-pill px-3 py-2 d-flex align-items-center gap-1.5 flex-grow-1 flex-md-grow-0 justify-content-center shadow-sm">
                <i class="fas fa-gauge-high {{ request()->routeIs('subadmin.dashboard') ? 'text-primary' : '' }}"></i>
                <span>ড্যাশবোর্ড</span>
            </a>
            <a href="{{ route('subadmin.bills.create') }}" 
               class="btn {{ request()->routeIs('subadmin.bills.create') ? 'btn-warning text-dark fw-bold' : 'btn-outline-warning text-white' }} rounded-pill px-3.5 py-2 d-flex align-items-center gap-1.5 flex-grow-1 flex-md-grow-0 justify-content-center shadow-sm">
                <i class="fas fa-plus-circle"></i>
                <span>নতুন বিল (POS)</span>
            </a>
            <a href="{{ route('subadmin.bills.index') }}" 
               class="btn {{ request()->routeIs('subadmin.bills.index') ? 'btn-light text-dark fw-bold' : 'btn-outline-light' }} rounded-pill px-3 py-2 d-flex align-items-center gap-1.5 flex-grow-1 flex-md-grow-0 justify-content-center shadow-sm">
                <i class="fas fa-file-invoice-dollar {{ request()->routeIs('subadmin.bills.index') ? 'text-primary' : '' }}"></i>
                <span>বিল ও চালান তালিকা</span>
            </a>
            <a href="{{ route('subadmin.accounts') }}" 
               class="btn {{ request()->routeIs('subadmin.accounts') ? 'btn-light text-dark fw-bold' : 'btn-outline-light' }} rounded-pill px-3 py-2 d-flex align-items-center gap-1.5 flex-grow-1 flex-md-grow-0 justify-content-center shadow-sm">
                <i class="fas fa-wallet {{ request()->routeIs('subadmin.accounts') ? 'text-primary' : '' }}"></i>
                <span>হিসাব বিবরণী</span>
            </a>
        </div>
    </div>
</div>
