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

    // ══════════════════════════════════════════════════════════════════════════
    // Identifier helpers
    // ══════════════════════════════════════════════════════════════════════════

    /** EAN-13 / ISBN-13 check digit for the first 12 digits. */
    public static function ean13CheckDigit(string $digits12): int
    {
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int) $digits12[$i] * ($i % 2 === 0 ? 1 : 3);
        }

        return (10 - ($sum % 10)) % 10;
    }

    public static function isValidEan13(string $digits): bool
    {
        return (bool) preg_match('/^\d{13}$/', $digits)
            && self::ean13CheckDigit(substr($digits, 0, 12)) === (int) $digits[12];
    }

    /**
     * Normalise an ISBN to a valid 13-digit number (hyphens/spaces ignored, ISBN-10 upgraded
     * with the 978 prefix). Returns null when it is not a valid ISBN.
     */
    public static function normalizeIsbn(?string $isbn): ?string
    {
        $s = strtoupper((string) preg_replace('/[\s\-]/', '', (string) $isbn));

        if (preg_match('/^\d{13}$/', $s)) {
            return self::isValidEan13($s) ? $s : null;
        }

        if (preg_match('/^\d{9}[\dX]$/', $s)) {
            $sum = 0;
            for ($i = 0; $i < 10; $i++) {
                $sum += ($s[$i] === 'X' ? 10 : (int) $s[$i]) * (10 - $i);
            }
            if ($sum % 11 !== 0) {
                return null;
            }
            $core = '978' . substr($s, 0, 9);

            return $core . self::ean13CheckDigit($core);
        }

        return null;
    }

    /**
     * Pick the value a barcode encodes. "auto" keeps the form's long-standing order:
     * SKU, then ISBN, then Idea serial. Returns [value, source] (value '' when nothing to encode).
     *
     * @return array{0:string,1:string}
     */
    public static function resolveBarcodeValue(?string $sku, ?string $isbn, ?string $serial, string $source = 'auto'): array
    {
        $map = [
            'sku' => trim((string) $sku),
            'isbn' => trim((string) $isbn),
            'serial' => trim((string) $serial),
        ];

        if (isset($map[$source])) {
            return [$map[$source], $source];
        }

        foreach ($map as $key => $value) {
            if ($value !== '') {
                return [$value, $key];
            }
        }

        return ['', 'auto'];
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Code 128 (Set B + automatic Set C for digit runs)
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Encode text as Code 128 symbol values. Characters outside printable ASCII cannot be
     * represented and are reported back instead of being silently replaced.
     *
     * @return array{codes:int[],clean:string,dropped:string[]}
     */
    public static function code128Encode(string $text): array
    {
        $clean = '';
        $dropped = [];
        foreach (preg_split('//u', trim($text), -1, PREG_SPLIT_NO_EMPTY) ?: [] as $ch) {
            if (strlen($ch) === 1 && ord($ch) >= 32 && ord($ch) <= 126) {
                $clean .= $ch;
            } else {
                $dropped[] = $ch;
            }
        }

        $n = strlen($clean);
        if ($n === 0) {
            return ['codes' => [], 'clean' => '', 'dropped' => $dropped];
        }

        $codes = [];
        $mode = null; // 'B' | 'C'
        $i = 0;
        while ($i < $n) {
            $run = 0;
            while ($i + $run < $n && ctype_digit($clean[$i + $run])) {
                $run++;
            }

            if ($mode === 'C') {
                if ($run >= 2) {
                    $codes[] = (int) substr($clean, $i, 2);
                    $i += 2;
                    continue;
                }
                $codes[] = 100; // CODE B
                $mode = 'B';
            } else {
                // Set C packs two digits per symbol: worth it for 4+ digits at the edges, 6+ inside.
                if ($run >= 4 && ($i === 0 || $i + $run === $n || $run >= 6)) {
                    if ($run % 2 === 1) {
                        if ($mode === null) {
                            $codes[] = 104; // START B
                            $mode = 'B';
                        }
                        $codes[] = ord($clean[$i]) - 32;
                        $i++;
                    }
                    $codes[] = $mode === null ? 105 : 99; // START C or CODE C
                    $mode = 'C';
                    continue;
                }
            }

            if ($mode === null) {
                $codes[] = 104; // START B
                $mode = 'B';
            }
            $codes[] = ord($clean[$i]) - 32;
            $i++;
        }

        return ['codes' => $codes, 'clean' => $clean, 'dropped' => $dropped];
    }

    /**
     * Full symbol sequence including checksum and STOP, as element-width strings.
     *
     * @return array{codes:int[],widths:string[],modules:int,clean:string,dropped:string[]}
     */
    public static function code128Symbols(string $text): array
    {
        $enc = self::code128Encode($text);
        if ($enc['codes'] === []) {
            return ['codes' => [], 'widths' => [], 'modules' => 0, 'clean' => '', 'dropped' => $enc['dropped']];
        }

        $sum = $enc['codes'][0];
        foreach ($enc['codes'] as $pos => $value) {
            if ($pos > 0) {
                $sum += $value * $pos;
            }
        }
        $codes = $enc['codes'];
        $codes[] = $sum % 103;
        $codes[] = 106; // STOP

        $widths = [];
        $modules = 0;
        foreach ($codes as $c) {
            $widths[] = self::CODE128_PATTERNS[$c];
            $modules += array_sum(str_split(self::CODE128_PATTERNS[$c]));
        }

        return ['codes' => $codes, 'widths' => $widths, 'modules' => $modules, 'clean' => $enc['clean'], 'dropped' => $enc['dropped']];
    }

    /**
     * Build a Code 128 SVG. Geometry is in modules scaled by $module, so a whole-number
     * $module gives pixel-exact bars. Quiet zone is 10 modules per side (per the spec).
     *
     * @param  array{module?:float,height?:float,showText?:bool,responsive?:bool,color?:string,background?:string} $opts
     */
    public static function buildCode128Svg(string $text, array $opts = []): string
    {
        $module = (float) ($opts['module'] ?? 1.0);
        $height = (float) ($opts['height'] ?? 50.0);
        $showText = (bool) ($opts['showText'] ?? true);
        $responsive = (bool) ($opts['responsive'] ?? true);
        $color = (string) ($opts['color'] ?? '#000000');
        $background = (string) ($opts['background'] ?? '#ffffff');

        $sym = self::code128Symbols($text === '' ? 'IDEA-000' : $text);
        if ($sym['widths'] === []) {
            $sym = self::code128Symbols('IDEA-000');
        }

        $quiet = 10 * $module;
        $width = $sym['modules'] * $module + 2 * $quiet;
        $top = 4 * $module;
        $textBlock = $showText ? 14 * $module : 0;
        $totalHeight = $top + $height + $textBlock + ($showText ? 2 * $module : 4 * $module);

        $x = $quiet;
        $bars = '';
        foreach ($sym['widths'] as $pattern) {
            $isBar = true;
            foreach (str_split($pattern) as $w) {
                $wpx = (int) $w * $module;
                if ($isBar) {
                    $bars .= sprintf('M%s %sh%sv%sh-%sz', self::n($x), self::n($top), self::n($wpx), self::n($height), self::n($wpx));
                }
                $x += $wpx;
                $isBar = !$isBar;
            }
        }

        $label = '';
        if ($showText) {
            $label = sprintf(
                '<text x="%s" y="%s" text-anchor="middle" font-family="Consolas, Menlo, Monaco, monospace" font-size="%s" font-weight="700" fill="%s" letter-spacing="%s">%s</text>',
                self::n($width / 2),
                self::n($top + $height + 11 * $module),
                self::n(9 * $module),
                $color,
                self::n(0.6 * $module),
                htmlspecialchars($sym['clean'], ENT_XML1, 'UTF-8')
            );
        }

        $size = $responsive
            ? 'width="100%" height="100%" style="max-width:' . self::n($width) . 'px; display:inline-block; vertical-align:middle;"'
            : 'width="' . self::n($width) . '" height="' . self::n($totalHeight) . '"';

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %s %s" %s role="img" aria-label="Code 128 barcode %s" shape-rendering="crispEdges"><rect width="%s" height="%s" fill="%s"/><path d="%s" fill="%s"/>%s</svg>',
            self::n($width),
            self::n($totalHeight),
            $size,
            htmlspecialchars($sym['clean'], ENT_QUOTES, 'UTF-8'),
            self::n($width),
            self::n($totalHeight),
            $background,
            $bars,
            $color,
            $label
        );
    }

    /**
     * Backwards compatible entry point used by labels and the book data endpoint.
     */
    public static function generateCode128Svg(string $text, int $height = 50, float $barWidth = 2.0, bool $showText = true): string
    {
        return self::buildCode128Svg($text, [
            'module' => $barWidth,
            'height' => (float) $height,
            'showText' => $showText,
            'responsive' => true,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // EAN-13 (the retail barcode printed on books for the ISBN)
    // ══════════════════════════════════════════════════════════════════════════

    private const EAN_L = ['0001101', '0011001', '0010011', '0111101', '0100011', '0110001', '0101111', '0111011', '0110111', '0001011'];
    private const EAN_G = ['0100111', '0110011', '0011011', '0100001', '0011101', '0111001', '0000101', '0010001', '0001001', '0010111'];
    private const EAN_R = ['1110010', '1100110', '1101100', '1000010', '1011100', '1001110', '1010000', '1000100', '1001000', '1110100'];
    private const EAN_PARITY = ['LLLLLL', 'LLGLGG', 'LLGGLG', 'LLGGGL', 'LGLLGG', 'LGGLLG', 'LGGGLL', 'LGLGLG', 'LGLGGL', 'LGGLGL'];

    /** The 95-module bar pattern of an EAN-13 ('1' = bar). */
    public static function ean13Modules(string $digits13): string
    {
        if (!self::isValidEan13($digits13)) {
            throw new \InvalidArgumentException('Not a valid EAN-13 number.');
        }

        $parity = self::EAN_PARITY[(int) $digits13[0]];
        $bits = '101';
        for ($i = 0; $i < 6; $i++) {
            $d = (int) $digits13[$i + 1];
            $bits .= $parity[$i] === 'L' ? self::EAN_L[$d] : self::EAN_G[$d];
        }
        $bits .= '01010';
        for ($i = 7; $i < 13; $i++) {
            $bits .= self::EAN_R[(int) $digits13[$i]];
        }

        return $bits . '101';
    }

    /**
     * @param  array{module?:float,height?:float,showText?:bool,responsive?:bool,color?:string,background?:string} $opts
     */
    public static function buildEan13Svg(string $digits13, array $opts = []): string
    {
        $bits = self::ean13Modules($digits13);
        $module = (float) ($opts['module'] ?? 1.0);
        $height = (float) ($opts['height'] ?? 58.0);
        $showText = (bool) ($opts['showText'] ?? true);
        $responsive = (bool) ($opts['responsive'] ?? true);
        $color = (string) ($opts['color'] ?? '#000000');
        $background = (string) ($opts['background'] ?? '#ffffff');

        $quietL = 11 * $module;
        $quietR = 9 * $module;
        $width = 95 * $module + $quietL + $quietR;
        $top = 4 * $module;
        $guardExtra = $showText ? 5 * $module : 0;
        $totalHeight = $top + $height + $guardExtra + 4 * $module + ($showText ? 6 * $module : 0);

        // Guard bars (start, centre, end) hang below the normal bars like on printed books.
        $isGuard = static fn (int $m): bool => $m < 3 || ($m >= 45 && $m < 50) || $m >= 92;

        $path = '';
        $m = 0;
        $len = strlen($bits);
        while ($m < $len) {
            if ($bits[$m] !== '1') {
                $m++;
                continue;
            }
            $start = $m;
            $guard = $isGuard($m);
            while ($m < $len && $bits[$m] === '1' && $isGuard($m) === $guard) {
                $m++;
            }
            $h = $height + ($guard ? $guardExtra : 0);
            $w = ($m - $start) * $module;
            $path .= sprintf('M%s %sh%sv%sh-%sz', self::n($quietL + $start * $module), self::n($top), self::n($w), self::n($h), self::n($w));
        }

        $label = '';
        if ($showText) {
            $y = $top + $height + $guardExtra + 2 * $module;
            $attrs = 'font-family="Consolas, Menlo, Monaco, monospace" font-size="' . self::n(10 * $module) . '" font-weight="700" fill="' . $color . '"';
            $label .= sprintf('<text x="%s" y="%s" text-anchor="middle" %s>%s</text>', self::n($quietL - 5.5 * $module), self::n($y), $attrs, $digits13[0]);
            $label .= sprintf('<text x="%s" y="%s" text-anchor="middle" letter-spacing="%s" %s>%s</text>', self::n($quietL + 24 * $module), self::n($y), self::n(1.2 * $module), $attrs, substr($digits13, 1, 6));
            $label .= sprintf('<text x="%s" y="%s" text-anchor="middle" letter-spacing="%s" %s>%s</text>', self::n($quietL + 71 * $module), self::n($y), self::n(1.2 * $module), $attrs, substr($digits13, 7, 6));
        }

        $size = $responsive
            ? 'width="100%" height="100%" style="max-width:' . self::n($width) . 'px; display:inline-block; vertical-align:middle;"'
            : 'width="' . self::n($width) . '" height="' . self::n($totalHeight) . '"';

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %s %s" %s role="img" aria-label="EAN-13 barcode %s" shape-rendering="crispEdges"><rect width="%s" height="%s" fill="%s"/><path d="%s" fill="%s"/>%s</svg>',
            self::n($width),
            self::n($totalHeight),
            $size,
            $digits13,
            self::n($width),
            self::n($totalHeight),
            $background,
            $path,
            $color,
            $label
        );
    }

    /** Compact number formatting for SVG coordinates. */
    private static function n(float $v): string
    {
        $s = rtrim(rtrim(number_format($v, 3, '.', ''), '0'), '.');

        return $s === '' ? '0' : $s;
    }

    /**
     * Build unified mobile-scannable product URL payload incorporating serial, isbn and product URL.
     * The slug is percent-encoded so the payload is plain ASCII (Bengali slugs included), which
     * every scanner reads without character-set guessing.
     */
    public static function buildBookQrPayload(?string $slug = null, ?string $serial = null, ?string $isbn = null, $bookId = null): string
    {
        // A saved book is always addressed by its number: /books/450 (the page redirects to the name-based address).
        if ($bookId) {
            return url('/books/' . (int) $bookId);
        }

        $slug = trim((string) $slug);
        $path = $slug !== '' ? ('/books/' . rawurlencode($slug)) : ($bookId ? ('/books/' . $bookId) : '/books');
        $params = [];
        $cleanSerial = trim((string)$serial);
        $cleanIsbn = trim((string)$isbn);
        if ($cleanSerial !== '') {
            $params['serial'] = $cleanSerial;
        }
        if ($cleanIsbn !== '') {
            $params['isbn'] = $cleanIsbn;
        }
        $query = !empty($params) ? ('?' . http_build_query($params)) : '';
        return url($path . $query);
    }

    /**
     * Generate a self-contained Base64 SVG Data URI for QR code (perfect for html2canvas & zero-CORS).
     */
    public static function generateQrCodeDataUri(string $data, int $size = 120): string
    {
        try {
            if (class_exists(\BaconQrCode\Writer::class)) {
                $renderer = new \BaconQrCode\Renderer\ImageRenderer(
                    new \BaconQrCode\Renderer\RendererStyle\RendererStyle(max(60, $size), 1),
                    new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
                );
                $writer = new \BaconQrCode\Writer($renderer);
                $svg = $writer->writeString($data);
                $svg = preg_replace('/<\?xml[^\>]*\?>/i', '', $svg);
                return 'data:image/svg+xml;base64,' . base64_encode(trim($svg));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("BaconQrCode data URI generation failed: " . $e->getMessage());
        }

        return 'https://api.qrserver.com/v1/create-qr-code/?size=' . ($size * 2) . 'x' . ($size * 2) . '&data=' . urlencode($data);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // QR code
    // ══════════════════════════════════════════════════════════════════════════

    /** @return array{0:\BaconQrCode\Common\ErrorCorrectionLevel,1:string} */
    private static function qrLevel(string $ecc): array
    {
        $ecc = strtoupper($ecc);
        $level = match ($ecc) {
            'L' => \BaconQrCode\Common\ErrorCorrectionLevel::L(),
            'Q' => \BaconQrCode\Common\ErrorCorrectionLevel::Q(),
            'H' => \BaconQrCode\Common\ErrorCorrectionLevel::H(),
            default => \BaconQrCode\Common\ErrorCorrectionLevel::M(),
        };

        return [$level, in_array($ecc, ['L', 'M', 'Q', 'H'], true) ? $ecc : 'M'];
    }

    /**
     * The QR module matrix as rows of 0/1 (no quiet zone).
     *
     * @return int[][]
     */
    public static function qrMatrix(string $data, string $ecc = 'M'): array
    {
        [$level] = self::qrLevel($ecc);
        // Plain ASCII needs no ECI header; anything else is sent as UTF-8.
        $encoding = preg_match('/^[\x20-\x7E]*$/', $data) ? 'ISO-8859-1' : 'UTF-8';
        $matrix = \BaconQrCode\Encoder\Encoder::encode($data, $level, $encoding)->getMatrix();

        $rows = [];
        for ($y = 0; $y < $matrix->getHeight(); $y++) {
            $row = [];
            for ($x = 0; $x < $matrix->getWidth(); $x++) {
                $row[] = $matrix->get($x, $y) ? 1 : 0;
            }
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Crisp, scanner-friendly QR SVG: one path in module units, a quiet zone of $margin modules
     * (the spec asks for 4) and pure black on white. A whole-number $pixelsPerModule keeps
     * every module pixel-exact when the SVG is rasterised.
     *
     * @return array{svg:string,modules:int,total:int}
     */
    public static function buildQrSvg(string $data, string $ecc = 'M', int $margin = 4, ?float $pixelsPerModule = null, bool $responsive = true): array
    {
        $rows = self::qrMatrix($data, $ecc);
        $n = count($rows);
        $total = $n + 2 * $margin;

        $path = '';
        foreach ($rows as $y => $row) {
            $x = 0;
            while ($x < $n) {
                if ($row[$x] === 0) {
                    $x++;
                    continue;
                }
                $start = $x;
                while ($x < $n && $row[$x] === 1) {
                    $x++;
                }
                $path .= sprintf('M%d %dh%dv1h-%dz', $start + $margin, $y + $margin, $x - $start, $x - $start);
            }
        }

        $px = $pixelsPerModule !== null ? self::n($total * $pixelsPerModule) : null;
        $size = ($px !== null && !$responsive)
            ? 'width="' . $px . '" height="' . $px . '"'
            : 'width="100%" height="100%"';

        return [
            'svg' => sprintf(
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %1$d %1$d" %2$s role="img" aria-label="QR code" shape-rendering="crispEdges"><rect width="%1$d" height="%1$d" fill="#ffffff"/><path d="%3$s" fill="#000000"/></svg>',
                $total,
                $size,
                $path
            ),
            'modules' => $n,
            'total' => $total,
        ];
    }

    /**
     * Inline QR (legacy wrapper used by labels and the book data endpoint).
     */
    public static function generateQrCodeSvg(string $data, int $size = 120): string
    {
        $safeData = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

        try {
            $qr = self::buildQrSvg($data, 'M', 2);

            return sprintf(
                '<div class="idea-qr-container d-inline-block position-relative" data-qr-payload="%s" style="width:%dpx; height:%dpx;">%s</div>',
                $safeData,
                $size,
                $size,
                $qr['svg']
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('QR generation failed: ' . $e->getMessage());
        }

        // Never draw a look-alike: a QR that does not scan is worse than a clear message.
        return sprintf(
            '<div class="idea-qr-container d-inline-flex align-items-center justify-content-center text-center text-muted border rounded" data-qr-payload="%s" style="width:%dpx; height:%dpx; font-size:10px;">QR unavailable</div>',
            $safeData,
            $size,
            $size
        );
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Book form: everything the barcode + QR panel needs, from one place
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * @param  array{sku?:?string,isbn?:?string,serial?:?string,slug?:?string,book_id?:int|string|null,source?:?string,ecc?:?string} $in
     * @return array<string,mixed>
     */
    public static function buildCodes(array $in): array
    {
        $source = (string) ($in['source'] ?? 'auto');
        $source = in_array($source, ['auto', 'sku', 'isbn', 'serial'], true) ? $source : 'auto';
        $ecc = strtoupper((string) ($in['ecc'] ?? 'M'));
        $ecc = in_array($ecc, ['L', 'M', 'Q', 'H'], true) ? $ecc : 'M';
        $isbnRaw = trim((string) ($in['isbn'] ?? ''));
        $isbn13 = self::normalizeIsbn($isbnRaw);

        // ── Barcode ────────────────────────────────────────────────────────
        [$value, $usedSource] = self::resolveBarcodeValue($in['sku'] ?? null, $isbnRaw, $in['serial'] ?? null, $source);
        $barcode = ['value' => $value, 'source' => $usedSource, 'format' => null, 'svg' => null, 'warnings' => []];

        if ($value === '') {
            $barcode['warnings'][] = $source === 'auto'
                ? 'Enter a SKU, ISBN or Serial to generate a barcode.'
                : 'The selected ' . strtoupper($source === 'serial' ? 'serial' : $source) . ' is empty.';
        } elseif ($usedSource === 'isbn' && $isbn13 !== null) {
            $barcode['format'] = 'ean13';
            $barcode['value'] = $isbn13;
            $barcode['svg'] = self::buildEan13Svg($isbn13, ['module' => 2.0, 'height' => 116, 'showText' => true, 'responsive' => true]);
            if ($isbn13 !== preg_replace('/[\s\-]/', '', $value)) {
                $barcode['warnings'][] = 'ISBN-10 converted to ISBN-13 (' . $isbn13 . ') for the EAN-13 barcode.';
            }
        } else {
            if ($usedSource === 'isbn') {
                $barcode['warnings'][] = 'This ISBN is not valid (check digit mismatch), so a Code 128 barcode is shown instead of EAN-13.';
            }
            $sym = self::code128Symbols($value);
            if ($sym['widths'] === []) {
                $barcode['value'] = '';
                $barcode['warnings'][] = 'Code 128 supports printable English characters only.';
            } else {
                if ($sym['dropped'] !== []) {
                    $barcode['warnings'][] = 'Not supported in Code 128 and left out: ' . implode(' ', array_unique($sym['dropped'])) . '.';
                }
                $barcode['format'] = 'code128';
                $barcode['value'] = $sym['clean'];
                $barcode['svg'] = self::buildCode128Svg($sym['clean'], ['module' => 2.0, 'height' => 104, 'showText' => true, 'responsive' => true]);
            }
        }

        // ── QR ───────────────────────────────────────────────────────
        // The QR carries only the book's number: https://www.ideaabd.com/books/450. That address never changes,
        // even if the title or slug is edited later; the page redirects to the book's name-based address.
        $bookId = (int) ($in['book_id'] ?? 0);
        $provisional = $bookId <= 0;
        if ($provisional) {
            // Not saved yet: show the number the book is expected to get, but never let it be downloaded as final.
            $bookId = (int) (Book::withTrashed()->max('id') ?? 0) + 1;
        }
        $payload = url('/books/' . $bookId);
        $qr = ['payload' => $payload, 'number' => $bookId, 'provisional' => $provisional, 'ecc' => $ecc, 'svg' => null, 'modules' => null, 'warnings' => []];

        if ($provisional) {
            $qr['warnings'][] = "Preview only: the book number is assigned when you save (expected {$bookId}). Download the QR after saving.";
        }

        try {
            $built = self::buildQrSvg($payload, $ecc, 4);
            $qr['svg'] = $built['svg'];
            $qr['modules'] = $built['modules'];
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('QR generation failed: ' . $e->getMessage());
            $qr['warnings'][] = 'The QR code could not be generated for this link.';
        }

        return [
            'ok' => true,
            'barcode' => $barcode,
            'qr' => $qr,
            'isbn' => ['input' => $isbnRaw, 'valid' => $isbnRaw === '' ? null : $isbn13 !== null, 'isbn13' => $isbn13],
        ];
    }
}
