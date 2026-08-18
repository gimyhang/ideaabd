<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>লেখা অনুমোদন ও প্রকাশনা</title>
    <style>
        body { font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 20px; line-height: 1.6; }
        .email-container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        .header { background: linear-gradient(135deg, #198754 0%, #157347 100%); color: #ffffff; padding: 30px 25px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 700; }
        .header p { margin: 5px 0 0 0; opacity: 0.9; font-size: 14px; }
        .content { padding: 30px 25px; }
        .badge-success { display: inline-block; background-color: #dcfce7; color: #15803d; font-weight: 600; padding: 6px 14px; border-radius: 50px; font-size: 13px; margin-bottom: 20px; }
        .post-card { background: #f8fafc; border-radius: 10px; padding: 20px; margin: 20px 0; border: 1px solid #e2e8f0; }
        .post-title { font-size: 18px; font-weight: 700; color: #0f172a; margin: 0 0 8px 0; }
        .post-meta { font-size: 13px; color: #64748b; margin-bottom: 12px; }
        .btn { display: inline-block; background: #198754; color: #ffffff !important; text-decoration: none; padding: 12px 30px; border-radius: 50px; font-weight: 700; font-size: 15px; margin: 15px 0; text-align: center; }
        .footer { background: #f8fafc; padding: 20px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>আইডিয়া প্রকাশন ও ওয়েবজিন</h1>
            <p>সাহিত্য ও চিন্তার মুক্ত প্রকাশনা</p>
        </div>
        <div class="content">
            <div style="text-align: center;">
                <span class="badge-success">✓ লেখা প্রকাশিত হয়েছে</span>
            </div>
            
            <h2 style="font-size: 20px; color: #0f172a; margin-top: 0;">প্রিয় লেখক {{ $author?->name ?? 'লেখক' }},</h2>
            <p>আইডিয়া প্রকাশনের ব্লগে আপনার জমা দেওয়া লেখাটি অ্যাডমিন কর্তৃক পর্যালোচিত হয়ে <strong>অনুমোদিত ও প্রকাশিত</strong> হয়েছে।</p>
            
            <div class="post-card">
                <h3 class="post-title">“{{ $post->title }}”</h3>
                <div class="post-meta">
                    <span>ক্যাটাগরি: {{ $post->category?->name ?? 'সাধারণ' }}</span> | 
                    <span>তারিখ: {{ date('d M Y') }}</span>
                </div>
                @if($post->excerpt)
                <p style="font-size: 14px; color: #475569; margin: 0;">{{ \Illuminate\Support\Str::limit($post->excerpt, 150) }}</p>
                @endif
            </div>

            <p>এখন পাঠকরা আপনার লেখাটি সরাসরি ওয়েবসাইটে পড়তে ও মন্তব্য করতে পারবেন। নিচে দেওয়া বাটনে ক্লিক করে আপনার প্রকাশিত লেখাটি দেখুন:</p>

            <div style="text-align: center;">
                <a href="{{ url('/blog/' . ($post->slug ?? $post->id)) }}" class="btn">লেখাটি পড়ুন</a>
            </div>
        </div>
        <div class="footer">
            <p>© {{ date('Y') }} আইডিয়া প্রকাশন (ideaabd.com)। সর্বস্বত্ব সংরক্ষিত।</p>
            <p>লেখা সম্পর্কিত যেকোনো জিজ্ঞাসায়: support@ideaabd.com</p>
        </div>
    </div>
</body>
</html>
