@extends('layouts.app')

@php
    $eventTitle = $campaign->title ?: 'শিক্ষা অনুদান ও মেধা বৃত্তি আবেদন ফরম ২০২৬';
    $eventDesc  = $campaign->short_description ?: 'আইডিয়া প্রকাশন ও বুকস অব আইডিয়া শিক্ষা অনুদান ও মেধা বৃত্তি নিবন্ধন আবেদন ফরম।';
    $eventCover = $campaign->banner_image ? asset('storage/' . $campaign->banner_image) : asset('images/og-banner.jpg');
    $wizardFields = [
        0 => ['name', 'gender', 'father_name', 'mother_name', 'religion', 'nationality', 'birth_date', 'phone', 'marital_status', 'annual_income', 'father_income', 'mother_income', 'guardian_income', 'guardian_name', 'guardian_phone'],
        1 => ['group', 'admission_roll', 'merit_position', 'college_code', 'college_name', 'assigned_subject', 'scholarship_degree', 'admission_fee', 'monthly_fee', 'hostel_cost', 'ssc_roll', 'ssc_board', 'ssc_institute', 'ssc_year', 'ssc_gpa', 'hsc_roll', 'hsc_board', 'hsc_institute', 'hsc_year', 'hsc_gpa', 'grad_roll', 'grad_board', 'grad_institute', 'grad_year', 'grad_cgpa', 'other_roll', 'other_board', 'other_institute', 'other_year', 'other_cgpa'],
        2 => ['perm_division', 'permanent_district', 'perm_upazila', 'perm_post_office', 'perm_village', 'pres_division', 'present_district', 'pres_upazila', 'pres_post_office', 'pres_village'],
        3 => ['scholarship_reason', 'other_scholarship', 'other_scholarship_details'],
    ];
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

@section('title', $eventTitle . ' — আইডিয়া প্রকাশন')
@section('meta_description', Str::limit(strip_tags($eventDesc), 180))
@section('meta_keywords', 'শিক্ষা অনুদান, মেধা বৃত্তি, স্কলারশিপ আবেদন, শিক্ষার্থী বৃত্তি, আইডিয়া প্রকাশন, scholarship registration bangladesh')
@section('og_type', 'website')
@section('og_title', $eventTitle . ' | আইডিয়া প্রকাশন')
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
        border: 1.5px solid #0f3a68;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        overflow: hidden;
    }
    .form-header-bar {
        background: #0f3a68;
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
        width: 18%;
    }
    .grid-table .val-col {
        padding: 5px 8px;
    }
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
        border-color: #0f3a68;
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
        border-color: #0f3a68;
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
        background: #0f3a68;
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
        color: #0f3a68;
    }
    .scholarship-wizard-step.active span,
    .scholarship-wizard-step.complete span {
        color: #fff;
        background: #0f3a68;
        border-color: #0f3a68;
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
        background: #0f3a68;
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
</style>

<div class="container py-4">
    <div class="scholarship-form-wrap">

        @auth
            <div class="card border-0 rounded-3 p-3 mb-3 shadow-xs d-flex flex-row align-items-center justify-content-between flex-wrap gap-2" style="background: #f0fdf4; border: 1px solid #86efac !important;">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; font-size: 14px;">
                                            </div>
                    <div>
                        <div class="fw-bold text-dark" style="font-size: 13px;">
                            <span>আপনি <strong>{{ auth()->user()->name }}</strong> হিসেবে সাইন-ইন আছেন</span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle ms-1">লগইনকৃত শিক্ষার্থী</span>
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
                                                <span>লগআউট / অন্য একাউন্ট</span>
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

            {{-- HEADER BAR --}}
            <div class="form-header-bar">
                <div>
                    <h5 class="mb-0 fw-bold" style="font-size: 16px;">Scholarship</h5>
                    <div style="font-size: 11px; opacity: 0.9;">Session: 2026-2027 | Higher Secondary & Bachelor's (Hons)</div>
                </div>
                <div class="text-end">
                    <span class="badge bg-light text-dark fw-bold" style="font-size: 11px;">ENTRY</span>
                </div>
            </div>

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
            {{-- 1. APPLICANT DETAILS --}}
            <div class="form-section-head">Applicant</div>
            <table class="grid-table">
                <tr>
                    <td class="label-col">Name <span class="text-danger">*</span></td>
                    <td class="val-col" style="width: 48%;">
                        <input type="text" name="name" class="grid-input text-uppercase" placeholder="FULL NAME IN CAPITAL" value="{{ old('name', $user?->name) }}" required>
                    </td>
                    <td rowspan="2" colspan="2" class="text-center" style="width: 34%; vertical-align: middle; background: #fafafa;">
                        <div class="photo-cell-box" onclick="document.getElementById('rawPhotoInput').click()" title="Click to upload/optimize photo">
                            <img id="previewImg" class="photo-cell-img" alt="Student Photo">
                            <div id="uploadPrompt" class="p-2 text-center">
                                <div style="font-size: 24px;">📷</div>
                                <div style="font-size: 11px; font-weight: bold; color: #0f3a68;">Student Photo</div>
                                <div style="font-size: 9.5px; color: #64748b;">Auto-Optimized</div>
                            </div>
                        </div>
                        <div id="optSizeBadge" class="opt-badge">✓ Auto-Optimized</div>
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
                    <td class="label-col">Father <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <input type="text" name="father_name" class="grid-input text-uppercase" placeholder="FATHER'S FULL NAME" value="{{ old('father_name') }}" required>
                    </td>
                    <td class="label-col">Income</td>
                    <td class="val-col"><input type="number" min="0" name="father_income" class="grid-input font-monospace" placeholder="Tk" value="{{ old('father_income') }}"></td>
                </tr>
                <tr>
                    <td class="label-col">Mother <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <input type="text" name="mother_name" class="grid-input text-uppercase" placeholder="MOTHER'S FULL NAME" value="{{ old('mother_name') }}" required>
                    </td>
                    <td class="label-col">Income</td>
                    <td class="val-col"><input type="number" min="0" name="mother_income" class="grid-input font-monospace" placeholder="Tk" value="{{ old('mother_income') }}"></td>
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
                    <td class="label-col">Birth <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <input type="date" name="birth_date" id="birthDate" class="grid-input" min="1970-01-01" max="{{ now()->subYears(10)->toDateString() }}" value="{{ old('birth_date') }}" required>
                    </td>
                    <td class="label-col">Marital <span class="text-danger">*</span></td>
                    <td class="val-col">
                        <select name="marital_status" class="grid-input" required>
                            <option value="Unmarried" {{ old('marital_status') === 'Unmarried' ? 'selected' : '' }}>Unmarried</option>
                            <option value="Married" {{ old('marital_status') === 'Married' ? 'selected' : '' }}>Married</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Mobile <span class="text-danger">*</span></td>
                    <td colspan="3" class="val-col">
                        <div class="d-flex align-items-stretch" style="max-width: 380px;">
                            <select name="country_code" class="form-select p-1 bg-light fw-bold" style="width: 90px; flex: 0 0 90px; font-size: 11px; border: 1px solid #94a3b8; border-right: 0;" aria-label="Country Code">
                                @include('partials.country-code-options', ['selected' => old('country_code', '+880')])
                            </select>
                            <input type="tel" name="phone" id="studentPhone" class="grid-input font-monospace flex-grow-1" placeholder="017XXXXXXXX" inputmode="numeric" value="{{ old('phone', $user?->phone) }}" required maxlength="15" oninput="formatPhone(this)">
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Guardian</td>
                    <td class="val-col">
                        <input type="text" name="guardian_name" class="grid-input text-uppercase" placeholder="NAME (if not parent)" value="{{ old('guardian_name') }}">
                    </td>
                    <td class="label-col">Income</td>
                    <td class="val-col"><input type="number" min="0" name="guardian_income" class="grid-input font-monospace" placeholder="Tk" value="{{ old('guardian_income') }}"></td>
                </tr>
                <tr>
                    <td class="label-col">Contact</td>
                    <td colspan="3" class="val-col">
                        <div class="d-flex align-items-stretch" style="max-width: 380px;">
                            <select name="guardian_country_code" class="form-select p-1 bg-light fw-bold" style="width: 90px; flex: 0 0 90px; font-size: 11px; border: 1px solid #94a3b8; border-right: 0;" aria-label="Guardian Country Code">
                                @include('partials.country-code-options', ['selected' => old('guardian_country_code', '+880')])
                            </select>
                            <input type="tel" name="guardian_phone" class="grid-input font-monospace flex-grow-1" placeholder="Parent / Guardian mobile" inputmode="numeric" value="{{ old('guardian_phone') }}" maxlength="15" oninput="formatPhone(this)">
                        </div>
                        <input type="hidden" name="annual_income" id="totalIncome" value="{{ old('annual_income') }}">
                    </td>
                </tr>
            </table>

            </section>

            <section class="scholarship-step" data-wizard-step="1">
            {{-- 2. ACADEMIC INFORMATION --}}
            <div class="form-section-head">Academic</div>
            <table class="grid-table">
                <tr>
                    <td class="label-col">Degree <span class="text-danger">*</span></td>
                    <td colspan="3" class="val-col">
                        <select name="scholarship_degree" class="grid-input" required>
                            @foreach(['HSC' => 'HSC / Intermediate', 'HONOURS' => 'Honours / Bachelor', 'MASTERS' => 'Masters', 'DIPLOMA' => 'Diploma', 'OTHERS' => 'Others'] as $dv => $dl)
                                <option value="{{ $dv }}" {{ old('scholarship_degree') === $dv ? 'selected' : '' }}>{{ $dl }}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Group <span class="text-danger">*</span></td>
                    <td class="val-col" style="width: 32%;">
                        <select name="group" class="grid-input" required>
                            <option value="HUMANITIES" {{ old('group') === 'HUMANITIES' ? 'selected' : '' }}>HUMANITIES</option>
                            <option value="SCIENCE" {{ old('group') === 'SCIENCE' ? 'selected' : '' }}>SCIENCE</option>
                            <option value="BUSINESS STUDIES" {{ old('group') === 'BUSINESS STUDIES' ? 'selected' : '' }}>BUSINESS STUDIES</option>
                            <option value="OTHERS" {{ old('group') === 'OTHERS' ? 'selected' : '' }}>OTHERS</option>
                        </select>
                    </td>
                    <td class="label-col">Roll <span class="text-danger">*</span></td>
                    <td class="val-col" style="width: 32%;">
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
</table>

            {{-- 3. EDUCATIONAL QUALIFICATIONS --}}
            <div class="form-section-head">Qualifications</div>
            <div class="table-responsive">
                <table class="grid-table text-center mb-0">
                    <thead>
                        <tr style="background: #f8fafc; font-size: 11px; font-weight: bold;">
                            <th style="width: 20%; text-align: left;">Exam</th>
                            <th style="width: 14%;">Roll</th>
                            <th style="width: 16%;">Board</th>
                            <th style="width: 28%;">Institute</th>
                            <th style="width: 11%;">Year</th>
                            <th style="width: 11%;">GPA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="text-align: left; font-weight: 600; font-size: 11.5px;">SSC <span class="text-danger">*</span></td>
                            <td><input type="text" name="ssc_roll" class="grid-input text-center font-monospace" placeholder="Roll" value="{{ old('ssc_roll') }}" required></td>
                            <td><input type="text" name="ssc_board" class="grid-input text-center text-uppercase" placeholder="e.g. DINAJPUR" value="{{ old('ssc_board') }}" required></td>
                            <td><input type="text" name="ssc_institute" class="grid-input text-uppercase" placeholder="School Name" value="{{ old('ssc_institute') }}" required></td>
                            <td><input type="number" name="ssc_year" class="grid-input text-center font-monospace" placeholder="2019" value="{{ old('ssc_year') }}" required></td>
                            <td><input type="text" name="ssc_gpa" class="grid-input text-center font-monospace" placeholder="4.67" value="{{ old('ssc_gpa') }}" required></td>
                        </tr>
                        <tr data-qual-row="HSC">
                            <td style="text-align: left; font-weight: 600; font-size: 11.5px;">HSC</td>
                            <td><input type="text" name="hsc_roll" class="grid-input text-center font-monospace" placeholder="Roll" value="{{ old('hsc_roll') }}"></td>
                            <td><input type="text" name="hsc_board" class="grid-input text-center text-uppercase" placeholder="e.g. DINAJPUR" value="{{ old('hsc_board') }}"></td>
                            <td><input type="text" name="hsc_institute" class="grid-input text-uppercase" placeholder="College Name" value="{{ old('hsc_institute') }}"></td>
                            <td><input type="number" name="hsc_year" class="grid-input text-center font-monospace" placeholder="2021" value="{{ old('hsc_year') }}"></td>
                            <td><input type="text" name="hsc_gpa" class="grid-input text-center font-monospace" placeholder="4.67" value="{{ old('hsc_gpa') }}"></td>
                        </tr>
                        <tr data-qual-row="Graduation">
                            <td style="text-align: left; font-weight: 600; font-size: 11.5px;">Graduation</td>
                            <td><input type="text" name="grad_roll" class="grid-input text-center font-monospace" placeholder="Roll / Reg" value="{{ old('grad_roll') }}"></td>
                            <td><input type="text" name="grad_board" class="grid-input text-center text-uppercase" placeholder="e.g. NU / DU" value="{{ old('grad_board') }}"></td>
                            <td><input type="text" name="grad_institute" class="grid-input text-uppercase" placeholder="College / University" value="{{ old('grad_institute') }}"></td>
                            <td><input type="number" name="grad_year" class="grid-input text-center font-monospace" placeholder="2025" value="{{ old('grad_year') }}"></td>
                            <td><input type="text" name="grad_cgpa" class="grid-input text-center font-monospace" placeholder="3.50" value="{{ old('grad_cgpa') }}"></td>
                        </tr>
                        <tr data-qual-row="Masters">
                            <td style="text-align: left; font-weight: 600; font-size: 11.5px;">Masters</td>
                            <td><input type="text" name="other_roll" class="grid-input text-center font-monospace" placeholder="Roll / Reg" value="{{ old('other_roll') }}"></td>
                            <td><input type="text" name="other_board" class="grid-input text-center text-uppercase" placeholder="Board / Univ" value="{{ old('other_board') }}"></td>
                            <td><input type="text" name="other_institute" class="grid-input text-uppercase" placeholder="Institute / Dept" value="{{ old('other_institute') }}"></td>
                            <td><input type="number" name="other_year" class="grid-input text-center font-monospace" placeholder="Year" value="{{ old('other_year') }}"></td>
                            <td><input type="text" name="other_cgpa" class="grid-input text-center font-monospace" placeholder="CGPA" value="{{ old('other_cgpa') }}"></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            </section>

            <section class="scholarship-step" data-wizard-step="2">
            {{-- 4. ADDRESS DETAILS --}}
            <div class="form-section-head d-flex justify-content-between align-items-center">
                <span>Address</span>
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
                    <td class="val-col" style="width: 32%;">
                        <select name="perm_division" id="permDivision" class="grid-input" data-old="{{ old('perm_division') }}" required></select>
                    </td>
                    <td class="label-col">District <span class="text-danger">*</span></td>
                    <td class="val-col" style="width: 32%;">
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
                    <td class="val-col" style="width: 32%;">
                        <select name="pres_division" id="presDivision" class="grid-input" data-old="{{ old('pres_division') }}" required></select>
                    </td>
                    <td class="label-col">District <span class="text-danger">*</span></td>
                    <td class="val-col" style="width: 32%;">
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
            {{-- SCHOLARSHIP HISTORY --}}
            <div class="form-section-head">Scholarships</div>
            <table class="grid-table text-center mb-3">
                <tr style="background:#f8fafc;font-size:11px;font-weight:bold;">
                    <th style="width:10%;">No</th>
                    <th>Year</th>
                    <th>Times</th>
                    <th>Amount</th>
                </tr>
                @for($i = 0; $i < 3; $i++)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td class="val-col"><input type="text" name="prev_scholarships[{{ $i }}][year]" class="grid-input text-center font-monospace" placeholder="e.g. 2025" inputmode="numeric" value="{{ old('prev_scholarships.'.$i.'.year') }}"></td>
                        <td class="val-col"><input type="text" name="prev_scholarships[{{ $i }}][times]" class="grid-input text-center font-monospace" inputmode="numeric" value="{{ old('prev_scholarships.'.$i.'.times') }}"></td>
                        <td class="val-col"><input type="text" name="prev_scholarships[{{ $i }}][amount]" class="grid-input text-center font-monospace" inputmode="numeric" placeholder="Tk" value="{{ old('prev_scholarships.'.$i.'.amount') }}"></td>
                    </tr>
                @endfor
                <tr>
                    <td class="label-col" colspan="2" style="text-align:left;">Other</td>
                    <td class="val-col">
                        <select name="other_scholarship" class="grid-input">
                            <option value="No" {{ old('other_scholarship') !== 'Yes' ? 'selected' : '' }}>No</option>
                            <option value="Yes" {{ old('other_scholarship') === 'Yes' ? 'selected' : '' }}>Yes</option>
                        </select>
                    </td>
                    <td class="val-col"><input type="text" name="other_scholarship_details" class="grid-input" placeholder="Organization & amount" value="{{ old('other_scholarship_details') }}"></td>
                </tr>
            </table>

{{-- 5. REASON FOR SCHOLARSHIP (50 WORDS MAX) --}}
            <div class="form-section-head d-flex justify-content-between align-items-center">
                <span>Statement (Max 50 Words) <span class="text-danger">*</span></span>
                <span id="wordCounter" class="word-badge bg-secondary text-white">0 / 50 words</span>
            </div>
            <div class="p-3 bg-white">
                <textarea name="scholarship_reason" id="reasonText" rows="3" class="grid-input" placeholder="State briefly why you need this scholarship (Max 50 words in English)..." oninput="handleWordCount(this)" required>{{ old('scholarship_reason') }}</textarea>
                <div id="wordLimitAlert" class="text-danger small mt-1 d-none font-monospace">
                    ⚠ Exceeded 50 words limit. Extra words will be ignored.
                </div>
            </div>

            </section>

            <section class="scholarship-step" data-wizard-step="4">
            <div class="form-section-head">Preview</div>
            @include('frontend.events.partials.custom-fields')

            <div class="a4-stage" id="a4Stage">
                <div class="a4-sheet" id="a4Preview"></div>
            </div>

            <div class="p-3 bg-light border-top text-center">
                <div class="small fw-semibold text-danger mb-2">জমা দেওয়ার (Submit) পর আর সংশোধন করা যাবে না। অনুগ্রহ করে সব তথ্য ভালো করে দেখে নিন।</div>
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
            const postcodeMatch = postOffice?.value.match(/\((\d{4})\)/);
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
            : (input.name === 'guardian_phone' ? form?.elements['guardian_country_code'] : null);
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

        // Show only the qualification rows that apply to the chosen degree
        const toggleQualRows = () => {
            const d = val('scholarship_degree');
            const show = {
                'HSC': d !== 'HSC',
                'Graduation': ['MASTERS', 'DIPLOMA', 'OTHERS'].includes(d),
                'Masters': ['MASTERS', 'OTHERS'].includes(d),
            };
            form.querySelectorAll('[data-qual-row]').forEach(tr => {
                const on = show[tr.dataset.qualRow];
                tr.hidden = !on;
                tr.querySelectorAll('input').forEach(i => { i.disabled = !on; });
            });
        };
        form.elements['scholarship_degree']?.addEventListener('change', toggleQualRows);
        toggleQualRows();
        // Year / birth-date consistency (clears itself when fixed)
        function checkYears() {
            const birthYear = Number((val('birth_date') || '').slice(0, 4)) || 0;
            const ssc = Number(val('ssc_year')) || 0;
            const hsc = Number(val('hsc_year')) || 0;
            const thisYear = new Date().getFullYear();
            const set = (name, msg) => { const f = form.elements[name]; if (f) f.setCustomValidity(msg); };
            ['ssc_year', 'hsc_year', 'grad_year', 'other_year'].forEach(n => {
                const y = Number(val(n)) || 0;
                set(n, y && (y < 1980 || y > thisYear + 1) ? 'সাল সঠিক নয় (1980–' + (thisYear + 1) + ')' : '');
            });
            if (birthYear && ssc && ssc - birthYear < 10) set('ssc_year', 'SSC সাল জন্মসালের সাথে মিলছে না');
            if (ssc && hsc && hsc <= ssc) set('hsc_year', 'HSC সাল SSC সালের পরে হতে হবে');
            const bd = form.elements['birth_date'];
            if (bd) bd.setCustomValidity(birthYear && (birthYear < 1970 || birthYear > thisYear - 10) ? 'জন্মসাল সঠিক নয়' : '');
        }
        ['birth_date', 'ssc_year', 'hsc_year', 'grad_year', 'other_year'].forEach(n =>
            form.elements[n]?.addEventListener('input', checkYears));
        checkYears();

        // Re-normalise phone when country code changes
        [['country_code', 'phone'], ['guardian_country_code', 'guardian_phone']].forEach(([c, p]) =>
            form.elements[c]?.addEventListener('change', () => form.elements[p] && formatPhone(form.elements[p])));

        function buildPreview() {
            const g = {};
            Array.from(form.elements).forEach(f => { if (f.name && !(f.name in g)) g[f.name] = f.value; });
            const row = (a, b, c, d) => `<tr><td class="pv-lbl">${a}</td><td>${b}</td>` + (c !== undefined ? `<td class="pv-lbl">${c}</td><td>${d}</td>` : '') + '</tr>';
            const taka = v => v ? esc(v) + ' টাকা' : '';
            const photo = document.getElementById('previewImg');
            const photoHtml = photo && photo.getAttribute('src') ? `<img src="${photo.src}" alt="">` : '<div style="padding-top:44px;">ছবি</div>';
            const exams = [['SSC / সমমান', 'ssc', 'ssc_gpa'], ['HSC / সমমান', 'hsc', 'hsc_gpa'], ['স্নাতক / ডিগ্রি', 'grad', 'grad_cgpa'], ['স্নাতকোত্তর / অন্যান্য', 'other', 'other_cgpa']]
                .filter(([, p, gp]) => p === 'ssc' || (!form.elements[p + '_roll']?.disabled && (val(p + '_roll') || val(p + '_institute') || val(gp))))
                .map(([l, p, gp]) => `<tr><td>${l}</td><td align="center">${esc(val(p + '_roll'))}</td><td align="center">${up(val(p + '_board'))}</td><td>${up(val(p + '_institute'))}</td><td align="center">${esc(val(p + '_year'))}</td><td align="center"><b>${esc(val(gp))}</b></td></tr>`).join('');
            const prev = [0, 1, 2].map(i => ({y: val(`prev_scholarships[${i}][year]`), t: val(`prev_scholarships[${i}][times]`), a: val(`prev_scholarships[${i}][amount]`)}))
                .filter(r => r.y || r.t || r.a)
                .map(r => `<tr><td align="center">${esc(r.y)}</td><td align="center">${esc(r.t)}</td><td align="center">${esc(r.a)}</td></tr>`).join('')
                || '<tr><td colspan="3" align="center">—</td></tr>';
            const perm = document.getElementById('permFullAddress')?.value || val('permanent_address');
            const pres = document.getElementById('presFullAddress')?.value || val('present_address');
            const phone = (form.elements['country_code']?.value || '') + ' ' + val('phone');
            const parentIncome = (n) => val(n) ? ' <span style="white-space:nowrap">(আয়: ' + esc(val(n)) + ')</span>' : '';
            const parentPhone = (n) => val(n) ? ' — ' + esc(val(n)) : '';
            const sig = t => `<td style="border:0;width:42%;padding-top:30px;"><div class="pv-sign">${t}</div></td>`;

            document.getElementById('a4Preview').innerHTML = `
                <table class="pv-head"><tr>
                    <td style="width:90px;"></td>
                    <td align="center"><div style="font-weight:700;font-size:13px;">জয়ী শিক্ষাবৃত্তি</div>
                        <div style="font-size:26px;font-weight:700;letter-spacing:3px;text-transform:uppercase;line-height:1.15;">Scholarship</div>
                        <div style="font-size:10px;">Session 2026-2027 &nbsp;|&nbsp; ${esc(form.elements['scholarship_degree']?.selectedOptions[0]?.text || '')}</div></td>
                    <td align="right" style="width:98px;"><div class="pv-photo">${photoHtml}</div></td>
                </tr></table>

                <div class="pv-sec">ব্যক্তিগত তথ্য</div>
                <table>
                    ${row('আবেদনকারীর নাম', '<b>' + up(val('name')) + '</b>', 'মোবাইল নম্বর', '<b>' + esc(phone) + '</b>')}
                    ${row('পিতার নাম', up(val('father_name')) + parentIncome('father_income'), 'মাতার নাম', up(val('mother_name')) + parentIncome('mother_income'))}
                    ${row('জন্ম তারিখ', esc(val('birth_date')), 'লিঙ্গ / ধর্ম', esc(val('gender') + ' / ' + val('religion')))}
                    ${row('অভিভাবক', up(val('guardian_name')) + parentIncome('guardian_income') + (val('guardian_phone') ? ' (' + esc(val('guardian_phone')) + ')' : ''), 'পারিবারিক আয়', taka(val('annual_income')))}
                </table>

                <div class="pv-sec">একাডেমিক তথ্য</div>
                <table>
                    ${row('বৃত্তির জন্য ডিগ্রি', esc(form.elements['scholarship_degree']?.selectedOptions[0]?.text || ''), 'প্রতিষ্ঠান', '<b>' + up(val('college_name')) + '</b>' + (val('college_code') ? ' (' + esc(val('college_code')) + ')' : ''))}
                    ${row('গ্রুপ', up(val('group')), 'ভর্তি রোল / মেধাক্রম', esc(val('admission_roll')) + (val('merit_position') ? ' / ' + esc(val('merit_position')) : ''))}
                    ${row('বিষয়', up(val('assigned_subject')), 'ভর্তি ফি', taka(val('admission_fee')))}
                    ${row('মাসিক বেতন', taka(val('monthly_fee')), 'হোস্টেল খরচ', taka(val('hostel_cost')))}
                </table>
                <table>
                    <tr><th>পরীক্ষা</th><th>রোল</th><th>বোর্ড</th><th>প্রতিষ্ঠান</th><th>সাল</th><th>GPA</th></tr>
                    ${exams}
                </table>

                <div class="pv-sec">Scholarships</div>
                <table>
                    <tr><th>Year</th><th>Times</th><th>Amount</th></tr>${prev}
                    <tr><td class="pv-lbl">অন্য প্রতিষ্ঠান থেকে বৃত্তি</td><td colspan="2">${esc(val('other_scholarship') || 'No')}${val('other_scholarship_details') ? ' — ' + esc(val('other_scholarship_details')) : ''}</td></tr>
                </table>

                <div class="pv-sec">ঠিকানা</div>
                <table>
                    <tr><td class="pv-lbl">স্থায়ী ঠিকানা</td><td>${esc(perm)}</td></tr>
                    <tr><td class="pv-lbl">বর্তমান ঠিকানা</td><td>${pres && pres !== perm ? esc(pres) : 'স্থায়ী ঠিকানার অনুরূপ'}</td></tr>
                </table>

                <table><tr><td style="min-height:40px;"><b>আবেদনের কারণ (অনধিক ৫০ শব্দ):</b><br>${esc(val('scholarship_reason'))}</td></tr></table>

                <div style="font-size:10px;text-align:justify;">আমি <b>${up(val('name'))}</b> ঘোষণা করছি যে, উপরে প্রদত্ত সকল তথ্য ও ছবি সঠিক। কোনো তথ্য মিথ্যা প্রমাণিত হলে বৃত্তি বাতিল হবে।</div>
                <table style="margin-top:4px;"><tr><td style="border:0;width:55%;"></td>${sig('আবেদনকারীর স্বাক্ষর ও তারিখ').replace('42%', '45%')}</tr></table>

                <table style="margin-top:8px;">
                    <tr><th colspan="2" align="left" style="text-align:left;">মন্তব্য (অনধিক ৫০ শব্দ)</th></tr>
                    <tr><td colspan="2" style="height:50px;"></td></tr>
                    <tr><td style="height:70px;vertical-align:bottom;">শিক্ষা প্রতিষ্ঠান প্রধানের স্বাক্ষর, মোবাইল ও সিল</td><td style="vertical-align:bottom;">জনপ্রতিনিধির স্বাক্ষর, মোবাইল ও সিল</td></tr>
                </table>
                <table style="margin-top:6px;"><tr>${sig('পরিচালক (অনুমোদনকারী)')}<td style="border:0;"></td>${sig('কোষাধ্যক্ষ')}</tr></table>`;
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

        function validateStep(index) {
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
