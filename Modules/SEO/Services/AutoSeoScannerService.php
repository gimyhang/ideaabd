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
    public function scanAndSave(Model $model, ?string $customFocusKeyword = null, bool $force = false): SeoMeta
    {
        $existing = SeoMeta::where('seoable_type', get_class($model))->where('seoable_id', $model->getKey())->first();
        $scanData = $this->scanModel($model, $customFocusKeyword);
        $scanData['url_path'] = self::pathOf($scanData['canonical_url'] ?? '');
        $auto = true;

        // Text an admin wrote by hand is kept (saving the book used to overwrite it); facts that follow the record
        // (link, schema, image when none was chosen) are still refreshed. "Fix" ($force) regenerates everything.
        if ($existing && !$existing->is_auto_generated && !$force) {
            $auto = false;
            foreach (['meta_title', 'meta_description', 'meta_keywords', 'focus_keyword', 'robots', 'og_title', 'og_description', 'twitter_title', 'twitter_description'] as $f) {
                if (filled($existing->{$f})) {
                    $scanData[$f] = $existing->{$f};
                }
            }
            if (filled($existing->og_image) && !str_contains((string) $existing->og_image, 'placeholder')) {
                $scanData['og_image'] = $scanData['twitter_image'] = $existing->og_image;
            }
            $analysis = $this->analyzeSeoQuality($scanData['meta_title'], (string) $scanData['meta_description'], (string) $scanData['meta_keywords'], '', $scanData['og_image'], $scanData['focus_keyword']);
            $scanData['seo_score'] = $analysis['score'];
            $scanData['seo_analysis'] = $analysis;
        }

        return SeoMeta::updateOrCreate(
            [
                'seoable_type' => get_class($model),
                'seoable_id'   => $model->getKey(),
            ],
            array_merge($scanData, [
                'is_auto_generated' => $auto,
                'last_scanned_at'   => now(),
            ])
        );
    }

    /** "/books/rokeya" from "https://www.ideaabd.com/books/rokeya" — the key the site layout looks tags up by. */
    public static function pathOf(string $url): ?string
    {
        if ($url === '') {
            return null;
        }
        $path = parse_url($url, PHP_URL_PATH);

        return '/' . ltrim(rawurldecode((string) $path), '/');
    }

    /**
     * Scan a specific static URL or page path.
     */
    public function scanAndSavePath(string $urlPath, array $customData = []): SeoMeta
    {
        $cleanPath = '/' . ltrim($urlPath, '/');
        $siteName = SiteSetting::name() ?: config('app.name', 'আইডিয়া প্রকাশন');

        // A rescan without new data keeps what an admin wrote for this page.
        $existing = SeoMeta::whereNull('seoable_type')->where('url_path', $cleanPath)->first();
        $adminFields = ['meta_title', 'meta_description', 'meta_keywords', 'focus_keyword', 'robots', 'og_image', 'canonical_url'];
        $byAdmin = array_filter(array_intersect_key($customData, array_flip($adminFields)), 'filled');
        if ($byAdmin === [] && $existing && !$existing->is_auto_generated && !empty($existing->seo_analysis['by_admin']) && empty($customData['force'])) {
            foreach ($adminFields as $f) {
                if (filled($existing->{$f})) {
                    $customData[$f] = $existing->{$f};
                }
            }
            $byAdmin = ['kept' => true];
        }
        $customData = array_filter($customData, 'filled');

        // [focus keyword, title, description] for the site's own pages.
        $tagline = SiteSetting::tagline() ?: 'বই ও মুক্তচিন্তার ডিজিটাল প্রকাশনা';
        [$defaultFocus, $defaultTitle, $defaultDesc] = match ($cleanPath) {
            '/'           => [$siteName, "{$siteName} — বাংলা বই, ই-বুক ও মুক্তচিন্তার প্রকাশনা", "{$siteName} — {$tagline}। বাংলা বই, ই-বুক, গবেষণা ও সাহিত্য সাময়িকী এক জায়গায়; অনলাইনে অর্ডার করুন, সারাদেশে হোম ডেলিভারি।"],
            '/books'      => ['বাংলা বই', "বাংলা বই অনলাইনে কিনুন — বুকশপ | {$siteName}", "বাংলা বই অনলাইনে কিনুন: উপন্যাস, গল্প, কবিতা, প্রবন্ধ, গবেষণা ও শিশুতোষ বইয়ের বিশাল সংগ্রহ। {$siteName} থেকে সারাদেশে হোম ডেলিভারি ও ক্যাশ অন ডেলিভারি।"],
            '/ebooks'     => ['ই-বুক', "ই-বুক লাইব্রেরি ও ডিজিটাল বাংলা বই | {$siteName}", "ই-বুক লাইব্রেরি: জনপ্রিয় বাংলা বইয়ের ডিজিটাল সংস্করণ স্মার্টফোন, ট্যাবলেট বা কম্পিউটারে সাথে সাথে পড়ুন। {$siteName}-এর ই-বুক সংগ্রহ।"],
            '/authors'    => ['লেখক', "লেখক ডিরেক্টরি — জীবনী ও প্রকাশিত বই | {$siteName}", "লেখক ডিরেক্টরি: {$siteName}-এর লেখকদের জীবনী, ছবি ও প্রকাশিত সব বইয়ের তালিকা। প্রিয় লেখকের নতুন বই অনলাইনে অর্ডার করুন।"],
            '/publishers' => ['প্রকাশক', "প্রকাশক ও প্রকাশনা সংস্থার তালিকা | {$siteName}", "প্রকাশক ও প্রকাশনা সংস্থার তালিকা: প্রতিটি প্রকাশকের বই, নতুন প্রকাশনা ও ক্যাটালগ দেখুন এবং {$siteName} থেকে অনলাইনে অর্ডার করুন।"],
            '/blog'       => ['আইডিয়াপত্র', 'আইডিয়াপত্র — সাহিত্য, প্রবন্ধ ও মুক্তচিন্তার ব্লগ', 'আইডিয়াপত্র — সমকালীন গল্প, কবিতা, প্রবন্ধ, নতুন বইয়ের পর্যালোচনা ও মুক্তচিন্তার ডিজিটাল সাময়িকী। নিয়মিত নতুন লেখা পড়ুন ও মতামত দিন।'],
            '/webzines'   => ['ওয়েবজিন', "ওয়েবজিন — সাহিত্য ও সংস্কৃতি সাময়িকী | {$siteName}", 'ওয়েবজিন: সাহিত্য, শিল্প-সংস্কৃতি, প্রবন্ধ ও সমকালীন ভাবনার নিয়মিত ও বিশেষ সংখ্যাগুলোর ডিজিটাল সংকলন। প্রতিটি সংখ্যা অনলাইনে পড়ুন।'],
            '/research'   => ['গবেষণা', "গবেষণা ও সমকালীন গবেষণাপত্র | {$siteName}", "গবেষণা বিভাগ: সাহিত্য, ইতিহাস, সমাজ ও সংস্কৃতি নিয়ে সমকালীন গবেষণাপত্র ও প্রবন্ধ। {$siteName}-এর গবেষণা সংকলন অনলাইনে পড়ুন।"],
            '/about'      => [$siteName, "আমাদের সম্পর্কে — {$siteName}-এর পথচলা", (SiteSetting::aboutCustomizer()['page_subtitle'] ?? '') ?: "{$siteName}: উত্তরবঙ্গের জ্ঞানচর্চা, সৃজনশীল প্রকাশনা ও সাংস্কৃতিক আত্মপরিচয় নির্মাণের দীর্ঘ অভিযাত্রা।"],
            '/contact'    => ['যোগাযোগ', "যোগাযোগ ও হেল্পডেস্ক — {$siteName}", (SiteSetting::contactCustomizer()['hero_subtitle'] ?? '') ?: "যোগাযোগ করুন {$siteName}-এর সাথে: বই অর্ডার, ডেলিভারি, লেখক ও প্রকাশনা সেবা নিয়ে যেকোনো প্রশ্নে হেল্পলাইন, ইমেইল বা হোয়াটসঅ্যাপে।"],
            '/terms'      => ['শর্তাবলী', "ব্যবহারের শর্তাবলী ও নীতিমালা — {$siteName}", "শর্তাবলী ও নীতিমালা: {$siteName}-এ অর্ডার, ডেলিভারি, রিটার্ন, রিফান্ড, ই-বুক ব্যবহার ও লেখক রয়্যালটি সংক্রান্ত নিয়মাবলি বিস্তারিত জানুন।"],
            default       => (function () use ($cleanPath, $siteName, $tagline) {
                $name = Str::of(trim($cleanPath, '/'))->afterLast('/')->replace(['-', '_'], ' ')->title()->value();

                return [$name, "{$name} | {$siteName}", "{$name} — {$siteName}। {$tagline}।"];
            })(),
        };

        $title = $customData['meta_title'] ?? $defaultTitle;
        $focusKeyword = $customData['focus_keyword'] ?? $defaultFocus;
        // Long site names can push a default title past what search results show.
        if (!isset($customData['meta_title']) && mb_strlen($title) > self::TITLE_MAX) {
            $title = $this->fitTitle($defaultFocus, null, '', $siteName)[0];
        }
        $desc = $customData['meta_description']
            ?? $this->fitDescription($defaultDesc, $focusKeyword, [], "{$siteName} — {$tagline}।");
        $keywords = $customData['meta_keywords'] ?? implode(', ', array_unique([$focusKeyword, $siteName, 'বাংলা বই', 'ই-বুক', 'সাহিত্য', 'অনলাইন বই অর্ডার']));
        $canonical = $customData['canonical_url'] ?? url($cleanPath);
        $ogImage = $customData['og_image'] ?? self::siteShareImage();

        $analysis = $this->analyzeSeoQuality($title, $desc, $keywords, $cleanPath, $ogImage, $focusKeyword);
        $analysis['by_admin'] = $byAdmin !== [];

        return SeoMeta::updateOrCreate(
            ['url_path' => $cleanPath],
            [
                'meta_title'          => $title,
                'meta_description'    => $desc,
                'meta_keywords'       => $keywords,
                'canonical_url'       => $canonical,
                'robots'              => $customData['robots'] ?? 'index, follow',
                'focus_keyword'       => $focusKeyword,
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
                'is_auto_generated'   => $byAdmin === [],
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
                $genre = \Modules\Book\Models\Genre::nameFor($model->genre_category, 'bn') ?: ($model->category?->name ?? 'বই');
                $price = $model->discount_price > 0 ? $model->discount_price : $model->price;
                
                $metaTitle = $title . ($authorName ? ' — ' . $authorName : '') . ' | ' . $siteName;
                $rawSummary = $model->summary ?: $model->description ?: ($title . ($authorName ? ' বইটি লিখেছেন ' . $authorName . '।' : ' — ' . $siteName . '-এর বই।'));
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
                    '@type'       => 'Book',
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
                $ogImage = \Modules\Blog\Services\BlogPostOgImageService::resolveOgImageUrl($model);
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
                    '@type'       => 'Book',
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

            case 'ResearchPaper':
                $title = trim($model->title ?? '');
                $authorName = $model->author?->name ?? ($model->author_name ?: 'আইডিয়া গবেষক');
                $metaTitle = $title . ' — ' . $authorName . ' | আইডিয়া গবেষণা';
                $abstract = $model->abstract ?: $model->content ?: $title;
                $metaDescription = $this->cleanExcerpt($abstract, 155);

                $keywordsList = [$title, $authorName, 'গবেষণাপত্র', 'রিসার্চ পেপার', 'সাহিত্য গবেষণা', 'আইডিয়া প্রকাশন'];
                $metaKeywords = array_filter(array_unique($keywordsList));

                $canonicalUrl = \Illuminate\Support\Facades\Route::has('research.show') ? route('research.show', $model->slug ?? $model->id) : url('/research/' . ($model->slug ?? $model->id));
                $ogImage = SiteSetting::blogOgBannerUrl() ?: asset('images/og-banner.jpg');
                $ogType = 'article';
                $schemaType = 'ScholarlyArticle';
                $contentText = ($model->abstract ?? '') . ' ' . ($model->content ?? '');

                $schemaJson = [
                    '@context'    => 'https://schema.org',
                    '@type'       => 'ScholarlyArticle',
                    'headline'    => $title,
                    'name'        => $title,
                    'description' => $metaDescription,
                    'url'         => $canonicalUrl,
                    'author'      => [
                        '@type' => 'Person',
                        'name'  => $authorName,
                    ],
                    'publisher'   => [
                        '@type' => 'Organization',
                        'name'  => $siteName,
                        'url'   => url('/'),
                    ],
                    'inLanguage'  => 'bn',
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

        // 2. Fit the tags to what search engines show: a 30–65 character title that starts with the page's own
        //    name, a 120–160 character description built from the record's real facts, and a real share image.
        $core = trim((string) ($title ?? $name ?? ''));
        [$suffix, $typePhrase, $facts, $cta] = $this->factsFor($className, $model, $siteName);
        [$metaTitle, $coreInTitle] = $this->fitTitle($core, $suffix, $typePhrase, $siteName);
        $focusKeyword = $customFocusKeyword ?: $coreInTitle;
        $metaDescription = $this->fitDescription($metaDescription, $focusKeyword, $facts, $cta);
        if ($ogImage === '' || str_contains($ogImage, 'placeholder') || str_ends_with(strtolower($ogImage), '.svg')) {
            $ogImage = $this->shareImageFor($className, $model);
        }
        if (isset($schemaJson['description'])) {
            $schemaJson['description'] = $metaDescription;
        }
        if (isset($schemaJson['image'])) {
            $schemaJson['image'] = $ogImage;
        }
        $metaKeywords = array_values(array_unique(array_filter(array_merge([$focusKeyword], $metaKeywords), fn ($k) => trim((string) $k) !== '')));

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
     * SEO audit: six checks worth 100 points in total. Returns the score, a structured list of checks
     * (for the editor and the dashboard) and the same results as passed / warnings / critical messages.
     */
    public function analyzeSeoQuality(string $title, string $description, string $keywords, string $content, ?string $image, ?string $focusKeyword): array
    {
        $titleLen = mb_strlen(trim($title));
        $descLen = mb_strlen(trim($description));
        $focusKeyword = trim((string) $focusKeyword);
        $keywordsCount = count(array_filter(array_map('trim', explode(',', $keywords))));
        $hasImage = !empty($image) && !str_contains((string) $image, 'placeholder');
        $inTitle = $focusKeyword !== '' && mb_stripos($title, $focusKeyword) !== false;
        $inDesc = $focusKeyword !== '' && mb_stripos($description, $focusKeyword) !== false;

        $checks = [];
        $add = function (string $key, string $label, int $earned, int $max, string $hint) use (&$checks) {
            $checks[] = [
                'key' => $key, 'label' => $label, 'earned' => $earned, 'max' => $max, 'hint' => $hint,
                'status' => $earned >= $max ? 'pass' : ($earned > 0 ? 'warn' : 'fail'),
            ];
        };

        // 1. Title length (20)
        if ($titleLen >= 30 && $titleLen <= 70) {
            $add('title', 'Title length', 20, 20, "টাইটেল {$titleLen} অক্ষর — আদর্শ (৩০–৬৫)।");
        } elseif ($titleLen > 70) {
            $add('title', 'Title length', 10, 20, "টাইটেল {$titleLen} অক্ষর — দীর্ঘ; সার্চে শেষাংশ কেটে যাবে। ৬৫-এর মধ্যে আনুন।");
        } else {
            $add('title', 'Title length', 0, 20, "টাইটেল মাত্র {$titleLen} অক্ষর — অন্তত ৩০ অক্ষর লিখুন।");
        }

        // 2. Description length (25)
        if ($descLen >= 100 && $descLen <= 170) {
            $add('description', 'Description length', 25, 25, "বিবরণ {$descLen} অক্ষর — আদর্শ (১২০–১৬০)।");
        } elseif ($descLen > 170) {
            $add('description', 'Description length', 15, 25, "বিবরণ {$descLen} অক্ষর — দীর্ঘ; ১৬০-এর মধ্যে আনুন।");
        } else {
            $add('description', 'Description length', $descLen > 0 ? 5 : 0, 25, $descLen > 0 ? "বিবরণ মাত্র {$descLen} অক্ষর — ১২০–১৬০ অক্ষর লিখুন।" : 'বিবরণ নেই।');
        }

        // 3–4. Focus keyword in title (20) and description (15)
        if ($focusKeyword === '') {
            $add('keyword_title', 'Keyword in title', 0, 20, 'ফোকাস কিওয়ার্ড দেওয়া নেই।');
            $add('keyword_desc', 'Keyword in description', 0, 15, 'ফোকাস কিওয়ার্ড দেওয়া নেই।');
        } else {
            $add('keyword_title', 'Keyword in title', $inTitle ? 20 : 0, 20, $inTitle ? "“{$focusKeyword}” টাইটেলে আছে।" : "“{$focusKeyword}” টাইটেলে নেই — টাইটেলের শুরুতে রাখুন।");
            $add('keyword_desc', 'Keyword in description', $inDesc ? 15 : 0, 15, $inDesc ? "“{$focusKeyword}” বিবরণে আছে।" : "“{$focusKeyword}” বিবরণে নেই — প্রথম বাক্যে রাখুন।");
        }

        // 5. Share image (10)
        $add('image', 'Share image', $hasImage ? 10 : 0, 10, $hasImage ? 'শেয়ার ছবি আছে।' : 'নিজস্ব শেয়ার ছবি নেই (প্লেসহোল্ডার)।');

        // 6. Keywords (10)
        $add('keywords', 'Keywords', $keywordsCount >= 3 ? 10 : 0, 10, $keywordsCount >= 3 ? "{$keywordsCount}টি কিওয়ার্ড।" : "মাত্র {$keywordsCount}টি কিওয়ার্ড — অন্তত ৩টি দিন।");

        $score = min(100, array_sum(array_column($checks, 'earned')));
        $passed = $warnings = $critical = [];
        foreach ($checks as $c) {
            if ($c['status'] === 'pass') {
                $passed[] = $c['hint'];
            } elseif ($c['status'] === 'warn' || in_array($c['key'], ['keyword_title', 'keyword_desc', 'image', 'keywords'], true)) {
                $warnings[] = $c['hint'];
            } else {
                $critical[] = $c['hint'];
            }
        }

        return [
            'score'      => $score,
            'title_len'  => $titleLen,
            'desc_len'   => $descLen,
            'checks'     => $checks,
            'passed'     => $passed,
            'warnings'   => $warnings,
            'critical'   => $critical,
            'scanned_at' => now()->toDateTimeString(),
        ];
    }

    // ── Fitting tags to search results ──────────────────────

    public const TITLE_MIN = 30;
    public const TITLE_MAX = 65;
    public const DESC_MIN = 120;
    public const DESC_MAX = 160;

    /**
     * What a record can say about itself.
     *
     * @return array{0: ?string, 1: string, 2: string[], 3: string} [title suffix, type phrase, fact sentences, closing line]
     */
    protected function factsFor(string $className, Model $m, string $siteName): array
    {
        $bn = fn ($n) => strtr((string) $n, ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯']);
        $g = fn ($attr) => trim((string) ($m->getAttribute($attr) ?? ''));

        switch ($className) {
            case 'Book':
                $author = $g('author_name') ?: (string) ($m->author?->name ?? '');
                $genre = '';
                try {
                    $genre = \Modules\Book\Models\Genre::nameFor($m->genre_category, 'bn') ?: (string) ($m->category?->name ?? '');
                } catch (\Throwable) {
                }
                $price = (float) ($m->discount_price > 0 ? $m->discount_price : $m->price);
                $facts = array_filter([
                    $author ? "লেখক: {$author}।" : null,
                    $genre ? "বিষয়: {$genre}।" : null,
                    ($m->publisher?->name ?? null) ? 'প্রকাশক: ' . $m->publisher->name . '।' : null,
                    $m->page_count ? 'পৃষ্ঠা: ' . $bn($m->page_count) . '।' : null,
                    $price > 0 ? 'মূল্য ৳' . $bn((int) round($price)) . '।' : null,
                ]);

                return [$author ?: null, 'বই', $facts, "{$siteName} থেকে অনলাইনে অর্ডার করুন, সারাদেশে হোম ডেলিভারি ও ক্যাশ অন ডেলিভারি।"];

            case 'BlogPost':
                $author = (string) ($m->author?->name ?? '');
                $cat = (string) ($m->category?->name ?? '');

                return ['আইডিয়াপত্র', 'লেখা', array_filter([$author ? "লিখেছেন {$author}।" : null, $cat ? "বিভাগ: {$cat}।" : null]),
                    "আইডিয়াপত্রে পড়ুন সমকালীন প্রবন্ধ, গল্প, কবিতা ও মুক্তচিন্তার লেখা — {$siteName}।"];

            case 'Author':
                $count = 0;
                try {
                    $count = method_exists($m, 'books') ? (int) $m->books()->count() : 0;
                } catch (\Throwable) {
                }

                return ['লেখক পরিচিতি ও বইসমূহ', 'লেখক', array_filter([$count ? "প্রকাশিত বই: {$bn($count)}টি।" : null]),
                    "লেখকের জীবনী, প্রকাশিত সব বই ও নতুন প্রকাশনার খবর পাবেন {$siteName}-এ। পছন্দের বই অনলাইনে অর্ডার করুন।"];

            case 'Publisher':
                return ['প্রকাশনা সংস্থা', 'প্রকাশক', [],
                    "প্রকাশনা সংস্থার সব বই, নতুন প্রকাশনা ও ক্যাটালগ দেখুন {$siteName}-এ এবং অনলাইনে অর্ডার করুন, সারাদেশে হোম ডেলিভারি।"];

            case 'Ebook':
                return [(string) ($m->author?->name ?? $g('author_name')) ?: null, 'ই-বুক', [],
                    "ডিজিটাল ই-বুক সংস্করণ — স্মার্টফোন, ট্যাবলেট বা কম্পিউটারে সাথে সাথে পড়ুন। {$siteName}।"];

            case 'Webzine':
                return ['ওয়েবজিন', 'সাময়িকী', [],
                    "সাহিত্য, শিল্প-সংস্কৃতি ও সমকালীন ভাবনার ডিজিটাল সাময়িকী — {$siteName}-এর ওয়েবজিনে পড়ুন।"];

            case 'ResearchPaper':
                return ['আইডিয়া গবেষণা', 'গবেষণাপত্র', [],
                    "সমকালীন সাহিত্য ও সমাজ নিয়ে গবেষণাপত্র ও প্রবন্ধ পড়ুন {$siteName}-এর গবেষণা বিভাগে।"];

            default:
                return [null, '', [], "বিস্তারিত জানুন {$siteName}-এ।"];
        }
    }

    /**
     * A title of TITLE_MIN–TITLE_MAX characters that starts with the page's own name.
     *
     * @return array{0: string, 1: string} [title, the part of the name that is in it (the focus keyword)]
     */
    public function fitTitle(string $core, ?string $suffix, string $typePhrase, string $siteName): array
    {
        $core = trim(preg_replace('/\s+/u', ' ', $core));
        if ($core === '') {
            return [$siteName, $siteName];
        }
        // A name longer than a title can be: cut it at a word.
        if (mb_strlen($core) > self::TITLE_MAX) {
            $core = $this->cutAtWord($core, self::TITLE_MAX - 1);
        }
        $candidates = array_values(array_unique(array_filter([
            $suffix ? "{$core} — {$suffix} | {$siteName}" : null,
            $suffix ? "{$core} — {$suffix}" : null,
            $typePhrase ? "{$core} — {$typePhrase} | {$siteName}" : null,
            "{$core} | {$siteName}",
            $typePhrase ? "{$core} {$typePhrase} — অনলাইনে পড়ুন ও কিনুন | {$siteName}" : null,
            "{$core} — {$siteName}-এর বিশেষ আয়োজন",
            $core,
        ])));
        foreach ($candidates as $t) {
            $len = mb_strlen($t);
            if ($len >= self::TITLE_MIN && $len <= self::TITLE_MAX) {
                return [$t, $core];
            }
        }
        // Nothing in range: the longest one that still fits.
        $fits = array_filter($candidates, fn ($t) => mb_strlen($t) <= self::TITLE_MAX);
        usort($fits, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        return [$fits[0] ?? $core, $core];
    }

    /** A DESC_MIN–DESC_MAX character description that names the focus keyword early. */
    public function fitDescription(string $text, ?string $focus, array $facts, string $cta): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8'))));
        // (preg with /u, never rtrim(): rtrim cuts bytes and broke Bangla letters at the end)
        $text = preg_replace('/[\s.…]+$/u', '', $text);
        $focus = trim((string) $focus);

        // The keyword must be there, and early (a long summary is cut at DESC_MAX).
        if ($focus !== '') {
            $at = mb_stripos($text, $focus);
            if ($at === false || $at + mb_strlen($focus) > self::DESC_MAX - 20) {
                $text = $text === '' ? $focus : $focus . ' — ' . $text;
            }
        }
        if ($text !== '' && !preg_match('/[।.!?]$/u', $text)) {
            $text .= '।';
        }
        foreach (array_merge($facts, [$cta]) as $add) {
            if (mb_strlen($text) >= self::DESC_MIN) {
                break;
            }
            // skip a fact the text already states ("লেখক: X" when X is already named)
            $value = trim(preg_replace(['/^[^:]+:\s*/u', '/[।\s]+$/u'], '', (string) $add));
            if ($add !== '' && ($value === '' || mb_stripos($text, $value) === false)) {
                $text = trim($text . ' ' . $add);
            }
        }
        if (mb_strlen($text) < self::DESC_MIN) {
            $text .= ' বিস্তারিত তথ্য, পাঠকের মতামত ও নতুন প্রকাশনার খবর পেতে নিয়মিত চোখ রাখুন।';
        }
        if (mb_strlen($text) > self::DESC_MAX) {
            $text = $this->cutAtWord($text, self::DESC_MAX - 1) . '…';
        }

        return $text;
    }

    /** The landscape share card for books with a stored cover, otherwise the site's own share image. */
    protected function shareImageFor(string $className, Model $m): string
    {
        if ($className === 'Book') {
            try {
                if (class_exists(\Modules\Book\Services\ShareCardService::class) && \Illuminate\Support\Facades\Route::has('book.share-card')
                    && app(\Modules\Book\Services\ShareCardService::class)->coverPath($m)) {
                    return route('book.share-card', $m->slug ?: $m->id);
                }
            } catch (\Throwable) {
            }
        }

        return self::siteShareImage();
    }

    /** The share image set in Settings › Brand (what the layout uses when a page has none). */
    public static function siteShareImage(): string
    {
        return SiteSetting::blogOgBannerUrl() ?: asset('images/og-banner.jpg');
    }

    private function cutAtWord(string $s, int $max): string
    {
        if (mb_strlen($s) <= $max) {
            return $s;
        }
        $cut = mb_substr($s, 0, $max);
        $sp = mb_strrpos($cut, ' ');
        if ($sp !== false && $sp > $max * 0.6) {
            $cut = mb_substr($cut, 0, $sp);
        }

        // preg with /u, never rtrim(): rtrim cuts bytes and broke Bangla letters at the end
        return preg_replace('/[\s,;:—|।-]+$/u', '', $cut);
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

    // ── Scanning everything, a slice at a time ──────────────

    /** Static pages that always get tags. */
    public const STATIC_PAGES = ['/', '/books', '/ebooks', '/authors', '/publishers', '/blog', '/webzines', '/research', '/about', '/contact', '/terms'];

    /**
     * What a full scan covers, in order: kind => [label, model class, "public" scope]. Only public records get tags;
     * the rest are removed in the final "cleanup" step (a hidden book has no page to rank).
     */
    public function scanKinds(): array
    {
        $kinds = [
            'books'      => ['Books', \Modules\Book\Models\Book::class, fn ($q) => $q->where('is_active', true)],
            'blog'       => ['Posts', \Modules\Blog\Models\BlogPost::class, fn ($q) => $q->where('status', 'published')],
            'authors'    => ['Authors', \Modules\Author\Models\Author::class, fn ($q) => $q->where('is_active', true)],
            'publishers' => ['Publishers', \Modules\Publisher\Models\Publisher::class, null],
            'ebooks'     => ['Ebooks', \Modules\Ebook\Models\Ebook::class, null],
            'webzines'   => ['Webzines', \Modules\Webzine\Models\Webzine::class, null],
            'research'   => ['Research', \Modules\Research\Models\ResearchPaper::class, fn ($q) => $q->where('is_published', true)],
        ];

        return array_filter($kinds, function ($k) {
            try {
                return class_exists($k[1]) && \App\Support\SchemaCache::has((new $k[1]())->getTable());
            } catch (\Throwable) {
                return false;
            }
        });
    }

    /** @return array<string, int> records each step will go through */
    public function scanTotals(): array
    {
        $out = [];
        foreach ($this->scanKinds() as $key => [, $class, $scope]) {
            $q = $class::query();
            if ($scope) {
                $scope($q);
            }
            $out[$key] = (int) $q->count();
        }
        $out['pages'] = count(self::STATIC_PAGES);

        return $out;
    }

    /**
     * One slice of a full scan: records of $kind with id > $after, for up to $seconds.
     *
     * @return array{kind: string, after: int, scanned: int, done: bool, next: ?string, removed?: int}
     */
    public function scanStep(string $kind, int $after = 0, float $seconds = 8.0, bool $force = false): array
    {
        $order = array_merge(array_keys($this->scanKinds()), ['pages', 'cleanup']);
        $pos = array_search($kind, $order, true);
        if ($pos === false) {
            throw new \InvalidArgumentException('Unknown scan step.');
        }
        $next = $order[$pos + 1] ?? null;
        $until = microtime(true) + $seconds;
        $scanned = 0;

        if ($kind === 'pages') {
            foreach (self::STATIC_PAGES as $path) {
                $this->scanAndSavePath($path, $force ? ['force' => true] : []);
                $scanned++;
            }

            return ['kind' => $kind, 'after' => 0, 'scanned' => $scanned, 'done' => true, 'next' => $next];
        }

        if ($kind === 'cleanup') {
            return ['kind' => $kind, 'after' => 0, 'scanned' => 0, 'done' => true, 'next' => null, 'removed' => $this->removeStale()];
        }

        [, $class, $scope] = $this->scanKinds()[$kind];
        $q = $class::query()->where((new $class())->getKeyName(), '>', $after)->orderBy((new $class())->getKeyName());
        if ($scope) {
            $scope($q);
        }
        $last = $after;
        foreach ($q->limit(200)->cursor() as $model) {
            try {
                $this->scanAndSave($model, null, $force);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("SEO scan skipped {$kind} #{$model->getKey()}: " . $e->getMessage());
            }
            $last = (int) $model->getKey();
            $scanned++;
            if (microtime(true) >= $until) {
                break;
            }
        }
        $done = $scanned === 0 || !(clone $q)->where((new $class())->getKeyName(), '>', $last)->exists();

        return ['kind' => $kind, 'after' => $done ? 0 : $last, 'scanned' => $scanned, 'done' => $done, 'next' => $done ? $next : $kind];
    }

    /** Tags of records that are gone or no longer public (they only pulled the average down). */
    public function removeStale(): int
    {
        $removed = 0;
        $public = [];
        foreach ($this->scanKinds() as [, $class, $scope]) {
            $public[$class] = $scope;
        }
        SeoMeta::whereNotNull('seoable_type')->select(['id', 'seoable_type', 'seoable_id'])->chunkById(500, function ($rows) use (&$removed, $public) {
            foreach ($rows->groupBy('seoable_type') as $type => $group) {
                if (!class_exists($type)) {
                    $removed += SeoMeta::whereIn('id', $group->pluck('id'))->delete();
                    continue;
                }
                $q = $type::query()->whereIn((new $type())->getKeyName(), $group->pluck('seoable_id'));
                if (!empty($public[$type])) {
                    ($public[$type])($q);
                }
                $alive = $q->pluck((new $type())->getKeyName())->map(fn ($v) => (string) $v)->flip();
                $dead = $group->filter(fn ($m) => !isset($alive[(string) $m->seoable_id]))->pluck('id');
                if ($dead->isNotEmpty()) {
                    $removed += SeoMeta::whereIn('id', $dead)->delete();
                }
            }
        });

        return $removed;
    }

    /**
     * Batch Scan all records across all key models (command line; the admin page runs scanStep() slice by slice).
     */
    public function batchScanAll(bool $force = false): array
    {
        $summary = array_fill_keys(array_merge(array_keys($this->scanKinds()), ['pages']), 0) + ['removed' => 0];
        $kind = array_key_first($summary);
        $after = 0;
        while ($kind) {
            $r = $this->scanStep($kind, $after, 60.0, $force);
            if (isset($summary[$kind]) && $kind !== 'removed') {
                $summary[$kind] += $r['scanned'];
            }
            $summary['removed'] += $r['removed'] ?? 0;
            [$kind, $after] = [$r['next'], $r['after']];
        }
        // older callers read these names
        $summary['blog_posts'] = $summary['blog'] ?? 0;
        $summary['total'] = array_sum(array_intersect_key($summary, array_flip(array_merge(array_keys($this->scanKinds()), ['pages']))));

        return $summary;
    }
}
