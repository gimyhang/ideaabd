<footer style="background:#1a3a52; color:#cde4f0;">
    <div class="container py-5">
        <div class="row g-4">

            {{-- Brand --}}
            <div class="col-lg-3 col-md-6">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <img src="{{ asset('images/logo.svg') }}" alt="ideaabd" width="36" height="36">
                    <span class="fw-bold fs-5 text-white">ideaabd</span>
                </div>
                <p style="color:#a8c8dc; font-size:.88rem; line-height:1.8;">
                    বাংলাদেশের বিশ্বস্ত অনলাইন বই মার্কেটপ্লেস। লেখক, প্রকাশক ও পাঠকদের একই ছাদের নিচে।
                </p>
                <div class="d-flex gap-3 mt-3">
                    <a href="#" style="color:#66ccff;"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" style="color:#66ccff;"><i class="fab fa-twitter"></i></a>
                    <a href="#" style="color:#66ccff;"><i class="fab fa-instagram"></i></a>
                    <a href="#" style="color:#66ccff;"><i class="fab fa-youtube"></i></a>
                </div>
            </div>

            {{-- Quick links --}}
            <div class="col-lg-2 col-md-3 col-6">
                <h6 class="fw-bold text-white mb-3" style="font-size:.9rem; letter-spacing:.04em;">দ্রুত লিঙ্ক</h6>
                <ul class="list-unstyled mb-0" style="font-size:.86rem;">
                    <li class="mb-2"><a href="{{ route('book.index') }}" style="color:#a8c8dc; text-decoration:none;" onmouseover="this.style.color='#66ccff'" onmouseout="this.style.color='#a8c8dc'">বই</a></li>
                    <li class="mb-2"><a href="{{ route('ebook.index') }}" style="color:#a8c8dc; text-decoration:none;" onmouseover="this.style.color='#66ccff'" onmouseout="this.style.color='#a8c8dc'">ই-বুক</a></li>
                    <li class="mb-2"><a href="{{ route('webzine.index') }}" style="color:#a8c8dc; text-decoration:none;" onmouseover="this.style.color='#66ccff'" onmouseout="this.style.color='#a8c8dc'">ওয়েবজিন</a></li>
                    <li class="mb-2"><a href="{{ route('authors.index') }}" style="color:#a8c8dc; text-decoration:none;" onmouseover="this.style.color='#66ccff'" onmouseout="this.style.color='#a8c8dc'">লেখকগণ</a></li>
                    <li class="mb-2"><a href="{{ route('publishers.index') }}" style="color:#a8c8dc; text-decoration:none;" onmouseover="this.style.color='#66ccff'" onmouseout="this.style.color='#a8c8dc'">প্রকাশকগণ</a></li>
                    <li><a href="{{ route('blog.index') }}" style="color:#a8c8dc; text-decoration:none;" onmouseover="this.style.color='#66ccff'" onmouseout="this.style.color='#a8c8dc'">ব্লগ</a></li>
                </ul>
            </div>

            {{-- Help --}}
            <div class="col-lg-2 col-md-3 col-6">
                <h6 class="fw-bold text-white mb-3" style="font-size:.9rem; letter-spacing:.04em;">সহায়তা</h6>
                <ul class="list-unstyled mb-0" style="font-size:.86rem;">
                    <li class="mb-2"><a href="#" style="color:#a8c8dc; text-decoration:none;" onmouseover="this.style.color='#66ccff'" onmouseout="this.style.color='#a8c8dc'">এফএকিউ</a></li>
                    <li class="mb-2"><a href="#" style="color:#a8c8dc; text-decoration:none;" onmouseover="this.style.color='#66ccff'" onmouseout="this.style.color='#a8c8dc'">শিপিং নীতি</a></li>
                    <li class="mb-2"><a href="#" style="color:#a8c8dc; text-decoration:none;" onmouseover="this.style.color='#66ccff'" onmouseout="this.style.color='#a8c8dc'">রিটার্ন পলিসি</a></li>
                    <li class="mb-2"><a href="#" style="color:#a8c8dc; text-decoration:none;" onmouseover="this.style.color='#66ccff'" onmouseout="this.style.color='#a8c8dc'">গোপনীয়তা নীতি</a></li>
                    <li class="mb-2"><a href="#" style="color:#a8c8dc; text-decoration:none;" onmouseover="this.style.color='#66ccff'" onmouseout="this.style.color='#a8c8dc'">শর্তাবলী</a></li>
                    <li><a href="{{ route('contact') }}" style="color:#a8c8dc; text-decoration:none;" onmouseover="this.style.color='#66ccff'" onmouseout="this.style.color='#a8c8dc'">যোগাযোগ</a></li>
                </ul>
            </div>

            {{-- Contact --}}
            <div class="col-lg-2 col-md-6">
                <h6 class="fw-bold text-white mb-3" style="font-size:.9rem; letter-spacing:.04em;">যোগাযোগ</h6>
                <ul class="list-unstyled mb-0" style="font-size:.86rem;">
                    <li class="mb-2 d-flex align-items-start gap-2">
                        <i class="fas fa-phone mt-1" style="color:#66ccff; width:14px;"></i>
                        <span style="color:#a8c8dc;">+88 01XXXXXXXXX</span>
                    </li>
                    <li class="mb-2 d-flex align-items-start gap-2">
                        <i class="fas fa-envelope mt-1" style="color:#66ccff; width:14px;"></i>
                        <span style="color:#a8c8dc;">info@ideaabd.com</span>
                    </li>
                    <li class="d-flex align-items-start gap-2">
                        <i class="fas fa-map-marker-alt mt-1" style="color:#66ccff; width:14px;"></i>
                        <span style="color:#a8c8dc;">ঢাকা, বাংলাদেশ</span>
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
