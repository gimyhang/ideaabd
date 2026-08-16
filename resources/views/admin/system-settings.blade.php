@extends('layouts.admin')

@section('title', 'সিস্টেম সেটিংস ও থিম কন্ট্রোল')
@section('heading', 'সিস্টেম সেটিংস ও থিম কন্ট্রোল প্যানেল')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">অ্যাডমিন</a></li>
    <li class="breadcrumb-item active">সিস্টেম সেটিংস</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css" />
<style>
    .fixed-preview-container {
        position: relative;
        overflow: hidden;
        background-color: #f8fafc;
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: border-color 0.2s;
    }
    .fixed-preview-container:hover {
        border-color: #3b82f6;
    }
    .banner-preview-box {
        width: 100%;
        aspect-ratio: 3 / 1;
    }
    .logo-preview-box {
        width: 100%;
        max-width: 320px;
        height: 76px;
    }
    .favicon-preview-box {
        width: 60px;
        height: 60px;
        aspect-ratio: 1 / 1;
    }
    .cropper-view-box,
    .cropper-face {
        border-radius: 6px;
    }
</style>
@endpush

@php
    $siteLogo = $settings['site_logo'] ?? config('brand.logo');
    $logoUrl = null;
    if ($siteLogo) {
        $logoUrl = str_starts_with($siteLogo, 'http') ? $siteLogo : asset($siteLogo);
    }
    
    $banner1 = $settings['home_banner_1'] ?? null;
    $banner1Url = $banner1 ? (str_starts_with($banner1, 'http') ? $banner1 : asset($banner1)) : null;

    $banner2 = $settings['home_banner_2'] ?? null;
    $banner2Url = $banner2 ? (str_starts_with($banner2, 'http') ? $banner2 : asset($banner2)) : null;

    $siteFavicon = $settings['site_favicon'] ?? null;
    $faviconUrl = $siteFavicon ? (str_starts_with($siteFavicon, 'http') ? $siteFavicon : asset($siteFavicon)) : null;
@endphp

@section('content')
<div class="system-settings-wrapper pb-5">
    
    <!-- Top Action Bar -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-body p-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-sliders text-primary"></i> সিস্টেম সেটিংস, ইমেজ ক্রপার ও থিম কন্ট্রোল
                </h5>
                <p class="text-muted small mb-0">লোগো, ব্যানার ক্রপিং ও রিসাইজিং, ডেলিভারি চার্জ, নোটিশ এবং থিম কালারসমূহ নিয়ন্ত্রণ করুন।</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <!-- Clear Cache Form -->
                <form action="{{ route('admin.system-settings.clear-cache') }}" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিত যে সকল ক্যাশ ক্লিয়ার করতে চান?');">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-xs">
                        <i class="fa-solid fa-broom"></i> ক্যাশ ক্লিয়ার করুন
                    </button>
                </form>
                <!-- Live Site Link -->
                <a href="{{ route('home') }}" target="_blank" class="btn btn-primary btn-sm rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-xs">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> ওয়েবসাইট দেখুন
                </a>
            </div>
        </div>
    </div>

    <!-- Main Settings Form -->
    <form action="{{ route('admin.system-settings.update') }}" method="POST" enctype="multipart/form-data" id="systemSettingsForm">
        @csrf

        <!-- Hidden Inputs for Cropped Base64 Images -->
        <input type="hidden" name="site_logo_cropped" id="site_logo_cropped">
        <input type="hidden" name="site_favicon_cropped" id="site_favicon_cropped">
        <input type="hidden" name="banner_1_cropped" id="banner_1_cropped">
        <input type="hidden" name="banner_2_cropped" id="banner_2_cropped">

        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
            
            <!-- Navigation Tabs Header -->
            <div class="card-header bg-white border-bottom p-3 p-md-4">
                <ul class="nav nav-pills nav-fill gap-2" id="settingsTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill fw-semibold py-2.5 px-3 d-flex align-items-center justify-content-center gap-2" 
                                id="tab-brand-btn" data-bs-toggle="pill" data-bs-target="#tab-brand" type="button" role="tab">
                            <i class="fa-solid fa-crop-simple text-primary"></i>
                            <span>ব্র্যান্ডিং, লোগো ও ক্রপার</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill fw-semibold py-2.5 px-3 d-flex align-items-center justify-content-center gap-2" 
                                id="tab-theme-btn" data-bs-toggle="pill" data-bs-target="#tab-theme" type="button" role="tab">
                            <i class="fa-solid fa-wand-magic-sparkles text-info"></i>
                            <span>থিম ও কালার</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill fw-semibold py-2.5 px-3 d-flex align-items-center justify-content-center gap-2" 
                                id="tab-notice-btn" data-bs-toggle="pill" data-bs-target="#tab-notice" type="button" role="tab">
                            <i class="fa-solid fa-bullhorn text-warning"></i>
                            <span>নোটিশ ব্যানার</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill fw-semibold py-2.5 px-3 d-flex align-items-center justify-content-center gap-2" 
                                id="tab-ecom-btn" data-bs-toggle="pill" data-bs-target="#tab-ecom" type="button" role="tab">
                            <i class="fa-solid fa-truck-fast text-success"></i>
                            <span>ডেলিভারি ও হেল্পলাইন</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill fw-semibold py-2.5 px-3 d-flex align-items-center justify-content-center gap-2" 
                                id="tab-invoice-btn" data-bs-toggle="pill" data-bs-target="#tab-invoice" type="button" role="tab">
                            <i class="fa-solid fa-file-invoice-dollar text-primary"></i>
                            <span>ইনভয়েস ও প্রেরক তথ্য</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill fw-semibold py-2.5 px-3 d-flex align-items-center justify-content-center gap-2" 
                                id="tab-payment-btn" data-bs-toggle="pill" data-bs-target="#tab-payment" type="button" role="tab">
                            <i class="fa-solid fa-credit-card text-danger"></i>
                            <span>পেমেন্ট গেটওয়ে কাস্টমাইজ</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill fw-semibold py-2.5 px-3 d-flex align-items-center justify-content-center gap-2" 
                                id="tab-system-btn" data-bs-toggle="pill" data-bs-target="#tab-system" type="button" role="tab">
                            <i class="fa-solid fa-server text-secondary"></i>
                            <span>সার্ভার ডায়াগনস্টিকস</span>
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Tabs Body -->
            <div class="card-body p-4 p-md-5">
                <div class="tab-content" id="settingsTabContent">
                    
                    <!-- Tab 1: Branding & Visuals (Logo, Banners, Favicon + Cropper) -->
                    <div class="tab-pane fade show active" id="tab-brand" role="tabpanel">
                        <div class="row g-4">
                            
                            <!-- Left: Site Name & Tagline & Favicon -->
                            <div class="col-lg-6">
                                <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-signature text-primary me-2"></i>সাইটের নাম ও বিবরণ</h6>
                                
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">ওয়েবসাইটের মূল নাম</label>
                                    <input type="text" name="site_name" value="{{ $settings['site_name'] ?? config('brand.name', 'আইডিয়া প্রকাশন') }}" class="form-control rounded-3" placeholder="যেমন: আইডিয়া প্রকাশন">
                                </div>

                                <div class="mb-4">
                                    <label class="form-label small fw-semibold text-muted">ট্যাগলাইন / স্লোগান</label>
                                    <input type="text" name="site_tagline" value="{{ $settings['site_tagline'] ?? 'বই ও মুক্তচিন্তার ডিজিটাল প্রকাশনা' }}" class="form-control rounded-3" placeholder="যেমন: বই ও মুক্তচিন্তার ডিজিটাল প্রকাশনা">
                                </div>

                                <!-- Favicon Upload Box with Fixed Container & Cropper -->
                                <div class="p-3 bg-light rounded-4 border mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label small fw-bold text-dark mb-0">ফেভিকন (Favicon Icon - 1:1)</label>
                                        <span class="badge bg-white text-muted border rounded-pill px-2.5 py-0.5 small">বর্গাকার ১:১</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <!-- Fixed 1:1 Favicon Preview Container -->
                                        <div class="fixed-preview-container favicon-preview-box shadow-xs" id="faviconContainer">
                                            @if($faviconUrl)
                                                <img src="{{ $faviconUrl }}" alt="Favicon" class="w-100 h-100 object-fit-contain" id="faviconPreviewImg">
                                            @else
                                                <i class="fa-solid fa-globe fs-4 text-muted" id="faviconPreviewImg"></i>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <input type="file" id="faviconInput" name="site_favicon" class="form-control form-control-sm rounded-3 mb-1" accept="image/*" onchange="initCropper(this, 'favicon', 1)">
                                            <div class="form-text small text-muted">ফাইল সিলেক্ট করলে ক্রপ ও রিসাইজ উইন্ডো আসবে।</div>
                                            @if($faviconUrl)
                                                <div class="form-check mt-1">
                                                    <input class="form-check-input" type="checkbox" name="remove_site_favicon" value="1" id="rmFavicon">
                                                    <label class="form-check-label small text-danger fw-semibold" for="rmFavicon">ফেভিকন মুছুন</label>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Fixed Frame Logo Upload & Interactive Cropper -->
                            <div class="col-lg-6">
                                <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-image text-primary me-2"></i>প্রতিষ্ঠানের মূল লোগো (Site Logo)</h6>
                                
                                <div class="p-4 bg-light rounded-4 border text-center">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="badge bg-white text-dark border rounded-pill px-3 py-1 small fw-semibold">
                                            <i class="fa-solid fa-arrows-to-dot text-primary me-1"></i> ফিক্সড হেডস্পেস ফ্রেম
                                        </span>
                                        <span class="small text-muted">অটো-ফিট মোড সক্রিয়</span>
                                    </div>
                                    
                                    <!-- Fixed Frame Logo Box (Simulating Navbar Height) -->
                                    <div class="fixed-preview-container logo-preview-box shadow-xs bg-white mx-auto mb-3" id="logoContainer">
                                        @if($logoUrl)
                                            <img src="{{ $logoUrl }}" alt="Site Logo" id="logoPreviewImg" 
                                                 style="max-height: 52px; max-width: 220px; width: auto; height: auto; object-fit: contain;">
                                        @else
                                            <div class="d-flex align-items-center gap-2 text-primary" id="logoPreviewImg">
                                                <span class="badge bg-primary text-white p-2 rounded fs-5">আই</span>
                                                <span class="fw-bold fs-5">আইডিয়া প্রকাশন</span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="text-start mb-2">
                                        <label class="form-label small fw-semibold text-muted">লোগো ফাইল সিলেক্ট বা ক্রপ করুন:</label>
                                        <input type="file" id="logoInput" name="site_logo" class="form-control rounded-3" accept="image/*" onchange="initCropper(this, 'logo', NaN)">
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <p class="small text-muted mb-0 text-start">
                                            <i class="fa-solid fa-circle-check text-success me-1"></i>
                                            হেডারের নির্দিষ্ট সাইজে সুন্দরভাবে ফিক্সড হয়ে বসে যাবে।
                                        </p>
                                        @if($logoUrl)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="remove_site_logo" value="1" id="rmLogo">
                                                <label class="form-check-label small text-danger fw-semibold" for="rmLogo">লোগো মুছুন</label>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Promotional Banners Section with 3:1 Cropper & Fixed Container -->
                            <div class="col-12 pt-3 border-top">
                                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1"><i class="fa-solid fa-rectangle-ad text-primary me-2"></i>হোমপেজ প্রমোশনাল ব্যানার (Fixed 3:1 Aspect Ratio)</h6>
                                        <p class="small text-muted mb-0">ব্যানার আপলোড করলে স্বয়ংক্রিয় ৩:১ রেশিওতে ক্রপ করে নেওয়া যাবে যাতে কোনো ডিভাইসে ফেটে না যায়।</p>
                                    </div>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1.5 fw-bold">
                                        ১২০০ × ৪০০ px (৩:১ অনুপাত)
                                    </span>
                                </div>
                                
                                <div class="row g-4">
                                    
                                    <!-- Banner 1 -->
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-4 border h-100">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <label class="form-label small fw-bold text-dark mb-0">ব্যানার ১ (অফার / মেগা সেল)</label>
                                                <span class="badge bg-white text-muted border small">৩:১ ফিক্সড ফ্রেম</span>
                                            </div>
                                            
                                            <!-- Fixed Aspect Ratio 3:1 Box -->
                                            <div class="fixed-preview-container banner-preview-box mb-3" id="banner1Container">
                                                @if($banner1Url)
                                                    <img src="{{ $banner1Url }}" alt="Banner 1" class="w-100 h-100 object-fit-cover" id="banner1PreviewImg">
                                                @else
                                                    <div class="text-muted small text-center p-3" id="banner1PreviewImg">
                                                        <i class="fa-solid fa-image display-6 opacity-25 d-block mb-1"></i>
                                                        ব্যানার ১ আপলোড ও ক্রপ করুন
                                                    </div>
                                                @endif
                                            </div>

                                            <input type="file" id="banner1Input" name="banner_1" class="form-control form-control-sm rounded-3 mb-1" accept="image/*" onchange="initCropper(this, 'banner_1', 3/1)">
                                            <div class="form-text small text-muted">ফাইল নির্বাচন করলে ৩:১ রেশিওতে ক্রপ করার অপশন আসবে।</div>
                                        </div>
                                    </div>

                                    <!-- Banner 2 -->
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-4 border h-100">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <label class="form-label small fw-bold text-dark mb-0">ব্যানার ২ (ই-বুক / বিশেষ অফার)</label>
                                                <span class="badge bg-white text-muted border small">৩:১ ফিক্সড ফ্রেম</span>
                                            </div>
                                            
                                            <!-- Fixed Aspect Ratio 3:1 Box -->
                                            <div class="fixed-preview-container banner-preview-box mb-3" id="banner2Container">
                                                @if($banner2Url)
                                                    <img src="{{ $banner2Url }}" alt="Banner 2" class="w-100 h-100 object-fit-cover" id="banner2PreviewImg">
                                                @else
                                                    <div class="text-muted small text-center p-3" id="banner2PreviewImg">
                                                        <i class="fa-solid fa-image display-6 opacity-25 d-block mb-1"></i>
                                                        ব্যানার ২ আপলোড ও ক্রপ করুন
                                                    </div>
                                                @endif
                                            </div>

                                            <input type="file" id="banner2Input" name="banner_2" class="form-control form-control-sm rounded-3 mb-1" accept="image/*" onchange="initCropper(this, 'banner_2', 3/1)">
                                            <div class="form-text small text-muted">ফাইল নির্বাচন করলে ৩:১ রেশিওতে ক্রপ করার অপশন আসবে।</div>
                                        </div>
                                    </div>
                                        <div class="p-3 bg-light rounded-4 border h-100">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <label class="form-label small fw-bold text-dark mb-0">ব্যানার ২ (ই-বুক / বিশেষ প্রকাশনা)</label>
                                                <span class="badge bg-white text-muted border small">৩:১ ফিক্সড ফ্রেম</span>
                                            </div>
                                            
                                            <!-- Fixed Aspect Ratio 3:1 Box -->
                                            <div class="fixed-preview-container banner-preview-box mb-3" id="banner2Container">
                                                @if($banner2Url)
                                                    <img src="{{ $banner2Url }}" alt="Banner 2" class="w-100 h-100 object-fit-cover" id="banner2PreviewImg">
                                                @else
                                                    <div class="text-muted small text-center p-3" id="banner2PreviewImg">
                                                        <i class="fa-solid fa-image display-6 opacity-25 d-block mb-1"></i>
                                                        ব্যানার ২ আপলোড ও ক্রপ করুন
                                                    </div>
                                                @endif
                                            </div>

                                            <input type="file" id="banner2Input" class="form-control form-control-sm rounded-3 mb-1" accept="image/*" onchange="initCropper(this, 'banner_2', 3/1)">
                                            <div class="form-text small text-muted">ফাইল নির্বাচন করলে ৩:১ রেশিওতে ক্রপ করার অপশন আসবে।</div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Tab 2: Theme & Colors Customizer -->
                    <div class="tab-pane fade" id="tab-theme" role="tabpanel">
                        <div class="row g-4">
                            
                            <div class="col-lg-6">
                                <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-swatchbook text-primary me-2"></i>ব্র্যান্ড কালার স্কিম</h6>
                                <p class="small text-muted mb-3">এক ক্লিকে আপনার ওয়েবসাইটের প্রাইমারি ব্র্যান্ড কালার প্যালেট নির্বাচন করুন:</p>

                                <div class="d-flex flex-column gap-2 mb-4">
                                    <button type="button" class="btn btn-outline-light text-dark p-3 rounded-4 border d-flex align-items-center justify-content-between hover-lift transition-all" 
                                            onclick="applyPresetTheme('#0066cc', '#0099ff')">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="rounded-circle shadow-xs" style="width: 28px; height: 28px; background: #0066cc;"></div>
                                            <div class="text-start">
                                                <div class="fw-bold fs-6">ইন্ডিগো ক্লাসিক (Classic Royal Blue)</div>
                                                <div class="small text-muted">স্ট্যান্ডার্ড ডিজিটাল পাবলিকেশন থিম</div>
                                            </div>
                                        </div>
                                        <span class="badge bg-primary rounded-pill px-3 py-1.5">ডিফল্ট</span>
                                    </button>

                                    <button type="button" class="btn btn-outline-light text-dark p-3 rounded-4 border d-flex align-items-center justify-content-between hover-lift transition-all" 
                                            onclick="applyPresetTheme('#059669', '#10b981')">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="rounded-circle shadow-xs" style="width: 28px; height: 28px; background: #059669;"></div>
                                            <div class="text-start">
                                                <div class="fw-bold fs-6">পান্না সবুজ (Emerald Green)</div>
                                                <div class="small text-muted">প্রাকৃতিক ও মননশীল আভা</div>
                                            </div>
                                        </div>
                                    </button>

                                    <button type="button" class="btn btn-outline-light text-dark p-3 rounded-4 border d-flex align-items-center justify-content-between hover-lift transition-all" 
                                            onclick="applyPresetTheme('#4f46e5', '#818cf8')">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="rounded-circle shadow-xs" style="width: 28px; height: 28px; background: #4f46e5;"></div>
                                            <div class="text-start">
                                                <div class="fw-bold fs-6">ভায়োলেট পার্পল (Deep Violet)</div>
                                                <div class="small text-muted">মডার্ন ও ক্রিয়েটিভ লুক</div>
                                            </div>
                                        </div>
                                    </button>

                                    <button type="button" class="btn btn-outline-light text-dark p-3 rounded-4 border d-flex align-items-center justify-content-between hover-lift transition-all" 
                                            onclick="applyPresetTheme('#be123c', '#f43f5e')">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="rounded-circle shadow-xs" style="width: 28px; height: 28px; background: #be123c;"></div>
                                            <div class="text-start">
                                                <div class="fw-bold fs-6">ক্রিমসন রোজ (Crimson Rose)</div>
                                                <div class="small text-muted">আকর্ষণীয় ও প্রাণবন্ত স্টাইল</div>
                                            </div>
                                        </div>
                                    </button>
                                </div>

                                <!-- Custom Color Hex Inputs -->
                                <div class="p-3 bg-light rounded-4 border">
                                    <label class="form-label small fw-bold text-dark mb-2">কাস্টম কালার কোড (Custom HEX)</label>
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <label class="small text-muted">প্রাইমারি কালার</label>
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="color" id="primaryColorPicker" value="{{ $themeSetting['primary_color'] ?? '#0066cc' }}" class="form-control form-control-color border-0 p-0 rounded-circle" style="width: 36px; height: 36px;" onchange="document.getElementById('primaryColorInput').value = this.value">
                                                <input type="text" name="primary_color" id="primaryColorInput" value="{{ $themeSetting['primary_color'] ?? '#0066cc' }}" class="form-control form-control-sm rounded-3 font-monospace">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <label class="small text-muted">সেকেন্ডারি কালার</label>
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="color" id="secondaryColorPicker" value="{{ $themeSetting['secondary_color'] ?? '#0099ff' }}" class="form-control form-control-color border-0 p-0 rounded-circle" style="width: 36px; height: 36px;" onchange="document.getElementById('secondaryColorInput').value = this.value">
                                                <input type="text" name="secondary_color" id="secondaryColorInput" value="{{ $themeSetting['secondary_color'] ?? '#0099ff' }}" class="form-control form-control-sm rounded-3 font-monospace">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Live Theme Mode Switch & Simulation -->
                            <div class="col-lg-6">
                                <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-moon text-primary me-2"></i>ডিফল্ট ডিসপ্লে মোড</h6>
                                
                                <div class="p-4 bg-light rounded-4 border mb-4">
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <label class="card p-3 border-2 rounded-4 text-center cursor-pointer hover-lift transition-all {{ ($themeSetting['default_mode'] ?? 'light') === 'light' ? 'border-primary bg-white shadow-xs' : 'border-transparent bg-white' }}">
                                                <input type="radio" name="default_mode" value="light" class="d-none" {{ ($themeSetting['default_mode'] ?? 'light') === 'light' ? 'checked' : '' }}>
                                                <i class="fa-solid fa-sun fs-2 text-warning mb-2"></i>
                                                <div class="fw-bold fs-6">লাইট মোড</div>
                                                <span class="small text-muted">স্বচ্ছ ও পরিচ্ছন্ন রিডিং</span>
                                            </label>
                                        </div>
                                        <div class="col-6">
                                            <label class="card p-3 border-2 rounded-4 text-center cursor-pointer hover-lift transition-all {{ ($themeSetting['default_mode'] ?? '') === 'dark' ? 'border-primary bg-white shadow-xs' : 'border-transparent bg-white' }}">
                                                <input type="radio" name="default_mode" value="dark" class="d-none" {{ ($themeSetting['default_mode'] ?? '') === 'dark' ? 'checked' : '' }}>
                                                <i class="fa-solid fa-moon fs-2 text-indigo-500 mb-2"></i>
                                                <div class="fw-bold fs-6">ডার্ক মোড</div>
                                                <span class="small text-muted">চোখের জন্য আরামদায়ক</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Tab 3: System Notice & Announcement -->
                    <div class="tab-pane fade" id="tab-notice" role="tabpanel">
                        <div class="row g-4">
                            
                            <div class="col-lg-7">
                                <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-bullhorn text-warning me-2"></i>নোটিশ মেসেজ ও কনফিগারেশন</h6>
                                
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">নোটিশ টেক্সট (বার্তা)</label>
                                    <textarea name="notice_text" id="noticeTextInput" class="form-control rounded-3" rows="4" placeholder="যেমন: পবিত্র ঈদ উপলক্ষে সকল বইয়ে ২৫% ছাড়! ক্যাশ অন ডেলিভারি সারা দেশে।" oninput="updateLiveNoticePreview()">{{ $noticeSetting['text'] ?? '' }}</textarea>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted">নোটিশের ধরণ / ভিজ্যুয়াল স্টাইল</label>
                                        <select name="notice_type" id="noticeTypeSelect" class="form-select rounded-3" onchange="updateLiveNoticePreview()">
                                            <option value="info" {{ ($noticeSetting['type'] ?? '') === 'info' ? 'selected' : '' }}>তথ্যমূলক (নীল)</option>
                                            <option value="warning" {{ ($noticeSetting['type'] ?? '') === 'warning' ? 'selected' : '' }}>সতর্কতা / ঘোষণা (হলুদ)</option>
                                            <option value="success" {{ ($noticeSetting['type'] ?? '') === 'success' ? 'selected' : '' }}>অফার / সেলিব্রেশন (সবুজ)</option>
                                            <option value="danger" {{ ($noticeSetting['type'] ?? '') === 'danger' ? 'selected' : '' }}>জরুরি নোটিশ (লাল)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted">স্ট্যাটাস</label>
                                        <div class="form-check form-switch p-2 bg-light rounded-3 px-3 d-flex align-items-center justify-content-between border">
                                            <label class="form-check-label small fw-bold text-dark cursor-pointer ms-0 me-2" for="noticeActiveSwitch">
                                                নোটিশ প্রদর্শন করুন
                                            </label>
                                            <input class="form-check-input cursor-pointer" type="checkbox" role="switch" id="noticeActiveSwitch" name="notice_active" value="1" {{ !empty($noticeSetting['active']) ? 'checked' : '' }} onchange="updateLiveNoticePreview()">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Live Notice Preview -->
                            <div class="col-lg-5">
                                <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-eye text-primary me-2"></i>লাইভ প্রিভিউ (ওয়েবসাইটে যেমন দেখাবে)</h6>
                                
                                <div class="p-4 bg-light rounded-4 border">
                                    <div id="noticePreviewBox" class="alert alert-{{ $noticeSetting['type'] ?? 'info' }} rounded-3 shadow-xs d-flex align-items-center gap-2 mb-0 {{ empty($noticeSetting['active']) ? 'opacity-50' : '' }}">
                                        <i class="fa-solid fa-circle-info fs-5" id="noticePreviewIcon"></i>
                                        <span id="noticePreviewText">{{ $noticeSetting['text'] ?? 'এখানে নোটিশ বার্তাটি লাইভ প্রদর্শিত হবে।' }}</span>
                                    </div>
                                    <span class="small text-muted mt-2 d-block" id="noticeStatusText">
                                        {{ !empty($noticeSetting['active']) ? '🟢 বর্তমানে সাইটে নোটিশ সক্রিয় রয়েছে' : '⚪ নোটিশটি বর্তমানে বন্ধ রাখা হয়েছে' }}
                                    </span>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Tab 4: E-commerce & Delivery Configuration -->
                    <div class="tab-pane fade" id="tab-ecom" role="tabpanel">
                        <div class="row g-4">
                            
                            <!-- Delivery Charges -->
                            <div class="col-lg-6">
                                <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-truck-ramp-box text-success me-2"></i>ডেলিভারি চার্জ কনফিগারেশন</h6>
                                
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">ঢাকা সিটি কর্পোরেশন ডেলিভারি চার্জ (৳)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light fw-bold">৳</span>
                                        <input type="number" name="delivery_dhaka" value="{{ $ecomSetting['delivery_dhaka'] ?? 50 }}" class="form-control rounded-end-3">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">ঢাকা উপশহর / সাভার / গাজীপুর (৳)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light fw-bold">৳</span>
                                        <input type="number" name="delivery_sub" value="{{ $ecomSetting['delivery_sub'] ?? 100 }}" class="form-control rounded-end-3">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">ঢাকার বাইরে সমগ্র বাংলাদেশ (৳)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light fw-bold">৳</span>
                                        <input type="number" name="delivery_outside" value="{{ $ecomSetting['delivery_outside'] ?? 120 }}" class="form-control rounded-end-3">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">গিফট র‍্যাপিং চার্জ (৳)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light fw-bold">৳</span>
                                        <input type="number" name="gift_wrap_fee" value="{{ $ecomSetting['gift_wrap_fee'] ?? 20 }}" class="form-control rounded-end-3">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">ফ্রি ডেলিভারির ন্যূনতম অর্ডার লিমিট (৳)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light fw-bold">৳</span>
                                        <input type="number" name="free_delivery_threshold" value="{{ $ecomSetting['free_delivery_threshold'] ?? 1500 }}" class="form-control rounded-end-3">
                                    </div>
                                    <div class="form-text small">এই পরিমাণের বেশি অর্ডারে স্বয়ংক্রিয়ভাবে ফ্রি ডেলিভারি অফার হবে।</div>
                                </div>
                            </div>

                            <!-- Customer Support Contacts -->
                            <div class="col-lg-6">
                                <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-headset text-primary me-2"></i>অর্ডার হেল্পলাইন ও সাপোর্ট তথ্য</h6>
                                
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">অফিসিয়াল হেল্পলাইন নম্বর</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fa-solid fa-phone text-success"></i></span>
                                        <input type="text" name="helpline_phone" value="{{ $ecomSetting['helpline_phone'] ?? '01726976982' }}" class="form-control rounded-end-3">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">হোয়াটসঅ্যাপ অর্ডার নম্বর</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fa-brands fa-whatsapp text-success"></i></span>
                                        <input type="text" name="whatsapp_number" value="{{ $ecomSetting['whatsapp_number'] ?? '01726976982' }}" class="form-control rounded-end-3">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">অফিসিয়াল সাপোর্ট ইমেইল</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fa-solid fa-envelope text-primary"></i></span>
                                        <input type="email" name="helpline_email" value="{{ $ecomSetting['helpline_email'] ?? 'ideapbd@gmail.com' }}" class="form-control rounded-end-3">
                                    </div>
                                </div>

                                <div class="p-3 bg-white rounded-3 border border-2 border-danger-subtle mt-3">
                                    <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-mobile-screen-button text-danger me-1"></i> অনলাইন পেমেন্ট গেটওয়ে নম্বর</h6>
                                    
                                    <div class="row g-2 mb-2">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold text-muted mb-1">বিকাশ (bKash) নম্বর</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-pink text-white fw-bold" style="background:#d82a6f;">বিকাশ</span>
                                                <input type="text" name="bkash_number" value="{{ $ecomSetting['bkash_number'] ?? '01558712810' }}" class="form-control rounded-end-3 font-monospace">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold text-muted mb-1">নগদ (Nagad) নম্বর</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text text-white fw-bold" style="background:#e8590c;">নগদ</span>
                                                <input type="text" name="nagad_number" value="{{ $ecomSetting['nagad_number'] ?? '01558712810' }}" class="form-control rounded-end-3 font-monospace">
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="form-label small fw-semibold text-muted mb-1">পেমেন্ট নির্দেশনা (গ্রাহকদের প্রদর্শিত হবে)</label>
                                        <input type="text" name="payment_instruction" value="{{ $ecomSetting['payment_instruction'] ?? 'বিকাশ বা নগদ থেকে উল্লেখিত নম্বরে সেন্ড মানি করে TrxID ও পেমেন্ট নম্বর দিন।' }}" class="form-control form-control-sm rounded-3">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Tab: Invoice & Sender Details -->
                    <div class="tab-pane fade" id="tab-invoice" role="tabpanel">
                        <div class="row g-4">
                            
                            <!-- Sender Information Form -->
                            <div class="col-lg-7">
                                <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-building-user text-primary me-2"></i>বিলের প্রেরক (Sender) ও মেমো কনফিগারেশন</h6>
                                <p class="small text-muted mb-3">প্রতিটি প্রিন্টকৃত ইনভয়েস ও মেমোর বাম পাশে এই প্রেরকের নাম, ঠিকানা ও যোগাযোগের তথ্য প্রদর্শিত হবে।</p>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">প্রেরক / প্রকাশনীর নাম <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fa-solid fa-book-open text-primary"></i></span>
                                        <input type="text" name="invoice_sender_name" id="invSenderName" value="{{ $invoiceSetting['sender_name'] ?? 'আইডিয়া প্রকাশন' }}" class="form-control rounded-end-3" required oninput="updateInvoicePreview()">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">প্রেরকের পূর্ণাঙ্গ ঠিকানা <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fa-solid fa-location-dot text-danger"></i></span>
                                        <input type="text" name="invoice_sender_address" id="invSenderAddress" value="{{ $invoiceSetting['sender_address'] ?? 'সেন্ট্রাল রোড, রংপুর ৫৪০০, বাংলাদেশ' }}" class="form-control rounded-end-3" required oninput="updateInvoicePreview()">
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted">প্রেরকের মোবাইল নম্বর <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="fa-solid fa-phone text-success"></i></span>
                                            <input type="text" name="invoice_sender_phone" id="invSenderPhone" value="{{ $invoiceSetting['sender_phone'] ?? '01558712870' }}" class="form-control rounded-end-3" required oninput="updateInvoicePreview()">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted">অফিসিয়াল ইমেইল</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="fa-solid fa-envelope text-primary"></i></span>
                                            <input type="email" name="invoice_sender_email" id="invSenderEmail" value="{{ $invoiceSetting['sender_email'] ?? 'ideapbd@gmail.com' }}" class="form-control rounded-end-3" oninput="updateInvoicePreview()">
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted">ওয়েবসাইট ঠিকানা</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="fa-solid fa-globe text-info"></i></span>
                                            <input type="text" name="invoice_sender_website" id="invSenderWebsite" value="{{ $invoiceSetting['sender_website'] ?? 'www.ideaabd.com' }}" class="form-control rounded-end-3" oninput="updateInvoicePreview()">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted">ইনভয়েস শিরোনাম (Header Title)</label>
                                        <input type="text" name="invoice_title" id="invTitle" value="{{ $invoiceSetting['invoice_title'] ?? 'ক্যাশ মেমো / ইনভয়েস' }}" class="form-control rounded-3" oninput="updateInvoicePreview()">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">ইনভয়েসের শর্তাবলী ও চেক পলিসি (Terms)</label>
                                    <textarea name="invoice_terms" id="invTerms" rows="2" class="form-control rounded-3" oninput="updateInvoicePreview()">{{ $invoiceSetting['invoice_terms'] ?? 'পণ্য গ্রহণের সময় অনুগ্রহ করে চেক করে নিন। কোনো ত্রুটি থাকলে ডেলিভারি ম্যানের সামনেই হেল্পলাইনে যোগাযোগ করুন।' }}</textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">পাদটীকা / শুভেচ্ছা বার্তা (Footer Message)</label>
                                    <input type="text" name="invoice_footer" id="invFooter" value="{{ $invoiceSetting['invoice_footer'] ?? 'বই পড়ার আনন্দ ছড়িয়ে পড়ুক সবার মাঝে। ideaabd-এর সাথে থাকার জন্য ধন্যবাদ!' }}" class="form-control rounded-3" oninput="updateInvoicePreview()">
                                </div>
                            </div>

                            <!-- Live Invoice Sender Preview -->
                            <div class="col-lg-5">
                                <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-file-invoice text-primary me-2"></i>ইনভয়েস প্রেরক অংশ (লাইভ প্রিভিউ)</h6>
                                <div class="p-4 bg-light rounded-4 border">
                                    <div class="card border border-2 border-primary-subtle rounded-3 shadow-xs overflow-hidden">
                                        <div class="card-header bg-primary text-white py-2 px-3 d-flex justify-content-between align-items-center">
                                            <span class="small fw-bold" id="prevInvTitle">{{ $invoiceSetting['invoice_title'] ?? 'ক্যাশ মেমো / ইনভয়েস' }}</span>
                                            <span class="badge bg-white text-primary font-monospace small">#IDP-2026-1001</span>
                                        </div>
                                        <div class="card-body p-3 bg-white">
                                            <div class="d-flex align-items-center gap-2 mb-2 pb-2 border-bottom">
                                                <img src="{{ asset('images/logo.svg') }}" alt="Logo" style="height: 28px; width: auto;" onerror="this.style.display='none'">
                                                <div>
                                                    <div class="fw-bold text-dark fs-6 mb-0" id="prevSenderName">{{ $invoiceSetting['sender_name'] ?? 'আইডিয়া প্রকাশন' }}</div>
                                                    <div class="small text-muted" id="prevSenderWebsite">{{ $invoiceSetting['sender_website'] ?? 'www.ideaabd.com' }}</div>
                                                </div>
                                            </div>
                                            <div class="small text-secondary mb-1">
                                                <i class="fa-solid fa-location-dot text-danger me-1"></i>
                                                <span id="prevSenderAddress">{{ $invoiceSetting['sender_address'] ?? 'সেন্ট্রাল রোড, রংপুর ৫৪০০, বাংলাদেশ' }}</span>
                                            </div>
                                            <div class="small text-secondary mb-1">
                                                <i class="fa-solid fa-phone text-success me-1"></i>
                                                <span id="prevSenderPhone">{{ $invoiceSetting['sender_phone'] ?? '01558712870' }}</span>
                                            </div>
                                            <div class="small text-secondary mb-2">
                                                <i class="fa-solid fa-envelope text-primary me-1"></i>
                                                <span id="prevSenderEmail">{{ $invoiceSetting['sender_email'] ?? 'ideapbd@gmail.com' }}</span>
                                            </div>
                                            <div class="p-2 bg-light rounded text-muted" style="font-size: 0.75rem;">
                                                <i class="fa-solid fa-circle-info text-info me-1"></i>
                                                <span id="prevInvTerms">{{ $invoiceSetting['invoice_terms'] ?? 'পণ্য গ্রহণের সময় অনুগ্রহ করে চেক করে নিন।' }}</span>
                                            </div>
                                        </div>
                                        <div class="card-footer bg-light py-1.5 px-3 text-center border-top">
                                            <span class="text-muted small" style="font-size: 0.72rem;" id="prevInvFooter">{{ $invoiceSetting['invoice_footer'] ?? 'ideaabd-এর সাথে থাকার জন্য ধন্যবাদ!' }}</span>
                                        </div>
                                    </div>
                                    <small class="text-muted d-block text-center mt-2">অর্ডার প্রিন্ট করার সময় এই তথ্য বিলের বাম পাশে স্বয়ংক্রিয়ভাবে প্রদর্শিত হবে।</small>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Tab 5: Customizable Payment Gateways -->
                    <div class="tab-pane fade" id="tab-payment" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="fw-bold text-dark mb-1"><i class="fa-solid fa-credit-card text-danger me-2"></i>পেমেন্ট মেথড ও গেটওয়ে কাস্টমাইজেশন</h6>
                                <p class="small text-muted mb-0">গ্রাহকদের জন্য বিকাশ, নগদ, রকেট, ব্যাংক ও ক্যাশ অন ডেলিভারি নম্বর ও নিয়মাবলী নিয়ন্ত্রণ করুন।</p>
                            </div>
                        </div>

                        <div class="row g-4">
                            <!-- bKash Box -->
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-4 border h-100 border-2" style="border-color: #fce7f3 !important;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge text-white px-2.5 py-1 fw-bold" style="background:#d82a6f;">bKash বিকাশ</span>
                                            <span class="fw-bold text-dark small">বিকাশ পেমেন্ট</span>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" name="gateways[bkash][enabled]" value="1" {{ !empty($paymentGateways['bkash']['enabled']) ? 'checked' : '' }}>
                                        </div>
                                    </div>

                                    <div class="row g-2 mb-2">
                                        <div class="col-7">
                                            <label class="form-label small fw-semibold text-muted mb-1">বিকাশ নম্বর</label>
                                            <input type="text" name="gateways[bkash][number]" value="{{ $paymentGateways['bkash']['number'] ?? '01558712810' }}" class="form-control form-control-sm font-monospace rounded-3">
                                        </div>
                                        <div class="col-5">
                                            <label class="form-label small fw-semibold text-muted mb-1">অ্যাকাউন্ট টাইপ</label>
                                            <select name="gateways[bkash][type]" class="form-select form-select-sm rounded-3">
                                                <option value="personal" {{ ($paymentGateways['bkash']['type'] ?? '') === 'personal' ? 'selected' : '' }}>Personal (সেন্ড মানি)</option>
                                                <option value="merchant" {{ ($paymentGateways['bkash']['type'] ?? '') === 'merchant' ? 'selected' : '' }}>Merchant (পেমেন্ট)</option>
                                                <option value="agent" {{ ($paymentGateways['bkash']['type'] ?? '') === 'agent' ? 'selected' : '' }}>Agent (ক্যাশ আউট)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="form-label small fw-semibold text-muted mb-1">পেমেন্ট নির্দেশিকা</label>
                                        <input type="text" name="gateways[bkash][instructions]" value="{{ $paymentGateways['bkash']['instructions'] ?? 'বিকাশ অ্যাপ থেকে Send Money অপশনে গিয়ে উপরে উল্লেখিত নম্বরে সর্বমোট বিল পাঠান।' }}" class="form-control form-control-sm rounded-3">
                                    </div>
                                </div>
                            </div>

                            <!-- Nagad Box -->
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-4 border h-100 border-2" style="border-color: #ffedd5 !important;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge text-white px-2.5 py-1 fw-bold" style="background:#e8590c;">Nagad নগদ</span>
                                            <span class="fw-bold text-dark small">নগদ পেমেন্ট</span>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" name="gateways[nagad][enabled]" value="1" {{ !empty($paymentGateways['nagad']['enabled']) ? 'checked' : '' }}>
                                        </div>
                                    </div>

                                    <div class="row g-2 mb-2">
                                        <div class="col-7">
                                            <label class="form-label small fw-semibold text-muted mb-1">নগদ নম্বর</label>
                                            <input type="text" name="gateways[nagad][number]" value="{{ $paymentGateways['nagad']['number'] ?? '01558712810' }}" class="form-control form-control-sm font-monospace rounded-3">
                                        </div>
                                        <div class="col-5">
                                            <label class="form-label small fw-semibold text-muted mb-1">অ্যাকাউন্ট টাইপ</label>
                                            <select name="gateways[nagad][type]" class="form-select form-select-sm rounded-3">
                                                <option value="personal" {{ ($paymentGateways['nagad']['type'] ?? '') === 'personal' ? 'selected' : '' }}>Personal (সেন্ড মানি)</option>
                                                <option value="merchant" {{ ($paymentGateways['nagad']['type'] ?? '') === 'merchant' ? 'selected' : '' }}>Merchant (পেমেন্ট)</option>
                                                <option value="agent" {{ ($paymentGateways['nagad']['type'] ?? '') === 'agent' ? 'selected' : '' }}>Agent</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="form-label small fw-semibold text-muted mb-1">পেমেন্ট নির্দেশিকা</label>
                                        <input type="text" name="gateways[nagad][instructions]" value="{{ $paymentGateways['nagad']['instructions'] ?? 'নগদ অ্যাপ থেকে Send Money অপশনে গিয়ে উপরে উল্লেখিত নম্বরে সর্বমোট বিল পাঠান।' }}" class="form-control form-control-sm rounded-3">
                                    </div>
                                </div>
                            </div>

                            <!-- Rocket Box -->
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-4 border h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-purple text-white px-2.5 py-1 fw-bold" style="background:#8b5cf6;">Rocket রকেট</span>
                                            <span class="fw-bold text-dark small">রকেট পেমেন্ট</span>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" name="gateways[rocket][enabled]" value="1" {{ !empty($paymentGateways['rocket']['enabled']) ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                    <div class="row g-2 mb-2">
                                        <div class="col-7">
                                            <label class="form-label small fw-semibold text-muted mb-1">রকেট নম্বর (১২ ডিজিট)</label>
                                            <input type="text" name="gateways[rocket][number]" value="{{ $paymentGateways['rocket']['number'] ?? '01558712810' }}" class="form-control form-control-sm font-monospace rounded-3">
                                        </div>
                                        <div class="col-5">
                                            <label class="form-label small fw-semibold text-muted mb-1">অ্যাকাউন্ট টাইপ</label>
                                            <select name="gateways[rocket][type]" class="form-select form-select-sm rounded-3">
                                                <option value="personal">Personal</option>
                                                <option value="merchant">Merchant</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="form-label small fw-semibold text-muted mb-1">পেমেন্ট নির্দেশিকা</label>
                                        <input type="text" name="gateways[rocket][instructions]" value="{{ $paymentGateways['rocket']['instructions'] ?? 'রকেট একাউন্ট থেকে সেন্ড মানি করুন।' }}" class="form-control form-control-sm rounded-3">
                                    </div>
                                </div>
                            </div>

                            <!-- Cash on Delivery (COD) Box -->
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-4 border h-100 border-2" style="border-color: #dcfce7 !important;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-success text-white px-2.5 py-1 fw-bold">COD ক্যাশ অন ডেলিভারি</span>
                                            <span class="fw-bold text-dark small">হাতে পেয়ে পরিশোধ</span>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" name="gateways[cod][enabled]" value="1" {{ !empty($paymentGateways['cod']['enabled']) ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small fw-semibold text-muted mb-1">পদ্ধতির নাম</label>
                                        <input type="text" name="gateways[cod][name]" value="{{ $paymentGateways['cod']['name'] ?? 'ক্যাশ অন ডেলিভারি (Cash on Delivery)' }}" class="form-control form-control-sm rounded-3">
                                    </div>
                                    <div>
                                        <label class="form-label small fw-semibold text-muted mb-1">নির্দেশনা</label>
                                        <input type="text" name="gateways[cod][instructions]" value="{{ $paymentGateways['cod']['instructions'] ?? 'বই হাতে পেয়ে মূল্য পরিশোধ করুন।' }}" class="form-control form-control-sm rounded-3">
                                    </div>
                                </div>
                            </div>

                            <!-- Bank Transfer Box -->
                            <div class="col-12">
                                <div class="p-3 bg-light rounded-4 border">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-secondary text-white px-2.5 py-1 fw-bold"><i class="fa-solid fa-building-columns me-1"></i> ব্যাংক ট্রান্সফার</span>
                                            <span class="fw-bold text-dark small">সরাসরি ব্যাংক ডিপোজিট</span>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" name="gateways[bank][enabled]" value="1" {{ !empty($paymentGateways['bank']['enabled']) ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                    <div class="row g-2 mb-2">
                                        <div class="col-md-3">
                                            <label class="form-label small fw-semibold text-muted mb-1">ব্যাংকের নাম</label>
                                            <input type="text" name="gateways[bank][bank_name]" value="{{ $paymentGateways['bank']['bank_name'] ?? 'Islami Bank Bangladesh Ltd' }}" class="form-control form-control-sm rounded-3">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-semibold text-muted mb-1">হিসাবের নাম (Account Name)</label>
                                            <input type="text" name="gateways[bank][account_name]" value="{{ $paymentGateways['bank']['account_name'] ?? 'Idea Prokashon' }}" class="form-control form-control-sm rounded-3">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-semibold text-muted mb-1">হিসাব নম্বর (Account No)</label>
                                            <input type="text" name="gateways[bank][account_no]" value="{{ $paymentGateways['bank']['account_no'] ?? '2050XXXXXXXXX' }}" class="form-control form-control-sm font-monospace rounded-3">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-semibold text-muted mb-1">শাখা (Branch Name)</label>
                                            <input type="text" name="gateways[bank][branch]" value="{{ $paymentGateways['bank']['branch'] ?? 'Rangpur Branch' }}" class="form-control form-control-sm rounded-3">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="form-label small fw-semibold text-muted mb-1">ব্যাংক ট্রান্সফার নির্দেশিকা</label>
                                        <input type="text" name="gateways[bank][instructions]" value="{{ $paymentGateways['bank']['instructions'] ?? 'ব্যাংক ডিপোজিট করে রসিদ স্লিপ বা রেফারেন্স নম্বর দিন।' }}" class="form-control form-control-sm rounded-3">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 6: Server Diagnostics & Maintenance -->
                    <div class="tab-pane fade" id="tab-system" role="tabpanel">
                        <div class="row g-4">
                            
                            <!-- Diagnostics Table -->
                            <div class="col-lg-7">
                                <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-gauge-high text-secondary me-2"></i>সার্ভার ও সিস্টেম ডায়াগনস্টিকস</h6>
                                
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover align-middle mb-0">
                                        <tbody>
                                            <tr>
                                                <th class="text-muted fw-semibold">PHP ভার্সন</th>
                                                <td class="text-dark fw-bold font-monospace">{{ $diagnostics['php_version'] }}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted fw-semibold">Laravel ফ্রেমওয়ার্ক</th>
                                                <td class="text-dark fw-bold font-monospace">{{ $diagnostics['laravel_version'] }}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted fw-semibold">ডাটাবেজ ড্রাইভার</th>
                                                <td class="text-dark fw-bold text-uppercase">{{ $diagnostics['db_connection'] }}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted fw-semibold">এনভায়রনমেন্ট মোড</th>
                                                <td><span class="badge bg-info-subtle text-info border border-info-subtle">{{ strtoupper($diagnostics['app_env']) }}</span></td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted fw-semibold">ডিবাগ মোড</th>
                                                <td>{{ $diagnostics['app_debug'] }}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted fw-semibold">পাবলিক ফাইল স্টোরেজ</th>
                                                <td><span class="badge bg-success-subtle text-success border border-success-subtle">{{ $diagnostics['storage_link'] }}</span></td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted fw-semibold">হোস্ট অপারেটিং সিস্টেম</th>
                                                <td class="text-dark">{{ $diagnostics['server_os'] }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Maintenance Mode Switch -->
                            <div class="col-lg-5">
                                <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-screwdriver-wrench text-danger me-2"></i>মেইনটেন্যান্স মোড</h6>
                                
                                <div class="p-4 bg-light rounded-4 border">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input cursor-pointer" type="checkbox" name="maintenance_mode" value="1" id="maintSwitch" {{ !empty($maintSetting['enabled']) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-dark cursor-pointer" for="maintSwitch">
                                            মেইনটেন্যান্স মোড সক্রিয় করুন
                                        </label>
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label small text-muted">রক্ষণাবেক্ষণ বার্তা (ভিজিটররা যা দেখবেন)</label>
                                        <textarea name="maintenance_reason" class="form-control rounded-3" rows="3" placeholder="ওয়েবসাইটে নিয়মিত সিস্টেম আপডেট চলছে। কিছুক্ষণের মধ্যেই সাইট উন্মুক্ত হবে।">{{ $maintSetting['reason'] ?? '' }}</textarea>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>

            <!-- Footer Save Button Bar -->
            <div class="card-footer bg-light border-top p-4 d-flex justify-content-between align-items-center">
                <span class="small text-muted d-none d-sm-inline">
                    <i class="fa-solid fa-shield-halved text-success me-1"></i> সেটিংস পরিবর্তনের সাথে সাথে তা সর্বত্র কার্যকর হবে।
                </span>
                <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold shadow-sm ms-auto">
                    <i class="fa-solid fa-floppy-disk me-1.5"></i> সেটিংস সংরক্ষণ করুন
                </button>
            </div>

        </div>
    </form>
</div>

<!-- Interactive Image Cropper Modal -->
<div class="modal fade" id="imageCropperModal" tabindex="-1" aria-labelledby="imageCropperModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title fw-bold fs-6 d-flex align-items-center gap-2" id="imageCropperModalLabel">
                    <i class="fa-solid fa-crop-simple text-primary"></i> ইমেজ ক্রপ ও সাইজ অ্যাডজাস্টমেন্ট
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 bg-dark">
                <!-- Cropper Canvas Container -->
                <div style="max-height: 480px; width: 100%; display: flex; align-items: center; justify-content: center; background: #0f172a; border-radius: 8px; overflow: hidden;">
                    <img id="cropperImageElement" src="" alt="Crop Target" style="max-width: 100%; max-height: 460px; display: block;">
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <!-- Control Buttons -->
                <div class="btn-group shadow-xs">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cropper.zoom(0.1)" title="জুম ইন">
                        <i class="fa-solid fa-magnifying-glass-plus"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cropper.zoom(-0.1)" title="জুম আউট">
                        <i class="fa-solid fa-magnifying-glass-minus"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cropper.rotate(-90)" title="বামে ঘোরান">
                        <i class="fa-solid fa-rotate-left"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cropper.rotate(90)" title="ডানে ঘোরান">
                        <i class="fa-solid fa-rotate-right"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cropper.reset()" title="রিসেট">
                        <i class="fa-solid fa-arrows-rotate"></i>
                    </button>
                </div>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-3 fw-semibold" data-bs-dismiss="modal">বাতিল</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-xs" onclick="applyCroppedImage()">
                        <i class="fa-solid fa-check me-1.5"></i> ক্রপ সম্পন্ন করুন
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
<script>
    let cropper = null;
    let currentTargetType = '';
    let currentAspectRatio = NaN;

    function initCropper(input, targetType, aspectRatio) {
        if (input.files && input.files[0]) {
            currentTargetType = targetType;
            currentAspectRatio = aspectRatio;

            const file = input.files[0];
            const reader = new FileReader();

            reader.onload = function(e) {
                const imageEl = document.getElementById('cropperImageElement');
                imageEl.src = e.target.result;

                const cropModal = new bootstrap.Modal(document.getElementById('imageCropperModal'));
                cropModal.show();

                document.getElementById('imageCropperModal').addEventListener('shown.bs.modal', function onModalShown() {
                    if (cropper) {
                        cropper.destroy();
                    }
                    cropper = new Cropper(imageEl, {
                        aspectRatio: isNaN(aspectRatio) ? NaN : aspectRatio,
                        viewMode: 2,
                        autoCropArea: 1,
                        responsive: true,
                        restore: false,
                        guides: true,
                        center: true,
                        highlight: false,
                        cropBoxMovable: true,
                        cropBoxResizable: true,
                        toggleDragModeOnDblclick: false,
                    });
                    document.getElementById('imageCropperModal').removeEventListener('shown.bs.modal', onModalShown);
                });
            }
            reader.readAsDataURL(file);
        }
    }

    function applyCroppedImage() {
        if (!cropper) return;

        let canvasOpts = {};
        if (currentTargetType === 'banner_1' || currentTargetType === 'banner_2') {
            canvasOpts = { width: 1200, height: 400, imageSmoothingQuality: 'high' };
        } else if (currentTargetType === 'logo') {
            canvasOpts = { height: 120, imageSmoothingQuality: 'high' };
        } else if (currentTargetType === 'favicon') {
            canvasOpts = { width: 128, height: 128, imageSmoothingQuality: 'high' };
        }

        const croppedCanvas = cropper.getCroppedCanvas(canvasOpts);
        const base64Data = croppedCanvas.toDataURL('image/png');

        if (currentTargetType === 'logo') {
            document.getElementById('site_logo_cropped').value = base64Data;
            const preview = document.getElementById('logoPreviewImg');
            preview.outerHTML = `<img src="${base64Data}" alt="Site Logo" id="logoPreviewImg" style="max-height: 52px; max-width: 220px; width: auto; height: auto; object-fit: contain;">`;
        } else if (currentTargetType === 'favicon') {
            document.getElementById('site_favicon_cropped').value = base64Data;
            const preview = document.getElementById('faviconPreviewImg');
            preview.outerHTML = `<img src="${base64Data}" alt="Favicon" class="w-100 h-100 object-fit-contain" id="faviconPreviewImg">`;
        } else if (currentTargetType === 'banner_1') {
            document.getElementById('banner_1_cropped').value = base64Data;
            const preview = document.getElementById('banner1PreviewImg');
            preview.outerHTML = `<img src="${base64Data}" alt="Banner 1" class="w-100 h-100 object-fit-cover" id="banner1PreviewImg">`;
        } else if (currentTargetType === 'banner_2') {
            document.getElementById('banner_2_cropped').value = base64Data;
            const preview = document.getElementById('banner2PreviewImg');
            preview.outerHTML = `<img src="${base64Data}" alt="Banner 2" class="w-100 h-100 object-fit-cover" id="banner2PreviewImg">`;
        }

        const modalEl = document.getElementById('imageCropperModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
    }

    function applyPresetTheme(primary, secondary) {
        document.getElementById('primaryColorPicker').value = primary;
        document.getElementById('primaryColorInput').value = primary;
        document.getElementById('secondaryColorPicker').value = secondary;
        document.getElementById('secondaryColorInput').value = secondary;
        document.documentElement.style.setProperty('--brand', primary);
        document.documentElement.style.setProperty('--brand-2', secondary);
    }

    function updateLiveNoticePreview() {
        const text = document.getElementById('noticeTextInput').value || 'এখানে নোটিশ বার্তাটি লাইভ প্রদর্শিত হবে।';
        const type = document.getElementById('noticeTypeSelect').value;
        const isActive = document.getElementById('noticeActiveSwitch').checked;
        const box = document.getElementById('noticePreviewBox');
        const textEl = document.getElementById('noticePreviewText');
        const statusEl = document.getElementById('noticeStatusText');

        box.className = `alert alert-${type} rounded-3 shadow-xs d-flex align-items-center gap-2 mb-0 ${!isActive ? 'opacity-50' : ''}`;
        textEl.textContent = text;
        statusEl.textContent = isActive ? '🟢 বর্তমানে সাইটে নোটিশ সক্রিয় রয়েছে' : '⚪ নোটিশটি বর্তমানে বন্ধ রাখা হয়েছে';
    }

    function updateInvoicePreview() {
        const name = document.getElementById('invSenderName')?.value || 'আইডিয়া প্রকাশন';
        const addr = document.getElementById('invSenderAddress')?.value || 'সেন্ট্রাল রোড, রংপুর ৫৪০০, বাংলাদেশ';
        const phone = document.getElementById('invSenderPhone')?.value || '01558712870';
        const email = document.getElementById('invSenderEmail')?.value || 'ideapbd@gmail.com';
        const website = document.getElementById('invSenderWebsite')?.value || 'www.ideaabd.com';
        const title = document.getElementById('invTitle')?.value || 'ক্যাশ মেমো / ইনভয়েস';
        const terms = document.getElementById('invTerms')?.value || 'পণ্য গ্রহণের সময় অনুগ্রহ করে চেক করে নিন।';
        const footer = document.getElementById('invFooter')?.value || 'ideaabd-এর সাথে থাকার জন্য ধন্যবাদ!';

        if (document.getElementById('prevSenderName')) document.getElementById('prevSenderName').textContent = name;
        if (document.getElementById('prevSenderAddress')) document.getElementById('prevSenderAddress').textContent = addr;
        if (document.getElementById('prevSenderPhone')) document.getElementById('prevSenderPhone').textContent = phone;
        if (document.getElementById('prevSenderEmail')) document.getElementById('prevSenderEmail').textContent = email;
        if (document.getElementById('prevSenderWebsite')) document.getElementById('prevSenderWebsite').textContent = website;
        if (document.getElementById('prevInvTitle')) document.getElementById('prevInvTitle').textContent = title;
        if (document.getElementById('prevInvTerms')) document.getElementById('prevInvTerms').textContent = terms;
        if (document.getElementById('prevInvFooter')) document.getElementById('prevInvFooter').textContent = footer;
    }
</script>
@endpush
@endsection
