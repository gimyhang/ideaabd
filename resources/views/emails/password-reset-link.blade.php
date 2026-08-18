<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>পাসওয়ার্ড রিসেট লিংক</title>
    <style>
        body { font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 20px; line-height: 1.6; }
        .email-container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 14px; overflow: hidden; box-shadow: 0 6px 20px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; }
        .header { background: linear-gradient(135deg, #003366 0%, #0066cc 100%); color: #ffffff; padding: 32px 25px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 700; letter-spacing: 0.5px; }
        .header p { margin: 6px 0 0 0; opacity: 0.9; font-size: 14px; }
        .content { padding: 32px 28px; }
        .alert-timer { display: inline-block; background-color: #fffbeb; color: #b45309; border: 1px solid #fef3c7; font-weight: 700; padding: 8px 18px; border-radius: 50px; font-size: 13.5px; margin-bottom: 22px; }
        .info-box { background: #f8fafc; border-radius: 10px; padding: 18px 20px; margin: 22px 0; border: 1px dashed #cbd5e1; border-left: 4px solid #0066cc; }
        .btn-wrapper { text-align: center; margin: 28px 0 24px; }
        .btn { display: inline-block; background: #0066cc; color: #ffffff !important; text-decoration: none; padding: 14px 34px; border-radius: 50px; font-weight: 700; font-size: 15px; box-shadow: 0 4px 12px rgba(0,102,204,0.25); text-align: center; }
        .link-fallback { word-break: break-all; background: #f1f5f9; padding: 12px 14px; border-radius: 6px; font-size: 12px; color: #475569; margin-top: 15px; }
        .footer { background: #f8fafc; padding: 22px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>আইডিয়া প্রকাশন</h1>
            <p>অনলাইন বই ও প্রকাশনা প্ল্যাটফর্ম</p>
        </div>
        <div class="content">
            <div style="text-align: center;">
                <span class="alert-timer">⏳ ওয়ান-টাইম লিংক — মেয়াদ {{ $expireMinutes }} মিনিট</span>
            </div>
            
            <h2 style="font-size: 20px; color: #0f172a; margin-top: 0;">প্রিয় {{ $user->name }},</h2>
            <p style="color: #334155; font-size: 15px;">
                আপনার আইডিয়া প্রকাশন অ্যাকাউন্টের পাসওয়ার্ড রিসেট করার জন্য একটি অনুরোধ পাওয়া গেছে। নিচের বাটনে ক্লিক করে নতুন পাসওয়ার্ড সেট করুন:
            </p>
            
            <div class="btn-wrapper">
                <a href="{{ $resetUrl }}" class="btn">পাসওয়ার্ড রিসেট করুন</a>
            </div>

            <div class="info-box">
                <p style="margin: 0 0 6px 0; font-size: 13.5px; color: #334155;"><strong>⚠️ গুরুত্বপূর্ণ নিরাপত্তা নির্দেশনা:</strong></p>
                <ul style="margin: 0; padding-left: 20px; font-size: 13px; color: #64748b;">
                    <li>এই লিংকটি শুধুমাত্র <strong>একবার (One-Time)</strong> ব্যবহার করা যাবে।</li>
                    <li>লিংকটির মেয়াদ ইমেইল পাঠানোর পর থেকে ঠিক <strong>{{ $expireMinutes }} মিনিট</strong> থাকবে।</li>
                    <li>আপনি যদি এই পাসওয়ার্ড রিসেটের অনুরোধ না করে থাকেন, তবে কোনো পদক্ষেপ নেওয়ার প্রয়োজন নেই। আপনার পাসওয়ার্ড নিরাপদ থাকবে।</li>
                </ul>
            </div>

            <p style="font-size: 13px; color: #64748b; margin-top: 25px; margin-bottom: 5px;">
                বাটনটি কাজ না করলে নিচের পুরো লিংকটি কপি করে আপনার ব্রাউজারে পেস্ট করুন:
            </p>
            <div class="link-fallback">
                <a href="{{ $resetUrl }}" style="color: #0066cc; text-decoration: none;">{{ $resetUrl }}</a>
            </div>
        </div>
        <div class="footer">
            <p style="margin: 0 0 4px 0;">© {{ date('Y') }} আইডিয়া প্রকাশন (ideaabd.com)। সর্বস্বত্ব সংরক্ষিত।</p>
            <p style="margin: 0;">যেকোনো সহায়তায় যোগাযোগ করুন: support@ideaabd.com</p>
        </div>
    </div>
</body>
</html>
