<?php

namespace Modules\SEO\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Modules\SEO\Models\SeoMeta;
use App\Support\SiteSetting;

class AutoSeoScannerService
{
    /**
     * Stopwords list in Bengali and English for keyword extraction.
     */
    protected array $banglaStopwords = [
        'এই', 'সেই', 'একটি', 'এবং', 'বা', 'কিন্তু', 'অথবা', 'জন্য', 'থেকে', 'দ্বারা', 'হতে', 'পর্যন্ত', 
        'করা', 'হওয়া', 'আছে', 'ছিল', 'হবে', 'পারে', 'করে', 'হলে', 'নিয়ে', 'সাথে', 'মধ্যে', 'উপর', 
        'নিচে', 'সকল', 'সব', 'প্রতি', 'এক', 'দুই', 'তিন', 'কি', 'কেন', 'কী', 'কেমন', 'কোথায়', 'যা', 'তা',
        'the', 'is', 'at', 'which', 'on', 'and', 'a', 'an', 'in', 'to', 'for', 'of', 'or', 'by', 'with'
    ];

    /**
     * Scan any Model and persist its SeoMeta record.
     */
    public function scanAndSave(Model $model, ?string $customFocusKeyword = null): SeoMeta
    {
        $scanData = $this->scanModel($model, $customFocusKeyword);

        return SeoMeta::updateOrCreate(
            [
                'seoable_type' => get_class($model),
                'seoable_id'   => $model->getKey(),
            ],
            array_merge($scanData, [
                'is_auto_generated' => true,
                'last_scanned_at'   => now(),
            ])
        );
    }

    /**
     * Scan a specific static URL or page path.
     */
    public function scanAndSavePath(string $urlPath, array $customData = []): SeoMeta
    {
        $cleanPath = '/' . ltrim($urlPath, '/');
        $siteName = SiteSetting::name() ?: config('app.name', 'আইডিয়া প্রকাশন');

        $defaultTitle = match ($cleanPath) {
            '/'           => $siteName . ' — বই ও মুক্তচিন্তার ডিজিটাল প্রকাশনা প্ল্যাটফর্ম',
            '/books'      => 'বুকশপ ও সকল বইসমূহ — ' . $siteName,
            '/ebooks'     => 'ই-বুক লাইব্রেরি ও ডিজিটাল বই — ' . $siteName,
            '/authors'    => 'লেখক ডিরেক্টরি ও জীবনবৃত্তান্ত — ' . $siteName,
            '/publishers' => 'প্রকাশক ও প্রকাশনা সংস্থা তালিকা — ' . $siteName,
            '/blog'       => 'আইডিয়াপত্র — সাহিত্য, প্রবন্ধ ও মুক্তচিন্তার ব্লগ — ' . $siteName,
            '/webzines'   => 'ওয়েবজিন ও সাহিত্য সাময়িকী সংকলন — ' . $siteName,
            '/research'   => 'গবেষণা ও সমকালীন গবেষণাপত্র — ' . $siteName,
            '/about'      => 'আমাদের সম্পর্কে — ' . $siteName,
            '/contact'    => 'যোগাযোগ ও হেল্পডেস্ক — ' . $siteName,
            '/terms'      => 'ব্যবহারের শর্তাবলী ও নীতিমালা — ' . $siteName,
            default       => ucfirst(trim($cleanPath, '/')) . ' — ' . $siteName,
        };

        $defaultDesc = match ($cleanPath) {
            '/about'   => SiteSetting::aboutCustomizer()['page_subtitle'] ?? 'উত্তরবঙ্গের জ্ঞানচর্চা, সৃজনশীল প্রকাশ ও সাংস্কৃতিক আত্মপরিচয় নির্মাণের দুই দশকের অভিযাত্রা। ৪৫০+ প্রকাশিত বই ও ২৭,০০০+ পাঠাগার বই অনুদান।',
            '/contact' => SiteSetting::contactCustomizer()['hero_subtitle'] ?? 'আইডিয়া প্রকাশন কাস্টমার কেয়ার, বই অর্ডার, লেখক প্রকাশনা সেবা ও হেল্পলাইন।',
            '/blog'    => 'আইডিয়াপত্র — সমকালীন গল্প, কবিতা, প্রবন্ধ, নতুন বইয়ের প্রামাণ্য পর্যালোচনা ও মুক্তচিন্তার ডিজিটাল সাময়িকী।',
            '/webzines'=> 'সাহিত্য, শিল্প-সংস্কৃতি, প্রবন্ধ ও সমকালীন ভাবনার নিয়মিত ও বিশেষ সংখ্যাগুলোর ডিজিটাল সংকলন।',
            default    => SiteSetting::tagline() ?: 'বই ও মুক্তচিন্তার ডিজিটাল প্রকাশনা',
        };

        $title = $customData['meta_title'] ?? $defaultTitle;
        $desc = $customData['meta_description'] ?? $defaultDesc;
        $keywords = $customData['meta_keywords'] ?? 'আইডিয়া প্রকাশন, বই, ই-বুক, সাহিত্য, রংপুর প্রকাশনা, সাকিল মাসুদ';
        $canonical = url($cleanPath);
        $ogImage = SiteSetting::blogOgBannerUrl() ?: asset('images/og-banner.jpg');

        $analysis = $this->analyzeSeoQuality($title, $desc, $keywords, $cleanPath, $ogImage, $customData['focus_keyword'] ?? null);

        return SeoMeta::updateOrCreate(
            ['url_path' => $cleanPath],
            [
                'meta_title'          => $title,
                'meta_description'    => $desc,
                'meta_keywords'       => $keywords,
                'canonical_url'       => $canonical,
                'robots'              => $customData['robots'] ?? 'index, follow',
                'focus_keyword'       => $customData['focus_keyword'] ?? null,
                'og_title'            => $customData['og_title'] ?? $title,
                'og_description'      => $customData['og_description'] ?? $desc,
                'og_image'            => $customData['og_image'] ?? $ogImage,
                'og_type'             => 'website',
                'twitter_card'        => 'summary_large_image',
                'twitter_title'       => $customData['twitter_title'] ?? $title,
                'twitter_description' => $customData['twitter_description'] ?? $desc,
                'twitter_image'       => $customData['twitter_image'] ?? $ogImage,
                'schema_type'         => 'WebPage',
                'schema_json'         => [
                    '@context'    => 'https://schema.org',
                    '@type'       => 'WebPage',
                    'name'        => $title,
                    'description' => $desc,
                    'url'         => $canonical,
                    'publisher'   => [
                        '@type' => 'Organization',
                        'name'  => $siteName,
                        'url'   => url('/'),
                    ]
                ],
                'seo_score'           => $analysis['score'],
                'seo_analysis'        => $analysis,
                'is_auto_generated'   => false,
                'last_scanned_at'     => now(),
            ]
        );
    }

    /**
     * Inspect and scan any supported model to generate full SEO properties.
     */
    public function scanModel(Model $model, ?string $customFocusKeyword = null): array
    {
        $siteName = SiteSetting::name() ?: config('app.name', 'আইডিয়া প্রকাশন');
        $className = class_basename($model);

        $metaTitle = '';
        $metaDescription = '';
        $metaKeywords = [];
        $canonicalUrl = '';
        $ogImage = '';
        $ogType = 'website';
        $schemaType = 'Thing';
        $schemaJson = [];
        $contentText = '';

        // 1. Scan Model Specific Attributes
        switch ($className) {
            case 'Book':
                $title = trim($model->title ?? '');
                $authorName = $model->author_name ?: ($model->author?->name ?? '');
                $genre = $model->genre_category ?: ($model->category?->name ?? 'বই');
                $price = $model->discount_price > 0 ? $model->discount_price : $model->price;
                
                $metaTitle = $title . ($authorName ? ' — ' . $authorName : '') . ' | ' . $siteName;
                $rawSummary = $model->summary ?: $model->description ?: ($title . ' বইটি লিখেছেন ' . $authorName . '। আইডিয়া প্রকাশন থেকে অনলাইনে সেরা মূল্যে অর্ডার করুন।');
                $metaDescription = $this->cleanExcerpt($rawSummary, 155);
                
                $keywordsList = [$title, $authorName, $genre, 'আইডিয়া প্রকাশন', 'অনলাইন বইমেলা', 'বই কিনুন', $model->isbn, $model->publisher?->name];
                $metaKeywords = array_filter(array_unique($keywordsList));
                
                $canonicalUrl = \Illuminate\Support\Facades\Route::has('book.show') ? route('book.show', $model->slug ?? $model->id) : url('/books/' . ($model->slug ?? $model->id));
                $ogImage = SiteSetting::resolveImageUrl($model->cover_image, 'images/book-placeholder.jpg');
                $ogType = 'book';
                $schemaType = 'Book';
                $contentText = ($model->description ?? '') . ' ' . ($model->summary ?? '');

                $schemaJson = [
                    '@context'    => 'https://schema.org',
                    '@type'       => ['Book', 'Product'],
                    'name'        => $title,
                    'description' => $metaDescription,
                    'image'       => $ogImage,
                    'isbn'        => $model->isbn ?: null,
                    'inLanguage'  => $model->language ?: 'bn',
                    'numberOfPages' => $model->page_count ?: null,
                    'author'      => [
                        '@type' => 'Person',
                        'name'  => $authorName ?: 'আইডিয়া প্রকাশন',
                    ],
                    'publisher'   => [
                        '@type' => 'Organization',
                        'name'  => $model->publisher?->name ?: $siteName,
                    ],
                    'offers'      => [
                        '@type'         => 'Offer',
                        'price'         => (float) $price,
                        'priceCurrency' => 'BDT',
                        'availability'  => ($model->stock_quantity > 0 || $model->stock_status === 'in_stock') ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                        'url'           => $canonicalUrl,
                    ],
                ];
                break;

            case 'BlogPost':
                $title = trim($model->title ?? '');
                $authorName = $model->author?->name ?? 'আইডিয়াপত্র লেখক';
                $categoryName = $model->category?->name ?? 'আইডিয়াপত্র';
                
                $metaTitle = $title . ' — আইডিয়াপত্র | ' . $siteName;
                $rawContent = $model->excerpt ?: $model->content ?: $title;
                $metaDescription = $this->cleanExcerpt($rawContent, 155);
                
                $keywordsList = [$title, $authorName, $categoryName, 'আইডিয়াপত্র', 'মুক্তচিন্তা', 'বাংলা সাহিত্য', 'প্রবন্ধ', 'আইডিয়া প্রকাশন'];
                $metaKeywords = array_filter(array_unique($keywordsList));
                
                $canonicalUrl = \Illuminate\Support\Facades\Route::has('blog.show') ? route('blog.show', $model->slug ?? $model->id) : url('/blog/' . ($model->slug ?? $model->id));
                $ogImage = SiteSetting::resolveImageUrl($model->featured_image, 'images/blog-placeholder.jpg');
                $ogType = 'article';
                $schemaType = 'BlogPosting';
                $contentText = ($model->content ?? '') . ' ' . ($model->excerpt ?? '');

                $schemaJson = [
                    '@context'         => 'https://schema.org',
                    '@type'            => 'BlogPosting',
                    'headline'         => $title,
                    'description'      => $metaDescription,
                    'image'            => $ogImage,
                    'datePublished'    => $model->published_at ? $model->published_at->toIso8601String() : ($model->created_at ? $model->created_at->toIso8601String() : now()->toIso8601String()),
                    'dateModified'     => $model->updated_at ? $model->updated_at->toIso8601String() : now()->toIso8601String(),
                    'mainEntityOfPage' => $canonicalUrl,
                    'author'           => [
                        '@type' => 'Person',
                        'name'  => $authorName,
                    ],
                    'publisher'        => [
                        '@type' => 'Organization',
                        'name'  => $siteName,
                        'logo'  => [
                            '@type' => 'ImageObject',
                            'url'   => SiteSetting::logoUrl() ?: asset('images/logo.svg'),
                        ]
                    ],
                ];
                break;

            case 'Author':
                $name = trim($model->name_bn ?: $model->name ?: '');
                $metaTitle = $name . ' — লেখক পরিচিতি, জীবনী ও প্রকাশিত বইসমূহ | ' . $siteName;
                $bio = $model->bio ?: ($name . ' এর জীবনী ও আইডিয়া প্রকাশনী থেকে প্রকাশিত সকল বই ও প্রকাশনা সামগ্রী।');
                $metaDescription = $this->cleanExcerpt($bio, 155);
                
                $metaKeywords = array_filter(array_unique([$name, $model->name_en, 'লেখক পরিচিতি', 'বাংলা সাহিত্যিক', 'আইডিয়া প্রকাশন লেখক', 'গ্রন্থাবলী']));
                $canonicalUrl = \Illuminate\Support\Facades\Route::has('authors.show') ? route('authors.show', $model->slug ?? $model->id) : url('/authors/' . ($model->slug ?? $model->id));
                $ogImage = SiteSetting::resolveImageUrl($model->avatar, 'images/avatar-placeholder.png');
                $ogType = 'profile';
                $schemaType = 'Person';
                $contentText = $model->bio ?? '';

                $schemaJson = [
                    '@context'    => 'https://schema.org',
                    '@type'       => 'Person',
                    'name'        => $name,
                    'description' => $metaDescription,
                    'image'       => $ogImage,
                    'url'         => $canonicalUrl,
                    'jobTitle'    => 'Author / Writer',
                    'worksFor'    => [
                        '@type' => 'Organization',
                        'name'  => $siteName,
                    ]
                ];
                break;

            case 'Publisher':
                $name = trim($model->name ?? '');
                $metaTitle = $name . ' — প্রকাশক ডিরেক্টরি ও প্রকাশিত গ্রন্থাবলী | ' . $siteName;
                $desc = $model->description ?: ($name . ' এর প্রকাশিত সকল বই, ক্যাটালগ ও প্রকাশনা সামগ্রী আইডিয়া প্রকাশন প্ল্যাটফর্মে।');
                $metaDescription = $this->cleanExcerpt($desc, 155);
                
                $metaKeywords = array_filter(array_unique([$name, 'প্রকাশক', 'প্রকাশনা সংস্থা', 'বইমেলা', 'আইডিয়া প্রকাশন']));
                $canonicalUrl = \Illuminate\Support\Facades\Route::has('publishers.show') ? route('publishers.show', $model->slug ?? $model->id) : url('/publishers/' . ($model->slug ?? $model->id));
                $ogImage = SiteSetting::resolveImageUrl($model->logo, 'images/publisher-placeholder.png');
                $ogType = 'website';
                $schemaType = 'Organization';
                $contentText = $model->description ?? '';

                $schemaJson = [
                    '@context'    => 'https://schema.org',
                    '@type'       => 'Organization',
                    'name'        => $name,
                    'description' => $metaDescription,
                    'image'       => $ogImage,
                    'url'         => $canonicalUrl,
                ];
                break;

            case 'Ebook':
                $title = trim($model->title ?? '');
                $authorName = $model->author?->name ?? ($model->author_name ?? '');
                $metaTitle = $title . ' — ডিজিটাল ই-বুক সংস্করণ | ' . $siteName;
                $rawDesc = $model->description ?: ($title . ' ডিজিটাল ই-বুক। স্মার্টফোন, ট্যাবলেট বা কম্পিউটারে সাথে সাথে পড়ুন।');
                $metaDescription = $this->cleanExcerpt($rawDesc, 155);
                
                $metaKeywords = array_filter(array_unique([$title, $authorName, 'ই-বুক', 'পিডিএফ বই', 'ডিজিটাল রিডিং', 'আইডিয়া প্রকাশন']));
                $canonicalUrl = \Illuminate\Support\Facades\Route::has('ebook.show') ? route('ebook.show', $model->slug ?? $model->id) : url('/ebooks/' . ($model->slug ?? $model->id));
                $ogImage = SiteSetting::resolveImageUrl($model->cover_image, 'images/ebook-placeholder.jpg');
                $ogType = 'book';
                $schemaType = 'EBook';
                $contentText = $model->description ?? '';

                $schemaJson = [
                    '@context'    => 'https://schema.org',
                    '@type'       => ['Book', 'DigitalDocument'],
                    'name'        => $title,
                    'bookFormat'  => 'https://schema.org/EBook',
                    'description' => $metaDescription,
                    'image'       => $ogImage,
                    'url'         => $canonicalUrl,
                    'author'      => [
                        '@type' => 'Person',
                        'name'  => $authorName,
                    ],
                ];
                break;

            case 'Webzine':
                $title = trim($model->title ?? $model->issue_title ?? 'ওয়েবজিন');
                $metaTitle = $title . ' — সাহিত্য ও সংস্কৃতি সাময়িকী | ' . $siteName;
                $rawNote = $model->description ?: $model->editorial_note ?: ($title . ' আইডিয়া প্রকাশনের সাহিত্য সাময়িকীর বিশেষ সংকলন।');
                $metaDescription = $this->cleanExcerpt($rawNote, 155);
                
                $metaKeywords = array_filter(array_unique([$title, 'ওয়েবজিন', 'সাহিত্য সাময়িকী', 'গবেষণা পত্রিকা', 'আইডিয়া প্রকাশন']));
                $canonicalUrl = \Illuminate\Support\Facades\Route::has('webzine.show') ? route('webzine.show', $model->slug ?? $model->id) : url('/webzines/' . ($model->slug ?? $model->id));
                $ogImage = SiteSetting::resolveImageUrl($model->cover_image, 'images/webzine-placeholder.jpg');
                $ogType = 'article';
                $schemaType = 'PublicationIssue';
                $contentText = ($model->editorial_note ?? '') . ' ' . ($model->description ?? '');

                $schemaJson = [
                    '@context'    => 'https://schema.org',
                    '@type'       => 'PublicationIssue',
                    'name'        => $title,
                    'description' => $metaDescription,
                    'image'       => $ogImage,
                    'url'         => $canonicalUrl,
                ];
                break;

            default:
                $title = trim($model->title ?? $model->name ?? $className);
                $metaTitle = $title . ' | ' . $siteName;
                $metaDescription = $this->cleanExcerpt($model->description ?? $model->summary ?? $model->bio ?? $title, 155);
                $metaKeywords = [$title, $siteName, 'আইডিয়া প্রকাশন'];
                $canonicalUrl = url('/' . strtolower(Str::plural($className)) . '/' . ($model->slug ?? $model->id));
                $ogImage = SiteSetting::blogOgBannerUrl() ?: asset('images/og-banner.jpg');
                $schemaType = 'Thing';
                $contentText = (string) ($model->description ?? $model->content ?? '');
                break;
        }

        // 2. Extract Focus Keyword if not provided
        $focusKeyword = $customFocusKeyword ?: ($metaKeywords[0] ?? $title);

        // 3. Dynamic Health Audit & Scoring
        $keywordsStr = implode(', ', $metaKeywords);
        $analysis = $this->analyzeSeoQuality($metaTitle, $metaDescription, $keywordsStr, $contentText, $ogImage, $focusKeyword);

        return [
            'meta_title'          => $metaTitle,
            'meta_description'    => $metaDescription,
            'meta_keywords'       => $keywordsStr,
            'canonical_url'       => $canonicalUrl,
            'robots'              => 'index, follow',
            'focus_keyword'       => $focusKeyword,
            'og_title'            => $metaTitle,
            'og_description'      => $metaDescription,
            'og_image'            => $ogImage,
            'og_type'             => $ogType,
            'twitter_card'        => 'summary_large_image',
            'twitter_title'       => $metaTitle,
            'twitter_description' => $metaDescription,
            'twitter_image'       => $ogImage,
            'schema_type'         => $schemaType,
            'schema_json'         => $schemaJson,
            'seo_score'           => $analysis['score'],
            'seo_analysis'        => $analysis,
        ];
    }

    /**
     * Comprehensive SEO Quality & Health Audit Engine (Calculates 0-100 score + diagnostics)
     */
    public function analyzeSeoQuality(string $title, string $description, string $keywords, string $content, ?string $image, ?string $focusKeyword): array
    {
        $score = 0;
        $passed = [];
        $warnings = [];
        $critical = [];

        // Check 1: Title Length (Recommended 40 - 65 characters)
        $titleLen = mb_strlen($title);
        if ($titleLen >= 30 && $titleLen <= 70) {
            $score += 20;
            $passed[] = "মেটা টাইটেল আদর্শ দৈর্ঘ্য বিশিষ্ট ({$titleLen} অক্ষর, আদর্শ: ৪০-৬৫)।";
        } elseif ($titleLen > 70) {
            $score += 10;
            $warnings[] = "মেটা টাইটেল কিছুটা দীর্ঘ ({$titleLen} অক্ষর)। গুগল সার্চে কিছু অংশ ট্রাঙ্কেট হতে পারে।";
        } else {
            $critical[] = "মেটা টাইটেল অত্যন্ত ছোট ({$titleLen} অক্ষর)। যথাযথ কিওয়ার্ড যোগ করুন।";
        }

        // Check 2: Meta Description Length (Recommended 120 - 160 characters)
        $descLen = mb_strlen($description);
        if ($descLen >= 100 && $descLen <= 170) {
            $score += 25;
            $passed[] = "মেটা বিবরণ আদর্শ পরিমাপের ({$descLen} অক্ষর, আদর্শ: ১২০-১৬০)।";
        } elseif ($descLen > 170) {
            $score += 15;
            $warnings[] = "মেটা বিবরণ ১৬০ অক্ষরের বেশি ({$descLen} অক্ষর)। সার্চ স্নিপেটে শেষাংশ কেটে যেতে পারে।";
        } else {
            $score += 5;
            $critical[] = "মেটা বিবরণ অত্যন্ত সংক্ষিপ্ত ({$descLen} অক্ষর)। বিস্তারিত বর্ণনা লিখুন।";
        }

        // Check 3: Focus Keyword in Title
        if ($focusKeyword) {
            if (mb_stripos($title, $focusKeyword) !== false) {
                $score += 20;
                $passed[] = "ফোকাস কিওয়ার্ড '{$focusKeyword}' সফলভাবে টাইটেলে উপস্থিত।";
            } else {
                $warnings[] = "ফোকাস কিওয়ার্ড '{$focusKeyword}' পেজের মূল টাইটেলে পাওয়া যায়নি।";
            }

            // Check 4: Focus Keyword in Description
            if (mb_stripos($description, $focusKeyword) !== false) {
                $score += 15;
                $passed[] = "ফোকাস কিওয়ার্ড মেটা বর্ণনায় স্বাভাবিকভাবে যুক্ত আছে।";
            } else {
                $warnings[] = "মেটা বর্ণনায় ফোকাস কিওয়ার্ড যুক্ত করলে CTR বৃদ্ধি পাবে।";
            }
        } else {
            $warnings[] = "কোনো নির্দিষ্ট ফোকাস কিওয়ার্ড নির্ধারিত নেই।";
        }

        // Check 5: OG / Social Preview Image
        if (!empty($image) && !str_contains($image, 'placeholder')) {
            $score += 10;
            $passed[] = "উচ্চমানের ওপেনগ্রাফ (OG) সোশ্যাল শেয়ারিং ইমেজ সংযুক্ত আছে।";
        } else {
            $warnings[] = "ডিফল্ট প্লেসহোল্ডার ইমেজ বিদ্যমান। আকর্ষণীয় কাস্টম ব্যানার ব্যবহার করুন।";
        }

        // Check 6: Content Depth & Keywords
        $keywordsCount = count(array_filter(explode(',', $keywords)));
        if ($keywordsCount >= 3) {
            $score += 10;
            $passed[] = "পর্যাপ্ত মেটা কিওয়ার্ড ও ট্যাগ ({$keywordsCount}টি) সমৃদ্ধ।";
        } else {
            $warnings[] = "মেটা কিওয়ার্ডের সংখ্যা কম ({$keywordsCount}টি)। কমপক্ষে ৩-৫টি ট্যাগ যোগ করুন।";
        }

        return [
            'score'        => min(100, $score),
            'title_len'    => $titleLen,
            'desc_len'     => $descLen,
            'passed'       => $passed,
            'warnings'     => $warnings,
            'critical'     => $critical,
            'scanned_at'   => now()->toDateTimeString(),
        ];
    }

    /**
     * Clean and strip HTML / Markdown to create a crisp plain-text excerpt.
     */
    protected function cleanExcerpt(string $rawText, int $limit = 160): string
    {
        $clean = strip_tags($rawText);
        $clean = preg_replace('/(\r\n|\n|\r|\t)/', ' ', $clean);
        $clean = preg_replace('/\s+/', ' ', $clean);
        $clean = trim($clean);

        if (mb_strlen($clean) <= $limit) {
            return $clean;
        }

        $cut = mb_substr($clean, 0, $limit);
        $lastSpace = mb_strrpos($cut, ' ');
        if ($lastSpace !== false && $lastSpace > ($limit - 30)) {
            $cut = mb_substr($cut, 0, $lastSpace);
        }

        return $cut . '...';
    }

    /**
     * Batch Scan all records across all key models.
     */
    public function batchScanAll(): array
    {
        $summary = [
            'books'       => 0,
            'blog_posts'  => 0,
            'authors'     => 0,
            'publishers'  => 0,
            'ebooks'      => 0,
            'webzines'    => 0,
            'pages'       => 0,
            'total'       => 0,
        ];

        // 1. Books
        if (class_exists(\Modules\Book\Models\Book::class)) {
            $books = \Modules\Book\Models\Book::where('is_active', true)->get();
            foreach ($books as $b) {
                $this->scanAndSave($b);
                $summary['books']++;
            }
        }

        // 2. Blog Posts
        if (class_exists(\Modules\Blog\Models\BlogPost::class)) {
            $posts = \Modules\Blog\Models\BlogPost::where('status', 'published')->get();
            foreach ($posts as $p) {
                $this->scanAndSave($p);
                $summary['blog_posts']++;
            }
        }

        // 3. Authors
        if (class_exists(\Modules\Author\Models\Author::class)) {
            $authors = \Modules\Author\Models\Author::where('is_active', true)->get();
            foreach ($authors as $a) {
                $this->scanAndSave($a);
                $summary['authors']++;
            }
        }

        // 4. Publishers
        if (class_exists(\Modules\Publisher\Models\Publisher::class)) {
            $publishers = \Modules\Publisher\Models\Publisher::all();
            foreach ($publishers as $pub) {
                $this->scanAndSave($pub);
                $summary['publishers']++;
            }
        }

        // 5. Ebooks
        if (class_exists(\Modules\Ebook\Models\Ebook::class)) {
            $ebooks = \Modules\Ebook\Models\Ebook::all();
            foreach ($ebooks as $eb) {
                $this->scanAndSave($eb);
                $summary['ebooks']++;
            }
        }

        // 6. Webzines
        if (class_exists(\Modules\Webzine\Models\Webzine::class)) {
            $webzines = \Modules\Webzine\Models\Webzine::all();
            foreach ($webzines as $wz) {
                $this->scanAndSave($wz);
                $summary['webzines']++;
            }
        }

        // 7. Core Static Pages
        $staticPages = ['/', '/books', '/ebooks', '/authors', '/publishers', '/blog', '/webzines', '/research', '/about', '/contact', '/terms'];
        foreach ($staticPages as $path) {
            $this->scanAndSavePath($path);
            $summary['pages']++;
        }

        $summary['total'] = array_sum($summary);

        return $summary;
    }
}
