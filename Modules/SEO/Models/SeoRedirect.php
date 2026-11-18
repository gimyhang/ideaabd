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
    /**
     * The active rule for a request path, or null. Matches the path as the browser sent it
     * (%E0%A6…) and decoded (বাংলা), with or without a trailing slash, so a rule typed in
     * Bangla also catches the encoded address and vice versa.
     */
    public static function findForPath(string $path): ?self
    {
        $path = '/' . ltrim($path, '/');
        $forms = [];
        foreach ([$path, rawurldecode($path)] as $p) {
            $trim = rtrim($p, '/') ?: '/';
            array_push($forms, $p, $trim, $trim . '/');
        }
        $forms = array_values(array_unique($forms));

        $rule = static::where('is_active', true)->whereIn('source_url', $forms)->orderByDesc('id')->first();
        if (!$rule) {
            return null;
        }

        // Never send a page to itself (that would loop forever).
        $target = '/' . ltrim((string) (parse_url($rule->target_url, PHP_URL_PATH) ?? ''), '/');
        $sameHost = !str_starts_with($rule->target_url, 'http') || parse_url($rule->target_url, PHP_URL_HOST) === request()->getHost();
        if ($sameHost && in_array(rtrim(rawurldecode($target), '/'), array_map(fn ($f) => rtrim(rawurldecode($f), '/'), $forms), true)) {
            return null;
        }

        return $rule;
    }

    /** Redirect response for this rule (keeps the visitor's query string) and counts the hit. */
    public function toResponse(\Illuminate\Http\Request $request): \Illuminate\Http\RedirectResponse
    {
        $this->forceFill(['hits_count' => (int) $this->hits_count + 1, 'last_hit_at' => now()])->saveQuietly();

        $target = str_starts_with($this->target_url, 'http') ? $this->target_url : url($this->target_url);
        $query = $request->getQueryString();
        if ($query && !str_contains($target, '?')) {
            $target .= '?' . $query;
        }

        return redirect($target, in_array((int) $this->status_code, [301, 302, 307, 308], true) ? (int) $this->status_code : 301);
    }

    public static function normalizePath(string $url): string
    {
        $parsed = parse_url($url, PHP_URL_PATH);
        return '/' . ltrim(rtrim((string)$parsed, '/'), '/');
    }
}
