<?php

namespace Modules\SEO\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SeoMeta extends Model
{
    protected $table = 'seo_metas';

    protected $fillable = [
        'seoable_type',
        'seoable_id',
        'url_path',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'canonical_url',
        'robots',
        'focus_keyword',
        'og_title',
        'og_description',
        'og_image',
        'og_type',
        'twitter_card',
        'twitter_title',
        'twitter_description',
        'twitter_image',
        'schema_type',
        'schema_json',
        'seo_score',
        'seo_analysis',
        'is_auto_generated',
        'last_scanned_at',
    ];

    protected $casts = [
        'schema_json'       => 'array',
        'seo_analysis'      => 'array',
        'is_auto_generated' => 'boolean',
        'seo_score'         => 'integer',
        'last_scanned_at'   => 'datetime',
    ];

    /** The site layout caches the record per path for 10 minutes: an edit must show at once. */
    protected static function booted(): void
    {
        $forget = function (self $m) {
            foreach (array_filter([$m->url_path, $m->getOriginal('url_path')]) as $path) {
                $encoded = implode('/', array_map('rawurlencode', explode('/', $path)));
                foreach (array_unique([$path, $encoded]) as $p) {
                    \Illuminate\Support\Facades\Cache::forget('seo_meta_path_' . md5($p));
                }
            }
        };
        static::saved($forget);
        static::deleted($forget);
    }

    /**
     * Get the owning seoable model (Book, BlogPost, Author, etc.).
     */
    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get effective Title with fallback
     */
    public function getEffectiveTitleAttribute(): string
    {
        return $this->meta_title ?: ($this->seoable?->title ?? $this->seoable?->name ?? config('app.name', 'Idea প্রকাশন'));
    }

    /**
     * Get effective Description with fallback
     */
    public function getEffectiveDescriptionAttribute(): string
    {
        return $this->meta_description ?: ($this->seoable?->excerpt ?? $this->seoable?->summary ?? $this->seoable?->bio ?? \App\Support\SiteSetting::tagline());
    }

    /**
     * Get score status badge class and label
     */
    public function getScoreBadgeAttribute(): array
    {
        $score = $this->seo_score;
        if ($score >= 80) {
            return ['class' => 'bg-success text-white', 'status' => 'উচ্চমানের (Excellent)', 'color' => '#10b981'];
        }
        if ($score >= 50) {
            return ['class' => 'bg-warning text-dark', 'status' => 'উন্নতিযোগ্য (Needs Work)', 'color' => '#f59e0b'];
        }
        return ['class' => 'bg-danger text-white', 'status' => 'দুর্বল (Poor)', 'color' => '#ef4444'];
    }
}
