<?php

namespace Modules\SEO\Traits;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Modules\SEO\Models\SeoMeta;
use Modules\SEO\Services\AutoSeoScannerService;

trait HasSeo
{
    /**
     * Get the SEO metadata associated with this model.
     */
    public function seoMeta(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }

    /**
     * Get or automatically scan & create SEO metadata for this model.
     */
    public function getOrScanSeo(): SeoMeta
    {
        if ($this->seoMeta) {
            return $this->seoMeta;
        }

        return app(AutoSeoScannerService::class)->scanAndSave($this);
    }

    /**
     * Boot trait to automatically generate or update SEO meta on model save/create.
     */
    public static function bootHasSeo(): void
    {
        static::saved(function ($model) {
            try {
                if (config('seo.auto_scan_on_save', true)) {
                    app(AutoSeoScannerService::class)->scanAndSave($model);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('SEO Auto Scan on saved failed: ' . $e->getMessage());
            }
        });

        static::deleted(function ($model) {
            try {
                $model->seoMeta()?->delete();
            } catch (\Throwable $e) {
                // Ignore
            }
        });
    }
}
