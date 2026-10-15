<?php

namespace Modules\Blog\Services;

use Modules\Blog\Models\BlogPost;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Support\SiteSetting;

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
        return self::toCanonicalHttpsUrl(route('blog.og-image', $slug));
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

        $postHash = md5($post->id . '_' . $post->title . '_' . ($post->author_name ?? '') . '_' . ($post->category?->name ?? '') . '_' . ($post->updated_at ? $post->updated_at->timestamp : '0'));
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

        $fontPath = public_path('fonts/kalpurush/kalpurush.ttf');
        $hasTtf = file_exists($fontPath);

        // Category Badge
        $catName = $post->category?->name ?: 'সাহিত্য ও মুক্তচিন্তা';
        $badgeText = '  ❖ ' . $catName . '  ';
        $badgeX = 70;
        $badgeY = 95;

        // Draw category badge pill
        imagefilledrectangle($img, $badgeX, $badgeY - 32, $badgeX + (mb_strlen($badgeText) * 16) + 30, $badgeY + 8, $cCrimson);
        imagerectangle($img, $badgeX, $badgeY - 32, $badgeX + (mb_strlen($badgeText) * 16) + 30, $badgeY + 8, $cGold);

        if ($hasTtf) {
            imagettftext($img, 18, 0, $badgeX + 12, $badgeY - 6, $cWhite, $fontPath, $badgeText);
        } else {
            imagestring($img, 5, $badgeX + 10, $badgeY - 24, $catName, $cWhite);
        }

        // Post Title (Word wrap into max 3 lines)
        $title = trim($post->title ?? 'আইডিয়াপত্র সাহিত্যকর্ম');
        $titleWords = explode(' ', $title);
        $titleLines = [];
        $currentLine = '';

        foreach ($titleWords as $wrd) {
            $test = ($currentLine === '') ? $wrd : $currentLine . ' ' . $wrd;
            if ($hasTtf) {
                $box = imagettfbbox(38, 0, $fontPath, $test);
                $textWidth = abs($box[4] - $box[0]);
                if ($textWidth > 1050 && $currentLine !== '') {
                    $titleLines[] = $currentLine;
                    $currentLine = $wrd;
                    if (count($titleLines) >= 3) break;
                } else {
                    $currentLine = $test;
                }
            } else {
                if (strlen($test) > 55 && $currentLine !== '') {
                    $titleLines[] = $currentLine;
                    $currentLine = $wrd;
                } else {
                    $currentLine = $test;
                }
            }
        }
        if ($currentLine !== '' && count($titleLines) < 3) {
            $titleLines[] = $currentLine;
        }

        // Draw Title Lines
        $titleStartY = 200;
        $lineHeight = 65;
        foreach ($titleLines as $idx => $line) {
            $y = $titleStartY + ($idx * $lineHeight);
            if ($hasTtf) {
                // Drop shadow
                imagettftext($img, 38, 0, 72, $y + 3, $cBgDark, $fontPath, $line);
                // Crisp white text
                imagettftext($img, 38, 0, 70, $y, $cWhite, $fontPath, $line);
            } else {
                imagestring($img, 5, 70, $y, $line, $cWhite);
            }
        }

        // Subtitle / Excerpt line (if available)
        $rawExcerpt = !empty($post->subtitle) ? $post->subtitle : (!empty($post->excerpt) ? $post->excerpt : strip_tags((string) $post->content));
        $cleanExcerpt = trim(preg_replace('/\s+/', ' ', $rawExcerpt));
        if (!empty($cleanExcerpt)) {
            $shortExcerpt = Str::limit($cleanExcerpt, 95);
            $excerptY = $titleStartY + (count($titleLines) * $lineHeight) + 30;
            if ($excerptY < 480 && $hasTtf) {
                imagettftext($img, 20, 0, 70, $excerptY, $cMuted, $fontPath, $shortExcerpt);
            }
        }

        // Divider Line before Footer
        imagesetthickness($img, 2);
        imageline($img, 70, 500, $w - 70, 500, $cBorder);
        imageline($img, 70, 500, 220, 500, $cGold);
        imagesetthickness($img, 1);

        // Author Name (Left)
        $authorName = $post->author_name ?: 'সম্পাদকীয় বিভাগ';
        $authorLabel = '✍️ রচনা: ' . $authorName;
        if ($hasTtf) {
            imagettftext($img, 24, 0, 70, 560, $cGoldLight, $fontPath, $authorLabel);
        } else {
            imagestring($img, 5, 70, 550, 'Author: ' . $authorName, $cGoldLight);
        }

        // Brand & Domain (Right)
        $brandText = 'আইডিয়া প্রকাশন • ideaabd.com';
        if ($hasTtf) {
            $brandBox = imagettfbbox(20, 0, $fontPath, $brandText);
            $brandW = abs($brandBox[4] - $brandBox[0]);
            imagettftext($img, 20, 0, ($w - 70 - $brandW), 560, $cOffWhite, $fontPath, $brandText);
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
