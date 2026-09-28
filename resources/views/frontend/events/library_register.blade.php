@extends('layouts.app')

@section('title', 'আইডিয়া পাঠাগার — বই অনুদান আবেদন ফরম')

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/library-grant.css') }}">
    <style>
        body, .library-grant-wrapper, .lib-main-title, .lib-field-label, .lib-input, .lib-select, .genre-chip-item {
            font-family: 'Hind Siliguri', 'Inter', system-ui, -apple-system, sans-serif;
        }
    </style>
@endpush

@section('content')
<div class="container py-3 py-md-4">
    <div class="library-grant-wrapper">

        {{-- নোটিফিকেশন এলার্ট --}}
        @if(session('error'))
            <div class="alert alert-danger rounded-3 py-2.5 px-3 mb-3 small d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-danger flex-shrink-0"></i>
                <div><strong>ত্রুটি:</strong> {{ session('error') }}</div>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info rounded-3 py-2.5 px-3 mb-3 small d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-info text-info flex-shrink-0"></i>
                <div>{{ session('info') }}</div>
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="alert alert-danger rounded-3 py-2.5 px-3 mb-3 small">
                <div class="fw-bold mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i> অনুগ্রহ করে নিচের ত্রুটিগুলো সংশোধন করুন:</div>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('event.submit', $campaign->slug) }}" method="POST" enctype="multipart/form-data" id="dynamicLibraryForm" class="library-card-box">
            @csrf
            
            {{-- অপ্টিমাইজড ফটো বেস৬৪ ডাটা --}}
            <input type="hidden" name="optimized_photo_data" id="optimizedPhotoData">

            @php
                $siteLogo = \App\Support\SiteSetting::logoUrl() ?: (\App\Support\SiteSetting::loginLogoUrl() ?: asset('images/logo.png'));
            @endphp

            {{-- হেডার ব্যানার --}}
            <div class="lib-header-banner">
                <div class="lib-header-inner">
                    <div class="lib-title-area">
                        <div class="lib-logo-wrapper" title="আইডিয়া পাঠাগার">
                            <img src="{{ $siteLogo }}" alt="আইডিয়া পাঠাগার" class="lib-header-logo" onerror="this.src='{{ asset('images/logo.png') }}';">
                        </div>
                        <div>
                            <h1 class="lib-main-title">আইডিয়া পাঠাগার</h1>
                            <div class="lib-sub-title">বই অনুদান আবেদন ফরম</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ফরম পূরণ অগ্রগতি বার --}}
            <div class="lib-progress-wrap">
                <div class="lib-progress-info">
                    <i class="fa-solid fa-list-check text-success"></i>
                    <span>আবেদন ফরম পূরণ অগ্রগতি:</span>
                </div>
                <div class="lib-progress-track">
                    <div class="lib-progress-fill" id="formProgressFill"></div>
                </div>
                <div class="lib-progress-percent" id="formProgressPercent">০%</div>
            </div>

            {{-- সেকশন ১: পাঠাগার ও প্রতিষ্ঠানের তথ্য --}}
            <div class="lib-section-head">
                <div class="sec-left">
                    <i class="fa-solid fa-landmark"></i>
                    <span>১. পাঠাগার ও প্রতিষ্ঠানের তথ্য</span>
                </div>
            </div>
            <div class="lib-form-body">
                <div class="lib-grid-row">
                    <div class="lib-col-8">
                        <div class="lib-grid-row">
                            <div class="lib-col-12">
                                <div class="lib-field-group">
                                    <label class="lib-field-label">
                                        <span>পাঠাগারের পূর্ণ নাম <span class="text-danger">*</span></span>
                                    </label>
                                    <input type="text" name="institution_or_org" class="lib-input" placeholder="পাঠাগারের পূর্ণ নাম লিখুন" value="{{ old('institution_or_org') }}" required autocomplete="off">
                                </div>
                            </div>
                            <div class="lib-col-12">
                                <div class="lib-field-group">
                                    <label class="lib-field-label">
                                        <span>পাঠাগারের ধরন <span class="text-danger">*</span></span>
                                    </label>
                                    <select name="library_type" class="lib-select" required>
                                        <option value="গণপাঠাগার / পাবলিক লাইব্রেরি" {{ old('library_type') === 'গণপাঠাগার / পাবলিক লাইব্রেরি' || old('library_type') === 'Public / Community Library' ? 'selected' : '' }}>গণপাঠাগার / পাবলিক লাইব্রেরি</option>
                                        <option value="বিদ্যালয় লাইব্রেরি" {{ old('library_type') === 'বিদ্যালয় লাইব্রেরি' || old('library_type') === 'School Library' ? 'selected' : '' }}>বিদ্যালয় লাইব্রেরি</option>
                                        <option value="কলেজ লাইব্রেরি" {{ old('library_type') === 'কলেজ লাইব্রেরি' || old('library_type') === 'College Library' ? 'selected' : '' }}>কলেজ লাইব্রেরি</option>
                                        <option value="মাদরাসা পাঠাগার" {{ old('library_type') === 'মাদরাসা পাঠাগার' || old('library_type') === 'Madrasa Library' ? 'selected' : '' }}>মাদরাসা পাঠাগার</option>
                                        <option value="বিশ্ববিদ্যালয় লাইব্রেরি" {{ old('library_type') === 'বিশ্ববিদ্যালয় লাইব্রেরি' || old('library_type') === 'University / Departmental Library' ? 'selected' : '' }}>বিশ্ববিদ্যালয় লাইব্রেরি</option>
                                        <option value="ক্লাব / সামাজিক পাঠাগার" {{ old('library_type') === 'ক্লাব / সামাজিক পাঠাগার' || old('library_type') === 'Youth Club / Organization' ? 'selected' : '' }}>ক্লাব / সামাজিক পাঠাগার</option>
                                        <option value="অন্যান্য পাঠাগার" {{ old('library_type') === 'অন্যান্য পাঠাগার' || old('library_type') === 'Other Library' ? 'selected' : '' }}>অন্যান্য পাঠাগার</option>
                                    </select>
                                </div>
                            </div>
                            <div class="lib-col-6">
                                <div class="lib-field-group">
                                    <label class="lib-field-label">
                                        <span>সরকারি / গ্রন্থকেন্দ্র নিবন্ধন নম্বর</span>
                                    </label>
                                    <input type="text" name="reg_no" class="lib-input" placeholder="নিবন্ধন নম্বর লিখুন (যদি থাকে)" value="{{ old('reg_no') }}">
                                </div>
                            </div>
                            <div class="lib-col-6">
                                <div class="lib-field-group">
                                    <label class="lib-field-label">
                                        <span>প্রতিষ্ঠা সাল</span>
                                    </label>
                                    <input type="text" name="established_year" class="lib-input" placeholder="যেমন: ২০১৮" value="{{ old('established_year') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- সাইনবোর্ড / ভবনের ছবি --}}
                    <div class="lib-col-4">
                        <div class="lib-photo-card">
                            <label class="lib-field-label mb-2 text-center">
                                <span>পাঠাগারের ছবি</span>
                            </label>
                            <div class="lib-photo-box" id="libPhotoBox" title="ছবি নির্বাচন বা ড্রপ করুন">
                                <button type="button" class="lib-photo-remove" id="libPhotoRemove" title="ছবি বাদ দিন">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                                <img id="libPreviewImg" class="lib-photo-preview" alt="পাঠাগারের ছবি">
                                <div id="libUploadPrompt" class="lib-photo-placeholder">
                                    <div class="icon"><i class="fa-solid fa-camera"></i></div>
                                    <div class="prompt-title">ছবি আপলোড করুন</div>
                                </div>
                            </div>
                            <div id="libOptBadge" class="lib-photo-badge">✓ ছবি প্রস্তুত হয়েছে</div>
                            <input type="file" id="libPhotoInput" name="student_photo" accept="image/*" class="d-none">
                        </div>
                    </div>
                </div>
            </div>

            {{-- সেকশন ২: দায়িত্বপ্রাপ্ত প্রতিনিধি ও যোগাযোগের তথ্য --}}
            <div class="lib-section-head">
                <div class="sec-left">
                    <i class="fa-solid fa-id-badge"></i>
                    <span>২. প্রতিনিধি ও যোগাযোগের তথ্য</span>
                </div>
            </div>
            <div class="lib-form-body">
                <div class="lib-grid-row">
                    <div class="lib-col-6">
                        <div class="lib-field-group">
                            <label class="lib-field-label">
                                <span>প্রতিনিধির নাম <span class="text-danger">*</span></span>
                            </label>
                            <input type="text" name="name" class="lib-input" placeholder="আবেদনকারী প্রতিনিধির পূর্ণ নাম" value="{{ old('name', $user?->name) }}" required>
                        </div>
                    </div>
                    <div class="lib-col-6">
                        <div class="lib-field-group">
                            <label class="lib-field-label">
                                <span>পদবি <span class="text-danger">*</span></span>
                            </label>
                            <select name="designation_or_class" class="lib-select" required>
                                <option value="সাধারণ সম্পাদক" {{ old('designation_or_class') === 'সাধারণ সম্পাদক' || old('designation_or_class') === 'General Secretary' ? 'selected' : '' }}>সাধারণ সম্পাদক</option>
                                <option value="সভাপতি" {{ old('designation_or_class') === 'সভাপতি' || old('designation_or_class') === 'President' ? 'selected' : '' }}>সভাপতি</option>
                                <option value="প্রতিষ্ঠাতা / পরিচালক" {{ old('designation_or_class') === 'প্রতিষ্ঠাতা / পরিচালক' || old('designation_or_class') === 'Director / Founder' ? 'selected' : '' }}>প্রতিষ্ঠাতা / পরিচালক</option>
                                <option value="প্রধান শিক্ষক / অধ্যক্ষ" {{ old('designation_or_class') === 'প্রধান শিক্ষক / অধ্যক্ষ' || old('designation_or_class') === 'Principal / Headmaster' ? 'selected' : '' }}>প্রধান শিক্ষক / অধ্যক্ষ</option>
                                <option value="গ্রন্থাগারিক" {{ old('designation_or_class') === 'গ্রন্থাগারিক' || old('designation_or_class') === 'Librarian' ? 'selected' : '' }}>গ্রন্থাগারিক</option>
                                <option value="আহ্বায়ক / সদস্য" {{ old('designation_or_class') === 'আহ্বায়ক / সদস্য' || old('designation_or_class') === 'Member / Convener' ? 'selected' : '' }}>আহ্বায়ক / সদস্য</option>
                            </select>
                        </div>
                    </div>
                    <div class="lib-col-6">
                        <div class="lib-field-group">
                            <label class="lib-field-label">
                                <span>মোবাইল নম্বর <span class="text-danger">*</span></span>
                            </label>
                            <div class="lib-phone-group">
                                <span class="lib-phone-prefix"><i class="fa-solid fa-phone"></i> +৮৮</span>
                                <input type="tel" name="phone" id="libPhone" class="lib-input font-monospace" placeholder="017XXXXXXXX" value="{{ old('phone', $user?->phone) }}" required maxlength="11">
                            </div>
                        </div>
                    </div>
                    <div class="lib-col-6">
                        <div class="lib-field-group">
                            <label class="lib-field-label">
                                <span>বিকল্প মোবাইল নম্বর</span>
                            </label>
                            <div class="lib-phone-group">
                                <span class="lib-phone-prefix"><i class="fa-solid fa-mobile-screen"></i> +৮৮</span>
                                <input type="tel" name="guardian_phone" class="lib-input font-monospace" placeholder="018XXXXXXXX" value="{{ old('guardian_phone') }}" maxlength="11">
                            </div>
                        </div>
                    </div>
                    <div class="lib-col-6">
                        <div class="lib-field-group">
                            <label class="lib-field-label">
                                <span>ইমেইল ঠিকানা</span>
                            </label>
                            <input type="email" name="email" class="lib-input" placeholder="library@example.com" value="{{ old('email', $user?->email) }}">
                        </div>
                    </div>
                    <div class="lib-col-6">
                        <div class="lib-field-group">
                            <label class="lib-field-label">
                                <span>জাতীয় পরিচয়পত্র (NID) নম্বর</span>
                            </label>
                            <input type="text" name="nid" class="lib-input font-monospace" placeholder="জাতীয় পরিচয়পত্র নম্বর" value="{{ old('nid') }}">
                        </div>
                    </div>

                    {{-- পরিচালনা কমিটির তথ্য --}}
                    <div class="lib-col-12 mt-2 pt-2 border-top">
                        <div class="d-flex align-items-center gap-1.5 text-success fw-bold small mb-2">
                            <i class="fa-solid fa-users-gear"></i>
                            <span>পরিচালনা কমিটি <span class="text-danger">*</span>:</span>
                        </div>
                    </div>
                    <div class="lib-col-6">
                        <div class="lib-field-group">
                            <label class="lib-field-label">
                                <span>সভাপতির নাম <span class="text-danger">*</span></span>
                            </label>
                            <input type="text" name="president_name" class="lib-input" placeholder="সভাপতির নাম লিখুন" value="{{ old('president_name') }}" required>
                        </div>
                    </div>
                    <div class="lib-col-6">
                        <div class="lib-field-group">
                            <label class="lib-field-label">
                                <span>সভাপতির মোবাইল নম্বর <span class="text-danger">*</span></span>
                            </label>
                            <div class="lib-phone-group">
                                <span class="lib-phone-prefix"><i class="fa-solid fa-phone"></i> +৮৮</span>
                                <input type="tel" name="president_phone" class="lib-input font-monospace" placeholder="01XXXXXXXXX" value="{{ old('president_phone') }}" required maxlength="11">
                            </div>
                        </div>
                    </div>
                    <div class="lib-col-6">
                        <div class="lib-field-group">
                            <label class="lib-field-label">
                                <span>সাধারণ সম্পাদকের নাম <span class="text-danger">*</span></span>
                            </label>
                            <input type="text" name="secretary_name" class="lib-input" placeholder="সাধারণ সম্পাদকের নাম লিখুন" value="{{ old('secretary_name') }}" required>
                        </div>
                    </div>
                    <div class="lib-col-6">
                        <div class="lib-field-group">
                            <label class="lib-field-label">
                                <span>সাধারণ সম্পাদকের মোবাইল নম্বর <span class="text-danger">*</span></span>
                            </label>
                            <div class="lib-phone-group">
                                <span class="lib-phone-prefix"><i class="fa-solid fa-phone"></i> +৮৮</span>
                                <input type="tel" name="secretary_phone" class="lib-input font-monospace" placeholder="01XXXXXXXXX" value="{{ old('secretary_phone') }}" required maxlength="11">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- সেকশন ৩: অবস্থান ও পূর্ণ ডাক ঠিকানা --}}
            <div class="lib-section-head">
                <div class="sec-left">
                    <i class="fa-solid fa-map-location-dot"></i>
                    <span>৩. পাঠাগারের অবস্থান ও পূর্ণ ডাক ঠিকানা</span>
                </div>
            </div>
            <div class="lib-form-body">
                <div class="lib-grid-row">
                    <div class="lib-col-6">
                        <div class="lib-field-group">
                            <label class="lib-field-label">
                                <span>বিভাগ <span class="text-danger">*</span></span>
                            </label>
                            <select name="division" id="libDivision" class="lib-select" data-old="{{ old('division') }}" required>
                                <option value="">-- বিভাগ নির্বাচন করুন --</option>
                            </select>
                        </div>
                    </div>
                    <div class="lib-col-6">
                        <div class="lib-field-group">
                            <label class="lib-field-label">
                                <span>জেলা <span class="text-danger">*</span></span>
                            </label>
                            <select name="district" id="libDistrict" class="lib-select" data-old="{{ old('district') }}" required>
                                <option value="">-- জেলা নির্বাচন করুন --</option>
                            </select>
                        </div>
                    </div>
                    <div class="lib-col-6">
                        <div class="lib-field-group">
                            <label class="lib-field-label">
                                <span>উপজেলা / থানা <span class="text-danger">*</span></span>
                            </label>
                            <select name="thana" id="libUpazila" class="lib-select" data-old="{{ old('thana') }}" required>
                                <option value="">-- উপজেলা / থানা নির্বাচন করুন --</option>
                            </select>
                        </div>
                    </div>
                    <div class="lib-col-6">
                        <div class="lib-field-group">
                            <label class="lib-field-label">
                                <span>ডাকঘর ও পোস্ট কোড</span>
                            </label>
                            <select name="post_office" id="libPostOffice" class="lib-select" data-old="{{ old('post_office') }}">
                                <option value="">-- পোস্ট অফিস নির্বাচন করুন --</option>
                            </select>
                        </div>
                    </div>
                    <div class="lib-col-12">
                        <div class="lib-field-group">
                            <label class="lib-field-label">
                                <span>বিস্তারিত ঠিকানা <span class="text-danger">*</span></span>
                            </label>
                            <input type="text" name="address" id="libAddress" class="lib-input" placeholder="গ্রাম/মহল্লা, সড়ক নম্বর, বাজার বা সুনির্দিষ্ট অবস্থান লিখুন..." value="{{ old('address') }}" required>
                        </div>
                    </div>
                </div>
            </div>

            {{-- সেকশন ৪: পরিসংখ্যান ও বইয়ের চাহিদা --}}
            <div class="lib-section-head">
                <div class="sec-left">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>৪. পরিসংখ্যান ও বইয়ের চাহিদা</span>
                </div>
            </div>
            <div class="lib-form-body">
                <div class="lib-grid-row">
                    <div class="lib-col-6">
                        <div class="lib-field-group">
                            <label class="lib-field-label">
                                <span>দৈনিক পাঠক সংখ্যা</span>
                            </label>
                            <input type="text" name="reader_count" class="lib-input" placeholder="যেমন: ৪৫ জন" value="{{ old('reader_count') }}">
                        </div>
                    </div>
                    <div class="lib-col-6">
                        <div class="lib-field-group">
                            <label class="lib-field-label">
                                <span>বর্তমানে মোট বই সংখ্যা</span>
                            </label>
                            <input type="text" name="current_book_count" class="lib-input" placeholder="যেমন: ৬৫০ টি" value="{{ old('current_book_count') }}">
                        </div>
                    </div>
                    <div class="lib-col-12">
                        <div class="lib-field-group">
                            <label class="lib-field-label">
                                <span>বই গ্রহণের মাধ্যম <span class="text-danger">*</span></span>
                            </label>
                            <select name="delivery_method" class="lib-select" required>
                                <option value="সরাসরি আনুষ্ঠানিক গ্রহণ করতে হবে (বিকল্প নেই)" selected>সরাসরি আনুষ্ঠানিক গ্রহণ করতে হবে (বিকল্প নেই)</option>
                            </select>
                        </div>
                    </div>
                    <div class="lib-col-12">
                        <div class="lib-field-group">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="lib-field-label mb-0">
                                    <span>প্রত্যাশিত বইয়ের বিষয়সমূহ</span>
                                </label>
                                <span id="selectedGenreCount" class="badge bg-success-subtle text-success border border-success-subtle"></span>
                            </div>
                            <div class="genre-chips-container">
                                @php
                                    $genreOptions = [
                                        'Literature & Novels'       => 'সাহিত্য ও উপন্যাস',
                                        'Liberation War & History'  => 'মুক্তিযুদ্ধ ও ইতিহাস',
                                        'Poetry & Rhymes'           => 'কবিতা ও ছড়া',
                                        'Children & Teenagers'      => 'শিশু-কিশোর সাহিত্য',
                                        'Science & Technology'      => 'বিজ্ঞান ও প্রযুক্তি',
                                        'Career & Self-Development' => 'ক্যারিয়ার ও আত্মউন্নয়ন',
                                        'Islamic & Religious'       => 'ইসলামিক ও নৈতিক শিক্ষা',
                                        'General Knowledge'         => 'সাধারণ জ্ঞান ও রেফারেন্স',
                                        'Biography & Memoirs'       => 'জীবনী ও স্মৃতিকথা'
                                    ];
                                    $oldGenres = old('preferred_genres', []);
                                @endphp
                                @foreach($genreOptions as $key => $bnLabel)
                                    <label class="genre-chip-item {{ in_array($key, $oldGenres) || in_array($bnLabel, $oldGenres) ? 'active' : '' }}">
                                        <input type="checkbox" name="preferred_genres[]" value="{{ $bnLabel }}" {{ in_array($key, $oldGenres) || in_array($bnLabel, $oldGenres) ? 'checked' : '' }}>
                                        <span>{{ $bnLabel }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- সেকশন ৫: অনুদানের প্রয়োজনীয়তা ও উদ্দেশ্য --}}
            <div class="lib-section-head">
                <div class="sec-left">
                    <i class="fa-solid fa-feather-pointed"></i>
                    <span>৫. অনুদানের প্রয়োজনীয়তা ও উদ্দেশ্য</span>
                </div>
                <span id="wordCounter" class="lib-word-badge badge-safe">০ / ৮০ শব্দ</span>
            </div>
            <div class="lib-form-body">
                <div class="lib-field-group">
                    <textarea name="scholarship_reason" id="reasonText" rows="3" class="lib-textarea" placeholder="আপনার পাঠাগারের বর্তমান কার্যক্রম এবং বিনামূল্যে বই অনুদানের প্রয়োজনীয়তা সংক্ষেপে লিখুন (সর্বোচ্চ ৮০ শব্দ)...">{{ old('scholarship_reason') }}</textarea>
                    <div id="wordLimitAlert" class="text-danger small mt-1 d-none font-monospace fw-bold">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> সতর্কবার্তা: ৮০ শব্দের সীমা অতিক্রম করেছে। অনুগ্রহ করে বিবরণটি সংক্ষিপ্ত করুন।
                    </div>
                </div>
            </div>

            {{-- নীতিমালার স্পষ্ট বার্তা --}}
            <div class="alert alert-warning border-0 rounded-4 py-2.5 px-3.5 mb-3 d-flex align-items-center gap-2.5 small text-dark shadow-xs" style="background-color: #fef3c7;">
                <i class="fa-solid fa-circle-exclamation text-warning-emphasis fs-5 flex-shrink-0"></i>
                <div class="fw-semibold">
                    আইডিয়া পাঠাগার নিজ উদ্যোগে বই বিতরণ করে। বই প্রদানের ক্ষেত্রে যে কোনো সিদ্ধান্ত গ্রহণের ক্ষমতা সংরক্ষণ করে।
                </div>
            </div>

            {{-- সেকশন ৬: অঙ্গীকারনামা ও সাবমিশন --}}
            <div class="lib-submission-footer">
                <div>
                    <label for="agreeCheck" class="lib-declaration-check">
                        <input type="checkbox" id="agreeCheck" name="declaration_agreed" checked required>
                        <span>আমি ঘোষণা করছি যে, উপরে প্রদত্ত সমস্ত তথ্য সম্পূর্ণ সত্য ও সঠিক। <span class="text-danger">*</span></span>
                    </label>
                </div>

                <button type="submit" id="submitBtn" class="lib-btn-submit">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>আবেদন জমা দিন</span>
                </button>
            </div>

        </form>

    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/bd-geo-data.js') }}"></script>
    <script src="{{ asset('js/library-grant.js') }}"></script>
@endpush
