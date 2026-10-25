{{-- Shared by the seller dashboard and the billing pages. The dashboard passes $shopName, $profile …; other pages fall back to the signed-in user. --}}
@php
    $me          = auth()->user();
    $hasProfile  = array_key_exists('profile', get_defined_vars());   // the dashboard says whose shop this is (or null = all sellers)
    $owner       = $hasProfile ? $profile : $me;
    $shop        = $shopName ?? ($owner ? ($owner->reg_data['shop_name'] ?? ($owner->name . ' - বুকশপ')) : 'সকল বিক্রেতা');
    $address     = $shopAddress ?? ($owner ? ($owner->reg_data['address'] ?? $owner->address ?? null) : null);
    $license     = $tradeLicense ?? ($owner ? ($owner->reg_data['trade_license'] ?? null) : null);
    $nav = [
        ['subadmin.dashboard',     'fa-gauge-high',          'ড্যাশবোর্ড'],
        ['subadmin.bills.create',  'fa-plus',                'নতুন বিল'],
        ['subadmin.bills.index',   'fa-file-invoice-dollar', 'বিল তালিকা'],
        ['subadmin.accounts',      'fa-wallet',              'হিসাব'],
        ['my-account',             'fa-user',                'আমার অ্যাকাউন্ট'],
    ];
@endphp
<link rel="stylesheet" href="{{ asset('css/seller-panel.css') }}?v={{ @filemtime(public_path('css/seller-panel.css')) ?: 1 }}">

<header class="sp-head">
    <div class="sp-head-top">
        <div class="sp-logo"><i class="fas fa-store"></i></div>
        <div class="me-auto min-w-0">
            <h1 class="sp-shop text-truncate">{{ $shop }}</h1>
            <div class="sp-meta">
                @if($owner)
                    <span><i class="fas fa-user me-1"></i>{{ $owner->name }}</span>
                    @if($owner->phone)<span><i class="fas fa-phone me-1"></i>{{ $owner->phone }}</span>@endif
                @endif
                @if($address)<span><i class="fas fa-location-dot me-1"></i>{{ $address }}</span>@endif
                @if($license)<span><i class="fas fa-id-card me-1"></i>লাইসেন্স {{ $license }}</span>@endif
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if($me && $me->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3"><i class="fas fa-gauge-high me-1"></i>অ্যাডমিন</a>
            @endif
            <a href="{{ route('subadmin.bills.create') }}" class="btn btn-sm btn-success rounded-pill px-3"><i class="fas fa-plus me-1"></i>নতুন বিল</a>
        </div>
    </div>

    <nav class="sp-nav" aria-label="সেলার মেনু">
        @foreach($nav as [$route, $icon, $label])
            <a href="{{ route($route) }}" class="{{ request()->routeIs($route) ? 'active' : '' }}" @if(request()->routeIs($route)) aria-current="page" @endif>
                <i class="fas {{ $icon }}"></i>{{ $label }}
            </a>
        @endforeach
    </nav>
</header>
