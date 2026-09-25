@extends('layouts.app')

@section('title', $campaign->title . ' — লেখকদের অংশগ্রহণ ফরম')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/event-campaign.css') }}">
    <style>
        :root {
            --writer-theme: {{ $campaign->theme_color ?: '#991b1b' }};
            --writer-theme-dark: #7f1d1d;
            --writer-theme-light: #fef2f2;
            --writer-accent: #f59e0b;
        }

        /* Hero Banner */
        .writer-hero-banner {
            background: linear-gradient(135deg, #450a0a 0%, #7f1d1d 50%, #991b1b 100%);
            border-radius: 20px;
            padding: 34px 28px;
            color: #ffffff;
            position: relative;
            overflow: hidden;
            box-shadow: 0 12px 35px rgba(127, 29, 29, 0.25);
            margin-bottom: 24px;
        }
        .writer-hero-banner::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, rgba(245, 158, 11, 0.2) 0%, rgba(255, 255, 255, 0) 70%);
            border-radius: 50%;
        }
        .writer-hero-banner::after {
            content: '✒️';
            position: absolute;
            right: 20px;
            bottom: -15px;
            font-size: 110px;
            opacity: 0.08;
            pointer-events: none;
        }

        /* Badges */
        .badge-festival {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: #ffffff;
            font-size: 12px;
            font-weight: 600;
            padding: 5px 14px;
            border-radius: 30px;
            backdrop-filter: blur(6px);
        }

        /* Progress Meter */
        .form-progress-wrap {
            background: rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            padding: 10px 14px;
            margin-top: 20px;
            backdrop-filter: blur(4px);
        }
        .form-progress-bar-bg {
            height: 6px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            overflow: hidden;
            margin-top: 6px;
        }
        .form-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #f59e0b, #10b981);
            width: 0%;
            transition: width 0.4s ease;
        }

        /* Main Form Card */
        .writer-form-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin-bottom: 30px;
        }
        .writer-form-body {
            padding: 28px 24px;
        }

        /* Section Headings */
        .section-tag-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 15px;
            font-weight: 700;
            color: #7f1d1d;
            background: #fef2f2;
            padding: 10px 16px;
            border-radius: 10px;
            border-left: 4px solid #991b1b;
            margin: 24px 0 16px 0;
        }
        .section-tag-head:first-child {
            margin-top: 0;
        }

        /* Inputs */
        .w-label {
            font-size: 13px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 6px;
            display: block;
        }
        .w-input, .w-select {
            border: 1.5px solid #cbd5e1;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 14px;
            color: #0f172a;
            transition: all 0.2s ease;
            background-color: #f8fafc;
            width: 100%;
        }
        .w-input:focus, .w-select:focus {
            background-color: #ffffff;
            border-color: #991b1b;
            box-shadow: 0 0 0 3.5px rgba(153, 27, 27, 0.12);
            outline: none;
        }

        /* Interactive Category Chips */
        .category-chips-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 6px;
        }
        .cat-chip {
            border: 1.5px solid #e2e8f0;
            background: #ffffff;
            color: #334155;
            font-size: 12.5px;
            font-weight: 600;
            padding: 6px 14px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.2s ease;
            user-select: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .cat-chip:hover {
            border-color: #991b1b;
            background: #fef2f2;
            color: #991b1b;
            transform: translateY(-1px);
        }
        .cat-chip.active {
            border-color: #991b1b;
            background: #991b1b;
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(153, 27, 27, 0.25);
        }

        /* Book Count Chips */
        .book-chip {
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #334155;
            font-size: 12px;
            font-weight: 600;
            padding: 5px 12px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .book-chip:hover {
            border-color: #991b1b;
            background: #fef2f2;
        }
        .book-chip.active {
            border-color: #991b1b;
            background: #7f1d1d;
            color: #ffffff;
        }

        /* Photo Upload Box */
        .author-photo-uploader {
            border: 2px dashed #cbd5e1;
            border-radius: 14px;
            padding: 14px;
            text-align: center;
            background: #f8fafc;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .author-photo-uploader:hover {
            border-color: #991b1b;
            background: #fef2f2;
        }
        .author-photo-preview {
            width: 76px;
            height: 76px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #991b1b;
            margin: 0 auto 8px auto;
            display: none;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* Live 3.5x5 Invitation Card Preview (Sticky on Desktop) */
        .delegate-card-preview {
            background: #d97706 url('{{ asset("images/events/rangpur_card_bg.jpg") }}') no-repeat center center;
            background-size: cover;
            border-radius: 16px;
            padding: 16px 16px;
            color: #1e1b4b;
            box-shadow: 0 12px 30px rgba(180, 83, 9, 0.25);
            border: 2px solid #b45309;
            position: relative;
            overflow: hidden;
            max-width: 320px;
            margin: 0 auto 16px auto;
            aspect-ratio: 3.5 / 5;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .invitation-overlay {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(4px);
            border-radius: 12px;
            padding: 12px 14px;
            border: 1px solid rgba(255, 255, 255, 0.9);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
        }
        .invitation-logo-space {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1.5px dashed #b45309;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .invitation-logo-badge {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #991b1b;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: bold;
            box-shadow: 0 2px 6px rgba(153, 27, 27, 0.3);
        }
        .delegate-avatar {
            width: 58px;
            height: 58px;
            border-radius: 50%;
            border: 2px solid #b45309;
            object-fit: cover;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #991b1b;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .delegate-badge-tag {
            background: linear-gradient(135deg, #991b1b 0%, #7f1d1d 100%);
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 14px;
            display: inline-block;
            box-shadow: 0 2px 6px rgba(153, 27, 27, 0.25);
        }

        /* Submit Button */
        .btn-submit-writer {
            background: linear-gradient(135deg, #991b1b 0%, #7f1d1d 100%);
            color: #ffffff;
            font-size: 16px;
            font-weight: 700;
            padding: 13px 40px;
            border-radius: 30px;
            border: none;
            box-shadow: 0 6px 20px rgba(153, 27, 27, 0.35);
            transition: all 0.25s ease;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-submit-writer:hover {
            background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(153, 27, 27, 0.45);
            color: #ffffff;
        }

        /* Operator Badge */
        .operator-pill {
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 6px;
            display: none;
        }
    </style>
@endpush

@section('content')
<div class="container py-4 py-md-5">

    {{-- HERO BANNER --}}
    <div class="writer-hero-banner">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <span class="badge-festival">
                        <i class="fa-solid fa-feather-pointed text-warning"></i> সাহিত্য উৎসব ও লিটিলম্যাগমেলা
                    </span>
                    <span class="badge bg-white text-dark fw-bold rounded-pill px-3 py-1" style="font-size: 11px;">
                        নিবন্ধন উন্মুক্ত
                    </span>
                </div>

                <h1 class="h2 fw-bold text-white mb-2" style="letter-spacing: -0.3px;">
                    {{ $campaign->title }}
                </h1>

                <p class="text-white-50 mb-0" style="font-size: 14.5px; line-height: 1.6;">
                    {{ $campaign->short_description ?: 'রংপুর সাহিত্য উৎসব ও লিটিলম্যাগমেলা ২০২৬-এ লেখকদের অংশগ্রহণের জন্য সংক্ষিপ্ত তথ্য ও নিবন্ধন ফরম।' }}
                </p>
            </div>

            <div class="col-lg-4 mt-3 mt-lg-0 text-lg-end">
                <div class="form-progress-wrap text-start">
                    <div class="d-flex justify-content-between align-items-center text-white small" style="font-size: 12px;">
                        <span><i class="fa-solid fa-list-check me-1 text-warning"></i> ফরম পূরণ অগ্রগতি</span>
                        <strong id="progressPercentText">০%</strong>
                    </div>
                    <div class="form-progress-bar-bg">
                        <div class="form-progress-fill" id="formProgressFill"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ALERTS --}}
    @if(session('error'))
        <div class="alert alert-danger rounded-3 py-2.5 px-3 mb-3 small d-flex align-items-center gap-2">
            <i class="fa-solid fa-circle-exclamation text-danger fs-5"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger rounded-3 py-2.5 px-3 mb-3 small">
            <div class="fw-bold mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i> অনুগ্রহ করে নিচের তথ্যগুলো পূরণ করুন:</div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- MAIN 2-COLUMN GRID --}}
    <div class="row g-4">

        {{-- LEFT COLUMN: THE INTERACTIVE FORM (8 Cols) --}}
        <div class="col-lg-8">
            <div class="writer-form-card">
                <div class="writer-form-body">
                    <form action="{{ route('event.submit', $campaign->slug) }}" method="POST" enctype="multipart/form-data" id="writerRegisterForm">
                        @csrf

                        {{-- Hidden Auto-Optimized Base64 Photo --}}
                        <input type="hidden" name="optimized_photo_data" id="optimizedPhotoData">

                        {{-- ==========================================
                             ১. সংক্ষিপ্ত তথ্য ও লেখক পরিচিতি
                             ========================================== --}}
                        <div class="section-tag-head">
                            <span><i class="fa-solid fa-user-pen me-1"></i> ১. সংক্ষিপ্ত তথ্য ও লেখক পরিচিতি</span>
                            <small class="text-muted fw-normal" style="font-size: 11px;">* চিহ্নিত ফিল্ড আবশ্যক</small>
                        </div>

                        <div class="row g-3">
                            {{-- লেখক নাম --}}
                            <div class="col-md-7">
                                <label class="w-label" for="writerName">
                                    লেখক নাম <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="name" id="writerName" class="w-input" placeholder="আপনার পূর্ণ নাম লিখুন" value="{{ old('name', $user?->name) }}" required oninput="updateLivePreview()">
                            </div>

                            {{-- মোবাইল নম্বর --}}
                            <div class="col-md-5">
                                <label class="w-label d-flex justify-content-between align-items-center" for="writerPhone">
                                    <span>মোবাইল নম্বর <span class="text-danger">*</span></span>
                                    <span id="operatorBadge" class="operator-pill">GP</span>
                                </label>
                                <input type="tel" name="phone" id="writerPhone" class="w-input font-monospace" placeholder="01XXXXXXXXX" value="{{ old('phone', $user?->phone) }}" required oninput="handlePhoneInput(this)">
                            </div>

                            {{-- ইমেইল --}}
                            <div class="col-md-6">
                                <label class="w-label" for="writerEmail">
                                    ইমেইল ঠিকানা <small class="text-muted fw-normal">(ঐচ্ছিক)</small>
                                </label>
                                <input type="email" name="email" id="writerEmail" class="w-input" placeholder="example@gmail.com" value="{{ old('email', $user?->email) }}" oninput="updateLivePreview()">
                            </div>

                            {{-- ছবি আপলোড (Canvas Auto-Optimized) --}}
                            <div class="col-md-6">
                                <label class="w-label">
                                    লেখকের ছবি / পোর্ট্রেট <small class="text-muted fw-normal">(কার্ডের জন্য • ঐচ্ছিক)</small>
                                </label>
                                <div class="author-photo-uploader" onclick="document.getElementById('writerPhotoInput').click()">
                                    <img id="photoPreviewThumb" class="author-photo-preview" alt="Author Photo">
                                    <div id="photoUploadPlaceholder">
                                        <i class="fa-solid fa-camera text-secondary fs-4 mb-1"></i>
                                        <div style="font-size: 11.5px; font-weight: 600; color: #7f1d1d;">ছবি যুক্ত করুন (অটো রিসাইজ হবে)</div>
                                    </div>
                                    <input type="file" id="writerPhotoInput" accept="image/*" class="d-none" onchange="optimizeWriterPhoto(this)">
                                </div>
                            </div>

                            {{-- লেখক ক্যাটাগরি --}}
                            <div class="col-12">
                                <label class="w-label" for="authorCategorySelect">
                                    লেখক ক্যাটাগরি (ক্লিক করে নির্বাচন করুন)
                                </label>
                                
                                {{-- Hidden Native Input --}}
                                <input type="hidden" name="author_category" id="authorCategoryInput" value="{{ old('author_category', 'কবিতা') }}">

                                {{-- Visual Category Chips --}}
                                <div class="category-chips-wrap" id="categoryChipsWrap">
                                    <div class="cat-chip active" data-val="কবিতা">✍️ কবিতা</div>
                                    <div class="cat-chip" data-val="কথাসাহিত্য / গল্প-উপন্যাস">📖 কথাসাহিত্য / উপন্যাস</div>
                                    <div class="cat-chip" data-val="প্রবন্ধ ও গবেষণা">🧐 প্রবন্ধ ও গবেষণা</div>
                                    <div class="cat-chip" data-val="শিশুসাহিত্য">🧒 শিশুসাহিত্য</div>
                                    <div class="cat-chip" data-val="ছড়া ও রম্য">🎤 ছড়া ও রম্য</div>
                                    <div class="cat-chip" data-val="নাটক ও চিত্রনাট্য">🎭 নাটক ও চিত্রনাট্য</div>
                                    <div class="cat-chip" data-val="অনুবাদ">🌍 অনুবাদ সাহিত্য</div>
                                    <div class="cat-chip" data-val="ছোটকাগজ সম্পাদনা">📰 লিটিলম্যাগাজিন সম্পাদনা</div>
                                    <div class="cat-chip" data-val="সাহিত্য সাংবাদিকতা">🖋️ সাহিত্য সাংবাদিকতা</div>
                                    <div class="cat-chip" data-val="অন্যান্য">✨ অন্যান্য</div>
                                </div>
                            </div>
                        </div>

                        {{-- ==========================================
                             ২. গ্রন্থ ও প্রকাশনা সংক্রান্ত তথ্য
                             ========================================== --}}
                        <div class="section-tag-head">
                            <span><i class="fa-solid fa-book-open me-1"></i> ২. গ্রন্থ ও প্রকাশনা সংক্রান্ত তথ্য</span>
                        </div>

                        <div class="row g-3">
                            {{-- প্রকাশিত গ্রন্থ সংখ্যা --}}
                            <div class="col-12">
                                <label class="w-label">প্রকাশিত গ্রন্থ সংখ্যা</label>
                                <input type="hidden" name="published_books_count" id="publishedBooksInput" value="{{ old('published_books_count', '০ (এখনও বই প্রকাশিত হয়নি)') }}">
                                
                                <div class="d-flex flex-wrap gap-2 mb-2" id="booksCountChips">
                                    <div class="book-chip active" data-count="০ (এখনও বই প্রকাশিত হয়নি)">০ (এখনও বই বের হয়নি)</div>
                                    <div class="book-chip" data-count="১টি গ্রন্থ">১টি</div>
                                    <div class="book-chip" data-count="২টি গ্রন্থ">২টি</div>
                                    <div class="book-chip" data-count="৩ থেকে ৫টি গ্রন্থ">৩-৫টি</div>
                                    <div class="book-chip" data-count="৬ থেকে ১০টি গ্রন্থ">৬-১০টি</div>
                                    <div class="book-chip" data-count="১০টির অধিক গ্রন্থ">১০+</div>
                                </div>
                            </div>

                            {{-- উল্লেখযোগ্য বইয়ের নাম --}}
                            <div class="col-12">
                                <label class="w-label" for="notableBooks">
                                    উল্লেখযোগ্য বইয়ের নাম ও প্রকাশনী
                                </label>
                                <textarea name="notable_books" id="notableBooks" rows="2" class="w-input" placeholder="যেমন: 'জলছাপ' (কাব্যগ্রন্থ), 'উত্তরের নদী' (গবেষণা)..." oninput="updateLivePreview()">{{ old('notable_books') }}</textarea>
                            </div>
                        </div>

                        {{-- ==========================================
                             ৩. ছোটকাগজ / লিটিলম্যাগাজিন সম্পাদনা
                             ========================================== --}}
                        <div class="section-tag-head" id="magazineSectionHead">
                            <span><i class="fa-solid fa-newspaper me-1"></i> ৩. ছোটকাগজ / লিটিলম্যাগাজিন সম্পাদনা (যদি থাকে)</span>
                        </div>

                        <div class="row g-3">
                            {{-- ছোটকাগজ সম্পাদক হলে পত্রিকার নাম --}}
                            <div class="col-md-8">
                                <label class="w-label" for="magazineName">
                                    ছোটকাগজ সম্পাদক হলে পত্রিকার নাম
                                </label>
                                <input type="text" name="magazine_name" id="magazineName" class="w-input" placeholder="সম্পাদিত লিটিলম্যাগ / সাময়িকীর নাম" value="{{ old('magazine_name') }}" oninput="updateLivePreview()">
                            </div>

                            {{-- প্রকাশিত সংখ্যা --}}
                            <div class="col-md-4">
                                <label class="w-label" for="magazineIssueCount">
                                    প্রকাশিত সংখ্যা
                                </label>
                                <input type="text" name="magazine_issue_count" id="magazineIssueCount" class="w-input" placeholder="যেমন: ৫টি সংখ্যা" value="{{ old('magazine_issue_count') }}">
                            </div>
                        </div>

                        {{-- ==========================================
                             ৪. ঠিকানা (Address Details)
                             ========================================== --}}
                        <div class="section-tag-head">
                            <span><i class="fa-solid fa-location-dot me-1"></i> ৪. ঠিকানা (Address Details)</span>
                        </div>

                        {{-- Hidden Full Address --}}
                        <input type="hidden" name="address" id="fullWriterAddress" value="{{ old('address') }}">

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="w-label" for="writerDivision">বিভাগ <span class="text-danger">*</span></label>
                                <select name="perm_division" id="writerDivision" class="w-select" required></select>
                            </div>
                            <div class="col-md-4">
                                <label class="w-label" for="writerDistrict">জেলা <span class="text-danger">*</span></label>
                                <select name="district" id="writerDistrict" class="w-select" required></select>
                            </div>
                            <div class="col-md-4">
                                <label class="w-label" for="writerUpazila">মহানগর / উপজেলা <span class="text-danger">*</span></label>
                                <select name="thana" id="writerUpazila" class="w-select" required></select>
                            </div>
                            <div class="col-md-5">
                                <label class="w-label" for="writerPostOffice">পোস্ট অফিস</label>
                                <select name="perm_post_office" id="writerPostOffice" class="w-select"></select>
                            </div>
                            <div class="col-md-7">
                                <label class="w-label" for="writerVillage">গ্রাম / এলাকা / বিস্তারিত ঠিকানা <span class="text-danger">*</span></label>
                                <input type="text" name="perm_village" id="writerVillage" class="w-input" placeholder="মহল্লা, সড়ক বা এলাকার নাম" required oninput="formatWriterAddress()">
                            </div>
                            <div class="col-12" id="fullAddressBadgeWrap" style="display: none;">
                                <div class="p-2 rounded bg-light border text-muted small font-monospace d-flex align-items-center gap-1.5">
                                    <i class="fa-solid fa-map-pin text-danger"></i> <span id="fullAddressText"></span>
                                </div>
                            </div>
                        </div>

                        {{-- SUBMIT BUTTON SECTION --}}
                        <div class="mt-4 pt-3 border-top text-center">
                            <div class="text-dark small fw-semibold mb-2" style="font-size: 13.5px;">
                                <i class="fa-solid fa-clock-rotate-left text-danger me-1"></i> ২৪ ঘণ্টা পর মোবাইল নম্বর দিয়ে লগিন করে কার্ড নম্বর ও আমন্ত্রণ কার্ড ডাউনলোড করুন।
                            </div>
                            <div class="text-muted small mb-3" style="font-size: 11.5px; line-height: 1.55; max-width: 620px; margin: 0 auto;">
                                পুনশ্চ: অনুষ্ঠান আয়োজক ফিরেদেখা সংগঠন। আইডিয়া প্রকাশন লেখক তথ্য সংগ্রহ ও নিবন্ধনে সহযোগিতা করছে মাত্র।
                            </div>
                            <button type="submit" id="submitWriterBtn" class="btn-submit-writer">
                                <i class="fa-solid fa-paper-plane"></i> নিবন্ধন সম্পন্ন করুন
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: LIVE DELEGATE PASS PREVIEW & EVENT HIGHLIGHTS (4 Cols) --}}
        <div class="col-lg-4">
            <div class="sticky-top" style="top: 24px; z-index: 10;">
                
                {{-- Live 3.5x5 Invitation Card (আমন্ত্রণ কার্ড) --}}
                <div class="delegate-card-preview mb-3">
                    
                    {{-- Card Inner Overlay Box --}}
                    <div class="invitation-overlay">
                        {{-- Top Logo & Festival Header --}}
                        <div class="invitation-logo-space">
                            <div class="d-flex align-items-center gap-2">
                                <div class="invitation-logo-badge">
                                    <i class="fa-solid fa-feather-pointed"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 11px; line-height: 1.2;">আইডিয়া প্রকাশন</div>
                                    <div class="text-muted font-monospace" style="font-size: 8.5px;">www.ideaabd.com</div>
                                </div>
                            </div>
                            <span class="delegate-badge-tag">
                                <i class="fa-solid fa-envelope-open-text me-1"></i> আমন্ত্রণ কার্ড
                            </span>
                        </div>

                        {{-- Event Title --}}
                        <div class="text-center mb-2">
                            <div class="fw-bold" style="font-size: 11.5px; color: #7f1d1d; line-height: 1.3;">
                                রংপুর সাহিত্য উৎসব ও লিটিলম্যাগমেলা ২০২৬
                            </div>
                            <div class="text-muted" style="font-size: 9.5px;">
                                সশ্রদ্ধ আমন্ত্রণ পত্র • ডেলিগেট কার্ড
                            </div>
                        </div>

                        {{-- Author Avatar & Info --}}
                        <div class="d-flex align-items-center gap-2.5 p-2 bg-white rounded-3 border mb-2 shadow-xs">
                            <div class="delegate-avatar" id="cardAvatarBox">
                                <img id="cardAvatarImg" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover; display: none;" alt="Avatar">
                                <span id="cardAvatarIcon">✍️</span>
                            </div>
                            <div style="min-width: 0;">
                                <div style="font-size: 9px; color: #64748b;">শ্রদ্ধেয় লেখক / অতিথি:</div>
                                <h6 class="fw-bold mb-0 text-dark text-truncate" id="cardNamePreview" style="font-size: 13px;">
                                    {{ $user?->name ?: 'আপনার লেখক নাম' }}
                                </h6>
                                <div class="text-danger small fw-bold" id="cardCategoryPreview" style="font-size: 11px;">
                                    কবিতা
                                </div>
                            </div>
                        </div>

                        {{-- District & Phone Meta --}}
                        <div class="p-2 rounded-3 mb-2" style="background: #fefce8; border: 1px solid #fef08a; font-size: 10.5px;">
                            <div class="d-flex justify-content-between mb-0.5">
                                <span class="text-muted">মোবাইল:</span>
                                <strong class="font-monospace text-dark" id="cardPhonePreview">01XXXXXXXXX</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">জেলা:</span>
                                <strong class="text-dark" id="cardDistrictPreview">রংপুর</strong>
                            </div>
                        </div>

                        {{-- Invitation Message --}}
                        <div class="text-center text-secondary" style="font-size: 9.5px; line-height: 1.45;">
                            সাহিত্য উৎসব ও লিটিলম্যাগমেলায় আপনার উপস্থিতি ও অংশগ্রহণ আমাদের সম্মানিত করবে।
                        </div>
                    </div>

                    {{-- Bottom Footer Bar --}}
                    <div class="text-center mt-2" style="background: rgba(255, 255, 255, 0.9); border-radius: 8px; padding: 4px 8px; font-size: 9.5px; color: #7f1d1d; font-weight: 700; border: 1px solid rgba(255,255,255,0.8);">
                        <i class="fa-solid fa-star text-warning me-1"></i> আমন্ত্রণ কার্ড • রংপুর সাহিত্য উৎসব
                    </div>
                </div>

                {{-- Literary Festival Highlights Card --}}
                <div class="card border-0 rounded-4 shadow-xs bg-white p-3.5">
                    <h6 class="fw-bold text-dark mb-2 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-circle-info text-danger"></i> উৎসবের তথ্যাবলী
                    </h6>
                    <ul class="list-unstyled small text-secondary mb-0" style="line-height: 1.7; font-size: 12.5px;">
                        <li class="mb-1.5"><i class="fa-solid fa-check-circle text-success me-1.5"></i> কবি, কথাসাহিত্যিক ও গবেষকদের সম্মিলন।</li>
                        <li class="mb-1.5"><i class="fa-solid fa-check-circle text-success me-1.5"></i> লিটিলম্যাগাজিন ও ছোটকাগজ প্রদর্শনী।</li>
                        <li class="mb-1.5"><i class="fa-solid fa-check-circle text-success me-1.5"></i> অংশগ্রহণকারীদের জন্য বিশেষ ডেলিগেট কার্ড।</li>
                        <li><i class="fa-solid fa-check-circle text-success me-1.5"></i> বিনামূল্যে অনলাইন নিবন্ধন।</li>
                    </ul>
                </div>

            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('js/bd-geo-data.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Initialize 4-Tier BD Geo Dropdown
    if (typeof initAddressChaining === 'function') {
        initAddressChaining(
            'writerDivision',
            'writerDistrict',
            'writerUpazila',
            'writerPostOffice',
            'writerVillage',
            'Rangpur', // Default division
            'Rangpur'  // Default district
        );
    } else {
        // Fallback immediate populator
        const divEl = document.getElementById('writerDivision');
        if (divEl && typeof window.BD_GEO !== 'undefined') {
            divEl.innerHTML = '<option value="">-- বিভাগ নির্বাচন করুন --</option>';
            Object.keys(window.BD_GEO.divisions).forEach(d => {
                const opt = document.createElement('option');
                opt.value = d;
                opt.textContent = d;
                if (d === 'Rangpur') opt.selected = true;
                divEl.appendChild(opt);
            });
            divEl.dispatchEvent(new Event('change'));
        }
    }

    // 2. Setup Address Formatting Watchers
    ['writerDivision', 'writerDistrict', 'writerUpazila', 'writerPostOffice', 'writerVillage'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', () => { formatWriterAddress(); updateProgress(); });
            el.addEventListener('input', () => { formatWriterAddress(); updateProgress(); });
        }
    });

    // 3. Category Chips Interactive Selector
    const chips = document.querySelectorAll('#categoryChipsWrap .cat-chip');
    const catInput = document.getElementById('authorCategoryInput');
    chips.forEach(chip => {
        chip.addEventListener('click', function () {
            chips.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            const val = this.getAttribute('data-val');
            if (catInput) catInput.value = val;

            // Highlight magazine section if selected
            const magHead = document.getElementById('magazineSectionHead');
            if (val.includes('লিটিলম্যাগাজিন') || val.includes('সম্পাদনা')) {
                if (magHead) {
                    magHead.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    magHead.style.background = '#fef3c7';
                    magHead.style.borderLeftColor = '#f59e0b';
                }
            } else {
                if (magHead) {
                    magHead.style.background = '#fef2f2';
                    magHead.style.borderLeftColor = '#991b1b';
                }
            }

            updateLivePreview();
            updateProgress();
        });
    });

    // 4. Books Count Chips Interactive Selector
    const bookChips = document.querySelectorAll('#booksCountChips .book-chip');
    const bookInput = document.getElementById('publishedBooksInput');
    bookChips.forEach(bChip => {
        bChip.addEventListener('click', function () {
            bookChips.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            const cnt = this.getAttribute('data-count');
            if (bookInput) bookInput.value = cnt;
            updateProgress();
        });
    });

    // 5. Restore Draft from LocalStorage if available
    restoreDraft();

    // 6. Setup Live Auto-Save to LocalStorage
    ['writerName', 'writerPhone', 'writerEmail', 'notableBooks', 'magazineName', 'magazineIssueCount', 'writerVillage'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', () => {
                saveDraft();
                updateLivePreview();
                updateProgress();
            });
        }
    });

    formatWriterAddress();
    updateLivePreview();
    updateProgress();
});

// Format Full Bangla Address
function formatWriterAddress() {
    const div = document.getElementById('writerDivision')?.value || '';
    const dist = document.getElementById('writerDistrict')?.value || '';
    const upz = document.getElementById('writerUpazila')?.value || '';
    const po = document.getElementById('writerPostOffice')?.value || '';
    const vil = document.getElementById('writerVillage')?.value || '';

    const parts = [];
    if (vil) parts.push(vil);
    if (po) parts.push('ডাকঘর: ' + po);
    if (upz) parts.push('উপজেলা/থানা: ' + upz);
    if (dist) parts.push('জেলা: ' + dist);
    if (div) parts.push('বিভাগ: ' + div);

    const full = parts.join(', ');
    const hiddenAddr = document.getElementById('fullWriterAddress');
    if (hiddenAddr) hiddenAddr.value = full;

    const badgeWrap = document.getElementById('fullAddressBadgeWrap');
    const badgeText = document.getElementById('fullAddressText');
    if (badgeWrap && badgeText && full) {
        badgeText.textContent = full;
        badgeWrap.style.display = 'block';
    }

    const distPreview = document.getElementById('cardDistrictPreview');
    if (distPreview) distPreview.textContent = dist || 'রংপুর';
}

// Live Phone Formatting & Operator Detector
function handlePhoneInput(input) {
    let val = input.value.replace(/[^0-9+]/g, '');
    if (val.startsWith('+880')) {
        val = '0' + val.substring(4);
    } else if (val.startsWith('880')) {
        val = '0' + val.substring(3);
    }
    input.value = val;

    const opBadge = document.getElementById('operatorBadge');
    if (opBadge) {
        if (val.startsWith('017') || val.startsWith('013')) {
            opBadge.textContent = 'GP / Skitto';
            opBadge.style.display = 'inline-block';
            opBadge.style.background = '#dcfce7';
            opBadge.style.color = '#15803d';
        } else if (val.startsWith('018')) {
            opBadge.textContent = 'Robi';
            opBadge.style.display = 'inline-block';
            opBadge.style.background = '#fee2e2';
            opBadge.style.color = '#b91c1c';
        } else if (val.startsWith('019') || val.startsWith('014')) {
            opBadge.textContent = 'Banglalink';
            opBadge.style.display = 'inline-block';
            opBadge.style.background = '#fef3c7';
            opBadge.style.color = '#b45309';
        } else if (val.startsWith('016')) {
            opBadge.textContent = 'Airtel';
            opBadge.style.display = 'inline-block';
            opBadge.style.background = '#e0e7ff';
            opBadge.style.color = '#4338ca';
        } else if (val.startsWith('015')) {
            opBadge.textContent = 'Teletalk';
            opBadge.style.display = 'inline-block';
            opBadge.style.background = '#cffafe';
            opBadge.style.color = '#0e7490';
        } else {
            opBadge.style.display = 'none';
        }
    }

    updateLivePreview();
}

// Client-Side Canvas Image Auto-Optimizer for Author Photo
function optimizeWriterPhoto(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];

    const reader = new FileReader();
    reader.onload = function (e) {
        const img = new Image();
        img.onload = function () {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const size = 320;

            canvas.width = size;
            canvas.height = size;

            // Crop to square center
            const minDim = Math.min(img.width, img.height);
            const startX = (img.width - minDim) / 2;
            const startY = (img.height - minDim) / 2;

            ctx.drawImage(img, startX, startY, minDim, minDim, 0, 0, size, size);

            const optimizedBase64 = canvas.toDataURL('image/jpeg', 0.85);

            // Set hidden field
            document.getElementById('optimizedPhotoData').value = optimizedBase64;

            // Update previews
            const thumb = document.getElementById('photoPreviewThumb');
            thumb.src = optimizedBase64;
            thumb.style.display = 'block';
            document.getElementById('photoUploadPlaceholder').style.display = 'none';

            const cardAvatarImg = document.getElementById('cardAvatarImg');
            const cardAvatarIcon = document.getElementById('cardAvatarIcon');
            if (cardAvatarImg && cardAvatarIcon) {
                cardAvatarImg.src = optimizedBase64;
                cardAvatarImg.style.display = 'block';
                cardAvatarIcon.style.display = 'none';
            }

            updateProgress();
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}

// Update Live Delegate Card Preview
function updateLivePreview() {
    const name = document.getElementById('writerName')?.value.trim();
    const phone = document.getElementById('writerPhone')?.value.trim();
    const cat = document.getElementById('authorCategoryInput')?.value || 'কবিতা';

    const cardName = document.getElementById('cardNamePreview');
    if (cardName) cardName.textContent = name || 'আপনার লেখক নাম';

    const cardCat = document.getElementById('cardCategoryPreview');
    if (cardCat) cardCat.textContent = cat;

    const cardPh = document.getElementById('cardPhonePreview');
    if (cardPh) cardPh.textContent = phone || '01XXXXXXXXX';
}

// Real-Time Form Progress Bar
function updateProgress() {
    let score = 0;
    const total = 5;

    if (document.getElementById('writerName')?.value.trim()) score++;
    if (document.getElementById('writerPhone')?.value.trim().length >= 11) score++;
    if (document.getElementById('authorCategoryInput')?.value) score++;
    if (document.getElementById('writerDistrict')?.value) score++;
    if (document.getElementById('writerVillage')?.value.trim()) score++;

    const percent = Math.round((score / total) * 100);
    const fill = document.getElementById('formProgressFill');
    const text = document.getElementById('progressPercentText');

    if (fill) fill.style.width = percent + '%';
    if (text) text.textContent = percent + '%';
}

// Draft Auto-Save in LocalStorage
function saveDraft() {
    try {
        const draft = {
            name: document.getElementById('writerName')?.value || '',
            phone: document.getElementById('writerPhone')?.value || '',
            email: document.getElementById('writerEmail')?.value || '',
            notable_books: document.getElementById('notableBooks')?.value || '',
            magazine_name: document.getElementById('magazineName')?.value || '',
            magazine_issue_count: document.getElementById('magazineIssueCount')?.value || '',
            village: document.getElementById('writerVillage')?.value || '',
        };
        localStorage.setItem('rsutshab_writer_draft', JSON.stringify(draft));
    } catch (e) {}
}

function restoreDraft() {
    try {
        const saved = localStorage.getItem('rsutshab_writer_draft');
        if (!saved) return;
        const data = JSON.parse(saved);

        if (data.name && !document.getElementById('writerName').value) document.getElementById('writerName').value = data.name;
        if (data.phone && !document.getElementById('writerPhone').value) document.getElementById('writerPhone').value = data.phone;
        if (data.email && !document.getElementById('writerEmail').value) document.getElementById('writerEmail').value = data.email;
        if (data.notable_books && !document.getElementById('notableBooks').value) document.getElementById('notableBooks').value = data.notable_books;
        if (data.magazine_name && !document.getElementById('magazineName').value) document.getElementById('magazineName').value = data.magazine_name;
        if (data.magazine_issue_count && !document.getElementById('magazineIssueCount').value) document.getElementById('magazineIssueCount').value = data.magazine_issue_count;
        if (data.village && !document.getElementById('writerVillage').value) document.getElementById('writerVillage').value = data.village;
    } catch (e) {}
}
</script>
@endpush
