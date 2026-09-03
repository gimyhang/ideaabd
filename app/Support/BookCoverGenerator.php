<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BookCoverGenerator
{
    /**
     * Color themes for plain minimalist generated book covers.
     */
    public const THEMES = [
        'royal_blue' => [
            'name'        => 'Royal Navy',
            'bg'          => '#0f172a',
            'title_color' => '#ffffff',
            'author_color'=> '#fde047',
        ],
        'deep_emerald' => [
            'name'        => 'Deep Forest Green',
            'bg'          => '#064e3b',
            'title_color' => '#ffffff',
            'author_color'=> '#fef08a',
        ],
        'crimson_ruby' => [
            'name'        => 'Deep Maroon',
            'bg'          => '#450a0a',
            'title_color' => '#ffffff',
            'author_color'=> '#fed7aa',
        ],
        'regal_purple' => [
            'name'        => 'Royal Plum',
            'bg'          => '#2e1065',
            'title_color' => '#ffffff',
            'author_color'=> '#fef08a',
        ],
        'midnight_slate' => [
            'name'        => 'Midnight Charcoal',
            'bg'          => '#18181b',
            'title_color' => '#ffffff',
            'author_color'=> '#e2e8f0',
        ],
    ];

    /**
     * Generate an SVG book cover and store it in the public disk.
     *
     * @param string $title
     * @param string|null $authorName
     * @param string|null $subtitle
     * @param string|null $categoryName
     * @param string|null $themeKey
     * @param array $customOptions [bg_color, title_color, author_color, font_family, title_size, author_size]
     * @return string Relative path on public storage disk (e.g. 'books/covers/generated_xxx.svg')
     */
    public static function generate(
        string $title,
        ?string $authorName = null,
        ?string $subtitle = null,
        ?string $categoryName = null,
        ?string $themeKey = null,
        array $customOptions = []
    ): string {
        $title = trim($title) ?: 'নতুন বই';
        $authorName = trim((string)$authorName) ?: 'আইডিয়া প্রকাশন';
        
        // Pick theme based on title hash or requested key
        $themes = self::THEMES;
        if (!$themeKey || !isset($themes[$themeKey])) {
            $keys = array_keys($themes);
            $idx = abs(crc32($title . $authorName)) % count($keys);
            $themeKey = $keys[$idx];
        }
        $theme = $themes[$themeKey];

        // Apply custom overrides if provided
        if (!empty($customOptions['bg_color'])) {
            $theme['bg'] = $customOptions['bg_color'];
        }
        if (!empty($customOptions['title_color'])) {
            $theme['title_color'] = $customOptions['title_color'];
        }
        if (!empty($customOptions['author_color'])) {
            $theme['author_color'] = $customOptions['author_color'];
        }
        if (!empty($customOptions['font_family'])) {
            $theme['font_family'] = $customOptions['font_family'];
        }

        $svgContent = self::renderSvg($title, $authorName, $subtitle, $categoryName, $theme, $customOptions);

        $slug = Str::slug($title) ?: 'book';
        $filename = 'books/covers/cover_' . substr($slug, 0, 40) . '_' . Str::random(8) . '.svg';

        Storage::disk('public')->put($filename, $svgContent);

        return $filename;
    }

    /**
     * Render the SVG markup for the clean plain cover.
     */
    public static function renderSvg(
        string $title,
        string $authorName,
        ?string $subtitle,
        ?string $categoryName,
        array $theme,
        array $customOptions = []
    ): string {
        $width = 600;
        $height = 900;

        // Escape text for XML
        $safeTitle = htmlspecialchars($title, ENT_XML1, 'UTF-8');
        $safeAuthor = htmlspecialchars($authorName, ENT_XML1, 'UTF-8');

        // Split title into lines with large typography (max ~11 chars per line)
        $titleWords = explode(' ', $title);
        $titleLines = [];
        $currentLine = '';
        foreach ($titleWords as $w) {
            if (mb_strlen($currentLine . ' ' . $w) > 11) {
                if (!empty($currentLine)) {
                    $titleLines[] = htmlspecialchars(trim($currentLine), ENT_XML1, 'UTF-8');
                }
                $currentLine = $w;
            } else {
                $currentLine .= ' ' . $w;
            }
        }
        if (!empty($currentLine)) {
            $titleLines[] = htmlspecialchars(trim($currentLine), ENT_XML1, 'UTF-8');
        }
        if (empty($titleLines)) {
            $titleLines = [$safeTitle];
        }
        $titleLines = array_slice($titleLines, 0, 4);

        // Huge Title Size
        $baseTitleSize = count($titleLines) > 2 ? 62 : (count($titleLines) === 2 ? 70 : 80);
        if (!empty($customOptions['title_size'])) {
            $sizeModifier = match($customOptions['title_size']) {
                'small'  => 0.8,
                'medium' => 0.9,
                'huge'   => 1.15,
                default  => 1.0,
            };
            $titleFontSize = (int) round($baseTitleSize * $sizeModifier);
        } else {
            $titleFontSize = $baseTitleSize;
        }

        $lineHeight = $titleFontSize * 1.25;
        $titleBlockHeight = count($titleLines) * $lineHeight;
        
        // Position title in the upper section (top)
        $titleStartY = 240;

        $bgColor = $theme['bg'];
        $titleColor = $theme['title_color'];
        $authorColor = $theme['author_color'];
        $fontFamily = !empty($theme['font_family'])
            ? "'{$theme['font_family']}', 'Hind Siliguri', 'SolaimanLipi', 'Noto Serif Bengali', serif"
            : "'Hind Siliguri', 'SolaimanLipi', 'Kalpurush', 'Noto Serif Bengali', 'Georgia', serif";

        $authorFontSize = 30;
        if (!empty($customOptions['author_size'])) {
            $authorFontSize = match($customOptions['author_size']) {
                'small' => 24,
                'large' => 34,
                default => 30,
            };
        }

        $tspanTags = '';
        foreach ($titleLines as $idx => $line) {
            $y = $titleStartY + ($idx * $lineHeight);
            $tspanTags .= "<tspan x=\"300\" y=\"{$y}\">{$line}</tspan>\n";
        }

        $authorY = $titleStartY + $titleBlockHeight + 40;

        return <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$width} {$height}" width="100%" height="100%">
  <!-- Plain Solid Color Background -->
  <rect width="{$width}" height="{$height}" fill="{$bgColor}" />

  <!-- Book Title (Upper Section) -->
  <g id="titleGroup" text-anchor="middle">
    <text font-family="{$fontFamily}" font-size="{$titleFontSize}" font-weight="800" fill="{$titleColor}">
      {$tspanTags}
    </text>
  </g>

  <!-- Author Name Directly Below Title (Clean, No Box, No Borders) -->
  <g transform="translate(300, {$authorY})" text-anchor="middle">
    <text x="0" y="0" font-family="{$fontFamily}" font-size="{$authorFontSize}" font-weight="600" fill="{$authorColor}">{$safeAuthor}</text>
  </g>

  <!-- Bottom Minimal Imprint -->
  <g transform="translate(300, 840)" text-anchor="middle">
    <text x="0" y="0" font-family="{$fontFamily}" font-size="13" font-weight="500" fill="#ffffff" opacity="0.6" letter-spacing="2">আইডিয়া প্রকাশন</text>
  </g>
</svg>
SVG;
    }
}
