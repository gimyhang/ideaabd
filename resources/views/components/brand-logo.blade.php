@props([
    'size'  => 38,      // px
    'mark'  => false,   // true → prefer the compact mark file
])

@php
    $resolvedUrl = \App\Support\SiteSetting::logoUrl();
    $brandName = \App\Support\SiteSetting::name();
@endphp

@if ($resolvedUrl)
    <img src="{{ $resolvedUrl }}"
         alt="{{ $brandName }}"
         width="{{ $size }}" height="{{ $size }}"
         {{ $attributes->merge(['class' => 'adm-brand__logo object-fit-contain', 'style' => 'border-radius:10px;flex:0 0 auto']) }}
         onerror="this.style.display='none'">
@else
    <span {{ $attributes->merge(['class' => 'adm-brand__mark']) }}
          style="width:{{ $size }}px;height:{{ $size }}px;font-size:{{ round($size * 0.45) }}px">
        {{ config('brand.lettermark') }}
    </span>
@endif
