@extends('layouts.app')

@php
    $cardDesign        = $campaign->card_design ?? [];
    $themeColor        = $campaign->theme_color ?: '#0f172a';
    $customFields      = $campaign->custom_fields ?? [];
    $eventDesc         = $campaign->short_description ?: strip_tags($campaign->description ?: 'লেখকদের অংশগ্রহণ ও ডেলিগেট কার্ড নিবন্ধন ফরম — আইডিয়া প্রকাশন।');
    $eventCover        = $campaign->banner_image ? asset('storage/' . $campaign->banner_image) : asset('images/og-banner.jpg');
@endphp

@section('title', $campaign->title . ' — লেখকদের অংশগ্রহণ ফরম')
@section('meta_description', Str::limit(strip_tags($eventDesc), 180))
@section('meta_keywords', e($campaign->title) . ', লেখক নিবন্ধন, ডেলিগেট কার্ড, সাহিত্য সম্মেলন, লেখক সম্মেলন, আইডিয়া প্রকাশন')
@section('og_type', 'website')
@section('og_title', $campaign->title . ' — লেখকদের অংশগ্রহণ ফরম | আইডিয়া প্রকাশন')
@section('og_description', Str::limit(strip_tags($eventDesc), 180))
@section('og_image', $eventCover)
@section('og_url', url('/rsu-writer-2026'))

@section('schema_json')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "Event",
  "name": @json($campaign->title),
  "description": @json(Str::limit(strip_tags($eventDesc), 300)),
  "image": @json($eventCover),
  "url": @json(url('/rsu-writer-2026')),
  "startDate": "{{ optional($campaign->starts_at)->toIso8601String() ?: date('c') }}",
  "endDate": "{{ optional($campaign->ends_at)->toIso8601String() ?: date('c', strtotime('+30 days')) }}",
  "eventStatus": "https://schema.org/EventScheduled",
  "eventAttendanceMode": "https://schema.org/OfflineEventAttendanceMode",
  "location": {
    "@@type": "Place",
    "name": "আইডিয়া প্রকাশন সম্মেলন কেন্দ্র",
    "address": {
      "@@type": "PostalAddress",
      "addressLocality": "ঢাকা",
      "addressCountry": "BD"
    }
  },
  "organizer": {
    "@@type": "Organization",
    "name": "আইডিয়া প্রকাশন (Idea Publication)",
    "url": "https://www.ideaabd.com"
  },
  "offers": {
    "@@type": "Offer",
    "url": @json(url('/rsu-writer-2026')),
    "price": "{{ $campaign->has_fee_or_donation ? ($campaign->fee_amount ?: 0) : 0 }}",
    "priceCurrency": "BDT",
    "availability": "https://schema.org/InStock"
  }
}
</script>
@endsection

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700;800&family=Noto+Serif+Bengali:wght@600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --form-border: #0f172a;
            --form-grid: #334155;
            --form-bg-label: #f8fafc;
            --form-accent: #991b1b;
        }

        body {
            background-color: #f1f5f9;
            font-family: 'Hind Siliguri', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .letterhead-container {
            max-width: 900px;
            margin: 20px auto 50px auto;
            padding: 0 12px;
        }

        /* Top Action Bar */
        .form-top-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 12px;
        }

        /* The Main Paper Form */
        .official-letterhead-form {
            background: #ffffff;
            border: 2px solid #0f172a;
            border-radius: 4px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            padding: 24px 28px;
            color: #0f172a;
        }

        /* =========================================================================
           শীর্ষ পূর্ণাঙ্গ লেটার হেড প্যাড (Top Full-Width Letterhead Pad)
           ========================================================================= */
        .lh-pad-header {
            border-bottom: none;
            padding-bottom: 6px;
            margin-bottom: 12px;
            position: relative;
        }

        .lh-pad-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
        }

        .lh-pad-emblem-left {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            flex-shrink: 0;
            padding: 0;
            margin: 0;
            margin-top: -8px;
        }

        .logo-display-container {
            display: flex;
            align-items: center;
            justify-content: center;
            transition: width 0.15s ease, height 0.15s ease;
            position: relative;
        }

        .default-emblem-icon {
            color: #991b1b;
            font-size: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
        }

        .logo-control-toolbar {
            position: absolute;
            bottom: -28px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            align-items: center;
            gap: 3px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
            border-radius: 20px;
            padding: 2px 6px;
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease-in-out;
            z-index: 20;
            white-space: nowrap;
        }

        .lh-pad-emblem-left:hover .logo-control-toolbar,
        .lh-pad-emblem-left:focus-within .logo-control-toolbar {
            opacity: 1;
            visibility: visible;
            bottom: -22px;
        }

        .btn-logo-tool {
            background: transparent;
            border: none;
            padding: 2px 6px;
            font-size: 11px;
            color: #334155;
            cursor: pointer;
            border-radius: 4px;
            transition: background 0.15s, color 0.15s;
        }

        .btn-logo-tool:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        .btn-logo-tool.text-danger:hover {
            background: #fee2e2;
            color: #dc2626 !important;
        }

        .lh-pad-center {
            text-align: center;
            flex-grow: 1;
        }

        .lh-pad-title {
            font-size: 22px;
            font-weight: 900;
            color: #0f172a;
            line-height: 1.25;
            margin-bottom: 2px;
            font-family: 'Noto Serif Bengali', serif;
            letter-spacing: -0.3px;
        }

        .lh-pad-subhead {
            font-size: 14px;
            font-weight: 800;
            color: #991b1b;
            margin-bottom: 2px;
            letter-spacing: 0.3px;
        }

        .lh-pad-meta {
            font-size: 12.5px;
            font-weight: 700;
            color: #475569;
            margin-bottom: 1px;
        }

        .lh-pad-org {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
        }

        .lh-pad-badge-right {
            text-align: right;
            flex-shrink: 0;
        }

        .lh-pad-copy-tag {
            display: inline-block;
            background: #0f172a;
            color: #ffffff;
            font-size: 10px;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 3px;
            letter-spacing: 0.5px;
        }

        /* Clean Structured Grid Tables */
        .clean-grid-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            border: 1.5px solid #0f172a;
            margin-bottom: 12px;
        }

        .clean-grid-table td, .clean-grid-table th {
            border: 1px solid #0f172a;
            padding: 6px 8px;
            font-size: 12.5px;
            vertical-align: middle;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .clean-grid-table .c-label {
            background-color: #f8fafc;
            font-weight: 700;
            color: #0f172a;
            white-space: nowrap;
            width: 18%;
        }

        .clean-grid-table .c-val {
            background: #ffffff;
            padding: 4px 6px;
        }

        .c-input, .c-select {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 3px;
            padding: 5px 8px;
            font-size: 13px;
            font-weight: 500;
            color: #0f172a;
            background: #ffffff;
            font-family: inherit;
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        .c-input:focus, .c-select:focus {
            border-color: #0f172a;
            box-shadow: 0 0 0 2px rgba(15, 23, 42, 0.12);
        }

        .c-input.font-monospace {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }

        /* কান্ট্রি কোড বোল্ড কালার বাটন */
        .btn-country-code {
            background: #0f172a;
            color: #ffffff;
            font-weight: 800;
            font-size: 12.5px;
            border: 1.5px solid #0f172a;
            border-radius: 4px;
            padding: 5px 8px;
            cursor: pointer;
            outline: none;
            transition: all 0.2s ease-in-out;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.18);
            flex-shrink: 0;
            width: 95px;
        }

        .btn-country-code:hover, .btn-country-code:focus {
            background: #991b1b;
            border-color: #991b1b;
            color: #ffffff;
        }

        .btn-country-code option {
            background: #ffffff;
            color: #0f172a;
            font-weight: 600;
        }

        /* Section Category Header in Table */
        .grid-section-header {
            background: #0f172a !important;
            color: #ffffff !important;
            font-weight: 800;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 5px 8px !important;
        }

        /* Photo Upload Box inside personal table */
        .lh-photo-box {
            width: 115px;
            height: 135px;
            border: 1.5px solid #0f172a;
            background: #f8fafc;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            margin: 0 auto;
            transition: all 0.2s;
        }

        .lh-photo-box:hover {
            background: #fef2f2;
            border-color: #991b1b;
        }

        .lh-photo-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none;
        }

        .lh-photo-placeholder {
            font-size: 11px;
            color: #64748b;
            text-align: center;
            padding: 6px;
            font-weight: 600;
        }

        .lh-photo-placeholder i {
            font-size: 26px;
            color: #94a3b8;
            margin-bottom: 3px;
            display: block;
        }

        /* Category Checkbox Grid & Pills */
        .category-checkbox-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            padding: 2px 0;
        }

        .cat-pill {
            display: inline-flex;
            align-items: center;
            background: #f8fafc;
            border: 1.5px solid #cbd5e1;
            border-radius: 4px;
            padding: 3px 8px;
            font-size: 11.5px;
            font-weight: 700;
            color: #1e293b;
            cursor: pointer;
            user-select: none;
            transition: all 0.15s ease-in-out;
            margin: 0;
            touch-action: manipulation;
        }

        .cat-pill:hover {
            border-color: #0f172a;
            background: #f1f5f9;
        }

        .cat-pill input[type="checkbox"] {
            margin-right: 5px;
            accent-color: #991b1b;
            cursor: pointer;
            width: 14px;
            height: 14px;
        }

        .cat-pill:has(input:checked) {
            background: #0f172a;
            color: #ffffff;
            border-color: #0f172a;
        }

        .cat-pill:has(input:checked) input[type="checkbox"] {
            accent-color: #ef4444;
        }

        /* Address Toggle & Style */
        .res-type-pill-group {
            display: inline-flex;
            border: 1px solid #0f172a;
            border-radius: 4px;
            overflow: hidden;
        }

        .res-type-pill-group label {
            cursor: pointer;
            padding: 3px 10px;
            font-size: 11.5px;
            font-weight: 700;
            background: #ffffff;
            color: #0f172a;
            margin: 0;
            user-select: none;
            transition: all 0.15s;
            touch-action: manipulation;
        }

        .res-type-pill-group input[type="radio"]:checked + label {
            background: #0f172a;
            color: #ffffff;
        }

        /* Primary Submit Button */
        .btn-submit-letterhead {
            background: #0f172a;
            color: #ffffff;
            font-size: 15px;
            font-weight: 800;
            padding: 11px 36px;
            border-radius: 4px;
            border: 2px solid #0f172a;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            touch-action: manipulation;
        }

        .btn-submit-letterhead:hover {
            background: #991b1b;
            border-color: #991b1b;
            color: #ffffff;
            transform: translateY(-1px);
        }

        /* Live Badge for Address */
        #fullAddressBadgeWrap {
            background: #f8fafc;
            border: 1px dashed #334155;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 11.5px;
            font-family: ui-monospace, monospace;
            color: #0f172a;
            margin-top: 4px;
        }

        /* =========================================================================
           মোবাইল ও ট্যাবলেট আধুনিক রেসপন্সিভ ডিজাইন (Mobile & Tablet Device Friendly)
           ========================================================================= */
        @media (max-width: 768px) {
            .letterhead-container {
                padding: 0 8px;
                margin: 10px auto 40px auto;
            }

            .official-letterhead-form {
                padding: 16px 12px;
                border-width: 1.5px;
                border-radius: 8px;
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
            }

            .lh-pad-inner {
                flex-direction: column;
                text-align: center;
                gap: 12px;
            }

            .lh-pad-emblem-left {
                margin-bottom: 2px;
            }

            .lh-pad-badge-right {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                margin-top: 4px;
            }

            .lh-pad-title {
                font-size: 19px;
                line-height: 1.3;
            }

            .lh-pad-subhead {
                font-size: 13.5px;
            }

            .lh-pad-meta, .lh-pad-org {
                font-size: 12px;
            }

            /* মোবাইল টেবিল ফ্লেক্স ও টাচ ফ্রেন্ডলি কাঠামো */
            .clean-grid-table {
                display: block;
                border: 1.5px solid #0f172a;
                border-radius: 6px;
                overflow: hidden;
                margin-bottom: 14px;
            }

            .clean-grid-table tbody {
                display: block;
            }

            .clean-grid-table tr {
                display: flex;
                flex-direction: column;
                border-bottom: 1px solid #e2e8f0;
                padding: 8px 10px;
                background: #ffffff;
            }

            .clean-grid-table tr:last-child {
                border-bottom: none;
            }

            .clean-grid-table td.grid-section-header {
                display: block;
                width: 100%;
                border: none;
                padding: 6px 10px;
                font-size: 13px;
                font-weight: 800;
                background: #0f172a;
                color: #ffffff;
            }

            .clean-grid-table td {
                display: block;
                width: 100% !important;
                border: none !important;
                padding: 3px 0 !important;
            }

            .clean-grid-table .c-label {
                font-size: 12.5px;
                font-weight: 700;
                color: #334155;
                background: transparent !important;
                margin-bottom: 3px;
            }

            .clean-grid-table .c-val {
                padding: 0 !important;
                background: transparent !important;
            }

            /* ইনপুট ও ড্রপডাউনের টাচ ফ্রেন্ডলি উচ্চতা */
            .c-input, .c-select {
                min-height: 42px;
                padding: 8px 12px;
                font-size: 14.5px;
                border-radius: 6px;
                border: 1.5px solid #cbd5e1;
                background: #f8fafc;
                width: 100%;
                box-sizing: border-box;
                -webkit-appearance: none;
                appearance: none;
            }

            .c-select {
                background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
                background-repeat: no-repeat;
                background-position: right 10px center;
                background-size: 14px;
                padding-right: 32px;
            }

            .c-input:focus, .c-select:focus {
                background: #ffffff;
                border-color: #0f172a;
                box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.12);
            }

            /* ছবি আপলোড বক্স মোবাইলে দৃষ্টিনন্দন ও কেন্দ্রীয় */
            .photo-cell-desktop {
                order: -1;
                width: 100% !important;
                margin: 4px auto 10px auto;
                background: transparent !important;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
            }

            .lh-photo-box {
                width: 110px;
                height: 130px;
                border-radius: 6px;
                border: 2px dashed #0f172a;
                background: #f1f5f9;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            }

            .lh-photo-box:hover {
                background: #fee2e2;
                border-color: #991b1b;
            }

            /* ক্যাটাগরি চিপস মোবাইলে বড় ও সহজে ট্যাপযোগ্য */
            .category-checkbox-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
                gap: 6px;
                margin-top: 4px;
            }

            .cat-pill {
                font-size: 12px;
                padding: 7px 10px;
                min-height: 40px;
                border-radius: 6px;
                display: flex;
                align-items: center;
                gap: 6px;
                justify-content: flex-start;
                touch-action: manipulation;
            }

            .cat-pill input[type="checkbox"] {
                width: 16px;
                height: 16px;
            }

            /* দেশি / প্রবাসী টগল বাটন */
            .res-type-pill-group {
                width: 100%;
                display: flex;
                border-radius: 6px;
            }

            .res-type-pill-group label {
                flex: 1;
                text-align: center;
                padding: 8px 10px;
                font-size: 12.5px;
            }

            /* সাবমিট বাটন মোবাইলে সম্পূর্ণ প্রস্থ ও দৃশ্যমান */
            .btn-submit-letterhead {
                width: 100%;
                min-height: 48px;
                justify-content: center;
                padding: 12px 20px;
                font-size: 16px;
                font-weight: 800;
                border-radius: 8px;
                box-shadow: 0 4px 14px rgba(15, 23, 42, 0.25);
            }
        }

        /* =========================================================================
           প্রিন্ট রেসপন্সিভ ডিজাইন (Print-Perfect Letterhead A4 Fit)
           ========================================================================= */
        @media print {
            body { 
                background: #fff !important; 
                margin: 0 !important; 
                padding: 0 !important;
            }
            @page {
                size: A4 portrait;
                margin: 8mm 10mm;
            }
            .letterhead-container { 
                margin: 0 !important; 
                padding: 0 !important; 
                max-width: 100% !important; 
            }
            .official-letterhead-form { 
                border: 1.5px solid #000 !important; 
                box-shadow: none !important;
                padding: 12px 16px !important;
                page-break-inside: avoid;
            }
            .clean-grid-table {
                display: table !important;
                border: 1.5px solid #000 !important;
                margin-bottom: 8px !important;
            }
            .clean-grid-table tbody {
                display: table-row-group !important;
            }
            .clean-grid-table tr {
                display: table-row !important;
                border: none !important;
                padding: 0 !important;
                background: transparent !important;
            }
            .clean-grid-table td, .clean-grid-table th {
                display: table-cell !important;
                border: 1px solid #000 !important;
                padding: 4px 6px !important;
                font-size: 11.5px !important;
            }
            .clean-grid-table .c-label {
                width: 18% !important;
                background-color: #f8fafc !important;
            }
            .clean-grid-table .c-val {
                background: #ffffff !important;
            }
            .photo-cell-desktop {
                display: table-cell !important;
                width: 22% !important;
                margin: 0 !important;
            }
            .c-input, .c-select {
                border: none !important;
                background: transparent !important;
                padding: 0 !important;
                font-weight: 700 !important;
                color: #000 !important;
                min-height: auto !important;
                font-size: 12px !important;
            }
            .form-top-actions, .btn-submit-letterhead, .no-print { 
                display: none !important; 
            }
        }
    </style>
@endpush

@section('content')
@php
    $adminLogo = $campaign->form_settings['logo_image'] ?? null;
    if ($adminLogo && !str_starts_with($adminLogo, 'http') && !str_starts_with($adminLogo, '/')) {
        $adminLogo = asset('storage/' . $adminLogo);
    }
    $adminLogoSize = intval($campaign->form_settings['logo_size'] ?? 70);
    $isAdmin = auth()->check() && (auth()->user()->is_admin || auth()->user()->role === 'admin' || auth()->user()->is_super_admin);
@endphp
<div class="letterhead-container">

    {{-- Top Action Bar --}}
    <div class="form-top-actions">
        <div>
            <a href="{{ route('home') }}" class="btn btn-sm btn-outline-dark fw-semibold">
                <i class="fa-solid fa-arrow-left me-1"></i> Home
            </a>
            <span class="badge bg-danger text-white ms-2 px-2.5 py-1.5 fw-bold">
                <i class="fa-solid fa-feather-pointed me-1"></i> Author Entry
            </span>
            @if($isAdmin)
                <span class="badge bg-dark text-white ms-1 px-2 py-1 small">
                    <i class="fa-solid fa-shield-halved me-1"></i> Admin Mode
                </span>
            @endif
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                <i class="fa-solid fa-print me-1"></i> Print Form
            </button>
        </div>
    </div>

    {{-- Alert Notifications --}}
    @auth
        <div class="card border-0 rounded-3 p-3 mb-3 shadow-xs d-flex flex-row align-items-center justify-content-between flex-wrap gap-2" style="background: #f0fdf4; border: 1px solid #86efac !important;">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; font-size: 14px;">
                    <i class="fa-solid fa-user-check"></i>
                </div>
                <div>
                    <div class="fw-bold text-dark" style="font-size: 13px;">
                        <span>আপনি <strong>{{ auth()->user()->name }}</strong> হিসেবে সাইন-ইন আছেন</span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle ms-1">লগইনকৃত</span>
                    </div>
                    <div class="small text-muted font-monospace" style="font-size: 11.5px;">
                        <i class="fa-solid fa-phone me-1 text-success"></i> {{ auth()->user()->phone ?? '—' }}
                        @if(auth()->user()->email)
                            <span class="mx-1">|</span> <i class="fa-solid fa-envelope me-1 text-success"></i> {{ auth()->user()->email }}
                        @endif
                    </div>
                </div>
            </div>
            <div>
                <a href="{{ route('logout') }}" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1.5" onclick="if(window.siteLogout){window.siteLogout(event);}else{event.preventDefault();document.getElementById('writerLogoutForm').submit();}">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    <span>লগআউট / অন্য একাউন্ট</span>
                </a>
                <form id="writerLogoutForm" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </div>
    @endauth

    @if(session('error'))
        <div class="alert alert-danger rounded-2 py-2 px-3 mb-3 small d-flex align-items-center gap-2">
            <i class="fa-solid fa-circle-exclamation text-danger fs-5"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger rounded-2 py-2 px-3 mb-3 small">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- MAIN OFFICIAL LETTERHEAD FORM --}}
    <form action="{{ route('event.submit', $campaign->slug) }}" method="POST" enctype="multipart/form-data" id="writerRegisterForm" class="official-letterhead-form">
        @csrf

        {{-- Hidden input for Client-Side Auto-Optimized Photo Blob (Base64) --}}
        <input type="hidden" name="optimized_photo_data" id="optimizedPhotoData">

        {{-- =========================================================================
             ১. শীর্ষ লেটার হেড প্যাড (Authentic Full-Width Letterhead Pad)
             ========================================================================= --}}
        <div class="lh-pad-header">
            <div class="lh-pad-inner">
                {{-- লোগো / এমব্লেম (বর্ডারলেস, এডমিন কর্তৃক নিয়ন্ত্রিত) --}}
                <div class="lh-pad-emblem-left borderless-emblem-wrap" id="logoEmblemWrap" title="{{ $isAdmin ? 'লোগো পরিবর্তন ও সাইজ নির্ধারণ করতে হোভার করুন (অ্যাডমিন সেটিং)' : '' }}">
                    <div class="logo-display-container" id="logoDisplayContainer" style="width: {{ $adminLogoSize }}px; height: {{ $adminLogoSize }}px;">
                        <img id="customLogoImg" src="{{ $adminLogo ?: '' }}" alt="{{ $campaign->title }}" style="{{ $adminLogo ? 'display: block;' : 'display: none;' }} width: 100%; height: 100%; object-fit: contain;">
                        <div id="defaultEmblemIcon" class="default-emblem-icon" style="{{ $adminLogo ? 'display: none;' : 'display: flex;' }} font-size: {{ round($adminLogoSize * 0.55) }}px;">
                            <i class="fa-solid fa-feather-pointed"></i>
                        </div>
                    </div>

                    @if($isAdmin)
                        {{-- অ্যাডমিন লোগো কন্ট্রোল টুলবার (শুধুমাত্র অ্যাডমিন লগইন অবস্থায় দৃশ্যমান) --}}
                        <div class="logo-control-toolbar no-print">
                            <span class="badge bg-dark text-white me-1" style="font-size: 8.5px; padding: 2px 4px;">ADMIN</span>
                            <button type="button" class="btn-logo-tool" title="লোগো আপলোড / পরিবর্তন" onclick="document.getElementById('customLogoFileInput').click()">
                                <i class="fa-solid fa-camera"></i>
                            </button>
                            <button type="button" class="btn-logo-tool" title="লোগো বড় করুন (+)" onclick="adjustLogoSize(8)">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                            <button type="button" class="btn-logo-tool" title="লোগো ছোট করুন (-)" onclick="adjustLogoSize(-8)">
                                <i class="fa-solid fa-minus"></i>
                            </button>
                            <button type="button" class="btn-logo-tool text-danger" title="লোগো রিমুভ" onclick="removeCustomLogo()">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                        <input type="file" id="customLogoFileInput" accept="image/*" class="d-none" onchange="handleCustomLogoUpload(this)">
                    @endif
                </div>

                {{-- মূল লেটার হেড বিবরণী --}}
                <div class="lh-pad-center">
                    <div class="lh-pad-title">
                        {{ $campaign->title }}
                    </div>
                    <div class="lh-pad-meta" style="font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 2px;">
                        স্থান: সরকারি টিচার্স ট্রেনিং কলেজ, রংপুর, বাংলাদেশ
                    </div>
                    <div class="lh-pad-subhead" style="font-size: 13.5px; font-weight: 800; color: #991b1b; margin-bottom: 3px;">
                        তারিখ: ১৩ নভেম্বর ২০২৬
                    </div>
                    <div class="lh-pad-org" style="font-size: 11.5px; font-weight: 700; color: #475569;">
                        নিবন্ধন সহযোগিতায়: আইডিয়া প্রকাশন &nbsp;|&nbsp; <span class="font-monospace">www.ideaabd.com</span>
                    </div>
                </div>

                {{-- কপি স্ট্যাম্প --}}
                <div class="lh-pad-badge-right">
                    <div class="lh-pad-copy-tag">{{ !empty($existingRegistration) ? 'REGISTERED' : 'DELEGATE COPY' }}</div>
                    <div class="mt-1 font-monospace" style="font-size: 11px; font-weight: 800; color: #0f172a;">
                        #{{ $previewRegNumber ?? ($existingRegistration?->registration_number ?? 'RSU-2026-001') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- =========================================================================
             ১. পরিচিতি ও বিবরণী (Author / Delegate Profile & Literary Details) + Photo
             ========================================================================= --}}
        <table class="clean-grid-table">
            <colgroup>
                <col style="width: 18%;">
                <col style="width: 32%;">
                <col style="width: 18%;">
                <col style="width: 32%;">
            </colgroup>
            <tr>
                <td class="grid-section-header" colspan="4">
                    ১. পরিচিতি
                </td>
            </tr>
            <tr>
                <td class="c-label">নাম <span class="text-danger">*</span></td>
                <td class="c-val" colspan="2">
                    <input type="text" name="name" id="writerName" class="c-input text-uppercase fw-bold" placeholder="লেখকের পূর্ণ নাম" value="{{ old('name', $existingRegistration?->name ?? $user?->name) }}" required oninput="handleNameInput(this)">
                </td>
                <td rowspan="4" class="text-center photo-cell-desktop" style="width: 32%; vertical-align: middle; background: #fafafa;">
                    <div class="lh-photo-box" onclick="document.getElementById('rawPhotoInput').click()" title="ছবি আপলোড করতে ক্লিক করুন">
                        @php
                            $existingPhoto = $existingRegistration?->form_data['student_photo'] ?? null;
                            if ($existingPhoto && !str_starts_with($existingPhoto, 'http') && !str_starts_with($existingPhoto, '/')) {
                                $existingPhoto = asset('storage/' . $existingPhoto);
                            }
                        @endphp
                        <img id="photoPreviewThumb" class="lh-photo-img" src="{{ $existingPhoto ?: '' }}" style="{{ $existingPhoto ? 'display: block;' : 'display: none;' }}" alt="Author Photo">
                        <div id="photoUploadPlaceholder" class="lh-photo-placeholder" style="{{ $existingPhoto ? 'display: none;' : '' }}">
                            <i class="fa-solid fa-camera text-primary fs-5 mb-1"></i>
                            <div style="font-weight: 700; color: #0f172a; font-size: 12px;">ছবি আপলোড <span class="text-danger">*</span></div>
                            <div style="font-size: 10px; color: #dc2626; font-weight: 600;">(বাধ্যতামূলক)</div>
                            <div style="font-size: 8.5px; color: #64748b; margin-top: 2px;">পাসপোর্ট সাইজ</div>
                        </div>
                    </div>
                    <input type="file" id="rawPhotoInput" name="student_photo" accept="image/*" class="d-none" onchange="optimizeWriterPhoto(this)">
                </td>
            </tr>
            <tr>
                <td class="c-label">ক্যাটাগরি <span class="text-danger">*</span></td>
                <td class="c-val" colspan="2">
                    @php
                        $existingCats = $existingRegistration?->form_data['author_categories'] ?? ($existingRegistration?->form_data['author_category'] ? explode(', ', $existingRegistration->form_data['author_category']) : null);
                        $oldCategories = old('author_categories', (array) (old('author_category') ? explode(', ', old('author_category')) : ($existingCats ?: ['কবি ও কথাসাহিত্যিক'])));
                    @endphp
                    <div class="category-checkbox-grid">
                        @foreach([
                            'কবি ও কথাসাহিত্যিক',
                            'লিটিলম্যাগাজিন সম্পাদক',
                            'শিল্পী / সংস্কৃতিকর্মী',
                            'প্রাবন্ধিক ও গবেষক',
                            'শিশুসাহিত্যিক ও ছড়াকার',
                            'প্রকাশক',
                            'বই প্রতিনিধি ও সংগঠক',
                            'অন্যান্য / প্রতিনিধি'
                        ] as $catOption)
                            <label class="cat-pill">
                                <input type="checkbox" name="author_categories[]" value="{{ $catOption }}" {{ in_array($catOption, $oldCategories) ? 'checked' : '' }} onchange="handleMultipleCategoryChange()">
                                <span>{{ $catOption }}</span>
                            </label>
                        @endforeach
                    </div>
                    <input type="hidden" name="author_category" id="authorCategoryHidden" value="{{ old('author_category', implode(', ', $oldCategories)) }}">
                </td>
            </tr>
            <tr>
                <td class="c-label">মোবাইল <span class="text-danger">*</span></td>
                <td class="c-val" colspan="2">
                    <div class="d-flex align-items-center gap-2">
                        <select name="country_code" id="countryCodeSelect" class="btn-country-code">
                            <option value="+880" selected>🇧🇩 +880</option>
                            <option value="+91">🇮🇳 +91</option>
                            <option value="+44">🇬🇧 +44</option>
                            <option value="+1">🇺🇸 +1</option>
                            <option value="+966">🇸🇦 +966</option>
                            <option value="+971">🇦🇪 +971</option>
                            <option value="+60">🇲🇾 +60</option>
                            <option value="+65">🇸🇬 +65</option>
                        </select>
                        <input type="tel" name="phone" id="writerPhone" class="c-input font-monospace fw-bold" placeholder="017XXXXXXXX" value="{{ old('phone', $existingRegistration?->phone ?? $user?->phone) }}" required oninput="handlePhoneInput(this)">
                    </div>
                </td>
            </tr>
            <tr>
                <td class="c-label">ইমেইল</td>
                <td class="c-val" colspan="2">
                    <input type="email" name="email" id="writerEmail" class="c-input" placeholder="ইমেইল (ঐচ্ছিক)" value="{{ old('email', $existingRegistration?->email ?? $user?->email) }}">
                </td>
            </tr>

            {{-- =========================================================================
                 ক্যাটাগরি ভিত্তিক স্বয়ংক্রিয় তথ্য সারি (Dynamic Category-Specific Rows)
                 ========================================================================= --}}
            
            {{-- ১. শিল্পী / সংস্কৃতিকর্মী --}}
            <tr id="catRowArtist" style="display: none;">
                <td class="c-label">শিল্পমাধ্যম <span class="text-danger">*</span></td>
                <td class="c-val" style="width: 32%;">
                    <input type="text" name="art_medium" id="writerArtMedium" class="c-input" placeholder="সংগীত, আবৃত্তি, নাটক, চিত্র ইত্যাদি" value="{{ old('art_medium', $existingRegistration?->form_data['art_medium'] ?? '') }}">
                </td>
                <td class="c-label" style="width: 18%;">সংগঠন</td>
                <td class="c-val" style="width: 32%;">
                    <input type="text" name="institution_or_org" id="writerArtistOrg" class="c-input" placeholder="সংগঠন / দল / নাট্যদল" value="{{ old('institution_or_org', $existingRegistration?->form_data['institution_or_org'] ?? ($existingRegistration?->form_data['organization_name'] ?? '')) }}">
                </td>
            </tr>

            {{-- ২. বই প্রতিনিধি ও সংগঠক --}}
            <tr id="catRowOrganizer" style="display: none;">
                <td class="c-label">সংগঠন / প্রতিষ্ঠান <span class="text-danger">*</span></td>
                <td class="c-val" style="width: 32%;">
                    <input type="text" name="institution_or_org" id="writerOrgName" class="c-input" placeholder="প্রতিষ্ঠান / সংগঠনের নাম" value="{{ old('institution_or_org', $existingRegistration?->form_data['institution_or_org'] ?? ($existingRegistration?->form_data['organization_name'] ?? '')) }}">
                </td>
                <td class="c-label" style="width: 18%;">দায়িত্ব / পদবি</td>
                <td class="c-val" style="width: 32%;">
                    <input type="text" name="designation_or_class" id="writerOrgDesignation" class="c-input" placeholder="যেমন: প্রতিনিধি / সমন্বয়ক" value="{{ old('designation_or_class', $existingRegistration?->designation_or_class ?? ($existingRegistration?->form_data['designation_or_class'] ?? '')) }}">
                </td>
            </tr>

            {{-- ৩. লিটিলম্যাগাজিন ও প্রকাশনা সারি --}}
            <tr id="catRowMag">
                <td class="c-label" id="labelMagName">ছোটকাগজ</td>
                <td class="c-val" style="width: 32%;">
                    <input type="text" name="magazine_name" id="magazineName" class="c-input" placeholder="লিটিলম্যাগাজিন / পত্রিকার নাম" value="{{ old('magazine_name', $existingRegistration?->form_data['magazine_name'] ?? '') }}">
                </td>
                <td class="c-label" id="labelMagIssue" style="width: 18%;">সংখ্যা</td>
                <td class="c-val" style="width: 32%;">
                    <input type="text" name="magazine_issue_count" id="magazineIssueCount" class="c-input" placeholder="যেমন: ৫টি সংখ্যা" value="{{ old('magazine_issue_count', $existingRegistration?->form_data['magazine_issue_count'] ?? '') }}">
                </td>
            </tr>

            {{-- ৪. সাহিত্যকর্ম ও বইসংখ্যা সারি --}}
            <tr id="catRowLiterary">
                <td class="c-label" id="labelGenreOrOrg">শাখা / মাধ্যম</td>
                <td class="c-val" style="width: 32%;">
                    <input type="text" name="designation_or_class" id="writerDesignationInput" class="c-input" placeholder="কবিতা, কথাসাহিত্য, প্রবন্ধ..." value="{{ old('designation_or_class', $existingRegistration?->designation_or_class ?? ($existingRegistration?->form_data['designation_or_class'] ?? 'কবিতা ও কথাসাহিত্য')) }}">
                </td>
                <td class="c-label" id="labelBooksCount" style="width: 18%;">বইসংখ্যা</td>
                <td class="c-val" style="width: 32%;">
                    @php
                        $savedBooksCount = old('published_books_count', $existingRegistration?->form_data['published_books_count'] ?? '২-৫টি গ্রন্থ');
                    @endphp
                    <select name="published_books_count" id="publishedBooksInput" class="c-select">
                        <option value="১টি গ্রন্থ" {{ $savedBooksCount == '১টি গ্রন্থ' ? 'selected' : '' }}>১টি গ্রন্থ</option>
                        <option value="২-৫টি গ্রন্থ" {{ $savedBooksCount == '২-৫টি গ্রন্থ' ? 'selected' : '' }}>২-৫টি গ্রন্থ</option>
                        <option value="৬-১০টি গ্রন্থ" {{ $savedBooksCount == '৬-১০টি গ্রন্থ' ? 'selected' : '' }}>৬-১০টি গ্রন্থ</option>
                        <option value="১০টির অধিক গ্রন্থ" {{ $savedBooksCount == '১০টির অধিক গ্রন্থ' ? 'selected' : '' }}>১০টির অধিক গ্রন্থ</option>
                        <option value="গ্রন্থ প্রকাশের অপেক্ষায়" {{ $savedBooksCount == 'গ্রন্থ প্রকাশের অপেক্ষায়' ? 'selected' : '' }}>গ্রন্থ প্রকাশের অপেক্ষায়</option>
                    </select>
                </td>
            </tr>

            {{-- ৫. উল্লেখযোগ্য প্রকাশিত গ্রন্থসমূহ --}}
            <tr id="catRowBooks">
                <td class="c-label" id="labelNotableBooks">গ্রন্থসমূহ</td>
                <td colspan="3" class="c-val">
                    <input type="text" name="notable_books" id="notableBooks" class="c-input text-uppercase" placeholder="উল্লেখযোগ্য প্রকাশিত বইয়ের নাম..." value="{{ old('notable_books', $existingRegistration?->form_data['notable_books'] ?? '') }}">
                </td>
            </tr>
        </table>

        {{-- =========================================================================
             ২. ঠিকানা (Address Details) — এক ক্লিকে টগল ও স্বয়ংক্রিয় ড্রপডাউন
             ========================================================================= --}}
        <div class="d-flex align-items-center justify-content-between my-2">
            <span class="fw-bold" style="font-size: 13px;"><i class="fa-solid fa-location-dot me-1 text-danger"></i> ২. ঠিকানা</span>
            <div class="res-type-pill-group">
                <input type="radio" name="resident_type" id="resTypeBd" value="domestic" checked onchange="toggleResidentAddress('domestic')">
                <label for="resTypeBd"><i class="fa-solid fa-flag me-1"></i> বাংলাদেশ</label>

                <input type="radio" name="resident_type" id="resTypeIntl" value="international" onchange="toggleResidentAddress('international')">
                <label for="resTypeIntl"><i class="fa-solid fa-earth-americas me-1"></i> প্রবাসী / বিদেশী</label>
            </div>
        </div>

        {{-- Hidden Full Address --}}
        <input type="hidden" name="address" id="fullWriterAddress" value="{{ old('address') }}">

        {{-- ২.A বাংলাদেশ ঠিকানা গ্রিড --}}
        <table class="clean-grid-table" id="bdAddressTable">
            <tr>
                <td class="c-label">বিভাগ <span class="text-danger">*</span></td>
                <td class="c-val" style="width: 32%;">
                    <select name="perm_division" id="writerDivision" class="c-select" required></select>
                </td>
                <td class="c-label">জেলা <span class="text-danger">*</span></td>
                <td class="c-val" style="width: 32%;">
                    <select name="district" id="writerDistrict" class="c-select" required></select>
                </td>
            </tr>
            <tr>
                <td class="c-label">উপজেলা <span class="text-danger">*</span></td>
                <td class="c-val">
                    <select name="thana" id="writerUpazila" class="c-select" required></select>
                </td>
                <td class="c-label">ডাকঘর</td>
                <td class="c-val">
                    <select name="perm_post_office" id="writerPostOffice" class="c-select"></select>
                </td>
            </tr>
            <tr>
                <td class="c-label">ঠিকানা <span class="text-danger">*</span></td>
                <td colspan="3" class="c-val">
                    <input type="text" name="perm_village" id="writerVillage" class="c-input text-uppercase" placeholder="গ্রাম / মহল্লা / সড়ক নম্বর" required oninput="formatWriterAddress()">
                </td>
            </tr>
        </table>

        {{-- ২.B প্রবাসী / বিদেশি ঠিকানা গ্রিড --}}
        <table class="clean-grid-table" id="intlAddressTable" style="display: none;">
            <tr>
                <td class="c-label">দেশ <span class="text-danger">*</span></td>
                <td class="c-val" style="width: 32%;">
                    <select name="country_name" id="writerCountrySelect" class="c-select" onchange="handleCountryChange(this.value)">
                        <option value="ভারত (West Bengal / India)">ভারত (পশ্চিমবঙ্গ / অন্যান্য)</option>
                        <option value="United Kingdom">যুক্তরাজ্য (UK)</option>
                        <option value="United States">যুক্তরাষ্ট্র (USA)</option>
                        <option value="Canada">কানাডা (Canada)</option>
                        <option value="Saudi Arabia">সৌদি আরব (Saudi Arabia)</option>
                        <option value="United Arab Emirates">সংযুক্ত আরব আমিরাত (UAE)</option>
                        <option value="Qatar">কাতার (Qatar)</option>
                        <option value="Kuwait">কুয়েত (Kuwait)</option>
                        <option value="Oman">ওমান (Oman)</option>
                        <option value="Malaysia">মালয়েশিয়া (Malaysia)</option>
                        <option value="Singapore">সিঙ্গাপুর (Singapore)</option>
                        <option value="Australia">অস্ট্রেলিয়া (Australia)</option>
                        <option value="Germany">জার্মানি (Germany)</option>
                        <option value="Italy">ইতালি (Italy)</option>
                        <option value="OTHER">-- অন্যান্য দেশ (কাস্টম নাম) --</option>
                    </select>
                </td>
                <td class="c-label">শহর / রাজ্য <span class="text-danger">*</span></td>
                <td class="c-val" style="width: 32%;">
                    <input type="text" name="state_or_city" id="writerForeignCity" class="c-input text-uppercase" placeholder="Kolkata, London, New York..." oninput="formatWriterAddress()">
                </td>
            </tr>
            <tr id="customCountryRow" style="display: none;">
                <td class="c-label">দেশ <span class="text-danger">*</span></td>
                <td colspan="3" class="c-val">
                    <input type="text" name="custom_country" id="writerCustomCountry" class="c-input" placeholder="দেশের নাম লিখুন" oninput="formatWriterAddress()">
                </td>
            </tr>
            <tr>
                <td class="c-label">পোস্টকোড</td>
                <td class="c-val">
                    <input type="text" name="zip_code" id="writerForeignZip" class="c-input font-monospace" placeholder="700001, 10001, E1 6AN..." oninput="formatWriterAddress()">
                </td>
                <td class="c-label">ঠিকানা <span class="text-danger">*</span></td>
                <td class="c-val">
                    <input type="text" name="foreign_address" id="writerForeignStreet" class="c-input" placeholder="বাড়ি ও পূর্ণ আন্তর্জাতিক সড়ক ঠিকানা" oninput="formatWriterAddress()">
                </td>
            </tr>
        </table>

        {{-- ঠিকানা প্রিভিউ বার --}}
        <div id="fullAddressBadgeWrap" style="display: none;">
            <i class="fa-solid fa-location-arrow text-danger me-1"></i> <strong>পূর্ণ ঠিকানা:</strong> <span id="fullAddressText"></span>
        </div>

        {{-- =========================================================================
             ৩. পেমেন্ট বিবরণী (যদি ফি প্রযোজ্য হয়)
             ========================================================================= --}}
        @if($campaign->has_fee_or_donation)
            <table class="clean-grid-table mt-2">
                <tr>
                    <td class="grid-section-header" colspan="4">৩. পেমেন্ট বিবরণী</td>
                </tr>
                <tr>
                    <td class="c-label">ফি</td>
                    <td class="c-val font-monospace fw-bold text-danger">
                        ৳{{ number_format($campaign->fee_amount ?: 0) }}
                        <input type="hidden" name="amount_paid" value="{{ $campaign->fee_amount ?: 0 }}">
                    </td>
                    <td class="c-label">ট্রানজেকশন <span class="text-danger">*</span></td>
                    <td class="c-val">
                        <input type="text" name="transaction_id" class="c-input font-monospace" placeholder="বিকাশ/নগদ TrxID" value="{{ old('transaction_id') }}" required>
                    </td>
                </tr>
            </table>
        @endif

        {{-- অফিসিয়াল লেখকের স্বাক্ষর ব্লক --}}
        <div style="display: flex; justify-content: flex-end; margin-top: 35px; margin-bottom: 25px; padding-right: 15px;">
            <div style="text-align: center; min-width: 200px;">
                <div id="authorSigName" class="text-uppercase fw-bold font-monospace" style="font-size: 13px; color: #0f172a; min-height: 20px; letter-spacing: 0.5px; padding-bottom: 4px;">
                    {{ old('name', $user?->name) }}
                </div>
                <div style="border-top: 1.5px solid #0f172a; padding-top: 4px; font-size: 12px; font-weight: 700; color: #0f172a;">
                    লেখকের স্বাক্ষর
                </div>
            </div>
        </div>

        {{-- সাবমিট বাটন --}}
        <div class="text-center mt-4 no-print">
            <button type="submit" id="submitWriterBtn" class="btn-submit-letterhead">
                <i class="fa-solid fa-paper-plane"></i>
                <span>জমা দিন</span>
            </button>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/bd-geo-data.js') }}"></script>
<script>
let currentResidentType = 'domestic';
let currentLogoSize = {{ $adminLogoSize }};
const isAdmin = {{ $isAdmin ? 'true' : 'false' }};
const logoSettingsUrl = "{{ $isAdmin ? route('admin.event-campaigns.logo-settings', $campaign->id) : '' }}";
const csrfToken = "{{ csrf_token() }}";

// লোগো আপলোড ও সাইজ কন্ট্রোল ফাংশন (অ্যাডমিন নিয়ন্ত্রিত)
function handleCustomLogoUpload(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];

    const reader = new FileReader();
    reader.onload = function (e) {
        setCustomLogo(e.target.result);
    };
    reader.readAsDataURL(file);

    if (isAdmin && logoSettingsUrl) {
        const formData = new FormData();
        formData.append('logo_image', file);
        formData.append('logo_size', currentLogoSize);
        formData.append('_token', csrfToken);

        fetch(logoSettingsUrl, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.logo_url) {
                setCustomLogo(data.logo_url);
            }
        })
        .catch(err => console.error('Logo upload error:', err));
    }
}

function setCustomLogo(src) {
    const img = document.getElementById('customLogoImg');
    const defaultIcon = document.getElementById('defaultEmblemIcon');
    if (img && defaultIcon) {
        img.src = src;
        img.style.display = 'block';
        defaultIcon.style.display = 'none';
    }
}

function removeCustomLogo() {
    if (!confirm('লোগোটি রিমুভ করতে চান?')) return;

    const img = document.getElementById('customLogoImg');
    const defaultIcon = document.getElementById('defaultEmblemIcon');
    const fileInput = document.getElementById('customLogoFileInput');
    if (img) {
        img.src = '';
        img.style.display = 'none';
    }
    if (defaultIcon) {
        defaultIcon.style.display = 'flex';
    }
    if (fileInput) fileInput.value = '';

    if (isAdmin && logoSettingsUrl) {
        const formData = new FormData();
        formData.append('remove_logo', '1');
        formData.append('_token', csrfToken);

        fetch(logoSettingsUrl, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).catch(err => console.error(err));
    }
}

let saveLogoSizeTimer = null;
function adjustLogoSize(delta) {
    currentLogoSize = Math.max(35, Math.min(150, currentLogoSize + delta));
    applyLogoSize(currentLogoSize);

    if (isAdmin && logoSettingsUrl) {
        clearTimeout(saveLogoSizeTimer);
        saveLogoSizeTimer = setTimeout(() => {
            const formData = new FormData();
            formData.append('logo_size', currentLogoSize);
            formData.append('_token', csrfToken);

            fetch(logoSettingsUrl, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).catch(err => console.error(err));
        }, 500);
    }
}

function applyLogoSize(size) {
    const container = document.getElementById('logoDisplayContainer');
    if (container) {
        container.style.width = size + 'px';
        container.style.height = size + 'px';
    }
    const icon = document.querySelector('.default-emblem-icon');
    if (icon) {
        icon.style.fontSize = Math.round(size * 0.55) + 'px';
    }
}

function toggleResidentAddress(type) {
    currentResidentType = type;
    const bdTable = document.getElementById('bdAddressTable');
    const intlTable = document.getElementById('intlAddressTable');
    const divSelect = document.getElementById('writerDivision');
    const distSelect = document.getElementById('writerDistrict');
    const upaSelect = document.getElementById('writerUpazila');
    const vilInput = document.getElementById('writerVillage');
    const countrySelect = document.getElementById('writerCountrySelect');
    const cityInput = document.getElementById('writerForeignCity');
    const streetInput = document.getElementById('writerForeignStreet');
    const customCountryInput = document.getElementById('writerCustomCountry');
    const zipInput = document.getElementById('writerForeignZip');

    if (type === 'international') {
        if (bdTable) {
            bdTable.style.display = 'none';
            bdTable.querySelectorAll('input, select, textarea').forEach(el => el.disabled = true);
        }
        if (intlTable) {
            intlTable.style.display = 'table';
            intlTable.querySelectorAll('input, select, textarea').forEach(el => el.disabled = false);
        }

        if (divSelect) divSelect.required = false;
        if (distSelect) distSelect.required = false;
        if (upaSelect) upaSelect.required = false;
        if (vilInput) vilInput.required = false;

        if (countrySelect) countrySelect.required = true;
        if (cityInput) cityInput.required = true;
        if (streetInput) streetInput.required = true;
    } else {
        if (bdTable) {
            bdTable.style.display = 'table';
            bdTable.querySelectorAll('input, select, textarea').forEach(el => el.disabled = false);
        }
        if (intlTable) {
            intlTable.style.display = 'none';
            intlTable.querySelectorAll('input, select, textarea').forEach(el => el.disabled = true);
        }

        if (divSelect) divSelect.required = true;
        if (distSelect) distSelect.required = true;
        if (upaSelect) upaSelect.required = true;
        if (vilInput) vilInput.required = true;

        if (countrySelect) countrySelect.required = false;
        if (cityInput) cityInput.required = false;
        if (streetInput) streetInput.required = false;
        if (customCountryInput) customCountryInput.required = false;
    }

    formatWriterAddress();
}

function handleCountryChange(val) {
    const row = document.getElementById('customCountryRow');
    const customInput = document.getElementById('writerCustomCountry');
    if (val === 'OTHER') {
        if (row) row.style.display = 'table-row';
        if (customInput) {
            customInput.required = true;
            customInput.focus();
        }
    } else {
        if (row) row.style.display = 'none';
        if (customInput) customInput.required = false;
    }
    formatWriterAddress();
}

function handleNameInput(input) {
    const val = input.value.trim();
    const sigName = document.getElementById('authorSigName');
    if (sigName) {
        sigName.textContent = val ? val.toUpperCase() : '';
    }
}

// ক্যাটাগরি অনুসারে তথ্য সারির স্বয়ংক্রিয় পরিবর্তন (মাল্টি-সিলেক্ট সাপোর্ট)
function handleMultipleCategoryChange() {
    const checkedBoxes = Array.from(document.querySelectorAll('input[name="author_categories[]"]:checked'));
    const selectedVals = checkedBoxes.map(cb => cb.value);
    
    // Hidden author_category field sync
    const hiddenCat = document.getElementById('authorCategoryHidden');
    if (hiddenCat) {
        hiddenCat.value = selectedVals.join(', ');
    }

    const rowArtist = document.getElementById('catRowArtist');
    const rowOrganizer = document.getElementById('catRowOrganizer');
    const rowMag = document.getElementById('catRowMag');
    const rowLiterary = document.getElementById('catRowLiterary');
    const rowBooks = document.getElementById('catRowBooks');

    const artMedium = document.getElementById('writerArtMedium');
    const orgName = document.getElementById('writerOrgName');
    const publishedBooks = document.getElementById('publishedBooksInput');

    if (rowArtist) rowArtist.style.display = 'none';
    if (rowOrganizer) rowOrganizer.style.display = 'none';
    if (rowMag) rowMag.style.display = 'none';
    if (rowLiterary) rowLiterary.style.display = 'none';
    if (rowBooks) rowBooks.style.display = 'none';

    if (artMedium) artMedium.required = false;
    if (orgName) orgName.required = false;
    if (publishedBooks) publishedBooks.required = false;

    const isArtist = selectedVals.includes('শিল্পী / সংস্কৃতিকর্মী');
    const isOrganizer = selectedVals.includes('বই প্রতিনিধি ও সংগঠক');
    const isMagEditor = selectedVals.includes('লিটিলম্যাগাজিন সম্পাদক');
    const isPublisher = selectedVals.includes('প্রকাশক');
    const isWriter = selectedVals.some(v => ['কবি ও কথাসাহিত্যিক', 'প্রাবন্ধিক ও গবেষক', 'শিশুসাহিত্যিক ও ছড়াকার', 'অন্যান্য / প্রতিনিধি'].includes(v));

    if (isArtist) {
        if (rowArtist) rowArtist.style.display = 'table-row';
        if (artMedium) artMedium.required = true;
    }

    if (isOrganizer) {
        if (rowOrganizer) rowOrganizer.style.display = 'table-row';
        if (orgName) orgName.required = true;
    }

    if (isMagEditor || isPublisher) {
        if (rowMag) {
            rowMag.style.display = 'table-row';
            const lMag = document.getElementById('labelMagName');
            if (lMag) lMag.textContent = isPublisher ? 'ছোটকাগজ / প্রকাশনা' : 'ছোটকাগজ';
            const lIssue = document.getElementById('labelMagIssue');
            if (lIssue) lIssue.textContent = isPublisher ? 'সংখ্যা / বইসংখ্যা' : 'সংখ্যা';
        }
    }

    if (isWriter || isPublisher) {
        if (rowLiterary) {
            rowLiterary.style.display = 'table-row';
            const lGenre = document.getElementById('labelGenreOrOrg');
            if (lGenre) lGenre.textContent = 'শাখা / মাধ্যম';
            if (publishedBooks) publishedBooks.required = true;
        }
        if (rowBooks) rowBooks.style.display = 'table-row';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    // পুরনো টেস্ট ক্যাশ থাকলে তা ক্লিন করা
    try {
        localStorage.removeItem('rsu_custom_logo');
        localStorage.removeItem('rsu_logo_size');
    } catch (err) {}

    // ০. ক্যাটাগরি ও রেসিডেন্ট ইনিশিয়ালাইজেশন
    handleMultipleCategoryChange();
    const initialResType = document.querySelector('input[name="resident_type"]:checked')?.value || 'domestic';
    toggleResidentAddress(initialResType);

    // ১. ৪-স্তরের স্বয়ংক্রিয় ঠিকানা ইনিশিয়ালাইজেশন
    if (typeof initAddressChaining === 'function') {
        initAddressChaining(
            'writerDivision',
            'writerDistrict',
            'writerUpazila',
            'writerPostOffice',
            'writerVillage',
            'রংপুর',
            'রংপুর'
        );
    }

    // ২. দেশি ঠিকানা লিসেনার
    ['writerDivision', 'writerDistrict', 'writerUpazila', 'writerPostOffice', 'writerVillage'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', formatWriterAddress);
            el.addEventListener('input', formatWriterAddress);
        }
    });

    // ৩. বিদেশি ঠিকানা লিসেনার
    ['writerCountrySelect', 'writerCustomCountry', 'writerForeignCity', 'writerForeignZip', 'writerForeignStreet'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', formatWriterAddress);
            el.addEventListener('input', formatWriterAddress);
        }
    });

    formatWriterAddress();
});

// পূর্ণ ঠিকানা ফরম্যাটিং
function formatWriterAddress() {
    let full = '';
    const hiddenAddr = document.getElementById('fullWriterAddress');
    const badgeWrap = document.getElementById('fullAddressBadgeWrap');
    const badgeText = document.getElementById('fullAddressText');

    if (currentResidentType === 'international') {
        const cSelect = document.getElementById('writerCountrySelect')?.value || '';
        const customC = document.getElementById('writerCustomCountry')?.value || '';
        const country = (cSelect === 'OTHER' && customC) ? customC : (cSelect !== 'OTHER' ? cSelect : '');
        const city = document.getElementById('writerForeignCity')?.value || '';
        const zip = document.getElementById('writerForeignZip')?.value || '';
        const street = document.getElementById('writerForeignStreet')?.value || '';

        const parts = [];
        if (street) parts.push(street);
        if (city) {
            let cityPart = city;
            if (zip) cityPart += ' - ' + zip;
            parts.push(cityPart);
        }
        if (country) parts.push(country);

        full = parts.join(', ');
    } else {
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

        full = parts.join(', ');
    }

    if (hiddenAddr) hiddenAddr.value = full;

    if (badgeWrap && badgeText) {
        if (full) {
            badgeText.textContent = full;
            badgeWrap.style.display = 'block';
        } else {
            badgeWrap.style.display = 'none';
        }
    }
}

// বাংলা ও ইংরেজি মোবাইল নম্বর হ্যান্ডলার
function handlePhoneInput(input) {
    const bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
    const en = ['0','1','2','3','4','5','6','7','8','9'];
    let raw = (input.value || '').toString();
    for (let i = 0; i < bn.length; i++) {
        raw = raw.replaceAll(bn[i], en[i]);
    }
    let val = raw.replace(/[^0-9+]/g, '');
    if (val.startsWith('+880')) {
        val = '0' + val.substring(4);
    } else if (val.startsWith('880')) {
        val = '0' + val.substring(3);
    }
    input.value = val;
}

// ছবি অটো-কম্প্রেশন ও প্রিভিউ (যেকোনো ফরম্যাট ও সাইজের জন্য অপ্টিমাইজড)
function optimizeWriterPhoto(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];

    const thumb = document.getElementById('photoPreviewThumb');
    const placeholder = document.getElementById('photoUploadPlaceholder');

    const reader = new FileReader();
    reader.onload = function (e) {
        // তাত্ক্ষণিক প্রিভিউ প্রদর্শন
        if (thumb) {
            thumb.src = e.target.result;
            thumb.style.display = 'block';
        }
        if (placeholder) {
            placeholder.style.display = 'none';
        }

        const img = new Image();
        img.onload = function () {
            try {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                const size = 320;

                canvas.width = size;
                canvas.height = size;

                const minDim = Math.min(img.width, img.height);
                const startX = (img.width - minDim) / 2;
                const startY = (img.height - minDim) / 2;

                ctx.drawImage(img, startX, startY, minDim, minDim, 0, 0, size, size);

                const optimizedBase64 = canvas.toDataURL('image/jpeg', 0.88);

                const optInput = document.getElementById('optimizedPhotoData');
                if (optInput) {
                    optInput.value = optimizedBase64;
                }
                if (thumb) {
                    thumb.src = optimizedBase64;
                }
            } catch (err) {
                console.warn('Canvas optimization notice, raw file upload will be used:', err);
            }
        };
        img.onerror = function () {
            console.warn('Image parse error on client, falling back to raw upload.');
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('writerRegisterForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const checkedCategories = document.querySelectorAll('input[name="author_categories[]"]:checked');
            if (checkedCategories.length === 0) {
                e.preventDefault();
                alert('অনুগ্রহ করে অন্তত একটি ক্যাটাগরি নির্বাচন করুন।');
                return false;
            }

            // Mandatory Photo Check
            const optPhoto = document.getElementById('optimizedPhotoData')?.value;
            const rawPhoto = document.getElementById('rawPhotoInput')?.files?.length;
            const previewImg = document.getElementById('photoPreviewThumb');
            const hasExistingPhoto = previewImg && previewImg.src && !previewImg.src.includes('data:image/svg') && previewImg.style.display !== 'none' && previewImg.getAttribute('src') !== '';

            if (!optPhoto && !rawPhoto && !hasExistingPhoto) {
                e.preventDefault();
                alert('অনুগ্রহ করে আপনার পাসপোর্ট সাইজের ছবি আপলোড করুন। ছবি আপলোড বাধ্যতামূলক।');
                const photoBox = document.querySelector('.lh-photo-box');
                if (photoBox) {
                    photoBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    photoBox.style.border = '2px dashed #dc2626';
                    photoBox.style.background = '#fef2f2';
                }
                return false;
            }

            formatWriterAddress();
            const btn = document.getElementById('submitWriterBtn');
            if (btn) {
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Submitting...';
                btn.style.opacity = '0.8';
                btn.style.pointerEvents = 'none';
            }
        });
    }
});
</script>
@endpush
