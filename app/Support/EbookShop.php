<?php

namespace App\Support;

use App\Models\User;
use App\Models\UserEbookLibrary;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Modules\Ebook\Models\Ebook;

/**
 * E-book shop rules shared by the shop pages, the reader and the file stream.
 *
 *  - who may read the whole book (free + claimed, bought and paid, author, admin)
 *  - where a book's files are on disk (never a different book's file)
 *  - the free sample: the uploaded sample file, or — for an EPUB without one — the first chapters of the book,
 *    built once and kept outside the public folder
 */
class EbookShop
{
    /** Share of an EPUB's chapters given as an automatic sample (at least 2, at most 6 chapters). */
    private const SAMPLE_SHARE = 0.12;

    /** Reading position and highlights for the library table (added from a web request: production artisan uses another database). */
    public static function ensureSchema(): void
    {
        if (Cache::get('ebookshop:schema:v1')) {
            return;
        }
        try {
            if (Schema::hasTable('user_ebook_library')) {
                $add = array_values(array_filter(['last_cfi', 'highlights_data'], fn ($c) => !Schema::hasColumn('user_ebook_library', $c)));
                if ($add) {
                    Schema::table('user_ebook_library', function (Blueprint $t) use ($add) {
                        if (in_array('last_cfi', $add, true)) $t->string('last_cfi', 500)->nullable();
                        if (in_array('highlights_data', $add, true)) $t->longText('highlights_data')->nullable();
                    });
                }
            }
            Cache::put('ebookshop:schema:v1', true, 86400);
        } catch (\Throwable $e) {
            Log::warning('EbookShop::ensureSchema: ' . $e->getMessage());
        }
    }

    // ── access ───────────────────────────────────────────────────────────────────────────────────────────────

    public static function isFree(Ebook $e): bool
    {
        return (bool) $e->is_free;
    }

    public static function price(Ebook $e): float
    {
        $p = (float) $e->price;
        $d = (float) $e->discount_price;

        return ($e->discount_price !== null && $d >= 0 && $d < $p) ? $d : $p;
    }

    public static function isStaff(?User $u, Ebook $e): bool
    {
        return $u && ($u->isAdmin() || $u->isSubAdmin() || ($e->author_user_id && (int) $e->author_user_id === (int) $u->id));
    }

    /** Library row of this user for this book (null when none). */
    public static function entry(?User $u, Ebook $e): ?UserEbookLibrary
    {
        if (!$u) {
            return null;
        }
        try {
            return UserEbookLibrary::where('user_id', $u->id)->where('ebook_id', $e->id)->first();
        } catch (\Throwable) {
            return null;
        }
    }

    /** May this visitor read the whole book online? Free books: anyone. Paid: bought and paid, author or staff. */
    public static function canReadFull(?User $u, Ebook $e): bool
    {
        return self::isFree($e) || self::owns($u, $e);
    }

    /**
     * Does the user own the book (it is in their library)? Free: claimed. Paid: a paid order.
     * A paid order found here (payment confirmed but library row missing) is added to the library.
     */
    public static function owns(?User $u, Ebook $e): bool
    {
        if (!$u) {
            return false;
        }
        if (self::isStaff($u, $e)) {
            return true;
        }
        $entry = self::entry($u, $e);
        if ($entry && $entry->is_active && (self::isFree($e) || $entry->access_type === 'purchased')) {
            return true;
        }
        if (self::isFree($e)) {
            return false;
        }
        if (self::hasPaidOrder($u, $e)) {
            UserEbookLibrary::updateOrCreate(['user_id' => $u->id, 'ebook_id' => $e->id], ['access_type' => 'purchased', 'is_active' => true]);

            return true;
        }

        return false;
    }

    /** A paid / completed order of this user that contains this e-book (tag in the notes or a line in `items`). */
    public static function hasPaidOrder(User $u, Ebook $e): bool
    {
        try {
            $orders = \App\Models\Order::where('user_id', $u->id)
                ->where(fn ($q) => $q->where('payment_status', 'paid')->orWhereIn('status', ['completed', 'delivered']))
                ->where(fn ($q) => $q->where('admin_notes', 'like', '%[EBOOK_ID:' . $e->id . ']%')
                    ->orWhere('items', 'like', '%"ebook"%'))
                ->latest('id')->limit(50)->get(['id', 'admin_notes', 'items']);
            foreach ($orders as $o) {
                if (str_contains((string) $o->admin_notes, '[EBOOK_ID:' . $e->id . ']')) {
                    return true;
                }
                foreach ((array) ($o->items ?? []) as $line) {
                    if (($line['type'] ?? '') === 'ebook' && (int) ($line['id'] ?? 0) === (int) $e->id) {
                        return true;
                    }
                }
            }
        } catch (\Throwable $ex) {
            Log::warning('EbookShop::hasPaidOrder: ' . $ex->getMessage());
        }

        return false;
    }

    // ── files ────────────────────────────────────────────────────────────────────────────────────────────────

    /** Absolute path of a stored file value (path, /storage/… or a full URL to our storage), or null. */
    public static function path(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '' || str_contains($value, '..')) {
            return null;
        }
        $clean = ltrim(preg_replace(['#^https?://[^/]+/storage/#', '#^/?storage/#'], '', $value), '/');
        foreach ([storage_path('app/public/' . $clean), storage_path('app/' . $clean), public_path('storage/' . $clean), public_path($clean)] as $p) {
            if (is_file($p) && filesize($p) > 100) {
                return $p;
            }
        }

        return null;
    }

    public static function kind(?string $path): ?string
    {
        if (!$path) {
            return null;
        }
        $fh = @fopen($path, 'rb');
        $head = $fh ? (string) fread($fh, 4) : '';
        if ($fh) fclose($fh);
        if (str_starts_with($head, '%PDF')) return 'pdf';
        if (str_starts_with($head, "PK")) return 'epub';

        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) { 'pdf' => 'pdf', 'epub' => 'epub', default => null };
    }

    /** The whole book: the EPUB when there is one (reflows on phones), otherwise the main file. */
    public static function fullFile(Ebook $e): ?string
    {
        return self::path($e->epub_file_path) ?: self::path($e->file_path);
    }

    /** The free sample: uploaded sample file, else first chapters of the EPUB, else null (no sample). */
    public static function sampleFile(Ebook $e): ?string
    {
        if ($p = self::path($e->sample_file_path)) {
            return $p;
        }
        $full = self::fullFile($e);
        if ($full && self::kind($full) === 'epub') {
            return self::buildEpubSample($e, $full);
        }

        return null;
    }

    public static function hasSample(Ebook $e): bool
    {
        if (self::path($e->sample_file_path)) {
            return true;
        }
        $full = self::fullFile($e);

        return $full && self::kind($full) === 'epub';
    }

    /**
     * A copy of the EPUB whose later chapters are replaced by a short "sample ended" page. The table of contents
     * and the first chapters stay, so the sample reads like the book. Cached under storage/app/ebook-samples.
     */
    public static function buildEpubSample(Ebook $e, string $full): ?string
    {
        $dir = storage_path('app/ebook-samples');
        $target = $dir . '/' . $e->id . '-' . substr(md5($full . '|' . filemtime($full) . '|' . filesize($full)), 0, 12) . '.epub';
        if (is_file($target)) {
            return $target;
        }
        if (!class_exists(\ZipArchive::class)) {
            return null;
        }
        try {
            if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
                return null;
            }
            $tmp = $target . '.' . getmypid() . '.tmp';
            if (!@copy($full, $tmp)) {
                return null;
            }
            $zip = new \ZipArchive();
            if ($zip->open($tmp) !== true) {
                @unlink($tmp);
                return null;
            }
            // container.xml → OPF → spine order
            $container = (string) $zip->getFromName('META-INF/container.xml');
            if (!preg_match('/full-path="([^"]+)"/', $container, $m)) {
                $zip->close(); @unlink($tmp);
                return null;
            }
            $opfPath = $m[1];
            $opfDir = str_contains($opfPath, '/') ? substr($opfPath, 0, strrpos($opfPath, '/') + 1) : '';
            $opf = (string) $zip->getFromName($opfPath);
            preg_match_all('/<item\b[^>]*>/i', $opf, $items);
            $hrefById = [];
            foreach ($items[0] as $tag) {
                if (preg_match('/\bid="([^"]+)"/', $tag, $i) && preg_match('/\bhref="([^"]+)"/', $tag, $h)) {
                    $hrefById[$i[1]] = $h[1];
                }
            }
            preg_match_all('/<itemref\b[^>]*idref="([^"]+)"/i', $opf, $spine);
            $chapters = array_values(array_filter(array_map(fn ($id) => $hrefById[$id] ?? null, $spine[1])));
            $n = count($chapters);
            if ($n < 2) {
                $zip->close(); @unlink($tmp);
                return null;
            }
            $keep = max(2, min(6, (int) ceil($n * self::SAMPLE_SHARE)));
            $keep = min($keep, $n - 1);
            $title = e($e->title);
            $stub = '<?xml version="1.0" encoding="utf-8"?><!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml" lang="bn"><head><meta charset="utf-8"/><title>' . $title . '</title></head>'
                . '<body style="text-align:center;padding:3em 1em;font-family:Kalpurush,sans-serif"><h2>নমুনা অংশ এখানেই শেষ</h2>'
                . '<p>«' . $title . '» বইটির বাকি অংশ পড়তে আইডিয়া প্রকাশনের ই-বুকশপ থেকে বইটি সংগ্রহ করুন।</p></body></html>';
            for ($i = $keep; $i < $n; $i++) {
                $name = $opfDir . rawurldecode(explode('#', $chapters[$i])[0]);
                if ($zip->locateName($name) !== false) {
                    $zip->addFromString($name, $stub);
                }
            }
            $zip->close();
            if (!@rename($tmp, $target)) {
                @unlink($tmp);
                return null;
            }
            foreach (glob($dir . '/' . $e->id . '-*.epub') ?: [] as $old) {
                if ($old !== $target) @unlink($old);
            }

            return $target;
        } catch (\Throwable $ex) {
            Log::warning('EbookShop::buildEpubSample #' . $e->id . ': ' . $ex->getMessage());

            return null;
        }
    }
}
