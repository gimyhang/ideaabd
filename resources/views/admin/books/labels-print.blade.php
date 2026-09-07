<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Barcode & QR Code Labels — আইডিয়া প্রকাশন</title>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Hind Siliguri', 'Inter', sans-serif;
            background: #f1f5f9;
            color: #0f172a;
            padding: 20px;
        }
        .no-print-bar {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 16px 20px;
            max-width: 900px;
            margin: 0 auto 24px auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }
        .btn-print {
            background: #2563eb;
            color: #ffffff;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(37,99,235,0.3);
            transition: all 0.2s ease;
        }
        .btn-print:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }
        .btn-back {
            background: #e2e8f0;
            color: #334155;
            text-decoration: none;
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
        }

        /* Label Container Grid */
        .labels-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: center;
            max-width: 1000px;
            margin: 0 auto;
        }

        /* Standard 50mm x 30mm or 60mm x 35mm Thermal Sticker */
        .book-sticker-label {
            width: 220px;
            height: 140px;
            background: #ffffff;
            border: 1px dashed #94a3b8;
            border-radius: 6px;
            padding: 8px 10px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            page-break-inside: avoid;
            position: relative;
            overflow: hidden;
        }

        .label-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 6px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 4px;
            margin-bottom: 4px;
        }

        .label-title {
            font-size: 11px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.25;
            max-height: 28px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .label-author {
            font-size: 9.5px;
            color: #475569;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-top: 1px;
        }

        .label-price-tag {
            text-align: right;
            flex-shrink: 0;
        }

        .label-price-mrp {
            font-size: 8.5px;
            color: #64748b;
            text-decoration: line-through;
        }

        .label-price-net {
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
            font-family: 'Inter', sans-serif;
            white-space: nowrap;
        }

        .label-body {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            flex: 1;
        }

        .label-barcode-wrap {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .label-barcode-wrap svg {
            width: 100%;
            height: 38px;
        }

        .label-qr-wrap {
            width: 48px;
            height: 48px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .label-qr-wrap svg {
            width: 100%;
            height: 100%;
        }

        .label-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 8px;
            color: #64748b;
            border-top: 1px solid #f1f5f9;
            padding-top: 3px;
            margin-top: 3px;
        }

        .label-brand {
            font-weight: 700;
            color: #1e293b;
        }

        .label-sku {
            font-family: monospace;
            font-weight: 700;
            color: #0f172a;
        }

        /* Print Settings */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .no-print-bar {
                display: none !important;
            }
            .labels-grid {
                gap: 4px !important;
            }
            .book-sticker-label {
                border: 1px solid #e2e8f0 !important;
                box-shadow: none !important;
                margin: 0 !important;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <div>
            <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 2px;">🔖 বইমেলা ও শপ বারকোড / কিউআর স্টিকার লেবেল</h3>
            <p style="font-size: 12px; color: #64748b;">মোট {{ count($books) }} টি বইয়ের লেবেল জেনারেট হয়েছে (কপি: {{ $copies }} প্রতি বই)। থার্মাল স্টিকার রোল অথবা A4 শিটে প্রিন্ট করা যাবে।</p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <a href="{{ route('admin.books') }}" class="btn-back">← ফিরে যান</a>
            <button onclick="window.print()" class="btn-print">🖨️ প্রিন্ট লেবেল</button>
        </div>
    </div>

    <div class="labels-grid">
        @foreach($books as $book)
            @php
                $isIdea = $book->is_idea_prokashon;
                $serial = $book->idea_serial_no ?: ($book->sku ?: ($isIdea ? sprintf('IP%03d', $book->id) : 'BK-' . $book->id));
                $price = (float)($book->discount_price > 0 && $book->discount_price < $book->price ? $book->discount_price : $book->price);
                $hasDiscount = $book->discount_price > 0 && $book->discount_price < $book->price;
                $barcodeSvg = \App\Services\BarcodeService::generateCode128Svg($serial, 36, 1.5, false);
                $qrSvg = \App\Services\BarcodeService::generateQrCodeSvg(url('/books/' . ($book->slug ?: $book->id)), 46);
            @endphp

            @for($c = 0; $c < $copies; $c++)
                <div class="book-sticker-label">
                    <div class="label-header">
                        <div style="overflow: hidden; flex: 1;">
                            <div class="label-title">{{ $book->title }}</div>
                            <div class="label-author">{{ $book->author_name ?: ($book->authorLink?->name ?? 'আইডিয়া লেখক') }}</div>
                        </div>
                        <div class="label-price-tag">
                            @if($hasDiscount)
                                <div class="label-price-mrp">৳{{ number_format($book->price, 0) }}</div>
                            @endif
                            <div class="label-price-net">৳{{ number_format($price, 0) }}</div>
                        </div>
                    </div>

                    <div class="label-body">
                        <div class="label-barcode-wrap">
                            {!! $barcodeSvg !!}
                        </div>
                        <div class="label-qr-wrap">
                            {!! $qrSvg !!}
                        </div>
                    </div>

                    <div class="label-footer">
                        <span class="label-brand">{{ $isIdea ? '⭐ আইডিয়া প্রকাশন' : ($book->publisher?->name ?? 'বইমেলা স্টল') }}</span>
                        <span class="label-sku">{{ $serial }}</span>
                    </div>
                </div>
            @endfor
        @endforeach
    </div>

</body>
</html>
