@php
    $formData = $registration->form_data ?? [];
    $campaign = $registration->campaign;
    $isPdf = $isPdf ?? false;

    $fd = fn ($key) => trim((string) ($formData[$key] ?? ''));
    $up = fn ($v) => strtoupper((string) $v);

    $logoRel = file_exists(public_path('images/logo.png')) ? 'images/logo.png' : (file_exists(public_path('images/logo-mark.svg')) ? 'images/logo-mark.svg' : null);

    $photoUrl = null;
    $photoPath = $fd('student_photo');
    if ($photoPath) {
        if (file_exists(public_path('storage/' . $photoPath))) {
            $photoUrl = $isPdf ? public_path('storage/' . $photoPath) : asset('storage/' . $photoPath);
        } elseif (file_exists(storage_path('app/public/' . $photoPath))) {
            $photoUrl = $isPdf ? storage_path('app/public/' . $photoPath) : asset('storage/' . $photoPath);
        }
    }

    $collegeName = $fd('college_name') ?: ($registration->institution_or_org ?: '');
    // Place names are stored in Bengali; print them with their English spelling when one is known.
    $geoEn = \Illuminate\Support\Facades\Cache::rememberForever('geo_en_map_' . @filemtime(public_path('js/bd-geo-en.js')), function () {
        $raw = @file_get_contents(public_path('js/bd-geo-en.js')) ?: '';
        $json = trim(preg_replace('/^.*?window\.BD_GEO_EN\s*=\s*/s', '', $raw), " 
	;");
        return json_decode($json, true) ?: [];
    });
    $permAddr = $fd('permanent_address') ?: (string) $registration->address;
    $presAddr = $fd('present_address');
    foreach (['perm' => 'permAddr', 'pres' => 'presAddr'] as $pfx => $var) {
        $city = $fd($pfx . '_city_corporation');
        $city = $geoEn[$city] ?? $city;
        if ($city !== '' && $$var !== '' && !str_contains($$var, $city)) { $$var = $city . ', ' . $$var; }
    }
    $sameAddr = $presAddr === '' || $presAddr === $permAddr;
    $permDistrict = $fd('permanent_district') ?: (string) $registration->district;
    $presDistrict = $fd('present_district');
    $permDistrict = $geoEn[$permDistrict] ?? $permDistrict;
    $presDistrict = $geoEn[$presDistrict] ?? $presDistrict;

    $guardianPhone = $fd('guardian_phone');
    if ($guardianPhone === $registration->phone) {
        $guardianPhone = '';
    }

    // Academic exam rows: label => [prefix, gpa key]
    $examRows = [
        'SSC'     => ['ssc', 'ssc_gpa', 'GPA'],
        'HSC'     => ['hsc', 'hsc_gpa', 'GPA'],
        'Honours' => ['grad', 'grad_cgpa', 'CGPA'],
        'Masters' => ['other', 'other_cgpa', 'CGPA'],
    ];
    $prevSch = collect($formData['prev_scholarships'] ?? [])->filter(fn ($r) => is_array($r) && (trim($r['year'] ?? '') !== '' || trim($r['amount'] ?? '') !== ''))->values();
    $hasViva = !empty($formData['viva_total']);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scholarship — {{ $registration->registration_number }} — {{ $registration->name }}</title>
    @if(!$isPdf)
        <link href="{{ asset('css/kalpurush-font.css') }}" rel="stylesheet">
    @endif
    <style>
        @font-face {
            font-family: 'Kalpurush';
            src: url('{{ $isPdf ? public_path("fonts/kalpurush/kalpurush.ttf") : asset("fonts/kalpurush/kalpurush.ttf") }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        @page {
            size: 8.5in 11in;
            margin: 8mm 10mm;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        body {
            font-family: 'Kalpurush', 'SolaimanLipi', Arial, sans-serif;
            font-size: 14px;
            line-height: 1.3;
            color: #000;
            background: #f4f6f9;
            margin: 0;
            padding: 16px 0;
        }
        .page-container {
            width: 8.5in;
            max-width: 100%;
            min-height: 11in;
            margin: 0 auto;
            background: #fff;
            padding: 10mm 11mm;
            border: 1px solid #d1d5db;
            box-shadow: 0 4px 14px rgba(0, 0, 0, .08);
        }
        @media print {
            body { background: #fff !important; padding: 0 !important; }
            .no-print { display: none !important; }
            .page-container {
                width: 100% !important; min-height: 0 !important;
                margin: 0 !important; padding: 0 !important;
                border: none !important; box-shadow: none !important;
            }
        }
        @if($isPdf)
            body { background: #fff; padding: 0; }
            .page-container { width: 100%; min-height: 0; margin: 0; padding: 0; border: none; box-shadow: none; }
        @endif

        .action-bar {
            width: 8.5in; max-width: 100%; margin: 0 auto 12px;
            display: flex; align-items: center; justify-content: space-between; gap: 10px;
        }
        .btn-action {
            display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px;
            font-size: 13px; font-weight: 600; border-radius: 6px; text-decoration: none;
            cursor: pointer; border: 1px solid transparent; font-family: inherit;
        }
        .btn-print { background: #0f172a; color: #fff; }
        .btn-pdf { background: #dc2626; color: #fff; }
        .btn-back { background: #f1f5f9; color: #334155; border-color: #cbd5e1; }
        .badge-selected-status {
            background: #15803d; color: #fff; padding: 3px 8px; border-radius: 4px;
            font-size: 11px; font-weight: bold;
        }

        /* Header */
        .head-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .head-table td { vertical-align: middle; padding: 0; }
        .head-logo { width: 90px; text-align: left; }
        .head-logo img { max-height: 60px; max-width: 86px; }
        .head-title { text-align: center; }
        .head-title .org { font-size: 16px; font-weight: bold; }
        .head-title .form-title { font-size: 29px; font-weight: bold; letter-spacing: 3px; line-height: 1.15; text-transform: uppercase; }
        .head-title .meta { font-size: 13px; margin-top: 2px; }
        .head-photo { width: 98px; text-align: right; }
        .photo-box {
            width: 90px; height: 108px; border: 1.5px solid #000; display: inline-block;
            overflow: hidden; text-align: center; font-size: 12px; color: #64748b;
        }
        .photo-box img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .photo-box .ph { padding-top: 44px; }

        /* Sections */
        .sec-title {
            font-size: 14px; font-weight: bold; background: #e5e7eb; border: 1px solid #000;
            border-bottom: none; padding: 3px 6px;
        }
        .grid { width: 100%; border-collapse: collapse; margin-bottom: 7px; page-break-inside: avoid; }
        .grid tr { page-break-inside: avoid; }
        .sec-title { page-break-after: avoid; }
        .pb { page-break-before: always; break-before: page; height: 0; margin: 0; padding: 0; }
        .reason, .declaration, .remark-cols, .sig-table, .footer-line { page-break-inside: avoid; }
        .grid td, .grid th { border: 1px solid #000; padding: 4px 5px; font-size: 13.5px; vertical-align: middle; }
        .grid th { background: #f3f4f6; font-weight: bold; text-align: center; font-size: 13px; }
        .lbl { white-space: nowrap; width: 17%; }
        .val-b { font-weight: bold; }

        .reason { border: 1px solid #000; padding: 4px 7px; margin-bottom: 6px; min-height: 40px; }
        .reason-body { text-align: justify; font-size: 13.5px; }
        .declaration { font-size: 13px; text-align: justify; margin: 4px 0 0; }

        /* Signatures */
        .sig-line { border-top: 1px solid #000; margin-top: 54px; padding-top: 2px; font-size: 13px; text-align: center; }
        .sig-table { width: 100%; border-collapse: collapse; }
        .sig-table td { vertical-align: bottom; }

        .remark-box { border: 1px solid #000; margin-top: 8px; }
        .remark-head { padding: 3px 7px; font-size: 13.5px; font-weight: bold; border-bottom: 1px solid #000; background: #f3f4f6; }
        .remark-lines { height: 50px; padding: 0 7px; background-image: repeating-linear-gradient(transparent, transparent 15px, #9ca3af 15px, #9ca3af 16px); }
        .remark-cols { width: 100%; border-collapse: collapse; border: 1px solid #000; margin-top: 8px; }
        .remark-cols > tbody > tr > td, .remark-cols > tr > td { width: 50%; vertical-align: top; padding: 0; }
        .remark-cols td + td { border-left: 1px solid #000; }
        .remark-cols .remark-lines { height: 130px; }
        .remark-sign { padding: 4px 8px 5px; font-size: 13px; border-top: 1px solid #000; }
        .remark-sigs { width: 100%; border-collapse: collapse; border-top: 1px solid #000; }
        .remark-sigs td { width: 50%; padding: 4px 8px 5px; vertical-align: top; font-size: 13px; }
        .remark-sigs td + td { border-left: 1px solid #000; }
        .sig-space { height: 64px; }
        .fill-line { border-bottom: 1px dotted #000; display: inline-block; width: 60%; height: 11px; vertical-align: bottom; }
        .seal-box { float: right; width: 52px; height: 52px; border: 1px dashed #6b7280; border-radius: 50%; text-align: center; font-size: 11px; color: #6b7280; padding-top: 20px; }

        .footer-line { border-top: 1px solid #000; margin-top: 8px; padding-top: 3px; font-size: 12px; }
        .footer-line .r { float: right; }
    </style>
</head>
<body>

@if(!$isPdf)
    <div class="action-bar no-print">
        <div>
            <a href="{{ url('/') }}" class="btn-action btn-back">← Home</a>
            @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->role === 'admin'))
                <a href="{{ route('admin.event-campaigns.show', $campaign->id ?? 1) }}" class="btn-action btn-back">← Admin Panel</a>
            @endif
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
            @if($registration->status === 'selected' || !empty($formData['is_scholarship_awarded']))
                <span class="badge-selected-status">★ Scholarship awarded</span>
            @endif
            <button type="button" onclick="window.print()" class="btn-action btn-print">Print</button>
            <a href="{{ route('event.registration.pdf', $registration->registration_number) }}" class="btn-action btn-pdf">Download PDF</a>
        </div>
    </div>
@endif

<div class="page-container">

    {{-- Header: logo | title | photo --}}
    <table class="head-table">
        <tr>
            <td class="head-logo">
                @if($logoRel)
                    <img src="{{ $isPdf ? public_path($logoRel) : asset($logoRel) }}" alt="Logo">
                @endif
            </td>
            <td class="head-title">
                <div class="org">Joyee Shikkhabritti</div>
                <div class="form-title">Scholarship</div>
                <div class="meta" style="margin-bottom:2px;">Session 2027</div>
                <div class="meta">
                    Application No: <strong>{{ $registration->registration_number }}</strong>
                    &nbsp;|&nbsp; Date: {{ $registration->created_at->format('d/m/Y') }}
                </div>
            </td>
            <td class="head-photo">
                <div class="photo-box">
                    @if($photoUrl)
                        <img src="{{ $photoUrl }}" alt="Photo">
                    @else
                        <div class="ph">Photo</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- Applicant --}}
    <div class="sec-title">1. Applicant</div>
    <table class="grid">
        <tr>
            <td class="lbl">Name</td>
            <td class="val-b" style="width:33%;">{{ $up($registration->name) }}</td>
            <td class="lbl">Mobile</td>
            <td class="val-b">{{ $registration->phone }}</td>
        </tr>
        <tr>
            <td class="lbl">Date of Birth</td>
            <td>{{ $fd('birth_date') }}</td>
            <td class="lbl">Gender / Religion</td>
            <td>{{ trim($fd('gender') . ($fd('religion') ? ' / ' . $fd('religion') : ''), ' /') }}</td>
        </tr>
        <tr>
            <td class="lbl">NID / Birth Reg.</td>
            <td>{{ $fd('national_id') }}</td>
            <td class="lbl">Email</td>
            <td>{{ $fd('email') ?: $registration->email }}</td>
        </tr>
        @if($fd('marital_status') !== '' || $fd('nationality') !== '')
            <tr>
                <td class="lbl">Marital Status</td>
                <td>{{ $fd('marital_status') }}</td>
                <td class="lbl">Nationality</td>
                <td>{{ $up($fd('nationality')) }}</td>
            </tr>
        @endif
        @if($fd('disability') !== '')
            @php
                $disabilityTypes = ['Visual' => 'Visual impairment', 'Hearing' => 'Hearing impairment', 'Speech' => 'Speech impairment', 'Physical' => 'Physical / Mobility', 'Intellectual' => 'Intellectual', 'Autism' => 'Autism / Neurodevelopmental', 'Multiple' => 'Multiple', 'Other' => 'Other'];
            @endphp
            <tr>
                <td class="lbl">Disability</td>
                <td colspan="3">{{ $fd('disability') }}@if($fd('disability') === 'Yes' && $fd('disability_type') !== '') — {{ $disabilityTypes[$fd('disability_type')] ?? $fd('disability_type') }}@endif @if($fd('disability_details') !== '') ({{ $fd('disability_details') }})@endif</td>
            </tr>
        @endif
    </table>

    {{-- Parents & guardian --}}
    <div class="sec-title">2. Parents &amp; Guardian</div>
    <table class="grid">
        @foreach(['father' => "Father", 'mother' => "Mother"] as $pk => $pl)
            @php $gone = $fd($pk . '_status') === 'Deceased'; @endphp
            <tr>
                <td class="lbl">{{ $pl }}</td>
                <td style="width:33%;">{{ $up($fd($pk . '_name')) }}@if($fd($pk . '_status') !== '') <span style="white-space:nowrap;">({{ $fd($pk . '_status') }})</span>@endif</td>
                <td class="lbl">{{ $pl }} Occupation</td>
                <td>{{ $gone ? 'Nil' : $fd($pk . '_occupation') }}</td>
            </tr>
            <tr>
                <td class="lbl">{{ $pl }} Income</td>
                <td>{{ !$gone && $fd($pk . '_income') !== '' ? $fd($pk . '_income') . ' Tk' : ($gone ? 'Nil' : '') }}</td>
                <td class="lbl">{{ $pl }} Mobile</td>
                <td>{{ $gone ? 'Nil' : $fd($pk . '_phone') }}</td>
            </tr>
        @endforeach
        <tr>
            <td class="lbl">Guardian</td>
            <td>{{ $up($fd('guardian_name')) }}@if($fd('guardian_relation') !== '') <span style="white-space:nowrap;">({{ $fd('guardian_relation') }})</span>@endif @if($fd('guardian_income') !== '') <span style="white-space:nowrap;">— Income: {{ $fd('guardian_income') }} Tk</span>@endif</td>
            <td class="lbl">Guardian Contact</td>
            <td>{{ $fd('guardian_phone') }}</td>
        </tr>
        <tr>
            <td class="lbl">Annual Family Income</td>
            <td>{{ $fd('annual_income') !== '' ? $fd('annual_income') . ' Tk' : '' }}</td>
            <td class="lbl">Family / Earning Members</td>
            <td>{{ $fd('family_count') }}@if($fd('earning_count') !== '') / {{ $fd('earning_count') }}@endif @if($fd('orphan_status') !== '' && $fd('orphan_status') !== 'None') — {{ $fd('orphan_status') }}@endif</td>
        </tr>
    </table>
    {{-- Academic (all in one place) --}}
    <div class="sec-title">3. Academic</div>
    <table class="grid">
        <tr>
            <td class="lbl">Institute</td>
            <td colspan="3" class="val-b">
                {{ $collegeName }}@if($fd('college_code')) <span style="font-weight:normal;">(Code: {{ $fd('college_code') }})</span>@endif
            </td>
        </tr>
        <tr>
            <td class="lbl">Group</td>
            <td style="width:33%;">{{ $up($fd('group')) }}</td>
            <td class="lbl">Admission Roll / Merit</td>
            <td>{{ $fd('admission_roll') }}@if($fd('merit_position')) / {{ $fd('merit_position') }}@endif</td>
        </tr>
        <tr>
            <td class="lbl">Degree Applied For</td>
            <td>{{ $up($fd('scholarship_degree')) }}</td>
            <td class="lbl">Subject</td>
            <td>{{ $up($fd('assigned_subject')) }}</td>
        </tr>
        <tr>
            <td class="lbl">Admission Fee</td>
            <td>{{ $fd('admission_fee') !== '' ? $fd('admission_fee') . ' Tk' : ($fd('tuition_fee') !== '' ? $fd('tuition_fee') . ' Tk' : '') }}</td>
            <td class="lbl">Monthly Fee</td>
            <td>{{ $fd('monthly_fee') !== '' ? $fd('monthly_fee') . ' Tk' : '' }}</td>
        </tr>
        @if($fd('hostel_cost') !== '' || $fd('current_year') !== '')
            <tr>
                <td class="lbl">Hostel Cost</td>
                <td>{{ $fd('hostel_cost') !== '' ? $fd('hostel_cost') . ' Tk' : '' }}</td>
                <td class="lbl">Current Year</td>
                <td>{{ $fd('current_year') }}</td>
            </tr>
        @endif
    </table>
    <div class="sec-title">4. Educational Qualification</div>
    <table class="grid">
        <thead>
            <tr>
                <th style="width:17%;">Level</th>
                <th style="width:11%;">Roll / Reg</th>
                <th style="width:14%;">Board / University</th>
                <th>Institute</th>
                <th style="width:8%;">Year</th>
                <th style="width:8%;">GPA</th>
            </tr>
        </thead>
        <tbody>
            @foreach($examRows as $label => [$p, $gpaKey, $gpaLabel])
                @php
                    $isCore = in_array($p, ['ssc', 'hsc'], true);
                    $inst = $fd($p . '_institute');
                    // HSC institute equals current college -> avoid repeating it
                    if ($p === 'hsc' && $inst !== '' && $inst === $collegeName) { $inst = 'Same as above'; }
                    $rowHas = $fd($p . '_roll') !== '' || $fd($p . '_institute') !== '' || $fd($gpaKey) !== '';
                @endphp
                @if($isCore || $rowHas)
                    <tr>
                        <td>{{ $label }}</td>
                        <td style="text-align:center;">{{ $fd($p . '_roll') }}</td>
                        <td style="text-align:center;">{{ $up($fd($p . '_board')) }}</td>
                        <td>{{ $up($inst) }}</td>
                        <td style="text-align:center;">{{ $fd($p . '_year') }}</td>
                        <td style="text-align:center;" class="val-b">{{ $fd($gpaKey) }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    {{-- Address --}}
    <div class="sec-title">5. Address</div>
    <table class="grid">
        <tr>
            <td class="lbl">Permanent</td>
            <td>{{ $permAddr }}@if($permDistrict) , District: {{ $permDistrict }}@endif</td>
        </tr>
        <tr>
            <td class="lbl">Present</td>
            <td>
                @if($sameAddr)
                    Same as permanent address
                @else
                    {{ $presAddr }}@if($presDistrict) , District: {{ $presDistrict }}@endif
                @endif
            </td>
        </tr>
    </table>

    {{-- Scholarships --}}
    <div class="sec-title">6. Scholarships</div>
    <table class="grid">
        <tr>
            <td class="lbl" style="width:46%;">Received Joyee Scholarship before?</td>
            <td>{{ $fd('prev_joyee') ?: ($prevSch->isNotEmpty() ? 'Yes' : '—') }}</td>
        </tr>
        <tr>
            <td class="lbl">Scholarship from another organization?</td>
            <td>{{ $fd('other_scholarship') ?: 'No' }}@if($fd('other_scholarship_details')) — {{ $fd('other_scholarship_details') }}@endif</td>
        </tr>
    </table>
    @if($prevSch->isNotEmpty())
        <table class="grid" style="margin-top:-5px;">
            <thead>
                <tr><th>Year</th><th style="width:25%;">Times</th><th style="width:30%;">Amount (Tk)</th></tr>
            </thead>
            <tbody>
                @foreach($prevSch as $r)
                    <tr>
                        <td style="text-align:center;">{{ $r['year'] ?? '' }}</td>
                        <td style="text-align:center;">{{ $r['times'] ?? '' }}</td>
                        <td style="text-align:center;">{{ $r['amount'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Page 2 starts here: 1-6 on the first sheet, 7 onwards on the second --}}
    <div class="pb"></div>

    {{-- Reading --}}
    @if($fd('books_read_count') !== '' || $fd('reading_habit') !== '' || $fd('books_read_names') !== '')
        <div class="sec-title">7. Reading</div>
        <table class="grid">
            <tr>
                <td class="lbl">Original books read</td>
                <td style="width:33%;">{{ $fd('books_read_count') }}</td>
                <td class="lbl">Reading habit</td>
                <td>{{ $fd('reading_habit') === 'I do not read' ? 'Does not read non-academic books' : ($fd('reading_habit') === 'I have read' ? 'Has read non-academic books' : $fd('reading_habit')) }}</td>
            </tr>
            @if($fd('books_read_names') !== '')
                <tr>
                    <td class="lbl">Book names</td>
                    <td colspan="3">{!! nl2br(e($fd('books_read_names'))) !!}</td>
                </tr>
            @endif
        </table>
    @endif
    {{-- Reason --}}
    <div class="reason">
        <div style="font-weight:bold;font-size: 13.5px;">8. Statement (max 50 words):</div>
        <div class="reason-body">{{ $fd('scholarship_reason') }}</div>
    </div>

    {{-- Payment account --}}
    @if($fd('pay_method') !== '')
        <div class="sec-title">9. Payment Account</div>
        <table class="grid">
            <tr>
                <td class="lbl">Method</td>
                <td style="width:33%;">{{ $fd('pay_method') === 'Bank' ? 'Bank Account' : $fd('pay_method') }}</td>
                <td class="lbl">Account Owner</td>
                <td>{{ $fd('pay_account_owner') }}</td>
            </tr>
            <tr>
                <td class="lbl">Holder Name</td>
                <td>{{ $up($fd('pay_account_name')) }}</td>
                <td class="lbl">{{ $fd('pay_method') === 'Bank' ? 'Account No.' : 'Wallet No.' }}</td>
                <td class="val-b">{{ $fd('pay_account_no') }}</td>
            </tr>
            @if($fd('pay_method') === 'Bank')
                <tr>
                    <td class="lbl">Bank</td>
                    <td>{{ $fd('pay_bank_name') }}</td>
                    <td class="lbl">Branch</td>
                    <td>{{ $fd('pay_bank_branch') }}</td>
                </tr>
            @endif
        </table>
    @endif

    @if($hasViva)
        <table class="grid">
            <tr>
                <td class="lbl">Viva Marks</td>
                <td class="val-b">{{ $fd('viva_total') }} / 50</td>
            </tr>
        </table>
    @endif

    {{-- Declaration + applicant signature --}}
    <div class="declaration">
        I, <strong>{{ $up($registration->name) }}</strong>, declare that all information and the photo provided above are correct. The scholarship authority reserves the right to cancel my scholarship if any information is found false.
    </div>
    <table class="sig-table">
        <tr>
            <td style="width:55%;"></td>
            <td style="width:45%;">
                <div class="sig-line">Applicant signature &amp; date</div>
            </td>
        </tr>
    </table>

    {{-- Recommendation (50 words) + head of institution + public representative --}}
    <table class="remark-box remark-cols">
        <tr>
            <td>
                <div class="remark-head">Remarks (max 50 words) for Head of institute</div>
                <div class="remark-lines"></div>
                <div class="remark-sign">
                    <div class="seal-box">Seal</div>
                    <div class="sig-space"></div>
                    <div style="border-top:1px solid #000;padding-top:2px;width:62%;">Head of institute — signature</div>
                    <div style="margin-top:5px;">Mobile: <span class="fill-line"></span></div>
                </div>
            </td>
            <td>
                <div class="remark-head">Remarks (max 50 words) for Public representative</div>
                <div class="remark-lines"></div>
                <div class="remark-sign">
                    <div class="seal-box">Seal</div>
                    <div class="sig-space"></div>
                    <div style="border-top:1px solid #000;padding-top:2px;width:62%;">Public representative — signature</div>
                    <div style="margin-top:5px;">Mobile: <span class="fill-line"></span></div>
                </div>
            </td>
        </tr>
    </table>
    {{-- Approval --}}
    <table class="sig-table" style="margin-top:6px;">
        <tr>
            <td style="width:42%;">
                <div class="sig-line">Director<br><span style="font-size: 12px;">Joyee Shikkhabritti — approver</span></div>
            </td>
            <td style="width:16%;"></td>
            <td style="width:42%;">
                <div class="sig-line">Treasurer<br><span style="font-size: 12px;">Joyee Shikkhabritti</span></div>
            </td>
        </tr>
    </table>

    <div class="footer-line">
        <span class="r">Ref: #{{ $registration->registration_number }}</span>
        Generated: {{ now()->format('d/m/Y H:i') }}
    </div>
</div>

</body>
</html>
