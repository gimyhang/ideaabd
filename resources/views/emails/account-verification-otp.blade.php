<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>অ্যাকাউন্ট ভেরিফিকেশন কোড</title>
    <style>
        body { font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 20px; line-height: 1.6; }
        .email-container { max-width: 580px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 6px 20px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; }
        .header { background: linear-gradient(135deg, #003366 0%, #0066cc 100%); color: #ffffff; padding: 30px 24px; text-align: center; }
        .header h1 { margin: 0; font-size: 22px; font-weight: 700; }
        .header p { margin: 6px 0 0 0; opacity: 0.9; font-size: 13.5px; }
        .content { padding: 30px 24px; }
        .alert-timer { display: inline-block; background-color: #fffbeb; color: #b45309; border: 1px solid #fef3c7; font-weight: 700; padding: 6px 16px; border-radius: 50px; font-size: 13px; margin-bottom: 20px; }
        .otp-box { background: linear-gradient(135deg, #f0fdf4 0%, #e0f2fe 100%); border: 2px dashed #0284c7; border-radius: 12px; padding: 20px; text-align: center; margin: 20px 0; }
        .otp-code { font-size: 34px; font-weight: 800; letter-spacing: 8px; color: #0369a1; font-family: monospace; display: block; margin: 8px 0; }
        .whatsapp-box { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 12px 16px; margin-top: 20px; text-align: center; font-size: 13px; color: #166534; }
        .footer { background: #f8fafc; padding: 20px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>আইডিয়া প্রকাশন (ideaabd.com)</h1>
            <p>নতুন অ্যাকাউন্ট রেজিস্ট্রেশন ও ভেরিফিকেশন</p>
        </div>
        <div class="content">
            <div style="text-align: center;">
                <span class="alert-timer">⏳ কোডের মেয়াদ {{ $expireMinutes }} মিনিট</span>
            </div>
            
            <h2 style="font-size: 18px; color: #0f172a; margin-top: 0;">প্রিয় গ্রাহক,</h2>
            <p style="color: #334155; font-size: 14.5px;">
                আইডিয়া প্রকাশনে নতুন অ্যাকাউন্ট রেজিস্ট্রেশনের জন্য নিচে আপনার ৬ ডিজিটের ওটিপি ভেরিফিকেশন কোড দেওয়া হলো:
            </p>

            <div class="otp-box">
                <span style="font-size: 12.5px; color: #64748b; text-transform: uppercase; font-weight: bold;">আপনার ৬ ডিজিটের ইমেইল ভেরিফিকেশন কোড</span>
                <span class="otp-code">{{ $otpCode }}</span>
                <span style="font-size: 12px; color: #64748b;">এই কোডটির মেয়াদ মাত্র ২ মিনিট। কারো সাথে শেয়ার করবেন না।</span>
            </div>

            <div class="whatsapp-box">
                💬 <strong>জরুরি সহায়তা / হেল্পলাইন:</strong> ০১৭২৬-৯৭৬৯৮২ / ০১৫৫৮-৭১২৮১০ | <a href="https://www.ideaabd.com" style="color: #0369a1; font-weight: bold; text-decoration: none;">www.ideaabd.com</a>
            </div>
        </div>
        <div class="footer">
            <p style="margin: 0 0 4px 0; font-weight: 600; color: #475569;">© {{ date('Y') }} আইডিয়া প্রকাশন (ideaabd.com)। সর্বস্বত্ব সংরক্ষিত।</p>
        </div>
    </div>
</body>
</html>
