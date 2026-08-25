@props([
    'size'   => 38,      // px height
    'height' => null,
    'width'  => null,
    'mark'   => false,   // true → prefer the compact mark file
])

@php
    $resolvedUrl = \App\Support\SiteSetting::logoUrl();
    $brandName = \App\Support\SiteSetting::name();
    $h = $height ?? $size;
    $w = $width ?? ($h * 2); // 1:2 aspect ratio (height fixed, double width)
@endphp

@if ($resolvedUrl)
    <img src="{{ $resolvedUrl }}"
         alt="{{ $brandName }}"
         style="height:{{ $h }}px;width:{{ $w }}px;max-width:{{ $w }}px;max-height:{{ $h }}px;object-fit:contain;border-radius:8px;flex:0 0 auto;"
         {{ $attributes->merge(['class' => 'adm-brand__logo']) }}
         onerror="this.style.display='none'">
@else
    <span {{ $attributes->merge(['class' => 'adm-brand__mark']) }}
          style="width:{{ $w }}px;height:{{ $h }}px;font-size:{{ round($h * 0.45) }}px;display:inline-flex;align-items:center;justify-content:center;border-radius:8px;">
        {{ config('brand.lettermark') }}
    </span>
@endif
