@extends('layouts.admin')

@section('title', 'Media & Asset Studio — ideaabd')
@section('heading', 'Media & Asset Studio')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Media & Asset Studio</li>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-media.css') }}">
@endpush

@section('actions')
    {{-- Desktop Action Bar --}}
    <div class="d-none d-md-flex align-items-center gap-2 flex-wrap">
        {{-- 1-Click Purge Unused / Replaced Images & Cache --}}
        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold shadow-xs d-inline-flex align-items-center gap-1.5" id="btnPurgeUnusedMedia" onclick="triggerPurgeUnusedMedia(this)" title="Purge unused, replaced, or orphaned cache images not in database">
            <i class="fa-solid fa-broom text-danger"></i>
            <span>Purge Unused / Cache</span>
        </button>

        {{-- 1-Click Convert All Existing to WebP --}}
        <button type="button" class="btn btn-webp-gradient btn-sm shadow-xs" id="btnConvertAllWebp" onclick="openConvertWebpEngineModal()" title="Batch convert all PNG and JPG images to WebP">
            <i class="fa-solid fa-bolt-lightning"></i>
            <span>Convert All to WebP</span>
        </button>

        {{-- Create Folder Modal Trigger --}}
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold shadow-xs d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#createFolderModal">
            <i class="fa-solid fa-folder-plus text-warning"></i>
            <span>New Folder</span>
        </button>

        {{-- 1-Click Auto Optimizer Engine --}}
        <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-bold shadow-xs d-inline-flex align-items-center gap-1.5" id="btnOptimizeAll" onclick="runMediaOptimization(this)">
            <i class="fa-solid fa-bolt text-success"></i>
            <span>Auto Optimize</span>
        </button>

        {{-- Upload Modal Trigger --}}
        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3.5 shadow-sm fw-bold d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#uploadMediaModal">
            <i class="fa-solid fa-cloud-arrow-up"></i>
            <span>Upload Media</span>
        </button>
    </div>

    {{-- Mobile Header Action Bar --}}
    <div class="d-flex d-md-none align-items-center gap-1.5 w-100 justify-content-between">
        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm fw-bold d-inline-flex align-items-center gap-1.5 flex-grow-1 justify-content-center" data-bs-toggle="modal" data-bs-target="#uploadMediaModal">
            <i class="fa-solid fa-cloud-arrow-up"></i>
            <span>Upload Media</span>
        </button>

        <div class="dropdown">
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-2.5 fw-semibold d-inline-flex align-items-center gap-1" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-ellipsis-vertical"></i>
                <span>Tools</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2" style="min-width: 220px;">
                <li>
                    <button class="dropdown-item rounded-3 py-2 fw-semibold small text-success d-flex align-items-center gap-2" onclick="openConvertWebpEngineModal()">
                        <i class="fa-solid fa-bolt-lightning text-success"></i> Convert All to WebP
                    </button>
                </li>
                <li>
                    <button class="dropdown-item rounded-3 py-2 fw-semibold small text-primary d-flex align-items-center gap-2" onclick="runMediaOptimization(this)">
                        <i class="fa-solid fa-bolt text-primary"></i> Auto Optimize All
                    </button>
                </li>
                <li>
                    <button class="dropdown-item rounded-3 py-2 fw-semibold small text-dark d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createFolderModal">
                        <i class="fa-solid fa-folder-plus text-warning"></i> Create New Folder
                    </button>
                </li>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <button class="dropdown-item rounded-3 py-2 fw-semibold small text-danger d-flex align-items-center gap-2" onclick="triggerPurgeUnusedMedia(this)">
                        <i class="fa-solid fa-broom text-danger"></i> Purge Unused / Cache
                    </button>
                </li>
            </ul>
        </div>
    </div>
@endsection

@section('content')
<div class="d-flex flex-column gap-3.5 pb-5 mb-4" style="padding-bottom: 90px !important;">

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-0 rounded-4 shadow-xs border-0 border-start border-4 border-success bg-white py-3 px-4" role="alert">
            <i class="fa-solid fa-circle-check text-success fs-4 me-3"></i>
            <div class="fw-semibold text-dark">{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-0 rounded-4 shadow-xs border-0 border-start border-4 border-danger bg-white py-3 px-4" role="alert">
            <i class="fa-solid fa-triangle-exclamation text-danger fs-4 me-3"></i>
            <div class="fw-semibold text-dark">{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Dynamic Live Alert Container -->
    <div id="mediaLiveAlert"></div>

    <!-- Dynamic Classic WebP & Storage Intelligence Hero Bar -->
    <div class="card bg-white rounded-4 shadow-sm border-0 overflow-hidden media-hero-kpi-bar">
        <div class="p-3 p-md-3.5">
            <div class="row g-3 align-items-center justify-content-between">
                {{-- Left: WebP Speed Adoption & Dynamic Filter Meter --}}
                <div class="col-12 col-lg-7">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success text-white rounded-pill px-2.5 py-1 fw-bold font-monospace d-flex align-items-center gap-1.5 shadow-xs">
                                <span class="spinner-grow spinner-grow-sm text-white" style="width: 7px; height: 7px;" role="status"></span>
                                WebP Speed Engine
                            </span>
                            <h5 class="fw-bold text-dark mb-0 font-monospace fs-6">
                                <span class="text-success">{{ $webpPercent }}%</span> Next-Gen Adoption
                            </h5>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('admin.media.index', array_merge(request()->query(), ['format' => 'webp'])) }}" 
                               class="badge {{ $formatFilter === 'webp' ? 'bg-success text-white' : 'bg-success-subtle text-success' }} text-decoration-none rounded-pill px-2.5 py-1 font-monospace border border-success-subtle transition-all" title="Filter WebP Files">
                                <i class="fa-solid fa-bolt me-1"></i> {{ $webpCount }} WebP
                            </a>
                            <a href="{{ route('admin.media.index', array_merge(request()->query(), ['format' => 'jpg'])) }}" 
                               class="badge {{ ($formatFilter !== 'all' && $formatFilter !== 'webp') ? 'bg-warning text-dark' : 'bg-light text-muted' }} text-decoration-none rounded-pill px-2.5 py-1 font-monospace border transition-all" title="Filter Non-WebP Legacy Files">
                                {{ max(0, $totalCount - $webpCount) }} Legacy (JPG/PNG)
                            </a>
                        </div>
                    </div>

                    {{-- Dynamic Animated Progress Bar --}}
                    <div class="progress rounded-pill bg-light border overflow-hidden" style="height: 8px;">
                        <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" 
                             role="progressbar" 
                             style="width: {{ $webpPercent }}%;" 
                             aria-valuenow="{{ $webpPercent }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between text-muted fs-xs font-monospace mt-1.5">
                        <span><i class="fa-solid fa-gauge-high text-success me-1"></i> Ultra-Fast Loading & 50% Reduced Payload</span>
                        <span>{{ $webpPercent >= 80 ? '🌟 Excellent Optimization' : '⚡ 1-Click Conversion Ready' }}</span>
                    </div>
                </div>

                {{-- Right: Quick Stats & 1-Click Convert Engine Button --}}
                <div class="col-12 col-lg-5">
                    <div class="d-flex flex-wrap align-items-center justify-content-lg-end gap-2.5">
                        <div class="d-flex align-items-center gap-2.5 bg-light border rounded-pill px-3 py-1.5">
                            <div class="text-end">
                                <div class="fw-bold text-dark font-monospace" style="font-size: 0.85rem;">{{ number_format($totalCount) }} Assets</div>
                                <div class="text-muted fs-xs font-monospace">{{ $totalFormatted }} Disk</div>
                            </div>
                            <i class="fa-solid fa-hard-drive text-primary ms-1 fs-5"></i>
                        </div>

                        <button type="button" class="btn btn-webp-gradient rounded-pill px-3.5 py-2 fw-bold d-flex align-items-center gap-2" onclick="openConvertWebpEngineModal()">
                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                            <span>1-Click WebP Booster</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2-Row Modern Folder Directories Button Grid -->
    <div class="card bg-white rounded-4 shadow-sm border-0 p-3.5 media-folder-hub-card">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 pb-2 border-bottom">
            <div class="d-flex align-items-center gap-2">
                <div class="folder-header-icon-box">
                    <i class="fa-solid fa-folder-tree"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0" style="font-size: 0.94rem;">Folder Directories Library</h6>
                    <small class="text-muted font-monospace" style="font-size: 11px;">Categorized & organized storage directories</small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark border rounded-pill px-3 py-1.5 font-monospace small">
                    <i class="fa-solid fa-folder-open text-primary me-1"></i> {{ count($folderDefs) }} Folder Directories
                </span>
            </div>
        </div>

        <div class="media-folder-grid-container">
            {{-- 1. All Media Master Button --}}
            <a href="{{ route('admin.media.index', array_merge(request()->query(), ['folder' => 'all'])) }}" 
               class="folder-btn-card folder-theme-indigo {{ $folderFilter === 'all' ? 'active' : '' }}" 
               data-folder="all"
               title="All Media Library ({{ $totalFormatted }})">
                <div class="folder-btn-icon">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
                <div class="folder-btn-content">
                    <span class="folder-btn-title">All Media Files</span>
                    <span class="folder-btn-meta">
                        <span class="folder-btn-count">{{ $totalCount }} files</span>
                        <span class="folder-btn-size">{{ $totalFormatted }}</span>
                    </span>
                </div>
                @if($folderFilter === 'all')
                    <span class="folder-btn-active-check"><i class="fa-solid fa-circle-check"></i></span>
                @endif
            </a>

            {{-- 2. Categorized Folders --}}
            @foreach($folderDefs as $k => $fInfo)
                @php
                    $fStat = $folderStats[$k] ?? ['count' => 0, 'formatted' => '0 B'];
                    $themeClass = $fInfo['theme'] ?? ('folder-theme-' . ($fInfo['color'] ?? 'indigo'));
                @endphp
                <a href="{{ route('admin.media.index', array_merge(request()->query(), ['folder' => $k])) }}" 
                   class="folder-btn-card {{ $themeClass }} {{ $folderFilter === $k ? 'active' : '' }}" 
                   data-folder="{{ $k }}"
                   title="{{ $fInfo['label'] }} ({{ $fStat['formatted'] }})">
                    <div class="folder-btn-icon">
                        <i class="{{ $fInfo['icon'] }}"></i>
                    </div>
                    <div class="folder-btn-content">
                        <span class="folder-btn-title">{{ $fInfo['label'] }}</span>
                        <span class="folder-btn-meta">
                            <span class="folder-btn-count">{{ $fStat['count'] }} files</span>
                            <span class="folder-btn-size">{{ $fStat['formatted'] }}</span>
                        </span>
                    </div>
                    @if($folderFilter === $k)
                        <span class="folder-btn-active-check"><i class="fa-solid fa-circle-check"></i></span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>

    <!-- Advanced Filter, Search, Sorter & View Switcher Bar -->
    <div class="card bg-white rounded-4 shadow-sm border-0 p-3">
        <form action="{{ route('admin.media.index') }}" method="GET" id="mediaFilterForm">
            <input type="hidden" name="folder" value="{{ $folderFilter }}">
            <input type="hidden" name="view" id="mediaViewModeInput" value="{{ $viewMode }}">
            <input type="hidden" name="per_page" id="mediaPerPageInput" value="{{ $perPage }}">

            <div class="row g-2 align-items-center">
                {{-- Live Search Input --}}
                <div class="col-12 col-md-4 col-xl-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-primary">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="search" name="search" id="mediaLiveSearch" class="form-control border-start-0 ps-0 fw-semibold" 
                               placeholder="Search file name or folder..." 
                               value="{{ $search }}" oninput="filterMediaLive(this.value)">
                    </div>
                </div>

                {{-- Format Filter --}}
                <div class="col-6 col-md-2 col-xl-2">
                    <select name="format" class="form-select form-select-sm rounded-3 fw-semibold" onchange="document.getElementById('mediaFilterForm').submit()">
                        <option value="all" {{ $formatFilter === 'all' ? 'selected' : '' }}>All Formats</option>
                        <option value="webp" {{ $formatFilter === 'webp' ? 'selected' : '' }}>WebP</option>
                        <option value="png" {{ $formatFilter === 'png' ? 'selected' : '' }}>PNG</option>
                        <option value="jpg" {{ $formatFilter === 'jpg' ? 'selected' : '' }}>JPG / JPEG</option>
                        <option value="svg" {{ $formatFilter === 'svg' ? 'selected' : '' }}>SVG Vector</option>
                        <option value="gif" {{ $formatFilter === 'gif' ? 'selected' : '' }}>GIF Animation</option>
                    </select>
                </div>

                {{-- Dimension Filter --}}
                <div class="col-6 col-md-2 col-xl-2">
                    <select name="dim" class="form-select form-select-sm rounded-3 fw-semibold" onchange="document.getElementById('mediaFilterForm').submit()">
                        <option value="all" {{ $dimensionFilter === 'all' ? 'selected' : '' }}>All Dimensions</option>
                        <option value="portrait" {{ $dimensionFilter === 'portrait' ? 'selected' : '' }}>Book / Portrait (2:3)</option>
                        <option value="banner" {{ $dimensionFilter === 'banner' ? 'selected' : '' }}>Banner (≥1200px)</option>
                        <option value="square" {{ $dimensionFilter === 'square' ? 'selected' : '' }}>Square (1:1)</option>
                        <option value="thumb" {{ $dimensionFilter === 'thumb' ? 'selected' : '' }}>Thumbnail (≤400px)</option>
                    </select>
                </div>

                {{-- Sorter --}}
                <div class="col-6 col-md-2 col-xl-2">
                    <select name="sort" class="form-select form-select-sm rounded-3 fw-semibold" onchange="document.getElementById('mediaFilterForm').submit()">
                        <option value="latest" {{ $sort === 'latest' ? 'selected' : '' }}>Latest Upload</option>
                        <option value="oldest" {{ $sort === 'oldest' ? 'selected' : '' }}>Oldest First</option>
                        <option value="size_desc" {{ $sort === 'size_desc' ? 'selected' : '' }}>Size (High to Low)</option>
                        <option value="size_asc" {{ $sort === 'size_asc' ? 'selected' : '' }}>Size (Low to High)</option>
                        <option value="name_asc" {{ $sort === 'name_asc' ? 'selected' : '' }}>Name (A - Z)</option>
                        <option value="name_desc" {{ $sort === 'name_desc' ? 'selected' : '' }}>Name (Z - A)</option>
                    </select>
                </div>

                {{-- View Switcher Buttons & Select All --}}
                <div class="col-6 col-md-2 col-xl-2 d-flex align-items-center justify-content-end gap-2">
                    <div class="form-check d-flex align-items-center gap-1.5 mb-0" title="Select all items">
                        <input class="form-check-input" type="checkbox" id="selectAllMaster" onchange="toggleSelectAll(this)">
                        <label class="form-check-label small fw-bold text-dark cursor-pointer" for="selectAllMaster">Select All</label>
                    </div>

                    <div class="btn-group btn-group-sm rounded-pill overflow-hidden border">
                        <button type="button" class="btn {{ $viewMode === 'grid' ? 'btn-primary' : 'btn-light' }} px-2.5" onclick="document.getElementById('mediaViewModeInput').value='grid'; document.getElementById('mediaFilterForm').submit();" title="Grid View">
                            <i class="fa-solid fa-table-cells-large"></i>
                        </button>
                        <button type="button" class="btn {{ $viewMode === 'list' ? 'btn-primary' : 'btn-light' }} px-2.5" onclick="document.getElementById('mediaViewModeInput').value='list'; document.getElementById('mediaFilterForm').submit();" title="List View">
                            <i class="fa-solid fa-list"></i>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Media Library Content Area -->
    @if($viewMode === 'grid')
        {{-- ================================================================= --}}
        {{-- GRID VIEW                                                         --}}
        {{-- ================================================================= --}}
        <div class="row g-2.5 g-md-3 {{ $folderFilter === 'covers' || $folderFilter === 'books' ? 'is-book-folder' : '' }}" id="mediaGridContainer">
            @forelse($paginatedItems as $index => $item)
                <div class="col-6 col-sm-4 col-md-3 col-lg-3 col-xl-2 media-item-card" 
                     data-filename="{{ strtolower($item['filename']) }}" 
                     data-ext="{{ strtolower($item['ext']) }}" 
                     data-folder="{{ strtolower($item['folder']) }}"
                     data-index="{{ $index }}"
                     data-url="{{ $item['url'] }}"
                     data-path="{{ $item['path'] }}"
                     data-title="{{ $item['item_title'] ?? $item['filename'] }}"
                     data-size="{{ $item['size'] }}"
                     data-date="{{ $item['updated_at']->format('d M, Y h:i A') }}"
                     data-res="{{ $item['width'] ? $item['width'].'x'.$item['height'] : 'N/A' }}"
                     data-folderlabel="{{ $item['folder_label'] }}">
                    <div class="card media-card position-relative shadow-xs" onclick="openLightboxByIndex({{ $index }})" style="cursor: pointer;">
                        {{-- Selection Checkbox --}}
                        <div class="media-select-cb-wrapper" onclick="event.stopPropagation();">
                            <input type="checkbox" class="form-check-input media-select-cb" 
                                   data-path="{{ $item['path'] }}" 
                                   data-url="{{ $item['url'] }}" 
                                   data-filename="{{ $item['filename'] }}">
                        </div>

                        {{-- Thumbnail Container --}}
                        <div class="media-thumb-container">
                            <img src="{{ $item['url'] }}" alt="{{ $item['filename'] }}" class="media-thumb-img" loading="lazy">
                            
                            {{-- Dimension & Format Badges --}}
                            <div class="media-badge-meta">
                                <span class="media-badge-tag {{ $item['is_webp'] ? 'badge-webp-glow' : '' }}">{{ strtoupper($item['ext']) }}</span>
                                @if($item['width'] && $item['height'])
                                    <span class="media-badge-tag">{{ $item['width'] }}×{{ $item['height'] }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- Card Details (Clean & Minimal) --}}
                        <div class="media-details">
                            <div class="media-item-title" title="{{ $item['item_title'] ?? $item['filename'] }}">
                                {{ $item['item_title'] ?? $item['filename'] }}
                            </div>
                            
                            <div class="d-flex align-items-center justify-content-between mt-1 text-muted fs-xs font-monospace">
                                <span class="badge bg-light text-dark border px-1.5 py-0.5 rounded-pill text-truncate" style="max-width: 100px;" title="{{ $item['folder_label'] }}">
                                    <i class="{{ $item['folder_icon'] }} me-0.5 text-primary"></i> {{ $item['folder_label'] }}
                                </span>
                                <span class="fw-bold text-dark">{{ $item['size'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12" id="emptyMediaNotice">
                    <div class="card bg-white rounded-4 p-5 text-center text-muted border-0 shadow-sm">
                        <i class="fa-solid fa-images fs-1 text-secondary mb-3 opacity-50"></i>
                        <h5 class="text-dark fw-bold">No Media Files Found</h5>
                        <p class="small mb-3">Click <strong>Upload Media</strong> button above to add images.</p>
                    </div>
                </div>
            @endforelse
        </div>
    @else
        {{-- ================================================================= --}}
        {{-- LIST / TABLE VIEW                                                 --}}
        {{-- ================================================================= --}}
        <div class="card bg-white rounded-4 shadow-sm border-0 p-0 overflow-hidden">
            <div class="table-responsive">
                <table class="media-table mb-0" id="mediaTable">
                    <thead>
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" class="form-check-input" onchange="toggleSelectAll(this)">
                            </th>
                            <th style="width: 60px;">Preview</th>
                            <th>File Name & Details</th>
                            <th>Folder</th>
                            <th>Resolution</th>
                            <th>Format</th>
                            <th>Size</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paginatedItems as $index => $item)
                            <tr class="media-item-row" 
                                data-filename="{{ strtolower($item['filename']) }}" 
                                data-ext="{{ strtolower($item['ext']) }}" 
                                data-folder="{{ strtolower($item['folder']) }}"
                                data-index="{{ $index }}"
                                data-url="{{ $item['url'] }}"
                                data-path="{{ $item['path'] }}"
                                data-title="{{ $item['item_title'] ?? $item['filename'] }}"
                                data-size="{{ $item['size'] }}"
                                data-date="{{ $item['updated_at']->format('d M, Y h:i A') }}"
                                data-res="{{ $item['width'] ? $item['width'].'x'.$item['height'] : 'N/A' }}"
                                data-folderlabel="{{ $item['folder_label'] }}">
                                <td>
                                    <input type="checkbox" class="form-check-input media-select-cb" 
                                           data-path="{{ $item['path'] }}" 
                                           data-url="{{ $item['url'] }}" 
                                           data-filename="{{ $item['filename'] }}">
                                </td>
                                <td>
                                    <img src="{{ $item['url'] }}" alt="{{ $item['filename'] }}" class="media-table-thumb" 
                                         onclick="openLightboxByIndex({{ $index }})" loading="lazy">
                                </td>
                                <td>
                                    @if(!empty($item['item_title']))
                                        <div class="fw-bold text-dark text-truncate" style="max-width: 280px;" title="{{ $item['item_title'] }}">{{ $item['item_title'] }}</div>
                                        <small class="text-muted font-monospace fs-xs">{{ $item['item_subtitle'] ?? $item['filename'] }}</small>
                                    @else
                                        <div class="fw-bold text-dark text-truncate" style="max-width: 280px;" title="{{ $item['filename'] }}">{{ $item['filename'] }}</div>
                                        <small class="text-muted font-monospace fs-xs">{{ $item['mime'] }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border rounded-pill font-monospace px-2.5 py-1">
                                        <i class="{{ $item['folder_icon'] }} text-primary me-1"></i> {{ $item['folder_label'] }}
                                    </span>
                                </td>
                                <td>
                                    @if($item['width'] && $item['height'])
                                        <span class="badge bg-dark-subtle text-dark rounded-pill font-monospace px-2 py-0.5" style="font-size: 11px;">
                                            {{ $item['width'] }} × {{ $item['height'] }} ({{ $item['aspect_ratio'] }})
                                        </span>
                                    @else
                                        <span class="text-muted font-monospace small">Vector / Auto</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $item['is_webp'] ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-primary-subtle text-primary' }} font-monospace text-uppercase px-2 py-0.5">
                                        {{ $item['ext'] }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold font-monospace text-dark">{{ $item['size'] }}</span>
                                </td>
                                <td>
                                    <span class="text-muted small font-monospace">{{ $item['updated_at']->format('d M, Y') }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex align-items-center justify-content-end gap-1.5">
                                        <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-1 fw-semibold" onclick="openStudioModal('{{ $item['url'] }}', '{{ addslashes($item['path']) }}', '{{ addslashes($item['item_title'] ?? $item['filename']) }}', {{ $item['width'] ?? 0 }}, {{ $item['height'] ?? 0 }})">
                                            <i class="fa-solid fa-palette me-1"></i> Studio
                                        </button>
                                        <a href="{{ $item['url'] }}" download="{{ $item['filename'] }}" class="btn btn-xs btn-outline-success rounded-pill px-2 py-1" title="Download">
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-1" onclick="copySnippet('url', '{{ $item['url'] }}', '{{ addslashes($item['item_title'] ?? $item['filename']) }}')" title="Copy URL">
                                            <i class="fa-regular fa-copy"></i>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary border-0 p-1" onclick="openReplaceModal('{{ addslashes($item['path']) }}', '{{ addslashes($item['filename']) }}')" title="Replace Image">
                                            <i class="fa-solid fa-arrow-right-arrow-left"></i>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary border-0 p-1" onclick="openRenameModal('{{ addslashes($item['path']) }}', '{{ addslashes($item['filename']) }}')" title="Rename File">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <form action="{{ route('admin.media.destroy') }}" method="POST"
                                              data-confirm="Are you sure you want to delete this media file?"
                                              data-confirm-title="Delete Media File"
                                              data-confirm-icon="warning"
                                              data-confirm-btn="<i class='fa-solid fa-trash-can me-1'></i> Delete"
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="path" value="{{ $item['path'] }}">
                                            <button type="submit" class="btn btn-xs btn-outline-danger border-0 p-1" title="Delete">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-images fs-1 text-secondary opacity-50 mb-2"></i>
                                    <div>No media files found.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Pagination & Per-Page Controls Bar --}}
    @if($filteredCount > 0)
        <div class="card bg-white rounded-4 shadow-sm border-0 p-3 mt-2">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="small text-muted font-monospace">
                    Showing <strong>{{ count($paginatedItems) }}</strong> of <strong>{{ number_format($filteredCount) }}</strong> files (Page {{ $currentPage }} of {{ $totalPages }})
                </div>

                <div class="d-flex align-items-center gap-3">
                    {{-- Per-Page Selector --}}
                    <div class="d-flex align-items-center gap-1.5">
                        <span class="small text-muted fw-semibold">Per Page:</span>
                        <select class="form-select form-select-sm rounded-pill font-monospace" style="width: 80px;" onchange="changePerPage(this.value)">
                            <option value="24" {{ $perPage == '24' ? 'selected' : '' }}>24</option>
                            <option value="48" {{ $perPage == '48' ? 'selected' : '' }}>48</option>
                            <option value="96" {{ $perPage == '96' ? 'selected' : '' }}>96</option>
                            <option value="all" {{ $perPage == 'all' ? 'selected' : '' }}>All</option>
                        </select>
                    </div>

                    {{-- Page Navigation Links --}}
                    @if($totalPages > 1 && $perPage !== 'all')
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                @if($currentPage > 1)
                                    <li class="page-item">
                                        <a class="page-link rounded-pill me-1 px-2.5" href="{{ route('admin.media.index', array_merge(request()->query(), ['page' => $currentPage - 1])) }}">
                                            <i class="fa-solid fa-chevron-left"></i>
                                        </a>
                                    </li>
                                @endif

                                @for($p = max(1, $currentPage - 2); $p <= min($totalPages, $currentPage + 2); $p++)
                                    <li class="page-item {{ $p === $currentPage ? 'active' : '' }}">
                                        <a class="page-link rounded-circle mx-0.5 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;" href="{{ route('admin.media.index', array_merge(request()->query(), ['page' => $p])) }}">
                                            {{ $p }}
                                        </a>
                                    </li>
                                @endfor

                                @if($currentPage < $totalPages)
                                    <li class="page-item">
                                        <a class="page-link rounded-pill ms-1 px-2.5" href="{{ route('admin.media.index', array_merge(request()->query(), ['page' => $currentPage + 1])) }}">
                                            <i class="fa-solid fa-chevron-right"></i>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </nav>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- No Search Results Placeholder -->
    <div id="noSearchMatchNotice" class="d-none col-12 mt-3">
        <div class="card bg-white rounded-4 p-5 text-center text-muted border-0 shadow-sm">
            <i class="fa-solid fa-magnifying-glass fs-1 text-secondary mb-3 opacity-50"></i>
            <h5 class="text-dark fw-bold">No Matching Files</h5>
            <p class="small mb-0">Try searching with a different file name, extension or folder.</p>
        </div>
    </div>

</div>

{{-- ========================================================================= --}}
{{-- FLOATING BATCH ACTIONS TOOLBAR (NON-CONFLICTING & ULTRA-RESPONSIVE)        --}}
{{-- ========================================================================= --}}
<div id="mediaFloatingBar" class="media-floating-bar" role="toolbar" aria-label="Selected Media Actions">
    <div class="d-flex align-items-center">
        <span class="badge bg-primary text-white font-monospace px-2.5 py-1.5 rounded-pill shadow-xs" id="selectedMediaCountBadge">0 Selected</span>
    </div>
    <div class="media-floating-actions-scroll">
        <button type="button" class="btn-floating-action btn-floating-warning" onclick="executeBulkConvertToWebp()" title="Convert selected files to WebP">
            <i class="fa-solid fa-bolt-lightning text-warning"></i> <span>WebP</span>
        </button>
        <button type="button" class="btn-floating-action" onclick="copySelectedUrls()" title="Copy URL of selected files">
            <i class="fa-solid fa-copy"></i> <span>Copy URLs</span>
        </button>
        <button type="button" class="btn-floating-action" onclick="openBulkMoveModal()" title="Move selected files to another folder">
            <i class="fa-solid fa-folder-tree"></i> <span>Move</span>
        </button>
        <button type="button" class="btn-floating-action" onclick="executeBulkDownloadZip()" title="Download selected files as ZIP">
            <i class="fa-solid fa-file-zipper"></i> <span>ZIP</span>
        </button>
        <button type="button" class="btn-floating-action btn-floating-danger" onclick="executeBulkDelete()" title="Delete selected files">
            <i class="fa-solid fa-trash-can"></i> <span>Delete</span>
        </button>
    </div>
    <button type="button" class="btn-floating-close ms-auto" onclick="clearAllSelections()" title="Deselect All (Esc)">
        <i class="fa-solid fa-xmark"></i>
    </button>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 1: ✨ IMAGE CUSTOMIZER & STUDIO                                      --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="mediaStudioModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header border-bottom py-3 px-4 bg-dark text-white rounded-top-4">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2" id="studioModalTitle">
                    <i class="fa-solid fa-wand-magic-sparkles text-warning"></i>
                    <span>Image Customizer & Studio</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="row g-4">
                    {{-- Left Side: Live Canvas Workspace --}}
                    <div class="col-12 col-lg-7">
                        <div class="studio-canvas-container mb-2">
                            <canvas id="studioCanvas" class="studio-canvas"></canvas>
                        </div>
                        <div class="d-flex align-items-center justify-content-between text-muted small px-1 font-monospace">
                            <span id="studioOrigMetaDisplay">Original Resolution: -</span>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5" onclick="downloadStudioCanvasLocally()">
                                    <i class="fa-solid fa-download me-1"></i> Download Local Copy
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Right Side: Customizer Controls Panel --}}
                    <div class="col-12 col-lg-5" style="max-height: 560px; overflow-y: auto; scrollbar-width: thin;">
                        
                        {{-- 1. Aspect Ratio & Presets --}}
                        <div class="studio-control-box">
                            <div class="studio-control-title">
                                <i class="fa-solid fa-crop text-primary"></i> Aspect Ratio & Size Presets
                            </div>
                            <div class="d-flex flex-wrap gap-1.5 mb-2.5">
                                <span class="studio-preset-chip active" data-preset="original" onclick="setStudioPreset('original')">Original</span>
                                <span class="studio-preset-chip" data-preset="book-cover-std" onclick="setStudioPreset('book-cover-std')">📚 Book Cover (800×1200)</span>
                                <span class="studio-preset-chip" data-preset="book-cover-compact" onclick="setStudioPreset('book-cover-compact')">📖 Compact (600×900)</span>
                                <span class="studio-preset-chip" data-preset="1:1" onclick="setStudioPreset('1:1')">1:1 Square</span>
                                <span class="studio-preset-chip" data-preset="16:9" onclick="setStudioPreset('16:9')">16:9 Banner</span>
                                <span class="studio-preset-chip" data-preset="4:3" onclick="setStudioPreset('4:3')">4:3 Standard</span>
                                <span class="studio-preset-chip" data-preset="1200x630" onclick="setStudioPreset('1200x630')">🔗 Social Banner (1200×630)</span>
                                <span class="studio-preset-chip" data-preset="1080x1080" onclick="setStudioPreset('1080x1080')">📱 Square Post (1080×1080)</span>
                                <span class="studio-preset-chip" data-preset="1920x1080" onclick="setStudioPreset('1920x1080')">1920×1080 HD</span>
                            </div>
                            <div class="row g-2 align-items-center">
                                <div class="col-5">
                                    <label class="form-label fs-xs text-muted mb-0.5">Width (px)</label>
                                    <input type="number" id="studioWidthInput" class="form-control form-control-sm font-monospace fw-bold" placeholder="Width">
                                </div>
                                <div class="col-2 text-center pt-3">
                                    <i class="fa-solid fa-xmark text-muted"></i>
                                </div>
                                <div class="col-5">
                                    <label class="form-label fs-xs text-muted mb-0.5">Height (px)</label>
                                    <input type="number" id="studioHeightInput" class="form-control form-control-sm font-monospace fw-bold" placeholder="Height">
                                </div>
                                <div class="col-12 mt-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="studioLockRatio" checked>
                                        <label class="form-check-label small fw-semibold text-dark" for="studioLockRatio">Lock Aspect Ratio</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 2. Transform: Rotate & Flip --}}
                        <div class="studio-control-box">
                            <div class="studio-control-title">
                                <i class="fa-solid fa-arrows-rotate text-info"></i> Rotate & Transform
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="studioRotate(-90)">
                                    <i class="fa-solid fa-rotate-left me-1"></i> -90°
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="studioRotate(90)">
                                    <i class="fa-solid fa-rotate-right me-1"></i> +90°
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="studioFlip('h')">
                                    <i class="fa-solid fa-arrows-left-right me-1"></i> Flip (H)
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="studioFlip('v')">
                                    <i class="fa-solid fa-arrows-up-down me-1"></i> Flip (V)
                                </button>
                            </div>
                        </div>

                        {{-- 3. Filters & Tone Adjustment --}}
                        <div class="studio-control-box">
                            <div class="studio-control-title">
                                <i class="fa-solid fa-sliders text-success"></i> Filters & Color Adjustment
                            </div>
                            <div class="d-flex flex-wrap gap-1.5 mb-3">
                                <button type="button" class="btn btn-xs btn-outline-dark rounded-pill px-2.5" onclick="setStudioQuickFilter('none')">Normal</button>
                                <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2.5" onclick="setStudioQuickFilter('crisp')">Crisp & Bright</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5" onclick="setStudioQuickFilter('bw')">Black & White</button>
                                <button type="button" class="btn btn-xs btn-outline-warning rounded-pill px-2.5" onclick="setStudioQuickFilter('vintage')">Vintage (Sepia)</button>
                            </div>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between small text-muted mb-0.5">
                                    <span>Brightness</span>
                                    <span id="studioBrightnessVal" class="font-monospace fw-bold text-dark">100%</span>
                                </div>
                                <input type="range" class="form-range" id="studioBrightness" min="30" max="180" value="100">
                            </div>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between small text-muted mb-0.5">
                                    <span>Contrast</span>
                                    <span id="studioContrastVal" class="font-monospace fw-bold text-dark">100%</span>
                                </div>
                                <input type="range" class="form-range" id="studioContrast" min="30" max="180" value="100">
                            </div>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between small text-muted mb-0.5">
                                    <span>Saturation</span>
                                    <span id="studioSaturationVal" class="font-monospace fw-bold text-dark">100%</span>
                                </div>
                                <input type="range" class="form-range" id="studioSaturation" min="0" max="200" value="100">
                            </div>
                        </div>

                        {{-- 4. Branding & Watermark Overlay --}}
                        <div class="studio-control-box">
                            <div class="studio-control-title">
                                <i class="fa-solid fa-stamp text-danger"></i> Watermark & Branding
                            </div>
                            <div class="mb-2">
                                <input type="text" id="studioWatermarkTextInput" class="form-control form-control-sm" placeholder="e.g. © IDEA Prokashon / ideaabd.com">
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="d-flex justify-content-between small text-muted mb-0.5">
                                        <span>Opacity</span>
                                        <span id="studioWatermarkOpacityVal" class="font-monospace fw-bold text-dark">60%</span>
                                    </div>
                                    <input type="range" class="form-range" id="studioWatermarkOpacity" min="10" max="100" value="60">
                                </div>
                                <div class="col-6">
                                    <label class="form-label fs-xs text-muted mb-0.5">Position</label>
                                    <select id="studioWatermarkPos" class="form-select form-select-sm" onchange="studioState.watermarkPosition = this.value; renderStudioCanvas();">
                                        <option value="bottom-right" selected>Bottom Right</option>
                                        <option value="bottom-left">Bottom Left</option>
                                        <option value="center">Center</option>
                                        <option value="top-right">Top Right</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- 5. Format & Compression --}}
                        <div class="studio-control-box">
                            <div class="studio-control-title">
                                <i class="fa-solid fa-file-export text-purple"></i> Export Format & Quality
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label fs-xs text-muted mb-0.5">Target Format</label>
                                    <select id="studioFormatSelect" class="form-select form-select-sm fw-bold">
                                        <option value="webp" selected>⚡ WebP (Optimal)</option>
                                        <option value="png">PNG (Lossless / Transparent)</option>
                                        <option value="jpg">JPG / JPEG (Standard)</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fs-xs text-muted mb-0.5">Target Folder</label>
                                    <select id="studioFolderSelect" class="form-select form-select-sm">
                                        @foreach($folderDefs as $fk => $finfo)
                                            <option value="{{ $fk }}" {{ ($folderFilter !== 'all' && $folderFilter === $fk) || ($folderFilter === 'all' && $fk === 'uploads') ? 'selected' : '' }}>{{ $finfo['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div>
                                <div class="d-flex justify-content-between small text-muted mb-0.5">
                                    <span>Quality Compression</span>
                                    <span id="studioQualityVal" class="font-monospace fw-bold text-success">85% (Optimal)</span>
                                </div>
                                <input type="range" class="form-range" id="studioQuality" min="30" max="100" value="85">
                            </div>
                        </div>

                        {{-- 6. File Name --}}
                        <div class="studio-control-box">
                            <label class="form-label small fw-bold text-dark mb-1">File Name</label>
                            <input type="text" id="studioFilenameInput" class="form-control form-control-sm font-monospace fw-semibold" placeholder="e.g. customized_book_cover">
                        </div>

                    </div>
                </div>
            </div>
            <div class="modal-footer border-top py-3 px-4 bg-white d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3.5 fw-bold" id="btnStudioOverwrite" onclick="saveCustomizedImage('overwrite')">
                        <i class="fa-solid fa-arrows-rotate me-1"></i> Overwrite Original
                    </button>
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-sm" id="btnStudioSaveCopy" onclick="saveCustomizedImage('new_copy')">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Save as New Copy
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 2: DRAG & DROP MULTI-FILE UPLOADER MODAL                            --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="uploadMediaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom py-3 px-4 bg-light rounded-top-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="fa-solid fa-cloud-arrow-up text-primary"></i>
                    <span>Media Upload Engine</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                
                {{-- Drop Zone --}}
                <div class="media-dropzone mb-3" id="mediaUploadZone" onclick="document.getElementById('multiFileInput').click()">
                    <i class="fa-solid fa-cloud-arrow-up media-dropzone-icon"></i>
                    <h6 class="fw-bold text-dark mb-1">Drag and drop images here or click to browse</h6>
                    <p class="text-muted small mb-2">Supported Formats: JPG, PNG, WEBP, SVG, GIF, AVIF, ICO | Max 10MB per file</p>
                    <span class="badge bg-primary text-white rounded-pill px-3 py-1.5 font-monospace">
                        <i class="fa-solid fa-folder-open me-1"></i> Browse Files
                    </span>
                    <input type="file" id="multiFileInput" class="d-none" multiple accept="image/*">
                </div>

                {{-- Upload Options --}}
                <div class="row g-3 p-3 bg-light rounded-3 border mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-dark">Target Folder</label>
                        <select id="uploadTargetFolder" class="form-select form-select-sm rounded-3 fw-semibold">
                            @foreach($folderDefs as $fk => $finfo)
                                <option value="{{ $fk }}" {{ ($folderFilter !== 'all' && $folderFilter === $fk) || ($folderFilter === 'all' && $fk === 'uploads') ? 'selected' : '' }}>
                                    {{ $finfo['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-dark">Max Resolution Limit</label>
                        <select id="uploadMaxDim" class="form-select form-select-sm rounded-3 fw-semibold">
                            <option value="600" selected>600px (Ultra-Compact ≤ 20KB — Optimal & Crisp)</option>
                            <option value="800">800px (Compact Web)</option>
                            <option value="1200">1200px (Standard Web)</option>
                            <option value="1920">1920px (Full HD)</option>
                            <option value="0">Keep Original Size (No Resize)</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="uploadAutoWebp" checked>
                            <label class="form-check-label small fw-semibold text-dark" for="uploadAutoWebp">
                                Auto-convert to WebP during upload (Faster Page Speed)
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Preview List for Pending Files --}}
                <div id="uploadPreviewWrap" class="d-none mb-3">
                    <label class="form-label small fw-bold text-dark mb-1.5">Selected Files:</label>
                    <div id="uploadThumbPreviewList" style="max-height: 180px; overflow-y: auto; scrollbar-width: thin;"></div>
                </div>

                {{-- Progress Bar --}}
                <div id="uploadProgressWrap" class="d-none mb-2">
                    <div class="d-flex justify-content-between small text-muted mb-1 font-monospace">
                        <span>Uploading & Optimizing...</span>
                        <span id="uploadPercentLabel">Processing</span>
                    </div>
                    <div class="media-upload-progress">
                        <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated" id="uploadProgressBar" style="width: 0%;"></div>
                    </div>
                </div>

            </div>
            <div class="modal-footer border-top py-2.5 px-4 bg-light rounded-bottom-4 d-flex justify-content-between">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-sm" id="btnSubmitUpload" onclick="submitMultiUploadAjax()">
                    <i class="fa-solid fa-cloud-arrow-up me-1"></i> Start Upload & Optimize
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 3: CREATE NEW FOLDER MODAL                                          --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="createFolderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-2.5 px-3 bg-light rounded-top-4">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="fa-solid fa-folder-plus text-warning"></i>
                    <span>Create New Folder</span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div class="mb-2.5">
                    <label class="form-label small fw-bold text-dark">Folder Name (Slug)</label>
                    <input type="text" id="newFolderNameInput" class="form-control form-control-sm font-monospace" placeholder="e.g. spring_campaign">
                </div>
                <div class="mb-1">
                    <label class="form-label small fw-bold text-dark">Storage Location</label>
                    <select id="newFolderLocSelect" class="form-select form-select-sm">
                        <option value="storage" selected>Storage Public (storage/app/public)</option>
                        <option value="public">Root Images (public/images)</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-top py-2 px-3 bg-light rounded-bottom-4">
                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-xs btn-warning text-dark fw-bold rounded-pill px-3" id="btnConfirmCreateFolder" onclick="submitCreateFolder()">
                    <i class="fa-solid fa-check me-1"></i> Create Folder
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 4: RENAME FILE MODAL                                                --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="renameFileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-2.5 px-3 bg-light rounded-top-4">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-primary"></i>
                    <span>Rename File</span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <input type="hidden" id="renamePathInput">
                <label class="form-label small fw-bold text-dark">New File Name</label>
                <input type="text" id="renameNewNameInput" class="form-control form-control-sm font-monospace fw-semibold" placeholder="new-file-name">
                <small class="text-muted fs-xs mt-1 d-block">File extension will be preserved automatically.</small>
            </div>
            <div class="modal-footer border-top py-2 px-3 bg-light rounded-bottom-4">
                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-xs btn-primary fw-bold rounded-pill px-3" id="btnConfirmRename" onclick="submitRenameFile()">
                    <i class="fa-solid fa-check me-1"></i> Save Name
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 5: BULK MOVE FOLDER MODAL                                           --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="bulkMoveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-2.5 px-3 bg-light rounded-top-4">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="fa-solid fa-folder-tree text-primary"></i>
                    <span>Move to Folder</span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <label class="form-label small fw-bold text-dark">Select Target Folder</label>
                <select id="bulkMoveTargetFolder" class="form-select form-select-sm fw-semibold">
                    @foreach($folderDefs as $fk => $finfo)
                        <option value="{{ $fk }}">{{ $finfo['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="modal-footer border-top py-2 px-3 bg-light rounded-bottom-4">
                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-xs btn-primary fw-bold rounded-pill px-3" id="btnConfirmBulkMove" onclick="executeBulkMove()">
                    <i class="fa-solid fa-arrow-right-arrow-left me-1"></i> Move Files
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 6: REPLACE MEDIA ASSET MODAL                                         --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="replaceMediaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom py-3 px-4 bg-light rounded-top-4">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="fa-solid fa-arrow-right-arrow-left text-primary"></i>
                    <span>Replace Media Asset</span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="replaceTargetPathInput">
                <div class="alert alert-primary py-2 px-3 small rounded-3 mb-3 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-circle-info fs-5 text-primary"></i>
                    <div>Replaces the asset image while keeping the exact URL and file name, with automatic optimization.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark mb-1">Target Asset:</label>
                    <div id="replaceTargetFilenameDisplay" class="font-monospace fw-bold text-primary small p-2.5 bg-light rounded border"></div>
                </div>
                <div>
                    <label class="form-label small fw-bold text-dark mb-1">Select Replacement Image:</label>
                    <input type="file" id="replaceFileInput" class="form-control form-control-sm" accept="image/*">
                </div>
            </div>
            <div class="modal-footer border-top py-2.5 px-4 bg-light rounded-bottom-4 d-flex justify-content-between">
                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-xs btn-primary fw-bold rounded-pill px-4 shadow-sm" id="btnConfirmReplace" onclick="submitReplaceFile()">
                    <i class="fa-solid fa-check me-1"></i> Replace & Save
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 7: FULLSCREEN LIGHTBOX PREVIEW MODAL (CAROUSEL & STUDIO INTEGRATED)   --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="lightboxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden bg-dark">
            <div class="modal-header border-bottom border-secondary py-2.5 px-4 bg-dark text-white d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2 text-truncate me-2">
                    <i class="fa-solid fa-expand text-primary"></i>
                    <h6 class="modal-title fw-bold text-white small text-truncate mb-0" id="lightboxTitle">Image Preview</h6>
                </div>
                <div class="d-flex align-items-center gap-1.5 flex-shrink-0">
                    <button type="button" class="btn btn-xs btn-outline-light rounded-pill px-2.5" onclick="lightboxZoom(0.2)" title="Zoom In">
                        <i class="fa-solid fa-magnifying-glass-plus"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-light rounded-pill px-2.5" onclick="lightboxZoom(-0.2)" title="Zoom Out">
                        <i class="fa-solid fa-magnifying-glass-minus"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-light rounded-pill px-2.5" onclick="lightboxResetZoom()" title="Reset Zoom">
                        <i class="fa-solid fa-rotate-left"></i>
                    </button>
                    <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body p-0 position-relative d-flex align-items-center justify-content-center bg-black overflow-hidden" style="min-height: 500px; max-height: 75vh;">
                <!-- Nav Prev Arrow -->
                <button type="button" class="lightbox-nav-btn lightbox-nav-prev" onclick="lightboxNavigate(-1)" title="Previous (Left Arrow)">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <div class="lightbox-img-wrapper d-flex align-items-center justify-content-center w-100 h-100 p-2" id="lightboxImgWrapper">
                    <img id="lightboxImage" src="" alt="Full Preview" class="img-fluid rounded shadow transition-all" style="max-height: 70vh; max-width: 88vw; object-fit: contain; transition: transform 0.2s ease;">
                </div>
                <!-- Nav Next Arrow -->
                <button type="button" class="lightbox-nav-btn lightbox-nav-next" onclick="lightboxNavigate(1)" title="Next (Right Arrow)">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>
            <div class="modal-footer border-top border-secondary py-2.5 px-4 bg-dark d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="small text-light font-monospace" id="lightboxMeta"></div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    {{-- 1. Studio Editor --}}
                    <button type="button" class="btn btn-xs btn-warning text-dark fw-bold rounded-pill px-3 shadow-xs" id="lightboxStudioBtn" onclick="openStudioFromLightbox()">
                        <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Open in Studio
                    </button>

                    {{-- 2. Replace Image --}}
                    <button type="button" class="btn btn-xs btn-outline-warning rounded-pill px-3" onclick="openReplaceFromLightbox()" title="Replace Image">
                        <i class="fa-solid fa-arrow-right-arrow-left me-1"></i> Replace
                    </button>

                    {{-- 3. Rename File --}}
                    <button type="button" class="btn btn-xs btn-outline-info rounded-pill px-3" onclick="openRenameFromLightbox()" title="Rename File">
                        <i class="fa-solid fa-pen-to-square me-1"></i> Rename
                    </button>

                    {{-- 4. Copy Snippets Dropdown --}}
                    <div class="dropdown d-inline-block">
                        <button type="button" class="btn btn-xs btn-outline-light rounded-pill px-3" data-bs-toggle="dropdown">
                            <i class="fa-regular fa-copy me-1"></i> Copy Code
                        </button>
                        <div class="dropdown-menu dropdown-menu-end snippet-dropdown-menu shadow-lg border-0 rounded-3">
                            <div class="snippet-dropdown-item" onclick="copySnippet('url', document.getElementById('lightboxImage').src, document.getElementById('lightboxTitle').textContent)">
                                <i class="fa-solid fa-link text-primary"></i> Copy Direct URL
                            </div>
                            <div class="snippet-dropdown-item" onclick="copySnippet('html', document.getElementById('lightboxImage').src, document.getElementById('lightboxTitle').textContent)">
                                <i class="fa-brands fa-html5 text-danger"></i> Copy HTML &lt;img&gt;
                            </div>
                            <div class="snippet-dropdown-item" onclick="copySnippet('markdown', document.getElementById('lightboxImage').src, document.getElementById('lightboxTitle').textContent)">
                                <i class="fa-brands fa-markdown text-info"></i> Copy Markdown
                            </div>
                            <div class="snippet-dropdown-item" onclick="copySnippet('blade', document.getElementById('lightboxImage').src, document.getElementById('lightboxTitle').textContent)">
                                <i class="fa-brands fa-laravel text-warning"></i> Copy Blade Snippet
                            </div>
                        </div>
                    </div>

                    {{-- 5. Download --}}
                    <a href="" id="lightboxDownloadBtn" download class="btn btn-xs btn-outline-success rounded-pill px-3" title="Download">
                        <i class="fa-solid fa-download me-1"></i> Download
                    </a>

                    {{-- 6. Open in Browser --}}
                    <a href="" target="_blank" class="btn btn-xs btn-outline-light rounded-pill px-3" id="lightboxOpenBtn" title="Open Original File">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Open
                    </a>

                    {{-- 7. Delete File --}}
                    <button type="button" class="btn btn-xs btn-outline-danger rounded-pill px-3" onclick="deleteFromLightbox()" title="Delete File">
                        <i class="fa-solid fa-trash-can me-1"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 8: NEXT-GEN WEBP CONVERSION & STORAGE BOOSTER ENGINE                --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="convertWebpEngineModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header border-bottom py-3 px-4 bg-dark text-white rounded-top-4">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
                    <i class="fa-solid fa-bolt-lightning text-warning"></i>
                    <span>Next-Gen WebP Conversion & Storage Booster</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <!-- Step 1: Configuration Form -->
                <div id="webpEngineConfigView">
                    <div class="alert alert-success d-flex align-items-center gap-3 p-3 rounded-3 mb-4 border-0 bg-success-subtle text-success-emphasis shadow-sm">
                        <i class="fa-solid fa-wand-magic-sparkles fs-2 text-success"></i>
                        <div>
                            <strong class="d-block fs-6">Automatic Image Compression & Speed Optimization</strong>
                            <span class="small opacity-90">Convert PNG & JPG images to modern lossless/high-quality WebP format to speed up page loading up to 70% and reclaim disk space.</span>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                <i class="fa-regular fa-folder-open text-primary me-1"></i> Target Folder
                            </label>
                            <select id="webpTargetFolderSelect" class="form-select rounded-3 fw-semibold py-2">
                                <option value="all" selected>🌐 All Folders (Entire System)</option>
                                @foreach($folderDefs as $fk => $finfo)
                                    <option value="{{ $fk }}">{{ $finfo['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                <i class="fa-solid fa-gauge-high text-success me-1"></i> WebP Quality Level
                            </label>
                            <select id="webpQualitySelect" class="form-select rounded-3 fw-semibold py-2">
                                <option value="85" selected>85% (Optimal — Fast & Crisp)</option>
                                <option value="90">90% (Ultra High Quality)</option>
                                <option value="75">75% (Maximum Storage Saving)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch p-3 bg-white rounded-3 border shadow-sm">
                                <input class="form-check-input ms-0 me-2" type="checkbox" id="webpDeleteOriginalCheck" checked>
                                <label class="form-check-label small fw-bold text-dark cursor-pointer" for="webpDeleteOriginalCheck">
                                    Delete original PNG / JPG files to reclaim disk space (Recommended)
                                </label>
                                <div class="fs-xs text-muted ms-4 ps-2">After successful conversion, the original heavy files will be removed.</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end align-items-center gap-2 pt-3 border-top">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="button" class="btn btn-webp-gradient rounded-pill px-4 py-2 shadow fw-bold" onclick="startWebpConversionEngine()">
                            <i class="fa-solid fa-bolt-lightning me-1.5"></i> Start Conversion Process
                        </button>
                    </div>
                </div>

                <!-- Step 2: Live Progress Screen -->
                <div id="webpEngineProgressView" class="d-none">
                    <div class="text-center py-3">
                        <div class="spinner-grow text-success mb-2" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <h5 class="fw-bold text-dark mb-1" id="webpProgressTitle">Scanning & Converting to WebP...</h5>
                        <p class="text-muted small mb-3" id="webpProgressSubtitle">Processing server images, please wait...</p>
                    </div>

                    <!-- Progress Bar -->
                    <div class="media-upload-progress mb-3" style="height: 10px;">
                        <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" id="webpEngineProgressBar" style="width: 25%;"></div>
                    </div>

                    <!-- Live Stat Counter Boxes -->
                    <div class="row g-2 mb-3 text-center font-monospace">
                        <div class="col-4">
                            <div class="p-2 bg-white rounded-3 border">
                                <div class="fs-xs text-muted">Status</div>
                                <div class="fw-bold text-primary small" id="webpStatusBadge">Processing...</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 bg-white rounded-3 border">
                                <div class="fs-xs text-muted">Converted</div>
                                <div class="fw-bold text-success small" id="webpConvertedCount">0 files</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 bg-white rounded-3 border">
                                <div class="fs-xs text-muted">Storage Saved</div>
                                <div class="fw-bold text-dark small" id="webpMemorySaved">0 B</div>
                            </div>
                        </div>
                    </div>

                    <!-- Live Log Terminal Box -->
                    <div class="p-2.5 bg-dark text-success rounded-3 font-monospace small" id="webpEngineLogBox" style="max-height: 150px; overflow-y: auto; font-size: 11px; scrollbar-width: thin;">
                        <div>[Init] WebP engine initialized...</div>
                        <div>[Scan] Scanning system image directories...</div>
                    </div>
                </div>

                <!-- Step 3: Finished Success Screen -->
                <div id="webpEngineSuccessView" class="d-none text-center py-4">
                    <div class="mb-3">
                        <i class="fa-solid fa-circle-check text-success fs-1 animate__animated animate__bounceIn" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-1">WebP Conversion Completed!</h4>
                    <p class="text-muted small mb-4" id="webpSuccessMessage">All images converted to modern WebP format and optimized.</p>

                    <div class="d-inline-flex gap-2">
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-sm" onclick="window.location.reload()">
                            <i class="fa-solid fa-rotate me-1"></i> Refresh Page
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Global Drag & Drop Overlay --}}
<div id="globalDragOverlay" class="global-drag-overlay d-none">
    <div class="global-drag-box">
        <i class="fa-solid fa-cloud-arrow-up global-drag-icon mb-3"></i>
        <h3 class="fw-bold text-dark mb-1">Drop Image Files Anywhere</h3>
        <p class="text-muted small mb-0">Release to auto-upload and optimize with Next-Gen WebP</p>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/admin-media.js') }}"></script>
@endpush
@endsection

