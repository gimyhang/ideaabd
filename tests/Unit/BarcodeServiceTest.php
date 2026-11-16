<?php

namespace Tests\Unit;

use App\Services\BarcodeService;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Round-trip tests: every symbol is decoded again from the bars the service draws, so these
 * fail if the encoder produces something a real scanner would not read back correctly.
 */
class BarcodeServiceTest extends TestCase
{
    /** @return array<string,int> pattern => symbol value */
    private function code128Table(): array
    {
        $patterns = (new ReflectionClass(BarcodeService::class))->getConstant('CODE128_PATTERNS');

        return array_flip($patterns);
    }

    /** Decode Code 128 element widths back to text, verifying start, checksum and stop. */
    private function decode128(string $text): string
    {
        $sym = BarcodeService::code128Symbols($text);
        $table = $this->code128Table();

        $values = [];
        foreach ($sym['widths'] as $w) {
            $this->assertArrayHasKey($w, $table, "unknown bar pattern $w");
            $values[] = $table[$w];
        }

        $this->assertSame(106, array_pop($values), 'must end with STOP');
        $check = array_pop($values);
        $sum = $values[0];
        foreach ($values as $pos => $v) {
            if ($pos > 0) {
                $sum += $v * $pos;
            }
        }
        $this->assertSame($sum % 103, $check, 'checksum mismatch');

        $set = match (array_shift($values)) {
            104 => 'B',
            105 => 'C',
            default => $this->fail('bad start code'),
        };
        $out = '';
        foreach ($values as $v) {
            if ($v === 99 && $set === 'B') { $set = 'C'; continue; }
            if ($v === 100 && $set === 'C') { $set = 'B'; continue; }
            $out .= $set === 'C' ? sprintf('%02d', $v) : chr($v + 32);
        }

        return $out;
    }

    /** @return array<string,array{0:string}> */
    public static function code128Samples(): array
    {
        $samples = [
            'BK-00012', 'IP001', 'IP-NEW', '9780306406157', '123', '1234', '12345', '123456', '1234567',
            'AB123456CD', 'A1234', '1234B', 'X12345678Y', '0', 'PUB3-001', 'Hello, World!~', ' spaced ', '12ab34', '00',
            '98765432109876543210',
        ];

        return array_combine($samples, array_map(fn ($s) => [$s], $samples));
    }

    /** @dataProvider code128Samples */
    public function test_code128_round_trips(string $text): void
    {
        $this->assertSame(trim($text), $this->decode128($text));
    }

    public function test_code128_uses_set_c_for_digit_runs_so_isbns_are_compact(): void
    {
        $isbn = BarcodeService::code128Symbols('9780306406157');
        $letters = BarcodeService::code128Symbols('ABCDEFGHIJKLM');

        $this->assertContains(99, $isbn['codes'], 'a long digit run must switch to Set C');
        $this->assertSame(105, BarcodeService::code128Symbols('978030640615')['codes'][0], 'an even digit run starts directly in Set C');
        $this->assertLessThan($letters['modules'], $isbn['modules']);
    }

    public function test_code128_reports_characters_it_cannot_encode(): void
    {
        $enc = BarcodeService::code128Encode('IP০০১-A');

        $this->assertSame('IP-A', $enc['clean']);
        $this->assertSame(['০', '০', '১'], $enc['dropped']);
        $this->assertSame([], BarcodeService::code128Encode('বাংলা')['codes']);
    }

    public function test_code128_svg_is_crisp_and_has_quiet_zone(): void
    {
        $svg = BarcodeService::buildCode128Svg('IP001', ['module' => 2, 'responsive' => false]);

        $this->assertStringContainsString('shape-rendering="crispEdges"', $svg);
        $this->assertMatchesRegularExpression('/width="\d+(\.\d+)?" height="\d+(\.\d+)?"/', $svg);
        // First bar starts after a 10-module quiet zone (20px at module 2).
        $this->assertStringContainsString('M20 ', $svg);
    }

    public function test_isbn_normalisation(): void
    {
        $this->assertSame('9780306406157', BarcodeService::normalizeIsbn('978-0-306-40615-7'));
        $this->assertSame('9780306406157', BarcodeService::normalizeIsbn('0-306-40615-2'));
        $this->assertSame('9780306406157', BarcodeService::normalizeIsbn('0306406152'));
        $this->assertSame('9780804429573', BarcodeService::normalizeIsbn('080442957X'));
        $this->assertNull(BarcodeService::normalizeIsbn('9780306406158'));
        $this->assertNull(BarcodeService::normalizeIsbn('1234'));
        $this->assertNull(BarcodeService::normalizeIsbn(''));
        $this->assertNull(BarcodeService::normalizeIsbn(null));
    }

    /** @return array<string,array{0:string}> */
    public static function eanSamples(): array
    {
        return [
            'dune-like' => ['9780306406157'],
            'oxford' => ['9780198526636'],
            'zero lead' => ['0012345678905'],
            'bangla book' => ['9789847011493'],
        ];
    }

    /** @dataProvider eanSamples */
    public function test_ean13_round_trips(string $digits): void
    {
        // Fix up the sample so its check digit is valid, then decode the 95 modules back.
        $digits = substr($digits, 0, 12) . BarcodeService::ean13CheckDigit(substr($digits, 0, 12));
        $bits = BarcodeService::ean13Modules($digits);
        $this->assertSame(95, strlen($bits));
        $this->assertSame('101', substr($bits, 0, 3));
        $this->assertSame('01010', substr($bits, 45, 5));
        $this->assertSame('101', substr($bits, 92, 3));

        $ref = new ReflectionClass(BarcodeService::class);
        $L = $ref->getConstant('EAN_L');
        $G = $ref->getConstant('EAN_G');
        $R = $ref->getConstant('EAN_R');
        $parities = $ref->getConstant('EAN_PARITY');

        $left = [];
        $parity = '';
        for ($i = 0; $i < 6; $i++) {
            $chunk = substr($bits, 3 + $i * 7, 7);
            if (($d = array_search($chunk, $L, true)) !== false) { $parity .= 'L'; $left[] = $d; }
            elseif (($d = array_search($chunk, $G, true)) !== false) { $parity .= 'G'; $left[] = $d; }
            else { $this->fail("bad left chunk $chunk"); }
        }
        $first = array_search($parity, $parities, true);
        $this->assertNotFalse($first);

        $right = [];
        for ($i = 0; $i < 6; $i++) {
            $d = array_search(substr($bits, 50 + $i * 7, 7), $R, true);
            $this->assertNotFalse($d);
            $right[] = $d;
        }

        $this->assertSame($digits, $first . implode('', $left) . implode('', $right));
        $this->assertTrue(BarcodeService::isValidEan13($digits));
    }

    public function test_ean13_rejects_an_invalid_number(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        BarcodeService::ean13Modules('9780306406158');
    }

    public function test_barcode_source_priority_and_explicit_choice(): void
    {
        $this->assertSame(['BK-1', 'sku'], BarcodeService::resolveBarcodeValue('BK-1', '978', 'IP001'));
        $this->assertSame(['978', 'isbn'], BarcodeService::resolveBarcodeValue('', '978', 'IP001'));
        $this->assertSame(['IP001', 'serial'], BarcodeService::resolveBarcodeValue('', '', 'IP001'));
        $this->assertSame(['IP001', 'serial'], BarcodeService::resolveBarcodeValue('BK-1', '978', 'IP001', 'serial'));
        $this->assertSame(['', 'isbn'], BarcodeService::resolveBarcodeValue('BK-1', '', 'IP001', 'isbn'));
        $this->assertSame(['', 'auto'], BarcodeService::resolveBarcodeValue(null, null, null));
    }

    public function test_qr_matrix_is_a_valid_symbol_with_a_four_module_quiet_zone(): void
    {
        $rows = BarcodeService::qrMatrix('https://www.ideaabd.com/books/x?serial=IP001', 'M');
        $n = count($rows);
        $this->assertSame(0, ($n - 17) % 4, 'size must be 17 + 4 * version');

        // Finder pattern: 7x7 ring with a 3x3 core in three corners.
        foreach ([[0, 0], [$n - 7, 0], [0, $n - 7]] as [$x, $y]) {
            for ($dy = 0; $dy < 7; $dy++) {
                for ($dx = 0; $dx < 7; $dx++) {
                    $ring = $dx === 0 || $dx === 6 || $dy === 0 || $dy === 6;
                    $core = $dx >= 2 && $dx <= 4 && $dy >= 2 && $dy <= 4;
                    $this->assertSame(($ring || $core) ? 1 : 0, $rows[$y + $dy][$x + $dx], "finder at $x,$y");
                }
            }
        }

        $built = BarcodeService::buildQrSvg('https://www.ideaabd.com/books/x', 'M', 4);
        $this->assertSame($built['modules'] + 8, $built['total']);
        $this->assertStringContainsString('viewBox="0 0 ' . $built['total'] . ' ' . $built['total'] . '"', $built['svg']);
    }

    public function test_higher_error_correction_makes_a_denser_qr(): void
    {
        $url = 'https://www.ideaabd.com/books/some-book?serial=IP001&isbn=9780306406157';
        $l = count(BarcodeService::qrMatrix($url, 'L'));
        $h = count(BarcodeService::qrMatrix($url, 'H'));

        $this->assertGreaterThan($l, $h);
    }
}
