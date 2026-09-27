@php
    $formData = $registration->form_data ?? [];
    $campaign = $registration->campaign;
    $libName = $registration->institution_or_org ?: ($formData['library_name'] ?? 'Library');
    $libType = $formData['library_type'] ?? ($registration->designation_or_class ?: 'Public Library');
    $allocated = intval($formData['books_allocated'] ?? 0);
    $dispatchedDate = $formData['dispatched_date'] ?? null;
    $receivedCount = intval($formData['received_books_count'] ?? 0);
    $receivedDate = $formData['received_date'] ?? null;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Book Grant Token & Slip — #{{ $registration->registration_number }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            padding: 30px 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .token-card {
            width: 100%;
            max-width: 800px;
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 2px solid #047857;
            overflow: hidden;
            position: relative;
        }
        .token-header {
            background: linear-gradient(135deg, #064e3b 0%, #047857 60%, #059669 100%);
            color: #ffffff;
            padding: 26px 30px;
            text-align: center;
            position: relative;
        }
        .token-header h1 {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 4px;
        }
        .token-header p {
            font-size: 13.5px;
            opacity: 0.9;
        }
        .token-badge {
            display: inline-block;
            background: #fef08a;
            color: #854d0e;
            font-weight: 700;
            font-size: 12px;
            padding: 4px 14px;
            border-radius: 20px;
            margin-bottom: 8px;
        }
        .token-body {
            padding: 30px;
        }
        .reg-banner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #ecfdf5;
            border: 1px dashed #059669;
            border-radius: 12px;
            padding: 12px 20px;
            margin-bottom: 24px;
        }
        .reg-number {
            font-family: monospace;
            font-size: 20px;
            font-weight: 800;
            color: #064e3b;
            letter-spacing: 1px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 24px;
        }
        .info-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 16px;
        }
        .info-label {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .info-val {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
        }
        .dispatch-box {
            background: #fffbeb;
            border: 1.5px solid #fde68a;
            border-radius: 12px;
            padding: 18px 20px;
            margin-bottom: 24px;
        }
        .signature-row {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px dashed #cbd5e1;
        }
        .sig-box {
            text-align: center;
            width: 220px;
        }
        .sig-line {
            border-top: 1.5px solid #475569;
            margin-bottom: 6px;
        }
        .sig-title {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
        }
        .btn-print-bar {
            margin-bottom: 20px;
            display: flex;
            gap: 12px;
        }
        .btn-print {
            background: #047857;
            color: #ffffff;
            border: none;
            padding: 10px 24px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(4, 120, 87, 0.3);
            text-decoration: none;
        }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .btn-print-bar {
                display: none !important;
            }
            .token-card {
                border: 1px solid #047857;
                box-shadow: none;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

    <div class="btn-print-bar">
        <button onclick="window.print()" class="btn-print">
            <i class="fa-solid fa-print"></i> Print Slip / Save PDF
        </button>
        <a href="{{ url('/pathagar/acknowledgment/' . $registration->registration_number) }}" class="btn-print" style="background: #0284c7;">
            <i class="fa-solid fa-signature"></i> Online Acknowledgment
        </a>
    </div>

    <div class="token-card" id="printArea">
        <div class="token-header">
            <div class="token-badge">Annual Free Book Distribution Campaign 2026</div>
            <h1>Idea Prokashon & Books of Idea</h1>
            <p>Library Book Grant Allocation Token & Official Slip</p>
        </div>

        <div class="token-body">
            <div class="reg-banner">
                <div>
                    <div style="font-size: 12px; color: #047857; font-weight: 600;">Token / Registration ID:</div>
                    <div class="reg-number">#{{ $registration->registration_number }}</div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 12px; color: #64748b;">Registration Date:</div>
                    <div style="font-weight: 700; color: #0f172a;">{{ $registration->created_at ? $registration->created_at->format('d M, Y') : date('d M, Y') }}</div>
                </div>
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Library / Institution</div>
                    <div class="info-val" style="color: #047857;">{{ $libName }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Library Type & Est.</div>
                    <div class="info-val">{{ $libType }} @if(!empty($formData['established_year'])) (Est: {{ $formData['established_year'] }}) @endif</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Representative & Designation</div>
                    <div class="info-val">{{ $registration->name }} ({{ $registration->designation_or_class ?: 'Representative' }})</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Contact Phone</div>
                    <div class="info-val">{{ $registration->phone }}</div>
                </div>
                <div class="info-item" style="grid-column: span 2;">
                    <div class="info-label">Address & Location</div>
                    <div class="info-val">{{ $registration->address }}, {{ $registration->thana ? $registration->thana . ', ' : '' }}{{ $registration->district }} ({{ $formData['division'] ?? '' }})</div>
                </div>
            </div>

            <div class="dispatch-box">
                <h4 style="font-size: 15px; font-weight: 800; color: #854d0e; margin-bottom: 12px;">
                    <i class="fa-solid fa-boxes-stacked me-1"></i> Book Grant Allocation & Status
                </h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
                    <div>
                        <div style="font-size: 11px; color: #854d0e; text-transform: uppercase; font-weight: 600;">Allocated Books</div>
                        <div style="font-size: 18px; font-weight: 800; color: #064e3b;">{{ $allocated > 0 ? $allocated . ' Books' : 'Under Review' }}</div>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: #854d0e; text-transform: uppercase; font-weight: 600;">Dispatch Date</div>
                        <div style="font-size: 14px; font-weight: 700; color: #1e293b;">{{ $dispatchedDate ? date('d M, Y', strtotime($dispatchedDate)) : '—' }}</div>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: #854d0e; text-transform: uppercase; font-weight: 600;">Receipt Status</div>
                        <div style="font-size: 14px; font-weight: 700; color: {{ $receivedCount > 0 ? '#166534' : '#d97706' }};">
                            {{ $receivedCount > 0 ? 'Acknowledged (' . $receivedCount . ' Books)' : ($allocated > 0 ? 'Dispatched' : 'Pending') }}
                        </div>
                    </div>
                </div>
                @if(!empty($formData['allocated_books_list']) && is_array($formData['allocated_books_list']))
                    <div style="margin-top: 14px; border-top: 1px dashed #fde68a; padding-top: 10px;">
                        <div style="font-size: 12px; font-weight: 700; color: #854d0e; margin-bottom: 6px;">
                            <i class="fa-solid fa-list-check me-1"></i> Itemized Books Breakdown Table:
                        </div>
                        <table style="width: 100%; border-collapse: collapse; font-size: 12px; background: #ffffff; border-radius: 6px; overflow: hidden; border: 1px solid #fde68a;">
                            <thead>
                                <tr style="background: #fef3c7; color: #92400e; text-align: left;">
                                    <th style="padding: 6px 8px; width: 35px;">#</th>
                                    <th style="padding: 6px 8px;">Book Title</th>
                                    <th style="padding: 6px 8px;">Author</th>
                                    <th style="padding: 6px 8px;">Category</th>
                                    <th style="padding: 6px 8px; text-align: center; width: 70px;">Copies</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($formData['allocated_books_list'] as $i => $item)
                                    <tr style="border-bottom: 1px solid #fef3c7;">
                                        <td style="padding: 5px 8px; color: #94a3b8;">{{ $i + 1 }}</td>
                                        <td style="padding: 5px 8px; font-weight: 600; color: #1e293b;">{{ $item['title'] ?? '—' }}</td>
                                        <td style="padding: 5px 8px; color: #64748b;">{{ $item['author'] ?? '—' }}</td>
                                        <td style="padding: 5px 8px; color: #64748b;">{{ $item['category'] ?? 'General' }}</td>
                                        <td style="padding: 5px 8px; text-align: center; font-weight: 700; color: #047857;">{{ $item['copies'] ?? 1 }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="signature-row">
                <div class="sig-box">
                    <div class="sig-line"></div>
                    <div class="sig-title">Library Representative Signature</div>
                </div>
                <div class="sig-box">
                    <div class="sig-line"></div>
                    <div class="sig-title">Authorized Officer (Idea Prokashon)</div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
