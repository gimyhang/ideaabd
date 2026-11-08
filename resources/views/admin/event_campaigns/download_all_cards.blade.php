@php
    $cardDesign = $cardDesign ?? ($campaign->form_settings['card_design'] ?? []);
    
    // 1. Background Settings
    $bgImage        = $cardDesign['bg_image'] ?? null;
    $bgColor        = $cardDesign['bg_color'] ?? '#c98c21';
    
    // 2. Element Visibility Toggles
    $showHeader       = !empty($cardDesign['show_header']);
    $showPhoto        = $cardDesign['show_photo'] ?? true;
    $showQuote        = $cardDesign['show_quote'] ?? true;
    $showQrCode       = $cardDesign['show_qr_code'] ?? ($cardDesign['show_organizers'] ?? true);

    // 3. Sizing & Typography Controls
    $photoSize          = intval($cardDesign['photo_size'] ?? 86);
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
    $photoNameGap       = intval($cardDesign['photo_name_gap'] ?? 6);

    $nameFontSize       = intval($cardDesign['name_font_size'] ?? 18);
    $designationFontSize = intval($cardDesign['designation_font_size'] ?? 11);
    $locationFontSize    = intval($cardDesign['location_font_size'] ?? 10);
    $nameLineHeight     = floatval($cardDesign['name_line_height'] ?? 1.25);
    $nameSpacing        = intval($cardDesign['name_spacing'] ?? 2);
    $nameOffsetX        = intval($cardDesign['name_offset_x'] ?? 0);
    $nameOffsetY        = intval($cardDesign['name_offset_y'] ?? 0);
    $designationColor   = $cardDesign['designation_color'] ?? ($cardDesign['meta_color'] ?? '#f1f5f9');
    $locationColor      = $cardDesign['location_color'] ?? ($cardDesign['meta_color'] ?? '#e2e8f0');

    $fontFamily         = $cardDesign['font_family'] ?? 'Hind Siliguri';
    $textShadow         = $cardDesign['text_shadow'] ?? 'white';
    if ($textShadow === 'dark') {
        $textShadow = 'white';
    }
    $nameShadowStyle    = match($textShadow) {
        'none' => 'none',
        default => '0 0 5px #ffffff, 0 0 2px #ffffff, 0 1px 2px rgba(255, 255, 255, 0.95)',
    };
    $metaShadowStyle    = match($textShadow) {
        'none' => 'none',
        default => '0 0 4px #ffffff, 0 1px 2px rgba(255, 255, 255, 0.95)',
    };

    // Text Colors & Background Effects
    $annivColor         = $cardDesign['anniv_color'] ?? '#fef08a';
    $titleColor         = $cardDesign['title_color'] ?? '#ffffff';
    $subtitleColor      = $cardDesign['subtitle_color'] ?? '#fde047';
    $nameColor          = $cardDesign['name_color'] ?? '#ffffff';
    $rawQuoteColor      = $cardDesign['quote_color'] ?? null;
    if (empty($rawQuoteColor)) {
        $isNameDark = !empty($nameColor) && !in_array(strtolower($nameColor), ['#ffffff', '#fff', '#f8fafc', '#f1f5f9', '#fef08a']);
        $quoteColor = $isNameDark ? $nameColor : '#ffffff';
    } else {
        $quoteColor = $rawQuoteColor;
    }
    $bgOverlayOpacity   = intval($cardDesign['bg_overlay_opacity'] ?? 0);
    $bgOverlayColor     = $cardDesign['bg_overlay_color'] ?? '#000000';
    $bgBlur             = intval($cardDesign['bg_blur'] ?? 0);

    // Customizable Texts
    $anniversaryText = $cardDesign['anniversary_text'] ?? '২০ অক্টোবর ১৩তম প্রতিষ্ঠাবার্ষিকী উপলক্ষে';
    $titleText       = $cardDesign['title_text'] ?? 'রংপুর সাহিত্য উৎসব';
    $subtitleText    = $cardDesign['subtitle_text'] ?? 'ও ৩য় লিটিলম্যাগ মেলা';
    $quoteText       = $cardDesign['quote_text'] ?? "সাহিত্য উৎসব ও লিটিলম্যাগমেলায়\nআপনার উপস্থিতি ও অংশগ্রহণ\nআমাদের সম্মানিত করবে ।";
    $quoteOffsetX    = intval($cardDesign['quote_offset_x'] ?? 0);
    $quoteOffsetY    = intval($cardDesign['quote_offset_y'] ?? 0);
    $quoteSize       = intval($cardDesign['quote_font_size'] ?? 11);

    // Verification QR Code Settings
    $qrCodeSize     = intval($cardDesign['qr_code_size'] ?? 72);
    $qrOffsetX      = intval($cardDesign['qr_offset_x'] ?? 0);
    $qrOffsetY      = intval($cardDesign['qr_offset_y'] ?? 0);
    $qrCaptionText  = $cardDesign['qr_caption_text'] ?? 'উপস্থিতি যাচাই ও নিশ্চিতকরণ QR কোড';
    $qrSubtext      = $cardDesign['qr_subtext'] ?? 'ক্লিক বা স্ক্যান করলেই অনুষ্ঠানে উপস্থিতি নিশ্চিত হবে';

    $printCustomObjects = $cardDesign['custom_objects'] ?? [];
@endphp
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সকল কার্ড এক ক্লিকে ডাউনলোড ও প্রিন্ট — {{ $campaign->title }} (মোট {{ $totalCount }} টি)</title>
    
    <!-- Google Fonts for Bengali Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700;800&family=Noto+Serif+Bengali:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- html2canvas, JSZip, and FileSaver for 1-Click High-Speed Batch Card PNG Archive -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

    <style>
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        html, body {
            font-family: 'Hind Siliguri', 'SolaimanLipi', Arial, sans-serif;
            background-color: #0b1120;
            color: #1e1b4b;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        /* Top Action Bar (Hidden on Print) */
        .batch-action-bar {
            position: sticky;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            padding: 12px 24px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
        }
        .batch-title-group {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .batch-title {
            color: #ffffff;
            font-size: 15px;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .batch-badge {
            background: #10b981;
            color: #ffffff;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }
        .batch-btn-group {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn-batch {
            display: inline-flex;
            align-items: center;
            gap: 7px;
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
        .btn-zip { background: linear-gradient(135deg, #10b981, #059669); color: #ffffff; }
        .btn-pdf { background: linear-gradient(135deg, #dc2626, #b91c1c); color: #ffffff; }
        .btn-back { background: #334155; color: #ffffff; }
        .btn-batch:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0, 0, 0, 0.35); }
        .btn-batch:disabled { opacity: 0.6; cursor: not-allowed; }

        /* Filter Pills */
        .filter-pill-link {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            color: #cbd5e1;
            border: 1px solid rgba(255, 255, 255, 0.15);
            transition: all 0.2s ease;
        }
        .filter-pill-link.active {
            background: #facc15;
            color: #0f172a;
            border-color: #facc15;
            font-weight: 700;
        }

        /* Live Progress Modal */
        .progress-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 2000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .progress-box {
            background: #1e293b;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            padding: 28px;
            max-width: 480px;
            width: 100%;
            text-align: center;
            color: #ffffff;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
        }
        .progress-bar-track {
            height: 12px;
            background: #334155;
            border-radius: 10px;
            overflow: hidden;
            margin: 18px 0 12px 0;
        }
        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #38bdf8);
            width: 0%;
            transition: width 0.15s ease;
            border-radius: 10px;
        }

        /* Cards Grid Container */
        .cards-canvas-wrapper {
            padding: 30px 20px;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 28px;
            max-width: 1400px;
            margin: 0 auto;
        }
        .card-item-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }
        .card-single-action-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 365px;
            padding: 0 4px;
        }
        .card-label-reg {
            color: #94a3b8;
            font-size: 11px;
            font-family: monospace;
            font-weight: 600;
        }
        .btn-single-dl {
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.25);
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .btn-single-dl:hover {
            background: #10b981;
            color: #ffffff;
            border-color: #10b981;
        }

        /* ==========================================================================
           RSU OFFICIAL INVITATION CARD (365px x 518px / 3.8 x 5.4 inch Exact Layout)
           ========================================================================== */
        .rsu-invitation-card {
            width: 365px;
            height: 518px;
            min-height: 518px;
            max-height: 518px;
            box-sizing: border-box;
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.35);
            @if(!empty($bgDataUri))
                background: url('{{ $bgDataUri }}') no-repeat center center / cover;
            @elseif($bgImage)
                background: url('{{ asset("storage/" . $bgImage) }}') no-repeat center center / cover;
            @else
                background: {{ $bgColor }} linear-gradient(145deg, #c4871e 0%, #db9e2a 45%, #b57a15 100%);
            @endif
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 14px 14px 10px 14px;
            border: 1.5px solid rgba(255, 255, 255, 0.25);
            font-family: '{{ $fontFamily }}', 'Hind Siliguri', 'Noto Serif Bengali', serif;
        }

        .rsu-invitation-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(circle at 50% 30%, rgba(255, 255, 255, 0.12) 0%, rgba(0, 0, 0, 0.08) 100%);
            pointer-events: none;
        }

        /* 1. TOP HEADER SECTION */
        .card-top-section {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            width: 100%;
            padding: 2px 4px;
        }
        .festival-anniversary {
            color: {{ $annivColor }};
            font-size: 8.5px;
            font-weight: 700;
            letter-spacing: 0.3px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.6);
            margin-bottom: 2px;
        }
        .festival-main-title {
            color: {{ $titleColor }};
            font-family: '{{ $fontFamily }}', 'Noto Serif Bengali', 'Hind Siliguri', serif;
            font-size: 16px;
            font-weight: 900;
            line-height: 1.2;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.6);
            letter-spacing: -0.2px;
        }
        .festival-subtitle {
            color: {{ $subtitleColor }};
            font-family: '{{ $fontFamily }}', 'Noto Serif Bengali', 'Hind Siliguri', serif;
            font-size: 12.5px;
            font-weight: 800;
            line-height: 1.2;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.6);
            margin-top: 1px;
        }

        /* 2. MIDDLE SECTION: PHOTO & CLEAN TYPOGRAPHY (White Glow Shadow) */
        .card-middle-section {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            margin: 4px 0;
            width: 100%;
        }

        .author-photo-wrapper {
            margin-bottom: {{ $photoNameGap }}px;
            transform: translate({{ $photoOffsetX }}px, {{ $photoOffsetY }}px) scale({{ $photoScale / 100 }});
            position: relative;
            z-index: 4;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

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
            background-color: #f8fafc;
            position: relative;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
            overflow: hidden;
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
            border-radius: inherit;
        }

        /* Clean Typography with Soft White Glow */
        .author-text-container {
            width: 100%;
            text-align: center;
            position: relative;
            z-index: 3;
            padding: 0 6px;
            transform: translate({{ $nameOffsetX }}px, {{ $nameOffsetY }}px);
        }
        .author-text-container .author-name {
            font-family: '{{ $fontFamily }}', 'Noto Serif Bengali', 'Hind Siliguri', serif;
            font-size: {{ $nameFontSize }}px;
            font-weight: 800;
            color: {{ $nameColor }};
            line-height: {{ $nameLineHeight }};
            margin-bottom: {{ $nameSpacing }}px;
            text-shadow: {{ $nameShadowStyle }} !important;
            letter-spacing: 0.2px;
        }
        .author-text-container .author-designation {
            font-size: {{ $designationFontSize }}px;
            font-weight: 700;
            color: {{ $designationColor }};
            line-height: 1.25;
            margin-bottom: 2px;
            text-shadow: {{ $metaShadowStyle }} !important;
        }
        .author-text-container .author-location {
            font-size: {{ $locationFontSize }}px;
            font-weight: 600;
            color: {{ $locationColor }};
            opacity: 0.95;
            line-height: 1.2;
            text-shadow: {{ $metaShadowStyle }} !important;
        }

        /* 3. VERIFICATION QR CODE SECTION */
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
            transform: translate({{ $qrOffsetX }}px, {{ $qrOffsetY }}px);
        }

        /* 4. INVITATION MESSAGE SECTION */
        .card-message-section {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 3px 10px 2px 10px;
            transform: translate({{ $quoteOffsetX }}px, {{ $quoteOffsetY }}px);
        }
        .invitation-quote-text {
            color: {{ $quoteColor }};
            font-size: {{ $quoteSize }}px;
            font-weight: 700;
            line-height: 1.4;
            text-shadow: {{ $metaShadowStyle }} !important;
            width: 100%;
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
            max-width: 96%;
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

        /* PRINT STYLES (Exact 3.8 x 5.4 in per page) */
        @media print {
            @page {
                size: 3.8in 5.4in;
                margin: 0;
            }
            html, body {
                background: none !important;
                background-color: transparent !important;
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
            }
            .batch-action-bar, .card-single-action-bar, .progress-overlay, .no-print {
                display: none !important;
            }
            .cards-canvas-wrapper {
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
                max-width: none !important;
            }
            .card-item-container {
                page-break-after: always !important;
                page-break-inside: avoid !important;
                margin: 0 !important;
                padding: 0 !important;
                display: block !important;
                width: 3.8in !important;
                height: 5.4in !important;
            }
            .rsu-invitation-card {
                width: 3.8in !important;
                height: 5.4in !important;
                min-height: 5.4in !important;
                max-height: 5.4in !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                margin: 0 !important;
                page-break-inside: avoid !important;
                page-break-after: avoid !important;
            }
        }
    </style>
</head>
<body>

    <!-- Sticky Top Action Bar -->
    <header class="batch-action-bar no-print">
        <div class="batch-title-group">
            <a href="{{ route('admin.event-campaigns.show', $campaign->id) }}" class="btn-batch btn-back" title="ক্যাম্পেইনে ফিরে যান">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="batch-title">
                    <i class="fa-solid fa-file-zipper text-success"></i>
                    <span>অনুমোদিত কার্ড জিপ ডাউনলোড</span>
                    <span class="batch-badge">{{ $totalCount }} টি অনুমোদিত কার্ড</span>
                </h1>
                <small style="color: #94a3b8; font-size: 11px;">{{ $campaign->title }} • সিঙ্গেল কার্ড স্টাইল PNG প্যাকেজ</small>
            </div>
        </div>

        <div class="batch-btn-group">
            <button type="button" id="btnZipAll" onclick="startBatchZipDownload()" class="btn-batch btn-zip" style="font-size: 13px; padding: 10px 22px;">
                <i class="fa-solid fa-file-zipper"></i>
                <span id="btnZipText">সকল অনুমোদিত কার্ড জিপ ডাউনলোড (ZIP - All Single Cards)</span>
            </button>
            <button type="button" onclick="window.print()" class="btn-batch btn-pdf" title="ব্রাউজার থেকে সরাসরি প্রিন্ট বা 'Save as PDF' করুন">
                <i class="fa-solid fa-print"></i>
                <span>প্রিন্ট / PDF</span>
            </button>
        </div>
    </header>

    <!-- Live Batch Processing Progress Overlay Modal -->
    <div id="progressOverlay" class="progress-overlay">
        <div class="progress-box">
            <div style="font-size: 38px; color: #10b981; margin-bottom: 8px;">
                <i class="fa-solid fa-file-zipper fa-bounce"></i>
            </div>
            <h4 style="margin: 0 0 4px 0; font-size: 18px; font-weight: 800;">সব কার্ড তৈরি হচ্ছে...</h4>
            <div id="progressCurrentStatus" style="font-size: 13px; color: #cbd5e1; margin-bottom: 4px;">অপেক্ষা করুন...</div>
            <div class="progress-bar-track">
                <div id="progressBarFill" class="progress-bar-fill"></div>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 12px; color: #94a3b8; font-family: monospace;">
                <span id="progressCountText">০ / {{ $totalCount }}</span>
                <span id="progressPercentText">০%</span>
            </div>
            <div style="margin-top: 14px; font-size: 11px; color: #64748b;">
                সব কার্ড স্বয়ংক্রিয়ভাবে প্রসেস শেষে ZIP ফাইলটি আপনার কম্পিউটারে সেভ হবে।
            </div>
        </div>
    </div>

    <!-- Cards Grid -->
    <main class="cards-canvas-wrapper">
        @forelse($registrations as $index => $registration)
            @php
                $formData = $registration->form_data ?? [];

                // Candidate Photo: resolve base64 or asset
                $photoPath = $formData['student_photo'] ?? ($formData['photo'] ?? ($formData['author_photo'] ?? ($formData['applicant_photo'] ?? ($registration->photo ?? null))));
                $photoBgUrl = null;
                if (!empty($photoPath)) {
                    try {
                        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($photoPath)) {
                            $m = \Illuminate\Support\Facades\Storage::disk('public')->mimeType($photoPath) ?: 'image/jpeg';
                            $c = \Illuminate\Support\Facades\Storage::disk('public')->get($photoPath);
                            $photoBgUrl = 'data:' . $m . ';base64,' . base64_encode($c);
                        } else {
                            $photoBgUrl = asset('storage/' . $photoPath);
                        }
                    } catch (\Throwable $e) {
                        $photoBgUrl = asset('storage/' . $photoPath);
                    }
                }

                // Designation
                $catList = $formData['author_categories'] ?? (!empty($formData['author_category']) ? (is_array($formData['author_category']) ? $formData['author_category'] : explode(', ', $formData['author_category'])) : []);
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

                // Verification URL & BaconQrCode SVG Data URI (Zero Network Requests)
                $verifyUrl = route('event.attendance.verify', $registration->registration_number);
                $qrApiUrl  = \App\Services\BarcodeService::generateQrCodeDataUri($verifyUrl, $qrCodeSize);

                $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $registration->name);
                $filename = "Invitation_Card_{$registration->registration_number}_" . ($safeName ?: 'delegate') . ".png";
            @endphp

            <div class="card-item-container" id="cardContainer_{{ $registration->id }}">
                <div class="card-single-action-bar no-print">
                    <span class="card-label-reg">#{{ $index + 1 }} • {{ $registration->registration_number }}</span>
                    <button type="button" class="btn-single-dl" onclick="downloadSingleCard('card_{{ $registration->id }}', '{{ $filename }}')">
                        <i class="fa-solid fa-download me-1"></i> ডাউনলোড PNG
                    </button>
                </div>

                <div class="rsu-invitation-card card-export-target" id="card_{{ $registration->id }}" data-filename="{{ $filename }}" data-name="{{ $registration->name }}" data-reg="{{ $registration->registration_number }}">
                    
                    {{-- Background Overlay Tint --}}
                    @if($bgOverlayOpacity > 0 || $bgBlur > 0)
                        <div style="position: absolute; inset: 0; background: {{ $bgOverlayColor }}; opacity: {{ $bgOverlayOpacity / 100 }}; @if($bgBlur > 0) backdrop-filter: blur({{ $bgBlur }}px); -webkit-backdrop-filter: blur({{ $bgBlur }}px); @endif pointer-events: none; border-radius: 12px; z-index: 1;"></div>
                    @endif

                    {{-- 1. TOP HEADER SECTION --}}
                    @if($showHeader && (!empty($anniversaryText) || !empty($titleText) || !empty($subtitleText)))
                    <div class="card-top-section">
                        @if(!empty($anniversaryText))
                            <div class="festival-anniversary">{{ $anniversaryText }}</div>
                        @endif
                        @if(!empty($titleText))
                            <div class="festival-main-title">{{ $titleText }}</div>
                        @endif
                        @if(!empty($subtitleText))
                            <div class="festival-subtitle">{{ $subtitleText }}</div>
                        @endif
                    </div>
                    @endif

                    {{-- 2. CENTER SECTION: AUTHOR PHOTO & CLEAN TYPOGRAPHY (White Glow) --}}
                    <div class="card-middle-section">
                        @if($showPhoto)
                            <div class="author-photo-wrapper">
                                <div class="author-photo-frame">
                                    @if($photoBgUrl)
                                        <img src="{{ $photoBgUrl }}" alt="{{ $registration->name }}" class="author-actual-photo" style="width: 100%; height: 100%; object-fit: cover; border-radius: inherit; display: block; filter: brightness({{ $photoBrightness }}%) contrast({{ $photoContrast }}%) grayscale({{ $photoGrayscale }}%) sepia({{ $photoSepia }}%);" crossorigin="anonymous">
                                    @else
                                        <div class="photo-placeholder">
                                            <i class="fa-solid fa-user-pen"></i>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <div class="author-text-container">
                            <div class="author-name" style="text-shadow: {{ $nameShadowStyle }} !important;">{{ $registration->name }}</div>
                            @if(!empty($designation))
                                <div class="author-designation" style="text-shadow: {{ $metaShadowStyle }} !important;">{{ $designation }}</div>
                            @endif
                            @if(!empty($location))
                                <div class="author-location" style="text-shadow: {{ $metaShadowStyle }} !important;">
                                    <i class="fa-solid fa-location-dot" style="font-size: 8.5px; opacity: 0.85; margin-right: 2px;"></i>{{ $location }}
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- 3. VERIFICATION QR CODE SECTION --}}
                    @if($showQrCode)
                        <div class="card-verify-qr-section">
                            <a href="{{ $verifyUrl }}" target="_blank" class="verify-qr-anchor">
                                <div class="qr-code-box" style="width: {{ $qrCodeSize }}px; height: {{ $qrCodeSize }}px;">
                                    <img src="{{ $qrApiUrl }}" alt="Verify QR Code" style="width: 100%; height: 100%; object-fit: contain; display: block;">
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

                    {{-- 4. BOTTOM INVITATION MESSAGE SECTION --}}
                    @if($showQuote)
                        <div class="card-message-section">
                            <div class="invitation-quote-text" style="color: {{ $quoteColor }}; text-shadow: {{ $metaShadowStyle }} !important;">
                                {!! nl2br(e($quoteText)) !!}
                            </div>
                        </div>
                    @endif

                    {{-- 5. FLOATING CUSTOM OBJECTS / STICKERS LAYER --}}
                    @if(is_array($printCustomObjects) && count($printCustomObjects) > 0)
                        <div style="position: absolute; inset: 0; pointer-events: none; z-index: 10; overflow: hidden; border-radius: 12px;">
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
        @empty
            <div style="color: #94a3b8; text-align: center; padding: 60px 20px;">
                <i class="fa-solid fa-circle-exclamation fa-3x mb-3 text-warning"></i>
                <h4 style="color: #ffffff;">কোনো কার্ড পাওয়া যায়নি</h4>
                <p>বর্তমান ফিল্টার অনুযায়ী কোনো অংশগ্রহণকারী নেই।</p>
                <a href="{{ route('admin.event-campaigns.cards.download-all', ['campaign' => $campaign->id, 'status' => 'all']) }}" class="btn-batch btn-zip" style="display: inline-flex; margin-top: 10px;">
                    সকল কার্ড দেখুন
                </a>
            </div>
        @endforelse
    </main>

    <script>
        // Single Card Quick Download
        async function downloadSingleCard(cardId, filename) {
            const cardEl = document.getElementById(cardId);
            if (!cardEl) return;
            
            const readyPromise = document.fonts ? document.fonts.ready : Promise.resolve();
            await readyPromise;

            try {
                const canvas = await html2canvas(cardEl, {
                    scale: 2.5,
                    windowWidth: 1200,
                    windowHeight: 900,
                    width: 365,
                    height: 518,
                    useCORS: true,
                    allowTaint: false,
                    backgroundColor: null,
                    scrollX: 0,
                    scrollY: 0,
                    onclone: (clonedDoc) => {
                        const target = clonedDoc.getElementById(cardId);
                        if (target) {
                            target.style.transform = 'none';
                            target.style.width = '365px';
                            target.style.minWidth = '365px';
                            target.style.maxWidth = '365px';
                            target.style.height = '518px';
                            target.style.minHeight = '518px';
                            target.style.maxHeight = '518px';
                            target.style.boxShadow = 'none';
                            target.style.margin = '0 auto';
                        }
                    }
                });

                canvas.toBlob(blob => {
                    if (window.saveAs) {
                        saveAs(blob, filename);
                    } else {
                        const link = document.createElement('a');
                        link.download = filename;
                        link.href = URL.createObjectURL(blob);
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    }
                }, 'image/png');
            } catch (err) {
                console.error('Error generating card image:', err);
                alert('ছবি তৈরি করতে ত্রুটি হয়েছে: ' + (err.message || err));
            }
        }

        // 1-Click All Cards Batch ZIP Downloader
        async function startBatchZipDownload() {
            const cardElements = document.querySelectorAll('.card-export-target');
            const total = cardElements.length;
            if (total === 0) {
                alert('ডাউনলোড করার মতো কোনো কার্ড পাওয়া যায়নি!');
                return;
            }

            if (typeof JSZip === 'undefined' || typeof html2canvas === 'undefined') {
                alert('প্রয়োজনীয় লাইব্রেরি লোড হচ্ছে, অনুগ্রহ করে কয়েক সেকেন্ড পর আবার চেষ্টা করুন।');
                return;
            }

            const btnZip = document.getElementById('btnZipAll');
            const btnText = document.getElementById('btnZipText');
            const progressOverlay = document.getElementById('progressOverlay');
            const progressBarFill = document.getElementById('progressBarFill');
            const progressCountText = document.getElementById('progressCountText');
            const progressPercentText = document.getElementById('progressPercentText');
            const progressCurrentStatus = document.getElementById('progressCurrentStatus');

            // Lock UI & show modal
            btnZip.disabled = true;
            progressOverlay.style.display = 'flex';

            const zip = new JSZip();
            const readyPromise = document.fonts ? document.fonts.ready : Promise.resolve();
            await readyPromise;

            let completed = 0;

            for (let i = 0; i < total; i++) {
                const cardEl = cardElements[i];
                const cardName = cardEl.getAttribute('data-name') || 'Delegate';
                const cardReg = cardEl.getAttribute('data-reg') || ('card_' + (i + 1));
                const filename = cardEl.getAttribute('data-filename') || (cardReg + '.png');

                // Update Progress Modal
                progressCurrentStatus.textContent = `প্রস্তুত হচ্ছে: ${cardName} (${cardReg})...`;
                progressCountText.textContent = `${i + 1} / ${total}`;
                const pct = Math.round(((i + 1) / total) * 100);
                progressPercentText.textContent = `${pct}%`;
                progressBarFill.style.width = `${pct}%`;

                try {
                    const canvas = await html2canvas(cardEl, {
                        scale: 2.5, // 2.5x gives 912x1295px razor-sharp studio quality matching single card print
                        windowWidth: 1200,
                        windowHeight: 900,
                        width: 365,
                        height: 518,
                        useCORS: true,
                        allowTaint: false,
                        backgroundColor: null,
                        logging: false,
                        scrollX: 0,
                        scrollY: 0,
                        onclone: (clonedDoc) => {
                            const target = clonedDoc.getElementById(cardEl.id);
                            if (target) {
                                target.style.transform = 'none';
                                target.style.width = '365px';
                                target.style.minWidth = '365px';
                                target.style.maxWidth = '365px';
                                target.style.height = '518px';
                                target.style.minHeight = '518px';
                                target.style.maxHeight = '518px';
                                target.style.boxShadow = 'none';
                                target.style.margin = '0 auto';
                            }
                        }
                    });

                    await new Promise(resolve => {
                        canvas.toBlob(blob => {
                            if (blob) {
                                zip.file(filename, blob);
                            }
                            resolve();
                        }, 'image/png');
                    });

                    completed++;
                } catch (err) {
                    console.error('Card capture error on ' + cardReg, err);
                }

                // Yield to browser event loop every 2 cards for smooth progress animation and memory GC
                if (i % 2 === 0) {
                    await new Promise(r => setTimeout(r, 10));
                }
            }

            // Generate ZIP Archive
            progressCurrentStatus.textContent = 'সকল কার্ড জিপ ফাইলে প্যাকেজ করা হচ্ছে...';
            
            try {
                const zipContent = await zip.generateAsync({
                    type: 'blob',
                    compression: 'DEFLATE',
                    compressionOptions: { level: 6 }
                }, function updateCallback(metadata) {
                    progressPercentText.textContent = `${Math.round(metadata.percent)}%`;
                });

                const zipFilename = "All_Approved_Cards_{{ preg_replace('/[^a-zA-Z0-9_\-]/', '_', $campaign->title) }}_Total_{{ $totalCount }}.zip";
                
                if (window.saveAs) {
                    saveAs(zipContent, zipFilename);
                } else {
                    const link = document.createElement('a');
                    link.download = zipFilename;
                    link.href = URL.createObjectURL(zipContent);
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }

                progressCurrentStatus.innerHTML = '<span style="color: #10b981; font-weight: bold;"><i class="fa-solid fa-circle-check"></i> সফলভাবে জিপ ডাউনলোড সম্পন্ন হয়েছে!</span>';
                setTimeout(() => {
                    progressOverlay.style.display = 'none';
                    btnZip.disabled = false;
                }, 1800);
            } catch (err) {
                console.error('ZIP generation error:', err);
                alert('ZIP ফাইল তৈরিতে সমস্যা হয়েছে: ' + (err.message || err));
                progressOverlay.style.display = 'none';
                btnZip.disabled = false;
            }
        }

        // Auto-start download if requested via query param ?autostart=1
        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('autostart') === '1' && {{ $totalCount }} > 0) {
                setTimeout(() => {
                    startBatchZipDownload();
                }, 600);
            }
        });
    </script>
</body>
</html>
