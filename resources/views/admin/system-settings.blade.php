@extends('layouts.admin')

@section('title', 'System Settings & Theme Control')
@section('heading', 'System Settings & Theme Control Panel')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">System Settings</li>
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
    .og-preview-box {
        width: 100%;
        max-width: 460px;
        aspect-ratio: 16 / 9;
    }
    .cropper-view-box,
    .cropper-face {
        border-radius: 6px;
    }
</style>
@endpush

@php
    $logoUrl = \App\Support\SiteSetting::logoUrl();
    $banner1Url = \App\Support\SiteSetting::banner1Url();
    $banner2Url = \App\Support\SiteSetting::banner2Url();
    $faviconUrl = \App\Support\SiteSetting::faviconUrl();
    $blogOgBannerUrl = \App\Support\SiteSetting::blogOgBannerUrl();
@endphp

@section('content')
<div class="system-settings-wrapper pb-5">
    
    <!-- Top Action Bar -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-body p-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-sliders text-primary"></i> System Settings, Image Cropper & Theme Control
                </h5>
                <p class="text-muted small mb-0">Control logos, promotional banners, delivery fees, notice bar, invoice details, and color palettes.</p>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <!-- Save Button in Top Header -->
                <button type="submit" form="systemSettingsForm" class="btn btn-primary btn-sm rounded-pill px-3.5 py-2 fw-bold d-inline-flex align-items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-floppy-disk"></i> Save Changes
                </button>
                <!-- Clear Cache Form -->
                <form action="{{ route('admin.system-settings.clear-cache') }}" method="POST" data-confirm="আপনি কি নিশ্চিত যে সিস্টেম ক্যাশ পরিষ্কার (Clear Cache) করতে চান?" data-confirm-title="সিস্টেম ক্যাশ">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-xs">
                        <i class="fa-solid fa-broom"></i> Clear Cache
                    </button>
                </form>
                <!-- Live Site Link -->
                <a href="{{ route('home') }}" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-xs">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> View Website
                </a>
            </div>
        </div>
    </div>

    <!-- Main Settings Form -->
    <form action="{{ route('admin.system-settings.update') }}" method="POST" enctype="multipart/form-data" id="systemSettingsForm">
        @csrf

        <!-- Hidden Inputs for Cropped Base64 Images & Dynamic Menus -->
        <input type="hidden" name="site_logo_cropped" id="site_logo_cropped">
        <input type="hidden" name="site_favicon_cropped" id="site_favicon_cropped">
        <input type="hidden" name="banner_1_cropped" id="banner_1_cropped">
        <input type="hidden" name="banner_2_cropped" id="banner_2_cropped">
        <input type="hidden" name="header_menu_items" id="header_menu_items">
        <input type="hidden" name="home_hero_slides" id="home_hero_slides">

        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
            
            <!-- Navigation Tabs Header -->
            <div class="card-header bg-white border-bottom p-3 p-md-4">
                <ul class="nav nav-pills nav-fill gap-2" id="settingsTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill fw-semibold py-2.5 px-3 d-flex align-items-center justify-content-center gap-2" 
                                id="tab-brand-btn" data-bs-toggle="pill" data-bs-target="#tab-brand" type="button" role="tab">
                            <i class="fa-solid fa-crop-simple text-primary"></i>
                            <span>Branding & Logo</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill fw-semibold py-2.5 px-3 d-flex align-items-center justify-content-center gap-2" 
                                id="tab-hero-btn" data-bs-toggle="pill" data-bs-target="#tab-hero" type="button" role="tab">
                            <i class="fa-solid fa-images text-warning"></i>
                            <span>Hero Slides & Carousel</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill fw-semibold py-2.5 px-3 d-flex align-items-center justify-content-center gap-2" 
                                id="tab-menu-btn" data-bs-toggle="pill" data-bs-target="#tab-menu" type="button" role="tab">
                            <i class="fa-solid fa-compass text-success"></i>
                            <span>Menu Builder & Nav</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill fw-semibold py-2.5 px-3 d-flex align-items-center justify-content-center gap-2" 
                                id="tab-theme-btn" data-bs-toggle="pill" data-bs-target="#tab-theme" type="button" role="tab">
                            <i class="fa-solid fa-wand-magic-sparkles text-info"></i>
                            <span>Theme & Colors</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill fw-semibold py-2.5 px-3 d-flex align-items-center justify-content-center gap-2" 
                                id="tab-notice-btn" data-bs-toggle="pill" data-bs-target="#tab-notice" type="button" role="tab">
                            <i class="fa-solid fa-bullhorn text-warning"></i>
                            <span>Notice Banner</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill fw-semibold py-2.5 px-3 d-flex align-items-center justify-content-center gap-2" 
                                id="tab-ecom-btn" data-bs-toggle="pill" data-bs-target="#tab-ecom" type="button" role="tab">
                            <i class="fa-solid fa-truck-fast text-success"></i>
                            <span>Delivery & Helpline</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill fw-semibold py-2.5 px-3 d-flex align-items-center justify-content-center gap-2" 
                                id="tab-invoice-btn" data-bs-toggle="pill" data-bs-target="#tab-invoice" type="button" role="tab">
                            <i class="fa-solid fa-file-invoice-dollar text-primary"></i>
                            <span>Invoice & Sender Info</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill fw-semibold py-2.5 px-3 d-flex align-items-center justify-content-center gap-2" 
                                id="tab-payment-btn" data-bs-toggle="pill" data-bs-target="#tab-payment" type="button" role="tab">
                            <i class="fa-solid fa-credit-card text-danger"></i>
                            <span>Payment Gateways</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill fw-semibold py-2.5 px-3 d-flex align-items-center justify-content-center gap-2" 
                                id="tab-editorial-btn" data-bs-toggle="pill" data-bs-target="#tab-editorial" type="button" role="tab">
                            <i class="fa-solid fa-feather-pointed text-primary"></i>
                            <span>আইডিয়াপত্র ও সম্পাদনা পরিষদ</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill fw-semibold py-2.5 px-3 d-flex align-items-center justify-content-center gap-2" 
                                id="tab-system-btn" data-bs-toggle="pill" data-bs-target="#tab-system" type="button" role="tab">
                            <i class="fa-solid fa-server text-secondary"></i>
                            <span>Server Diagnostics</span>
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
                                <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-signature text-primary me-2"></i>Site Name & Identity</h6>
                                
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">Website Name</label>
                                    <input type="text" name="site_name" value="{{ $settings['site_name'] ?? config('brand.name', 'Idea Prakashan') }}" class="form-control rounded-3" placeholder="e.g. Idea Prakashan">
                                </div>

                                <div class="mb-4">
                                    <label class="form-label small fw-semibold text-muted">Tagline / Slogan</label>
                                    <input type="text" name="site_tagline" value="{{ $settings['site_tagline'] ?? 'Books & Digital Publications' }}" class="form-control rounded-3" placeholder="e.g. Books & Digital Publications">
                                </div>

                                <!-- Favicon Upload Box with Fixed Container & Cropper -->
                                <div class="p-3 bg-light rounded-4 border mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label small fw-bold text-dark mb-0">Favicon Icon (1:1 Aspect Ratio)</label>
                                        <span class="badge bg-white text-muted border rounded-pill px-2.5 py-0.5 small">Square 1:1</span>
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
                                            <div class="form-text small text-muted">Selecting an image will open the crop & resize tool.</div>
                                            @if($faviconUrl)
                                                <div class="form-check mt-1">
                                                    <input class="form-check-input" type="checkbox" name="remove_site_favicon" value="1" id="rmFavicon">
                                                    <label class="form-check-label small text-danger fw-semibold" for="rmFavicon">Remove Favicon</label>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Modern World-Class Logo Sizing, Navbar Simulator & Cropper -->
                            <div class="col-lg-6">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                        <i class="fa-solid fa-image text-primary"></i>
                                        <span>Primary Site Logo & Live Resizer</span>
                                    </h6>
                                    <div class="btn-group btn-group-sm shadow-2xs rounded-pill p-0.5 bg-light border" role="group" aria-label="Logo Preview Background">
                                        <button type="button" class="btn btn-sm btn-white active rounded-pill px-2.5 py-1 text-dark fw-semibold" onclick="setLogoBg('white', this)" title="Light Header">
                                            <i class="fa-solid fa-sun text-warning me-1"></i> Light
                                        </button>
                                        <button type="button" class="btn btn-sm btn-light rounded-pill px-2.5 py-1 text-dark fw-semibold" onclick="setLogoBg('dark', this)" title="Dark Header">
                                            <i class="fa-solid fa-moon text-info me-1"></i> Dark
                                        </button>
                                        <button type="button" class="btn btn-sm btn-light rounded-pill px-2.5 py-1 text-dark fw-semibold" onclick="setLogoBg('grid', this)" title="Transparent Grid">
                                            <i class="fa-solid fa-border-none text-muted me-1"></i> Grid
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="card p-3.5 bg-white rounded-4 border shadow-sm">
                                    
                                    {{-- Live Navbar Simulator Box --}}
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 small fw-bold">
                                                <i class="fa-solid fa-desktop me-1"></i> Live Header Preview
                                            </span>
                                            <span class="text-muted small" id="logoDimBadge">
                                                Height: <strong id="lblHeight">{{ \App\Support\SiteSetting::logoHeight() }}px</strong> | 
                                                Width: <strong id="lblWidth">{{ \App\Support\SiteSetting::logoWidth() }}px</strong> | 
                                                Scale: <strong id="lblScale">{{ \App\Support\SiteSetting::logoScale() }}%</strong>
                                            </span>
                                        </div>

                                        <div class="navbar-simulator p-2.5 rounded-3 border d-flex align-items-center justify-content-between transition-all" 
                                             id="logoContainer" 
                                             style="background: #ffffff; min-height: 74px; border-color: #cbd5e1 !important;">
                                            
                                            <!-- Logo Target Box -->
                                            <div class="d-flex align-items-center gap-2" id="simBrandBox">
                                                <div class="d-flex align-items-center justify-content-center overflow-hidden flex-shrink-0" 
                                                     id="logoWrapper"
                                                     style="height: {{ \App\Support\SiteSetting::logoHeight() }}px; max-height: {{ \App\Support\SiteSetting::logoHeight() }}px; max-width: {{ \App\Support\SiteSetting::logoWidth() }}px; padding: {{ \App\Support\SiteSetting::logoPaddingY() }}px {{ \App\Support\SiteSetting::logoPaddingX() }}px; transition: all 0.15s ease;">
                                                    @if($logoUrl)
                                                        <img src="{{ $logoUrl }}" alt="Site Logo" id="logoPreviewImg" 
                                                             style="max-height: 100%; max-width: 100%; width: auto; height: auto; object-fit: contain; transform: scale({{ \App\Support\SiteSetting::logoScale() / 100 }}); transition: all 0.15s ease;">
                                                    @else
                                                        <div class="d-flex align-items-center gap-2 text-primary" id="logoPreviewImg" style="transform: scale({{ \App\Support\SiteSetting::logoScale() / 100 }});">
                                                            <span class="badge bg-primary text-white p-2 rounded fs-5">ID</span>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="site-brand__text d-flex flex-column justify-content-center" id="simBrandText" style="{{ \App\Support\SiteSetting::showBrandText() ? '' : 'display: none !important;' }}">
                                                    <span class="fw-bold text-primary fs-5 lh-1" id="simSiteName">{{ \App\Support\SiteSetting::name() }}</span>
                                                    <small class="text-muted" style="font-size: 11px;">{{ \App\Support\SiteSetting::tagline() }}</small>
                                                </div>
                                            </div>

                                            <!-- Mock Navbar Navigation elements on the right -->
                                            <div class="d-none d-sm-flex align-items-center gap-2 opacity-50 pe-1">
                                                <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1 small"><i class="fa-solid fa-search me-1"></i>সার্চ</span>
                                                <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1 small"><i class="fa-solid fa-cart-shopping me-1"></i>কার্ট (০)</span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Sliders for Height, Width, Scale, and Padding --}}
                                    <div class="p-3 bg-light rounded-3 border mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                                            <span class="fw-bold text-dark small"><i class="fa-solid fa-sliders text-primary me-1"></i> লোগোর আকার, পেডিং ও অ্যালাইনমেন্ট সমন্বয়</span>
                                            <button type="button" class="btn btn-sm btn-link text-primary p-0 text-decoration-none small fw-semibold" onclick="resetLogoSliders()">
                                                <i class="fa-solid fa-rotate-left me-1"></i> ডিফল্ট সাইজ
                                            </button>
                                        </div>

                                        <div class="row g-2 mb-2">
                                            <!-- Height Slider -->
                                            <div class="col-md-4">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <label class="form-label small fw-semibold text-muted mb-0">উচ্চতা (Height)</label>
                                                    <span class="badge bg-white text-dark border px-2 py-0.5 small" id="badgeHeight">{{ \App\Support\SiteSetting::logoHeight() }}px</span>
                                                </div>
                                                <input type="range" class="form-range" name="site_logo_height" id="sliderLogoHeight" 
                                                       min="30" max="100" step="2" value="{{ \App\Support\SiteSetting::logoHeight() }}" 
                                                       oninput="updateLogoDimensions()">
                                            </div>

                                            <!-- Width Slider -->
                                            <div class="col-md-4">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <label class="form-label small fw-semibold text-muted mb-0">সর্বোচ্চ প্রস্থ (Max Width)</label>
                                                    <span class="badge bg-white text-dark border px-2 py-0.5 small" id="badgeWidth">{{ \App\Support\SiteSetting::logoWidth() }}px</span>
                                                </div>
                                                <input type="range" class="form-range" name="site_logo_width" id="sliderLogoWidth" 
                                                       min="80" max="360" step="5" value="{{ \App\Support\SiteSetting::logoWidth() }}" 
                                                       oninput="updateLogoDimensions()">
                                            </div>

                                            <!-- Scale Slider -->
                                            <div class="col-md-4">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <label class="form-label small fw-semibold text-muted mb-0">স্কেল / জুম (Scale)</label>
                                                    <span class="badge bg-white text-dark border px-2 py-0.5 small" id="badgeScale">{{ \App\Support\SiteSetting::logoScale() }}%</span>
                                                </div>
                                                <input type="range" class="form-range" name="site_logo_scale" id="sliderLogoScale" 
                                                       min="50" max="150" step="5" value="{{ \App\Support\SiteSetting::logoScale() }}" 
                                                       oninput="updateLogoDimensions()">
                                            </div>
                                        </div>

                                        <div class="row g-2 pt-2 border-top">
                                            <!-- Vertical Padding (Y) -->
                                            <div class="col-md-6">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <label class="form-label small fw-semibold text-muted mb-0">উপরে-নিচে পেডিং (Padding Y)</label>
                                                    <span class="badge bg-white text-dark border px-2 py-0.5 small" id="badgePadY">{{ \App\Support\SiteSetting::logoPaddingY() }}px</span>
                                                </div>
                                                <input type="range" class="form-range" name="site_logo_padding_y" id="sliderLogoPadY" 
                                                       min="0" max="25" step="1" value="{{ \App\Support\SiteSetting::logoPaddingY() }}" 
                                                       oninput="updateLogoDimensions()">
                                            </div>

                                            <!-- Horizontal Padding (X) -->
                                            <div class="col-md-6">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <label class="form-label small fw-semibold text-muted mb-0">ডানে-বামে পেডিং (Padding X)</label>
                                                    <span class="badge bg-white text-dark border px-2 py-0.5 small" id="badgePadX">{{ \App\Support\SiteSetting::logoPaddingX() }}px</span>
                                                </div>
                                                <input type="range" class="form-range" name="site_logo_padding_x" id="sliderLogoPadX" 
                                                       min="0" max="30" step="1" value="{{ \App\Support\SiteSetting::logoPaddingX() }}" 
                                                       oninput="updateLogoDimensions()">
                                            </div>
                                        </div>

                                        <div class="pt-2 mt-2 border-top">
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input" type="checkbox" name="site_logo_show_text" value="1" id="chkShowBrandText" {{ \App\Support\SiteSetting::showBrandText() ? 'checked' : '' }} onchange="updateLogoDimensions()">
                                                <label class="form-check-label small fw-semibold text-dark" for="chkShowBrandText">
                                                    লোগোর পাশে সাইটের নাম ও স্লোগান দেখান (Show Brand Name Text Beside Logo)
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Upload & Cropper Actions -->
                                    <div class="text-start mb-2">
                                        <label class="form-label small fw-bold text-dark mb-1">নতুন লোগো আপলোড ও ক্রপ করুন:</label>
                                        <div class="input-group">
                                            <input type="file" id="logoInput" name="site_logo" class="form-control rounded-start-3" accept="image/*" onchange="initCropper(this, 'logo', 0)">
                                            <button type="button" class="btn btn-outline-primary fw-semibold" onclick="document.getElementById('logoInput').click()">
                                                <i class="fa-solid fa-crop-simple me-1"></i> ছবি নির্বাচন
                                            </button>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                        <span class="small text-muted">
                                            <i class="fa-solid fa-circle-info text-primary me-1"></i> পিএনজি (PNG) বা এসভিজি (SVG) ফরম্যাট প্রস্তাবিত।
                                        </span>
                                        @if($logoUrl)
                                            <div class="form-check mb-0">
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
                                        <h6 class="fw-bold text-dark mb-1"><i class="fa-solid fa-rectangle-ad text-primary me-2"></i>Homepage Promotional Banners (Fixed 3:1 Aspect Ratio)</h6>
                                        <p class="small text-muted mb-0">Banners are automatically cropped to 3:1 ratio to preserve perfect responsive alignment across all devices.</p>
                                    </div>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1.5 fw-bold">
                                        1200 × 400 px (3:1 Ratio)
                                    </span>
                                </div>
                                
                                <div class="row g-4">
                                    
                                    <!-- Banner 1 -->
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-4 border h-100">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <label class="form-label small fw-bold text-dark mb-0">Banner 1 (Featured Promotion / Offers)</label>
                                                <span class="badge bg-white text-muted border small">3:1 Fixed Frame</span>
                                            </div>
                                            
                                            <!-- Fixed Aspect Ratio 3:1 Box -->
                                            <div class="fixed-preview-container banner-preview-box mb-3" id="banner1Container">
                                                @if($banner1Url)
                                                    <img src="{{ $banner1Url }}" alt="Banner 1" class="w-100 h-100 object-fit-cover" id="banner1PreviewImg">
                                                @else
                                                    <div class="text-muted small text-center p-3" id="banner1PreviewImg">
                                                        <i class="fa-solid fa-image display-6 opacity-25 d-block mb-1"></i>
                                                        Upload & Crop Banner 1
                                                    </div>
                                                @endif
                                            </div>

                                            <input type="file" id="banner1Input" name="banner_1" class="form-control form-control-sm rounded-3 mb-1" accept="image/*" onchange="initCropper(this, 'banner_1', 3/1)">
                                            <div class="form-text small text-muted">Selecting an image will open the 3:1 aspect ratio crop tool.</div>
                                        </div>
                                    </div>

                                    <!-- Banner 2 -->
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-4 border h-100">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <label class="form-label small fw-bold text-dark mb-0">Banner 2 (E-Books / Special Campaigns)</label>
                                                <span class="badge bg-white text-muted border small">3:1 Fixed Frame</span>
                                            </div>
                                            
                                            <!-- Fixed Aspect Ratio 3:1 Box -->
                                            <div class="fixed-preview-container banner-preview-box mb-3" id="banner2Container">
                                                @if($banner2Url)
                                                    <img src="{{ $banner2Url }}" alt="Banner 2" class="w-100 h-100 object-fit-cover" id="banner2PreviewImg">
                                                @else
                                                    <div class="text-muted small text-center p-3" id="banner2PreviewImg">
                                                        <i class="fa-solid fa-image display-6 opacity-25 d-block mb-1"></i>
                                                        Upload & Crop Banner 2
                                                    </div>
                                                @endif
                                            </div>

                                            <input type="file" id="banner2Input" name="banner_2" class="form-control form-control-sm rounded-3 mb-1" accept="image/*" onchange="initCropper(this, 'banner_2', 3/1)">
                                            <div class="form-text small text-muted">Selecting an image will open the 3:1 aspect ratio crop tool.</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Social Media & Open Graph Banner Section -->
                                <div class="col-12 pt-4 border-top mt-3">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                                        <div>
                                            <h6 class="fw-bold text-dark mb-1">
                                                <i class="fa-solid fa-share-nodes text-primary me-2"></i>Social Open Graph Sharing Banner
                                            </h6>
                                            <p class="small text-muted mb-0">Displayed as preview card when website or blog links are shared on Facebook, WhatsApp, X/Twitter, or LinkedIn.</p>
                                        </div>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 fw-bold">
                                            1200 × 630 px (16:9 / 1.91:1)
                                        </span>
                                    </div>

                                    <div class="p-4 bg-light rounded-4 border">
                                        <div class="row g-3 align-items-center">
                                            <div class="col-lg-6">
                                                <!-- Fixed Aspect Ratio 16:9 Preview Box -->
                                                <div class="fixed-preview-container og-preview-box shadow-sm mx-auto" id="blogOgContainer" style="max-height: 240px; border-radius: 14px; overflow: hidden; background: #0f172a;">
                                                    @if($blogOgBannerUrl)
                                                        <img src="{{ $blogOgBannerUrl }}" alt="Social Share Banner" class="w-100 h-100 object-fit-cover" id="blog_ogPreviewImg">
                                                    @else
                                                        <div class="text-muted small text-center p-3 text-white-50" id="blog_ogPreviewImg">
                                                            <i class="fa-solid fa-image display-6 opacity-25 d-block mb-1"></i>
                                                            Upload & Crop Social Share Banner
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <label class="form-label small fw-bold text-dark mb-1">Select Banner Image:</label>
                                                <input type="file" id="blogOgInput" name="blog_og_banner" class="form-control rounded-3 mb-2" accept="image/*" onchange="initCropper(this, 'blog_og', 16/9)">
                                                <div class="form-text small text-muted mb-3">
                                                    <i class="fa-solid fa-lightbulb text-warning me-1"></i>
                                                    Upload any 1200×630 (or 16:9) image. The live cropper will open automatically upon selection.
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    @if($blogOgBannerUrl)
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="remove_blog_og_banner" value="1" id="rmBlogOg">
                                                            <label class="form-check-label small text-danger fw-semibold" for="rmBlogOg">Remove custom banner & use default</label>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Hero Carousel & Slides Customizer -->
                    <div class="tab-pane fade" id="tab-hero" role="tabpanel">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 pb-3 border-bottom">
                            <div>
                                <h6 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-images text-warning"></i>
                                    <span>হোমপেজ ব্যানার ও হিরো স্লাইডার কাস্টমাইজার (Hero Carousel Slides & Icons)</span>
                                </h6>
                                <p class="text-muted small mb-0">হোমপেজের ৩টি মূল ব্যানার স্লাইডের লেখা, আকর্ষণীয় আধুনিক ৩ডি/গ্লাস আইকন, ব্যাকগ্রাউন্ড গ্রেডিয়েন্ট ও বাটন লিঙ্ক সহজে পরিবর্তন ও যোগ করুন।</p>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1.5 fw-semibold shadow-2xs" onclick="resetHeroSlidesToDefaults()">
                                    <i class="fa-solid fa-rotate-left me-1"></i> ডিফল্ট স্লাইড রিস্টোর
                                </button>
                                <button type="button" class="btn btn-warning btn-sm rounded-pill px-3.5 py-1.5 fw-bold shadow-xs text-dark d-inline-flex align-items-center gap-1.5" onclick="addNewHeroSlide()">
                                    <i class="fa-solid fa-plus"></i> নতুন স্লাইড যোগ করুন
                                </button>
                            </div>
                        </div>

                        <!-- Live Slide Simulator Preview -->
                        <div class="card p-3.5 bg-light rounded-4 border shadow-2xs mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-white text-dark border px-2.5 py-1 small fw-bold d-inline-flex align-items-center gap-1">
                                    <i class="fa-solid fa-eye text-primary"></i> লাইভ স্লাইডার সিমুলেটর প্রিভিউ (Live Slide Simulator)
                                </span>
                                <span class="text-muted small" id="liveSlideCountBadge">৬টি সক্রিয় স্লাইড</span>
                            </div>
                            <div class="rounded-4 overflow-hidden shadow-sm p-4 text-white position-relative" id="liveHeroSlideSimulator" style="background: linear-gradient(135deg, #003366 0%, #0066cc 100%); min-height: 200px; transition: all 0.3s ease;">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <span class="badge bg-warning text-dark fw-bold px-3 py-1 mb-2 rounded-pill shadow-sm small" id="simSlideBadge">বইমেলা বিশেষ ছাড়</span>
                                        <h4 class="fw-bold mb-2 text-white" id="simSlideTitle">জ্ঞানের আলোয় উদ্ভাসিত হোক প্রতিটি মন</h4>
                                        <p class="small opacity-90 mb-3" id="simSlideSubtitle" style="max-width: 480px;">আইডিয়া প্রকাশনীর সকল নতুন ও জনপ্রিয় বইয়ে পাচ্ছেন আকর্ষণীয় মূল্যছাড়।</p>
                                        <button type="button" class="btn btn-light btn-sm fw-bold rounded-pill px-3 py-1.5 text-primary shadow-sm d-inline-flex align-items-center gap-1.5" id="simSlideBtn">
                                            <i class="fa-solid fa-cart-shopping" id="simSlideBtnIcon"></i>
                                            <span id="simSlideBtnText">বই কিনুন</span>
                                        </button>
                                    </div>
                                    <div class="col-md-4 text-center d-none d-md-flex align-items-center justify-content-center">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center shadow-lg position-relative" 
                                             style="width: 120px; height: 120px; background: rgba(255, 255, 255, 0.15); border: 2px solid rgba(255, 255, 255, 0.4); backdrop-filter: blur(8px);">
                                            <i class="fa-solid fa-book-open-reader text-white" id="simSlideIcon" style="font-size: 3.8rem; filter: drop-shadow(0 4px 12px rgba(0,0,0,0.3));"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 1-Click Quick Preset Insert Bar for all 6 core banners -->
                        <div class="card p-3 bg-white rounded-4 border shadow-2xs mb-4">
                            <label class="form-label small fw-bold text-dark mb-2 d-flex align-items-center gap-1.5">
                                <i class="fa-solid fa-bolt text-warning"></i>
                                <span>১-ক্লিকে নতুন নির্ধারিত ব্যানার যুক্ত করুন (Quick Banner Presets):</span>
                            </label>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-3 py-1.5 fw-semibold" onclick="addHeroSlidePreset('bookshop')">
                                    <i class="fa-solid fa-book-open-reader me-1"></i>+ বুকশপ / বইমেলা ব্যানার
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-dark rounded-pill px-3 py-1.5 fw-semibold" onclick="addHeroSlidePreset('ideapatra')">
                                    <i class="fa-solid fa-pen-nib text-danger me-1"></i>+ আইডিয়াপত্র / ব্লগ ব্যানার
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-success rounded-pill px-3 py-1.5 fw-semibold" onclick="addHeroSlidePreset('ebook')">
                                    <i class="fa-solid fa-mobile-screen-button me-1"></i>+ ই-বুক শপ ব্যানার
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-info rounded-pill px-3 py-1.5 fw-semibold text-dark" onclick="addHeroSlidePreset('webzine')">
                                    <i class="fa-solid fa-newspaper text-info me-1"></i>+ ম্যাগাজিন / সাময়িকী ব্যানার
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold" onclick="addHeroSlidePreset('authors')">
                                    <i class="fa-solid fa-user-pen text-primary me-1"></i>+ লেখক ডিরেক্টরি ব্যানার
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-danger rounded-pill px-3 py-1.5 fw-semibold" onclick="addHeroSlidePreset('publishers')">
                                    <i class="fa-solid fa-building-columns me-1"></i>+ প্রকাশক ডিরেক্টরি ব্যানার
                                </button>
                            </div>
                        </div>

                        <!-- Dynamic Slide Cards Container -->
                        <div id="heroSlidesContainer" class="d-flex flex-column gap-3 mb-4">
                            <!-- Injected via JavaScript -->
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <button type="button" class="btn btn-outline-warning text-dark btn-sm rounded-pill px-3 fw-bold shadow-2xs" onclick="addNewHeroSlide()">
                                <i class="fa-solid fa-plus me-1"></i> আরও একটি ব্যানার স্লাইড যোগ করুন
                            </button>
                            <span class="small text-muted">
                                <i class="fa-solid fa-circle-info text-primary me-1"></i> পরিবর্তন সংরক্ষণ করতে উপরের <strong>Save Changes</strong> বাটনে ক্লিক করুন।
                            </span>
                        </div>
                    </div>

                    <!-- Tab: Dynamic Header & Navigation Menu Builder -->
                    <div class="tab-pane fade" id="tab-menu" role="tabpanel">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 pb-3 border-bottom">
                            <div>
                                <h6 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-compass text-success"></i>
                                    <span>হেডার মেনু ও নেভিগেশন নিয়ন্ত্রণ (Header Navigation Menu Builder)</span>
                                </h6>
                                <p class="text-muted small mb-0">ওয়েবসাইটের প্রধান হেডার মেনু আইটেমসমূহ সাজান, নতুন লিঙ্ক বা রুট যোগ করুন, ক্রম পরিবর্তন করুন এবং অন/অফ করুন।</p>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1.5 fw-semibold shadow-2xs" onclick="resetMenuToDefaults()">
                                    <i class="fa-solid fa-rotate-left me-1"></i> ডিফল্ট মেনু রিস্টোর
                                </button>
                                <button type="button" class="btn btn-success btn-sm rounded-pill px-3.5 py-1.5 fw-bold shadow-xs d-inline-flex align-items-center gap-1.5" onclick="addCustomMenuItem()">
                                    <i class="fa-solid fa-plus"></i> নতুন আইটেম
                                </button>
                            </div>
                        </div>

                        <!-- Live Navbar Simulator Preview -->
                        <div class="card p-3.5 bg-light rounded-4 border shadow-2xs mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-white text-dark border px-2.5 py-1 small fw-bold d-inline-flex align-items-center gap-1">
                                    <i class="fa-solid fa-eye text-primary"></i> লাইভ হেডার প্রিভিউ (Live Header Preview)
                                </span>
                                <span class="text-muted small" id="liveMenuCountBadge">১০টি সক্রিয় মেনু আইটেম</span>
                            </div>
                            <div class="bg-white p-2.5 rounded-3 border d-flex align-items-center justify-content-center overflow-x-auto scrollbar-none shadow-2xs" style="min-height: 48px;">
                                <ul class="nav align-items-center justify-content-center gap-1 my-0 py-0 flex-nowrap" id="liveHeaderNavPreview">
                                    <!-- Live items injected via JS -->
                                </ul>
                            </div>
                        </div>

                        <!-- Quick Preset Insert Bar -->
                        <div class="card p-3 bg-white rounded-4 border shadow-2xs mb-4">
                            <label class="form-label small fw-bold text-dark mb-2 d-flex align-items-center gap-1.5">
                                <i class="fa-solid fa-bolt text-warning"></i>
                                <span>১-ক্লিকে দ্রুত মেনু আইটেম যুক্ত করুন (Quick Presets):</span>
                            </label>
                            <div class="d-flex flex-wrap gap-1.5">
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 fw-semibold" onclick="addMenuItemPreset('হোম', 'home', '/', 'house', '')">+ হোম</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 fw-semibold" onclick="addMenuItemPreset('বইসমূহ', 'book.index', '/books', 'book', '')">+ বইসমূহ</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 fw-semibold" onclick="addMenuItemPreset('ই-বুক', 'ebook.index', '/ebooks', 'tablet-screen-button', 'নতুন')">+ ই-বুক</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 fw-semibold" onclick="addMenuItemPreset('লেখক', 'authors.index', '/authors', 'pen-fancy', '')">+ লেখক</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 fw-semibold" onclick="addMenuItemPreset('প্রকাশক', 'publishers.index', '/publishers', 'building', '')">+ প্রকাশক</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 fw-semibold" onclick="addMenuItemPreset('আইডিয়াপত্র', 'blog.index', '/blog', 'newspaper', '')">+ আইডিয়াপত্র</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 fw-semibold" onclick="addMenuItemPreset('ওয়েবজিন', 'webzine.index', '/webzines', 'book-open', '')">+ ওয়েবজিন</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 fw-semibold" onclick="addMenuItemPreset('গবেষণা', 'research.index', '/research', 'flask', '')">+ গবেষণা</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 fw-semibold" onclick="addMenuItemPreset('আইডিয়া হাব', 'hub', '/hub', 'compass', '')">+ আইডিয়া হাব</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 fw-semibold" onclick="addMenuItemPreset('আমাদের সম্পর্কে', 'about', '/about', 'circle-info', '')">+ আমাদের সম্পর্কে</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 fw-semibold" onclick="addMenuItemPreset('যোগাযোগ', 'contact', '/contact', 'envelope', '')">+ যোগাযোগ</button>
                            </div>
                        </div>

                        <!-- Interactive Menu Items Table -->
                        <div class="table-responsive rounded-4 border bg-white shadow-2xs mb-4">
                            <table class="table table-hover align-middle mb-0" id="menuItemsTable">
                                <thead class="table-light">
                                    <tr class="text-uppercase text-muted" style="font-size: 11px; letter-spacing: 0.5px;">
                                        <th class="ps-3" style="width: 50px;">ক্রম</th>
                                        <th style="min-width: 150px;">লেবেল / নাম (Label)</th>
                                        <th style="min-width: 160px;">রুট / URL (Route/URL)</th>
                                        <th style="min-width: 110px;">আইকন (Icon)</th>
                                        <th style="min-width: 90px;">ব্যাজ (Badge)</th>
                                        <th style="width: 110px;">টার্গেট</th>
                                        <th class="text-center" style="width: 80px;">স্ট্যাটাস</th>
                                        <th class="text-end pe-3" style="width: 140px;">একশন</th>
                                    </tr>
                                </thead>
                                <tbody id="menuItemsTableBody">
                                    <!-- Dynamic Rows rendered via JS -->
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold" onclick="addCustomMenuItem()">
                                <i class="fa-solid fa-plus me-1"></i> আরও একটি মেনু আইটেম যোগ করুন
                            </button>
                            <span class="small text-muted">
                                <i class="fa-solid fa-circle-info text-primary me-1"></i> পরিবর্তন কার্যকর করতে উপরের <strong>Save Changes</strong> বাটনে ক্লিক করুন।
                            </span>
                        </div>
                    </div>

                    <!-- Tab 2: Theme & Colors Customizer -->
                    <div class="tab-pane fade" id="tab-theme" role="tabpanel">
                        <div class="row g-4">
                            
                            <div class="col-lg-6">
                                <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-swatchbook text-primary me-2"></i>Brand Color Palettes</h6>
                                <p class="small text-muted mb-3">Select your website's primary brand theme with a single click:</p>

                                <div class="d-flex flex-column gap-2 mb-4">
                                    <button type="button" class="btn btn-outline-light text-dark p-3 rounded-4 border d-flex align-items-center justify-content-between hover-lift transition-all" 
                                            onclick="applyPresetTheme('#0066cc', '#0099ff')">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="rounded-circle shadow-xs" style="width: 28px; height: 28px; background: #0066cc;"></div>
                                            <div class="text-start">
                                                <div class="fw-bold fs-6">Classic Indigo (Royal Blue)</div>
                                                <div class="small text-muted">Standard digital publishing theme</div>
                                            </div>
                                        </div>
                                        <span class="badge bg-primary rounded-pill px-3 py-1.5">Default</span>
                                    </button>

                                    <button type="button" class="btn btn-outline-light text-dark p-3 rounded-4 border d-flex align-items-center justify-content-between hover-lift transition-all" 
                                            onclick="applyPresetTheme('#059669', '#10b981')">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="rounded-circle shadow-xs" style="width: 28px; height: 28px; background: #059669;"></div>
                                            <div class="text-start">
                                                <div class="fw-bold fs-6">Emerald Green</div>
                                                <div class="small text-muted">Clean & natural aesthetic</div>
                                            </div>
                                        </div>
                                    </button>

                                    <button type="button" class="btn btn-outline-light text-dark p-3 rounded-4 border d-flex align-items-center justify-content-between hover-lift transition-all" 
                                            onclick="applyPresetTheme('#4f46e5', '#818cf8')">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="rounded-circle shadow-xs" style="width: 28px; height: 28px; background: #4f46e5;"></div>
                                            <div class="text-start">
                                                <div class="fw-bold fs-6">Deep Violet</div>
                                                <div class="small text-muted">Modern & creative feel</div>
                                            </div>
                                        </div>
                                    </button>

                                    <button type="button" class="btn btn-outline-light text-dark p-3 rounded-4 border d-flex align-items-center justify-content-between hover-lift transition-all" 
                                            onclick="applyPresetTheme('#be123c', '#f43f5e')">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="rounded-circle shadow-xs" style="width: 28px; height: 28px; background: #be123c;"></div>
                                            <div class="text-start">
                                                <div class="fw-bold fs-6">Crimson Rose</div>
                                                <div class="small text-muted">Vibrant & energetic look</div>
                                            </div>
                                        </div>
                                    </button>
                                </div>

                                <!-- Custom Color Hex Inputs -->
                                <div class="p-3 bg-light rounded-4 border">
                                    <label class="form-label small fw-bold text-dark mb-2">Custom Color Codes (HEX)</label>
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <label class="small text-muted">Primary Color</label>
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="color" id="primaryColorPicker" value="{{ $themeSetting['primary_color'] ?? '#0066cc' }}" class="form-control form-control-color border-0 p-0 rounded-circle" style="width: 36px; height: 36px;" onchange="document.getElementById('primaryColorInput').value = this.value">
                                                <input type="text" name="primary_color" id="primaryColorInput" value="{{ $themeSetting['primary_color'] ?? '#0066cc' }}" class="form-control form-control-sm rounded-3 font-monospace">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <label class="small text-muted">Secondary Color</label>
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="color" id="secondaryColorPicker" value="{{ $themeSetting['secondary_color'] ?? '#0099ff' }}" class="form-control form-control-color border-0 p-0 rounded-circle" style="width: 36px; height: 36px;" onchange="document.getElementById('secondaryColorInput').value = this.value">
                                                <input type="text" name="secondary_color" id="secondaryColorInput" value="{{ $themeSetting['secondary_color'] ?? '#0099ff' }}" class="form-control form-control-sm rounded-3 font-monospace">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Live Theme Mode Switch -->
                            <div class="col-lg-6">
                                <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-moon text-primary me-2"></i>Default Display Mode</h6>
                                
                                <div class="p-4 bg-light rounded-4 border mb-4">
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <label class="card p-3 border-2 rounded-4 text-center cursor-pointer hover-lift transition-all {{ ($themeSetting['default_mode'] ?? 'light') === 'light' ? 'border-primary bg-white shadow-xs' : 'border-transparent bg-white' }}">
                                                <input type="radio" name="default_mode" value="light" class="d-none" {{ ($themeSetting['default_mode'] ?? 'light') === 'light' ? 'checked' : '' }}>
                                                <i class="fa-solid fa-sun fs-2 text-warning mb-2"></i>
                                                <div class="fw-bold fs-6">Light Mode</div>
                                                <span class="small text-muted">Clean & bright reading</span>
                                            </label>
                                        </div>
                                        <div class="col-6">
                                            <label class="card p-3 border-2 rounded-4 text-center cursor-pointer hover-lift transition-all {{ ($themeSetting['default_mode'] ?? '') === 'dark' ? 'border-primary bg-white shadow-xs' : 'border-transparent bg-white' }}">
                                                <input type="radio" name="default_mode" value="dark" class="d-none" {{ ($themeSetting['default_mode'] ?? '') === 'dark' ? 'checked' : '' }}>
                                                <i class="fa-solid fa-moon fs-2 text-indigo-500 mb-2"></i>
                                                <div class="fw-bold fs-6">Dark Mode</div>
                                                <span class="small text-muted">Easy on the eyes</span>
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
                                <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-bullhorn text-warning me-2"></i>Notice Message & Configuration</h6>
                                
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">Notice Text</label>
                                    <textarea name="notice_text" id="noticeTextInput" class="form-control rounded-3" rows="4" placeholder="e.g. 25% Special Discount on all books! Cash on Delivery nationwide." oninput="updateLiveNoticePreview()">{{ $noticeSetting['text'] ?? '' }}</textarea>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted">Notice Style / Type</label>
                                        <select name="notice_type" id="noticeTypeSelect" class="form-select rounded-3" onchange="updateLiveNoticePreview()">
                                            <option value="info" {{ ($noticeSetting['type'] ?? '') === 'info' ? 'selected' : '' }}>Informational (Blue)</option>
                                            <option value="warning" {{ ($noticeSetting['type'] ?? '') === 'warning' ? 'selected' : '' }}>Announcement (Yellow)</option>
                                            <option value="success" {{ ($noticeSetting['type'] ?? '') === 'success' ? 'selected' : '' }}>Promotion / Offer (Green)</option>
                                            <option value="danger" {{ ($noticeSetting['type'] ?? '') === 'danger' ? 'selected' : '' }}>Urgent Alert (Red)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted">Status</label>
                                        <div class="form-check form-switch p-2 bg-light rounded-3 px-3 d-flex align-items-center justify-content-between border">
                                            <label class="form-check-label small fw-bold text-dark cursor-pointer ms-0 me-2" for="noticeActiveSwitch">
                                                Display Notice
                                            </label>
                                            <input class="form-check-input cursor-pointer" type="checkbox" role="switch" id="noticeActiveSwitch" name="notice_active" value="1" {{ !empty($noticeSetting['active']) ? 'checked' : '' }} onchange="updateLiveNoticePreview()">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Live Notice Preview -->
                            <div class="col-lg-5">
                                <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-eye text-primary me-2"></i>Live Preview</h6>
                                
                                <div class="p-4 bg-light rounded-4 border">
                                    <div id="noticePreviewBox" class="alert alert-{{ $noticeSetting['type'] ?? 'info' }} rounded-3 shadow-xs d-flex align-items-center gap-2 mb-0 {{ empty($noticeSetting['active']) ? 'opacity-50' : '' }}">
                                        <i class="fa-solid fa-circle-info fs-5" id="noticePreviewIcon"></i>
                                        <span id="noticePreviewText">{{ $noticeSetting['text'] ?? 'The announcement message will appear here in real-time.' }}</span>
                                    </div>
                                    <span class="small text-muted mt-2 d-block" id="noticeStatusText">
                                        {{ !empty($noticeSetting['active']) ? '🟢 Notice is currently active on live website' : '⚪ Notice is currently disabled' }}
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
                                <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-truck-ramp-box text-success me-2"></i>Delivery Fee Configuration</h6>
                                
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">Dhaka City Delivery Fee (৳)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light fw-bold">৳</span>
                                        <input type="number" name="delivery_dhaka" value="{{ $ecomSetting['delivery_dhaka'] ?? 50 }}" class="form-control rounded-end-3">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">Dhaka Suburbs / Savar / Gazipur (৳)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light fw-bold">৳</span>
                                        <input type="number" name="delivery_sub" value="{{ $ecomSetting['delivery_sub'] ?? 100 }}" class="form-control rounded-end-3">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">Outside Dhaka / Nationwide (৳)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light fw-bold">৳</span>
                                        <input type="number" name="delivery_outside" value="{{ $ecomSetting['delivery_outside'] ?? 120 }}" class="form-control rounded-end-3">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">Gift Wrapping Fee (৳)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light fw-bold">৳</span>
                                        <input type="number" name="gift_wrap_fee" value="{{ $ecomSetting['gift_wrap_fee'] ?? 20 }}" class="form-control rounded-end-3">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">Free Delivery Minimum Order (৳)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light fw-bold">৳</span>
                                        <input type="number" name="free_delivery_threshold" value="{{ $ecomSetting['free_delivery_threshold'] ?? 1500 }}" class="form-control rounded-end-3">
                                    </div>
                                    <div class="form-text small">Orders exceeding this amount receive free delivery automatically.</div>
                                </div>
                            </div>

                            <!-- Customer Support Contacts -->
                            <div class="col-lg-6">
                                <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-headset text-primary me-2"></i>Helpline & Support Contacts</h6>
                                
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">Official Helpline Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fa-solid fa-phone text-success"></i></span>
                                        <input type="text" name="helpline_phone" value="{{ $ecomSetting['helpline_phone'] ?? '01726976982' }}" class="form-control rounded-end-3">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">WhatsApp Order Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fa-brands fa-whatsapp text-success"></i></span>
                                        <input type="text" name="whatsapp_number" value="{{ $ecomSetting['whatsapp_number'] ?? '01726976982' }}" class="form-control rounded-end-3">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">Official Support Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fa-solid fa-envelope text-primary"></i></span>
                                        <input type="email" name="helpline_email" value="{{ $ecomSetting['helpline_email'] ?? 'ideapbd@gmail.com' }}" class="form-control rounded-end-3">
                                    </div>
                                </div>

                                <div class="p-3 bg-white rounded-3 border border-2 border-danger-subtle mt-3">
                                    <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-mobile-screen-button text-danger me-1"></i> Online Mobile Banking Accounts</h6>
                                    
                                    <div class="row g-2 mb-2">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold text-muted mb-1">bKash Number</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text text-white fw-bold" style="background:#d82a6f;">bKash</span>
                                                <input type="text" name="bkash_number" value="{{ $ecomSetting['bkash_number'] ?? '01558712810' }}" class="form-control rounded-end-3 font-monospace">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold text-muted mb-1">Nagad Number</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text text-white fw-bold" style="background:#e8590c;">Nagad</span>
                                                <input type="text" name="nagad_number" value="{{ $ecomSetting['nagad_number'] ?? '01558712810' }}" class="form-control rounded-end-3 font-monospace">
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="form-label small fw-semibold text-muted mb-1">Payment Instructions (Shown to customers)</label>
                                        <input type="text" name="payment_instruction" value="{{ $ecomSetting['payment_instruction'] ?? 'Send money to the provided number and enter TrxID and sending number.' }}" class="form-control form-control-sm rounded-3">
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Coupon & Special Threshold Offers Row -->
                        <div class="row g-4 mt-2 pt-3 border-top">
                            <!-- Coupon Code Settings -->
                            <div class="col-lg-6">
                                <div class="p-3.5 bg-light rounded-4 border">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                            <i class="fa-solid fa-ticket text-warning"></i> Coupon Code & Promotions
                                        </h6>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input cursor-pointer" type="checkbox" name="coupon_enabled" value="1" id="couponEnabledSwitch" {{ !empty($ecomSetting['coupon_enabled']) ? 'checked' : '' }}>
                                            <label class="form-check-label small fw-bold text-dark cursor-pointer ms-1" for="couponEnabledSwitch">Enable</label>
                                        </div>
                                    </div>
                                    <p class="small text-muted mb-3">Allow customers to apply promo codes at checkout for discounts.</p>

                                    <div class="row g-2 mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold text-muted mb-1">Coupon Code (Promo)</label>
                                            <input type="text" name="coupon_code" value="{{ $ecomSetting['coupon_code'] ?? 'IDEA2026' }}" class="form-control rounded-3 font-monospace text-uppercase fw-bold" placeholder="e.g. IDEA2026">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold text-muted mb-1">Discount Type</label>
                                            <select name="coupon_type" class="form-select rounded-3">
                                                <option value="percent" {{ ($ecomSetting['coupon_type'] ?? 'percent') === 'percent' ? 'selected' : '' }}>Percentage (%)</option>
                                                <option value="fixed" {{ ($ecomSetting['coupon_type'] ?? '') === 'fixed' ? 'selected' : '' }}>Fixed Amount (৳)</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row g-2 mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold text-muted mb-1">Discount Value</label>
                                            <input type="number" step="any" name="coupon_discount" value="{{ $ecomSetting['coupon_discount'] ?? 10 }}" class="form-control rounded-3" placeholder="e.g. 10 or 100">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold text-muted mb-1">Minimum Order Amount (৳)</label>
                                            <input type="number" step="any" name="coupon_min_order" value="{{ $ecomSetting['coupon_min_order'] ?? 500 }}" class="form-control rounded-3" placeholder="e.g. 500">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="form-label small fw-semibold text-muted mb-1">Coupon Description</label>
                                        <input type="text" name="coupon_description" value="{{ $ecomSetting['coupon_description'] ?? 'Special Promo Discount' }}" class="form-control form-control-sm rounded-3" placeholder="e.g. 10% Book Fair Special Discount!">
                                    </div>
                                </div>
                            </div>

                            <!-- Special Threshold-based Offer Settings -->
                            <div class="col-lg-6">
                                <div class="p-3.5 bg-light rounded-4 border">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                            <i class="fa-solid fa-gift text-primary"></i> Tiered Order Special Offers
                                        </h6>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input cursor-pointer" type="checkbox" name="threshold_offer_enabled" value="1" id="thresholdOfferSwitch" {{ !empty($ecomSetting['threshold_offer_enabled']) ? 'checked' : '' }}>
                                            <label class="form-check-label small fw-bold text-dark cursor-pointer ms-1" for="thresholdOfferSwitch">Enable</label>
                                        </div>
                                    </div>
                                    <p class="small text-muted mb-3">Automatically reward customers when cart subtotal reaches specific amounts.</p>

                                    <div class="row g-2 mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold text-muted mb-1">Minimum Order Threshold (৳)</label>
                                            <input type="number" step="any" name="threshold_offer_amount" value="{{ $ecomSetting['threshold_offer_amount'] ?? 1000 }}" class="form-control rounded-3" placeholder="e.g. 1000">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold text-muted mb-1">Reward Type</label>
                                            <select name="threshold_offer_type" class="form-select rounded-3">
                                                <option value="free_delivery" {{ ($ecomSetting['threshold_offer_type'] ?? 'free_delivery') === 'free_delivery' ? 'selected' : '' }}>Free Delivery</option>
                                                <option value="flat_discount" {{ ($ecomSetting['threshold_offer_type'] ?? '') === 'flat_discount' ? 'selected' : '' }}>Flat Discount (৳)</option>
                                                <option value="percent_discount" {{ ($ecomSetting['threshold_offer_type'] ?? '') === 'percent_discount' ? 'selected' : '' }}>Percentage Discount (%)</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold text-muted mb-1">Reward Value (if discount applied)</label>
                                        <input type="number" step="any" name="threshold_offer_discount" value="{{ $ecomSetting['threshold_offer_discount'] ?? 100 }}" class="form-control rounded-3" placeholder="e.g. 100 or 10">
                                    </div>

                                    <div>
                                        <label class="form-label small fw-semibold text-muted mb-1">Offer Banner Title</label>
                                        <input type="text" name="threshold_offer_title" value="{{ $ecomSetting['threshold_offer_title'] ?? 'Free Delivery on Orders Over ৳1000!' }}" class="form-control form-control-sm rounded-3" placeholder="e.g. Free Delivery on Orders Over ৳1000!">
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
                                <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-building-user text-primary me-2"></i>Invoice Sender & Memo Configuration</h6>
                                <p class="small text-muted mb-3">Sender name, address, and contact details displayed on all printed invoices and delivery challans.</p>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">Sender / Company Name <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fa-solid fa-book-open text-primary"></i></span>
                                        <input type="text" name="invoice_sender_name" id="invSenderName" value="{{ $invoiceSetting['sender_name'] ?? 'Idea Prakashan' }}" class="form-control rounded-end-3" required oninput="updateInvoicePreview()">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">Full Address <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fa-solid fa-location-dot text-danger"></i></span>
                                        <input type="text" name="invoice_sender_address" id="invSenderAddress" value="{{ $invoiceSetting['sender_address'] ?? 'Central Road, Rangpur 5400, Bangladesh' }}" class="form-control rounded-end-3" required oninput="updateInvoicePreview()">
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted">Phone Number <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="fa-solid fa-phone text-success"></i></span>
                                            <input type="text" name="invoice_sender_phone" id="invSenderPhone" value="{{ $invoiceSetting['sender_phone'] ?? '01558712870' }}" class="form-control rounded-end-3" required oninput="updateInvoicePreview()">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted">Official Email</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="fa-solid fa-envelope text-primary"></i></span>
                                            <input type="email" name="invoice_sender_email" id="invSenderEmail" value="{{ $invoiceSetting['sender_email'] ?? 'ideapbd@gmail.com' }}" class="form-control rounded-end-3" oninput="updateInvoicePreview()">
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted">Website Address</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="fa-solid fa-globe text-info"></i></span>
                                            <input type="text" name="invoice_sender_website" id="invSenderWebsite" value="{{ $invoiceSetting['sender_website'] ?? 'www.ideaabd.com' }}" class="form-control rounded-end-3" oninput="updateInvoicePreview()">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted">Invoice Title</label>
                                        <input type="text" name="invoice_title" id="invTitle" value="{{ $invoiceSetting['invoice_title'] ?? 'Cash Memo / Invoice' }}" class="form-control rounded-3" oninput="updateInvoicePreview()">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">Terms & Return Policy</label>
                                    <textarea name="invoice_terms" id="invTerms" rows="2" class="form-control rounded-3" oninput="updateInvoicePreview()">{{ $invoiceSetting['invoice_terms'] ?? 'Please inspect books upon delivery. For queries, contact helpline immediately.' }}</textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">Footer Greeting / Remarks</label>
                                    <input type="text" name="invoice_footer" id="invFooter" value="{{ $invoiceSetting['invoice_footer'] ?? 'Thank you for choosing ideaabd!' }}" class="form-control rounded-3" oninput="updateInvoicePreview()">
                                </div>
                            </div>

                            <!-- Live Invoice Sender Preview -->
                            <div class="col-lg-5">
                                <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-file-invoice text-primary me-2"></i>Invoice Header Preview</h6>
                                <div class="p-4 bg-light rounded-4 border">
                                    <div class="card border border-2 border-primary-subtle rounded-3 shadow-xs overflow-hidden">
                                        <div class="card-header bg-primary text-white py-2 px-3 d-flex justify-content-between align-items-center">
                                            <span class="small fw-bold" id="prevInvTitle">{{ $invoiceSetting['invoice_title'] ?? 'Cash Memo / Invoice' }}</span>
                                            <span class="badge bg-white text-primary font-monospace small">#IDP-2026-1001</span>
                                        </div>
                                        <div class="card-body p-3 bg-white">
                                            <div class="d-flex align-items-center gap-2 mb-2 pb-2 border-bottom">
                                                <img src="{{ asset('images/logo.svg') }}" alt="Logo" style="height: 28px; width: auto;" onerror="this.style.display='none'">
                                                <div>
                                                    <div class="fw-bold text-dark fs-6 mb-0" id="prevSenderName">{{ $invoiceSetting['sender_name'] ?? 'Idea Prakashan' }}</div>
                                                    <div class="small text-muted" id="prevSenderWebsite">{{ $invoiceSetting['sender_website'] ?? 'www.ideaabd.com' }}</div>
                                                </div>
                                            </div>
                                            <div class="small text-secondary mb-1">
                                                <i class="fa-solid fa-location-dot text-danger me-1"></i>
                                                <span id="prevSenderAddress">{{ $invoiceSetting['sender_address'] ?? 'Central Road, Rangpur 5400, Bangladesh' }}</span>
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
                                                <span id="prevInvTerms">{{ $invoiceSetting['invoice_terms'] ?? 'Please inspect books upon delivery.' }}</span>
                                            </div>
                                        </div>
                                        <div class="card-footer bg-light py-1.5 px-3 text-center border-top">
                                            <span class="text-muted small" style="font-size: 0.72rem;" id="prevInvFooter">{{ $invoiceSetting['invoice_footer'] ?? 'Thank you for choosing ideaabd!' }}</span>
                                        </div>
                                    </div>
                                    <small class="text-muted d-block text-center mt-2">These details are rendered automatically on invoices and challans.</small>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Tab 5: Customizable Payment Gateways -->
                    <div class="tab-pane fade" id="tab-payment" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="fw-bold text-dark mb-1"><i class="fa-solid fa-credit-card text-danger me-2"></i>Payment Gateways & Methods</h6>
                                <p class="small text-muted mb-0">Configure bKash, Nagad, Rocket, Bank Transfer, and Cash on Delivery settings.</p>
                            </div>
                        </div>

                        <div class="row g-4">
                            <!-- bKash Box -->
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-4 border h-100 border-2" style="border-color: #fce7f3 !important;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge text-white px-2.5 py-1 fw-bold" style="background:#d82a6f;">bKash</span>
                                            <span class="fw-bold text-dark small">bKash Payment</span>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" name="gateways[bkash][enabled]" value="1" {{ !empty($paymentGateways['bkash']['enabled']) ? 'checked' : '' }}>
                                        </div>
                                    </div>

                                    <div class="row g-2 mb-2">
                                        <div class="col-7">
                                            <label class="form-label small fw-semibold text-muted mb-1">bKash Number</label>
                                            <input type="text" name="gateways[bkash][number]" value="{{ $paymentGateways['bkash']['number'] ?? '01558712810' }}" class="form-control form-control-sm font-monospace rounded-3">
                                        </div>
                                        <div class="col-5">
                                            <label class="form-label small fw-semibold text-muted mb-1">Account Type</label>
                                            <select name="gateways[bkash][type]" class="form-select form-select-sm rounded-3">
                                                <option value="personal" {{ ($paymentGateways['bkash']['type'] ?? '') === 'personal' ? 'selected' : '' }}>Personal (Send Money)</option>
                                                <option value="merchant" {{ ($paymentGateways['bkash']['type'] ?? '') === 'merchant' ? 'selected' : '' }}>Merchant (Payment)</option>
                                                <option value="agent" {{ ($paymentGateways['bkash']['type'] ?? '') === 'agent' ? 'selected' : '' }}>Agent (Cash Out)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="form-label small fw-semibold text-muted mb-1">Payment Instructions</label>
                                        <input type="text" name="gateways[bkash][instructions]" value="{{ $paymentGateways['bkash']['instructions'] ?? 'Use Send Money in bKash app to transfer bill total to the provided number.' }}" class="form-control form-control-sm rounded-3">
                                    </div>
                                </div>
                            </div>

                            <!-- Nagad Box -->
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-4 border h-100 border-2" style="border-color: #ffedd5 !important;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge text-white px-2.5 py-1 fw-bold" style="background:#e8590c;">Nagad</span>
                                            <span class="fw-bold text-dark small">Nagad Payment</span>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" name="gateways[nagad][enabled]" value="1" {{ !empty($paymentGateways['nagad']['enabled']) ? 'checked' : '' }}>
                                        </div>
                                    </div>

                                    <div class="row g-2 mb-2">
                                        <div class="col-7">
                                            <label class="form-label small fw-semibold text-muted mb-1">Nagad Number</label>
                                            <input type="text" name="gateways[nagad][number]" value="{{ $paymentGateways['nagad']['number'] ?? '01558712810' }}" class="form-control form-control-sm font-monospace rounded-3">
                                        </div>
                                        <div class="col-5">
                                            <label class="form-label small fw-semibold text-muted mb-1">Account Type</label>
                                            <select name="gateways[nagad][type]" class="form-select form-select-sm rounded-3">
                                                <option value="personal" {{ ($paymentGateways['nagad']['type'] ?? '') === 'personal' ? 'selected' : '' }}>Personal (Send Money)</option>
                                                <option value="merchant" {{ ($paymentGateways['nagad']['type'] ?? '') === 'merchant' ? 'selected' : '' }}>Merchant (Payment)</option>
                                                <option value="agent" {{ ($paymentGateways['nagad']['type'] ?? '') === 'agent' ? 'selected' : '' }}>Agent</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="form-label small fw-semibold text-muted mb-1">Payment Instructions</label>
                                        <input type="text" name="gateways[nagad][instructions]" value="{{ $paymentGateways['nagad']['instructions'] ?? 'Use Send Money in Nagad app to transfer bill total to the provided number.' }}" class="form-control form-control-sm rounded-3">
                                    </div>
                                </div>
                            </div>

                            <!-- Rocket Box -->
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-4 border h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge text-white px-2.5 py-1 fw-bold" style="background:#8b5cf6;">Rocket</span>
                                            <span class="fw-bold text-dark small">Rocket Payment</span>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" name="gateways[rocket][enabled]" value="1" {{ !empty($paymentGateways['rocket']['enabled']) ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                    <div class="row g-2 mb-2">
                                        <div class="col-7">
                                            <label class="form-label small fw-semibold text-muted mb-1">Rocket Number (12 Digits)</label>
                                            <input type="text" name="gateways[rocket][number]" value="{{ $paymentGateways['rocket']['number'] ?? '01558712810' }}" class="form-control form-control-sm font-monospace rounded-3">
                                        </div>
                                        <div class="col-5">
                                            <label class="form-label small fw-semibold text-muted mb-1">Account Type</label>
                                            <select name="gateways[rocket][type]" class="form-select form-select-sm rounded-3">
                                                <option value="personal">Personal</option>
                                                <option value="merchant">Merchant</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="form-label small fw-semibold text-muted mb-1">Payment Instructions</label>
                                        <input type="text" name="gateways[rocket][instructions]" value="{{ $paymentGateways['rocket']['instructions'] ?? 'Transfer bill amount via Rocket Send Money.' }}" class="form-control form-control-sm rounded-3">
                                    </div>
                                </div>
                            </div>

                            <!-- Cash on Delivery (COD) Box -->
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-4 border h-100 border-2" style="border-color: #dcfce7 !important;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-success text-white px-2.5 py-1 fw-bold">COD Cash on Delivery</span>
                                            <span class="fw-bold text-dark small">Pay on Receipt</span>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" name="gateways[cod][enabled]" value="1" {{ !empty($paymentGateways['cod']['enabled']) ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small fw-semibold text-muted mb-1">Method Display Name</label>
                                        <input type="text" name="gateways[cod][name]" value="{{ $paymentGateways['cod']['name'] ?? 'Cash on Delivery (COD)' }}" class="form-control form-control-sm rounded-3">
                                    </div>
                                    <div>
                                        <label class="form-label small fw-semibold text-muted mb-1">Instructions</label>
                                        <input type="text" name="gateways[cod][instructions]" value="{{ $paymentGateways['cod']['instructions'] ?? 'Pay cash directly upon book delivery.' }}" class="form-control form-control-sm rounded-3">
                                    </div>
                                </div>
                            </div>

                            <!-- Bank Transfer Box -->
                            <div class="col-12">
                                <div class="p-3 bg-light rounded-4 border">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-secondary text-white px-2.5 py-1 fw-bold"><i class="fa-solid fa-building-columns me-1"></i> Bank Transfer</span>
                                            <span class="fw-bold text-dark small">Direct Bank Deposit</span>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" name="gateways[bank][enabled]" value="1" {{ !empty($paymentGateways['bank']['enabled']) ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                    <div class="row g-2 mb-2">
                                        <div class="col-md-3">
                                            <label class="form-label small fw-semibold text-muted mb-1">Bank Name</label>
                                            <input type="text" name="gateways[bank][bank_name]" value="{{ $paymentGateways['bank']['bank_name'] ?? 'Islami Bank Bangladesh Ltd' }}" class="form-control form-control-sm rounded-3">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-semibold text-muted mb-1">Account Name</label>
                                            <input type="text" name="gateways[bank][account_name]" value="{{ $paymentGateways['bank']['account_name'] ?? 'Idea Prakashan' }}" class="form-control form-control-sm rounded-3">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-semibold text-muted mb-1">Account Number</label>
                                            <input type="text" name="gateways[bank][account_no]" value="{{ $paymentGateways['bank']['account_no'] ?? '2050XXXXXXXXX' }}" class="form-control form-control-sm font-monospace rounded-3">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-semibold text-muted mb-1">Branch Name</label>
                                            <input type="text" name="gateways[bank][branch]" value="{{ $paymentGateways['bank']['branch'] ?? 'Rangpur Branch' }}" class="form-control form-control-sm rounded-3">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="form-label small fw-semibold text-muted mb-1">Bank Deposit Instructions</label>
                                        <input type="text" name="gateways[bank][instructions]" value="{{ $paymentGateways['bank']['instructions'] ?? 'Deposit amount in bank and provide receipt slip or reference number.' }}" class="form-control form-control-sm rounded-3">
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
                                <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-gauge-high text-secondary me-2"></i>Server & System Diagnostics</h6>
                                
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover align-middle mb-0">
                                        <tbody>
                                            <tr>
                                                <th class="text-muted fw-semibold">PHP Version</th>
                                                <td class="text-dark fw-bold font-monospace">{{ $diagnostics['php_version'] }}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted fw-semibold">Laravel Framework</th>
                                                <td class="text-dark fw-bold font-monospace">{{ $diagnostics['laravel_version'] }}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted fw-semibold">Database Driver</th>
                                                <td class="text-dark fw-bold text-uppercase">{{ $diagnostics['db_connection'] }}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted fw-semibold">Environment Mode</th>
                                                <td><span class="badge bg-info-subtle text-info border border-info-subtle">{{ strtoupper($diagnostics['app_env']) }}</span></td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted fw-semibold">Debug Mode</th>
                                                <td>{{ $diagnostics['app_debug'] }}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted fw-semibold">Public File Storage</th>
                                                <td><span class="badge bg-success-subtle text-success border border-success-subtle">{{ $diagnostics['storage_link'] }}</span></td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted fw-semibold">Operating System</th>
                                                <td class="text-dark">{{ $diagnostics['server_os'] }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Maintenance Mode Switch -->
                            <div class="col-lg-5">
                                <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-screwdriver-wrench text-danger me-2"></i>Maintenance Mode</h6>
                                
                                <div class="p-4 bg-light rounded-4 border">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input cursor-pointer" type="checkbox" name="maintenance_mode" value="1" id="maintSwitch" {{ !empty($maintSetting['enabled']) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-dark cursor-pointer" for="maintSwitch">
                                            Enable Maintenance Mode
                                        </label>
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label small text-muted">Maintenance Message (Seen by visitors)</label>
                                        <textarea name="maintenance_reason" class="form-control rounded-3" rows="3" placeholder="We are performing scheduled system maintenance. Please check back shortly.">{{ $maintSetting['reason'] ?? '' }}</textarea>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Tab: Editorial & Publishing Board (আইডিয়াপত্র ও সম্পাদনা পরিষদ) -->
                    <div class="tab-pane fade" id="tab-editorial" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                            <div>
                                <h5 class="fw-bold text-dark mb-1"><i class="fa-solid fa-feather-pointed text-primary me-2"></i>আইডিয়াপত্র ও সম্পাদকমণ্ডলী ব্যবস্থাপনা</h5>
                                <p class="small text-muted mb-0">প্রকাশক, সম্পাদক এবং সম্পাদকীয় পরিষদের অন্যান্য পদ ও দায়িত্বশীলদের তালিকা সাইটের ফুটার ও প্রকাশনায় ডাইনামিক প্রদর্শন করুন।</p>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-lg-6">
                                <div class="p-3.5 bg-light rounded-4 border mb-3">
                                    <label class="form-label fw-bold text-dark small mb-1">
                                        <i class="fa-solid fa-building text-primary me-1"></i> প্রকাশক (Publisher)
                                    </label>
                                    <input type="text" name="editorial_publisher" class="form-control rounded-3" 
                                           value="{{ $settings['editorial_publisher'] ?? 'আইডিয়া প্রকাশন' }}" placeholder="যেমন: আইডিয়া প্রকাশন">
                                </div>
                                
                                <div class="p-3.5 bg-light rounded-4 border">
                                    <label class="form-label fw-bold text-dark small mb-1">
                                        <i class="fa-solid fa-user-pen text-success me-1"></i> সম্পাদক (Editor)
                                    </label>
                                    <input type="text" name="editorial_editor" class="form-control rounded-3" 
                                           value="{{ $settings['editorial_editor'] ?? 'সাকিল মাসুদ' }}" placeholder="যেমন: সাকিল মাসুদ">
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="p-3.5 bg-white rounded-4 border shadow-2xs">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <label class="form-label fw-bold text-dark small mb-0">
                                            <i class="fa-solid fa-users text-info me-1"></i> সম্পাদকীয় পরিষদের অন্যান্য পদ ও সদস্যবৃন্দ
                                        </label>
                                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-2.5 py-1" onclick="addBoardMemberRow()">
                                            <i class="fa-solid fa-plus me-1"></i> নতুন পদ যোগ করুন
                                        </button>
                                    </div>
                                    
                                    <div id="boardMembersContainer" class="d-flex flex-column gap-2">
                                        @php
                                            $savedBoard = $settings['editorial_board'] ?? [];
                                        @endphp
                                        @if(is_array($savedBoard) && count($savedBoard) > 0)
                                            @foreach($savedBoard as $idx => $m)
                                                <div class="d-flex align-items-center gap-2 board-row">
                                                    <input type="text" name="board_role[]" class="form-control form-control-sm rounded-3" placeholder="পদ (যেমন: সহ-সম্পাদক)" value="{{ $m['role'] ?? '' }}" style="flex: 1;">
                                                    <input type="text" name="board_name[]" class="form-control form-control-sm rounded-3" placeholder="নাম (যেমন: নাসিম আহমেদ)" value="{{ $m['name'] ?? '' }}" style="flex: 1.2;">
                                                    <button type="button" class="btn btn-outline-danger btn-sm rounded-3 px-2" onclick="this.closest('.board-row').remove()"><i class="fa-solid fa-trash"></i></button>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="d-flex align-items-center gap-2 board-row">
                                                <input type="text" name="board_role[]" class="form-control form-control-sm rounded-3" placeholder="পদ (যেমন: নির্বাহী সম্পাদক)" style="flex: 1;">
                                                <input type="text" name="board_name[]" class="form-control form-control-sm rounded-3" placeholder="নাম..." style="flex: 1.2;">
                                                <button type="button" class="btn btn-outline-danger btn-sm rounded-3 px-2" onclick="this.closest('.board-row').remove()"><i class="fa-solid fa-trash"></i></button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ideapatra Homepage Section Customization -->
                        <div class="mt-4 pt-4 border-top">
                            <h6 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                                <i class="fa-solid fa-newspaper text-primary"></i> হোম পেজে "আইডিয়াপত্র" সেকশন হেডার ও বার্তা কাস্টমাইজেশন
                            </h6>
                            <p class="small text-muted mb-3">হোম পেজে আইডিয়াপত্র সাময়িকী ও ব্লগের টাইটেল, ট্যাগলাইন এবং বিবরণ এখান থেকে পরিবর্তন করুন।</p>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <label class="form-label fw-bold text-dark small mb-1">
                                            <i class="fa-solid fa-tag text-success me-1"></i> সেকশন ব্যাজ / ট্যাগলাইন
                                        </label>
                                        <input type="text" name="ideapatra_section_badge" class="form-control rounded-3" 
                                               value="{{ $settings['ideapatra_section_badge'] ?? 'আইডিয়াপত্র সাময়িকী ও ব্লগ' }}" 
                                               placeholder="যেমন: আইডিয়াপত্র সাময়িকী ও ব্লগ">
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <label class="form-label fw-bold text-dark small mb-1">
                                            <i class="fa-solid fa-heading text-primary me-1"></i> প্রধান শিরোনাম (Title)
                                        </label>
                                        <input type="text" name="ideapatra_section_title" class="form-control rounded-3" 
                                               value="{{ $settings['ideapatra_section_title'] ?? 'সমকালীন সাহিত্য, প্রবন্ধ ও মুক্তচিন্তার পোস্ট' }}" 
                                               placeholder="যেমন: সমকালীন সাহিত্য, প্রবন্ধ ও মুক্তচিন্তার পোস্ট">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <label class="form-label fw-bold text-dark small mb-1">
                                            <i class="fa-solid fa-align-left text-info me-1"></i> বিবরণ ও সাবটাইটেল (Subtitle / Description)
                                        </label>
                                        <textarea name="ideapatra_section_subtitle" class="form-control rounded-3" rows="2" 
                                                  placeholder="যেমন: আইডিয়া প্রকাশনের লেখক ও গবেষকদের সমকালীন সাহিত্যকর্ম ও পাঠপ্রতিক্রিয়া">{{ $settings['ideapatra_section_subtitle'] ?? 'আইডিয়া প্রকাশনের লেখক ও গবেষকদের সমকালীন সাহিত্যকর্ম ও পাঠপ্রতিক্রিয়া' }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Footer Save Button Bar -->
            <div class="card-footer bg-light border-top p-3.5 d-flex justify-content-between align-items-center">
                <span class="small text-muted d-none d-sm-inline">
                    <i class="fa-solid fa-shield-halved text-success me-1"></i> Changes take effect immediately upon saving.
                </span>
                <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold shadow-sm ms-auto">
                    <i class="fa-solid fa-floppy-disk me-1.5"></i> Save Settings
                </button>
            </div>

        </div>

        <!-- Sticky Floating Bottom Save Bar -->
        <div class="position-sticky bottom-0 bg-white border-top shadow-lg p-3 rounded-4 mt-3 z-3 d-flex flex-wrap align-items-center justify-content-between gap-3" 
             style="background: rgba(255, 255, 255, 0.96) !important; backdrop-filter: blur(10px); border: 1px solid #e2e8f0 !important;">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 small fw-semibold">
                    <i class="fa-solid fa-circle-check me-1"></i> Settings Ready
                </span>
                <span class="small text-muted d-none d-md-inline">Save modifications across all tabs with one click.</span>
            </div>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <button type="reset" class="btn btn-outline-secondary rounded-pill px-3.5 py-2 fw-semibold">
                    <i class="fa-solid fa-rotate-left me-1"></i> Reset
                </button>
                <button type="submit" class="btn btn-primary btn-lg rounded-pill px-4 py-2 fw-bold shadow-sm d-inline-flex align-items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Save Settings</span>
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
                    <i class="fa-solid fa-crop-simple text-primary"></i> Image Crop & Size Adjustment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 bg-dark">
                <!-- Aspect Ratio Presets Bar -->
                <div class="d-flex align-items-center justify-content-center gap-1.5 mb-2.5 flex-wrap">
                    <span class="text-white-50 small me-1">Aspect Ratio:</span>
                    <button type="button" class="btn btn-xs btn-outline-light rounded-pill px-2.5 py-1 crop-ratio-btn active" onclick="setCropRatio(2/1, this)">2:1 (Header)</button>
                    <button type="button" class="btn btn-xs btn-outline-light rounded-pill px-2.5 py-1 crop-ratio-btn" onclick="setCropRatio(NaN, this)">Free Crop</button>
                    <button type="button" class="btn btn-xs btn-outline-light rounded-pill px-2.5 py-1 crop-ratio-btn" onclick="setCropRatio(1/1, this)">1:1 (Square)</button>
                    <button type="button" class="btn btn-xs btn-outline-light rounded-pill px-2.5 py-1 crop-ratio-btn" onclick="setCropRatio(3/1, this)">3:1 (Banner)</button>
                    <button type="button" class="btn btn-xs btn-outline-light rounded-pill px-2.5 py-1 crop-ratio-btn" onclick="setCropRatio(16/9, this)">16:9 (Social)</button>
                </div>

                <!-- Cropper Canvas Container -->
                <div style="max-height: 460px; width: 100%; display: flex; align-items: center; justify-content: center; background: #0f172a; border-radius: 8px; overflow: hidden;">
                    <img id="cropperImageElement" src="" alt="Crop Target" style="max-width: 100%; max-height: 440px; display: block;">
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <!-- Control Buttons -->
                <div class="btn-group shadow-xs">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cropper.zoom(0.1)" title="Zoom In">
                        <i class="fa-solid fa-magnifying-glass-plus"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cropper.zoom(-0.1)" title="Zoom Out">
                        <i class="fa-solid fa-magnifying-glass-minus"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cropper.rotate(-90)" title="Rotate Left">
                        <i class="fa-solid fa-rotate-left"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cropper.rotate(90)" title="Rotate Right">
                        <i class="fa-solid fa-rotate-right"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cropper.reset()" title="Reset">
                        <i class="fa-solid fa-arrows-rotate"></i>
                    </button>
                </div>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-3 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-xs" onclick="applyCroppedImage()">
                        <i class="fa-solid fa-check me-1.5"></i> Apply Crop
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

    function setCropRatio(ratio, btn) {
        if (!cropper) return;
        cropper.setAspectRatio(ratio);
        document.querySelectorAll('.crop-ratio-btn').forEach(b => b.classList.remove('active', 'btn-primary'));
        if (btn) btn.classList.add('active', 'btn-primary');
    }

    function setLogoBg(mode, btn) {
        const container = document.getElementById('logoContainer');
        if (!container) return;
        if (mode === 'white') {
            container.style.background = '#ffffff';
            container.style.backgroundImage = 'none';
        } else if (mode === 'dark') {
            container.style.background = '#0f172a';
            container.style.backgroundImage = 'none';
        } else if (mode === 'grid') {
            container.style.background = '#f1f5f9';
            container.style.backgroundImage = 'linear-gradient(45deg, #e2e8f0 25%, transparent 25%), linear-gradient(-45deg, #e2e8f0 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #e2e8f0 75%), linear-gradient(-45deg, transparent 75%, #e2e8f0 75%)';
            container.style.backgroundSize = '16px 16px';
            container.style.backgroundPosition = '0 0, 0 8px, 8px -8px, -8px 0px';
        }
        if (btn && btn.parentElement) {
            btn.parentElement.querySelectorAll('button').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        }
    }

    function updateLogoDimensions() {
        const height = document.getElementById('sliderLogoHeight')?.value || 52;
        const width = document.getElementById('sliderLogoWidth')?.value || 220;
        const scale = document.getElementById('sliderLogoScale')?.value || 100;

        // Update badges
        const bHeight = document.getElementById('badgeHeight');
        const bWidth = document.getElementById('badgeWidth');
        const bScale = document.getElementById('badgeScale');
        if (bHeight) bHeight.textContent = `${height}px`;
        if (bWidth) bWidth.textContent = `${width}px`;
        if (bScale) bScale.textContent = `${scale}%`;

        const lblH = document.getElementById('lblHeight');
        const lblW = document.getElementById('lblWidth');
        const lblS = document.getElementById('lblScale');
        if (lblH) lblH.textContent = `${height}px`;
        if (lblW) lblW.textContent = `${width}px`;
        if (lblS) lblS.textContent = `${scale}%`;

        // Update Live Header Preview
        const wrapper = document.getElementById('logoWrapper');
        const img = document.getElementById('logoPreviewImg');

        if (wrapper) {
            wrapper.style.maxHeight = `${height}px`;
            wrapper.style.maxWidth = `${width}px`;
        }
        if (img) {
            img.style.maxHeight = `${height}px`;
            img.style.maxWidth = `${width}px`;
            img.style.transform = `scale(${scale / 100})`;
            img.style.transformOrigin = 'left center';
        }
    }

    function resetLogoSliders() {
        const sHeight = document.getElementById('sliderLogoHeight');
        const sWidth = document.getElementById('sliderLogoWidth');
        const sScale = document.getElementById('sliderLogoScale');

        if (sHeight) sHeight.value = 52;
        if (sWidth) sWidth.value = 220;
        if (sScale) sScale.value = 100;

        updateLogoDimensions();
    }

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
        const text = document.getElementById('noticeTextInput').value || 'The announcement message will appear here in real-time.';
        const type = document.getElementById('noticeTypeSelect').value;
        const isActive = document.getElementById('noticeActiveSwitch').checked;
        const box = document.getElementById('noticePreviewBox');
        const textEl = document.getElementById('noticePreviewText');
        const statusEl = document.getElementById('noticeStatusText');

        box.className = `alert alert-${type} rounded-3 shadow-xs d-flex align-items-center gap-2 mb-0 ${!isActive ? 'opacity-50' : ''}`;
        textEl.textContent = text;
        statusEl.textContent = isActive ? '🟢 Notice is currently active on live website' : '⚪ Notice is currently disabled';
    }

    function updateInvoicePreview() {
        const name = document.getElementById('invSenderName')?.value || 'Idea Prakashan';
        const addr = document.getElementById('invSenderAddress')?.value || 'Central Road, Rangpur 5400, Bangladesh';
        const phone = document.getElementById('invSenderPhone')?.value || '01558712870';
        const email = document.getElementById('invSenderEmail')?.value || 'ideapbd@gmail.com';
        const website = document.getElementById('invSenderWebsite')?.value || 'www.ideaabd.com';
        const title = document.getElementById('invTitle')?.value || 'Cash Memo / Invoice';
        const terms = document.getElementById('invTerms')?.value || 'Please inspect books upon delivery.';
        const footer = document.getElementById('invFooter')?.value || 'Thank you for choosing ideaabd!';

        if (document.getElementById('prevSenderName')) document.getElementById('prevSenderName').textContent = name;
        if (document.getElementById('prevSenderAddress')) document.getElementById('prevSenderAddress').textContent = addr;
        if (document.getElementById('prevSenderPhone')) document.getElementById('prevSenderPhone').textContent = phone;
        if (document.getElementById('prevSenderEmail')) document.getElementById('prevSenderEmail').textContent = email;
        if (document.getElementById('prevSenderWebsite')) document.getElementById('prevSenderWebsite').textContent = website;
        if (document.getElementById('prevInvTitle')) document.getElementById('prevInvTitle').textContent = title;
        if (document.getElementById('prevInvTerms')) document.getElementById('prevInvTerms').textContent = terms;
        if (document.getElementById('prevInvFooter')) document.getElementById('prevInvFooter').textContent = footer;
    }

    // ══════════════════════════════════════════════════════════════════════
    // DYNAMIC HEADER MENU BUILDER JAVASCRIPT
    // ══════════════════════════════════════════════════════════════════════
    window.headerMenuItemsData = @json($headerMenuItems ?? \App\Support\SiteSetting::headerNav());

    const defaultMenuItems = [
        { id: '1', label: 'হোম', route: 'home', url: '/', icon: 'house', active: 'home', is_active: true, target: '_self', badge: '' },
        { id: '2', label: 'বইসমূহ', route: 'book.index', url: '/books', icon: 'book', active: 'book.*', is_active: true, target: '_self', badge: '' },
        { id: '3', label: 'ই-বুক', route: 'ebook.index', url: '/ebooks', icon: 'tablet-screen-button', active: 'ebook.*', is_active: true, target: '_self', badge: 'নতুন' },
        { id: '4', label: 'লেখক', route: 'authors.index', url: '/authors', icon: 'pen-fancy', active: 'authors.*', is_active: true, target: '_self', badge: '' },
        { id: '5', label: 'প্রকাশক', route: 'publishers.index', url: '/publishers', icon: 'building', active: 'publishers.*', is_active: true, target: '_self', badge: '' },
        { id: '6', label: 'আইডিয়াপত্র', route: 'blog.index', url: '/blog', icon: 'newspaper', active: 'blog.*', is_active: true, target: '_self', badge: '' },
        { id: '7', label: 'ওয়েবজিন', route: 'webzine.index', url: '/webzines', icon: 'book-open', active: 'webzine.*', is_active: true, target: '_self', badge: '' },
        { id: '8', label: 'গবেষণা', route: 'research.index', url: '/research', icon: 'flask', active: 'research.*', is_active: true, target: '_self', badge: '' },
        { id: '9', label: 'আইডিয়া হাব', route: 'hub', url: '/hub', icon: 'compass', active: 'hub', is_active: true, target: '_self', badge: '' },
        { id: '10', label: 'আমাদের সম্পর্কে', route: 'about', url: '/about', icon: 'circle-info', active: 'about', is_active: true, target: '_self', badge: '' },
        { id: '11', label: 'যোগাযোগ', route: 'contact', url: '/contact', icon: 'envelope', active: 'contact', is_active: true, target: '_self', badge: '' },
    ];

    function renderMenuTable() {
        const tbody = document.getElementById('menuItemsTableBody');
        if (!tbody) return;
        tbody.innerHTML = '';

        if (!window.headerMenuItemsData || window.headerMenuItemsData.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-muted small"><i class="fa-solid fa-folder-open fs-3 d-block mb-1"></i>কোনো মেনু আইটেম নেই। উপরের প্রিসেট বা নতুন আইটেম বাটনে ক্লিক করুন।</td></tr>`;
            renderLiveMenuSimulator();
            syncMenuJson();
            return;
        }

        window.headerMenuItemsData.forEach((item, idx) => {
            const tr = document.createElement('tr');
            tr.className = item.is_active ? '' : 'table-light opacity-75';
            
            tr.innerHTML = `
                <td class="ps-3 fw-bold text-muted small">${idx + 1}</td>
                <td>
                    <input type="text" class="form-control form-control-sm rounded-3 fw-semibold" 
                           value="${escapeHtml(item.label || '')}" 
                           placeholder="যেমন: বইসমূহ" 
                           oninput="updateMenuItem(${idx}, 'label', this.value)">
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control rounded-start-3 font-monospace small" 
                               value="${escapeHtml(item.route || item.url || '')}" 
                               placeholder="e.g. book.index or /books" 
                               oninput="updateMenuItem(${idx}, 'route_or_url', this.value)">
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-primary px-2"><i class="fa-solid fa-${escapeHtml(item.icon || 'link')}"></i></span>
                        <input type="text" class="form-control rounded-end-3 small" 
                               value="${escapeHtml(item.icon || 'link')}" 
                               placeholder="icon name" 
                               oninput="updateMenuItem(${idx}, 'icon', this.value)">
                    </div>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm rounded-3 small text-center" 
                           value="${escapeHtml(item.badge || '')}" 
                           placeholder="যেমন: নতুন" 
                           oninput="updateMenuItem(${idx}, 'badge', this.value)">
                </td>
                <td>
                    <select class="form-select form-select-sm rounded-3 small" onchange="updateMenuItem(${idx}, 'target', this.value)">
                        <option value="_self" ${item.target === '_self' ? 'selected' : ''}>_self</option>
                        <option value="_blank" ${item.target === '_blank' ? 'selected' : ''}>_blank</option>
                    </select>
                </td>
                <td class="text-center">
                    <div class="form-check form-switch d-inline-block">
                        <input class="form-check-input" type="checkbox" ${item.is_active ? 'checked' : ''} onchange="toggleMenuItemActive(${idx})">
                    </div>
                </td>
                <td class="text-end pe-3">
                    <div class="btn-group btn-group-sm shadow-2xs">
                        <button type="button" class="btn btn-outline-secondary btn-xs" onclick="moveMenuItem(${idx}, -1)" ${idx === 0 ? 'disabled' : ''} title="উপরে নিন">
                            <i class="fa-solid fa-arrow-up"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-xs" onclick="moveMenuItem(${idx}, 1)" ${idx === window.headerMenuItemsData.length - 1 ? 'disabled' : ''} title="নিচে নিন">
                            <i class="fa-solid fa-arrow-down"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-xs" onclick="deleteMenuItem(${idx})" title="মুছুন">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });

        renderLiveMenuSimulator();
        syncMenuJson();
    }

    function renderLiveMenuSimulator() {
        const navEl = document.getElementById('liveHeaderNavPreview');
        const badgeEl = document.getElementById('liveMenuCountBadge');
        if (!navEl) return;
        navEl.innerHTML = '';

        const activeItems = (window.headerMenuItemsData || []).filter(item => item.is_active);
        if (badgeEl) badgeEl.textContent = `${activeItems.length}টি সক্রিয় মেনু আইটেম`;

        if (activeItems.length === 0) {
            navEl.innerHTML = `<li class="text-muted small py-1">কোনো সক্রিয় মেনু আইটেম নেই</li>`;
            return;
        }

        activeItems.forEach(item => {
            const li = document.createElement('li');
            li.className = 'nav-item';
            li.innerHTML = `
                <span class="nav-link py-1.5 px-2.5 text-dark fw-semibold small d-inline-flex align-items-center gap-1 border rounded-pill bg-light mx-0.5" style="font-size: 12px;">
                    <i class="fa-solid fa-${escapeHtml(item.icon || 'link')} text-primary" style="font-size: 11px;"></i>
                    <span>${escapeHtml(item.label)}</span>
                    ${item.badge ? `<span class="badge bg-danger text-white rounded-pill px-1 py-0.2" style="font-size: 8px;">${escapeHtml(item.badge)}</span>` : ''}
                </span>
            `;
            navEl.appendChild(li);
        });
    }

    function updateMenuItem(index, field, value) {
        if (!window.headerMenuItemsData[index]) return;
        if (field === 'route_or_url') {
            if (value.startsWith('/') || value.startsWith('http')) {
                window.headerMenuItemsData[index].url = value;
                window.headerMenuItemsData[index].route = '';
            } else {
                window.headerMenuItemsData[index].route = value;
                window.headerMenuItemsData[index].url = '';
            }
        } else {
            window.headerMenuItemsData[index][field] = value;
        }
        renderLiveMenuSimulator();
        syncMenuJson();
    }

    function toggleMenuItemActive(index) {
        if (!window.headerMenuItemsData[index]) return;
        window.headerMenuItemsData[index].is_active = !window.headerMenuItemsData[index].is_active;
        renderMenuTable();
    }

    function moveMenuItem(index, direction) {
        const targetIndex = index + direction;
        if (targetIndex < 0 || targetIndex >= window.headerMenuItemsData.length) return;
        const temp = window.headerMenuItemsData[index];
        window.headerMenuItemsData[index] = window.headerMenuItemsData[targetIndex];
        window.headerMenuItemsData[targetIndex] = temp;
        renderMenuTable();
    }

    function deleteMenuItem(index) {
        SwalConfirm({
            title: 'আইটেম মুছে ফেলুন',
            text: `আপনি কি "${window.headerMenuItemsData[index]?.label || 'এই'}" আইটেমটি মুছে ফেলতে চান?`,
            icon: 'warning',
            confirmButtonText: '<i class="fas fa-trash-can me-1"></i> হ্যাঁ, মুছুন',
            confirmButtonColor: '#ef4444',
            cancelButtonText: 'বাতিল'
        }).then(function(result) {
            if (result.isConfirmed) {
                window.headerMenuItemsData.splice(index, 1);
                renderMenuTable();
            }
        });
    }

    // Live Logo Dimensions & Simulator Updates
    function updateLogoDimensions() {
        const h = document.getElementById('sliderLogoHeight')?.value || 52;
        const w = document.getElementById('sliderLogoWidth')?.value || 220;
        const s = document.getElementById('sliderLogoScale')?.value || 100;
        const py = document.getElementById('sliderLogoPadY')?.value || 0;
        const px = document.getElementById('sliderLogoPadX')?.value || 0;
        const showText = document.getElementById('chkShowBrandText')?.checked ?? true;

        const badgeH = document.getElementById('badgeHeight');
        const badgeW = document.getElementById('badgeWidth');
        const badgeS = document.getElementById('badgeScale');
        const badgePy = document.getElementById('badgePadY');
        const badgePx = document.getElementById('badgePadX');

        const lblH = document.getElementById('lblHeight');
        const lblW = document.getElementById('lblWidth');
        const lblS = document.getElementById('lblScale');

        if (badgeH) badgeH.textContent = h + 'px';
        if (badgeW) badgeW.textContent = w + 'px';
        if (badgeS) badgeS.textContent = s + '%';
        if (badgePy) badgePy.textContent = py + 'px';
        if (badgePx) badgePx.textContent = px + 'px';

        if (lblH) lblH.textContent = h + 'px';
        if (lblW) lblW.textContent = w + 'px';
        if (lblS) lblS.textContent = s + '%';

        const wrapper = document.getElementById('logoWrapper');
        const img = document.getElementById('logoPreviewImg');
        const simBrandText = document.getElementById('simBrandText');

        if (wrapper) {
            wrapper.style.height = h + 'px';
            wrapper.style.maxHeight = h + 'px';
            wrapper.style.maxWidth = w + 'px';
            wrapper.style.padding = `${py}px ${px}px`;
        }
        if (img) {
            img.style.transform = `scale(${s / 100})`;
        }
        if (simBrandText) {
            simBrandText.style.display = showText ? '' : 'none';
        }
    }

    function resetLogoSliders() {
        const slH = document.getElementById('sliderLogoHeight');
        const slW = document.getElementById('sliderLogoWidth');
        const slS = document.getElementById('sliderLogoScale');
        const slPy = document.getElementById('sliderLogoPadY');
        const slPx = document.getElementById('sliderLogoPadX');
        const chkText = document.getElementById('chkShowBrandText');

        if (slH) slH.value = 52;
        if (slW) slW.value = 220;
        if (slS) slS.value = 100;
        if (slPy) slPy.value = 2;
        if (slPx) slPx.value = 0;
        if (chkText) chkText.checked = true;

        updateLogoDimensions();
    }

    function addMenuItemPreset(label, route, url, icon, badge) {
        window.headerMenuItemsData.push({
            id: String(Date.now()),
            label: label,
            route: route,
            url: url,
            icon: icon,
            active: route ? `${route.replace('.index', '')}.*` : '',
            is_active: true,
            target: '_self',
            badge: badge
        });
        renderMenuTable();
    }

    function addCustomMenuItem() {
        window.headerMenuItemsData.push({
            id: String(Date.now()),
            label: 'নতুন লিঙ্ক',
            route: '',
            url: '/custom-url',
            icon: 'link',
            active: '',
            is_active: true,
            target: '_self',
            badge: ''
        });
        renderMenuTable();
    }

    function resetMenuToDefaults() {
        SwalConfirm({
            title: 'ডিফল্ট রিস্টোর',
            text: 'আপনি কি ডিফল্ট হেডার মেনু তালিকা রিস্টোর করতে চান?',
            icon: 'question',
            confirmButtonText: '<i class="fas fa-rotate me-1"></i> হ্যাঁ, রিস্টোর করুন',
            confirmButtonColor: '#0284c7',
            cancelButtonText: 'বাতিল'
        }).then(function(result) {
            if (result.isConfirmed) {
                window.headerMenuItemsData = JSON.parse(JSON.stringify(defaultMenuItems));
                renderMenuTable();
            }
        });
    }

    function syncMenuJson() {
        const input = document.getElementById('header_menu_items');
        if (input) {
            input.value = JSON.stringify(window.headerMenuItemsData);
        }
    }

    function escapeHtml(str) {
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // ══════════════════════════════════════════════════════════════════
    // HERO SLIDES & CAROUSEL MANAGER JAVASCRIPT ENGINE
    // ══════════════════════════════════════════════════════════════════
    const defaultHeroSlides = [
        {
            id: '1',
            badge: 'বইমেলা বিশেষ ছাড়',
            badge_color: 'bg-warning text-dark',
            title: 'জ্ঞানের আলোয় উদ্ভাসিত হোক প্রতিটি মন',
            subtitle: 'আইডিয়া প্রকাশনীর সকল নতুন ও জনপ্রিয় বইয়ে পাচ্ছেন আকর্ষণীয় মূল্যছাড়।',
            btn_text: 'বই কিনুন',
            btn_url: '/books',
            btn_icon: 'fa-solid fa-cart-shopping',
            btn_class: 'btn-light text-primary',
            icon: 'fa-solid fa-book-open-reader',
            image_url: '',
            bg_gradient: 'linear-gradient(135deg, #003366 0%, #0066cc 100%)',
            is_active: true
        },
        {
            id: '2',
            badge: 'আইডিয়াপত্র ও ব্লগ',
            badge_color: 'bg-warning text-dark',
            title: 'আইডিয়াপত্র — মুক্তচিন্তা, সাহিত্য ও ব্লগ',
            subtitle: 'সমকালীন গল্প, কবিতা, প্রবন্ধ ও মুক্তচিন্তার ডিজিটাল প্রকাশনা ও নিবন্ধ।',
            btn_text: 'আইডিয়াপত্র পড়ুন',
            btn_url: '/blog',
            btn_icon: 'fa-solid fa-pen-nib',
            btn_class: 'btn-warning text-dark',
            icon: 'fa-solid fa-pen-nib',
            image_url: '',
            bg_gradient: 'linear-gradient(135deg, #4a044e 0%, #86198f 100%)',
            is_active: true
        },
        {
            id: '3',
            badge: 'স্মার্ট রিডিং',
            badge_color: 'bg-success text-white',
            title: 'হাজারো ডিজিটাল ই-বুক কালেকশন',
            subtitle: 'স্মার্টফোন বা যেকোনো ডিভাইসে তাৎক্ষণিক পিডিএফ ও ই-পাব ডাউনলোড করে পড়ার সুবিধা।',
            btn_text: 'ই-বুক লাইব্রেরি',
            btn_url: '/ebooks',
            btn_icon: 'fa-solid fa-mobile-screen-button',
            btn_class: 'btn-light text-primary',
            icon: 'fa-solid fa-mobile-screen-button',
            image_url: '',
            bg_gradient: 'linear-gradient(135deg, #064e3b 0%, #059669 100%)',
            is_active: true
        },
        {
            id: '4',
            badge: 'ডিজিটাল সাময়িকী',
            badge_color: 'bg-info text-dark',
            title: 'আইডিয়া ওয়েবজিন ও সাহিত্য সাময়িকী',
            subtitle: 'সাহিত্য, শিল্প ও সংস্কৃতির মাসিক ও বিশেষ সংখ্যাগুলোর ডিজিটাল সংকলন।',
            btn_text: 'সংখ্যাগুলো পড়ুন',
            btn_url: '/webzine',
            btn_icon: 'fa-solid fa-newspaper',
            btn_class: 'btn-warning text-dark',
            icon: 'fa-solid fa-newspaper',
            image_url: '',
            bg_gradient: 'linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%)',
            is_active: true
        },
        {
            id: '5',
            badge: 'লেখক কর্নার',
            badge_color: 'bg-light text-dark',
            title: 'লেখক ডিরেক্টরি ও সাহিত্যিক পরিচিতি',
            subtitle: 'দেশ-বিদেশের খ্যাতনামা ও প্রতিশ্রুতিশীল লেখকদের জীবন ও গ্রন্থাবলী।',
            btn_text: 'লেখক তালিকা দেখুন',
            btn_url: '/authors',
            btn_icon: 'fa-solid fa-user-pen',
            btn_class: 'btn-light text-primary',
            icon: 'fa-solid fa-user-pen',
            image_url: '',
            bg_gradient: 'linear-gradient(135deg, #1e1b4b 0%, #4338ca 100%)',
            is_active: true
        },
        {
            id: '6',
            badge: 'প্রকাশনা সংকলন',
            badge_color: 'bg-danger text-white',
            title: 'প্রকাশক ডিরেক্টরি ও প্রকাশনা সংস্থা',
            subtitle: 'বাংলাদেশের সকল স্বনামধন্য প্রকাশনীর বইয়ের বিশাল সম্ভার এক প্ল্যাটফর্মে।',
            btn_text: 'প্রকাশক তালিকা দেখুন',
            btn_url: '/publishers',
            btn_icon: 'fa-solid fa-building-columns',
            btn_class: 'btn-light text-primary',
            icon: 'fa-solid fa-building-columns',
            image_url: '',
            bg_gradient: 'linear-gradient(135deg, #881337 0%, #e11d48 100%)',
            is_active: true
        }
    ];

    window.heroSlidesData = @json(\App\Support\SiteSetting::heroSlides());
    if (!Array.isArray(window.heroSlidesData) || window.heroSlidesData.length === 0) {
        window.heroSlidesData = JSON.parse(JSON.stringify(defaultHeroSlides));
    }

    const iconPresets = [
        { icon: 'fa-solid fa-pen-nib', label: '✍️ পেন / নিব (আইডিয়াপত্র)' },
        { icon: 'fa-solid fa-book-open-reader', label: '📖 বই ও পাঠক (বুকশপ)' },
        { icon: 'fa-solid fa-mobile-screen-button', label: '📱 স্মার্টফোন (ই-বুক)' },
        { icon: 'fa-solid fa-newspaper', label: '📰 সাময়িকী ও ম্যাগাজিন' },
        { icon: 'fa-solid fa-user-pen', label: '👤 লেখক ডিরেক্টরি' },
        { icon: 'fa-solid fa-building-columns', label: '🏢 প্রকাশক ডিরেক্টরি' },
        { icon: 'fa-solid fa-feather-pointed', label: '✒️ পালক ও সাহিত্য' },
        { icon: 'fa-solid fa-wand-magic-sparkles', label: '✨ জ্ঞান ও ম্যাজিক' },
        { icon: 'fa-solid fa-crown', label: '👑 রয়্যাল বেস্টসেলার' },
        { icon: 'fa-solid fa-rocket', label: '🚀 ফিউচার ও ইনোভেশন' },
        { icon: 'fa-solid fa-graduation-cap', label: '🎓 শিক্ষা ও একাডেমি' },
        { icon: 'fa-solid fa-fire', label: '🔥 মেগা বইমেলা' },
    ];

    const gradientPresets = [
        { val: 'linear-gradient(135deg, #003366 0%, #0066cc 100%)', name: '🔵 রয়্যাল ব্লু' },
        { val: 'linear-gradient(135deg, #4a044e 0%, #86198f 100%)', name: '🟣 ম্যাজেন্টা পার্পল' },
        { val: 'linear-gradient(135deg, #064e3b 0%, #059669 100%)', name: '🟢 ডিপ এমারেল্ড' },
        { val: 'linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%)', name: '🌌 মিডনাইট স্যাফায়ার' },
        { val: 'linear-gradient(135deg, #1e1b4b 0%, #4338ca 100%)', name: '🟣 ইন্ডিগো ভায়োলেট' },
        { val: 'linear-gradient(135deg, #881337 0%, #e11d48 100%)', name: '🔴 সানসেট ক্রিমসন' },
        { val: 'linear-gradient(135deg, #18181b 0%, #27272a 100%)', name: '⚫ ডার্ক ওনিক্স' },
    ];

    function renderHeroSlides() {
        const container = document.getElementById('heroSlidesContainer');
        if (!container) return;
        container.innerHTML = '';

        window.heroSlidesData.forEach((slide, idx) => {
            const card = document.createElement('div');
            card.className = `card border rounded-4 shadow-sm overflow-hidden p-3 p-md-4 transition-all ${slide.is_active ? 'bg-white' : 'bg-light opacity-75'}`;
            
            let iconPresetBtns = iconPresets.map(p => `
                <button type="button" class="btn btn-xs ${slide.icon === p.icon ? 'btn-primary' : 'btn-outline-secondary'} rounded-pill px-2.5 py-1" onclick="updateHeroSlideField(${idx}, 'icon', '${p.icon}')">
                    <i class="${p.icon} me-1"></i>${p.label}
                </button>
            `).join('');

            let gradientPresetBtns = gradientPresets.map(g => `
                <button type="button" class="btn btn-xs ${slide.bg_gradient === g.val ? 'btn-primary' : 'btn-outline-secondary'} rounded-pill px-2.5 py-1" onclick="updateHeroSlideField(${idx}, 'bg_gradient', '${g.val}')">
                    ${g.name}
                </button>
            `).join('');

            card.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-dark text-white rounded-pill px-2.5 py-1">Slide #${idx + 1}</span>
                        <strong class="text-dark fs-6">${escapeHtml(slide.title || 'শিরোনামহীন স্লাইড')}</strong>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-info btn-xs rounded-pill px-2.5" onclick="renderLiveSlideSimulator(${idx})" title="সিমুলেটরে দেখুন">
                            <i class="fa-solid fa-eye me-1"></i>প্রিভিউ
                        </button>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" ${slide.is_active ? 'checked' : ''} onchange="toggleHeroSlideActive(${idx})" title="সক্রিয়/নিষ্ক্রিয়">
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary btn-xs" onclick="moveHeroSlide(${idx}, -1)" ${idx === 0 ? 'disabled' : ''} title="উপরে নিন"><i class="fa-solid fa-arrow-up"></i></button>
                            <button type="button" class="btn btn-outline-secondary btn-xs" onclick="moveHeroSlide(${idx}, 1)" ${idx === window.heroSlidesData.length - 1 ? 'disabled' : ''} title="নিচে নিন"><i class="fa-solid fa-arrow-down"></i></button>
                            <button type="button" class="btn btn-outline-danger btn-xs" onclick="deleteHeroSlide(${idx})" title="মুছুন"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-7">
                        <div class="mb-2.5">
                            <label class="form-label small fw-bold text-dark mb-1">মূল শিরোনাম (Title / Headline):</label>
                            <input type="text" class="form-control rounded-3" value="${escapeHtml(slide.title || '')}" oninput="updateHeroSlideField(${idx}, 'title', this.value)">
                        </div>
                        <div class="mb-2.5">
                            <label class="form-label small fw-bold text-dark mb-1">সংক্ষিপ্ত বিবরণী (Subtitle / Description):</label>
                            <textarea class="form-control rounded-3" rows="2" oninput="updateHeroSlideField(${idx}, 'subtitle', this.value)">${escapeHtml(slide.subtitle || '')}</textarea>
                        </div>
                        <div class="row g-2 mb-2.5">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark mb-1">টপ ব্যাজ লেখা (Badge Text):</label>
                                <input type="text" class="form-control form-control-sm rounded-3" value="${escapeHtml(slide.badge || '')}" oninput="updateHeroSlideField(${idx}, 'badge', this.value)">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark mb-1">ব্যাজ কালার (Badge Color):</label>
                                <select class="form-select form-select-sm rounded-3" onchange="updateHeroSlideField(${idx}, 'badge_color', this.value)">
                                    <option value="bg-warning text-dark" ${slide.badge_color === 'bg-warning text-dark' ? 'selected' : ''}>🟡 গোল্ডেন হলুদ (Warning)</option>
                                    <option value="bg-info text-dark" ${slide.badge_color === 'bg-info text-dark' ? 'selected' : ''}>🔵 আকাশি নীল (Info)</option>
                                    <option value="bg-success text-white" ${slide.badge_color === 'bg-success text-white' ? 'selected' : ''}>🟢 সবুজ (Success)</option>
                                    <option value="bg-danger text-white" ${slide.badge_color === 'bg-danger text-white' ? 'selected' : ''}>🔴 লাল (Danger)</option>
                                    <option value="bg-primary text-white" ${slide.badge_color === 'bg-primary text-white' ? 'selected' : ''}>🔷 ব্লু (Primary)</option>
                                    <option value="bg-light text-dark" ${slide.badge_color === 'bg-light text-dark' ? 'selected' : ''}>⚪ সাদা (Light)</option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark mb-1">বাটন টেক্সট:</label>
                                <input type="text" class="form-control form-control-sm rounded-3" value="${escapeHtml(slide.btn_text || '')}" oninput="updateHeroSlideField(${idx}, 'btn_text', this.value)">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark mb-1">বাটন লিংক (URL):</label>
                                <input type="text" class="form-control form-control-sm rounded-3" value="${escapeHtml(slide.btn_url || '')}" oninput="updateHeroSlideField(${idx}, 'btn_url', this.value)">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark mb-1">বাটন আইকন:</label>
                                <input type="text" class="form-control form-control-sm rounded-3" value="${escapeHtml(slide.btn_icon || 'fa-solid fa-arrow-right')}" oninput="updateHeroSlideField(${idx}, 'btn_icon', this.value)">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <!-- Modern Exclusive Icon Selector -->
                        <div class="p-3 bg-light rounded-4 border mb-3">
                            <label class="form-label small fw-bold text-dark mb-1 d-flex align-items-center justify-content-between">
                                <span><i class="fa-solid fa-wand-magic-sparkles text-primary me-1"></i>আধুনিক ৩ডি/গ্লাস আইকন:</span>
                                <span class="badge bg-white text-primary border"><i class="${slide.icon || 'fa-solid fa-book'} me-1"></i>প্রিভিউ</span>
                            </label>
                            <div class="d-flex flex-wrap gap-1 mb-2">
                                ${iconPresetBtns}
                            </div>
                            <input type="text" class="form-control form-control-sm rounded-3 mb-2" placeholder="কাস্টম FontAwesome আইকন ক্লাস (যেমন: fa-solid fa-pen-nib)" value="${escapeHtml(slide.icon || '')}" oninput="updateHeroSlideField(${idx}, 'icon', this.value)">
                            <div class="small text-muted mb-1">অথবা কাস্টম ছবি/SVG লিংক:</div>
                            <input type="text" class="form-control form-control-sm rounded-3" placeholder="যেমন: images/icons/custom.png" value="${escapeHtml(slide.image_url || '')}" oninput="updateHeroSlideField(${idx}, 'image_url', this.value)">
                        </div>

                        <!-- Gradient Style Selector -->
                        <div class="p-3 bg-light rounded-4 border">
                            <label class="form-label small fw-bold text-dark mb-1 d-flex align-items-center justify-content-between">
                                <span><i class="fa-solid fa-palette text-info me-1"></i>ব্যাকগ্রাউন্ড গ্রেডিয়েন্ট কালার:</span>
                            </label>
                            <div class="d-flex flex-wrap gap-1 mb-2">
                                ${gradientPresetBtns}
                            </div>
                            <input type="text" class="form-control form-control-sm rounded-3 font-monospace" style="font-size: 11px;" value="${escapeHtml(slide.bg_gradient || '')}" oninput="updateHeroSlideField(${idx}, 'bg_gradient', this.value)">
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(card);
        });

        renderLiveSlideSimulator(0);
        syncHeroSlidesJson();
    }

    function renderLiveSlideSimulator(slideIndex = 0) {
        const slide = window.heroSlidesData[slideIndex] || window.heroSlidesData[0];
        if (!slide) return;

        const simBox = document.getElementById('liveHeroSlideSimulator');
        const badgeEl = document.getElementById('simSlideBadge');
        const titleEl = document.getElementById('simSlideTitle');
        const subtitleEl = document.getElementById('simSlideSubtitle');
        const btnTextEl = document.getElementById('simSlideBtnText');
        const btnIconEl = document.getElementById('simSlideBtnIcon');
        const iconEl = document.getElementById('simSlideIcon');
        const countBadge = document.getElementById('liveSlideCountBadge');

        if (simBox) simBox.style.background = slide.bg_gradient || 'linear-gradient(135deg, #003366 0%, #0066cc 100%)';
        if (badgeEl) {
            badgeEl.textContent = slide.badge || 'অফার';
            badgeEl.className = `badge ${slide.badge_color || 'bg-warning text-dark'} fw-bold px-3 py-1 mb-2 rounded-pill shadow-sm small`;
        }
        if (titleEl) titleEl.textContent = slide.title || '';
        if (subtitleEl) subtitleEl.textContent = slide.subtitle || '';
        if (btnTextEl) btnTextEl.textContent = slide.btn_text || 'দেখুন';
        if (btnIconEl) btnIconEl.className = slide.btn_icon || 'fa-solid fa-arrow-right';
        if (iconEl) iconEl.className = `${slide.icon || 'fa-solid fa-book-open-reader'} text-white`;

        const activeCount = window.heroSlidesData.filter(s => s.is_active).length;
        if (countBadge) countBadge.textContent = `${activeCount}টি সক্রিয় স্লাইড`;
    }

    function updateHeroSlideField(index, field, value) {
        if (!window.heroSlidesData[index]) return;
        window.heroSlidesData[index][field] = value;
        renderLiveSlideSimulator(index);
        syncHeroSlidesJson();
    }

    function toggleHeroSlideActive(index) {
        if (!window.heroSlidesData[index]) return;
        window.heroSlidesData[index].is_active = !window.heroSlidesData[index].is_active;
        renderHeroSlides();
    }

    function moveHeroSlide(index, direction) {
        const targetIndex = index + direction;
        if (targetIndex < 0 || targetIndex >= window.heroSlidesData.length) return;
        const temp = window.heroSlidesData[index];
        window.heroSlidesData[index] = window.heroSlidesData[targetIndex];
        window.heroSlidesData[targetIndex] = temp;
        renderHeroSlides();
    }

    function deleteHeroSlide(index) {
        if (window.heroSlidesData.length <= 1) {
            Swal.fire({ title: 'সতর্কতা', text: 'কমপক্ষে একটি স্লাইড থাকা আবশ্যক!', icon: 'warning' });
            return;
        }
        SwalConfirm({
            title: 'স্লাইড মুছে ফেলুন',
            text: `আপনি কি "${window.heroSlidesData[index]?.title || 'এই'}" স্লাইডটি মুছে ফেলতে চান?`,
            icon: 'warning',
            confirmButtonText: '<i class="fas fa-trash-can me-1"></i> হ্যাঁ, মুছুন',
            confirmButtonColor: '#ef4444',
            cancelButtonText: 'বাতিল'
        }).then(function(result) {
            if (result.isConfirmed) {
                window.heroSlidesData.splice(index, 1);
                renderHeroSlides();
            }
        });
    }

    function addHeroSlidePreset(type) {
        let newSlide = null;
        if (type === 'bookshop') {
            newSlide = {
                id: String(Date.now()),
                badge: 'বইমেলা বিশেষ ছাড়',
                badge_color: 'bg-warning text-dark',
                title: 'জ্ঞানের আলোয় উদ্ভাসিত হোক প্রতিটি মন',
                subtitle: 'আইডিয়া প্রকাশনীর সকল নতুন ও জনপ্রিয় বইয়ে পাচ্ছেন আকর্ষণীয় মূল্যছাড়।',
                btn_text: 'বই কিনুন',
                btn_url: '/books',
                btn_icon: 'fa-solid fa-cart-shopping',
                btn_class: 'btn-light text-primary',
                icon: 'fa-solid fa-book-open-reader',
                image_url: '',
                bg_gradient: 'linear-gradient(135deg, #003366 0%, #0066cc 100%)',
                is_active: true
            };
        } else if (type === 'ideapatra') {
            newSlide = {
                id: String(Date.now()),
                badge: 'আইডিয়াপত্র ও ব্লগ',
                badge_color: 'bg-warning text-dark',
                title: 'আইডিয়াপত্র — মুক্তচিন্তা, সাহিত্য ও ব্লগ',
                subtitle: 'সমকালীন গল্প, কবিতা, প্রবন্ধ ও মুক্তচিন্তার ডিজিটাল প্রকাশনা ও নিবন্ধ।',
                btn_text: 'আইডিয়াপত্র পড়ুন',
                btn_url: '/blog',
                btn_icon: 'fa-solid fa-pen-nib',
                btn_class: 'btn-warning text-dark',
                icon: 'fa-solid fa-pen-nib',
                image_url: '',
                bg_gradient: 'linear-gradient(135deg, #4a044e 0%, #86198f 100%)',
                is_active: true
            };
        } else if (type === 'ebook') {
            newSlide = {
                id: String(Date.now()),
                badge: 'স্মার্ট রিডিং',
                badge_color: 'bg-success text-white',
                title: 'হাজারো ডিজিটাল ই-বুক কালেকশন',
                subtitle: 'স্মার্টফোন বা যেকোনো ডিভাইসে তাৎক্ষণিক পিডিএফ ও ই-পাব ডাউনলোড করে পড়ার সুবিধা।',
                btn_text: 'ই-বুক লাইব্রেরি',
                btn_url: '/ebooks',
                btn_icon: 'fa-solid fa-mobile-screen-button',
                btn_class: 'btn-light text-primary',
                icon: 'fa-solid fa-mobile-screen-button',
                image_url: '',
                bg_gradient: 'linear-gradient(135deg, #064e3b 0%, #059669 100%)',
                is_active: true
            };
        } else if (type === 'webzine') {
            newSlide = {
                id: String(Date.now()),
                badge: 'ডিজিটাল সাময়িকী',
                badge_color: 'bg-info text-dark',
                title: 'আইডিয়া ওয়েবজিন ও সাহিত্য সাময়িকী',
                subtitle: 'সাহিত্য, শিল্প ও সংস্কৃতির মাসিক ও বিশেষ সংখ্যাগুলোর ডিজিটাল সংকলন।',
                btn_text: 'সংখ্যাগুলো পড়ুন',
                btn_url: '/webzine',
                btn_icon: 'fa-solid fa-newspaper',
                btn_class: 'btn-warning text-dark',
                icon: 'fa-solid fa-newspaper',
                image_url: '',
                bg_gradient: 'linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%)',
                is_active: true
            };
        } else if (type === 'authors') {
            newSlide = {
                id: String(Date.now()),
                badge: 'লেখক কর্নার',
                badge_color: 'bg-light text-dark',
                title: 'লেখক ডিরেক্টরি ও সাহিত্যিক পরিচিতি',
                subtitle: 'দেশ-বিদেশের খ্যাতনামা ও প্রতিশ্রুতিশীল লেখকদের জীবন ও গ্রন্থাবলী।',
                btn_text: 'লেখক তালিকা দেখুন',
                btn_url: '/authors',
                btn_icon: 'fa-solid fa-user-pen',
                btn_class: 'btn-light text-primary',
                icon: 'fa-solid fa-user-pen',
                image_url: '',
                bg_gradient: 'linear-gradient(135deg, #1e1b4b 0%, #4338ca 100%)',
                is_active: true
            };
        } else if (type === 'publishers') {
            newSlide = {
                id: String(Date.now()),
                badge: 'প্রকাশনা সংকলন',
                badge_color: 'bg-danger text-white',
                title: 'প্রকাশক ডিরেক্টরি ও প্রকাশনা সংস্থা',
                subtitle: 'বাংলাদেশের সকল স্বনামধন্য প্রকাশনীর বইয়ের বিশাল সম্ভার এক প্ল্যাটফর্মে।',
                btn_text: 'প্রকাশক তালিকা দেখুন',
                btn_url: '/publishers',
                btn_icon: 'fa-solid fa-building-columns',
                btn_class: 'btn-light text-primary',
                icon: 'fa-solid fa-building-columns',
                image_url: '',
                bg_gradient: 'linear-gradient(135deg, #881337 0%, #e11d48 100%)',
                is_active: true
            };
        }

        if (newSlide) {
            window.heroSlidesData.push(newSlide);
            renderHeroSlides();
        }
    }

    function addNewHeroSlide() {
        addHeroSlidePreset('bookshop');
    }

    function resetHeroSlidesToDefaults() {
        SwalConfirm({
            title: 'ডিফল্ট রিস্টোর',
            text: 'আপনি কি ডিফল্ট ৬টি হিরো স্লাইডার তালিকা রিস্টোর করতে চান?',
            icon: 'question',
            confirmButtonText: '<i class="fas fa-rotate me-1"></i> হ্যাঁ, রিস্টোর করুন',
            confirmButtonColor: '#0284c7',
            cancelButtonText: 'বাতিল'
        }).then(function(result) {
            if (result.isConfirmed) {
                window.heroSlidesData = JSON.parse(JSON.stringify(defaultHeroSlides));
                renderHeroSlides();
            }
        });
    }

    function syncHeroSlidesJson() {
        const input = document.getElementById('home_hero_slides');
        if (input) {
            input.value = JSON.stringify(window.heroSlidesData);
        }
    }

    // Auto-restore Active Tab from URL Hash & Init Menu/Slide Tables
    document.addEventListener('DOMContentLoaded', function () {
        renderHeroSlides();
        renderMenuTable();

        const form = document.getElementById('systemSettingsForm');
        if (form) {
            form.addEventListener('submit', function () {
                syncHeroSlidesJson();
                syncMenuJson();
            });
        }

        const hash = window.location.hash;
        if (hash) {
            const targetBtn = document.querySelector(`button[data-bs-target="${hash}"]`);
            if (targetBtn) {
                const tab = new bootstrap.Tab(targetBtn);
                tab.show();
            }
        }

        document.querySelectorAll('button[data-bs-toggle="pill"]').forEach(btn => {
            btn.addEventListener('shown.bs.tab', function (e) {
                const target = e.target.getAttribute('data-bs-target');
                if (target) {
                    history.replaceState(null, null, target);
                }
            });
        });
    });

    function addBoardMemberRow() {
        const container = document.getElementById('boardMembersContainer');
        if (!container) return;
        const row = document.createElement('div');
        row.className = 'd-flex align-items-center gap-2 board-row animate__animated animate__fadeIn';
        row.innerHTML = `
            <input type="text" name="board_role[]" class="form-control form-control-sm rounded-3" placeholder="পদ (যেমন: সহ-সম্পাদক)" style="flex: 1;">
            <input type="text" name="board_name[]" class="form-control form-control-sm rounded-3" placeholder="নাম..." style="flex: 1.2;">
            <button type="button" class="btn btn-outline-danger btn-sm rounded-3 px-2" onclick="this.closest('.board-row').remove()"><i class="fa-solid fa-trash"></i></button>
        `;
        container.appendChild(row);
    }
</script>
@endpush
@endsection
