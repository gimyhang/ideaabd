<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BookCoverGenerator
{
    /**
     * Color themes for aesthetic premium generated book covers.
     */
    public const THEMES = [
        'royal_blue' => [
            'name'         => 'Royal Navy',
            'bg_start'     => '#0f172a',
            'bg_end'       => '#1e1b4b',
            'bg'           => '#0f172a',
            'title_color'  => '#ffffff',
            'author_color' => '#fbbf24',
            'accent_color' => '#f59e0b',
            'border_color' => '#fbbf24',
        ],
        'deep_emerald' => [
            'name'         => 'Deep Forest Green',
            'bg_start'     => '#064e3b',
            'bg_end'       => '#022c22',
            'bg'           => '#064e3b',
            'title_color'  => '#ffffff',
            'author_color' => '#fef08a',
            'accent_color' => '#10b981',
            'border_color' => '#34d399',
        ],
        'crimson_ruby' => [
            'name'         => 'Deep Maroon',
            'bg_start'     => '#450a0a',
            'bg_end'       => '#280505',
            'bg'           => '#450a0a',
            'title_color'  => '#ffffff',
            'author_color' => '#fed7aa',
            'accent_color' => '#f97316',
            'border_color' => '#fb923c',
        ],
        'regal_purple' => [
            'name'         => 'Royal Plum',
            'bg_start'     => '#2e1065',
            'bg_end'       => '#1a083a',
            'bg'           => '#2e1065',
            'title_color'  => '#ffffff',
            'author_color' => '#fef08a',
            'accent_color' => '#c084fc',
            'border_color' => '#e9d5ff',
        ],
        'midnight_slate' => [
            'name'         => 'Midnight Charcoal',
            'bg_start'     => '#18181b',
            'bg_end'       => '#09090b',
            'bg'           => '#18181b',
            'title_color'  => '#ffffff',
            'author_color' => '#e2e8f0',
            'accent_color' => '#94a3b8',
            'border_color' => '#cbd5e1',
        ],
        'warm_brown' => [
            'name'         => 'Warm Terracotta',
            'bg_start'     => '#451a03',
            'bg_end'       => '#230c02',
            'bg'           => '#451a03',
            'title_color'  => '#ffffff',
            'author_color' => '#fde68a',
            'accent_color' => '#f59e0b',
            'border_color' => '#fbbf24',
        ],
        'dark_teal' => [
            'name'         => 'Dark Teal',
            'bg_start'     => '#042f2e',
            'bg_end'       => '#021a19',
            'bg'           => '#042f2e',
            'title_color'  => '#ffffff',
            'author_color' => '#99f6e4',
            'accent_color' => '#14b8a6',
            'border_color' => '#5eead4',
        ],
    ];

    /**
     * Generate an SVG book cover and store it in the public disk.
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
        
        $themes = self::THEMES;
        if (!$themeKey || !isset($themes[$themeKey])) {
            $keys = array_keys($themes);
            $idx = abs(crc32($title . $authorName)) % count($keys);
            $themeKey = $keys[$idx];
        }

        $theme = $themes[$themeKey];
        $svg = self::renderSvg($title, $authorName, $subtitle, $categoryName, $theme, $customOptions);

        $folder = 'books/covers';
        $disk = 'public';
        $randomName = 'cover_' . time() . '_' . Str::random(8) . '.svg';
        $path = "{$folder}/{$randomName}";

        Storage::disk($disk)->put($path, $svg);
        return $path;
    }

    /**
     * Render the SVG markup for the clean, aesthetic vector book cover.
     */
    public static function renderSvg(
        string $title,
        string $authorName,
        ?string $subtitle = null,
        ?string $categoryName = null,
        array $theme = [],
        array $customOptions = []
    ): string {
        $width = 600;
        $height = 900;

        if (empty($theme)) {
            $theme = self::THEMES['royal_blue'];
        }

        $bgStart = $theme['bg_start'] ?? ($theme['bg'] ?? '#0f172a');
        $bgEnd = $theme['bg_end'] ?? ($theme['bg'] ?? '#1e1b4b');
        $titleColor = $theme['title_color'] ?? '#ffffff';
        $authorColor = $theme['author_color'] ?? '#fbbf24';
        $accentColor = $theme['accent_color'] ?? '#f59e0b';
        $borderColor = $theme['border_color'] ?? '#fbbf24';

        // Escape text for XML
        $safeTitle = htmlspecialchars($title, ENT_XML1, 'UTF-8');
        $safeAuthor = htmlspecialchars($authorName, ENT_XML1, 'UTF-8');
        $safeCategory = $categoryName ? htmlspecialchars($categoryName, ENT_XML1, 'UTF-8') : null;

        // Split title into balanced lines for maximum readability (~14-16 chars per line)
        $titleWords = explode(' ', $title);
        $titleLines = [];
        $currentLine = '';
        foreach ($titleWords as $w) {
            if (mb_strlen($currentLine . ' ' . $w) > 15) {
                if (!empty($currentLine)) {
                    $titleLines[] = htmlspecialchars(trim($currentLine), ENT_XML1, 'UTF-8');
                }
                $currentLine = $w;
            } else {
                $currentLine = $currentLine ? $currentLine . ' ' . $w : $w;
            }
        }
        if (!empty($currentLine)) {
            $titleLines[] = htmlspecialchars(trim($currentLine), ENT_XML1, 'UTF-8');
        }
        if (empty($titleLines)) {
            $titleLines = [$safeTitle];
        }
        $titleLines = array_slice($titleLines, 0, 4);

        // Optimal responsive title font size
        $lineCount = count($titleLines);
        $baseTitleSize = $lineCount >= 4 ? 44 : ($lineCount === 3 ? 50 : ($lineCount === 2 ? 58 : 68));
        if (!empty($customOptions['title_size'])) {
            $sizeModifier = match($customOptions['title_size']) {
                'small'  => 0.82,
                'medium' => 0.92,
                'huge'   => 1.15,
                default  => 1.0,
            };
            $titleFontSize = (int) round($baseTitleSize * $sizeModifier);
        } else {
            $titleFontSize = $baseTitleSize;
        }

        $lineHeight = (int) round($titleFontSize * 1.35);
        $titleBlockHeight = $lineCount * $lineHeight;
        
        // Vertically balance title and author
        $titleStartY = max(260, (int) round(430 - ($titleBlockHeight / 2)));

        $tspanTags = '';
        foreach ($titleLines as $idx => $line) {
            $y = $titleStartY + ($idx * $lineHeight);
            $tspanTags .= "    <tspan x=\"300\" y=\"{$y}\">{$line}</tspan>\n";
        }

        $dividerY = $titleStartY + $titleBlockHeight + 25;
        $authorY = $dividerY + 55;

        $categoryBadge = '';
        if ($safeCategory) {
            $categoryBadge = <<<CAT
  <!-- Category Badge -->
  <g transform="translate(300, 130)" text-anchor="middle">
    <rect x="-80" y="-18" width="160" height="32" rx="16" fill="rgba(255,255,255,0.12)" stroke="{$borderColor}" stroke-width="1.2" />
    <text x="0" y="4" font-family="'Hind Siliguri', 'SolaimanLipi', 'Kalpurush', 'Noto Sans Bengali', sans-serif" font-size="14" font-weight="600" fill="{$borderColor}">{$safeCategory}</text>
  </g>
CAT;
        }

        return <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$width} {$height}" width="100%" height="100%">
  <defs>
    <style>
      @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@500;600;700;800&amp;family=Noto+Sans+Bengali:wght@500;600;700;800&amp;display=swap');
      .cover-title { font-family: 'Hind Siliguri', 'Noto Sans Bengali', 'SolaimanLipi', 'Kalpurush', sans-serif; }
      .cover-author { font-family: 'Hind Siliguri', 'Noto Sans Bengali', 'SolaimanLipi', 'Kalpurush', sans-serif; }
      .cover-badge { font-family: 'Hind Siliguri', 'Noto Sans Bengali', 'SolaimanLipi', 'Kalpurush', sans-serif; }
    </style>
    <linearGradient id="bgGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="{$bgStart}" />
      <stop offset="100%" stop-color="{$bgEnd}" />
    </linearGradient>
    <radialGradient id="glowGrad" cx="50%" cy="40%" r="60%">
      <stop offset="0%" stop-color="{$borderColor}" stop-opacity="0.12" />
      <stop offset="100%" stop-color="#000000" stop-opacity="0" />
    </radialGradient>
  </defs>

  <!-- Background -->
  <rect width="{$width}" height="{$height}" fill="url(#bgGrad)" />
  <rect width="{$width}" height="{$height}" fill="url(#glowGrad)" />

  <!-- Elegant Framing Borders -->
  <rect x="25" y="25" width="550" height="850" fill="none" stroke="{$borderColor}" stroke-width="1.5" opacity="0.4" rx="4" />
  <rect x="35" y="35" width="530" height="830" fill="none" stroke="{$borderColor}" stroke-width="2.5" opacity="0.85" rx="3" />

  <!-- Top Publisher Emblem -->
  <g transform="translate(300, 85)" text-anchor="middle">
    <text x="0" y="0" class="cover-badge" font-size="16" font-weight="700" fill="{$borderColor}" letter-spacing="1">✦ আইডিয়া প্রকাশন ✦</text>
  </g>

{$categoryBadge}

  <!-- Book Title -->
  <g class="cover-title" text-anchor="middle">
    <text font-size="{$titleFontSize}" font-weight="800" fill="{$titleColor}" letter-spacing="0.5">
{$tspanTags}    </text>
  </g>

  <!-- Decorative Divider -->
  <g transform="translate(300, {$dividerY})" text-anchor="middle">
    <line x1="-120" y1="0" x2="-35" y2="0" stroke="{$borderColor}" stroke-width="1.5" opacity="0.75" />
    <text x="0" y="5" class="cover-badge" font-size="15" fill="{$borderColor}">❖ ── ✦ ── ❖</text>
    <line x1="35" y1="0" x2="120" y2="0" stroke="{$borderColor}" stroke-width="1.5" opacity="0.75" />
  </g>

  <!-- Author Name -->
  <g transform="translate(300, {$authorY})" text-anchor="middle">
    <text x="0" y="0" class="cover-author" font-size="26" font-weight="700" fill="{$authorColor}">{$safeAuthor}</text>
  </g>

  <!-- Bottom Imprint -->
  <g transform="translate(300, 835)" text-anchor="middle">
    <text x="0" y="0" class="cover-badge" font-size="13" font-weight="500" fill="#ffffff" opacity="0.75" letter-spacing="1.5">আইডিয়া প্রকাশন | www.ideaabd.com</text>
  </g>
</svg>
SVG;
    }
}

