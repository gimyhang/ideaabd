@php
    $footerLogo = \App\Support\SiteSetting::logoUrl();
    $footerName = \App\Support\SiteSetting::name() ?: 'আইডিয়া প্রকাশন';
    $footerTagline = \App\Support\SiteSetting::tagline();
    $helplinePhone = \App\Support\SiteSetting::helplinePhone() ?: '+8801558712810';
    $helplineEmail = \App\Support\SiteSetting::helplineEmail() ?: 'support@ideaabd.com';
    $cleanPhone = preg_replace('/[^0-9]/', '', $helplinePhone);
@endphp

<footer class="site-footer text-white position-relative" style="background: linear-gradient(180deg, #0a192f 0%, #030b17 100%); border-top: 3px solid #0284c7;">
    {{-- Trust Features Bar --}}
    <div class="py-3 border-bottom" style="border-color: rgba(255,255,255,0.08) !important; background: rgba(255,255,255,0.02);">
        <div class="container">
            <div class="row g-3 text-center text-md-start">
                <div class="col-6 col-md-3 d-flex align-items-center justify-content-center justify-content-md-start gap-2.5">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-20 text-info" style="width: 40px; height: 40px;">
                        <i class="fa-solid fa-truck-fast fs-5"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-white small">সারাদেশে হোম ডেলিভারি</div>
                        <div class="text-white-50" style="font-size: 11px;">দ্রুত ও নির্ভরযোগ্য শিপিং</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 d-flex align-items-center justify-content-center justify-content-md-start gap-2.5">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-success bg-opacity-20 text-success" style="width: 40px; height: 40px;">
                        <i class="fa-solid fa-shield-halved fs-5"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-white small">১০০% নিরাপদ পেমেন্ট</div>
                        <div class="text-white-50" style="font-size: 11px;">SSL এনক্রিপ্টেড গেটওয়ে</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 d-flex align-items-center justify-content-center justify-content-md-start gap-2.5">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-warning bg-opacity-20 text-warning" style="width: 40px; height: 40px;">
                        <i class="fa-solid fa-book-bookmark fs-5"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-white small">অরিজিনাল প্রকাশনা</div>
                        <div class="text-white-50" style="font-size: 11px;">প্রকৃত বই ও মুক্তচিন্তা</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 d-flex align-items-center justify-content-center justify-content-md-start gap-2.5">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-info bg-opacity-20 text-cyan-400" style="width: 40px; height: 40px;">
                        <i class="fa-brands fa-whatsapp fs-4 text-success"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-white small">তাৎক্ষণিক সহায়তা</div>
                        <div class="text-white-50" style="font-size: 11px;">+8801558712810</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Footer Body --}}
    <div class="container py-5">
        <div class="row g-4 justify-content-between">

            {{-- Column 1: Brand & About --}}
            <div class="col-lg-3 col-md-6">
                <div class="d-flex align-items-center gap-2.5 mb-3">
                    @if($footerLogo)
                        <img src="{{ $footerLogo }}" alt="{{ $footerName }}" style="max-height: 42px; width: auto; object-fit: contain; filter: brightness(1.1);">
                    @else
                        <div class="rounded-3 bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-xs" style="width: 40px; height: 40px; font-size: 20px;">
                            আই
                        </div>
                    @endif
                    <span class="fw-bold fs-4 text-white letter-spacing-wide">{{ $footerName }}</span>
                </div>
                <p class="text-slate-300 small mb-3" style="color: #cbd5e1; line-height: 1.8; font-size: 13.5px;">
                    {{ $footerTagline ?: 'বাংলাদেশের বিশ্বস্ত অনলাইন বই, ই-বুক ও সৃজনশীল প্রকাশনা প্ল্যাটফর্ম। লেখক, প্রকাশক ও পাঠকদের মুক্ত চিন্তার মিলনমেলা।' }}
                </p>
                <div class="d-flex align-items-center gap-2 pt-1">
                    <a href="https://facebook.com" target="_blank" rel="noopener" class="footer-social-btn" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://wa.me/{{ $cleanPhone ?: '8801558712810' }}" target="_blank" rel="noopener" class="footer-social-btn whatsapp" title="WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    <a href="https://youtube.com" target="_blank" rel="noopener" class="footer-social-btn youtube" title="YouTube">
                        <i class="fab fa-youtube"></i>
                    </a>
                    <a href="https://instagram.com" target="_blank" rel="noopener" class="footer-social-btn instagram" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                </div>
            </div>

            {{-- Column 2: Quick Links --}}
            <div class="col-lg-2 col-md-3 col-6">
                <h6 class="footer-heading text-white fw-bold mb-3 position-relative pb-2">
                    <span>ক্যাটালগ ও মেনু</span>
                </h6>
                <ul class="list-unstyled mb-0 footer-links">
                    <li><a href="{{ route('book.index') }}"><i class="fa-solid fa-angle-right me-1.5 opacity-50"></i>বই ক্যাটালগ</a></li>
                    <li><a href="{{ route('ebook.index') }}"><i class="fa-solid fa-angle-right me-1.5 opacity-50"></i>ই-বুক সমাহার</a></li>
                    <li><a href="{{ route('webzine.index') }}"><i class="fa-solid fa-angle-right me-1.5 opacity-50"></i>ওয়েবজিন</a></li>
                    <li><a href="{{ route('authors.index') }}"><i class="fa-solid fa-angle-right me-1.5 opacity-50"></i>লেখক তালিকা</a></li>
                    <li><a href="{{ route('publishers.index') }}"><i class="fa-solid fa-angle-right me-1.5 opacity-50"></i>প্রকাশকবৃন্দ</a></li>
                    <li><a href="{{ route('blog.index') }}"><i class="fa-solid fa-angle-right me-1.5 opacity-50"></i>আইডিয়াপত্র ব্লগ</a></li>
                </ul>
            </div>

            {{-- Column 3: Customer Care & Policies --}}
            <div class="col-lg-2 col-md-3 col-6">
                <h6 class="footer-heading text-white fw-bold mb-3 position-relative pb-2">
                    <span>নীতিমালা ও সহায়তা</span>
                </h6>
                <ul class="list-unstyled mb-0 footer-links">
                    <li><a href="{{ route('contact') }}"><i class="fa-solid fa-angle-right me-1.5 opacity-50"></i>হেল্পলাইন ও যোগাযোগ</a></li>
                    <li><a href="{{ route('blog.write') }}"><i class="fa-solid fa-angle-right me-1.5 opacity-50"></i>লেখা সাবমিট করুন</a></li>
                    <li><a href="{{ route('register.choose') }}"><i class="fa-solid fa-angle-right me-1.5 opacity-50"></i>লেখক রেজিস্ট্রেশন</a></li>
                    <li><a href="{{ route('login') }}"><i class="fa-solid fa-angle-right me-1.5 opacity-50"></i>অ্যাকাউন্ট লগইন</a></li>
                    <li><a href="{{ route('contact') }}"><i class="fa-solid fa-angle-right me-1.5 opacity-50"></i>আমাদের ঠিকানা</a></li>
                </ul>
            </div>

            {{-- Column 4: Editorial & Publishing Board (৫ম কলাম / সম্পাদকমণ্ডলী) --}}
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
                        <i class="fa-solid fa-phone-volume text-info"></i>
                        <a href="tel:{{ $helplinePhone }}" class="text-white text-decoration-none fw-bold hover-info">{{ $helplinePhone }}</a>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fa-brands fa-whatsapp text-success fs-6"></i>
                        <a href="https://wa.me/{{ $cleanPhone ?: '8801558712810' }}" target="_blank" class="text-white text-decoration-none fw-bold hover-success">+8801558712810 (WhatsApp)</a>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fa-solid fa-envelope text-primary"></i>
                        <a href="mailto:{{ $helplineEmail }}" class="text-white-50 text-decoration-none hover-white">{{ $helplineEmail }}</a>
                    </div>
                </div>

                {{-- Newsletter Box --}}
                <div class="p-2.5 rounded-3 border" style="background: rgba(255,255,255,0.04); border-color: rgba(255,255,255,0.1) !important;">
                    <label class="small text-white fw-semibold mb-1.5 d-block">নতুন বই ও অফারের আপডেট পান:</label>
                    <form action="{{ route('newsletter.subscribe') }}" method="POST">
                        @csrf
                        <div class="input-group input-group-sm">
                            <input type="email" name="email" class="form-control bg-dark text-white border-secondary border-opacity-50" placeholder="আপনার ইমেইল..." required style="font-size: 12px;">
                            <button class="btn btn-primary px-3 fw-bold" type="submit" style="background: #0284c7; border: none;">
                                <i class="fa-solid fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>

        {{-- Dynamic High-Contrast Payment Gateways Strip --}}
        <div class="mt-4 pt-4 border-top" style="border-color: rgba(255,255,255,0.08) !important;">
            <div class="row align-items-center g-3">
                <div class="col-lg-3 text-center text-lg-start">
                    <span class="small fw-bold text-white text-uppercase tracking-wider d-inline-flex align-items-center gap-1.5">
                        <i class="fa-solid fa-wallet text-warning"></i> গ্রহণযোগ্য পেমেন্ট মাধ্যম:
                    </span>
                </div>
                <div class="col-lg-9">
                    <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-end gap-2">
                        {{-- bKash --}}
                        <div class="payment-badge bkash shadow-xs" title="bKash Payment">
                            <span class="dot"></span>
                            <span class="badge-text">bKash</span>
                        </div>
                        {{-- Nagad --}}
                        <div class="payment-badge nagad shadow-xs" title="Nagad Payment">
                            <span class="dot"></span>
                            <span class="badge-text">Nagad</span>
                        </div>
                        {{-- Rocket --}}
                        <div class="payment-badge rocket shadow-xs" title="Rocket Payment">
                            <span class="dot"></span>
                            <span class="badge-text">Rocket</span>
                        </div>
                        {{-- Upay --}}
                        <div class="payment-badge upay shadow-xs" title="Upay Payment">
                            <span class="dot"></span>
                            <span class="badge-text">Upay</span>
                        </div>
                        {{-- Visa Card --}}
                        <div class="payment-badge card-pill visa shadow-xs" title="Visa Card Accepted">
                            <i class="fa-brands fa-cc-visa text-primary fs-5"></i>
                            <span class="badge-text text-dark fw-bold">VISA</span>
                        </div>
                        {{-- Mastercard --}}
                        <div class="payment-badge card-pill mastercard shadow-xs" title="MasterCard Accepted">
                            <i class="fa-brands fa-cc-mastercard text-danger fs-5"></i>
                            <span class="badge-text text-dark fw-bold">Mastercard</span>
                        </div>
                        {{-- Cash on Delivery --}}
                        <div class="payment-badge cod shadow-xs" title="Cash on Delivery Available">
                            <i class="fa-solid fa-hand-holding-dollar text-success me-1"></i>
                            <span class="badge-text">ক্যাশ অন ডেলিভারি</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Copyright Bar --}}
    <div class="py-3" style="background: #020710; border-top: 1px solid rgba(255,255,255,0.06);">
        <div class="container">
            <div class="row align-items-center g-2 text-center text-md-start">
                <div class="col-md-7">
                    <p class="mb-0 small" style="color: #94a3b8; font-size: 12.5px;">
                        &copy; {{ date('Y') }} <strong class="text-white">{{ $footerName }}</strong> (ideaabd.com) &mdash; সর্বস্বত্ব সংরক্ষিত। 
                        <span class="d-inline-block ms-1" style="color: #64748b;">| ডিজাইনার: 
                            <a href="{{ route('authors.show', 'sakil-masud') }}" class="text-info text-decoration-none fw-semibold" style="transition: color 0.2s ease;" onmouseover="this.style.color='#38bdf8'; this.style.textDecoration='underline';" onmouseout="this.style.color=''; this.style.textDecoration='none';" title="মাসুদ রানা সাকিল — প্রোফাইল দেখুন">মাসুদ রানা সাকিল</a>
                        </span>
                    </p>
                </div>
                <div class="col-md-5 text-md-end">
                    <span class="small" style="color: #64748b; font-size: 12px;">
                        <i class="fa-solid fa-shield-check text-success me-1"></i> সুরক্ষিত ও নিরাপদ ডিজিটাল প্রকাশনা প্ল্যাটফর্ম
                    </span>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
.site-footer {
    font-family: 'Hind Siliguri', 'Segoe UI', system-ui, -apple-system, sans-serif;
}
.footer-heading {
    font-size: 0.95rem;
    letter-spacing: 0.3px;
}
.footer-heading::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 32px;
    height: 2px;
    background: #0284c7;
    border-radius: 2px;
}
.footer-links li {
    margin-bottom: 8px;
}
.footer-links a {
    color: #94a3b8;
    text-decoration: none;
    font-size: 13.5px;
    transition: all 0.2s ease;
    display: inline-block;
}
.footer-links a:hover {
    color: #38bdf8;
    transform: translateX(3px);
}
.footer-social-btn {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: rgba(255,255,255,0.08);
    color: #cbd5e1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.25s ease;
    font-size: 14px;
}
.footer-social-btn:hover {
    background: #0284c7;
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(2,132,199,0.35);
}
.footer-social-btn.whatsapp:hover {
    background: #25D366;
    box-shadow: 0 4px 10px rgba(37,211,102,0.35);
}
.footer-social-btn.youtube:hover {
    background: #FF0000;
    box-shadow: 0 4px 10px rgba(255,0,0,0.35);
}
.footer-social-btn.instagram:hover {
    background: #E1306C;
    box-shadow: 0 4px 10px rgba(225,48,108,0.35);
}

/* Payment Gateway Badges (High Contrast & Distinct Branding) */
.payment-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    color: #ffffff;
    letter-spacing: 0.3px;
    border: 1px solid rgba(255,255,255,0.15);
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
    border: 1px solid #e2e8f0;
}
.payment-badge.cod {
    background: rgba(34, 197, 94, 0.15);
    color: #4ade80;
    border: 1px solid rgba(74, 222, 128, 0.35);
}
.payment-badge .dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #ffffff;
    display: inline-block;
}
</style>
