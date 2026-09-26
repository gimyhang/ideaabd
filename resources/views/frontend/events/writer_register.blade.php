@extends('layouts.app')

@php
    $cardDesign = $campaign->card_design ?? [];

    // Background & Canvas
    $cBgImg            = $cardDesign['bg_image'] ?? null;
    $cBgColor          = $cardDesign['bg_color'] ?? '#c98c21';
    $cBgOverlayColor   = $cardDesign['bg_overlay_color'] ?? 'rgba(0,0,0,0.45)';
    $cBgOverlayOpacity = isset($cardDesign['bg_overlay_opacity']) ? intval($cardDesign['bg_overlay_opacity']) : 0;
    $cBgBlur           = isset($cardDesign['bg_blur']) ? intval($cardDesign['bg_blur']) : 0;
    $cFontFamily       = $cardDesign['font_family'] ?? 'Hind Siliguri';

    // Logos
    $cLogoImg          = $cardDesign['logo_image'] ?? null;
    $cLogoSize         = intval($cardDesign['logo_size'] ?? 48);
    $cShowLogo         = isset($cardDesign['show_logo']) ? (bool)$cardDesign['show_logo'] : true;

    $cEventLogoImg     = $cardDesign['event_logo_image'] ?? null;
    $cEventLogoSize    = intval($cardDesign['event_logo_size'] ?? 60);
    $cShowEventLogo    = isset($cardDesign['show_event_logo']) ? (bool)$cardDesign['show_event_logo'] : true;

    // Visibility toggles
    $cShowHeader       = isset($cardDesign['show_header']) ? (bool)$cardDesign['show_header'] : true;
    $cShowBadge        = isset($cardDesign['show_badge']) ? (bool)$cardDesign['show_badge'] : true;
    $cShowPhoto        = isset($cardDesign['show_photo']) ? (bool)$cardDesign['show_photo'] : true;
    $cShowNamePlate    = isset($cardDesign['show_name_plate']) ? (bool)$cardDesign['show_name_plate'] : true;
    $cShowQuote        = isset($cardDesign['show_quote']) ? (bool)$cardDesign['show_quote'] : true;
    $cShowArtwork      = isset($cardDesign['show_artwork']) ? (bool)$cardDesign['show_artwork'] : true;
    $cShowOrganizers   = isset($cardDesign['show_organizers']) ? (bool)$cardDesign['show_organizers'] : true;

    // Text Headings
    $cAnniv            = $cardDesign['anniversary_text'] ?? '২০ অক্টোবর ১৩তম প্রতিষ্ঠাবার্ষিকী উপলক্ষে';
    $cAnnivColor       = $cardDesign['anniversary_color'] ?? '#fef08a';
    $cTitle            = $cardDesign['title_text'] ?? ($campaign->title ?: 'রংপুর সাহিত্য উৎসব');
    $cTitleColor       = $cardDesign['title_color'] ?? '#ffffff';
    $cSub              = $cardDesign['subtitle_text'] ?? 'ও ৩য় লিটিলম্যাগ মেলা';
    $cSubtitleColor    = $cardDesign['subtitle_color'] ?? '#fef08a';
    $cBadge            = $cardDesign['badge_text'] ?? ($campaign->badge_text ?: 'আমন্ত্রণ কার্ড');

    // Photo & Name Plate
    $cPhotoSize        = intval($cardDesign['photo_size'] ?? 68);
    $cPlateBg          = $cardDesign['name_plate_bg'] ?? '#ecd8b4';
    $cNameSize         = intval($cardDesign['name_size'] ?? 14);
    $cNameColor        = $cardDesign['name_color'] ?? '#0f172a';
    $cMetaColor        = $cardDesign['meta_color'] ?? '#334155';

    // Message / Quote
    $cQuote            = $cardDesign['quote_text'] ?? "সাহিত্য উৎসব ও লিটিলম্যাগমেলায়\nআপনার উপস্থিতি ও অংশগ্রহণ\nআমাদের সম্মানিত করবে ।";
    $cQuoteColor       = $cardDesign['quote_color'] ?? '#ffffff';

    // Organizers
    $cOrgColor         = $cardDesign['org_color'] ?? '#ffffff';
    $cOrg1Name         = $cardDesign['org_1_name'] ?? 'সাকিল মাসুদ';
    $cOrg1Role         = $cardDesign['org_1_role'] ?? "সদস্যসচিব, আয়োজক কমিটি\nও সাধারণ সম্পাদক, ফিরেদেখা";
    $cOrg1Phone        = $cardDesign['org_1_phone'] ?? '০১৭২৬৯৭৬৯৮২';

    $cOrg2Name         = $cardDesign['org_2_name'] ?? 'বাবুল সরকার';
    $cOrg2Role         = $cardDesign['org_2_role'] ?? "আহ্বায়ক, আয়োজক কমিটি\nও সাহিত্য সম্পাদক, ফিরেদেখা";
    $cOrg2Phone        = $cardDesign['org_2_phone'] ?? '01763170342';

    $cOrg3Name         = $cardDesign['org_3_name'] ?? 'তাপস মাহমুদ';
    $cOrg3Role         = $cardDesign['org_3_role'] ?? "সভাপতি,\nফিরেদেখা";
    $cOrg3Phone        = $cardDesign['org_3_phone'] ?? '01820-547307';

    $themeColor        = $campaign->theme_color ?: '#991b1b';
    $customFields      = $campaign->custom_fields ?? [];
@endphp

@section('title', $campaign->title . ' — লেখকদের অংশগ্রহণ ফরম')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/event-campaign.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700;800&family=Noto+Serif+Bengali:wght@600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --writer-theme: {{ $themeColor }};
            --writer-theme-dark: #7f1d1d;
            --writer-theme-light: #fef2f2;
            --writer-accent: #f59e0b;
        }

        /* Hero Banner */
        .writer-hero-banner {
            @if($campaign->banner_image)
                background: linear-gradient(135deg, rgba(69, 10, 10, 0.88) 0%, rgba(127, 29, 29, 0.82) 100%), url('{{ asset('storage/' . $campaign->banner_image) }}') no-repeat center center / cover;
            @else
                background: linear-gradient(135deg, #450a0a 0%, #7f1d1d 50%, {{ $themeColor }} 100%);
            @endif
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

        /* Live Invitation Card Preview (Exact Design Match) */
        .delegate-card-preview {
            @if($cBgImg)
                background: url('{{ asset('storage/' . $cBgImg) }}') no-repeat center center / cover;
            @else
                background: {{ $cBgColor }} linear-gradient(145deg, #c4871e 0%, #db9e2a 45%, #b57a15 100%);
            @endif
            font-family: '{{ $cFontFamily }}', 'Hind Siliguri', 'SolaimanLipi', sans-serif;
            border-radius: 14px;
            padding: 14px 12px 10px 12px;
            color: #1e1b4b;
            box-shadow: 0 14px 35px rgba(0, 0, 0, 0.2);
            border: 2px solid #8d5c0b;
            position: relative;
            overflow: hidden;
            max-width: 330px;
            margin: 0 auto 16px auto;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .preview-top-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 4px;
            position: relative;
            z-index: 2;
        }
        .preview-logo-emblem {
            width: {{ $cLogoSize }}px;
            height: {{ $cLogoSize }}px;
            border-radius: 50%;
            background: transparent;
            border: none;
            box-shadow: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            padding: 0;
        }
        .preview-logo-emblem .logo-inner {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: transparent;
        }
        .preview-festival-headings {
            flex-grow: 1;
            text-align: center;
            padding: 0 2px;
        }
        .preview-fest-anniv {
            color: {{ $cAnnivColor }};
            font-size: 7.5px;
            font-weight: 700;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
            margin-bottom: 1px;
        }
        .preview-fest-main {
            color: {{ $cTitleColor }};
            font-family: '{{ $cFontFamily }}', 'Noto Serif Bengali', 'Hind Siliguri', serif;
            font-size: 13.5px;
            font-weight: 900;
            line-height: 1.15;
            text-shadow: 0 2px 4px rgba(0,0,0,0.35);
        }
        .preview-fest-sub {
            color: {{ $cSubtitleColor }};
            font-family: '{{ $cFontFamily }}', 'Noto Serif Bengali', 'Hind Siliguri', serif;
            font-size: 11.5px;
            font-weight: 800;
            line-height: 1.15;
            text-shadow: 0 2px 4px rgba(0,0,0,0.35);
        }
        .preview-vert-ribbon {
            background: linear-gradient(180deg, #facc15 0%, #eab308 100%);
            color: #713f12;
            font-family: '{{ $cFontFamily }}', 'Noto Serif Bengali', serif;
            font-size: 9.5px;
            font-weight: 900;
            padding: 4px 3px;
            border-radius: 4px;
            writing-mode: vertical-rl;
            text-orientation: upright;
            letter-spacing: 1px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
            border: 1px solid #ca8a04;
            line-height: 1.1;
            flex-shrink: 0;
        }
        .preview-card-pill {
            background: #ecd8b4;
            border: 1px solid #d4b886;
            color: #0f172a;
            font-size: 8.5px;
            font-weight: 800;
            padding: 1.5px 6px;
            border-radius: 10px;
            margin-top: 4px;
            white-space: nowrap;
        }
        .preview-center-area {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 6px 0 4px 0;
        }
        .preview-photo-round {
            width: {{ $cPhotoSize }}px;
            height: {{ $cPhotoSize }}px;
            border-radius: 50%;
            border: 3px solid #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
            background: #f8fafc;
            overflow: hidden;
            margin-bottom: -14px;
            position: relative;
            z-index: 4;
        }
        .preview-photo-round img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .preview-name-plate {
            width: 95%;
            background: {{ $cPlateBg }};
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            padding: {{ $cShowPhoto ? '18px' : '10px' }} 8px 8px 8px;
            text-align: center;
            border: 1px solid #d8be92;
            position: relative;
            z-index: 3;
        }
        .preview-name-plate .p-author-name {
            font-family: '{{ $cFontFamily }}', 'Noto Serif Bengali', 'Hind Siliguri', serif;
            font-size: {{ $cNameSize }}px;
            font-weight: 800;
            color: {{ $cNameColor }};
            line-height: 1.2;
            margin-bottom: 2px;
        }
        .preview-name-plate .p-author-desc {
            font-size: 9.5px;
            font-weight: 700;
            color: {{ $cMetaColor }};
            line-height: 1.2;
        }
        .preview-name-plate .p-author-loc {
            font-size: 8.5px;
            font-weight: 600;
            color: {{ $cMetaColor }};
            opacity: 0.85;
        }
        .preview-quote-row {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            padding: 4px 4px 2px 4px;
        }
        .preview-quote-txt {
            color: {{ $cQuoteColor }};
            font-size: 9px;
            font-weight: 700;
            line-height: 1.35;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.4);
            flex-grow: 1;
            white-space: pre-line;
        }
        .preview-book-svg {
            width: 58px;
            height: 48px;
            flex-shrink: 0;
        }
        .preview-organizers-row {
            position: relative;
            z-index: 2;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            border-top: 1px solid rgba(255, 255, 255, 0.35);
            padding-top: 4px;
            gap: 3px;
            color: {{ $cOrgColor }};
        }
        .p-org-col {
            color: {{ $cOrgColor }};
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.35);
        }
        .p-org-col:not(:last-child) {
            border-right: 1px solid rgba(255, 255, 255, 0.35);
            padding-right: 2px;
        }
        .p-org-col .p-org-title {
            font-size: 8px;
            font-weight: 800;
            line-height: 1.15;
            color: {{ $cOrgColor }};
        }
        .p-org-col .p-org-sub {
            font-size: 5.8px;
            line-height: 1.2;
            color: {{ $cOrgColor }};
            opacity: 0.9;
            white-space: pre-line;
        }
        .p-org-col .p-org-tel {
            font-size: 6.2px;
            font-weight: 700;
            font-family: Arial, sans-serif;
            color: {{ $cOrgColor }};
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

                        {{-- ==========================================
                             ৫. পেমেন্ট ও ফি (যদি প্রযোজ্য হয়)
                             ========================================== --}}
                        @if($campaign->has_fee_or_donation)
                            <div class="section-tag-head">
                                <span><i class="fa-solid fa-wallet me-1"></i> ৫. নিবন্ধন ফি ও পেমেন্ট বিবরণী</span>
                                <span class="badge bg-danger text-white">ফি: ৳{{ number_format($campaign->fee_amount ?: 0) }}</span>
                            </div>

                            <div class="p-3 bg-light rounded-3 border mb-3">
                                @if($campaign->payment_instructions)
                                    <div class="small text-dark mb-2" style="white-space: pre-line;">{!! nl2br(e($campaign->payment_instructions)) !!}</div>
                                @endif
                                @if($campaign->payment_methods)
                                    <div class="small text-muted mb-2"><strong>পেমেন্ট মাধ্যম:</strong> {{ $campaign->payment_methods }}</div>
                                @endif

                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="w-label" for="writerAmountPaid">পরিশোধিত টাকার পরিমাণ (৳)</label>
                                        <input type="number" step="1" name="amount_paid" id="writerAmountPaid" class="w-input font-monospace" value="{{ old('amount_paid', $campaign->fee_amount ?: 0) }}" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="w-label" for="writerTrxId">ট্রানজেকশন আইডি (TrxID) <span class="text-danger">*</span></label>
                                        <input type="text" name="transaction_id" id="writerTrxId" class="w-input font-monospace" placeholder="বিকাশ/নগদ TrxID" value="{{ old('transaction_id') }}" required>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- ==========================================
                             ৬. অতিরিক্ত ফিল্ডসমূহ (Custom Fields)
                             ========================================== --}}
                        @if(!empty($customFields) && count($customFields) > 0)
                            <div class="section-tag-head">
                                <span><i class="fa-solid fa-list-check me-1"></i> ৬. অতিরিক্ত তথ্যাবলী</span>
                            </div>
                            <div class="row g-3 mb-3">
                                @foreach($customFields as $cf)
                                    <div class="col-md-6">
                                        <label class="w-label">{{ $cf['label'] ?? $cf['name'] }} @if(!empty($cf['required'])) <span class="text-danger">*</span> @endif</label>
                                        @if(($cf['type'] ?? 'text') === 'select')
                                            <select name="custom_fields[{{ $cf['name'] }}]" class="w-select" @if(!empty($cf['required'])) required @endif>
                                                <option value="">-- নির্বাচন করুন --</option>
                                                @foreach((array)($cf['options'] ?? []) as $opt)
                                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                                @endforeach
                                            </select>
                                        @elseif(($cf['type'] ?? 'text') === 'textarea')
                                            <textarea name="custom_fields[{{ $cf['name'] }}]" class="w-input" rows="2" @if(!empty($cf['required'])) required @endif></textarea>
                                        @else
                                            <input type="{{ $cf['type'] ?? 'text' }}" name="custom_fields[{{ $cf['name'] }}]" class="w-input" @if(!empty($cf['required'])) required @endif>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- SUBMIT BUTTON SECTION --}}
                        <div class="mt-4 pt-3 border-top text-center">
                            {{-- Event Organizer Collaboration Notice --}}
                            <div class="p-3 rounded-3 mb-3 text-start d-flex align-items-center gap-3 border shadow-xs" style="background: #fffbeb; border-color: #fde68a !important; color: #92400e;">
                                <i class="fa-solid fa-feather-pointed fs-4 text-warning flex-shrink-0"></i>
                                <div style="font-size: 13px; line-height: 1.55;">
                                    <strong>বিশেষ বিজ্ঞপ্তি:</strong> ইভেন্ট আয়োজক “ফিরেদেখা” আইডিয়া প্রকাশন ইউআরএল ব্যবহারের অনুমতি দিয়েছেন সংগঠনকে সহযোগিতা করা ও লেখকগণের সুবিধার্থে
                                </div>
                            </div>
                            <div class="text-dark small fw-semibold mb-3" style="font-size: 13.5px;">
                                <i class="fa-solid fa-clock-rotate-left text-danger me-1"></i> ২৪ ঘণ্টা পর মোবাইল নম্বর দিয়ে লগিন করে কার্ড নম্বর ও আমন্ত্রণ কার্ড ডাউনলোড করুন।
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
                
                {{-- Live Exact Invitation Card Preview (আমন্ত্রণ কার্ড) --}}
                <div class="delegate-card-preview mb-3 position-relative" style="overflow: hidden;">
                    
                    {{-- Background Overlay Tint & Blur --}}
                    @if($cBgOverlayOpacity > 0 || $cBgBlur > 0)
                        <div style="position: absolute; inset: 0; background: {{ $cBgOverlayColor }}; opacity: {{ $cBgOverlayOpacity / 100 }}; @if($cBgBlur > 0) backdrop-filter: blur({{ $cBgBlur }}px); -webkit-backdrop-filter: blur({{ $cBgBlur }}px); @endif pointer-events: none; border-radius: 14px; z-index: 1;"></div>
                    @endif

                    {{-- 1. Top Section --}}
                    @if($cShowHeader || $cShowBadge)
                        <div class="preview-top-row" style="position: relative; z-index: 2;">
                            {{-- Org Logo --}}
                            @if($cShowLogo)
                                <div class="preview-logo-emblem" style="width: {{ $cLogoSize }}px; height: {{ $cLogoSize }}px; border: none; background: transparent;">
                                    @if($cLogoImg && file_exists(public_path('storage/' . $cLogoImg)))
                                        <img src="{{ asset('storage/' . $cLogoImg) }}" alt="Logo" style="width: 100%; height: 100%; object-fit: contain;">
                                    @else
                                        <div class="logo-inner" style="border: none; background: transparent;">
                                            <span style="font-size: 7px; font-weight: 800; color: {{ $cTitleColor }}; line-height: 1;">ফিরেদেখা</span>
                                            <i class="fa-solid fa-feather-pointed" style="font-size: 13px; color: {{ $cTitleColor }}; line-height: 1;"></i>
                                            <span style="font-size: 5px; font-weight: 700; color: {{ $cTitleColor }}; opacity: 0.85; line-height: 1;">প্রতিষ্ঠা: ২০১৬</span>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            {{-- Festival Headings & Event Logo --}}
                            @if($cShowHeader)
                                <div class="preview-festival-headings">
                                    @if($cShowEventLogo && $cEventLogoImg && file_exists(public_path('storage/' . $cEventLogoImg)))
                                        <div style="display: flex; justify-content: center; align-items: center; margin-bottom: 2px;">
                                            <img src="{{ asset('storage/' . $cEventLogoImg) }}" alt="Event Logo" style="max-height: {{ $cEventLogoSize }}px; max-width: 100%; object-fit: contain;">
                                        </div>
                                    @endif
                                    <div class="preview-fest-anniv" style="color: {{ $cAnnivColor }};">{{ $cAnniv }}</div>
                                    <div class="preview-fest-main" style="color: {{ $cTitleColor }}; font-family: '{{ $cFontFamily }}', 'Noto Serif Bengali', serif;">{{ $cTitle }}</div>
                                    <div class="preview-fest-sub" style="color: {{ $cSubtitleColor }}; font-family: '{{ $cFontFamily }}', 'Noto Serif Bengali', serif;">{{ $cSub }}</div>
                                </div>
                            @endif

                            {{-- Ribbon Badge --}}
                            @if($cShowBadge)
                                <div class="d-flex flex-column align-items-end flex-shrink-0">
                                    <div class="preview-vert-ribbon" style="font-family: '{{ $cFontFamily }}', 'Noto Serif Bengali', serif;">{{ $cBadge }}</div>
                                    <div class="preview-card-pill">কার্ড নং- ১০১</div>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- 2. Center Section: Photo & Name Plate --}}
                    @if($cShowPhoto || $cShowNamePlate)
                        <div class="preview-center-area" style="position: relative; z-index: 2;">
                            @if($cShowPhoto)
                                <div class="preview-photo-round" id="cardAvatarBox" style="width: {{ $cPhotoSize }}px; height: {{ $cPhotoSize }}px;">
                                    <img id="cardAvatarImg" style="width: 100%; height: 100%; object-fit: cover; display: none;" alt="Author Photo">
                                    <div id="cardAvatarIcon" style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #f1f5f9; color: #94a3b8; font-size: 24px;">
                                        <i class="fa-solid fa-user-pen"></i>
                                    </div>
                                </div>
                            @endif

                            @if($cShowNamePlate)
                                <div class="preview-name-plate" style="background: {{ $cPlateBg }}; padding: {{ $cShowPhoto ? '18px' : '10px' }} 8px 8px 8px;">
                                    <div class="p-author-name" id="cardNamePreview" style="font-size: {{ $cNameSize }}px; color: {{ $cNameColor }}; font-family: '{{ $cFontFamily }}', 'Noto Serif Bengali', serif;">{{ $user?->name ?: 'আপনার নাম' }}</div>
                                    <div class="p-author-desc" id="cardCategoryPreview" style="color: {{ $cMetaColor }};">কবি, সম্পাদক ও প্রকাশক</div>
                                    <div class="p-author-loc" id="cardLocationPreview" style="color: {{ $cMetaColor }}; opacity: 0.85;">রংপুর</div>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- 3. Message & Book Artwork --}}
                    @if($cShowQuote || $cShowArtwork)
                        <div class="preview-quote-row" style="position: relative; z-index: 2;">
                            @if($cShowQuote)
                                <div class="preview-quote-txt" style="color: {{ $cQuoteColor }}; white-space: pre-line;">{!! nl2br(e($cQuote)) !!}</div>
                            @endif

                            @if($cShowArtwork)
                                <div class="preview-book-svg">
                                    <svg viewBox="0 0 100 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M50 20 C32 6 12 14 6 22 C6 50 10 65 50 72 C90 65 94 50 94 22 C88 14 68 6 50 20 Z" fill="#ffffff" fill-opacity="0.95" stroke="#713f12" stroke-width="2"/>
                                        <path d="M50 22 C34 10 16 16 10 24 L10 60 C30 52 46 58 50 68 C54 58 70 52 90 60 L90 24 C84 16 66 10 50 22 Z" fill="#fef9c3"/>
                                        <path d="M50 22 L50 68" stroke="#ca8a04" stroke-width="2.5"/>
                                        <path d="M22 32 C30 30 38 32 44 36" stroke="#ca8a04" stroke-width="1.5" stroke-linecap="round"/>
                                        <path d="M22 40 C30 38 38 40 44 44" stroke="#ca8a04" stroke-width="1.5" stroke-linecap="round"/>
                                        <path d="M22 48 C30 46 38 48 44 52" stroke="#ca8a04" stroke-width="1.5" stroke-linecap="round"/>
                                        <path d="M78 32 C70 30 62 32 56 36" stroke="#ca8a04" stroke-width="1.5" stroke-linecap="round"/>
                                        <path d="M78 40 C70 38 62 40 56 44" stroke="#ca8a04" stroke-width="1.5" stroke-linecap="round"/>
                                        <path d="M78 48 C70 46 62 48 56 52" stroke="#ca8a04" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- 4. Bottom 3 Organizers Columns --}}
                    @if($cShowOrganizers)
                        <div class="preview-organizers-row" style="position: relative; z-index: 2; color: {{ $cOrgColor }}; border-top-color: {{ $cOrgColor }}55;">
                            <div class="p-org-col" style="color: {{ $cOrgColor }};">
                                <div class="p-org-title" style="color: {{ $cOrgColor }};">{{ $cOrg1Name }}</div>
                                <div class="p-org-sub" style="color: {{ $cOrgColor }}; white-space: pre-line;">{!! nl2br(e($cOrg1Role)) !!}</div>
                                <div class="p-org-tel" style="color: {{ $cOrgColor }};"><i class="fa-solid fa-phone"></i> {{ $cOrg1Phone }}</div>
                            </div>
                            <div class="p-org-col" style="color: {{ $cOrgColor }};">
                                <div class="p-org-title" style="color: {{ $cOrgColor }};">{{ $cOrg2Name }}</div>
                                <div class="p-org-sub" style="color: {{ $cOrgColor }}; white-space: pre-line;">{!! nl2br(e($cOrg2Role)) !!}</div>
                                <div class="p-org-tel" style="color: {{ $cOrgColor }};"><i class="fa-solid fa-phone"></i> {{ $cOrg2Phone }}</div>
                            </div>
                            <div class="p-org-col" style="color: {{ $cOrgColor }};">
                                <div class="p-org-title" style="color: {{ $cOrgColor }};">{{ $cOrg3Name }}</div>
                                <div class="p-org-sub" style="color: {{ $cOrgColor }}; white-space: pre-line;">{!! nl2br(e($cOrg3Role)) !!}</div>
                                <div class="p-org-tel" style="color: {{ $cOrgColor }};"><i class="fa-solid fa-phone"></i> {{ $cOrg3Phone }}</div>
                            </div>
                        </div>
                    @endif
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
                        <li><i class="fa-solid fa-check-circle text-success me-1.5"></i> @if($campaign->has_fee_or_donation && $campaign->fee_amount > 0) নিবন্ধন ফি: ৳{{ number_format($campaign->fee_amount) }} @else বিনামূল্যে অনলাইন নিবন্ধন। @endif</li>
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
    const cat = document.getElementById('authorCategoryInput')?.value || 'কবি, সম্পাদক ও প্রকাশক';
    const district = document.getElementById('writerDistrict')?.value || 'রংপুর';
    const thana = document.getElementById('writerUpazila')?.value || '';

    const cardName = document.getElementById('cardNamePreview');
    if (cardName) cardName.textContent = name || 'আপনার নাম';

    const cardCat = document.getElementById('cardCategoryPreview');
    if (cardCat) cardCat.textContent = cat;

    const cardLoc = document.getElementById('cardLocationPreview');
    if (cardLoc) {
        const parts = [thana, district].filter(Boolean);
        cardLoc.textContent = parts.length > 0 ? parts.join(', ') : 'রংপুর';
    }
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
