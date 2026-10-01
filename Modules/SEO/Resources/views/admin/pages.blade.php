@extends('layouts.admin')

@section('title', 'কাস্টম ও স্ট্যাটিক পেজ এসইও — Idea প্রকাশন')
@section('heading', 'স্ট্যাটিক ও কাস্টম ইউআরএল এসইও ম্যানেজার')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.seo.index') }}">এসইও ম্যানেজার</a></li>
    <li class="breadcrumb-item active">পেজ এসইও</li>
@endsection

@section('actions')
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.seo.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5">
            <i class="fa-solid fa-arrow-left"></i> ব্যাক
        </a>
    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('modules/seo/css/seo-modern.css') }}">
@endpush

@section('content')
<div class="seo-pages-wrapper pb-4">

    <div class="row g-4">
        
        <!-- Left: Page List Accordion / Selector -->
        <div class="col-lg-5">
            <div class="card border-0 rounded-4 bg-white shadow-xs border p-3.5 mb-4">
                <h6 class="fw-bold text-dark mb-3 d-flex align-items-center justify-content-between">
                    <span><i class="fa-solid fa-list-check text-primary me-2"></i>প্রধান স্ট্যাটিক পেজসমূহ</span>
                    <span class="badge bg-light text-dark border rounded-pill">{{ count($pages) }}টি পেজ</span>
                </h6>
                <div class="list-group list-group-flush gap-2" id="pagesList">
                    @php
                        $standardPages = [
                            '/'           => 'হোম পেজ (Home)',
                            '/books'      => 'বুকশপ ও ক্যাটালগ (Bookshop)',
                            '/ebooks'     => 'ই-বুক লাইব্রেরি (E-Books)',
                            '/authors'    => 'লেখক ডিরেক্টরি (Authors)',
                            '/publishers' => 'প্রকাশক তালিকা (Publishers)',
                            '/blog'       => 'আইডিয়াপত্র ও ব্লগ (Ideapatra)',
                            '/webzines'   => 'ওয়েবজিন ও সাময়িকী (Webzines)',
                            '/research'   => 'গবেষণা জার্নাল (Research)',
                            '/about'      => 'আমাদের সম্পর্কে (About Us)',
                            '/contact'    => 'যোগাযোগ ও হেল্পডেস্ক (Contact)',
                            '/terms'      => 'শর্তাবলী ও নীতিমালা (Terms & Policies)',
                        ];
                    @endphp

                    @foreach($standardPages as $path => $label)
                        @php
                            $matched = $pages->firstWhere('url_path', $path);
                            $score = $matched?->seo_score ?? 85;
                        @endphp
                        <button type="button" class="list-group-item list-group-item-action rounded-3 border d-flex align-items-center justify-content-between p-3" 
                                onclick="loadPageData('{{ $path }}', '{{ $label }}', @js($matched))">
                            <div>
                                <div class="fw-bold text-dark">{{ $label }}</div>
                                <div class="small text-muted font-monospace">{{ $path }}</div>
                            </div>
                            <span class="badge {{ $score >= 80 ? 'bg-success' : 'bg-warning' }} rounded-pill px-2.5 py-1 font-monospace">
                                {{ $score }}%
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right: Dedicated Page SEO Editor -->
        <div class="col-lg-7">
            <div class="card border-0 rounded-4 bg-white shadow-xs border p-4">
                <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-pen-nib text-primary"></i>
                    <span id="editorPageLabel">হোম পেজ (Home)</span>
                </h5>
                <p class="text-muted small mb-4">এই পেজের মেটা টাইটেল, বিবরণ, ওপেনগ্রাফ ব্যানার ও ক্যানোনিকাল ট্যাগ পরিচালনা করুন।</p>

                <form action="{{ route('admin.seo.pages.update') }}" method="POST" id="pageSeoForm">
                    @csrf
                    <input type="hidden" name="url_path" id="formUrlPath" value="/">

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-bold text-dark mb-0">মেটা টাইটেল (Page Title)</label>
                            <span id="pageTitleCount" class="char-counter-pill char-good">০ অক্ষর</span>
                        </div>
                        <input type="text" name="meta_title" id="pageMetaTitle" class="form-control rounded-3" 
                               value="আইডিয়া প্রকাশন — বই ও মুক্তচিন্তার ডিজিটাল প্রকাশনা প্ল্যাটফর্ম" required>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-bold text-dark mb-0">মেটা বিবরণ (Meta Description)</label>
                            <span id="pageDescCount" class="char-counter-pill char-good">০ অক্ষর</span>
                        </div>
                        <textarea name="meta_description" id="pageMetaDesc" class="form-control rounded-3" rows="3">আইডিয়া প্রকাশন: উত্তরবঙ্গের জ্ঞান, সাহিত্য ও সংস্কৃতি চর্চার নিরন্তর অভিযাত্রা। বই, ই-বুক, আইডিয়াপত্র ও সাময়িকী সরাসরি অনলাইনে সংগ্রহ করুন।</textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark mb-1">ফোকাস কিওয়ার্ড (Focus Keyword)</label>
                            <input type="text" name="focus_keyword" id="pageFocusKeyword" class="form-control rounded-3" value="আইডিয়া প্রকাশন">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark mb-1">রোবটস নির্দেশিকা (Robots)</label>
                            <select name="robots" id="pageRobots" class="form-select rounded-3">
                                <option value="index, follow" selected>index, follow (সার্চ ইনডেক্সিং সচল)</option>
                                <option value="noindex, follow">noindex, follow (লুকানো)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark mb-1">মেটা কিওয়ার্ডস</label>
                        <input type="text" name="meta_keywords" id="pageKeywords" class="form-control rounded-3" value="আইডিয়া প্রকাশন, বইমেলা, বাংলা বই, ই-বুক, রংপুর প্রকাশনা">
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-dark mb-1">সোশ্যাল শেয়ার ইমেজ (OpenGraph Image URL)</label>
                        <input type="text" name="og_image" id="pageOgImage" class="form-control rounded-3 font-monospace small" placeholder="/images/og-banner.jpg">
                    </div>

                    <!-- Live Google SERP Box -->
                    <div class="serp-card mb-4">
                        <div class="serp-url-box">
                            <div class="serp-favicon d-flex align-items-center justify-content-center small text-primary"><i class="fa-solid fa-globe"></i></div>
                            <div>
                                <div class="serp-site-name">আইডিয়া প্রকাশন — ideaabd.com</div>
                                <div class="serp-breadcrumbs" id="pageSerpUrl">https://www.ideaabd.com/</div>
                            </div>
                        </div>
                        <div class="serp-title" id="pageSerpTitle">আইডিয়া প্রকাশন — বই ও মুক্তচিন্তার ডিজিটাল প্রকাশনা প্ল্যাটফর্ম</div>
                        <div class="serp-snippet" id="pageSerpDesc">আইডিয়া প্রকাশন: উত্তরবঙ্গের জ্ঞান, সাহিত্য ও সংস্কৃতি চর্চার নিরন্তর অভিযাত্রা...</div>
                    </div>

                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-xs">
                        <i class="fa-solid fa-floppy-disk me-1.5"></i> এই পেজের এসইও সেভ করুন
                    </button>
                </form>
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('modules/seo/js/seo-scanner.js') }}"></script>
<script>
function loadPageData(path, label, seo) {
    document.getElementById('editorPageLabel').textContent = label;
    document.getElementById('formUrlPath').value = path;
    document.getElementById('pageSerpUrl').textContent = `https://www.ideaabd.com${path}`;

    if (seo) {
        document.getElementById('pageMetaTitle').value = seo.meta_title || '';
        document.getElementById('pageMetaDesc').value = seo.meta_description || '';
        document.getElementById('pageFocusKeyword').value = seo.focus_keyword || '';
        document.getElementById('pageKeywords').value = seo.meta_keywords || '';
        document.getElementById('pageRobots').value = seo.robots || 'index, follow';
        document.getElementById('pageOgImage').value = seo.og_image || '';
    }

    // Trigger input events to refresh counters & SERP
    document.getElementById('pageMetaTitle').dispatchEvent(new Event('input'));
    document.getElementById('pageMetaDesc').dispatchEvent(new Event('input'));
}
</script>
@endpush
