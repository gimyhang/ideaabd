@php
    $formData = $registration->form_data ?? [];
    $campaign = $registration->campaign;
    $isPdf = $isPdf ?? false;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scholarship Form — {{ $registration->registration_number }} — {{ $registration->name }}</title>
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
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            line-height: 1.25;
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
            width: 760px;
            max-width: 100%;
            margin: 0 auto;
            background: #fff;
            padding: 24px 28px;
            border: 1px solid #d1d5db;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
            position: relative;
        }
        
        /* Action buttons bar */
        .action-bar {
            width: 760px;
            max-width: 100%;
            margin: 0 auto 16px auto;
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
        }
        .btn-print {
            background-color: #0f172a;
            color: #fff;
        }
        .btn-print:hover {
            background-color: #1e293b;
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

        /* Top Header 3-box Grid */
        .top-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .top-header-table td {
            vertical-align: top;
            padding: 0;
        }
        .header-box-left {
            width: 33%;
            border: 1.5px solid #000;
            text-align: center;
            padding: 6px 4px 4px 4px;
            height: 155px;
        }
        .header-box-left .inst-title {
            font-size: 13px;
            font-weight: bold;
            line-height: 1.2;
            text-transform: uppercase;
        }
        .header-box-left .sub-title {
            font-size: 10.5px;
            font-weight: bold;
            margin-top: 2px;
        }
        .header-box-left .session-text {
            font-size: 10.5px;
            font-weight: bold;
            margin-top: 2px;
            margin-bottom: 6px;
        }
        .header-box-left .logo-img {
            max-height: 52px;
            max-width: 90px;
            object-fit: contain;
            margin: 0 auto;
            display: block;
        }

        .header-box-mid {
            width: 47%;
            border: 1.5px solid #000;
            border-left: none;
            border-right: none;
            height: 155px;
        }
        .mid-table {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
        }
        .mid-table td, .mid-table th {
            border: 1px solid #000;
            padding: 2.5px 5px;
            font-size: 10px;
        }
        .mid-table .label-cell {
            width: 38%;
            font-size: 9.5px;
        }
        .mid-table .value-cell {
            text-align: center;
            font-weight: bold;
            font-size: 12px;
        }
        .mid-table .official-header {
            text-align: left;
            font-weight: bold;
            font-size: 10.5px;
            padding: 2px 5px;
        }
        .digit-box {
            display: inline-block;
            width: 15px;
            height: 15px;
            border: 1px solid #000;
            margin-right: 2px;
            vertical-align: middle;
            text-align: center;
            line-height: 14px;
            font-size: 10px;
            font-weight: bold;
        }

        .header-box-right {
            width: 20%;
            border: 1.5px solid #000;
            text-align: center;
            padding: 0;
            height: 155px;
            background: #fafafa;
        }
        .student-photo {
            width: 100%;
            height: 100%;
            max-height: 155px;
            object-fit: cover;
            display: block;
        }
        .photo-placeholder {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            font-size: 9.5px;
            text-align: center;
            padding: 6px;
            border: 1px dashed #94a3b8;
        }

        /* Form Title Bar */
        .form-banner-title {
            font-size: 12px;
            font-weight: bold;
            margin: 6px 0;
            text-align: left;
            letter-spacing: -0.2px;
        }

        /* Standard Form Tables */
        .form-grid-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .form-grid-table td, .form-grid-table th {
            border: 1px solid #000;
            padding: 3.5px 6px;
            font-size: 10.5px;
            vertical-align: middle;
        }
        .th-label {
            font-weight: normal;
            color: #000;
            white-space: nowrap;
        }
        .td-val {
            font-weight: normal;
            color: #000;
        }
        .td-val-bold {
            font-weight: bold;
        }

        /* Reason Box */
        .reason-container {
            border: 1px solid #000;
            padding: 5px 8px;
            margin-bottom: 6px;
            background-color: #fff;
        }
        .reason-title {
            font-weight: bold;
            font-size: 10.5px;
            margin-bottom: 3px;
        }
        .reason-body {
            font-size: 10.5px;
            line-height: 1.35;
            color: #111;
            text-align: justify;
        }

        /* Declaration */
        .declaration-text {
            font-size: 9.8px;
            line-height: 1.3;
            text-align: justify;
            margin: 6px 0 24px 0;
        }

        /* Signatures */
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .signatures-table td {
            width: 50%;
            padding: 10px 14px 18px 14px;
            text-align: center;
            vertical-align: bottom;
        }
        .sig-line {
            border-top: 1px solid #000;
            margin: 0 10px 4px 10px;
        }
        .sig-label {
            font-size: 10px;
            font-weight: normal;
        }

        /* Footer line */
        .footer-line {
            border-top: 1px solid #000;
            padding-top: 4px;
            margin-top: 8px;
            font-size: 9px;
            color: #000;
            display: flex;
            justify-content: space-between;
        }
        
        .badge-selected-status {
            background-color: #15803d;
            color: #fff;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
    </style>
</head>
<body>

    {{-- Action Bar for Screen / Printing --}}
    @if(!$isPdf)
        <div class="action-bar no-print">
            <div>
                <a href="{{ url('/') }}" class="btn-action btn-back">← Home</a>
                @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->role === 'admin'))
                    <a href="{{ route('admin.event-campaigns.show', $campaign->id ?? 1) }}" class="btn-action btn-back">← Admin Panel</a>
                @endif
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                @if($registration->status === 'selected' || !empty($formData['is_scholarship_awarded']))
                    <span class="badge-selected-status">★ Scholarship Awarded</span>
                @endif
                <button type="button" onclick="window.print()" class="btn-action btn-print">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Print Form
                </button>
                <a href="{{ route('event.registration.pdf', $registration->registration_number) }}" class="btn-action btn-pdf">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Download PDF
                </a>
            </div>
        </div>
    @endif

    <div class="page-container">

        {{-- 1. TOP HEADER (3-Column exact grid) --}}
        <table class="top-header-table">
            <tr>
                {{-- Left Box: Institution / Organization / Emblem --}}
                <td class="header-box-left">
                    <div class="inst-title">JOYEE SHIKKHA BRITTI</div>
                    <div class="sub-title">1st year Bachelor's (Hons)</div>
                    <div class="session-text">Scholarship Session: 2026-2027</div>
                    @if(file_exists(public_path('images/logo.png')))
                        <img src="{{ $isPdf ? public_path('images/logo.png') : asset('images/logo.png') }}" class="logo-img" alt="Logo">
                    @elseif(file_exists(public_path('images/logo-mark.svg')))
                        <img src="{{ $isPdf ? public_path('images/logo-mark.svg') : asset('images/logo-mark.svg') }}" class="logo-img" alt="Logo">
                    @else
                        <div style="font-weight: bold; font-size: 16px; color: #0f3a68; margin-top: 10px;">IDEA</div>
                    @endif
                </td>

                {{-- Middle Box: Group, Roll, Merit, Official Box --}}
                <td class="header-box-mid">
                    <table class="mid-table">
                        <tr>
                            <td class="label-cell">Group</td>
                            <td class="value-cell" style="font-size: 13px; letter-spacing: 0.5px;">
                                {{ strtoupper($formData['group'] ?? 'HUMANITIES') }}
                            </td>
                        </tr>
                        <tr>
                            <td class="label-cell">Admission Roll</td>
                            <td class="value-cell" style="font-size: 14px;">
                                {{ $formData['admission_roll'] ?? preg_replace('/[^0-9]/', '', $registration->registration_number) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="label-cell" style="line-height: 1.1;">
                                Merit Position in College<br>
                                <span style="font-size: 8px;">(in First Merit List)</span>
                            </td>
                            <td class="value-cell">
                                {{ $formData['merit_position'] ?? '1682' }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" class="official-header">For Official Use Only</td>
                        </tr>
                        <tr>
                            <td class="label-cell">Class Roll</td>
                            <td style="padding: 2px 4px;">
                                @php
                                    $rawDigits = str_pad(substr(preg_replace('/[^0-9]/', '', $registration->registration_number), -6), 6, '0', STR_PAD_LEFT);
                                    $digits = str_split($rawDigits);
                                @endphp
                                @foreach($digits as $d)
                                    <span class="digit-box">{{ $d }}</span>
                                @endforeach
                            </td>
                        </tr>
                        <tr>
                            <td class="label-cell">Admission Date</td>
                            <td style="font-size: 10.5px; padding-left: 6px;">
                                {{ $registration->created_at->format('d/m/Y') }}
                            </td>
                        </tr>
                    </table>
                </td>

                {{-- Right Box: Passport Size Student Photo --}}
                <td class="header-box-right">
                    @php
                        $photoPath = $formData['student_photo'] ?? null;
                    @endphp
                    @if($photoPath && file_exists(public_path('storage/' . $photoPath)))
                        <img src="{{ $isPdf ? public_path('storage/' . $photoPath) : asset('storage/' . $photoPath) }}" class="student-photo" alt="Student Photo">
                    @elseif($photoPath && file_exists(storage_path('app/public/' . $photoPath)))
                        <img src="{{ $isPdf ? storage_path('app/public/' . $photoPath) : asset('storage/' . $photoPath) }}" class="student-photo" alt="Student Photo">
                    @else
                        <div class="photo-placeholder">
                            <div>
                                <div style="font-size: 20px; margin-bottom: 2px;">👤</div>
                                <div>PASSPORT PHOTO</div>
                            </div>
                        </div>
                    @endif
                </td>
            </tr>
        </table>

        {{-- 2. DOCUMENT TITLE BANNER --}}
        <div class="form-banner-title">
            Education Scholarship Application Form - First Merit List (STUDENT'S COPY)
        </div>

        {{-- 3. INSTITUTION & SUBJECT TABLE --}}
        <table class="form-grid-table">
            <tr>
                <td class="th-label" style="width: 16%;">College Name</td>
                <td class="td-val" style="width: 44%;">{{ $formData['college_name'] ?? ($registration->institution_or_org ?: 'Dinajpur Govt. College') }}</td>
                <td class="th-label" style="width: 18%;">College Code</td>
                <td class="td-val" style="width: 22%;">{{ $formData['college_code'] ?? '3401' }}</td>
            </tr>
            <tr>
                <td class="th-label">Assigned Subject</td>
                <td class="td-val">{{ $formData['assigned_subject'] ?? 'ENGLISH (1101)' }}</td>
                <td class="th-label">Previous Subject</td>
                <td class="td-val">{{ $formData['previous_subject'] ?? 'ECONOMICS (2201)' }}</td>
            </tr>
            <tr>
                <td class="th-label">Subject Choice</td>
                <td colspan="3" class="td-val" style="font-size: 9.5px;">
                    {{ $formData['subject_choice'] ?? 'ENGLISH, ECONOMICS, BANGLA, SOCIOLOGY, POLITICAL SCIENCE, HISTORY, PHILOSOPHY' }}
                </td>
            </tr>
        </table>

        {{-- 4. PERSONAL & GUARDIAN DETAILS TABLE --}}
        <table class="form-grid-table">
            <tr>
                <td class="th-label" style="width: 16%;">Name</td>
                <td class="td-val-bold" style="width: 44%;">{{ strtoupper($registration->name) }}</td>
                <td class="th-label" style="width: 18%;">Gender</td>
                <td class="td-val" style="width: 22%;">{{ $formData['gender'] ?? 'Female' }}</td>
            </tr>
            <tr>
                <td class="th-label">Father's Name</td>
                <td class="td-val">{{ strtoupper($formData['father_name'] ?? 'TAPOS KUMAR ROY') }}</td>
                <td class="th-label">Religion</td>
                <td class="td-val">{{ $formData['religion'] ?? 'Hinduism' }}</td>
            </tr>
            <tr>
                <td class="th-label">Mother's Name</td>
                <td class="td-val">{{ strtoupper($formData['mother_name'] ?? 'RADHA RANI ROY') }}</td>
                <td class="th-label">Nationality</td>
                <td class="td-val">{{ strtoupper($formData['nationality'] ?? 'BANGLADESHI') }}</td>
            </tr>
            <tr>
                <td class="th-label">Birth Date</td>
                <td class="td-val">
                    {{ $formData['birth_date'] ?? '15/11/2002' }}
                    <span style="display: inline-block; margin-left: 12px; margin-right: 4px; font-weight: normal;">Mobile No.</span>
                    <span style="font-weight: bold;">{{ $registration->phone }}</span>
                </td>
                <td class="th-label">Marital Status</td>
                <td class="td-val">{{ $formData['marital_status'] ?? 'Unmarried' }}</td>
            </tr>
            <tr>
                <td class="th-label">Guardian's Name</td>
                <td class="td-val">{{ strtoupper($formData['guardian_name'] ?? ($formData['father_name'] ?? 'TAPOS KUMAR ROY')) }}</td>
                <td class="th-label">Guardian's Mobile No.</td>
                <td class="td-val">{{ $formData['guardian_phone'] ?? $registration->phone }}</td>
            </tr>
            <tr>
                <td colspan="3" class="th-label">Father's/Mother's/Guardian's Annual Income (Tk)</td>
                <td class="td-val">{{ $formData['annual_income'] ?? '50000' }}</td>
            </tr>
        </table>

        {{-- 5. ACADEMIC QUALIFICATIONS TABLE --}}
        <table class="form-grid-table">
            <tr>
                <td class="th-label" style="width: 18%;">SSC/ Equivalent</td>
                <td class="th-label" style="width: 6%;">Roll</td>
                <td class="td-val" style="width: 14%;">{{ $formData['ssc_roll'] ?? '251351' }}</td>
                <td class="th-label" style="width: 6%;">Board</td>
                <td class="td-val" style="width: 12%;">{{ strtoupper($formData['ssc_board'] ?? 'DINAJPUR') }}</td>
                <td class="th-label" style="width: 8%;">Institute</td>
                <td class="td-val" style="width: 22%;">{{ strtoupper($formData['ssc_institute'] ?? 'Dinajpur Zilla School') }}</td>
                <td class="th-label" style="width: 5%;">Year</td>
                <td class="td-val" style="width: 5%;">{{ $formData['ssc_year'] ?? '2019' }}</td>
                <td class="th-label" style="width: 5%;">GPA</td>
                <td class="td-val" style="width: 5%;">{{ $formData['ssc_gpa'] ?? '4.67' }}</td>
            </tr>
            <tr>
                <td class="th-label">HSC/ Equivalent</td>
                <td class="th-label">Roll</td>
                <td class="td-val">{{ $formData['hsc_roll'] ?? '342161' }}</td>
                <td class="th-label">Board</td>
                <td class="td-val">{{ strtoupper($formData['hsc_board'] ?? 'DINAJPUR') }}</td>
                <td class="th-label">Institute</td>
                <td class="td-val">{{ strtoupper($formData['hsc_institute'] ?? ($formData['college_name'] ?? 'Dinajpur Govt. College')) }}</td>
                <td class="th-label">Year</td>
                <td class="td-val">{{ $formData['hsc_year'] ?? '2021' }}</td>
                <td class="th-label">GPA</td>
                <td class="td-val">{{ $formData['hsc_gpa'] ?? '4.67' }}</td>
            </tr>
            @if(!empty($formData['grad_roll']) || !empty($formData['grad_institute']) || !empty($formData['grad_cgpa']))
                <tr>
                    <td class="th-label">Graduation / Degree</td>
                    <td class="th-label">Roll</td>
                    <td class="td-val">{{ $formData['grad_roll'] ?? '-' }}</td>
                    <td class="th-label">Univ</td>
                    <td class="td-val">{{ strtoupper($formData['grad_board'] ?? '-') }}</td>
                    <td class="th-label">Institute</td>
                    <td class="td-val">{{ strtoupper($formData['grad_institute'] ?? '-') }}</td>
                    <td class="th-label">Year</td>
                    <td class="td-val">{{ $formData['grad_year'] ?? '-' }}</td>
                    <td class="th-label">CGPA</td>
                    <td class="td-val">{{ $formData['grad_cgpa'] ?? '-' }}</td>
                </tr>
            @endif
            @if(!empty($formData['other_roll']) || !empty($formData['other_institute']) || !empty($formData['other_cgpa']))
                <tr>
                    <td class="th-label">Masters / Other</td>
                    <td class="th-label">Roll</td>
                    <td class="td-val">{{ $formData['other_roll'] ?? '-' }}</td>
                    <td class="th-label">Univ</td>
                    <td class="td-val">{{ strtoupper($formData['other_board'] ?? '-') }}</td>
                    <td class="th-label">Institute</td>
                    <td class="td-val">{{ strtoupper($formData['other_institute'] ?? '-') }}</td>
                    <td class="th-label">Year</td>
                    <td class="td-val">{{ $formData['other_year'] ?? '-' }}</td>
                    <td class="th-label">CGPA</td>
                    <td class="td-val">{{ $formData['other_cgpa'] ?? '-' }}</td>
                </tr>
            @endif
        </table>

        {{-- 6. ADDRESS DETAILS TABLE --}}
        <table class="form-grid-table">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <div style="font-weight: bold; text-align: center; margin-bottom: 2px;">Permanent Address</div>
                    <div style="min-height: 28px; line-height: 1.25;">
                        {{ strtoupper($formData['permanent_address'] ?? ($registration->address ?: 'VILL- MUSHIDHAT, POST- SETABGANJ, THANA- BOCHAGANJ, DIST- DINAJPUR')) }}
                    </div>
                </td>
                <td style="width: 50%; vertical-align: top;">
                    <div style="font-weight: bold; text-align: center; margin-bottom: 2px;">Present Address</div>
                    <div style="min-height: 28px; line-height: 1.25;">
                        {{ strtoupper($formData['present_address'] ?? ($registration->address ?: 'VILL- MUSHIDHAT, POST- SETABGANJ, THANA- BOCHAGANJ, DIST- DINAJPUR')) }}
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <span style="font-weight: normal;">District</span> &nbsp;
                    <span style="font-weight: normal;">{{ $formData['permanent_district'] ?? ($registration->district ?: 'Dinajpur') }}</span>
                </td>
                <td>
                    <span style="font-weight: normal;">District</span> &nbsp;
                    <span style="font-weight: normal;">{{ $formData['present_district'] ?? ($registration->district ?: 'Dinajpur') }}</span>
                </td>
            </tr>
        </table>

        {{-- 7. REASON FOR SCHOLARSHIP (50 Words requirement) --}}
        <div class="reason-container">
            <div class="reason-title">Reason for Scholarship Application (Max 50 Words):</div>
            <div class="reason-body">
                {{ $formData['scholarship_reason'] ?? 'I am applying for this educational scholarship to support my higher secondary and graduation academic expenses. Coming from an underprivileged rural family with limited income, this scholarship assistance will help me purchase required course books, pay institutional fees, and continue my bachelor studies successfully.' }}
            </div>
        </div>

        {{-- 8. VIVA & SELECTION ASSESSMENT EVALUATION (50 MARKS) --}}
        <div style="margin-top: 4px; margin-bottom: 4px;">
            <div style="font-weight: bold; font-size: 10px; margin-bottom: 2px;">
                Viva & Selection Assessment Evaluation (Total 50 Marks) — ভাইভাতে অংশগ্রহণ মূল্যায়ন
            </div>
            <table class="form-grid-table" style="text-align: center; margin-bottom: 4px;">
                <thead>
                    <tr style="background: #f8fafc; font-size: 8.5px; font-weight: bold;">
                        <th style="width: 12%;">উপস্থিতি<br><span style="font-size: 7.5px; font-weight: normal;">Attendance (01)</span></th>
                        <th style="width: 12%;">কাগজপত্র<br><span style="font-size: 7.5px; font-weight: normal;">Documents (02)</span></th>
                        <th style="width: 15%;">পোশাক পরিচ্ছেদ<br><span style="font-size: 7.5px; font-weight: normal;">Attire (10)</span></th>
                        <th style="width: 15%;">ফিউচার প্লান<br><span style="font-size: 7.5px; font-weight: normal;">Future Plan (10)</span></th>
                        <th style="width: 15%;">পাঠ অভ্যাস<br><span style="font-size: 7.5px; font-weight: normal;">Reading (10)</span></th>
                        <th style="width: 16%;">স্বেচ্ছাসেবী অভিজ্ঞতা<br><span style="font-size: 7.5px; font-weight: normal;">Volunteer (10)</span></th>
                        <th style="width: 15%;">উপস্থিত বুদ্ধিমত্তা<br><span style="font-size: 7.5px; font-weight: normal;">IQ / Mind (07)</span></th>
                        <th style="width: 15%; background: #f1f5f9;">সর্বমোট প্রাপ্ত নম্বর<br><span style="font-size: 7.5px; font-weight: normal;">Total (50)</span></th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="height: 22px; font-weight: bold; font-size: 10px;">
                        <td>{{ !empty($formData['viva_attendance']) ? $formData['viva_attendance'] : '' }}</td>
                        <td>{{ !empty($formData['viva_documents']) ? $formData['viva_documents'] : '' }}</td>
                        <td>{{ !empty($formData['viva_attire']) ? $formData['viva_attire'] : '' }}</td>
                        <td>{{ !empty($formData['viva_future_plan']) ? $formData['viva_future_plan'] : '' }}</td>
                        <td>{{ !empty($formData['viva_reading_habit']) ? $formData['viva_reading_habit'] : '' }}</td>
                        <td>{{ !empty($formData['viva_volunteer_exp']) ? $formData['viva_volunteer_exp'] : '' }}</td>
                        <td>{{ !empty($formData['viva_iq']) ? $formData['viva_iq'] : '' }}</td>
                        <td style="background: #fafafa; font-size: 11px; color: #0f3a68;">
                            {{ !empty($formData['viva_total']) ? $formData['viva_total'] . ' / 50' : '' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- 9. DECLARATION STATEMENT --}}
        <div class="declaration-text" style="margin-top: 4px; margin-bottom: 18px;">
            I, <strong>{{ strtoupper($registration->name) }}</strong>, do hereby declare that the above mentioned information and photo are correct. If any information provided by me is found false, the scholarship authority reserves the right to cancel my scholarship. I shall be obliged to obey the rules and regulations of the scholarship authority as well as my college and to pay all the required fees.
        </div>

        {{-- 9. SIGNATURES SECTION (4 Signatures across 2 rows) --}}
        <table class="signatures-table">
            <tr>
                <td>
                    <div class="sig-line"></div>
                    <div class="sig-label">Signature of the Student & Date</div>
                </td>
                <td>
                    <div class="sig-line"></div>
                    <div class="sig-label">Signature of Father/Mother/Guardian & Date</div>
                </td>
            </tr>
            <tr>
                <td style="padding-top: 22px;">
                    <div class="sig-line"></div>
                    <div class="sig-label">Seal, Signature of Dept. Head & Date</div>
                </td>
                <td style="padding-top: 22px;">
                    <div class="sig-line"></div>
                    <div class="sig-label">Seal, Signature of College Principal & Date</div>
                </td>
            </tr>
        </table>

        {{-- 10. FOOTER TIMESTAMP --}}
        <div class="footer-line">
            <div>Generated on : {{ $registration->created_at->format('D M d H:i:s T Y') }}</div>
            <div>Ref: #{{ $registration->registration_number }}</div>
        </div>

    </div>

</body>
</html>
