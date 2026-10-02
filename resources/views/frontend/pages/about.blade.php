@extends('layouts.app')

@php
    $about = \App\Support\SiteSetting::aboutCustomizer();
    $siteName = \App\Support\SiteSetting::name() ?: 'আইডিয়া প্রকাশন';
    $pageTitle = $about['page_title'] ?? 'আইডিয়া প্রকাশন: উত্তরবঙ্গের জ্ঞান, সাহিত্য ও সংস্কৃতি চর্চার নিরন্তর অভিযাত্রা';
    $pageDesc = $about['page_subtitle'] ?? 'উত্তরবঙ্গের জ্ঞানচর্চা, সৃজনশীল প্রকাশ ও সাংস্কৃতিক আত্মপরিচয় নির্মাণের দুই দশকের অভিযাত্রা। ৪৫০+ প্রকাশিত বই ও ২৭,০০০+ পাঠাগার বই অনুদান।';
    $ogBanner = \App\Support\SiteSetting::blogOgBannerUrl() ?: asset('images/og-banner.jpg');
    $publisherUrl = !empty($about['publisher_url']) ? url($about['publisher_url']) : (Route::has('authors.show') ? route('authors.show', 'sakil-masud') : url('/authors/sakil-masud'));
@endphp

@section('title', $pageTitle . ' — ' . $siteName)
@section('meta_description', $pageDesc)
@section('meta_keywords', 'আইডিয়া প্রকাশন, রংপুর প্রকাশনা, উত্তরবঙ্গের সাহিত্য, সাকিল মাসুদ, বইমেলা, পাঠাগার আন্দোলন, প্রকাশনা প্রতিষ্ঠান, সাহিত্য পত্রিকা')
@section('og_type', 'article')
@section('og_title', $pageTitle)
@section('og_description', $pageDesc)
@section('og_image', $ogBanner)
@section('og_url', url('/about'))

@section('schema_json')
@php
    $aboutSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'AboutPage',
        'name' => $pageTitle,
        'description' => $pageDesc,
        'url' => url('/about'),
        'publisher' => [
            '@type' => 'Organization',
            'name' => $siteName,
            'url' => 'https://www.ideaabd.com',
            'founder' => [
                '@type' => 'Person',
                'name' => $about['publisher_name'] ?? 'সাকিল মাসুদ',
                'jobTitle' => $about['publisher_role'] ?? 'সিইও ও প্রকাশক',
            ],
        ],
    ];
@endphp
<script type="application/ld+json">
{!! json_encode($aboutSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endsection

@section('content')
<!-- Reading Progress Bar -->
<div id="aboutReadingProgressBar" class="position-fixed top-0 start-0 z-3" style="height: 4px; width: 0%; background: linear-gradient(90deg, #f59e0b, #0284c7, #10b981); transition: width 0.1s linear;"></div>

<style>
    /* Universal Kalpurush Typography System */
    .about-kalpurush-root,
    .about-kalpurush-root * {
        font-family: 'Kalpurush', 'Nikosh', 'SolaimanLipi', 'Hind Siliguri', sans-serif !important;
    }

    .about-hero-section {
        background: linear-gradient(135deg, #07192f 0%, #0d2847 50%, #0369a1 100%);
        color: #ffffff;
        padding: 55px 0 50px 0;
        position: relative;
        overflow: hidden;
    }

    .about-hero-section::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 6px;
        background: linear-gradient(90deg, #f59e0b 0%, #38bdf8 50%, #10b981 100%);
    }

    .about-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.25);
        backdrop-filter: blur(10px);
        padding: 7px 18px;
        border-radius: 9999px;
        font-size: 14px;
        font-weight: 700;
        color: #fcd34d;
        margin-bottom: 18px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
    }

    .about-main-headline {
        font-size: 2.35rem;
        font-weight: 800;
        line-height: 1.45;
        letter-spacing: -0.01em;
        color: #ffffff;
    }

    @media (max-width: 768px) {
        .about-main-headline {
            font-size: 1.65rem;
            line-height: 1.4;
        }
        .about-hero-section {
            padding: 38px 0 35px 0;
        }
    }

    /* KPI Cards */
    .about-kpi-card {
        background: var(--bs-card-bg, #ffffff);
        border: 1px solid var(--bs-border-color, #e2e8f0);
        border-radius: 18px;
        padding: 22px 16px;
        text-align: center;
        box-shadow: 0 4px 18px -3px rgba(15, 23, 42, 0.06);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        height: 100%;
        position: relative;
        overflow: hidden;
    }

    .about-kpi-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 28px -4px rgba(15, 23, 42, 0.12);
        border-color: #0284c7;
    }

    .about-kpi-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        margin-bottom: 12px;
    }

    /* Article Body & Reading Experience */
    .about-article-body {
        font-size: 18.5px;
        line-height: 2.05;
        color: var(--bs-body-color, #334155);
    }

    .about-article-body p {
        margin-bottom: 24px;
        text-align: justify;
        text-justify: inter-word;
    }

    .about-dropcap::first-letter {
        float: left;
        font-size: 3.8rem;
        line-height: 0.8;
        padding-top: 4px;
        padding-right: 12px;
        padding-bottom: 2px;
        color: #0284c7;
        font-weight: 800;
    }

    .about-section-heading {
        font-size: 24px;
        font-weight: 800;
        color: var(--bs-heading-color, #0f172a);
        margin-top: 42px;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 12px;
        scroll-margin-top: 80px;
    }

    .about-section-heading::before {
        content: '';
        width: 6px;
        height: 26px;
        background: linear-gradient(180deg, #0284c7 0%, #0369a1 100%);
        border-radius: 4px;
        display: inline-block;
    }

    /* Pull Quotes */
    .about-pullquote {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, rgba(5, 150, 105, 0.04) 100%);
        border-left: 5px solid #10b981;
        border-radius: 0 18px 18px 0;
        padding: 26px 30px;
        margin: 36px 0;
        font-size: 20px;
        font-weight: 700;
        line-height: 1.85;
        color: #065f46;
        position: relative;
    }

    [data-bs-theme="dark"] .about-pullquote {
        color: #6ee7b7;
        background: rgba(16, 185, 129, 0.12);
    }

    .about-pullquote-blue {
        background: linear-gradient(135deg, rgba(2, 132, 199, 0.08) 0%, rgba(3, 105, 161, 0.04) 100%);
        border-left-color: #0284c7;
        color: #0369a1;
    }

    [data-bs-theme="dark"] .about-pullquote-blue {
        color: #7dd3fc;
        background: rgba(2, 132, 199, 0.12);
    }

    /* Signature & Publisher Card */
    .about-signature-card {
        background: var(--bs-tertiary-bg, #f8fafc);
        border: 1.5px solid var(--bs-border-color, #cbd5e1);
        border-radius: 20px;
        padding: 26px 30px;
        margin-top: 45px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
    }

    /* Floating / Sticky Interactive Toolbox */
    .about-reader-tools {
        position: sticky;
        top: 90px;
        background: var(--bs-card-bg, #ffffff);
        border: 1px solid var(--bs-border-color, #e2e8f0);
        border-radius: 18px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .about-toc-link {
        color: var(--bs-secondary-color, #64748b);
        text-decoration: none;
        display: block;
        padding: 6px 10px;
        border-radius: 8px;
        font-size: 14.5px;
        transition: all 0.2s ease;
        line-height: 1.5;
    }

    .about-toc-link:hover,
    .about-toc-link.active {
        color: #0284c7;
        background: rgba(2, 132, 199, 0.08);
        font-weight: 700;
        transform: translateX(3px);
    }

    /* Mobile Responsive Optimizations */
    @media (max-width: 991px) {
        .about-article-body {
            font-size: 17px;
            line-height: 1.95;
        }
    }
</style>

<div class="about-kalpurush-root">
    
    {{-- 1. Hero Title Banner --}}
    <div class="about-hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-10 mx-auto text-center">
                    <span class="about-badge">
                        <i class="fa-solid fa-feather-pointed me-1"></i> {{ $about['hero_badge'] ?? 'আমাদের কথা ও মূল দর্শন' }}
                    </span>
                    <h1 class="about-main-headline mb-3">
                        {{ $pageTitle }}
                    </h1>
                    <p class="text-white-50 fs-6 mb-0" style="max-width: 820px; margin: 0 auto; line-height: 1.75;">
                        {{ $pageDesc }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. Main Content, Statistics & Reader Navigator --}}
    <div class="container py-4 py-md-5">
        
        {{-- KPI Statistics Grid (4 Key Highlights) --}}
        <div class="row g-3 mb-5">
            <div class="col-6 col-md-3">
                <div class="about-kpi-card">
                    <div class="about-kpi-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fa-solid fa-book-open"></i>
                    </div>
                    <h3 class="fw-bold mb-1 text-dark">{{ $about['stat_books_count'] ?? '৪৫০+' }}</h3>
                    <div class="small text-muted fw-semibold">{{ $about['stat_books_label'] ?? 'প্রকাশিত বই' }}</div>
                    <div class="text-muted opacity-75" style="font-size: 12px;">{{ $about['stat_books_sub'] ?? 'গবেষণা ও সাহিত্য' }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="about-kpi-card">
                    <div class="about-kpi-icon bg-success bg-opacity-10 text-success">
                        <i class="fa-solid fa-book-bookmark"></i>
                    </div>
                    <h3 class="fw-bold mb-1 text-dark">{{ $about['stat_lib_count'] ?? '২৭,০০০+' }}</h3>
                    <div class="small text-muted fw-semibold">{{ $about['stat_lib_label'] ?? 'বিনামূল্যে বই বিতরণ' }}</div>
                    <div class="text-muted opacity-75" style="font-size: 12px;">{{ $about['stat_lib_sub'] ?? 'পাঠাগার আন্দোলন' }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="about-kpi-card">
                    <div class="about-kpi-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fa-solid fa-award"></i>
                    </div>
                    <h3 class="fw-bold mb-1 text-dark">{{ $about['stat_years_count'] ?? '২ দশক' }}</h3>
                    <div class="small text-muted fw-semibold">{{ $about['stat_years_label'] ?? 'নিরবচ্ছিন্ন প্রকাশনা' }}</div>
                    <div class="text-muted opacity-75" style="font-size: 12px;">{{ $about['stat_years_sub'] ?? 'জ্ঞানচর্চা ও বিকাশ' }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="about-kpi-card">
                    <div class="about-kpi-icon bg-info bg-opacity-10 text-info">
                        <i class="fa-solid fa-landmark"></i>
                    </div>
                    <h3 class="fw-bold mb-1 text-dark">{{ $about['stat_fair_count'] ?? '১ দশক' }}</h3>
                    <div class="small text-muted fw-semibold">{{ $about['stat_fair_label'] ?? 'অমর একুশে বইমেলা' }}</div>
                    <div class="text-muted opacity-75" style="font-size: 12px;">{{ $about['stat_fair_sub'] ?? 'জাতীয় পরিসরে আঞ্চলিক স্বর' }}</div>
                </div>
            </div>
        </div>

        <div class="row g-4 g-lg-5">
            
            {{-- Main Article & Institutional Manifesto --}}
            <div class="col-lg-8">
                <article class="about-article-body" id="aboutArticleContent">

                    <!-- Section 1 -->
                    <div id="sec-philosophy">
                        <p class="lead fw-semibold text-dark about-dropcap" style="font-size: 19.5px; line-height: 1.9;">
                            {{ $about['statement_p1'] ?? 'একটি জনপদের ইতিহাস কেবল তার রাজা-রাজন্য, স্থাপত্য কিংবা রাজনৈতিক উত্থান-পতনের ইতিহাস নয়; তার প্রকৃত পরিচয় নিহিত থাকে মানুষের চিন্তা, মনন, সৃজনশীলতা, ভাষা ও সংস্কৃতির পরম্পরায়। বই সেই পরম্পরার অন্যতম প্রধান বাহন, আর প্রকাশনা সেই বাহনের নির্মাতা। মানুষের কাছে পৌঁছে দেয়ার বুদ্ধিবৃত্তিক সেতু বা উদ্যোগ। উত্তরবঙ্গের, বিশেষত রংপুরের সাহিত্য-সংস্কৃতির পরিসরকে বৃহত্তর দৃষ্টিভঙ্গিতে বিবেচনা করলে আইডিয়া প্রকাশনের অভিযাত্রা কেবল প্রকাশনা প্রতিষ্ঠানের বিকাশের ইতিহাস নয়; বরং এই পিছিয়ে থাকা অবহেলিত জনপদের জ্ঞানচর্চা, সৃজনশীল প্রকাশ ও সাংস্কৃতিক আত্মপরিচয় নির্মাণের প্রচেষ্টারও অংশ।' }}
                        </p>

                        <p>
                            {{ $about['statement_p2'] ?? 'রংপুরের প্রকাশনা ও সাহিত্যচর্চার ইতিহাসে রঙ্গপুর বার্তাবহ একটি ঐতিহাসিক স্মারক। সেই ঐতিহ্যের উত্তরসূরি হিসেবে উত্তরবঙ্গের সাহিত্য-সংস্কৃতির পরিসর বিস্তৃত করার প্রত্যয়ে আইডিয়া প্রকাশন কাজ করে চলেছে। অতীত অর্জন স্মৃতির বিষয় হিসেবে নয়, বরং বর্তমান ও ভবিষ্যতের সম্ভাবনার ভিত্তি হিসেবে দেখাই এই প্রতিষ্ঠানের মূল দর্শন। কারণ, যে জনপদ তার জ্ঞানগত ঐতিহ্যকে ধারণ করতে পারে না, সে জনপদের ভবিষ্যৎ নির্মাণও অসম্পূর্ণ থেকে যায়।' }}
                        </p>
                    </div>

                    <!-- Section 2 -->
                    <div id="sec-journey">
                        <h2 class="about-section-heading">দুই দশকের পথচলা ও বহুমাত্রিক প্রকাশনা</h2>

                        <p>
                            {{ $about['statement_p3'] ?? 'প্রায় দুই দশকের অভিযাত্রায় আইডিয়া প্রকাশন বই প্রকাশকে নিছক বাণিজ্যিক কর্মকাণ্ডের মধ্যে সীমাবদ্ধ রাখেনি; বরং সাহিত্য, গবেষণা, ইতিহাস, সংস্কৃতি ও সমাজভাবনার বহুমাত্রিক প্রকাশমাধ্যম হিসেবে নিজেকে বিকশিত করার চেষ্টা করেছে। বর্তমানে প্রতিষ্ঠানটির প্রকাশিত বইয়ের সংখ্যা ৪৫০-এর বেশি। গবেষণাগ্রন্থ, গল্প, উপন্যাস, ছড়া, কবিতা, অনুবাদ এবং শিশুসাহিত্যসহ বিচিত্র বিষয়ে বই প্রকাশের মধ্য দিয়ে এই প্রতিষ্ঠান জ্ঞান ও সৃজনশীলতার বহুমুখী প্রবাহকে ধারণ করেছে। এই বৈচিত্র্য কেবল প্রকাশিত বইয়ের সংখ্যাগত বিস্তার নয়; এটি পাঠ, চিন্তা ও মননের বিভিন্ন ধারাকে একই সাংস্কৃতিক পরিসরে যুক্ত করার প্রয়াস।' }}
                        </p>
                    </div>

                    <!-- Quote 1 -->
                    <div class="about-pullquote position-relative">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <i class="fa-solid fa-quote-left fs-3 opacity-50"></i>
                            <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-0.5" style="font-size: 11px;" onclick="copyQuoteText(this, '{{ addslashes($about['quote_text'] ?? 'একটি জনপদের ইতিহাস কেবল তার রাজা-রাজন্য বা রাজনৈতিক উত্থান-পতনের ইতিহাস নয়...') }}')">
                                <i class="fa-regular fa-copy me-1"></i>উক্তি কপি
                            </button>
                        </div>
                        “{{ $about['quote_text'] ?? 'একটি জনপদের ইতিহাস কেবল তার রাজা-রাজন্য বা রাজনৈতিক উত্থান-পতনের ইতিহাস নয়; তার প্রকৃত পরিচয় নিহিত থাকে মানুষের চিন্তা, মনন, সৃজনশীলতা, ভাষা ও সংস্কৃতির পরম্পরায়।' }}”
                        <div class="text-end mt-2 small opacity-75">— {{ $about['quote_author'] ?? 'সাকিল মাসুদ, সিইও ও প্রকাশক' }}</div>
                    </div>

                    <!-- Section 3 -->
                    <div id="sec-children">
                        <h2 class="about-section-heading">শিশুসাহিত্য ও ভবিষ্যৎ প্রজন্ম বিনির্মাণ</h2>

                        <p>
                            {{ $about['statement_p4'] ?? 'বিশেষত শিশুদের জন্য বই প্রকাশের উদ্যোগ ভবিষ্যৎ পাঠকসমাজ নির্মাণের সঙ্গে গভীরভাবে সম্পর্কিত। একটি জাতির মননশীল ভবিষ্যৎ গড়ে ওঠে শৈশবের পাঠাভ্যাস, কল্পনাশক্তি ও প্রশ্ন করার স্বাধীনতার মধ্য দিয়ে। তাই শিশুসাহিত্য প্রকাশ আইডিয়া প্রকাশনের কাছে কেবল প্রকাশনাসূচির একটি বিভাগ নয়; এটি আগামী দিনের মুক্তবুদ্ধি, মানবিকতা ও সৃজনশীলতার ভিত নির্মাণের অংশ। যে শিশু বইয়ের সঙ্গে বন্ধুত্ব গড়ে তোলে, তার সামনে পৃথিবীকে জানার, বোঝার ও নতুনভাবে আবিষ্কার করার অসংখ্য দরজা খুলে যায়।' }}
                        </p>
                    </div>

                    <!-- Section 4 -->
                    <div id="sec-research">
                        <h2 class="about-section-heading">গবেষণা, সাময়িকপত্র ও স্থানীয় জ্ঞানচর্চা</h2>

                        <p>
                            {{ $about['statement_p5'] ?? 'একই সঙ্গে গবেষণা ও সাহিত্যপত্র প্রকাশের মধ্য দিয়ে প্রতিষ্ঠানটি স্থানীয় জ্ঞানচর্চার ধারাবাহিকতা রক্ষায় ভূমিকা রাখছে। গবেষণা অতীতকে অনুসন্ধান করে, সাহিত্য বর্তমানের অনুভূতি ও সংকটকে ভাষা দেয়, আর সাময়িকপত্র ও সাহিত্যপত্র নতুন চিন্তা, বিতর্ক ও সৃজনশীলতার জন্য উন্মুক্ত পরিসর তৈরি করে। আইডিয়া প্রকাশনের উদ্যোগে প্রকাশিত একটি গবেষণা সাময়িকী ও দুটি সাহিত্যপত্র এই বৃহত্তর বুদ্ধিবৃত্তিক চর্চার অংশ। এসব প্রকাশনার মধ্য দিয়ে স্থানীয় ইতিহাস, জনজীবন, সাহিত্যিক অভিজ্ঞতা ও সমকালীন ভাবনার সঙ্গে পাঠকের সংযোগ স্থাপনের সুযোগ তৈরি হয়। একটি জনপদের নিজস্ব জ্ঞানভান্ডার নির্মাণে এ ধরনের উদ্যোগের গুরুত্ব তাই বিশেষভাবে তাৎপর্যপূর্ণ।' }}
                        </p>
                    </div>

                    <!-- Section 5 -->
                    <div id="sec-libraries">
                        <h2 class="about-section-heading">পাঠাগার আন্দোলন ও সামাজিক দায়বদ্ধতা</h2>

                        <p>
                            {{ $about['statement_p6'] ?? 'প্রকাশনার সার্থকতা অবশ্য কেবল বই ছাপা ও বিতরণের মধ্যে সীমাবদ্ধ নয়; বইয়ের সঙ্গে মানুষের সম্পর্ক তৈরি করাও এর অন্যতম দায়িত্ব। এই উপলব্ধি থেকেই রংপুরের বেসরকারি পাঠাগারগুলোতে ২৭ হাজারের বেশি বই বিনামূল্যে বিতরণ করা হয়েছে। যা এটি বইকে পাঠকের নাগালে পৌঁছে দেওয়ার পাশাপাশি প্রাতিষ্ঠানিক ও সামাজিক পাঠসংস্কৃতি বিস্তারেরও একটি প্রয়াস। পাঠাগার মানুষের সম্মিলিত জ্ঞানচর্চা, সামাজিক বোঝাপড়া ও মুক্তচিন্তার পরিসর। গণবিশ্ববিদ্যালয় তো বটে।' }}
                        </p>
                    </div>

                    <!-- Section 6 -->
                    <div id="sec-boimela">
                        <h2 class="about-section-heading">অমর একুশে বইমেলা ও জাতীয় পরিসর</h2>

                        <p>
                            {{ $about['statement_p7'] ?? 'আইডিয়া প্রকাশনের আরেকটি উল্লেখযোগ্য অভিযাত্রা অমর একুশে বইমেলাকে কেন্দ্র করে। এক দশক ধরে জাতীয় সাংস্কৃতিক আয়োজনে অংশগ্রহণের মধ্য দিয়ে প্রতিষ্ঠানটি রংপুরের লেখক, গবেষক ও সৃজনশীল মানুষদের প্রকাশিত বই বৃহত্তর পাঠকসমাজের সামনে তুলে ধরার সুযোগ তৈরি করেছে। উত্তরবঙ্গের একটি প্রকাশনা প্রতিষ্ঠানের জন্য রাজধানীকেন্দ্রিক প্রকাশনা ও পাঠপরিসরে ধারাবাহিকভাবে উপস্থিত থাকা শুধু প্রাতিষ্ঠানিক পরিচিতি অর্জনের বিষয় নয়; এটি ভৌগোলিক দূরত্ব অতিক্রম করে সাহিত্যিক ও সাংস্কৃতিক বিনিময়ের ক্ষেত্র সম্প্রসারণেও প্রয়াস। উত্তরবঙ্গের কণ্ঠস্বরকে জাতীয় পরিসরে পৌঁছে দেওয়া এবং জাতীয় সাহিত্যপ্রবাহের সঙ্গে আঞ্চলিক সৃজনশীলতার সংযোগ স্থাপন— এই দুইয়ের মধ্যবর্তী সেতু নির্মাণেই এমন অংশগ্রহণের তাৎপর্য নিহিত। প্রতি বছর যদিও ২-৩ লক্ষ টাকা ভর্তুকি দিয়ে কাজটি পরিচালনা করছে আইডিয়া প্রকাশন।' }}
                        </p>
                    </div>

                    <!-- Quote 2 -->
                    <div class="about-pullquote about-pullquote-blue">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <i class="fa-solid fa-quote-left fs-3 opacity-50"></i>
                        </div>
                        “প্রকাশনা কেবল লেখক ও পাঠকের মধ্যবর্তী কোনো বাণিজ্যিক সেতু নয়; এটি অতীত ও ভবিষ্যৎ, স্থানীয় অভিজ্ঞতা ও বৈশ্বিক জ্ঞান, ব্যক্তির চিন্তা ও সমাজের সম্মিলিত মননের মধ্যকার জীবন্ত সংযোগ।”
                    </div>

                    <!-- Section 7 -->
                    <div id="sec-future">
                        <h2 class="about-section-heading">উত্তরবঙ্গের নিজস্ব জ্ঞানভান্ডার ও ভবিষ্যৎ অঙ্গীকার</h2>

                        <p>
                            {{ $about['statement_p8'] ?? 'তবে অতীত অর্জন কিংবা বর্তমানের বিস্তার প্রতিষ্ঠানের চূড়ান্ত পরিচয় নয়। প্রকৃত পরিচয় নির্ধারিত হয় তার ভবিষ্যৎ ভাবনা, সামাজিক দায়বদ্ধতা ও সময়ের পরিবর্তনকে ধারণ করার ক্ষমতা দিয়ে। আইডিয়া প্রকাশনের সামনে তাই রয়েছে আরও বিস্তৃত দায়িত্ব। উত্তরবঙ্গের ইতিহাস, প্রত্নঐতিহ্য, লোকসংস্কৃতি, ভাষা, জনজীবন ও সামাজিক পরিবর্তন নিয়ে পরিকল্পিত গবেষণা প্রকাশ; নবীন লেখক ও গবেষকদের সৃজনশীল প্রকাশের সুযোগ সৃষ্টি; শিশু-কিশোরদের জন্য মানসম্মত বইয়ের পরিসর বৃদ্ধি; এবং মুদ্রিত বইয়ের পাশাপাশি ই-বুক ও ডিজিটাল পাঠমাধ্যমের সম্প্রসারণ— এসব উদ্যোগ ভবিষ্যৎ অভিযাত্রাকে নতুন মাত্রা দিতে পারে।' }}
                        </p>

                        <p>
                            {{ $about['statement_p9'] ?? 'বিশেষভাবে প্রয়োজন উত্তরবঙ্গের নিজস্ব জ্ঞানভান্ডার নির্মাণ। দেশের বিভিন্ন অঞ্চলের ইতিহাস, সাহিত্য ও সংস্কৃতির মতো উত্তরবঙ্গেরও রয়েছে স্বতন্ত্র অভিজ্ঞতা, সামাজিক বাস্তবতা, ঐতিহাসিক স্মৃতি এবং ভবিষ্যৎ সম্ভাবনা। এসব বিষয়কে তথ্যনির্ভর গবেষণা, সৃজনশীল সাহিত্য ও মননশীল প্রকাশনার মাধ্যমে সংরক্ষণ করা জরুরি। স্থানীয় ইতিহাসের উপাদান সংগ্রহ, হারিয়ে যেতে থাকা স্মৃতি ও মৌখিক ইতিহাস লিপিবদ্ধ করা, আঞ্চলিক সাহিত্যকে মূল্যায়ন করা এবং নতুন প্রজন্মের কাছে উত্তরবঙ্গের বহুমাত্রিক পরিচয় তুলে ধরা— এসব কাজের মধ্য দিয়েই একটি প্রকাশনা প্রতিষ্ঠান তার ভৌগোলিক অবস্থানকে অতিক্রম করে বৃহত্তর বুদ্ধিবৃত্তিক ভূমিকা পালন করতে পারে।' }}
                        </p>

                        <p>
                            {{ $about['statement_p10'] ?? 'আমাদের বিশ্বাস, প্রকাশনা কেবল লেখক ও পাঠকের মধ্যবর্তী কোনো বাণিজ্যিক সেতু নয়; এটি অতীত ও ভবিষ্যৎ, স্থানীয় অভিজ্ঞতা ও বৈশ্বিক জ্ঞান, ব্যক্তির চিন্তা ও সমাজের সম্মিলিত মননের মধ্যকার জীবন্ত সংযোগ। বই মানুষের চিন্তার স্বাধীনতাকে প্রসারিত করে, প্রতিষ্ঠিত ধারণাকে প্রশ্ন করতে শেখায় এবং নতুন সম্ভাবনার কল্পনা নির্মাণ করে। সেই অর্থে একটি প্রকাশনা প্রতিষ্ঠান একই সঙ্গে সাংস্কৃতিক স্মৃতির সংরক্ষক, সমকালীন চিন্তার বহুভাষিক সহযাত্রী এবং ভবিষ্যৎ নির্মাণের অংশীদার।' }}
                        </p>

                        <p>
                            {{ $about['statement_p11'] ?? 'আইডিয়া প্রকাশনের অগ্রযাত্রার মূল প্রত্যয় এখানেই— অতীতকে ধারণ করা, বর্তমানকে গভীরভাবে পাঠ করা এবং ভবিষ্যতের জন্য জ্ঞান ও সৃজনশীলতার নতুন দিগন্ত উন্মোচন করা। রংপুরের মাটি, মানুষের জীবন, ইতিহাস ও সাংস্কৃতিক ঐতিহ্য আমাদের শিকড়; বই, গবেষণা, সাহিত্য ও মুক্তচিন্তা আমাদের কর্মক্ষেত্র; আর একটি মননশীল, পাঠাভ্যাসসম্পন্ন ও সাংস্কৃতিকভাবে সমৃদ্ধ সমাজ নির্মাণ আমাদের অভীষ্ট।' }}
                        </p>

                        <p>
                            {{ $about['statement_p12'] ?? 'আমাদের নিজস্ব পাঠাগারে মননপাঠের আসর বসে। নিয়মিত নবীন ও পাঠক এবং লেখকগণ আসা যাওয়া, চর্চার ভেতর থাকেন। আমরা বিশ্বাস করি, একটি বই কেবল তার সময়ের কথা বলে না; অনাগত সময়ের জন্যও চিন্তার বীজ রেখে যায়। তাই আইডিয়া প্রকাশনের পথচলা শুধু প্রকাশিত বইয়ের সংখ্যা বাড়ানোর যাত্রা নয়, বরং পাঠক তৈরি, জ্ঞানচর্চার পরিসর বিস্তার, আঞ্চলিক সৃজনশীলতার মর্যাদা প্রতিষ্ঠা এবং উত্তরবঙ্গের সাংস্কৃতিক ভবিষ্যৎ নির্মাণে অংশগ্রহণের এক অব্যাহত অঙ্গীকার।' }}
                        </p>
                    </div>

                    {{-- Publisher Official Signature Card --}}
                    <div class="about-signature-card d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div>
                            <h4 class="fw-bold mb-1 text-dark">{{ $about['publisher_name'] ?? 'সাকিল মাসুদ' }}</h4>
                            <div class="text-primary fw-bold small">{{ $about['publisher_role'] ?? 'সিইও ও প্রকাশক' }}</div>
                            <div class="text-muted small">{{ $about['publisher_note'] ?? 'আইডিয়া প্রকাশন • বুকস অব আইডিয়া' }}</div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ $publisherUrl }}" class="btn btn-outline-primary btn-sm rounded-pill px-3.5 py-1.5 fw-semibold shadow-2xs">
                                <i class="fa-solid fa-feather-pointed me-1"></i> লেখকের প্রোফাইল
                            </a>
                            <a href="{{ route('contact') }}" class="btn btn-primary btn-sm rounded-pill px-3.5 py-1.5 fw-semibold shadow-xs">
                                <i class="fa-solid fa-envelope me-1"></i> যোগাযোগ ও বার্তা
                            </a>
                        </div>
                    </div>

                </article>
            </div>

            {{-- Right Sidebar: Interactive Reader Tools & Table of Contents --}}
            <div class="col-lg-4 d-none d-lg-block">
                <div class="about-reader-tools">
                    
                    <!-- Font Resize & Controls -->
                    <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                        <span class="small fw-bold text-dark"><i class="fa-solid fa-font text-primary me-1"></i> ফন্ট সাইজ</span>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary" onclick="adjustFontSize(-1)" title="ছোট করুন">A-</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="resetFontSize()" title="ডিফল্ট">স্বাভাবিক</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="adjustFontSize(1)" title="বড় করুন">A+</button>
                        </div>
                    </div>

                    <!-- Table of Contents Links -->
                    <h6 class="fw-bold text-dark mb-2.5 small text-uppercase" style="letter-spacing: 0.5px;">
                        <i class="fa-solid fa-list-ol text-info me-1"></i> বিষয়সূচি ও অনুচ্ছেদ
                    </h6>
                    <nav class="d-flex flex-column gap-1 mb-4" id="aboutTocNav">
                        <a href="#sec-philosophy" class="about-toc-link active">১. মূল দর্শন ও বুদ্ধিবৃত্তিক সেতু</a>
                        <a href="#sec-journey" class="about-toc-link">২. দুই দশকের পথচলা ও বইসমূহ</a>
                        <a href="#sec-children" class="about-toc-link">৩. শিশুসাহিত্য ও আগামী প্রজন্ম</a>
                        <a href="#sec-research" class="about-toc-link">৪. গবেষণা ও সাহিত্যপত্র</a>
                        <a href="#sec-libraries" class="about-toc-link">৫. পাঠাগার আন্দোলন ও অনুদান</a>
                        <a href="#sec-boimela" class="about-toc-link">৬. অমর একুশে বইমেলা</a>
                        <a href="#sec-future" class="about-toc-link">৭. উত্তরবঙ্গের জ্ঞানভান্ডার ও অঙ্গীকার</a>
                    </nav>

                    <!-- Quick Share -->
                    <div class="pt-3 border-top">
                        <div class="small fw-bold text-muted mb-2">এই প্রত্যয়পত্রটি শেয়ার করুন:</div>
                        <div class="d-flex gap-2">
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url('/about')) }}" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill flex-fill">
                                <i class="fa-brands fa-facebook me-1"></i> ফেসবুক
                            </a>
                            <a href="https://api.whatsapp.com/send?text={{ urlencode($pageTitle . ' ' . url('/about')) }}" target="_blank" class="btn btn-outline-success btn-sm rounded-pill flex-fill">
                                <i class="fa-brands fa-whatsapp me-1"></i> হোয়াটসঅ্যাপ
                            </a>
                        </div>
                    </div>

                </div>
            </div>

</div>
</div>
@endsection

@push('scripts')
<script>
    // 1. Reading Progress Bar & TOC ScrollSpy
    let currentFontSize = 18.5;
    const articleEl = document.getElementById('aboutArticleContent');
    const progressBar = document.getElementById('aboutReadingProgressBar');
    const tocLinks = document.querySelectorAll('#aboutTocNav .about-toc-link');

    window.addEventListener('scroll', function() {
        // Update Progress Bar
        const scrollTop = window.scrollY || document.documentElement.scrollTop;
        const scrollHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        const scrollPercent = scrollHeight > 0 ? (scrollTop / scrollHeight) * 100 : 0;
        if (progressBar) {
            progressBar.style.width = scrollPercent + '%';
        }

        // Active TOC Highlighting
        const sections = ['sec-philosophy', 'sec-journey', 'sec-children', 'sec-research', 'sec-libraries', 'sec-boimela', 'sec-future'];
        let currentSection = '';
        sections.forEach(secId => {
            const secEl = document.getElementById(secId);
            if (secEl) {
                const rect = secEl.getBoundingClientRect();
                if (rect.top <= 160) {
                    currentSection = secId;
                }
            }
        });

        if (currentSection) {
            tocLinks.forEach(link => {
                if (link.getAttribute('href') === '#' + currentSection) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
        }
    });

    // 2. Interactive Font Size Scaler
    function adjustFontSize(delta) {
        currentFontSize = Math.min(24, Math.max(15, currentFontSize + delta));
        if (articleEl) {
            articleEl.style.fontSize = currentFontSize + 'px';
        }
    }

    function resetFontSize() {
        currentFontSize = 18.5;
        if (articleEl) {
            articleEl.style.fontSize = '18.5px';
        }
    }

    // 3. Copy Quote to Clipboard
    function copyQuoteText(btn, text) {
        navigator.clipboard.writeText(text).then(() => {
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-check me-1"></i>কপি হয়েছে!';
            btn.classList.replace('btn-outline-success', 'btn-success');
            setTimeout(() => {
                btn.innerHTML = originalHtml;
                btn.classList.replace('btn-success', 'btn-outline-success');
            }, 2000);
        });
    }
</script>
@endpush
