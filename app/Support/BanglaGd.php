<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Draws Bengali text with GD.
 *
 * GD cannot shape Bengali (vowel signs land after the consonant, conjuncts
 * fall apart). So Bengali runs are converted to the Bijoy layout and drawn
 * with "Kalpurush ANSI", whose glyphs already contain the conjuncts and
 * vowel positions; Latin text and digits are drawn with the Unicode font.
 */
final class BanglaGd
{
    private const HASANTA = "\u{09CD}";

    private const VOWELS = [
        'অ' => 'A', 'আ' => 'Av', 'ই' => 'B', 'ঈ' => 'C', 'উ' => 'D', 'ঊ' => 'E', 'ঋ' => 'F',
        'এ' => 'G', 'ঐ' => 'H', 'ও' => 'I', 'ঔ' => 'J',
    ];

    private const CONSONANTS = [
        'ক' => 'K', 'খ' => 'L', 'গ' => 'M', 'ঘ' => 'N', 'ঙ' => 'O', 'চ' => 'P', 'ছ' => 'Q', 'জ' => 'R', 'ঝ' => 'S', 'ঞ' => 'T',
        'ট' => 'U', 'ঠ' => 'V', 'ড' => 'W', 'ঢ' => 'X', 'ণ' => 'Y', 'ত' => 'Z', 'থ' => '_', 'দ' => '`', 'ধ' => 'a', 'ন' => 'b',
        'প' => 'c', 'ফ' => 'd', 'ব' => 'e', 'ভ' => 'f', 'ম' => 'g', 'য' => 'h', 'র' => 'i', 'ল' => 'j', 'শ' => 'k', 'ষ' => 'l',
        'স' => 'm', 'হ' => 'n', "\u{09DC}" => 'o', "\u{09DD}" => 'p', "\u{09DF}" => 'q', 'ৎ' => 'r',
    ];

    /** Joined consonants that have their own glyph (longest match wins). */
    private const CONJUNCTS = [
        'ক্ষ্ম' => '²', 'ক্ক' => '°', 'ক্ট' => '±', 'ক্ত' => '³', 'ক্ম' => '´', 'ক্র' => 'µ', 'ক্ষ' => '¶', 'ক্স' => '·',
        'গ্ধ' => '»', 'গ্ন' => '¹', 'ঙ্ক' => '¼', 'ঙ্গ' => '½', 'জ্জ' => '¾', 'জ্ঝ' => 'À', 'জ্ঞ' => 'Á',
        'ঞ্চ' => 'Â', 'ঞ্ছ' => 'Ã', 'ঞ্জ' => 'Ä', 'ঞ্ঝ' => 'Å', 'ট্ট' => 'Æ', 'ড্ড' => 'Ç', 'ণ্ট' => 'È', 'ণ্ঠ' => 'É', 'ণ্ড' => 'Ê',
        'ত্ত' => 'Ë', 'ত্থ' => 'Ì', 'ত্ম' => 'Í', 'ত্র' => 'Î', 'দ্দ' => 'Ï', 'দ্ধ' => '×', 'দ্ব' => 'Ø', 'দ্ম' => 'Ù',
        'ন্ঠ' => 'Ú', 'ন্ড' => 'Û', 'ন্ধ' => 'Ü', 'ন্স' => 'Ý', 'প্ট' => 'Þ', 'প্ত' => 'ß', 'প্প' => 'à', 'প্স' => 'á',
        'ব্জ' => 'â', 'ব্দ' => 'ã', 'ব্ধ' => 'ä', 'ভ্র' => 'å', 'ম্ন' => 'æ', 'ম্ফ' => 'ç',
        'ল্ক' => 'é', 'ল্গ' => 'ê', 'ল্ট' => 'ë', 'ল্ড' => 'ì', 'ল্প' => 'í', 'ল্ফ' => 'î',
        'শ্চ' => 'ð', 'শ্ছ' => 'ñ', 'ষ্ণ' => 'ò', 'ষ্ট' => 'ó', 'ষ্ঠ' => 'ô', 'ষ্ফ' => 'õ',
        'স্খ' => 'ö', 'স্ট' => '÷', 'স্ন' => 'ø', 'স্ফ' => 'ù', 'হ্ন' => 'ý', 'হ্ম' => 'þ',
        // built from half forms
        'ন্ত্র' => 'š¿', 'স্ত্র' => '¯¿', 'ন্ত' => 'š—', 'ন্থ' => 'š’', 'ন্দ' => '›`', 'ন্ট' => '›U', 'ন্ন' => 'bœ', 'ন্ব' => 'š^',
        'স্ক' => '¯‹', 'স্ত' => '¯—', 'স্থ' => '¯’', 'স্প' => '¯ú', 'স্ম' => '¯§', 'স্ব' => '¯^', 'স্ল' => '¯¬',
        'ম্প' => '¤ú', 'ম্ব' => '¤^', 'ম্ভ' => '¤¢', 'ম্ম' => '¤§', 'ম্ল' => '¤¬',
        'ষ্ক' => '®‹', 'ষ্প' => '®ú', 'ষ্ম' => '®§', 'চ্চ' => '”P', 'চ্ছ' => '”Q', 'দ্ভ' => '™¢',
    ];

    /** Second member of a cluster drawn as a sign under/after the first. */
    private const PHALA = ['য' => '¨', 'র' => 'ª', 'ব' => '¡', 'ন' => 'œ', 'ম' => '§', 'ল' => '¬'];

    private const SIGNS_AFTER = ['া' => 'v', 'ী' => 'x', 'ু' => 'y', 'ূ' => '~', 'ৃ' => '…', 'ৗ' => 'Š'];
    private const SIGNS_BEFORE = ['ি' => 'w', 'ে' => '‡', 'ৈ' => '‰'];
    private const MARKS = ['ং' => 's', 'ঃ' => 't', 'ঁ' => 'u', '।' => '|', '॥' => '||'];
    /** Consonant + vowel sign pairs with their own glyph. */
    private const SPECIAL = ['গু' => '¸', 'শু' => 'ï', 'হু' => 'û', 'হৃ' => 'ü', 'রু' => 'i“', 'রূ' => 'iƒ'];

    /** Characters safe to keep inside a Bengali run (ASCII the ANSI font draws as-is). */
    private const RUN_PUNCT = " ,.!?'\"()-/";

    public static function ansiFont(): string
    {
        return public_path('fonts/kalpurush/kalpurush-ansi.ttf');
    }

    public static function unicodeFont(): string
    {
        return public_path('fonts/kalpurush/kalpurush.ttf');
    }

    public static function available(): bool
    {
        return is_file(self::ansiFont()) && is_file(self::unicodeFont()) && function_exists('imagettftext');
    }

    /** Unicode Bengali → Bijoy text for the ANSI font. */
    public static function toBijoy(string $text): string
    {
        $text = \Normalizer::normalize($text, \Normalizer::FORM_C) ?: $text;
        // split two-part vowel signs and nukta forms
        $text = strtr($text, [
            "\u{09CB}" => "\u{09C7}\u{09BE}", "\u{09CC}" => "\u{09C7}\u{09D7}",
            // NFC keeps these as letter + nukta; the font has them as one glyph
            "\u{09A1}\u{09BC}" => "\u{09DC}", "\u{09A2}\u{09BC}" => "\u{09DD}", "\u{09AF}\u{09BC}" => "\u{09DF}",
        ]);
        $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $n = count($chars);
        $out = '';
        $isCons = fn ($c) => isset(self::CONSONANTS[$c]);

        for ($i = 0; $i < $n;) {
            $c = $chars[$i];

            if ($c === "\u{200C}" || $c === "\u{200D}" || $c === "\u{09BC}") {
                $i++;
                continue;
            }
            if (isset(self::VOWELS[$c])) {
                $out .= self::VOWELS[$c];
                $i++;
                continue;
            }
            if ($c >= '০' && $c <= '৯' && mb_strlen($c) === 1 && preg_match('/[০-৯]/u', $c)) {
                $out .= (string) (mb_ord($c) - 0x09E6);
                $i++;
                continue;
            }
            if (isset(self::MARKS[$c])) {
                $out .= self::MARKS[$c];
                $i++;
                continue;
            }
            if (!$isCons($c)) {
                // stray sign or hasanta, or punctuation
                $out .= self::SIGNS_AFTER[$c] ?? self::SIGNS_BEFORE[$c] ?? ($c === self::HASANTA ? '&' : $c);
                $i++;
                continue;
            }

            // reph: র + ্ + consonant (not র‍্য, which keeps a full র)
            $reph = false;
            if ($c === 'র' && ($chars[$i + 1] ?? '') === self::HASANTA && $isCons($chars[$i + 2] ?? '')) {
                $reph = true;
                $i += 2;
            } elseif ($c === 'র' && ($chars[$i + 1] ?? '') === "\u{200D}" && ($chars[$i + 2] ?? '') === self::HASANTA) {
                array_splice($chars, $i + 1, 1);
                $n--;
            }

            // consonant cluster
            $cluster = [$chars[$i]];
            $i++;
            while ($i + 1 < $n && $chars[$i] === self::HASANTA && $isCons($chars[$i + 1])) {
                $cluster[] = $chars[$i + 1];
                $i += 2;
            }
            $endHasanta = false;
            if ($i < $n && $chars[$i] === self::HASANTA) {
                $endHasanta = true;
                $i++;
                if (($chars[$i] ?? '') === "\u{200C}" || ($chars[$i] ?? '') === "\u{200D}") $i++;
            }

            // vowel signs and marks that follow
            $before = '';
            $after = '';
            $firstSign = null;
            while ($i < $n && (isset(self::SIGNS_AFTER[$chars[$i]]) || isset(self::SIGNS_BEFORE[$chars[$i]]))) {
                $s = $chars[$i];
                $firstSign ??= $s;
                if (isset(self::SIGNS_BEFORE[$s])) $before .= self::SIGNS_BEFORE[$s];
                else $after .= self::SIGNS_AFTER[$s];
                $i++;
            }

            $glyphs = self::cluster($cluster);
            if (count($cluster) === 1 && $firstSign !== null && isset(self::SPECIAL[$cluster[0] . $firstSign]) && !$reph) {
                $glyphs = self::SPECIAL[$cluster[0] . $firstSign];
                $after = mb_substr($after, 1);
            }

            $out .= $before . $glyphs . ($reph ? '©' : '') . ($endHasanta ? '&' : '') . $after;
        }

        return $out;
    }

    private static function cluster(array $cons): string
    {
        $out = '';
        $n = count($cons);
        for ($i = 0; $i < $n;) {
            $matched = false;
            for ($j = $n - 1; $j > $i; $j--) {
                $key = implode(self::HASANTA, array_slice($cons, $i, $j - $i + 1));
                if (isset(self::CONJUNCTS[$key])) {
                    $out .= self::CONJUNCTS[$key];
                    $i = $j + 1;
                    $matched = true;
                    break;
                }
            }
            if ($matched) continue;

            $c = $cons[$i];
            if ($i > 0) {
                $out .= self::PHALA[$c] ?? ('&' . self::CONSONANTS[$c]);
            } else {
                $out .= self::CONSONANTS[$c];
            }
            $i++;
        }
        return $out;
    }

    /**
     * Split into runs: [isBangla, text]. Bengali words (with spaces and
     * plain punctuation between them) form one run; everything else another.
     */
    public static function runs(string $text): array
    {
        $runs = [];
        foreach (preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $ch) {
            $bn = (bool) preg_match('/[\x{0980}-\x{09FF}\x{200C}\x{200D}]/u', $ch);
            $neutral = !$bn && str_contains(self::RUN_PUNCT, $ch) && strlen($ch) === 1;
            $last = count($runs) - 1;
            if ($last >= 0 && ($neutral || $runs[$last][0] === $bn)) {
                $runs[$last][1] .= $ch;
            } else {
                $runs[] = [$bn, $ch];
            }
        }
        return $runs;
    }

    /** Width in pixels of $text at $size. */
    public static function width(float $size, string $text): int
    {
        $w = 0;
        foreach (self::runs($text) as [$bn, $part]) {
            $w += self::advance($size, $bn ? self::ansiFont() : self::unicodeFont(), $bn ? self::toBijoy($part) : $part);
        }
        return $w;
    }

    /** Draw text with its baseline at $y; returns the width drawn. */
    public static function draw(\GdImage $img, float $size, int $x, int $y, int $color, string $text): int
    {
        $start = $x;
        foreach (self::runs($text) as [$bn, $part]) {
            $font = $bn ? self::ansiFont() : self::unicodeFont();
            $str = $bn ? self::toBijoy($part) : $part;
            imagettftext($img, $size, 0, $x, $y, $color, $font, $str);
            $x += self::advance($size, $font, $str);
        }
        return $x - $start;
    }

    /** Break $text into lines no wider than $maxWidth (at most $maxLines; the last one gets "…"). */
    public static function wrap(float $size, string $text, int $maxWidth, int $maxLines = 3): array
    {
        $words = preg_split('/\s+/u', trim($text)) ?: [];
        $lines = [];
        $line = '';
        foreach ($words as $idx => $word) {
            $test = $line === '' ? $word : $line . ' ' . $word;
            if ($line !== '' && self::width($size, $test) > $maxWidth) {
                $lines[] = $line;
                $line = $word;
                if (count($lines) === $maxLines) {
                    $line = '';
                    $last = $lines[$maxLines - 1];
                    while ($last !== '' && self::width($size, $last . '…') > $maxWidth) {
                        $last = preg_replace('/\s*\S+$/u', '', $last) ?? '';
                    }
                    $lines[$maxLines - 1] = $last . '…';
                    break;
                }
            } else {
                $line = $test;
            }
        }
        if ($line !== '') {
            $lines[] = $line;
        }
        return $lines;
    }

    private static function advance(float $size, string $font, string $str): int
    {
        if ($str === '') return 0;
        // measure with a marker so trailing spaces count
        $box = imagettfbbox($size, 0, $font, $str . '|');
        $bar = imagettfbbox($size, 0, $font, '|');
        // right edge of the marker minus its own right edge = the advance of $str
        return (int) round($box[2] - $bar[2]);
    }
}
