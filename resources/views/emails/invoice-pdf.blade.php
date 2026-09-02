<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $invoice->type_label ?? 'Invoice' }} #{{ $invoice->invoice_no }}</title>
    <style>
        @page {
            margin: 15mm 12mm 15mm 12mm;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.4;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0284c7;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .biz-title {
            font-size: 18px;
            font-weight: bold;
            color: #0369a1;
            margin: 0 0 3px 0;
        }
        .biz-sub {
            font-size: 9.5px;
            color: #64748b;
            margin: 0;
        }
        .doc-badge {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            text-align: right;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 15px;
        }
        .meta-box {
            padding: 8px 10px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            vertical-align: top;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .items-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-size: 10px;
            font-weight: bold;
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            text-align: left;
        }
        .items-table td {
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
            font-size: 10px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .summary-table {
            width: 45%;
            margin-left: auto;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 4px 6px;
            font-size: 10px;
        }
        .total-row {
            font-size: 12px;
            font-weight: bold;
            color: #0369a1;
            border-top: 1px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
        }
        .footer-note {
            margin-top: 25px;
            padding-top: 8px;
            border-top: 1px dashed #cbd5e1;
            font-size: 9px;
            color: #64748b;
            text-align: center;
        }
    </style>
</head>
<body>
    @php
        $settings = $invoiceSettings ?? [];
        $bizName = $settings['business_name'] ?? 'Idea Publication';
        $bizAddress = $settings['address'] ?? 'Dhaka, Bangladesh';
        $bizPhone = $settings['phone'] ?? '';
        $bizEmail = $settings['email'] ?? '';
    @endphp

    <table class="header-table">
        <tr>
            <td style="width: 60%; vertical-align: top;">
                <div class="biz-title">{{ $bizName }}</div>
                <div class="biz-sub">{{ $bizAddress }} @if($bizPhone) | Phone: {{ $bizPhone }} @endif</div>
                @if($bizEmail)<div class="biz-sub">Email: {{ $bizEmail }}</div>@endif
            </td>
            <td style="width: 40%; text-align: right; vertical-align: top;">
                <div class="doc-badge">{{ strtoupper($invoice->type_label ?? 'INVOICE') }}</div>
                <div style="font-size: 12px; font-weight: bold; color: #0284c7;">#{{ $invoice->invoice_no }}</div>
                <div style="font-size: 9.5px; color: #64748b;">Date: {{ $invoice->invoice_date ? $invoice->invoice_date->format('d M, Y') : date('d M, Y') }}</div>
            </td>
        </tr>
    </table>

    <table class="meta-table">
        <tr>
            <td class="meta-box" style="width: 55%; margin-right: 10px;">
                <div style="font-size: 9px; font-weight: bold; color: #64748b; text-transform: uppercase; margin-bottom: 3px;">Billed To / Customer:</div>
                <div style="font-size: 12px; font-weight: bold; color: #0f172a;">{{ $invoice->customer_name }}</div>
                @if($invoice->customer_org)<div style="color: #334155;">{{ $invoice->customer_org }} @if($invoice->customer_designation)({{ $invoice->customer_designation }})@endif</div>@endif
                @if($invoice->customer_phone)<div>Phone: {{ $invoice->customer_phone }}</div>@endif
                @if($invoice->customer_address)<div>Address: {{ $invoice->customer_address }}</div>@endif
            </td>
            <td style="width: 5%;"></td>
            <td class="meta-box" style="width: 40%;">
                <div style="font-size: 9px; font-weight: bold; color: #64748b; text-transform: uppercase; margin-bottom: 3px;">Invoice Summary:</div>
                <div>Status: <strong>{{ ucfirst($invoice->payment_status ?: 'Unpaid') }}</strong></div>
                <div>Payment Method: <strong>{{ ucfirst($invoice->payment_method ?: 'Cash') }}</strong></div>
                @if($invoice->due_date)<div>Due Date: <strong>{{ $invoice->due_date->format('d M, Y') }}</strong></div>@endif
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 30px;">#</th>
                <th>Item Description</th>
                <th class="text-center" style="width: 50px;">Qty</th>
                <th class="text-right" style="width: 70px;">Unit Price</th>
                <th class="text-right" style="width: 80px;">Total</th>
            </tr>
        </thead>
        <tbody>
            @php $items = $invoice->items ?? []; @endphp
            @forelse($items as $i => $item)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>
                        <strong style="color: #0f172a;">{{ $item['title'] ?? 'Item' }}</strong>
                        @if(!empty($item['author'])) <span style="color: #64748b; font-size: 9px;">— {{ $item['author'] }}</span> @endif
                    </td>
                    <td class="text-center font-bold">{{ $item['quantity'] ?? 1 }}</td>
                    <td class="text-right">৳{{ number_format((float)($item['unit_price'] ?? 0), 2) }}</td>
                    <td class="text-right font-bold">৳{{ number_format((float)($item['total'] ?? (($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0))), 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="color: #94a3b8; padding: 15px;">No item details available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="summary-table">
        <tr>
            <td style="color: #64748b;">Subtotal:</td>
            <td class="text-right font-bold">৳{{ number_format((float)($invoice->subtotal ?? $invoice->grand_total), 2) }}</td>
        </tr>
        @if((float)$invoice->discount > 0)
        <tr>
            <td style="color: #64748b;">Discount:</td>
            <td class="text-right" style="color: #dc2626;">- ৳{{ number_format((float)$invoice->discount, 2) }}</td>
        </tr>
        @endif
        @if((float)$invoice->shipping_cost > 0)
        <tr>
            <td style="color: #64748b;">Shipping / Delivery:</td>
            <td class="text-right">৳{{ number_format((float)$invoice->shipping_cost, 2) }}</td>
        </tr>
        @endif
        <tr class="total-row">
            <td>Grand Total:</td>
            <td class="text-right">৳{{ number_format((float)$invoice->grand_total, 2) }}</td>
        </tr>
        @if(in_array($invoice->type, ['invoice', 'challan']))
        <tr>
            <td style="color: #16a34a;">Paid Amount:</td>
            <td class="text-right font-bold" style="color: #16a34a;">৳{{ number_format((float)$invoice->paid_amount, 2) }}</td>
        </tr>
        @if((float)$invoice->due_amount > 0)
        <tr>
            <td style="color: #dc2626;">Due Balance:</td>
            <td class="text-right font-bold" style="color: #dc2626;">৳{{ number_format((float)$invoice->due_amount, 2) }}</td>
        </tr>
        @endif
        @endif
    </table>

        @if($invoice->type === 'invoice')
    <table style="width: 100%; margin-top: 15px; border-top: 1px solid #cbd5e1; padding-top: 8px;">
        <tr>
            <td style="width: 50%; vertical-align: top; padding-right: 10px;">
                <div style="font-size: 9px; font-weight: bold; color: #0284c7; text-transform: uppercase; margin-bottom: 2px;">Bank Payment Details:</div>
                <div style="font-size: 9.5px; font-weight: bold; color: #0f172a;">Bank: {{ $settings['bank_name'] ?? 'Islami Bank Bangladesh Ltd' }}</div>
                <div style="font-size: 9px; color: #334155;">A/C Name: {{ $settings['bank_account_name'] ?? ($settings['business_name'] ?? 'Idea Publication') }}</div>
                <div style="font-size: 9px; color: #334155;">A/C No: <strong>{{ $settings['bank_account_no'] ?? '2050XXXXXXXXXXXXX' }}</strong></div>
                @if(!empty($settings['bank_branch']))<div style="font-size: 8.5px; color: #64748b;">Branch: {{ $settings['bank_branch'] }} @if(!empty($settings['bank_routing_no']))| Routing: {{ $settings['bank_routing_no'] }}@endif</div>@endif
            </td>
            <td style="width: 50%; vertical-align: top; text-align: right;">
                <div style="font-size: 9px; font-weight: bold; color: #16a34a; text-transform: uppercase; margin-bottom: 2px;">MFS & Bangla QR:</div>
                <div style="font-size: 9px; color: #334155;">Payment: <strong>{{ $settings['payment_qr_note'] ?? 'bKash / Nagad / Rocket' }}</strong></div>
                <div style="font-size: 8.5px; color: #64748b;">Scan QR on digital invoice to pay instantly.</div>
            </td>
        </tr>
    </table>
    @endif

    <div class="footer-note">
        This is an official computer-generated document from {{ $bizName }}. View digital copy at: {{ $invoice->public_url }}
    </div>
</body>
</html>
