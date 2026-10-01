<?php

namespace Modules\SEO\Models;

use Illuminate\Database\Eloquent\Model;

class SeoRedirect extends Model
{
    protected $table = 'seo_redirects';

    protected $fillable = [
        'source_url',
        'target_url',
        'status_code',
        'is_active',
        'hits_count',
        'last_hit_at',
        'notes',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'status_code'  => 'integer',
        'hits_count'   => 'integer',
        'last_hit_at'  => 'datetime',
    ];

    /**
     * Clean and normalize a URL path
     */
    public static function normalizePath(string $url): string
    {
        $parsed = parse_url($url, PHP_URL_PATH);
        return '/' . ltrim(rtrim((string)$parsed, '/'), '/');
    }
}
