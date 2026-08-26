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

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = self::all();
        return $all[$key] ?? $default;
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public static function name(): string
    {
        return (string) (self::get('site_name') ?: config('brand.name', 'আইডিয়া প্রকাশন'));
    }

    public static function tagline(): string
    {
        return (string) (self::get('site_tagline') ?: config('brand.tagline', 'বই ও মুক্তচিন্তার ডিজিটাল প্রকাশনা'));
    }

    public static function logoUrl(): ?string
    {
        $logo = self::get('site_logo') ?: config('brand.logo');
        return self::resolveImageUrl($logo, 'images/logo.svg');
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
        return self::resolveImageUrl($favicon, 'images/favicon.ico');
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
            'font_family'       => "'Hind Siliguri', 'Kalpurush', 'SolaimanLipi', sans-serif",
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
        return $ecom['whatsapp_number'] ?? '01726976982';
    }

    public static function resolveImageUrl(?string $path, ?string $fallbackAsset = null): ?string
    {
        if (empty($path)) {
            return $fallbackAsset && file_exists(public_path($fallbackAsset)) ? asset($fallbackAsset) : null;
        }

        $clean = trim($path, '"\' ');

        if (str_starts_with($clean, 'http://') || str_starts_with($clean, 'https://')) {
            return $clean;
        }

        if (str_starts_with($clean, 'data:image/')) {
            return $clean;
        }

        $clean = ltrim($clean, '/');

        if (str_starts_with($clean, 'storage/')) {
            return asset($clean);
        }

        if (file_exists(public_path($clean))) {
            return asset($clean);
        }

        return asset('storage/' . $clean);
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
            ['id' => '2', 'label' => 'বইসমূহ', 'route' => 'book.index', 'url' => '/books', 'icon' => 'book', 'active' => 'book.*', 'is_active' => true, 'target' => '_self', 'badge' => ''],
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
}
