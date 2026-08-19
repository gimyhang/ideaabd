<?php

namespace App\Support;

use App\Models\AdminDashboardSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SiteSetting
{
    protected const CACHE_KEY = 'site_global_settings_cache';

    public static function all(): array
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            try {
                if (! Schema::hasTable('admin_dashboard_settings')) {
                    return [];
                }

                $rows = DB::table('admin_dashboard_settings')->get();
                $settings = [];
                foreach ($rows as $row) {
                    $val = $row->value;
                    $decoded = json_decode($val, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        if (is_string($decoded)) {
                            $second = json_decode($decoded, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($second)) {
                                $decoded = $second;
                            }
                        }
                        $settings[$row->key] = $decoded;
                    } else {
                        $settings[$row->key] = $val;
                    }
                }
                return $settings;
            } catch (\Throwable $e) {
                return [];
            }
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = self::all();
        return $all[$key] ?? $default;
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public static function name(): string
    {
        return (string) (self::get('site_name') ?: config('brand.name', 'আইডিয়া প্রকাশন'));
    }

    public static function tagline(): string
    {
        return (string) (self::get('site_tagline') ?: config('brand.tagline', 'বই ও মুক্তচিন্তার ডিজিটাল প্রকাশনা'));
    }

    public static function logoUrl(): ?string
    {
        $logo = self::get('site_logo') ?: config('brand.logo');
        return self::resolveImageUrl($logo, 'images/logo.svg');
    }

    public static function faviconUrl(): ?string
    {
        $favicon = self::get('site_favicon') ?: config('brand.favicon');
        return self::resolveImageUrl($favicon, 'images/favicon.ico');
    }

    public static function banner1Url(): ?string
    {
        $banner = self::get('home_banner_1');
        return self::resolveImageUrl($banner);
    }

    public static function banner2Url(): ?string
    {
        $banner = self::get('home_banner_2');
        return self::resolveImageUrl($banner);
    }

    public static function ecommerce(): array
    {
        $ecom = self::get('ecommerce_settings', []);
        return is_array($ecom) ? $ecom : [];
    }

    public static function helplinePhone(): string
    {
        $ecom = self::ecommerce();
        return $ecom['helpline_phone'] ?? '01726976982';
    }

    public static function helplineEmail(): string
    {
        $ecom = self::ecommerce();
        return $ecom['helpline_email'] ?? 'ideapbd@gmail.com';
    }

    public static function whatsappNumber(): string
    {
        $ecom = self::ecommerce();
        return $ecom['whatsapp_number'] ?? '01726976982';
    }

    public static function resolveImageUrl(?string $path, ?string $fallbackAsset = null): ?string
    {
        if (empty($path)) {
            return $fallbackAsset && file_exists(public_path($fallbackAsset)) ? asset($fallbackAsset) : null;
        }

        $clean = trim($path, '"\' ');

        if (str_starts_with($clean, 'http://') || str_starts_with($clean, 'https://')) {
            return $clean;
        }

        if (str_starts_with($clean, 'data:image/')) {
            return $clean;
        }

        $clean = ltrim($clean, '/');

        if (str_starts_with($clean, 'storage/')) {
            return asset($clean);
        }

        if (file_exists(public_path($clean))) {
            return asset($clean);
        }

        return asset('storage/' . $clean);
    }
}
