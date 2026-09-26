@php
    $formData = $registration->form_data ?? [];
    $campaign = $registration->campaign;
    $isPdf = $isPdf ?? false;

    // Card Design Customizer Settings
    $cardDesign = $campaign->form_settings['card_design'] ?? [];
    
    // 1. Background Settings
    $bgImage        = $cardDesign['bg_image'] ?? null;
    $logoImage      = $cardDesign['logo_image'] ?? null;
    $eventLogoImage = $cardDesign['event_logo_image'] ?? null;
    $bgColor        = $cardDesign['bg_color'] ?? '#c98c21';
    
    // 2. Element Visibility Toggles (বাদ দেওয়ার / প্রদর্শন করার সেটিংস)
    $showHeader     = !empty($cardDesign['show_header']);
    $showLogo       = !empty($cardDesign['show_logo']);
    $showEventLogo  = !empty($cardDesign['show_event_logo']);
    $eventLogoSize  = intval($cardDesign['event_logo_size'] ?? 64);
    $showBadge      = !empty($cardDesign['show_badge']);
    $showPhoto      = $cardDesign['show_photo'] ?? true;
    $showNamePlate  = $cardDesign['show_name_plate'] ?? true;
    $showQuote      = $cardDesign['show_quote'] ?? true;
    $showArtwork    = $cardDesign['show_artwork'] ?? true;
    $showOrganizers = $cardDesign['show_organizers'] ?? true;
    $headerHeight   = intval($cardDesign['header_height'] ?? 90);

    // 3. Sizing & Typography Controls (ছোট বড়ো করার সেটিংস)
    $photoSize          = intval($cardDesign['photo_size'] ?? 82);
    $photoBorderRadius  = $cardDesign['photo_border_radius'] ?? '50%';
    $photoBorderWidth   = intval($cardDesign['photo_border_width'] ?? 3);
    $photoBorderColor   = $cardDesign['photo_border_color'] ?? '#ffffff';
    $photoShadow        = $cardDesign['photo_shadow'] ?? 'soft';
    $photoBrightness    = intval($cardDesign['photo_brightness'] ?? 100);
    $photoContrast      = intval($cardDesign['photo_contrast'] ?? 100);
    $photoGrayscale     = intval($cardDesign['photo_grayscale'] ?? 0);
    $photoSepia         = intval($cardDesign['photo_sepia'] ?? 0);

    $nameFontSize       = intval($cardDesign['name_font_size'] ?? 16);
    $nameLineHeight     = floatval($cardDesign['name_line_height'] ?? 1.25);
    $nameSpacing        = intval($cardDesign['name_spacing'] ?? 2);
    $platePadding       = intval($cardDesign['plate_padding'] ?? 14);
    $logoSize           = intval($cardDesign['logo_size'] ?? 58);
    $plateBgColor       = $cardDesign['plate_bg_color'] ?? '#ecd8b4';
    $fontFamily         = $cardDesign['font_family'] ?? 'Hind Siliguri';

    // 3.1 Detailed Text Colors & Background Effects
    $annivColor         = $cardDesign['anniv_color'] ?? '#ffffff';
    $titleColor         = $cardDesign['title_color'] ?? '#ffffff';
    $subtitleColor      = $cardDesign['subtitle_color'] ?? '#ffffff';
    $nameColor          = $cardDesign['name_color'] ?? '#0f172a';
    $metaColor          = $cardDesign['meta_color'] ?? '#334155';
    $quoteColor         = $cardDesign['quote_color'] ?? '#ffffff';
    $orgColor           = $cardDesign['org_color'] ?? '#ffffff';
    $bgOverlayOpacity   = intval($cardDesign['bg_overlay_opacity'] ?? 0);
    $bgOverlayColor     = $cardDesign['bg_overlay_color'] ?? '#000000';
    $bgBlur             = intval($cardDesign['bg_blur'] ?? 0);

    // 4. Customizable Texts
    $anniversaryText = $cardDesign['anniversary_text'] ?? '২০ অক্টোবর ১৩তম প্রতিষ্ঠাবার্ষিকী উপলক্ষে';
    $titleText       = $cardDesign['title_text'] ?? 'রংপুর সাহিত্য উৎসব';
    $subtitleText    = $cardDesign['subtitle_text'] ?? 'ও ৩য় লিটিলম্যাগ মেলা';
    $badgeText       = $cardDesign['badge_text'] ?? 'আমন্ত্রণ কার্ড';
    $quoteText       = $cardDesign['quote_text'] ?? "সাহিত্য উৎসব ও লিটিলম্যাগমেলায়\nআপনার উপস্থিতি ও অংশগ্রহণ\nআমাদের সম্মানিত করবে ।";

    // 5. Organizers 3 Columns
    $org1Name  = $cardDesign['org_1_name'] ?? 'সাকিল মাসুদ';
    $org1Role  = $cardDesign['org_1_role'] ?? "সদস্যসচিব, প্রতিষ্ঠাবার্ষিকী আয়োজক কমিটি ২০২৬\nও সাধারণ সম্পাদক, ফিরেদেখা";
    $org1Phone = $cardDesign['org_1_phone'] ?? '০১৭২৬৯৭৬৯৮২';

    $org2Name  = $cardDesign['org_2_name'] ?? 'বাবুল সরকার';
    $org2Role  = $cardDesign['org_2_role'] ?? "আহ্বায়ক, প্রতিষ্ঠাবার্ষিকী আয়োজক কমিটি ২০২৬\nও সাহিত্য সম্পাদক, ফিরেদেখা";
    $org2Phone = $cardDesign['org_2_phone'] ?? '01763170342';

    $org3Name  = $cardDesign['org_3_name'] ?? 'তাপস মাহমুদ';
    $org3Role  = $cardDesign['org_3_role'] ?? "সভাপতি,\nফিরেদেখা";
    $org3Phone = $cardDesign['org_3_phone'] ?? '01820-547307';

    // Photo path resolution
    $photoPath = $formData['student_photo'] ?? ($formData['author_photo'] ?? null);

    // Extract dynamic Card Number
    $regNumber = $registration->registration_number;
    $cardNo = preg_replace('/[^0-9]/', '', $regNumber);
    if (empty($cardNo)) {
        $cardNo = str_pad($registration->id, 3, '0', STR_PAD_LEFT);
    } else {
        $cardNo = ltrim($cardNo, '0') ?: $registration->id;
    }

    // Author Category / Designation
    $genres = $formData['genres'] ?? [];
    if (is_array($genres) && count($genres) > 0) {
        $designation = implode(', ', array_slice($genres, 0, 3));
    } else {
        $designation = $registration->designation_or_class ?: ($formData['author_category'] ?? 'কবি, লেখক ও সাহিত্যিক');
    }

    // Location
    $locationParts = array_filter([$registration->thana, $registration->district]);
    $location = count($locationParts) > 0 ? implode(', ', $locationParts) : 'রংপুর';
@endphp
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>আমন্ত্রণ কার্ড — {{ $registration->name }} (কার্ড নং- {{ $cardNo }})</title>
    
    <!-- Google Fonts for Bengali Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700;800&family=Noto+Serif+Bengali:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- html2pdf for high quality Bengali Card PDF Export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        @page {
            size: 3.8in 5.4in;
            margin: 0;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        html, body {
            font-family: 'Hind Siliguri', 'SolaimanLipi', Arial, sans-serif;
            background-color: #f1f5f9;
            color: #1e1b4b;
            margin: 0;
            padding: 24px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .no-print {
            display: block;
            margin-bottom: 16px;
        }
        @media print {
            html, body {
                background: none !important;
                background-color: transparent !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 3.8in !important;
                height: 5.4in !important;
                min-height: auto !important;
                display: block !important;
            }
            .no-print, .action-bar {
                display: none !important;
            }
            .card-wrapper {
                margin: 0 !important;
                padding: 0 !important;
                width: 3.8in !important;
                height: 5.4in !important;
                page-break-inside: avoid !important;
                page-break-after: avoid !important;
            }
            .rsu-invitation-card {
                width: 3.8in !important;
                height: 5.4in !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                margin: 0 !important;
                page-break-inside: avoid !important;
                page-break-after: avoid !important;
            }
        }

        .action-bar {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
        }
        .btn-print { background: #0f172a; color: #ffffff; }
        .btn-pdf { background: #dc2626; color: #ffffff; }
        .btn-back { background: #ffffff; color: #334155; border: 1px solid #cbd5e1; }
        .btn-action:hover { transform: translateY(-1px); }

        /* ==========================================================================
           RSU OFFICIAL INVITATION CARD (3.8 x 5.4 inch Layout)
           ========================================================================== */
        .card-wrapper {
            width: 3.8in;
            max-width: 100%;
        }

        .rsu-invitation-card {
            width: 3.8in;
            height: 5.4in;
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.25);
            @if($bgImage)
                background: url('{{ $isPdf ? public_path("storage/" . $bgImage) : asset("storage/" . $bgImage) }}') no-repeat center center;
                background-size: cover;
            @else
                background: {{ $bgColor }} linear-gradient(145deg, #c4871e 0%, #db9e2a 45%, #b57a15 100%);
            @endif
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 14px 14px 10px 14px;
            border: 2px solid #8d5c0b;
        }

        /* Subtle textured background paper effect */
        .rsu-invitation-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(circle at 50% 30%, rgba(255, 255, 255, 0.12) 0%, rgba(0, 0, 0, 0.08) 100%);
            pointer-events: none;
        }

        /* TOP HEADER SECTION */
        .card-top-section {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 6px;
        }

        /* Top Left Emblem / Organization Logo */
        .phiredekha-logo {
            width: {{ $logoSize }}px;
            height: {{ $logoSize }}px;
            border-radius: 50%;
            background: transparent;
            border: none;
            box-shadow: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            text-align: center;
            padding: 0;
            position: relative;
            overflow: hidden;
        }
        .phiredekha-logo .custom-logo-img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }
        .phiredekha-logo .logo-inner-ring {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 1px dashed #7f1d1d;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #fef2f2;
        }
        .phiredekha-logo .logo-brand {
            font-size: 8.5px;
            font-weight: 800;
            color: #7f1d1d;
            line-height: 1;
            margin-bottom: 2px;
        }
        .phiredekha-logo .logo-icon {
            font-size: 15px;
            color: #991b1b;
            line-height: 1;
        }
        .phiredekha-logo .logo-est {
            font-size: 6px;
            color: #7f1d1d;
            font-weight: 700;
            line-height: 1;
            margin-top: 1px;
        }

        /* Top Center Festival Typography */
        .festival-text-wrap {
            flex-grow: 1;
            text-align: center;
            padding: 0 4px;
        }
        .festival-anniversary {
            color: {{ $annivColor }};
            font-size: 8.5px;
            font-weight: 700;
            letter-spacing: 0.2px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
            margin-bottom: 1px;
        }
        .festival-main-title {
            color: {{ $titleColor }};
            font-family: '{{ $fontFamily }}', 'Noto Serif Bengali', 'Hind Siliguri', serif;
            font-size: 16px;
            font-weight: 900;
            line-height: 1.15;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.35);
            letter-spacing: -0.2px;
        }
        .festival-subtitle {
            color: {{ $subtitleColor }};
            font-family: '{{ $fontFamily }}', 'Noto Serif Bengali', 'Hind Siliguri', serif;
            font-size: 13.5px;
            font-weight: 800;
            line-height: 1.15;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.35);
        }

        /* Top Right Vertical Badge & Card No */
        .card-badge-wrap {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            flex-shrink: 0;
        }
        .vertical-invite-ribbon {
            color: #facc15;
            font-family: '{{ $fontFamily }}', 'Noto Serif Bengali', 'Hind Siliguri', serif;
            font-size: 15px;
            font-weight: 900;
            letter-spacing: 2px;
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.75), 0 0 2px #000;
            white-space: nowrap;
            line-height: 1;
            padding: 0 4px;
        }
        .card-number-pill {
            background: #ecd8b4;
            border: 1px solid #c9a76d;
            color: #0f172a;
            font-size: 10px;
            font-weight: 800;
            padding: 3px 10px;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
            margin-top: 8px;
            white-space: nowrap;
        }

        /* ==========================================================================
           MIDDLE SECTION: PHOTO & AUTHOR PLATE
           ========================================================================== */
        .card-middle-section {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 2px 0 2px 0;
        }

        /* Circular/Custom Author Photo (10px upwards from nameplate) */
        .author-photo-frame {
            width: {{ $photoSize }}px;
            height: {{ $photoSize }}px;
            border-radius: {{ $photoBorderRadius }};
            border: {{ $photoBorderWidth }}px solid {{ $photoBorderColor }};
            @if($photoShadow === 'glow')
                box-shadow: 0 0 16px rgba(250, 204, 21, 0.6);
            @elseif($photoShadow === 'deep')
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.45);
            @elseif($photoShadow === 'none')
                box-shadow: none;
            @else
                box-shadow: 0 6px 16px rgba(0, 0, 0, 0.25);
            @endif
            filter: brightness({{ $photoBrightness }}%) contrast({{ $photoContrast }}%) grayscale({{ $photoGrayscale }}%) sepia({{ $photoSepia }}%);
            background: #f8fafc;
            overflow: hidden;
            margin-bottom: -8px; /* shifted 10px upwards from -18px */
            position: relative;
            z-index: 4;
        }
        .author-photo-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .author-photo-frame .photo-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            color: #94a3b8;
            font-size: 32px;
        }

        /* Cream Author Name Plate */
        .author-name-plate {
            width: 92%;
            background: {{ $plateBgColor }};
            border-radius: 14px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
            padding: {{ $showPhoto ? ($platePadding + 2) : $platePadding }}px 12px {{ $platePadding }}px 12px;
            text-align: center;
            border: 1px solid #d8be92;
            position: relative;
            z-index: 3;
        }
        .author-name-plate .author-name {
            font-family: '{{ $fontFamily }}', 'Noto Serif Bengali', 'Hind Siliguri', serif;
            font-size: {{ $nameFontSize }}px;
            font-weight: 800;
            color: {{ $nameColor }};
            line-height: {{ $nameLineHeight }};
            margin-bottom: {{ $nameSpacing }}px;
        }
        .author-name-plate .author-designation {
            font-size: 10px;
            font-weight: 700;
            color: {{ $metaColor }};
            line-height: 1.25;
            margin-bottom: 1px;
        }
        .author-name-plate .author-location {
            font-size: 9.5px;
            font-weight: 600;
            color: {{ $metaColor }};
            opacity: 0.85;
            line-height: 1.2;
        }

        /* ==========================================================================
           INVITATION MESSAGE SECTION (Clean without artwork)
           ========================================================================== */
        .card-message-section {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 6px 10px 4px 10px;
        }
        .invitation-quote-text {
            color: {{ $quoteColor }};
            font-size: 11px;
            font-weight: 700;
            line-height: 1.45;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.4);
            width: 100%;
        }

        /* ==========================================================================
           BOTTOM ORGANIZERS & SIGNATORIES SECTION (3 Columns - Centered & Clean)
           ========================================================================== */
        .card-organizers-section {
            position: relative;
            z-index: 2;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            border-top: 1.5px solid rgba(255, 255, 255, 0.45);
            padding-top: 6px;
            margin-top: 4px;
            gap: 6px;
            text-align: center;
        }
        .org-col {
            color: {{ $orgColor }};
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.35);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            text-align: center;
        }
        .org-col:not(:last-child) {
            border-right: 1px solid rgba(255, 255, 255, 0.35);
            padding-right: 4px;
        }
        .org-col .org-name {
            font-size: 10px;
            font-weight: 800;
            line-height: 1.25;
            margin-bottom: 2px;
            color: {{ $orgColor }};
            text-align: center;
            width: 100%;
        }
        .org-col .org-role {
            font-size: 7.2px;
            line-height: 1.35;
            color: rgba(255, 255, 255, 0.95);
            margin-bottom: 3px;
            text-align: center;
            width: 100%;
            flex-grow: 1;
        }
        .org-col .org-phone {
            font-size: 7.8px;
            font-weight: 700;
            color: {{ $orgColor }};
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 3px;
            font-family: Arial, sans-serif;
            text-align: center;
        }
    </style>
</head>
<body>

    {{-- Top Action Bar (Hidden on Print / PDF) --}}
    @if(!$isPdf)
        <div class="action-bar no-print">
            <button type="button" onclick="window.print()" class="btn-action btn-print">
                <i class="fa-solid fa-print"></i> Print Card
            </button>
            <button type="button" id="btnDownloadPdf" onclick="downloadCardPdf()" class="btn-action btn-pdf">
                <i class="fa-solid fa-file-pdf"></i> Download PDF
            </button>
            <a href="{{ url('/') }}" class="btn-action btn-back">
                <i class="fa-solid fa-house"></i> Home
            </a>
        </div>
    @endif

    {{-- 3.8 x 5.4 INCH INVITATION CARD --}}
    <div class="card-wrapper">
        <div class="rsu-invitation-card" id="printableCard">
            
            {{-- Background Overlay Tint --}}
            @if($bgOverlayOpacity > 0 || $bgBlur > 0)
                <div style="position: absolute; inset: 0; background: {{ $bgOverlayColor }}; opacity: {{ $bgOverlayOpacity / 100 }}; @if($bgBlur > 0) backdrop-filter: blur({{ $bgBlur }}px); -webkit-backdrop-filter: blur({{ $bgBlur }}px); @endif pointer-events: none; border-radius: 12px; z-index: 1;"></div>
            @endif

            {{-- 1. TOP HEADER ROW (Left Org Logo, Center Event Logo & Title, Right Badge / Spacer) --}}
            <div class="card-top-section" style="min-height: {{ $headerHeight }}px; display: flex; align-items: center; justify-content: space-between; gap: 6px;">
                
                {{-- Left: Organization Logo --}}
                <div class="header-left-col" style="min-width: {{ $logoSize }}px; display: flex; align-items: center; justify-content: flex-start;">
                    @if($showLogo && $logoImage && (file_exists(public_path('storage/' . $logoImage)) || file_exists(storage_path('app/public/' . $logoImage))))
                        <div class="phiredekha-logo" style="width: {{ $logoSize }}px; height: {{ $logoSize }}px;">
                            <img src="{{ asset('storage/' . $logoImage) }}" alt="Logo" class="custom-logo-img" crossorigin="anonymous">
                        </div>
                    @endif
                </div>

                {{-- Center: Event Logo & Title --}}
                <div class="festival-text-wrap" style="flex-grow: 1; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                    @if($showEventLogo && $eventLogoImage && (file_exists(public_path('storage/' . $eventLogoImage)) || file_exists(storage_path('app/public/' . $eventLogoImage))))
                        <div class="event-header-logo-wrap" style="display: flex; justify-content: center; align-items: center;">
                            <img src="{{ asset('storage/' . $eventLogoImage) }}" alt="Event Logo" style="max-height: {{ $eventLogoSize }}px; max-width: 100%; object-fit: contain;" crossorigin="anonymous">
                        </div>
                    @endif
                    @if($showHeader)
                        <div class="festival-anniversary">{{ $anniversaryText }}</div>
                        <div class="festival-main-title">{{ $titleText }}</div>
                        <div class="festival-subtitle">{{ $subtitleText }}</div>
                    @endif
                </div>

                {{-- Right: Badge / Symmetrical Spacer --}}
                <div class="header-right-col" style="min-width: {{ $logoSize }}px; display: flex; align-items: center; justify-content: flex-end;">
                    @if($showBadge)
                        <div class="card-badge-wrap">
                            <div class="vertical-invite-ribbon">{{ $badgeText }}</div>
                            <div class="card-number-pill">কার্ড নং- {{ $cardNo }}</div>
                        </div>
                    @endif
                </div>

            </div>

            {{-- 2. CENTER SECTION: AUTHOR PHOTO & CREAM PLATE (Toggleable) --}}
            @if($showPhoto || $showNamePlate)
                <div class="card-middle-section">
                    {{-- Circular Author Portrait (10px upwards) --}}
                    @if($showPhoto)
                        <div class="author-photo-frame">
                            @if($photoPath && (file_exists(public_path('storage/' . $photoPath)) || file_exists(storage_path('app/public/' . $photoPath))))
                                <img src="{{ asset('storage/' . $photoPath) }}" alt="Photo" crossorigin="anonymous">
                            @else
                                <div class="photo-placeholder">
                                    <i class="fa-solid fa-user-pen"></i>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Author Info Plate --}}
                    @if($showNamePlate)
                        <div class="author-name-plate">
                            <div class="author-name">{{ $registration->name }}</div>
                            <div class="author-designation">{{ $designation }}</div>
                            <div class="author-location">{{ $location }}</div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- 3. INVITATION MESSAGE SECTION (Artwork Removed) --}}
            @if($showQuote)
                <div class="card-message-section">
                    <div class="invitation-quote-text">
                        {!! nl2br(e($quoteText)) !!}
                    </div>
                </div>
            @endif

            {{-- 4. BOTTOM ORGANIZERS SECTION (3 COLUMNS - Centered & Clean) --}}
            @if($showOrganizers)
                <div class="card-organizers-section">
                    
                    {{-- Col 1 --}}
                    <div class="org-col">
                        <div class="org-name">{{ $org1Name }}</div>
                        <div class="org-role">{!! nl2br(e($org1Role)) !!}</div>
                        <div class="org-phone">
                            <i class="fa-solid fa-phone" style="font-size: 6px;"></i> {{ $org1Phone }}
                        </div>
                    </div>

                    {{-- Col 2 --}}
                    <div class="org-col">
                        <div class="org-name">{{ $org2Name }}</div>
                        <div class="org-role">{!! nl2br(e($org2Role)) !!}</div>
                        <div class="org-phone">
                            <i class="fa-solid fa-phone" style="font-size: 6px;"></i> {{ $org2Phone }}
                        </div>
                    </div>

                    {{-- Col 3 --}}
                    <div class="org-col">
                        <div class="org-name">{{ $org3Name }}</div>
                        <div class="org-role">{!! nl2br(e($org3Role)) !!}</div>
                        <div class="org-phone">
                            <i class="fa-solid fa-phone" style="font-size: 6px;"></i> {{ $org3Phone }}
                        </div>
                    </div>

                </div>
            @endif

            {{-- 5. FLOATING CUSTOM OBJECTS / STICKERS / WATERMARK LAYER --}}
            @php
                $printCustomObjects = $cardDesign['custom_objects'] ?? [];
            @endphp
            @if(is_array($printCustomObjects) && count($printCustomObjects) > 0)
                <div class="card-custom-objects-layer" style="position: absolute; inset: 0; pointer-events: none; z-index: 10; overflow: hidden; border-radius: 12px;">
                    @foreach($printCustomObjects as $pObj)
                        @php
                            $pOUrl = $pObj['url'] ?? '';
                            $pOX = floatval($pObj['x'] ?? 50);
                            $pOY = floatval($pObj['y'] ?? 50);
                            $pOW = intval($pObj['width'] ?? 60);
                            $pOOp = floatval($pObj['opacity'] ?? 1);
                            $pORot = floatval($pObj['rotation'] ?? 0);
                        @endphp
                        @if(!empty($pOUrl))
                            <img src="{{ $pOUrl }}" alt="Object" style="position: absolute; left: {{ $pOX }}%; top: {{ $pOY }}%; transform: translate(-50%, -50%) rotate({{ $pORot }}deg); width: {{ $pOW }}px; height: auto; opacity: {{ $pOOp }}; pointer-events: none;" crossorigin="anonymous">
                        @endif
                    @endforeach
                </div>
            @endif

        </div>
    </div>

    <script>
        function downloadCardPdf() {
            const btn = document.getElementById('btnDownloadPdf');
            let originalHtml = '';
            if (btn) {
                originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generating PDF...';
                btn.disabled = true;
            }

            const element = document.getElementById('printableCard');
            const safeName = "{{ preg_replace('/[^a-zA-Z0-9_\-]/', '_', $registration->name) }}";
            const filename = "Event_Pass_{{ $registration->registration_number }}_" + (safeName || 'delegate') + ".pdf";

            const opt = {
                margin:       0,
                filename:     filename,
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { 
                    scale: 3, 
                    useCORS: true, 
                    allowTaint: true,
                    logging: false,
                    letterRendering: true,
                    scrollY: 0
                },
                jsPDF:        { unit: 'in', format: [3.8, 5.4], orientation: 'portrait' }
            };

            // Ensure fonts are loaded before generating canvas
            const readyPromise = document.fonts ? document.fonts.ready : Promise.resolve();

            readyPromise.then(() => {
                return html2pdf().set(opt).from(element).save();
            }).then(() => {
                if (btn) {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }
            }).catch(err => {
                console.error('PDF Generation Error:', err);
                if (btn) {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }
                // Fallback to browser print dialog
                window.print();
            });
        }

        // Auto trigger download if URL has ?download=1 or ?pdf=1
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('download') === '1' || urlParams.get('pdf') === '1') {
                setTimeout(downloadCardPdf, 600);
            }
        });
    </script>

</body>
</html>
