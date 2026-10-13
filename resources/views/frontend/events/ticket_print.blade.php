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
    $showEventLogo    = !empty($cardDesign['show_event_logo']);
    $eventLogoSize    = intval($cardDesign['event_logo_size'] ?? 64);
    $eventLogoOffsetX = intval($cardDesign['event_logo_offset_x'] ?? 0);
    $eventLogoOffsetY = intval($cardDesign['event_logo_offset_y'] ?? 0);
    $showBadge      = !empty($cardDesign['show_badge']);
    $showPhoto      = $cardDesign['show_photo'] ?? true;
    $showNamePlate  = $cardDesign['show_name_plate'] ?? true;
    $showQuote      = $cardDesign['show_quote'] ?? true;
    $showArtwork    = $cardDesign['show_artwork'] ?? true;
    $showOrganizers = $cardDesign['show_organizers'] ?? true;
    $headerHeight   = intval($cardDesign['header_height'] ?? 90);

    // 3. Sizing & Typography Controls (ছোট বড়ো করার সেটিংস)
    $photoSize          = intval($cardDesign['photo_size'] ?? 82);
    $photoOffsetX       = intval($cardDesign['photo_offset_x'] ?? 0);
    $photoOffsetY       = intval($cardDesign['photo_offset_y'] ?? 0);
    $photoScale         = intval($cardDesign['photo_scale'] ?? 100);
    $photoBorderRadius  = $cardDesign['photo_border_radius'] ?? '50%';
    $photoBorderWidth   = intval($cardDesign['photo_border_width'] ?? 3);
    $photoBorderColor   = $cardDesign['photo_border_color'] ?? '#ffffff';
    $photoShadow        = $cardDesign['photo_shadow'] ?? 'soft';
    $photoBrightness    = intval($cardDesign['photo_brightness'] ?? 100);
    $photoContrast      = intval($cardDesign['photo_contrast'] ?? 100);
    $photoGrayscale     = intval($cardDesign['photo_grayscale'] ?? 0);
    $photoSepia         = intval($cardDesign['photo_sepia'] ?? 0);
    $photoNameGap       = intval($cardDesign['photo_name_gap'] ?? 10);

    $nameFontSize       = intval($cardDesign['name_font_size'] ?? 16);
    $designationFontSize = intval($cardDesign['designation_font_size'] ?? 10);
    $locationFontSize    = intval($cardDesign['location_font_size'] ?? 9);
    $nameLineHeight     = floatval($cardDesign['name_line_height'] ?? 1.25);
    $nameSpacing        = intval($cardDesign['name_spacing'] ?? 2);
    $platePadding       = intval($cardDesign['plate_padding'] ?? 14);
    $showPlateBg        = isset($cardDesign['show_plate_bg']) ? !empty($cardDesign['show_plate_bg']) : true;
    $plateBgOpacity     = intval($cardDesign['plate_bg_opacity'] ?? 100);
    $plateBorderWidth   = intval($cardDesign['plate_border_width'] ?? 1);
    $plateBorderColor   = $cardDesign['plate_border_color'] ?? '#d8be92';
    $plateBorderRadius  = intval($cardDesign['plate_border_radius'] ?? 14);
    $designationColor   = $cardDesign['designation_color'] ?? ($cardDesign['meta_color'] ?? '#334155');
    $locationColor      = $cardDesign['location_color'] ?? ($cardDesign['meta_color'] ?? '#475569');

    // Calculate RGBA for plate background
    $plateAlpha = ($plateBgOpacity / 100);
    $hexClean = ltrim($cardDesign['plate_bg_color'] ?? '#ecd8b4', '#');
    if (strlen($hexClean) === 3) {
        $pRed = hexdec(substr($hexClean, 0, 1) . substr($hexClean, 0, 1));
        $pGreen = hexdec(substr($hexClean, 1, 1) . substr($hexClean, 1, 1));
        $pBlue = hexdec(substr($hexClean, 2, 1) . substr($hexClean, 2, 1));
    } elseif (strlen($hexClean) >= 6) {
        $pRed = hexdec(substr($hexClean, 0, 2));
        $pGreen = hexdec(substr($hexClean, 2, 2));
        $pBlue = hexdec(substr($hexClean, 4, 2));
    } else {
        $pRed = 236; $pGreen = 216; $pBlue = 180;
    }
    $effectivePlateBg = $showPlateBg ? "rgba({$pRed}, {$pGreen}, {$pBlue}, {$plateAlpha})" : 'transparent';
    $effectivePlateBorder = ($showPlateBg && $plateBorderWidth > 0) ? "{$plateBorderWidth}px solid {$plateBorderColor}" : 'none';
    $effectivePlateShadow = $showPlateBg ? '0 4px 14px rgba(0, 0, 0, 0.12)' : 'none';

    $logoSize           = intval($cardDesign['logo_size'] ?? 58);
    $logoOffsetX        = intval($cardDesign['logo_offset_x'] ?? 0);
    $logoOffsetY        = intval($cardDesign['logo_offset_y'] ?? 0);
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

    // 5. Verification QR Code & Attendance Settings (স্বাক্ষর বাদ হয়ে অটোমেটিক ভেরিফাই কিউ আর কোড)
    $showQrCode     = $cardDesign['show_qr_code'] ?? ($cardDesign['show_organizers'] ?? true);
    $qrCodeSize     = intval($cardDesign['qr_code_size'] ?? 78);
    $qrCaptionText  = $cardDesign['qr_caption_text'] ?? 'উপস্থিতি যাচাই ও নিশ্চিতকরণ QR কোড';
    $qrSubtext      = $cardDesign['qr_subtext'] ?? 'ক্লিক বা স্ক্যান করলেই অনুষ্ঠানে উপস্থিতি নিশ্চিত হবে';
    $verifyUrl      = route('event.attendance.verify', $registration->registration_number);
    $qrApiUrl       = 'https://api.qrserver.com/v1/create-qr-code/?size=' . ($qrCodeSize * 2) . 'x' . ($qrCodeSize * 2) . '&data=' . urlencode($verifyUrl);

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
    $catList = $formData['author_categories'] ?? ($formData['author_category'] ? (is_array($formData['author_category']) ? $formData['author_category'] : explode(', ', $formData['author_category'])) : []);
    if (!empty($catList) && is_array($catList)) {
        $designation = implode(' • ', array_slice($catList, 0, 2));
    } elseif (!empty($registration->designation_or_class)) {
        $designation = $registration->designation_or_class;
    } else {
        $designation = $formData['author_category'] ?? 'কবি, লেখক ও সাহিত্যিক';
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
    
    <!-- html2canvas and html2pdf for robust Bengali Card Image & PDF Export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
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
            background-color: #0f172a;
            color: #1e1b4b;
            margin: 0;
            padding: 20px 10px;
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

        /* Suppress any intrusive third-party ads or offerwall overlay conflicts */
        ins.adsbygoogle, .google-auto-placed, #monetag-overlay, .ad-banner, .rewarded-ad-overlay, .adsense-slot {
            display: none !important;
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
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-bottom: 18px;
            z-index: 100;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 18px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            border: none;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
            transition: all 0.2s ease;
        }
        .btn-image { background: linear-gradient(135deg, #10b981, #059669); color: #ffffff; }
        .btn-pdf { background: linear-gradient(135deg, #dc2626, #b91c1c); color: #ffffff; }
        .btn-print { background: #334155; color: #ffffff; }
        .btn-back { background: #ffffff; color: #0f172a; }
        .btn-action:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0, 0, 0, 0.35); }
        .status-badge-pill {
            background: #fef08a;
            color: #854d0e;
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 700;
            border: 1px solid #facc15;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

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
            margin-bottom: {{ $photoNameGap }}px; /* Cleanly separated from name plate shade */
            transform: translate({{ $photoOffsetX }}px, {{ $photoOffsetY }}px) scale({{ $photoScale / 100 }});
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

        /* Author Name Plate & Separated Text Styling */
        .author-name-plate {
            width: 92%;
            background: {{ $effectivePlateBg }};
            border-radius: {{ $plateBorderRadius }}px;
            box-shadow: {{ $effectivePlateShadow }};
            padding: {{ $platePadding }}px 12px;
            text-align: center;
            border: {{ $effectivePlateBorder }};
            position: relative;
            z-index: 3;
            transition: all 0.2s ease;
        }
        .author-name-plate .author-name {
            font-family: '{{ $fontFamily }}', 'Noto Serif Bengali', 'Hind Siliguri', serif;
            font-size: {{ $nameFontSize }}px;
            font-weight: 800;
            color: {{ $nameColor }};
            line-height: {{ $nameLineHeight }};
            margin-bottom: {{ $nameSpacing }}px;
            @if(!$showPlateBg)
                text-shadow: 0 1px 3px rgba(0, 0, 0, 0.45);
            @endif
        }
        .author-name-plate .author-designation {
            font-size: {{ $designationFontSize }}px;
            font-weight: 700;
            color: {{ $designationColor }};
            line-height: 1.25;
            margin-bottom: 2px;
            @if(!$showPlateBg)
                text-shadow: 0 1px 2px rgba(0, 0, 0, 0.35);
            @endif
        }
        .author-name-plate .author-location {
            font-size: {{ $locationFontSize }}px;
            font-weight: 600;
            color: {{ $locationColor }};
            opacity: 0.9;
            line-height: 1.2;
            @if(!$showPlateBg)
                text-shadow: 0 1px 2px rgba(0, 0, 0, 0.35);
            @endif
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
           BOTTOM DYNAMIC VERIFICATION QR CODE SECTION (স্বাক্ষর বাদ হয়ে অটোমেটিক ভেরিফাই কিউ আর কোড)
           ========================================================================== */
        .card-verify-qr-section {
            position: relative;
            z-index: 2;
            border-top: 1.5px solid rgba(255, 255, 255, 0.45);
            padding-top: 5px;
            margin-top: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .verify-qr-anchor {
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: rgba(0, 0, 0, 0.35);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            padding: 5px 12px 5px 6px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.35);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.28);
            transition: all 0.2s ease;
            max-width: 96%;
        }
        .verify-qr-anchor:hover {
            transform: translateY(-1px) scale(1.02);
            background: rgba(0, 0, 0, 0.45);
            border-color: rgba(255, 255, 255, 0.6);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.35);
        }
        .qr-code-box {
            background: #ffffff;
            padding: 4px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .qr-info-box {
            text-align: left;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .qr-verify-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 8px;
            font-weight: 800;
            background: #10b981;
            color: #ffffff;
            padding: 1.5px 7px;
            border-radius: 20px;
            width: fit-content;
            letter-spacing: 0.3px;
            margin-bottom: 2px;
        }
        .qr-title {
            color: #ffffff;
            font-size: 10px;
            font-weight: 800;
            line-height: 1.25;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
        }
        .qr-subtitle {
            color: rgba(255, 255, 255, 0.92);
            font-size: 7.8px;
            font-weight: 600;
            line-height: 1.3;
            margin-top: 1px;
        }
        .qr-url-text {
            color: #fef08a;
            font-size: 7.5px;
            font-family: monospace;
            font-weight: 700;
            margin-top: 1.5px;
            opacity: 0.95;
            letter-spacing: 0.2px;
        }
    </style>
</head>
<body>

    {{-- Top Action Bar (Hidden on Print / PDF) --}}
    @if(!$isPdf)
        <div class="action-bar no-print">
            <div class="status-badge-pill">
                <i class="fa-solid fa-feather-pointed"></i> আমন্ত্রণ কার্ড • লেখক কপি
            </div>
            <button type="button" id="btnDownloadImage" onclick="downloadCardImage()" class="btn-action btn-image">
                <i class="fa-solid fa-image"></i> কার্ড ছবি ডাউনলোড (PNG)
            </button>
            <button type="button" id="btnDownloadPdf" onclick="downloadCardPdf()" class="btn-action btn-pdf">
                <i class="fa-solid fa-file-pdf"></i> PDF ডাউনলোড
            </button>
            <button type="button" onclick="window.print()" class="btn-action btn-print">
                <i class="fa-solid fa-print"></i> প্রিন্ট করুন
            </button>
            <a href="{{ url('/') }}" class="btn-action btn-back">
                <i class="fa-solid fa-house"></i> হোমপেজ
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
                <div class="header-left-col" style="min-width: {{ $logoSize }}px; display: flex; align-items: center; justify-content: flex-start; position: relative;">
                    @if($showLogo && !empty($logoImage))
                        <div class="phiredekha-logo" style="width: {{ $logoSize }}px; height: {{ $logoSize }}px; transform: translate({{ $logoOffsetX }}px, {{ $logoOffsetY }}px);">
                            <img src="{{ asset('storage/' . $logoImage) }}" alt="Logo" class="custom-logo-img" crossorigin="anonymous">
                        </div>
                    @endif
                </div>

                {{-- Center: Event Logo & Title --}}
                <div class="festival-text-wrap" style="flex-grow: 1; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative;">
                    @if($showEventLogo && !empty($eventLogoImage))
                        <div class="event-header-logo-wrap" style="display: flex; justify-content: center; align-items: center; margin-bottom: {{ $showHeader ? '2px' : '0' }}; transform: translate({{ $eventLogoOffsetX }}px, {{ $eventLogoOffsetY }}px);">
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
                            @if(!empty($photoPath))
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

            {{-- 4. BOTTOM DYNAMIC VERIFICATION QR CODE SECTION (স্বাক্ষর বাদ হয়ে অটোমেটিক ভেরিফাই কিউ আর কোড ইউআরএলসহ) --}}
            @if($showQrCode)
                <div class="card-verify-qr-section">
                    <a href="{{ $verifyUrl }}" target="_blank" class="verify-qr-anchor" title="ক্লিক করলেই অনুষ্ঠানে উপস্থিতি নিশ্চিত হবে">
                        <div class="qr-code-box" style="width: {{ $qrCodeSize }}px; height: {{ $qrCodeSize }}px;">
                            <img src="{{ $qrApiUrl }}" alt="Verify QR Code" style="width: 100%; height: 100%; object-fit: contain; display: block;" crossorigin="anonymous">
                        </div>
                        <div class="qr-info-box">
                            <div class="qr-verify-badge">
                                <i class="fa-solid fa-circle-check" style="font-size: 7px;"></i> LIVE VERIFIED
                            </div>
                            <div class="qr-title">
                                {{ $qrCaptionText }}
                            </div>
                            <div class="qr-subtitle">
                                <i class="fa-solid fa-arrow-pointer" style="font-size: 6.5px; color: #facc15; margin-right: 2px;"></i> {{ $qrSubtext }}
                            </div>
                            <div class="qr-url-text">
                                {{ url('/v/' . $registration->registration_number) }}
                            </div>
                        </div>
                    </a>
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
        function downloadCardImage() {
            const btn = document.getElementById('btnDownloadImage');
            let originalHtml = '';
            if (btn) {
                originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> কার্ড তৈরি হচ্ছে...';
                btn.disabled = true;
            }

            const element = document.getElementById('printableCard');
            const safeName = "{{ preg_replace('/[^a-zA-Z0-9_\-]/', '_', $registration->name) }}";
            const filename = "Invitation_Card_{{ $registration->registration_number }}_" + (safeName || 'delegate') + ".png";

            const readyPromise = document.fonts ? document.fonts.ready : Promise.resolve();

            readyPromise.then(() => {
                return html2canvas(element, {
                    scale: 3,
                    useCORS: true,
                    allowTaint: true,
                    logging: false,
                    backgroundColor: null,
                    scrollY: 0
                });
            }).then(canvas => {
                const link = document.createElement('a');
                link.download = filename;
                link.href = canvas.toDataURL('image/png', 1.0);
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                if (btn) {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }
            }).catch(err => {
                console.error('Image Generation Error:', err);
                if (btn) {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }
                // Fallback to PDF download
                downloadCardPdf();
            });
        }

        function downloadCardPdf() {
            const btn = document.getElementById('btnDownloadPdf');
            let originalHtml = '';
            if (btn) {
                originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> PDF তৈরি হচ্ছে...';
                btn.disabled = true;
            }

            const element = document.getElementById('printableCard');
            const safeName = "{{ preg_replace('/[^a-zA-Z0-9_\-]/', '_', $registration->name) }}";
            const filename = "Invitation_Card_{{ $registration->registration_number }}_" + (safeName || 'delegate') + ".pdf";

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

        // Auto trigger download if URL has ?download=1 or ?pdf=1 or ?image=1
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('image') === '1') {
                setTimeout(downloadCardImage, 600);
            } else if (urlParams.get('download') === '1' || urlParams.get('pdf') === '1') {
                setTimeout(downloadCardPdf, 600);
            }
        });
    </script>

</body>
</html>
