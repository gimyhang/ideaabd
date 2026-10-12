@extends('layouts.app')

@php
    $cardDesign        = $campaign->card_design ?? [];
    $themeColor        = $campaign->theme_color ?: '#0f172a';
    $customFields      = $campaign->custom_fields ?? [];
    $eventDesc         = $campaign->short_description ?: strip_tags($campaign->description ?: 'অংশগ্রহণ ও ডেলিগেট কার্ড নিবন্ধন ফরম — আইডিয়া প্রকাশন।');
    $eventCover        = $campaign->banner_image ? asset('storage/' . $campaign->banner_image) : asset('images/og-banner.jpg');
    
    $fSettings         = $campaign->form_settings['fields'] ?? [];
    $emailEnabled      = $fSettings['email']['enabled'] ?? true;
    $instEnabled       = $fSettings['institution']['enabled'] ?? true;
    $locEnabled        = $fSettings['location']['enabled'] ?? true;
    $addrEnabled       = $fSettings['address']['enabled'] ?? true;
@endphp

@section('title', $campaign->title . ' — নিবন্ধন ফরম')
@section('meta_description', Str::limit(strip_tags($eventDesc), 180))
@section('meta_keywords', e($campaign->title) . ', নিবন্ধন, ডেলিগেট কার্ড, আইডিয়া প্রকাশন')
@section('og_type', 'website')
@section('og_title', $campaign->title . ' — নিবন্ধন ফরম | আইডিয়া প্রকাশন')
@section('og_description', Str::limit(strip_tags($eventDesc), 180))
@section('og_image', $eventCover)
@section('og_url', url('/' . $campaign->slug))

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700;800&family=Noto+Serif+Bengali:wght@600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --form-border: {{ $campaign->form_settings['form_border_color'] ?? '#0f172a' }};
            --form-grid: {{ $campaign->form_settings['form_border_color'] ?? '#334155' }};
            --form-bg-label: {{ $campaign->form_settings['form_bg_label'] ?? '#f8fafc' }};
            --form-accent: {{ $campaign->form_settings['theme_color'] ?? ($campaign->theme_color ?: '#991b1b') }};
        }

        body {
            background-color: #f1f5f9;
            font-family: '{{ $campaign->form_settings['font_family'] ?? 'Hind Siliguri' }}', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
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
            border: 2px solid var(--form-border, #0f172a);
            border-radius: 4px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            padding: 24px 28px;
            color: #0f172a;
        }

        /* Top Full-Width Letterhead Pad */
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
            border: 1.5px solid var(--form-grid, #334155);
            margin-bottom: 12px;
        }

        .clean-grid-table td, .clean-grid-table th {
            border: 1px solid var(--form-grid, #334155);
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

        /* Category Radio / Single-Select Grid & Pills */
        .category-radio-grid {
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
            padding: 4px 10px;
            font-size: 12px;
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

        .cat-pill input[type="radio"] {
            margin-right: 6px;
            accent-color: #991b1b;
            cursor: pointer;
            width: 15px;
            height: 15px;
            vertical-align: middle;
        }

        .cat-pill.active,
        .cat-pill:has(input:checked) {
            background: #0f172a !important;
            color: #ffffff !important;
            border-color: #0f172a !important;
        }

        .cat-pill.active span,
        .cat-pill:has(input:checked) span {
            color: #ffffff !important;
            font-weight: 800;
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

        /* Responsive Mobile Layout */
        @media (max-width: 768px) {
            .letterhead-container {
                padding: 0 8px;
                margin: 10px auto 40px auto;
            }

            .official-letterhead-form {
                padding: 16px 12px;
                border-width: 1.5px;
                border-radius: 8px;
            }

            .lh-pad-inner {
                flex-direction: column;
                text-align: center;
                gap: 12px;
            }

            .lh-pad-title {
                font-size: 19px;
            }

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

            .clean-grid-table tr:not([style*="display: none"]) {
                display: flex !important;
                flex-direction: column !important;
                border-bottom: 1px solid #e2e8f0;
                padding: 8px 10px;
                background: #ffffff;
            }

            .clean-grid-table td.grid-section-header {
                display: block;
                width: 100%;
                border: none;
                padding: 6px 10px;
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

            .c-input, .c-select {
                min-height: 42px;
                padding: 8px 12px;
                font-size: 14.5px;
                border-radius: 6px;
            }

            .photo-cell-desktop {
                order: -1;
                width: 100% !important;
                margin: 4px auto 10px auto;
                background: transparent !important;
                display: flex;
            }

            .btn-submit-letterhead {
                width: 100%;
                min-height: 48px;
                justify-content: center;
            }
        }

        /* Print Design */
        @media print {
            body { background: #fff !important; margin: 0 !important; padding: 0 !important; }
            @page { size: A4 portrait; margin: 8mm 10mm; }
            .letterhead-container { margin: 0 !important; padding: 0 !important; max-width: 100% !important; }
            .official-letterhead-form { border: 1.5px solid #000 !important; box-shadow: none !important; padding: 12px 16px !important; }
            .clean-grid-table { display: table !important; border: 1.5px solid #000 !important; margin-bottom: 8px !important; }
            .clean-grid-table tbody { display: table-row-group !important; }
            .clean-grid-table tr { display: table-row !important; border: none !important; }
            .clean-grid-table td, .clean-grid-table th { display: table-cell !important; border: 1px solid #000 !important; padding: 4px 6px !important; font-size: 11.5px !important; }
            .clean-grid-table .c-label { width: 18% !important; background-color: #f8fafc !important; }
            .clean-grid-table .c-val { background: #ffffff !important; }
            .photo-cell-desktop { display: table-cell !important; width: 22% !important; margin: 0 !important; }
            .c-input, .c-select { border: none !important; background: transparent !important; padding: 0 !important; font-weight: 700 !important; font-size: 12px !important; }
            .form-top-actions, .btn-submit-letterhead, .no-print { display: none !important; }
        }
    </style>
@endpush

@section('content')
@php
    $adminLogo = $campaign->form_settings['logo_image'] ?? null;
    if ($adminLogo && !str_starts_with($adminLogo, 'http')) {
        $cleanAdminLogo = ltrim(preg_replace('#^(public/|storage/)+#', '', $adminLogo), '/');
        $adminLogo = '/storage/' . $cleanAdminLogo;
    }
    $adminLogoSize = intval($campaign->form_settings['logo_size'] ?? 75);
    $adminLogoWidth = intval($campaign->form_settings['logo_width'] ?? $adminLogoSize);
    $adminLogoHeight = intval($campaign->form_settings['logo_height'] ?? $adminLogoSize);
    $adminLogoFit = $campaign->form_settings['logo_fit'] ?? 'contain';
    $logoShape = $campaign->form_settings['logo_shape'] ?? 'default';
    $logoBorderWidth = intval($campaign->form_settings['logo_border_width'] ?? 0);
    $logoBorderColor = $campaign->form_settings['logo_border_color'] ?? '#0f172a';
    $emblemIcon = $campaign->form_settings['emblem_icon'] ?? 'fa-file-signature';
    $logoOffsetX = intval($campaign->form_settings['logo_offset_x'] ?? 0);
    $logoOffsetY = intval($campaign->form_settings['logo_offset_y'] ?? 0);
    $logoBg = $campaign->form_settings['logo_bg'] ?? 'transparent';
    $logoBgColor = ($logoBg === 'white' || $logoBg === '#ffffff') ? '#ffffff' : 'transparent';
    $logoBorderRadius = $logoShape === 'circle' ? '50%' : ($logoShape === 'rounded' ? '12px' : '0px');
    $logoBorderStyle = $logoBorderWidth > 0 ? "border: {$logoBorderWidth}px solid {$logoBorderColor};" : "";
    $photoReqMode = $campaign->form_settings['photo_required'] ?? 'optional';
    $enableSignature = $campaign->form_settings['enable_signature'] ?? true;
    $enableIntlAddress = $campaign->form_settings['enable_intl_address'] ?? true;
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
                <i class="fa-solid fa-file-pen me-1"></i> {{ $campaign->badge_text ?: 'নিবন্ধন' }}
            </span>
            @if($isAdmin)
                <span class="badge bg-dark text-white ms-1 px-2 py-1 small">
                    <i class="fa-solid fa-shield-halved me-1"></i> Admin Mode
                </span>
                <a href="{{ route('admin.event-campaigns.show', $campaign->id) }}" class="btn btn-sm btn-dark text-white rounded-pill px-3 py-1 ms-2 small fw-bold">
                    <i class="fa-solid fa-gauge me-1 text-warning"></i> ড্যাশবোর্ড এডিটর
                </a>
            @endif
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                <i class="fa-solid fa-print me-1"></i> Print Form
            </button>
        </div>
    </div>

    {{-- Alert Notifications --}}
    @if(!empty($existingRegistration))
        @php
            $isRegApproved = in_array($existingRegistration->status, ['confirmed', 'approved', 'selected', 'attended'], true);
        @endphp
        <div class="card border-0 rounded-3 p-3 mb-3 shadow-xs" style="background: {{ $isRegApproved ? '#f0fdf4' : '#fffbeb' }}; border: 1.5px solid {{ $isRegApproved ? '#86efac' : '#fde68a' }} !important;">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle {{ $isRegApproved ? 'bg-success' : 'bg-warning text-dark' }} text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size: 18px; flex-shrink: 0;">
                        <i class="fa-solid {{ $isRegApproved ? 'fa-circle-check' : 'fa-clock' }}"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark" style="font-size: 14px;">
                            @if($isRegApproved)
                                <span>আপনার নিবন্ধন সম্পন্ন ও <strong>অনুমোদিত</strong>!</span>
                                <span class="badge bg-success text-white ms-1">✓ অনুমোদিত</span>
                            @else
                                <span>আপনার আবেদন জমা হয়েছে — <strong>পর্যালোচনায় রয়েছে</strong></span>
                                <span class="badge bg-warning text-dark ms-1">PENDING APPROVAL</span>
                            @endif
                        </div>
                        <div class="small text-muted" style="font-size: 12px;">
                            রেজিস্ট্রেশন নং: <strong class="font-monospace text-primary">#{{ $existingRegistration->registration_number }}</strong>
                            | মোবাইল: <strong class="font-monospace text-dark">{{ $existingRegistration->phone }}</strong>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    @if($isRegApproved)
                        <a href="{{ route('event.registration.print', $existingRegistration->registration_number) }}" target="_blank" class="btn btn-warning text-dark fw-bold rounded-pill px-3 py-1.5 shadow-2xs d-inline-flex align-items-center gap-1.5" style="font-size: 13px;">
                            <i class="fa-solid fa-id-card"></i>
                            <span>কার্ড ডাউনলোড</span>
                        </a>
                    @else
                        <span class="badge bg-light text-dark border px-3 py-2 rounded-pill small">
                            <i class="fa-solid fa-hourglass-half me-1 text-warning"></i> অনুমোদন হলে SMS পাঠানো হবে
                        </span>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @auth
        <div class="card border-0 rounded-3 p-3 mb-3 shadow-xs d-flex flex-row align-items-center justify-content-between flex-wrap gap-2" style="background: #f8fafc; border: 1px solid #e2e8f0 !important;">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; font-size: 14px;">
                    <i class="fa-solid fa-user-check"></i>
                </div>
                <div>
                    <div class="fw-bold text-dark" style="font-size: 13px;">
                        <span>আপনি <strong>{{ auth()->user()->name }}</strong> হিসেবে সাইন-ইন আছেন</span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle ms-1">✓ একাউন্ট</span>
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
                <a href="{{ route('logout') }}" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1.5" onclick="if(window.siteLogout){window.siteLogout(event);}else{event.preventDefault();document.getElementById('eventLogoutForm').submit();}">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    <span>লগআউট / অন্য একাউন্ট</span>
                </a>
                <form id="eventLogoutForm" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </div>
    @endauth

    @if(!empty($campaign->form_settings['form_notice_active']) && !empty($campaign->form_settings['form_notice_text']))
        <div class="alert alert-{{ $campaign->form_settings['form_notice_type'] ?? 'info' }} rounded-3 py-2.5 px-3 mb-3 small d-flex align-items-center gap-2 shadow-xs border">
            <i class="fa-solid fa-circle-exclamation fs-5 flex-shrink-0"></i>
            <div class="fw-semibold">{!! nl2br(e($campaign->form_settings['form_notice_text'])) !!}</div>
        </div>
    @endif

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
    <form action="{{ route('event.submit', $campaign->slug) }}" method="POST" enctype="multipart/form-data" id="eventRegForm" class="official-letterhead-form">
        @csrf

        <input type="hidden" name="optimized_photo_data" id="optimizedPhotoData">

        {{-- ১. শীর্ষ লেটার হেড প্যাড (Authentic Full-Width Letterhead Pad) --}}
        <div class="lh-pad-header">
            <div class="lh-pad-inner">
                {{-- লোগো / এমব্লেম --}}
                <div class="lh-pad-emblem-left borderless-emblem-wrap" id="logoEmblemWrap" title="{{ $isAdmin ? 'লোগো পরিবর্তন ও সাইজ নির্ধারণ করতে হোভার করুন' : '' }}" style="transform: translate({{ $logoOffsetX }}px, {{ $logoOffsetY }}px);">
                    <div class="logo-display-container" id="logoDisplayContainer" style="width: {{ $adminLogoWidth }}px; height: {{ $adminLogoHeight }}px; border-radius: {{ $logoBorderRadius }}; {{ $logoBorderStyle }} background-color: {{ $logoBgColor }}; overflow: hidden;">
                        <img id="customLogoImg" src="{{ $adminLogo ?: '' }}" alt="{{ $campaign->title }}" class="{{ $adminLogo ? '' : 'd-none' }}" style="{{ $adminLogo ? 'display: block;' : 'display: none !important;' }} width: 100%; height: 100%; object-fit: {{ $adminLogoFit }}; border-radius: {{ $logoBorderRadius }};" onerror="this.style.setProperty('display', 'none', 'important'); this.classList.add('d-none'); const iconEl = document.getElementById('defaultEmblemIcon'); if (iconEl) { iconEl.style.setProperty('display', 'flex', 'important'); iconEl.classList.remove('d-none'); iconEl.classList.add('d-flex'); }" onload="this.style.setProperty('display', 'block', 'important'); this.classList.remove('d-none'); const iconEl = document.getElementById('defaultEmblemIcon'); if (iconEl) { iconEl.style.setProperty('display', 'none', 'important'); iconEl.classList.add('d-none'); iconEl.classList.remove('d-flex'); }">
                        <div id="defaultEmblemIcon" class="default-emblem-icon {{ $adminLogo ? 'd-none' : 'd-flex' }}" style="{{ $adminLogo ? 'display: none !important;' : 'display: flex !important;' }} font-size: {{ round(min($adminLogoWidth, $adminLogoHeight) * 0.55) }}px;">
                            <i class="fa-solid {{ $emblemIcon }}"></i>
                        </div>
                    </div>

                    @if($isAdmin)
                        <div class="logo-control-toolbar no-print">
                            <span class="badge bg-dark text-white me-1" style="font-size: 8.5px; padding: 2px 4px;">ADMIN</span>
                            <button type="button" class="btn-logo-tool" title="লোগো আপলোড" onclick="document.getElementById('customLogoFileInput').click()">
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
                @php
                    $formVenue = $campaign->form_settings['form_venue'] ?? ($campaign->location_name ? 'স্থান: ' . $campaign->location_name : null);
                    $formDate = $campaign->form_settings['form_date'] ?? ($campaign->starts_at ? 'তারিখ: ' . $campaign->starts_at->format('d M, Y') : null);
                    $formOrg = $campaign->form_settings['form_org'] ?? 'নিবন্ধন সহযোগিতায়: আইডিয়া প্রকাশন &nbsp;|&nbsp; <span class="font-monospace">www.ideaabd.com</span>';
                    $formCopyTag = $campaign->form_settings['form_copy_tag'] ?? (!empty($existingRegistration) ? 'REGISTERED' : 'DELEGATE COPY');
                    $formSubhead = $campaign->form_settings['form_subhead'] ?? null;
                @endphp
                <div class="lh-pad-center">
                    <div class="lh-pad-title" id="pubPadTitle">
                        {{ $campaign->title }}
                    </div>
                    @if(!empty($formSubhead))
                        <div class="lh-pad-subhead" id="pubPadSubhead" style="font-size: 13px; font-weight: 700; color: #475569; margin-bottom: 2px;">
                            {{ $formSubhead }}
                        </div>
                    @endif
                    @if(!empty($formVenue))
                        <div class="lh-pad-meta" id="pubPadVenue" style="font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 2px;">
                            {{ $formVenue }}
                        </div>
                    @endif
                    @if(!empty($formDate))
                        <div class="lh-pad-subhead" id="pubPadDate" style="font-size: 13.5px; font-weight: 800; color: var(--form-accent); margin-bottom: 3px;">
                            {{ $formDate }}
                        </div>
                    @endif
                    @if(!empty($formOrg))
                        <div class="lh-pad-org" id="pubPadOrg" style="font-size: 11.5px; font-weight: 700; color: #475569;">
                            {!! $formOrg !!}
                        </div>
                    @endif
                </div>

                {{-- কপি স্ট্যাম্প --}}
                <div class="lh-pad-badge-right">
                    <div class="lh-pad-copy-tag" id="pubPadCopyTag">{{ $formCopyTag }}</div>
                    <div class="mt-1 font-monospace" style="font-size: 11px; font-weight: 800; color: #0f172a;">
                        #{{ $previewRegNumber ?? ($existingRegistration?->registration_number ?? 'REG-2026-001') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- ১. পরিচিতি (Profile Details) + Photo --}}
        <table class="clean-grid-table">
            <colgroup>
                <col style="width: 18%;">
                <col style="width: 32%;">
                <col style="width: 18%;">
                <col style="width: 32%;">
            </colgroup>
            <tr>
                <td class="grid-section-header" colspan="4">
                    পরিচিতি
                </td>
            </tr>
            <tr>
                <td class="c-label">নাম <span class="text-danger">*</span></td>
                <td class="c-val" {{ $photoReqMode === 'hidden' ? 'colspan=3' : 'colspan=2' }}>
                    <input type="text" name="name" id="writerName" class="c-input text-uppercase fw-bold" placeholder="নাম" value="{{ old('name', $existingRegistration?->name ?? $user?->name) }}" required oninput="handleNameInput(this)">
                </td>
                @if($photoReqMode !== 'hidden')
                <td rowspan="4" class="text-center photo-cell-desktop" style="width: 32%; vertical-align: middle; background: #fafafa;">
                    <div class="lh-photo-box" onclick="document.getElementById('rawPhotoInput').click()" title="ছবি আপলোড করতে ক্লিক করুন">
                        @php
                            $existingPhoto = $existingRegistration?->form_data['student_photo'] ?? ($existingRegistration?->form_data['photo'] ?? null);
                            if ($existingPhoto && !str_starts_with($existingPhoto, 'http') && !str_starts_with($existingPhoto, '/')) {
                                $existingPhoto = asset('storage/' . $existingPhoto);
                            }
                        @endphp
                        <img id="photoPreviewThumb" class="lh-photo-img" src="{{ $existingPhoto ?: '' }}" style="{{ $existingPhoto ? 'display: block;' : 'display: none;' }}" alt="Photo">
                        <div id="photoUploadPlaceholder" class="lh-photo-placeholder" style="{{ $existingPhoto ? 'display: none;' : '' }}">
                            <i class="fa-solid fa-camera text-primary fs-5 mb-1"></i>
                            <div style="font-weight: 700; color: #0f172a; font-size: 12px;">ছবি আপলোড @if($photoReqMode === 'required')<span class="text-danger">*</span>@endif</div>
                            <div style="font-size: 8.5px; color: #64748b; margin-top: 2px;">পাসপোর্ট সাইজ</div>
                        </div>
                    </div>
                    <input type="file" id="rawPhotoInput" name="student_photo" accept="image/*" class="d-none" onchange="optimizeWriterPhoto(this)">
                </td>
                @endif
            </tr>

            {{-- ক্যাটাগরি (যদি কনফিগার করা থাকে) --}}
            @if(!empty($campaign->form_settings['categories']) && is_array($campaign->form_settings['categories']) && count($campaign->form_settings['categories']) > 0)
            <tr>
                <td class="c-label">ক্যাটাগরি <span class="text-danger">*</span></td>
                <td class="c-val" {{ $photoReqMode === 'hidden' ? 'colspan=3' : 'colspan=2' }}>
                    @php
                        $activeCats = $campaign->form_settings['categories'];
                        $existingCat = $existingRegistration?->form_data['author_category'] ?? ($existingRegistration?->form_data['author_categories'][0] ?? null);
                        $selectedCategory = old('author_category', old('author_categories.0', $existingCat ?: ($activeCats[0] ?? '')));
                    @endphp
                    <div class="category-radio-grid" id="categoryRadioGrid">
                        @foreach($activeCats as $catOption)
                            <label class="cat-pill {{ $selectedCategory == $catOption ? 'active' : '' }}">
                                <input type="radio" name="author_category" value="{{ $catOption }}" {{ $selectedCategory == $catOption ? 'checked' : '' }} required>
                                <span>{{ $catOption }}</span>
                            </label>
                        @endforeach
                    </div>
                </td>
            </tr>
            @endif

            <tr>
                <td class="c-label">মোবাইল <span class="text-danger">*</span></td>
                <td class="c-val" {{ $photoReqMode === 'hidden' ? 'colspan=3' : 'colspan=2' }}>
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
                        <input type="tel" name="phone" id="writerPhone" class="c-input font-monospace fw-bold" placeholder="মোবাইল" value="{{ old('phone', $existingRegistration?->phone ?? $user?->phone) }}" required oninput="handlePhoneInput(this)">
                    </div>
                </td>
            </tr>
            <tr>
                <td class="c-label">ইমেইল</td>
                <td class="c-val" {{ $photoReqMode === 'hidden' ? 'colspan=3' : 'colspan=2' }}>
                    <input type="email" name="email" id="writerEmail" class="c-input" placeholder="ইমেইল" value="{{ old('email', $existingRegistration?->email ?? $user?->email) }}">
                </td>
            </tr>

            {{-- প্রতিষ্ঠান ও পদবি (যদি সক্রিয় থাকে) --}}
            @if($instEnabled)
            <tr>
                <td class="c-label">প্রতিষ্ঠান</td>
                <td class="c-val" style="width: 32%;">
                    <input type="text" name="institution_or_org" class="c-input" placeholder="প্রতিষ্ঠান" value="{{ old('institution_or_org', $existingRegistration?->form_data['institution_or_org'] ?? '') }}">
                </td>
                <td class="c-label" style="width: 18%;">পদবি</td>
                <td class="c-val" style="width: 32%;">
                    <input type="text" name="designation_or_class" class="c-input" placeholder="পদবি" value="{{ old('designation_or_class', $existingRegistration?->designation_or_class ?? ($existingRegistration?->form_data['designation_or_class'] ?? '')) }}">
                </td>
            </tr>
            @endif

            {{-- ডাইনামিক কাস্টম ফিল্ডস --}}
            @if(!empty($campaign->custom_fields) && is_array($campaign->custom_fields))
                @foreach($campaign->custom_fields as $field)
                    @php
                        $fName = $field['name'] ?? ('field_' . $loop->iteration);
                        $fLabel = $field['label'] ?? ucfirst(str_replace('_', ' ', $fName));
                        $fType = $field['type'] ?? 'text';
                        $fRequired = !empty($field['required']);
                        $fOptions = array_map('trim', explode(',', $field['options'] ?? ''));
                    @endphp
                    <tr>
                        <td class="c-label">{{ $fLabel }} @if($fRequired)<span class="text-danger">*</span>@endif</td>
                        <td colspan="3" class="c-val">
                            @if($fType === 'select')
                                <select name="{{ $fName }}" class="c-select" {{ $fRequired ? 'required' : '' }}>
                                    <option value="">-- {{ $fLabel }} --</option>
                                    @foreach($fOptions as $opt)
                                        @if($opt !== '')
                                            <option value="{{ $opt }}" {{ old($fName) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            @elseif($fType === 'textarea')
                                <textarea name="{{ $fName }}" rows="2" class="c-input" placeholder="{{ $fLabel }}" {{ $fRequired ? 'required' : '' }}>{{ old($fName) }}</textarea>
                            @else
                                <input type="{{ $fType }}" name="{{ $fName }}" class="c-input" placeholder="{{ $fLabel }}" value="{{ old($fName) }}" {{ $fRequired ? 'required' : '' }}>
                            @endif
                        </td>
                    </tr>
                @endforeach
            @endif
        </table>

        {{-- ২. ঠিকানা (Address Details) --}}
        <div class="d-flex align-items-center justify-content-between my-2">
            <span class="fw-bold" style="font-size: 13px;"><i class="fa-solid fa-location-dot me-1 text-danger"></i> ঠিকানা</span>
            @if($enableIntlAddress)
            <div class="res-type-pill-group">
                <input type="radio" name="resident_type" id="resTypeBd" value="domestic" checked onchange="toggleResidentAddress('domestic')">
                <label for="resTypeBd"><i class="fa-solid fa-flag me-1"></i> বাংলাদেশ</label>

                <input type="radio" name="resident_type" id="resTypeIntl" value="international" onchange="toggleResidentAddress('international')">
                <label for="resTypeIntl"><i class="fa-solid fa-earth-americas me-1"></i> প্রবাসী</label>
            </div>
            @endif
        </div>

        <input type="hidden" name="address" id="fullWriterAddress" value="{{ old('address') }}">

        {{-- বাংলাদেশ ঠিকানা গ্রিড --}}
        <table class="clean-grid-table" id="bdAddressTable">
            <tr>
                <td class="grid-section-header" colspan="4">ঠিকানা</td>
            </tr>
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
                <td class="c-label">পোস্টকোড</td>
                <td class="c-val">
                    <select name="perm_post_office" id="writerPostOffice" class="c-select"></select>
                </td>
            </tr>
            <tr>
                <td class="c-label">ঠিকানা <span class="text-danger">*</span></td>
                <td colspan="3" class="c-val">
                    <input type="text" name="perm_village" id="writerVillage" class="c-input text-uppercase" placeholder="ঠিকানা" required oninput="formatWriterAddress()">
                </td>
            </tr>
        </table>

        {{-- প্রবাসী / বিদেশি ঠিকানা গ্রিড --}}
        <table class="clean-grid-table" id="intlAddressTable" style="display: none;">
            <tr>
                <td class="grid-section-header" colspan="4">ঠিকানা</td>
            </tr>
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
                        <option value="OTHER">-- অন্যান্য দেশ --</option>
                    </select>
                </td>
                <td class="c-label">শহর <span class="text-danger">*</span></td>
                <td class="c-val" style="width: 32%;">
                    <input type="text" name="state_or_city" id="writerForeignCity" class="c-input text-uppercase" placeholder="শহর" oninput="formatWriterAddress()">
                </td>
            </tr>
            <tr id="customCountryRow" style="display: none;">
                <td class="c-label">দেশ <span class="text-danger">*</span></td>
                <td colspan="3" class="c-val">
                    <input type="text" name="custom_country" id="writerCustomCountry" class="c-input" placeholder="দেশ" oninput="formatWriterAddress()">
                </td>
            </tr>
            <tr>
                <td class="c-label">পোস্টকোড</td>
                <td class="c-val">
                    <input type="text" name="zip_code" id="writerForeignZip" class="c-input font-monospace" placeholder="পোস্টকোড" oninput="formatWriterAddress()">
                </td>
                <td class="c-label">ঠিকানা <span class="text-danger">*</span></td>
                <td class="c-val">
                    <input type="text" name="foreign_address" id="writerForeignStreet" class="c-input" placeholder="ঠিকানা" oninput="formatWriterAddress()">
                </td>
            </tr>
        </table>

        {{-- ঠিকানা প্রিভিউ বার --}}
        <div id="fullAddressBadgeWrap" style="display: none;">
            <i class="fa-solid fa-location-arrow text-danger me-1"></i> <strong>ঠিকানা:</strong> <span id="fullAddressText"></span>
        </div>

        {{-- ৩. পেমেন্ট (যদি প্রযোজ্য হয়) --}}
        @if($campaign->has_fee_or_donation)
            <table class="clean-grid-table mt-2">
                <tr>
                    <td class="grid-section-header" colspan="4">পেমেন্ট</td>
                </tr>
                <tr>
                    <td class="c-label">ফি</td>
                    <td class="c-val font-monospace fw-bold text-danger">
                        ৳{{ number_format($campaign->fee_amount ?: 0) }}
                        <input type="hidden" name="amount_paid" value="{{ $campaign->fee_amount ?: 0 }}">
                    </td>
                    <td class="c-label">ট্রানজেকশন <span class="text-danger">*</span></td>
                    <td class="c-val">
                        <input type="text" name="transaction_id" class="c-input font-monospace" placeholder="ট্রানজেকশন" value="{{ old('transaction_id') }}" required>
                    </td>
                </tr>
            </table>
        @endif

        @if($enableSignature)
        {{-- স্বাক্ষর ব্লক --}}
        <div style="display: flex; justify-content: flex-end; margin-top: 35px; margin-bottom: 25px; padding-right: 15px;">
            <div style="text-align: center; min-width: 200px;">
                <div id="authorSigName" class="text-uppercase fw-bold font-monospace" style="font-size: 13px; color: #0f172a; min-height: 20px; letter-spacing: 0.5px; padding-bottom: 4px;">
                    {{ old('name', $user?->name) }}
                </div>
                <div style="border-top: 1.5px solid #0f172a; padding-top: 4px; font-size: 12px; font-weight: 700; color: #0f172a;">
                    স্বাক্ষর
                </div>
            </div>
        </div>
        @endif

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
    var bdTable = document.getElementById('bdAddressTable');
    var intlTable = document.getElementById('intlAddressTable');
    var divSelect = document.getElementById('writerDivision');
    var distSelect = document.getElementById('writerDistrict');
    var upaSelect = document.getElementById('writerUpazila');
    var vilInput = document.getElementById('writerVillage');
    var countrySelect = document.getElementById('writerCountrySelect');
    var cityInput = document.getElementById('writerForeignCity');
    var streetInput = document.getElementById('writerForeignStreet');
    var customCountryInput = document.getElementById('writerCustomCountry');
    var zipInput = document.getElementById('writerForeignZip');

    if (type === 'international') {
        if (bdTable) {
            bdTable.style.display = 'none';
            var bdInputs = bdTable.querySelectorAll('input, select, textarea');
            for (var i = 0; i < bdInputs.length; i++) bdInputs[i].disabled = true;
        }
        if (intlTable) {
            intlTable.style.display = '';
            var intlInputs = intlTable.querySelectorAll('input, select, textarea');
            for (var j = 0; j < intlInputs.length; j++) intlInputs[j].disabled = false;
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
            bdTable.style.display = '';
            var bdInputs2 = bdTable.querySelectorAll('input, select, textarea');
            for (var k = 0; k < bdInputs2.length; k++) bdInputs2[k].disabled = false;
        }
        if (intlTable) {
            intlTable.style.display = 'none';
            var intlInputs2 = intlTable.querySelectorAll('input, select, textarea');
            for (var l = 0; l < intlInputs2.length; l++) intlInputs2[l].disabled = true;
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
    var row = document.getElementById('customCountryRow');
    var customInput = document.getElementById('writerCustomCountry');
    if (val === 'OTHER') {
        if (row) row.style.display = '';
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
    if (!input) return;
    var val = input.value.trim();
    var sigName = document.getElementById('authorSigName');
    if (sigName) {
        sigName.textContent = val ? val.toUpperCase() : '';
    }
}

document.addEventListener('DOMContentLoaded', function () {
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
    var domesticFields = ['writerDivision', 'writerDistrict', 'writerUpazila', 'writerPostOffice', 'writerVillage'];
    for (var i = 0; i < domesticFields.length; i++) {
        var el = document.getElementById(domesticFields[i]);
        if (el) {
            el.addEventListener('change', formatWriterAddress);
            el.addEventListener('input', formatWriterAddress);
        }
    }

    // ৩. বিদেশি ঠিকানা লিসেনার
    var foreignFields = ['writerCountrySelect', 'writerCustomCountry', 'writerForeignCity', 'writerForeignZip', 'writerForeignStreet'];
    for (var j = 0; j < foreignFields.length; j++) {
        var fEl = document.getElementById(foreignFields[j]);
        if (fEl) {
            fEl.addEventListener('change', formatWriterAddress);
            fEl.addEventListener('input', formatWriterAddress);
        }
    }

    formatWriterAddress();
});

function formatWriterAddress() {
    var full = '';
    var hiddenAddr = document.getElementById('fullWriterAddress');
    var badgeWrap = document.getElementById('fullAddressBadgeWrap');
    var badgeText = document.getElementById('fullAddressText');

    if (currentResidentType === 'international') {
        var cSelectEl = document.getElementById('writerCountrySelect');
        var cSelect = cSelectEl ? cSelectEl.value : '';
        var customCEl = document.getElementById('writerCustomCountry');
        var customC = customCEl ? customCEl.value : '';
        var country = (cSelect === 'OTHER' && customC) ? customC : (cSelect !== 'OTHER' ? cSelect : '');
        var cityEl = document.getElementById('writerForeignCity');
        var city = cityEl ? cityEl.value : '';
        var zipEl = document.getElementById('writerForeignZip');
        var zip = zipEl ? zipEl.value : '';
        var streetEl = document.getElementById('writerForeignStreet');
        var street = streetEl ? streetEl.value : '';

        var parts = [];
        if (street) parts.push(street);
        if (city) {
            var cityPart = city;
            if (zip) cityPart += ' - ' + zip;
            parts.push(cityPart);
        }
        if (country) parts.push(country);

        full = parts.join(', ');
    } else {
        var divEl = document.getElementById('writerDivision');
        var div = divEl ? divEl.value : '';
        var distEl = document.getElementById('writerDistrict');
        var dist = distEl ? distEl.value : '';
        var upzEl = document.getElementById('writerUpazila');
        var upz = upzEl ? upzEl.value : '';
        var poEl = document.getElementById('writerPostOffice');
        var po = poEl ? poEl.value : '';
        var vilEl = document.getElementById('writerVillage');
        var vil = vilEl ? vilEl.value : '';

        var parts2 = [];
        if (vil) parts2.push(vil);
        if (po) parts2.push('পোস্টকোড: ' + po);
        if (upz) parts2.push('উপজেলা: ' + upz);
        if (dist) parts2.push('জেলা: ' + dist);
        if (div) parts2.push('বিভাগ: ' + div);

        full = parts2.join(', ');
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

function handlePhoneInput(input) {
    if (!input) return;
    var bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
    var en = ['0','1','2','3','4','5','6','7','8','9'];
    var raw = (input.value || '').toString();
    for (var i = 0; i < bn.length; i++) {
        raw = raw.split(bn[i]).join(en[i]);
    }
    var val = raw.replace(/[^0-9]/g, '');
    if (val.indexOf('880') === 0) {
        val = val.substring(3);
    } else if (val.indexOf('88') === 0 && val.length === 12 && val.charAt(2) === '1') {
        val = val.substring(2);
    }
    if (val.length === 10 && val.indexOf('1') === 0) {
        val = '0' + val;
    }
    if (val.length > 11) {
        val = val.substring(0, 11);
    }
    input.value = val;
}

function optimizeWriterPhoto(input) {
    if (!input.files || !input.files[0]) return;
    var file = input.files[0];

    var reader = new FileReader();
    reader.onload = function(e) {
        var img = new Image();
        img.onload = function() {
            var canvas = document.createElement('canvas');
            var maxDimension = 600;
            var w = img.width;
            var h = img.height;

            if (w > h) {
                if (w > maxDimension) {
                    h = Math.round((h * maxDimension) / w);
                    w = maxDimension;
                }
            } else {
                if (h > maxDimension) {
                    w = Math.round((w * maxDimension) / h);
                    h = maxDimension;
                }
            }

            canvas.width = w;
            canvas.height = h;
            var ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, w, h);

            var optimizedBase64 = canvas.toDataURL('image/jpeg', 0.85);

            var optInput = document.getElementById('optimizedPhotoData');
            if (optInput) optInput.value = optimizedBase64;

            var thumb = document.getElementById('photoPreviewThumb');
            var placeholder = document.getElementById('photoUploadPlaceholder');
            if (thumb) {
                thumb.src = optimizedBase64;
                thumb.style.display = 'block';
            }
            if (placeholder) {
                placeholder.style.display = 'none';
            }
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}
</script>
@endpush
