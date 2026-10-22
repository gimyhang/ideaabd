<?php

declare(strict_types=1);

namespace Modules\Book\Services;

use Modules\Book\Models\Book;

/**
 * Builds the 1200x630 picture Facebook, WhatsApp and friends show for a book link:
 * the cover, centred and framed, over a blurred and darkened copy of itself.
 * A landscape card avoids the crop a portrait cover gets in link previews. No text is drawn
 * (GD cannot shape Bengali conjuncts), so every language renders correctly.
 */
class ShareCardService
{
    public const WIDTH = 1200;
    public const HEIGHT = 630;

    /** Local file path of the cover, or null when the book has no cover stored on this server. */
    public function coverPath(Book $book): ?string
    {
        $cover = (string) $book->cover_image;
        if ($cover === '' || str_starts_with($cover, 'http')) {
            return null;
        }
        $rel = preg_replace('#^/?storage/#', '', ltrim($cover, '/'));
        foreach ([storage_path('app/public/' . $rel), public_path('storage/' . $rel), public_path(ltrim($cover, '/'))] as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    /** Path of the cached card (created on first use, rebuilt when the book or its cover changes), or null. */
    public function path(Book $book): ?string
    {
        if (!extension_loaded('gd')) {
            return null;
        }
        $cover = $this->coverPath($book);
        if (!$cover) {
            return null;
        }

        $dir = storage_path('app/public/share-cards');
        $file = $dir . '/' . $book->id . '-' . filemtime($cover) . '.jpg';
        if (is_file($file)) {
            return $file;
        }
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $src = $this->load($cover);
        if (!$src) {
            return null;
        }

        $W = self::WIDTH; $H = self::HEIGHT;
        $sw = imagesx($src); $sh = imagesy($src);

        // 1. background: cover-fill, blurred (blur on a small copy: fast and smooth), then darkened
        $small = imagecreatetruecolor(240, 126);
        $scale = max(240 / $sw, 126 / $sh);
        $cw = (int) ceil(240 / $scale); $ch = (int) ceil(126 / $scale);
        imagecopyresampled($small, $src, 0, 0, (int) (($sw - $cw) / 2), (int) (($sh - $ch) / 2), 240, 126, $cw, $ch);
        for ($i = 0; $i < 6; $i++) {
            imagefilter($small, IMG_FILTER_GAUSSIAN_BLUR);
        }
        $canvas = imagecreatetruecolor($W, $H);
        imagecopyresampled($canvas, $small, 0, 0, 0, 0, $W, $H, 240, 126);
        imagefilter($canvas, IMG_FILTER_BRIGHTNESS, -70);
        imagedestroy($small);

        // 2. the cover itself, fitted into a 520px high frame with a white edge and a soft shadow
        $maxH = 520; $maxW = 760;
        $r = min($maxW / $sw, $maxH / $sh);
        $dw = (int) round($sw * $r); $dh = (int) round($sh * $r);
        $x = (int) (($W - $dw) / 2); $y = (int) (($H - $dh) / 2);
        $shadow = imagecolorallocatealpha($canvas, 0, 0, 0, 90);
        for ($i = 14; $i > 0; $i -= 2) {
            imagefilledrectangle($canvas, $x - $i + 6, $y - $i + 10, $x + $dw + $i + 6, $y + $dh + $i + 10, $shadow);
        }
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, $x - 6, $y - 6, $x + $dw + 5, $y + $dh + 5, $white);
        imagecopyresampled($canvas, $src, $x, $y, 0, 0, $dw, $dh, $sw, $sh);

        imagejpeg($canvas, $file, 86);
        imagedestroy($canvas);
        imagedestroy($src);

        // drop older cards of the same book
        foreach (glob($dir . '/' . $book->id . '-*.jpg') ?: [] as $old) {
            if ($old !== $file) {
                @unlink($old);
            }
        }

        return is_file($file) ? $file : null;
    }

    private function load(string $path)
    {
        $info = @getimagesize($path);
        $type = $info[2] ?? null;
        $img = match (true) {
            $type === IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            $type === IMAGETYPE_PNG => @imagecreatefrompng($path),
            $type === IMAGETYPE_GIF => @imagecreatefromgif($path),
            $type === IMAGETYPE_WEBP && function_exists('imagecreatefromwebp') => @imagecreatefromwebp($path),
            default => false,
        };
        if ($img && !imageistruecolor($img)) {
            imagepalettetotruecolor($img);
        }

        return $img ?: null;
    }
}
