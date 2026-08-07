<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }} — IdeaABD</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
            background-color: #f1f5f9;
            margin: 0;
            padding: 0;
            font-size: 13px;
            line-height: 1.5;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Top Action Bar (Screen Only) */
        .action-bar {
            background-color: #0f172a;
            color: #ffffff;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }
        .action-bar a, .action-bar button {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-back {
            background-color: #334155;
            color: #f8fafc;
            border: none;
        }
        .btn-back:hover {
            background-color: #475569;
        }
        .btn-print {
            background-color: #0284c7;
            color: #ffffff;
            border: none;
        }
        .btn-print:hover {
            background-color: #0369a1;
        }

        /* Centered A4 Document Sheet Container */
        .page-container {
            width: 210mm;
            min-height: 297mm;
            margin: 30px auto;
            background-color: #ffffff;
            padding: 15mm;
            border-radius: 8px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            position: relative;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .logo {
            font-size: 24px;
            font-weight: 900;
            color: #0284c7;
            text-transform: uppercase;
            letter-spacing: -0.5px;
        }
        .invoice-title {
            font-size: 20px;
            font-weight: 800;
            text-align: right;
            color: #0f172a;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .meta-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 14px;
            vertical-align: top;
        }
        .meta-label {
            font-size: 10px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .items-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 10px 12px;
            text-align: left;
        }
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
        }

        .summary-table {
            width: 280px;
            margin-left: auto;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 6px 12px;
            font-size: 12px;
        }
        .summary-table .grand-total {
            font-size: 15px;
            font-weight: 800;
            color: #0284c7;
            border-top: 2px solid #0f172a;
            padding-top: 10px;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            background-color: #dcfce7;
            color: #15803d;
        }

        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px dashed #cbd5e1;
            text-align: center;
            font-size: 11px;
            color: #64748b;
        }

        /* Print Media Overrides */
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: #ffffff !important;
            }
            .page-container {
                width: 100% !important;
                min-height: auto !important;
                margin: 0 !important;
                padding: 15mm !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                border: none !important;
            }
        }
    </style>
</head>
<body>

    <!-- Top Action Bar for Web View -->
    <div class="action-bar no-print">
        <div style="font-weight: 700; font-size: 13px;">
            📄 Invoice Preview — {{ $invoice->invoice_number }}
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="javascript:history.back()" class="btn-back">
                ← Back to Order
            </a>
            <button onclick="window.print()" class="btn-print">
                🖨️ Print / Save as PDF (A4)
            </button>
        </div>
    </div>

    <!-- Centered A4 Paper Sheet Container -->
    <div class="page-container">
        <!-- Header Banner -->
        <table class="header-table">
            <tr>
                <td style="width: 50%;">
                    <div class="logo">IdeaABD</div>
                    <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Enterprise Digital Bookstore Platform</div>
                    <div style="font-size: 11px; color: #64748b;">Dhaka, Bangladesh | support@IdeaABD.com</div>
                </td>
                <td style="width: 50%;" class="invoice-title">
                    INVOICE
                    <div style="font-size: 12px; font-weight: 700; color: #0284c7; margin-top: 4px;">{{ $invoice->invoice_number }}</div>
                    <div style="font-size: 11px; font-weight: 500; color: #64748b;">Date: {{ $invoice->created_at ? \Carbon\Carbon::parse($invoice->created_at)->format('d M Y, h:i A') : now()->format('d M Y, h:i A') }}</div>
                </td>
            </tr>
        </table>

        <!-- Billing & Order Meta Details -->
        <table class="meta-table">
            <tr>
                <td class="meta-box" style="width: 48%;">
                    <div class="meta-label">Billed / Shipped To</div>
                    <div style="font-weight: 800; font-size: 14px; color: #0f172a;">{{ $order->shipping_name }}</div>
                    <div>Phone: {{ $order->shipping_phone }}</div>
                    @if($order->shipping_email)
                        <div>Email: {{ $order->shipping_email }}</div>
                    @endif
                    <div style="margin-top: 4px; font-size: 11px; color: #475569;">
                        Address: {{ $order->delivery_address_string }}
                    </div>
                </td>
                <td style="width: 4%;"></td>
                <td class="meta-box" style="width: 48%;">
                    <div class="meta-label">Order Reference Details</div>
                    <div><strong>Order Number:</strong> {{ $order->order_number }}</div>
                    <div><strong>Payment Method:</strong> {{ $order->payment_method->label() }}</div>
                    <div><strong>Payment Status:</strong> <span class="badge">{{ $order->payment_status->label() }}</span></div>
                    <div><strong>Transaction ID:</strong> {{ $order->transaction_id ?? 'N/A' }}</div>
                </td>
            </tr>
        </table>

        <!-- Line Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 45%;">Book Title</th>
                    <th style="width: 20%;">SKU / Format</th>
                    <th style="width: 12%; text-align: right;">Unit Price</th>
                    <th style="width: 8%; text-align: center;">Qty</th>
                    <th style="width: 15%; text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td style="font-weight: 700;">{{ $item->book_title }}</td>
                        <td style="font-size: 11px; color: #64748b;">{{ $item->sku }} ({{ ucfirst($item->format) }})</td>
                        <td style="text-align: right;">৳{{ number_format($item->unit_price, 2) }}</td>
                        <td style="text-align: center; font-weight: 700;">{{ $item->quantity }}</td>
                        <td style="text-align: right; font-weight: 700;">৳{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary Breakdown -->
        <table class="summary-table">
            <tr>
                <td style="color: #64748b;">Subtotal:</td>
                <td style="text-align: right; font-weight: 700;">৳{{ number_format($order->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td style="color: #64748b;">Shipping Fee:</td>
                <td style="text-align: right; font-weight: 700;">৳{{ number_format($order->shipping_fee, 2) }}</td>
            </tr>
            @if($order->discount_amount > 0)
                <tr>
                    <td style="color: #16a34a;">Coupon Discount:</td>
                    <td style="text-align: right; font-weight: 700; color: #16a34a;">-৳{{ number_format($order->discount_amount, 2) }}</td>
                </tr>
            @endif
            <tr class="grand-total">
                <td>Grand Total:</td>
                <td style="text-align: right;">৳{{ number_format($order->total_amount, 2) }}</td>
            </tr>
        </table>

        <!-- Footer Disclaimer -->
        <div class="footer">
            Thank you for purchasing from IdeaABD Library! For any billing queries, contact support@IdeaABD.com.
            <br>
            This is an official computer-generated A4 invoice document and does not require a physical signature.
        </div>
    </div>

</body>
</html>

