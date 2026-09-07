@props([
    'isRoot' => true,
    'iconClass' => null
])

@php
    $id = 'fgrad_' . uniqid();
    $gradStart = $isRoot ? '#fbbf24' : '#60a5fa';
    $gradMid   = $isRoot ? '#f59e0b' : '#3b82f6';
    $gradEnd   = $isRoot ? '#d97706' : '#2563eb';
    $backColor = $isRoot ? '#b45309' : '#1d4ed8';
    $tabColor  = $isRoot ? '#fcd34d' : '#93c5fd';
@endphp

<svg class="folder-svg {{ $isRoot ? '' : 'folder-svg-sub' }}" viewBox="0 0 100 90" fill="none" xmlns="http://www.w3.org/2000/svg">
    <defs>
        {{-- Front Cover Gradient --}}
        <linearGradient id="{{ $id }}_front" x1="0%" y1="0%" x2="0%" y2="100%">
            <stop offset="0%" stop-color="{{ $gradStart }}" />
            <stop offset="60%" stop-color="{{ $gradMid }}" />
            <stop offset="100%" stop-color="{{ $gradEnd }}" />
        </linearGradient>

        {{-- Gloss / Highlight Gradient --}}
        <linearGradient id="{{ $id }}_gloss" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#ffffff" stop-opacity="0.65" />
            <stop offset="40%" stop-color="#ffffff" stop-opacity="0.1" />
            <stop offset="100%" stop-color="#ffffff" stop-opacity="0" />
        </linearGradient>

        {{-- Back Plate Gradient --}}
        <linearGradient id="{{ $id }}_back" x1="0%" y1="0%" x2="0%" y2="100%">
            <stop offset="0%" stop-color="{{ $tabColor }}" />
            <stop offset="100%" stop-color="{{ $backColor }}" />
        </linearGradient>

        {{-- Folder Paper Shadow --}}
        <filter id="{{ $id }}_drop" x="-10%" y="-10%" width="120%" height="130%">
            <feDropShadow dx="0" dy="2" stdDeviation="1.5" flood-opacity="0.15" />
        </filter>
    </defs>

    {{-- Folder Back Plate & Tab --}}
    <path d="M8 20C8 16.6863 10.6863 14 14 14H38C40.5 14 42.8 15.2 44.5 17.2L48.5 22H86C89.3137 22 92 24.6863 92 28V74C92 77.3137 89.3137 80 86 80H14C10.6863 80 8 77.3137 8 74V20Z" fill="url(#{{ $id }}_back)" />

    {{-- Inserted Clean Document Sheet (Inside Folder) --}}
    <rect x="17" y="19" width="66" height="30" rx="3" fill="#ffffff" filter="url(#{{ $id }}_drop)" />
    <rect x="23" y="24" width="28" height="2.5" rx="1.2" fill="#cbd5e1" />
    <rect x="23" y="29" width="40" height="2" rx="1" fill="#e2e8f0" />
    <rect x="23" y="33" width="34" height="2" rx="1" fill="#e2e8f0" />

    {{-- Folder Front Plate --}}
    <path d="M6 34C6 30.6863 8.6863 28 12 28H88C91.3137 28 94 30.6863 94 34V76C94 79.3137 91.3137 82 88 82H12C8.6863 82 6 79.3137 6 76V34Z" fill="url(#{{ $id }}_front)" />

    {{-- Front Gloss Highlight --}}
    <path d="M7 34C7 31.2386 9.23858 29 12 29H88C90.7614 29 93 31.2386 93 34V52C70 48 30 52 7 60V34Z" fill="url(#{{ $id }}_gloss)" />

    {{-- Center Motif / Icon --}}
    @if($iconClass)
        <foreignObject x="30" y="44" width="40" height="30">
            <div xmlns="http://www.w3.org/1999/xhtml" style="display: flex; align-items: center; justify-content: center; height: 100%; color: #ffffff; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">
                <i class="{{ $iconClass }}" style="font-size: 18px;"></i>
            </div>
        </foreignObject>
    @else
        {{-- Elegant Book/Library Symbol embossed on folder --}}
        <g opacity="0.9" transform="translate(42, 48)">
            <path d="M8 2.5C5.8 1.4 3 1.5 1 2.5V13.5C3 12.5 5.8 12.4 8 13.5C10.2 12.4 13 12.5 15 13.5V2.5C13 1.5 10.2 1.4 8 2.5Z" fill="#ffffff" fill-opacity="0.3" stroke="#ffffff" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M8 2.5V13.5" stroke="#ffffff" stroke-width="1.3" stroke-linecap="round" />
        </g>
    @endif
</svg>
