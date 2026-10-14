<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isLittleMagOnly ? 'লিটিলম্যাগাজিন সম্পাদক তালিকা' : 'অংশগ্রহণকারী তালিকা' }} — {{ $campaign->title }}</title>
    
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
            font-size: 13px;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        /* Top Action Bar (Hidden on print) */
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
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .print-container {
            max-width: 1280px;
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
            max-height: 70px;
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
            font-size: 12px;
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
        }
        .print-table tbody td {
            padding: 6px 10px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
            color: #0f172a;
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

        /* Print Media Styles */
        @media print {
            @page {
                size: A4 landscape;
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
                font-size: 11px !important;
            }
            .print-table thead th {
                background: #1e293b !important;
                color: #ffffff !important;
                padding: 6px 8px !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .print-table tbody td {
                padding: 5px 8px !important;
                border: 1px solid #94a3b8 !important;
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
                {{ $isLittleMagOnly ? 'লিটিলম্যাগাজিন সম্পাদক তালিকা প্রিন্ট' : 'এক কমান্ডে সকল এন্ট্রি প্রিন্ট' }}
            </span>
            <span class="badge bg-secondary font-mono">{{ $totalCount }} টি এন্ট্রি</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3" onclick="window.close()">
                <i class="fa-solid fa-xmark me-1"></i> বন্ধ করুন
            </button>
            <button type="button" class="btn btn-sm btn-success rounded-pill px-4 fw-bold shadow-sm" onclick="window.print()">
                <i class="fa-solid fa-print me-1.5"></i> এখনই প্রিন্ট করুন (Ctrl + P)
            </button>
        </div>
    </div>

    {{-- Printable Sheet Container --}}
    <div class="print-container">
        
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

        {{-- Main Table --}}
        <table class="print-table">
            <thead>
                <tr>
                    <th style="width: 35px;" class="text-center">ক্র.</th>
                    <th style="width: 100px;">আইডি</th>
                    <th>অংশগ্রহণকারী ও ক্যাটাগরি</th>
                    <th>যোগাযোগ</th>
                    <th>ঠিকানা</th>
                    @if($campaign->has_fee_or_donation)
                        <th style="width: 75px;">ফি</th>
                        <th style="width: 80px;">পেমেন্ট</th>
                    @endif
                    <th style="width: 85px;" class="text-center">স্ট্যাটাস</th>
                    <th style="width: 95px;">নিবন্ধন তারিখ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($allRegistrations as $reg)
                    @php
                        $authorCat = $reg->form_data['author_category'] ?? ($reg->form_data['author_categories'][0] ?? null);
                        $magName = $reg->form_data['magazine_name'] ?? null;
                    @endphp
                    <tr>
                        <td class="text-center font-mono">{{ $loop->iteration }}</td>
                        <td class="font-mono fw-bold text-dark">{{ $reg->registration_number }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $reg->name }}</div>
                            @if(!empty($reg->form_data['pen_name']))
                                <small class="text-muted d-block">কলমী নাম: {{ $reg->form_data['pen_name'] }}</small>
                            @endif
                            @if($magName)
                                <span class="badge bg-warning-subtle text-dark border border-warning-subtle py-0 px-1" style="font-size: 10px;">
                                    পত্রিকা: {{ $magName }}
                                </span>
                            @elseif($authorCat)
                                <span class="badge bg-light text-secondary border py-0 px-1" style="font-size: 10px;">
                                    {{ $authorCat }}
                                </span>
                            @elseif($reg->designation_or_class)
                                <small class="text-muted d-block" style="font-size: 10.5px;">{{ $reg->designation_or_class }}</small>
                            @endif
                        </td>
                        <td>
                            <div class="font-mono fw-semibold">{{ $reg->phone }}</div>
                            @if($reg->email)
                                <small class="text-muted d-block text-truncate" style="max-width: 140px;">{{ $reg->email }}</small>
                            @endif
                        </td>
                        <td>
                            <div class="fw-medium">{{ $reg->district ?: '-' }}</div>
                            @if($reg->thana)
                                <small class="text-muted d-block">{{ $reg->thana }}</small>
                            @endif
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
                            @if(in_array($reg->status, ['confirmed', 'approved', 'selected']))
                                <span class="status-pill status-confirmed">Approved</span>
                            @elseif($reg->status === 'rejected')
                                <span class="status-pill status-rejected">Rejected</span>
                            @else
                                <span class="status-pill status-pending">{{ ucfirst($reg->status) }}</span>
                            @endif
                        </td>
                        <td class="font-mono text-muted" style="font-size: 11px;">
                            {{ $reg->created_at->format('d/m/Y') }}<br>
                            <span style="font-size: 10px;">{{ $reg->created_at->format('h:i A') }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">কোনো রেকর্ড পাওয়া যায়নি।</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Footer Note --}}
        <div class="d-flex justify-content-between align-items-center pt-3 border-top text-muted small">
            <div>
                ক্যাম্পেইন: <strong>{{ $campaign->title }}</strong> &bull; আইডিয়া প্রকাশন ম্যানেজমেন্ট সিস্টেম
            </div>
            <div>
                পৃষ্ঠা ১ / সর্বমোট প্রিন্ট কপি
            </div>
        </div>

    </div>

    {{-- Auto Print Script --}}
    <script>
        window.addEventListener('load', function() {
            // Optional slight delay to ensure fonts and styles are fully rendered
            setTimeout(function() {
                // Focus and trigger print
                window.focus();
            }, 300);
        });
    </script>
</body>
</html>
