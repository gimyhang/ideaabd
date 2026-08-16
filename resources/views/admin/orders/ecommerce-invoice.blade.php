<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ইনভয়েস #{{ $order->order_number ?? $order->id }} - {{ $invoiceSettings['sender_name'] ?? 'আইডিয়া প্রকাশন' }}</title>
    <!-- Google Fonts Bangla -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #0066cc;
            --dark: #1e293b;
            --slate: #475569;
            --light-bg: #f8fafc;
            --border-color: #cbd5e1;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Hind Siliguri', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            font-size: 13.5px;
            line-height: 1.45;
            margin: 0;
            padding: 20px 0;
        }
        .invoice-container {
            max-width: 820px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .invoice-inner {
            padding: 32px 36px;
        }
        .invoice-header-badge {
            background: linear-gradient(135deg, #0066cc, #004c99);
            color: #ffffff;
            padding: 6px 18px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 14px;
            letter-spacing: 0.5px;
            display: inline-block;
        }
        .order-meta-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 14px;
        }
        .party-box {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px 16px;
            background: #ffffff;
            height: 100%;
        }
        .party-box-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #64748b;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 5px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .party-box.sender {
            border-left: 4px solid #0066cc;
            background: #f0f7ff;
        }
        .party-box.recipient {
            border-left: 4px solid #10b981;
            background: #f0fdf4;
        }
        .party-name {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
        }
        .table-invoice {
            width: 100%;
            margin-bottom: 16px;
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            overflow: hidden;
        }
        .table-invoice th {
            background: #f1f5f9;
            color: #334155;
            font-weight: 700;
            font-size: 12.5px;
            padding: 10px 12px;
            border-bottom: 2px solid #cbd5e1;
            border-right: 1px solid #e2e8f0;
        }
        .table-invoice th:last-child {
            border-right: none;
        }
        .table-invoice td {
            padding: 10px 12px;
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        .table-invoice td:last-child {
            border-right: none;
        }
        .table-invoice tbody tr:last-child td {
            border-bottom: none;
        }
        .table-invoice tbody tr:hover {
            background-color: #fafbfc;
        }
        .summary-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 16px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            font-size: 13px;
        }
        .summary-row.grand-total {
            border-top: 2px dashed #cbd5e1;
            margin-top: 6px;
            padding-top: 8px;
            font-size: 16px;
            font-weight: 700;
            color: #0066cc;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 11.5px;
        }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-processing { background: #e0f2fe; color: #0369a1; }
        .badge-confirmed { background: #e0e7ff; color: #3730a3; }
        .badge-shipped { background: #ede9fe; color: #5b21b6; }
        .badge-delivered { background: #dcfce7; color: #166534; }
        .badge-cancelled { background: #fee2e2; color: #991b1b; }
        
        .barcode-sim {
            font-family: monospace;
            letter-spacing: 4px;
            font-size: 16px;
            font-weight: bold;
            background: #f8fafc;
            padding: 4px 10px;
            border-radius: 4px;
            border: 1px dashed #cbd5e1;
            display: inline-block;
        }

        /* Optimized Clean Print Styles */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
                color: #000000 !important;
                font-size: 12px !important;
            }
            .d-print-none {
                display: none !important;
            }
            .invoice-container {
                max-width: 100% !important;
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .invoice-inner {
                padding: 12px 18px !important;
            }
            .party-box {
                background: #ffffff !important;
                border: 1px solid #94a3b8 !important;
                box-shadow: none !important;
            }
            .table-invoice th {
                background: #f1f5f9 !important;
                color: #000000 !important;
                border-color: #94a3b8 !important;
            }
            .table-invoice td {
                border-color: #94a3b8 !important;
            }
            .summary-card {
                background: #ffffff !important;
                border: 1px solid #94a3b8 !important;
            }
            .order-meta-box {
                background: #ffffff !important;
                border: 1px solid #94a3b8 !important;
            }
            @page {
                size: A4 portrait;
                margin: 8mm;
            }
        }
    </style>
</head>
<body>

    <!-- Action Toolbar (Hidden during Print) -->
    <div class="container d-print-none mb-3" style="max-width: 820px;">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('admin.ecommerce-orders') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                        <i class="fa-solid fa-arrow-left me-1"></i> সকল অর্ডার
                    </a>
                    <span class="text-muted small">|</span>
                    <span class="fw-bold text-dark">অর্ডার: {{ $order->order_number ?? $order->id }}</span>
                    <span class="status-badge badge-{{ $order->status }}">{{ $order->status_label }}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('admin.ecommerce-orders.slip', $order) }}" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                        <i class="fa-solid fa-tag me-1"></i> পার্সেল স্টিকার / স্লিপ
                    </a>
                    <button onclick="window.print()" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold shadow-xs">
                        <i class="fa-solid fa-print me-1"></i> প্রিন্ট ইনভয়েস
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Printable Invoice Wrapper -->
    <div class="invoice-container">
        <div class="invoice-inner">
            
            <!-- Top Header & Brand Bar -->
            <div class="row align-items-center mb-3 pb-3 border-bottom">
                <div class="col-7 d-flex align-items-center gap-3">
                    <img src="{{ asset('images/logo.svg') }}" alt="Logo" style="height: 48px; width: auto;" onerror="this.src='/images/logo.png'; this.onerror=null;">
                    <div>
                        <h4 class="fw-bold text-primary mb-0" style="letter-spacing: -0.3px;">{{ $invoiceSettings['sender_name'] ?? 'আইডিয়া প্রকাশন' }}</h4>
                        <div class="text-muted small">{{ $invoiceSettings['sender_website'] ?? 'www.ideaabd.com' }} | জ্ঞান ও মননের ডিজিটাল বাতিঘর</div>
                    </div>
                </div>
                <div class="col-5 text-end">
                    <div class="invoice-header-badge mb-1">
                        {{ $invoiceSettings['invoice_title'] ?? 'ক্যাশ মেমো / ইনভয়েস' }}
                    </div>
                    <div class="barcode-sim text-dark">#{{ $order->order_number ?? $order->id }}</div>
                </div>
            </div>

            <!-- Order Metadata Quick Strip -->
            <div class="order-meta-box mb-3">
                <div class="row g-2 text-center text-md-start align-items-center">
                    <div class="col-6 col-md-3">
                        <span class="text-muted small d-block">ইনভয়েস নম্বর:</span>
                        <strong class="text-dark font-monospace">#{{ $order->order_number ?? $order->id }}</strong>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="text-muted small d-block">অর্ডারের তারিখ:</span>
                        <strong class="text-dark">{{ $order->created_at->format('d M, Y - h:i A') }}</strong>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="text-muted small d-block">পেমেন্ট মেথড:</span>
                        <strong class="text-dark">{{ $order->payment_method_label }}</strong>
                        @if($order->transaction_id)
                            <div class="small font-monospace text-primary" style="font-size: 11px;">TrxID: <strong>{{ $order->transaction_id }}</strong></div>
                        @endif
                    </div>
                    <div class="col-6 col-md-3 text-md-end">
                        <span class="text-muted small d-block">পেমেন্ট অবস্থা:</span>
                        <span class="badge {{ $order->payment_status === 'paid' ? 'bg-success' : 'bg-warning text-dark' }} px-2 py-1">
                            {{ $order->payment_status_label }}
                        </span>
                        @if($order->payment_phone)
                            <div class="small text-muted" style="font-size: 11px;">প্রেরক: {{ $order->payment_phone }}</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sender & Recipient Two Column Box -->
            <div class="row g-3 mb-3">
                
                <!-- Left: Sender (প্রেরক) -->
                <div class="col-md-6">
                    <div class="party-box sender">
                        <div class="party-box-title">
                            <span><i class="fa-solid fa-paper-plane text-primary me-1"></i> প্রেরক (From / Sender)</span>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">হেড অফিস</span>
                        </div>
                        <div class="party-name text-primary">{{ $invoiceSettings['sender_name'] ?? 'আইডিয়া প্রকাশন' }}</div>
                        <div class="text-dark mb-1">
                            <i class="fa-solid fa-location-dot text-danger me-1.5 small"></i>
                            {{ $invoiceSettings['sender_address'] ?? 'সেন্ট্রাল রোড, রংপুর ৫৪০০, বাংলাদেশ' }}
                        </div>
                        <div class="text-dark mb-1">
                            <i class="fa-solid fa-phone text-success me-1.5 small"></i>
                            <strong>মোবাইল:</strong> {{ $invoiceSettings['sender_phone'] ?? '01558712870' }}
                        </div>
                        @if(!empty($invoiceSettings['sender_email']))
                        <div class="text-muted small">
                            <i class="fa-solid fa-envelope text-primary me-1.5 small"></i>
                            {{ $invoiceSettings['sender_email'] }}
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Right: Recipient (প্রাপক) -->
                <div class="col-md-6">
                    <div class="party-box recipient">
                        <div class="party-box-title">
                            <span><i class="fa-solid fa-user-check text-success me-1"></i> প্রাপক (To / Recipient)</span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle">গ্রাহক</span>
                        </div>
                        <div class="party-name text-success">{{ $order->customer_name }}</div>
                        <div class="text-dark mb-1">
                            <i class="fa-solid fa-phone text-success me-1.5 small"></i>
                            <strong>মোবাইল:</strong> <span class="fw-bold fs-6">{{ $order->customer_phone }}</span>
                        </div>
                        <div class="text-dark mb-1">
                            <i class="fa-solid fa-map-location-dot text-primary me-1.5 small"></i>
                            <strong>ঠিকানা:</strong>
                            @if($order->house_road)
                                {{ $order->house_road }},
                            @endif
                            {{ $order->customer_address }}
                        </div>
                        <div class="text-muted small">
                            @if($order->thana)
                                <span class="me-2"><strong>থানা/উপজেলা:</strong> {{ $order->thana }}</span>
                            @endif
                            @if($order->post_code)
                                <span class="me-2"><strong>পোস্ট:</strong> {{ $order->post_code }}</span>
                            @endif
                            <span><strong>জেলা:</strong> {{ $order->district_label }}</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Gift Notice Box (If Order is Gift) -->
            @if($order->is_gift)
            <div class="p-2.5 px-3 bg-amber-50 rounded-3 border border-amber-200 mb-3 text-amber-900" style="background-color: #fffbeb; border-color: #fde68a;">
                <div class="d-flex align-items-start gap-2">
                    <i class="fa-solid fa-gift text-warning fs-5 mt-0.5"></i>
                    <div>
                        <strong class="d-block text-amber-900" style="color: #92400e;">উপহার প্রাপকের তথ্য (Gift Package):</strong>
                        <span class="me-3"><strong>নাম:</strong> {{ $order->gift_recipient_name }}</span>
                        <span class="me-3"><strong>ফোন:</strong> {{ $order->gift_recipient_phone }}</span>
                        @if($order->gift_recipient_address)
                            <span class="d-block mt-0.5"><strong>ঠিকানা:</strong> {{ $order->gift_recipient_address }}</span>
                        @endif
                        @if($order->gift_message)
                            <div class="mt-1 small fst-italic p-1.5 px-2 bg-white rounded border border-amber-200">
                                <strong>উপহার বার্তা:</strong> "{{ $order->gift_message }}"
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Items Table (বিলের পণ্যের বিবরণ) -->
            <table class="table-invoice">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 5%;">#</th>
                        <th style="width: 50%;">বই / পণ্যের বিবরণ</th>
                        <th class="text-center" style="width: 15%;">একক মূল্য</th>
                        <th class="text-center" style="width: 12%;">পরিমাণ</th>
                        <th class="text-end" style="width: 18%;">মোট টাকা</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center">১</td>
                        <td>
                            @if($order->book)
                                <div class="fw-bold text-dark fs-6">{{ $order->book->title }}</div>
                                @if($order->book->authors && $order->book->authors->count())
                                    <div class="text-muted small">লেখক: {{ $order->book->authors->pluck('name')->implode(', ') }}</div>
                                @endif
                                @if($order->book->isbn)
                                    <div class="text-muted small">ISBN: {{ $order->book->isbn }}</div>
                                @endif
                            @else
                                <div class="fw-bold text-dark">বইয়ের অর্ডার</div>
                            @endif
                        </td>
                        <td class="text-center fw-semibold">
                            ৳ {{ number_format($order->unit_price > 0 ? $order->unit_price : ($order->book->discount_price ?? $order->book->price ?? 0), 2) }}
                        </td>
                        <td class="text-center fw-bold">
                            {{ $order->quantity ?? 1 }} টি
                        </td>
                        <td class="text-end fw-bold text-dark">
                            @php
                                $itemUnit = $order->unit_price > 0 ? $order->unit_price : ($order->book->discount_price ?? $order->book->price ?? 0);
                                $itemQty = $order->quantity ?? 1;
                                $itemSubtotal = $itemUnit * $itemQty;
                            @endphp
                            ৳ {{ number_format($itemSubtotal, 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Summary & Terms Section (কাগজ সাশ্রয়ী কম্প্যাক্ট লেআউট) -->
            <div class="row g-3 align-items-start mb-3">
                
                <!-- Left: Terms & Instructions -->
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3 border" style="font-size: 12px;">
                        <div class="fw-bold text-dark mb-1 d-flex align-items-center gap-1.5">
                            <i class="fa-solid fa-shield-halved text-primary"></i> পলিসি ও শর্তাবলী:
                        </div>
                        <p class="text-muted mb-2">{{ $invoiceSettings['invoice_terms'] ?? 'পণ্য গ্রহণের সময় অনুগ্রহ করে চেক করে নিন। কোনো ত্রুটি থাকলে ডেলিভারি ম্যানের সামনেই হেল্পলাইনে যোগাযোগ করুন।' }}</p>
                        
                        @if($order->courier_name || $order->tracking_code)
                        <div class="p-2 bg-white rounded border mt-2">
                            <div class="small"><strong>কুরিয়ার:</strong> {{ $order->courier_name ?? 'নির্ধারিত নয়' }}</div>
                            @if($order->tracking_code)
                                <div class="small"><strong>ট্র্যাকিং আইডি:</strong> <span class="font-monospace text-primary fw-bold">{{ $order->tracking_code }}</span></div>
                            @endif
                        </div>
                        @endif

                        @if($order->admin_notes)
                        <div class="mt-2 text-muted small">
                            <strong>নোট:</strong> {{ $order->admin_notes }}
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Right: Financial Summary Breakdown -->
                <div class="col-md-6">
                    <div class="summary-card">
                        <div class="summary-row">
                            <span class="text-muted">পণ্যের সাবটোটাল:</span>
                            <span class="fw-bold text-dark">৳ {{ number_format($itemSubtotal, 2) }}</span>
                        </div>
                        <div class="summary-row">
                            <span class="text-muted">ডেলিভারি চার্জ:</span>
                            <span class="fw-semibold text-dark">৳ {{ number_format($order->shipping_cost ?? 0, 2) }}</span>
                        </div>
                        @if($order->is_gift && ($order->gift_wrap_fee > 0 || $order->is_gift))
                        <div class="summary-row">
                            <span class="text-muted">উপহার র‍্যাপিং চার্জ:</span>
                            <span class="fw-semibold text-dark">৳ {{ number_format($order->gift_wrap_fee > 0 ? $order->gift_wrap_fee : 20, 2) }}</span>
                        </div>
                        @endif
                        @if($order->discount_amount > 0)
                        <div class="summary-row text-danger">
                            <span>বিশেষ ছাড় (Discount):</span>
                            <span>- ৳ {{ number_format($order->discount_amount, 2) }}</span>
                        </div>
                        @endif
                        <div class="summary-row grand-total">
                            <span>সর্বমোট প্রদেয় (Total):</span>
                            <span>৳ {{ number_format($order->total_amount, 2) }}</span>
                        </div>
                        <div class="text-end mt-1">
                            <span class="small {{ $order->payment_status === 'paid' ? 'text-success' : 'text-danger' }} fw-bold">
                                @if($order->payment_status === 'paid')
                                    <i class="fa-solid fa-check-circle me-1"></i> পরিশোধিত (PAID)
                                @else
                                    <i class="fa-solid fa-hand-holding-dollar me-1"></i> ক্যাশ অন ডেলিভারি (Due on Delivery)
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Footer Signature & Greeting -->
            <div class="pt-3 border-top mt-3">
                <div class="row align-items-end">
                    <div class="col-7">
                        <p class="small text-muted mb-0 fw-semibold">
                            <i class="fa-solid fa-heart text-danger me-1"></i>
                            {{ $invoiceSettings['invoice_footer'] ?? 'বই পড়ার আনন্দ ছড়িয়ে পড়ুক সবার মাঝে। ideaabd-এর সাথে থাকার জন্য ধন্যবাদ!' }}
                        </p>
                        <div class="text-muted" style="font-size: 11px;">প্রিন্ট তারিখ: {{ date('d M, Y - h:i A') }} | সিস্টেম জেনারেটেড ইনভয়েস</div>
                    </div>
                    <div class="col-5 text-end">
                        <div class="d-inline-block text-center" style="min-width: 140px;">
                            <div style="height: 35px;"></div>
                            <div class="border-top border-dark pt-1 small fw-bold text-dark">অনুমোদিত স্বাক্ষরকারী</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</body>
</html>
