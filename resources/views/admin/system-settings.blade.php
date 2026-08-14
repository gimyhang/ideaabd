@extends('layouts.admin')

@section('title', 'সিস্টেম সেটিংস ও থিম কন্ট্রোল')
@section('heading', 'ড্যাশবোর্ড সেটিংস ও ডাইনামিক থিম')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">অ্যাডমিন</a></li>
    <li class="breadcrumb-item active">সিস্টেম সেটিংস</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Notice & Announcements -->
    <div class="col-lg-7">
        <div class="adm-card h-100">
            <div class="adm-card__head">
                <h6><i class="fas fa-bullhorn me-2 text-warning"></i> ড্যাশবোর্ড নোটিশ ব্যানার সেটিংস</h6>
            </div>
            <form action="{{ route('admin.system-settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="adm-card__body">
                    @php
                        $noticeData = $noticeSetting->value ?? ['text' => '', 'active' => false, 'type' => 'info'];
                    @endphp
                    
                    <div class="mb-4 pb-3 border-bottom border-light">
                        <label class="form-label fw-semibold"><i class="fas fa-image me-1 text-primary"></i> ওয়েবসাইটের মূল লোগো (Site Logo)</label>
                        <input type="file" name="site_logo" class="form-control" accept="image/png, image/jpeg, image/svg+xml, image/webp">
                        <div class="form-text mt-1">লোগো পরিবর্তন করতে নতুন ছবি আপলোড করুন (সর্বোচ্চ ২MB, PNG/SVG রিকমেন্ডেড)।</div>
                    </div>

                    <div class="mb-4 pb-3 border-bottom border-light">
                        <label class="form-label fw-semibold"><i class="fas fa-ad me-1 text-primary"></i> হোমপেজ প্রমোশনাল ব্যানার</label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small">ব্যানার ১ (অফার ব্যানার)</label>
                                <input type="file" name="banner_1" class="form-control" accept="image/*">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">ব্যানার ২ (ই-বুক ব্যানার)</label>
                                <input type="file" name="banner_2" class="form-control" accept="image/*">
                            </div>
                        </div>
                        <div class="form-text mt-1">হোমপেজের সাইডবারে দেখানোর জন্য সুন্দর ব্যানার ছবি আপলোড করুন।</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">নোটিশ মেসেজ</label>
                        <textarea name="notice_text" class="form-control" rows="3" placeholder="সকল অ্যাডমিন ও সাব-অ্যাডমিনদের জন্য ড্যাশবোর্ড ব্যানার বার্তা লিখুন...">{{ $noticeData['text'] ?? '' }}</textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">নোটিশ ধরণ</label>
                            <select name="notice_type" class="form-select">
                                <option value="info" {{ ($noticeData['type'] ?? '') === 'info' ? 'selected' : '' }}>ইনফো (নীল)</option>
                                <option value="warning" {{ ($noticeData['type'] ?? '') === 'warning' ? 'selected' : '' }}>সতর্কতা (হলুদ)</option>
                                <option value="success" {{ ($noticeData['type'] ?? '') === 'success' ? 'selected' : '' }}>সফলতা (সবুজ)</option>
                                <option value="danger" {{ ($noticeData['type'] ?? '') === 'danger' ? 'selected' : '' }}>জরুরি (লাল)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">স্ট্যাটাস</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="notice_active" value="1" id="noticeActive" {{ !empty($noticeData['active']) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="noticeActive">নোটিশ প্রকাশ করুন</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="adm-card__foot text-end">
                    <button type="submit" class="btn btn-primary fw-semibold px-4">
                        <i class="fas fa-save me-1"></i> সেটিংস সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Dynamic Theme & Accent Customizer -->
    <div class="col-lg-5">
        <div class="adm-card h-100">
            <div class="adm-card__head">
                <h6><i class="fas fa-palette me-2 text-primary"></i> ডাইনামিক সিএসএস ও থিম কাস্টমাইজার</h6>
            </div>
            <div class="adm-card__body">
                <p class="small text-muted mb-3">আপনার পছন্দমতো ড্যাশবোর্ডের প্রাইমারি ব্র্যান্ড কালার ও লেআউট থিম রিয়েল-টাইমে কাস্টমাইজ করুন:</p>

                <div class="mb-4">
                    <label class="form-label fw-semibold small text-uppercase">১. প্রাইমারি কালার অ্যাকসেন্ট</label>
                    <div class="d-flex flex-wrap gap-2" id="colorPickerGroup">
                        <button type="button" class="btn btn-sm rounded-circle p-3 border-2 border-white shadow-sm" style="background:#0066cc;" data-accent="#0066cc" data-brand2="#0099ff" title="ইন্ডিগো রয়্যাল"></button>
                        <button type="button" class="btn btn-sm rounded-circle p-3 border-2 border-white shadow-sm" style="background:#7048e8;" data-accent="#7048e8" data-brand2="#9470ff" title="পার্পল এনার্জি"></button>
                        <button type="button" class="btn btn-sm rounded-circle p-3 border-2 border-white shadow-sm" style="background:#10b981;" data-accent="#10b981" data-brand2="#34d399" title="এমেরাল্ড গ্রিন"></button>
                        <button type="button" class="btn btn-sm rounded-circle p-3 border-2 border-white shadow-sm" style="background:#f59e0b;" data-accent="#f59e0b" data-brand2="#fbbf24" title="অ্যাম্বার গ্লো"></button>
                        <button type="button" class="btn btn-sm rounded-circle p-3 border-2 border-white shadow-sm" style="background:#e11d48;" data-accent="#e11d48" data-brand2="#fb7185" title="রোজ রেড"></button>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small text-uppercase">২. ভিজ্যুয়াল মোড</label>
                    <div class="d-grid gap-2 d-md-flex">
                        <button type="button" class="btn btn-outline-secondary w-100 fw-semibold" data-theme-toggle>
                            <i class="fas fa-moon me-2"></i> ডার্ক / লাইট মোড সুইচ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('#colorPickerGroup button').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var brand = this.getAttribute('data-accent');
            var brand2 = this.getAttribute('data-brand2');
            document.documentElement.style.setProperty('--brand', brand);
            document.documentElement.style.setProperty('--brand-2', brand2);
            localStorage.setItem('adm-dynamic-brand', brand);
            localStorage.setItem('adm-dynamic-brand2', brand2);
        });
    });
</script>
@endpush
