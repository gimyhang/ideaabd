@php
    $formData = $registration->form_data ?? [];
    $campaign = $registration->campaign;
    $isPdf = $isPdf ?? false;
    $libName = $registration->institution_or_org ?: ($formData['library_name'] ?? 'পাঠাগার');
    $libType = $formData['library_type'] ?? ($registration->designation_or_class ?: 'গণপাঠাগার');
    $allocated = intval($formData['books_allocated'] ?? 0);
    $dispatchedDate = $formData['dispatched_date'] ?? null;
    $receivedCount = intval($formData['received_books_count'] ?? 0);
    $receivedDate = $formData['received_date'] ?? null;
    $bookItems = $formData['allocated_books_list'] ?? [];
    $photoUrl = null;
    if (!empty($formData['student_photo'])) {
        $photoPath = $formData['student_photo'];
        $fullDiskPath = storage_path('app/public/' . $photoPath);
        if (file_exists($fullDiskPath)) {
            if ($isPdf) {
                $ext = pathinfo($fullDiskPath, PATHINFO_EXTENSION) ?: 'jpg';
                $photoData = @file_get_contents($fullDiskPath);
                $photoUrl = $photoData ? 'data:image/' . $ext . ';base64,' . base64_encode($photoData) : null;
            } else {
                $photoUrl = asset('storage/' . $photoPath);
            }
        } elseif (str_starts_with($photoPath, 'http')) {
            $photoUrl = $photoPath;
        } elseif (str_starts_with($photoPath, 'data:image')) {
            $photoUrl = $photoPath;
        } elseif (file_exists(public_path($photoPath))) {
            $photoUrl = asset($photoPath);
        }
    }

    $fSettings = $campaign->form_settings ?? [];
    $brandName = $fSettings['brand_name'] ?? 'আইডিয়া পাঠাগার';
    $subTitle = $fSettings['sub_title'] ?? 'বই অনুদান আবেদন ফরম';
    $sessionText = $fSettings['session_text'] ?? 'আইডিয়া প্রকাশন ও বুকস অব আইডিয়া';
    $brandTag = $fSettings['brand_tag'] ?? "প্রধান কার্যালয়: ঢাকা, বাংলাদেশ\nwww.ideaabd.com";
    $bannerTitle = $fSettings['banner_title'] ?? 'বিনামূল্যে বই বিতরণ কর্মসূচি ও পাঠাগার নিবন্ধন আবেদন ফরম';
    $grantSession = $fSettings['grant_session'] ?? '২০২৬ অনুদান কর্মসূচি';
    $officerName = $fSettings['officer_name'] ?? 'সাকিল মাসুদ';
    $officerDesignation = $fSettings['officer_designation'] ?? 'তত্বাবধায়ক ও প্রতিষ্ঠাতা';
    $officerOrg = $fSettings['officer_org'] ?? '';
    $declarationText = $fSettings['declaration_text'] ?? 'আইডিয়া পাঠাগার নিজ উদ্যোগে বই বিতরণ করে। বই প্রদানের ক্ষেত্রে যে কোনো সিদ্ধান্ত গ্রহণের ক্ষমতা সংরক্ষণ করে।';
    $themeColor = $fSettings['theme_color'] ?? '#047857';
    $logoSize = intval($fSettings['logo_size'] ?? 24);
    $customCss = $fSettings['custom_css'] ?? '';

    $logoUrl = null;
    if (!empty($fSettings['logo_url'])) {
        $customLogo = $fSettings['logo_url'];
        if (str_starts_with($customLogo, 'http') || str_starts_with($customLogo, 'data:image')) {
            $logoUrl = $customLogo;
        } elseif (file_exists(public_path($customLogo))) {
            $logoUrl = $isPdf ? public_path($customLogo) : asset($customLogo);
        } else {
            $logoUrl = $customLogo;
        }
    } else {
        $logoDiskPath = public_path('images/logo.png');
        if (file_exists($logoDiskPath)) {
            if ($isPdf) {
                $logoData = @file_get_contents($logoDiskPath);
                $logoUrl = $logoData ? 'data:image/png;base64,' . base64_encode($logoData) : asset('images/logo.png');
            } else {
                $logoUrl = asset('images/logo.png');
            }
        } elseif (file_exists(public_path('images/logo.svg'))) {
            $logoUrl = asset('images/logo.svg');
        } else {
            $logoUrl = \App\Support\SiteSetting::logoUrl();
        }
    }
@endphp
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>পাঠাগার বই অনুদান ফরম — {{ $registration->registration_number }} — {{ $libName }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700;800&family=Noto+Serif+Bengali:wght@600;700;800;900&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 12mm 10mm 12mm;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        body {
            font-family: 'Hind Siliguri', Arial, sans-serif;
            font-size: 11.5px;
            line-height: 1.3;
            color: #000;
            background-color: #f4f6f9;
            margin: 0;
            padding: 20px 0;
        }
        .no-print {
            display: block;
        }
        @media print {
            body {
                background-color: #fff !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .page-container {
                box-shadow: none !important;
                border: none !important;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
        }
        .page-container {
            width: 780px;
            max-width: 100%;
            margin: 0 auto;
            background: #fff;
            padding: 20px 24px;
            border: 1px solid #d1d5db;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
            position: relative;
        }
        
        /* Action buttons bar */
        .action-bar {
            width: 780px;
            max-width: 100%;
            margin: 0 auto 14px auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.2s;
            font-family: 'Hind Siliguri', sans-serif;
        }
        .btn-print {
            background-color: #047857;
            color: #fff;
        }
        .btn-print:hover {
            background-color: #064e3b;
        }
        .btn-pdf {
            background-color: #dc2626;
            color: #fff;
        }
        .btn-pdf:hover {
            background-color: #b91c1c;
        }
        .btn-back {
            background-color: #f1f5f9;
            color: #334155;
            border-color: #cbd5e1;
        }
        .btn-back:hover {
            background-color: #e2e8f0;
        }

        /* RSU Style Authentic Full-Width Letterhead Pad */
        .lh-pad-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            padding-bottom: 6px;
            border-bottom: 1.5px solid #0f172a;
        }
        .lh-pad-header-table td {
            vertical-align: middle;
            padding: 2px 2px;
        }
        .lh-emblem-td {
            width: 135px;
            text-align: center;
            vertical-align: middle;
        }
        .lh-emblem-circle {
            width: 120px;
            height: 60px;
            aspect-ratio: 2 / 1;
            border-radius: 8px;
            border: 2px solid #047857;
            background: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
            margin: 0 auto;
            padding: 4px 6px;
        }
        .lh-emblem-img {
            max-width: 100%;
            max-height: 100%;
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }
        .lh-emblem-icon {
            font-size: 28px;
            line-height: 1;
        }

        .lh-center-td {
            text-align: center;
            padding: 0 10px;
        }
        .lh-title-text {
            font-size: 21px;
            font-weight: 900;
            color: #0f172a;
            line-height: 1.25;
            margin-bottom: 2px;
            font-family: 'Noto Serif Bengali', serif;
            letter-spacing: -0.3px;
        }
        .lh-subhead-text {
            font-size: 13.5px;
            font-weight: 800;
            color: #047857;
            margin-bottom: 2px;
            letter-spacing: 0.2px;
        }
        .lh-venue-text {
            font-size: 11.5px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 2px;
        }
        .lh-session-text {
            font-size: 11.5px;
            font-weight: 800;
            color: #047857;
            margin-bottom: 2px;
        }
        .lh-org-text {
            font-size: 10px;
            font-weight: 600;
            color: #64748b;
        }

        .lh-badge-td {
            width: 125px;
            text-align: right;
            vertical-align: top;
            padding-top: 2px;
        }
        .lh-copy-tag {
            display: inline-block;
            background: #0f172a;
            color: #ffffff;
            font-size: 9.5px;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 3px;
            letter-spacing: 0.5px;
            font-family: monospace;
            text-transform: uppercase;
        }
        .lh-token-text {
            font-family: monospace;
            font-size: 11.5px;
            font-weight: 800;
            color: #0f172a;
            margin-top: 3px;
            margin-bottom: 3px;
        }
        .lh-photo-thumb-wrap {
            width: 74px;
            height: 60px;
            border: 1px solid #000;
            border-radius: 4px;
            overflow: hidden;
            display: inline-block;
            background: #fafafa;
        }
        .lh-photo-thumb-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .lh-photo-thumb-placeholder {
            width: 74px;
            height: 60px;
            border: 1px dashed #94a3b8;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            color: #94a3b8;
            background: #f8fafc;
            text-align: center;
            padding: 2px;
        }

        /* Official Record Alert Banner (RSU Alert Strip Style) */
        .official-record-banner-table {
            width: 100%;
            border-collapse: collapse;
            background: #f0fdf4;
            border: 1.5px solid #86efac;
            border-radius: 4px;
            margin: 6px 0 8px 0;
        }
        .official-record-banner-table td {
            padding: 5px 8px;
            font-size: 10.5px;
            vertical-align: middle;
        }
        .rec-label {
            color: #166534;
            font-weight: 700;
            margin-right: 3px;
        }
        .rec-val {
            color: #0f172a;
            font-weight: 700;
        }
        .status-pill {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        .status-approved {
            background: #16a34a;
            color: #ffffff;
        }
        .status-pending {
            background: #d97706;
            color: #ffffff;
        }

        /* Banner Title */
        .form-banner-strip {
            background: #047857;
            color: #fff;
            padding: 5px 10px;
            font-size: 13px;
            font-weight: bold;
            text-align: center;
            letter-spacing: 0.3px;
            margin: 6px 0;
        }

        /* Standard Grid Tables */
        .form-grid-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .form-grid-table td, .form-grid-table th {
            border: 1px solid #000;
            padding: 4px 7px;
            font-size: 11px;
            vertical-align: middle;
        }
        .th-label {
            font-weight: bold;
            color: #000;
            background-color: #f8fafc;
            width: 24%;
        }
        .td-val {
            font-weight: normal;
            color: #000;
        }
        .sec-header-row {
            background: #e2e8f0;
            font-weight: bold;
            font-size: 11px;
            padding: 3px 6px;
            border: 1px solid #000;
        }

        /* Category Chips in Print */
        .print-chip {
            display: inline-block;
            border: 1px solid #64748b;
            padding: 1.5px 6px;
            border-radius: 3px;
            font-size: 10px;
            margin: 1.5px;
            background: #f8fafc;
        }
        .print-chip.checked {
            background: #dcfce7;
            border-color: #047857;
            font-weight: bold;
            color: #047857;
        }

        /* Statement Box */
        .statement-print-box {
            border: 1px solid #000;
            padding: 6px 8px;
            font-size: 11px;
            line-height: 1.45;
            min-height: 48px;
            background: #fff;
            margin-bottom: 6px;
        }

        /* Signatures Grid */
        .sign-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .sign-table td {
            width: 50%;
            vertical-align: bottom;
            text-align: center;
            padding: 0 20px;
        }
        .sign-line {
            border-top: 1px dashed #000;
            padding-top: 4px;
            font-size: 10.5px;
            font-weight: bold;
        }
        .sign-sub {
            font-size: 9.5px;
            color: #475569;
        }
        @if(!empty($customCss))
            {!! $customCss !!}
        @endif
    </style>
</head>
<body>

@if(!$isPdf)
<div class="action-bar no-print">
    <div style="display: flex; gap: 8px;">
        <a href="javascript:window.history.back()" class="btn-action btn-back">
            ← ফিরে যান
        </a>
        <a href="{{ route('admin.libraries.index') }}" class="btn-action btn-back">
            ড্যাশবোর্ড
        </a>
    </div>
    <div style="display: flex; gap: 8px;">
        <button onclick="window.print()" class="btn-action btn-print">
            🖨️ আবেদন ফরম প্রিন্ট করুন
        </button>
        <a href="{{ route('event.registration.pdf', $registration->registration_number) }}" class="btn-action btn-pdf">
            📥 পিডিএফ ডাউনলোড
        </a>
    </div>
</div>
@endif

<div class="page-container">

    {{-- =========================================================================
         ১. শীর্ষ লেটার হেড প্যাড (RSU Authentic Full-Width Letterhead Pad Structure)
         ========================================================================= --}}
    <table class="lh-pad-header-table">
        <tr>
            {{-- বাম পাশের গোল লোগো এমব্লেম --}}
            <td class="lh-emblem-td">
                <div class="lh-emblem-circle" style="border-color: {{ $themeColor }};">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $brandName }}" class="lh-emblem-img">
                    @else
                        <div class="lh-emblem-icon" style="color: {{ $themeColor }};">
                            🏛️
                        </div>
                    @endif
                </div>
            </td>

            {{-- মাঝের মূল লেটারহেড বিবরণী --}}
            <td class="lh-center-td">
                <div class="lh-title-text">{{ $brandName }}</div>
                <div class="lh-subhead-text" style="color: {{ $themeColor }};">{{ $subTitle }}</div>
                <div class="lh-venue-text">{{ $sessionText }}</div>
                <div class="lh-org-text">{!! nl2br(e($brandTag)) !!}</div>
            </td>

            {{-- ডান পাশের কপি ট্যাগ ও আইডি + ফটো --}}
            <td class="lh-badge-td">
                <div class="lh-copy-tag">OFFICIAL COPY</div>
                <div class="lh-token-text" style="color: {{ $themeColor }};">
                    #{{ $registration->registration_number }}
                </div>
                @if($photoUrl)
                    <div class="lh-photo-thumb-wrap" title="পাঠাগারের সাইনবোর্ড / ছবি">
                        <img src="{{ $photoUrl }}" alt="পাঠাগারের ছবি" class="lh-photo-thumb-img">
                    </div>
                @else
                    <div class="lh-photo-thumb-placeholder">
                        <span>🏛️ পাঠাগার ছবি</span>
                    </div>
                @endif
            </td>
        </tr>
    </table>

    {{-- ব্যানার শিরোনাম (RSU Banner Strip) --}}
    <div class="form-banner-strip" style="background: {{ $themeColor }};">
        {{ $bannerTitle }}
    </div>

    {{-- অফিসিয়াল রেকর্ড ও স্ট্যাটাস ইনফো বার (RSU Alert Banner Structure) --}}
    <table class="official-record-banner-table">
        <tr>
            <td style="width: 38%;">
                <span class="rec-label">📅 আবেদনের তারিখ:</span>
                <span class="rec-val">{{ $registration->created_at ? $registration->created_at->format('d M, Y - h:i A') : date('d M, Y') }}</span>
            </td>
            <td style="width: 32%; text-align: center;">
                <span class="rec-label">স্ট্যাটাস:</span>
                @if(in_array($registration->status, ['confirmed', 'approved', 'selected']))
                    <span class="status-pill status-approved">✔ অনুমোদিত (Approved)</span>
                @else
                    <span class="status-pill status-pending">⏳ যাচাই প্রক্রিয়াধীন (Pending)</span>
                @endif
            </td>
            <td style="width: 30%; text-align: right;">
                <span class="rec-label">📦 গ্রহণ মাধ্যম:</span>
                <span class="rec-val" style="font-size: 10px;">{{ $formData['delivery_method'] ?? 'অফিস থেকে সরাসরি গ্রহণ' }}</span>
            </td>
        </tr>
    </table>

    {{-- ব্যানার শিরোনাম --}}
    <div class="form-banner-strip" style="background: {{ $themeColor }};">
        {{ $bannerTitle }}
    </div>

    {{-- ১. পাঠাগার ও প্রতিষ্ঠানের বিবরণ --}}
    <div class="sec-header-row">১. পাঠাগার ও প্রতিষ্ঠানের বিবরণ</div>
    <table class="form-grid-table">
        <tr>
            <td class="th-label">পাঠাগারের নাম:</td>
            <td class="td-val" colspan="3" style="font-weight: bold; font-size: 12px; color: {{ $themeColor }};">
                {{ $libName }}
            </td>
        </tr>
        <tr>
            <td class="th-label">পাঠাগারের ধরন:</td>
            <td class="td-val" style="width: 30%;">{{ $libType }}</td>
            <td class="th-label" style="width: 22%;">সরকারি / গ্রন্থকেন্দ্র রেজি নং:</td>
            <td class="td-val" style="width: 24%;">{{ $formData['reg_no'] ?? 'প্রযোজ্য নয়' }}</td>
        </tr>
        <tr>
            <td class="th-label">প্রতিষ্ঠা সাল:</td>
            <td class="td-val">{{ $formData['established_year'] ?? 'প্রযোজ্য নয়' }}</td>
            <td class="th-label">নিয়মিত পাঠক সংখ্যা:</td>
            <td class="td-val">{{ $formData['reader_count'] ?? '০' }} জন</td>
        </tr>
        <tr>
            <td class="th-label">বর্তমানে মোট বই সংখ্যা:</td>
            <td class="td-val">{{ $formData['current_book_count'] ?? '০' }} টি</td>
            <td class="th-label">কর্মসূচি সেশন:</td>
            <td class="td-val">{{ $grantSession }}</td>
        </tr>
    </table>

    {{-- ২. দায়িত্বপ্রাপ্ত প্রতিনিধি ও পরিচালনা কমিটির তথ্য --}}
    <div class="sec-header-row">২. দায়িত্বপ্রাপ্ত প্রতিনিধি ও পরিচালনা কমিটির তথ্য</div>
    <table class="form-grid-table">
        <tr>
            <td class="th-label">আবেদনকারী প্রতিনিধির নাম:</td>
            <td class="td-val" style="font-weight: bold; width: 30%;">{{ $registration->name }}</td>
            <td class="th-label" style="width: 22%;">প্রতিষ্ঠানে পদবি:</td>
            <td class="td-val" style="width: 24%;">{{ $registration->designation_or_class ?: ($formData['designation_or_class'] ?? 'সাধারণ সম্পাদক') }}</td>
        </tr>
        <tr>
            <td class="th-label">প্রধান মোবাইল নম্বর:</td>
            <td class="td-val" style="font-family: monospace; font-weight: bold;">{{ $registration->phone }}</td>
            <td class="th-label">বিকল্প মোবাইল নম্বর:</td>
            <td class="td-val" style="font-family: monospace;">{{ $formData['guardian_phone'] ?? 'প্রযোজ্য নয়' }}</td>
        </tr>
        <tr>
            <td class="th-label">ইমেইল ঠিকানা:</td>
            <td class="td-val">{{ $registration->email ?: 'প্রযোজ্য নয়' }}</td>
            <td class="th-label">জাতীয় পরিচয়পত্র (NID) নম্বর:</td>
            <td class="td-val" style="font-family: monospace;">{{ $formData['nid'] ?? 'প্রযোজ্য নয়' }}</td>
        </tr>
        <tr>
            <td class="th-label">সভাপতির নাম:</td>
            <td class="td-val" style="font-weight: bold;">{{ $formData['president_name'] ?? 'প্রযোজ্য নয়' }}</td>
            <td class="th-label">সভাপতির মোবাইল নম্বর:</td>
            <td class="td-val" style="font-family: monospace; font-weight: bold;">{{ $formData['president_phone'] ?? 'প্রযোজ্য নয়' }}</td>
        </tr>
        <tr>
            <td class="th-label">সাধারণ সম্পাদকের নাম:</td>
            <td class="td-val" style="font-weight: bold;">{{ $formData['secretary_name'] ?? 'প্রযোজ্য নয়' }}</td>
            <td class="th-label">সাধারণ সম্পাদকের মোবাইল:</td>
            <td class="td-val" style="font-family: monospace; font-weight: bold;">{{ $formData['secretary_phone'] ?? 'প্রযোজ্য নয়' }}</td>
        </tr>
    </table>

    {{-- ৩. পাঠাগারের অবস্থান ও পূর্ণ ডাক ঠিকানা --}}
    <div class="sec-header-row">৩. পাঠাগারের অবস্থান ও পূর্ণ ডাক ঠিকানা</div>
    <table class="form-grid-table">
        <tr>
            <td class="th-label">বিভাগ:</td>
            <td class="td-val" style="width: 30%;">{{ $formData['division'] ?? '—' }}</td>
            <td class="th-label" style="width: 22%;">জেলা:</td>
            <td class="td-val" style="width: 24%;">{{ $registration->district ?? '—' }}</td>
        </tr>
        <tr>
            <td class="th-label">উপজেলা / থানা:</td>
            <td class="td-val">{{ $registration->thana ?? '—' }}</td>
            <td class="th-label">ডাকঘর ও কোড:</td>
            <td class="td-val">{{ $formData['post_office'] ?? '—' }}</td>
        </tr>
        <tr>
            <td class="th-label">বিস্তারিত ঠিকানা:</td>
            <td class="td-val" colspan="3">{{ $registration->address ?: '—' }}</td>
        </tr>
    </table>

    {{-- ৪. প্রত্যাশিত বইয়ের বিষয়সমূহ --}}
    <div class="sec-header-row">৪. প্রত্যাশিত বইয়ের বিষয়সমূহ</div>
    <table class="form-grid-table">
        <tr>
            <td class="th-label">পছন্দনীয় বিষয়সমূহ:</td>
            <td class="td-val" colspan="3">
                @php
                    $allGenres = [
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
                    $selectedGenres = (array) ($formData['preferred_genres'] ?? []);
                @endphp
                <div>
                    @foreach($allGenres as $gKey => $gBn)
                        @php $isSelected = in_array($gKey, $selectedGenres) || in_array($gBn, $selectedGenres); @endphp
                        <span class="print-chip {{ $isSelected ? 'checked' : '' }}">
                            [{{ $isSelected ? '✔' : ' ' }}] {{ $gBn }}
                        </span>
                    @endforeach
                </div>
            </td>
        </tr>
    </table>

    {{-- ৫. অনুদানের প্রয়োজনীয়তা ও উদ্দেশ্য --}}
    <div class="sec-header-row">৫. অনুদানের প্রয়োজনীয়তা ও কার্যক্রমের সংক্ষিপ্ত উদ্দেশ্য</div>
    <div class="statement-print-box">
        {{ $formData['scholarship_reason'] ?? 'কোনো বিবরণ প্রদান করা হয়নি।' }}
    </div>

    {{-- ৬. অফিসিয়াল যাচাই ও বই বরাদ্দ অংশ --}}
    <div class="sec-header-row">৬. অফিসিয়াল যাচাই ও বই বরাদ্দ অংশ</div>
    <table class="form-grid-table">
        <tr>
            <td class="th-label" style="width: 24%;">মোট বরাদ্দকৃত বই:</td>
            <td class="td-val" style="width: 26%; font-weight: bold; color: #047857;">
                {{ $allocated > 0 ? $allocated . ' টি বই' : 'যাচাই প্রক্রিয়াধীন' }}
            </td>
            <td class="th-label" style="width: 22%;">ডেলিভারি প্রেরণের তারিখ:</td>
            <td class="td-val" style="width: 28%;">
                {{ $dispatchedDate ? date('d M, Y', strtotime($dispatchedDate)) : 'প্রেরণ করা হয়নি' }}
            </td>
        </tr>
        <tr>
            <td class="th-label">কুরিয়ার ট্র্যাকিং নম্বর:</td>
            <td class="td-val">{{ $formData['dispatch_tracking_no'] ?? 'প্রযোজ্য নয়' }}</td>
            <td class="th-label">প্রাপ্তিস্বীকার স্ট্যাটাস:</td>
            <td class="td-val">
                @if($receivedCount > 0 || $registration->isAcknowledged())
                    <span style="color: #047857; font-weight: bold;">✔ গ্রহণ সম্পন্ন ({{ $receivedCount ?: $allocated }} টি বই, তারিখ: {{ $receivedDate ? date('d M, Y', strtotime($receivedDate)) : '—' }})</span>
                @else
                    <span style="color: #64748b;">প্রাপ্তিস্বীকার অপেক্ষমাণ</span>
                @endif
            </td>
        </tr>
        @if(count($bookItems) > 0)
        <tr>
            <td class="th-label">বরাদ্দকৃত বইয়ের তালিকা:</td>
            <td class="td-val" colspan="3">
                <ol style="margin: 0; padding-left: 18px; font-size: 10px;">
                    @foreach($bookItems as $item)
                        <li><strong>{{ $item['title'] ?? 'বইয়ের নাম' }}</strong> — সংখ্যা: {{ $item['qty'] ?? 1 }} কপি {{ !empty($item['author']) ? '('.$item['author'].')' : '' }}</li>
                    @endforeach
                </ol>
            </td>
        </tr>
        @endif
    </table>

    {{-- নীতিমালা ও ঘোষণা --}}
    <div style="font-size: 10px; color: #334155; margin-top: 10px; padding: 5px 8px; background: #f8fafc; border: 1px solid #cbd5e1; text-align: center; border-radius: 4px;">
        <strong>ঘোষণা:</strong> {{ $declarationText }}
    </div>

    {{-- স্বাক্ষর অংশ --}}
    <table class="sign-table">
        <tr>
            <td>
                <div class="sign-line">
                    আবেদনকারী প্রতিনিধির স্বাক্ষর ও তারিখ<br>
                    <span class="sign-sub">পাঠাগার পরিচালনা কমিটির সীল (যদি থাকে)</span>
                </div>
            </td>
            <td>
                <div class="sign-line">
                    {{ $officerName }}<br>
                    <span class="sign-sub">{{ $officerDesignation }}{{ $officerOrg ? ' • ' . $officerOrg : '' }}</span>
                </div>
            </td>
        </tr>
    </table>

</div>

</body>
</html>
