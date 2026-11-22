<?php

namespace Modules\Blog\Services;

use Modules\Blog\Models\BlogPost;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Support\SiteSetting;
use App\Support\BanglaGd;

class BlogPostOgImageService
{
    /**
     * Resolve the absolute HTTPS Open Graph image URL for a blog post.
     * Guarantees a valid, high-resolution thumbnail for EVERY post shared on WhatsApp, Facebook, etc.
     */
    public static function resolveOgImageUrl(BlogPost $post): string
    {
        // 1. If post has a valid explicit featured_image (excluding SVG or data URIs)
        $img = trim((string) $post->featured_image);
        if (!empty($img) && !str_starts_with($img, 'data:') && !str_ends_with(strtolower($img), '.svg')) {
            if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://')) {
                // Ensure external images use HTTPS where possible and clean ampersands
                return preg_replace('/^http:\/\//i', 'https://', $img);
            }

            // Local storage image
            $clean = ltrim($img, '/');
            if (str_starts_with($clean, 'storage/')) {
                $clean = substr($clean, 8);
            }

            if (file_exists(public_path('storage/' . $clean)) || file_exists(storage_path('app/public/' . $clean))) {
                return self::toCanonicalHttpsUrl(asset('storage/' . $clean));
            }
            if (file_exists(public_path($clean))) {
                return self::toCanonicalHttpsUrl(asset($clean));
            }
        }

        // 2. Check if post content has an embedded image
        if (!empty($post->content)) {
            if (preg_match('/<img[^>]+src=[\'"]([^\'"]+)[\'"]/i', $post->content, $match)) {
                $contentImg = trim($match[1]);
                if (!empty($contentImg) && !str_starts_with($contentImg, 'data:') && !str_ends_with(strtolower($contentImg), '.svg')) {
                    if (str_starts_with($contentImg, 'http://') || str_starts_with($contentImg, 'https://')) {
                        return preg_replace('/^http:\/\//i', 'https://', $contentImg);
                    }
                    return self::toCanonicalHttpsUrl(asset(ltrim($contentImg, '/')));
                }
            }
        }

        // 3. Fallback to Dynamic Branded Editorial Photocard (Prothom Alo style card)
        $slug = $post->slug ?: (string) $post->id;
        // ?v changes when the card design changes, so Facebook/WhatsApp fetch the new picture
        return self::toCanonicalHttpsUrl(route('blog.og-image', $slug) . '?v=2');
    }

    /**
     * Ensure a URL is fully qualified with production HTTPS canonical domain
     */
    public static function toCanonicalHttpsUrl(string $url): string
    {
        $canonicalDomain = rtrim(config('app.url', 'https://www.ideaabd.com'), '/');
        if (str_contains($canonicalDomain, 'localhost') || str_contains($canonicalDomain, '127.0.0.1')) {
            $canonicalDomain = 'https://www.ideaabd.com';
        }

        $canonicalDomain = preg_replace('/^http:\/\//i', 'https://', $canonicalDomain);

        if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
            return $canonicalDomain . '/' . ltrim($url, '/');
        }

        // If URL has localhost or 127.0.0.1, swap host with canonicalDomain
        $parsed = parse_url($url);
        $host = $parsed['host'] ?? '';
        if ($host === '127.0.0.1' || $host === 'localhost' || str_contains($host, '127.0.0.1')) {
            $path = $parsed['path'] ?? '/';
            $query = isset($parsed['query']) ? '?' . $parsed['query'] : '';
            return $canonicalDomain . $path . $query;
        }

        return preg_replace('/^http:\/\//i', 'https://', $url);
    }

    /**
     * Generate or serve cached 1200x630 JPEG editorial photocard for WhatsApp / Facebook.
     */
    public static function serve(BlogPost $post)
    {
        $cacheDir = storage_path('app/public/blog/og_cards');
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }

        $postHash = md5('bgd1_' . $post->id . '_' . $post->title . '_' . ($post->author_name ?? '') . '_' . ($post->category?->name ?? '') . '_' . ($post->updated_at ? $post->updated_at->timestamp : '0'));
        $cacheFile = $cacheDir . '/og_' . $post->id . '_' . $postHash . '.jpg';

        if (file_exists($cacheFile) && filesize($cacheFile) > 1000) {
            return response()->file($cacheFile, [
                'Content-Type'  => 'image/jpeg',
                'Cache-Control' => 'public, max-age=2592000, immutable',
            ]);
        }

        // Generate image with GD
        $imageBinary = self::renderPhotocard($post);

        @file_put_contents($cacheFile, $imageBinary);
        // older cards of this post (previous title or design) are no longer linked anywhere
        foreach (glob($cacheDir . '/og_' . $post->id . '_*.jpg') ?: [] as $old) {
            if ($old !== $cacheFile) {
                @unlink($old);
                @unlink(public_path('storage/blog/og_cards/' . basename($old)));
            }
        }

        return response($imageBinary, 200, [
            'Content-Type'   => 'image/jpeg',
            'Content-Length' => strlen($imageBinary),
            'Cache-Control'  => 'public, max-age=2592000, immutable',
        ]);
    }

    /**
     * Render the 1200x630 editorial photocard using GD and Kalpurush font.
     */
    public static function renderPhotocard(BlogPost $post): string
    {
        $w = 1200;
        $h = 630;
        $img = imagecreatetruecolor($w, $h);

        // Colors
        $cBgDark   = imagecolorallocate($img, 15, 23, 42);      // #0f172a (Deep Slate)
        $cBgSubtle = imagecolorallocate($img, 30, 41, 59);     // #1e293b
        $cWhite    = imagecolorallocate($img, 255, 255, 255);
        $cOffWhite = imagecolorallocate($img, 241, 245, 249);  // #f1f5f9
        $cMuted    = imagecolorallocate($img, 203, 213, 225);  // #cbd5e1
        $cGold     = imagecolorallocate($img, 245, 158, 11);   // #f59e0b (Amber 500)
        $cGoldLight= imagecolorallocate($img, 252, 211, 77);   // #fcd34d
        $cCrimson  = imagecolorallocate($img, 185, 28, 28);    // #b91c1c (Category badge)
        $cBorder   = imagecolorallocate($img, 51, 65, 85);     // #334155

        // Fill background with rich editorial gradient
        for ($y = 0; $y < $h; $y++) {
            $ratio = $y / $h;
            $r = (int) (15 + (20 * $ratio));
            $g = (int) (23 + (15 * $ratio));
            $b = (int) (42 + (30 * $ratio));
            $gradColor = imagecolorallocate($img, $r, $g, $b);
            imageline($img, 0, $y, $w, $y, $gradColor);
        }

        // Inner decorative border
        imagerectangle($img, 24, 24, $w - 25, $h - 25, $cBorder);
        imagerectangle($img, 28, 28, $w - 29, $h - 29, $cBorder);

        // Gold corner accents
        $cornerLen = 45;
        imagesetthickness($img, 3);
        // Top-left
        imageline($img, 24, 24, 24 + $cornerLen, 24, $cGold);
        imageline($img, 24, 24, 24, 24 + $cornerLen, $cGold);
        // Top-right
        imageline($img, $w - 25, 24, $w - 25 - $cornerLen, 24, $cGold);
        imageline($img, $w - 25, 24, $w - 25, 24 + $cornerLen, $cGold);
        // Bottom-left
        imageline($img, 24, $h - 25, 24 + $cornerLen, $h - 25, $cGold);
        imageline($img, 24, $h - 25, 24, $h - 25 - $cornerLen, $cGold);
        // Bottom-right
        imageline($img, $w - 25, $h - 25, $w - 25 - $cornerLen, $h - 25, $cGold);
        imageline($img, $w - 25, $h - 25, $w - 25, $h - 25 - $cornerLen, $cGold);
        imagesetthickness($img, 1);

        // GD cannot shape Bengali; BanglaGd draws it with the ANSI font (see App\Support\BanglaGd)
        $hasTtf = BanglaGd::available();

        // Category Badge
        $catName = trim((string) ($post->category?->name ?: 'সাহিত্য ও মুক্তচিন্তা'));
        $badgeX = 70;
        $badgeY = 95;
        $badgeW = $hasTtf ? BanglaGd::width(18, $catName) + 36 : strlen($catName) * 9 + 24;

        // Draw category badge pill
        imagefilledrectangle($img, $badgeX, $badgeY - 32, $badgeX + $badgeW, $badgeY + 8, $cCrimson);
        imagerectangle($img, $badgeX, $badgeY - 32, $badgeX + $badgeW, $badgeY + 8, $cGold);

        if ($hasTtf) {
            BanglaGd::draw($img, 18, $badgeX + 18, $badgeY - 6, $cWhite, $catName);
        } else {
            imagestring($img, 5, $badgeX + 10, $badgeY - 24, $catName, $cWhite);
        }

        // Post Title (max 3 lines)
        $title = trim(preg_replace('/\s+/u', ' ', (string) ($post->title ?? '')) ?? '');
        $title = $title !== '' ? $title : 'আইডিয়াপত্র সাহিত্যকর্ম';
        $titleLines = $hasTtf ? BanglaGd::wrap(38, $title, 1050, 3) : array_slice(explode("\n", wordwrap($title, 55)), 0, 3);

        // Draw Title Lines
        $titleStartY = 200;
        $lineHeight = 65;
        foreach ($titleLines as $idx => $line) {
            $y = $titleStartY + ($idx * $lineHeight);
            if ($hasTtf) {
                BanglaGd::draw($img, 38, 72, $y + 3, $cBgDark, $line);   // drop shadow
                BanglaGd::draw($img, 38, 70, $y, $cWhite, $line);
            } else {
                imagestring($img, 5, 70, $y, $line, $cWhite);
            }
        }

        // Subtitle / Excerpt (up to two lines, above the footer)
        $rawExcerpt = !empty($post->subtitle) ? $post->subtitle : (!empty($post->excerpt) ? $post->excerpt : strip_tags((string) $post->content));
        $cleanExcerpt = trim(html_entity_decode(preg_replace('/\s+/u', ' ', (string) $rawExcerpt) ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $excerptY = $titleStartY + (count($titleLines) * $lineHeight) + 20;
        if ($cleanExcerpt !== '' && $hasTtf && $excerptY < 470) {
            $room = $excerptY + 40 < 470 ? 2 : 1;
            foreach (BanglaGd::wrap(21, Str::limit($cleanExcerpt, 400, ''), 1060, $room) as $i => $line) {
                BanglaGd::draw($img, 21, 70, $excerptY + $i * 38, $cMuted, $line);
            }
        }

        // Divider Line before Footer
        imagesetthickness($img, 2);
        imageline($img, 70, 500, $w - 70, 500, $cBorder);
        imageline($img, 70, 500, 220, 500, $cGold);
        imagesetthickness($img, 1);

        // Author Name (Left)
        $authorName = $post->author_name ?: 'সম্পাদকীয় বিভাগ';
        $authorLabel = 'রচনা: ' . $authorName;
        if ($hasTtf) {
            BanglaGd::draw($img, 24, 70, 560, $cGoldLight, $authorLabel);
        } else {
            imagestring($img, 5, 70, 550, 'Author: ' . $authorName, $cGoldLight);
        }

        // Brand & Domain (Right)
        $brandText = 'আইডিয়া প্রকাশন · ideaabd.com';
        if ($hasTtf) {
            BanglaGd::draw($img, 20, $w - 70 - BanglaGd::width(20, $brandText), 560, $cOffWhite, $brandText);
        } else {
            imagestring($img, 4, $w - 280, 550, 'ideaabd.com', $cOffWhite);
        }

        ob_start();
        imagejpeg($img, null, 92);
        $binary = ob_get_clean();
        imagedestroy($img);

        return $binary ?: '';
    }
}
