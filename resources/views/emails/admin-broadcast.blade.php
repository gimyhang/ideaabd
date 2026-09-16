<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $mailSubject }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif, 'SolaimanLipi', Arial;
            color: #334155;
            -webkit-text-size-adjust: 100%;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f4f6f9;
            padding: 30px 0;
        }
        .main-card {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            padding: 30px 25px;
            text-align: center;
            border-bottom: 3px solid #f59e0b;
        }
        .brand-title {
            color: #ffffff;
            font-size: 24px;
            font-weight: 700;
            margin: 0;
            letter-spacing: 0.5px;
        }
        .brand-tagline {
            color: #94a3b8;
            font-size: 13px;
            margin-top: 6px;
        }
        .content {
            padding: 35px 30px;
            font-size: 15px;
            line-height: 1.7;
            color: #334155;
        }
        .greeting {
            font-size: 17px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 18px;
        }
        .body-text {
            color: #475569;
            white-space: pre-line;
            margin-bottom: 25px;
        }
        .btn-wrapper {
            text-align: center;
            margin: 30px 0;
        }
        .btn-action {
            display: inline-block;
            background: #0f172a;
            color: #ffffff !important;
            text-decoration: none;
            padding: 13px 32px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 15px;
            box-shadow: 0 4px 10px rgba(15, 23, 42, 0.2);
            border: 1px solid #f59e0b;
        }
        .footer {
            background-color: #f8fafc;
            padding: 24px 30px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            font-size: 12px;
            color: #64748b;
        }
        .footer a {
            color: #2563eb;
            text-decoration: none;
        }
        .footer-note {
            margin-top: 8px;
            color: #94a3b8;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="main-card">
            
            {{-- Email Header --}}
            <div class="header">
                <h1 class="brand-title">{{ $siteName }}</h1>
                <div class="brand-tagline">{{ $siteTagline }}</div>
            </div>

            {{-- Main Message Body --}}
            <div class="content">
                @if(!empty($recipientName))
                    <div class="greeting">প্রিয় {{ $recipientName }},</div>
                @else
                    <div class="greeting">সম্মানিত গ্রাহক/সুধী,</div>
                @endif

                <div class="body-text">{!! nl2br(e($bodyContent)) !!}</div>

                @if(!empty($actionUrl) && !empty($actionText))
                    <div class="btn-wrapper">
                        <a href="{{ $actionUrl }}" target="_blank" class="btn-action">{{ $actionText }}</a>
                    </div>
                @endif

                <div style="margin-top: 25px; padding-top: 15px; border-top: 1px dashed #e2e8f0; font-size: 13px; color: #64748b;">
                    বই পড়ুন, জ্ঞানের সাথে থাকুন।<br>
                    <strong>{{ $siteName }} টিম</strong>
                </div>
            </div>

            {{-- Footer Section --}}
            <div class="footer">
                <div>ওয়েবসাইট: <a href="{{ $siteUrl }}" target="_blank">{{ $siteUrl }}</a></div>
                <div class="footer-note">
                    © {{ date('Y') }} {{ $siteName }}। সর্বস্বত্ব সংরক্ষিত। আপনি এই প্ল্যাটফর্মের একজন নিবন্ধিত গ্রাহক বা ব্যবহারকারী হওয়ায় বার্তাটি পাঠানো হয়েছে।
                </div>
            </div>

        </div>
    </div>
</body>
</html>
