@php
    $footerLogo = \App\Support\SiteSetting::logoUrl();
    $footerName = \App\Support\SiteSetting::name() ?: 'আইডিয়া প্রকাশন';
    $footerTagline = \App\Support\SiteSetting::tagline();
    $helplinePhone = \App\Support\SiteSetting::helplinePhone() ?: '+8801558712810';
    $helplineEmail = \App\Support\SiteSetting::helplineEmail() ?: 'support@ideaabd.com';
    $cleanPhone = preg_replace('/[^0-9]/', '', $helplinePhone);
@endphp

<footer class="site-footer text-white position-relative" style="background: linear-gradient(180deg, #0a192f 0%, #030b17 100%); border-top: 3px solid #0284c7;">
    {{-- Trust Features Bar (Dynamic Features, Sharp Geometry, Clean Padding) --}}
    @php
        $footerFeatures = \App\Support\SiteSetting::footerFeatures();
    @endphp
    <div class="py-3.5 border-bottom footer-features-strip" style="border-color: rgba(255,255,255,0.08) !important; background: rgba(255,255,255,0.02);">
        <div class="container">
            <div class="row g-3 text-center text-md-start">
                @foreach($footerFeatures as $feat)
                    @php
                        $featUrl = $feat['url'] ?? null;
                    @endphp
                    <div class="col-6 col-lg-3">
                        @if($featUrl)
                            <a href="{{ $featUrl }}" class="footer-feature-item text-decoration-none d-flex align-items-center justify-content-center justify-content-md-start gap-2.5 p-2 rounded-3 hover-feature transition-all">
                        @else
                            <div class="footer-feature-item d-flex align-items-center justify-content-center justify-content-md-start gap-2.5 p-2 rounded-3">
                        @endif
                            <div class="footer-trust-icon" style="background: {{ $feat['bg_tint'] ?? 'rgba(255,255,255,0.05)' }}; border-color: {{ $feat['border_tint'] ?? 'rgba(255,255,255,0.1)' }}; color: {{ $feat['color'] ?? '#38bdf8' }};">
                                <i class="{{ $feat['icon'] }}"></i>
                            </div>
                            <div class="text-start min-w-0">
                                <div class="fw-bold text-white small text-truncate">{{ $feat['title'] }}</div>
                                <div class="text-white-50 text-truncate" style="font-size: 11.5px;">{{ $feat['subtitle'] }}</div>
                            </div>
                        @if($featUrl)
                            </a>
                        @else
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Main Footer Body (Balanced Padding & Sharp Typography) --}}
    <div class="container py-4">
        <div class="row g-4 justify-content-between">

            {{-- Column 1: Brand & About --}}
            <div class="col-lg-3 col-md-6">
                <div class="d-flex align-items-center gap-2.5 mb-3">
                    @if($footerLogo)
                        <img src="{{ $footerLogo }}" alt="{{ $footerName }}" style="max-height: 40px; width: auto; object-fit: contain; filter: brightness(1.1);" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">
                    @else
                        <div class="bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-xs footer-brand-box">
                            আই
                        </div>
                    @endif
                    <span class="fw-bold fs-4 text-white letter-spacing-wide">{{ $footerName }}</span>
                </div>
                <p class="text-slate-300 small mb-3" style="color: #cbd5e1; line-height: 1.8; font-size: 13.5px;">
                    {{ $footerTagline ?: 'বাংলাদেশের বিশ্বস্ত অনলাইন বই, ই-বুক ও সৃজনশীল প্রকাশনা প্ল্যাটফর্ম। লেখক, প্রকাশক ও পাঠকদের মুক্ত চিন্তার মিলনমেলা।' }}
                </p>
                <div class="d-flex align-items-center gap-2 pt-1 flex-wrap">
                    @foreach(\App\Support\SiteSetting::socialLinks() as $social)
                        <a href="{{ $social['url'] }}" target="_blank" rel="noopener" class="footer-social-btn" title="{{ $social['title'] }}" style="--social-hover-color: {{ $social['color'] ?? '#0284c7' }};">
                            <i class="{{ $social['icon'] }}"></i>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Column 2: Dynamic Catalog & Menu --}}
            <div class="col-lg-2 col-md-3 col-6">
                <h6 class="footer-heading text-white fw-bold mb-3 position-relative pb-2">
                    <span>ক্যাটালগ ও মেনু</span>
                </h6>
                <ul class="list-unstyled mb-0 footer-links">
                    @php
                        $footerNav = \App\Support\SiteSetting::headerNav();
                        $footerNavSlice = array_slice($footerNav, 0, 7);
                    @endphp
                    @foreach ($footerNavSlice as $fItem)
                        <li><a href="{{ $fItem['target_url'] ?? $fItem['url'] ?? '#' }}">{{ $fItem['label'] }}</a></li>
                    @endforeach
                </ul>
            </div>

            {{-- Column 3: Customer Care & Policies (Pure Bengali, Clean Links) --}}
            <div class="col-lg-2 col-md-3 col-6">
                <h6 class="footer-heading text-white fw-bold mb-3 position-relative pb-2">
                    <span>নীতিমালা ও সহায়তা</span>
                </h6>
                <ul class="list-unstyled mb-0 footer-links">
                    <li><a href="{{ route('terms') }}">ব্যবহারের শর্তাবলী</a></li>
                    <li><a href="{{ route('privacy') }}">গোপনীয়তা নীতি</a></li>
                    <li><a href="{{ route('contact') }}">যোগাযোগ ও সহায়তা</a></li>
                    <li><a href="{{ route('refund.policy') }}">রিটার্ন ও মূল্যফেরত নীতি</a></li>
                    <li><a href="{{ route('faq') }}">সাধারণ জিজ্ঞাসা ও উত্তর</a></li>
                    <li><a href="{{ route('blog.write') }}">লেখা পাঠান ও সম্মাননা</a></li>
                </ul>
            </div>

            {{-- Column 4: Editorial Board --}}
            <div class="col-lg-2 col-md-6 col-6">
                <h6 class="footer-heading text-white fw-bold mb-3 position-relative pb-2">
                    <span>আইডিয়াপত্র ও সম্পাদনা</span>
                </h6>
                <div class="small text-slate-300" style="font-size: 13px; line-height: 1.8; color: #cbd5e1;">
                    <div class="mb-1.5">
                        <span class="text-white-50 d-block" style="font-size: 11px;">প্রকাশক:</span>
                        <strong class="text-white">{{ \App\Support\SiteSetting::publisherName() ?: 'আইডিয়া প্রকাশন' }}</strong>
                    </div>
                    <div class="mb-1.5">
                        <span class="text-white-50 d-block" style="font-size: 11px;">সম্পাদক:</span>
                        <a href="{{ route('authors.show', 'sakil-masud') }}" class="text-info text-decoration-none fw-semibold" style="transition: color 0.2s ease;" onmouseover="this.style.color='#38bdf8'; this.style.textDecoration='underline';" onmouseout="this.style.color=''; this.style.textDecoration='none';" title="সাকিল মাসুদ — প্রোফাইল দেখুন">
                            {{ \App\Support\SiteSetting::editorName() ?: 'সাকিল মাসুদ' }}
                        </a>
                    </div>
                    @foreach(\App\Support\SiteSetting::editorialBoard() as $boardMember)
                        @if(!empty($boardMember['role']) && !empty($boardMember['name']))
                            <div class="mb-1.5">
                                <span class="text-white-50 d-block" style="font-size: 11px;">{{ $boardMember['role'] }}:</span>
                                <strong class="text-white">{{ $boardMember['name'] }}</strong>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- Column 5: Contact Info & Newsletter --}}
            <div class="col-lg-3 col-md-6">
                <h6 class="footer-heading text-white fw-bold mb-3 position-relative pb-2">
                    <span>যোগাযোগ ও বার্তা</span>
                </h6>
                <div class="mb-3" style="font-size: 13px; color: #cbd5e1;">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="footer-contact-icon text-info"><i class="fa-solid fa-phone-volume"></i></span>
                        <a href="tel:{{ $helplinePhone }}" class="text-white text-decoration-none fw-bold hover-info">{{ $helplinePhone }}</a>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="footer-contact-icon text-success"><i class="fa-brands fa-whatsapp"></i></span>
                        <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="text-white text-decoration-none fw-bold hover-success">{{ $helplinePhone }} (হোয়াটসঅ্যাপ)</a>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="footer-contact-icon text-primary"><i class="fa-solid fa-envelope"></i></span>
                        <a href="mailto:{{ $helplineEmail }}" class="text-white-50 text-decoration-none hover-white">{{ $helplineEmail }}</a>
                    </div>
                </div>

                {{-- Newsletter Box (Crisp Geometry, Sharp Borders) --}}
                <div class="footer-newsletter-box">
                    <label class="small text-white fw-semibold mb-1.5 d-block">নতুন বই ও অফারের আপডেট পেতে:</label>
                    <form action="{{ route('newsletter.subscribe') }}" method="POST">
                        @csrf <x-bot-trap />
                        <div class="input-group input-group-sm">
                            <input type="email" name="email" class="form-control footer-email-input text-white" placeholder="আপনার ইমেইল ঠিকানা..." required>
                            <button class="btn btn-primary footer-email-btn" type="submit" title="যুক্ত হোন">
                                <i class="fa-solid fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>

        {{-- Payment Gateways Strip (Dynamic & Pure Bengali) --}}
        <div class="mt-4 pt-3 border-top" style="border-color: rgba(255,255,255,0.08) !important;">
            <div class="row align-items-center g-3">
                <div class="col-lg-3 text-center text-lg-start">
                    <span class="small fw-bold text-white d-inline-flex align-items-center gap-1.5" style="font-size: 12.5px;">
                        <i class="fa-solid fa-wallet text-amber"></i> গ্রহণযোগ্য পেমেন্ট মাধ্যম:
                    </span>
                </div>
                <div class="col-lg-9">
                    <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-end gap-2">
                        @foreach(\App\Support\SiteSetting::footerPaymentMethods() as $pay)
                            <div class="payment-badge {{ $pay['badge_class'] ?? '' }} shadow-xs" title="{{ $pay['name'] }}">
                                @if(!empty($pay['dot_color']))
                                    <span class="dot" style="background: {{ $pay['dot_color'] }};"></span>
                                @endif
                                @if(!empty($pay['icon']))
                                    <i class="{{ $pay['icon'] }}" style="font-size: 15px; color: {{ $pay['icon_color'] ?? '#ffffff' }}; margin-right: 4px;"></i>
                                @endif
                                <span class="badge-text {{ ($pay['type'] ?? '') === 'card' ? 'text-dark fw-bold' : '' }}">{{ $pay['name'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Copyright Bar (Crisp Spacing & Pure Bengali) --}}
    <div class="py-2.5" style="background: #020710; border-top: 1px solid rgba(255,255,255,0.06);">
        <div class="container">
            <div class="row align-items-center g-2 text-center text-lg-start">
                <div class="col-lg-7">
                    <p class="mb-0 small" style="color: #94a3b8; font-size: 12.5px; line-height: 1.6;">
                        &copy; ২০২৬ {{ $footerName }} । ডিজিটাল বুক ও সৃজনশীল প্রকাশনা প্ল্যাটফর্ম । সর্বস্বত্ব সংরক্ষিত। 
                        @if(\App\Support\SiteSetting::showDesignerCredit())
                            <span class="d-inline-block ms-1" style="color: #64748b;">কারিগরি ও নকশায়: 
                                <a href="{{ \App\Support\SiteSetting::designerUrl() }}" class="text-info text-decoration-none fw-semibold" style="transition: color 0.2s ease;" onmouseover="this.style.color='#38bdf8'; this.style.textDecoration='underline';" onmouseout="this.style.color=''; this.style.textDecoration='none';" title="{{ \App\Support\SiteSetting::designerName() }} — লেখক প্রোফাইল দেখুন">{{ \App\Support\SiteSetting::designerName() }}</a>
                            </span>
                        @endif
                    </p>
                </div>
                <div class="col-lg-5 text-lg-end">
                    <div class="small d-inline-flex flex-wrap align-items-center justify-content-center justify-content-lg-end gap-1" style="font-size: 12px; color: #94a3b8;">
                        <a href="{{ route('terms') }}" class="text-secondary text-decoration-none hover-white">ব্যবহারের শর্তাবলী</a>
                        <span class="text-white-50 mx-1">•</span>
                        <a href="{{ route('privacy') }}" class="text-secondary text-decoration-none hover-white">গোপনীয়তা নীতি</a>
                        <span class="text-white-50 mx-1">•</span>
                        <a href="{{ route('contact') }}" class="text-secondary text-decoration-none hover-white">যোগাযোগ ও সহায়তা</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
.site-footer {
    font-family: 'Kalpurush', 'Nikosh', 'SolaimanLipi', 'Hind Siliguri', 'Segoe UI', system-ui, -apple-system, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

/* Sharp Color Tokens for Icons */
.text-sky { color: #38bdf8 !important; }
.text-emerald { color: #34d399 !important; }
.text-amber { color: #fbbf24 !important; }

/* Sharp Trust Features Icon (No Excessive Circle Radius) */
.footer-trust-icon {
    width: 38px;
    height: 38px;
    border-radius: 4px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
    transition: all 0.2s ease;
}

.footer-trust-icon:hover {
    background: rgba(255, 255, 255, 0.09);
    border-color: rgba(255, 255, 255, 0.2);
}

.footer-feature-item {
    transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
}
.footer-feature-item.hover-feature:hover {
    background: rgba(255, 255, 255, 0.05);
    transform: translateY(-2px);
}
.footer-feature-item.hover-feature:hover .footer-trust-icon {
    transform: scale(1.08);
}

.footer-brand-box {
    width: 38px;
    height: 38px;
    font-size: 19px;
    border-radius: 4px;
}

/* Sharp Section Heading with Crisp Line */
.footer-heading {
    font-size: 0.95rem;
    letter-spacing: 0.2px;
}

.footer-heading::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 28px;
    height: 2px;
    background: #0284c7;
    border-radius: 0;
}

/* Footer Links & Sharp Chevron */
.footer-links li {
    margin-bottom: 7.5px;
}

.footer-links a {
    color: #94a3b8;
    text-decoration: none;
    font-size: 13.5px;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
}

.footer-links a:hover {
    color: #38bdf8;
    transform: translateX(2.5px);
}

.footer-link-icon {
    font-size: 9px;
    color: #38bdf8;
    margin-right: 8px;
    transition: transform 0.2s ease;
    flex-shrink: 0;
}

.footer-links a:hover .footer-link-icon {
    transform: translateX(2px);
    color: #7dd3fc;
}

/* Sharp Social Buttons (No 50% circle radius) */
.footer-social-btn {
    width: 33px;
    height: 33px;
    border-radius: 4px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.12);
    color: #cbd5e1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.2s ease;
    font-size: 13.5px;
}

.footer-social-btn:hover {
    background: var(--social-hover-color, #0284c7) !important;
    border-color: var(--social-hover-color, #38bdf8) !important;
    color: #ffffff !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35);
}

/* Contact Icons */
.footer-contact-icon {
    width: 18px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    flex-shrink: 0;
}

/* Sharp Newsletter Box & Inputs */
.footer-newsletter-box {
    padding: 12px;
    border-radius: 4px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.09);
}

.footer-email-input {
    background: #06111f !important;
    border: 1px solid rgba(255, 255, 255, 0.15) !important;
    border-radius: 4px 0 0 4px !important;
    font-size: 12px;
}

.footer-email-input:focus {
    border-color: #0284c7 !important;
    box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.25) !important;
}

.footer-email-btn {
    border-radius: 0 4px 4px 0 !important;
    background: #0284c7 !important;
    border: 1px solid #0284c7 !important;
    padding: 0 13px;
    font-size: 12px;
    transition: all 0.2s ease;
}

.footer-email-btn:hover {
    background: #0369a1 !important;
    border-color: #0369a1 !important;
}

/* Payment Gateway Badges (Sharp Rectangular Badges, 4px Radius) */
.payment-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4.5px 10px;
    border-radius: 4px;
    font-size: 11.5px;
    font-weight: 700;
    color: #ffffff;
    letter-spacing: 0.2px;
    border: 1px solid rgba(255, 255, 255, 0.15);
    transition: transform 0.2s ease;
}

.payment-badge:hover {
    transform: translateY(-1.5px);
}

.payment-badge.bkash {
    background: linear-gradient(135deg, #e2136e 0%, #b80c57 100%);
    border-color: #ff4d94;
}

.payment-badge.nagad {
    background: linear-gradient(135deg, #f7941d 0%, #d85a08 100%);
    border-color: #ffaa4d;
}

.payment-badge.rocket {
    background: linear-gradient(135deg, #8c3494 0%, #611768 100%);
    border-color: #b85ec1;
}

.payment-badge.upay {
    background: linear-gradient(135deg, #005baa 0%, #003666 100%);
    border-color: #3388dd;
}

.payment-badge.card-pill {
    background: #ffffff;
    color: #1e293b;
    border: 1px solid #cbd5e1;
}

.payment-badge.cod {
    background: rgba(34, 197, 94, 0.12);
    color: #4ade80;
    border: 1px solid rgba(74, 222, 128, 0.35);
}

.payment-badge .dot {
    width: 5px;
    height: 5px;
    border-radius: 2px;
    background: #ffffff;
    display: inline-block;
}
</style>
