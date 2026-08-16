<footer style="background:#1a3a52; color:#cde4f0;">
    <div class="container py-5">
        <div class="row g-4">

            {{-- Brand --}}
            <div class="col-lg-3 col-md-6">
                @php
                    $footerLogo = \App\Support\SiteSetting::logoUrl();
                    $footerName = \App\Support\SiteSetting::name();
                    $footerTagline = \App\Support\SiteSetting::tagline();
                    $helplinePhone = \App\Support\SiteSetting::helplinePhone();
                    $helplineEmail = \App\Support\SiteSetting::helplineEmail();
                @endphp
                <div class="d-flex align-items-center gap-2 mb-3">
                    @if($footerLogo)
                        <img src="{{ $footerLogo }}" alt="{{ $footerName }}" style="max-height: 38px; width: auto; object-fit: contain;">
                    @else
                        <span class="badge bg-primary text-white p-2 rounded fs-5">আই</span>
                    @endif
                    <span class="fw-bold fs-5 text-white">{{ $footerName }}</span>
                </div>
                <p style="color:#a8c8dc; font-size:.88rem; line-height:1.8;">
                    {{ $footerTagline ?: 'বাংলাদেশের বিশ্বস্ত অনলাইন বই ও মুক্তচিন্তার প্রকাশনা। লেখক, প্রকাশক ও পাঠকদের মিলনমেলা।' }}
                </p>
                <div class="d-flex gap-3 mt-3">
                    <a href="https://facebook.com" target="_blank" rel="noopener" style="color:#66ccff;"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://instagram.com" target="_blank" rel="noopener" style="color:#66ccff;"><i class="fab fa-instagram"></i></a>
                    <a href="https://youtube.com" target="_blank" rel="noopener" style="color:#66ccff;"><i class="fab fa-youtube"></i></a>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $helplinePhone) }}" target="_blank" rel="noopener" style="color:#25D366;"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>

            {{-- Quick links --}}
            <div class="col-lg-2 col-md-3 col-6">
                <h6 class="fw-bold text-white mb-3" style="font-size:.9rem; letter-spacing:.04em;">দ্রুত লিঙ্ক</h6>
                <ul class="list-unstyled mb-0" style="font-size:.86rem;">
                    <li class="mb-2"><a href="{{ route('book.index') }}" style="color:#a8c8dc; text-decoration:none;" onmouseover="this.style.color='#66ccff'" onmouseout="this.style.color='#a8c8dc'">বই ক্যাটালগ</a></li>
                    <li class="mb-2"><a href="{{ route('ebook.index') }}" style="color:#a8c8dc; text-decoration:none;" onmouseover="this.style.color='#66ccff'" onmouseout="this.style.color='#a8c8dc'">ই-বুক</a></li>
                    <li class="mb-2"><a href="{{ route('webzine.index') }}" style="color:#a8c8dc; text-decoration:none;" onmouseover="this.style.color='#66ccff'" onmouseout="this.style.color='#a8c8dc'">ওয়েবজিন</a></li>
                    <li class="mb-2"><a href="{{ route('authors.index') }}" style="color:#a8c8dc; text-decoration:none;" onmouseover="this.style.color='#66ccff'" onmouseout="this.style.color='#a8c8dc'">লেখকবৃন্দ</a></li>
                    <li class="mb-2"><a href="{{ route('publishers.index') }}" style="color:#a8c8dc; text-decoration:none;" onmouseover="this.style.color='#66ccff'" onmouseout="this.style.color='#a8c8dc'">প্রকাশকগণ</a></li>
                    <li><a href="{{ route('blog.index') }}" style="color:#a8c8dc; text-decoration:none;" onmouseover="this.style.color='#66ccff'" onmouseout="this.style.color='#a8c8dc'">ব্লগ ও সাহিত্য</a></li>
                </ul>
            </div>

            {{-- Help --}}
            <div class="col-lg-2 col-md-3 col-6">
                <h6 class="fw-bold text-white mb-3" style="font-size:.9rem; letter-spacing:.04em;">সহায়তা ও নীতি</h6>
                <ul class="list-unstyled mb-0" style="font-size:.86rem;">
                    <li class="mb-2"><a href="{{ route('contact') }}" style="color:#a8c8dc; text-decoration:none;" onmouseover="this.style.color='#66ccff'" onmouseout="this.style.color='#a8c8dc'">যোগাযোগ ও হেল্পলাইন</a></li>
                    <li class="mb-2"><a href="#" style="color:#a8c8dc; text-decoration:none;" onmouseover="this.style.color='#66ccff'" onmouseout="this.style.color='#a8c8dc'">ডেলিভারি ও শিপিং নীতি</a></li>
                    <li class="mb-2"><a href="#" style="color:#a8c8dc; text-decoration:none;" onmouseover="this.style.color='#66ccff'" onmouseout="this.style.color='#a8c8dc'">রিটার্ন ও রিফান্ড নীতি</a></li>
                    <li class="mb-2"><a href="#" style="color:#a8c8dc; text-decoration:none;" onmouseover="this.style.color='#66ccff'" onmouseout="this.style.color='#a8c8dc'">শর্তাবলী</a></li>
                    <li><a href="{{ route('contact') }}" style="color:#a8c8dc; text-decoration:none;" onmouseover="this.style.color='#66ccff'" onmouseout="this.style.color='#a8c8dc'">আমাদের ঠিকানা</a></li>
                </ul>
            </div>

            {{-- Contact --}}
            <div class="col-lg-2 col-md-6">
                <h6 class="fw-bold text-white mb-3" style="font-size:.9rem; letter-spacing:.04em;">যোগাযোগ</h6>
                <ul class="list-unstyled mb-0" style="font-size:.86rem;">
                    <li class="mb-2 d-flex align-items-start gap-2">
                        <i class="fas fa-phone mt-1" style="color:#66ccff; width:14px;"></i>
                        <a href="tel:{{ $helplinePhone }}" style="color:#a8c8dc; text-decoration:none;">{{ $helplinePhone }}</a>
                    </li>
                    <li class="mb-2 d-flex align-items-start gap-2">
                        <i class="fas fa-envelope mt-1" style="color:#66ccff; width:14px;"></i>
                        <a href="mailto:{{ $helplineEmail }}" style="color:#a8c8dc; text-decoration:none;">{{ $helplineEmail }}</a>
                    </li>
                    <li class="d-flex align-items-start gap-2">
                        <i class="fas fa-map-marker-alt mt-1" style="color:#66ccff; width:14px;"></i>
                        <span style="color:#a8c8dc;">সেন্ট্রাল রোড, রংপুর ও ঢাকা, বাংলাদেশ</span>
                    </li>
                </ul>
            </div>

            {{-- Newsletter --}}
            <div class="col-lg-3 col-md-6">
                <h6 class="fw-bold text-white mb-3" style="font-size:.9rem; letter-spacing:.04em;">নিউজলেটার</h6>
                <p style="color:#a8c8dc; font-size:.86rem; line-height:1.6;">সর্বশেষ বই ও অফারের আপডেট পেতে সাবস্ক্রাইব করুন।</p>
                <form action="{{ route('newsletter.subscribe') }}" method="POST" class="mt-2">
                    @csrf
                    <div class="input-group">
                        <input type="email" name="email" class="form-control border-0"
                            placeholder="আপনার ইমেইল..."
                            style="background:#243e52; color:#e0f0f8; border-radius:8px 0 0 8px; font-size:.85rem;">
                        <button class="btn px-3" style="background:#0099ff; border:none; border-radius:0 8px 8px 0; color:#fff;">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    {{-- Bottom bar --}}
    <div style="background:#0f2535; border-top:1px solid #243e52;">
        <div class="container py-3">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <p class="mb-0" style="color:#7aadcc; font-size:.83rem;">
                        &copy; {{ date('Y') }} <strong class="text-white">ideaabd</strong>
                        &mdash; সর্বস্বত্ব সংরক্ষিত &mdash;
                        নির্মিত <i class="fas fa-heart" style="color:#e05c6a;"></i> দ্বারা
                        <strong style="color:#66ccff;">Masud Rana Shakil</strong>
                    </p>
                </div>
                <div class="col-md-5 text-md-end mt-2 mt-md-0">
                    <span style="color:#7aadcc; font-size:.83rem;">
                        পেমেন্ট:
                        <span class="ms-2 fw-bold" style="color:#e91e8c;">bKash</span>
                        <span class="ms-2 fw-bold" style="color:#f26522;">Nagad</span>
                        <i class="fab fa-cc-visa fs-5 ms-2" style="color:#1a1f71;"></i>
                        <i class="fab fa-cc-mastercard fs-5 ms-1" style="color:#eb001b;"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
</footer>
