@php
    $type = $type ?? 'sidebar'; // 'sidebar', 'in-article', 'banner'
    $adClient = config('services.google_adsense.client_id') ?? env('GOOGLE_ADSENSE_CLIENT_ID', 'ca-pub-XXXXXXXXXXXXXXXX');
    $adSlot = $slot ?? ($type === 'sidebar' ? env('GOOGLE_ADSENSE_SLOT_SIDEBAR', '1234567890') : env('GOOGLE_ADSENSE_SLOT_ARTICLE', '0987654321'));
@endphp

@if($type === 'sidebar')
    {{-- Sidebar Google Ad Container (Responsive Display Ad) --}}
    <div class="card p-3 mb-3.5 border-0 shadow-sm rounded-4 bg-white text-center position-relative overflow-hidden google-ad-container">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="badge bg-light text-muted border px-2 py-0.5" style="font-size: 0.62rem; letter-spacing: 0.5px; text-transform: uppercase;">বিজ্ঞাপন / ADVERTISEMENT</span>
            <small class="text-muted" style="font-size: 0.62rem;">Google Ads</small>
        </div>

        <div class="google-ad-slot-wrapper rounded-3 overflow-hidden bg-light d-flex align-items-center justify-content-center" style="min-height: 250px;">
            {{-- Google AdSense Code --}}
            <ins class="adsbygoogle"
                 style="display:block"
                 data-ad-client="{{ $adClient }}"
                 data-ad-slot="{{ $adSlot }}"
                 data-ad-format="auto"
                 data-full-width-responsive="true"></ins>
            
            {{-- Fallback aesthetic notice if ads are blocked or loading --}}
            <div class="p-3 text-muted small google-ad-fallback" style="pointer-events: none;">
                <i class="fa-brands fa-google text-primary fs-3 mb-1 d-block opacity-75"></i>
                <span class="fw-semibold d-block text-dark" style="font-size: 0.8rem;">আইডিয়া সাহিত্য সাময়িকী</span>
                <span style="font-size: 0.72rem;">গুগল বিজ্ঞাপন প্রচার এলাকা</span>
            </div>
        </div>
    </div>

@elseif($type === 'in-article' || $type === 'bottom')
    {{-- In-Article / Post Bottom Google Ad Container (Matched / Responsive In-Feed Ad) --}}
    <div class="card p-3 my-4 border-0 shadow-sm rounded-4 bg-white text-center position-relative overflow-hidden google-ad-container">
        <div class="d-flex align-items-center justify-content-between mb-2 px-1">
            <span class="badge bg-light text-muted border px-2 py-0.5" style="font-size: 0.62rem; letter-spacing: 0.5px; text-transform: uppercase;">বিজ্ঞাপন / SPONSORED</span>
            <small class="text-muted" style="font-size: 0.62rem;">Google Ads</small>
        </div>

        <div class="google-ad-slot-wrapper rounded-3 overflow-hidden bg-light d-flex align-items-center justify-content-center py-2" style="min-height: 100px;">
            {{-- Google AdSense Code --}}
            <ins class="adsbygoogle"
                 style="display:block"
                 data-ad-format="fluid"
                 data-ad-layout-key="-fb+5w+4e-db+86"
                 data-ad-client="{{ $adClient }}"
                 data-ad-slot="{{ $adSlot }}"></ins>

            {{-- Fallback aesthetic notice --}}
            <div class="p-2 text-muted small google-ad-fallback" style="pointer-events: none;">
                <div class="d-inline-flex align-items-center gap-2">
                    <i class="fa-brands fa-google text-primary fs-4 opacity-75"></i>
                    <div class="text-start">
                        <span class="fw-semibold d-block text-dark" style="font-size: 0.82rem;">আইডিয়া প্রকাশন ও সাহিত্যপত্র</span>
                        <span style="font-size: 0.72rem;">বিজ্ঞাপন প্রচার স্পেস</span>
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
