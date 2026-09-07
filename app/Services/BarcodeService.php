<?php

declare(strict_types=1);

namespace App\Services;

use Modules\Book\Models\Book;

class BarcodeService
{
    /**
     * Code 128 Patterns table (B & C subsets)
     * Each pattern consists of 6 element widths (alternating bar and space).
     */
    private const CODE128_PATTERNS = [
        '212222', '222122', '222221', '121223', '121322', '131222', '122213', '122312', '132212', '221213', // 0-9
        '221312', '231212', '112232', '122132', '122231', '113222', '123122', '123221', '223211', '221132', // 10-19
        '221231', '213212', '223112', '312131', '311222', '321122', '321221', '312212', '322112', '322211', // 20-29
        '212123', '212321', '232121', '111323', '131123', '131321', '112313', '132113', '132311', '211313', // 30-39
        '231113', '231311', '112133', '112331', '132131', '113123', '113321', '133121', '313121', '211331', // 40-49
        '231131', '213113', '213311', '213131', '311123', '311321', '331121', '312113', '312311', '332111', // 50-59
        '314111', '221411', '431111', '111224', '111422', '121124', '121421', '141122', '141221', '112214', // 60-69
        '112412', '122114', '122411', '142112', '142211', '241211', '221114', '413111', '241112', '134111', // 70-79
        '111242', '121142', '121241', '114212', '124112', '124211', '411212', '421112', '421211', '212141', // 80-89
        '214121', '412121', '111143', '111341', '131141', '114113', '114311', '411113', '411311', '113141', // 90-99
        '114131', '311141', '411131', '211412', '211214', '211232', '2331112' // 100-106 (106 is STOP: 7 elements)
    ];

    /**
     * Generate next available Idea Publication In-House Serial Number (e.g. 'IP001', 'IP002'...)
     */
    public static function generateNextIdeaSerial(): string
    {
        $allIdeaSerials = Book::where(function ($q) {
            $q->where('publisher_id', 2)->orWhereNull('publisher_id');
        })
        ->whereNotNull('idea_serial_no')
        ->where('idea_serial_no', '!=', '')
        ->pluck('idea_serial_no');

        $maxNum = 0;
        foreach ($allIdeaSerials as $serial) {
            if (preg_match('/^IP-?(\d+)$/i', trim($serial), $matches)) {
                $num = (int) $matches[1];
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }

        if ($maxNum === 0) {
            $count = Book::where(function ($q) {
                $q->where('publisher_id', 2)->orWhereNull('publisher_id');
            })->count();
            $maxNum = max(0, $count);
        }

        $next = $maxNum + 1;
        return sprintf('IP%03d', $next);
    }

    /**
     * Generate next available General Catalog SKU (গণ সিরিয়াল / Global SKU e.g. 'BK-00001')
     */
    public static function generateNextGeneralSku(): string
    {
        $allSkus = Book::where('sku', 'like', 'BK-%')->pluck('sku');
        $maxNum = 0;
        foreach ($allSkus as $sku) {
            if (preg_match('/^BK-(\d+)$/i', trim($sku), $matches)) {
                $num = (int) $matches[1];
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }

        if ($maxNum === 0) {
            $count = Book::count();
            $maxNum = max(0, $count);
        }

        $next = $maxNum + 1;
        return sprintf('BK-%05d', $next);
    }

    /**
     * Unified serial resolver based on publisher
     */
    public static function generateNextSerial(?int $publisherId = null): string
    {
        $isIdeaProkashon = ($publisherId === 2 || empty($publisherId));
        if ($isIdeaProkashon) {
            return self::generateNextIdeaSerial();
        }

        $prefix = 'PUB' . (int)$publisherId . '-';
        $allSkus = Book::where('publisher_id', $publisherId)
            ->where('sku', 'like', $prefix . '%')
            ->pluck('sku');

        $maxNum = 0;
        foreach ($allSkus as $sku) {
            if (preg_match('/^PUB\d+-(\d+)$/i', trim($sku), $matches)) {
                $num = (int) $matches[1];
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }

        $nextNum = $maxNum + 1;
        return sprintf('%s%03d', $prefix, $nextNum);
    }

    /**
     * Auto-assign missing General SKUs (গণ সিরিয়াল) and Idea Publication Serial numbers (IP-XXX)
     */
    public static function assignMissingSerials(): int
    {
        $updated = 0;

        // 1. Process Idea Prokashon books for dedicated idea_serial_no
        $ideaBooks = Book::where(function ($q) {
            $q->where('publisher_id', 2)->orWhereNull('publisher_id');
        })
        ->orderBy('id')
        ->get();

        $ideaSerialCounter = 1;
        foreach ($ideaBooks as $book) {
            $changed = false;
            $ideaSerial = sprintf('IP%03d', $ideaSerialCounter);
            
            if (empty($book->idea_serial_no) || preg_match('/^IP-?\d+$/i', $book->idea_serial_no)) {
                if ($book->idea_serial_no !== $ideaSerial) {
                    $book->idea_serial_no = $ideaSerial;
                    $changed = true;
                }
            }

            // General SKU
            if (empty($book->sku) || str_starts_with($book->sku, 'IP-') || str_starts_with($book->sku, 'IP')) {
                $book->sku = sprintf('BK-%05d', $book->id);
                $changed = true;
            }

            if ($changed) {
                $book->saveQuietly();
                $updated++;
            }
            $ideaSerialCounter++;
        }

        // 2. Process other publisher books
        $otherBooks = Book::where('publisher_id', '!=', 2)
            ->whereNotNull('publisher_id')
            ->orderBy('id')
            ->get();

        foreach ($otherBooks as $book) {
            $changed = false;
            if (empty($book->sku)) {
                $book->sku = sprintf('BK-%05d', $book->id);
                $changed = true;
            }
            if ($changed) {
                $book->saveQuietly();
                $updated++;
            }
        }

        return $updated;
    }

    /**
     * Generate an ultra-clean, vector SVG Code 128 Barcode.
     */
    public static function generateCode128Svg(string $text, int $height = 50, float $barWidth = 2.0, bool $showText = true): string
    {
        $cleanText = trim($text);
        if ($cleanText === '') {
            $cleanText = 'IDEA-000';
        }

        // Encode using Code 128 Set B (standard ASCII 32-126)
        $codes = [104]; // START B (104)
        $checksum = 104;

        $len = strlen($cleanText);
        for ($i = 0; $i < $len; $i++) {
            $char = $cleanText[$i];
            $ascii = ord($char);
            $codeValue = $ascii - 32;
            if ($codeValue < 0 || $codeValue > 95) {
                $codeValue = 0; // Fallback to space
            }
            $codes[] = $codeValue;
            $checksum += $codeValue * ($i + 1);
        }

        $checkDigit = $checksum % 103;
        $codes[] = $checkDigit;
        $codes[] = 106; // STOP (106)

        // Calculate total module width
        $totalModules = 0;
        $patternSequence = [];
        foreach ($codes as $c) {
            $pattern = self::CODE128_PATTERNS[$c] ?? '212222';
            $patternSequence[] = $pattern;
            $totalModules += array_sum(str_split($pattern));
        }

        // Add quiet zones (10 modules each side)
        $quietModules = 10;
        $svgWidth = ($totalModules + ($quietModules * 2)) * $barWidth;
        $textHeight = $showText ? 16 : 0;
        $svgHeight = $height + $textHeight + 6;

        $x = $quietModules * $barWidth;
        $rects = [];

        foreach ($patternSequence as $pattern) {
            $isBar = true;
            $pLen = strlen($pattern);
            for ($j = 0; $j < $pLen; $j++) {
                $w = (int)$pattern[$j] * $barWidth;
                if ($isBar) {
                    $rects[] = sprintf('<rect x="%.2f" y="4" width="%.2f" height="%d" fill="#0f172a" />', $x, $w, $height);
                }
                $x += $w;
                $isBar = !$isBar;
            }
        }

        $rectsSvg = implode("\n    ", $rects);
        $textSvg = '';
        if ($showText) {
            $textY = $height + 17;
            $safeText = htmlspecialchars($cleanText, ENT_XML1, 'UTF-8');
            $textSvg = sprintf(
                '<text x="50%%" y="%d" text-anchor="middle" font-family="Consolas, Monaco, monospace" font-size="12" font-weight="700" fill="#0f172a" letter-spacing="1">%s</text>',
                $textY,
                $safeText
            );
        }

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %.2f %d" width="100%%" height="100%%" style="background:#ffffff; border-radius:4px; max-width:%.2fpx; display:inline-block; vertical-align:middle;">
    %s
    %s
</svg>',
            $svgWidth,
            $svgHeight,
            $svgWidth,
            $rectsSvg,
            $textSvg
        );
    }

    /**
     * Generate an inline Data URI / SVG for a QR Code.
     */
    public static function generateQrCodeSvg(string $data, int $size = 120): string
    {
        $safeData = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return sprintf(
            '<div class="idea-qr-container d-inline-block position-relative" data-qr-payload="%s" style="width:%dpx; height:%dpx;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="%d" height="%d" class="idea-qr-svg" style="background:#ffffff; padding:4px; border-radius:6px; border:1px solid #e2e8f0;">
                    <!-- QR Finder Patterns -->
                    <rect width="100" height="100" fill="#ffffff"/>
                    <!-- Top-Left Finder -->
                    <rect x="6" y="6" width="28" height="28" fill="#0f172a" rx="4"/>
                    <rect x="10" y="10" width="20" height="20" fill="#ffffff" rx="2"/>
                    <rect x="14" y="14" width="12" height="12" fill="#0f172a" rx="2"/>
                    <!-- Top-Right Finder -->
                    <rect x="66" y="6" width="28" height="28" fill="#0f172a" rx="4"/>
                    <rect x="70" y="10" width="20" height="20" fill="#ffffff" rx="2"/>
                    <rect x="74" y="14" width="12" height="12" fill="#0f172a" rx="2"/>
                    <!-- Bottom-Left Finder -->
                    <rect x="6" y="66" width="28" height="28" fill="#0f172a" rx="4"/>
                    <rect x="10" y="70" width="20" height="20" fill="#ffffff" rx="2"/>
                    <rect x="14" y="74" width="12" height="12" fill="#0f172a" rx="2"/>
                    <!-- Matrix Payload Dots -->
                    <g fill="#0f172a">
                        <rect x="38" y="10" width="4" height="4"/><rect x="46" y="10" width="4" height="4"/><rect x="54" y="10" width="4" height="4"/>
                        <rect x="38" y="18" width="4" height="4"/><rect x="50" y="18" width="4" height="4"/><rect x="58" y="18" width="4" height="4"/>
                        <rect x="38" y="26" width="4" height="4"/><rect x="42" y="26" width="4" height="4"/><rect x="54" y="26" width="4" height="4"/>
                        <!-- Center Data Matrix -->
                        <rect x="10" y="38" width="4" height="4"/><rect x="18" y="38" width="4" height="4"/><rect x="26" y="38" width="4" height="4"/><rect x="34" y="38" width="4" height="4"/><rect x="42" y="38" width="4" height="4"/><rect x="50" y="38" width="4" height="4"/><rect x="58" y="38" width="4" height="4"/><rect x="66" y="38" width="4" height="4"/><rect x="74" y="38" width="4" height="4"/><rect x="82" y="38" width="4" height="4"/>
                        <rect x="10" y="46" width="4" height="4"/><rect x="22" y="46" width="4" height="4"/><rect x="38" y="46" width="4" height="4"/><rect x="46" y="46" width="4" height="4"/><rect x="62" y="46" width="4" height="4"/><rect x="70" y="46" width="4" height="4"/><rect x="86" y="46" width="4" height="4"/>
                        <rect x="14" y="54" width="4" height="4"/><rect x="30" y="54" width="4" height="4"/><rect x="42" y="54" width="4" height="4"/><rect x="54" y="54" width="4" height="4"/><rect x="66" y="54" width="4" height="4"/><rect x="78" y="54" width="4" height="4"/>
                        <!-- Bottom-Right Matrix -->
                        <rect x="38" y="66" width="4" height="4"/><rect x="46" y="66" width="4" height="4"/><rect x="58" y="66" width="4" height="4"/><rect x="70" y="66" width="4" height="4"/><rect x="82" y="66" width="4" height="4"/>
                        <rect x="42" y="74" width="4" height="4"/><rect x="54" y="74" width="4" height="4"/><rect x="66" y="74" width="4" height="4"/><rect x="74" y="74" width="4" height="4"/><rect x="86" y="74" width="4" height="4"/>
                        <rect x="38" y="82" width="4" height="4"/><rect x="50" y="82" width="4" height="4"/><rect x="62" y="82" width="4" height="4"/><rect x="78" y="82" width="4" height="4"/><rect x="86" y="82" width="4" height="4"/>
                    </g>
                </svg>
            </div>',
            $safeData,
            $size,
            $size,
            $size,
            $size
        );
    }
}
