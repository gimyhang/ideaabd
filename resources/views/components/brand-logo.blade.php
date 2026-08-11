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

    $exists = $path && is_file(public_path($path));
@endphp

@if ($exists)
    <img src="{{ asset($path) }}?v={{ @filemtime(public_path($path)) ?: 1 }}"
         alt="{{ config('brand.name') }}"
         width="{{ $size }}" height="{{ $size }}"
         {{ $attributes->merge(['class' => 'adm-brand__logo', 'style' => 'border-radius:10px;flex:0 0 auto']) }}>
@else
    <span {{ $attributes->merge(['class' => 'adm-brand__mark']) }}
          style="width:{{ $size }}px;height:{{ $size }}px;font-size:{{ round($size * 0.45) }}px">
        {{ config('brand.lettermark') }}
    </span>
@endif
