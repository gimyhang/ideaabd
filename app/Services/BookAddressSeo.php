<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Modules\Book\Models\Book;
use Modules\Book\Models\BookSlugRedirect;
use Modules\SEO\Models\SeoMeta;
use Modules\SEO\Models\SeoRedirect;

/**
 * Everything search engines and shared links have learned about a book's address, moved over when the
 * address changes, so a new slug never costs the book its ranking or its custom SEO text:
 *
 *  - the old address is kept and 301-redirects to the new one (BookSlugRedirect);
 *  - the book's saved SEO record keeps its own titles/descriptions but points at the new address;
 *  - SEO records and admin redirect rules keyed by the old path follow the book (no two-step redirects);
 *  - the cached sitemap is dropped so it lists the new address, not a redirecting one.
 */
class BookAddressSeo
{
    public static function moved(int $bookId, ?string $oldSlug, ?string $newSlug): void
    {
        $old = trim((string) $oldSlug);
        $new = trim((string) $newSlug);
        if ($old === '' || $new === '' || $old === $new) {
            return;
        }

        try {
            if (Schema::hasTable('book_slug_redirects')) {
                BookSlugRedirect::remember($bookId, $old);
                // the address now in use is never also an "old" one
                BookSlugRedirect::where('old_slug', $new)->delete();
            }

            self::moveSeoRecords($bookId, $old, $new);
            self::fixRedirectRules($old, $new);

            Cache::forget('master_sitemap_xml_v2');
        } catch (\Throwable $e) {
            // The address change itself must never fail because a SEO table is missing or unreadable.
            Log::warning("Book address SEO move failed for #{$bookId}: " . $e->getMessage());
        }
    }

    /** "/books/old-slug" in the ways it can appear (plain and percent-encoded). */
    private static function pathForms(string $slug): array
    {
        return array_values(array_unique(['/books/' . $slug, '/books/' . rawurlencode($slug)]));
    }

    private static function swap(?string $text, string $old, string $new): ?string
    {
        if ($text === null || $text === '') {
            return $text;
        }
        $pairs = array_combine(self::pathForms($old), self::pathForms($new)) ?: [];
        // longest first, and only whole path segments: "/books/1-a" must not rewrite "/books/1-ab"
        uksort($pairs, fn ($a, $b) => strlen($b) <=> strlen($a));
        foreach ($pairs as $from => $to) {
            $text = preg_replace('#' . preg_quote($from, '#') . '(?=$|[/?\#"\'\\\\])#u', $to, $text) ?? $text;
        }

        return $text;
    }

    private static function moveSeoRecords(int $bookId, string $old, string $new): void
    {
        if (! Schema::hasTable('seo_metas')) {
            return;
        }

        // the book's own record: custom text stays, only address-bearing fields change
        foreach (SeoMeta::where('seoable_type', Book::class)->where('seoable_id', $bookId)->get() as $meta) {
            $meta->canonical_url = self::swap($meta->canonical_url, $old, $new);
            $meta->og_image = self::swap($meta->og_image, $old, $new);
            $meta->twitter_image = self::swap($meta->twitter_image, $old, $new);
            if (is_array($meta->schema_json)) {
                $json = json_encode($meta->schema_json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $swapped = self::swap(str_replace('\\/', '/', (string) $json), $old, $new);
                $meta->schema_json = json_decode((string) $swapped, true) ?? $meta->schema_json;
            }
            $meta->saveQuietly();
        }

        // records keyed by path (typed in by hand under /admin/seo)
        foreach (self::pathForms($old) as $path) {
            foreach (SeoMeta::where('url_path', $path)->get() as $meta) {
                if (SeoMeta::where('url_path', '/books/' . $new)->where('id', '!=', $meta->id)->exists()) {
                    continue; // the new path already has its own record: keep it
                }
                $meta->url_path = '/books/' . $new;
                $meta->canonical_url = self::swap($meta->canonical_url, $old, $new);
                $meta->saveQuietly();
            }
        }
    }

    private static function fixRedirectRules(string $old, string $new): void
    {
        if (! Schema::hasTable('seo_redirects')) {
            return;
        }

        // a rule that sends people to the old address would now be a two-step redirect: point it at the new one
        foreach (SeoRedirect::query()->get() as $rule) {
            $target = (string) $rule->target_url;
            $swapped = self::swap($target, $old, $new);
            if ($swapped !== $target) {
                $rule->target_url = $swapped;
                $rule->save();
            }
        }

        // a rule that starts at the NEW address would hijack the book's own page (middleware runs first): switch it off
        foreach (self::pathForms($new) as $path) {
            SeoRedirect::where('source_url', $path)->orWhere('source_url', $path . '/')->update(['is_active' => false]);
        }
    }
}
