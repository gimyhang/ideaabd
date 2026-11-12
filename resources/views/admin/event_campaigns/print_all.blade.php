<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isLittleMagOnly ? 'লিটিলম্যাগাজিন সম্পাদক তালিকা ও ফরমসমূহ' : 'অংশগ্রহণকারী তালিকা ও আবেদন ফরমসমূহ' }} — {{ $campaign->title }}</title>
    
    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --font-bengali: 'Hind Siliguri', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        body {
            font-family: var(--font-bengali);
            background-color: #f8fafc;
            color: #1e293b;
            margin: 0;
            padding: 0;
            font-size: 14px;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        /* Top Action Bar (Screen Only, Hidden on print) */
        .print-topbar {
            background: #0f172a;
            color: #ffffff;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.18);
        }

        .print-container {
            max-width: 1200px;
            margin: 24px auto;
            background: #ffffff;
            padding: 32px;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
        }

        /* Official Header */
        .official-header {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }
        .official-logo {
            max-height: 65px;
            object-fit: contain;
        }

        /* Summary Stats Cards */
        .stat-badge {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        /* Printable Table */
        .print-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .print-table thead th {
            background: #0f172a !important;
            color: #ffffff !important;
            font-weight: 700;
            padding: 8px 10px;
            border: 1px solid #0f172a;
            text-align: left;
            white-space: nowrap;
            font-size: 13.5px;
        }
        .print-table tbody td {
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
            color: #0f172a;
            font-size: 14px;
        }
        .print-table tbody tr:nth-child(even) td {
            background-color: #f8fafc !important;
        }

        .status-pill {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            text-align: center;
            border: 1px solid transparent;
        }
        .status-confirmed {
            background-color: #dcfce7 !important;
            color: #15803d !important;
            border-color: #86efac !important;
        }
        .status-pending {
            background-color: #fef9c3 !important;
            color: #854d0e !important;
            border-color: #fde047 !important;
        }
        .status-rejected {
            background-color: #fee2e2 !important;
            color: #b91c1c !important;
            border-color: #fca5a5 !important;
        }

        .font-mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }

        /* =========================================================================
           INDIVIDUAL REGISTRATION FORM SHEET STYLES
           ========================================================================= */
        .applicant-form-sheet {
            background: #ffffff;
            border: 2px solid #0f172a;
            border-radius: 8px;
            padding: 24px;
            margin-top: 32px;
            page-break-before: always;
            break-before: page;
            page-break-inside: avoid;
            break-inside: avoid;
            position: relative;
        }
        .applicant-form-sheet:first-child {
            margin-top: 0;
        }
        .form-sheet-header {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .applicant-photo-box {
            width: 120px;
            height: 140px;
            border: 2px solid #334155;
            border-radius: 6px;
            overflow: hidden;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }
        .applicant-photo-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .info-section-title {
            font-size: 13px;
            font-weight: 700;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 4px 10px;
            border-radius: 4px;
            margin-bottom: 8px;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .info-grid-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-bottom: 12px;
        }
        .info-grid-table td, .info-grid-table th {
            padding: 5px 8px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        .info-grid-table th {
            background: #f8fafc;
            color: #475569;
            font-weight: 600;
            width: 25%;
            white-space: nowrap;
        }
        .info-grid-table td {
            color: #0f172a;
        }
        .form-sheet-signature-box {
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px dashed #cbd5e1;
        }
        .division-banner {
            background: #1e293b;
            color: #ffffff;
            border-left: 6px solid #10b981;
            padding: 8px 14px;
            margin-bottom: 8px;
            border-radius: 6px;
        }
        .district-banner {
            background: #f1f5f9;
            color: #0f172a;
            border-left: 4px solid #0284c7;
            padding: 6px 12px;
            margin-bottom: 6px;
            border-radius: 4px;
        }
        .district-sub-block {
            margin-bottom: 18px;
        }

        /* Print Media Styles */
        @media print {
            @page {
                size: A4 portrait;
                margin: 8mm 10mm;
            }
            body {
                background: #ffffff !important;
                color: #000000 !important;
                padding: 0 !important;
            }
            .print-topbar {
                display: none !important;
            }
            .print-container {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
            }
            .print-table {
                font-size: 14px !important;
            }
            .print-table thead th {
                background: #1e293b !important;
                color: #ffffff !important;
                padding: 7px 8px !important;
                font-size: 13.5px !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .print-table tbody td {
                padding: 7px 8px !important;
                border: 1px solid #94a3b8 !important;
                font-size: 14px !important;
                line-height: 1.35 !important;
            }
            .division-print-block {
                page-break-before: always !important;
                break-before: page !important;
            }
            .division-print-block:first-of-type {
                page-break-before: avoid !important;
                break-before: avoid !important;
            }
            .division-banner {
                background: #1e293b !important;
                color: #ffffff !important;
                border-left: 6px solid #10b981 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .district-banner {
                background: #f1f5f9 !important;
                color: #0f172a !important;
                border-left: 4px solid #0284c7 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .district-sub-block {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            .applicant-photo-box {
                border: 1.5px solid #000000 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .applicant-photo-img {
                width: 100% !important;
                height: 100% !important;
                object-fit: cover !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                display: block !important;
            }
            .applicant-form-sheet {
                border: 1.5px solid #000000 !important;
                padding: 16px !important;
                margin-top: 0 !important;
                page-break-before: always !important;
                break-before: page !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            tr {
                page-break-inside: avoid;
            }
            thead {
                display: table-header-group;
            }
        }
    </style>
</head>
<body>

    {{-- Top Action Bar (Screen Only) --}}
    <div class="print-topbar">
        <div class="d-flex align-items-center gap-3">
            <span class="fs-6 fw-bold">
                <i class="fa-solid fa-print text-warning me-2"></i> 
                {{ $isLittleMagOnly ? 'লিটিলম্যাগাজিন সম্পাদক তালিকা ও ফরম' : 'এক ক্লিকে সকল তালিকা ও ফরম প্রিন্ট' }}
            </span>
            <span class="badge bg-secondary font-mono">{{ $totalCount }} টি এন্ট্রি ও ফরম</span>
        </div>

        {{-- Interactive Section Toggles --}}
        <div class="d-flex align-items-center flex-wrap gap-2">
            {{-- Status Filter Switcher --}}
            <div class="btn-group btn-group-sm" role="group" aria-label="Status filter">
                <a href="?{{ http_build_query(array_merge(request()->all(), ['status' => 'approved'])) }}" 
                   class="btn btn-sm {{ in_array(($currentStatus ?? 'approved'), ['approved', 'confirmed']) ? 'btn-success text-white fw-bold shadow-xs' : 'btn-outline-secondary text-white' }}" 
                   style="font-size: 11.5px;" 
                   title="পেন্ডিং বাদ দিয়ে শুধুমাত্র অনুমোদিত তালিকা ও ফরম লোড করুন">
                    <i class="fa-solid fa-circle-check me-1"></i> অনুমোদিত (পেন্ডিং ছাড়া)
                </a>
                <a href="?{{ http_build_query(array_merge(request()->all(), ['status' => 'pending'])) }}" 
                   class="btn btn-sm {{ ($currentStatus ?? '') === 'pending' ? 'btn-warning text-dark fw-bold shadow-xs' : 'btn-outline-secondary text-white' }}" 
                   style="font-size: 11.5px;" 
                   title="শুধুমাত্র পেন্ডিং আবেদনগুলো আলাদাভাবে লোড করুন">
                    <i class="fa-solid fa-clock-rotate-left me-1"></i> পেন্ডিং আলাদা
                </a>
                <a href="?{{ http_build_query(array_merge(request()->all(), ['status' => 'all'])) }}" 
                   class="btn btn-sm {{ ($currentStatus ?? '') === 'all' ? 'btn-info text-dark fw-bold shadow-xs' : 'btn-outline-secondary text-white' }}" 
                   style="font-size: 11.5px;" 
                   title="সকল আবেদন (অনুমোদিত + পেন্ডিং)">
                    <i class="fa-solid fa-users me-1"></i> সকল
                </a>
            </div>

            {{-- Grouping Switcher (Category vs Division) --}}
            <div class="btn-group btn-group-sm" role="group" aria-label="Group by switcher">
                <a href="?{{ http_build_query(array_merge(request()->all(), ['group_by' => 'division'])) }}" 
                   class="btn btn-sm {{ ($groupBy ?? 'division') === 'division' ? 'btn-primary text-white fw-bold' : 'btn-outline-secondary text-white' }}" 
                   style="font-size: 11.5px;" 
                   title="বিভাগ অনুসারে তালিকা সাজান (রংপুর, ঢাকা, রাজশাহী ইত্যাদি)">
                    <i class="fa-solid fa-map-location-dot me-1"></i> বিভাগ ভিত্তিক
                </a>
                <a href="?{{ http_build_query(array_merge(request()->all(), ['group_by' => 'category'])) }}" 
                   class="btn btn-sm {{ ($groupBy ?? 'division') === 'category' ? 'btn-primary text-white fw-bold' : 'btn-outline-secondary text-white' }}" 
                   style="font-size: 11.5px;" 
                   title="ক্যাটাগরি অনুসারে তালিকা সাজান (কবি, ছড়াকার, লিটিলম্যাগ ইত্যাদি)">
                    <i class="fa-solid fa-layer-group me-1"></i> ক্যাটাগরি ভিত্তিক
                </a>
            </div>

            <div class="form-check form-check-inline text-white m-0 d-flex align-items-center gap-1.5" style="cursor: pointer;">
                <input class="form-check-input" type="checkbox" id="toggleSummaryTable" checked onchange="toggleSection('printSummarySection', this.checked)">
                <label class="form-check-label fw-semibold small" for="toggleSummaryTable" style="cursor: pointer;">
                    <i class="fa-solid fa-table-list text-info me-1"></i> তালিকা
                </label>
            </div>
            <div class="form-check form-check-inline text-white m-0 d-flex align-items-center gap-1.5" style="cursor: pointer;">
                <input class="form-check-input" type="checkbox" id="toggleForms" checked onchange="toggleSection('printFormsSection', this.checked)">
                <label class="form-check-label fw-semibold small" for="toggleForms" style="cursor: pointer;">
                    <i class="fa-solid fa-file-lines text-warning me-1"></i> ফরমগুলো ({{ $totalCount }})
                </label>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.event-campaigns.export', $campaign->id) }}?{{ http_build_query(request()->all()) }}" 
               class="btn btn-sm btn-outline-info text-white rounded-pill px-3" 
               style="font-size: 11.5px;"
               title="বর্তমান ফিল্টার ও সাজানো অনুসারে CSV ডাউনলোড করুন">
                <i class="fa-solid fa-file-csv me-1 text-info"></i> CSV ডাউনলোড
            </a>
            <a href="{{ route('admin.event-campaigns.export', $campaign->id) }}?{{ http_build_query(array_merge(request()->all(), ['format' => 'excel'])) }}" 
               class="btn btn-sm btn-outline-success text-white rounded-pill px-3" 
               style="font-size: 11.5px;"
               title="বিভাগ ও জেলা অনুসারে এক্সেল ফাইল ডাউনলোড করুন">
                <i class="fa-solid fa-file-excel me-1 text-success"></i> বিভাগ-জেলা Excel
            </a>
            @if($campaign->isWriter())
            <a href="{{ route('admin.event-campaigns.cards.download-all', $campaign->id) }}?{{ http_build_query(request()->all()) }}" 
               target="_blank"
               class="btn btn-sm btn-outline-warning text-white rounded-pill px-3" 
               style="font-size: 11.5px;"
               title="বর্তমান ফিল্টার অনুসারে কার্ড ডাউনলোড করুন">
                <i class="fa-solid fa-id-card me-1 text-warning"></i> কার্ড ডাউনলোড
            </a>
            @endif
            <button type="button" class="btn btn-sm btn-warning rounded-pill px-3 fw-bold text-dark shadow-xs" onclick="printSummaryOnly()" title="ফরমগুলো বাদ দিয়ে শুধুমাত্র বিভাগ ও জেলাওয়ারী সামারি তালিকা প্রিন্ট করুন">
                <i class="fa-solid fa-list-check me-1"></i> শুধু তালিকা প্রিন্ট
            </button>
            <button type="button" class="btn btn-sm btn-success rounded-pill px-3 fw-bold shadow-sm" onclick="printAllSections()" title="তালিকা ও সকল আবেদন ফরম একসাথে প্রিন্ট করুন">
                <i class="fa-solid fa-print me-1"></i> সম্পূর্ণ প্রিন্ট
            </button>
            <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-2.5" onclick="window.close()" title="উইন্ডো বন্ধ করুন">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    </div>

    {{-- Printable Sheet Container --}}
    <div class="print-container">
        
        {{-- =========================================================================
             SECTION 1: SUMMARY PARTICIPANTS TABLE
             ========================================================================= --}}
        <div id="printSummarySection">
            {{-- Official Header --}}
            <div class="official-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    @php
                        $formLogo = $campaign->form_settings['logo_image'] ?? null;
                        if ($formLogo && !str_starts_with($formLogo, 'http')) {
                            $formLogo = '/storage/' . ltrim(preg_replace('#^(public/|storage/)+#', '', $formLogo), '/');
                        }
                    @endphp
                    @if($formLogo)
                        <img src="{{ $formLogo }}" alt="Logo" class="official-logo">
                    @else
                        <div style="width: 50px; height: 50px; border-radius: 50%; background: #0f172a; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                            <i class="fa-solid fa-feather-pointed"></i>
                        </div>
                    @endif
                    <div>
                        <h3 class="fw-bold mb-0 text-dark">{{ $campaign->title }}</h3>
                        <div class="text-muted small">
                            {{ $isLittleMagOnly ? 'লিটিলম্যাগাজিন সম্পাদক তালিকা ও ডিরেক্টরি' : 'অফিশিয়াল অংশগ্রহণকারী ও প্রতিনিধি নিবন্ধন তালিকা' }}
                        </div>
                        <small class="text-secondary">
                            আয়োজনে: আইডিয়া প্রকাশন &bull; ওয়েবসাইট: www.ideaabd.com &bull; তারিখ: {{ date('d M, Y') }}
                        </small>
                    </div>
                </div>

                <div class="text-end">
                    <div class="fs-6 fw-bold text-dark">মোট নিবন্ধিত: {{ $totalCount }} জন</div>
                    <small class="text-muted font-mono">মুদ্রণের তারিখ: {{ date('d/m/Y h:i A') }}</small>
                </div>
            </div>

            {{-- Summary Stats Pills --}}
            @php
                $confirmedCount = $allRegistrations->whereIn('status', ['confirmed', 'approved', 'selected'])->count();
                $pendingCount = $allRegistrations->where('status', 'pending')->count();
                $rejectedCount = $allRegistrations->where('status', 'rejected')->count();
                $paidCount = $allRegistrations->where('payment_status', 'verified')->count();
                $totalFee = $allRegistrations->sum('amount_paid');
            @endphp
            <div class="d-flex flex-wrap gap-2 mb-3">
                <div class="stat-badge text-dark">
                    <i class="fa-solid fa-users text-primary"></i> মোট এন্ট্রি: <strong>{{ $totalCount }}</strong>
                </div>
                <div class="stat-badge text-success">
                    <i class="fa-solid fa-circle-check"></i> অনুমোদিত: <strong>{{ $confirmedCount }}</strong>
                </div>
                <div class="stat-badge text-warning-emphasis">
                    <i class="fa-solid fa-clock"></i> পেন্ডিং: <strong>{{ $pendingCount }}</strong>
                </div>
                @if($rejectedCount > 0)
                    <div class="stat-badge text-danger">
                        <i class="fa-solid fa-circle-xmark"></i> বাতিল: <strong>{{ $rejectedCount }}</strong>
                    </div>
                @endif
                @if($campaign->has_fee_or_donation)
                    <div class="stat-badge text-info-emphasis">
                        <i class="fa-solid fa-receipt"></i> ফি যাচাইকৃত: <strong>{{ $paidCount }}</strong> (৳{{ number_format($totalFee) }})
                    </div>
                @endif
            </div>

            {{-- Main Summary Table Grouped by Category or Division --}}
            @php 
                $globalSerial = 1; 
                $isDivisionGroup = ($groupBy ?? 'division') === 'division';
            @endphp
            @forelse($groupedRegistrations as $groupTitle => $groupRegistrations)
                @php
                    $groupTotal = $isDivisionGroup ? $groupRegistrations->flatten(1)->count() : $groupRegistrations->count();
                @endphp
                <div class="division-print-block mb-4">
                    <div class="division-banner d-flex justify-content-between align-items-center mb-2">
                        <div class="fw-bold fs-6 d-flex align-items-center gap-2">
                            @if(!$isDivisionGroup)
                                <i class="fa-solid fa-feather-pointed text-warning"></i>
                                <span>ক্যাটাগরি: <strong>{{ $groupTitle }}</strong></span>
                            @else
                                <i class="fa-solid fa-map-location-dot text-warning"></i>
                                <span>বিভাগ: <strong>{{ $groupTitle }}</strong></span>
                            @endif
                        </div>
                        <span class="badge bg-light text-dark font-mono px-3 py-1 fw-bold" style="font-size: 11.5px;">
                            মোট: {{ $groupTotal }} জন
                        </span>
                    </div>

                    @if($isDivisionGroup)
                        {{-- Under this Division, group by District --}}
                        @foreach($groupRegistrations as $districtName => $districtRegistrations)
                            <div class="district-sub-block">
                                <div class="district-banner d-flex justify-content-between align-items-center">
                                    <div class="fw-bold text-dark" style="font-size: 12.5px;">
                                        <i class="fa-solid fa-location-dot text-primary me-1"></i> জেলা: <strong>{{ $districtName }}</strong>
                                    </div>
                                    <span class="badge bg-secondary-subtle text-dark border font-mono px-2 py-0.5" style="font-size: 11px;">
                                        {{ $districtRegistrations->count() }} জন
                                    </span>
                                </div>

                                <table class="print-table mb-2">
                                    <thead>
                                        <tr>
                                            <th style="width: 35px;" class="text-center">ক্র.</th>
                                            <th style="width: 95px;">আইডি</th>
                                            <th>{{ $campaign->isScholarship() ? 'শিক্ষার্থী ও অভিভাবক' : 'অংশগ্রহণকারী ও বিবরণ' }}</th>
                                            <th>{{ $campaign->isScholarship() ? 'শিক্ষা প্রতিষ্ঠান ও বিভাগ' : 'ক্যাটাগরি / শাখা' }}</th>
                                            <th>যোগাযোগ</th>
                                            <th>উপজেলা / ঠিকানা</th>
                                            @if($campaign->has_fee_or_donation)
                                                <th style="width: 75px;">ফি</th>
                                                <th style="width: 80px;">পেমেন্ট</th>
                                            @endif
                                            <th style="width: 85px;" class="text-center">স্ট্যাটাস</th>
                                            <th style="width: 145px; min-width: 135px;" class="text-center">মন্তব্য ও স্বাক্ষর</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($districtRegistrations as $reg)
                                            @php
                                                $authorCat = $reg->resolved_category;
                                                $magName = $reg->form_data['magazine_name'] ?? null;
                                            @endphp
                                            <tr>
                                                <td class="text-center font-mono">{{ $globalSerial++ }}</td>
                                                <td class="font-mono fw-bold text-dark">#{{ $reg->registration_number }}</td>
                                                <td>
                                                    <div class="fw-bold text-dark" style="font-size: 14.5px;">{{ $reg->name }}</div>
                                                    @if($campaign->isScholarship())
                                                        @if(!empty($reg->form_data['father_name']))
                                                            <small class="text-muted d-block" style="font-size: 12.5px;">পিতা: {{ $reg->form_data['father_name'] }}</small>
                                                        @endif
                                                    @else
                                                        @if(!empty($reg->form_data['pen_name']))
                                                            <small class="text-muted d-block" style="font-size: 12.5px;">কলমী নাম: {{ $reg->form_data['pen_name'] }}</small>
                                                        @endif
                                                        @if($magName)
                                                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle py-0 px-1" style="font-size: 10px;">
                                                                পত্রিকা: {{ $magName }}
                                                            </span>
                                                        @endif
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($campaign->isScholarship())
                                                        <div class="fw-medium text-dark" style="font-size: 14px;">{{ $reg->form_data['college_name'] ?? ($reg->institution_or_org ?: '-') }}</div>
                                                        <small class="text-muted d-block" style="font-size: 12px;">{{ $reg->form_data['scholarship_degree'] ?? '' }} • {{ $reg->form_data['group'] ?? '' }} @if(!empty($reg->form_data['ssc_gpa'])) &bull; GPA: {{ $reg->form_data['ssc_gpa'] }} @endif</small>
                                                    @else
                                                        <span class="badge bg-light text-dark border py-0.5 px-2 fw-semibold" style="font-size: 11px;">
                                                            <i class="fa-solid fa-feather-pointed text-warning me-1"></i>{{ $authorCat }}
                                                        </span>
                                                        @if(!empty($reg->designation_or_class) && $reg->designation_or_class !== $authorCat)
                                                            <small class="text-muted d-block mt-0.5" style="font-size: 11px;">{{ $reg->designation_or_class }}</small>
                                                        @endif
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="font-mono fw-semibold" style="font-size: 13.5px;">{{ $reg->phone }}</div>
                                                    @if($reg->email)
                                                        <small class="text-muted d-block text-truncate" style="max-width: 130px; font-size: 11.5px;">{{ $reg->email }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="fw-medium text-dark" style="font-size: 14px;">{{ $reg->resolved_district ?: '-' }}</div>
                                                    <small class="text-muted d-block" style="font-size: 12px;">{{ $reg->resolved_thana ?: ($reg->thana ?: '') }}</small>
                                                </td>
                                                @if($campaign->has_fee_or_donation)
                                                    <td class="font-mono fw-semibold">৳{{ number_format($reg->amount_paid) }}</td>
                                                    <td>
                                                        <span class="status-pill {{ $reg->payment_status === 'verified' ? 'status-confirmed' : 'status-pending' }}">
                                                            {{ $reg->payment_status === 'verified' ? 'Verified' : ucfirst($reg->payment_status) }}
                                                        </span>
                                                        @if($reg->transaction_id)
                                                            <small class="font-mono text-muted d-block" style="font-size: 9.5px;">{{ $reg->transaction_id }}</small>
                                                        @endif
                                                    </td>
                                                @endif
                                                <td class="text-center">
                                                    @if(in_array($reg->status, ['confirmed', 'approved', 'selected', 'attended']))
                                                        <span class="status-pill status-confirmed">Approved</span>
                                                    @elseif($reg->status === 'rejected')
                                                        <span class="status-pill status-rejected">Rejected</span>
                                                    @else
                                                        <span class="status-pill status-pending">{{ ucfirst($reg->status) }}</span>
                                                    @endif
                                                </td>
                                                <td style="min-width: 135px; vertical-align: middle;">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <span class="font-mono text-muted" style="font-size: 11px;">{{ $reg->created_at->format('d/m/Y') }}</span>
                                                        <span class="badge bg-light text-secondary border px-1" style="font-size: 9.5px;">মন্তব্য</span>
                                                    </div>
                                                    <div style="min-height: 28px; border-bottom: 1.5px dotted #64748b; margin-top: 4px;"></div>
                                                    <div class="text-end text-muted font-bengali" style="font-size: 10px; margin-top: 2px;">স্বাক্ষর</div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endforeach
                    @else
                        {{-- Category Group Mode --}}
                        <table class="print-table mb-3">
                            <thead>
                                <tr>
                                    <th style="width: 35px;" class="text-center">ক্র.</th>
                                    <th style="width: 95px;">আইডি</th>
                                    <th>{{ $campaign->isScholarship() ? 'শিক্ষার্থী ও অভিভাবক' : 'অংশগ্রহণকারী ও বিবরণ' }}</th>
                                    <th>{{ $campaign->isScholarship() ? 'শিক্ষা প্রতিষ্ঠান ও বিভাগ' : 'ক্যাটাগরি / শাখা' }}</th>
                                    <th>যোগাযোগ</th>
                                    <th>ঠিকানা (বিভাগ ও জেলা)</th>
                                    @if($campaign->has_fee_or_donation)
                                        <th style="width: 75px;">ফি</th>
                                        <th style="width: 80px;">পেমেন্ট</th>
                                    @endif
                                    <th style="width: 85px;" class="text-center">স্ট্যাটাস</th>
                                    <th style="width: 145px; min-width: 135px;" class="text-center">মন্তব্য ও স্বাক্ষর</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($groupRegistrations as $reg)
                                    @php
                                        $authorCat = $reg->resolved_category;
                                        $magName = $reg->form_data['magazine_name'] ?? null;
                                    @endphp
                                    <tr>
                                        <td class="text-center font-mono">{{ $globalSerial++ }}</td>
                                        <td class="font-mono fw-bold text-dark">#{{ $reg->registration_number }}</td>
                                        <td>
                                            <div class="fw-bold text-dark" style="font-size: 14.5px;">{{ $reg->name }}</div>
                                            @if($campaign->isScholarship())
                                                @if(!empty($reg->form_data['father_name']))
                                                    <small class="text-muted d-block" style="font-size: 12.5px;">পিতা: {{ $reg->form_data['father_name'] }}</small>
                                                @endif
                                            @else
                                                @if(!empty($reg->form_data['pen_name']))
                                                    <small class="text-muted d-block" style="font-size: 12.5px;">কলমী নাম: {{ $reg->form_data['pen_name'] }}</small>
                                                @endif
                                                @if($magName)
                                                    <span class="badge bg-warning-subtle text-dark border border-warning-subtle py-0 px-1" style="font-size: 10px;">
                                                        পত্রিকা: {{ $magName }}
                                                    </span>
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            @if($campaign->isScholarship())
                                                <div class="fw-medium text-dark" style="font-size: 14px;">{{ $reg->form_data['college_name'] ?? ($reg->institution_or_org ?: '-') }}</div>
                                                <small class="text-muted d-block" style="font-size: 12px;">{{ $reg->form_data['scholarship_degree'] ?? '' }} • {{ $reg->form_data['group'] ?? '' }} @if(!empty($reg->form_data['ssc_gpa'])) &bull; GPA: {{ $reg->form_data['ssc_gpa'] }} @endif</small>
                                            @else
                                                <span class="badge bg-light text-dark border py-0.5 px-2 fw-semibold" style="font-size: 11px;">
                                                    <i class="fa-solid fa-feather-pointed text-warning me-1"></i>{{ $authorCat }}
                                                </span>
                                                @if(!empty($reg->designation_or_class) && $reg->designation_or_class !== $authorCat)
                                                    <small class="text-muted d-block mt-0.5" style="font-size: 11px;">{{ $reg->designation_or_class }}</small>
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            <div class="font-mono fw-semibold" style="font-size: 13.5px;">{{ $reg->phone }}</div>
                                            @if($reg->email)
                                                <small class="text-muted d-block text-truncate" style="max-width: 130px; font-size: 11.5px;">{{ $reg->email }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-medium text-dark" style="font-size: 14px;">{{ $reg->resolved_district ?: '-' }}</div>
                                            <small class="text-muted d-block" style="font-size: 12px;">{{ $reg->resolved_division }} &bull; {{ $reg->thana ?: '' }}</small>
                                        </td>
                                        @if($campaign->has_fee_or_donation)
                                            <td class="font-mono fw-semibold">৳{{ number_format($reg->amount_paid) }}</td>
                                            <td>
                                                <span class="status-pill {{ $reg->payment_status === 'verified' ? 'status-confirmed' : 'status-pending' }}">
                                                    {{ $reg->payment_status === 'verified' ? 'Verified' : ucfirst($reg->payment_status) }}
                                                </span>
                                                @if($reg->transaction_id)
                                                    <small class="font-mono text-muted d-block" style="font-size: 9.5px;">{{ $reg->transaction_id }}</small>
                                                @endif
                                            </td>
                                        @endif
                                        <td class="text-center">
                                            @if(in_array($reg->status, ['confirmed', 'approved', 'selected', 'attended']))
                                                <span class="status-pill status-confirmed">Approved</span>
                                            @elseif($reg->status === 'rejected')
                                                <span class="status-pill status-rejected">Rejected</span>
                                            @else
                                                <span class="status-pill status-pending">{{ ucfirst($reg->status) }}</span>
                                            @endif
                                        </td>
                                        <td style="min-width: 135px; vertical-align: middle;">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="font-mono text-muted" style="font-size: 11px;">{{ $reg->created_at->format('d/m/Y') }}</span>
                                                <span class="badge bg-light text-secondary border px-1" style="font-size: 9.5px;">মন্তব্য</span>
                                            </div>
                                            <div style="min-height: 28px; border-bottom: 1.5px dotted #64748b; margin-top: 4px;"></div>
                                            <div class="text-end text-muted font-bengali" style="font-size: 10px; margin-top: 2px;">স্বাক্ষর</div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-folder-open fs-2 mb-2 d-block"></i>
                    কোনো রেকর্ড পাওয়া যায়নি।
                </div>
            @endforelse

            {{-- Summary Footer --}}
            <div class="d-flex justify-content-between align-items-center pt-2 pb-4 border-top text-muted small">
                <div>
                    ক্যাম্পেইন: <strong>{{ $campaign->title }}</strong> &bull; আইডিয়া প্রকাশন ম্যানেজমেন্ট সিস্টেম
                </div>
                <div>
                    মোট নিবন্ধিত এন্ট্রি: <strong>{{ $totalCount }} জন</strong>
                </div>
            </div>
        </div>

        {{-- =========================================================================
             SECTION 2: INDIVIDUAL REGISTRATION & APPLICATION FORMS (GROUPED)
             ========================================================================= --}}
        <div id="printFormsSection">
            @foreach($allRegistrations as $reg)
                @php
                        $fData = $reg->form_data ?? [];
                        $rawPhoto = $fData['student_photo'] 
                            ?? ($fData['photo'] 
                            ?? ($fData['author_photo'] 
                            ?? ($fData['avatar'] 
                            ?? ($fData['image'] 
                            ?? ($reg->photo ?? null)))));

                        $photoSrc = null;
                        if (!empty($rawPhoto)) {
                            if (str_starts_with($rawPhoto, 'data:image') || str_starts_with($rawPhoto, 'http://') || str_starts_with($rawPhoto, 'https://')) {
                                $photoSrc = $rawPhoto;
                            } else {
                                $cleanPath = ltrim(preg_replace('#^(public/|storage/)+#', '', $rawPhoto), '/');
                                $photoSrc = asset('storage/' . $cleanPath);
                            }
                        }

                        $authorCat = $reg->resolved_category;
                        $magName = $fData['magazine_name'] ?? null;
                        $penName = $fData['pen_name'] ?? null;
                        $isForeign = ($fData['resident_type'] ?? '') === 'international';
                        $statusApproved = in_array($reg->status, ['confirmed', 'approved', 'selected', 'attended']);
                    @endphp

                    <div class="applicant-form-sheet">
                        {{-- Grouping & Location Tag --}}
                        <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom border-light">
                            <span class="badge bg-dark-subtle text-dark border border-secondary-subtle font-mono px-2 py-0.5" style="font-size: 11px;">
                                <i class="fa-solid fa-feather-pointed text-warning me-1"></i> ক্যাটাগরি: <strong>{{ $authorCat }}</strong> &bull; <i class="fa-solid fa-map-location-dot text-primary me-1"></i> বিভাগ: <strong>{{ $reg->resolved_division }}</strong> &bull; জেলা: <strong>{{ $reg->resolved_district ?: '-' }}</strong>
                            </span>
                            <span class="text-muted font-mono" style="font-size: 10px;">ID: #{{ $reg->registration_number }}</span>
                        </div>

                        {{-- Form Header --}}
                        <div class="form-sheet-header d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                @if($formLogo)
                                    <img src="{{ $formLogo }}" alt="Logo" style="max-height: 52px; object-fit: contain;">
                                @else
                                    <div style="width: 44px; height: 44px; border-radius: 50%; background: #0f172a; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                        <i class="fa-solid fa-feather-pointed"></i>
                                    </div>
                                @endif
                                <div>
                                    <h4 class="fw-bold mb-0 text-dark" style="font-size: 18px;">{{ $campaign->title }}</h4>
                                    <div class="text-primary fw-bold" style="font-size: 13px;">
                                        <i class="fa-solid fa-id-card-clip me-1"></i> অংশগ্রহণকারী / প্রতিনিধি নিবন্ধন ফরম
                                    </div>
                                    <small class="text-muted">আয়োজনে: আইডিয়া প্রকাশন &bull; ওয়েবসাইট: www.ideaabd.com</small>
                                </div>
                            </div>

                            <div class="text-end">
                                <div class="border border-dark rounded-2 px-3 py-1 bg-light d-inline-block text-center">
                                    <div style="font-size: 10px; font-weight: 700; color: #475569; text-transform: uppercase;">নিবন্ধন নম্বর</div>
                                    <div class="fs-5 fw-bold font-mono text-dark">#{{ $reg->registration_number }}</div>
                                </div>
                                <div class="mt-1">
                                    @if($statusApproved)
                                        <span class="status-pill status-confirmed px-2.5 py-0.5">Approved</span>
                                    @elseif($reg->status === 'rejected')
                                        <span class="status-pill status-rejected px-2.5 py-0.5">Rejected</span>
                                    @else
                                        <span class="status-pill status-pending px-2.5 py-0.5">Pending</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Profile Grid: Photo Box & Personal Details --}}
                        <div class="row g-3 mb-2 align-items-center">
                            {{-- Photo Box --}}
                            <div class="col-3 text-center">
                                <div class="applicant-photo-box">
                                    @if($photoSrc)
                                        <img src="{{ $photoSrc }}" alt="{{ $reg->name }}" class="applicant-photo-img" loading="eager" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'text-center text-muted p-2\'><div style=\'font-size: 32px;\'><i class=\'fa-solid fa-user-tie\'></i></div><div style=\'font-size: 10px; font-weight: 600;\'>ছবি লোড হয়নি</div></div>';">
                                    @else
                                        <div class="text-center text-muted p-2">
                                            <div style="font-size: 32px;"><i class="fa-solid fa-user-tie"></i></div>
                                            <div style="font-size: 10px; font-weight: 600;">ছবি সংযুক্ত নেই</div>
                                        </div>
                                    @endif
                                </div>
                                <div class="mt-1 small fw-bold font-mono text-secondary" style="font-size: 10px;">
                                    ID: #{{ $reg->registration_number }}
                                </div>
                            </div>

                            {{-- Core Personal Info --}}
                            <div class="col-9">
                                <table class="info-grid-table mb-0">
                                    <tr>
                                        <th>পূর্ণ নাম:</th>
                                        <td class="fw-bold fs-6 text-dark" colspan="3">{{ $reg->name }}</td>
                                    </tr>
                                    @if($campaign->isScholarship())
                                        <tr>
                                            <th>পিতার নাম:</th>
                                            <td>{{ $fData['father_name'] ?? '-' }}</td>
                                            <th>মাতার নাম:</th>
                                            <td>{{ $fData['mother_name'] ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>মোবাইল নম্বর:</th>
                                            <td class="font-mono fw-bold text-primary">{{ $reg->phone }}</td>
                                            <th>অভিভাবকের ফোন:</th>
                                            <td class="font-mono text-dark">{{ $fData['guardian_phone'] ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>শিক্ষা প্রতিষ্ঠান:</th>
                                            <td>{{ $fData['college_name'] ?? ($reg->institution_or_org ?: '-') }}</td>
                                            <th>শ্রেণি / ডিগ্রি / বিষয়:</th>
                                            <td>{{ trim(($fData['scholarship_degree'] ?? '') . ' • ' . ($fData['group'] ?? '') . ' • ' . ($fData['assigned_subject'] ?? '')) ?: '-' }}</td>
                                        </tr>
                                    @else
                                        @if(!empty($penName))
                                        <tr>
                                            <th>কলমী নাম / সাহিত্যিক নাম:</th>
                                            <td colspan="3">{{ $penName }}</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <th>মোবাইল নম্বর:</th>
                                            <td class="font-mono fw-bold text-primary">{{ $reg->phone }}</td>
                                            <th>ইমেইল:</th>
                                            <td class="font-mono">{{ $reg->email ?: '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>প্রতিষ্ঠান / সংগঠন:</th>
                                            <td>{{ $reg->institution_or_org ?: ($fData['organization_name'] ?? '-') }}</td>
                                            <th>পদবি / শাখা / পেশা:</th>
                                            <td>{{ $reg->designation_or_class ?: '-' }}</td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        </div>

                        {{-- Address Details --}}
                        <div class="info-section-title">
                            <i class="fa-solid fa-location-dot text-danger"></i> ১. ঠিকানা বিবরণী
                        </div>
                        <table class="info-grid-table">
                            <tr>
                                <th>দেশ (Country):</th>
                                <td>{{ $isForeign ? ($fData['country_name'] ?? 'প্রবাসী / আন্তর্জাতিক') : 'বাংলাদেশ' }}</td>
                                <th>বিভাগ (Division):</th>
                                <td class="fw-bold text-dark">{{ $reg->resolved_division }}</td>
                            </tr>
                            <tr>
                                <th>জেলা (District):</th>
                                <td class="fw-bold">{{ $reg->district ?: '-' }}</td>
                                <th>থানা / উপজেলা (Upazila):</th>
                                <td>{{ $reg->thana ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th>পোস্টকোড:</th>
                                <td class="font-mono">{{ $fData['zip_code'] ?? ($fData['perm_postcode'] ?? '-') }}</td>
                                <th>গ্রাম / সড়ক / হোল্ডিং:</th>
                                <td>{{ $reg->address ?: ($fData['permanent_address'] ?? ($fData['foreign_address'] ?? '-')) }}</td>
                            </tr>
                        </table>

                        @if($campaign->isScholarship())
                            {{-- Academic Results & Details for Scholarship --}}
                            <div class="info-section-title">
                                <i class="fa-solid fa-graduation-cap text-primary"></i> ২. শিক্ষাগত ফলাফল ও পরিবারের তথ্য
                            </div>
                            <table class="info-grid-table">
                                <tr>
                                    <th>এসএসসি (SSC):</th>
                                    <td>রোল: <strong>{{ $fData['ssc_roll'] ?? '-' }}</strong> | বোর্ড: {{ $fData['ssc_board'] ?? '-' }} | সন: {{ $fData['ssc_year'] ?? '-' }}</td>
                                    <th>এসএসসি জিপিএ:</th>
                                    <td class="font-mono fw-bold text-success">{{ $fData['ssc_gpa'] ?? '-' }}</td>
                                </tr>
                                @if(!empty($fData['hsc_roll']) || !empty($fData['hsc_gpa']))
                                <tr>
                                    <th>এইচএসসি (HSC):</th>
                                    <td>রোল: <strong>{{ $fData['hsc_roll'] ?? '-' }}</strong> | বোর্ড: {{ $fData['hsc_board'] ?? '-' }} | সন: {{ $fData['hsc_year'] ?? '-' }}</td>
                                    <th>এইচএসসি জিপিএ:</th>
                                    <td class="font-mono fw-bold text-success">{{ $fData['hsc_gpa'] ?? '-' }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th>জাতীয় পরিচয়পত্র / জন্মনিবন্ধন:</th>
                                    <td class="font-mono">{{ $fData['national_id'] ?? '-' }}</td>
                                    <th>পারিবারিক আয় / পেশা:</th>
                                    <td>{{ $fData['father_occupation'] ?? '' }} (বার্ষিক আয়: ৳{{ number_format((float)($fData['annual_income'] ?? 0)) }})</td>
                                </tr>
                                @if(!empty($fData['pay_account_no']))
                                <tr>
                                    <th>বৃত্তির পেমেন্ট হিসাব:</th>
                                    <td colspan="3" class="font-mono">
                                        {{ $fData['pay_method'] ?? 'Bank' }} &bull; হিসাব নম্বর: <strong>{{ $fData['pay_account_no'] }}</strong> &bull; ব্যাংক / শাখা: {{ $fData['pay_bank_name'] ?? '' }} ({{ $fData['pay_bank_branch'] ?? '' }}) &bull; হিসাবের নাম: {{ $fData['pay_account_name'] ?? '' }}
                                    </td>
                                </tr>
                                @endif
                            </table>
                        @else
                            {{-- Publication / Category / Magazine Details for Writers & General Events --}}
                            <div class="info-section-title">
                                <i class="fa-solid fa-book-bookmark text-primary"></i> ২. সাহিত্য ও প্রকাশনা তথ্য
                            </div>
                            <table class="info-grid-table">
                                <tr>
                                    <th>অংশগ্রহণের ক্যাটাগরি:</th>
                                    <td class="fw-bold text-dark">{{ $authorCat ?: '-' }}</td>
                                    <th>লিটিলম্যাগাজিনের নাম:</th>
                                    <td class="fw-bold text-warning-emphasis">{{ $magName ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>প্রকাশিত বইয়ের সংখ্যা:</th>
                                    <td>{{ !empty($fData['published_book_count']) ? $fData['published_book_count'] . ' টি' : '-' }}</td>
                                    <th>প্রকাশনা সংস্থা:</th>
                                    <td>{{ $fData['publisher_name'] ?? '-' }}</td>
                                </tr>
                                @if(!empty($fData['published_books']))
                                    <tr>
                                        <th>প্রকাশিত বইসমূহের নাম:</th>
                                        <td colspan="3">{{ $fData['published_books'] }}</td>
                                    </tr>
                                @endif
                            </table>
                        @endif

                        {{-- Fee & Payment Info (If Applicable) --}}
                        @if($campaign->has_fee_or_donation)
                            <div class="info-section-title">
                                <i class="fa-solid fa-receipt text-success"></i> ৩. ফি ও পেমেন্ট বিবরণী
                            </div>
                            <table class="info-grid-table">
                                <tr>
                                    <th>নিবন্ধন ফি:</th>
                                    <td class="font-mono fw-bold">৳{{ number_format($reg->amount_paid) }}</td>
                                    <th>পেমেন্ট মাধ্যম:</th>
                                    <td>{{ ucfirst($reg->payment_method ?? 'N/A') }}</td>
                                </tr>
                                <tr>
                                    <th>ট্রানজেকশন আইডি:</th>
                                    <td class="font-mono fw-bold text-primary">{{ $reg->transaction_id ?: '-' }}</td>
                                    <th>পেমেন্ট স্ট্যাটাস:</th>
                                    <td>
                                        <span class="status-pill {{ $reg->payment_status === 'verified' ? 'status-confirmed' : 'status-pending' }}">
                                            {{ $reg->payment_status === 'verified' ? 'Verified (যাচাইকৃত)' : ucfirst($reg->payment_status) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        @endif

                        {{-- Signature & Official Approval Section --}}
                        <div class="form-sheet-signature-box d-flex justify-content-between align-items-end">
                            <div class="text-center" style="width: 240px;">
                                <div style="border-bottom: 1.5px dotted #334155; min-height: 38px; display: flex; align-items: flex-end; justify-content: center; padding-bottom: 3px;">
                                    <span style="font-weight: 700; font-size: 14.5px; color: #0f172a; letter-spacing: 0.3px;">
                                        {{ $reg->name }}
                                    </span>
                                </div>
                                <div class="small fw-semibold mt-1 text-dark">আবেদনকারীর স্বাক্ষর ও তারিখ</div>
                            </div>

                            <div class="text-center" style="font-size: 11px; color: #64748b;">
                                <div>ভেরিফিকেশন লিংক: <span class="font-mono text-dark">www.ideaabd.com/card/{{ $reg->registration_number }}</span></div>
                                <div>নিবন্ধন তারিখ: {{ $reg->created_at->format('d/m/Y h:i A') }}</div>
                            </div>

                            <div class="text-center" style="width: 240px;">
                                <div style="border-bottom: 1.5px dotted #334155; min-height: 38px;"></div>
                                <div class="small fw-semibold mt-1 text-dark">যাচাইকারী / অনুমোদকের স্বাক্ষর ও সীল</div>
                            </div>
                        </div>
                    </div>
                @endforeach
        </div>

    </div>

    {{-- Interactive Controls & Print Script --}}
    <script>
        function toggleSection(sectionId, isVisible) {
            const el = document.getElementById(sectionId);
            if (el) {
                el.style.display = isVisible ? 'block' : 'none';
            }
        }

        function printSummaryOnly() {
            const formsSection = document.getElementById('printFormsSection');
            const summarySection = document.getElementById('printSummarySection');
            const toggleSummary = document.getElementById('toggleSummaryTable');
            const toggleForms = document.getElementById('toggleForms');

            if (summarySection) summarySection.style.display = 'block';
            if (formsSection) formsSection.style.display = 'none';
            if (toggleSummary) toggleSummary.checked = true;
            if (toggleForms) toggleForms.checked = false;

            window.focus();
            window.print();
        }

        function printAllSections() {
            const formsSection = document.getElementById('printFormsSection');
            const summarySection = document.getElementById('printSummarySection');
            const toggleSummary = document.getElementById('toggleSummaryTable');
            const toggleForms = document.getElementById('toggleForms');

            if (summarySection) summarySection.style.display = 'block';
            if (formsSection) formsSection.style.display = 'block';
            if (toggleSummary) toggleSummary.checked = true;
            if (toggleForms) toggleForms.checked = true;

            window.focus();
            window.print();
        }

        window.addEventListener('load', function() {
            setTimeout(function() {
                window.focus();
            }, 300);
        });
    </script>
</body>
</html>
