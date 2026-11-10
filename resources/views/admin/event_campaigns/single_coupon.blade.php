@php
    $eventTitle = $campaign->title ?: 'রংপুর সাহিত্য উৎসব ও লেখক সমাবেশ ২০২৬';
    $rawRegNo = $registration->registration_number ?: ('ID-' . $registration->id);
    $cleanCardNo = preg_replace('/^RSU-?(?:2026-?)?/i', '', $rawRegNo);
    $cardDisplay = !empty($cleanCardNo) ? (is_numeric($cleanCardNo) ? sprintf('%03d', (int)$cleanCardNo) : $cleanCardNo) : $rawRegNo;
    $cardNoFormatted = 'RSU-' . $cardDisplay;

    $pName = $registration->name ?: 'অংশগ্রহণকারী';
    $pCat = $registration->resolved_category ?: ($registration->designation_or_class ?: 'কবি ও প্রতিনিধি');
    $pPhone = $registration->phone ?: '';
    $pMaskedPhone = !empty($pPhone) ? (substr($pPhone, 0, 5) . '***' . substr($pPhone, -3)) : '';
    $stripDomId = 'single_coupon_strip';
    $pngFileName = 'Coupon_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $cardNoFormatted) . '.png';
@endphp
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>খাবার ও র‍্যাফেল ড্র কুপন — {{ $pName }} ({{ $cardNoFormatted }})</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700;800&family=Noto+Serif+Bengali:wght@600;700;800;900&family=Fira+Code:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

    <style>
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        html, body {
            font-family: 'Hind Siliguri', sans-serif;
            background-color: #0b1120;
            color: #0f172a;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .action-bar {
            position: sticky;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
            z-index: 1000;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .btn-act {
            font-size: 13px;
            font-weight: 700;
            padding: 8px 18px;
            border-radius: 24px;
            text-decoration: none;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        .btn-download { background: linear-gradient(135deg, #10b981, #059669); color: #fff; }
        .btn-print { background: #334155; color: #fff; }
        .btn-card { background: #38bdf8; color: #0f172a; }
        .btn-all { background: #facc15; color: #0f172a; }
        .btn-act:hover { transform: translateY(-2px); box-shadow: 0 4px 14px rgba(0, 0, 0, 0.35); }

        .stage-wrapper {
            padding: 40px 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
        }

        /* EXACT 8in x 2in STRIP */
        .coupon-strip {
            width: 8in;
            min-width: 8in;
            max-width: 8in;
            height: 2in;
            min-height: 2in;
            max-height: 2in;
            background: #ffffff;
            border: 1.5px solid #1e293b;
            border-radius: 5px;
            display: grid;
            grid-template-columns: repeat(5, 1.6in);
            overflow: hidden;
            position: relative;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            background-color: #ffffff;
        }

        .coupon-col {
            width: 1.6in;
            height: 2in;
            padding: 4px 6px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
            background: #ffffff;
        }
        .coupon-col:not(:last-child) {
            border-right: 1.5px dashed #94a3b8;
        }
        .cut-guide-icon {
            position: absolute;
            right: -6px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 8px;
            color: #64748b;
            background: #ffffff;
            padding: 2px 0;
            line-height: 1;
            z-index: 10;
        }
        .coupon-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            padding: 2px 4px;
            border-radius: 4px;
            font-size: 10.5px;
            font-weight: 800;
            color: #ffffff;
            text-align: center;
            letter-spacing: -0.2px;
            line-height: 1.2;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
        }
        .coupon-col-breakfast { background: linear-gradient(180deg, #fffbeb 0%, #ffffff 100%); }
        .coupon-col-breakfast .coupon-badge { background: linear-gradient(135deg, #d97706, #b45309); }
        .coupon-col-breakfast .coupon-card-badge { border-color: #d97706; color: #92400e; }

        .coupon-col-snacks1 { background: linear-gradient(180deg, #ecfdf5 0%, #ffffff 100%); }
        .coupon-col-snacks1 .coupon-badge { background: linear-gradient(135deg, #059669, #047857); }
        .coupon-col-snacks1 .coupon-card-badge { border-color: #059669; color: #065f46; }

        .coupon-col-lunch { background: linear-gradient(180deg, #eff6ff 0%, #ffffff 100%); }
        .coupon-col-lunch .coupon-badge { background: linear-gradient(135deg, #2563eb, #1d4ed8); }
        .coupon-col-lunch .coupon-card-badge { border-color: #2563eb; color: #1e40af; }

        .coupon-col-snacks2 { background: linear-gradient(180deg, #faf5ff 0%, #ffffff 100%); }
        .coupon-col-snacks2 .coupon-badge { background: linear-gradient(135deg, #7c3aed, #6d28d9); }
        .coupon-col-snacks2 .coupon-card-badge { border-color: #7c3aed; color: #5b21b6; }

        .coupon-col-raffle { background: linear-gradient(180deg, #fff1f2 0%, #ffffff 100%); }
        .coupon-col-raffle .coupon-badge { background: linear-gradient(135deg, #e11d48, #be123c); }
        .coupon-col-raffle .coupon-card-badge { border-color: #e11d48; color: #9f1239; background: #ffe4e6; }

        .coupon-event-name {
            font-size: 7.2px;
            font-weight: 700;
            color: #334155;
            text-align: center;
            line-height: 1.1;
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        /* Prominent Card Number Badge */
        .coupon-card-badge {
            background: #ffffff;
            border: 1.5px solid #0f172a;
            border-radius: 4px;
            padding: 1px 2px;
            text-align: center;
            font-family: 'Fira Code', 'Hind Siliguri', monospace;
            font-size: 20px;
            font-weight: 900;
            line-height: 1.1;
            letter-spacing: -0.5px;
            margin: 2px 0;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .coupon-card-raffle {
            border-color: #e11d48 !important;
            color: #be123c !important;
            background: #ffe4e6 !important;
        }

        /* Participant Name & Category */
        .coupon-participant-info {
            text-align: center;
            line-height: 1.12;
            padding: 0 1px;
            margin-top: 1px;
        }
        .coupon-participant-name {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.12;
            text-align: center;
            max-height: 46px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            word-break: break-word;
            margin: 1px 0;
        }
        .coupon-participant-category {
            font-size: 8px;
            font-weight: 600;
            color: #475569;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
            margin-top: 1px;
        }

        /* Mobile number in Column 5 */
        .raffle-phone {
            font-size: 20px;
            font-weight: 900;
            font-family: 'Fira Code', monospace;
            color: #be123c;
            line-height: 1.1;
            letter-spacing: -0.8px;
            text-align: center;
            display: block;
            margin: 2px 0 1px 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .coupon-footer {
            border-top: 0.8px solid #cbd5e1;
            padding-top: 2px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 6.5px;
            color: #64748b;
            font-weight: 600;
            line-height: 1;
        }
        .coupon-serial { font-family: 'Fira Code', monospace; font-size: 6.8px; color: #334155; font-weight: 700; }
        .coupon-barcode-graphic { letter-spacing: 0.5px; font-family: monospace; font-size: 8px; font-weight: 900; color: #0f172a; line-height: 0.9; }
        .raffle-box-drop { font-size: 7px; font-weight: 700; color: #be123c; background: rgba(225, 29, 72, 0.1); border: 0.5px dashed #e11d48; padding: 1px 2px; border-radius: 3px; text-align: center; margin-top: 1px; line-height: 1; }

        @media print {
            @page {
                size: 8in 2in;
                margin: 0;
            }
            html, body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .no-print, .action-bar { display: none !important; }
            .stage-wrapper { padding: 0 !important; }
            .coupon-strip {
                border: 1px solid #000000 !important;
                box-shadow: none !important;
                margin: 0 !important;
            }
        }
    </style>
</head>
<body>

    <div class="action-bar no-print">
        <button type="button" onclick="downloadStripPng()" class="btn-act btn-download">
            <i class="fa-solid fa-download"></i> কুপন স্লিপ ডাউনলোড (PNG)
        </button>
        <button type="button" onclick="window.print()" class="btn-act btn-print">
            <i class="fa-solid fa-print"></i> প্রিন্ট করুন (Ctrl + P)
        </button>
        <a href="{{ route('event.registration.print', $registration->registration_number) }}" class="btn-act btn-card">
            <i class="fa-solid fa-id-card"></i> আমন্ত্রণ কার্ড
        </a>
        <a href="{{ route('admin.event-campaigns.coupons', $campaign->id) }}" class="btn-act btn-all">
            <i class="fa-solid fa-list-check"></i> সকল কুপন তালিকা
        </a>
    </div>

    <div class="stage-wrapper">
        <div class="coupon-strip" id="{{ $stripDomId }}">
            
            {{-- COLUMN 1: সকালের নাস্তা --}}
            <div class="coupon-col coupon-col-breakfast">
                <span class="cut-guide-icon"><i class="fa-solid fa-scissors"></i></span>
                <div>
                    <div class="coupon-badge"><i class="fa-solid fa-mug-hot"></i> সকালের নাস্তা</div>
                    <div class="coupon-event-name">{{ $eventTitle }}</div>
                    <div class="coupon-card-badge">কার্ড নং: {{ $cardNoFormatted }}</div>
                    <div class="coupon-participant-info">
                        <span class="coupon-participant-name" title="{{ $pName }}">{{ $pName }}</span>
                        <span class="coupon-participant-category">{{ $pCat }}</span>
                    </div>
                </div>
                <div class="coupon-footer">
                    <span class="coupon-serial">#BKF-{{ $cardDisplay }}</span>
                    <span class="coupon-barcode-graphic">|||| | ||</span>
                    <span>নাস্তা কুপন</span>
                </div>
            </div>

            {{-- COLUMN 2: সকালের স্নাকস --}}
            <div class="coupon-col coupon-col-snacks1">
                <span class="cut-guide-icon"><i class="fa-solid fa-scissors"></i></span>
                <div>
                    <div class="coupon-badge"><i class="fa-solid fa-cookie-bite"></i> স্নাকস (সকাল)</div>
                    <div class="coupon-event-name">{{ $eventTitle }}</div>
                    <div class="coupon-card-badge">কার্ড নং: {{ $cardNoFormatted }}</div>
                    <div class="coupon-participant-info">
                        <span class="coupon-participant-name" title="{{ $pName }}">{{ $pName }}</span>
                        <span class="coupon-participant-category">{{ $pCat }}</span>
                    </div>
                </div>
                <div class="coupon-footer">
                    <span class="coupon-serial">#SN1-{{ $cardDisplay }}</span>
                    <span class="coupon-barcode-graphic">|| ||| |</span>
                    <span>স্নাকস কুপন</span>
                </div>
            </div>

            {{-- COLUMN 3: দুপুরের খাবার --}}
            <div class="coupon-col coupon-col-lunch">
                <span class="cut-guide-icon"><i class="fa-solid fa-scissors"></i></span>
                <div>
                    <div class="coupon-badge"><i class="fa-solid fa-bowl-rice"></i> দুপুরের খাবার</div>
                    <div class="coupon-event-name">{{ $eventTitle }}</div>
                    <div class="coupon-card-badge">কার্ড নং: {{ $cardNoFormatted }}</div>
                    <div class="coupon-participant-info">
                        <span class="coupon-participant-name" title="{{ $pName }}">{{ $pName }}</span>
                        <span class="coupon-participant-category">{{ $pCat }}</span>
                    </div>
                </div>
                <div class="coupon-footer">
                    <span class="coupon-serial">#LUN-{{ $cardDisplay }}</span>
                    <span class="coupon-barcode-graphic">|||| ||||</span>
                    <span>খাবার কুপন</span>
                </div>
            </div>

            {{-- COLUMN 4: বিকেলের স্নাকস --}}
            <div class="coupon-col coupon-col-snacks2">
                <span class="cut-guide-icon"><i class="fa-solid fa-scissors"></i></span>
                <div>
                    <div class="coupon-badge"><i class="fa-solid fa-mug-saucer"></i> বিকেলের স্নাকস</div>
                    <div class="coupon-event-name">{{ $eventTitle }}</div>
                    <div class="coupon-card-badge">কার্ড নং: {{ $cardNoFormatted }}</div>
                    <div class="coupon-participant-info">
                        <span class="coupon-participant-name" title="{{ $pName }}">{{ $pName }}</span>
                        <span class="coupon-participant-category">{{ $pCat }}</span>
                    </div>
                </div>
                <div class="coupon-footer">
                    <span class="coupon-serial">#SN2-{{ $cardDisplay }}</span>
                    <span class="coupon-barcode-graphic">|| |||| |</span>
                    <span>স্নাকস কুপন</span>
                </div>
            </div>

            {{-- COLUMN 5: র‍্যাফেল ড্র --}}
            <div class="coupon-col coupon-col-raffle">
                <div>
                    <div class="coupon-badge"><i class="fa-solid fa-ticket"></i> র‍্যাফেল ড্র</div>
                    <div class="coupon-event-name">{{ $eventTitle }}</div>
                    <div class="coupon-card-badge coupon-card-raffle">ড্র নং: {{ $cardNoFormatted }}</div>
                    <div class="coupon-participant-info">
                        <span class="coupon-participant-name" title="{{ $pName }}">{{ $pName }}</span>
                        @if(!empty($pPhone))
                            <span class="raffle-phone" title="মোবাইল">{{ $pPhone }}</span>
                        @else
                            <span class="coupon-participant-category">{{ $pCat }}</span>
                        @endif
                    </div>
                    <div class="raffle-box-drop">(ড্র বক্সে ফেলুন)</div>
                </div>
                <div class="coupon-footer">
                    <span class="coupon-serial">#DRW-{{ $cardDisplay }}</span>
                    <span class="coupon-barcode-graphic">|||| ||</span>
                    <span>ড্র কুপন</span>
                </div>
            </div>

        </div>
    </div>

    <script>
        async function downloadStripPng() {
            const el = document.getElementById('{{ $stripDomId }}');
            if (!el) return;
            try {
                const canvas = await html2canvas(el, {
                    scale: 3,
                    useCORS: true,
                    allowTaint: false,
                    backgroundColor: '#ffffff',
                    logging: false,
                    scrollX: 0,
                    scrollY: 0,
                    x: 0,
                    y: 0,
                    width: el.offsetWidth,
                    height: el.offsetHeight,
                    onclone: (clonedDoc) => {
                        const s = clonedDoc.getElementById('{{ $stripDomId }}');
                        if (s) {
                            s.style.transform = 'none';
                            s.style.margin = '0 auto';
                        }
                    }
                });
                canvas.toBlob(blob => {
                    if (!blob) throw new Error('Blob failed');
                    if (window.saveAs) {
                        saveAs(blob, '{{ $pngFileName }}');
                    } else {
                        const url = URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = '{{ $pngFileName }}';
                        document.body.appendChild(a);
                        a.click();
                        setTimeout(() => {
                            document.body.removeChild(a);
                            URL.revokeObjectURL(url);
                        }, 1000);
                    }
                }, 'image/png');
            } catch (e) {
                console.error(e);
                alert('ডাউনলোড সম্পন্ন করা যায়নি।');
            }
        }
    </script>
</body>
</html>

