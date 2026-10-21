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
    $permAddr = $fd('permanent_address') ?: (string) $registration->address;
    $presAddr = $fd('present_address');
    $sameAddr = $presAddr === '' || $presAddr === $permAddr;
    $permDistrict = $fd('permanent_district') ?: (string) $registration->district;
    $presDistrict = $fd('present_district');

    $guardianPhone = $fd('guardian_phone');
    if ($guardianPhone === $registration->phone) {
        $guardianPhone = '';
    }

    // Academic exam rows: label => [prefix, gpa key]
    $examRows = [
        'SSC / সমমান'     => ['ssc', 'ssc_gpa', 'GPA'],
        'HSC / সমমান'     => ['hsc', 'hsc_gpa', 'GPA'],
        'স্নাতক / ডিগ্রি'    => ['grad', 'grad_cgpa', 'CGPA'],
        'স্নাতকোত্তর / অন্যান্য' => ['other', 'other_cgpa', 'CGPA'],
    ];
    $prevSch = collect($formData['prev_scholarships'] ?? [])->filter(fn ($r) => is_array($r) && (trim($r['year'] ?? '') !== '' || trim($r['amount'] ?? '') !== ''))->values();
    $hasViva = !empty($formData['viva_total']);
@endphp
<!DOCTYPE html>
<html lang="bn">
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
            font-size: 11px;
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
        .head-title .org { font-size: 13px; font-weight: bold; }
        .head-title .form-title { font-size: 26px; font-weight: bold; letter-spacing: 3px; line-height: 1.15; text-transform: uppercase; }
        .head-title .meta { font-size: 10px; margin-top: 2px; }
        .head-photo { width: 98px; text-align: right; }
        .photo-box {
            width: 90px; height: 108px; border: 1.5px solid #000; display: inline-block;
            overflow: hidden; text-align: center; font-size: 9px; color: #64748b;
        }
        .photo-box img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .photo-box .ph { padding-top: 44px; }

        /* Sections */
        .sec-title {
            font-size: 11px; font-weight: bold; background: #e5e7eb; border: 1px solid #000;
            border-bottom: none; padding: 2px 6px;
        }
        .grid { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .grid td, .grid th { border: 1px solid #000; padding: 3px 5px; font-size: 10.5px; vertical-align: middle; }
        .grid th { background: #f3f4f6; font-weight: bold; text-align: center; font-size: 10px; }
        .lbl { white-space: nowrap; width: 17%; }
        .val-b { font-weight: bold; }

        .reason { border: 1px solid #000; padding: 4px 7px; margin-bottom: 6px; min-height: 40px; }
        .reason-body { text-align: justify; font-size: 10.5px; }
        .declaration { font-size: 10px; text-align: justify; margin: 4px 0 0; }

        /* Signatures */
        .sig-line { border-top: 1px solid #000; margin-top: 30px; padding-top: 2px; font-size: 10px; text-align: center; }
        .sig-table { width: 100%; border-collapse: collapse; }
        .sig-table td { vertical-align: bottom; }

        .remark-box { border: 1px solid #000; margin-top: 8px; }
        .remark-head { padding: 3px 7px; font-size: 10.5px; font-weight: bold; border-bottom: 1px solid #000; background: #f3f4f6; }
        .remark-lines { height: 50px; padding: 0 7px; background-image: repeating-linear-gradient(transparent, transparent 15px, #9ca3af 15px, #9ca3af 16px); }
        .remark-sigs { width: 100%; border-collapse: collapse; border-top: 1px solid #000; }
        .remark-sigs td { width: 50%; padding: 4px 8px 5px; vertical-align: top; font-size: 10px; }
        .remark-sigs td + td { border-left: 1px solid #000; }
        .sig-space { height: 34px; }
        .fill-line { border-bottom: 1px dotted #000; display: inline-block; width: 60%; height: 11px; vertical-align: bottom; }
        .seal-box { float: right; width: 52px; height: 52px; border: 1px dashed #6b7280; border-radius: 50%; text-align: center; font-size: 8px; color: #6b7280; padding-top: 20px; }

        .footer-line { border-top: 1px solid #000; margin-top: 8px; padding-top: 3px; font-size: 9px; }
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
                <span class="badge-selected-status">★ বৃত্তিপ্রাপ্ত</span>
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
                <div class="org">জয়ী শিক্ষাবৃত্তি</div>
                <div class="form-title">Scholarship</div>
                <div class="meta">
                    আবেদন নং: <strong>{{ $registration->registration_number }}</strong>
                    &nbsp;|&nbsp; তারিখ: {{ $registration->created_at->format('d/m/Y') }}
                </div>
            </td>
            <td class="head-photo">
                <div class="photo-box">
                    @if($photoUrl)
                        <img src="{{ $photoUrl }}" alt="Photo">
                    @else
                        <div class="ph">ছবি</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- Personal --}}
    <div class="sec-title">ব্যক্তিগত তথ্য</div>
    <table class="grid">
        <tr>
            <td class="lbl">আবেদনকারীর নাম</td>
            <td class="val-b" style="width:33%;">{{ $up($registration->name) }}</td>
            <td class="lbl">মোবাইল নম্বর</td>
            <td class="val-b">{{ $registration->phone }}</td>
        </tr>
        <tr>
            <td class="lbl">পিতার নাম</td>
            <td>{{ $up($fd('father_name')) }}@if($fd('father_income') !== '') <span style="white-space:nowrap;">(আয়: {{ $fd('father_income') }})</span>@endif @if($fd('father_phone')) <br>{{ $fd('father_phone') }}@endif</td>
            <td class="lbl">মাতার নাম</td>
            <td>{{ $up($fd('mother_name')) }}@if($fd('mother_income') !== '') <span style="white-space:nowrap;">(আয়: {{ $fd('mother_income') }})</span>@endif @if($fd('mother_phone')) <br>{{ $fd('mother_phone') }}@endif</td>
        </tr>
        <tr>
            <td class="lbl">জন্ম তারিখ</td>
            <td>{{ $fd('birth_date') }}</td>
            <td class="lbl">লিঙ্গ / ধর্ম</td>
            <td>{{ trim($fd('gender') . ($fd('religion') ? ' / ' . $fd('religion') : ''), ' /') }}</td>
        </tr>
        <tr>
            <td class="lbl">অভিভাবকের নাম</td>
            <td>{{ $up($fd('guardian_name')) }}@if($fd('guardian_income') !== '') <span style="white-space:nowrap;">(আয়: {{ $fd('guardian_income') }})</span>@endif @if($guardianPhone) <span style="white-space:nowrap;">({{ $guardianPhone }})</span>@endif</td>
            <td class="lbl">বার্ষিক পারিবারিক আয়</td>
            <td>{{ $fd('annual_income') !== '' ? $fd('annual_income') . ' টাকা' : '' }}</td>
        </tr>
        @if($fd('marital_status') !== '' || $fd('nationality') !== '')
            <tr>
                <td class="lbl">বৈবাহিক অবস্থা</td>
                <td>{{ $fd('marital_status') }}</td>
                <td class="lbl">জাতীয়তা</td>
                <td>{{ $up($fd('nationality')) }}</td>
            </tr>
        @endif
    </table>

    {{-- Academic (all in one place) --}}
    <div class="sec-title">একাডেমিক তথ্য</div>
    <table class="grid">
        <tr>
            <td class="lbl">প্রতিষ্ঠান</td>
            <td colspan="3" class="val-b">
                {{ $collegeName }}@if($fd('college_code')) <span style="font-weight:normal;">(কোড: {{ $fd('college_code') }})</span>@endif
            </td>
        </tr>
        <tr>
            <td class="lbl">বিভাগ / গ্রুপ</td>
            <td style="width:33%;">{{ $up($fd('group')) }}</td>
            <td class="lbl">ভর্তি রোল / মেধাক্রম</td>
            <td>{{ $fd('admission_roll') }}@if($fd('merit_position')) / {{ $fd('merit_position') }}@endif</td>
        </tr>
        <tr>
            <td class="lbl">বৃত্তির জন্য ডিগ্রি</td>
            <td>{{ $up($fd('scholarship_degree')) }}</td>
            <td class="lbl">বিষয়</td>
            <td>{{ $up($fd('assigned_subject')) }}</td>
        </tr>
        <tr>
            <td class="lbl">ভর্তি ফি</td>
            <td>{{ $fd('admission_fee') !== '' ? $fd('admission_fee') . ' টাকা' : ($fd('tuition_fee') !== '' ? $fd('tuition_fee') . ' টাকা' : '') }}</td>
            <td class="lbl">মাসিক বেতন</td>
            <td>{{ $fd('monthly_fee') !== '' ? $fd('monthly_fee') . ' টাকা' : '' }}</td>
        </tr>
        @if($fd('hostel_cost') !== '')
            <tr>
                <td class="lbl">হোস্টেল খরচ</td>
                <td colspan="3">{{ $fd('hostel_cost') }} টাকা</td>
            </tr>
        @endif
    </table>
    <table class="grid" style="margin-top:-5px;">
        <thead>
            <tr>
                <th style="width:17%;">পরীক্ষা</th>
                <th style="width:11%;">রোল</th>
                <th style="width:14%;">বোর্ড / বিশ্ববিদ্যালয়</th>
                <th>প্রতিষ্ঠান</th>
                <th style="width:8%;">সাল</th>
                <th style="width:8%;">GPA</th>
            </tr>
        </thead>
        <tbody>
            @foreach($examRows as $label => [$p, $gpaKey, $gpaLabel])
                @php
                    $isCore = in_array($p, ['ssc', 'hsc'], true);
                    $inst = $fd($p . '_institute');
                    // HSC institute equals current college -> avoid repeating it
                    if ($p === 'hsc' && $inst !== '' && $inst === $collegeName) { $inst = 'একই (উপরে উল্লিখিত)'; }
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

    {{-- Scholarships --}}
    <div class="sec-title">Scholarships</div>
    <table class="grid">
        <thead>
            <tr><th>Year</th><th style="width:25%;">Times</th><th style="width:30%;">Amount</th></tr>
        </thead>
        <tbody>
            @forelse($prevSch as $r)
                <tr>
                    <td style="text-align:center;">{{ $r['year'] ?? '' }}</td>
                    <td style="text-align:center;">{{ $r['times'] ?? '' }}</td>
                    <td style="text-align:center;">{{ $r['amount'] ?? '' }}</td>
                </tr>
            @empty
                <tr><td colspan="3" style="text-align:center;">—</td></tr>
            @endforelse
            <tr>
                <td>অন্য প্রতিষ্ঠান থেকে বৃত্তি পান?</td>
                <td colspan="2">{{ $fd('other_scholarship') ?: 'No' }}@if($fd('other_scholarship_details')) — {{ $fd('other_scholarship_details') }}@endif</td>
            </tr>
        </tbody>
    </table>
    {{-- Address --}}
    <div class="sec-title">ঠিকানা</div>
    <table class="grid">
        <tr>
            <td class="lbl">স্থায়ী ঠিকানা</td>
            <td>{{ $permAddr }}@if($permDistrict) , জেলা: {{ $permDistrict }}@endif</td>
        </tr>
        <tr>
            <td class="lbl">বর্তমান ঠিকানা</td>
            <td>
                @if($sameAddr)
                    স্থায়ী ঠিকানার অনুরূপ
                @else
                    {{ $presAddr }}@if($presDistrict) , জেলা: {{ $presDistrict }}@endif
                @endif
            </td>
        </tr>
    </table>

    {{-- Reason --}}
    <div class="reason">
        <div style="font-weight:bold;font-size:10.5px;">শিক্ষাবৃত্তির আবেদনের কারণ (অনধিক ৫০ শব্দ):</div>
        <div class="reason-body">{{ $fd('scholarship_reason') }}</div>
    </div>

    @if($hasViva)
        <table class="grid">
            <tr>
                <td class="lbl">ভাইভা প্রাপ্ত নম্বর</td>
                <td class="val-b">{{ $fd('viva_total') }} / 50</td>
            </tr>
        </table>
    @endif

    {{-- Declaration + applicant signature --}}
    <div class="declaration">
        আমি <strong>{{ $up($registration->name) }}</strong> ঘোষণা করছি যে, উপরে প্রদত্ত সকল তথ্য ও ছবি সঠিক। কোনো তথ্য মিথ্যা প্রমাণিত হলে শিক্ষাবৃত্তি কর্তৃপক্ষ আমার বৃত্তি বাতিল করার অধিকার সংরক্ষণ করে।
    </div>
    <table class="sig-table">
        <tr>
            <td style="width:55%;"></td>
            <td style="width:45%;">
                <div class="sig-line">আবেদনকারীর স্বাক্ষর ও তারিখ</div>
            </td>
        </tr>
    </table>

    {{-- Recommendation (50 words) + head of institution + public representative --}}
    <div class="remark-box">
        <div class="remark-head">মন্তব্য (অনধিক ৫০ শব্দ)</div>
        <div class="remark-lines"></div>
        <table class="remark-sigs">
            <tr>
                <td>
                    <div class="seal-box">সিল</div>
                    <div class="sig-space"></div>
                    <div style="border-top:1px solid #000;padding-top:2px;width:62%;">শিক্ষা প্রতিষ্ঠান প্রধানের স্বাক্ষর</div>
                    <div style="margin-top:5px;">মোবাইল: <span class="fill-line"></span></div>
                </td>
                <td>
                    <div class="seal-box">সিল</div>
                    <div class="sig-space"></div>
                    <div style="border-top:1px solid #000;padding-top:2px;width:62%;">জনপ্রতিনিধির স্বাক্ষর</div>
                    <div style="margin-top:5px;">মোবাইল: <span class="fill-line"></span></div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Approval --}}
    <table class="sig-table" style="margin-top:6px;">
        <tr>
            <td style="width:42%;">
                <div class="sig-line">পরিচালক<br><span style="font-size:9px;">জয়ী শিক্ষাবৃত্তি অনুমোদনকারী</span></div>
            </td>
            <td style="width:16%;"></td>
            <td style="width:42%;">
                <div class="sig-line">কোষাধ্যক্ষ<br><span style="font-size:9px;">জয়ী শিক্ষাবৃত্তি</span></div>
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
