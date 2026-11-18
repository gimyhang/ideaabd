<?php

namespace App\Services;

use App\Models\IdeaInvoice;
use App\Support\SiteSetting;

/**
 * Link-preview (Open Graph) card for a shared invoice / challan / quotation / tender:
 * 1200×630 PNG for WhatsApp, Messenger, Facebook, Telegram.
 *
 * GD cannot shape Bangla script (vowel signs and conjuncts come out in the wrong order),
 * so the card itself uses Latin text only; Bangla names go in og:title / og:description,
 * which the apps render properly next to the image.
 */
class InvoiceOgImage
{
    public const W = 1200;
    public const H = 630;

    protected const TONES = [
        'invoice'   => [5, 150, 105],
        'challan'   => [37, 99, 235],
        'quotation' => [124, 58, 237],
        'tender'    => [217, 119, 6],
    ];

    public const LABELS = [
        'invoice'   => 'INVOICE',
        'challan'   => 'DELIVERY CHALLAN',
        'quotation' => 'QUOTATION',
        'tender'    => 'TENDER',
    ];

    /** Path of a cached PNG for this invoice version; renders it when missing. */
    public static function path(IdeaInvoice $invoice): string
    {
        $dir = storage_path('app/og-invoices');
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $file = $dir . '/' . $invoice->id . '-' . self::version($invoice) . '.png';

        if (!is_file($file)) {
            foreach (glob($dir . '/' . $invoice->id . '-*.png') ?: [] as $old) {
                @unlink($old);
            }
            imagepng(self::render($invoice), $file, 6);
        }

        return $file;
    }

    /** Changes whenever anything on the card changes, so apps that cache by URL fetch a fresh image. */
    public static function version(IdeaInvoice $invoice): string
    {
        return substr(md5(implode('|', [
            $invoice->updated_at?->timestamp, $invoice->type, $invoice->invoice_no, $invoice->grand_total,
            $invoice->paid_amount, $invoice->due_amount, $invoice->payment_status, $invoice->customer_name, $invoice->customer_org,
            SiteSetting::billLogoUrl(self::logoSetting()), 'v3',
        ])), 0, 12);
    }

    public static function render(IdeaInvoice $invoice): \GdImage
    {
        $im = imagecreatetruecolor(self::W, self::H);
        imagealphablending($im, true);
        imagesavealpha($im, true);

        $type = array_key_exists($invoice->type, self::LABELS) ? $invoice->type : 'invoice';
        [$r, $g, $b] = self::TONES[$type];
        $tone = imagecolorallocate($im, $r, $g, $b);
        $toneSoft = imagecolorallocate($im, (int) ($r + (255 - $r) * .9), (int) ($g + (255 - $g) * .9), (int) ($b + (255 - $b) * .9));
        $bg = imagecolorallocate($im, 241, 245, 249);
        $white = imagecolorallocate($im, 255, 255, 255);
        $ink = imagecolorallocate($im, 15, 23, 42);
        $ink2 = imagecolorallocate($im, 71, 85, 105);
        $mute = imagecolorallocate($im, 148, 163, 184);
        $line = imagecolorallocate($im, 226, 232, 240);

        $bold = self::font(true);
        $reg = self::font(false);

        imagefilledrectangle($im, 0, 0, self::W, self::H, $bg);
        // Card with a coloured left edge: a tone card under a white card shifted right.
        self::roundRect($im, 36, 36, self::W - 36, self::H - 36, 28, $tone);
        self::roundRect($im, 50, 36, self::W - 36, self::H - 36, 28, $white);

        $x = 110;
        $right = self::W - 84;

        // Logo (top-left) and document type pill (top-right).
        $logoBottom = 150;
        if ($logo = self::logo()) {
            [$lw, $lh] = [imagesx($logo), imagesy($logo)];
            $scale = min(280 / max(1, $lw), 96 / max(1, $lh), 1.5);
            $dw = (int) round($lw * $scale);
            $dh = (int) round($lh * $scale);
            imagecopyresampled($im, $logo, $x, 72, 0, 0, $dw, $dh, $lw, $lh);
            $logoBottom = 72 + $dh;
            imagedestroy($logo);
        } else {
            self::text($im, 34, $bold, $ink, $x, 124, self::latinOr(SiteSetting::name(), 'Idea Publication'));
        }

        $label = self::LABELS[$type];
        $pw = self::width(22, $bold, $label) + 56;
        self::roundRect($im, $right - $pw, 76, $right, 126, 25, $tone);
        self::text($im, 22, $bold, $white, $right - $pw + 28, 112, $label);
        $host = preg_replace('/^www\./', '', (string) parse_url(config('app.url') ?: url('/'), PHP_URL_HOST)) ?: 'ideaabd.com';
        self::text($im, 19, $reg, $mute, $right - self::width(19, $reg, $host), 162, $host);

        // Number + dates.
        $y = max(240, $logoBottom + 88);
        self::text($im, 54, $bold, $ink, $x, $y, self::fit('#' . $invoice->invoice_no, 54, $bold, $right - $x));
        $dates = [];
        if ($invoice->invoice_date) {
            $dates[] = 'Date ' . $invoice->invoice_date->format('d M Y');
        }
        if (in_array($type, ['quotation', 'tender'], true) && $invoice->valid_until) {
            $dates[] = 'Valid until ' . $invoice->valid_until->format('d M Y');
        } elseif ($invoice->due_date) {
            $dates[] = 'Due ' . $invoice->due_date->format('d M Y');
        }
        self::text($im, 22, $reg, $ink2, $x, $y + 46, implode('   ·   ', $dates));

        $who = self::latinOr($invoice->customer_org ?: '', '') ?: self::latinOr($invoice->customer_name ?: '', '');
        if ($who !== '') {
            self::text($im, 24, $reg, $mute, $x, $y + 94, 'For');
            self::text($im, 26, $bold, $ink, $x + 56, $y + 94, self::fit($who, 26, $bold, $right - $x - 56));
        }

        // Total band across the bottom: amount on the left, payment status on the right.
        $by1 = self::H - 36 - 158;
        $by2 = self::H - 36 - 34;
        self::roundRect($im, $x, $by1, $right, $by2, 20, $toneSoft);
        self::text($im, 17, $bold, $ink2, $x + 30, $by1 + 38, 'GRAND TOTAL');
        $amount = 'BDT ' . number_format((float) $invoice->grand_total, 2);
        self::text($im, 44, $bold, $tone, $x + 30, $by1 + 98, self::fit($amount, 44, $bold, 560));

        $status = null;
        if (in_array($type, ['invoice', 'challan'], true) && (float) $invoice->grand_total > 0) {
            $due = (float) $invoice->due_amount;
            $paid = (float) $invoice->paid_amount;
            $status = $due <= 0.004
                ? ['PAID', [5, 150, 105], null]
                : ($paid > 0 ? ['PARTLY PAID', [217, 119, 6], 'Due BDT ' . number_format($due, 2)] : ['UNPAID', [225, 29, 72], 'Due BDT ' . number_format($due, 2)]);
        } elseif (in_array($type, ['quotation', 'tender'], true)) {
            $status = ['Tap to view details', null, null];
        }
        if ($status) {
            [$sText, $sRgb, $sSub] = $status;
            $cy = ($by1 + $by2) / 2;
            if ($sRgb) {
                $sc = imagecolorallocate($im, ...$sRgb);
                $sw = self::width(20, $bold, $sText) + 44;
                $top = (int) ($sSub ? $cy - 42 : $cy - 22);
                self::roundRect($im, $right - 30 - $sw, $top, $right - 30, $top + 44, 22, $sc);
                self::text($im, 20, $bold, $white, $right - 30 - $sw + 22, $top + 32, $sText);
                if ($sSub) {
                    self::text($im, 20, $bold, $sc, $right - 30 - self::width(20, $bold, $sSub), $top + 84, $sSub);
                }
            } else {
                self::text($im, 20, $reg, $ink2, $right - 30 - self::width(20, $reg, $sText), (int) $cy + 8, $sText);
            }
        }

        return $im;
    }

    // ── helpers ─────────────────────────────────────────────

    protected static function logoSetting(): ?string
    {
        $s = \App\Http\Controllers\Admin\IdeaAccountingController::getInvoiceSettings();

        return $s['logo'] ?? null;
    }

    protected static function font(bool $bold): string
    {
        $candidates = $bold
            ? [resource_path('fonts/DejaVuSans-Bold.ttf'), base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans-Bold.ttf')]
            : [resource_path('fonts/DejaVuSans.ttf'), base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf')];
        $candidates[] = public_path('fonts/kalpurush/kalpurush.ttf');
        foreach ($candidates as $f) {
            if (is_file($f)) {
                return $f;
            }
        }
        throw new \RuntimeException('No TTF font available for the invoice preview image.');
    }

    protected static function text(\GdImage $im, int $size, string $font, int $color, int $x, int $y, string $text): void
    {
        imagettftext($im, $size, 0, $x, $y, $color, $font, $text);
    }

    protected static function width(int $size, string $font, string $text): int
    {
        $b = imagettfbbox($size, 0, $font, $text);

        return (int) abs($b[2] - $b[0]);
    }

    /** Shorten with an ellipsis so the text fits in $max pixels. */
    protected static function fit(string $text, int $size, string $font, int $max): string
    {
        if (self::width($size, $font, $text) <= $max) {
            return $text;
        }
        while (mb_strlen($text) > 1 && self::width($size, $font, $text . '…') > $max) {
            $text = mb_substr($text, 0, -1);
        }

        return rtrim($text) . '…';
    }

    /** The text if it has no Bangla (or other complex-script) letters, else the fallback. */
    protected static function latinOr(string $text, string $fallback): string
    {
        $text = trim($text);

        return $text !== '' && !preg_match('/[\x{0980}-\x{09FF}\x{0900}-\x{097F}\x{0600}-\x{06FF}]/u', $text) ? $text : $fallback;
    }

    protected static function roundRect(\GdImage $im, int $x1, int $y1, int $x2, int $y2, int $r, int $color): void
    {
        imagefilledrectangle($im, $x1 + $r, $y1, $x2 - $r, $y2, $color);
        imagefilledrectangle($im, $x1, $y1 + $r, $x2, $y2 - $r, $color);
        foreach ([[$x1 + $r, $y1 + $r], [$x2 - $r, $y1 + $r], [$x1 + $r, $y2 - $r], [$x2 - $r, $y2 - $r]] as [$cx, $cy]) {
            imagefilledellipse($im, $cx, $cy, $r * 2, $r * 2, $color);
        }
    }

    /** The bill logo as a GD image, read from local disk (never over HTTP). */
    protected static function logo(): ?\GdImage
    {
        $url = SiteSetting::billLogoUrl(self::logoSetting());
        $path = (string) parse_url($url, PHP_URL_PATH);
        $candidates = array_filter([
            $path !== '' ? public_path(ltrim(rawurldecode($path), '/')) : null,
            str_starts_with($path, '/storage/') ? storage_path('app/public/' . substr(rawurldecode($path), 9)) : null,
            public_path('images/logo.png'),
        ]);

        foreach ($candidates as $file) {
            if (!is_file($file)) {
                continue;
            }
            $data = @file_get_contents($file);
            $img = $data !== false ? @imagecreatefromstring($data) : false;
            if ($img) {
                return $img;
            }
        }

        return null;
    }
}

