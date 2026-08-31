<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>অ্যাকাউন্ট অনুমোদন</title>
    <style>
        body { font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 20px; line-height: 1.6; }
        .email-container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        .header { background: linear-gradient(135deg, #003366 0%, #0066cc 100%); color: #ffffff; padding: 30px 25px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 700; }
        .header p { margin: 5px 0 0 0; opacity: 0.9; font-size: 14px; }
        .content { padding: 30px 25px; }
        .badge-success { display: inline-block; background-color: #dcfce7; color: #15803d; font-weight: 600; padding: 6px 14px; border-radius: 50px; font-size: 13px; margin-bottom: 20px; }
        .info-box { background: #f1f5f9; border-radius: 8px; padding: 20px; margin: 20px 0; border-left: 4px solid #0066cc; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; }
        .info-row:last-child { margin-bottom: 0; }
        .info-label { color: #64748b; font-weight: 600; }
        .info-value { color: #0f172a; font-weight: 700; }
        .btn { display: inline-block; background: #0066cc; color: #ffffff !important; text-decoration: none; padding: 12px 30px; border-radius: 50px; font-weight: 700; font-size: 15px; margin: 15px 0; text-align: center; }
        .footer { background: #f8fafc; padding: 20px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
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
                <span class="badge-success">✓ অনুমোদন সম্পন্ন</span>
            </div>
            
            <h2 style="font-size: 20px; color: #0f172a; margin-top: 0;">অভিনন্দন, {{ $user->name }}!</h2>
            <p>আইডিয়া প্রকাশনে আপনার <strong>{{ $user->reg_type === 'author' ? 'লেখক' : ($user->reg_type === 'seller' ? 'সেলার' : ($user->reg_type === 'publisher' ? 'প্রকাশক' : 'ব্যবহারকারী')) }}</strong> অ্যাকাউন্টটি সফলভাবে অ্যাডমিন কর্তৃক অনুমোদিত হয়েছে।</p>
            
            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">লগইন ইউজারনেম (মোবাইল):</span>
                    <span class="info-value">{{ $user->phone }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">ইমেইল এড্রেস:</span>
                    <span class="info-value">{{ $user->email }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">অ্যাকাউন্ট টাইপ:</span>
                    <span class="info-value">{{ ucfirst($user->reg_type ?? $user->role) }}</span>
                </div>
            </div>

            <p>আপনি এখন আপনার মোবাইল নম্বর এবং নিবন্ধিত পাসওয়ার্ড দিয়ে যেকোনো সময় লগইন করতে পারবেন।</p>

            <div style="text-align: center;">
                <a href="{{ url('/login') }}" class="btn">লগইন করুন</a>
            </div>

            <div style="background-color: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 12px 16px; margin-top: 20px; text-align: center; font-size: 12px; color: #64748b; line-height: 1.5;">
                🔔 <strong>বিশেষ বিজ্ঞপ্তি:</strong> এটি আইডিয়া প্রকাশনের একটি স্বয়ংক্রিয় অফিসিয়াল বার্তা, এতে রিপ্লাই (Reply) করার প্রয়োজন নেই। যেকোনো তথ্য বা জরুরি প্রয়োজনে অনুগ্রহ করে আমাদের হেল্পলাইনে <strong>০১৭২৬-৯৭৬৯৮২ / ০১৫৫৮-৭১২৮১০</strong> নম্বরে কল করুন অথবা ভিজিট করুন <a href="https://www.ideaabd.com" style="color: #0066cc; text-decoration: none; font-weight: bold;">www.ideaabd.com</a>।
            </div>
        </div>
        <div class="footer">
            <p style="margin: 0 0 4px 0; font-weight: 600; color: #475569;">© {{ date('Y') }} আইডিয়া প্রকাশন (ideaabd.com)। সর্বস্বত্ব সংরক্ষিত।</p>
            <p style="margin: 0;">হেল্পলাইন: ০১৭২৬-৯৭৬৯৮২, ০১৫৫৮-৭১২৮১০ • ইমেইল: ad@ideaabd.com</p>
        </div>
    </div>
</body>
</html>
