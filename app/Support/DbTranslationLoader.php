<?php

namespace App\Support;

use App\Models\SiteTranslation;
use Illuminate\Contracts\Translation\Loader;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Wraps Laravel's file loader: text saved on /admin/translations is laid over
 * the lang/*.php lines, so __('group.key') and validation messages use it.
 * The file text stays the fallback whenever a field is empty.
 */
class DbTranslationLoader implements Loader
{
    public const LOCALES = ['bn' => 'text_bn', 'en' => 'text_en', 'ar' => 'text_ar'];
    private const CACHE = 'site_tr_v1_';

    public function __construct(private Loader $files) {}

    public function load($locale, $group, $namespace = null)
    {
        $lines = $this->files->load($locale, $group, $namespace);

        // only plain PHP groups (not JSON '*' nor package namespaces)
        if ($group === '*' || ($namespace !== null && $namespace !== '*') || !isset(self::LOCALES[$locale])) {
            return $lines;
        }

        foreach (self::overrides($locale, $group) as $key => $text) {
            if (self::fits($lines, $key)) {
                Arr::set($lines, $key, $text);
            }
        }

        return $lines;
    }

    /** @return array<string,string> key => text for one locale and group */
    public static function overrides(string $locale, string $group): array
    {
        $column = self::LOCALES[$locale] ?? null;
        if (!$column) {
            return [];
        }

        try {
            return Cache::rememberForever(self::CACHE . $group, function () use ($group) {
                $out = [];
                foreach (SiteTranslation::where('group', $group)->get(['key', 'text_bn', 'text_en', 'text_ar']) as $row) {
                    foreach (self::LOCALES as $loc => $col) {
                        $text = trim((string) $row->{$col});
                        if ($text !== '') {
                            $out[$loc][$row->key] = $text;
                        }
                    }
                }
                return $out;
            })[$locale] ?? [];
        } catch (\Throwable $e) {
            // missing table or database down: the file text is still served
            Log::debug('DB translations skipped: ' . $e->getMessage());
            return [];
        }
    }

    public static function forget(?string $group = null): void
    {
        if ($group !== null) {
            Cache::forget(self::CACHE . $group);
            return;
        }
        try {
            foreach (SiteTranslation::query()->distinct()->pluck('group') as $g) {
                Cache::forget(self::CACHE . $g);
            }
        } catch (\Throwable) {
        }
    }

    /**
     * A saved string must not replace a whole set of lines (for example
     * "between", which holds numeric/file/string) or sit below a plain line.
     */
    public static function fits(array $lines, string $key): bool
    {
        if (is_array(Arr::get($lines, $key))) {
            return false;
        }
        $path = '';
        foreach (array_slice(explode('.', $key), 0, -1) as $part) {
            $path = $path === '' ? $part : $path . '.' . $part;
            if (Arr::has($lines, $path) && !is_array(Arr::get($lines, $path))) {
                return false;
            }
        }
        return true;
    }

    /** The lang/*.php text alone, without saved overrides. */
    public function fileLines(string $locale, string $group): array
    {
        return $this->files->load($locale, $group, '*');
    }

    public function addNamespace($namespace, $hint)
    {
        $this->files->addNamespace($namespace, $hint);
    }

    public function addJsonPath($path)
    {
        $this->files->addJsonPath($path);
    }

    public function namespaces()
    {
        return $this->files->namespaces();
    }

    public function __call($method, $args)
    {
        return $this->files->{$method}(...$args);
    }
}
