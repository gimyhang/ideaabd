<?php

namespace Modules\SEO\Helpers;

use Illuminate\Database\Eloquent\Model;
use Modules\SEO\Models\SeoMeta;
use Modules\SEO\Services\AutoSeoScannerService;
use App\Support\SiteSetting;

class SeoHelper
{
    /**
     * Render full HTML SEO meta tags, OpenGraph, Twitter, and Schema JSON-LD.
     */
    public static function render(mixed $target = null): string
    {
        $seo = null;
        $scanner = app(AutoSeoScannerService::class);

        if ($target instanceof Model) {
            $seo = $target->seoMeta ?? $scanner->scanAndSave($target);
        } elseif (is_string($target)) {
            $seo = SeoMeta::where('url_path', $target)->first() ?? $scanner->scanAndSavePath($target);
        } else {
            $currentPath = '/' . ltrim(request()->path(), '/');
            $seo = SeoMeta::where('url_path', $currentPath)->first() ?? $scanner->scanAndSavePath($currentPath);
        }

        if (!$seo) {
            return '';
        }

        $siteName = SiteSetting::name() ?: config('app.name', 'আইডিয়া প্রকাশন');
        $title = htmlspecialchars($seo->meta_title ?: $siteName, ENT_QUOTES, 'UTF-8');
        $desc = htmlspecialchars($seo->meta_description ?: '', ENT_QUOTES, 'UTF-8');
        $keywords = htmlspecialchars($seo->meta_keywords ?: '', ENT_QUOTES, 'UTF-8');
        $canonical = htmlspecialchars($seo->canonical_url ?: url()->current(), ENT_QUOTES, 'UTF-8');
        $robots = htmlspecialchars($seo->robots ?: 'index, follow', ENT_QUOTES, 'UTF-8');
        $ogImage = htmlspecialchars($seo->og_image ?: (SiteSetting::blogOgBannerUrl() ?: asset('images/og-banner.jpg')), ENT_QUOTES, 'UTF-8');
        $ogType = htmlspecialchars($seo->og_type ?: 'website', ENT_QUOTES, 'UTF-8');

        $html = "<!-- Dynamic SEO Meta Tags by Idea Prakashan Engine -->\n";
        $html .= "<meta name=\"description\" content=\"{$desc}\">\n";
        if ($keywords) {
            $html .= "<meta name=\"keywords\" content=\"{$keywords}\">\n";
        }
        $html .= "<meta name=\"robots\" content=\"{$robots}\">\n";
        $html .= "<link rel=\"canonical\" href=\"{$canonical}\">\n";

        // OpenGraph / Facebook
        $html .= "<meta property=\"og:site_name\" content=\"{$siteName}\">\n";
        $html .= "<meta property=\"og:title\" content=\"{$title}\">\n";
        $html .= "<meta property=\"og:description\" content=\"{$desc}\">\n";
        $html .= "<meta property=\"og:url\" content=\"{$canonical}\">\n";
        $html .= "<meta property=\"og:image\" content=\"{$ogImage}\">\n";
        $html .= "<meta property=\"og:type\" content=\"{$ogType}\">\n";

        // Twitter Card
        $html .= "<meta name=\"twitter:card\" content=\"summary_large_image\">\n";
        $html .= "<meta name=\"twitter:title\" content=\"{$title}\">\n";
        $html .= "<meta name=\"twitter:description\" content=\"{$desc}\">\n";
        $html .= "<meta name=\"twitter:image\" content=\"{$ogImage}\">\n";

        // Structured Data (JSON-LD)
        if (!empty($seo->schema_json)) {
            $json = json_encode($seo->schema_json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            $html .= "<script type=\"application/ld+json\">\n{$json}\n</script>\n";
        }

        return $html;
    }
}
