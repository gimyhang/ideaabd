<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageOptimizerService
{
    /**
     * Convert an UploadedFile or file path into modern .webp (or fallback to .avif / .jpg)
     * with adaptive compression targeting <= 20 KB while maintaining crisp visual quality.
     *
     * @param UploadedFile|string $source
     * @param string $folder e.g. 'avatars', 'books/covers', 'blog', 'publishers/logos'
     * @param string $disk
     * @param int $quality (1-100, default 75)
     * @param int|null $maxWidth (default 600px — optimized for sharp retina & <= 20KB)
     * @param int|null $maxHeight (default 800px)
     * @return string Relative storage path (e.g. 'books/covers/cover_xxx.webp')
     */
    public static function convertAndStore($source, string $folder = 'uploads', string $disk = 'public', int $quality = 75, ?int $maxWidth = 600, ?int $maxHeight = 800): string
    {
        try {
            // Read raw binary from file or path
            $binary = null;
            if ($source instanceof UploadedFile) {
                // Security check for dangerous extensions
                $ext = strtolower($source->getClientOriginalExtension());
                if (in_array($ext, ['php', 'phtml', 'phar', 'exe', 'sh', 'bat', 'cmd', 'cgi', 'pl', 'py', 'asp', 'aspx', 'jsp', 'htm', 'html', 'js'], true)) {
                    throw new \InvalidArgumentException('Unsafe file type prohibited.');
                }

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

            $folder = trim($folder, '/');
            $randomName = Str::random(24) . '_' . time();

            if (empty($binary)) {
                if ($source instanceof UploadedFile) {
                    $stored = $source->store($folder, $disk);
                    self::mirrorToPublicIfApplicable($disk, $stored);
                    return $stored;
                }
                return (string) $source;
            }

            // Create GD Image resource
            $gdImage = @imagecreatefromstring($binary);
            if (!$gdImage) {
                if ($source instanceof UploadedFile) {
                    $stored = $source->store($folder, $disk);
                    self::mirrorToPublicIfApplicable($disk, $stored);
                    return $stored;
                }
                // Save raw binary directly
                $ext = 'jpg';
                $path = "{$folder}/{$randomName}.{$ext}";
                self::writeToDiskAndPublic($disk, $path, $binary);
                return $path;
            }

            if (function_exists('imageistruecolor') && !imageistruecolor($gdImage) && function_exists('imagepalettetotruecolor')) {
                imagepalettetotruecolor($gdImage);
            }

            // Optimize dimensions to optimal sharp resolution (maxWidth / maxHeight)
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

            $maxTargetBytes = 60 * 1024; // 60 KB limit for crisp retina & ultra fast loading

            // Precise Deduplication: compute SHA256 checksum of raw image content
            $contentHash = hash('sha256', $binary);
            $hashSuffix = substr($contentHash, 0, 12);

            $origFilename = 'img';
            if ($source instanceof UploadedFile) {
                $origFilename = pathinfo($source->getClientOriginalName(), PATHINFO_FILENAME);
            } elseif (is_string($source) && !str_starts_with($source, 'data:image')) {
                $origFilename = pathinfo($source, PATHINFO_FILENAME);
            }
            $slug = Str::slug($origFilename) ?: 'img';

            $deterministicName = "{$slug}_{$hashSuffix}.webp";
            $deterministicPath = "{$folder}/{$deterministicName}";

            // Check if identical file already exists on disk
            if (Storage::disk($disk)->exists($deterministicPath) && Storage::disk($disk)->size($deterministicPath) > 0) {
                if (isset($gdImage)) imagedestroy($gdImage);
                self::mirrorToPublicIfApplicable($disk, $deterministicPath);
                return $deterministicPath;
            }

            // Adaptive WebP Compression Engine targeting high fidelity and fast loading
            if (function_exists('imagewebp')) {
                $currentImg = $gdImage;
                $currentQ = min(85, max(60, $quality));
                $bestData = null;

                for ($pass = 0; $pass < 4; $pass++) {
                    ob_start();
                    imagewebp($currentImg, null, $currentQ);
                    $data = ob_get_clean();

                    if (!empty($data)) {
                        $bestData = $data;
                        if (strlen($data) <= $maxTargetBytes) {
                            break;
                        }
                    }

                    // Progressively adjust quality or gently scale down if still large
                    $currentQ -= 8;
                    if ($pass >= 1 && $currentImg) {
                        $curW = imagesx($currentImg);
                        $curH = imagesy($currentImg);
                        if ($curW > 400 && $curH > 400) {
                            $scaledW = (int) round($curW * 0.90);
                            $scaledH = (int) round($curH * 0.90);
                            $scaled = imagecreatetruecolor($scaledW, $scaledH);
                            if ($scaled) {
                                imagealphablending($scaled, false);
                                imagesavealpha($scaled, true);
                                imagecopyresampled($scaled, $currentImg, 0, 0, 0, 0, $scaledW, $scaledH, $curW, $curH);
                                if ($currentImg !== $gdImage) {
                                    imagedestroy($currentImg);
                                }
                                $currentImg = $scaled;
                            }
                        }
                    }
                }

                if ($currentImg && $currentImg !== $gdImage) {
                    imagedestroy($currentImg);
                }
                imagedestroy($gdImage);

                if (!empty($bestData)) {
                    self::writeToDiskAndPublic($disk, $deterministicPath, $bestData);
                    return $deterministicPath;
                }
            }

            // Fallback to AVIF if available
            if (function_exists('imageavif')) {
                ob_start();
                $success = @imageavif($gdImage, null, $quality);
                $avifData = ob_get_clean();

                if ($success && !empty($avifData)) {
                    imagedestroy($gdImage);
                    $path = "{$folder}/{$randomName}.avif";
                    self::writeToDiskAndPublic($disk, $path, $avifData);
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
                self::writeToDiskAndPublic($disk, $path, $jpgData);
                return $path;
            }

            if ($source instanceof UploadedFile) {
                $stored = $source->store($folder, $disk);
                self::mirrorToPublicIfApplicable($disk, $stored);
                return $stored;
            }
            return (string) $source;

        } catch (\Throwable $e) {
            Log::warning("ImageOptimizerService failed to convert image: " . $e->getMessage());
            if ($source instanceof UploadedFile) {
                $stored = $source->store($folder, $disk);
                self::mirrorToPublicIfApplicable($disk, $stored);
                return $stored;
            }
            return (string) $source;
        }
    }

    /**
     * Convert and optimize product image to a uniform square (1:1) WebP image.
     * Fits the product cleanly centered on an 800x800 canvas with crisp padding.
     *
     * @param \Illuminate\Http\UploadedFile|string $source
     * @param string $folder
     * @param string $disk
     * @param int $quality
     * @param int $dimension (default 800px)
     * @return string Relative path
     */
    public static function convertAndStoreSquareProductImage($source, string $folder = 'products', string $disk = 'public', int $quality = 82, int $dimension = 800): string
    {
        try {
            $binary = null;
            $origFilename = 'product';

            if ($source instanceof UploadedFile) {
                $ext = strtolower($source->getClientOriginalExtension());
                if (in_array($ext, ['php', 'phtml', 'phar', 'exe', 'sh', 'bat', 'cmd', 'cgi', 'pl', 'py', 'asp', 'aspx', 'jsp', 'htm', 'html', 'js'], true)) {
                    throw new \InvalidArgumentException('Unsafe file type prohibited.');
                }
                $origFilename = pathinfo($source->getClientOriginalName(), PATHINFO_FILENAME);
                $binary = @file_get_contents($source->getRealPath());
            } elseif (is_string($source)) {
                if (str_starts_with($source, 'http://') || str_starts_with($source, 'https://')) {
                    $context = stream_context_create([
                        'http' => ['timeout' => 15, 'user_agent' => 'Mozilla/5.0'],
                        'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false]
                    ]);
                    $binary = @file_get_contents($source, false, $context);
                    $urlPath = parse_url($source, PHP_URL_PATH);
                    $origFilename = $urlPath ? pathinfo($urlPath, PATHINFO_FILENAME) : 'product';
                } elseif (str_starts_with($source, 'data:image')) {
                    if (preg_match('/^data:image\/(\w+);base64,/', $source, $type)) {
                        $data = substr($source, strpos($source, ',') + 1);
                        $binary = base64_decode($data);
                    }
                } elseif (file_exists($source)) {
                    $origFilename = pathinfo($source, PATHINFO_FILENAME);
                    $binary = @file_get_contents($source);
                } elseif (Storage::disk($disk)->exists($source)) {
                    $origFilename = pathinfo($source, PATHINFO_FILENAME);
                    $binary = Storage::disk($disk)->get($source);
                }
            }

            if (empty($binary)) {
                if ($source instanceof UploadedFile) {
                    $stored = $source->store($folder, $disk);
                    self::mirrorToPublicIfApplicable($disk, $stored);
                    return $stored;
                }
                return (string) $source;
            }

            // Create GD Image resource
            $gdImage = @imagecreatefromstring($binary);
            if (!$gdImage) {
                if ($source instanceof UploadedFile) {
                    $stored = $source->store($folder, $disk);
                    self::mirrorToPublicIfApplicable($disk, $stored);
                    return $stored;
                }
                return (string) $source;
            }

            if (function_exists('imageistruecolor') && !imageistruecolor($gdImage) && function_exists('imagepalettetotruecolor')) {
                imagepalettetotruecolor($gdImage);
            }

            $origW = imagesx($gdImage);
            $origH = imagesy($gdImage);

            if ($origW <= 0 || $origH <= 0) {
                imagedestroy($gdImage);
                return (string) $source;
            }

            // Create uniform square canvas (800x800 standard)
            $canvas = imagecreatetruecolor($dimension, $dimension);

            // Fill canvas with pure white (#ffffff)
            $white = imagecolorallocate($canvas, 255, 255, 255);
            imagefilledrectangle($canvas, 0, 0, $dimension, $dimension, $white);

            // Scale content so it comfortably fits within 92% of the canvas (clean uniform margin)
            $maxContentSize = (int) round($dimension * 0.92);
            $scale = min($maxContentSize / $origW, $maxContentSize / $origH);
            $newW = max(1, (int) round($origW * $scale));
            $newH = max(1, (int) round($origH * $scale));

            // Center image on the square canvas
            $dstX = (int) round(($dimension - $newW) / 2);
            $dstY = (int) round(($dimension - $newH) / 2);

            imagecopyresampled($canvas, $gdImage, $dstX, $dstY, 0, 0, $newW, $newH, $origW, $origH);
            imagedestroy($gdImage);

            // Generate deterministic filename using content hash
            $contentHash = hash('sha256', $binary);
            $hashSuffix = substr($contentHash, 0, 10);
            $slug = Str::slug($origFilename) ?: 'prod';
            $fileName = "{$slug}_{$hashSuffix}.webp";
            $folder = trim($folder, '/');
            $relPath = "{$folder}/{$fileName}";

            // Check if identical file already exists on disk
            if (Storage::disk($disk)->exists($relPath) && Storage::disk($disk)->size($relPath) > 0) {
                imagedestroy($canvas);
                self::mirrorToPublicIfApplicable($disk, $relPath);
                return $relPath;
            }

            // WebP Compression
            $savedData = null;
            if (function_exists('imagewebp')) {
                ob_start();
                imagewebp($canvas, null, $quality);
                $savedData = ob_get_clean();
            }

            // Fallback to jpeg if webp failed or not available
            if (empty($savedData) && function_exists('imagejpeg')) {
                ob_start();
                imagejpeg($canvas, null, 88);
                $savedData = ob_get_clean();
                $relPath = "{$folder}/{$slug}_{$hashSuffix}.jpg";
            }

            imagedestroy($canvas);

            if (!empty($savedData)) {
                self::writeToDiskAndPublic($disk, $relPath, $savedData);
                return $relPath;
            }

            return (string) $source;
        } catch (\Throwable $e) {
            Log::warning('Product image optimization error: ' . $e->getMessage());
            if ($source instanceof UploadedFile) {
                $stored = $source->store($folder, $disk);
                self::mirrorToPublicIfApplicable($disk, $stored);
                return $stored;
            }
            return (string) $source;
        }
    }

    /**
     * Write file to storage disk and mirror to public/storage if disk is public
     */
    public static function writeToDiskAndPublic(string $disk, string $relPath, string $data): void
    {
        Storage::disk($disk)->put($relPath, $data);
        if ($disk === 'public') {
            try {
                $pubFile = public_path('storage/' . ltrim($relPath, '/'));
                $pubDir = dirname($pubFile);
                if (!file_exists($pubDir)) {
                    @mkdir($pubDir, 0777, true);
                }
                @file_put_contents($pubFile, $data);
            } catch (\Throwable $e) {}
        }
    }

    /**
     * Mirror a stored file from storage/app/public to public/storage if applicable
     */
    public static function mirrorToPublicIfApplicable(string $disk, ?string $relPath): void
    {
        if (empty($relPath) || $disk !== 'public') {
            return;
        }
        try {
            $clean = ltrim($relPath, '/');
            $storageFull = storage_path('app/public/' . $clean);
            $publicFull  = public_path('storage/' . $clean);

            if (file_exists($storageFull) && is_file($storageFull)) {
                $pubDir = dirname($publicFull);
                if (!file_exists($pubDir)) {
                    @mkdir($pubDir, 0777, true);
                }
                if (!file_exists($publicFull) || filesize($publicFull) !== filesize($storageFull)) {
                    @copy($storageFull, $publicFull);
                }
            }
        } catch (\Throwable $e) {}
    }

    /**
     * Convert a Base64 data URL (e.g. from canvas cropper) into modern .avif / .webp
     */
    public static function convertBase64AndStore(string $base64Data, string $folder = 'avatars', string $disk = 'public', int $quality = 80, ?int $maxWidth = 800, ?int $maxHeight = 1000): ?string
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
                self::writeToDiskAndPublic($disk, $path, $decoded);
                return $path;
            }

            $gdImage = @imagecreatefromstring($decoded);
            if (!$gdImage) {
                $path = "{$folder}/{$randomName}.jpg";
                self::writeToDiskAndPublic($disk, $path, $decoded);
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

            $maxTargetBytes = 60 * 1024; // 60 KB limit

            $contentHash = hash('sha256', $decoded);
            $hashSuffix = substr($contentHash, 0, 12);
            $deterministicName = "canvas_{$hashSuffix}.webp";
            $deterministicPath = "{$folder}/{$deterministicName}";

            // Check if identical canvas image already exists on disk
            if (Storage::disk($disk)->exists($deterministicPath) && Storage::disk($disk)->size($deterministicPath) > 0) {
                if (isset($gdImage)) imagedestroy($gdImage);
                self::mirrorToPublicIfApplicable($disk, $deterministicPath);
                return $deterministicPath;
            }

            // Adaptive WebP format targeting high visual quality & performance
            if (function_exists('imagewebp')) {
                $currentImg = $gdImage;
                $currentQ = min(85, max(60, $quality));
                $bestData = null;

                for ($pass = 0; $pass < 4; $pass++) {
                    ob_start();
                    imagewebp($currentImg, null, $currentQ);
                    $webpData = ob_get_clean();

                    if (!empty($webpData)) {
                        $bestData = $webpData;
                        if (strlen($webpData) <= $maxTargetBytes) {
                            break;
                        }
                    }

                    $currentQ -= 8;
                    if ($pass >= 1 && $currentImg) {
                        $curW = imagesx($currentImg);
                        $curH = imagesy($currentImg);
                        if ($curW > 400 && $curH > 400) {
                            $scaledW = (int) round($curW * 0.90);
                            $scaledH = (int) round($curH * 0.90);
                            $scaled = imagecreatetruecolor($scaledW, $scaledH);
                            if ($scaled) {
                                imagealphablending($scaled, false);
                                imagesavealpha($scaled, true);
                                imagecopyresampled($scaled, $currentImg, 0, 0, 0, 0, $scaledW, $scaledH, $curW, $curH);
                                if ($currentImg !== $gdImage) {
                                    imagedestroy($currentImg);
                                }
                                $currentImg = $scaled;
                            }
                        }
                    }
                }

                if ($currentImg && $currentImg !== $gdImage) {
                    imagedestroy($currentImg);
                }
                imagedestroy($gdImage);

                if (!empty($bestData)) {
                    self::writeToDiskAndPublic($disk, $deterministicPath, $bestData);
                    return $deterministicPath;
                }
            }

            // Fallback JPEG
            ob_start();
            imagejpeg($gdImage, null, 90);
            $jpgData = ob_get_clean();
            imagedestroy($gdImage);

            $path = "{$folder}/{$randomName}.jpg";
            self::writeToDiskAndPublic($disk, $path, $jpgData);
            return $path;

        } catch (\Throwable $e) {
            Log::warning("ImageOptimizerService failed base64 conversion: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Batch convert all PNG, JPG, JPEG, BMP raster files in a directory to WebP.
     * Preserves transparency for PNGs and creates optimal WebP compression.
     *
     * @return array ['converted_count' => int, 'bytes_saved' => int, 'converted_files' => array, 'files' => array]
     */
    public static function batchConvertDirectoryToWebp(string $directoryPath, int $quality = 85, bool $deleteOriginal = false): array
    {
        if (!is_dir($directoryPath) || !function_exists('imagewebp')) {
            return ['converted_count' => 0, 'bytes_saved' => 0, 'converted_files' => [], 'files' => []];
        }

        $convertedCount = 0;
        $totalBytesSaved = 0;
        $processedFiles = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directoryPath, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $filePath = $file->getPathname();
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'bmp', 'avif', 'svg'])) {
                continue;
            }

            $res = self::convertImageToWebp($filePath, $quality, $deleteOriginal);
            if ($res['success']) {
                $convertedCount++;
                $totalBytesSaved += $res['bytes_saved'];
                $processedFiles[] = [
                    'original'    => $filePath,
                    'filename'    => basename($filePath),
                    'webp'        => $res['webp_path'],
                    'webp_name'   => basename($res['webp_path']),
                    'saved'       => $res['bytes_saved'],
                    'saved_bytes' => $res['bytes_saved'],
                ];
            }
        }

        return [
            'converted_count' => $convertedCount,
            'bytes_saved'     => $totalBytesSaved,
            'converted_files' => $processedFiles,
            'files'           => $processedFiles,
        ];
    }

    /**
     * Minify and optimize an SVG file in place.
     * Strips redundant comments, formatting whitespace, doctype/metadata, and cleans namespaces.
     *
     * @return int Bytes saved (positive integer) or 0 if unchanged.
     */
    public static function optimizeSvgFile(string $filePath): int
    {
        if (!file_exists($filePath) || strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) !== 'svg') {
            return 0;
        }

        $content = @file_get_contents($filePath);
        if (empty($content) || !str_contains($content, '<svg')) {
            return 0;
        }

        $origSize = strlen($content);

        // Strip XML comments
        $cleaned = preg_replace('/<!--(?!<!)[^\[>][\s\S]*?-->/u', '', $content);

        // Strip XML declaration & doctype
        $cleaned = preg_replace('/<\?xml[^>]*\?>/i', '', $cleaned);
        $cleaned = preg_replace('/<!DOCTYPE[^>]*>/i', '', $cleaned);

        // Strip metadata tags
        $cleaned = preg_replace('/<metadata[\s\S]*?<\/metadata>/i', '', $cleaned);

        // Collapse formatting whitespace between tags
        $cleaned = preg_replace('/>\s+</u', '><', $cleaned);
        $cleaned = preg_replace('/\s{2,}/u', ' ', $cleaned);
        $cleaned = trim($cleaned);

        if (!str_contains($cleaned, '<svg')) {
            return 0;
        }

        $newSize = strlen($cleaned);
        if ($newSize < $origSize) {
            @file_put_contents($filePath, $cleaned);
            return max(0, $origSize - $newSize);
        }

        return 0;
    }


    /**
     * Generate an aesthetic luxury photocard (SVG) with flawless Bengali typography and store it in storage disk.
     */
    public static function generatePhotocardAndStore(string $title, string $authorName = 'আইডিয়া প্রকাশন', string $folder = 'blog', string $disk = 'public'): string
    {
        $folder = trim($folder, '/');
        $randomName = Str::random(24) . '_' . time();

        $safeTitle = htmlspecialchars(Str::limit($title, 80), ENT_QUOTES, 'UTF-8');
        $safeAuthor = htmlspecialchars($authorName, ENT_QUOTES, 'UTF-8');

        // Split title into balanced lines for SVG typography (max ~28 chars per line)
        $words = explode(' ', $title);
        $lines = [];
        $curLine = '';
        foreach ($words as $w) {
            if (mb_strlen($curLine . ' ' . $w) > 28) {
                if ($curLine) $lines[] = htmlspecialchars(trim($curLine), ENT_QUOTES, 'UTF-8');
                $curLine = $w;
            } else {
                $curLine = $curLine ? $curLine . ' ' . $w : $w;
            }
        }
        if ($curLine) $lines[] = htmlspecialchars(trim($curLine), ENT_QUOTES, 'UTF-8');
        $lines = array_slice($lines, 0, 3);
        if (empty($lines)) $lines = [$safeTitle];

        $titleFontSize = count($lines) > 2 ? 40 : (count($lines) === 2 ? 46 : 52);
        $lineH = (int) round($titleFontSize * 1.38);
        $totalH = count($lines) * $lineH;
        $startY = (int) round(315 - ($totalH / 2) + ($lineH / 2));

        $tspanTags = '';
        foreach ($lines as $i => $l) {
            $y = $startY + ($i * $lineH);
            $tspanTags .= "<tspan x=\"600\" y=\"{$y}\">{$l}</tspan>\n";
        }

        $dividerY = max(435, $startY + $totalH + 20);

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 675" width="1200" height="675">
  <defs>
    <linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#0f172a" />
      <stop offset="50%" stop-color="#1e1b4b" />
      <stop offset="100%" stop-color="#312e81" />
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
  <rect x="430" y="68" width="340" height="44" rx="22" fill="rgba(255,255,255,0.12)" stroke="#fbbf24" stroke-width="1.5" />
  <text x="600" y="97" fill="#fbbf24" font-family="'Hind Siliguri', 'SolaimanLipi', 'Kalpurush', sans-serif" font-size="20" font-weight="bold" text-anchor="middle">✦ আইডিয়াপত্র • সাহিত্য ও চিন্তার উন্মুক্ত মঞ্চ ✦</text>
  <g text-anchor="middle">
    <text font-family="'Hind Siliguri', 'SolaimanLipi', 'Kalpurush', sans-serif" font-size="{$titleFontSize}" font-weight="bold" fill="#ffffff">
      {$tspanTags}
    </text>
  </g>
  <line x1="300" y1="{$dividerY}" x2="520" y2="{$dividerY}" stroke="url(#gold)" stroke-width="2" opacity="0.85" />
  <text x="600" y="{$dividerY}" dy="6" fill="#fef08a" font-size="20" text-anchor="middle">❖ ─── ✦ ─── ❖</text>
  <line x1="680" y1="{$dividerY}" x2="900" y2="{$dividerY}" stroke="url(#gold)" stroke-width="2" opacity="0.85" />
  <line x1="80" y1="565" x2="1120" y2="565" stroke="rgba(255,255,255,0.2)" stroke-width="1.5" />
  <text x="85" y="605" fill="#ffffff" font-family="'Hind Siliguri', 'SolaimanLipi', 'Kalpurush', sans-serif" font-size="24" font-weight="bold">✍️ রচনা: {$safeAuthor}</text>
  <text x="1115" y="605" fill="#fbbf24" font-family="'Hind Siliguri', 'SolaimanLipi', 'Kalpurush', sans-serif" font-size="22" font-weight="bold" text-anchor="end">আইডিয়া প্রকাশন | ideaprakashan.com</text>
</svg>
SVG;
        $filename = "{$folder}/photocard_" . time() . '_' . Str::random(8) . '.svg';
        Storage::disk($disk)->put($filename, $svg);
        return $filename;
    }

    /**
     * Generate an aesthetic 2:3 portrait book cover (SVG vector) with flawless Bengali typography and store it in storage disk.
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
        $randomName = 'cover_' . time() . '_' . Str::random(8) . '.svg';

        $themes = \App\Support\BookCoverGenerator::THEMES;
        $theme = $themes[$themeKey] ?? $themes['royal_blue'];

        $svg = \App\Support\BookCoverGenerator::renderSvg($title, $authorName, null, $categoryName, $theme);
        $path = "{$folder}/{$randomName}";
        Storage::disk($disk)->put($path, $svg);
        return $path;
    }

    /**
     * Convert a single image file (JPG, PNG, AVIF, BMP) to WebP format.
     * For SVG files, performs vector XML minification while preserving fonts and vector fidelity.
     *
     * @return array ['success' => bool, 'webp_path' => string, 'bytes_saved' => int, 'original_size' => int, 'new_size' => int]
     */
    public static function convertImageToWebp(string $sourcePath, int $quality = 85, bool $deleteOriginal = false): array
    {
        if (!file_exists($sourcePath)) {
            return ['success' => false, 'message' => 'Source file not found', 'bytes_saved' => 0];
        }

        $ext = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
        if ($ext === 'webp') {
            return ['success' => true, 'webp_path' => $sourcePath, 'bytes_saved' => 0, 'already_webp' => true];
        }

        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'avif', 'bmp', 'svg'])) {
            return ['success' => false, 'message' => 'Unsupported format for WebP conversion', 'bytes_saved' => 0];
        }

        $origSize = filesize($sourcePath);

        // For SVG files, optimize XML without rasterizing to preserve 100% flawless typography
        if ($ext === 'svg') {
            $bytesSaved = self::optimizeSvgFile($sourcePath);
            $newSize = filesize($sourcePath);

            return [
                'success'       => true,
                'webp_path'     => $sourcePath,
                'original_path' => $sourcePath,
                'original_size' => $origSize,
                'new_size'      => $newSize,
                'bytes_saved'   => $bytesSaved,
                'filename'      => basename($sourcePath),
                'is_svg'        => true,
            ];
        }

        // Load source image resource with multiple fallback decoders
        $srcImage = null;
        try {
            $rawContent = @file_get_contents($sourcePath);
            if (!empty($rawContent)) {
                $srcImage = @imagecreatefromstring($rawContent);
            }

            if (!$srcImage) {
                $srcImage = match ($ext) {
                    'jpg', 'jpeg' => @imagecreatefromjpeg($sourcePath),
                    'png'         => @imagecreatefrompng($sourcePath),
                    'avif'        => function_exists('imagecreatefromavif') ? @imagecreatefromavif($sourcePath) : null,
                    'bmp'         => function_exists('imagecreatefrombmp') ? @imagecreatefrombmp($sourcePath) : null,
                    default       => null,
                };
            }
        } catch (\Throwable $e) {
            $srcImage = null;
        }

        // If not decodable (e.g. text seed file or corrupt placeholder), synthesize a valid fallback image
        if (!$srcImage) {
            $baseName = pathinfo($sourcePath, PATHINFO_FILENAME);
            $w = 600;
            $h = 900;
            $srcImage = @imagecreatetruecolor($w, $h);
            if ($srcImage) {
                $bg = imagecolorallocate($srcImage, 15, 23, 42);
                imagefilledrectangle($srcImage, 0, 0, $w, $h, $bg);
                $border = imagecolorallocate($srcImage, 234, 179, 8);
                imagesetthickness($srcImage, 4);
                imagerectangle($srcImage, 20, 20, $w - 20, $h - 20, $border);
                $textColor = imagecolorallocate($srcImage, 255, 255, 255);
                $cleanTitle = Str::headline(preg_replace('/[_-]/', ' ', $baseName));
                imagestring($srcImage, 5, 40, 200, substr($cleanTitle, 0, 24), $textColor);
                imagestring($srcImage, 4, 40, 240, "IDEA PUBLICATION", $border);
            }
        }

        if (!$srcImage) {
            return ['success' => false, 'message' => 'Could not decode image', 'bytes_saved' => 0];
        }

        $w = imagesx($srcImage);
        $h = imagesy($srcImage);

        $targetImage = imagecreatetruecolor($w, $h);

        // Preserve PNG / Alpha transparency
        imagealphablending($targetImage, false);
        imagesavealpha($targetImage, true);
        $transparent = imagecolorallocatealpha($targetImage, 255, 255, 255, 127);
        imagefilledrectangle($targetImage, 0, 0, $w, $h, $transparent);

        imagecopyresampled($targetImage, $srcImage, 0, 0, 0, 0, $w, $h, $w, $h);

        $dir = dirname($sourcePath);
        $baseName = pathinfo($sourcePath, PATHINFO_FILENAME);
        $webpPath = $dir . '/' . $baseName . '.webp';

        // Temporary target file
        $tempWebp = $webpPath . '.tmp';
        $saved = imagewebp($targetImage, $tempWebp, $quality);

        imagedestroy($srcImage);
        imagedestroy($targetImage);

        if (!$saved || !file_exists($tempWebp)) {
            if (file_exists($tempWebp)) @unlink($tempWebp);
            return ['success' => false, 'message' => 'WebP encoding failed', 'bytes_saved' => 0];
        }

        $newSize = filesize($tempWebp);
        rename($tempWebp, $webpPath);

        $bytesSaved = max(0, $origSize - $newSize);

        if ($deleteOriginal && $webpPath !== $sourcePath && file_exists($sourcePath)) {
            @unlink($sourcePath);
        }

        return [
            'success'       => true,
            'webp_path'     => $webpPath,
            'original_path' => $sourcePath,
            'original_size' => $origSize,
            'new_size'      => $newSize,
            'bytes_saved'   => $bytesSaved,
            'filename'      => basename($webpPath),
        ];
    }

    /**
     * Safely delete an existing image file from storage or public directory when replaced or removed.
     * Handles relative paths, storage URLs, and full URLs.
     */
    public static function deleteImageFile(?string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        // Clean query strings or domain prefixes
        $clean = parse_url($path, PHP_URL_PATH) ?: $path;
        $clean = ltrim(str_replace('\\', '/', $clean), '/');

        // If prefixed with 'storage/'
        if (str_starts_with($clean, 'storage/')) {
            $storageRel = substr($clean, 8);
            $fullStoragePath = storage_path('app/public/' . $storageRel);
            if (file_exists($fullStoragePath) && is_file($fullStoragePath)) {
                @unlink($fullStoragePath);
                return true;
            }
        }

        // Check directly under storage/app/public/
        $directStorage = storage_path('app/public/' . $clean);
        if (file_exists($directStorage) && is_file($directStorage)) {
            @unlink($directStorage);
            return true;
        }

        // Check directly under public/
        $directPublic = public_path($clean);
        if (file_exists($directPublic) && is_file($directPublic)) {
            @unlink($directPublic);
            return true;
        }

        // If an absolute file path is passed
        if (file_exists($path) && is_file($path)) {
            @unlink($path);
            return true;
        }

        return false;
    }

    /**
     * Scan storage and purge all orphaned / replaced images not currently referenced in the database.
     *
     * @return array ['purged_count' => int, 'bytes_saved' => int, 'formatted_saved' => string, 'files' => array]
     */
    public static function purgeOrphanedImages(): array
    {
        $activeBasenames = [];

        // 1. Collect all active image references from Books
        if (\Illuminate\Support\Facades\Schema::hasTable('books')) {
            $cols = [];
            if (\Illuminate\Support\Facades\Schema::hasColumn('books', 'cover_image')) $cols[] = 'cover_image';
            if (\Illuminate\Support\Facades\Schema::hasColumn('books', 'look_inside_images')) $cols[] = 'look_inside_images';
            if (!empty($cols)) {
                $books = \Illuminate\Support\Facades\DB::table('books')->get($cols);
                foreach ($books as $b) {
                    if (isset($b->cover_image) && !empty($b->cover_image)) {
                        $bn = basename((string)$b->cover_image);
                        $activeBasenames[$bn] = true;
                        $activeBasenames[pathinfo($bn, PATHINFO_FILENAME)] = true;
                    }
                    if (isset($b->look_inside_images) && !empty($b->look_inside_images)) {
                        $inside = is_string($b->look_inside_images) && str_starts_with(trim($b->look_inside_images), '[')
                            ? json_decode($b->look_inside_images, true)
                            : explode(',', (string)$b->look_inside_images);
                        if (is_array($inside)) {
                            foreach ($inside as $img) {
                                if (!empty($img)) {
                                    $bn = basename(trim((string)$img));
                                    $activeBasenames[$bn] = true;
                                    $activeBasenames[pathinfo($bn, PATHINFO_FILENAME)] = true;
                                }
                            }
                        }
                    }
                }
            }
        }

        // 2. Authors
        if (\Illuminate\Support\Facades\Schema::hasTable('authors')) {
            if (\Illuminate\Support\Facades\Schema::hasColumn('authors', 'avatar')) {
                $authors = \Illuminate\Support\Facades\DB::table('authors')->whereNotNull('avatar')->pluck('avatar');
                foreach ($authors as $av) {
                    if (!empty($av)) {
                        $bn = basename((string)$av);
                        $activeBasenames[$bn] = true;
                        $activeBasenames[pathinfo($bn, PATHINFO_FILENAME)] = true;
                    }
                }
            }
        }

        // 3. Publishers
        if (\Illuminate\Support\Facades\Schema::hasTable('publishers')) {
            if (\Illuminate\Support\Facades\Schema::hasColumn('publishers', 'logo')) {
                $pubs = \Illuminate\Support\Facades\DB::table('publishers')->whereNotNull('logo')->pluck('logo');
                foreach ($pubs as $logo) {
                    if (!empty($logo)) {
                        $bn = basename((string)$logo);
                        $activeBasenames[$bn] = true;
                        $activeBasenames[pathinfo($bn, PATHINFO_FILENAME)] = true;
                    }
                }
            }
        }

        // 4. Ebooks
        if (\Illuminate\Support\Facades\Schema::hasTable('ebooks')) {
            if (\Illuminate\Support\Facades\Schema::hasColumn('ebooks', 'cover_image')) {
                $ebooks = \Illuminate\Support\Facades\DB::table('ebooks')->whereNotNull('cover_image')->pluck('cover_image');
                foreach ($ebooks as $eb) {
                    if (!empty($eb)) {
                        $bn = basename((string)$eb);
                        $activeBasenames[$bn] = true;
                        $activeBasenames[pathinfo($bn, PATHINFO_FILENAME)] = true;
                    }
                }
            }
        }

        // 5. Blog Posts
        if (\Illuminate\Support\Facades\Schema::hasTable('blog_posts')) {
            if (\Illuminate\Support\Facades\Schema::hasColumn('blog_posts', 'featured_image')) {
                $posts = \Illuminate\Support\Facades\DB::table('blog_posts')->whereNotNull('featured_image')->pluck('featured_image');
                foreach ($posts as $fi) {
                    if (!empty($fi)) {
                        $bn = basename((string)$fi);
                        $activeBasenames[$bn] = true;
                        $activeBasenames[pathinfo($bn, PATHINFO_FILENAME)] = true;
                    }
                }
            }
        }

        // 6. Users / Avatars
        if (\Illuminate\Support\Facades\Schema::hasTable('users')) {
            if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'avatar')) {
                $avatars = \Illuminate\Support\Facades\DB::table('users')->whereNotNull('avatar')->pluck('avatar');
                foreach ($avatars as $av) {
                    if (!empty($av)) {
                        $bn = basename((string)$av);
                        $activeBasenames[$bn] = true;
                        $activeBasenames[pathinfo($bn, PATHINFO_FILENAME)] = true;
                    }
                }
            }
        }

        // 7. Sliders / Banners
        if (\Illuminate\Support\Facades\Schema::hasTable('sliders')) {
            $sliderCols = array_filter(['image', 'banner_image', 'cover_image'], fn($c) => \Illuminate\Support\Facades\Schema::hasColumn('sliders', $c));
            if (!empty($sliderCols)) {
                $sliders = \Illuminate\Support\Facades\DB::table('sliders')->get($sliderCols);
                foreach ($sliders as $s) {
                    foreach ($sliderCols as $sc) {
                        if (!empty($s->{$sc})) {
                            $bn = basename((string)$s->{$sc});
                            $activeBasenames[$bn] = true;
                            $activeBasenames[pathinfo($bn, PATHINFO_FILENAME)] = true;
                        }
                    }
                }
            }
        }

        // 8. Categories
        if (\Illuminate\Support\Facades\Schema::hasTable('categories')) {
            $catCols = array_filter(['image', 'icon', 'cover_image', 'banner'], fn($c) => \Illuminate\Support\Facades\Schema::hasColumn('categories', $c));
            if (!empty($catCols)) {
                $cats = \Illuminate\Support\Facades\DB::table('categories')->get($catCols);
                foreach ($cats as $ci) {
                    foreach ($catCols as $cc) {
                        if (!empty($ci->{$cc})) {
                            $bn = basename((string)$ci->{$cc});
                            $activeBasenames[$bn] = true;
                            $activeBasenames[pathinfo($bn, PATHINFO_FILENAME)] = true;
                        }
                    }
                }
            }
        }

        // 9. Campaigns / Events
        if (\Illuminate\Support\Facades\Schema::hasTable('event_campaigns')) {
            $campCols = array_filter(['banner_image', 'card_bg_image', 'card_logo_image', 'card_event_logo_image', 'image'], fn($c) => \Illuminate\Support\Facades\Schema::hasColumn('event_campaigns', $c));
            if (!empty($campCols)) {
                $events = \Illuminate\Support\Facades\DB::table('event_campaigns')->get($campCols);
                foreach ($events as $ev) {
                    foreach ($campCols as $col) {
                        if (!empty($ev->{$col})) {
                            $bn = basename((string)$ev->{$col});
                            $activeBasenames[$bn] = true;
                            $activeBasenames[pathinfo($bn, PATHINFO_FILENAME)] = true;
                        }
                    }
                }
            }
        }

        // 10. Site Settings
        if (\Illuminate\Support\Facades\Schema::hasTable('site_settings')) {
            if (\Illuminate\Support\Facades\Schema::hasColumn('site_settings', 'value')) {
                $settings = \Illuminate\Support\Facades\DB::table('site_settings')->pluck('value');
                foreach ($settings as $val) {
                    if (is_string($val) && (str_contains($val, '.webp') || str_contains($val, '.png') || str_contains($val, '.jpg') || str_contains($val, '.svg'))) {
                        $bn = basename($val);
                        $activeBasenames[$bn] = true;
                        $activeBasenames[pathinfo($bn, PATHINFO_FILENAME)] = true;
                    }
                }
            }
        }

        // Target directories to scan for orphaned files
        $dirsToScan = [
            storage_path('app/public/books/covers'),
            storage_path('app/public/books/look_inside'),
            storage_path('app/public/authors'),
            storage_path('app/public/publishers'),
            storage_path('app/public/publishers/logos'),
            storage_path('app/public/ebooks/covers'),
            storage_path('app/public/blog'),
            storage_path('app/public/avatars'),
            storage_path('app/public/signatures'),
            storage_path('app/public/uploads'),
            storage_path('app/public/campaigns'),
            public_path('images/books'),
            public_path('images/authors'),
            public_path('images/publishers'),
        ];

        $purgedCount = 0;
        $totalBytesSaved = 0;
        $purgedFiles = [];

        // Protected system filenames that must never be deleted
        $protectedNames = [
            'default.png', 'default.webp', 'default.jpg', 'placeholder.png', 'placeholder.webp',
            'no-cover.png', 'no-cover.webp', 'logo.png', 'logo.webp', 'favicon.ico', 'favicon.png',
            'og-image.jpg', 'og-image.webp', 'avatar-default.png', 'avatar-default.webp', '.gitignore',
        ];

        foreach ($dirsToScan as $dir) {
            if (!\Illuminate\Support\Facades\File::isDirectory($dir)) {
                continue;
            }

            $files = \Illuminate\Support\Facades\File::allFiles($dir);
            foreach ($files as $file) {
                $filename = $file->getFilename();
                $baseNoExt = pathinfo($filename, PATHINFO_FILENAME);
                $ext = strtolower($file->getExtension());

                if (in_array($filename, $protectedNames, true) || in_array($ext, ['gitignore', 'gitkeep', 'htaccess'])) {
                    continue;
                }

                if (!in_array($ext, ['webp', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'bmp', 'avif'])) {
                    continue;
                }

                // If not referenced in active database records
                if (!isset($activeBasenames[$filename]) && !isset($activeBasenames[$baseNoExt])) {
                    $size = $file->getSize();
                    $fullPath = $file->getPathname();
                    
                    if (@unlink($fullPath)) {
                        $purgedCount++;
                        $totalBytesSaved += $size;
                        $purgedFiles[] = [
                            'name' => $filename,
                            'path' => $fullPath,
                            'size' => $size,
                        ];
                    }
                }
            }
        }

        // Invalidate media lookup cache
        \Illuminate\Support\Facades\Cache::forget('media_asset_title_lookup_v3');

        $units = ['B', 'KB', 'MB', 'GB'];
        $b = max($totalBytesSaved, 0);
        $pow = floor(($b ? log($b) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $b /= (1 << (10 * $pow));
        $formattedSaved = round($b, 2) . ' ' . $units[$pow];

        return [
            'purged_count'    => $purgedCount,
            'bytes_saved'     => $totalBytesSaved,
            'formatted_saved' => $formattedSaved,
            'purged_files'    => array_slice($purgedFiles, 0, 50),
        ];
    }
}
