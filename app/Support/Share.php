<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Share counting for everything that is not a book (books keep their own book_shares table).
 * Types: post, ebook, webzine, author (by id) and page (named site pages below).
 */
class Share
{
    public const CHANNELS = ['native', 'copy', 'whatsapp', 'facebook', 'messenger', 'x', 'telegram', 'linkedin', 'email', 'sms', 'qr'];

    /** Site pages that can be shared and counted: key => route name. */
    public const PAGES = ['home' => 'home', 'about' => 'about'];

    /** Model per countable type (only these may be counted). */
    public const MODELS = [
        'post' => \Modules\Blog\Models\BlogPost::class,
        'ebook' => \Modules\Ebook\Models\Ebook::class,
        'webzine' => \Modules\Webzine\Models\Webzine::class,
        'author' => \Modules\Author\Models\Author::class,
    ];

    public static function ready(): bool
    {
        static $ready;
        return $ready ??= rescue(fn () => Schema::hasTable('content_shares'), false, false);
    }

    /** Does the thing being shared exist? Stops the counter from being filled with made-up keys. */
    public static function valid(string $type, string $key): bool
    {
        if ($type === 'page') {
            return array_key_exists($key, self::PAGES);
        }
        $model = self::MODELS[$type] ?? null;
        if (! $model || ! ctype_digit($key) || ! class_exists($model)) {
            return false;
        }

        return $model::query()->whereKey((int) $key)->exists();
    }

    public static function count(string $type, string|int $key): int
    {
        if (! self::ready()) {
            return 0;
        }

        return (int) Cache::remember(self::cacheKey($type, (string) $key), 600, fn () => DB::table('content_shares')
            ->where('type', $type)->where('item_key', (string) $key)->count());
    }

    /**
     * Records one share. The same visitor sharing the same thing on the same channel within an hour counts once.
     * Returns the new total.
     */
    public static function record(string $type, string $key, string $channel, ?int $userId, string $visitor): int
    {
        if (! self::ready()) {
            return 0;
        }
        $recent = DB::table('content_shares')
            ->where('visitor_hash', $visitor)->where('type', $type)->where('item_key', $key)->where('channel', $channel)
            ->where('created_at', '>=', now()->subHour())->exists();
        if (! $recent) {
            DB::table('content_shares')->insert([
                'type' => $type, 'item_key' => $key, 'channel' => $channel,
                'user_id' => $userId, 'visitor_hash' => $visitor, 'created_at' => now(),
            ]);
            Cache::forget(self::cacheKey($type, $key));
        }

        return self::count($type, $key);
    }

    private static function cacheKey(string $type, string $key): string
    {
        return 'share_count:' . $type . ':' . $key;
    }
}
