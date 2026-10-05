<?php

namespace App\Support;

use App\Models\AdminDashboardSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SiteSetting
{
    protected const CACHE_KEY = 'site_global_settings_cache';

    public static function all(): array
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            try {
                if (! Schema::hasTable('admin_dashboard_settings')) {
                    return [];
                }

                $rows = DB::table('admin_dashboard_settings')->get();
                $settings = [];
                foreach ($rows as $row) {
                    $val = $row->value;
                    $decoded = json_decode($val, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        if (is_string($decoded)) {
                            $second = json_decode($decoded, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($second)) {
                                $decoded = $second;
                            }
                        }
                        $settings[$row->key] = $decoded;
                    } else {
                        $settings[$row->key] = $val;
                    }
                }
                return $settings;
            } catch (\Throwable $e) {
                return [];
            }
        });
    }

    public static function getAll(): array
    {
        return self::all();
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = self::all();
        return $all[$key] ?? $default;
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public static function isPhoneVerificationEnabled(): bool
    {
        $val = self::get('phone_verification_enabled', '1');
        if (is_bool($val)) {
            return $val;
        }
        if (is_array($val)) {
            $val = reset($val);
        }
        return !in_array(strtolower(trim((string)$val)), ['0', 'false', 'off', 'disabled', 'null', ''], true);
    }

    public static function isEmailVerificationEnabled(): bool
    {
        $val = self::get('email_verification_enabled', '1');
        if (is_bool($val)) {
            return $val;
        }
        if (is_array($val)) {
            $val = reset($val);
        }
        return !in_array(strtolower(trim((string)$val)), ['0', 'false', 'off', 'disabled', 'null', ''], true);
    }

    public static function name(): string
    {
        return (string) (self::get('site_name') ?: config('brand.name', 'আইডিয়া প্রকাশন'));
    }

    public static function siteName(): string
    {
        return self::name();
    }

    public static function tagline(): string
    {
        return (string) (self::get('site_tagline') ?: config('brand.tagline', 'বই ও মুক্তচিন্তার ডিজিটাল প্রকাশনা'));
    }

    public static function siteTagline(): string
    {
        return self::tagline();
    }

    public static function logoUrl(): ?string
    {
        $logo = self::get('site_logo') ?: config('brand.logo');
        return self::resolveImageUrl($logo, 'images/logo.svg');
    }

    public static function loginLogoUrl(): ?string
    {
        $loginLogo = self::get('site_login_logo') ?: self::get('site_logo') ?: config('brand.logo');
        return self::resolveImageUrl($loginLogo, 'images/logo.svg');
    }

    public static function logoHeight(): int
    {
        return (int) (self::get('site_logo_height') ?: 52);
    }

    public static function logoWidth(): int
    {
        return (int) (self::get('site_logo_width') ?: 220);
    }

    public static function logoScale(): int
    {
        return (int) (self::get('site_logo_scale') ?: 100);
    }

    public static function logoPaddingY(): int
    {
        return (int) (self::get('site_logo_padding_y') ?? 2);
    }

    public static function logoPaddingX(): int
    {
        return (int) (self::get('site_logo_padding_x') ?? 0);
    }

    public static function showBrandText(): bool
    {
        $val = self::get('site_logo_show_text');
        if ($val === null) {
            return true;
        }
        return filter_var($val, FILTER_VALIDATE_BOOLEAN);
    }

    public static function faviconUrl(): ?string
    {
        $favicon = self::get('site_favicon') ?: config('brand.favicon');
        return self::resolveImageUrl($favicon, 'favicon.ico') ?: (file_exists(public_path('images/logo-mark.svg')) ? asset('images/logo-mark.svg') : asset('favicon.ico'));
    }

    public static function banner1Url(): ?string
    {
        $banner = self::get('home_banner_1');
        return self::resolveImageUrl($banner);
    }

    public static function banner2Url(): ?string
    {
        $banner = self::get('home_banner_2');
        return self::resolveImageUrl($banner);
    }

    public static function heroSlides(): array
    {
        $slides = self::get('home_hero_slides');
        if (is_array($slides) && !empty($slides)) {
            return array_values(array_filter($slides, fn($s) => $s['is_active'] ?? true));
        }

        return [
            [
                'id' => '1',
                'badge' => 'বইমেলা বিশেষ ছাড়',
                'badge_color' => 'bg-warning text-dark',
                'title' => 'জ্ঞানের আলোয় উদ্ভাসিত হোক প্রতিটি মন',
                'subtitle' => 'আইডিয়া প্রকাশনীর সকল নতুন ও জনপ্রিয় বইয়ে পাচ্ছেন আকর্ষণীয় মূল্যছাড়।',
                'btn_text' => 'বই কিনুন',
                'btn_url' => '/books',
                'btn_icon' => 'fa-solid fa-cart-shopping',
                'btn_class' => 'btn-light text-primary',
                'icon' => 'fa-solid fa-book-open-reader',
                'bg_gradient' => 'linear-gradient(135deg, #003366 0%, #0066cc 100%)',
                'is_active' => true,
            ],
            [
                'id' => '2',
                'badge' => 'আইডিয়াপত্র ও ব্লগ',
                'badge_color' => 'bg-warning text-dark',
                'title' => 'আইডিয়াপত্র — মুক্তচিন্তা, সাহিত্য ও ব্লগ',
                'subtitle' => 'সমকালীন গল্প, কবিতা, প্রবন্ধ ও মুক্তচিন্তার ডিজিটাল প্রকাশনা ও নিবন্ধ।',
                'btn_text' => 'আইডিয়াপত্র পড়ুন',
                'btn_url' => '/blog',
                'btn_icon' => 'fa-solid fa-pen-nib',
                'btn_class' => 'btn-warning text-dark',
                'icon' => 'fa-solid fa-pen-nib',
                'bg_gradient' => 'linear-gradient(135deg, #4a044e 0%, #86198f 100%)',
                'is_active' => true,
            ],
            [
                'id' => '3',
                'badge' => 'স্মার্ট রিডিং',
                'badge_color' => 'bg-success text-white',
                'title' => 'হাজারো ডিজিটাল ই-বুক কালেকশন',
                'subtitle' => 'স্মার্টফোন বা যেকোনো ডিভাইসে তাৎক্ষণিক পিডিএফ ও ই-পাব ডাউনলোড করে পড়ার সুবিধা।',
                'btn_text' => 'ই-বুক লাইব্রেরি',
                'btn_url' => '/ebooks',
                'btn_icon' => 'fa-solid fa-mobile-screen-button',
                'btn_class' => 'btn-light text-primary',
                'icon' => 'fa-solid fa-mobile-screen-button',
                'bg_gradient' => 'linear-gradient(135deg, #064e3b 0%, #059669 100%)',
                'is_active' => true,
            ],
            [
                'id' => '4',
                'badge' => 'ডিজিটাল সাময়িকী',
                'badge_color' => 'bg-info text-dark',
                'title' => 'আইডিয়া ওয়েবজিন ও সাহিত্য সাময়িকী',
                'subtitle' => 'সাহিত্য, শিল্প ও সংস্কৃতির মাসিক ও বিশেষ সংখ্যাগুলোর ডিজিটাল সংকলন।',
                'btn_text' => 'সংখ্যাগুলো পড়ুন',
                'btn_url' => '/webzine',
                'btn_icon' => 'fa-solid fa-newspaper',
                'btn_class' => 'btn-warning text-dark',
                'icon' => 'fa-solid fa-newspaper',
                'bg_gradient' => 'linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%)',
                'is_active' => true,
            ],
            [
                'id' => '5',
                'badge' => 'লেখক কর্নার',
                'badge_color' => 'bg-light text-dark',
                'title' => 'লেখক ডিরেক্টরি ও সাহিত্যিক পরিচিতি',
                'subtitle' => 'দেশ-বিদেশের খ্যাতনামা ও প্রতিশ্রুতিশীল লেখকদের জীবন ও গ্রন্থাবলী।',
                'btn_text' => 'লেখক তালিকা দেখুন',
                'btn_url' => '/authors',
                'btn_icon' => 'fa-solid fa-user-pen',
                'btn_class' => 'btn-light text-primary',
                'icon' => 'fa-solid fa-user-pen',
                'bg_gradient' => 'linear-gradient(135deg, #1e1b4b 0%, #4338ca 100%)',
                'is_active' => true,
            ],
            [
                'id' => '6',
                'badge' => 'প্রকাশনা সংকলন',
                'badge_color' => 'bg-danger text-white',
                'title' => 'প্রকাশক ডিরেক্টরি ও প্রকাশনা সংস্থা',
                'subtitle' => 'বাংলাদেশের সকল স্বনামধন্য প্রকাশনীর বইয়ের বিশাল সম্ভার এক প্ল্যাটফর্মে।',
                'btn_text' => 'প্রকাশক তালিকা দেখুন',
                'btn_url' => '/publishers',
                'btn_icon' => 'fa-solid fa-building-columns',
                'btn_class' => 'btn-light text-primary',
                'icon' => 'fa-solid fa-building-columns',
                'bg_gradient' => 'linear-gradient(135deg, #881337 0%, #e11d48 100%)',
                'is_active' => true,
            ],
        ];
    }

    public static function blogOgBannerUrl(): ?string
    {
        $banner = self::get('blog_og_banner') ?: self::get('social_og_banner');
        return self::resolveImageUrl($banner, 'images/blog/ideapatra-og.jpg');
    }

    public static function ideapatraSectionBadge(): string
    {
        return (string) (self::get('ideapatra_section_badge') ?: 'আইডিয়াপত্র সাময়িকী ও ব্লগ');
    }

    public static function ideapatraSectionTitle(): string
    {
        return (string) (self::get('ideapatra_section_title') ?: 'সমকালীন সাহিত্য, প্রবন্ধ ও মুক্তচিন্তার পোস্ট');
    }

    public static function ideapatraSectionSubtitle(): string
    {
        return (string) (self::get('ideapatra_section_subtitle') ?: 'আইডিয়া প্রকাশনের লেখক ও গবেষকদের সমকালীন সাহিত্যকর্ম ও পাঠপ্রতিক্রিয়া');
    }

    public static function ecommerce(): array
    {
        $ecom = self::get('ecommerce_settings', []);
        return is_array($ecom) ? $ecom : [];
    }

    public static function helplinePhone(): string
    {
        $ecom = self::ecommerce();
        return $ecom['helpline_phone'] ?? '01726976982';
    }

    public static function blogCustomizer(): array
    {
        $default = [
            'hero_badge'        => 'সাহিত্য, শিল্প-সংস্কৃতি, গবেষণা ও মুক্তচিন্তা',
            'hero_title'        => 'আইডিয়াপত্র',
            'hero_subtitle'     => 'সমকালীন সাহিত্য আলোচনা, প্রবন্ধ, ছোটগল্প, কবিতা, নতুন বইয়ের প্রামাণ্য পর্যালোচনা ও গবেষণামূলক লেখার উন্মুক্ত ডিজিটাল সাময়িকী।',
            'write_button_text' => 'নিজের লেখা পোস্ট করুন',
            'write_button_url'  => '/blog/write',
            'font_family'       => "'Kalpurush', 'Nikosh', 'SolaimanLipi', 'Hind Siliguri', sans-serif",
            'reading_font_size' => '1.08rem',
            'line_height'       => '1.6',
            'poetry_line_height'=> '1.45',
            'poetry_align'      => 'left',
            'paragraph_margin'  => '0.85rem',
            'reading_bg'        => '#ffffff',
            'show_reading_bar'  => '1',
            'enable_share_bar'  => '1',
            'show_author_box'   => '1',
            'header_gradient'   => 'linear-gradient(135deg, #0c4a6e 0%, #0369a1 50%, #0284c7 100%)',
        ];

        $saved = self::get('blog_customizer_settings', []);
        if (is_array($saved)) {
            return array_merge($default, $saved);
        }
        return $default;
    }

    public static function helplineEmail(): string
    {
        $ecom = self::ecommerce();
        return $ecom['helpline_email'] ?? 'ideapbd@gmail.com';
    }

    public static function whatsappNumber(): string
    {
        $ecom = self::ecommerce();
        return $ecom['whatsapp_number'] ?? (string)(self::get('whatsapp_number') ?: self::get('contact_whatsapp') ?: '01726976982');
    }

    public static function contactAddress(): string
    {
        $ecom = self::ecommerce();
        return (string) ($ecom['contact_address'] ?? self::get('contact_address') ?? 'আইডিয়া প্রকাশন, প্রেসক্লাব গলি / সুপার মার্কেট, রংপুর ও ঢাকা, বাংলাদেশ');
    }

    public static function aboutCustomizer(): array
    {
        $default = [
            'hero_badge'        => 'ঐতিহ্য, মনন ও সাংস্কৃতিক প্রত্যয়',
            'page_title'        => 'আইডিয়া প্রকাশন: উত্তরবঙ্গের জ্ঞান, সাহিত্য ও সংস্কৃতি চর্চার নিরন্তর অভিযাত্রা',
            'page_subtitle'     => 'উত্তরবঙ্গের জ্ঞানচর্চা, সৃজনশীল প্রকাশ ও সাংস্কৃতিক আত্মপরিচয় নির্মাণের দুই দশকের অভিযাত্রা। ৪৫০+ প্রকাশিত বই ও ২৭,০০০+ পাঠাগার বই অনুদান।',
            'quote_text'        => 'একটি জনপদের ইতিহাস কেবল তার রাজা-রাজন্য বা রাজনৈতিক উত্থান-পতনের ইতিহাস নয়; তার প্রকৃত পরিচয় নিহিত থাকে মানুষের চিন্তা, মনন, সৃজনশীলতা, ভাষা ও সংস্কৃতির পরম্পরায়।',
            'quote_author'      => 'সাকিল মাসুদ, সিইও ও প্রকাশক',
            'stat_books_count'  => '৪৫০+',
            'stat_books_label'  => 'প্রকাশিত গ্রন্থ সম্ভার',
            'stat_books_sub'    => 'গবেষণা, সাহিত্য, উপন্যাস ও শিশুসাহিত্য',
            'stat_lib_count'    => '২৭,০০০+',
            'stat_lib_label'    => 'বিনামূল্যে বিতরণকৃত বই',
            'stat_lib_sub'      => 'রংপুরের বেসরকারি পাঠাগারগুলোতে বিতরণ',
            'stat_years_count'  => '২ দশক',
            'stat_years_label'  => 'ধারাবাহিক প্রকাশনা অভিযাত্রা',
            'stat_years_sub'    => 'জ্ঞানচর্চা ও সৃজনশীলতার বিকাশ',
            'stat_fair_count'   => '১ দশক',
            'stat_fair_label'   => 'অমর একুশে বইমেলা',
            'stat_fair_sub'     => 'জাতীয় পরিসরে আঞ্চলিক স্বর',
            'publisher_name'    => 'সাকিল মাসুদ',
            'publisher_role'    => 'সিইও ও প্রকাশক',
            'publisher_note'    => 'উত্তরবঙ্গের শিকড়ে প্রোথিত থেকে, বাংলা প্রকাশনার বৃহত্তর পরিসরে নিজস্ব স্বতন্ত্র পরিচয় নির্মাণে আইডিয়া প্রকাশন এগিয়ে চলেছে।',
            'publisher_url'     => '/authors/sakil-masud',
            'statement_p1'      => 'একটি জনপদের ইতিহাস কেবল তার রাজা-রাজন্য, স্থাপত্য কিংবা রাজনৈতিক উত্থান-পতনের ইতিহাস নয়; তার প্রকৃত পরিচয় নিহিত থাকে মানুষের চিন্তা, মনন, সৃজনশীলতা, ভাষা ও সংস্কৃতির পরম্পরায়। বই সেই পরম্পরার অন্যতম প্রধান বাহন, আর প্রকাশনা সেই বাহনের নির্মাতা। মানুষের কাছে পৌঁছে দেয়ার বুদ্ধিবৃত্তিক সেতু বা উদ্যোগ। উত্তরবঙ্গের, বিশেষত রংপুরের সাহিত্য-সংস্কৃতির পরিসরকে বৃহত্তর দৃষ্টিভঙ্গিতে বিবেচনা করলে আইডিয়া প্রকাশনের অভিযাত্রা কেবল প্রকাশনা প্রতিষ্ঠানের বিকাশের ইতিহাস নয়; বরং এই পিছিয়ে থাকা অবহেলিত জনপদের জ্ঞানচর্চা, সৃজনশীল প্রকাশ ও সাংস্কৃতিক আত্মপরিচয় নির্মাণের প্রচেষ্টারও অংশ।',
            'statement_p2'      => 'রংপুরের প্রকাশনা ও সাহিত্যচর্চার ইতিহাসে রঙ্গপুর বার্তাবহ একটি ঐতিহাসিক স্মারক। সেই ঐতিহ্যের উত্তরসূরি হিসেবে উত্তরবঙ্গের সাহিত্য-সংস্কৃতির পরিসর বিস্তৃত করার প্রত্যয়ে আইডিয়া প্রকাশন কাজ করে চলেছে। অতীত অর্জন স্মৃতির বিষয় হিসেবে নয়, বরং বর্তমান ও ভবিষ্যতের সম্ভাবনার ভিত্তি হিসেবে দেখাই এই প্রতিষ্ঠানের মূল দর্শন। কারণ, যে জনপদ তার জ্ঞানগত ঐতিহ্যকে ধারণ করতে পারে না, সে জনপদের ভবিষ্যৎ নির্মাণও অসম্পূর্ণ থেকে যায়।',
            'statement_p3'      => 'প্রায় দুই দশকের অভিযাত্রায় আইডিয়া প্রকাশন বই প্রকাশকে নিছক বাণিজ্যিক কর্মকাণ্ডের মধ্যে সীমাবদ্ধ রাখেনি; বরং সাহিত্য, গবেষণা, ইতিহাস, সংস্কৃতি ও সমাজভাবনার বহুমাত্রিক প্রকাশমাধ্যম হিসেবে নিজেকে বিকশিত করার চেষ্টা করেছে। বর্তমানে প্রতিষ্ঠানটির প্রকাশিত বইয়ের সংখ্যা ৪৫০-এর বেশি। গবেষণাগ্রন্থ, গল্প, উপন্যাস, ছড়া, কবিতা, অনুবাদ এবং শিশুসাহিত্যসহ বিচিত্র বিষয়ে বই প্রকাশের মধ্য দিয়ে এই প্রতিষ্ঠান জ্ঞান ও সৃজনশীলতার বহুমুখী প্রবাহকে ধারণ করেছে। এই বৈচিত্র্য কেবল প্রকাশিত বইয়ের সংখ্যাগত বিস্তার নয়; এটি পাঠ, চিন্তা ও মননের বিভিন্ন ধারাকে একই সাংস্কৃতিক পরিসরে যুক্ত করার প্রয়াস।',
            'statement_p4'      => 'বিশেষত শিশুদের জন্য বই প্রকাশের উদ্যোগ ভবিষ্যৎ পাঠকসমাজ নির্মাণের সঙ্গে গভীরভাবে সম্পর্কিত। একটি জাতির মননশীল ভবিষ্যৎ গড়ে ওঠে শৈশবের পাঠাভ্যাস, কল্পনাশক্তি ও প্রশ্ন করার স্বাধীনতার মধ্য দিয়ে। তাই শিশুসাহিত্য প্রকাশ আইডিয়া প্রকাশনের কাছে কেবল প্রকাশনাসূচির একটি বিভাগ নয়; এটি আগামী দিনের মুক্তবুদ্ধি, মানবিকতা ও সৃজনশীলতার ভিত নির্মাণের অংশ। যে শিশু বইয়ের সঙ্গে বন্ধুত্ব গড়ে তোলে, তার সামনে পৃথিবীকে জানার, বোঝার ও নতুনভাবে আবিষ্কার করার অসংখ্য দরজা খুলে যায়।',
            'statement_p5'      => 'একই সঙ্গে গবেষণা ও সাহিত্যপত্র প্রকাশের মধ্য দিয়ে প্রতিষ্ঠানটি স্থানীয় জ্ঞানচর্চার ধারাবাহিকতা রক্ষায় ভূমিকা রাখছে। গবেষণা অতীতকে অনুসন্ধান করে, সাহিত্য বর্তমানের অনুভূতি ও সংকটকে ভাষা দেয়, আর সাময়িকপত্র ও সাহিত্যপত্র নতুন চিন্তা, বিতর্ক ও সৃজনশীলতার জন্য উন্মুক্ত পরিসর তৈরি করে। আইডিয়া প্রকাশনের উদ্যোগে প্রকাশিত একটি গবেষণা সাময়িকী ও দুটি সাহিত্যপত্র এই বৃহত্তর বুদ্ধিবৃত্তিক চর্চার অংশ। এসব প্রকাশনার মধ্য দিয়ে স্থানীয় ইতিহাস, জনজীবন, সাহিত্যিক অভিজ্ঞতা ও সমকালীন ভাবনার সঙ্গে পাঠকের সংযোগ স্থাপনের সুযোগ তৈরি হয়। একটি জনপদের নিজস্ব জ্ঞানভান্ডার নির্মাণে এ ধরনের উদ্যোগের গুরুত্ব তাই বিশেষভাবে তাৎপর্যপূর্ণ।',
            'statement_p6'      => 'প্রকাশনার সার্থকতা অবশ্য কেবল বই ছাপা ও বিতরণের মধ্যে সীমাবদ্ধ নয়; বইয়ের সঙ্গে মানুষের সম্পর্ক তৈরি করাও এর অন্যতম দায়িত্ব। এই উপলব্ধি থেকেই রংপুরের বেসরকারি পাঠাগারগুলোতে ২৭ হাজারের বেশি বই বিনামূল্যে বিতরণ করা হয়েছে। যা এটি বইকে পাঠকের নাগালে পৌঁছে দেওয়ার পাশাপাশি প্রাতিষ্ঠানিক ও সামাজিক পাঠসংস্কৃতি বিস্তারেরও একটি প্রয়াস। পাঠাগার মানুষের সম্মিলিত জ্ঞানচর্চা, সামাজিক বোঝাপড়া ও মুক্তচিন্তার পরিসর। গণবিশ্ববিদ্যালয় তো বটে।',
            'statement_p7'      => 'আইডিয়া প্রকাশনের আরেকটি উল্লেখযোগ্য অভিযাত্রা অমর একুশে বইমেলাকে কেন্দ্র করে। এক দশক ধরে জাতীয় সাংস্কৃতিক আয়োজনে অংশগ্রহণের মধ্য দিয়ে প্রতিষ্ঠানটি রংপুরের লেখক, গবেষক ও সৃজনশীল মানুষদের প্রকাশিত বই বৃহত্তর পাঠকসমাজের সামনে তুলে ধরার সুযোগ তৈরি করেছে। উত্তরবঙ্গের একটি প্রকাশনা প্রতিষ্ঠানের জন্য রাজধানীকেন্দ্রিক প্রকাশনা ও পাঠপরিসরে ধারাবাহিকভাবে উপস্থিত থাকা শুধু প্রাতিষ্ঠানিক পরিচিতি অর্জনের বিষয় নয়; এটি ভৌগোলিক দূরত্ব অতিক্রম করে সাহিত্যিক ও সাংস্কৃতিক বিনিময়ের ক্ষেত্র সম্প্রসারণেও প্রয়াস। উত্তরবঙ্গের কণ্ঠস্বরকে জাতীয় পরিসরে পৌঁছে দেওয়া এবং জাতীয় সাহিত্যপ্রবাহের সঙ্গে আঞ্চলিক সৃজনশীলতার সংযোগ স্থাপন— এই দুইয়ের মধ্যবর্তী সেতু নির্মাণেই এমন অংশগ্রহণের তাৎপর্য নিহিত। প্রতি বছর যদিও ২-৩ লক্ষ টাকা ভর্তুকি দিয়ে কাজটি পরিচালনা করছে আইডিয়া প্রকাশন।',
            'statement_p8'      => 'তবে অতীত অর্জন কিংবা বর্তমানের বিস্তার প্রতিষ্ঠানের চূড়ান্ত পরিচয় নয়। প্রকৃত পরিচয় নির্ধারিত হয় তার ভবিষ্যৎ ভাবনা, সামাজিক দায়বদ্ধতা ও সময়ের পরিবর্তনকে ধারণ করার ক্ষমতা দিয়ে। আইডিয়া প্রকাশনের সামনে তাই রয়েছে আরও বিস্তৃত দায়িত্ব। উত্তরবঙ্গের ইতিহাস, প্রত্নঐতিহ্য, লোকসংস্কৃতি, ভাষা, জনজীবন ও সামাজিক পরিবর্তন নিয়ে পরিকল্পিত গবেষণা প্রকাশ; নবীন লেখক ও গবেষকদের সৃজনশীল প্রকাশের সুযোগ সৃষ্টি; শিশু-কিশোরদের জন্য মানসম্মত বইয়ের পরিসর বৃদ্ধি; এবং মুদ্রিত বইয়ের পাশাপাশি ই-বুক ও ডিজিটাল পাঠমাধ্যমের সম্প্রসারণ— এসব উদ্যোগ ভবিষ্যৎ অভিযাত্রাকে নতুন মাত্রা দিতে পারে।',
            'statement_p9'      => 'বিশেষভাবে প্রয়োজন উত্তরবঙ্গের নিজস্ব জ্ঞানভান্ডার নির্মাণ। দেশের বিভিন্ন অঞ্চলের ইতিহাস, সাহিত্য ও সংস্কৃতির মতো উত্তরবঙ্গেরও রয়েছে স্বতন্ত্র অভিজ্ঞতা, সামাজিক বাস্তবতা, ঐতিহাসিক স্মৃতি এবং ভবিষ্যৎ সম্ভাবনা। এসব বিষয়কে তথ্যনির্ভর গবেষণা, সৃজনশীল সাহিত্য ও মননশীল প্রকাশনার মাধ্যমে সংরক্ষণ করা জরুরি। স্থানীয় ইতিহাসের উপাদান সংগ্রহ, হারিয়ে যেতে থাকা স্মৃতি ও মৌখিক ইতিহাস লিপিবদ্ধ করা, আঞ্চলিক সাহিত্যকে মূল্যায়ন করা এবং নতুন প্রজন্মের কাছে উত্তরবঙ্গের বহুমাত্রিক পরিচয় তুলে ধরা— এসব কাজের মধ্য দিয়েই একটি প্রকাশনা প্রতিষ্ঠান তার ভৌগোলিক অবস্থানকে অতিক্রম করে বৃহত্তর বুদ্ধিবৃত্তিক ভূমিকা পালন করতে পারে।',
            'statement_p10'     => 'আমাদের বিশ্বাস, প্রকাশনা কেবল লেখক ও পাঠকের মধ্যবর্তী কোনো বাণিজ্যিক সেতু নয়; এটি অতীত ও ভবিষ্যৎ, স্থানীয় অভিজ্ঞতা ও বৈশ্বিক জ্ঞান, ব্যক্তির চিন্তা ও সমাজের সম্মিলিত মননের মধ্যকার জীবন্ত সংযোগ। বই মানুষের চিন্তার স্বাধীনতাকে প্রসারিত করে, প্রতিষ্ঠিত ধারণাকে প্রশ্ন করতে শেখায় এবং নতুন সম্ভাবনার কল্পনা নির্মাণ করে। সেই অর্থে একটি প্রকাশনা প্রতিষ্ঠান একই সঙ্গে সাংস্কৃতিক স্মৃতির সংরক্ষক, সমকালীন চিন্তার বহুভাষিক সহযাত্রী এবং ভবিষ্যৎ নির্মাণের অংশীদার।',
            'statement_p11'     => 'আইডিয়া প্রকাশনের অগ্রযাত্রার মূল প্রত্যয় এখানেই— অতীতকে ধারণ করা, বর্তমানকে গভীরভাবে পাঠ করা এবং ভবিষ্যতের জন্য জ্ঞান ও সৃজনশীলতার নতুন দিগন্ত উন্মোচন করা। রংপুরের মাটি, মানুষের জীবন, ইতিহাস ও সাংস্কৃতিক ঐতিহ্য আমাদের শিকড়; বই, গবেষণা, সাহিত্য ও মুক্তচিন্তা আমাদের কর্মক্ষেত্র; আর একটি মননশীল, পাঠাভ্যাসসম্পন্ন ও সাংস্কৃতিকভাবে সমৃদ্ধ সমাজ নির্মাণ আমাদের অভীষ্ট।',
            'statement_p12'     => 'আমাদের নিজস্ব পাঠাগারে মননপাঠের আসর বসে। নিয়মিত নবীন ও পাঠক এবং লেখকগণ আসা যাওয়া, চর্চার ভেতর থাকেন। আমরা বিশ্বাস করি, একটি বই কেবল তার সময়ের কথা বলে না; অনাগত সময়ের জন্যও চিন্তার বীজ রেখে যায়। তাই আইডিয়া প্রকাশনের পথচলা শুধু প্রকাশিত বইয়ের সংখ্যা বাড়ানোর যাত্রা নয়, বরং পাঠক তৈরি, জ্ঞানচর্চার পরিসর বিস্তার, আঞ্চলিক সৃজনশীলতার মর্যাদা প্রতিষ্ঠা এবং উত্তরবঙ্গের সাংস্কৃতিক ভবিষ্যৎ নির্মাণে অংশগ্রহণের এক অব্যাহত অঙ্গীকার।',
        ];
        $saved = self::get('about_page_settings', []);
        return is_array($saved) ? array_merge($default, $saved) : $default;
    }

    public static function contactCustomizer(): array
    {
        $default = [
            'hero_badge'        => '২৪/৭ কাস্টমার সাপোর্ট ও পাঠক সেবা',
            'hero_title'        => 'যোগাযোগ ও সহায়তা কেন্দ্র',
            'hero_subtitle'     => 'বই অর্ডার, প্রকাশনা সেবা, লেখক পান্ডুলিপি জমা, পাইকারি বুকশপ ডিস্ট্রিবিউশন বা যেকোনো তথ্যের জন্য আমাদের সাথে সরাসরি কথা বলুন বা বার্তা পাঠান।',
            'helpline_phone'    => self::helplinePhone(),
            'whatsapp_number'   => self::whatsappNumber(),
            'helpline_email'    => self::helplineEmail(),
            'contact_address'   => self::contactAddress(),
            'working_hours'     => 'শনিবার – বৃহস্পতিবার: সকাল ৯:০০ টা – রাত ১১:০০ টা',
            'friday_note'       => 'শুক্রবার অনলাইন ও হোয়াটসঅ্যাপ সাপোর্ট সার্বক্ষণিক সচল থাকে',
            'map_url'           => 'https://maps.google.com/?q=' . urlencode(self::contactAddress()),
            'dept_editorial'    => self::helplineEmail(),
            'dept_wholesale'    => self::helplinePhone(),
            'dept_accounts'     => self::helplineEmail(),
        ];
        $saved = self::get('contact_page_settings', []);
        return is_array($saved) ? array_merge($default, $saved) : $default;
    }

    public static function webzineCustomizer(): array
    {
        $default = [
            'hero_badge'        => 'ম্যাগাজিন ও সাময়িকী কালেকশন',
            'hero_title'        => 'ওয়েবজিন ও সাহিত্য সাময়িকী',
            'hero_subtitle'     => 'সাহিত্য, শিল্প-সংস্কৃতি, প্রবন্ধ ও সমকালীন ভাবনার নিয়মিত ও বিশেষ সংখ্যাগুলোর ডিজিটাল সংকলন। অনলাইনে সরাসরি পড়ুন ও সংগ্রহ করুন।',
            'editor_name'       => 'সাকিল মাসুদ',
            'editor_title'      => 'প্রধান সম্পাদক',
            'editorial_note'    => 'শিল্প, সাহিত্য ও মননের উন্মুক্ত পরিসর বিনির্মাণে আমাদের নিয়মিত প্রকাশনা। নতুন চিন্তা ও গবেষণার দ্বার উন্মোচন আমাদের লক্ষ্য।',
            'archive_notice'    => 'পূর্ববর্তী সকল সংখ্যা ও সংকলন আর্কাইভ থেকে যেকোনো সময় অনলাইনে পড়তে পারবেন।',
            'cta_title'         => 'আপনিও কি সাময়িকীতে লিখতে চান?',
            'cta_button_text'   => 'পান্ডুলিপি বা লেখা পাঠান',
            'cta_button_url'    => '/contact',
        ];
        $saved = self::get('webzine_customizer_settings', []);
        return is_array($saved) ? array_merge($default, $saved) : $default;
    }


    public static function resolveImageUrl(?string $path, ?string $fallbackAsset = null): ?string
    {
        if (empty($path)) {
            return $fallbackAsset && file_exists(public_path($fallbackAsset)) ? asset($fallbackAsset) : ($fallbackAsset ? asset($fallbackAsset) : null);
        }

        $clean = trim($path, '"\' ');

        if (str_starts_with($clean, 'http://') || str_starts_with($clean, 'https://')) {
            return $clean;
        }

        if (str_starts_with($clean, 'data:image/')) {
            return $clean;
        }

        $clean = ltrim($clean, '/');

        // 1. Check direct file in public/
        if (file_exists(public_path($clean)) && is_file(public_path($clean))) {
            return asset($clean);
        }

        // 2. Check storage/ prefix or path
        $storageRel = str_starts_with($clean, 'storage/') ? substr($clean, 8) : $clean;

        if (file_exists(public_path('storage/' . $storageRel)) || file_exists(storage_path('app/public/' . $storageRel))) {
            return asset('storage/' . $storageRel);
        }

        // 3. Check alternate extensions (.webp, .png, .jpg, .jpeg, .svg)
        $withoutExt = preg_replace('/\.[^.]+$/', '', $storageRel);
        foreach (['.webp', '.png', '.jpg', '.jpeg', '.svg'] as $ext) {
            if (file_exists(public_path('storage/' . $withoutExt . $ext)) || file_exists(storage_path('app/public/' . $withoutExt . $ext))) {
                return asset('storage/' . $withoutExt . $ext);
            }
            if (file_exists(public_path($withoutExt . $ext))) {
                return asset($withoutExt . $ext);
            }
        }

        // 4. Fallback asset if provided
        if ($fallbackAsset) {
            return asset($fallbackAsset);
        }

        // 5. Default return
        return asset(str_starts_with($clean, 'storage/') ? $clean : 'storage/' . $clean);
    }

    public static function publisherName(): string
    {
        return (string) (self::get('editorial_publisher') ?: 'আইডিয়া প্রকাশন');
    }

    public static function editorName(): string
    {
        return (string) (self::get('editorial_editor') ?: 'সাকিল মাসুদ');
    }

    public static function editorialBoard(): array
    {
        $board = self::get('editorial_board', []);
        return is_array($board) ? $board : [];
    }

    public static function ebookPreviewLimit(): int
    {
        $ebookSettings = self::get('ebook_settings');
        if (is_array($ebookSettings) && isset($ebookSettings['default_preview_pages'])) {
            return max(1, (int)$ebookSettings['default_preview_pages']);
        }
        return 16; // Standard default 16 pages
    }

    public static function defaultHeaderNav(): array
    {
        return [
            ['id' => '1', 'label' => 'হোম', 'route' => 'home', 'url' => '/', 'icon' => 'house', 'active' => 'home', 'is_active' => true, 'target' => '_self', 'badge' => ''],
            ['id' => '2', 'label' => 'বুকশপ', 'route' => 'book.index', 'url' => '/books', 'icon' => 'book', 'active' => 'book.*', 'is_active' => true, 'target' => '_self', 'badge' => ''],
            ['id' => '3', 'label' => 'ই-বুক', 'route' => 'ebook.index', 'url' => '/ebooks', 'icon' => 'tablet-screen-button', 'active' => 'ebook.*', 'is_active' => true, 'target' => '_self', 'badge' => 'নতুন'],
            ['id' => '4', 'label' => 'লেখক', 'route' => 'authors.index', 'url' => '/authors', 'icon' => 'pen-fancy', 'active' => 'authors.*', 'is_active' => true, 'target' => '_self', 'badge' => ''],
            ['id' => '5', 'label' => 'প্রকাশক', 'route' => 'publishers.index', 'url' => '/publishers', 'icon' => 'building', 'active' => 'publishers.*', 'is_active' => true, 'target' => '_self', 'badge' => ''],
            ['id' => '6', 'label' => 'আইডিয়াপত্র', 'route' => 'blog.index', 'url' => '/blog', 'icon' => 'newspaper', 'active' => 'blog.*', 'is_active' => true, 'target' => '_self', 'badge' => ''],
            ['id' => '7', 'label' => 'ওয়েবজিন', 'route' => 'webzine.index', 'url' => '/webzines', 'icon' => 'book-open', 'active' => 'webzine.*', 'is_active' => true, 'target' => '_self', 'badge' => ''],
            ['id' => '8', 'label' => 'গবেষণা', 'route' => 'research.index', 'url' => '/research', 'icon' => 'flask', 'active' => 'research.*', 'is_active' => true, 'target' => '_self', 'badge' => ''],
            ['id' => '9', 'label' => 'আইডিয়া হাব', 'route' => 'hub', 'url' => '/hub', 'icon' => 'compass', 'active' => 'hub', 'is_active' => true, 'target' => '_self', 'badge' => ''],
            ['id' => '10', 'label' => 'আমাদের সম্পর্কে', 'route' => 'about', 'url' => '/about', 'icon' => 'circle-info', 'active' => 'about', 'is_active' => true, 'target' => '_self', 'badge' => ''],
            ['id' => '11', 'label' => 'যোগাযোগ', 'route' => 'contact', 'url' => '/contact', 'icon' => 'envelope', 'active' => 'contact', 'is_active' => true, 'target' => '_self', 'badge' => ''],
        ];
    }

    public static function headerNav(): array
    {
        $saved = self::get('header_menu_items');
        if (is_array($saved) && !empty($saved)) {
            return $saved;
        }
        return self::defaultHeaderNav();
    }

    // --- Terms & Legal Policies Customization Helpers ---
    public static function termsBadge(): string
    {
        return (string) (self::get('terms_badge') ?: 'আইডিয়া প্রকাশন অফিশিয়াল পলিসি ফ্রেমওয়ার্ক');
    }

    public static function termsTitle(): string
    {
        return (string) (self::get('terms_title') ?: 'ব্যবহারের শর্তাবলী ও প্রাতিষ্ঠানিক নীতিমালা');
    }

    public static function termsSubtitle(): string
    {
        return (string) (self::get('terms_subtitle') ?: 'আইডিয়া প্রকাশন (ideaabd.com) প্ল্যাটফর্মের মাধ্যমে বই ও ই-বুক ক্রয়, ডেলিভারি সেবা, পাণ্ডুলিপি জমা, রয়্যালটি বণ্টন ও ডিজিটাল কনটেন্ট ব্যবহারের সুনির্দিষ্ট নিয়মাবলি।');
    }

    public static function termsVersion(): string
    {
        return (string) (self::get('terms_version') ?: 'সেপ্টেম্বর ২০২৬');
    }

    public static function termsReturnDays(): int
    {
        return (int) (self::get('terms_return_days') ?: 7);
    }

    public static function termsRefundTimeline(): string
    {
        return (string) (self::get('terms_refund_timeline') ?: '২৪-৭২ ঘণ্টা');
    }

    public static function termsReturnConditions(): ?string
    {
        return self::get('terms_return_conditions');
    }

    public static function termsReturnExcluded(): ?string
    {
        return self::get('terms_return_excluded');
    }

    public static function termsShippingNote(): ?string
    {
        return self::get('terms_shipping_note');
    }

    public static function termsEbookDrmNote(): ?string
    {
        return self::get('terms_ebook_drm_note');
    }

    public static function termsAuthorRoyaltyNote(): ?string
    {
        return self::get('terms_author_royalty_note');
    }

    public static function termsCustomNotice(): ?string
    {
        return self::get('terms_custom_notice');
    }

    // --- Designer Attribution & Author Profile Customization Helpers ---
    public static function showDesignerCredit(): bool
    {
        return (bool) (self::get('show_designer_credit', true));
    }

    public static function designerAuthorId(): ?int
    {
        $id = self::get('designer_author_id');
        return $id ? (int) $id : null;
    }

    public static function designerName(): string
    {
        $customName = self::get('designer_name');
        if (!empty($customName)) {
            return (string) $customName;
        }

        $authorId = self::designerAuthorId();
        if ($authorId && class_exists(\Modules\Author\Models\Author::class)) {
            $author = \Modules\Author\Models\Author::find($authorId);
            if ($author && !empty($author->name)) {
                return (string) $author->name;
            }
        }

        return 'Masud Rana Shakil';
    }

    public static function designerSlug(): string
    {
        $authorId = self::designerAuthorId();
        if ($authorId && class_exists(\Modules\Author\Models\Author::class)) {
            $author = \Modules\Author\Models\Author::find($authorId);
            if ($author && !empty($author->slug)) {
                return (string) $author->slug;
            }
        }

        $customSlug = self::get('designer_slug');
        if (!empty($customSlug)) {
            return (string) $customSlug;
        }

        return 'sakil-masud';
    }

    public static function designerUrl(): string
    {
        $customUrl = self::get('designer_url');
        if (!empty($customUrl)) {
            return (string) $customUrl;
        }

        $slug = self::designerSlug();
        return \Illuminate\Support\Facades\Route::has('authors.show') 
            ? route('authors.show', $slug) 
            : url('/authors/' . $slug);
    }

    // --- Dynamic Admin & Site Theme Customization Engine ---
    public static function themeSettings(): array
    {
        $theme = self::get('theme_settings', []);
        if (!is_array($theme)) {
            $theme = [];
        }

        return array_merge([
            'primary_color'   => '#0066cc',
            'secondary_color' => '#0099ff',
            'accent_color'    => '#ff6b35',
            'default_mode'    => 'light',
            'sidebar_theme'   => 'theme-deep-navy',
            'font_family'     => 'Kalpurush',
            'border_radius'   => 'rounded-modern',
            'card_style'      => 'elevated',
            'custom_css'      => '',
        ], $theme);
    }

    public static function primaryColor(): string
    {
        return (string) (self::themeSettings()['primary_color'] ?? '#0066cc');
    }

    public static function secondaryColor(): string
    {
        return (string) (self::themeSettings()['secondary_color'] ?? '#0099ff');
    }

    public static function accentColor(): string
    {
        return (string) (self::themeSettings()['accent_color'] ?? '#ff6b35');
    }

    public static function themeMode(): string
    {
        return (string) (self::themeSettings()['default_mode'] ?? 'light');
    }

    public static function sidebarTheme(): string
    {
        return (string) (self::themeSettings()['sidebar_theme'] ?? 'theme-deep-navy');
    }

    public static function fontFamily(): string
    {
        return (string) (self::themeSettings()['font_family'] ?? 'Kalpurush');
    }
}

