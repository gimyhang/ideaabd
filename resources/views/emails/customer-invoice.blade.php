@php
    $settings = is_array($invoiceSettings ?? null) && !empty($invoiceSettings) 
        ? $invoiceSettings 
        : \App\Http\Controllers\Admin\IdeaAccountingController::getInvoiceSettings();
    $invDate = $invoice->invoice_date;
    $invDateStr = is_object($invDate) ? $invDate->format('d/m/Y') : ($invDate ? date('d/m/Y', strtotime($invDate)) : date('d/m/Y'));
@endphp
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $invoice->type_label ?? 'ইনভয়েস' }} #{{ $invoice->invoice_no }}</title>
    <style>
        body { font-family: 'Segoe UI', 'Nikosh', 'Kalpurush', Roboto, Helvetica, Arial, sans-serif; background-color: #f1f5f9; color: #1e293b; margin: 0; padding: 20px; line-height: 1.6; }
        .email-container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; }
        .header { background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); color: #ffffff; padding: 25px; text-align: center; }
        .header h1 { margin: 0; font-size: 22px; font-weight: 700; }
        .header p { margin: 4px 0 0 0; opacity: 0.9; font-size: 13px; }
        .content { padding: 25px 20px; }
        .badge-type { display: inline-block; background-color: #dbeafe; color: #1d4ed8; font-weight: 700; padding: 5px 14px; border-radius: 50px; font-size: 12px; margin-bottom: 15px; }
        .invoice-card { background: #f8fafc; border-radius: 10px; padding: 18px; margin: 18px 0; border: 1px solid #e2e8f0; }
        .invoice-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .invoice-table td { padding: 6px 4px; border-bottom: 1px dashed #cbd5e1; }
        .invoice-table td:last-child { text-align: right; }
        .custom-note { background: #fffbeb; border-left: 4px solid #f59e0b; padding: 12px 15px; border-radius: 6px; margin: 15px 0; font-size: 13px; color: #92400e; }
        .btn-primary { display: inline-block; background: #0d6efd; color: #ffffff !important; text-decoration: none; padding: 12px 32px; border-radius: 50px; font-weight: 700; font-size: 15px; margin: 15px 0; text-align: center; box-shadow: 0 4px 12px rgba(13, 110, 253, 0.25); }
        .footer { background: #f8fafc; padding: 20px; text-align: center; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>{{ $settings['business_name'] ?? 'আইডিয়া প্রকাশন' }}</h1>
            <p>{{ $settings['tagline'] ?? 'বই প্রকাশনা, মুদ্রণ ও পরিবেশনা' }}</p>
        </div>
        <div class="content">
            <div style="text-align: center;">
                <span class="badge-type">{{ $invoice->type_label ?? 'ইনভয়েস' }} #{{ $invoice->invoice_no }}</span>
            </div>
            
            <h2 style="font-size: 18px; color: #0f172a; margin-top: 0;">
                সম্মানিত গ্রাহক {{ $invoice->customer_name ? $invoice->customer_name : '' }},
            </h2>
            <p style="font-size: 14px; color: #334155; margin-bottom: 10px;">
                {{ $settings['business_name'] ?? 'আইডিয়া প্রকাশন' }} থেকে আপনার অর্ডারের <strong>{{ $invoice->type_label ?? 'ইনভয়েস' }}</strong> প্রস্তুত করা হয়েছে। নিচে সংক্ষিপ্ত বিবরণ দেওয়া হলো:
            </p>

            @if(!empty($customMessage))
                <div class="custom-note">
                    <strong>বার্তা:</strong> {{ $customMessage }}
                </div>
            @endif
            
            <div class="invoice-card">
                <table class="invoice-table">
                    <tr>
                        <td style="color: #64748b;">দলিল নম্বর:</td>
                        <td><strong>#{{ $invoice->invoice_no }}</strong></td>
                    </tr>
                    <tr>
                        <td style="color: #64748b;">তারিখ:</td>
                        <td><strong>{{ $invDateStr }}</strong></td>
                    </tr>
                    @if($invoice->customer_org)
                    <tr>
                        <td style="color: #64748b;">প্রতিষ্ঠান / সংস্থা:</td>
                        <td><strong>{{ $invoice->customer_org }}</strong></td>
                    </tr>
                    @endif
                    <tr>
                        <td style="color: #64748b;">সর্বমোট বিল:</td>
                        <td style="color: #0d6efd; font-weight: bold; font-size: 15px;">৳{{ number_format((float)$invoice->grand_total, 2) }}</td>
                    </tr>
                    @if(in_array($invoice->type, ['invoice', 'challan']))
                    <tr>
                        <td style="color: #64748b;">পরিশোধিত টাকা:</td>
                        <td style="color: #15803d; font-weight: bold;">৳{{ number_format((float)$invoice->paid_amount, 2) }}</td>
                    </tr>
                    @if($invoice->due_amount > 0)
                    <tr>
                        <td style="color: #64748b;">অবশিষ্ট বকেয়া:</td>
                        <td style="color: #dc2626; font-weight: bold;">৳{{ number_format((float)$invoice->due_amount, 2) }}</td>
                    </tr>
                    @endif
                    @endif
                </table>
            </div>

            <p style="font-size: 13.5px; color: #475569; text-align: center;">
                আপনার সম্পূর্ণ বিল এবং ডেলিভারি চালান অনলাইন কপি দেখতে বা সরাসরি পিডিএফ (PDF) ডাউনলোড করতে নিচের বাটনে ক্লিক করুন:
            </p>

            <div style="text-align: center; margin: 25px 0;">
                <a href="{{ $invoice->public_url }}" class="btn-primary" target="_blank">
                    📄 বিল ও চালান দেখুন / PDF ডাউনলোড
                </a>
            </div>

            <p style="font-size: 12px; color: #94a3b8; text-align: center; word-break: break-all; margin-bottom: 20px;">
                বাটন কাজ না করলে এই লিংকে ক্লিক করুন:<br>
                <a href="{{ $invoice->public_url }}" style="color: #0d6efd;">{{ $invoice->public_url }}</a>
            </p>

            <div style="background-color: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 12px 16px; margin-top: 15px; text-align: center; font-size: 12px; color: #64748b; line-height: 1.5;">
                🔔 <strong>বিশেষ বিজ্ঞপ্তি:</strong> এটি আইডিয়া প্রকাশনের একটি স্বয়ংক্রিয় অফিসিয়াল বার্তা, এতে রিপ্লাই (Reply) করার প্রয়োজন নেই। যেকোনো তথ্য বা জরুরি প্রয়োজনে অনুগ্রহ করে আমাদের হেল্পলাইনে <strong>০১৭২৬-৯৭৬৯৮২ / ০১৫৫৮-৭১২৮১০</strong> নম্বরে কল করুন অথবা ভিজিট করুন <a href="https://www.ideaabd.com" style="color: #0d6efd; text-decoration: none; font-weight: bold;">www.ideaabd.com</a>।
            </div>
        </div>
        <div class="footer">
            <p style="margin: 0 0 5px 0; font-weight: 600; color: #475569;">{{ $settings['business_name'] ?? 'আইডিয়া প্রকাশন' }}</p>
            <p style="margin: 0 0 5px 0;">{{ $settings['address'] ?? 'ঢাকা, বাংলাদেশ' }} · হেল্পলাইন: {{ $settings['phone'] ?? '০১৭২৬-৯৭৬৯৮২, ০১৫৫৮-৭১২৮১০' }}</p>
            <p style="margin: 0;">ইমেইল: {{ $settings['email'] ?? 'ad@ideaabd.com' }}</p>
        </div>
    </div>
</body>
</html>
