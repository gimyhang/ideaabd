<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>পাসওয়ার্ড রিসেট কোড ও লিংক</title>
    <style>
        body { font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 20px; line-height: 1.6; }
        .email-container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 6px 20px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; }
        .header { background: linear-gradient(135deg, #003366 0%, #0066cc 100%); color: #ffffff; padding: 32px 25px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 700; letter-spacing: 0.5px; }
        .header p { margin: 6px 0 0 0; opacity: 0.9; font-size: 14px; }
        .content { padding: 32px 28px; }
        .alert-timer { display: inline-block; background-color: #fffbeb; color: #b45309; border: 1px solid #fef3c7; font-weight: 700; padding: 8px 18px; border-radius: 50px; font-size: 13.5px; margin-bottom: 22px; }
        .otp-box { background: linear-gradient(135deg, #f0fdf4 0%, #e0f2fe 100%); border: 2px dashed #0284c7; border-radius: 12px; padding: 20px; text-align: center; margin: 20px 0; }
        .otp-code { font-size: 32px; font-weight: 800; letter-spacing: 8px; color: #0369a1; font-family: monospace; display: block; margin: 8px 0; }
        .btn-wrapper { text-align: center; margin: 28px 0 24px; }
        .btn { display: inline-block; background: #0066cc; color: #ffffff !important; text-decoration: none; padding: 14px 34px; border-radius: 50px; font-weight: 700; font-size: 15px; box-shadow: 0 4px 12px rgba(0,102,204,0.25); text-align: center; }
        .whatsapp-box { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 14px 18px; margin-top: 20px; text-align: center; font-size: 13.5px; color: #166534; }
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
                <span class="alert-timer">⏳ কোডের মেয়াদ {{ $expireMinutes }} মিনিট</span>
            </div>
            
            <h2 style="font-size: 20px; color: #0f172a; margin-top: 0;">প্রিয় {{ $user->name }},</h2>
            <p style="color: #334155; font-size: 15px;">
                আপনার আইডিয়া প্রকাশন অ্যাকাউন্টের পাসওয়ার্ড রিসেট করার জন্য একটি অনুরোধ পাওয়া গেছে।
            </p>

            @if(!empty($otpCode))
            <div class="otp-box">
                <span style="font-size: 13px; color: #64748b; text-transform: uppercase; font-weight: bold;">আপনার ৬ ডিজিটের রিসেট কোড (OTP)</span>
                <span class="otp-code">{{ $otpCode }}</span>
                <span style="font-size: 12px; color: #64748b;">এই কোডটি কারো সাথে শেয়ার করবেন না</span>
            </div>
            @endif
            
            <div class="btn-wrapper">
                <a href="{{ $resetUrl }}" class="btn">এক ক্লিকে পাসওয়ার্ড রিসেট করুন</a>
            </div>

            <div class="whatsapp-box">
                💬 <strong>জরুরি সহায়তা / হোয়াটসঅ্যাপ:</strong> যেকোনো প্রয়োজনে সরাসরি আমাদের অফিশিয়াল হেল্পলাইনে যোগাযোগ করুন: <strong>০১৭২৬-৯৭৬৯৮২ / ০১৫৫৮-৭১২৮১০</strong>
            </div>

            <div style="background-color: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 12px 16px; margin-top: 22px; text-align: center; font-size: 12px; color: #64748b; line-height: 1.5;">
                🔔 <strong>বিশেষ বিজ্ঞপ্তি:</strong> এটি আইডিয়া প্রকাশনের একটি স্বয়ংক্রিয় অফিসিয়াল বার্তা, এতে রিপ্লাই (Reply) করার প্রয়োজন নেই। যেকোনো তথ্য বা প্রয়োজনে আমাদের হেল্পলাইনে কল করুন অথবা ভিজিট করুন <a href="https://www.ideaabd.com" style="color: #0066cc; text-decoration: none; font-weight: bold;">www.ideaabd.com</a>।
            </div>
        </div>
        <div class="footer">
            <p style="margin: 0 0 4px 0; font-weight: 600; color: #475569;">© {{ date('Y') }} আইডিয়া প্রকাশন (ideaabd.com)। সর্বস্বত্ব সংরক্ষিত।</p>
            <p style="margin: 0;">হেল্পলাইন: ০১৭২৬-৯৭৬৯৮২, ০১৫৫৮-৭১২৮১০ • ইমেইল: ad@ideaabd.com</p>
        </div>
    </div>
</body>
</html>
