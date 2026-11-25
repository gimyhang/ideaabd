<?php

namespace App\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * IPs the admin (or the abuse guard) has shut out. Lives in the database so a block survives cache clears and
 * the admin sees and controls every one of them (admin/visitor-reports → Security).
 *
 * AbuseGuard asks isBlocked() on every request: the active list is kept in the cache for 5 minutes and every
 * change clears it. Any failure means "not blocked" — the site never goes down because of this list.
 */
class IpBlocklist
{
    private const CACHE = 'abuse:blocklist:v1';
    private const SCHEMA = 'abuse:blocklist:schema:v1';

    public static function ensureSchema(): bool
    {
        if (Cache::get(self::SCHEMA)) {
            return true;
        }
        try {
            if (!Schema::hasTable('blocked_ips')) {
                Schema::create('blocked_ips', function (Blueprint $t) {
                    $t->id();
                    $t->string('ip', 45)->unique();
                    $t->string('reason', 255)->nullable();
                    $t->string('source', 10)->default('manual'); // manual | auto
                    $t->timestamp('expires_at')->nullable()->index(); // null = until unblocked
                    $t->unsignedBigInteger('created_by')->nullable();
                    $t->timestamps();
                });
            }
            Cache::forever(self::SCHEMA, true);
            return true;
        } catch (\Throwable $e) {
            Log::warning('IpBlocklist::ensureSchema: ' . $e->getMessage());
            return false;
        }
    }

    private static function ready(): bool
    {
        return (bool) Cache::get(self::SCHEMA) || SchemaCache::has('blocked_ips');
    }

    /** @return array<string, true> active blocked IPs */
    public static function active(): array
    {
        if (!self::ready()) {
            return [];
        }

        return Cache::remember(self::CACHE, 300, function () {
            return DB::table('blocked_ips')
                ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->pluck('ip')->mapWithKeys(fn ($ip) => [$ip => true])->all();
        });
    }

    public static function isBlocked(string $ip): bool
    {
        if ($ip === '') {
            return false;
        }
        try {
            return isset(self::active()[$ip]);
        } catch (\Throwable) {
            return false;
        }
    }

    /** Block (or extend) an IP. $minutes null = until the admin unblocks it. */
    public static function block(string $ip, ?int $minutes, string $reason, string $source = 'manual', ?int $by = null): void
    {
        self::ensureSchema();
        $row = [
            'reason'     => mb_substr($reason, 0, 255),
            'source'     => $source,
            'expires_at' => $minutes ? now()->addMinutes($minutes) : null,
            'created_by' => $by,
            'updated_at' => now(),
        ];
        if (DB::table('blocked_ips')->where('ip', $ip)->exists()) {
            DB::table('blocked_ips')->where('ip', $ip)->update($row);
        } else {
            DB::table('blocked_ips')->insert($row + ['ip' => $ip, 'created_at' => now()]);
        }
        Cache::forget(self::CACHE);
    }

    /** The abuse guard blocked an IP for a while: keep a row so the admin sees it (never shortens a longer block). */
    public static function recordAuto(string $ip, int $minutes, string $why): void
    {
        try {
            if (!self::ready()) {
                return;
            }
            $row = DB::table('blocked_ips')->where('ip', $ip)->first();
            if ($row && ($row->expires_at === null || strtotime($row->expires_at) > time() + $minutes * 60)) {
                return;
            }
            self::block($ip, $minutes, 'স্বয়ংক্রিয়: ' . $why, 'auto');
        } catch (\Throwable) {
            // the guard must never fail because of this
        }
    }

    public static function unblock(string $ip): void
    {
        if (self::ready()) {
            DB::table('blocked_ips')->where('ip', $ip)->delete();
        }
        Cache::forget("abuse:block:$ip"); // the guard's own short block
        Cache::forget("abuse:strikes:$ip");
        Cache::forget(self::CACHE);
    }

    /** Rows for the admin list (expired ones are removed on the way). */
    public static function all()
    {
        if (!self::ready()) {
            return collect();
        }
        DB::table('blocked_ips')->whereNotNull('expires_at')->where('expires_at', '<=', now())->delete();

        return DB::table('blocked_ips')->orderByDesc('updated_at')->limit(200)->get();
    }

    public static function valid(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }
}
