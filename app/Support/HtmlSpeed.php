<?php

namespace App\Support;

/**
 * Last pass over a finished HTML page (called by MinifyHtmlResponse, so its result is also what GuestPageCache keeps).
 *
 *  1. Every local .css / .js link gets ?v=<file change time>. public/.htaccess lets browsers keep versioned files
 *     for a year, and a changed file gets a new address, so visitors never run an old copy.
 *  2. Images below the top of the page get loading="lazy" (fetched only when scrolled near) and every image gets
 *     decoding="async" (decoded off the main thread). The first few images inside <main> stay eager: they are
 *     usually what the visitor sees first.
 *
 * Text inside <script>, <textarea>, <pre> and <noscript> is left exactly as it is.
 */
class HtmlSpeed
{
    /** Images inside <main> that keep loading at once. */
    private const EAGER = 4;

    public static function optimize(string $html): string
    {
        try {
            $parts = preg_split('#(<(script|textarea|pre|noscript)\b.*?</\2>)#is', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
            if (!is_array($parts)) {
                return $html;
            }
            $out = '';
            $inMain = false;
            $mainImages = 0;
            $n = count($parts);
            for ($i = 0; $i < $n; $i++) {
                $chunk = $parts[$i];
                // DELIM_CAPTURE: [text, whole-block, tag-name, text, whole-block, tag-name, ...]
                if ($i % 3 === 1) {
                    $out .= self::versionAssets($chunk, true);
                    continue;
                }
                if ($i % 3 === 2) {
                    continue;
                }
                $chunk = self::versionAssets($chunk, false);
                $out .= self::lazyImages($chunk, $inMain, $mainImages);
            }

            return $out;
        } catch (\Throwable) {
            return $html;
        }
    }

    /** In script blocks only the tag itself (src="...") is touched, never the code. */
    private static function versionAssets(string $chunk, bool $isBlock): string
    {
        static $prefix = null;
        if ($prefix === null) {
            $prefix = preg_quote(rtrim(asset(''), '/'), '#');
        }
        $re = '#\b(href|src)="((?:' . $prefix . ')?/((?:css|js|vendor)/[^"?\#<>\s]+\.(?:css|js)))"#';
        if ($isBlock) {
            $end = strpos($chunk, '>');
            if ($end === false) {
                return $chunk;
            }
            return preg_replace_callback($re, [self::class, 'addVersion'], substr($chunk, 0, $end + 1)) . substr($chunk, $end + 1);
        }

        return preg_replace_callback($re, [self::class, 'addVersion'], $chunk);
    }

    private static function addVersion(array $m): string
    {
        static $times = [];
        $file = $m[3];
        if (str_contains($file, '..')) {
            return $m[0];
        }
        $times[$file] ??= @filemtime(public_path($file)) ?: 0;

        return $times[$file] ? $m[1] . '="' . $m[2] . '?v=' . $times[$file] . '"' : $m[0];
    }

    private static function lazyImages(string $chunk, bool &$inMain, int &$mainImages): string
    {
        return preg_replace_callback('#<main\b|</main>|<img\b[^>]*>#i', function ($m) use (&$inMain, &$mainImages) {
            $tag = $m[0];
            $lower = strtolower($tag);
            if (str_starts_with($lower, '<main')) {
                $inMain = true;
                return $tag;
            }
            if ($lower === '</main>') {
                // footer and drawers: always below the fold
                $mainImages = max($mainImages, self::EAGER);
                return $tag;
            }
            $add = '';
            if (!preg_match('#\sdecoding=#i', $tag)) {
                $add .= ' decoding="async"';
            }
            if ($inMain) {
                $mainImages++;
                if ($mainImages > self::EAGER && !preg_match('#\s(loading|fetchpriority)=#i', $tag)) {
                    $add .= ' loading="lazy"';
                }
            }

            return $add === '' ? $tag : substr($tag, 0, 4) . $add . substr($tag, 4);
        }, $chunk);
    }
}
