@php
    $type = $type ?? 'sidebar'; // 'sidebar', 'in-article', 'bottom', 'compact-banner'
    $adSettings = \Illuminate\Support\Facades\Schema::hasTable('admin_dashboard_settings')
        ? \App\Models\AdminDashboardSetting::where('key', 'advertisement_settings')->value('value')
        : null;
    $adClient = $adSettings['google_adsense_client'] ?? config('services.google_adsense.client_id') ?? env('GOOGLE_ADSENSE_CLIENT_ID', 'ca-pub-1234567890123456');
    $adSlot = $slot ?? ($type === 'sidebar' ? ($adSettings['slot_sidebar'] ?? env('GOOGLE_ADSENSE_SLOT_SIDEBAR', '1234567890')) : ($adSettings['slot_article'] ?? env('GOOGLE_ADSENSE_SLOT_ARTICLE', '0987654321')));
@endphp

@if($type === 'sidebar')
    {{-- Compact Verified Sidebar Google Ad Container --}}
    <div class="card p-2.5 mb-3 border-0 shadow-sm rounded-4 bg-white text-center position-relative overflow-hidden google-ad-container" style="border: 1px solid #f1f5f9 !important;">
        <div class="d-flex align-items-center justify-content-between mb-1.5 px-1">
            <span class="badge bg-light text-muted border px-2 py-0.5 d-inline-flex align-items-center gap-1 rounded-pill" style="font-size: 0.62rem; letter-spacing: 0.3px;">
                <i class="fa-solid fa-circle-check text-success"></i> ভেরিফাইড বিজ্ঞাপন
            </span>
            <span class="d-inline-flex align-items-center gap-1 text-muted" style="font-size: 0.65rem;">
                <i class="fa-brands fa-google text-primary"></i> Ads
            </span>
        </div>

        <div class="google-ad-slot-wrapper rounded-3 overflow-hidden bg-light bg-opacity-50 d-flex align-items-center justify-content-center p-2" style="min-height: 180px; max-height: 280px;">
            {{-- Google AdSense Code --}}
            <ins class="adsbygoogle"
                 style="display:block; min-width: 200px; min-height: 160px;"
                 data-ad-client="{{ $adClient }}"
                 data-ad-slot="{{ $adSlot }}"
                 data-ad-format="rectangle,horizontal"
                 data-full-width-responsive="true"></ins>
            
            {{-- Fallback aesthetic notice if ads are loading or blocked --}}
            <div class="p-2.5 text-muted small google-ad-fallback text-center" style="pointer-events: none;">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-1" style="width: 32px; height: 32px;">
                    <i class="fa-brands fa-google fs-6"></i>
                </div>
                <span class="fw-semibold d-block text-dark" style="font-size: 0.78rem;">আইডিয়া সাহিত্য সাময়িকী</span>
                <span class="text-muted d-block" style="font-size: 0.68rem;">Google Verified Ad Space</span>
            </div>
        </div>
    </div>

@elseif($type === 'in-article' || $type === 'bottom' || $type === 'compact-banner')
    {{-- Compact Verified In-Article / Bottom Google Ad Container --}}
    <div class="card p-2.5 my-3 border-0 shadow-sm rounded-4 bg-white text-center position-relative overflow-hidden google-ad-container" style="border: 1px solid #f1f5f9 !important;">
        <div class="d-flex align-items-center justify-content-between mb-1.5 px-1">
            <div class="d-flex align-items-center gap-1.5">
                <span class="badge bg-light text-muted border px-2 py-0.5 rounded-pill d-inline-flex align-items-center gap-1" style="font-size: 0.62rem;">
                    <i class="fa-solid fa-circle-check text-success"></i> স্পন্সরড / বিজ্ঞাপন
                </span>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-1.5 py-0.5 rounded-pill" style="font-size: 0.58rem;">
                    ভেরিফাইড
                </span>
            </div>
            <span class="d-inline-flex align-items-center gap-1 text-muted" style="font-size: 0.65rem;">
                <i class="fa-brands fa-google text-primary"></i> Google Ads
            </span>
        </div>

        <div class="google-ad-slot-wrapper rounded-3 overflow-hidden bg-light bg-opacity-50 d-flex align-items-center justify-content-center p-1.5" style="min-height: 80px; max-height: 120px;">
            {{-- Google AdSense Code --}}
            <ins class="adsbygoogle"
                 style="display:block; min-height: 75px;"
                 data-ad-format="horizontal"
                 data-ad-client="{{ $adClient }}"
                 data-ad-slot="{{ $adSlot }}"
                 data-full-width-responsive="true"></ins>

            {{-- Fallback aesthetic notice --}}
            <div class="p-2 text-muted small google-ad-fallback" style="pointer-events: none;">
                <div class="d-inline-flex align-items-center gap-2">
                    <i class="fa-brands fa-google text-primary fs-5 opacity-75"></i>
                    <div class="text-start">
                        <span class="fw-semibold d-block text-dark" style="font-size: 0.78rem;">আইডিয়া প্রকাশন ও সাহিত্যপত্র</span>
                        <span class="text-muted" style="font-size: 0.68rem;">ভেরিফাইড গুগল বিজ্ঞাপন স্পেস</span>
                    </div>
                </div>
            </div>
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
