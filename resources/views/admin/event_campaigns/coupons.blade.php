@php
    $eventTitle = $campaign->title ?: 'রংপুর সাহিত্য উৎসব ও লেখক সমাবেশ ২০২৬';
    $eventDate = $campaign->event_date ? date('d M Y', strtotime($campaign->event_date)) : '৭ মার্চ ২০২৬';
@endphp
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>খাবার ও র‍্যাফেল ড্র কুপন — {{ $eventTitle }} (মোট {{ $totalCount }} টি)</title>

    <!-- Google Fonts for Bengali Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700;800&family=Noto+Serif+Bengali:wght@600;700;800;900&family=Libre+Barcode+39+Text&family=Fira+Code:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Libraries for Client-Side Batch & Single PNG / ZIP Download -->
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
            background-color: #0f172a;
            color: #0f172a;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        /* Top Action Bar (Screen Only) */
        .coupon-topbar {
            position: sticky;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: rgba(15, 23, 42, 0.96);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            padding: 10px 20px;
            color: #ffffff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
        }

        .btn-status-filter {
            font-size: 12px;
            padding: 5px 12px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #e2e8f0;
            background: rgba(255, 255, 255, 0.05);
        }
        .btn-status-filter:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.15);
        }
        .btn-status-filter.active-approved {
            background: #10b981;
            color: #ffffff;
            border-color: #10b981;
            box-shadow: 0 0 10px rgba(16, 185, 129, 0.4);
        }
        .btn-status-filter.active-pending {
            background: #f59e0b;
            color: #0f172a;
            border-color: #f59e0b;
            font-weight: 700;
            box-shadow: 0 0 10px rgba(245, 158, 11, 0.4);
        }
        .btn-status-filter.active-all {
            background: #3b82f6;
            color: #ffffff;
            border-color: #3b82f6;
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.4);
        }

        /* Search input */
        .coupon-search-input {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: #ffffff;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            outline: none;
            width: 180px;
            transition: all 0.2s;
        }
        .coupon-search-input:focus {
            background: rgba(255, 255, 255, 0.2);
            border-color: #facc15;
            width: 220px;
        }
        .coupon-search-input::placeholder {
            color: #94a3b8;
        }

        /* Main Screen Container */
        .coupons-screen-stage {
            padding: 24px 16px 60px 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

        /* Strip Card Wrapper (Holds the 8x2 inch strip and screen action controls) */
        .coupon-strip-row {
            display: flex;
            align-items: center;
            gap: 16px;
            width: 100%;
            max-width: 960px;
            justify-content: center;
        }

        .strip-side-actions {
            display: flex;
            flex-direction: column;
            gap: 6px;
            min-width: 120px;
        }

        .btn-strip-download {
            background: #1e293b;
            color: #38bdf8;
            border: 1px solid #334155;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s;
            text-decoration: none;
        }
        .btn-strip-download:hover {
            background: #38bdf8;
            color: #0f172a;
        }

        /* ==========================================================================
           EXACT 8 INCH x 2 INCH COUPON STRIP (৫ কলাম বিশিষ্ট খাবার ও ড্র কুপন)
           ========================================================================== */
        .coupon-strip {
            width: 8in;
            min-width: 8in;
            max-width: 8in;
            height: 2in;
            min-height: 2in;
            max-height: 2in;
            background: #ffffff;
            border: 1.5px solid #1e293b;
            border-radius: 5px;
            display: grid;
            grid-template-columns: repeat(5, 1.6in);
            overflow: hidden;
            position: relative;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.25);
            background-color: #ffffff;
        }

        /* Each of the 5 Columns */
        .coupon-col {
            width: 1.6in;
            height: 2in;
            padding: 4px 6px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
            font-family: 'Hind Siliguri', sans-serif;
            background: #ffffff;
        }

        /* Vertical Dashed Perforation Line Between Columns */
        .coupon-col:not(:last-child) {
            border-right: 1.5px dashed #94a3b8;
        }

        /* Little Scissors Perforation Icon Guide */
        .cut-guide-icon {
            position: absolute;
            right: -6px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 8px;
            color: #64748b;
            background: #ffffff;
            padding: 2px 0;
            line-height: 1;
            z-index: 10;
        }

        /* Coupon Header Badge */
        .coupon-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            padding: 2px 4px;
            border-radius: 4px;
            font-size: 10.5px;
            font-weight: 800;
            color: #ffffff;
            text-align: center;
            letter-spacing: -0.2px;
            line-height: 1.2;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
        }

        /* 5 Distinct Coupon Color Themes */
        /* 1. সকালের নাস্তা (Breakfast) - Amber/Gold */
        .coupon-col-breakfast {
            background: linear-gradient(180deg, #fffbeb 0%, #ffffff 100%);
        }
        .coupon-col-breakfast .coupon-badge {
            background: linear-gradient(135deg, #d97706, #b45309);
        }
        .coupon-col-breakfast .coupon-card-badge {
            border-color: #d97706;
            color: #92400e;
        }

        /* 2. সকালের স্নাকস (Morning Snacks) - Emerald */
        .coupon-col-snacks1 {
            background: linear-gradient(180deg, #ecfdf5 0%, #ffffff 100%);
        }
        .coupon-col-snacks1 .coupon-badge {
            background: linear-gradient(135deg, #059669, #047857);
        }
        .coupon-col-snacks1 .coupon-card-badge {
            border-color: #059669;
            color: #065f46;
        }

        /* 3. দুপুরের খাবার (Lunch) - Royal Blue / Indigo */
        .coupon-col-lunch {
            background: linear-gradient(180deg, #eff6ff 0%, #ffffff 100%);
        }
        .coupon-col-lunch .coupon-badge {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
        }
        .coupon-col-lunch .coupon-card-badge {
            border-color: #2563eb;
            color: #1e40af;
        }

        /* 4. বিকেলের স্নাকস (Afternoon Snacks) - Purple / Violet */
        .coupon-col-snacks2 {
            background: linear-gradient(180deg, #faf5ff 0%, #ffffff 100%);
        }
        .coupon-col-snacks2 .coupon-badge {
            background: linear-gradient(135deg, #7c3aed, #6d28d9);
        }
        .coupon-col-snacks2 .coupon-card-badge {
            border-color: #7c3aed;
            color: #5b21b6;
        }

        /* 5. র‍্যাফেল ড্র কুপন (Raffle Draw) - Rose / Crimson */
        .coupon-col-raffle {
            background: linear-gradient(180deg, #fff1f2 0%, #ffffff 100%);
        }
        .coupon-col-raffle .coupon-badge {
            background: linear-gradient(135deg, #e11d48, #be123c);
        }
        .coupon-col-raffle .coupon-card-badge {
            border-color: #e11d48;
            color: #9f1239;
            background: #ffe4e6;
        }

        /* Event Sub-Header */
        .coupon-event-name {
            font-size: 7.2px;
            font-weight: 700;
            color: #334155;
            text-align: center;
            line-height: 1.1;
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Prominent Card Number Badge */
        .coupon-card-badge {
            background: #ffffff;
            border: 1.2px solid #0f172a;
            border-radius: 4px;
            padding: 1.5px 3px;
            text-align: center;
            font-family: 'Fira Code', monospace;
            font-size: 11px;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.3px;
            margin: 2px 0;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        /* Participant Name & Category */
        .coupon-participant-info {
            text-align: center;
            line-height: 1.15;
            padding: 0 1px;
        }
        .coupon-participant-name {
            font-size: 9px;
            font-weight: 700;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
        }
        .coupon-participant-category {
            font-size: 7.5px;
            font-weight: 600;
            color: #64748b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
        }

        /* Footer & Barcode simulation */
        .coupon-footer {
            border-top: 0.8px solid #cbd5e1;
            padding-top: 2px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 6.5px;
            color: #64748b;
            font-weight: 600;
            line-height: 1;
        }
        .coupon-serial {
            font-family: 'Fira Code', monospace;
            font-size: 6.8px;
            color: #334155;
            font-weight: 700;
        }
        .coupon-barcode-graphic {
            letter-spacing: 0.5px;
            font-family: monospace;
            font-size: 8px;
            font-weight: 900;
            color: #0f172a;
            line-height: 0.9;
        }

        /* Raffle draw specific counterfoil styling */
        .raffle-box-drop {
            font-size: 7px;
            font-weight: 700;
            color: #be123c;
            background: rgba(225, 29, 72, 0.1);
            border: 0.5px dashed #e11d48;
            padding: 1px 2px;
            border-radius: 3px;
            text-align: center;
            margin-top: 1px;
            line-height: 1;
        }

        /* Progress Modal / Overlay for Batch ZIP Download */
        #batchProgressOverlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.85);
            z-index: 2000;
            display: none;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: #ffffff;
        }
        .progress-box {
            background: #1e293b;
            border: 1px solid #334155;
            padding: 24px;
            border-radius: 12px;
            width: 90%;
            max-width: 420px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
        }
        .progress-bar-bg {
            background: #334155;
            border-radius: 10px;
            height: 12px;
            overflow: hidden;
            margin: 16px 0 10px 0;
        }
        .progress-bar-fill {
            background: linear-gradient(90deg, #10b981, #38bdf8);
            height: 100%;
            width: 0%;
            transition: width 0.15s ease;
        }

        /* ==========================================================================
           PRINT STYLES (A4 PORTRAIT - EXACT 5 STRIPS PER SHEET)
           ========================================================================== */
        @media print {
            @page {
                size: A4 portrait;
                margin: 0.35in 0.135in;
            }
            html, body {
                background: #ffffff !important;
                color: #000000 !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .no-print, .coupon-topbar, .strip-side-actions {
                display: none !important;
            }
            .coupons-screen-stage {
                padding: 0 !important;
                margin: 0 !important;
                gap: 0 !important;
                display: block !important;
            }
            .coupon-strip-row {
                max-width: 100% !important;
                gap: 0 !important;
                margin: 0 0 0.14in 0 !important;
                justify-content: center !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            .coupon-strip {
                border: 1.2px solid #000000 !important;
                box-shadow: none !important;
                margin: 0 auto !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            /* Clean page break after every 5 strips to perfectly fill each A4 page */
            .coupon-page-block {
                page-break-after: always !important;
                break-after: page !important;
                margin-bottom: 0 !important;
            }
            .coupon-page-block:last-child {
                page-break-after: auto !important;
                break-after: auto !important;
            }
        }
    </style>
</head>
<body>

    {{-- Top Action Bar (Hidden on Print) --}}
    <div class="coupon-topbar no-print">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2" style="display: flex; justify-content: space-between; align-items: center;">
            {{-- Left Title & Event info --}}
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 15px; font-weight: 800; color: #facc15;">
                    <i class="fa-solid fa-utensils me-1"></i> খাবার ও র‍্যাফেল ড্র কুপন স্লিপ
                </span>
                <span style="background: #334155; color: #f8fafc; font-size: 11px; padding: 3px 9px; border-radius: 12px; font-family: monospace; font-weight: 700;">
                    মোট {{ $totalCount }} টি স্লিপ
                </span>
                <span style="font-size: 11px; color: #cbd5e1; border-left: 1px solid #475569; padding-left: 10px;">
                    সাইজ: <strong>৮" × ২"</strong> (৫ কলাম)
                </span>
            </div>

            {{-- Middle Controls: Status filter & search --}}
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                {{-- Status Filter Tabs --}}
                <div style="display: flex; gap: 4px; background: rgba(0, 0, 0, 0.3); padding: 3px; border-radius: 24px;">
                    <a href="?{{ http_build_query(array_merge(request()->all(), ['status' => 'approved'])) }}" 
                       class="btn-status-filter {{ in_array(($currentStatus ?? 'approved'), ['approved', 'confirmed']) ? 'active-approved' : '' }}" 
                       title="পেন্ডিং বাদ দিয়ে শুধুমাত্র অনুমোদিত কুপন লোড করুন">
                        <i class="fa-solid fa-circle-check"></i> অনুমোদিত (পেন্ডিং ছাড়া)
                    </a>
                    <a href="?{{ http_build_query(array_merge(request()->all(), ['status' => 'pending'])) }}" 
                       class="btn-status-filter {{ ($currentStatus ?? '') === 'pending' ? 'active-pending' : '' }}" 
                       title="শুধুমাত্র পেন্ডিং আবেদনগুলোর কুপন আলাদা প্রিন্ট করুন">
                        <i class="fa-solid fa-clock-rotate-left"></i> পেন্ডিং আলাদা
                    </a>
                    <a href="?{{ http_build_query(array_merge(request()->all(), ['status' => 'all'])) }}" 
                       class="btn-status-filter {{ ($currentStatus ?? '') === 'all' ? 'active-all' : '' }}" 
                       title="সকল কুপন (অনুমোদিত + পেন্ডিং)">
                        <i class="fa-solid fa-users"></i> সকল
                    </a>
                </div>

                {{-- Live Search Filter --}}
                <input type="text" id="couponSearchBox" class="coupon-search-input" placeholder="কার্ড নং, নাম বা ফোন..." onkeyup="filterCouponsRealtime(this.value)">
            </div>

            {{-- Right Buttons: Batch ZIP, Print, Close --}}
            <div style="display: flex; align-items: center; gap: 8px;">
                <button type="button" onclick="downloadAllCouponsZip()" class="btn-status-filter" style="background: linear-gradient(135deg, #2563eb, #1d4ed8); border: none; color: #ffffff;" title="সকল কুপন স্লিপ একসাথে জিপ ফাইলে ডাউনলোড করুন">
                    <i class="fa-solid fa-file-zipper"></i> সব স্লিপ জিপ ডাউনলোড
                </button>
                <button type="button" onclick="window.print()" class="btn-status-filter" style="background: linear-gradient(135deg, #10b981, #059669); border: none; color: #ffffff; font-weight: 700;" title="এক ক্লিকে সকল কুপন প্রিন্ট করুন (Ctrl + P)">
                    <i class="fa-solid fa-print"></i> এক ক্লিকে সব প্রিন্ট (Ctrl + P)
                </button>
                <a href="{{ route('admin.event-campaigns.show', $campaign->id) }}" class="btn-status-filter" style="background: #334155; border: none; color: #ffffff;">
                    <i class="fa-solid fa-arrow-left"></i> ব্যাক
                </a>
            </div>
        </div>
    </div>

    {{-- Main Printable Coupons Stage --}}
    <div class="coupons-screen-stage">
        @if($totalCount === 0)
            <div style="text-align: center; color: #94a3b8; padding: 60px 20px;">
                <i class="fa-solid fa-ticket" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                <h4 style="color: #f8fafc; font-weight: 700;">কোনো কুপন স্লিপ পাওয়া যায়নি</h4>
                <p>বর্তমান ফিল্টারে কোনো অংশগ্রহণকারী পাওয়া যায়নি। ফিল্টার পরিবর্তন করে পুনরায় চেষ্টা করুন।</p>
                <a href="?status=all" class="btn-status-filter" style="background: #3b82f6; color: #fff; margin-top: 10px;">সকল এন্ট্রি দেখুন</a>
            </div>
        @else
            @foreach($registrationChunks as $pageIndex => $pageRegistrations)
                <div class="coupon-page-block" style="width: 100%; display: flex; flex-direction: column; align-items: center; gap: 14px;">
                    @foreach($pageRegistrations as $reg)
                        @php
                            $rawRegNo = $reg->registration_number ?: ('ID-' . $reg->id);
                            // Clean Card Number (e.g. RSU-2026-001 -> 001 or RSU-001)
                            $cleanCardNo = preg_replace('/^RSU-?(?:2026-?)?/i', '', $rawRegNo);
                            $cardDisplay = !empty($cleanCardNo) ? (is_numeric($cleanCardNo) ? sprintf('%03d', (int)$cleanCardNo) : $cleanCardNo) : $rawRegNo;
                            $cardNoFormatted = 'RSU-' . $cardDisplay;

                            $pName = $reg->name ?: 'অংশগ্রহণকারী';
                            $pCat = $reg->resolved_category ?: ($reg->designation_or_class ?: 'কবি ও প্রতিনিধি');
                            $pPhone = $reg->phone ?: '';
                            $pMaskedPhone = !empty($pPhone) ? (substr($pPhone, 0, 5) . '***' . substr($pPhone, -3)) : '';
                            $stripDomId = 'coupon_strip_' . $reg->id;
                            $pngFileName = 'Coupon_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $cardNoFormatted) . '.png';
                        @endphp

                        <div class="coupon-strip-row" data-card="{{ strtolower($cardNoFormatted) }}" data-name="{{ strtolower($pName) }}" data-phone="{{ $pPhone }}">
                            {{-- THE 8in x 2in 5-COLUMN COUPON STRIP --}}
                            <div class="coupon-strip" id="{{ $stripDomId }}">
                                
                                {{-- =========================================================================
                                     COLUMN 1: সকালের নাস্তা (BREAKFAST)
                                     ========================================================================= --}}
                                <div class="coupon-col coupon-col-breakfast">
                                    <span class="cut-guide-icon"><i class="fa-solid fa-scissors"></i></span>
                                    <div>
                                        <div class="coupon-badge">
                                            <i class="fa-solid fa-mug-hot"></i> সকালের নাস্তা
                                        </div>
                                        <div class="coupon-event-name">
                                            {{ $eventTitle }}
                                        </div>
                                        <div class="coupon-card-badge">
                                            কার্ড নং: {{ $cardNoFormatted }}
                                        </div>
                                        <div class="coupon-participant-info">
                                            <span class="coupon-participant-name" title="{{ $pName }}">{{ $pName }}</span>
                                            <span class="coupon-participant-category">{{ $pCat }}</span>
                                        </div>
                                    </div>
                                    <div class="coupon-footer">
                                        <span class="coupon-serial">#BKF-{{ $cardDisplay }}</span>
                                        <span class="coupon-barcode-graphic">|||| | ||</span>
                                        <span>নাস্তা কুপন</span>
                                    </div>
                                </div>

                                {{-- =========================================================================
                                     COLUMN 2: সকালের স্নাকস (MORNING SNACKS)
                                     ========================================================================= --}}
                                <div class="coupon-col coupon-col-snacks1">
                                    <span class="cut-guide-icon"><i class="fa-solid fa-scissors"></i></span>
                                    <div>
                                        <div class="coupon-badge">
                                            <i class="fa-solid fa-cookie-bite"></i> স্নাকস (সকাল)
                                        </div>
                                        <div class="coupon-event-name">
                                            {{ $eventTitle }}
                                        </div>
                                        <div class="coupon-card-badge">
                                            কার্ড নং: {{ $cardNoFormatted }}
                                        </div>
                                        <div class="coupon-participant-info">
                                            <span class="coupon-participant-name" title="{{ $pName }}">{{ $pName }}</span>
                                            <span class="coupon-participant-category">{{ $pCat }}</span>
                                        </div>
                                    </div>
                                    <div class="coupon-footer">
                                        <span class="coupon-serial">#SN1-{{ $cardDisplay }}</span>
                                        <span class="coupon-barcode-graphic">|| ||| |</span>
                                        <span>স্নাকস কুপন</span>
                                    </div>
                                </div>

                                {{-- =========================================================================
                                     COLUMN 3: দুপুরের খাবার (LUNCH)
                                     ========================================================================= --}}
                                <div class="coupon-col coupon-col-lunch">
                                    <span class="cut-guide-icon"><i class="fa-solid fa-scissors"></i></span>
                                    <div>
                                        <div class="coupon-badge">
                                            <i class="fa-solid fa-bowl-rice"></i> দুপুরের খাবার
                                        </div>
                                        <div class="coupon-event-name">
                                            {{ $eventTitle }}
                                        </div>
                                        <div class="coupon-card-badge">
                                            কার্ড নং: {{ $cardNoFormatted }}
                                        </div>
                                        <div class="coupon-participant-info">
                                            <span class="coupon-participant-name" title="{{ $pName }}">{{ $pName }}</span>
                                            <span class="coupon-participant-category">{{ $pCat }}</span>
                                        </div>
                                    </div>
                                    <div class="coupon-footer">
                                        <span class="coupon-serial">#LUN-{{ $cardDisplay }}</span>
                                        <span class="coupon-barcode-graphic">|||| ||||</span>
                                        <span>খাবার কুপন</span>
                                    </div>
                                </div>

                                {{-- =========================================================================
                                     COLUMN 4: বিকেলের স্নাকস (AFTERNOON SNACKS)
                                     ========================================================================= --}}
                                <div class="coupon-col coupon-col-snacks2">
                                    <span class="cut-guide-icon"><i class="fa-solid fa-scissors"></i></span>
                                    <div>
                                        <div class="coupon-badge">
                                            <i class="fa-solid fa-mug-saucer"></i> বিকেলের স্নাকস
                                        </div>
                                        <div class="coupon-event-name">
                                            {{ $eventTitle }}
                                        </div>
                                        <div class="coupon-card-badge">
                                            কার্ড নং: {{ $cardNoFormatted }}
                                        </div>
                                        <div class="coupon-participant-info">
                                            <span class="coupon-participant-name" title="{{ $pName }}">{{ $pName }}</span>
                                            <span class="coupon-participant-category">{{ $pCat }}</span>
                                        </div>
                                    </div>
                                    <div class="coupon-footer">
                                        <span class="coupon-serial">#SN2-{{ $cardDisplay }}</span>
                                        <span class="coupon-barcode-graphic">|| |||| |</span>
                                        <span>স্নাকস কুপন</span>
                                    </div>
                                </div>

                                {{-- =========================================================================
                                     COLUMN 5: র‍্যাফেল ড্র কুপন (RAFFLE DRAW)
                                     ========================================================================= --}}
                                <div class="coupon-col coupon-col-raffle">
                                    <div>
                                        <div class="coupon-badge">
                                            <i class="fa-solid fa-ticket"></i> র‍্যাফেল ড্র
                                        </div>
                                        <div class="coupon-event-name">
                                            {{ $eventTitle }}
                                        </div>
                                        <div class="coupon-card-badge">
                                            ড্র নং: {{ $cardNoFormatted }}
                                        </div>
                                        <div class="coupon-participant-info">
                                            <span class="coupon-participant-name" title="{{ $pName }}">{{ $pName }}</span>
                                            @if(!empty($pMaskedPhone))
                                                <span class="coupon-participant-category" style="color: #be123c; font-family: monospace;">{{ $pMaskedPhone }}</span>
                                            @else
                                                <span class="coupon-participant-category">{{ $pCat }}</span>
                                            @endif
                                        </div>
                                        <div class="raffle-box-drop">
                                            (ড্র বক্সে ফেলুন)
                                        </div>
                                    </div>
                                    <div class="coupon-footer">
                                        <span class="coupon-serial">#DRW-{{ $cardDisplay }}</span>
                                        <span class="coupon-barcode-graphic">|||| ||</span>
                                        <span>ড্র কুপন</span>
                                    </div>
                                </div>

                            </div>

                            {{-- Side Action Buttons on Screen (Hidden on Print) --}}
                            <div class="strip-side-actions no-print">
                                <button type="button" class="btn-strip-download" onclick="downloadSingleStripPng('{{ $stripDomId }}', '{{ $pngFileName }}')" title="এই ৮x২ ইঞ্চি স্লিপটি PNG ছবি হিসেবে ডাউনলোড করুন">
                                    <i class="fa-solid fa-download"></i> স্লিপ ডাউনলোড
                                </button>
                                <a href="{{ route('admin.event-campaigns.registrations.coupon', $reg->id) }}" target="_blank" class="btn-strip-download" style="color: #facc15;" title="সিঙ্গেল ভিউ পেজে খুলুন">
                                    <i class="fa-solid fa-expand"></i> সিঙ্গেল ভিউ
                                </a>
                                <span style="font-size: 10px; color: #94a3b8; font-family: monospace; text-align: center;">
                                    {{ $cardNoFormatted }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        @endif
    </div>

    {{-- Batch Progress Overlay (Hidden by default) --}}
    <div id="batchProgressOverlay">
        <div class="progress-box">
            <i class="fa-solid fa-circle-notch fa-spin" style="font-size: 36px; color: #38bdf8; margin-bottom: 12px;"></i>
            <h5 style="margin: 0 0 6px 0; font-weight: 700;">সকল কুপন জিপ তৈরি হচ্ছে...</h5>
            <p id="progressStatusText" style="font-size: 13px; color: #cbd5e1; margin: 0;">অপেক্ষা করুন...</p>
            <div class="progress-bar-bg">
                <div class="progress-bar-fill" id="progressBarFill"></div>
            </div>
            <span id="progressCountText" style="font-size: 11px; color: #94a3b8; font-family: monospace;">0 / {{ $totalCount }}</span>
        </div>
    </div>

    <script>
        // Real-time Instant Search
        function filterCouponsRealtime(query) {
            const q = query.trim().toLowerCase();
            const rows = document.querySelectorAll('.coupon-strip-row');
            rows.forEach(row => {
                const card = row.getAttribute('data-card') || '';
                const name = row.getAttribute('data-name') || '';
                const phone = row.getAttribute('data-phone') || '';
                if (!q || card.includes(q) || name.includes(q) || phone.includes(q)) {
                    row.style.display = 'flex';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Single 8x2 inch Coupon Strip PNG Download
        async function downloadSingleStripPng(elementId, filename) {
            const el = document.getElementById(elementId);
            if (!el) return;

            try {
                const canvas = await html2canvas(el, {
                    scale: 3, // 300 DPI equivalent for sharp print quality
                    useCORS: true,
                    allowTaint: false,
                    backgroundColor: '#ffffff',
                    logging: false
                });

                canvas.toBlob(blob => {
                    if (blob && window.saveAs) {
                        saveAs(blob, filename);
                    } else if (blob) {
                        const url = URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = filename;
                        a.click();
                        setTimeout(() => URL.revokeObjectURL(url), 1000);
                    }
                }, 'image/png');
            } catch (err) {
                console.error(err);
                alert('স্লিপ ডাউনলোড করতে সমস্যা হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।');
            }
        }

        // 1-Click Batch Download All Visible Coupon Strips into a ZIP Archive
        async function downloadAllCouponsZip() {
            const rows = Array.from(document.querySelectorAll('.coupon-strip-row')).filter(r => r.style.display !== 'none');
            if (rows.length === 0) {
                alert('কোনো কুপন স্লিপ পাওয়া যায়নি!');
                return;
            }

            const overlay = document.getElementById('batchProgressOverlay');
            const progressFill = document.getElementById('progressBarFill');
            const statusText = document.getElementById('progressStatusText');
            const countText = document.getElementById('progressCountText');

            overlay.style.display = 'flex';
            const zip = new JSZip();
            const folder = zip.folder("RSU_Coupons_8x2");

            let processed = 0;
            const total = rows.length;

            for (const row of rows) {
                const strip = row.querySelector('.coupon-strip');
                const cardNo = row.getAttribute('data-card') || `item_${processed + 1}`;
                const safeName = `Coupon_${cardNo.toUpperCase().replace(/[^A-Z0-9_\-]/g, '_')}.png`;

                if (strip) {
                    try {
                        const canvas = await html2canvas(strip, {
                            scale: 2.5,
                            useCORS: true,
                            backgroundColor: '#ffffff',
                            logging: false
                        });

                        const dataUrl = canvas.toDataURL('image/png');
                        const base64Data = dataUrl.replace(/^data:image\/png;base64,/, "");
                        folder.file(safeName, base64Data, { base64: true });
                    } catch (e) {
                        console.error('Failed to capture:', safeName, e);
                    }
                }

                processed++;
                const pct = Math.round((processed / total) * 100);
                progressFill.style.width = pct + '%';
                statusText.innerText = `কুপন ইমেজ তৈরি হচ্ছে (${processed} / ${total})...`;
                countText.innerText = `${processed} / ${total} (${pct}%)`;

                // Give the UI thread a breather
                await new Promise(r => setTimeout(r, 20));
            }

            statusText.innerText = 'জিপ আর্কাইভ কম্প্রেস করা হচ্ছে, অপেক্ষা করুন...';
            zip.generateAsync({ type: "blob" }, (metadata) => {
                progressFill.style.width = metadata.percent.toFixed(0) + '%';
            }).then(content => {
                overlay.style.display = 'none';
                saveAs(content, "RSU_Food_and_Raffle_Coupons_8x2.zip");
            }).catch(err => {
                console.error(err);
                overlay.style.display = 'none';
                alert('জিপ ফাইল তৈরিতে সমস্যা হয়েছে।');
            });
        }
    </script>
</body>
</html>
