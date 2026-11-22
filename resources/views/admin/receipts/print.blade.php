@php
    use App\Support\Bn;
    $pay = $receipt->isHonorarium();
    $business = $settings['business_name'] ?? config('app.name');
    $tagline = $settings['tagline'] ?? '';
    $logo = \App\Support\SiteSetting::billLogoUrl($settings['logo'] ?? null);
    $invoice = $receipt->invoice_id ? $receipt->invoice : null;
    $items = $pay ? ($receipt->items ?: []) : [];
    $totals = $receipt->itemTotals();
    $title = $pay ? 'সম্মানী প্রদান রসিদ' : 'অর্থ প্রাপ্তি রসিদ';
    $copyLabels = $copies === 2 ? ['অফিস কপি', $pay ? 'লেখক কপি' : 'গ্রাহক কপি'] : [null];
    $purpose = trim(preg_replace('/\s*বাবদ\s*$/u', '', (string) $receipt->purpose) ?? (string) $receipt->purpose);
    $pct = fn ($v) => Bn::digits(rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.')) . '%';
@endphp
<!doctype html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $receipt->receipt_no }} — {{ $title }}</title>
    <style>
        @font-face { font-family: 'Kalpurush'; src: url('{{ asset('fonts/kalpurush/kalpurush.woff2') }}') format('woff2'); font-display: swap; }
        :root { --blue: #1d6fe0; --ink: #1e293b; --ink2: #475569; --mute: #94a3b8; --line: #e2e8f0; --soft: #f8fafc; --green: #059669; --red: #dc2626; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #f1f5f9; font-family: 'Kalpurush', 'Noto Sans Bengali', system-ui, sans-serif; color: var(--ink); font-size: 13px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .bar { display: flex; justify-content: center; gap: 8px; padding: 14px; position: sticky; top: 0; background: rgba(241, 245, 249, .92); backdrop-filter: blur(6px); z-index: 2; }
        .bar a, .bar button { display: inline-flex; align-items: center; height: 36px; padding: 0 16px; border-radius: 999px; border: 1px solid #cbd5e1; background: #fff; color: var(--ink); font: 600 13px 'Kalpurush', system-ui, sans-serif; text-decoration: none; cursor: pointer; }
        .bar .primary { background: var(--blue); border-color: var(--blue); color: #fff; }
        .sheet { width: 210mm; margin: 0 auto 24px; display: flex; flex-direction: column; gap: 6mm; }
        .page { background: #fff; border: 1px solid var(--line); border-radius: 10px; padding: 9mm 10mm 7mm; }

        .head { display: flex; align-items: center; gap: 14px; padding-bottom: 10px; border-bottom: 1px solid var(--line); }
        .head img { height: 52px; width: auto; max-width: 140px; object-fit: contain; }
        .org { flex: 1; min-width: 0; }
        .org h1 { margin: 0; font-size: 19px; font-weight: 700; color: var(--blue); }
        .org .tag { font-size: 11px; color: var(--ink2); }
        .org p { margin: 2px 0 0; font-size: 11px; color: var(--ink2); }
        .doc { text-align: right; }
        .pill { display: inline-block; font-weight: 700; font-size: 12px; padding: 2px 11px; border-radius: 999px; background: #dcfce7; color: var(--green); }
        .pill.is-pay { background: #ede9fe; color: #6d28d9; }
        .doc .no { font-size: 15px; font-weight: 700; margin-top: 4px; }
        .doc small { display: block; font-size: 11.5px; color: var(--ink2); }
        .copy { font-size: 11px; font-weight: 700; color: var(--mute); }

        .info { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin: 10px 0; padding: 12px 14px; border: 1px solid var(--line); border-radius: 8px; }
        .info h3 { margin: 0 0 6px; font-size: 12px; font-weight: 700; color: var(--ink2); }
        .info .col + .col { border-left: 1px solid var(--line); padding-left: 16px; }
        .row { display: grid; grid-template-columns: 70px 10px 1fr; font-size: 12.5px; line-height: 1.8; }
        .row span:first-child { color: var(--ink2); }
        .row b { font-weight: 700; }

        table { width: 100%; border-collapse: collapse; border: 1px solid var(--line); }
        th { font-size: 11.5px; font-weight: 700; color: var(--ink2); background: var(--soft); text-align: left; padding: 7px 9px; border-bottom: 1px solid var(--line); white-space: nowrap; }
        td { padding: 8px 9px; font-size: 12.5px; border-bottom: 1px solid var(--line); vertical-align: top; }
        tfoot td { background: var(--soft); font-weight: 700; }
        .r { text-align: right; white-space: nowrap; }
        .c { text-align: center; }
        .minus { color: var(--red); }
        .net { color: var(--blue); font-weight: 700; }

        .sum { display: grid; grid-template-columns: 1fr 270px; gap: 12px; margin-top: 10px; }
        .words, .totals { border: 1px solid var(--line); border-radius: 8px; padding: 10px 14px; }
        .words h4 { margin: 0 0 4px; font-size: 12px; font-weight: 700; color: var(--ink2); }
        .words p { margin: 0; font-size: 14px; font-weight: 700; }
        .totals div { display: flex; justify-content: space-between; gap: 10px; font-size: 12.5px; line-height: 1.9; }
        .totals .big { font-size: 15.5px; font-weight: 700; color: var(--blue); border-top: 1px solid var(--line); margin-top: 2px; padding-top: 2px; }
        .totals .due { color: var(--red); }
        .note { margin-top: 10px; padding: 8px 12px; border: 1px solid var(--line); border-radius: 8px; font-size: 12px; color: var(--ink2); }

        .sign { display: grid; grid-template-columns: 1fr 1fr; gap: 30mm; margin-top: 18mm; align-items: end; }
        .sign div { text-align: center; font-size: 12.5px; }
        .sign .got { font-size: 12.5px; font-weight: 700; margin-bottom: 12mm; }
        .sign .line { border-top: 1px solid var(--ink); padding-top: 5px; font-weight: 700; }
        .foot { display: flex; justify-content: space-between; margin-top: 8px; font-size: 10.5px; color: var(--mute); }

        @page { size: A4 portrait; margin: 8mm; }
        @media print {
            body { background: #fff; }
            .bar { display: none; }
            .sheet { width: auto; margin: 0; gap: 0; }
            .page { border: 0; border-radius: 0; padding: 2mm 3mm; page-break-inside: avoid; }
            .page + .page { border-top: 1px dashed var(--mute); padding-top: 6mm; margin-top: 4mm; }
        }
        @media (max-width: 820px) {
            .sheet { width: auto; margin: 0 10px 20px; }
            .page { padding: 16px; overflow-x: auto; }
            .head { flex-wrap: wrap; }
            .info, .sum { grid-template-columns: 1fr; }
            .info .col + .col { border-left: 0; padding-left: 0; border-top: 1px solid var(--line); padding-top: 8px; }
            .sign { gap: 20px; }
        }
    </style>
</head>
<body>
    <div class="bar">
        <a href="{{ route('admin.receipts.index') }}">← রসিদ তালিকা</a>
        <button type="button" class="primary" onclick="window.print()">প্রিন্ট / পিডিএফ</button>
        @if($copies === 1)
            <a href="?copies=2">দুই কপি</a>
        @else
            <a href="?">এক কপি</a>
        @endif
    </div>

    <main class="sheet">
        @foreach($copyLabels as $copy)
            <section class="page">
                <header class="head">
                    @if($logo)<img src="{{ $logo }}" alt="">@endif
                    <div class="org">
                        <h1>{{ $business }}</h1>
                        @if($tagline)<div class="tag">{{ $tagline }}</div>@endif
                        <p>{{ $settings['address'] ?? '' }} · {{ Bn::digits($settings['phone'] ?? '') }}</p>
                        @if(!empty($settings['email']))<p>{{ $settings['email'] }}</p>@endif
                    </div>
                    <div class="doc">
                        @if($copy)<div class="copy">{{ $copy }}</div>@endif
                        <span class="pill {{ $pay ? 'is-pay' : '' }}">{{ $title }}</span>
                        <div class="no">নং: {{ $receipt->receipt_no }}</div>
                        <small>তারিখ: {{ Bn::date($receipt->receipt_date) }}</small>
                    </div>
                </header>

                <div class="info">
                    <div class="col">
                        <h3>{{ $pay ? 'সম্মানী প্রাপক' : 'যাঁর নিকট থেকে গৃহীত' }}</h3>
                        <div class="row"><span>নাম</span><span>:</span><b>{{ $receipt->party_name }}</b></div>
                        @if($receipt->party_address)<div class="row"><span>ঠিকানা</span><span>:</span><span>{{ $receipt->party_address }}</span></div>@endif
                        @if($receipt->party_phone)<div class="row"><span>মোবাইল</span><span>:</span><span>{{ Bn::digits($receipt->party_phone) }}</span></div>@endif
                    </div>
                    <div class="col">
                        <h3>লেনদেনের বিবরণ</h3>
                        <div class="row"><span>বাবদ</span><span>:</span><span>{{ $purpose }}</span></div>
                        <div class="row"><span>মাধ্যম</span><span>:</span><span>{{ $receipt->methodLabel() }}</span></div>
                        @if($receipt->reference)<div class="row"><span>রেফারেন্স</span><span>:</span><span>{{ $receipt->reference }}</span></div>@endif
                        @if($invoice)<div class="row"><span>বিল নং</span><span>:</span><span>{{ $invoice->invoice_no }}</span></div>@endif
                    </div>
                </div>

                @if($items)
                    {{-- book sales: copies × price = sale − commission = writer's share --}}
                    <table>
                        <thead>
                            <tr><th class="c" style="width:34px">ক্রম</th><th>বইয়ের নাম</th><th class="r">কপি</th><th class="r">মূল্য</th><th class="r">মোট বিক্রি</th><th class="r">কমিশন</th><th class="r">লেখকের প্রাপ্য</th></tr>
                        </thead>
                        <tbody>
                            @foreach($items as $i => $it)
                                <tr>
                                    <td class="c">{{ Bn::digits($i + 1) }}</td>
                                    <td>{{ $it['title'] }}</td>
                                    <td class="r">{{ Bn::num($it['qty']) }}</td>
                                    <td class="r">{{ Bn::money($it['price']) }}</td>
                                    <td class="r">{{ Bn::money($it['sale']) }}</td>
                                    <td class="r minus">{{ $pct($it['pct']) }} = {{ Bn::money($it['commission']) }}</td>
                                    <td class="r net">{{ Bn::money($it['net']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td></td><td>মোট</td>
                                <td class="r">{{ Bn::num($totals['qty']) }}</td><td></td>
                                <td class="r">{{ Bn::money($totals['sale']) }}</td>
                                <td class="r minus">{{ Bn::money($totals['commission']) }}</td>
                                <td class="r net">{{ Bn::money($totals['net']) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                @else
                    <table>
                        <thead><tr><th class="c" style="width:34px">ক্রম</th><th>বিবরণ</th><th class="r">টাকা</th></tr></thead>
                        <tbody>
                            <tr>
                                <td class="c">১</td>
                                <td>{{ $purpose }} বাবদ{{ $pay && !str_contains($purpose, 'সম্মানী') ? ' সম্মানী' : '' }}</td>
                                <td class="r">{{ Bn::money($receipt->amount) }}</td>
                            </tr>
                        </tbody>
                    </table>
                @endif

                <div class="sum">
                    <div class="words">
                        <h4>কথায়</h4>
                        <p>{{ Bn::inWords($receipt->amount) }}</p>
                    </div>
                    <div class="totals">
                        @if($items)
                            <div><span>মোট বিক্রি</span><span>{{ Bn::money($totals['sale']) }}</span></div>
                            <div class="minus"><span>বাদ: কমিশন</span><span>− {{ Bn::money($totals['commission']) }}</span></div>
                        @elseif($invoice)
                            <div><span>বিলের মোট</span><span>{{ Bn::money($invoice->grand_total) }}</span></div>
                        @endif
                        <div class="big"><span>{{ $pay ? 'প্রদত্ত সম্মানী' : 'গৃহীত টাকা' }}</span><span>{{ Bn::money($receipt->amount) }}</span></div>
                        @if($invoice)
                            <div class="due"><span>বর্তমান বকেয়া</span><span>{{ Bn::money($invoice->due_amount) }}</span></div>
                        @endif
                    </div>
                </div>

                @if($receipt->notes)<div class="note">মন্তব্য: {{ $receipt->notes }}</div>@endif

                <div class="sign">
                    <div><div class="line">প্রদানকারীর স্বাক্ষর</div></div>
                    <div>
                        <div class="got">উপরোক্ত টাকা বুঝিয়া পাইলাম।</div>
                        <div class="line">{{ $pay ? 'লেখকের স্বাক্ষর' : 'গ্রহণকারীর স্বাক্ষর' }}</div>
                    </div>
                </div>
                <div class="foot"><span>{{ $business }} · কম্পিউটারে তৈরি রসিদ</span><span>নং: {{ $receipt->receipt_no }}</span></div>
            </section>
        @endforeach
    </main>
</body>
</html>
