@php
    $type = $type ?? 'sidebar'; // 'sidebar', 'video-shorts', 'in-article', 'sticky-anchor', 'multiplex', 'compact-banner'
    $adSettings = \Illuminate\Support\Facades\Schema::hasTable('admin_dashboard_settings')
        ? \App\Models\AdminDashboardSetting::where('key', 'advertisement_settings')->value('value')
        : null;
    $adClient = $adSettings['google_adsense_client'] ?? config('services.google_adsense.client_id') ?? env('GOOGLE_ADSENSE_CLIENT_ID', 'ca-pub-4534355865737776');
    $adSlot = $slot ?? ($adSettings['slot_' . str_replace('-', '_', $type)] ?? env('GOOGLE_ADSENSE_SLOT_' . strtoupper(str_replace('-', '_', $type)), '1234567890'));
@endphp

{{-- ══════════════════════════════════════════════════════════════════════
     1. VIDEO SHORTS / OUTSTREAM VIDEO AD (Prothom Alo / Kaler Kantho Style)
     ══════════════════════════════════════════════════════════════════════ --}}
@if($type === 'video-shorts' || $type === 'video')
    <div class="card p-2.5 my-3 border-0 shadow-sm rounded-4 bg-white text-center position-relative overflow-hidden google-ad-container google-video-shorts-ad" style="border: 1px solid #e2e8f0 !important;">
        {{-- Header Bar --}}
        <div class="d-flex align-items-center justify-content-between mb-2 px-1">
            <div class="d-flex align-items-center gap-1.5">
                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-0.5 rounded-pill d-inline-flex align-items-center gap-1" style="font-size: 0.65rem; font-weight: 600;">
                    <i class="fa-solid fa-circle-play text-danger"></i> স্পন্সরড ভিডিও শর্টস
                </span>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-1.5 py-0.5 rounded-pill" style="font-size: 0.60rem;">
                    <i class="fa-solid fa-shield-halved me-0.5"></i> ভেরিফাইড এড
                </span>
            </div>
            <span class="d-inline-flex align-items-center gap-1 text-muted" style="font-size: 0.65rem;">
                <i class="fa-brands fa-google text-primary"></i> Ads
            </span>
        </div>

        {{-- Video Container --}}
        <div class="google-ad-slot-wrapper rounded-3 overflow-hidden position-relative bg-dark bg-opacity-90 d-flex align-items-center justify-content-center" style="min-height: 200px; max-height: 320px; aspect-ratio: 16/9;">
            {{-- Google AdSense Outstream Video Slot --}}
            <ins class="adsbygoogle"
                 style="display:block; width: 100%; height: 100%;"
                 data-ad-client="{{ $adClient }}"
                 data-ad-slot="{{ $adSlot }}"
                 data-ad-format="video,fluid"
                 data-full-width-responsive="true"></ins>

            {{-- Aesthetic Verified Video Fallback (when loading or test mode) --}}
            <div class="google-ad-fallback position-absolute inset-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center text-white p-3" style="background: radial-gradient(circle, #0f172a 0%, #020617 100%); pointer-events: none;">
                <div class="rounded-circle bg-white bg-opacity-15 d-inline-flex align-items-center justify-content-center text-danger mb-2 shadow-sm" style="width: 44px; height: 44px; backdrop-filter: blur(4px);">
                    <i class="fa-solid fa-play fs-5 ms-0.5"></i>
                </div>
                <h6 class="fw-bold text-white mb-1" style="font-size: 0.88rem;">আইডিয়া সাহিত্য ও প্রকাশনা ডিজিটাল</h6>
                <span class="text-white-50 small" style="font-size: 0.72rem;">গুগল ভেরিফাইড শর্ট ভিডিও বিজ্ঞাপন স্পেস</span>
                <span class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-25 px-2 py-0.5 rounded-pill mt-2" style="font-size: 0.60rem;">
                    <i class="fa-brands fa-google me-1 text-warning"></i> Google Ad Manager
                </span>
            </div>
        </div>
    </div>

{{-- ══════════════════════════════════════════════════════════════════════
     2. IN-ARTICLE NON-INTRUSIVE NATIVE AD (Reads seamlessly with text)
     ══════════════════════════════════════════════════════════════════════ --}}
@elseif($type === 'in-article' || $type === 'compact-banner')
    <div class="card p-2.5 my-3.5 border-0 shadow-sm rounded-4 bg-white text-center position-relative overflow-hidden google-ad-container" style="border: 1px solid #f1f5f9 !important; background: #fafafa;">
        <div class="d-flex align-items-center justify-content-between mb-1.5 px-1">
            <div class="d-flex align-items-center gap-1.5">
                <span class="badge bg-light text-muted border px-2 py-0.5 rounded-pill d-inline-flex align-items-center gap-1" style="font-size: 0.62rem;">
                    <i class="fa-solid fa-circle-check text-success"></i> বিজ্ঞাপন
                </span>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-1.5 py-0.5 rounded-pill" style="font-size: 0.58rem;">
                    ভেরিফাইড
                </span>
            </div>
            <span class="d-inline-flex align-items-center gap-1 text-muted" style="font-size: 0.65rem;">
                <i class="fa-brands fa-google text-primary"></i> Google Ads
            </span>
        </div>

        <div class="google-ad-slot-wrapper rounded-3 overflow-hidden bg-white d-flex align-items-center justify-content-center p-2 border" style="min-height: 90px; border-color: #f1f5f9 !important;">
            {{-- Google AdSense In-Article Unit --}}
            <ins class="adsbygoogle"
                 style="display:block; text-align:center; width: 100%;"
                 data-ad-layout="in-article"
                 data-ad-format="fluid"
                 data-ad-client="{{ $adClient }}"
                 data-ad-slot="{{ $adSlot }}"></ins>

            {{-- Fallback aesthetic notice --}}
            <div class="p-2 text-muted small google-ad-fallback" style="pointer-events: none;">
                <div class="d-inline-flex align-items-center gap-2">
                    <i class="fa-brands fa-google text-primary fs-5 opacity-75"></i>
                    <div class="text-start">
                        <span class="fw-semibold d-block text-dark" style="font-size: 0.78rem;">আইডিয়া প্রকাশন ও সাহিত্যপত্র</span>
                        <span class="text-muted" style="font-size: 0.68rem;">Google Verified Ad Placement</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

{{-- ══════════════════════════════════════════════════════════════════════
     3. SIDEBAR RESPONSIVE DISPLAY AD
     ══════════════════════════════════════════════════════════════════════ --}}
@elseif($type === 'sidebar')
    <div class="card p-2.5 mb-3 border-0 shadow-sm rounded-4 bg-white text-center position-relative overflow-hidden google-ad-container" style="border: 1px solid #f1f5f9 !important;">
        <div class="d-flex align-items-center justify-content-between mb-1.5 px-1">
            <span class="badge bg-light text-muted border px-2 py-0.5 d-inline-flex align-items-center gap-1 rounded-pill" style="font-size: 0.62rem;">
                <i class="fa-solid fa-circle-check text-success"></i> ভেরিফাইড বিজ্ঞাপন
            </span>
            <span class="d-inline-flex align-items-center gap-1 text-muted" style="font-size: 0.65rem;">
                <i class="fa-brands fa-google text-primary"></i> Ads
            </span>
        </div>

        <div class="google-ad-slot-wrapper rounded-3 overflow-hidden bg-light bg-opacity-50 d-flex align-items-center justify-content-center p-2" style="min-height: 180px; max-height: 280px;">
            <ins class="adsbygoogle"
                 style="display:block; min-width: 200px; min-height: 160px;"
                 data-ad-client="{{ $adClient }}"
                 data-ad-slot="{{ $adSlot }}"
                 data-ad-format="rectangle,horizontal"
                 data-full-width-responsive="true"></ins>
            
            <div class="p-2.5 text-muted small google-ad-fallback text-center" style="pointer-events: none;">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-1" style="width: 32px; height: 32px;">
                    <i class="fa-brands fa-google fs-6"></i>
                </div>
                <span class="fw-semibold d-block text-dark" style="font-size: 0.78rem;">আইডিয়া সাহিত্য সাময়িকী</span>
                <span class="text-muted d-block" style="font-size: 0.68rem;">Google Verified Ad Space</span>
            </div>
        </div>
    </div>

{{-- ══════════════════════════════════════════════════════════════════════
     4. BOTTOM SMART STICKY ANCHOR BANNER (With 1-Click Dismiss)
     ══════════════════════════════════════════════════════════════════════ --}}
@elseif($type === 'sticky-anchor')
    <div id="smartStickyAdBar" class="fixed-bottom p-2 bg-white border-top shadow-lg d-none d-md-block" style="z-index: 1040; transition: transform 0.3s ease; border-color: #e2e8f0 !important; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(10px);">
        <div class="container d-flex align-items-center justify-content-between gap-3 position-relative" style="max-width: 980px;">
            
            {{-- Ad Badge & Info --}}
            <div class="d-none d-lg-flex align-items-center gap-1.5 flex-shrink-0">
                <span class="badge bg-light text-muted border px-2 py-1 rounded-pill" style="font-size: 0.65rem;">
                    <i class="fa-solid fa-circle-check text-success me-0.5"></i> স্পন্সরড
                </span>
            </div>

            {{-- Center Ad Slot --}}
            <div class="flex-grow-1 text-center overflow-hidden" style="min-height: 50px; max-height: 90px;">
                <ins class="adsbygoogle"
                     style="display:inline-block; min-width: 320px; height: 50px;"
                     data-ad-client="{{ $adClient }}"
                     data-ad-slot="{{ $adSlot }}"
                     data-ad-format="horizontal"></ins>
            </div>

            {{-- Close / Dismiss Button --}}
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1 flex-shrink-0 d-inline-flex align-items-center gap-1 shadow-2xs hover-danger" 
                    onclick="document.getElementById('smartStickyAdBar').style.display='none'" 
                    style="font-size: 0.72rem; border-color: #cbd5e1;" 
                    title="বিজ্ঞাপন বন্ধ করুন">
                <i class="fa-solid fa-xmark"></i>
                <span class="d-none d-sm-inline">বন্ধ করুন</span>
            </button>
        </div>
    </div>
@endif

@once
    @push('scripts')
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ $adClient }}" crossorigin="anonymous"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                try {
                    (adsbygoogle = window.adsbygoogle || []).push({});
                } catch (e) {}
            });
        </script>
    @endpush
@endonce
