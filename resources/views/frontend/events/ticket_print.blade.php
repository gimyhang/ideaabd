@php
    $formData = $registration->form_data ?? [];
    $campaign = $registration->campaign;
    $isPdf = $isPdf ?? false;
    $cardDesign = $campaign->form_settings['card_design'] ?? [];
    $cardBg = $cardDesign['bg_image'] ?? 'campaigns/cards/rangpur_card_bg.jpg';
    $themeColor = $cardDesign['theme_color'] ?? ($campaign->theme_color ?: '#7f1d1d');
    $badgeText = $cardDesign['badge_text'] ?? 'আমন্ত্রণ কার্ড';
    $photoPath = $formData['student_photo'] ?? ($formData['author_photo'] ?? null);
@endphp
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>আমন্ত্রণ কার্ড — {{ $registration->registration_number }} — {{ $campaign->title }}</title>
    <style>
        /* 3.5 x 5 Inch Standard Invitation Card Dimension */
        @page {
            size: 3.5in 5in;
            margin: 0;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        body {
            font-family: 'Hind Siliguri', 'SolaimanLipi', Arial, sans-serif;
            background-color: #f1f5f9;
            color: #1e1b4b;
            margin: 0;
            padding: 20px 0;
            font-size: 11px;
        }
        .no-print {
            display: block;
        }
        @media print {
            body {
                background: none !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .card-wrapper {
                margin: 0 auto !important;
                padding: 0 !important;
                width: 3.5in !important;
                height: 5in !important;
                page-break-after: avoid;
            }
            .invitation-card {
                width: 3.5in !important;
                height: 5in !important;
                box-shadow: none !important;
                border: 1px solid #b45309 !important;
                border-radius: 0 !important;
            }
        }
        .card-wrapper {
            width: 3.5in;
            margin: 0 auto;
        }
        .action-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
            width: 100%;
        }
        .btn-act {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid transparent;
        }
        .btn-print { background: #0f172a; color: #fff; }
        .btn-pdf { background: #dc2626; color: #fff; }
        .btn-back { background: #e2e8f0; color: #334155; }

        /* 3.5in x 5in Invitation Card Layout */
        .invitation-card {
            width: 3.5in;
            height: 5in;
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            border: 2px solid #b45309;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            background: #d97706;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 12px 14px;
        }

        /* Background Art */
        .card-bg-layer {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('{{ $isPdf ? public_path('images/events/rangpur_card_bg.jpg') : asset('images/events/rangpur_card_bg.jpg') }}');
            background-repeat: no-repeat;
            background-position: center bottom;
            background-size: cover;
            z-index: 1;
        }

        /* Content Layer with Glass Effect */
        .card-content-layer {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .card-inner-box {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            padding: 10px 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        /* Top Logo & Branding Space */
        .top-logo-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1.5px solid #b45309;
            padding-bottom: 6px;
            margin-bottom: 6px;
        }
        .logo-box {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .logo-circle {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #991b1b;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: bold;
        }
        .logo-text-org {
            font-size: 10.5px;
            font-weight: bold;
            color: #7f1d1d;
            line-height: 1.15;
        }
        .badge-invite {
            background: #991b1b;
            color: #ffffff;
            font-size: 9.5px;
            font-weight: bold;
            padding: 2.5px 8px;
            border-radius: 10px;
        }

        .event-main-heading {
            text-align: center;
            font-size: 11px;
            font-weight: 800;
            color: #7f1d1d;
            line-height: 1.3;
            margin-bottom: 2px;
        }
        .event-sub-heading {
            text-align: center;
            font-size: 8.5px;
            color: #475569;
            margin-bottom: 6px;
        }

        /* Author Profile Box */
        .author-info-flex {
            display: flex;
            gap: 8px;
            align-items: center;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 6px 8px;
            margin-bottom: 6px;
        }
        .author-thumb {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            border: 1.5px solid #b45309;
            object-fit: cover;
            flex-shrink: 0;
            background: #f8fafc;
        }
        .author-thumb-placeholder {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            border: 1.5px solid #b45309;
            background: #fef3c7;
            color: #991b1b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        .author-meta-name {
            font-size: 11px;
            font-weight: bold;
            color: #0f172a;
            line-height: 1.2;
            text-transform: uppercase;
        }
        .author-meta-cat {
            font-size: 9.5px;
            color: #b91c1c;
            font-weight: bold;
        }

        /* Key-Value Details */
        .details-table {
            width: 100%;
            font-size: 9px;
            border-collapse: collapse;
            margin-bottom: 6px;
            background: #fefce8;
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid #fef08a;
        }
        .details-table td {
            padding: 3px 6px;
            border-bottom: 1px solid #fef08a;
        }
        .details-table tr:last-child td {
            border-bottom: none;
        }
        .details-table .lbl {
            color: #713f12;
            width: 32%;
            font-weight: 600;
        }
        .details-table .val {
            color: #0f172a;
            font-weight: bold;
        }

        /* Invitation Message */
        .invitation-quote {
            text-align: center;
            font-size: 8px;
            color: #334155;
            line-height: 1.35;
            margin-bottom: 4px;
        }

        /* Barcode & Verification Seal */
        .card-bottom-bar {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 6px;
            padding: 4px 6px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 8px;
            border: 1px solid rgba(0,0,0,0.06);
        }
        .barcode-txt {
            font-family: monospace;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 1px;
            color: #7f1d1d;
        }
        .approved-tag {
            background: #dcfce7;
            color: #15803d;
            font-size: 7.5px;
            font-weight: 800;
            padding: 2px 6px;
            border-radius: 4px;
            border: 1px solid #86efac;
            text-transform: uppercase;
        }
    </style>
</head>
<body>

    <div class="card-wrapper">

        {{-- Top Action Buttons --}}
        @if(!$isPdf)
            <div class="action-bar no-print">
                <a href="{{ url('/') }}" class="btn-act btn-back">← Home</a>
                <div style="display: flex; gap: 6px;">
                    <button type="button" onclick="window.print()" class="btn-act btn-print">
                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        Print (৩.৫x৫)
                    </button>
                    <a href="{{ route('event.registration.pdf', $registration->registration_number) }}" class="btn-act btn-pdf">
                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Download PDF
                    </a>
                </div>
            </div>
        @endif

        {{-- 3.5 x 5 Inch Invitation Card Container --}}
        <div class="invitation-card">
            
            {{-- Background Graphic Layer --}}
            <div class="card-bg-layer"></div>

            {{-- Foreground Content Layer --}}
            <div class="card-content-layer">

                {{-- Upper Information Box --}}
                <div class="card-inner-box">
                    
                    {{-- 1. Logo & Header Space --}}
                    <div class="top-logo-row">
                        <div class="logo-box">
                            <div class="logo-circle">
                                <span>✒️</span>
                            </div>
                            <div>
                                <div class="logo-text-org">আইডিয়া প্রকাশন</div>
                                <div style="font-size: 7.5px; color: #64748b; font-family: monospace;">www.ideaabd.com</div>
                            </div>
                        </div>
                        <span class="badge-invite">আমন্ত্রণ কার্ড</span>
                    </div>

                    {{-- 2. Festival Title --}}
                    <div class="event-main-heading">
                        রংপুর সাহিত্য উৎসব ও লিটিলম্যাগমেলা ২০২৬
                    </div>
                    <div class="event-sub-heading">
                        লেখকদের অংশগ্রহণ ও ডেলিগেট পাস
                    </div>

                    {{-- 3. Author Profile Strip --}}
                    <div class="author-info-flex">
                        @if($photoPath && (file_exists(public_path('storage/' . $photoPath)) || file_exists(storage_path('app/public/' . $photoPath))))
                            <img src="{{ $isPdf ? public_path('storage/' . $photoPath) : asset('storage/' . $photoPath) }}" class="author-thumb" alt="Author Photo">
                        @else
                            <div class="author-thumb-placeholder">✍️</div>
                        @endif

                        <div style="min-width: 0; flex-grow: 1;">
                            <div style="font-size: 7.5px; color: #64748b;">শ্রদ্ধেয় লেখক / অতিথি:</div>
                            <div class="author-meta-name">{{ $registration->name }}</div>
                            <div class="author-meta-cat">
                                {{ $registration->designation_or_class ?: ($formData['author_category'] ?? 'লেখক') }}
                            </div>
                        </div>
                    </div>

                    {{-- 4. Key Details Table --}}
                    <table class="details-table">
                        <tr>
                            <td class="lbl">মোবাইল নং:</td>
                            <td class="val font-monospace">{{ $registration->phone }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">জেলা ও এলাকা:</td>
                            <td class="val">{{ $registration->district ?: 'রংপুর' }}</td>
                        </tr>
                        @if(!empty($formData['published_books_count']) && $formData['published_books_count'] !== '০ (এখনও বই প্রকাশিত হয়নি)')
                            <tr>
                                <td class="lbl">গ্রন্থ সংখ্যা:</td>
                                <td class="val">{{ $formData['published_books_count'] }}</td>
                            </tr>
                        @endif
                        @if(!empty($formData['magazine_name']))
                            <tr>
                                <td class="lbl">ছোটকাগজ:</td>
                                <td class="val">{{ $formData['magazine_name'] }}</td>
                            </tr>
                        @endif
                    </table>

                    {{-- 5. Formal Invitation Note --}}
                    <div class="invitation-quote">
                        সাহিত্য উৎসব ও লিটিলম্যাগমেলায় আপনার উপস্থিতি ও অংশগ্রহণ আমাদের সম্মানিত করবে।
                    </div>

                </div>

                {{-- Lower Verification & Barcode Bar --}}
                <div class="card-bottom-bar">
                    <span class="barcode-txt">#{{ $registration->registration_number }}</span>
                    <span class="approved-tag">✓ APPROVED DELEGATE</span>
                </div>

            </div>

        </div>

    </div>

</body>
</html>
