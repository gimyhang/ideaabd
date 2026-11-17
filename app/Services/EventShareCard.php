<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EventCampaign;
use Illuminate\Support\Facades\Storage;

/**
 * The picture a shared campaign link shows (og:image): a 1200x630 JPEG built from the campaign's
 * own banner, so a scholarship link never previews the site's main banner. Cards are cached on disk
 * and rebuilt automatically when the banner, title or colour changes (see EventCampaign::shareImageVersion).
 */
class EventShareCard
{
    public const WIDTH = 1200;
    public const HEIGHT = 630;

    public function path(EventCampaign $campaign): ?string
    {
        if (!function_exists('imagecreatetruecolor')) {
            return null;
        }

        $dir = Storage::disk('public')->path('share-cards');
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            return null;
        }

        $file = $dir . '/' . $campaign->id . '-' . $campaign->shareImageVersion() . '.jpg';
        if (is_file($file)) {
            return $file;
        }

        try {
            $image = $this->render($campaign);
            $ok = imagejpeg($image, $file, 88);
            imagedestroy($image);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Share card failed: ' . $e->getMessage());

            return null;
        }

        if (!$ok) {
            return null;
        }

        // Old versions of this campaign's card are no longer referenced.
        foreach (glob($dir . '/' . $campaign->id . '-*.jpg') ?: [] as $old) {
            if ($old !== $file) {
                @unlink($old);
            }
        }

        return $file;
    }

    /** @return \GdImage */
    private function render(EventCampaign $campaign)
    {
        $banner = $this->loadBanner($campaign);
        $theme = $this->themeColor($campaign);

        $canvas = imagecreatetruecolor(self::WIDTH, self::HEIGHT);

        if ($banner === null) {
            $this->themedCard($canvas, $campaign, $theme);

            return $canvas;
        }

        $bw = imagesx($banner);
        $bh = imagesy($banner);
        $targetRatio = self::WIDTH / self::HEIGHT;
        $ratio = $bw / max(1, $bh);

        if ($ratio >= $targetRatio * 0.8 && $ratio <= $targetRatio * 1.25) {
            // Close to the card shape: fill it, cropping a little from the long side.
            $this->cover($canvas, $banner);
        } else {
            // Very wide or tall banner: show all of it (text on a banner must not be cut off)
            // over a blurred, darkened copy of itself.
            $this->cover($canvas, $banner);
            for ($i = 0; $i < 6; $i++) {
                imagefilter($canvas, IMG_FILTER_GAUSSIAN_BLUR);
            }
            imagefilter($canvas, IMG_FILTER_BRIGHTNESS, -60);
            $wide = $ratio > 2.2 && $this->latin($campaign->title) !== null && $this->fontFile() !== null;
            $bottom = $this->contain($canvas, $banner, $wide ? -56 : 0);
            if ($wide) {
                $this->captionUnder($canvas, $campaign, $bottom);
            }
        }

        imagedestroy($banner);

        return $canvas;
    }

    /** @return \GdImage|null */
    private function loadBanner(EventCampaign $campaign)
    {
        if (!$campaign->banner_image) {
            return null;
        }

        $disk = Storage::disk('public');
        if (!$disk->exists($campaign->banner_image)) {
            return null;
        }

        $data = @file_get_contents($disk->path($campaign->banner_image));
        $img = $data !== false ? @imagecreatefromstring($data) : false;

        return $img ?: null;
    }

    private function cover($canvas, $src): void
    {
        $sw = imagesx($src);
        $sh = imagesy($src);
        $scale = max(self::WIDTH / $sw, self::HEIGHT / $sh);
        $cw = (int) round(self::WIDTH / $scale);
        $ch = (int) round(self::HEIGHT / $scale);
        $sx = (int) max(0, round(($sw - $cw) / 2));
        $sy = (int) max(0, round(($sh - $ch) / 2));
        imagecopyresampled($canvas, $src, 0, 0, $sx, $sy, self::WIDTH, self::HEIGHT, min($cw, $sw), min($ch, $sh));
    }

    /** Shows the whole banner; returns the y of its bottom edge. */
    private function contain($canvas, $src, int $shiftY = 0): int
    {
        $sw = imagesx($src);
        $sh = imagesy($src);
        $scale = min(self::WIDTH / $sw, self::HEIGHT / $sh);
        $dw = (int) round($sw * $scale);
        $dh = (int) round($sh * $scale);
        $y = (int) ((self::HEIGHT - $dh) / 2) + $shiftY;
        imagecopyresampled($canvas, $src, (int) ((self::WIDTH - $dw) / 2), $y, 0, 0, $dw, $dh, $sw, $sh);

        return $y + $dh;
    }

    /** A very wide banner leaves empty space: put the campaign's English title under it. */
    private function captionUnder($canvas, EventCampaign $campaign, int $bottomY): void
    {
        $font = $this->fontFile();
        $title = $this->latin($campaign->title);
        if ($font === null || $title === null) {
            return;
        }

        $white = imagecolorallocate($canvas, 255, 255, 255);
        $y = $bottomY + 62;
        foreach (array_slice($this->wrap($title, 34, $font, 1040), 0, 2) as $line) {
            $box = imagettfbbox(34, 0, $font, $line);
            imagettftext($canvas, 34, 0, (int) ((self::WIDTH - ($box[2] - $box[0])) / 2), $y, $white, $font, $line);
            $y += 52;
        }
    }

    /** No banner uploaded: a clean card in the campaign colour with its English title. */
    private function themedCard($canvas, EventCampaign $campaign, array $rgb): void
    {
        [$r, $g, $b] = $rgb;
        for ($y = 0; $y < self::HEIGHT; $y++) {
            $t = $y / self::HEIGHT;
            $c = imagecolorallocate($canvas, (int) ($r * (1 - 0.45 * $t)), (int) ($g * (1 - 0.45 * $t)), (int) ($b * (1 - 0.45 * $t)));
            imageline($canvas, 0, $y, self::WIDTH, $y, $c);
        }

        $white = imagecolorallocate($canvas, 255, 255, 255);
        $soft = imagecolorallocatealpha($canvas, 255, 255, 255, 96);
        imagefilledrectangle($canvas, 80, 96, 92, 534, $white);          // accent bar
        imagefilledellipse($canvas, 1060, 120, 360, 360, $soft);          // quiet decoration
        imagefilledellipse($canvas, 1130, 560, 240, 240, $soft);

        $font = $this->fontFile();
        $badge = $this->latin($campaign->badge_text) ?? 'Scholarship Application';
        $title = $this->latin($campaign->title) ?? 'Idea Prokashon';
        $site = 'ideaabd.com';

        if ($font === null) {
            return; // plain coloured card is still better than the site banner
        }

        imagettftext($canvas, 26, 0, 130, 160, $white, $font, strtoupper($badge));
        $y = 270;
        foreach ($this->wrap($title, 46, $font, 960) as $line) {
            imagettftext($canvas, 46, 0, 130, $y, $white, $font, $line);
            $y += 74;
        }
        imagettftext($canvas, 26, 0, 130, 560, $white, $font, 'Idea Prokashon  |  ' . $site);
    }

    /** @return int[] */
    private function themeColor(EventCampaign $campaign): array
    {
        $fs = is_array($campaign->form_settings) ? $campaign->form_settings : [];
        foreach ([$fs['theme_color'] ?? null, $campaign->theme_color, '#0f3a68'] as $hex) {
            if (is_string($hex) && preg_match('/^#([0-9a-fA-F]{6})$/', $hex, $m)) {
                return [hexdec(substr($m[1], 0, 2)), hexdec(substr($m[1], 2, 2)), hexdec(substr($m[1], 4, 2))];
            }
        }

        return [15, 58, 104];
    }

    /** The drawing font cannot shape Bengali, so only English text is drawn. */
    private function latin(?string $text): ?string
    {
        $text = trim((string) $text);

        return ($text !== '' && !preg_match('/\p{Bengali}/u', $text)) ? $text : null;
    }

    private function fontFile(): ?string
    {
        foreach ([
            base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans-Bold.ttf'),
            storage_path('fonts/DejaVuSans-Bold.ttf'),
        ] as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    /** @return string[] */
    private function wrap(string $text, int $size, string $font, int $maxWidth): array
    {
        $lines = [];
        $line = '';
        foreach (preg_split('/\s+/', $text) ?: [] as $word) {
            $try = trim($line . ' ' . $word);
            $box = imagettfbbox($size, 0, $font, $try);
            if ($line !== '' && ($box[2] - $box[0]) > $maxWidth) {
                $lines[] = $line;
                $line = $word;
            } else {
                $line = $try;
            }
        }
        if ($line !== '') {
            $lines[] = $line;
        }

        return array_slice($lines, 0, 3);
    }
}
