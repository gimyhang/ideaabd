@extends('layouts.app')

@php
    $eventTitle = $campaign->title ?: 'Scholarship Application Form 2026';
    $eventDesc  = $campaign->short_description ?: 'Idea Prokashon and Books of Idea scholarship application form.';
    $eventCover = $campaign->banner_image ? asset('storage/' . $campaign->banner_image) : asset('images/og-banner.jpg');
    $wizardFields = [
        0 => ['name', 'gender', 'father_name', 'mother_name', 'religion', 'nationality', 'birth_date', 'phone', 'marital_status', 'annual_income', 'father_income', 'mother_income', 'guardian_income', 'guardian_name', 'guardian_phone'],
        1 => ['group', 'admission_roll', 'merit_position', 'college_code', 'college_name', 'assigned_subject', 'scholarship_degree', 'admission_fee', 'monthly_fee', 'hostel_cost', 'ssc_roll', 'ssc_board', 'ssc_institute', 'ssc_year', 'ssc_gpa', 'hsc_roll', 'hsc_board', 'hsc_institute', 'hsc_year', 'hsc_gpa', 'grad_roll', 'grad_board', 'grad_institute', 'grad_year', 'grad_cgpa', 'other_roll', 'other_board', 'other_institute', 'other_year', 'other_cgpa'],
        2 => ['perm_division', 'permanent_district', 'perm_upazila', 'perm_post_office', 'perm_village', 'pres_division', 'present_district', 'pres_upazila', 'pres_post_office', 'pres_village'],
        3 => ['scholarship_reason', 'other_scholarship', 'other_scholarship_details'],
    ];
    $wizardFields[0] = array_merge($wizardFields[0], ['email', 'national_id', 'father_occupation', 'mother_occupation', 'father_status', 'mother_status', 'father_phone', 'mother_phone', 'guardian_phone', 'guardian_relation', 'guardian_name', 'guardian_income', 'family_count', 'earning_count', 'orphan_status', 'disability', 'disability_type', 'disability_details']);
    $wizardFields[1] = array_merge($wizardFields[1], ['current_year', 'ssc_roll', 'ssc_board', 'ssc_institute', 'ssc_year', 'ssc_gpa', 'hsc_roll', 'hsc_board', 'hsc_institute', 'hsc_year', 'hsc_gpa', 'grad_roll', 'grad_board', 'grad_institute', 'grad_year', 'grad_cgpa', 'other_roll', 'other_board', 'other_institute', 'other_year', 'other_cgpa']);
    $wizardFields[3] = array_merge($wizardFields[3], ['prev_joyee', 'prev_scholarships.0.year', 'prev_scholarships.0.amount', 'books_read_count', 'reading_habit', 'books_read_names', 'pay_method', 'pay_account_owner', 'pay_account_name', 'pay_account_no', 'pay_bank_name', 'pay_bank_branch', 'payment_method', 'transaction_id', 'payment_phone']);

    // Form settings from the campaign's "Form studio" / edit page
    $fs = is_array($campaign->form_settings ?? null) ? $campaign->form_settings : [];
    $schTheme = preg_match('/^#[0-9a-fA-F]{6}$/', (string) ($fs['theme_color'] ?? $campaign->theme_color ?? '')) ? ($fs['theme_color'] ?? $campaign->theme_color) : 'var(--sch-theme, #0f3a68)';
    // admin labels often carry a Bengali part: keep the English one in brackets, else the default
    $stdLabel = function (string $key, string $default) use ($fs) {
        $label = trim((string) ($fs['fields'][$key]['label'] ?? ''));
        if ($label === '') { return $default; }
        if (preg_match('/\(([^)]*[A-Za-z][^)]*)\)/', $label, $m)) { return trim($m[1]); }
        return preg_match('/\p{Bengali}/u', $label) ? $default : $label;
    };
    $stdEnabled = fn (string $key) => ($fs['fields'][$key]['enabled'] ?? true) !== false;
    $feeAmount = (float) ($campaign->fee_amount ?? 0);
    $hasFee = !empty($campaign->has_fee_or_donation) && $feeAmount > 0;
    $logoPath = $fs['logo_image'] ?? null;
    $logoUrl = $logoPath ? ((str_starts_with($logoPath, 'http') || str_starts_with($logoPath, '/')) ? $logoPath : asset('storage/' . $logoPath)) : null;
    $logoSize = max(30, min(200, (int) ($fs['logo_size'] ?? 56)));
    $knownWizardFields = array_merge(...array_values($wizardFields));
    $initialWizardStep = 4;
    foreach ($wizardFields as $step => $fields) {
        if (array_intersect($errors->keys(), $fields)) {
            $initialWizardStep = $step;
            break;
        }
    }
    if (!$errors->any() || count(array_intersect($errors->keys(), $knownWizardFields)) === count($errors->keys())) {
        $initialWizardStep = $errors->any() ? $initialWizardStep : 0;
    }
@endphp

@section('title', $eventTitle . ' — Idea Prokashon')
@section('meta_description', Str::limit(strip_tags($eventDesc), 180))
@section('meta_keywords', 'scholarship application, student scholarship, Idea Prokashon, scholarship registration bangladesh')
@section('og_type', 'website')
@section('og_title', $eventTitle . ' | Idea Prokashon')
@section('og_description', Str::limit(strip_tags($eventDesc), 180))
@section('og_image', $eventCover)
@section('og_url', url('/scholarship'))

@section('schema_json')
@php
    $scholarshipSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Event',
        'name' => $eventTitle,
        'description' => Str::limit(strip_tags($eventDesc), 300),
        'image' => $eventCover,
        'url' => url('/scholarship'),
        'startDate' => optional($campaign->starts_at)->toIso8601String() ?: date('c'),
        'endDate' => optional($campaign->ends_at)->toIso8601String() ?: date('c', strtotime('+30 days')),
        'eventStatus' => 'https://schema.org/EventScheduled',
        'eventAttendanceMode' => 'https://schema.org/OnlineEventAttendanceMode',
        'location' => [
            '@type' => 'VirtualLocation',
            'url' => url('/scholarship'),
        ],
        'organizer' => [
            '@type' => 'Organization',
            'name' => 'আইডিয়া প্রকাশন (Idea Publication)',
            'url' => 'https://www.ideaabd.com',
        ],
        'offers' => [
            '@type' => 'Offer',
            'url' => url('/scholarship'),
            'price' => '0',
            'priceCurrency' => 'BDT',
            'availability' => 'https://schema.org/InStock',
        ],
    ];
@endphp
<script type="application/ld+json">
{!! json_encode($scholarshipSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_TAG | JSON_HEX_AMP) !!}
</script>
@endsection

@section('content')
<link rel="stylesheet" href="{{ asset('css/event-register.css') }}?v={{ @filemtime(public_path('css/event-register.css')) ?: 1 }}">
<script src="{{ asset('js/event-register.js') }}?v={{ @filemtime(public_path('js/event-register.js')) ?: 1 }}" defer></script>
<style>
    .scholarship-form-wrap {
        max-width: 900px;
        margin: 0 auto;
    }
    .form-table-card {
        background: #ffffff;
        border: 1.5px solid var(--sch-theme, #0f3a68);
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        overflow: hidden;
    }
    .form-header-bar {
        background: var(--sch-theme, #0f3a68);
        color: #ffffff;
        padding: 12px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .a4-stage { background: #e5e7eb; padding: 14px 8px; overflow: hidden; }
    .a4-sheet {
        width: 210mm; min-height: 297mm; margin: 0 auto; background: #fff; color: #000;
        padding: 10mm 11mm; box-shadow: 0 2px 12px rgba(0,0,0,.2); font-size: 11px; line-height: 1.3;
        transform-origin: top left;
    }
    .a4-sheet table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
    .a4-sheet td, .a4-sheet th { border: 1px solid #000; padding: 3px 5px; font-size: 10.5px; vertical-align: middle; }
    .a4-sheet th { background: #f3f4f6; text-align: center; }
    .a4-sheet .pv-sec { font-weight: 700; background: #e5e7eb; border: 1px solid #000; border-bottom: 0; padding: 2px 6px; }
    .a4-sheet .pv-lbl { white-space: nowrap; width: 17%; }
    .a4-sheet .pv-head td { border: 0; padding: 0; }
    .a4-sheet .pv-photo { width: 90px; height: 108px; border: 1.5px solid #000; display: inline-block; overflow: hidden; text-align: center; color: #64748b; font-size: 9px; }
    .a4-sheet .pv-photo img { width: 100%; height: 100%; object-fit: cover; }
    .a4-sheet .pv-sign { border: 0; border-top: 1px solid #000; text-align: center; padding-top: 2px; font-size: 10px; }
    .form-section-head {
        background: #f1f5f9;
        color: #0f172a;
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 8px 16px;
        border-top: 1px solid #cbd5e1;
        border-bottom: 1px solid #cbd5e1;
    }
    .grid-table {
        width: 100%;
        border-collapse: collapse;
    }
    .grid-table td, .grid-table th {
        border: 1px solid #e2e8f0;
        padding: 7px 10px;
        vertical-align: middle;
    }
    .grid-table .label-col {
        background-color: #f8fafc;
        font-size: 12px;
        font-weight: 600;
        color: #334155;
        white-space: nowrap;
        width: 1%;           /* label hugs its text; the value column takes the rest */
        padding: 7px 8px;
    }
    .grid-table .val-col {
        padding: 5px 8px;
    }
    /* four-column rows: the left value gets the room, the right value is just wide enough for an NID / mobile number */
    .grid-table td.val-col:nth-child(4) { width: 25%; min-width: 190px; }
    .grid-input {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        padding: 6px 9px;
        font-size: 13px;
        color: #0f172a;
        background: #fff;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .grid-input:focus {
        border-color: var(--sch-theme, #0f3a68);
        outline: 0;
        box-shadow: 0 0 0 2px rgba(15, 58, 104, 0.15);
    }
    .grid-input.text-uppercase {
        text-transform: uppercase;
    }
    .photo-cell-box {
        width: 125px;
        height: 150px;
        border: 1.5px dashed #94a3b8;
        border-radius: 6px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: #f8fafc;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        margin: 0 auto;
        transition: all 0.2s;
    }
    .photo-cell-box:hover {
        border-color: var(--sch-theme, #0f3a68);
        background: #f0fdf4;
    }
    .photo-cell-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: none;
    }
    .opt-badge {
        font-size: 10px;
        font-weight: bold;
        background: #15803d;
        color: #fff;
        padding: 2px 6px;
        border-radius: 4px;
        margin-top: 4px;
        display: none;
    }
    .word-badge {
        font-size: 11px;
        font-weight: bold;
        padding: 3px 8px;
        border-radius: 4px;
    }
    .btn-submit-app {
        background: var(--sch-theme, #0f3a68);
        color: #ffffff;
        font-size: 15px;
        font-weight: 700;
        padding: 10px 32px;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-submit-app:hover {
        background: #1e40af;
        transform: translateY(-1px);
    }
    .scholarship-wizard {
        padding: 18px 20px 14px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    .scholarship-wizard-steps {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 8px;
    }
    .scholarship-wizard-step {
        border: 0;
        background: transparent;
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
        padding: 5px 2px;
        text-align: center;
    }
    .scholarship-wizard-step span {
        display: grid;
        place-items: center;
        width: 28px;
        height: 28px;
        margin: 0 auto 5px;
        border: 1px solid #cbd5e1;
        border-radius: 50%;
        background: #fff;
    }
    .scholarship-wizard-step.active,
    .scholarship-wizard-step.complete {
        color: var(--sch-theme, #0f3a68);
    }
    .scholarship-wizard-step.active span,
    .scholarship-wizard-step.complete span {
        color: #fff;
        background: var(--sch-theme, #0f3a68);
        border-color: var(--sch-theme, #0f3a68);
    }
    .scholarship-wizard-progress {
        height: 4px;
        margin-top: 8px;
        background: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
    }
    .scholarship-wizard-progress > span {
        display: block;
        width: 20%;
        height: 100%;
        background: var(--sch-theme, #0f3a68);
        transition: width .2s ease;
    }
    .scholarship-step[hidden] {
        display: none !important;
    }
    .scholarship-wizard-controls {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 16px;
        border-top: 1px solid #e2e8f0;
        background: #fff;
    }
    @media (max-width: 575.98px) {
        .scholarship-wizard { padding-inline: 10px; }
        .scholarship-wizard-step { font-size: 10px; }
    }
    /* ---------- device friendly layout ---------- */
    .grid-table [hidden] { display: none !important; }
    #prevJoyeeBox { overflow-x: auto; }
    #prevJoyeeBox .grid-table { min-width: 420px; }
    .table-responsive .grid-table { min-width: 640px; }
    .form-section-head.d-flex { flex-wrap: wrap; gap: 6px; }
    .scholarship-wizard-controls { padding-bottom: max(14px, env(safe-area-inset-bottom)); }

    /* tablets and phones: label above value, one field per line */
    @media (max-width: 767.98px) {
        .container.py-4 { padding-inline: 8px; padding-block: 10px !important; }
        .grid-table:not(.text-center), .grid-table:not(.text-center) tbody,
        .grid-table:not(.text-center) tr, .grid-table:not(.text-center) td { display: block; width: 100% !important; min-width: 0 !important; }
        .grid-table:not(.text-center) tr { border-bottom: 1px solid #e2e8f0; padding: 4px 0 6px; }
        .grid-table:not(.text-center) td { border: 0; }
        .grid-table:not(.text-center) td.label-col { background: transparent; padding: 6px 10px 2px; white-space: normal; font-size: 12px; }
        .grid-table:not(.text-center) td.val-col { padding: 2px 10px 6px; }
        .grid-table:not(.text-center) td[rowspan] { padding: 10px; border-top: 1px solid #e2e8f0; }
        /* 16px stops iOS from zooming into a focused field; taller targets for thumbs */
        .grid-input, .form-select { font-size: 16px !important; min-height: 42px; padding: 8px 10px; }
        textarea.grid-input { min-height: 84px; }
        .parent-row + tr input, input[name$="_phone"] { max-width: none !important; }
        .scholarship-wizard { padding-inline: 10px; }
        .scholarship-wizard-controls { position: sticky; bottom: 0; z-index: 5; box-shadow: 0 -4px 12px rgba(15, 23, 42, .08); }
        .scholarship-wizard-controls .btn { flex: 1; min-height: 44px; }
        .btn-submit-app { width: 100%; min-height: 48px; }
        .form-header-bar { flex-wrap: wrap; gap: 6px; }
    }
    @media (max-width: 399.98px) {
        .scholarship-wizard-step { font-size: 9px; letter-spacing: -.2px; }
        .scholarship-wizard-step span { width: 24px; height: 24px; }
    }
</style>

<div class="container py-4">
    <div class="scholarship-form-wrap" style="--sch-theme: {{ $schTheme }};">

        @auth
            <div class="card border-0 rounded-3 p-3 mb-3 shadow-xs d-flex flex-row align-items-center justify-content-between flex-wrap gap-2" style="background: #f0fdf4; border: 1px solid #86efac !important;">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; font-size: 14px;">
                                            </div>
                    <div>
                        <div class="fw-bold text-dark" style="font-size: 13px;">
                            <span>Signed in as <strong>{{ auth()->user()->name }}</strong></span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle ms-1">Student</span>
                        </div>
                        <div class="small text-muted font-monospace" style="font-size: 11.5px;">
                            {{ auth()->user()->phone ?? '—' }}
                            @if(auth()->user()->email)
                                <span class="mx-1">|</span> {{ auth()->user()->email }}
                            @endif
                        </div>
                    </div>
                </div>
                <div>
                    <a href="{{ route('logout') }}" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1.5" onclick="if(window.siteLogout){window.siteLogout(event);}else{event.preventDefault();document.getElementById('schLogoutForm').submit();}">
                                                <span>Log out / switch account</span>
                    </a>
                    <form id="schLogoutForm" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </div>
        @endauth

        @if(session('error'))
            <div class="alert alert-danger rounded-2 py-2.5 px-3 mb-3 small">
                <strong>Error:</strong> {{ session('error') }}
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

        <form action="{{ route('event.submit', $campaign->slug) }}" method="POST" enctype="multipart/form-data" id="dynamicScholarshipForm" class="form-table-card" data-initial-step="{{ $initialWizardStep }}" novalidate>
            @csrf <x-bot-trap />
            
            {{-- Hidden input for Client-Side Auto-Optimized Photo Blob (Base64) --}}
            <input type="hidden" name="optimized_photo_data" id="optimizedPhotoData">

            @if($campaign->banner_image)
                <img src="{{ asset('storage/' . $campaign->banner_image) }}" alt="" class="w-100 d-block" style="max-height: 200px; object-fit: cover;" loading="lazy">
            @endif

            {{-- HEADER BAR --}}
            <div class="form-header-bar">
                <div class="d-flex align-items-center gap-2">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="" style="height: {{ min($logoSize, 56) }}px; width: auto; max-width: 90px; object-fit: contain; background: #fff; border-radius: 6px; padding: 2px;">
                    @endif
                    <div>
                        <h5 class="mb-0 fw-bold" style="font-size: 16px;">Scholarship</h5>
                        <div style="font-size: 11px; opacity: 0.9;">Session 2027 | HSC / Honours / Masters / Others</div>
                        @if(!empty($fs['form_subhead']) && !preg_match('/\p{Bengali}/u', $fs['form_subhead']))<div style="font-size: 11px; opacity: 0.9;">{{ $fs['form_subhead'] }}</div>@endif
                    </div>
                </div>
                <div class="text-end">
                    <span class="badge bg-light text-dark fw-bold" style="font-size: 11px;">{{ $campaign->badge_text && !preg_match('/\p{Bengali}/u', $campaign->badge_text) ? $campaign->badge_text : 'ENTRY' }}</span>
                </div>
            </div>

            @if(!empty($fs['form_notice_active']) && !empty($fs['form_notice_text']))
                <div class="alert alert-{{ in_array($fs['form_notice_type'] ?? 'info', ['info', 'warning', 'danger', 'success'], true) ? $fs['form_notice_type'] : 'info' }} rounded-0 py-2 px-3 mb-0 small">{!! nl2br(e($fs['form_notice_text'])) !!}</div>
            @endif

            @if($campaign->contact_phone || $campaign->contact_email)
                <div class="px-3 py-1 small text-muted border-bottom">Need help? @if($campaign->contact_phone)<a href="tel:{{ preg_replace('/[^0-9+]/', '', $campaign->contact_phone) }}" class="fw-semibold">{{ $campaign->contact_phone }}</a>@endif @if($campaign->contact_email) · <a href="mailto:{{ $campaign->contact_email }}" class="fw-semibold">{{ $campaign->contact_email }}</a>@endif</div>
            @endif

            <nav class="scholarship-wizard" aria-label="Application steps">
                <div class="scholarship-wizard-steps">
                    @foreach(['Applicant', 'Academic', 'Address', 'Statement', 'Preview'] as $index => $stepTitle)
                        <button type="button" class="scholarship-wizard-step" data-wizard-target="{{ $index }}" aria-current="step">
                            <span>{{ $index + 1 }}</span>{{ $stepTitle }}
                        </button>
                    @endforeach
                </div>
                <div class="scholarship-wizard-progress" role="progressbar" aria-label="Application progress" aria-valuemin="1" aria-valuemax="5" aria-valuenow="1">
                    <span></span>
                </div>
            </nav>

            <section class="scholarship-step" data-wizard-step="0">
            {{-- 1. APPLICANT --}}
            <div class="form-section-head">1. Applicant</div>
            <table class="grid-table">
                <tr>
                    <td class="label-col">Name <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <input type="text" name="name" class="grid-input text-uppercase" placeholder="FULL NAME IN CAPITAL" value="{{ old('name', $user?->name) }}" required>
                    </td>
                    <td rowspan="3" colspan="2" class="text-center" style="width: 34%; vertical-align: middle; background: #fafafa;">
                        <div class="photo-cell-box" onclick="document.getElementById('rawPhotoInput').click()" title="Click to upload/optimize photo">
                            <img id="previewImg" class="photo-cell-img" alt="Student Photo">
                            <div id="uploadPrompt" class="p-2 text-center">
                                <div style="font-size: 24px;">📷</div>
                                <div style="font-size: 11px; font-weight: bold; color: var(--sch-theme, #0f3a68);">Student Photo <span class="text-danger">*</span></div>
                                <div style="font-size: 9.5px; color: #64748b;">Auto-Optimized</div>
                            </div>
                        </div>
                        <div id="optSizeBadge" class="opt-badge">✓ Auto-Optimized</div>
                        <div id="photoError" class="text-danger small fw-semibold mt-1" hidden>Photo is required</div>
                        <input type="file" id="rawPhotoInput" name="student_photo" accept="image/*" class="d-none" onchange="handlePhotoOptimize(this)">
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Gender <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <select name="gender" class="grid-input" required>
                            <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                            <option value="Male" {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Other" {{ old('gender') === 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Birth <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <input type="date" name="birth_date" id="birthDate" class="grid-input" min="1970-01-01" max="{{ now()->subYears(10)->toDateString() }}" value="{{ old('birth_date') }}" required>
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Religion <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <select name="religion" class="grid-input" required>
                            <option value="Islam" {{ old('religion') === 'Islam' ? 'selected' : '' }}>Islam</option>
                            <option value="Hinduism" {{ old('religion') === 'Hinduism' ? 'selected' : '' }}>Hinduism</option>
                            <option value="Christianity" {{ old('religion') === 'Christianity' ? 'selected' : '' }}>Christianity</option>
                            <option value="Buddhism" {{ old('religion') === 'Buddhism' ? 'selected' : '' }}>Buddhism</option>
                            <option value="Others" {{ old('religion') === 'Others' ? 'selected' : '' }}>Others</option>
                        </select>
                    </td>
                    <td class="label-col">Nationality <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <input type="text" name="nationality" class="grid-input text-uppercase" value="{{ old('nationality', 'BANGLADESHI') }}" required>
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Marital <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <select name="marital_status" class="grid-input" required>
                            <option value="Unmarried" {{ old('marital_status') === 'Unmarried' ? 'selected' : '' }}>Unmarried</option>
                            <option value="Married" {{ old('marital_status') === 'Married' ? 'selected' : '' }}>Married</option>
                        </select>
                    </td>
                    <td class="label-col">NID / Birth Reg. <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <input type="text" name="national_id" class="grid-input font-monospace" placeholder="10, 13 or 17 digits" inputmode="numeric" maxlength="20" pattern="(\d{10}|\d{13}|\d{17})" title="NID (10/13/17 digits) or birth registration number (17 digits)" value="{{ old('national_id') }}" required>
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Mobile <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <div class="d-flex align-items-stretch">
                            <select name="country_code" class="form-select p-1 bg-light fw-bold" style="width: 90px; flex: 0 0 90px; font-size: 11px; border: 1px solid #94a3b8; border-right: 0;" aria-label="Country Code">
                                @include('partials.country-code-options', ['selected' => old('country_code', '+880')])
                            </select>
                            <input type="tel" name="phone" id="studentPhone" class="grid-input font-monospace flex-grow-1" placeholder="017XXXXXXXX" inputmode="numeric" value="{{ old('phone', $user?->phone) }}" required maxlength="15" oninput="formatPhone(this)">
                        </div>
                    </td>
                    @if($stdEnabled('email'))
                    <td class="label-col">{{ $stdLabel('email', 'Email') }}</td>
                    <td class="val-col"><input type="email" name="email" class="grid-input" placeholder="Optional" value="{{ old('email', $user?->email) }}"></td>
                    @else
                    <td class="label-col"></td><td class="val-col"></td>
                    @endif
                </tr>
                <tr>
                    <td class="label-col">Disability <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <select name="disability" class="grid-input" required>
                            <option value="" {{ old('disability') ? '' : 'selected' }}>— Select —</option>
                            <option value="No" {{ old('disability') === 'No' ? 'selected' : '' }}>No</option>
                            <option value="Yes" {{ old('disability') === 'Yes' ? 'selected' : '' }}>Yes</option>
                        </select>
                    </td>
                    <td class="label-col" id="disabilityTypeLabel" hidden>Category <span class="text-danger">*</span></td>
                    <td class="val-col" id="disabilityTypeCell" hidden>
                        <select name="disability_type" class="grid-input">
                            @foreach(['' => '— Select —', 'Visual' => 'Visual impairment', 'Hearing' => 'Hearing impairment', 'Speech' => 'Speech impairment', 'Physical' => 'Physical / Mobility', 'Intellectual' => 'Intellectual', 'Autism' => 'Autism / Neurodevelopmental', 'Multiple' => 'Multiple', 'Other' => 'Other'] as $dv => $dl)
                                <option value="{{ $dv }}" {{ (string) old('disability_type') === (string) $dv ? 'selected' : '' }}>{{ $dl }}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>
                <tr id="disabilityDetailsRow" hidden>
                    <td class="label-col">Details <span class="text-danger" id="disabilityDetailsStar" hidden>*</span></td>
                    <td colspan="3" class="val-col"><input type="text" name="disability_details" class="grid-input" placeholder="Describe the condition / support needed" maxlength="255" value="{{ old('disability_details') }}"></td>
                </tr>
            </table>

            {{-- 2. PARENTS & GUARDIAN --}}
            <div class="form-section-head">2. Parents &amp; Guardian</div>
            <input type="hidden" name="orphan_status" id="orphanStatus" value="{{ old('orphan_status', 'None') }}">
            <table class="grid-table">
                @foreach(['father' => 'Father', 'mother' => 'Mother'] as $pk => $pl)
                    <tr class="parent-row" data-parent="{{ $pk }}">
                        <td class="label-col">{{ $pl }} <span class="text-danger">*</span></td>
                        <td class="val-col">
                            <input type="text" name="{{ $pk }}_name" class="grid-input text-uppercase" placeholder="{{ strtoupper($pl) }}'S FULL NAME" value="{{ old($pk.'_name') }}" required>
                        </td>
                        <td class="label-col">Status <span class="text-danger">*</span></td>
                        <td class="val-col">
                            <select name="{{ $pk }}_status" class="grid-input" required>
                                <option value="Alive" {{ old($pk.'_status') !== 'Deceased' ? 'selected' : '' }}>Alive</option>
                                <option value="Deceased" {{ old($pk.'_status') === 'Deceased' ? 'selected' : '' }}>Deceased</option>
                            </select>
                        </td>
                    </tr>
                    <tr data-parent="{{ $pk }}">
                        <td class="label-col">{{ $pl }} Occupation <span class="text-danger">*</span></td>
                        <td class="val-col"><input type="text" name="{{ $pk }}_occupation" class="grid-input" placeholder="e.g. Farmer, Teacher" maxlength="100" value="{{ old($pk.'_occupation') }}" required></td>
                        <td class="label-col">Income</td>
                        <td class="val-col"><input type="number" min="0" name="{{ $pk }}_income" class="grid-input font-monospace" placeholder="Tk / month or year" value="{{ old($pk.'_income') }}"></td>
                    </tr>
                    <tr data-parent="{{ $pk }}">
                        <td class="label-col">{{ $pl }} Mobile</td>
                        <td colspan="3" class="val-col">
                            <input type="tel" name="{{ $pk }}_phone" class="grid-input font-monospace" style="max-width: 290px;" placeholder="017XXXXXXXX" inputmode="numeric" maxlength="15" value="{{ old($pk.'_phone') }}" oninput="formatPhone(this)">
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <td class="label-col">Guardian</td>
                    <td class="val-col">
                        <input type="text" name="guardian_name" class="grid-input text-uppercase" placeholder="NAME (if not parent)" value="{{ old('guardian_name') }}">
                    </td>
                    <td class="label-col">Relation</td>
                    <td class="val-col"><input type="text" name="guardian_relation" class="grid-input" placeholder="e.g. Uncle" maxlength="50" value="{{ old('guardian_relation') }}"></td>
                </tr>
                <tr>
                    <td class="label-col">Guardian Income</td>
                    <td class="val-col"><input type="number" min="0" name="guardian_income" class="grid-input font-monospace" placeholder="Tk" value="{{ old('guardian_income') }}"></td>
                    <td class="label-col">Guardian Contact <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <input type="tel" name="guardian_phone" class="grid-input font-monospace" placeholder="017XXXXXXXX" inputmode="numeric" value="{{ old('guardian_phone') }}" required minlength="10" maxlength="15" oninput="formatPhone(this)">
                        <input type="hidden" name="annual_income" id="totalIncome" value="{{ old('annual_income') }}">
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Family Members <span class="text-danger">*</span></td>
                    <td class="val-col"><input type="number" min="1" max="40" name="family_count" class="grid-input font-monospace" placeholder="Total" value="{{ old('family_count') }}" required></td>
                    <td class="label-col">Earning Members <span class="text-danger">*</span></td>
                    <td class="val-col"><input type="number" min="0" max="40" name="earning_count" class="grid-input font-monospace" placeholder="Who earn" value="{{ old('earning_count') }}" required></td>
                </tr>
            </table>

            </section>

            <section class="scholarship-step" data-wizard-step="1">
            {{-- 2. ACADEMIC INFORMATION --}}
            <div class="form-section-head">3. Academic <span class="fw-normal" style="text-transform:none;">— for the degree you are applying for</span></div>
            <table class="grid-table">
                <tr>
                    <td class="label-col">Degree <span class="text-danger">*</span></td>
                    <td colspan="3" class="val-col">
                        <select name="scholarship_degree" class="grid-input" required>
                            @foreach(['HSC' => 'HSC', 'HONOURS' => 'Honours', 'MASTERS' => 'Masters', 'OTHERS' => 'Others'] as $dv => $dl)
                                <option value="{{ $dv }}" {{ old('scholarship_degree') === $dv ? 'selected' : '' }}>{{ $dl }}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Group <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <select name="group" class="grid-input" required>
                            <option value="HUMANITIES" {{ old('group') === 'HUMANITIES' ? 'selected' : '' }}>HUMANITIES</option>
                            <option value="SCIENCE" {{ old('group') === 'SCIENCE' ? 'selected' : '' }}>SCIENCE</option>
                            <option value="BUSINESS STUDIES" {{ old('group') === 'BUSINESS STUDIES' ? 'selected' : '' }}>BUSINESS STUDIES</option>
                            <option value="OTHERS" {{ old('group') === 'OTHERS' ? 'selected' : '' }}>OTHERS</option>
                        </select>
                    </td>
                    <td class="label-col">Roll <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <input type="text" name="admission_roll" class="grid-input" placeholder="e.g. 5288534" value="{{ old('admission_roll') }}" required>
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Merit</td>
                    <td class="val-col">
                        <input type="text" name="merit_position" class="grid-input" placeholder="e.g. 1682" value="{{ old('merit_position') }}">
                    </td>
                    <td class="label-col">Code</td>
                    <td class="val-col">
                        <input type="text" name="college_code" class="grid-input" placeholder="e.g. 3401" value="{{ old('college_code') }}">
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Institute <span class="text-danger">*</span></td>
                    <td colspan="3" class="val-col">
                        <input type="text" name="college_name" class="grid-input text-uppercase" placeholder="e.g. Dinajpur Govt. College" value="{{ old('college_name') }}" required>
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Subject <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <input type="text" name="assigned_subject" class="grid-input text-uppercase" placeholder="e.g. ENGLISH (1101)" value="{{ old('assigned_subject') }}" required>
                    </td>
                    <td class="label-col">Admission</td>
                    <td class="val-col">
                        <input type="number" min="0" name="admission_fee" class="grid-input font-monospace" placeholder="e.g. 5000" value="{{ old('admission_fee') }}">
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Monthly</td>
                    <td class="val-col">
                        <input type="number" min="0" name="monthly_fee" class="grid-input font-monospace" placeholder="e.g. 800" value="{{ old('monthly_fee') }}">
                    </td>
                    <td class="label-col">Hostel</td>
                    <td class="val-col">
                        <input type="number" min="0" name="hostel_cost" class="grid-input font-monospace" placeholder="If any" value="{{ old('hostel_cost') }}">
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Current Year <span class="text-danger">*</span></td>
                    <td colspan="3" class="val-col"><input type="text" name="current_year" class="grid-input" placeholder="e.g. 1st Year / 3rd Year / 2nd Semester" maxlength="50" value="{{ old('current_year') }}" required></td>
                </tr>
</table>

            {{-- 3. EDUCATIONAL QUALIFICATION: SSC up to Masters; rows below the chosen degree are mandatory --}}
            <div class="form-section-head">4. Educational Qualification <span class="fw-normal" style="text-transform:none;">— fill every level you have completed, up to the degree you apply for</span></div>
            <div class="table-responsive">
                <table class="grid-table text-center mb-0">
                    <thead>
                        <tr style="background: #f8fafc; font-size: 11px; font-weight: bold;">
                            <th style="width: 18%; text-align: left;">Level</th>
                            <th style="width: 14%;">Roll / Reg</th>
                            <th style="width: 16%;">Board / University</th>
                            <th style="width: 27%;">Institute</th>
                            <th style="width: 11%;">Year</th>
                            <th style="width: 14%;">GPA / CGPA</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach([
                            ['key' => 'ssc',   'label' => 'SSC',     'prefix' => 'ssc',   'gpa' => 'ssc_gpa',    'board' => 'e.g. DINAJPUR', 'inst' => 'School Name',               'year' => '2019'],
                            ['key' => 'hsc',   'label' => 'HSC',     'prefix' => 'hsc',   'gpa' => 'hsc_gpa',    'board' => 'e.g. DINAJPUR', 'inst' => 'College Name',              'year' => '2021'],
                            ['key' => 'grad',  'label' => 'Honours', 'prefix' => 'grad',  'gpa' => 'grad_cgpa',  'board' => 'e.g. NU / DU',  'inst' => 'College / University',      'year' => '2025'],
                            ['key' => 'other', 'label' => 'Masters', 'prefix' => 'other', 'gpa' => 'other_cgpa', 'board' => 'University',    'inst' => 'University / Department',  'year' => '2027'],
                        ] as $q)
                            <tr data-qual-row="{{ $q['key'] }}">
                                <td style="text-align: left; font-weight: 600; font-size: 11.5px;">{{ $q['label'] }} <span class="text-danger qual-star" hidden>*</span></td>
                                <td><input type="text" name="{{ $q['prefix'] }}_roll" class="grid-input text-center font-monospace" placeholder="Roll / Reg" value="{{ old($q['prefix'].'_roll') }}"></td>
                                <td><input type="text" name="{{ $q['prefix'] }}_board" class="grid-input text-center text-uppercase" placeholder="{{ $q['board'] }}" value="{{ old($q['prefix'].'_board') }}"></td>
                                <td><input type="text" name="{{ $q['prefix'] }}_institute" class="grid-input text-uppercase" placeholder="{{ $q['inst'] }}" value="{{ old($q['prefix'].'_institute') }}"></td>
                                <td><input type="number" name="{{ $q['prefix'] }}_year" class="grid-input text-center font-monospace" placeholder="{{ $q['year'] }}" value="{{ old($q['prefix'].'_year') }}"></td>
                                <td><input type="text" name="{{ $q['gpa'] }}" class="grid-input text-center font-monospace" placeholder="4.67" value="{{ old($q['gpa']) }}"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            </section>

            <section class="scholarship-step" data-wizard-step="2">
            {{-- 4. ADDRESS DETAILS --}}
            <div class="form-section-head d-flex justify-content-between align-items-center">
                <span>5. Address</span>
                <label class="mb-0 fw-normal small text-dark d-flex align-items-center gap-1" style="text-transform: none; cursor: pointer;">
                    <input type="checkbox" id="syncAddressCheck" onchange="syncPermanentToPresent()"> <strong>Same as Permanent Address</strong>
                </label>
            </div>
            
            {{-- Hidden Full Formatted Address Inputs for Backend Storage & Print --}}
            <input type="hidden" name="permanent_address" id="permFullAddress" value="{{ old('permanent_address') }}">
            <input type="hidden" name="present_address" id="presFullAddress" value="{{ old('present_address') }}">
            <input type="hidden" name="perm_postcode" id="permPostcode" value="{{ old('perm_postcode') }}">
            <input type="hidden" name="pres_postcode" id="presPostcode" value="{{ old('pres_postcode') }}">

            {{-- 4.1 Permanent Address --}}
            <div class="p-2 bg-light border-bottom fw-bold small text-primary">
                Permanent
            </div>
            <table class="grid-table">
                <tr>
                    <td class="label-col">Division <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <select name="perm_division" id="permDivision" data-lang="en" class="grid-input" data-old="{{ old('perm_division') }}" required></select>
                    </td>
                    <td class="label-col">District <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <select name="permanent_district" id="permDistrict" class="grid-input" data-old="{{ old('permanent_district') }}" required></select>
                    </td>
                </tr>
                <tr>
                    <td class="label-col">City</td>
                    <td class="val-col">
                        <select name="perm_city_corporation" id="permCityCorporation" class="grid-input" data-old="{{ old('perm_city_corporation') }}">
                            <option value="">-- Optional --</option>
                        </select>
                    </td>
                    <td class="label-col">Municipality</td>
                    <td class="val-col">
                        <input type="text" name="perm_municipality" id="permMunicipality" class="grid-input" value="{{ old('perm_municipality') }}" placeholder="Municipality (if applicable)" autocomplete="address-level3">
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Upazila <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <select name="perm_upazila" id="permUpazila" class="grid-input" data-old="{{ old('perm_upazila') }}" required></select>
                    </td>
                    <td class="label-col">Post <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <select name="perm_post_office" id="permPostOffice" class="grid-input" data-old="{{ old('perm_post_office') }}" required></select>
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Village <span class="text-danger">*</span></td>
                    <td colspan="3" class="val-col">
                        <input type="text" name="perm_village" id="permVillage" class="grid-input text-uppercase" value="{{ old('perm_village') }}" placeholder="Village / Ward / Road / House details" required>
                    </td>
                </tr>
            </table>

            {{-- 4.2 Present Address --}}
            <div class="p-2 bg-light border-top border-bottom fw-bold small text-primary">
                Present
            </div>
            <table class="grid-table">
                <tr>
                    <td class="label-col">Division <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <select name="pres_division" id="presDivision" data-lang="en" class="grid-input" data-old="{{ old('pres_division') }}" required></select>
                    </td>
                    <td class="label-col">District <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <select name="present_district" id="presDistrict" class="grid-input" data-old="{{ old('present_district') }}" required></select>
                    </td>
                </tr>
                <tr>
                    <td class="label-col">City</td>
                    <td class="val-col">
                        <select name="pres_city_corporation" id="presCityCorporation" class="grid-input" data-old="{{ old('pres_city_corporation') }}">
                            <option value="">-- Optional --</option>
                        </select>
                    </td>
                    <td class="label-col">Municipality</td>
                    <td class="val-col">
                        <input type="text" name="pres_municipality" id="presMunicipality" class="grid-input" value="{{ old('pres_municipality') }}" placeholder="Municipality (if applicable)" autocomplete="address-level3">
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Upazila <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <select name="pres_upazila" id="presUpazila" class="grid-input" data-old="{{ old('pres_upazila') }}" required></select>
                    </td>
                    <td class="label-col">Post <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <select name="pres_post_office" id="presPostOffice" class="grid-input" data-old="{{ old('pres_post_office') }}" required></select>
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Village <span class="text-danger">*</span></td>
                    <td colspan="3" class="val-col">
                        <input type="text" name="pres_village" id="presVillage" class="grid-input text-uppercase" value="{{ old('pres_village') }}" placeholder="Village / Ward / Road / House details" required>
                    </td>
                </tr>
            </table>

            </section>

            <section class="scholarship-step" data-wizard-step="3">
            {{-- SCHOLARSHIP HISTORY (mandatory) --}}
            <div class="form-section-head">6. Scholarships <span class="text-danger">*</span></div>
            <table class="grid-table mb-0">
                <tr>
                    <td class="label-col">Received Joyee Scholarship before? <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <select name="prev_joyee" id="prevJoyee" class="grid-input" required>
                            <option value="" {{ old('prev_joyee') ? '' : 'selected' }}>— Select —</option>
                            <option value="Yes" {{ old('prev_joyee') === 'Yes' ? 'selected' : '' }}>Yes</option>
                            <option value="No" {{ old('prev_joyee') === 'No' ? 'selected' : '' }}>No</option>
                        </select>
                    </td>
                </tr>
            </table>
            <div id="prevJoyeeBox" hidden>
                <div class="p-2 bg-light border-top border-bottom fw-bold small text-primary">If yes — how much, and in which years?</div>
                <table class="grid-table text-center mb-0">
                    <tr style="background:#f8fafc;font-size:11px;font-weight:bold;">
                        <th style="width:10%;">No</th>
                        <th>Year</th>
                        <th>Times</th>
                        <th>Amount (Tk)</th>
                    </tr>
                    @for($i = 0; $i < 3; $i++)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="val-col"><input type="text" name="prev_scholarships[{{ $i }}][year]" class="grid-input text-center font-monospace" placeholder="e.g. 2025" inputmode="numeric" value="{{ old('prev_scholarships.'.$i.'.year') }}"></td>
                            <td class="val-col"><input type="text" name="prev_scholarships[{{ $i }}][times]" class="grid-input text-center font-monospace" inputmode="numeric" value="{{ old('prev_scholarships.'.$i.'.times') }}"></td>
                            <td class="val-col"><input type="text" name="prev_scholarships[{{ $i }}][amount]" class="grid-input text-center font-monospace" inputmode="numeric" placeholder="Tk" value="{{ old('prev_scholarships.'.$i.'.amount') }}"></td>
                        </tr>
                    @endfor
                </table>
            </div>
            <table class="grid-table mb-3">
                <tr>
                    <td class="label-col">Receiving a scholarship from any other organization? <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <select name="other_scholarship" id="otherScholarship" class="grid-input" required>
                            <option value="" {{ old('other_scholarship') ? '' : 'selected' }}>— Select —</option>
                            <option value="No" {{ old('other_scholarship') === 'No' ? 'selected' : '' }}>No</option>
                            <option value="Yes" {{ old('other_scholarship') === 'Yes' ? 'selected' : '' }}>Yes</option>
                        </select>
                    </td>
                    <td class="val-col"><input type="text" name="other_scholarship_details" id="otherScholarshipDetails" class="grid-input" placeholder="Organization & amount (if yes)" value="{{ old('other_scholarship_details') }}"></td>
                </tr>
            </table>

            {{-- READING HABIT (mandatory) --}}
            <div class="form-section-head">7. Reading <span class="text-danger">*</span></div>
            <table class="grid-table mb-3">
                <tr>
                    <td class="label-col">Original (non-academic) books read <span class="text-danger">*</span></td>
                    <td class="val-col"><input type="number" min="0" max="9999" name="books_read_count" id="booksReadCount" class="grid-input font-monospace" placeholder="Number of books" value="{{ old('books_read_count') }}" required></td>
                </tr>
                <tr>
                    <td class="label-col">Reading habit <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <select name="reading_habit" id="readingHabit" class="grid-input" required>
                            <option value="" {{ old('reading_habit') ? '' : 'selected' }}>— Select —</option>
                            <option value="I have read" {{ old('reading_habit') === 'I have read' ? 'selected' : '' }}>I have read non-academic books</option>
                            <option value="I do not read" {{ old('reading_habit') === 'I do not read' ? 'selected' : '' }}>I do not read non-academic books</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Names of the books <span class="text-danger" id="booksNamesStar" hidden>*</span></td>
                    <td class="val-col"><textarea name="books_read_names" id="booksReadNames" rows="2" class="grid-input" maxlength="1000" placeholder="Book title, author (one per line)">{{ old('books_read_names') }}</textarea></td>
                </tr>
            </table>

{{-- 5. REASON FOR SCHOLARSHIP (50 WORDS MAX) --}}
            <div class="form-section-head d-flex justify-content-between align-items-center">
                <span>8. Statement (Max 50 Words) <span class="text-danger">*</span></span>
                <span id="wordCounter" class="word-badge bg-secondary text-white">0 / 50 words</span>
            </div>
            <div class="p-3 bg-white">
                <textarea name="scholarship_reason" id="reasonText" rows="3" class="grid-input" placeholder="State briefly why you need this scholarship (Max 50 words in English)..." oninput="handleWordCount(this)" required>{{ old('scholarship_reason') }}</textarea>
                <div id="wordLimitAlert" class="text-danger small mt-1 d-none font-monospace">
                    ⚠ Exceeded 50 words limit. Extra words will be ignored.
                </div>
            </div>

            {{-- DISBURSEMENT (where an awarded scholarship is paid) --}}
            <div class="form-section-head">9. Payment Account <span class="text-danger">*</span></div>
            <table class="grid-table mb-1">
                <tr>
                    <td class="label-col">Method <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <select name="pay_method" id="payMethod" class="grid-input" required>
                            @foreach(['' => '— Select —', 'bKash' => 'bKash', 'Nagad' => 'Nagad', 'Rocket' => 'Rocket', 'Bank' => 'Bank Account'] as $mv => $ml)
                                <option value="{{ $mv }}" {{ (string) old('pay_method') === (string) $mv ? 'selected' : '' }}>{{ $ml }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="label-col">Account Owner <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <select name="pay_account_owner" class="grid-input" required>
                            @foreach(['' => '— Select —', 'Self' => 'Self', 'Father' => 'Father', 'Mother' => 'Mother', 'Guardian' => 'Guardian'] as $av => $al)
                                <option value="{{ $av }}" {{ (string) old('pay_account_owner') === (string) $av ? 'selected' : '' }}>{{ $al }}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Holder Name <span class="text-danger">*</span></td>
                    <td class="val-col"><input type="text" name="pay_account_name" class="grid-input text-uppercase" placeholder="Name on the account" maxlength="120" value="{{ old('pay_account_name') }}" required></td>
                    <td class="label-col"><span id="payNoLabel">Wallet No.</span> <span class="text-danger">*</span></td>
                    <td class="val-col"><input type="text" name="pay_account_no" id="payAccountNo" class="grid-input font-monospace" inputmode="numeric" maxlength="20" placeholder="01XXXXXXXXX" value="{{ old('pay_account_no') }}" required></td>
                </tr>
                <tr id="payBankRow" hidden>
                    <td class="label-col">Bank <span class="text-danger">*</span></td>
                    <td class="val-col"><input type="text" name="pay_bank_name" class="grid-input" placeholder="Bank name" maxlength="120" value="{{ old('pay_bank_name') }}"></td>
                    <td class="label-col">Branch <span class="text-danger">*</span></td>
                    <td class="val-col"><input type="text" name="pay_bank_branch" class="grid-input" placeholder="Branch name" maxlength="120" value="{{ old('pay_bank_branch') }}"></td>
                </tr>
            </table>
            <div class="small text-muted px-2 mb-3" style="font-size:11px;">The account must belong to you or your parent/guardian. A wrong number can delay or lose the payment.</div>

            @if($hasFee)
                {{-- 10. APPLICATION FEE (campaign setting) --}}
                <div class="form-section-head">10. Application Fee <span class="text-danger">*</span></div>
                <input type="hidden" name="amount_paid" value="{{ $feeAmount }}">
                <table class="grid-table mb-1">
                    <tr>
                        <td class="label-col">Amount</td>
                        <td colspan="3" class="val-col fw-bold">৳ {{ number_format($feeAmount, 0) }}</td>
                    </tr>
                    @if(!empty($campaign->payment_instructions) && !preg_match('/\p{Bengali}/u', $campaign->payment_instructions))
                        <tr><td class="label-col">How to pay</td><td colspan="3" class="val-col small">{!! nl2br(e($campaign->payment_instructions)) !!}</td></tr>
                    @endif
                    <tr>
                        <td class="label-col">Method <span class="text-danger">*</span></td>
                        <td class="val-col">
                            <select name="payment_method" class="grid-input" required>
                                @foreach(['' => '— Select —', 'bkash' => 'bKash', 'nagad' => 'Nagad', 'rocket' => 'Rocket', 'bank' => 'Bank'] as $mv => $ml)
                                    <option value="{{ $mv }}" {{ (string) old('payment_method') === (string) $mv ? 'selected' : '' }}>{{ $ml }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="label-col">Transaction ID <span class="text-danger">*</span></td>
                        <td class="val-col"><input type="text" name="transaction_id" class="grid-input font-monospace text-uppercase" maxlength="100" value="{{ old('transaction_id') }}" required></td>
                    </tr>
                    <tr>
                        <td class="label-col">Sender Number</td>
                        <td colspan="3" class="val-col"><input type="text" name="payment_phone" class="grid-input font-monospace" inputmode="numeric" maxlength="30" placeholder="Number the money was sent from" value="{{ old('payment_phone') }}"></td>
                    </tr>
                </table>
            @endif
            </section>

            <section class="scholarship-step" data-wizard-step="4">
            <div class="form-section-head">Preview</div>
            @include('frontend.events.partials.custom-fields')

            <div class="a4-stage" id="a4Stage">
                <div class="a4-sheet" id="a4Preview"></div>
            </div>

            <div class="p-3 bg-light border-top text-center">
                <div class="small fw-semibold text-danger mb-2">Your application cannot be edited after submission. Please review all information carefully.</div>
                <button type="button" id="previewEditBtn" class="btn btn-outline-primary rounded-pill px-4 mb-3">
                    Edit
                </button>
                <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                    <input type="checkbox" id="agreeCheck" name="declaration_agreed" checked required style="width: 16px; height: 16px; cursor: pointer;">
                    <label for="agreeCheck" class="small mb-0 text-dark fw-semibold" style="cursor: pointer;">
                        I declare that all information provided is accurate and true. <span class="text-danger">*</span>
                    </label>
                </div>

                {{-- Organization Disclaimer --}}
                <div class="text-muted mt-1 mb-3" style="font-size: 11.5px; line-height: 1.55; max-width: 740px; margin: 0 auto;">
                    Idea Prokashon does not provide any scholarships; it only facilitates online application submission through its website to support the Firiye Dekha organization and make the application process easier for applicants.
                </div>

                <button type="submit" id="submitBtn" class="btn-submit-app">
                    Submit Scholarship Application
                </button>
            </div>
            </section>

            <div class="scholarship-wizard-controls">
                <button type="button" id="wizardPrevious" class="btn btn-outline-secondary rounded-pill px-4" disabled>Back</button>
                <button type="button" id="wizardNext" class="btn btn-primary rounded-pill px-4">Continue</button>
            </div>

        </form>

    </div>
</div>

<script>
    let syncingAddress = false;

    function addressText(prefix) {
        const names = [
            prefix + 'Division',
            prefix === 'perm' ? 'permDistrict' : 'presDistrict',
            prefix + 'CityCorporation',
            prefix + 'Municipality',
            prefix + 'Upazila',
            prefix + 'PostOffice',
            prefix + 'Village'
        ];
        return names.map(id => {
            const field = document.getElementById(id);
            const value = field ? (field.tagName === 'SELECT' ? field.options[field.selectedIndex]?.text : field.value) : '';
            return String(value || '').trim();
        }).filter(value => value && !value.startsWith('--')).join(', ');
    }

    function updateAddressFields() {
        ['perm', 'pres'].forEach(prefix => {
            const postOffice = document.getElementById(prefix + 'PostOffice');
            const postcode = document.getElementById(prefix + 'Postcode');
            const fullAddress = document.getElementById(prefix + 'FullAddress');
            const postcodeMatch = (postOffice?.value || '').replace(/[০-৯]/g, d => '০১২৩৪৫৬৭৮৯'.indexOf(d)).match(/\((\d{4})\)/);
            if (postcode) postcode.value = postcodeMatch ? postcodeMatch[1] : '';
            if (fullAddress) fullAddress.value = addressText(prefix);
        });
    }

    function syncPermanentToPresent() {
        const checked = document.getElementById('syncAddressCheck')?.checked;
        if (!checked || syncingAddress) {
            updateAddressFields();
            return;
        }

        syncingAddress = true;
        try {
            const division = document.getElementById('permDivision')?.value || '';
            const district = document.getElementById('permDistrict')?.value || '';
            const upazila = document.getElementById('permUpazila')?.value || '';
            const postOffice = document.getElementById('permPostOffice')?.value || '';
            const city = document.getElementById('permCityCorporation')?.value || '';
            const municipality = document.getElementById('permMunicipality')?.value || '';
            const village = document.getElementById('permVillage')?.value || '';

            const presentDivision = document.getElementById('presDivision');
            presentDivision.value = division;
            presentDivision.dispatchEvent(new Event('change', { bubbles: true }));

            const presentDistrict = document.getElementById('presDistrict');
            presentDistrict.value = district;
            presentDistrict.dispatchEvent(new Event('change', { bubbles: true }));

            document.getElementById('presCityCorporation').value = city;
            document.getElementById('presMunicipality').value = municipality;

            const presentUpazila = document.getElementById('presUpazila');
            presentUpazila.value = upazila;
            presentUpazila.dispatchEvent(new Event('change', { bubbles: true }));
            document.getElementById('presPostOffice').value = postOffice;
            document.getElementById('presVillage').value = village;
            updateAddressFields();
        } finally {
            syncingAddress = false;
        }
    }

    // Client-side instant Canvas Auto-Optimizer for student photo
    function handlePhotoOptimize(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        const initialSizeKB = (file.size / 1024).toFixed(0);

        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                // Target passport size: 300 x 360 (5:6 aspect ratio)
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                const targetW = 300;
                const targetH = 360;

                canvas.width = targetW;
                canvas.height = targetH;

                // Center Crop
                const srcRatio = img.width / img.height;
                const targetRatio = targetW / targetH;
                let cropW = img.width;
                let cropH = img.height;
                let cropX = 0;
                let cropY = 0;

                if (srcRatio > targetRatio) {
                    cropW = img.height * targetRatio;
                    cropX = (img.width - cropW) / 2;
                } else {
                    cropH = img.width / targetRatio;
                    cropY = (img.height - cropH) / 2;
                }

                ctx.drawImage(img, cropX, cropY, cropW, cropH, 0, 0, targetW, targetH);

                // High quality compressed WebP/JPEG data URL
                const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
                
                // Put in hidden input for instant upload
                document.getElementById('optimizedPhotoData').value = dataUrl;
                const pe = document.getElementById('photoError'); if (pe) pe.hidden = true;
                // Upload only the optimized photo, not the original (which can be several MB)
                canvas.toBlob(blob => {
                    if (!blob) return;
                    try {
                        const dt = new DataTransfer();
                        dt.items.add(new File([blob], 'photo.jpg', { type: 'image/jpeg' }));
                        input.files = dt.files;
                    } catch (e) { /* the optimized data URL is posted anyway */ }
                }, 'image/jpeg', 0.85);

                // Show preview
                const preview = document.getElementById('previewImg');
                const prompt = document.getElementById('uploadPrompt');
                const badge = document.getElementById('optSizeBadge');

                preview.src = dataUrl;
                preview.style.display = 'block';
                prompt.style.display = 'none';

                const optSizeKB = ((dataUrl.length * 0.75) / 1024).toFixed(0);
                badge.textContent = `✓ ${initialSizeKB} KB → ${optSizeKB} KB`;
                badge.style.display = 'inline-block';
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }

    // Live 50-word counter & strict limiter
    function handleWordCount(textarea) {
        const text = textarea.value.trim();
        const words = text ? text.split(/\s+/).filter(Boolean) : [];
        const count = words.length;
        const badge = document.getElementById('wordCounter');
        const alertBox = document.getElementById('wordLimitAlert');
        const submitBtn = document.getElementById('submitBtn');

        badge.textContent = count + ' / 50 words';

        if (count > 50) {
            badge.className = 'word-badge bg-danger text-white';
            alertBox.classList.remove('d-none');
            submitBtn.disabled = true;
        } else if (count >= 45) {
            badge.className = 'word-badge bg-warning text-dark';
            alertBox.classList.add('d-none');
            submitBtn.disabled = false;
        } else {
            badge.className = 'word-badge bg-success text-white';
            alertBox.classList.add('d-none');
            submitBtn.disabled = false;
        }
    }

    // Auto format phone (with Bengali digit normalization)
    function formatPhone(input) {
        const bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
        const en = ['0','1','2','3','4','5','6','7','8','9'];
        let val = (input.value || '').toString();
        for (let i = 0; i < bn.length; i++) {
            val = val.replaceAll(bn[i], en[i]);
        }
        let digits = val.replace(/[^0-9]/g, '');
        const form = input.form;
        const ccSelect = input.name === 'phone' ? form?.elements['country_code']
            : null;
        if (ccSelect && ccSelect.value && ccSelect.value !== '+880') {
            // Foreign number: keep every digit, only strip a typed leading country code.
            const cc = ccSelect.value.replace(/\D/g, '');
            if (digits.startsWith(cc) && digits.length > cc.length + 6) digits = digits.substring(cc.length);
            input.value = digits.slice(0, 15);
            return;
        }
        if (digits.startsWith('880')) {
            digits = digits.substring(3);
        } else if (digits.startsWith('88') && digits.length === 12 && digits.charAt(2) === '1') {
            digits = digits.substring(2);
        }
        if (digits.length === 10 && digits.startsWith('1')) {
            digits = '0' + digits;
        }
        input.value = digits.slice(0, 11);
    }

    // Prevent double submission
    document.getElementById('dynamicScholarshipForm').addEventListener('submit', function(e) {
        assembleFormattedAddress('perm');
        assembleFormattedAddress('pres');
        const btn = document.getElementById('submitBtn');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Submitting...';
        btn.disabled = true;
    });
</script>
<script src="{{ asset('js/bd-geo-data.js') }}"></script>
<script src="{{ asset('js/bd-geo-en.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('dynamicScholarshipForm');
        const steps = Array.from(form.querySelectorAll('[data-wizard-step]'));
        const stepButtons = Array.from(form.querySelectorAll('[data-wizard-target]'));
        const previousButton = document.getElementById('wizardPrevious');
        const nextButton = document.getElementById('wizardNext');
        const progress = form.querySelector('.scholarship-wizard-progress');
        const progressFill = progress.querySelector('span');
        let activeStep = Math.max(0, Math.min(steps.length - 1, Number(form.dataset.initialStep || 0)));

        const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
        const val = name => (form.elements[name]?.value || '').trim();
        const up = v => esc(v.toUpperCase());

        // Total income = father + mother (no double entry)
        const sumIncome = () => {
            const t = (Number(val('father_income')) || 0) + (Number(val('mother_income')) || 0) + (Number(val('guardian_income')) || 0);
            if (form.elements['annual_income']) form.elements['annual_income'].value = t || '';
        };
        ['father_income', 'mother_income', 'guardian_income'].forEach(n => form.elements[n]?.addEventListener('input', sumIncome));
        sumIncome();

        // Every level from SSC to Masters is shown; levels below the chosen degree are mandatory
        const QUAL_REQUIRED = { HSC: ['ssc'], HONOURS: ['ssc', 'hsc'], MASTERS: ['ssc', 'hsc', 'grad'], DIPLOMA: ['ssc'], OTHERS: ['ssc'] };
        const toggleQualRows = () => {
            const need = QUAL_REQUIRED[val('scholarship_degree')] || ['ssc'];
            form.querySelectorAll('[data-qual-row]').forEach(tr => {
                const on = need.includes(tr.dataset.qualRow);
                tr.querySelectorAll('input').forEach(i => { i.required = on; });
                const star = tr.querySelector('.qual-star');
                if (star) star.hidden = !on;
            });
        };
        form.elements['scholarship_degree']?.addEventListener('change', toggleQualRows);
        toggleQualRows();

        // Scholarship history: details become mandatory only when the answer is Yes
        const syncScholarshipFields = () => {
            const joyee = val('prev_joyee') === 'Yes';
            const box = document.getElementById('prevJoyeeBox');
            if (box) box.hidden = !joyee;
            form.querySelectorAll('[name^="prev_scholarships"]').forEach(i => {
                i.disabled = !joyee;
                if (i.name === 'prev_scholarships[0][year]' || i.name === 'prev_scholarships[0][amount]') i.required = joyee;
            });
            const other = val('other_scholarship') === 'Yes';
            const od = form.elements['other_scholarship_details'];
            if (od) od.required = other;
            const count = Number(val('books_read_count')) || 0;
            const needNames = count > 0 || val('reading_habit') === 'I have read';
            const names = form.elements['books_read_names'];
            if (names) names.required = needNames;
            const star = document.getElementById('booksNamesStar');
            if (star) star.hidden = !needNames;
        };
        ['prev_joyee', 'other_scholarship', 'books_read_count', 'reading_habit'].forEach(n => {
            form.elements[n]?.addEventListener('change', syncScholarshipFields);
            form.elements[n]?.addEventListener('input', syncScholarshipFields);
        });
        syncScholarshipFields();

        // Payment account: wallet = 11-digit mobile number, bank = account number + bank/branch
        const syncPayment = () => {
            const method = val('pay_method');
            const bank = method === 'Bank';
            const row = document.getElementById('payBankRow');
            if (row) row.hidden = !bank;
            ['pay_bank_name', 'pay_bank_branch'].forEach(n => { if (form.elements[n]) form.elements[n].required = bank; });
            const no = form.elements['pay_account_no'];
            const label = document.getElementById('payNoLabel');
            if (label) label.textContent = bank ? 'Account No.' : (method ? method + ' No.' : 'Wallet No.');
            if (no) {
                no.placeholder = bank ? 'Bank account number' : '01XXXXXXXXX';
                no.maxLength = bank ? 20 : 11;
                no.pattern = bank ? '\\d{8,20}' : '01\\d{9}';
                no.title = bank ? 'Account number: 8 to 20 digits' : 'Mobile wallet number: 11 digits starting with 01';
            }
        };
        form.elements['pay_method']?.addEventListener('change', syncPayment);
        form.elements['pay_account_no']?.addEventListener('input', e => {
            const bn = '০১২৩৪৫৬৭৮৯';
            e.target.value = e.target.value.replace(/[০-৯]/g, d => bn.indexOf(d)).replace(/\D+/g, '');
        });
        form.elements['national_id']?.addEventListener('input', e => {
            const bn = '০১২৩৪৫৬৭৮৯';
            e.target.value = e.target.value.replace(/[০-৯]/g, d => bn.indexOf(d)).replace(/\D+/g, '');
        });
        syncPayment();

        // Parents: a deceased parent has no occupation ("Nil"), income or mobile; orphan status follows from both selects
        const syncParents = () => {
            const dead = {};
            ['father', 'mother'].forEach(p => {
                const isDead = val(p + '_status') === 'Deceased';
                dead[p] = isDead;
                const occ = form.elements[p + '_occupation'], inc = form.elements[p + '_income'], ph = form.elements[p + '_phone'];
                if (occ) {
                    if (isDead) { occ.dataset.prev = occ.dataset.prev ?? occ.value; occ.value = 'Nil'; occ.readOnly = true; occ.required = false; }
                    else { if (occ.readOnly) { occ.value = occ.dataset.prev ?? ''; delete occ.dataset.prev; } occ.readOnly = false; occ.required = true; }
                }
                [inc, ph].forEach(i => { if (i) { if (isDead) i.value = ''; i.disabled = isDead; } });
            });
            const orphan = document.getElementById('orphanStatus');
            if (orphan) orphan.value = dead.father && dead.mother ? 'Both deceased' : dead.father ? 'Father deceased' : dead.mother ? 'Mother deceased' : 'None';
            sumIncome();
        };
        ['father_status', 'mother_status'].forEach(n => form.elements[n]?.addEventListener('change', syncParents));

        // Disability: category (and details for "Other") only when the answer is Yes
        const syncDisability = () => {
            const yes = val('disability') === 'Yes';
            ['disabilityTypeLabel', 'disabilityTypeCell'].forEach(id => { const e = document.getElementById(id); if (e) e.hidden = !yes; });
            const row = document.getElementById('disabilityDetailsRow');
            if (row) row.hidden = !yes;
            const type = form.elements['disability_type'], det = form.elements['disability_details'];
            if (type) { type.required = yes; if (!yes) type.value = ''; }
            const other = yes && val('disability_type') === 'Other';
            if (det) { det.required = other; if (!yes) det.value = ''; }
            const star = document.getElementById('disabilityDetailsStar');
            if (star) star.hidden = !other;
        };
        ['disability', 'disability_type'].forEach(n => form.elements[n]?.addEventListener('change', syncDisability));

        // Family / guardian follow-ups
        const syncFamily = () => {
            const fam = Number(val('family_count')) || 0;
            const earn = form.elements['earning_count'];
            if (earn) {
                earn.max = fam || 40;
                earn.setCustomValidity(fam && Number(earn.value) > fam ? 'Earning members cannot exceed total family members' : '');
            }
            const gr = form.elements['guardian_relation'];
            if (gr) gr.required = val('guardian_name') !== '';
        };
        ['family_count', 'earning_count', 'guardian_name'].forEach(n => {
            form.elements[n]?.addEventListener('input', syncFamily);
            form.elements[n]?.addEventListener('change', syncFamily);
        });
        syncFamily();
        syncParents();
        syncDisability();

        // Honours/Masters years must not be earlier than HSC/Honours
// Year / birth-date consistency (clears itself when fixed)
        function checkYears() {
            const birthYear = Number((val('birth_date') || '').slice(0, 4)) || 0;
            const ssc = Number(val('ssc_year')) || 0;
            const hsc = Number(val('hsc_year')) || 0;
            const thisYear = new Date().getFullYear();
            const set = (name, msg) => { const f = form.elements[name]; if (f) f.setCustomValidity(msg); };
            ['ssc_year', 'hsc_year', 'grad_year', 'other_year'].forEach(n => {
                const y = Number(val(n)) || 0;
                set(n, y && (y < 1980 || y > thisYear + 1) ? 'Invalid year (1980–' + (thisYear + 1) + ')' : '');
            });
            if (birthYear && ssc && ssc - birthYear < 10) set('ssc_year', 'SSC year does not match the birth year');
            if (ssc && hsc && hsc <= ssc) set('hsc_year', 'HSC year must be after the SSC year');
            const bd = form.elements['birth_date'];
            if (bd) bd.setCustomValidity(birthYear && (birthYear < 1970 || birthYear > thisYear - 10) ? 'Invalid birth year' : '');
        }
        ['birth_date', 'ssc_year', 'hsc_year', 'grad_year', 'other_year'].forEach(n =>
            form.elements[n]?.addEventListener('input', checkYears));
        checkYears();

        // Re-normalise phone when country code changes
        [['country_code', 'phone']].forEach(([c, p]) =>
            form.elements[c]?.addEventListener('change', () => form.elements[p] && formatPhone(form.elements[p])));

        function buildPreview() {
            const g = {};
            Array.from(form.elements).forEach(f => { if (f.name && !(f.name in g)) g[f.name] = f.value; });
            const row = (a, b, c, d) => `<tr><td class="pv-lbl">${a}</td><td>${b}</td>` + (c !== undefined ? `<td class="pv-lbl">${c}</td><td>${d}</td>` : '') + '</tr>';
            const taka = v => v ? esc(v) + ' Tk' : '';
            const photo = document.getElementById('previewImg');
            const photoSrc = document.getElementById('optimizedPhotoData')?.value || (photo && photo.getAttribute('src')) || '';
            const photoHtml = photoSrc ? `<img src="${photoSrc}" alt="">` : '<div style="padding-top:44px;">Photo</div>';
            const levels = [['SSC', 'ssc', 'ssc_gpa'], ['HSC', 'hsc', 'hsc_gpa'], ['Honours', 'grad', 'grad_cgpa'], ['Masters', 'other', 'other_cgpa']]
                .filter(([, p, gp]) => p === 'ssc' || val(p + '_roll') || val(p + '_institute') || val(gp))
                .map(([l, p, gp]) => `<tr><td>${l}</td><td align="center">${esc(val(p + '_roll'))}</td><td align="center">${up(val(p + '_board'))}</td><td>${up(val(p + '_institute'))}</td><td align="center">${esc(val(p + '_year'))}</td><td align="center"><b>${esc(val(gp))}</b></td></tr>`).join('');
            const joyee = val('prev_joyee');
            const prevRows = [0, 1, 2].map(i => ({y: val(`prev_scholarships[${i}][year]`), t: val(`prev_scholarships[${i}][times]`), a: val(`prev_scholarships[${i}][amount]`)}))
                .filter(r => r.y || r.t || r.a)
                .map(r => `<tr><td align="center">${esc(r.y)}</td><td align="center">${esc(r.t)}</td><td align="center">${esc(r.a)}</td></tr>`).join('');
            const joyeeHtml = joyee === 'Yes' && prevRows
                ? `<table><tr><th>Year</th><th>Times</th><th>Amount (Tk)</th></tr>${prevRows}</table>` : '';
            const perm = document.getElementById('permFullAddress')?.value || val('permanent_address');
            const pres = document.getElementById('presFullAddress')?.value || val('present_address');
            const phone = (form.elements['country_code']?.value || '') + ' ' + val('phone');
            const parentIncome = (n) => val(n) ? ' <span style="white-space:nowrap">(Income: ' + esc(val(n)) + ')</span>' : '';
            const sig = t => `<td style="border:0;width:42%;padding-top:30px;"><div class="pv-sign">${t}</div></td>`;
            const degreeText = form.elements['scholarship_degree']?.selectedOptions[0]?.text || '';

            document.getElementById('a4Preview').innerHTML = `
                <table class="pv-head"><tr>
                    <td style="width:90px;"></td>
                    <td align="center"><div style="font-weight:700;font-size:13px;">Joyee Shikkhabritti</div>
                        <div style="font-size:26px;font-weight:700;letter-spacing:3px;text-transform:uppercase;line-height:1.15;">Scholarship</div>
                        <div style="font-size:10px;">Session 2027</div>
                        <div style="font-size:11px;margin-top:2px;">Application No: <b>{{ $previewRegNumber ?? '' }}</b></div></td>
                    <td align="right" style="width:98px;"><div class="pv-photo">${photoHtml}</div></td>
                </tr></table>

                <div class="pv-sec">1. Applicant</div>
                <table>
                    ${row('Name', '<b>' + up(val('name')) + '</b>', 'Mobile', '<b>' + esc(phone) + '</b>')}
                    ${row('Date of Birth', esc(val('birth_date')), 'Gender / Religion', esc(val('gender') + ' / ' + val('religion')))}
                    ${row('NID / Birth Reg.', esc(val('national_id')), 'Email', esc(val('email')))}
                    ${row('Marital Status', esc(val('marital_status')), 'Nationality', up(val('nationality')))}
                    ${row('Disability', esc(val('disability')) + (val('disability') === 'Yes' ? ' — ' + esc(form.elements['disability_type']?.selectedOptions[0]?.text || '') + (val('disability_details') ? ' (' + esc(val('disability_details')) + ')' : '') : ''), '', '')}
                </table>

                <div class="pv-sec">2. Parents &amp; Guardian</div>
                <table>
                    ${['father', 'mother'].map(p => {
                        const L = p === 'father' ? 'Father' : 'Mother', gone = val(p + '_status') === 'Deceased';
                        return row(L, up(val(p + '_name')) + ' (' + esc(val(p + '_status')) + ')', L + ' Occupation', gone ? 'Nil' : esc(val(p + '_occupation')))
                             + row(L + ' Income', gone ? 'Nil' : taka(val(p + '_income')), L + ' Mobile', gone ? 'Nil' : esc(val(p + '_phone')));
                    }).join('')}
                    ${row('Guardian', up(val('guardian_name')) + (val('guardian_relation') ? ' (' + esc(val('guardian_relation')) + ')' : '') + (val('guardian_income') ? ' — Income: ' + esc(val('guardian_income')) + ' Tk' : ''), 'Guardian Contact', esc(val('guardian_phone')))}
                    ${row('Annual Family Income', taka(val('annual_income')), 'Family / Earning Members', esc(val('family_count')) + ' / ' + esc(val('earning_count')) + (val('orphan_status') && val('orphan_status') !== 'None' ? ' — ' + esc(val('orphan_status')) : ''))}
                </table>
                <div class="pv-sec">3. Academic</div>
                <table>
                    ${row('Degree Applied For', esc(degreeText), 'Institute', '<b>' + up(val('college_name')) + '</b>' + (val('college_code') ? ' (' + esc(val('college_code')) + ')' : ''))}
                    ${row('Group', up(val('group')), 'Admission Roll / Merit', esc(val('admission_roll')) + (val('merit_position') ? ' / ' + esc(val('merit_position')) : ''))}
                    ${row('Subject', up(val('assigned_subject')), 'Admission Fee', taka(val('admission_fee')))}
                    ${row('Monthly Fee', taka(val('monthly_fee')), 'Hostel Cost', taka(val('hostel_cost')))}
                    ${row('Current Year', esc(val('current_year')), '', '')}
                </table>

                <div class="pv-sec">4. Educational Qualification</div>
                <table>
                    <tr><th>Level</th><th>Roll / Reg</th><th>Board / University</th><th>Institute</th><th>Year</th><th>GPA</th></tr>
                    ${levels}
                </table>

                <div class="pv-sec">5. Address</div>
                <table>
                    <tr><td class="pv-lbl">Permanent</td><td>${esc(perm)}</td></tr>
                    <tr><td class="pv-lbl">Present</td><td>${pres && pres !== perm ? esc(pres) : 'Same as permanent address'}</td></tr>
                </table>

                <div class="pv-sec">6. Scholarships</div>
                <table>
                    <tr><td class="pv-lbl">Received Joyee Scholarship before?</td><td>${esc(joyee || '—')}</td></tr>
                    <tr><td class="pv-lbl">Scholarship from another organization?</td><td>${esc(val('other_scholarship') || '—')}${val('other_scholarship_details') ? ' — ' + esc(val('other_scholarship_details')) : ''}</td></tr>
                </table>
                ${joyeeHtml}

                <div class="pv-sec">7. Reading</div>
                <table>
                    ${row('Original books read', esc(val('books_read_count')), 'Reading habit', esc(val('reading_habit')))}
                    <tr><td class="pv-lbl">Book names</td><td colspan="3">${esc(val('books_read_names')).replace(/\n/g, '<br>')}</td></tr>
                </table>

                <div class="pv-sec">8. Statement (max 50 words)</div>
                <table><tr><td style="min-height:40px;">${esc(val('scholarship_reason'))}</td></tr></table>

                <div class="pv-sec">9. Payment Account</div>
                <table>
                    ${row('Method', esc(val('pay_method') === 'Bank' ? 'Bank Account' : val('pay_method')), 'Account Owner', esc(val('pay_account_owner')))}
                    ${row('Holder Name', up(val('pay_account_name')), val('pay_method') === 'Bank' ? 'Account No.' : 'Wallet No.', esc(val('pay_account_no')))}
                    ${val('pay_method') === 'Bank' ? row('Bank', esc(val('pay_bank_name')), 'Branch', esc(val('pay_bank_branch'))) : ''}
                </table>


                <div style="font-size:10px;text-align:justify;">I, <b>${up(val('name'))}</b>, declare that all information and the photo provided above are correct. The scholarship will be cancelled if any information is found false.</div>
                <table style="margin-top:4px;"><tr><td style="border:0;width:55%;"></td>${sig('Applicant signature &amp; date').replace('42%', '45%')}</tr></table>

                <table style="margin-top:8px;">
                    <tr><th style="text-align:left;width:50%;">Remarks (max 50 words) for Head of institute</th><th style="text-align:left;">Remarks (max 50 words) for Public representative</th></tr>
                    <tr><td style="height:50px;"></td><td></td></tr>
                    <tr><td style="height:70px;vertical-align:bottom;">Head of institute: signature, mobile &amp; seal</td><td style="vertical-align:bottom;">Public representative: signature, mobile &amp; seal</td></tr>
                </table>
                <table style="margin-top:6px;"><tr>${sig('Director (approver)')}<td style="border:0;"></td>${sig('Treasurer')}</tr></table>`;
            fitPreview();
        }

        function fitPreview() {
            const stage = document.getElementById('a4Stage');
            const sheet = document.getElementById('a4Preview');
            if (!stage || !sheet) return;
            sheet.style.transform = '';
            sheet.style.margin = '0 auto';
            const avail = stage.clientWidth - 16;
            const scale = Math.min(1, avail / sheet.offsetWidth);
            if (scale < 1) {
                sheet.style.transform = `scale(${scale})`;
                sheet.style.margin = '0';
                stage.style.height = (sheet.offsetHeight * scale + 28) + 'px';
            } else {
                stage.style.height = '';
            }
        }
        window.addEventListener('resize', () => { if (activeStep === steps.length - 1) fitPreview(); });
        document.getElementById('previewEditBtn')?.addEventListener('click', () => showStep(0));

        function showStep(index) {
            activeStep = index;
            if (activeStep === steps.length - 1) buildPreview();
            steps.forEach((step, stepIndex) => {
                step.hidden = stepIndex !== activeStep;
                step.setAttribute('aria-hidden', stepIndex !== activeStep ? 'true' : 'false');
            });
            stepButtons.forEach((button, stepIndex) => {
                button.classList.toggle('active', stepIndex === activeStep);
                button.classList.toggle('complete', stepIndex < activeStep);
                if (stepIndex === activeStep) {
                    button.setAttribute('aria-current', 'step');
                } else {
                    button.removeAttribute('aria-current');
                }
            });
            previousButton.disabled = activeStep === 0;
            nextButton.hidden = activeStep === steps.length - 1;
            progressFill.style.width = `${((activeStep + 1) / steps.length) * 100}%`;
            progress.setAttribute('aria-valuenow', String(activeStep + 1));
            if (activeStep === 2) updateAddressFields();
        }

        const hasPhoto = () => !!(document.getElementById('optimizedPhotoData')?.value || form.elements['student_photo']?.files?.length);
        function validateStep(index) {
            const photoError = document.getElementById('photoError');
            if (index === 0) {
                if (photoError) photoError.hidden = hasPhoto();
                if (!hasPhoto()) {
                    showStep(0);
                    document.querySelector('.photo-cell-box')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return false;
                }
            }
            const invalidField = Array.from(steps[index].querySelectorAll('input, select, textarea'))
                .find(field => field.willValidate && !field.checkValidity());
            if (!invalidField) return true;
            showStep(index);
            invalidField.reportValidity();
            invalidField.focus();
            return false;
        }

        stepButtons.forEach(button => {
            button.addEventListener('click', () => {
                const target = Number(button.dataset.wizardTarget);
                if (target <= activeStep) {
                    showStep(target);
                    return;
                }
                for (let index = activeStep; index < target; index++) {
                    if (!validateStep(index)) return;
                }
                showStep(target);
            });
        });
        previousButton.addEventListener('click', () => showStep(Math.max(0, activeStep - 1)));
        nextButton.addEventListener('click', () => {
            if (validateStep(activeStep)) showStep(Math.min(steps.length - 1, activeStep + 1));
        });
        form.addEventListener('submit', event => {
            for (let index = 0; index < steps.length; index++) {
                if (!validateStep(index)) {
                    event.preventDefault();
                    return;
                }
            }
        });
        showStep(activeStep);

        if (typeof initAddressChaining === 'function') {
            initAddressChaining('perm');
            initAddressChaining('pres');
        }
        form.querySelectorAll('[name^="perm_"], [name^="pres_"], [name="permanent_district"], [name="present_district"]').forEach(field => {
            ['change', 'input'].forEach(eventName => field.addEventListener(eventName, () => {
                if (document.getElementById('syncAddressCheck').checked && !syncingAddress) {
                    syncPermanentToPresent();
                } else {
                    updateAddressFields();
                }
            }));
        });
        updateAddressFields();

        const reason = document.getElementById('reasonText');
        if (reason && reason.value) {
            handleWordCount(reason);
        }
    });
</script>
@endsection
