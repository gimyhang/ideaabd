<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageOptimizerService
{
    /**
     * Convert an UploadedFile or file path into modern .avif (or fallback to .webp / .jpg)
     * and store it in the specified public disk folder.
     *
     * @param UploadedFile|string $source
     * @param string $folder e.g. 'avatars', 'books/covers', 'blog', 'publishers/logos'
     * @param string $disk
     * @param int $quality (1-100, default 82)
     * @param int|null $maxWidth
     * @param int|null $maxHeight
     * @return string Relative storage path (e.g. 'avatars/author_xxx.avif')
     */
    public static function convertAndStore($source, string $folder = 'uploads', string $disk = 'public', int $quality = 82, ?int $maxWidth = 1600, ?int $maxHeight = 1600): string
    {
        try {
            // Read raw binary from file or path
            $binary = null;
            if ($source instanceof UploadedFile) {
                // If not an image (e.g. PDF/EPUB), pass through directly
                $mime = $source->getMimeType();
                if (!str_starts_with($mime, 'image/')) {
                    return $source->store($folder, $disk);
                }
                $binary = file_get_contents($source->getRealPath());
            } elseif (is_string($source)) {
                if (str_starts_with($source, 'data:image')) {
                    return self::convertBase64AndStore($source, $folder, $disk, $quality, $maxWidth, $maxHeight);
                }
                if (file_exists($source)) {
                    $binary = file_get_contents($source);
                } elseif (Storage::disk($disk)->exists($source)) {
                    $binary = Storage::disk($disk)->get($source);
                }
            }

            if (empty($binary)) {
                if ($source instanceof UploadedFile) {
                    return $source->store($folder, $disk);
                }
                return (string) $source;
            }

            // Create GD Image resource
            $gdImage = @imagecreatefromstring($binary);
            if (!$gdImage) {
                if ($source instanceof UploadedFile) {
                    return $source->store($folder, $disk);
                }
                return (string) $source;
            }

            if (function_exists('imageistruecolor') && !imageistruecolor($gdImage) && function_exists('imagepalettetotruecolor')) {
                imagepalettetotruecolor($gdImage);
            }

            // Optimize dimensions if oversized
            $origW = imagesx($gdImage);
            $origH = imagesy($gdImage);

            if (($maxWidth && $origW > $maxWidth) || ($maxHeight && $origH > $maxHeight)) {
                $ratio = min($maxWidth / $origW, $maxHeight / $origH);
                $newW = (int) round($origW * $ratio);
                $newH = (int) round($origH * $ratio);

                if (function_exists('imagecreatetruecolor')) {
                    $resized = @imagecreatetruecolor($newW, $newH);
                    if ($resized) {
                        imagealphablending($resized, false);
                        imagesavealpha($resized, true);
                        imagecopyresampled($resized, $gdImage, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
                        imagedestroy($gdImage);
                        $gdImage = $resized;
                    }
                }
            }

            // Output to AVIF (or WebP fallback)
            $folder = trim($folder, '/');
            $randomName = Str::random(24) . '_' . time();

            if (function_exists('imageavif')) {
                ob_start();
                $success = @imageavif($gdImage, null, $quality);
                $avifData = ob_get_clean();

                if ($success && !empty($avifData)) {
                    imagedestroy($gdImage);
                    $path = "{$folder}/{$randomName}.avif";
                    Storage::disk($disk)->put($path, $avifData);
                    return $path;
                }
            }

            // Fallback to WebP
            if (function_exists('imagewebp')) {
                ob_start();
                $success = @imagewebp($gdImage, null, $quality);
                $webpData = ob_get_clean();

                if ($success && !empty($webpData)) {
                    imagedestroy($gdImage);
                    $path = "{$folder}/{$randomName}.webp";
                    Storage::disk($disk)->put($path, $webpData);
                    return $path;
                }
            }

            // Fallback to standard JPEG
            if (function_exists('imagejpeg')) {
                ob_start();
                imagejpeg($gdImage, null, 88);
                $jpgData = ob_get_clean();
                imagedestroy($gdImage);

                $path = "{$folder}/{$randomName}.jpg";
                Storage::disk($disk)->put($path, $jpgData);
                return $path;
            }

            if ($source instanceof UploadedFile) {
                return $source->store($folder, $disk);
            }
            return (string) $source;

        } catch (\Throwable $e) {
            Log::warning("ImageOptimizerService failed to convert image: " . $e->getMessage());
            if ($source instanceof UploadedFile) {
                return $source->store($folder, $disk);
            }
            return (string) $source;
        }
    }

    /**
     * Convert a Base64 data URL (e.g. from canvas cropper) into modern .avif / .webp
     */
    public static function convertBase64AndStore(string $base64Data, string $folder = 'avatars', string $disk = 'public', int $quality = 85, ?int $maxWidth = 800, ?int $maxHeight = 800): ?string
    {
        try {
            if (!str_starts_with($base64Data, 'data:image')) {
                return null;
            }

            @list(, $data) = explode(',', $base64Data);
            $decoded = base64_decode($data);
            if ($decoded === false) {
                return null;
            }

            $folder = trim($folder, '/');
            $randomName = Str::random(24) . '_' . time();

            if (!function_exists('imagecreatefromstring')) {
                $path = "{$folder}/{$randomName}.jpg";
                Storage::disk($disk)->put($path, $decoded);
                return $path;
            }

            $gdImage = @imagecreatefromstring($decoded);
            if (!$gdImage) {
                $path = "{$folder}/{$randomName}.jpg";
                Storage::disk($disk)->put($path, $decoded);
                return $path;
            }

            if (function_exists('imageistruecolor') && !imageistruecolor($gdImage) && function_exists('imagepalettetotruecolor')) {
                imagepalettetotruecolor($gdImage);
            }

            $origW = imagesx($gdImage);
            $origH = imagesy($gdImage);

            if (($maxWidth && $origW > $maxWidth) || ($maxHeight && $origH > $maxHeight)) {
                $ratio = min($maxWidth / $origW, $maxHeight / $origH);
                $newW = (int) round($origW * $ratio);
                $newH = (int) round($origH * $ratio);

                if (function_exists('imagecreatetruecolor')) {
                    $resized = @imagecreatetruecolor($newW, $newH);
                    if ($resized) {
                        imagealphablending($resized, false);
                        imagesavealpha($resized, true);
                        imagecopyresampled($resized, $gdImage, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
                        imagedestroy($gdImage);
                        $gdImage = $resized;
                    }
                }
            }

            $folder = trim($folder, '/');
            $randomName = Str::random(24) . '_' . time();

            // AVIF format
            if (function_exists('imageavif')) {
                ob_start();
                $success = @imageavif($gdImage, null, $quality);
                $avifData = ob_get_clean();

                if ($success && !empty($avifData)) {
                    imagedestroy($gdImage);
                    $path = "{$folder}/{$randomName}.avif";
                    Storage::disk($disk)->put($path, $avifData);
                    return $path;
                }
            }

            // WebP format
            if (function_exists('imagewebp')) {
                ob_start();
                $success = @imagewebp($gdImage, null, $quality);
                $webpData = ob_get_clean();

                if ($success && !empty($webpData)) {
                    imagedestroy($gdImage);
                    $path = "{$folder}/{$randomName}.webp";
                    Storage::disk($disk)->put($path, $webpData);
                    return $path;
                }
            }

            // Fallback JPEG
            ob_start();
            imagejpeg($gdImage, null, 90);
            $jpgData = ob_get_clean();
            imagedestroy($gdImage);

            $path = "{$folder}/{$randomName}.jpg";
            Storage::disk($disk)->put($path, $jpgData);
            return $path;

        } catch (\Throwable $e) {
            Log::warning("ImageOptimizerService failed base64 conversion: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate an aesthetic luxury photocard (.avif / .webp) and store it in storage disk.
     */
    public static function generatePhotocardAndStore(string $title, string $authorName = 'আইডিয়া প্রকাশন', string $folder = 'blog', string $disk = 'public'): string
    {
        $folder = trim($folder, '/');
        $randomName = Str::random(24) . '_' . time();

        // If GD extension is not loaded in PHP, fallback gracefully to luxury SVG without crashing
        if (!function_exists('imagecreatetruecolor') || !function_exists('imagecolorallocate')) {
            $safeTitle = htmlspecialchars(Str::limit($title, 70), ENT_QUOTES, 'UTF-8');
            $safeAuthor = htmlspecialchars($authorName, ENT_QUOTES, 'UTF-8');
            $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 675" width="1200" height="675">
  <defs>
    <linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#022c22" />
      <stop offset="50%" stop-color="#064e3b" />
      <stop offset="100%" stop-color="#022019" />
    </linearGradient>
    <radialGradient id="glow" cx="50%" cy="45%" r="65%">
      <stop offset="0%" stop-color="#fbbf24" stop-opacity="0.18" />
      <stop offset="100%" stop-color="#000000" stop-opacity="0" />
    </radialGradient>
    <linearGradient id="gold" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#eab308" />
      <stop offset="50%" stop-color="#fef08a" />
      <stop offset="100%" stop-color="#eab308" />
    </linearGradient>
  </defs>
  <rect width="1200" height="675" fill="url(#bg)" />
  <rect width="1200" height="675" fill="url(#glow)" />
  <rect x="30" y="30" width="1140" height="615" fill="none" stroke="#fbbf24" stroke-width="1.5" opacity="0.4" rx="10" />
  <rect x="42" y="42" width="1116" height="591" fill="none" stroke="#fbbf24" stroke-width="3" opacity="0.85" rx="6" />
  <rect x="475" y="68" width="250" height="42" rx="21" fill="rgba(255,255,255,0.12)" stroke="#fbbf24" stroke-width="1.5" />
  <text x="600" y="96" fill="#fbbf24" font-family="'Hind Siliguri', 'Kalpurush', sans-serif" font-size="19" font-weight="bold" text-anchor="middle">✦ সাহিত্যপত্র ও প্রবন্ধ ✦</text>
  <text x="600" y="305" fill="#ffffff" font-family="'Hind Siliguri', 'Kalpurush', sans-serif" font-size="46" font-weight="bold" text-anchor="middle">{$safeTitle}</text>
  <line x1="320" y1="410" x2="520" y2="410" stroke="url(#gold)" stroke-width="2" opacity="0.85" />
  <text x="600" y="416" fill="#fef08a" font-size="20" text-anchor="middle">❖ ─── ✦ ─── ❖</text>
  <line x1="680" y1="410" x2="880" y2="410" stroke="url(#gold)" stroke-width="2" opacity="0.85" />
  <line x1="80" y1="565" x2="1120" y2="565" stroke="rgba(255,255,255,0.2)" stroke-width="1.5" />
  <text x="85" y="605" fill="#ffffff" font-family="'Hind Siliguri', 'Kalpurush', sans-serif" font-size="22" font-weight="bold">✍️ রচনা: {$safeAuthor}</text>
  <text x="1115" y="605" fill="#fbbf24" font-family="'Hind Siliguri', 'Kalpurush', sans-serif" font-size="20" font-weight="bold" text-anchor="end">আইডিয়া প্রকাশন | www.ideaabd.com</text>
</svg>
SVG;
            $filename = "{$folder}/photocard_" . time() . '_' . Str::random(8) . '.svg';
            Storage::disk($disk)->put($filename, $svg);
            return $filename;
        }

        $fontPath = public_path('fonts/kalpurush/kalpurush.ttf');
        $useTtf = file_exists($fontPath);

        $im = @imagecreatetruecolor(1200, 675);
        if (!$im) {
            $im = imagecreate(1200, 675);
        }

        // Gradient emerald background
        for ($y = 0; $y < 675; $y++) {
            $ratio = $y / 675;
            $r = (int)(2 * (1 - $ratio) + 6 * $ratio * 0.5 + 2 * $ratio * 0.5);
            $g = (int)(44 * (1 - $ratio) + 78 * $ratio * 0.5 + 32 * $ratio * 0.5);
            $b = (int)(34 * (1 - $ratio) + 59 * $ratio * 0.5 + 25 * $ratio * 0.5);
            $col = imagecolorallocate($im, $r, $g, $b);
            imageline($im, 0, $y, 1200, $y, $col);
        }

        // Colors
        $gold = imagecolorallocate($im, 251, 191, 36);
        $goldDim = imagecolorallocate($im, 180, 140, 30);
        $white = imagecolorallocate($im, 255, 255, 255);
        $yellow = imagecolorallocate($im, 254, 240, 138);

        // Double Gold Borders & Corner lines
        imagesetthickness($im, 2);
        imagerectangle($im, 30, 30, 1170, 645, $goldDim);
        imagesetthickness($im, 4);
        imagerectangle($im, 42, 42, 1158, 633, $gold);

        // Corner decorative lines
        imagesetthickness($im, 2);
        imageline($im, 42, 85, 85, 42, $gold);
        imageline($im, 42, 105, 105, 42, $gold);
        imageline($im, 1158, 85, 1115, 42, $gold);
        imageline($im, 1158, 105, 1095, 42, $gold);
        imageline($im, 42, 590, 85, 633, $gold);
        imageline($im, 42, 570, 105, 633, $gold);
        imageline($im, 1158, 590, 1115, 633, $gold);
        imageline($im, 1158, 570, 1095, 633, $gold);

        if ($useTtf) {
            // Top category badge
            $badge = "✦ আইডিয়া সাহিত্যপত্র ✦";
            $bbox = imagettfbbox(16, 0, $fontPath, $badge);
            $w = abs($bbox[4] - $bbox[0]);
            $x = (int)((1200 - $w) / 2);
            imagettftext($im, 16, 0, $x, 100, $gold, $fontPath, $badge);

            // Bengali Title with word wrap
            $fontSize = mb_strlen($title) > 50 ? 32 : (mb_strlen($title) > 30 ? 36 : 42);
            $words = explode(' ', $title);
            $lines = [];
            $curLine = '';
            foreach ($words as $word) {
                $test = $curLine ? $curLine . ' ' . $word : $word;
                $bbox = imagettfbbox($fontSize, 0, $fontPath, $test);
                $w = abs($bbox[4] - $bbox[0]);
                if ($w > 950 && $curLine) {
                    $lines[] = $curLine;
                    $curLine = $word;
                } else {
                    $curLine = $test;
                }
            }
            if ($curLine) $lines[] = $curLine;

            $lineH = $fontSize + 24;
            $totalH = count($lines) * $lineH;
            $startY = (int)(330 - ($totalH / 2) + ($lineH / 2));

            foreach ($lines as $i => $lineText) {
                $bbox = imagettfbbox($fontSize, 0, $fontPath, $lineText);
                $w = abs($bbox[4] - $bbox[0]);
                $x = (int)((1200 - $w) / 2);
                imagettftext($im, $fontSize, 0, $x, $startY + ($i * $lineH), $white, $fontPath, $lineText);
            }

            // Divider
            $dividerY = max(430, $startY + $totalH + 20);
            imagesetthickness($im, 2);
            imageline($im, 300, $dividerY, 520, $dividerY, $goldDim);
            imageline($im, 680, $dividerY, 900, $dividerY, $goldDim);
            $ornament = "❖ ─── ✦ ─── ❖";
            $bbox = imagettfbbox(18, 0, $fontPath, $ornament);
            $w = abs($bbox[4] - $bbox[0]);
            imagettftext($im, 18, 0, (int)((1200 - $w) / 2), $dividerY + 7, $yellow, $fontPath, $ornament);

            // Bottom Footer
            imagesetthickness($im, 1);
            imageline($im, 75, 565, 1125, 565, $goldDim);
            imagettftext($im, 20, 0, 80, 608, $white, $fontPath, "রচনা: " . $authorName);

            $imprint = "আইডিয়া প্রকাশন | www.ideaabd.com";
            $bbox = imagettfbbox(18, 0, $fontPath, $imprint);
            $w = abs($bbox[4] - $bbox[0]);
            imagettftext($im, 18, 0, 1120 - $w, 608, $gold, $fontPath, $imprint);
        } else {
            // Built-in font fallback
            $titleCenter = max(20, (int)((1200 - (strlen($title) * 9)) / 2));
            imagestring($im, 5, $titleCenter, 310, $title, $white);
            imagestring($im, 4, 80, 600, "Author: " . $authorName, $white);
            imagestring($im, 4, 900, 600, "ideaabd.com", $gold);
        }

        // Save to AVIF
        if (function_exists('imageavif')) {
            ob_start();
            $success = @imageavif($im, null, 85);
            $avifData = ob_get_clean();

            if ($success && !empty($avifData)) {
                imagedestroy($im);
                $path = "{$folder}/{$randomName}.avif";
                Storage::disk($disk)->put($path, $avifData);
                return $path;
            }
        }

        // Fallback WebP
        if (function_exists('imagewebp')) {
            ob_start();
            $success = @imagewebp($im, null, 85);
            $webpData = ob_get_clean();

            if ($success && !empty($webpData)) {
                imagedestroy($im);
                $path = "{$folder}/{$randomName}.webp";
                Storage::disk($disk)->put($path, $webpData);
                return $path;
            }
        }

        // Fallback JPEG
        ob_start();
        imagejpeg($im, null, 90);
        $jpgData = ob_get_clean();
        imagedestroy($im);

        $path = "{$folder}/{$randomName}.jpg";
        Storage::disk($disk)->put($path, $jpgData);
        return $path;
    }

    /**
     * Generate an aesthetic 2:3 portrait book cover (.avif / .webp) and store it in storage disk.
     */
    public static function generateBookCoverAndStore(
        string $title,
        string $authorName = 'আইডিয়া প্রকাশন',
        ?string $categoryName = null,
        ?string $themeKey = 'royal_blue',
        string $folder = 'books/covers',
        string $disk = 'public'
    ): string {
        $folder = trim($folder, '/');
        $randomName = Str::random(24) . '_' . time();

        $themes = [
            'royal_blue'     => ['bg' => [15, 23, 42],    'title' => [255, 255, 255], 'author' => [253, 224, 71], 'accent' => [251, 191, 36]],
            'deep_emerald'   => ['bg' => [6, 78, 59],     'title' => [255, 255, 255], 'author' => [254, 240, 138], 'accent' => [167, 243, 208]],
            'crimson_ruby'   => ['bg' => [69, 10, 10],    'title' => [255, 255, 255], 'author' => [254, 215, 170], 'accent' => [251, 146, 60]],
            'regal_purple'   => ['bg' => [46, 16, 101],   'title' => [255, 255, 255], 'author' => [254, 240, 138], 'accent' => [216, 180, 254]],
            'midnight_slate' => ['bg' => [24, 24, 27],    'title' => [255, 255, 255], 'author' => [226, 232, 240], 'accent' => [203, 213, 225]],
            'warm_brown'     => ['bg' => [59, 29, 17],    'title' => [255, 255, 255], 'author' => [253, 224, 71], 'accent' => [245, 158, 11]],
            'dark_teal'      => ['bg' => [4, 47, 46],     'title' => [255, 255, 255], 'author' => [167, 243, 208], 'accent' => [45, 212, 191]],
        ];

        $palette = $themes[$themeKey] ?? $themes['royal_blue'];

        // If GD extension is not loaded in PHP, fallback gracefully to SVG
        if (!function_exists('imagecreatetruecolor') || !function_exists('imagecolorallocate')) {
            $safeTitle = htmlspecialchars(Str::limit($title, 80), ENT_QUOTES, 'UTF-8');
            $safeAuthor = htmlspecialchars($authorName, ENT_QUOTES, 'UTF-8');
            $svg = \App\Support\BookCoverGenerator::renderSvg($title, $authorName, null, $categoryName, [
                'bg' => sprintf('#%02x%02x%02x', ...$palette['bg']),
                'title_color' => '#ffffff',
                'author_color' => sprintf('#%02x%02x%02x', ...$palette['author'])
            ]);
            $filename = "{$folder}/cover_" . time() . '_' . Str::random(8) . '.svg';
            Storage::disk($disk)->put($filename, $svg);
            return $filename;
        }

        $width = 800;
        $height = 1200;
        $fontPath = public_path('fonts/kalpurush/kalpurush.ttf');
        $useTtf = file_exists($fontPath);

        $im = @imagecreatetruecolor($width, $height);
        if (!$im) {
            $im = imagecreate($width, $height);
        }

        // Background color & gradient shading
        for ($y = 0; $y < $height; $y++) {
            $ratio = $y / $height;
            $r = (int)($palette['bg'][0] * (1 - $ratio * 0.3));
            $g = (int)($palette['bg'][1] * (1 - $ratio * 0.3));
            $b = (int)($palette['bg'][2] * (1 - $ratio * 0.3));
            $lineColor = imagecolorallocate($im, $r, $g, $b);
            imageline($im, 0, $y, $width, $y, $lineColor);
        }

        // Colors
        $white = imagecolorallocate($im, 255, 255, 255);
        $accent = imagecolorallocate($im, $palette['accent'][0], $palette['accent'][1], $palette['accent'][2]);
        $authorCol = imagecolorallocate($im, $palette['author'][0], $palette['author'][1], $palette['author'][2]);
        $dimAccent = imagecolorallocate($im, (int)($palette['accent'][0] * 0.6), (int)($palette['accent'][1] * 0.6), (int)($palette['accent'][2] * 0.6));

        // Framing
        imagesetthickness($im, 2);
        imagerectangle($im, 35, 35, $width - 35, $height - 35, $dimAccent);
        imagesetthickness($im, 3);
        imagerectangle($im, 45, 45, $width - 45, $height - 45, $accent);

        if ($useTtf) {
            // Top Publisher Badge
            $topBadge = "✦ আইডিয়া প্রকাশন ✦";
            $bbox = imagettfbbox(16, 0, $fontPath, $topBadge);
            $w = abs($bbox[4] - $bbox[0]);
            imagettftext($im, 16, 0, (int)(($width - $w) / 2), 110, $accent, $fontPath, $topBadge);

            // Title with line-wrapping
            $fontSize = mb_strlen($title) > 40 ? 36 : (mb_strlen($title) > 20 ? 44 : 52);
            $words = explode(' ', $title);
            $lines = [];
            $curLine = '';
            foreach ($words as $word) {
                $test = $curLine ? $curLine . ' ' . $word : $word;
                $bbox = imagettfbbox($fontSize, 0, $fontPath, $test);
                $w = abs($bbox[4] - $bbox[0]);
                if ($w > 640 && $curLine) {
                    $lines[] = $curLine;
                    $curLine = $word;
                } else {
                    $curLine = $test;
                }
            }
            if ($curLine) $lines[] = $curLine;

            $lineH = $fontSize + 24;
            $totalH = count($lines) * $lineH;
            $startY = (int)(440 - ($totalH / 2) + ($lineH / 2));

            foreach ($lines as $i => $lineText) {
                $bbox = imagettfbbox($fontSize, 0, $fontPath, $lineText);
                $w = abs($bbox[4] - $bbox[0]);
                $x = (int)(($width - $w) / 2);
                imagettftext($im, $fontSize, 0, $x, $startY + ($i * $lineH), $white, $fontPath, $lineText);
            }

            // Divider ornament
            $dividerY = max(600, $startY + $totalH + 30);
            imagesetthickness($im, 2);
            imageline($im, 200, $dividerY, 340, $dividerY, $dimAccent);
            imageline($im, 460, $dividerY, 600, $dividerY, $dimAccent);
            $ornament = "❖ ── ✦ ── ❖";
            $bbox = imagettfbbox(16, 0, $fontPath, $ornament);
            $w = abs($bbox[4] - $bbox[0]);
            imagettftext($im, 16, 0, (int)(($width - $w) / 2), $dividerY + 6, $accent, $fontPath, $ornament);

            // Author Name
            $authorFontSize = 26;
            $bbox = imagettfbbox($authorFontSize, 0, $fontPath, $authorName);
            $w = abs($bbox[4] - $bbox[0]);
            imagettftext($im, $authorFontSize, 0, (int)(($width - $w) / 2), $dividerY + 60, $authorCol, $fontPath, $authorName);

            // Bottom Brand Footer
            $bottomText = "আইডিয়া প্রকাশন | www.ideaabd.com";
            $bbox = imagettfbbox(14, 0, $fontPath, $bottomText);
            $w = abs($bbox[4] - $bbox[0]);
            imagettftext($im, 14, 0, (int)(($width - $w) / 2), 1130, $accent, $fontPath, $bottomText);
        } else {
            $titleCenter = max(20, (int)(($width - (strlen($title) * 9)) / 2));
            imagestring($im, 5, $titleCenter, 450, $title, $white);
            imagestring($im, 4, 100, 600, "Author: " . $authorName, $authorCol);
            imagestring($im, 4, 300, 1100, "ideaabd.com", $accent);
        }

        // Save as AVIF
        if (function_exists('imageavif')) {
            ob_start();
            $success = @imageavif($im, null, 85);
            $avifData = ob_get_clean();

            if ($success && !empty($avifData)) {
                imagedestroy($im);
                $path = "{$folder}/{$randomName}.avif";
                Storage::disk($disk)->put($path, $avifData);
                return $path;
            }
        }

        // WebP Fallback
        if (function_exists('imagewebp')) {
            ob_start();
            $success = @imagewebp($im, null, 85);
            $webpData = ob_get_clean();

            if ($success && !empty($webpData)) {
                imagedestroy($im);
                $path = "{$folder}/{$randomName}.webp";
                Storage::disk($disk)->put($path, $webpData);
                return $path;
            }
        }

        // JPEG Fallback
        ob_start();
        imagejpeg($im, null, 90);
        $jpgData = ob_get_clean();
        imagedestroy($im);

        $path = "{$folder}/{$randomName}.jpg";
        Storage::disk($disk)->put($path, $jpgData);
        return $path;
    }
}
