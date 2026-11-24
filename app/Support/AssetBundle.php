<?php

namespace App\Support;

/**
 * Joins several files from /public into one file, so a page needs one request instead of many.
 *
 * The joined file's name carries a fingerprint of its parts (path, size, change time): editing any part produces a
 * new file name, so browsers may keep the file for a year (see public/.htaccess) and still never see an old copy.
 * The file is written on first use; if /public is not writable the separate files are linked as before.
 *
 *   {!! \App\Support\AssetBundle::css('site', ['vendor/bootstrap-5.3.0/bootstrap.min.css', 'css/site.css']) !!}
 */
class AssetBundle
{
    private const DIR = 'b';

    public static function css(string $name, array $files): string
    {
        $url = self::build($name, $files, 'css');
        if ($url !== null) {
            return '<link rel="stylesheet" href="' . e($url) . '">';
        }

        return implode("\n", array_map(fn ($f) => '<link rel="stylesheet" href="' . e(self::versioned($f)) . '">', $files));
    }

    public static function js(string $name, array $files, string $attrs = ''): string
    {
        $attrs = $attrs !== '' ? ' ' . trim($attrs) : '';
        $url = self::build($name, $files, 'js');
        if ($url !== null) {
            return '<script src="' . e($url) . '"' . $attrs . '></script>';
        }

        return implode("\n", array_map(fn ($f) => '<script src="' . e(self::versioned($f)) . '"' . $attrs . '></script>', $files));
    }

    /** asset() plus ?v=<change time>, for a single file. */
    public static function versioned(string $file): string
    {
        $t = @filemtime(public_path($file));

        return asset($file) . ($t ? '?v=' . $t : '');
    }

    /** Builds (once) and returns the joined file's URL, or null when it cannot be written. */
    private static function build(string $name, array $files, string $ext): ?string
    {
        static $done = [];
        $sig = '';
        foreach ($files as $f) {
            $p = public_path($f);
            $sig .= $f . '|' . @filesize($p) . '|' . @filemtime($p) . ';';
        }
        $file = $ext . '/' . self::DIR . '/' . $name . '-' . substr(md5($sig), 0, 10) . '.' . $ext;
        if (isset($done[$file])) {
            return $done[$file];
        }
        $target = public_path($file);
        if (!is_file($target)) {
            try {
                $parts = [];
                foreach ($files as $f) {
                    $body = @file_get_contents(public_path($f));
                    if ($body === false) {
                        return $done[$file] = null;          // a part is missing: link the files one by one
                    }
                    // source-map comments point at files that are not here
                    $body = preg_replace('~^\s*(//# sourceMappingURL=.*|/\*# sourceMappingURL=.*?\*/)\s*$~m', '', $body);
                    $parts[] = "/* {$f} */\n" . trim($body);
                }
                // ";" between scripts: a part without a trailing semicolon must not run into the next one
                $joined = implode($ext === 'js' ? "\n;\n" : "\n", $parts) . "\n";
                $dir = dirname($target);
                if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
                    return $done[$file] = null;
                }
                $tmp = $target . '.' . getmypid() . '.tmp';
                if (@file_put_contents($tmp, $joined) === false || !@rename($tmp, $target)) {
                    @unlink($tmp);
                    return $done[$file] = null;
                }
                // drop older builds of this bundle (kept a day: cached pages and open tabs may still ask for them)
                foreach (glob($dir . '/' . $name . '-*.' . $ext) ?: [] as $old) {
                    if ($old !== $target && @filemtime($old) < time() - 86400) {
                        @unlink($old);
                    }
                }
            } catch (\Throwable) {
                return $done[$file] = null;
            }
        }

        return $done[$file] = asset($file);
    }
}
