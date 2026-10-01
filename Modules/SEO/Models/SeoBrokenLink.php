<?php

namespace Modules\SEO\Models;

use Illuminate\Database\Eloquent\Model;

class SeoBrokenLink extends Model
{
    protected $table = 'seo_broken_links';

    protected $fillable = [
        'url',
        'referer',
        'user_agent',
        'ip_address',
        'hits_count',
        'is_resolved',
        'last_hit_at',
    ];

    protected $casts = [
        'is_resolved' => 'boolean',
        'hits_count'  => 'integer',
        'last_hit_at' => 'datetime',
    ];

    /**
     * Record a 404 hit
     */
    public static function logHit(string $url, ?string $referer = null, ?string $userAgent = null, ?string $ip = null): self
    {
        $cleanUrl = '/' . ltrim(parse_url($url, PHP_URL_PATH) ?? $url, '/');

        $record = static::firstOrNew(['url' => $cleanUrl]);
        $record->referer = $referer ?: $record->referer;
        $record->user_agent = $userAgent ?: $record->user_agent;
        $record->ip_address = $ip ?: $record->ip_address;
        $record->hits_count = ($record->hits_count ?? 0) + 1;
        $record->last_hit_at = now();
        $record->save();

        return $record;
    }
}
