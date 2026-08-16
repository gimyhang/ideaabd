@props([
    'size'  => 38,      // px
    'mark'  => false,   // true → prefer the compact mark file
])

@php
    /**
     * Renders the brand logo from config/brand.php.
     *
     * Falls back to a gradient lettermark when the configured file is missing,
     * so a fresh deploy without the image never shows a broken picture.
     */
    $path = $mark
        ? (config('brand.logo_mark') ?: config('brand.logo'))
        : (config('brand.logo') ?: config('brand.logo_mark'));

    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('admin_dashboard_settings')) {
            $dbLogo = \Illuminate\Support\Facades\DB::table('admin_dashboard_settings')->where('key', 'site_logo')->value('value');
            if ($dbLogo) {
                $path = $dbLogo;
            }
        }
    } catch (\Throwable $e) {}

    $resolvedUrl = null;
    if ($path) {
        if (str_starts_with($path, 'http')) {
            $resolvedUrl = $path;
        } elseif (file_exists(public_path($path))) {
            $resolvedUrl = asset($path);
        } elseif (file_exists(storage_path('app/public/' . ltrim($path, '/')))) {
            $resolvedUrl = asset('storage/' . ltrim($path, '/'));
        } elseif (file_exists(public_path('storage/' . ltrim($path, '/')))) {
            $resolvedUrl = asset('storage/' . ltrim($path, '/'));
        } elseif (file_exists(public_path('images/logo.svg'))) {
            $resolvedUrl = asset('images/logo.svg');
        }
    }
@endphp

@if ($resolvedUrl)
    <img src="{{ $resolvedUrl }}"
         alt="{{ config('brand.name') }}"
         width="{{ $size }}" height="{{ $size }}"
         {{ $attributes->merge(['class' => 'adm-brand__logo object-fit-contain', 'style' => 'border-radius:10px;flex:0 0 auto']) }}
         onerror="this.style.display='none'">
@else
    <span {{ $attributes->merge(['class' => 'adm-brand__mark']) }}
          style="width:{{ $size }}px;height:{{ $size }}px;font-size:{{ round($size * 0.45) }}px">
        {{ config('brand.lettermark') }}
    </span>
@endif
